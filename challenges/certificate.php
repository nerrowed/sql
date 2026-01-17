<?php
/**
 * Completion Certificate
 * INSIDER Training Platform
 */

require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$conn = getConnection();
$user_id = getCurrentUserId();
$username = getCurrentUsername();

// Check completion status
$total_challenges = $conn->query("SELECT COUNT(*) as count FROM challenges")->fetch_assoc()['count'];
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_progress WHERE user_id = ? AND completed = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_challenges = $stmt->get_result()->fetch_assoc()['count'];

// Get total points
$stmt = $conn->prepare("SELECT SUM(c.points) as total_points FROM user_progress up JOIN challenges c ON up.challenge_id = c.id WHERE up.user_id = ? AND up.completed = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_points = $stmt->get_result()->fetch_assoc()['total_points'] ?? 0;

// Get total hints used
$stmt = $conn->prepare("SELECT SUM(hints_used) as total_hints FROM user_progress WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_hints = $stmt->get_result()->fetch_assoc()['total_hints'] ?? 0;

closeConnection($conn);

$all_completed = ($completed_challenges == $total_challenges);

displayHeader('Certificate');
?>

<?php if ($all_completed): ?>
    <div class="card" style="text-align: center; padding: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h1 style="font-size: 3em; margin-bottom: 20px;">🎉 Congratulations! 🎉</h1>
        <h2 style="margin-bottom: 30px;">Certificate of Completion</h2>
        
        <div style="background: white; color: #2c3e50; padding: 40px; border-radius: 10px; margin: 30px 0;">
            <p style="font-size: 1.2em; margin-bottom: 20px;">This certifies that</p>
            <h2 style="font-size: 2.5em; color: #667eea; margin: 20px 0;"><?php echo htmlspecialchars($username); ?></h2>
            <p style="font-size: 1.2em; margin: 20px 0;">has successfully completed all</p>
            <h3 style="font-size: 2em; color: #764ba2; margin: 20px 0;"><?php echo $total_challenges; ?> SQL Injection Challenges</h3>
            <p style="font-size: 1.2em; margin: 20px 0;">and demonstrated mastery of SQL injection techniques</p>
            
            <div style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <p><strong>Total Points Earned:</strong> <?php echo $total_points; ?></p>
                <p><strong>Hints Used:</strong> <?php echo $total_hints; ?></p>
                <p><strong>Completion Date:</strong> <?php echo date('F j, Y'); ?></p>
            </div>
            
            <p style="margin-top: 30px; font-style: italic; color: #7f8c8d;">
                "With great power comes great responsibility"
            </p>
        </div>
        
        <p style="font-size: 1.1em; margin-top: 30px;">
            You are now equipped with the knowledge to identify and prevent SQL injection vulnerabilities.
            Use this knowledge ethically and responsibly!
        </p>
    </div>
    
    <div class="card">
        <h3>🏆 Your Achievements</h3>
        <ul style="font-size: 1.1em;">
            <li>✅ Mastered authentication bypass techniques</li>
            <li>✅ Learned UNION-based data extraction</li>
            <li>✅ Practiced boolean-based blind injection</li>
            <li>✅ Executed time-based blind injection</li>
            <li>✅ Performed privilege escalation attacks</li>
        </ul>
    </div>
    
    <div class="card">
        <h3>📚 Next Steps</h3>
        <p>Continue your cybersecurity journey:</p>
        <ul>
            <li>Study other OWASP Top 10 vulnerabilities</li>
            <li>Practice on platforms like HackTheBox, TryHackMe</li>
            <li>Learn about secure coding practices</li>
            <li>Explore bug bounty programs (ethically!)</li>
            <li>Get certified (CEH, OSCP, etc.)</li>
        </ul>
    </div>
    
    <div class="card" style="text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Certificate</button>
        <a href="index.php" class="btn btn-success">Back to Challenges</a>
    </div>
    
<?php else: ?>
    <div class="card">
        <h2>Certificate Not Yet Available</h2>
        <p>You need to complete all challenges to earn your certificate.</p>
        
        <div style="margin: 20px 0;">
            <h3>Your Progress</h3>
            <div style="background: #ecf0f1; border-radius: 10px; overflow: hidden; height: 30px;">
                <div style="background: #e74c3c; height: 100%; width: <?php echo ($completed_challenges / $total_challenges) * 100; ?>%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    <?php echo $completed_challenges; ?> / <?php echo $total_challenges; ?>
                </div>
            </div>
        </div>
        
        <p>Keep going! You're <?php echo $total_challenges - $completed_challenges; ?> challenge(s) away from completion!</p>
        <p><a href="index.php" class="btn btn-primary">Back to Challenges</a></p>
    </div>
<?php endif; ?>

<?php displayFooter(); ?>
