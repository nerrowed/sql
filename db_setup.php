<?php
/**
 * Database Setup Script
 * INSIDER Training Platform
 * 
 * This script creates all necessary tables and inserts sample data
 */

require_once 'config.php';

// Create connection without database selection first
// Try with config credentials first
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);

// If connection fails, try with root without password (common in fresh MySQL installs)
if ($conn->connect_error) {
    $conn = @new mysqli(DB_HOST, 'root', '');
    
    if ($conn->connect_error) {
        die("<h2>Connection failed!</h2>
        <p>Error: " . $conn->connect_error . "</p>
        <h3>Solutions:</h3>
        <ol>
            <li><strong>Set MySQL root password:</strong>
                <pre>sudo mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';
FLUSH PRIVILEGES;
EXIT;</pre>
            </li>
            <li><strong>Or create the database user manually:</strong>
                <pre>sudo mysql -u root
CREATE DATABASE sqli_training_db;
CREATE USER 'sqli_training'@'localhost' IDENTIFIED BY 'training_password';
GRANT ALL PRIVILEGES ON sqli_training_db.* TO 'sqli_training'@'localhost';
FLUSH PRIVILEGES;
EXIT;</pre>
            </li>
            <li><strong>Then update config.php with the correct credentials</strong></li>
        </ol>");
    }
}

echo "<h2>INSIDER Training Platform - Database Setup</h2>";

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Database created or already exists</p>";
} else {
    die("<p>✗ Error creating database: " . $conn->error . "</p>");
}

// Select the database
$conn->select_db(DB_NAME);

// Drop existing tables (for clean setup)
echo "<h3>Dropping existing tables...</h3>";
$tables = ['activity_logs', 'user_progress', 'hints', 'challenges', 'products', 'users'];
foreach ($tables as $table) {
    $conn->query("DROP TABLE IF EXISTS $table");
    echo "<p>✓ Dropped table: $table</p>";
}

// Create users table
$sql = "CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Table 'users' created successfully</p>";
} else {
    die("<p>✗ Error creating users table: " . $conn->error . "</p>");
}

// Create products table
$sql = "CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2),
    category VARCHAR(50),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Table 'products' created successfully</p>";
} else {
    die("<p>✗ Error creating products table: " . $conn->error . "</p>");
}

// Create challenges table
$sql = "CREATE TABLE challenges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty ENUM('Easy', 'Medium', 'Hard') DEFAULT 'Easy',
    target_page VARCHAR(100),
    flag VARCHAR(100),
    points INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Table 'challenges' created successfully</p>";
} else {
    die("<p>✗ Error creating challenges table: " . $conn->error . "</p>");
}

// Create user_progress table
$sql = "CREATE TABLE user_progress (
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
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Table 'user_progress' created successfully</p>";
} else {
    die("<p>✗ Error creating user_progress table: " . $conn->error . "</p>");
}

// Create activity_logs table
$sql = "CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    username VARCHAR(50),
    action VARCHAR(100),
    query_executed TEXT,
    success TINYINT(1),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Table 'activity_logs' created successfully</p>";
} else {
    die("<p>✗ Error creating activity_logs table: " . $conn->error . "</p>");
}

// Create hints table
$sql = "CREATE TABLE hints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    challenge_id INT NOT NULL,
    hint_level INT NOT NULL,
    hint_text TEXT NOT NULL,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Table 'hints' created successfully</p>";
} else {
    die("<p>✗ Error creating hints table: " . $conn->error . "</p>");
}

echo "<h3>Inserting sample data...</h3>";

// Insert sample users (plaintext passwords for demo)
$sql = "INSERT INTO users (username, password, email, is_admin) VALUES
('admin', 'admin123', 'admin@training.local', 1),
('user1', 'password1', 'user1@training.local', 0),
('user2', 'password2', 'user2@training.local', 0),
('trainee', 'trainee123', 'trainee@training.local', 0)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Sample users inserted</p>";
} else {
    die("<p>✗ Error inserting users: " . $conn->error . "</p>");
}

// Insert sample products
$sql = "INSERT INTO products (name, description, price, category, stock) VALUES
('Laptop', 'High-performance laptop', 15000000, 'Electronics', 10),
('Mouse', 'Wireless mouse', 250000, 'Electronics', 50),
('Keyboard', 'Mechanical keyboard', 750000, 'Electronics', 30),
('Monitor', '27-inch 4K monitor', 5000000, 'Electronics', 15),
('Headphones', 'Noise-cancelling headphones', 1500000, 'Electronics', 25),
('Webcam', 'HD webcam', 800000, 'Electronics', 20),
('USB Hub', '7-port USB hub', 300000, 'Electronics', 40),
('Cable', 'HDMI cable 2m', 150000, 'Electronics', 100),
('Adapter', 'USB-C adapter', 200000, 'Electronics', 60),
('SECRET_PRODUCT', 'Hidden product with flag: FLAG{union_select_master}', 99999999, 'Hidden', 0)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Sample products inserted</p>";
} else {
    die("<p>✗ Error inserting products: " . $conn->error . "</p>");
}

