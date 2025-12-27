<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$filter = $_GET['filter'] ?? 'all';
$userId = $_SESSION['user_id'];
$conn = getDBConnection();

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
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><b>Dashboard</b></h1>
                <a href="contacts/new.php" class="new-contact-btn">
                    <span>+</span> Add Contact
                </a>
            </div>
            <div class="filter-container">
            <div class="filters-section">
                <div class="filters-title"> <b><i class="bi bi-funnel-fill filt-fun"></i> </b>  <b>Filter By:</b> </div>
                <div class="filters">
                  <a href="?filter=all" class="filter-link <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                    <a href="?filter=sales" class="filter-link <?php echo $filter === 'sales' ? 'active' : ''; ?>">Sales Leads</a>
                    <a href="?filter=support" class="filter-link <?php echo $filter === 'support' ? 'active' : ''; ?>">Support</a>
                    <a href="?filter=assigned" class="filter-link <?php echo $filter === 'assigned' ? 'active' : ''; ?>">Assigned to me</a>
                </div>
            </div>
            
            <div class="contacts-table-container">
                <table class="contacts-table">
                    <thead>
                        <tr>
                            
                            <th> <b>Name</b></th>
                            <th> <b>Email</b></th>
                            <th> <b>Company</b></th>
                            <th> <b>Type</b></th>
                            <th> <b></b></th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                        <?php
                            // Format the name with title
                            $fullName = '';
                            if (!empty($contact['title'])) {
                                $fullName .= '<b>' .htmlspecialchars($contact['title']) . '</b> ';
                            }
                           if (!empty($contact['firstname'])) {
    $fullName .= '<b>' . htmlspecialchars($contact['firstname']) . '</b> ';
}

                            if (!empty($contact['lastname'])) {
                                $fullName .= '<b>' .htmlspecialchars($contact['lastname']) . '</b> ';
                            }
                            $fullName = trim($fullName);
                            
                            // Determine badge class
                            $badgeClass = strtolower(str_replace(' ', '-', $contact['type']));
                        ?>
                        <tr>
                        <td><?php echo $fullName ?: 'N/A'; ?></td>
                          <td>
                        <span class="muted-text">
                            <?php echo htmlspecialchars($contact['email'] ?? 'N/A'); ?>
                        </span>
                    </td>

                    <td>
                        <span class="muted-text">
                            <?php echo htmlspecialchars($contact['company'] ?? 'N/A'); ?>
                        </span>
                    </td>


                            <td>
                                <?php if (!empty($contact['type'])): ?>
                                <span class="type-badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($contact['type']); ?>
                                </span>
                                <?php else: ?>
                                N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="contacts/view.php?id=<?php echo $contact['id']; ?>" class="view-btn">
                                    View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($contacts)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #6b7280;">
                                No contacts found. Try adjusting your filters or 
                                <a href="contacts/new.php" style="color: #4f46e5;">add a new contact</a>.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
    </div>
</body>
</html>