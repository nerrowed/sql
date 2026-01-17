# SQL Injection Training Platform

⚠️ **WARNING: This platform is INTENTIONALLY VULNERABLE for educational purposes. DO NOT USE IN PRODUCTION!**

## Overview

Platform training cybersecurity berbasis web yang sengaja dibuat vulnerable terhadap SQL injection untuk tujuan edukasi. Platform ini menyediakan environment yang aman dan terkontrol untuk mempelajari berbagai teknik SQL injection.

## Features

- 🔓 **Vulnerable Login System** - Practice authentication bypass
- 🔍 **Vulnerable Search** - Learn UNION-based injection
- 👤 **Vulnerable Profile Page** - Practice blind SQL injection
- 🎯 **5 Progressive Challenges** - From easy to hard
- 💡 **Hint System** - Get help when stuck
- 📊 **Admin Dashboard** - Monitor trainee progress
- 📚 **Educational Feedback** - Learn from every attempt

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Web Server**: Apache with mod_php

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server with mod_php
- mysqli PHP extension

### Setup on Ubuntu Server

#### 1. Update System
```bash
sudo apt update
sudo apt upgrade -y
```

#### 2. Install Apache Web Server
```bash
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2
```

#### 3. Install MySQL Server
```bash
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure MySQL installation (optional but recommended)
sudo mysql_secure_installation
```

#### 4. Install PHP and Required Extensions
```bash
sudo apt install php libapache2-mod-php php-mysql -y

# Verify PHP installation
php -v
```

#### 5. Deploy Application Files
```bash
# Navigate to web root
cd /var/www/html

# Create project directory
sudo mkdir sql-injection-training
cd sql-injection-training

# Copy all your project files here
# Or clone from repository:
# sudo git clone <your-repo-url> .

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/sql-injection-training
sudo chmod -R 755 /var/www/html/sql-injection-training
```

#### 6. Configure MySQL Database
```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE sqli_training_db;
CREATE USER 'sqli_training'@'localhost' IDENTIFIED BY 'training_password';
GRANT ALL PRIVILEGES ON sqli_training_db.* TO 'sqli_training'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 7. Configure Application
Edit `config.php` and update database credentials:
```bash
sudo nano /var/www/html/sql-injection-training/config.php
```

Update these lines:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'sqli_training');
define('DB_PASS', 'training_password');
define('DB_NAME', 'sqli_training_db');
define('BASE_URL', 'http://your-server-ip/sql-injection-training');
```

#### 8. Configure Apache (Optional - Virtual Host)
```bash
sudo nano /etc/apache2/sites-available/sqli-training.conf
```

Add this configuration:
```apache
<VirtualHost *:80>
    ServerAdmin admin@localhost
    DocumentRoot /var/www/html/sql-injection-training
    ServerName your-domain.com
    
    <Directory /var/www/html/sql-injection-training>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sqli-training-error.log
    CustomLog ${APACHE_LOG_DIR}/sqli-training-access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite sqli-training.conf
sudo systemctl reload apache2
```

#### 9. Setup Database Tables
Open browser and navigate to:
```
http://your-server-ip/sql-injection-training/db_setup.php
```

This will create all tables and insert sample data.

#### 10. Test Installation
Navigate to:
```
http://your-server-ip/sql-injection-training/
```

Login with test accounts (see below).

### Troubleshooting Ubuntu Server

#### PHP Not Working
```bash
# Check if PHP module is enabled
sudo a2enmod php7.4  # or php8.0, php8.1 depending on version
sudo systemctl restart apache2
```

#### Permission Issues
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/sql-injection-training
sudo chmod -R 755 /var/www/html/sql-injection-training

# If session issues occur
sudo chmod 777 /var/lib/php/sessions
```

#### MySQL Connection Failed
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test MySQL connection
mysql -u sqli_training -p sqli_training_db

# Check MySQL error logs
sudo tail -f /var/log/mysql/error.log
```

#### Apache Not Starting
```bash
# Check Apache status
sudo systemctl status apache2

# Check Apache error logs
sudo tail -f /var/log/apache2/error.log

# Test Apache configuration
sudo apache2ctl configtest
```

#### Firewall Configuration
```bash
# Allow HTTP traffic
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp  # if using HTTPS

# Check firewall status
sudo ufw status
```

### Security Considerations for Ubuntu Server

⚠️ **CRITICAL: This is a training platform with intentional vulnerabilities!**

1. **Network Isolation**
   ```bash
   # Only allow access from specific IPs
   sudo ufw allow from 192.168.1.0/24 to any port 80
   ```

2. **Use VPN or Private Network**
   - Deploy on internal network only
   - Use VPN for remote access
   - Never expose to public internet

