<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit();
}

$contact_id = $_GET['id'];
$contact = getContactById($contact_id);
$notes = getNotesByContactId($contact_id);

if (!$contact) {
    header('Location: ../dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Details - Dolphin CRM</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../includes/aside.php'; ?>
     <div class="main-content">
    <div class="container">
        <div class="contact-header">
            <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <div class="contact-actions">
                <button class="btn btn-primary" id="assignToMe">Assign to Me</button>
                <button class="btn btn-switch-type">
                    Switch to <?php echo $contact['type'] === 'Sales Lead' ? 'Support' : 'Sales Lead'; ?>
                </button>
            </div>
        </div>
        
        <div class="contact-details">
            <div class="contact-info">
                <h1><?php echo htmlspecialchars($contact['title'] . ' ' . $contact['firstname'] . ' ' . $contact['lastname']); ?></h1>
                <p class="contact-meta">
                    Created on <?php echo date('F j, Y', strtotime($contact['created_at'])); ?> 
                    by <?php echo htmlspecialchars($contact['creator_first'] . ' ' . $contact['creator_last']); ?>
                    <br>
                    Updated on <?php echo date('F j, Y', strtotime($contact['updated_at'])); ?>
                </p>
                
                <div class="contact-grid">
                    <div class="contact-item">
                        <strong>Email:</strong>
                        <span><?php echo htmlspecialchars($contact['email']); ?></span>
                    </div>
                    <div class="contact-item">
                        <strong>Telephone:</strong>
                        <span><?php echo htmlspecialchars($contact['telephone']); ?></span>
                    </div>
                    <div class="contact-item">
                        <strong>Company:</strong>
                        <span><?php echo htmlspecialchars($contact['company']); ?></span>
                    </div>
                    <div class="contact-item">
                        <strong>Assigned To:</strong>
                        <span><?php echo htmlspecialchars($contact['assignee_first'] . ' ' . $contact['assignee_last']); ?></span>
                    </div>
                    <div class="contact-item">
                        <strong>Type:</strong>
                        <span class="type-badge <?php echo strtolower(str_replace(' ', '-', $contact['type'])); ?>">
                            <?php echo $contact['type']; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="notes-section">
                <h2>Notes</h2>
                <div id="notes-container">
                    <?php foreach ($notes as $note): ?>
                    <div class="note">
                        <div class="note-header">
                            <strong><?php echo htmlspecialchars($note['firstname'] . ' ' . $note['lastname']); ?></strong>
                            <span class="note-date"><?php echo date('F j, Y \a\t g:ia', strtotime($note['created_at'])); ?></span>
                        </div>
                        <div class="note-content">
                            <?php echo nl2br(htmlspecialchars($note['comment'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="add-note">
                    <h3>Add a note about <?php echo htmlspecialchars($contact['firstname']); ?></h3>
                    <textarea id="new-note" placeholder="Enter details here" rows="4"></textarea>
                    <button id="save-note" class="btn btn-primary">Save Note</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        $('#save-note').click(function() {
            var note = $('#new-note').val();
            var contactId = <?php echo $contact_id; ?>;
            
            if (note.trim() === '') {
                alert('Please enter a note');
                return;
            }
            
            $.ajax({
                url: '../api/notes.php',
                type: 'POST',
                data: {
                    action: 'add',
                    contact_id: contactId,
                    comment: note
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error saving note');
                    }
                }
            });
        });
        
        $('#assignToMe').click(function() {
            $.ajax({
                url: '../api/contacts.php',
                type: 'POST',
                data: {
                    action: 'assign',
                    contact_id: <?php echo $contact_id; ?>,
                    assign_to: <?php echo $_SESSION['user_id']; ?>
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        });
        
        $('.btn-switch-type').click(function() {
            var newType = $(this).text().includes('Sales') ? 'Sales Lead' : 'Support';
            
            $.ajax({
                url: '../api/contacts.php',
                type: 'POST',
                data: {
                    action: 'switch_type',
                    contact_id: <?php echo $contact_id; ?>,
                    new_type: newType
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        });
    });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</div>
</body>
</html>