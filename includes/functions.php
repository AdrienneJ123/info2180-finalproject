<?php
require_once __DIR__ . '/../config/database.php';

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate password meets requirements
 * - At least 8 characters
 * - At least one number
 * - At least one letter
 * - At least one capital letter
 * 
 * @param string $password Password to validate
 * @return bool True if password is valid
 */
function validatePassword($password) {
    $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/';
    return preg_match($pattern, $password);
}

/**
 * Get all users for dropdowns (Admin only)
 * 
 * @return array List of users with id and name
 */
function getUsers() {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, firstname, lastname, CONCAT(firstname, ' ', lastname) as name FROM users ORDER BY firstname, lastname");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get contact by ID with creator and assignee information
 * 
 * @param int $id Contact ID
 * @return array|false Contact data or false if not found
 */
function getContactById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT c.*, 
               creator.firstname as creator_first, creator.lastname as creator_last,
               assignee.firstname as assignee_first, assignee.lastname as assignee_last
        FROM contacts c
        LEFT JOIN users creator ON c.created_by = creator.id
        LEFT JOIN users assignee ON c.assigned_to = assignee.id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get all notes for a specific contact
 * 
 * @param int $contact_id Contact ID
 * @return array List of notes
 */
function getNotesByContactId($contact_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT n.*, u.firstname, u.lastname 
        FROM notes n
        JOIN users u ON n.created_by = u.id
        WHERE n.contact_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$contact_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all contacts with optional filters
 * 
 * @param string $filter Filter type (all, sales, support, assigned)
 * @param int $userId Current user ID for 'assigned' filter
 * @return array List of contacts
 */
function getContacts($filter = 'all', $userId = null) {
    $conn = getDBConnection();
    
    $sql = "SELECT c.*, 
                   u.firstname as assignee_first, u.lastname as assignee_last 
            FROM contacts c
            LEFT JOIN users u ON c.assigned_to = u.id
            WHERE 1=1";
    
    $params = [];
    
    switch ($filter) {
        case 'sales':
            $sql .= " AND c.type = 'Sales Lead'";
            break;
        case 'support':
            $sql .= " AND c.type = 'Support'";
            break;
        case 'assigned':
            if ($userId) {
                $sql .= " AND c.assigned_to = ?";
                $params[] = $userId;
            }
            break;
    }
    
    $sql .= " ORDER BY c.updated_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all users for admin view
 * 
 * @return array List of all users
 */
function getAllUsers() {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT id, firstname, lastname, email, role, created_at 
        FROM users 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Check if email already exists in users table
 * 
 * @param string $email Email to check
 * @param int $excludeId User ID to exclude (for updates)
 * @return bool True if email exists
 */
function emailExists($email, $excludeId = null) {
    $conn = getDBConnection();
    
    if ($excludeId) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $excludeId]);
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
    }
    
    return $stmt->fetch() !== false;
}

/**
 * Check if contact email already exists
 * 
 * @param string $email Email to check
 * @param int $excludeId Contact ID to exclude (for updates)
 * @return bool True if email exists
 */
function contactEmailExists($email, $excludeId = null) {
    $conn = getDBConnection();
    
    if ($excludeId) {
        $stmt = $conn->prepare("SELECT id FROM contacts WHERE email = ? AND id != ?");
        $stmt->execute([$email, $excludeId]);
    } else {
        $stmt = $conn->prepare("SELECT id FROM contacts WHERE email = ?");
        $stmt->execute([$email]);
    }
    
    return $stmt->fetch() !== false;
}

/**
 * Validate email format
 * 
 * @param string $email Email to validate
 * @return bool True if email is valid
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number format (basic validation)
 * 
 * @param string $phone Phone number to validate
 * @return bool True if phone number is valid
 */
function isValidPhone($phone) {
    // Basic phone validation - adjust as needed
    $pattern = '/^[\+]?[1-9][\d]{0,15}$/';
    return preg_match($pattern, preg_replace('/[^0-9+]/', '', $phone));
}

/**
 * Format date for display
 * 
 * @param string $dateString Date string
 * @param bool $includeTime Whether to include time
 * @return string Formatted date
 */
function formatDate($dateString, $includeTime = false) {
    if (!$dateString) {
        return 'N/A';
    }
    
    $timestamp = strtotime($dateString);
    if ($timestamp === false) {
        return $dateString;
    }
    
    if ($includeTime) {
        return date('F j, Y \a\t g:ia', $timestamp);
    } else {
        return date('F j, Y', $timestamp);
    }
}

/**
 * Generate random password that meets requirements
 * 
 * @return string Generated password
 */
function generatePassword() {
    $length = 12;
    $sets = [
        'abcdefghjkmnpqrstuvwxyz',
        'ABCDEFGHJKMNPQRSTUVWXYZ',
        '23456789',
        '!@#$%&*?'
    ];
    
    $password = '';
    
    // Get one character from each set
    foreach ($sets as $set) {
        $password .= $set[array_rand(str_split($set))];
    }
    
    // Fill the rest with random characters from all sets
    $all = implode('', $sets);
    for ($i = 0; $i < $length - count($sets); $i++) {
        $password .= $all[array_rand(str_split($all))];
    }
    
    // Shuffle the password
    return str_shuffle($password);
}

/**
 * Log user activity
 * 
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $details Additional details
 */
function logActivity($userId, $action, $details = '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, details, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $action, $details]);
}

/**
 * Get user by ID
 * 
 * @param int $id User ID
 * @return array|false User data or false if not found
 */
function getUserById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user full name by ID
 * 
 * @param int $id User ID
 * @return string User's full name or 'Unknown'
 */
function getUserFullName($id) {
    $user = getUserById($id);
    if ($user) {
        return $user['firstname'] . ' ' . $user['lastname'];
    }
    return 'Unknown';
}

/**
 * Check if user can edit/delete contact
 * 
 * @param array $contact Contact data
 * @param array $user Current user data
 * @return bool True if user has permission
 */
function canEditContact($contact, $user) {
    // Admins can edit all contacts
    if ($user['role'] === 'Admin') {
        return true;
    }
    
    // Members can edit contacts they created or are assigned to
    return $contact['created_by'] == $user['id'] || $contact['assigned_to'] == $user['id'];
}

/**
 * Add a new note to contact
 * 
 * @param int $contactId Contact ID
 * @param string $comment Note content
 * @param int $createdBy User ID who created the note
 * @return bool True if successful
 */
function addNoteToContact($contactId, $comment, $createdBy) {
    $conn = getDBConnection();
    
    try {
        $conn->beginTransaction();
        
        // Add note
        $stmt = $conn->prepare("
            INSERT INTO notes (contact_id, comment, created_by, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$contactId, $comment, $createdBy]);
        
        // Update contact's updated_at
        $stmt = $conn->prepare("
            UPDATE contacts 
            SET updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$contactId]);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error adding note: " . $e->getMessage());
        return false;
    }
}

