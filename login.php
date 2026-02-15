<?php
/**
 * Vulnerable Login Page
 * INSIDER Training Platform
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
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Build vulnerable SQL query using string concatenation
    // This is INTENTIONALLY VULNERABLE to SQL injection
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
    // Store query for display
    $query_executed = $query;
    
    // Execute the vulnerable query
    $conn = getConnection();
    
    try {
        $result = $conn->query($query);
        
        // Log the attempt
        $user_id = 0;
        
        if ($result && $result->num_rows > 0) {
            // Login successful
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            
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
    } catch (mysqli_sql_exception $e) {
        // SQL error (likely from injection attempt)
        $error = 'SQL Error: ' . htmlspecialchars($e->getMessage());
        logActivity(0, 'LOGIN_ERROR', $query);
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

<?php displayFooter(); ?>
