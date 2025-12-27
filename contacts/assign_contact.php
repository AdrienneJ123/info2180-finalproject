<?php
// assign_contact.php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only POST allowed.'
    ]);
    exit();
}

// Validate contact ID
if (!isset($_POST['contact_id']) || !is_numeric($_POST['contact_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid contact ID.'
    ]);
    exit();
}

$contact_id = (int)$_POST['contact_id'];
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'];

try {
    $conn = getDBConnection();
    
    // Begin transaction for data consistency
    $conn->beginTransaction();
    
    // Check if contact exists
    $checkStmt = $conn->prepare("
        SELECT c.id, c.assigned_to, c.firstname, c.lastname, 
               u.firstname as assignee_first, u.lastname as assignee_last
        FROM contacts c
        LEFT JOIN users u ON c.assigned_to = u.id
        WHERE c.id = ?
    ");
    
    if (!$checkStmt->execute([$contact_id])) {
        throw new Exception("Failed to check contact");
    }
    
    $contact = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$contact) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Contact not found.'
        ]);
        exit();
    }
    
    // Check if already assigned to current user
    if ($contact['assigned_to'] == $user_id) {
        $conn->rollBack();
        echo json_encode([
            'success' => true,
            'message' => 'Contact is already assigned to you.',
            'assignee_name' => $user_name
        ]);
        exit();
    }
    
    // Update contact assignment and timestamp
    $updateStmt = $conn->prepare("
        UPDATE contacts 
        SET assigned_to = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    $success = $updateStmt->execute([$user_id, $contact_id]);
    
    if (!$success) {
        throw new Exception("Failed to update contact assignment");
    }
    
    // Add a system note about the assignment
    $noteStmt = $conn->prepare("
        INSERT INTO notes (contact_id, comment, created_by) 
        VALUES (?, ?, ?)
    ");
    
    // Create note message
    $noteComment = "Contact assigned to " . $user_name;
    if ($contact['assigned_to'] && $contact['assignee_first']) {
        $noteComment = "Contact reassigned from " . 
                     $contact['assignee_first'] . ' ' . $contact['assignee_last'] . 
                     " to " . $user_name;
    }
    
    if (!$noteStmt->execute([$contact_id, $noteComment, $user_id])) {
        throw new Exception("Failed to add note");
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Contact has been successfully assigned to you.',
        'assignee_name' => $user_name,
        'contact_name' => $contact['firstname'] . ' ' . $contact['lastname']
    ]);
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Database error in assign_contact.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => 'PDO Exception occurred'
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("General error in assign_contact.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => 'General Exception occurred'
    ]);
}
?>