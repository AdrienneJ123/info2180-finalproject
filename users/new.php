<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin(); // Only admins can add users

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = sanitizeInput($_POST['firstname']);
    $lastname = sanitizeInput($_POST['lastname']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $role = sanitizeInput($_POST['role']);
    
    $errors = [];
    
    // Validate password
    if (!validatePassword($password)) {
        $errors[] = "Password must be at least 8 characters with one number, one letter, and one capital letter";
    }
    
    // Check if email exists
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already exists";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("
            INSERT INTO users (firstname, lastname, password, email, role, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$firstname, $lastname, $hashed_password, $email, $role])) {
            header('Location: list.php?success=User added successfully');
            exit();
        } else {
            $errors[] = "Failed to add user";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - Dolphin CRM</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
<?php include '../includes/aside.php'; ?>
    <div class="main-content">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><b> New User</b> </h1>
            </div>
            <!-- Errors -->
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="contacts-table-container">
                
                <form method="POST" action="new.php" class="user-form" style="padding: 24px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First Name </label>
                            <input type="text" id="firstname" name="firstname" required>
                        </div>

                        <div class="form-group">
                            <label for="lastname">Last Name </label>
                            <input type="text" id="lastname" name="lastname" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email </label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password </label>
                        <input type="password" id="password" name="password" required>
                        
                    </div>

                    <div class="form-group">
                        <label for="role">Role </label>
                        <select id="role" name="role" required>
                              <option value="Member">Member</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="new-contact-btn">Save User</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>

                </form>
        </div>
</div>
</body>
</html>