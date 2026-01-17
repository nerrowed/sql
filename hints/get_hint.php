<?php
/**
 * Hint AJAX Endpoint
 * INSIDER Training Platform
 */

require_once '../config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Get parameters
$challenge_id = isset($_POST['challenge_id']) ? intval($_POST['challenge_id']) : 0;
$hint_level = isset($_POST['hint_level']) ? intval($_POST['hint_level']) : 0;

if ($challenge_id <= 0 || $hint_level <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Get hint from database
$stmt = $conn->prepare("SELECT hint_text FROM hints WHERE challenge_id = ? AND hint_level = ?");
$stmt->bind_param("ii", $challenge_id, $hint_level);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $hint = $result->fetch_assoc()['hint_text'];
    
    // Update hints_used counter in user_progress
    // First check if progress record exists
    $stmt = $conn->prepare("SELECT id, hints_used FROM user_progress WHERE user_id = ? AND challenge_id = ?");
    $stmt->bind_param("ii", $user_id, $challenge_id);
    $stmt->execute();
    $progress_result = $stmt->get_result();
    
    if ($progress_result->num_rows > 0) {
        // Update existing record
        $progress = $progress_result->fetch_assoc();
        $new_hints_used = $progress['hints_used'] + 1;
        $stmt = $conn->prepare("UPDATE user_progress SET hints_used = ? WHERE user_id = ? AND challenge_id = ?");
        $stmt->bind_param("iii", $new_hints_used, $user_id, $challenge_id);
        $stmt->execute();
    } else {
        // Create new progress record
        $stmt = $conn->prepare("INSERT INTO user_progress (user_id, challenge_id, hints_used, attempts) VALUES (?, ?, 1, 0)");
        $stmt->bind_param("ii", $user_id, $challenge_id);
        $stmt->execute();
    }
    
    closeConnection($conn);
    
    echo json_encode([
        'success' => true,
        'hint' => $hint,
        'level' => $hint_level
    ]);
} else {
    closeConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Hint not found']);
}
?>
