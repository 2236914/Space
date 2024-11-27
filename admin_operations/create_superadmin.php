<?php
require_once '../configs/config.php';

function createSuperAdmin() {
    global $pdo;
    
    try {
        // Check if superadmin already exists
        $check_stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE role = 'superadmin'");
        $check_stmt->execute();
        if ($check_stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Superadmin already exists'];
        }

        // Superadmin details
        $admin_id = "AD-00001"; // Fixed ID for superadmin
        $firstname = "Space";
        $lastname = "Admin";
        $email = "space.admin@cvsu.edu.ph";
        $password = "SpaceAdmin2024"; // You should change this
        $contact_number = "09123456789";
        $role = "superadmin";

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert superadmin
        $stmt = $pdo->prepare("
            INSERT INTO admins (
                admin_id, 
                firstname, 
                lastname, 
                email, 
                password, 
                contact_number, 
                role
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $admin_id,
            $firstname,
            $lastname,
            $email,
            $hashed_password,
            $contact_number,
            $role
        ]);

        return [
            'success' => true,
            'message' => 'Superadmin created successfully',
            'credentials' => [
                'email' => $email,
                'password' => $password // Show this only once during setup
            ]
        ];

    } catch (PDOException $e) {
        error_log("Error creating superadmin: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

// Only run this if explicitly requested
if (isset($_GET['setup']) && $_GET['setup'] === 'initial') {
    $result = createSuperAdmin();
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<h2>Superadmin Created Successfully!</h2>";
        echo "<p>Please save these credentials securely:</p>";
        echo "<ul>";
        echo "<li>Email: " . htmlspecialchars($result['credentials']['email']) . "</li>";
        echo "<li>Password: " . htmlspecialchars($result['credentials']['password']) . "</li>";
        echo "</ul>";
        echo "<p style='color: red;'>Make sure to change the password after first login!</p>";
    } else {
        echo "<h2>Error</h2>";
        echo "<p>" . htmlspecialchars($result['message']) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Superadmin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .warning {
            color: red;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .credentials {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Space Superadmin Setup</h1>
        <?php if (!isset($_GET['setup'])): ?>
            <p class="warning">Warning: This script will create the superadmin account. 
            It should only be run once during initial setup.</p>
            <p>Click the button below to create the superadmin account:</p>
            <form method="get">
                <input type="hidden" name="setup" value="initial">
                <button type="submit">Create Superadmin</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>