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

### Setup Steps

1. **Clone or download this repository**
   ```bash
   git clone <repository-url>
   cd sql-injection-training
   ```

2. **Configure database connection**
   - Edit `config.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_mysql_user');
   define('DB_PASS', 'your_mysql_password');
   define('DB_NAME', 'sqli_training_db');
   ```

3. **Create MySQL user** (if needed)
   ```sql
   CREATE USER 'sqli_training'@'localhost' IDENTIFIED BY 'training_password';
   GRANT ALL PRIVILEGES ON sqli_training_db.* TO 'sqli_training'@'localhost';
   FLUSH PRIVILEGES;
   ```

4. **Setup database**
   - Navigate to `http://localhost/sql-injection-training/db_setup.php`
   - This will create all tables and insert sample data

5. **Start training!**
   - Go to `http://localhost/sql-injection-training/`
   - Login with test accounts (see below)

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
