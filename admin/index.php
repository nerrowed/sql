<?php
/**
 * Admin Dashboard
 * INSIDER Training Platform
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
    echo "<p>💡 Hint: Try using SQL injection to gain admin access!</p>";
    echo "<p><a href='" . BASE_URL . "/login.php'>Back to Login</a></p>";
    exit();
}

// Log admin access
logActivity(getCurrentUserId(), 'ADMIN_ACCESS', 'Accessed admin dashboard');

// Get statistics
$conn = getConnection();

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $result->fetch_assoc()['count'];

// Total challenges
$result = $conn->query("SELECT COUNT(*) as count FROM challenges");
$total_challenges = $result->fetch_assoc()['count'];

// Total completions
$result = $conn->query("SELECT COUNT(*) as count FROM user_progress WHERE completed = 1");
$total_completions = $result->fetch_assoc()['count'];

// Get all users
$users_result = $conn->query("SELECT id, username, email, is_admin, created_at, last_login FROM users ORDER BY id");

// Get challenge completion rates
$completion_rates = [];
$challenges_result = $conn->query("SELECT id, title, difficulty FROM challenges ORDER BY id");
while ($challenge = $challenges_result->fetch_assoc()) {
    $challenge_id = $challenge['id'];
    
    // Count completions for this challenge
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_progress WHERE challenge_id = ? AND completed = 1");
    $stmt->bind_param("i", $challenge_id);
    $stmt->execute();
    $completions = $stmt->get_result()->fetch_assoc()['count'];
    
    // Calculate rate
    $rate = $total_users > 0 ? ($completions / $total_users) * 100 : 0;
    
    $completion_rates[] = [
        'title' => $challenge['title'],
        'difficulty' => $challenge['difficulty'],
        'completions' => $completions,
        'rate' => $rate
    ];
}

// Get recent activities
$recent_activities = [];
$activities_result = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 20");
while ($activity = $activities_result->fetch_assoc()) {
    $recent_activities[] = $activity;
}

closeConnection($conn);

displayHeader('Admin Dashboard');
?>

<div class="success-box">
    <h2>🎉 Welcome to Admin Panel!</h2>
    <p><strong>Congratulations, <?php echo htmlspecialchars(getCurrentUsername()); ?>!</strong></p>
    <p>You have successfully accessed the admin panel. If you got here through SQL injection, excellent work!</p>
    <p>aW5zaWRlcntzcWxfZWFzeV9hdXRoX2J5cGFzc30</p>
</div>

<div class="card">
    <h2>📊 Platform Statistics</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div style="background: #3498db; color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; font-size: 2em;"><?php echo $total_users; ?></h3>
            <p style="margin: 10px 0 0 0;">Total Users</p>
        </div>
        <div style="background: #2ecc71; color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; font-size: 2em;"><?php echo $total_challenges; ?></h3>
            <p style="margin: 10px 0 0 0;">Total Challenges</p>
        </div>
        <div style="background: #e74c3c; color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; font-size: 2em;"><?php echo $total_completions; ?></h3>
            <p style="margin: 10px 0 0 0;">Total Completions</p>
        </div>
    </div>
</div>

<div class="card">
    <h2>🎯 Challenge Completion Rates</h2>
    <table>
        <thead>
            <tr>
                <th>Challenge</th>
                <th>Difficulty</th>
                <th>Completions</th>
                <th>Completion Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($completion_rates as $rate): ?>
                <tr>
                    <td><?php echo htmlspecialchars($rate['title']); ?></td>
                    <td>
                        <span class="difficulty-badge difficulty-<?php echo strtolower($rate['difficulty']); ?>">
                            <?php echo htmlspecialchars($rate['difficulty']); ?>
                        </span>
                    </td>
                    <td><?php echo $rate['completions']; ?></td>
                    <td>
                        <div style="background: #ecf0f1; border-radius: 10px; overflow: hidden;">
                            <div style="background: #2ecc71; height: 20px; width: <?php echo $rate['rate']; ?>%; min-width: 30px; text-align: center; color: white; font-size: 0.8em; line-height: 20px;">
                                <?php echo number_format($rate['rate'], 1); ?>%
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>👥 All Users</h2>
    <p><strong>⚠️ Sensitive Information:</strong> This shows all user accounts including passwords (in plaintext!).</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Created</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['is_admin'] ? '✅' : '❌'; ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($user['last_login'] ?? 'Never'); ?></td>
                    <td>
                        <a href="view_logs.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9em;">View Logs</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>📝 Recent Activities</h2>
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_activities as $activity): ?>
                <tr>
                    <td><?php echo htmlspecialchars($activity['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($activity['username']); ?></td>
                    <td><?php echo htmlspecialchars($activity['action']); ?></td>
                    <td style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <code><?php echo htmlspecialchars(substr($activity['query_executed'], 0, 100)); ?></code>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="view_logs.php" class="btn btn-primary">View All Logs</a></p>
</div>

<div class="card">
    <h2>🔧 Admin Tools</h2>
    <p><a href="view_logs.php" class="btn btn-primary">View Activity Logs</a></p>
    <p><a href="reset_db.php" class="btn btn-danger" onclick="return confirmDatabaseReset()">Reset Database</a></p>
</div>

<?php displayFooter(); ?>
