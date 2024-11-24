<?php
// Prevent direct access to this file
if (!defined('ALLOW_ACCESS')) {
    header("Location: ../index.php");
    exit();
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'update_profile_picture') {
    header('Content-Type: application/json');
    $profileOps = new ProfileOperations($pdo);
    echo json_encode($profileOps->handleProfilePictureUpdate());
    exit;
}

class ProfileOperations {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function updateStudentProfile($data) {
        try {
            // Debug log
            error_log('Starting updateStudentProfile with data: ' . print_r($data, true));

            // Validate phone number format
            if (!preg_match('/^09\d{9}$/', $data['phonenum'])) {
                error_log('Invalid phone number format: ' . $data['phonenum']);
                return false;
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                error_log('Invalid email format: ' . $data['email']);
                return false;
            }

            // Validate year (if provided)
            if (!empty($data['year']) && (!is_numeric($data['year']) || $data['year'] < 1 || $data['year'] > 5)) {
                error_log('Invalid year value: ' . $data['year']);
                return false;
            }

            // Check if email already exists for different student
            $stmt = $this->pdo->prepare("SELECT srcode FROM students WHERE email = ? AND srcode != ?");
            $stmt->execute([$data['email'], $data['srcode']]);
            if ($stmt->fetch()) {
                error_log('Email already exists for another student');
                throw new Exception('Email address is already in use by another student.');
            }

            // Check if phone number already exists for different student
            $stmt = $this->pdo->prepare("SELECT srcode FROM students WHERE phonenum = ? AND srcode != ?");
            $stmt->execute([$data['phonenum'], $data['srcode']]);
            if ($stmt->fetch()) {
                error_log('Phone number already exists for another student');
                throw new Exception('Phone number is already in use by another student.');
            }

            // Prepare the SQL with proper type handling
            $sql = "UPDATE students SET 
                    firstname = :firstname,
                    lastname = :lastname,
                    phonenum = :phonenum,
                    email = :email,
                    department = NULLIF(:department, ''),
                    course = NULLIF(:course, ''),
                    year = NULLIF(:year, ''),
                    section = NULLIF(:section, ''),
                    address = NULLIF(:address, ''),
                    personality = NULLIF(:personality, '')
                    WHERE srcode = :srcode";

            $stmt = $this->pdo->prepare($sql);
            
            // Bind parameters with proper type handling
            $params = [
                ':firstname' => trim($data['firstname']),
                ':lastname' => trim($data['lastname']),
                ':phonenum' => trim($data['phonenum']),
                ':email' => trim($data['email']),
                ':department' => trim($data['department'] ?? ''),
                ':course' => trim($data['course'] ?? ''),
                ':year' => !empty($data['year']) ? (int)$data['year'] : '',
                ':section' => trim($data['section'] ?? ''),
                ':address' => trim($data['address'] ?? ''),
                ':personality' => trim($data['personality'] ?? ''),
                ':srcode' => $data['srcode']
            ];

            // Debug log
            error_log('Executing SQL with params: ' . print_r($params, true));

            $result = $stmt->execute($params);
            
            // Check if any rows were actually updated
            if ($result && $stmt->rowCount() === 0) {
                error_log('No rows were updated. SRCode might not exist: ' . $data['srcode']);
                return false;
            }

            // Debug log
            error_log('Update result: ' . ($result ? 'true' : 'false') . ', Rows affected: ' . $stmt->rowCount());
            if (!$result) {
                error_log('PDO Error Info: ' . print_r($stmt->errorInfo(), true));
            }

            return $result;
        } catch (PDOException $e) {
            error_log('Database error in updateStudentProfile: ' . $e->getMessage());
            throw new Exception('Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            error_log('General error in updateStudentProfile: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getStudentData($srcode) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM students WHERE srcode = ?");
            $stmt->execute([$srcode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // You might want to log the error here
            return false;
        }
    }

    public function updateProfilePicture($userId, $userType, $fileData, $fileName, $fileType) {
        try {
            $this->pdo->beginTransaction();
            
            // Deactivate old profile picture
            $stmt = $this->pdo->prepare("
                UPDATE profile_pictures 
                SET status = 'inactive' 
                WHERE user_id = ? AND user_type = ? AND status = 'active'
            ");
            $stmt->execute([$userId, $userType]);
            
            // Insert new profile picture
            $stmt = $this->pdo->prepare("
                INSERT INTO profile_pictures 
                (user_id, user_type, file_name, file_type, file_data) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $userType, $fileName, $fileType, $fileData]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Profile picture update error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getProfilePicture($userId, $userType) {
        $stmt = $this->pdo->prepare("
            SELECT file_type, file_data 
            FROM profile_pictures 
            WHERE user_id = ? AND user_type = ? AND status = 'active' 
            ORDER BY upload_date DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId, $userType]);
        return $stmt->fetch();
    }

    public function handleProfilePictureUpdate() {
        if (isset($_FILES['profile_picture'])) {
            try {
                $file = $_FILES['profile_picture'];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                
                // Get file info
                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedType = finfo_file($fileInfo, $file['tmp_name']);
                finfo_close($fileInfo);
                
                // Get file extension
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($detectedType, $allowedTypes) || !in_array($extension, $allowedExtensions)) {
                    throw new Exception('Invalid file type. Only JPG, JPEG, and PNG files are allowed.');
                }
                
                // Validate file size (5MB max)
                $maxSize = 5 * 1024 * 1024;
                if ($file['size'] > $maxSize) {
                    throw new Exception('File size too large. Maximum size is 5MB.');
                }
                
                $fileData = file_get_contents($file['tmp_name']);
                $result = $this->updateProfilePicture(
                    $_SESSION['user_id'],
                    $_SESSION['role'],
                    $fileData,
                    $file['name'],
                    $file['type']
                );
                
                // Log the activity
                $sessionLogger = new SessionLogger($this->pdo);
                $user_id = $_SESSION['user_id'];
                $user_type = $_SESSION['role'];
                
                $srcode = ($user_type === 'student') ? $user_id : null;
                $therapist_id = ($user_type === 'therapist') ? $user_id : null;
                $admin_id = ($user_type === 'admin') ? $user_id : null;
                
                $sessionLogger->logActivity(
                    $srcode,
                    $therapist_id, 
                    $admin_id,
                    'UPDATE_PROFILE_PICTURE',
                    'Updated profile picture: ' . $file['name'],
                    $_SERVER['REMOTE_ADDR']
                );
                
                return ['status' => 'success', 'message' => 'Profile picture updated successfully'];
            } catch (Exception $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
        return ['status' => 'error', 'message' => 'No file uploaded'];
    }
}