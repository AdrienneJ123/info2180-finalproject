<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit();
}

$contact_id = (int)$_GET['id'];
$contact = getContactById($contact_id);
$notes = getNotesByContactId($contact_id);

if (!$contact) {
    header('Location: ../dashboard.php');
    exit();
}

// Check if current user is assigned to this contact
$is_assigned = ($contact['assigned_to'] == $_SESSION['user_id']);
$is_admin = ($_SESSION['user_role'] == 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Details | Dolphin CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-switch {
            padding: 10px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
            min-width: 200px;
            justify-content: center;
        }

        .btn-switch.purple {
            background-color: #9333ea !important;
            color: white !important;
        }

        .btn-switch.yellow {
            background-color: #facc15 !important;
            color: #000 !important;
        }

        .btn-switch:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn-switch:disabled {
            background-color: #95a5a6 !important;
            color: #6b7280 !important;
            cursor: not-allowed;
        }

        .btn-assign {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-assign:hover:not(:disabled) {
            background-color: #2980b9;
        }

        .btn-assign:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }

        #alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 10px;
            display: block;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .alert-success {
            background-color: #10b981;
            color: white;
            border-left: 4px solid #059669;
        }

        .alert-error {
            background-color: #ef4444;
            color: white;
            border-left: 4px solid #dc2626;
        }

        .spin {
            display: inline-block;
            animation: spin 1s linear infinite;
            margin-right: 5px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-primary {
            background-color: #3b82f6;
            color: white;
        }

        .badge-success {
            background-color: #10b981;
            color: white;
        }
    </style>
</head>
<body>

<?php include '../includes/aside.php'; ?>
<div class="main-content">
    <div class="dashboard-container">
        <div class="contact-top-row">
            <div class="contact-title">
                <div class="avatar-circle">
                    <?php echo strtoupper($contact['firstname'][0] . $contact['lastname'][0]); ?>
                </div>
                <div>
                    <h1>
                        <?php echo htmlspecialchars($contact['title'] . '. ' . $contact['firstname'] . ' ' . $contact['lastname']); ?>
                    </h1>
                    <p id="contact-timestamp">
                        Created on <?php echo date('F j, Y', strtotime($contact['created_at'])); ?>
                        by <?php echo htmlspecialchars($contact['creator_first'] . ' ' . $contact['creator_last']); ?><br>
                        Updated on <span id="updated-time"><?php echo date('F j, Y \a\t g:i A', strtotime($contact['updated_at'])); ?></span>
                    </p>
                </div>
            </div>

            <div class="contact-actions">
                <!-- Assign to me button -->
                <button class="btn-assign assign-to-me" 
                        data-contact-id="<?php echo $contact_id; ?>"
                        <?php echo $is_assigned ? 'disabled' : ''; ?>>
                    ✋ <?php echo $is_assigned ? 'Already Assigned to You' : 'Assign to Me'; ?>
                </button>

                <?php 
                $currentType = $contact['type'];
                $switchToType = ($currentType === 'Sales Lead') ? 'Support' : 'Sales Lead';
                // Button color should be opposite of current type
                $buttonColor = ($currentType === 'Sales Lead') ? 'yellow' : 'purple';
                ?>
                <button class="btn-switch toggle-type <?php echo $buttonColor; ?>"
                        data-contact-id="<?php echo $contact_id; ?>"
                        data-current-type="<?php echo htmlspecialchars($currentType); ?>"
                        <?php echo ($is_assigned || $is_admin) ? '' : 'disabled'; ?>>
                    <i class="bi bi-arrow-left-right"></i>
                    <b>Switch to <?php echo htmlspecialchars($switchToType); ?></b>
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
                <p id="assigned-to-text"><?php echo htmlspecialchars($contact['assignee_first'] . ' ' . $contact['assignee_last']); ?></p>
            </div>
            <div>
                <label>Type</label>
                <p><span class="badge badge-<?php echo $currentType === 'Sales Lead' ? 'primary' : 'success'; ?>" id="contact-type-badge">
                    <?php echo htmlspecialchars($currentType); ?>
                </span></p>
            </div>
        </div>

        <!-- NOTES SECTION -->
        <div class="notes-card">
            <div class="notes-header" id="notes-header">
                ✎ Notes (<?php echo count($notes); ?>)
            </div>
            
            <div id="notes-container">
                <?php foreach ($notes as $note): ?>
                    <div class="note-item" id="note-<?php echo $note['id']; ?>">
                        <div class="note-header">
                            <span class="note-author">
                                <?php echo htmlspecialchars($note['firstname'] . ' ' . $note['lastname']); ?>
                            </span>
                            <span class="note-date">
                                <?php echo date('F j, Y \a\t g:i A', strtotime($note['created_at'])); ?>
                            </span>
                        </div>
                        <div class="note-content">
                            <?php echo nl2br(htmlspecialchars($note['comment'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($notes)): ?>
                    <div class="no-notes" id="no-notes">No notes yet. Add the first note!</div>
                <?php endif; ?>
            </div>
            
            <div class="add-note">
                <label>Add a note about <?php echo htmlspecialchars($contact['firstname']); ?></label>
                <textarea id="new-note" placeholder="Enter note details here..."></textarea>
                <button id="save-note" class="note-added">Add Note</button>
            </div>
        </div>
    </div>
</div>

<div id="alert-container"></div>

<script>
$(document).ready(function() {
    // Show alert function
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type}">
                ${message}
            </div>
        `;
        $('#alert-container').append(alertHtml);
        
        // Remove alert after 3 seconds
        setTimeout(() => {
            $('#alert-container .alert').first().fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Format date for display
    function formatDateTime(date) {
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        
        const month = months[date.getMonth()];
        const day = date.getDate();
        const year = date.getFullYear();
        const hour = date.getHours() % 12 || 12;
        const minute = date.getMinutes().toString().padStart(2, '0');
        const ampm = date.getHours() >= 12 ? 'pm' : 'am';
        
        return `${month} ${day}, ${year} at ${hour}:${minute}${ampm}`;
    }

    // Update timestamp function
    function updateTimestamp() {
        const now = new Date();
        $('#updated-time').text(formatDateTime(now));
    }

    // Add note functionality
    $('#save-note').click(function() {
        const button = $(this);
        const contactId = <?php echo $contact_id; ?>;
        const comment = $('#new-note').val().trim();
        
        if (!comment) {
            showAlert('Please enter a note', 'error');
            return;
        }
        
        button.prop('disabled', true).text('Adding...');
        
        $.ajax({
            url: '../ajax/add_note.php',
            type: 'POST',
            data: { 
                contact_id: contactId,
                comment: comment 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Clear the textarea
                    $('#new-note').val('');
                    
                    // Add the new note to the top of the list
                    const noteHtml = `
                        <div class="note-item" id="note-${response.note.id}">
                            <div class="note-header">
                                <span class="note-author">${response.note.author}</span>
                                <span class="note-date">${response.note.date}</span>
                            </div>
                            <div class="note-content">
                                ${response.note.comment}
                            </div>
                        </div>
                    `;
                    
                    $('#notes-container').prepend(noteHtml);
                    
                    // Update notes count
                    const noteCount = $('#notes-container .note-item').length;
                    $('#notes-header').html(`✎ Notes (${noteCount})`);
                    
                    // Remove "no notes" message if present
                    $('#no-notes').remove();
                    
                    // Update timestamp
                    updateTimestamp();
                    
                    showAlert(response.message, 'success');
                } else {
                    showAlert(response.message, 'error');
                }
                button.prop('disabled', false).text('Add Note');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                button.prop('disabled', false).text('Add Note');
            }
        });
    });

    // Allow Enter key to submit note (Shift+Enter for new line)
    $('#new-note').keydown(function(e) {
        if (e.keyCode === 13 && !e.shiftKey) {
            e.preventDefault();
            $('#save-note').click();
        }
    });

    // Toggle contact type functionality - CORRECTED VERSION
    $('.toggle-type').click(function(e) {
        e.preventDefault();
        
        const button = $(this);
        const contactId = button.data('contact-id');
        const currentType = button.data('current-type');
        const newType = currentType === 'Sales Lead' ? 'Support' : 'Sales Lead';
        
        if (button.prop('disabled')) return;
        
        if (!confirm(`Are you sure you want to change this contact from ${currentType} to ${newType}?`)) {
            return;
        }
        
        // Store original button content
        const originalHtml = button.html();
        
        button.prop('disabled', true);
        button.html('<i class="bi bi-arrow-repeat spin"></i> Changing...');
        
        $.ajax({
            url: '../ajax/update_contact_type.php',
            type: 'POST',
            data: { 
                contact_id: contactId,
                new_type: newType 
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                
                if (response.success) {
                    showAlert(response.message, 'success');
                    
                    // Update button data attribute
                    button.data('current-type', newType);
                    
                    // Calculate the OPPOSITE type for the button text
                    const oppositeType = newType === 'Sales Lead' ? 'Support' : 'Sales Lead';
                    
                    // Update button text
                    button.html(`<i class="bi bi-arrow-left-right"></i> <b>Switch to ${oppositeType}</b>`);
                    
                    // Update button color
                    button.removeClass('purple yellow');
                    if (newType === 'Sales Lead') {
                        // If contact is NOW Sales Lead, button should show "Switch to Support" (purple)
                        button.addClass('yellow');
                    } else {
                        // If contact is NOW Support, button should show "Switch to Sales Lead" (yellow)
                        button.addClass('purple');
                    }
                    
                    // Update contact type badge
                    const badge = $('#contact-type-badge');
                    badge.text(newType)
                        .removeClass('badge-primary badge-success');
                    
                    if (newType === 'Sales Lead') {
                        badge.addClass('badge-primary');
                    } else {
                        badge.addClass('badge-success');
                    }
                    
                    // Update timestamp
                    updateTimestamp();
                    
                    // Re-enable button
                    button.prop('disabled', false);
                    
                } else {
                    showAlert(response.message, 'error');
                    button.html(originalHtml);
                    button.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                showAlert('An error occurred. Please try again.', 'error');
                button.html(originalHtml);
                button.prop('disabled', false);
            }
        });
    });

    $('.assign-to-me').click(function() {
        const button = $(this);
        const contactId = button.data('contact-id');
        
        if (button.prop('disabled')) return;
        
        button.prop('disabled', true).text('Assigning...');
        
        console.log('Attempting to assign contact ID:', contactId);
        
        $.ajax({
            url: '../ajax/assign_contact.php',
            type: 'POST',
            data: { contact_id: contactId },
            dataType: 'json',
            success: function(response) {
                console.log('Assign response:', response);
                
                if (response.success) {
                    showAlert(response.message, 'success');
                    
                    // Update UI without reload
                    button.prop('disabled', true).text('✋ Already Assigned to You');
                    
                    // Update the "Assigned To" field
                    $('#assigned-to-text').text(response.assignee_name || 'You');
                    
                    // Update timestamp
                    updateTimestamp();
                    
                } else {
                    // Show detailed error message
                    const errorMsg = response.message || 'Failed to assign contact';
                    const debugMsg = response.debug ? ' (' + response.debug + ')' : '';
                    showAlert(errorMsg + debugMsg, 'error');
                    button.prop('disabled', false);
                    button.html('✋ Assign to Me');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error Details:');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response Text:', xhr.responseText);
                
                let detailedError = 'AJAX Request Failed: ';
                if (xhr.status === 0) {
                    detailedError += 'Network error or CORS issue';
                } else if (xhr.status === 404) {
                    detailedError += 'Endpoint not found (404)';
                } else if (xhr.status === 500) {
                    detailedError += 'Server error (500)';
                } else {
                    detailedError += 'Status: ' + xhr.status;
                }
                
                showAlert(detailedError, 'error');
                button.prop('disabled', false);
                button.html('✋ Assign to Me');
            }
        });
    });
});
</script>
</body>
</html>