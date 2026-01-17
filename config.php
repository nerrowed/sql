<?php
/**
 * Database Configuration
 * SQL Injection Training Platform
 * 
 * WARNING: This is for EDUCATIONAL PURPOSES ONLY
 * DO NOT USE IN PRODUCTION
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'sqli_training');
define('DB_PASS', 'training_password');
define('DB_NAME', 'sqli_training_db');

// Application settings
define('APP_NAME', 'SQL Injection Training Platform');
define('BASE_URL', 'http://localhost/sql-injection-training');

// Error reporting (enabled for educational purposes)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
session_start();
?>
