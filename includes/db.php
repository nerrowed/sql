<?php
/**
 * Database Connection Management
 * SQL Injection Training Platform
 */

require_once __DIR__ . '/../config.php';

/**
 * Get database connection
 * @return mysqli Database connection object
 */
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Close database connection
 * @param mysqli $conn Database connection to close
 */
function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

/**
 * Execute a query (vulnerable - for educational purposes)
 * @param string $query SQL query to execute
 * @return mysqli_result|bool Query result
 */
function executeQuery($query) {
    $conn = getConnection();
    $result = $conn->query($query);
    
    if (!$result) {
        // Display error for educational purposes
        echo "<div class='error-box'>";
        echo "<h3>SQL Error (Educational)</h3>";
        echo "<p><strong>Error Message:</strong> " . htmlspecialchars($conn->error) . "</p>";
        echo "<p><strong>Why this is helpful:</strong> Error messages reveal database structure</p>";
        echo "</div>";
    }
    
    return $result;
}

/**
 * Get last database error
 * @return string Last error message
 */
function getLastError() {
    $conn = getConnection();
    return $conn->error;
}
?>
