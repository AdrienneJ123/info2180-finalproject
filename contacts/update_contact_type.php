<?php
// update_contact_type.php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only POST allowed.'
    ]);
    exit();
}

// Validate inputs
if (!isset($_POST['contact_id']) || !is_numeric($_POST['contact_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid contact ID.'
    ]);
    exit();
}

if (!isset($_POST['new_type']) || !in_array($_POST['new_type'], ['Sales Lead', 'Support'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid contact type. Must be "Sales Lead" or "Support".'
    ]);
    exit();
}

$contact_id = (int)$_POST['contact_id'];
$new_type = $_POST['new_type'];
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'];

try {
    $conn = getDBConnection();
    
    // Begin transaction for data consistency
    $conn->beginTransaction();
    
    // Check if contact exists and get current type
    $checkStmt = $conn->prepare("
        SELECT c.id, c.type, c.firstname, c.lastname, c.assigned_to
        FROM contacts c
        WHERE c.id = ?
    ");
    $checkStmt->execute([$contact_id]);
    $contact = $checkStmt->fetch();
    
    if (!$contact) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Contact not found.'
        ]);
        exit();
    }
    
    // Check permissions - only assigned user or admin can change type
    if ($contact['assigned_to'] != $user_id && $_SESSION['user_role'] != 'Admin') {
        $conn->rollBack();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'You are not authorized to change the type of this contact.'
        ]);
        exit();
    }
    
    // Check if type is already the same
    if ($contact['type'] === $new_type) {
        $conn->rollBack();
        echo json_encode([
            'success' => true,
            'message' => 'Contact type is already ' . $new_type . '.'
        ]);
        exit();
    }
    
    // Update contact type and timestamp
    $updateStmt = $conn->prepare("
        UPDATE contacts 
        SET type = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    $success = $updateStmt->execute([$new_type, $contact_id]);
    
    if ($success) {
        // Add a system note about the type change
        $noteStmt = $conn->prepare("
            INSERT INTO notes (contact_id, comment, created_by) 
            VALUES (?, ?, ?)
        ");
        
        $noteComment = "Contact type changed from " . $contact['type'] . " to " . $new_type;
        $noteStmt->execute([$contact_id, $noteComment, $user_id]);
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Contact type successfully changed to ' . $new_type . '.',
            'new_type' => $new_type,
            'old_type' => $contact['type'],
            'contact_name' => $contact['firstname'] . ' ' . $contact['lastname']
        ]);
        
    } else {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update contact type. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Update contact type error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A database error occurred. Please try again later.'
    ]);
}
?>