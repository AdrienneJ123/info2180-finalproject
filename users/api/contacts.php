// contacts.php (in api folder)
<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
requireLogin();

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'assign':
            if (isset($_POST['contact_id']) && isset($_POST['assign_to'])) {
                $contact_id = intval($_POST['contact_id']);
                $assign_to = intval($_POST['assign_to']);
                
                if (assignContactToUser($contact_id, $assign_to)) {
                    $response = ['success' => true, 'message' => 'Contact assigned successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to assign contact'];
                }
            }
            break;
            
        case 'switch_type':
            if (isset($_POST['contact_id']) && isset($_POST['new_type'])) {
                $contact_id = intval($_POST['contact_id']);
                $new_type = sanitizeInput($_POST['new_type']);
                
                if (isValidContactType($new_type) && switchContactType($contact_id, $new_type)) {
                    $response = ['success' => true, 'message' => 'Contact type updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to switch contact type'];
                }
            }
            break;
    }
}

echo json_encode($response);
?>