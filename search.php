<?php
/**
 * Vulnerable Product Search Page
 * INSIDER Training Platform
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

$search_term = '';
$query_executed = '';
$results = [];
$error = '';

// Handle search form submission
if (isset($_GET['search'])) {
    // Get search term WITHOUT sanitization (VULNERABLE!)
    $search_term = $_GET['search'];
    
    // Build vulnerable SQL query using string concatenation
    // This is INTENTIONALLY VULNERABLE to SQL injection
    $query = "SELECT id, name, description, price FROM products WHERE name LIKE '%$search_term%'";
    
    // Store query for display
    $query_executed = $query;
    
    // Execute the vulnerable query
    $conn = getConnection();
    $result = $conn->query($query);
    
    if ($result) {
        // Fetch all results
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        
        // Log the search attempt
        logActivity(getCurrentUserId(), 'SEARCH_QUERY', $query);
    } else {
        // Query failed - display error for educational purposes
        $error = $conn->error;
        logActivity(getCurrentUserId(), 'SEARCH_ERROR', $query . ' | Error: ' . $error);
    }
    
    closeConnection($conn);
}

displayHeader('Product Search');
?>

<div class="card">
    <h2>🔍 Product Search</h2>
    <p>Search for products in our database. Try searching for "laptop", "mouse", or... something more interesting? 😉</p>
    
    <form method="GET" action="search.php">
        <div class="form-group">
            <label for="search">Search Products:</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Enter product name">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="search.php" class="btn btn-warning">Clear</a>
    </form>
    
    <?php if ($query_executed): ?>
        <?php displayQuery($query_executed); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?php displayError($error); ?>
    <?php endif; ?>
</div>

<?php if (!empty($results)): ?>
    <div class="card">
        <h3>📦 Search Results (<?php echo count($results); ?> found)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif ($search_term && empty($results) && !$error): ?>
    <div class="card">
        <p>No products found matching "<?php echo htmlspecialchars($search_term); ?>"</p>
    </div>
<?php endif; ?>

<div class="card">
    <h3>💡 SQL Injection Techniques</h3>
    <p>This search function is <strong>intentionally vulnerable</strong> to SQL injection. Try these techniques:</p>
    
    <h4>1. Basic Injection Test</h4>
    <p>Try entering a single quote to see if you get an error:</p>
    <code>' </code>
    
    <h4>2. Column Enumeration</h4>
    <p>Find out how many columns the query returns using ORDER BY:</p>
    <code>' ORDER BY 1--</code><br>
    <code>' ORDER BY 2--</code><br>
    <code>' ORDER BY 3--</code><br>
    <code>' ORDER BY 4--</code><br>
    <code>' ORDER BY 5--</code> (This should give an error)
    
    <h4>3. UNION SELECT Attack</h4>
    <p>Once you know the column count, use UNION SELECT to extract data:</p>
    <code>' UNION SELECT 1,2,3,4--</code><br>
    <code>' UNION SELECT id, name, description, price FROM products--</code>
    
    <h4>4. Extract Hidden Data</h4>
    <p>There's a hidden product with category='Hidden'. Can you find it?</p>
    <code>' UNION SELECT id, name, description, price FROM products WHERE category='Hidden'--</code>
    
    <h4>5. Extract Data from Other Tables</h4>
    <p>Try extracting user information:</p>
    <code>' UNION SELECT id, username, password, email FROM users--</code>
    
    <h4>Why is this vulnerable?</h4>
    <div class="code-display">
        <pre>// VULNERABLE CODE (DO NOT USE IN PRODUCTION!)
$search_term = $_GET['search'];  // No sanitization!

// String concatenation with LIKE clause
$query = "SELECT id, name, description, price FROM products WHERE name LIKE '%$search_term%'";

// Direct query execution
$result = $conn->query($query);</pre>
    </div>
    
    <h4>How to fix it?</h4>
    <div class="code-display">
        <pre>// SECURE CODE (Use this in production!)
$search_term = $_GET['search'];

// Use prepared statements with parameter binding
$stmt = $conn->prepare("SELECT id, name, description, price FROM products WHERE name LIKE ?");
$search_param = "%$search_term%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();</pre>
    </div>
</div>

<div class="card">
    <h3>🎯 Challenge Hint</h3>
    <p>Looking for <strong>Challenge 2: Data Extraction</strong>? The flag is hidden in a secret product!</p>
    <p>Use UNION SELECT to extract products with category='Hidden' to find the flag.</p>
    <p><a href="challenges/challenge2.php" class="btn btn-success">Go to Challenge 2</a></p>
</div>

<?php displayFooter(); ?>
