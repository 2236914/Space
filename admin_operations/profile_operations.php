<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/upload_errors.log');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Fix the path by using correct relative path and ensure PDO is available
require_once __DIR__ . '/../configs/config.php';
require_once __DIR__ . '/SessionLogger.php';

// Verify PDO connection
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Recreate connection if needed
    try {
        $host = 'localhost';
        $dbname = 'space';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die(json_encode([
            'status' => 'error',
            'message' => 'Database connection failed'
        ]));
    }
}

class ProfileOperations {
    private $pdo;
    
    public function __construct($pdo) {
        if (!($pdo instanceof PDO)) {
            throw new Exception('Invalid PDO connection');
        }
        $this->pdo = $pdo;
    }

    public function handleProfilePictureUpdate() {
        if (!isset($_FILES['profile_picture'])) {
            throw new Exception('No file uploaded');
        }

        try {
            $file = $_FILES['profile_picture'];
            error_log("Processing file: " . print_r($file, true));
            
            // Validate upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception($this->getUploadErrorMessage($file['error']));
            }
            
            // File validations...
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedType = $fileInfo->file($file['tmp_name']);
            
            if (!in_array($detectedType, $allowedTypes)) {
                throw new Exception("Invalid file type: {$detectedType}");
            }
            
            // Size validation (5MB)
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                throw new Exception("File too large. Maximum size is 5MB");
            }
            
            // Read file data
            $fileData = file_get_contents($file['tmp_name']);
            if ($fileData === false) {
                throw new Exception('Failed to read uploaded file');
            }

            // Start transaction
            $this->pdo->beginTransaction();
            
            // Deactivate old profile picture
            $stmt = $this->pdo->prepare("
                UPDATE profile_pictures 
                SET status = 'inactive' 
                WHERE user_id = ? AND user_type = ? AND status = 'active'
            ");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['role']]);
            
            // Insert new profile picture
            $stmt = $this->pdo->prepare("
                INSERT INTO profile_pictures 
                (user_id, user_type, file_name, file_type, file_data) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $_SESSION['role'],
                $file['name'],
                $detectedType,
                $fileData
            ]);

            // Log the activity
            $sessionLogger = new SessionLogger($this->pdo);
            $this->logProfileUpdate($sessionLogger, $file['name']);
            
            // Commit transaction
            $this->pdo->commit();
            
            return [
                'status' => 'success',
                'message' => 'Profile picture updated successfully'
            ];
            
        } catch (Exception $e) {
            // Rollback transaction if active
            if ($this->pdo && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Profile picture update error: " . $e->getMessage());
            throw $e;
        }
    }

    private function logProfileUpdate($sessionLogger, $fileName) {
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
            'Updated profile picture: ' . $fileName,
            $_SERVER['REMOTE_ADDR']
        );
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

            if($result) {
                // Log the activity
                $sessionLogger = new SessionLogger($this->pdo);
                $sessionLogger->logActivity(
                    $_SESSION['user_id'],
                    null,
                    null,
                    'Updated Profile',
                    'Profile information updated',
                    $_SERVER['REMOTE_ADDR']
                );
                
                return ['success' => true, 'message' => 'Profile updated successfully'];
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
        try {
            $stmt = $this->pdo->prepare("
                SELECT file_type, file_data 
                FROM profile_pictures 
                WHERE user_id = ? 
                AND user_type = ? 
                AND status = 'active' 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId, $userType]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching profile picture: " . $e->getMessage());
            return null;
        }
    }

    // Add new helper method
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}