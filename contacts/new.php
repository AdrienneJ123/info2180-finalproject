<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$users = getUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $firstname = sanitizeInput($_POST['firstname']);
    $lastname = sanitizeInput($_POST['lastname']);
    $email = sanitizeInput($_POST['email']);
    $telephone = sanitizeInput($_POST['telephone']);
    $company = sanitizeInput($_POST['company']);
    $type = sanitizeInput($_POST['type']);
    $assigned_to = sanitizeInput($_POST['assigned_to']);
    $created_by = $_SESSION['user_id'];
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        INSERT INTO contacts (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    if ($stmt->execute([$title, $firstname, $lastname, $email, $telephone, $company, $type, $assigned_to, $created_by])) {
        header('Location: ../dashboard.php?success=Contact added successfully');
        exit();
    } else {
        $error = "Failed to add contact";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>

<?php include '../includes/aside.php'; ?>

    <div class="main-content">
        <div class="dashboard-container">
            <!-- Header -->
            <div class="dashboard-header">
                <h1><b>New Contact</b></h1>
            </div>

            <!-- Error -->
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <!-- Form Card -->
            <div class="contacts-table-container">
                <form method="POST" action="new.php" class="contact-form" style="padding: 24px;">

                  
                        <div class="form-group form-title"  >
                            <label for="title">Title</label>
                            <select id="title" name="title" required>
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Ms">Ms</option>
                                <option value="Dr">Dr</option>
                                <option value="Prof">Prof</option>
                            </select>
                        </div>
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
  <div class="form-row">

                    <div class="form-group">
                        <label for="email">Email </label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="telephone">Telephone</label>
                        <input type="tel" id="telephone" name="telephone">
                    </div>
            </div>
              <div class="form-row">
                    <div class="form-group">
                        <label for="company">Company</label>
                        <input type="text" id="company" name="company">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Type </label>
                            <select id="type" name="type" required>
                                <option value="Sales Lead">Sales Lead</option>
                                <option value="Support">Support</option>
                            </select>
                        </div>
            </div>
            </div>
                        <div class="form-group form-assign">
                            <label for="assigned_to">Assigned To </label>
                            <select id="assigned_to" name="assigned_to" required>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                         <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="new-contact-btn">
                            Save Contact
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            Reset
                        </button>
                    </div>

                    </div>

                   
                </form>
            </div>
                                
        </div>
</body>
</html>