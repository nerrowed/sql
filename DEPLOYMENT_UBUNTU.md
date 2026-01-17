# Deployment Guide - Ubuntu Server

Panduan lengkap untuk deploy SQL Injection Training Platform di Ubuntu Server.

## 📋 Prerequisites

- Ubuntu Server 20.04 LTS atau lebih baru
- Akses root atau sudo privileges
- Koneksi internet
- Minimal 1GB RAM
- Minimal 10GB disk space

## 🚀 Step-by-Step Installation

### Step 1: Update System

```bash
sudo apt update
sudo apt upgrade -y
```

### Step 2: Install Apache Web Server

```bash
# Install Apache
sudo apt install apache2 -y

# Start dan enable Apache
sudo systemctl start apache2
sudo systemctl enable apache2

# Verify Apache is running
sudo systemctl status apache2

# Test di browser: http://your-server-ip
# Seharusnya muncul "Apache2 Ubuntu Default Page"
```

### Step 3: Install MySQL Server

```bash
# Install MySQL
sudo apt install mysql-server -y

# Start dan enable MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Verify MySQL is running
sudo systemctl status mysql

# Secure MySQL installation (RECOMMENDED)
sudo mysql_secure_installation
```

**MySQL Secure Installation Prompts:**
- Set root password: YES (pilih password yang kuat)
- Remove anonymous users: YES
- Disallow root login remotely: YES
- Remove test database: YES
- Reload privilege tables: YES

### Step 4: Install PHP and Extensions

```bash
# Install PHP dan extensions yang dibutuhkan
sudo apt install php libapache2-mod-php php-mysql php-cli php-common php-mbstring -y

# Verify PHP installation
php -v

# Restart Apache untuk load PHP module
sudo systemctl restart apache2
```

**Test PHP:**
```bash
# Create test file
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php

# Akses di browser: http://your-server-ip/info.php
# Seharusnya muncul PHP info page

# Hapus file test (untuk keamanan)
sudo rm /var/www/html/info.php
```

### Step 5: Configure MySQL Database

```bash
# Login ke MySQL sebagai root
sudo mysql -u root -p
```

**Di MySQL prompt, jalankan:**
```sql
-- Create database
CREATE DATABASE sqli_training_db;

-- Create user dengan password
CREATE USER 'sqli_training'@'localhost' IDENTIFIED BY 'training_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON sqli_training_db.* TO 'sqli_training'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
SELECT user, host FROM mysql.user WHERE user = 'sqli_training';

-- Exit
EXIT;
```

**Test koneksi:**
```bash
mysql -u sqli_training -p sqli_training_db
# Masukkan password: training_password
# Jika berhasil login, berarti konfigurasi sudah benar
```

### Step 6: Deploy Application Files

#### Option A: Manual Upload

```bash
# Navigate to web root
cd /var/www/html

# Create project directory
sudo mkdir sql-injection-training

# Upload files menggunakan SCP dari komputer lokal:
# scp -r /path/to/local/files/* user@server-ip:/var/www/html/sql-injection-training/

# Atau gunakan SFTP client seperti FileZilla
```

#### Option B: Using Git (Recommended)

```bash
cd /var/www/html

# Clone repository
sudo git clone <your-repository-url> sql-injection-training

# Atau jika sudah ada folder, pull latest changes
cd sql-injection-training
sudo git pull
```

#### Set Proper Permissions

```bash
# Set ownership ke www-data (Apache user)
sudo chown -R www-data:www-data /var/www/html/sql-injection-training

# Set directory permissions
sudo find /var/www/html/sql-injection-training -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/html/sql-injection-training -type f -exec chmod 644 {} \;

# Verify permissions
ls -la /var/www/html/sql-injection-training
```

### Step 7: Configure Application

```bash
# Edit config.php
sudo nano /var/www/html/sql-injection-training/config.php
```

