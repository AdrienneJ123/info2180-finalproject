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
    <title>Contact Details | Dolphin CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>

<?php include '../includes/aside.php'; ?>
<div class="main-content">
    <div class="dashboard-container">
        <div class="contact-top-row">
            <div class="contact-title">
                <div class="avatar-circle">
                    <?php
                        echo strtoupper($contact['firstname'][0] . $contact['lastname'][0]);
                    ?>
                </div>

                <div>
                    <h1>
                        <?php echo htmlspecialchars($contact['title'] . '. ' . $contact['firstname'] . ' ' . $contact['lastname']); ?>
                    </h1>
                    <p>
                        Created on <?php echo date('F j, Y', strtotime($contact['created_at'])); ?>
                        by <?php echo htmlspecialchars($contact['creator_first'] . ' ' . $contact['creator_last']); ?><br>
                        Updated on <?php echo date('F j, Y', strtotime($contact['updated_at'])); ?>
                    </p>
                </div>
            </div>

            <div class="contact-actions">
                <button id="assignToMe" class="btn-assign">
                    ✋ Assign to me
                </button>

               <button class="btn-switch <?php echo ($contact['type'] === 'Sales Lead') ? 'sales' : 'support'; ?>">
    <i class="bi bi-arrow-left-right"></i>
    <b>
        Switch to <?php echo ($contact['type'] === 'Sales Lead') ? 'Sales Lead' : 'Support'; ?>
    </b>
</button>

            </div>
        </div>

        <div class="contact-info-card">
            <div>
                <label>Email</label>
                <p><?php echo htmlspecialchars($contact['email']); ?></p>
            </div>

            <div>
                <label>Telephone</label>
                <p><?php echo htmlspecialchars($contact['telephone']); ?></p>
            </div>

            <div>
                <label>Company</label>
                <p><?php echo htmlspecialchars($contact['company']); ?></p>
            </div>

            <div>
                <label>Assigned To</label>
                <p><?php echo htmlspecialchars($contact['assignee_first'] . ' ' . $contact['assignee_last']); ?></p>
            </div>
        </div>

        <!-- NOTES -->
        <div class="notes-card">
            <div class="notes-header">
                ✎ Notes
            </div>

            <?php foreach ($notes as $note): ?>
                <div class="note-item">
                    <strong><?php echo htmlspecialchars($note['firstname'] . ' ' . $note['lastname']); ?></strong>
                    <p><?php echo nl2br(htmlspecialchars($note['comment'])); ?></p>
                 
<span><?php echo date('Y-m-d H:i', strtotime($note['created_at'])); ?></span>                                 
                </div>
            <?php endforeach; ?>

            <div class="add-note">
                <label>Add a note about <?php echo htmlspecialchars($contact['firstname']); ?></label>
                <textarea id="new-note" placeholder="Enter details here"></textarea>
                <button id="save-note" class="new-contact-btn note-added">Add Note</button>
            </div>
        </div>

    </div>


<script>
$(document).ready(function() {

    $('#save-note').click(function() {
        let note = $('#new-note').val().trim();
        if (!note) return alert('Please enter a note');

        $.post('../api/notes.php', {
            action: 'add',
            contact_id: <?php echo $contact_id; ?>,
            comment: note
        }, function(response) {
            if (response.success) location.reload();
        }, 'json');
    });

    $('#assignToMe').click(function() {
        $.post('../api/contacts.php', {
            action: 'assign',
            contact_id: <?php echo $contact_id; ?>,
            assign_to: <?php echo $_SESSION['user_id']; ?>
        }, function(response) {
            if (response.success) location.reload();
        }, 'json');
    });

    $('.btn-switch').click(function() {
        let newType = $(this).text().includes('Sales') ? 'Sales Lead' : 'Support';

        $.post('../api/contacts.php', {
            action: 'switch_type',
            contact_id: <?php echo $contact_id; ?>,
            new_type: newType
        }, function(response) {
            if (response.success) location.reload();
        }, 'json');
    });

});
</script>

</body>
</html>