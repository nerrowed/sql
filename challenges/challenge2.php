<?php
/**
 * Challenge 2: Data Extraction
 * INSIDER Training Platform
 */

require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$challenge_id = 2;
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

displayHeader('Challenge 2');
?>

<div class="card">
    <h2>🎯 Challenge 2: Data Extraction</h2>
    <span class="difficulty-badge difficulty-medium">Medium</span>
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
    <p>Use UNION SELECT to extract hidden product information from the database. Find the SECRET_PRODUCT!</p>
    
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
    </div>
</div>

<div class="card">
    <h3>✅ Mark as Complete</h3>
    <p>Once you've found the SECRET_PRODUCT and its flag, mark this challenge as complete:</p>
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
