<?php
/**
 * Challenge 1: Authentication Bypass
 * SQL Injection Training Platform
 */

require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$challenge_id = 1;
$conn = getConnection();

// Get challenge details
$stmt = $conn->prepare("SELECT * FROM challenges WHERE id = ?");
$stmt->bind_param("i", $challenge_id);
$stmt->execute();
$challenge = $stmt->get_result()->fetch_assoc();

// Check if completed
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

// Check if user wants to mark as complete
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

displayHeader('Challenge 1');
?>

<div class="card">
    <h2>🎯 Challenge 1: Authentication Bypass</h2>
    <span class="difficulty-badge difficulty-easy">Easy</span>
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
    <p>Use SQL injection to bypass the login form and access any account without knowing the password.</p>
    
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
    <h3>🔍 Vulnerable Code</h3>
    <p>This is the vulnerable code in login.php:</p>
    <div class="code-display">
        <pre>$username = $_POST['username'];  // No sanitization!
$password = $_POST['password'];  // No sanitization!

// String concatenation - VULNERABLE!
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    // Login successful
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    // ...
}</pre>
    </div>
</div>

<div class="card">
    <h3>✅ Mark as Complete</h3>
    <p>Once you've successfully bypassed the login, mark this challenge as complete:</p>
    <form method="POST">
        <button type="submit" name="mark_complete" class="btn btn-success">Mark Challenge as Complete</button>
    </form>
    
    <?php if ($show_flag): ?>
        <div class="success-box" style="margin-top: 20px;">
            <h3>🎉 Flag Captured!</h3>
            <p><strong><?php echo htmlspecialchars($challenge['flag']); ?></strong></p>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <p><a href="index.php" class="btn btn-primary">Back to Challenges</a></p>
</div>

<?php displayFooter(); ?>
