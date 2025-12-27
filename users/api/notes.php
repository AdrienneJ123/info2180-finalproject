<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

if (!isset($_POST['contact_id']) || !is_numeric($_POST['contact_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid contact ID'
    ]);
    exit();
}

if (!isset($_POST['comment']) || empty(trim($_POST['comment']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Note cannot be empty'
    ]);
    exit();
}

$contact_id = (int)$_POST['contact_id'];
$comment = trim($_POST['comment']);
$created_by = $_SESSION['user_id'];

try {
    $conn = getDBConnection();
    
    // Check if contact exists
    $checkStmt = $conn->prepare("SELECT id FROM contacts WHERE id = ?");
    $checkStmt->execute([$contact_id]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Contact not found'
        ]);
        exit();
    }
    
    // Insert the note
    $stmt = $conn->prepare("
        INSERT INTO notes (contact_id, comment, created_by, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    if ($stmt->execute([$contact_id, $comment, $created_by])) {
        // Also update the contact's updated_at timestamp
        $updateStmt = $conn->prepare("
            UPDATE contacts 
            SET updated_at = NOW() 
            WHERE id = ?
        ");
        $updateStmt->execute([$contact_id]);
        
        // Get the newly created note with user details
        $noteId = $conn->lastInsertId();
        $noteStmt = $conn->prepare("
            SELECT n.*, u.firstname, u.lastname 
            FROM notes n 
            JOIN users u ON n.created_by = u.id 
            WHERE n.id = ?
        ");
        $noteStmt->execute([$noteId]);
        $newNote = $noteStmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Note added successfully',
            'note' => [
                'id' => $newNote['id'],
                'comment' => htmlspecialchars($newNote['comment']),
                'author' => $newNote['firstname'] . ' ' . $newNote['lastname'],
                'date' => date('F j, Y \a\t g:i A', strtotime($newNote['created_at']))
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add note'
        ]);
    }
} catch (PDOException $e) {
    error_log("Add note error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>