**Update these values:**
```php
// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USER', 'sqli_training');
define('DB_PASS', 'training_password');  // Ganti dengan password yang kamu set
define('DB_NAME', 'sqli_training_db');

// Application settings
define('APP_NAME', 'SQL Injection Training Platform');
define('BASE_URL', 'http://YOUR_SERVER_IP/sql-injection-training');  // Ganti dengan IP server kamu
```

**Save and exit:** `Ctrl+X`, `Y`, `Enter`

### Step 8: Initialize Database

**Open browser dan navigate to:**
```
http://YOUR_SERVER_IP/sql-injection-training/db_setup.php
```

Kamu akan melihat:
- ✓ Database created or already exists
- ✓ Table 'users' created successfully
- ✓ Table 'products' created successfully
- ✓ Table 'challenges' created successfully
- ✓ Sample users inserted
- ✓ Sample products inserted
- ✓ Challenges inserted
- ✓ Hints inserted

**✅ Database setup completed successfully!**

### Step 9: Test Installation

**Navigate to:**
```
http://YOUR_SERVER_IP/sql-injection-training/
```

**Login dengan test accounts:**
- Username: `admin` / Password: `admin123`
- Username: `trainee` / Password: `trainee123`

## 🔧 Optional: Configure Virtual Host

Untuk setup domain atau subdomain:

```bash
# Create virtual host config
sudo nano /etc/apache2/sites-available/sqli-training.conf
```

**Add this configuration:**
```apache
<VirtualHost *:80>
    ServerAdmin admin@localhost
    ServerName training.yourdomain.com
    DocumentRoot /var/www/html/sql-injection-training

    <Directory /var/www/html/sql-injection-training>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sqli-training-error.log
    CustomLog ${APACHE_LOG_DIR}/sqli-training-access.log combined
</VirtualHost>
```

**Enable site and reload Apache:**
```bash
sudo a2ensite sqli-training.conf
sudo systemctl reload apache2
```

**Update DNS:**
- Point `training.yourdomain.com` to your server IP

## 🔒 Security Configuration

### 1. Firewall Setup (UFW)

```bash
# Install UFW if not installed
sudo apt install ufw -y

# Default policies
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH (IMPORTANT! Jangan sampai terkunci)
sudo ufw allow 22/tcp

# Allow HTTP
sudo ufw allow 80/tcp

# Allow HTTPS (jika menggunakan SSL)
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status verbose
```

### 2. Restrict Access by IP (RECOMMENDED)

```bash
# Hanya allow akses dari IP tertentu
sudo ufw delete allow 80/tcp
sudo ufw allow from 192.168.1.0/24 to any port 80

# Atau allow dari IP spesifik
sudo ufw allow from 203.0.113.10 to any port 80
```

### 3. Disable Directory Listing

```bash
sudo nano /etc/apache2/apache2.conf
```

Find and change:
```apache
<Directory /var/www/>
    Options -Indexes FollowSymLinks  # Add minus sign before Indexes
    AllowOverride None
    Require all granted
</Directory>
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

### 4. Setup Fail2Ban (Optional)

```bash
# Install Fail2Ban
sudo apt install fail2ban -y

# Start and enable
sudo systemctl start fail2ban
sudo systemctl enable fail2ban

# Check status
sudo fail2ban-client status
```

## 📊 Monitoring & Logs

### Apache Logs

```bash
# Access logs
sudo tail -f /var/log/apache2/access.log

# Error logs
sudo tail -f /var/log/apache2/error.log

# Specific site logs (if using virtual host)
sudo tail -f /var/log/apache2/sqli-training-access.log
sudo tail -f /var/log/apache2/sqli-training-error.log
```

### MySQL Logs

```bash
# Error logs
sudo tail -f /var/log/mysql/error.log

# Enable general query log (for debugging)
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Add these lines:
# general_log = 1
# general_log_file = /var/log/mysql/mysql.log

# Restart MySQL
sudo systemctl restart mysql

# View query log
sudo tail -f /var/log/mysql/mysql.log
```

### System Resources

```bash
# Check disk space
df -h

# Check memory usage
free -h

# Check CPU usage
top

# Check Apache processes
ps aux | grep apache2

