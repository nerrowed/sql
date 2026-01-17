<?php
/**
 * Challenges List Page
 * SQL Injection Training Platform
 */

require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$conn = getConnection();
$user_id = getCurrentUserId();

// Get all challenges with completion status
$challenges = [];
$challenges_result = $conn->query("SELECT * FROM challenges ORDER BY id");

while ($challenge = $challenges_result->fetch_assoc()) {
    // Check if user has completed this challenge
    $stmt = $conn->prepare("SELECT completed, hints_used, attempts FROM user_progress WHERE user_id = ? AND challenge_id = ?");
    $stmt->bind_param("ii", $user_id, $challenge['id']);
    $stmt->execute();
    $progress_result = $stmt->get_result();
    
    if ($progress_result->num_rows > 0) {
        $progress = $progress_result->fetch_assoc();
        $challenge['completed'] = $progress['completed'];
        $challenge['hints_used'] = $progress['hints_used'];
        $challenge['attempts'] = $progress['attempts'];
    } else {
        $challenge['completed'] = 0;
        $challenge['hints_used'] = 0;
        $challenge['attempts'] = 0;
    }
    
    $challenges[] = $challenge;
}

// Calculate total progress
$total_challenges = count($challenges);
$completed_challenges = 0;
$total_points = 0;
$earned_points = 0;

foreach ($challenges as $challenge) {
    $total_points += $challenge['points'];
    if ($challenge['completed']) {
        $completed_challenges++;
        $earned_points += $challenge['points'];
    }
}

$completion_percentage = $total_challenges > 0 ? ($completed_challenges / $total_challenges) * 100 : 0;

closeConnection($conn);

displayHeader('Challenges');
?>

<div class="card">
    <h2>🎯 SQL Injection Challenges</h2>
    <p>Complete these challenges to master SQL injection techniques!</p>
    
    <div style="margin: 20px 0;">
        <h3>Your Progress</h3>
        <div style="background: #ecf0f1; border-radius: 10px; overflow: hidden; height: 30px;">
            <div style="background: #2ecc71; height: 100%; width: <?php echo $completion_percentage; ?>%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                <?php echo $completed_challenges; ?> / <?php echo $total_challenges; ?> (<?php echo number_format($completion_percentage, 0); ?>%)
            </div>
        </div>
        <p style="margin-top: 10px;">
            <strong>Points:</strong> <?php echo $earned_points; ?> / <?php echo $total_points; ?>
        </p>
    </div>
</div>

<div class="challenge-grid">
    <?php foreach ($challenges as $challenge): ?>
        <div class="challenge-card">
            <span class="difficulty-badge difficulty-<?php echo strtolower($challenge['difficulty']); ?>">
                <?php echo htmlspecialchars($challenge['difficulty']); ?>
            </span>
            
            <h3><?php echo htmlspecialchars($challenge['title']); ?></h3>
            <p><?php echo htmlspecialchars($challenge['description']); ?></p>
            
            <div style="margin: 15px 0;">
                <strong>Target:</strong> <code><?php echo htmlspecialchars($challenge['target_page']); ?></code><br>
                <strong>Points:</strong> <?php echo $challenge['points']; ?>
            </div>
            
            <?php if ($challenge['completed']): ?>
                <div class="success-box" style="padding: 10px; margin: 10px 0;">
                    <p style="margin: 0;"><strong>✅ Completed!</strong></p>
                    <p style="margin: 5px 0 0 0; font-size: 0.9em;">
                        Hints used: <?php echo $challenge['hints_used']; ?> | 
                        Attempts: <?php echo $challenge['attempts']; ?>
                    </p>
                </div>
            <?php else: ?>
                <div style="padding: 10px; margin: 10px 0; background: #fff3cd; border-radius: 4px;">
                    <p style="margin: 0;">❌ Not completed yet</p>
                </div>
            <?php endif; ?>
            
            <a href="challenge<?php echo $challenge['id']; ?>.php" class="btn btn-primary" style="width: 100%; text-align: center;">
                <?php echo $challenge['completed'] ? 'View Challenge' : 'Start Challenge'; ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($completed_challenges == $total_challenges): ?>
    <div class="success-box">
        <h2>🎉 Congratulations!</h2>
        <p>You've completed all challenges! You're now a SQL Injection expert!</p>
        <p><strong>Total Points Earned:</strong> <?php echo $earned_points; ?></p>
        <p><a href="certificate.php" class="btn btn-success">View Your Certificate</a></p>
    </div>
<?php endif; ?>

<div class="card">
    <h3>💡 Tips for Success</h3>
    <ul>
        <li>Start with easier challenges and work your way up</li>
        <li>Read the hints if you get stuck</li>
        <li>Pay attention to error messages - they reveal database structure</li>
        <li>Try different SQL injection techniques on each challenge</li>
        <li>Use the vulnerable pages (login, search, profile) to practice</li>
    </ul>
</div>

<?php displayFooter(); ?>
