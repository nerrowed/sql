<?php
/**
 * Utility Functions
 * SQL Injection Training Platform
 */

/**
 * Display executed SQL query for educational purposes
 * @param string $query The SQL query that was executed
 */
function displayQuery($query) {
    echo "<div class='query-display'>";
    echo "<h4>📋 Executed SQL Query:</h4>";
    echo "<pre>" . htmlspecialchars($query) . "</pre>";
    echo "<p class='hint'>💡 Notice how your input is directly inserted into the query!</p>";
    echo "</div>";
}

/**
 * Display error message with educational context
 * @param string $error Error message to display
 */
function displayError($error) {
    echo "<div class='error-box'>";
    echo "<h3>⚠️ SQL Error (Educational)</h3>";
    echo "<p><strong>Error Message:</strong> " . htmlspecialchars($error) . "</p>";
    echo "<p><strong>Why this is helpful:</strong> Error messages reveal database structure</p>";
    echo "</div>";
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool True if user is admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Get current user ID
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null Username or null if not logged in
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Display page header with warning banner
 * @param string $title Page title
 */
function displayHeader($title = '') {
    $pageTitle = $title ? $title . ' - ' . APP_NAME : APP_NAME;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle); ?></title>
        <link rel="stylesheet" href="/sql/assets/css/style.css">
    </head>
    <body>
        <div class="warning-banner">
            ⚠️ <strong>WARNING:</strong> This platform is INTENTIONALLY VULNERABLE for educational purposes. 
            DO NOT USE THIS CODE IN PRODUCTION!
        </div>
        <nav class="navbar">
            <div class="nav-brand"><?php echo APP_NAME; ?></div>
            <div class="nav-links">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/challenges/index.php">Challenges</a>
                    <a href="<?php echo BASE_URL; ?>/search.php">Search</a>
                    <a href="<?php echo BASE_URL; ?>/profile.php?id=<?php echo getCurrentUserId(); ?>">Profile</a>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/index.php">Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/logout.php">Logout (<?php echo htmlspecialchars(getCurrentUsername()); ?>)</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php">Login</a>
                <?php endif; ?>
            </div>
        </nav>
        <div class="container">
    <?php
}

/**
 * Display page footer
 */
function displayFooter() {
    ?>
        </div>
        <footer class="footer">
            <p>&copy; 2026 SQL Injection Training Platform - For Educational Purposes Only</p>
            <p><a href="<?php echo BASE_URL; ?>/docs/techniques.php">SQL Injection Techniques</a> | 
               <a href="<?php echo BASE_URL; ?>/docs/secure_examples.php">Secure Code Examples</a></p>
        </footer>
        <script src="/sql/assets/js/app.js"></script>
    </body>
    </html>
    <?php
}

/**
 * Redirect to another page
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>
