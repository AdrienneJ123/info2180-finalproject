<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$filter = $_GET['filter'] ?? 'all';
$userId = $_SESSION['user_id'];
$conn = getDBConnection();

// Build query based on filter
switch ($filter) {
    case 'sales':
        $sql = "SELECT * FROM contacts WHERE type = 'Sales Lead'";
        break;
    case 'support':
        $sql = "SELECT * FROM contacts WHERE type = 'Support'";
        break;
    case 'assigned':
        $sql = "SELECT * FROM contacts WHERE assigned_to = ?";
        $params = [$userId];
        break;
    default:
        $sql = "SELECT * FROM contacts";
        $params = [];
}

$stmt = $conn->prepare($sql);
$stmt->execute($params ?? []);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dolphin CRM</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'includes/aside.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <a href="contacts/new.php" class="btn btn-primary">+ Add New Contact</a>
            </div>
             
        <div class="filters">
            <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All Contacts</a>
            <a href="?filter=sales" class="<?php echo $filter === 'sales' ? 'active' : ''; ?>">Sales Leads</a>
            <a href="?filter=support" class="<?php echo $filter === 'support' ? 'active' : ''; ?>">Support</a>
            <a href="?filter=assigned" class="<?php echo $filter === 'assigned' ? 'active' : ''; ?>">Assigned to Me</a>
        </div>
          <div class="contacts-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($contact['title'] . ' ' . $contact['firstname'] . ' ' . $contact['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td><?php echo htmlspecialchars($contact['company']); ?></td>
                        <td><span class="type-badge <?php echo strtolower(str_replace(' ', '-', $contact['type'])); ?>"><?php echo $contact['type']; ?></span></td>
                        <td><a href="contacts/view.php?id=<?php echo $contact['id']; ?>" class="btn btn-view">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
        
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>