3. **Firewall Rules**
   ```bash
   # Block all incoming except SSH and HTTP from trusted IPs
   sudo ufw default deny incoming
   sudo ufw default allow outgoing
   sudo ufw allow from YOUR_IP to any port 22
   sudo ufw allow from YOUR_IP to any port 80
   sudo ufw enable
   ```

4. **Monitor Access**
   ```bash
   # Monitor Apache access logs
   sudo tail -f /var/log/apache2/access.log
   
   # Monitor MySQL logs
   sudo tail -f /var/log/mysql/mysql.log
   ```

### Quick Setup Script for Ubuntu

Create a setup script:
```bash
sudo nano setup.sh
```

Add this content:
```bash
#!/bin/bash

echo "Installing SQL Injection Training Platform..."

# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install MySQL
sudo apt install mysql-server -y

# Install PHP
sudo apt install php libapache2-mod-php php-mysql -y

# Start services
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql

# Create project directory
sudo mkdir -p /var/www/html/sql-injection-training

echo "Basic setup complete!"
echo "Next steps:"
echo "1. Copy your project files to /var/www/html/sql-injection-training"
echo "2. Configure MySQL database"
echo "3. Update config.php"
echo "4. Run db_setup.php in browser"
```

Make it executable and run:
```bash
chmod +x setup.sh
./setup.sh
```

## Test Accounts

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Administrator |
| trainee | trainee123 | Trainee |
| user1 | password1 | Regular User |
| user2 | password2 | Regular User |

## Project Structure

```
sql-injection-training/
├── index.php                 # Landing page with disclaimer
├── config.php               # Database configuration
├── db_setup.php            # Database initialization script
├── login.php               # Vulnerable login page
├── search.php              # Vulnerable search functionality
├── profile.php             # Vulnerable profile viewer
├── logout.php              # Logout handler
├── admin/                  # Admin panel
│   ├── index.php          # Admin dashboard
│   ├── reset_db.php       # Database reset
│   └── view_logs.php      # Activity logs
├── challenges/             # Challenge pages
│   ├── index.php          # Challenge list
│   ├── challenge1.php     # Basic SQL injection
│   ├── challenge2.php     # UNION-based injection
│   ├── challenge3.php     # Blind SQL injection
│   ├── challenge4.php     # Time-based blind
│   └── challenge5.php     # Privilege escalation
├── hints/                  # Hint system
│   └── get_hint.php       # AJAX endpoint
├── includes/               # Core functionality
│   ├── db.php             # Database connection
│   ├── functions.php      # Utility functions
│   └── logger.php         # Activity logging
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       └── main.js        # Frontend JavaScript
└── docs/                   # Documentation
    ├── secure_examples.php # Secure code examples
    └── techniques.php      # SQL injection techniques
```

## Challenges

### Challenge 1: Authentication Bypass (Easy)
Bypass the login form using SQL injection to access any account without knowing the password.

### Challenge 2: Data Extraction (Medium)
Use UNION SELECT to extract hidden product information from the database.

### Challenge 3: Blind Injection (Medium)
Use boolean-based blind SQL injection to extract data when you can't see query results directly.

### Challenge 4: Time-Based Blind (Hard)
Extract data using time-based blind SQL injection with SLEEP() function.

### Challenge 5: Privilege Escalation (Hard)
Gain admin access by manipulating the is_admin flag through SQL injection.

## Security Warnings

⚠️ **CRITICAL WARNINGS:**

1. **DO NOT deploy this to a public server**
2. **DO NOT use this code in production**
3. **DO NOT connect to production databases**
4. **ONLY use in isolated training environments**
5. **All passwords are stored in plaintext (intentionally)**
6. **All SQL queries are vulnerable (intentionally)**

## Ethical Guidelines

By using this platform, you agree to:

- ✅ Use knowledge only in authorized environments
- ✅ Practice responsible disclosure
- ✅ Respect privacy and data protection laws
- ✅ Use skills to improve security
- ❌ Never attack systems without permission
- ❌ Never use for malicious purposes

**Unauthorized access to computer systems is illegal!**

## Learning Resources

- [OWASP SQL Injection Guide](https://owasp.org/www-community/attacks/SQL_Injection)
- [PortSwigger SQL Injection Tutorial](https://portswigger.net/web-security/sql-injection)
- [SQL Injection Cheat Sheet](https://portswigger.net/web-security/sql-injection/cheat-sheet)

## Troubleshooting

### Database Connection Failed
- Check MySQL is running
- Verify credentials in `config.php`
- Ensure MySQL user has proper permissions

### Tables Not Found
- Run `db_setup.php` to create tables
- Check database name is correct

### Session Issues
- Ensure PHP session support is enabled
- Check file permissions on session directory

## License

This project is for educational purposes only. Use at your own risk.

## Disclaimer

This software is provided "as is" without warranty of any kind. The authors are not responsible for any misuse or damage caused by this software.

---

**Remember: With great power comes great responsibility. Use your knowledge ethically!**
