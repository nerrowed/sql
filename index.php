<?php
/**
 * Landing Page with Disclaimer
 * INSIDER Training Platform
 */

require_once 'config.php';
require_once 'includes/functions.php';

displayHeader('Home');
?>

<div class="disclaimer-box">
    <h2>⚠️ HEY ORANG ORANG HITAM ⚠️</h2>
    <p><strong>This platform contains INTENTIONALLY VULNERABLE code for educational purposes.</strong></p>
    <p>This training environment is designed to teach SQL injection techniques in a safe, controlled setting.</p>
    <br>
    <p><strong style="color: #e74c3c;">DO NOT USE THIS CODE IN PRODUCTION ENVIRONMENTS!</strong></p>
    <p>All vulnerabilities are intentional and for learning purposes only.</p>
</div>

<div class="card">
    <h2>Welcome to INSIDER SQL Training Platform</h2>
    <p>This platform provides a hands-on environment to learn about SQL injection vulnerabilities and how to exploit them ethically.</p>
    
    <h3>What You'll Learn:</h3>
    <ul>
        <li>🔓 Authentication bypass using SQL injection</li>
        <li>📊 Data extraction with UNION-based injection</li>
        <li>🕵️ Boolean-based blind SQL injection</li>
        <li>⏱️ Time-based blind SQL injection</li>
        <li>👑 Privilege escalation attacks</li>
    </ul>
    
    <h3>Features:</h3>
    <ul>
        <li>5 progressive challenges with varying difficulty</li>
        <li>Hint system to guide your learning</li>
        <li>Real-time query display for educational feedback</li>
        <li>Detailed error messages that reveal database structure</li>
        <li>Progress tracking and completion certificates</li>
    </ul>
    
    <h3>GET STARTED:</h3>
    <ol>
        <li><a href="login.php">Login</a> with one of the test accounts</li>
        <li>Navigate to <a href="challenges/index.php">Challenges</a> to start learning</li>
        <li>Use the <a href="docs/techniques.php">SQL Injection Techniques</a> guide for reference</li>
    </ol>
    
    <h3>Test Accounts:</h3>
    <table>
        <tr>
            <th>Username</th>
            <th>Password</th>
            <th>Role</th>
        </tr>
        <tr>
            <td><code>admin</code></td>
            <td><code>admin123</code></td>
            <td>Administrator</td>
        </tr>
        <tr>
            <td><code>trainee</code></td>
            <td><code>trainee123</code></td>
            <td>Trainee</td>
        </tr>
        <tr>
            <td><code>user1</code></td>
            <td><code>password1</code></td>
            <td>Regular User</td>
        </tr>
        <tr>
            <td><code>user2</code></td>
            <td><code>password2</code></td>
            <td>Regular User</td>
        </tr>
    </table>
    
    <div style="margin-top: 30px; text-align: center;">
        <?php if (isLoggedIn()): ?>
            <a href="challenges/index.php" class="btn btn-primary">Go to Challenges</a>
            <a href="search.php" class="btn btn-success">Try Search Feature</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary">Login to Start</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2>⚖️ Ethical Guidelines</h2>
    <p><strong>This platform is for educational purposes only.</strong> By using this platform, you agree to:</p>
    <ul>
        <li>Use the knowledge gained only in authorized environments</li>
        <li>Never attempt SQL injection on systems you don't own or have explicit permission to test</li>
        <li>Practice responsible disclosure if you discover vulnerabilities</li>
        <li>Respect privacy and data protection laws</li>
        <li>Use your skills to improve security, not to cause harm</li>
    </ul>
</div>

<div class="card">
    <h2>📚 Resources</h2>
    <ul>
        <li><a href="docs/techniques.php">SQL Injection Techniques Guide</a></li>
        <li><a href="docs/secure_examples.php">Secure Code Examples</a></li>
        <li><a href="https://owasp.org/www-community/attacks/SQL_Injection" target="_blank">OWASP SQL Injection Guide</a></li>
        <li><a href="https://portswigger.net/web-security/sql-injection" target="_blank">PortSwigger SQL Injection Tutorial</a></li>
    </ul>
</div>

<?php displayFooter(); ?>
