<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        if (isset($_POST['contact_id']) && isset($_POST['comment'])) {
            $contact_id = $_POST['contact_id'];
            $comment = sanitizeInput($_POST['comment']);
            $created_by = $_SESSION['user_id'];
            
            if (empty($comment)) {
                echo json_encode(['success' => false, 'error' => 'Comment cannot be empty']);
                exit();
            }
            
            $conn = getDBConnection();
            
            // Start transaction
            $conn->beginTransaction();
            
            try {
                // Insert note
                $stmt = $conn->prepare("INSERT INTO notes (contact_id, comment, created_by, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$contact_id, $comment, $created_by]);
                
                // Update contact's updated_at
                $stmt = $conn->prepare("UPDATE contacts SET updated_at = NOW() WHERE id = ?");
                $stmt->execute([$contact_id]);
                
                $conn->commit();
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $conn->rollBack();
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }
}
?>