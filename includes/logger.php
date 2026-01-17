<?php
/**
 * Activity Logging Functions
 * INSIDER Training Platform
 */

require_once __DIR__ . '/db.php';

/**
 * Log user activity
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $details Additional details
 */
function logActivity($userId, $action, $details) {
    $conn = getConnection();
    
    $username = $_SESSION['username'] ?? 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Use prepared statement for logging (not part of vulnerable training)
    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, username, action, query_executed, ip_address, user_agent) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    
    if ($stmt) {
        $stmt->bind_param("isssss", $userId, $username, $action, $details, $ip, $userAgent);
        $stmt->execute();
        $stmt->close();
    }
    
    closeConnection($conn);
}

/**
 * Log SQL injection attempt
 * @param int $userId User ID
 * @param string $query SQL query executed
 * @param bool $success Whether the injection was successful
 */
function logSQLInjection($userId, $query, $success) {
    $action = $success ? 'SQL_INJECTION_SUCCESS' : 'SQL_INJECTION_ATTEMPT';
    logActivity($userId, $action, $query);
}

/**
 * Get activity logs
 * @param int $limit Maximum number of logs to retrieve
 * @return array Array of activity logs
 */
function getActivityLogs($limit = 100) {
    $conn = getConnection();
    
    $stmt = $conn->prepare(
        "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    $stmt->close();
    closeConnection($conn);
    
    return $logs;
}

/**
 * Get activity logs for specific user
 * @param int $userId User ID
 * @return array Array of activity logs for the user
 */
function getUserLogs($userId) {
    $conn = getConnection();
    
    $stmt = $conn->prepare(
        "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    $stmt->close();
    closeConnection($conn);
    
    return $logs;
}
?>