/**
 * Assign contact to user
 * 
 * @param int $contactId Contact ID
 * @param int $userId User ID to assign to
 * @return bool True if successful
 */
function assignContactToUser($contactId, $userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        UPDATE contacts 
        SET assigned_to = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    return $stmt->execute([$userId, $contactId]);
}

/**
 * Switch contact type
 * 
 * @param int $contactId Contact ID
 * @param string $newType New type (Sales Lead/Support)
 * @return bool True if successful
 */
function switchContactType($contactId, $newType) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        UPDATE contacts 
        SET type = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    return $stmt->execute([$newType, $contactId]);
}

/**
 * Add a new user
 * 
 * @param array $userData User data (firstname, lastname, email, password, role)
 * @return int|false New user ID or false on failure
 */
function addUser($userData) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        INSERT INTO users (firstname, lastname, password, email, role, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    if ($stmt->execute([
        $userData['firstname'],
        $userData['lastname'],
        $hashedPassword,
        $userData['email'],
        $userData['role']
    ])) {
        return $conn->lastInsertId();
    }
    
    return false;
}

/**
 * Add a new contact
 * 
 * @param array $contactData Contact data
 * @param int $createdBy User ID who created the contact
 * @return int|false New contact ID or false on failure
 */
function addContact($contactData, $createdBy) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        INSERT INTO contacts (
            title, firstname, lastname, email, telephone, company, 
            type, assigned_to, created_by, created_at, updated_at
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    if ($stmt->execute([
        $contactData['title'],
        $contactData['firstname'],
        $contactData['lastname'],
        $contactData['email'],
        $contactData['telephone'],
        $contactData['company'],
        $contactData['type'],
        $contactData['assigned_to'],
        $createdBy
    ])) {
        return $conn->lastInsertId();
    }
    
    return false;
}

/**
 * Get dashboard statistics
 * 
 * @return array Dashboard stats
 */
function getDashboardStats() {
    $conn = getDBConnection();
    $stats = [];
    
    // Total contacts
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM contacts");
    $stmt->execute();
    $stats['total_contacts'] = $stmt->fetchColumn();
    
    // Sales leads
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM contacts WHERE type = 'Sales Lead'");
    $stmt->execute();
    $stats['sales_leads'] = $stmt->fetchColumn();
    
    // Support contacts
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM contacts WHERE type = 'Support'");
    $stmt->execute();
    $stats['support_contacts'] = $stmt->fetchColumn();
    
    // Total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $stats['total_users'] = $stmt->fetchColumn();
    
    return $stats;
}

/**
 * Redirect with message
 * 
 * @param string $url URL to redirect to
 * @param string $type Message type (success, error, info)
 * @param string $message Message content
 */
function redirectWithMessage($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit();
}

/**
 * Display flash message if exists
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        $class = $msg['type'] === 'error' ? 'error' : 'success';
        echo "<div class='$class'>{$msg['message']}</div>";
        unset($_SESSION['flash_message']);
    }
}

/**
 * Validate title selection
 * 
 * @param string $title Title to validate
 * @return bool True if title is valid
 */
function isValidTitle($title) {
    $validTitles = ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof'];
    return in_array($title, $validTitles);
}

/**
 * Validate contact type
 * 
 * @param string $type Type to validate
 * @return bool True if type is valid
 */
function isValidContactType($type) {
    $validTypes = ['Sales Lead', 'Support'];
    return in_array($type, $validTypes);
}

/**
 * Validate user role
 * 
 * @param string $role Role to validate
 * @return bool True if role is valid
 */
function isValidRole($role) {
    $validRoles = ['Admin', 'Member'];
    return in_array($role, $validRoles);
}

/**
 * Get the current URL
 * 
 * @return string Current URL
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if request is AJAX
 * 
 * @return bool True if request is AJAX
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response
 * 
 * @param array $data Data to encode as JSON
 */
function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool True if token is valid
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}
?>