<?php
/**
 * Challenge 5: Privilege Escalation
 * INSIDER Training Platform
 */

require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$challenge_id = 5;
$conn = getConnection();

$stmt = $conn->prepare("SELECT * FROM challenges WHERE id = ?");
$stmt->bind_param("i", $challenge_id);
$stmt->execute();
$challenge = $stmt->get_result()->fetch_assoc();

$user_id = getCurrentUserId();
$stmt = $conn->prepare("SELECT * FROM user_progress WHERE user_id = ? AND challenge_id = ?");
$stmt->bind_param("ii", $user_id, $challenge_id);
$stmt->execute();
$progress_result = $stmt->get_result();

$completed = false;
$show_flag = false;

if ($progress_result->num_rows > 0) {
    $progress = $progress_result->fetch_assoc();
    $completed = $progress['completed'];
}

if (isset($_POST['mark_complete'])) {
    if ($progress_result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE user_progress SET completed = 1, completed_at = NOW() WHERE user_id = ? AND challenge_id = ?");
    } else {
        $stmt = $conn->prepare("INSERT INTO user_progress (user_id, challenge_id, completed, completed_at) VALUES (?, ?, 1, NOW())");
    }
    $stmt->bind_param("ii", $user_id, $challenge_id);
    $stmt->execute();
    $completed = true;
    $show_flag = true;
}

closeConnection($conn);

displayHeader('Challenge 5');
?>

<div class="card">
    <h2>🎯 Challenge 5: Privilege Escalation</h2>
    <span class="difficulty-badge difficulty-hard">Hard</span>
    <p><strong>Points:</strong> <?php echo $challenge['points']; ?></p>
    
    <?php if ($completed): ?>
        <div class="success-box">
            <p><strong>✅ Challenge Completed!</strong></p>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h3>📋 Objective</h3>
    <p><?php echo htmlspecialchars($challenge['description']); ?></p>
    
    <h3>🎯 Goal</h3>
    <p>Use SQL injection to gain admin access and access the admin panel.</p>
    
    <h3>🔗 Target</h3>
    <p><a href="<?php echo BASE_URL; ?>/<?php echo $challenge['target_page']; ?>" class="btn btn-primary" target="_blank">
        Go to <?php echo htmlspecialchars($challenge['target_page']); ?>
    </a></p>
</div>

<div class="card">
    <h3>💡 Hints</h3>
    <div id="hint-container">
        <button onclick="getHint(<?php echo $challenge_id; ?>, 1)" class="btn btn-warning">Get Hint 1</button>
        <button onclick="getHint(<?php echo $challenge_id; ?>, 2)" class="btn btn-warning">Get Hint 2</button>
        <button onclick="getHint(<?php echo $challenge_id; ?>, 3)" class="btn btn-warning">Get Hint 3</button>
        <button onclick="getHint(<?php echo $challenge_id; ?>, 4)" class="btn btn-danger">Show Solution</button>
    </div>
</div>

<div class="card">
    <h3>📚 Privilege Escalation Techniques</h3>
    
    <h4>Method 1: UNION SELECT with Admin Flag</h4>
    <p>Manipulate the query to return a user with is_admin=1:</p>
    <code>trainee' UNION SELECT id, username, password, email, 1, created_at, last_login FROM users WHERE username='trainee' --</code>
    <p>This returns your user data but with is_admin set to 1!</p>
    
    <h4>Method 2: Multiple Queries (if supported)</h4>
    <p>Some databases allow multiple queries separated by semicolons:</p>
    <code>trainee'; UPDATE users SET is_admin=1 WHERE username='trainee';--</code>
    <p>⚠️ Note: MySQL with mysqli doesn't support this by default</p>
    
    <h4>Method 3: Login as Admin Directly</h4>
    <p>Use SQL injection to bypass authentication and login as admin:</p>
    <code>admin' OR '1'='1' --</code>
    <p>This logs you in as the first user (usually admin)</p>
    
    <h4>What Happens After?</h4>
    <p>Once you have admin privileges, you can:</p>
    <ul>
        <li>Access the admin panel at <code>/admin/index.php</code></li>
        <li>View all user accounts and passwords</li>
        <li>See activity logs</li>
        <li>Reset the database</li>
    </ul>
</div>

<div class="card">
    <h3>🔍 Understanding the Attack</h3>
    <p>This challenge demonstrates how SQL injection can lead to privilege escalation:</p>
    <ol>
        <li>Attacker finds SQL injection vulnerability</li>
        <li>Attacker manipulates query to return admin privileges</li>
        <li>Application trusts the database response</li>
        <li>Attacker gains unauthorized admin access</li>
    </ol>
    
    <h4>Real-World Impact:</h4>
    <ul>
        <li>Complete system compromise</li>
        <li>Access to sensitive data</li>
        <li>Ability to modify or delete data</li>
        <li>Potential for further attacks</li>
    </ul>
</div>

<div class="card">
    <h3>✅ Mark as Complete</h3>
    <p>Once you've gained admin access and viewed the admin panel, mark this challenge as complete:</p>
    <form method="POST">
        <button type="submit" name="mark_complete" class="btn btn-success">Mark Challenge as Complete</button>
    </form>
    
    <?php if ($show_flag): ?>
        <div class="success-box" style="margin-top: 20px;">
            <h3>🎉 Flag Captured!</h3>
            <p><strong><?php echo htmlspecialchars($challenge['flag']); ?></strong></p>
            <p>You've completed the hardest challenge! You're now a SQL Injection master! 🏆</p>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <p><a href="index.php" class="btn btn-primary">Back to Challenges</a></p>
    <?php if (isAdmin()): ?>
        <p><a href="<?php echo BASE_URL; ?>/admin/index.php" class="btn btn-success">Go to Admin Panel</a></p>
    <?php endif; ?>
</div>

<?php displayFooter(); ?>
