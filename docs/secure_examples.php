<?php
/**
 * Secure Code Examples
 * INSIDER Training Platform
 */

require_once '../config.php';
require_once '../includes/functions.php';

displayHeader('Secure Code Examples');
?>

<div class="card">
    <h2>🔒 Secure Code Examples</h2>
    <p>Learn how to write secure code that prevents SQL injection vulnerabilities.</p>
</div>

<div class="card">
    <h3>1. Login Authentication - Secure Version</h3>
    
    <h4>❌ Vulnerable Code:</h4>
    <div class="code-display">
        <pre>// NEVER DO THIS!
$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $conn->query($query);</pre>
    </div>
    
    <h4>✅ Secure Code:</h4>
    <div class="code-display">
        <pre>// Use prepared statements with parameter binding
$username = $_POST['username'];
$password = $_POST['password'];

// Prepare statement with placeholders
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verify password using password_verify()
    if (password_verify($password, $user['password_hash'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
    }
}</pre>
    </div>
    
    <h4>Why is this secure?</h4>
    <ul>
        <li>Uses prepared statements with parameter binding</li>
        <li>Database driver automatically escapes special characters</li>
        <li>User input is never directly concatenated into SQL</li>
        <li>Passwords are hashed with password_hash() and verified with password_verify()</li>
    </ul>
</div>

<div class="card">
    <h3>2. Search Functionality - Secure Version</h3>
    
    <h4>❌ Vulnerable Code:</h4>
    <div class="code-display">
        <pre>// NEVER DO THIS!
$search = $_GET['search'];

$query = "SELECT * FROM products WHERE name LIKE '%$search%'";
$result = $conn->query($query);</pre>
    </div>
    
    <h4>✅ Secure Code:</h4>
    <div class="code-display">
        <pre>// Use prepared statements for LIKE queries
$search = $_GET['search'];

// Prepare statement with placeholder
$stmt = $conn->prepare("SELECT id, name, description, price FROM products WHERE name LIKE ?");

// Add wildcards to the parameter, not the query
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();</pre>
    </div>
    
    <h4>Why is this secure?</h4>
    <ul>
        <li>Prepared statement prevents SQL injection</li>
        <li>Wildcards are added to the parameter value, not the query</li>
        <li>Only necessary columns are selected (principle of least privilege)</li>
    </ul>
</div>

<div class="card">
    <h3>3. Profile Page - Secure Version</h3>
    
    <h4>❌ Vulnerable Code:</h4>
    <div class="code-display">
        <pre>// NEVER DO THIS!
$user_id = $_GET['id'];

$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);</pre>
    </div>
    
    <h4>✅ Secure Code:</h4>
    <div class="code-display">
        <pre>// Validate input and use prepared statements
$user_id = $_GET['id'];

// Validate that ID is numeric
if (!is_numeric($user_id)) {
    die("Invalid user ID");
}

// Cast to integer for extra safety
$user_id = (int)$user_id;

// Use prepared statement
$stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Don't display sensitive data like passwords
// Use generic error messages</pre>
    </div>
    
    <h4>Why is this secure?</h4>
    <ul>
        <li>Input validation (is_numeric check)</li>
        <li>Type casting to integer</li>
        <li>Prepared statement with parameter binding</li>
        <li>Only non-sensitive columns are selected</li>
        <li>Generic error messages don't reveal database structure</li>
    </ul>
</div>

<div class="card">
    <h3>4. Password Storage - Secure Version</h3>
    
    <h4>❌ Vulnerable Code:</h4>
    <div class="code-display">
        <pre>// NEVER DO THIS!
$password = $_POST['password'];

// Storing plaintext password
$query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";</pre>
    </div>
    
    <h4>✅ Secure Code:</h4>
    <div class="code-display">
        <pre>// Hash passwords before storing
$password = $_POST['password'];

// Use password_hash() with default algorithm (bcrypt)
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Store the hash, not the plaintext password
$stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password_hash);
$stmt->execute();

// To verify during login:
// password_verify($input_password, $stored_hash)</pre>
    </div>
    
    <h4>Why is this secure?</h4>
    <ul>
        <li>Passwords are hashed using strong algorithm (bcrypt)</li>
        <li>Each password gets a unique salt automatically</li>
        <li>Even if database is compromised, passwords can't be easily recovered</li>
        <li>password_verify() handles timing-safe comparison</li>
    </ul>
</div>

<div class="card">
    <h3>5. Error Handling - Secure Version</h3>
    
    <h4>❌ Vulnerable Code:</h4>
    <div class="code-display">
        <pre>// NEVER DO THIS!
$result = $conn->query($query);

if (!$result) {
    // Exposing detailed error messages
    echo "Error: " . $conn->error;
    echo "Query: " . $query;
}</pre>
    </div>
    
    <h4>✅ Secure Code:</h4>
    <div class="code-display">
        <pre>// Use generic error messages for users
$result = $conn->query($query);

if (!$result) {
    // Log detailed error for developers
    error_log("Database error: " . $conn->error);
    error_log("Query: " . $query);
    
    // Show generic message to users
    die("An error occurred. Please try again later.");
}</pre>
    </div>
    
    <h4>Why is this secure?</h4>
    <ul>
        <li>Detailed errors are logged, not displayed</li>
        <li>Users see generic error messages</li>
        <li>Database structure is not revealed</li>
        <li>Developers can still debug using logs</li>
    </ul>
</div>

<div class="card">
    <h3>📋 Security Checklist</h3>
    <p>Follow these best practices to prevent SQL injection:</p>
    
    <h4>✅ Always Do:</h4>
    <ul>
        <li>Use prepared statements with parameter binding</li>
        <li>Validate and sanitize all user input</li>
        <li>Use type casting when appropriate (e.g., (int) for IDs)</li>
        <li>Hash passwords with password_hash()</li>
        <li>Use principle of least privilege (select only needed columns)</li>
        <li>Log errors for developers, show generic messages to users</li>
        <li>Keep database credentials secure</li>
        <li>Use ORM frameworks when possible (they use prepared statements)</li>
    </ul>
    
    <h4>❌ Never Do:</h4>
    <ul>
        <li>Concatenate user input directly into SQL queries</li>
        <li>Trust user input without validation</li>
        <li>Store passwords in plaintext</li>
        <li>Display detailed error messages to users</li>
        <li>Use dynamic SQL without proper escaping</li>
        <li>Disable security features for "convenience"</li>
    </ul>
</div>

<div class="card">
    <h3>🔧 PHP Security Functions</h3>
    
    <h4>Prepared Statements (mysqli):</h4>
    <div class="code-display">
        <pre>$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);  // "i" = integer, "s" = string
$stmt->execute();
$result = $stmt->get_result();</pre>
    </div>
    
    <h4>Prepared Statements (PDO):</h4>
    <div class="code-display">
        <pre>$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$result = $stmt->fetchAll();</pre>
    </div>
    
    <h4>Password Hashing:</h4>
    <div class="code-display">
        <pre>// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verify password
if (password_verify($input_password, $stored_hash)) {
    // Password is correct
}</pre>
    </div>
    
    <h4>Input Validation:</h4>
    <div class="code-display">
        <pre>// Validate integer
if (!is_numeric($id)) {
    die("Invalid ID");
}
$id = (int)$id;

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email");
}

// Sanitize string (for display, not for SQL!)
$clean = htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');</pre>
    </div>
</div>

<div class="card">
    <p><a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-primary">Back to Home</a></p>
    <p><a href="techniques.php" class="btn btn-success">View SQL Injection Techniques</a></p>
</div>

<?php displayFooter(); ?>
