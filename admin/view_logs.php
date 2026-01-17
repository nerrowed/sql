<?php
/**
 * Activity Logs Viewer
 * SQL Injection Training Platform
 */

require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/logger.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

// Check if user is admin
if (!isAdmin()) {
    echo "<h1>Access Denied</h1>";
    echo "<p>You need admin privileges to access this page.</p>";
    exit();
}

// Log admin access
logActivity(getCurrentUserId(), 'ADMIN_VIEW_LOGS', 'Accessed activity logs');

$conn = getConnection();

// Filter by user if specified
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$username_filter = '';

if ($user_filter) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $username_filter = $result->fetch_assoc()['username'];
    }
}

// Get logs
if ($user_filter) {
    $stmt = $conn->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 100");
    $stmt->bind_param("i", $user_filter);
    $stmt->execute();
    $logs_result = $stmt->get_result();
} else {
    $logs_result = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 100");
}

$logs = [];
while ($log = $logs_result->fetch_assoc()) {
    $logs[] = $log;
}

closeConnection($conn);

displayHeader('Activity Logs');
?>

<div class="card">
    <h2>📝 Activity Logs</h2>
    
    <?php if ($user_filter): ?>
        <div class="success-box">
            <p>Showing logs for user: <strong><?php echo htmlspecialchars($username_filter); ?></strong> (ID: <?php echo $user_filter; ?>)</p>
            <p><a href="view_logs.php" class="btn btn-primary">View All Logs</a></p>
        </div>
    <?php else: ?>
        <p>Showing last 100 activities from all users.</p>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Query/Details</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['id']); ?></td>
                    <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                    <td>
                        <a href="view_logs.php?user_id=<?php echo $log['user_id']; ?>">
                            <?php echo htmlspecialchars($log['username']); ?>
                        </a>
                    </td>
                    <td>
                        <?php 
                        $action = $log['action'];
                        $badge_class = 'btn-primary';
                        if (strpos($action, 'SUCCESS') !== false) {
                            $badge_class = 'btn-success';
                        } elseif (strpos($action, 'FAILED') !== false || strpos($action, 'ERROR') !== false) {
                            $badge_class = 'btn-danger';
                        }
                        ?>
                        <span class="btn <?php echo $badge_class; ?>" style="padding: 3px 8px; font-size: 0.8em;">
                            <?php echo htmlspecialchars($action); ?>
                        </span>
                    </td>
                    <td style="max-width: 500px; overflow: auto;">
                        <code style="font-size: 0.85em;"><?php echo htmlspecialchars($log['query_executed']); ?></code>
                    </td>
                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if (empty($logs)): ?>
        <p>No activity logs found.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h3>🔍 Log Analysis</h3>
    <p>This page shows all SQL injection attempts and other activities. Use this to:</p>
    <ul>
        <li>Monitor trainee progress and learning patterns</li>
        <li>Identify common mistakes and successful techniques</li>
        <li>Track which SQL injection payloads are being used</li>
        <li>Review security incidents and attack patterns</li>
    </ul>
    <p><a href="index.php" class="btn btn-primary">Back to Dashboard</a></p>
</div>

<?php displayFooter(); ?>