# Check MySQL processes
ps aux | grep mysql
```

## 🐛 Troubleshooting

### Issue: "403 Forbidden" Error

**Solution:**
```bash
# Check permissions
ls -la /var/www/html/sql-injection-training

# Fix permissions
sudo chown -R www-data:www-data /var/www/html/sql-injection-training
sudo chmod -R 755 /var/www/html/sql-injection-training
```

### Issue: "Connection refused" to MySQL

**Solution:**
```bash
# Check MySQL is running
sudo systemctl status mysql

# Start MySQL if stopped
sudo systemctl start mysql

# Check MySQL is listening
sudo netstat -tlnp | grep mysql

# Test connection
mysql -u sqli_training -p sqli_training_db
```

### Issue: PHP Not Working (Shows PHP Code)

**Solution:**
```bash
# Check PHP module is enabled
sudo a2enmod php7.4  # or php8.0, php8.1

# Restart Apache
sudo systemctl restart apache2

# Verify PHP is installed
php -v
```

### Issue: "Session Error" or "Headers Already Sent"

**Solution:**
```bash
# Fix session directory permissions
sudo chmod 777 /var/lib/php/sessions

# Or create custom session directory
sudo mkdir -p /var/www/sessions
sudo chown www-data:www-data /var/www/sessions
sudo chmod 700 /var/www/sessions
```

### Issue: Can't Access from External Network

**Solution:**
```bash
# Check firewall
sudo ufw status

# Check Apache is listening on all interfaces
sudo netstat -tlnp | grep :80

# Check Apache config
sudo nano /etc/apache2/ports.conf
# Should have: Listen 80

# Restart Apache
sudo systemctl restart apache2
```

## 🔄 Maintenance

### Backup Database

```bash
# Create backup directory
mkdir -p ~/backups

# Backup database
mysqldump -u sqli_training -p sqli_training_db > ~/backups/sqli_training_$(date +%Y%m%d).sql

# Compress backup
gzip ~/backups/sqli_training_$(date +%Y%m%d).sql
```

### Restore Database

```bash
# Restore from backup
mysql -u sqli_training -p sqli_training_db < ~/backups/sqli_training_20260117.sql
```

### Update Application

```bash
cd /var/www/html/sql-injection-training

# Pull latest changes (if using Git)
sudo git pull

# Or upload new files via SCP/SFTP

# Fix permissions after update
sudo chown -R www-data:www-data /var/www/html/sql-injection-training
```

### Restart Services

```bash
# Restart Apache
sudo systemctl restart apache2

# Restart MySQL
sudo systemctl restart mysql

# Restart both
sudo systemctl restart apache2 mysql
```

## ⚠️ Important Security Warnings

1. **NEVER expose this to public internet**
   - Use VPN or private network only
   - Restrict access by IP address
   - This platform is INTENTIONALLY VULNERABLE

2. **Isolate from production systems**
   - Use separate server
   - Separate network segment
   - No connection to production databases

3. **Monitor access logs**
   - Regularly check who's accessing
   - Look for suspicious activities
   - Keep logs for audit trail

4. **Regular backups**
   - Backup database daily
   - Backup application files
   - Test restore procedures

## 📞 Support

Jika ada masalah:
1. Check logs: `/var/log/apache2/` dan `/var/log/mysql/`
2. Verify services: `sudo systemctl status apache2 mysql`
3. Check permissions: `ls -la /var/www/html/sql-injection-training`
4. Test database connection: `mysql -u sqli_training -p`

## ✅ Post-Installation Checklist

- [ ] Apache installed and running
- [ ] MySQL installed and running
- [ ] PHP installed and working
- [ ] Database created and configured
- [ ] Application files deployed
- [ ] Permissions set correctly
- [ ] config.php updated
- [ ] db_setup.php executed successfully
- [ ] Can access homepage
- [ ] Can login with test accounts
- [ ] Firewall configured
- [ ] Access restricted (if needed)
- [ ] Logs are accessible
- [ ] Backup strategy in place

---

**Platform siap digunakan untuk training! 🎓**
