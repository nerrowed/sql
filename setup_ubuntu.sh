#!/bin/bash

# SQL Injection Training Platform - Ubuntu Setup Script
# This script automates the installation process on Ubuntu Server

set -e  # Exit on error

echo "=========================================="
echo "SQL Injection Training Platform"
echo "Ubuntu Server Setup Script"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root or with sudo"
    echo "Usage: sudo bash setup_ubuntu.sh"
    exit 1
fi

# Get server IP
SERVER_IP=$(hostname -I | awk '{print $1}')

echo "Detected Server IP: $SERVER_IP"
echo ""

# Prompt for database password
read -sp "Enter MySQL password for 'sqli_training' user: " DB_PASSWORD
echo ""
read -sp "Confirm password: " DB_PASSWORD_CONFIRM
echo ""

if [ "$DB_PASSWORD" != "$DB_PASSWORD_CONFIRM" ]; then
    echo "Passwords do not match!"
    exit 1
fi

echo ""
echo "Starting installation..."
echo ""

# Update system
echo "[1/9] Updating system packages..."
apt update -qq
apt upgrade -y -qq

# Install Apache
echo "[2/9] Installing Apache web server..."
apt install apache2 -y -qq
systemctl start apache2
systemctl enable apache2

# Install MySQL
echo "[3/9] Installing MySQL server..."
apt install mysql-server -y -qq
systemctl start mysql
systemctl enable mysql

# Install PHP
echo "[4/9] Installing PHP and extensions..."
apt install php libapache2-mod-php php-mysql php-cli php-common php-mbstring -y -qq
systemctl restart apache2

# Configure MySQL
echo "[5/9] Configuring MySQL database..."
mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS sqli_training_db;
CREATE USER IF NOT EXISTS 'sqli_training'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON sqli_training_db.* TO 'sqli_training'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

# Create project directory
echo "[6/9] Creating project directory..."
mkdir -p /var/www/html/sql-injection-training

# Set permissions
echo "[7/9] Setting permissions..."
chown -R www-data:www-data /var/www/html/sql-injection-training
chmod -R 755 /var/www/html/sql-injection-training

# Configure firewall
echo "[8/9] Configuring firewall..."
if command -v ufw &> /dev/null; then
    ufw --force enable
    ufw allow 22/tcp
    ufw allow 80/tcp
    echo "Firewall configured (SSH and HTTP allowed)"
else
    echo "UFW not found, skipping firewall configuration"
fi

# Create config file template
echo "[9/9] Creating configuration template..."
cat > /var/www/html/sql-injection-training/config.php.example <<'EOF'
<?php
/**
 * Database Configuration
 * SQL Injection Training Platform
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'sqli_training');
define('DB_PASS', 'YOUR_PASSWORD_HERE');
define('DB_NAME', 'sqli_training_db');

// Application settings
define('APP_NAME', 'SQL Injection Training Platform');
define('BASE_URL', 'http://YOUR_SERVER_IP/sql-injection-training');

// Error reporting (enabled for educational purposes)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
session_start();
?>
EOF

echo ""
echo "=========================================="
echo "Installation Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo ""
echo "1. Copy your application files to:"
echo "   /var/www/html/sql-injection-training/"
echo ""
echo "2. Update config.php with these values:"
echo "   DB_HOST: localhost"
echo "   DB_USER: sqli_training"
echo "   DB_PASS: [the password you entered]"
echo "   DB_NAME: sqli_training_db"
echo "   BASE_URL: http://$SERVER_IP/sql-injection-training"
echo ""
echo "3. Initialize database by visiting:"
echo "   http://$SERVER_IP/sql-injection-training/db_setup.php"
echo ""
echo "4. Access the platform at:"
echo "   http://$SERVER_IP/sql-injection-training/"
echo ""
echo "Test Accounts:"
echo "   admin / admin123"
echo "   trainee / trainee123"
echo ""
echo "=========================================="
echo "Service Status:"
echo "=========================================="
systemctl status apache2 --no-pager | grep Active
systemctl status mysql --no-pager | grep Active
echo ""
echo "Installation log saved to: /var/log/sqli-training-setup.log"
echo ""
echo "⚠️  SECURITY WARNING:"
echo "This platform is INTENTIONALLY VULNERABLE!"
echo "Only use in isolated training environments."
echo "Never expose to public internet!"
echo ""
