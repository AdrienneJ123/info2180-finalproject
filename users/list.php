<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireAdmin(); // Only admins can view user list

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List - Dolphin CRM</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
    <?php include '../includes/aside.php'; ?>
        <div class="main-content">
            <div class="dashboard-container">
<div class="dashboard-header">
    <h1><b>Users</b></h1>
    <a href="<?php echo htmlspecialchars('new.php'); ?>" class="new-contact-btn">
        <span>+</span> Add User
    </a>
</div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <div class="filter-container">
                <div class="contacts-table-container">
                    <table class="contacts-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                        <td><?php echo '<b>' . htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) . '</b>'; ?></td>
                                
                                <td><span class="muted-text">
                                <?php echo htmlspecialchars($user['email']); ?></td>
                            </span>
                                <td>
                                    <span class="muted-text <?php echo strtolower($user['role']); ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td> <span class="muted-text"><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:40px; color:#6b7280;">
                                    No users found.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                </div>
        </main>

</body>
</html>