// Insert challenges
$sql = "INSERT INTO challenges (title, description, difficulty, target_page, flag, points) VALUES
('Authentication Bypass', 'Bypass the login form using SQL injection', 'Easy', 'login.php', 'FLAG{auth_bypass_101}', 10),
('Data Extraction', 'Extract hidden product information using UNION SELECT', 'Medium', 'search.php', 'FLAG{union_select_master}', 20),
('Blind Injection', 'Use boolean-based blind SQL injection on profile page', 'Medium', 'profile.php', 'FLAG{blind_sqli_ninja}', 25),
('Time-Based Blind', 'Extract data using time-based blind SQL injection', 'Hard', 'profile.php', 'FLAG{time_based_expert}', 30),
('Privilege Escalation', 'Gain admin access through SQL injection', 'Hard', 'login.php', 'FLAG{admin_privilege_pwned}', 35)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Challenges inserted</p>";
} else {
    die("<p>✗ Error inserting challenges: " . $conn->error . "</p>");
}

// Insert hints for Challenge 1 (Authentication Bypass)
$sql = "INSERT INTO hints (challenge_id, hint_level, hint_text) VALUES
(1, 1, 'Look at how the login form processes your input. What happens if you add special characters like single quotes?'),
(1, 2, 'Try using the OR operator to make the WHERE clause always true. Think about: 1=1'),
(1, 3, 'Use SQL comments (--) to ignore the password check. The comment will make everything after it ignored.'),
(1, 4, 'Complete payload: admin\\' OR \\'1\\'=\\'1\\' -- (This makes the query always true and comments out the password check)')";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Hints for Challenge 1 inserted</p>";
} else {
    die("<p>✗ Error inserting hints: " . $conn->error . "</p>");
}

// Insert hints for Challenge 2 (Data Extraction)
$sql = "INSERT INTO hints (challenge_id, hint_level, hint_text) VALUES
(2, 1, 'The search function returns multiple columns. Can you add more columns to the result using UNION?'),
(2, 2, 'Use UNION SELECT to combine results from different tables. First, find out how many columns the original query returns.'),
(2, 3, 'Find out the column count using ORDER BY. Try: \\' ORDER BY 1--, then \\' ORDER BY 2--, etc until you get an error.'),
(2, 4, 'Payload: \\' UNION SELECT id, name, description, price FROM products WHERE category=\\'Hidden\\' -- (This extracts the hidden product)')";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Hints for Challenge 2 inserted</p>";
} else {
    die("<p>✗ Error inserting hints: " . $conn->error . "</p>");
}

// Insert hints for Challenge 3 (Blind Injection)
$sql = "INSERT INTO hints (challenge_id, hint_level, hint_text) VALUES
(3, 1, 'Boolean-based blind injection means you can\\'t see the data directly, but you can tell if a condition is true or false.'),
(3, 2, 'Try injecting conditions that return different results. For example: 1 AND 1=1 vs 1 AND 1=2'),
(3, 3, 'Use SUBSTRING to extract data one character at a time. Example: 1 AND SUBSTRING(password,1,1)=\\'a\\''),
(3, 4, 'Payload: 1 AND SUBSTRING((SELECT password FROM users WHERE username=\\'admin\\'),1,1)=\\'a\\' (Test each character)')";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Hints for Challenge 3 inserted</p>";
} else {
    die("<p>✗ Error inserting hints: " . $conn->error . "</p>");
}

// Insert hints for Challenge 4 (Time-Based Blind)
$sql = "INSERT INTO hints (challenge_id, hint_level, hint_text) VALUES
(4, 1, 'Time-based blind injection uses delays to extract data. If a condition is true, the response is delayed.'),
(4, 2, 'Use SLEEP() or BENCHMARK() functions to create delays. Example: IF(condition, SLEEP(5), 0)'),
(4, 3, 'Combine IF with SUBSTRING to test each character. If correct, the page will delay.'),
(4, 4, 'Payload: 1 AND IF(SUBSTRING((SELECT password FROM users WHERE id=1),1,1)=\\'a\\',SLEEP(5),0) (Page delays if true)')";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Hints for Challenge 4 inserted</p>";
} else {
    die("<p>✗ Error inserting hints: " . $conn->error . "</p>");
}

// Insert hints for Challenge 5 (Privilege Escalation)
$sql = "INSERT INTO hints (challenge_id, hint_level, hint_text) VALUES
(5, 1, 'You need to modify the is_admin flag in the database. Can you use SQL injection to UPDATE data?'),
(5, 2, 'Some SQL injection points allow multiple queries using semicolons. Try: \\'; UPDATE users SET is_admin=1 WHERE username=\\'trainee\\';--'),
(5, 3, 'If multiple queries don\\'t work, try using UNION to manipulate the login query to return admin privileges.'),
(5, 4, 'Payload: trainee\\' UNION SELECT id, username, password, email, 1, created_at, last_login FROM users WHERE username=\\'trainee\\' -- (Returns your user with is_admin=1)')";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Hints for Challenge 5 inserted</p>";
} else {
    die("<p>✗ Error inserting hints: " . $conn->error . "</p>");
}

echo "<h3>✅ Database setup completed successfully!</h3>";
echo "<p><strong>Test Accounts:</strong></p>";
echo "<ul>";
echo "<li>Admin: username=<code>admin</code>, password=<code>admin123</code></li>";
echo "<li>Trainee: username=<code>trainee</code>, password=<code>trainee123</code></li>";
echo "<li>User1: username=<code>user1</code>, password=<code>password1</code></li>";
echo "<li>User2: username=<code>user2</code>, password=<code>password2</code></li>";
echo "</ul>";
echo "<p><a href='index.php'>Go to Home Page</a> | <a href='login.php'>Go to Login</a></p>";

$conn->close();
?>
