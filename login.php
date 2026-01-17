<?php
/**
 * Vulnerable Login Page
 * SQL Injection Training Platform
 * 
 * WARNING: This code is INTENTIONALLY VULNERABLE for educational purposes
 * DO NOT USE IN PRODUCTION!
 */

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/logger.php';

$error = '';
$success = '';
$query_executed = '';

// Check if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/challenges/index.php');
}

// Handle logout message
if (isset($_GET['message']) && $_GET['message'] == 'logged_out') {
    $success = 'You have been logged out successfully.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input WITHOUT sanitization (VULNERABLE!)
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Build vulnerable SQL query using string concatenation
    // This is INTENTIONALLY VULNERABLE to SQL injection
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
    // Store query for display
    $query_executed = $query;
    
    // Execute the vulnerable query
    $conn = getConnection();
    $result = $conn->query($query);
    
    // Log the attempt
    $user_id = 0;
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
    }
    
    if ($result && $result->num_rows > 0) {
        // Login successful
        $user = $result->fetch_assoc();
        
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        // Log successful login
        logActivity($user['id'], 'LOGIN_SUCCESS', $query);
        
        // Update last login time
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = " . $user['id'];
        $conn->query($update_query);
        
        // Redirect to challenges page
        closeConnection($conn);
        redirect(BASE_URL . '/challenges/index.php');
    } else {
        // Login failed
        $error = 'Invalid username or password. Or maybe your SQL injection needs work? 😉';
        logActivity($user_id, 'LOGIN_FAILED', $query);
    }
    
    closeConnection($conn);
}

displayHeader('Login');
?>

<div class="card">
    <h2>🔐 Login</h2>
    
    <?php if ($error): ?>
        <div class="error-box">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-box">
            <p><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="login.php" onsubmit="return validateLoginForm()">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    
    <?php if ($query_executed): ?>
        <?php displayQuery($query_executed); ?>
    <?php endif; ?>
</div>

<div class="card">
    <h3>💡 Educational Hints</h3>
    <p>This login form is <strong>intentionally vulnerable</strong> to SQL injection. Try these techniques:</p>
    <ul>
        <li>What happens if you enter a single quote (<code>'</code>) in the username field?</li>
        <li>Can you make the WHERE clause always true using <code>OR 1=1</code>?</li>
        <li>How can you comment out the password check using <code>--</code>?</li>
        <li>Try: <code>admin' OR '1'='1' --</code> as username</li>
    </ul>
    
    <h4>Why is this vulnerable?</h4>
    <div class="code-display">
        <pre>// VULNERABLE CODE (DO NOT USE IN PRODUCTION!)
$username = $_POST['username'];  // No sanitization!
$password = $_POST['password'];  // No sanitization!

// String concatenation without escaping
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";

// Direct query execution
$result = $conn->query($query);</pre>
    </div>
    
    <h4>How to fix it?</h4>
    <div class="code-display">
        <pre>// SECURE CODE (Use this in production!)
$username = $_POST['username'];
$password = $_POST['password'];

// Use prepared statements with parameter binding
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

// Also: Hash passwords with password_hash() and verify with password_verify()</pre>
    </div>
</div>

<div class="card">
    <h3>📚 Test Accounts</h3>
    <p>You can login with these accounts (or try to bypass authentication!):</p>
    <table>
        <tr>
            <th>Username</th>
            <th>Password</th>
            <th>Role</th>
        </tr>
        <tr>
            <td><code>admin</code></td>
            <td><code>admin123</code></td>
            <td>Administrator</td>
        </tr>
        <tr>
            <td><code>trainee</code></td>
            <td><code>trainee123</code></td>
            <td>Trainee</td>
        </tr>
        <tr>
            <td><code>user1</code></td>
            <td><code>password1</code></td>
            <td>Regular User</td>
        </tr>
        <tr>
            <td><code>user2</code></td>
            <td><code>password2</code></td>
            <td>Regular User</td>
        </tr>
    </table>
</div>

<?php displayFooter(); ?>
