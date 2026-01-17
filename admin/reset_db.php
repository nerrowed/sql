<?php
/**
 * Database Reset Functionality
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
    exit();
}

$message = '';
$success = false;

// Handle reset request
if (isset($_POST['confirm_reset']) && $_POST['confirm_reset'] == 'yes') {
    $conn = getConnection();
    
    // Log the reset action
    logActivity(getCurrentUserId(), 'DATABASE_RESET', 'Initiated database reset');
    
    // Backup user progress
    $progress_backup = [];
    $result = $conn->query("SELECT * FROM user_progress");
    while ($row = $result->fetch_assoc()) {
        $progress_backup[] = $row;
    }
    
    // Drop tables
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("DROP TABLE IF EXISTS activity_logs");
    $conn->query("DROP TABLE IF EXISTS user_progress");
    $conn->query("DROP TABLE IF EXISTS hints");
    $conn->query("DROP TABLE IF EXISTS challenges");
    $conn->query("DROP TABLE IF EXISTS products");
    $conn->query("DROP TABLE IF EXISTS users");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Recreate tables (same as db_setup.php)
    $conn->query("CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        is_admin TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )");
    
    $conn->query("CREATE TABLE products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2),
        category VARCHAR(50),
        stock INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $conn->query("CREATE TABLE challenges (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        difficulty ENUM('Easy', 'Medium', 'Hard') DEFAULT 'Easy',
        target_page VARCHAR(100),
        flag VARCHAR(100),
        points INT DEFAULT 10,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $conn->query("CREATE TABLE user_progress (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        challenge_id INT NOT NULL,
        completed TINYINT(1) DEFAULT 0,
        completed_at TIMESTAMP NULL,
        hints_used INT DEFAULT 0,
        attempts INT DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (challenge_id) REFERENCES challenges(id),
        UNIQUE KEY unique_progress (user_id, challenge_id)
    )");
    
    $conn->query("CREATE TABLE activity_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        username VARCHAR(50),
        action VARCHAR(100),
        query_executed TEXT,
        success TINYINT(1),
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    $conn->query("CREATE TABLE hints (
        id INT PRIMARY KEY AUTO_INCREMENT,
        challenge_id INT NOT NULL,
        hint_level INT NOT NULL,
        hint_text TEXT NOT NULL,
        FOREIGN KEY (challenge_id) REFERENCES challenges(id)
    )");
    
    // Insert sample data
    $conn->query("INSERT INTO users (username, password, email, is_admin) VALUES
        ('admin', 'admin123', 'admin@training.local', 1),
        ('user1', 'password1', 'user1@training.local', 0),
        ('user2', 'password2', 'user2@training.local', 0),
        ('trainee', 'trainee123', 'trainee@training.local', 0)");
    
    $conn->query("INSERT INTO products (name, description, price, category, stock) VALUES
        ('Laptop', 'High-performance laptop', 15000000, 'Electronics', 10),
        ('Mouse', 'Wireless mouse', 250000, 'Electronics', 50),
        ('Keyboard', 'Mechanical keyboard', 750000, 'Electronics', 30),
        ('Monitor', '27-inch 4K monitor', 5000000, 'Electronics', 15),
        ('Headphones', 'Noise-cancelling headphones', 1500000, 'Electronics', 25),
        ('Webcam', 'HD webcam', 800000, 'Electronics', 20),
        ('USB Hub', '7-port USB hub', 300000, 'Electronics', 40),
        ('Cable', 'HDMI cable 2m', 150000, 'Electronics', 100),
        ('Adapter', 'USB-C adapter', 200000, 'Electronics', 60),
        ('SECRET_PRODUCT', 'Hidden product with flag: FLAG{union_select_master}', 99999999, 'Hidden', 0)");
    
    $conn->query("INSERT INTO challenges (title, description, difficulty, target_page, flag, points) VALUES
        ('Authentication Bypass', 'Bypass the login form using SQL injection', 'Easy', 'login.php', 'FLAG{auth_bypass_101}', 10),
        ('Data Extraction', 'Extract hidden product information using UNION SELECT', 'Medium', 'search.php', 'FLAG{union_select_master}', 20),
        ('Blind Injection', 'Use boolean-based blind SQL injection on profile page', 'Medium', 'profile.php', 'FLAG{blind_sqli_ninja}', 25),
        ('Time-Based Blind', 'Extract data using time-based blind SQL injection', 'Hard', 'profile.php', 'FLAG{time_based_expert}', 30),
        ('Privilege Escalation', 'Gain admin access through SQL injection', 'Hard', 'login.php', 'FLAG{admin_privilege_pwned}', 35)");
    
    // Insert hints (abbreviated for brevity)
    $conn->query("INSERT INTO hints (challenge_id, hint_level, hint_text) VALUES
        (1, 1, 'Look at how the login form processes your input. What happens if you add special characters like single quotes?'),
        (1, 2, 'Try using the OR operator to make the WHERE clause always true. Think about: 1=1'),
        (1, 3, 'Use SQL comments (--) to ignore the password check. The comment will make everything after it ignored.'),
        (1, 4, 'Complete payload: admin\\' OR \\'1\\'=\\'1\\' -- (This makes the query always true and comments out the password check)'),
        (2, 1, 'The search function returns multiple columns. Can you add more columns to the result using UNION?'),
        (2, 2, 'Use UNION SELECT to combine results from different tables. First, find out how many columns the original query returns.'),
        (2, 3, 'Find out the column count using ORDER BY. Try: \\' ORDER BY 1--, then \\' ORDER BY 2--, etc until you get an error.'),
        (2, 4, 'Payload: \\' UNION SELECT id, name, description, price FROM products WHERE category=\\'Hidden\\' -- (This extracts the hidden product)'),
        (3, 1, 'Boolean-based blind injection means you can\\'t see the data directly, but you can tell if a condition is true or false.'),
        (3, 2, 'Try injecting conditions that return different results. For example: 1 AND 1=1 vs 1 AND 1=2'),
        (3, 3, 'Use SUBSTRING to extract data one character at a time. Example: 1 AND SUBSTRING(password,1,1)=\\'a\\''),
        (3, 4, 'Payload: 1 AND SUBSTRING((SELECT password FROM users WHERE username=\\'admin\\'),1,1)=\\'a\\' (Test each character)'),
        (4, 1, 'Time-based blind injection uses delays to extract data. If a condition is true, the response is delayed.'),
        (4, 2, 'Use SLEEP() or BENCHMARK() functions to create delays. Example: IF(condition, SLEEP(5), 0)'),
        (4, 3, 'Combine IF with SUBSTRING to test each character. If correct, the page will delay.'),
        (4, 4, 'Payload: 1 AND IF(SUBSTRING((SELECT password FROM users WHERE id=1),1,1)=\\'a\\',SLEEP(5),0) (Page delays if true)'),
        (5, 1, 'You need to modify the is_admin flag in the database. Can you use SQL injection to UPDATE data?'),
        (5, 2, 'Some SQL injection points allow multiple queries using semicolons. Try: \\'; UPDATE users SET is_admin=1 WHERE username=\\'trainee\\';--'),
        (5, 3, 'If multiple queries don\\'t work, try using UNION to manipulate the login query to return admin privileges.'),
        (5, 4, 'Payload: trainee\\' UNION SELECT id, username, password, email, 1, created_at, last_login FROM users WHERE username=\\'trainee\\' -- (Returns your user with is_admin=1)')");
    
    // Restore user progress
    foreach ($progress_backup as $progress) {
        $stmt = $conn->prepare("INSERT INTO user_progress (user_id, challenge_id, completed, completed_at, hints_used, attempts) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisii", 
            $progress['user_id'], 
            $progress['challenge_id'], 
            $progress['completed'], 
            $progress['completed_at'], 
            $progress['hints_used'], 
            $progress['attempts']
        );
        $stmt->execute();
    }
    
    closeConnection($conn);
    
    $success = true;
    $message = 'Database has been successfully reset to initial state. User progress has been preserved.';
    
    // Log completion
    logActivity(getCurrentUserId(), 'DATABASE_RESET_COMPLETE', 'Database reset completed successfully');
}

displayHeader('Reset Database');
?>

<div class="card">
    <h2>🔄 Reset Database</h2>
    <p>This will reset the database to its initial state with fresh sample data.</p>
</div>

<?php if ($success): ?>
    <div class="success-box">
        <h3>✅ Reset Successful!</h3>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p><a href="index.php" class="btn btn-primary">Back to Dashboard</a></p>
    </div>
<?php else: ?>
    <div class="card">
        <h3>⚠️ Warning</h3>
        <p>This action will:</p>
        <ul>
            <li>Drop and recreate all database tables</li>
            <li>Restore sample data (users, products, challenges, hints)</li>
            <li>Preserve user progress records</li>
            <li>Clear activity logs</li>
        </ul>
        
        <h3>What will be preserved:</h3>
        <ul>
            <li>✅ User accounts</li>
            <li>✅ Challenge completion status</li>
            <li>✅ Hints used counters</li>
        </ul>
        
        <h3>What will be reset:</h3>
        <ul>
            <li>❌ Activity logs</li>
            <li>❌ Any custom data added to tables</li>
        </ul>
        
        <form method="POST" onsubmit="return confirmDatabaseReset()">
            <input type="hidden" name="confirm_reset" value="yes">
            <button type="submit" class="btn btn-danger">Reset Database</button>
            <a href="index.php" class="btn btn-primary">Cancel</a>
        </form>
    </div>
<?php endif; ?>

<?php displayFooter(); ?>
