<?php
/**
 * Logout Page
 * SQL Injection Training Platform
 */

require_once 'config.php';

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php?message=logged_out");
exit();
?>
