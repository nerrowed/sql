# Design Document: INSIDER Training Platform

## Overview

Platform training SQL injection berbasis web menggunakan PHP dan MySQL yang sengaja dibuat vulnerable untuk tujuan edukasi. Platform ini menyediakan environment yang aman dan terkontrol untuk mempelajari berbagai teknik SQL injection, mulai dari authentication bypass hingga data extraction dan privilege escalation.

**Technology Stack:**
- **Backend**: PHP 7.4+ (vanilla PHP, no framework untuk kesederhanaan)
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Web Server**: Apache dengan mod_php

**Key Design Principles:**
- Intentionally vulnerable untuk educational purposes
- Clear separation antara vulnerable dan secure code examples
- Comprehensive logging untuk instructor monitoring
- Progressive difficulty dalam challenges
- Educational feedback pada setiap interaction

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Web Browser                          │
│                    (Trainee Interface)                      │
└────────────────────────┬────────────────────────────────────┘
                         │ HTTP/HTTPS
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                     Apache Web Server                       │
│                         (mod_php)                           │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                    PHP Application Layer                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Vulnerable │  │   Challenge  │  │     Admin    │     │
│  │   Endpoints  │  │    System    │  │   Dashboard  │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │     Auth     │  │     Hint     │  │   Logging    │     │
│  │    System    │  │    System    │  │    System    │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└────────────────────────┬────────────────────────────────────┘
                         │ mysqli_*
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                      MySQL Database                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │  users   │  │ products │  │challenges│  │   logs   │  │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### Directory Structure

```
sql-injection-training/
├── index.php                 # Landing page dengan disclaimer
├── config.php               # Database configuration
├── db_setup.php            # Database initialization script
├── login.php               # Vulnerable login page
├── search.php              # Vulnerable search functionality
├── profile.php             # Vulnerable profile viewer
├── admin/
│   ├── index.php          # Admin dashboard
│   ├── reset_db.php       # Database reset functionality
│   └── view_logs.php      # Activity logs viewer
├── challenges/
│   ├── index.php          # Challenge list
│   ├── challenge1.php     # Basic SQL injection
│   ├── challenge2.php     # UNION-based injection
│   ├── challenge3.php     # Blind SQL injection
│   ├── challenge4.php     # Time-based blind injection
│   └── challenge5.php     # Privilege escalation
├── hints/
│   └── get_hint.php       # AJAX endpoint for hints
├── includes/
│   ├── db.php             # Database connection
│   ├── functions.php      # Utility functions
│   └── logger.php         # Logging functions
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       └── main.js        # Frontend JavaScript
└── docs/
    ├── secure_examples.php # Secure code examples
    └── techniques.php      # SQL injection techniques guide
```

## Components and Interfaces

### 1. Database Connection Component (`includes/db.php`)

**Purpose**: Establish dan manage koneksi ke MySQL database

**Interface**:
```php
function getConnection(): mysqli
function closeConnection(mysqli $conn): void
function executeQuery(string $query): mysqli_result|bool
function getLastError(): string
```

**Implementation Notes**:
- Menggunakan mysqli extension
- Connection pooling tidak diimplementasikan (single connection per request)
- Error reporting di-enable untuk educational purposes

### 2. Vulnerable Authentication Component (`login.php`)

**Purpose**: Menyediakan login form yang vulnerable terhadap SQL injection

**Vulnerable Query Pattern**:
```php
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
```

**Interface**:
```php
function authenticateUser(string $username, string $password): array|false
function displayQuery(string $query): void
function showLoginForm(string $error = ''): void
```

**Vulnerability Details**:
- Direct string concatenation tanpa escaping
- No prepared statements
- Plaintext password comparison
- SQL query ditampilkan di page untuk educational purposes

### 3. Vulnerable Search Component (`search.php`)

**Purpose**: Product search dengan UNION-based SQL injection vulnerability

**Vulnerable Query Pattern**:
```php
$query = "SELECT id, name, description, price FROM products WHERE name LIKE '%$search%'";
```

**Interface**:
```php
function searchProducts(string $searchTerm): array
function displayResults(array $results): void
function showSearchForm(): void
function displayExecutedQuery(string $query): void
```

**Vulnerability Details**:
- LIKE clause vulnerable to injection
- Allows UNION SELECT untuk data extraction
- Returns all columns dari query result
- Error messages expose database structure

### 4. Vulnerable Profile Component (`profile.php`)

**Purpose**: User profile viewer dengan GET parameter injection

**Vulnerable Query Pattern**:
```php
$query = "SELECT * FROM users WHERE id = $user_id";
```

**Interface**:
```php
function getUserProfile(int $userId): array|false
function displayProfile(array $userData): void
function showError(string $message): void
```

**Vulnerability Details**:
- Direct GET parameter usage tanpa validation
- Integer type casting tidak dilakukan
- Boolean-based blind injection possible
- Detailed error messages

### 5. Challenge System Component (`challenges/`)

**Purpose**: Manage progressive SQL injection challenges

**Interface**:
```php
function getChallengeDetails(int $challengeId): array
function checkSolution(int $challengeId, string $payload): bool
function markChallengeComplete(int $userId, int $challengeId): void
function getUserProgress(int $userId): array
```

**Challenge Structure**:
```php
[
    'id' => 1,
    'title' => 'Basic SQL Injection',
    'description' => 'Bypass login using SQL injection',
    'difficulty' => 'Easy',
    'target_url' => 'login.php',
    'flag' => 'FLAG{basic_sqli_bypass}',
    'hints' => [
        'Try using OR 1=1',
        'Comment out the rest of the query with --',
        'Full payload: admin\' OR \'1\'=\'1\' --'
    ]
]
```

### 6. Hint System Component (`hints/get_hint.php`)

**Purpose**: Provide progressive hints untuk challenges

**Interface**:
```php
function getHint(int $challengeId, int $hintLevel): string
function recordHintUsage(int $userId, int $challengeId, int $hintLevel): void
function getHintCount(int $userId, int $challengeId): int
```

**Hint Levels**:
- Level 1: General direction (e.g., "Look at the login form")
- Level 2: Specific technique (e.g., "Try using OR operator")
- Level 3: Near-complete solution (e.g., "Use: admin' OR '1'='1")
- Level 4: Complete solution with explanation

### 7. Logging Component (`includes/logger.php`)

**Purpose**: Track semua SQL injection attempts dan user activities

**Interface**:
```php
function logActivity(int $userId, string $action, string $details): void
function logSQLInjection(int $userId, string $query, bool $success): void
function getActivityLogs(int $limit = 100): array
function getUserLogs(int $userId): array
```

**Log Structure**:
```php
[
    'timestamp' => '2026-01-17 10:30:45',
    'user_id' => 5,
    'username' => 'trainee01',
    'action' => 'SQL_INJECTION_ATTEMPT',
    'query' => "SELECT * FROM users WHERE username = 'admin' OR '1'='1' --",
    'success' => true,
    'ip_address' => '192.168.1.100'
]
```

### 8. Admin Dashboard Component (`admin/index.php`)

**Purpose**: Monitor trainee progress dan activities

**Interface**:
```php
function getTraineeStats(): array
function getChallengeCompletionRates(): array
function getRecentActivities(int $limit = 50): array
function generateProgressReport(int $userId): array
```

**Dashboard Metrics**:
- Total trainees
- Challenge completion rates
- Most common successful payloads
- Most common mistakes
- Average time per challenge

### 9. Database Reset Component (`admin/reset_db.php`)

**Purpose**: Reset database ke initial state

**Interface**:
```php
function resetDatabase(): bool
function createTables(): bool
function insertSampleData(): bool
function preserveUserProgress(): bool
```

**Reset Process**:
1. Backup user progress data
2. Drop all tables
3. Recreate table structure
4. Insert sample data
5. Restore user progress

## Data Models

### Users Table

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Plaintext untuk demo
    email VARCHAR(100),
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);
```

**Sample Data**:
```sql
INSERT INTO users (username, password, email, is_admin) VALUES
('admin', 'admin123', 'admin@training.local', 1),
('user1', 'password1', 'user1@training.local', 0),
('user2', 'password2', 'user2@training.local', 0),
('trainee', 'trainee123', 'trainee@training.local', 0);
```

### Products Table

```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2),
    category VARCHAR(50),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Sample Data**:
```sql
INSERT INTO products (name, description, price, category, stock) VALUES
('Laptop', 'High-performance laptop', 15000000, 'Electronics', 10),
('Mouse', 'Wireless mouse', 250000, 'Electronics', 50),
('Keyboard', 'Mechanical keyboard', 750000, 'Electronics', 30),
('Monitor', '27-inch 4K monitor', 5000000, 'Electronics', 15),
('SECRET_PRODUCT', 'Hidden product with flag: FLAG{union_select_master}', 99999999, 'Hidden', 0);
```

### Challenges Table

```sql
CREATE TABLE challenges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty ENUM('Easy', 'Medium', 'Hard') DEFAULT 'Easy',
    target_page VARCHAR(100),
    flag VARCHAR(100),
    points INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Sample Data**:
```sql
INSERT INTO challenges (title, description, difficulty, target_page, flag, points) VALUES
('Authentication Bypass', 'Bypass the login form using SQL injection', 'Easy', 'login.php', 'FLAG{auth_bypass_101}', 10),
('Data Extraction', 'Extract hidden product information using UNION SELECT', 'Medium', 'search.php', 'FLAG{union_select_master}', 20),
('Blind Injection', 'Use boolean-based blind SQL injection on profile page', 'Medium', 'profile.php', 'FLAG{blind_sqli_ninja}', 25),
('Time-Based Blind', 'Extract data using time-based blind SQL injection', 'Hard', 'profile.php', 'FLAG{time_based_expert}', 30),
('Privilege Escalation', 'Gain admin access through SQL injection', 'Hard', 'login.php', 'FLAG{admin_privilege_pwned}', 35);
```

### User Progress Table

```sql
CREATE TABLE user_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    challenge_id INT NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    completed_at TIMESTAMP NULL,
    hints_used INT DEFAULT 0,
    attempts INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (challenge_id) REFERENCES challenges(id),
    UNIQUE KEY unique_progress (user_id, challenge_id)
);
```

### Activity Logs Table

```sql
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    username VARCHAR(50),
    action VARCHAR(100),
    query_executed TEXT,
    success TINYINT(1),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Hints Table

```sql
CREATE TABLE hints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    challenge_id INT NOT NULL,
    hint_level INT NOT NULL,
    hint_text TEXT NOT NULL,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id)
);
```

**Sample Data**:
```sql
INSERT INTO hints (challenge_id, hint_level, hint_text) VALUES
(1, 1, 'Look at how the login form processes your input. What happens if you add special characters?'),
(1, 2, 'Try using the OR operator to make the WHERE clause always true'),
(1, 3, 'Use SQL comments (--) to ignore the password check'),
(1, 4, 'Complete payload: admin\' OR \'1\'=\'1\' -- '),
(2, 1, 'The search function returns multiple columns. Can you add more columns to the result?'),
(2, 2, 'Use UNION SELECT to combine results from different tables'),
(2, 3, 'Find out how many columns are in the original query using ORDER BY'),
(2, 4, 'Payload: \' UNION SELECT id, name, description, price FROM products WHERE category=\'Hidden\' -- ');
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Core Vulnerability Properties

**Property 1: No Input Sanitization**
*For any* user input submitted to login, search, or profile endpoints, the input should appear unescaped and unsanitized in the constructed SQL query.
**Validates: Requirements 1.1, 2.1, 3.1**

**Property 2: SQL Injection Payload Execution**
*For any* SQL injection payload submitted through any vulnerable endpoint, the platform should execute the payload against the database and return results or modify database state accordingly.
**Validates: Requirements 1.2, 2.3, 3.2**

**Property 3: Authentication Bypass**
*For any* SQL injection payload that creates a true condition (e.g., OR 1=1), submitting it through the login form should grant access without requiring valid credentials.
**Validates: Requirements 1.3**

**Property 4: Plaintext Password Storage**
*For any* user account created in the system, the password stored in the database should be identical to the plaintext password provided during registration.
**Validates: Requirements 1.5**

**Property 5: Query Visibility**
*For any* SQL query executed by the platform, the complete query string should be displayed in the HTML output for educational purposes.
**Validates: Requirements 1.4, 2.2, 7.1**

**Property 6: UNION SELECT Support**
*For any* valid UNION SELECT payload submitted through the search functionality, the platform should successfully combine results from multiple tables and display all columns.
**Validates: Requirements 2.5**

**Property 7: All Columns Displayed**
*For any* SQL query result, all columns returned by the query should be visible in the HTML output, including hidden or sensitive data.
**Validates: Requirements 2.4, 3.3**

**Property 8: Detailed Error Messages**
*For any* SQL query that causes an error, the error message displayed should contain database structure information such as table names, column names, or syntax details.
**Validates: Requirements 3.4, 7.2**

**Property 9: Boolean-Based Blind Injection**
*For any* boolean condition injected through the profile endpoint, the response should differ based on whether the condition evaluates to true or false, enabling blind SQL injection.
**Validates: Requirements 3.5**

**Property 10: Privilege Escalation via SQL Injection**
*For any* SQL injection payload that modifies the is_admin flag in the database, the user should gain access to admin panel functionality.
**Validates: Requirements 4.1, 4.3**

**Property 11: Admin Access Logging**
*For any* attempt to access admin functionality, an entry should be created in the activity_logs table with the user ID, action, and timestamp.
**Validates: Requirements 4.5**

### Challenge System Properties

**Property 12: Challenge Completion Tracking**
*For any* challenge successfully completed by a trainee, the user_progress table should be updated with completed=1 and the completion timestamp, and the flag should be displayed to the user.
**Validates: Requirements 5.3, 5.4**

**Property 13: Challenge Details Display**
*For any* challenge accessed by a trainee, the output should contain the challenge objective, target vulnerability description, and difficulty level.
**Validates: Requirements 5.2**

**Property 14: Progressive Hints**
*For any* challenge, requesting hints at increasing levels should provide progressively more detailed information, with early hints not containing the complete solution.
**Validates: Requirements 6.1**

**Property 15: Minimum Hint Count**
*For any* challenge in the system, there should be at least 3 hint entries in the hints table for that challenge.
**Validates: Requirements 6.2**

**Property 16: Hint Usage Tracking**
*For any* hint viewed by a trainee, the hints_used counter in the user_progress table should increment by 1.
**Validates: Requirements 6.3**

**Property 17: Educational Hint Content**
*For any* hint retrieved from the system, the hint text should contain SQL injection related keywords such as "OR", "UNION", "SELECT", "--", or specific payload examples.
**Validates: Requirements 6.4**

### Educational Feedback Properties

**Property 18: Success Explanation**
*For any* successful SQL injection attempt, the platform should display explanatory text describing why the injection worked and what technique was used.
**Validates: Requirements 7.3**

**Property 19: Vulnerable Code Display**
*For any* challenge page, the HTML output should contain the vulnerable PHP code snippet that creates the SQL injection vulnerability.
**Validates: Requirements 7.4**

**Property 20: Documentation Links**
*For any* educational feedback displayed, the output should contain at least one hyperlink to documentation about SQL injection techniques.
**Validates: Requirements 7.5**

### Admin Dashboard Properties

**Property 21: Challenge Completion Rates**
*For any* challenge in the system, the admin dashboard should calculate and display the completion rate as (completed_count / total_trainees) * 100.
**Validates: Requirements 8.2**

**Property 22: Trainee Activity Logs**
*For any* trainee selected in the admin dashboard, all SQL injection attempts made by that trainee should be retrieved from activity_logs and displayed.
**Validates: Requirements 8.3**

**Property 23: Progress Reset**
*For any* trainee whose progress is reset by an admin, all entries in user_progress for that trainee should be deleted or set to completed=0.
**Validates: Requirements 8.4**

### Database Management Properties

**Property 24: Database Reset Completeness**
*For any* database reset operation, all tables (users, products, challenges, hints) should be recreated with the exact sample data defined in the schema, while preserving user_progress records.
**Validates: Requirements 9.2, 9.3**

### Security Warning Properties

**Property 25: Production Warning Display**
*For any* page that displays vulnerable code, the HTML output should contain a warning message stating the code should never be used in production environments.
**Validates: Requirements 10.2**

**Property 26: Vulnerability Explanation**
*For any* vulnerable code snippet displayed, the output should include both an explanation of why it's vulnerable and a secure code alternative.
**Validates: Requirements 10.3, 10.4**

## Error Handling

### Intentional Error Exposure

**For Educational Purposes:**
- All MySQL errors MUST be displayed to users with full detail
- Error messages MUST include table names, column names, and query syntax
- No error suppression or generic error messages
- Stack traces should be visible when available

**Error Display Format:**
```php
function displayError($error) {
    echo "<div class='error-box'>";
    echo "<h3>SQL Error (Educational)</h3>";
    echo "<p><strong>Error Message:</strong> " . htmlspecialchars($error) . "</p>";
    echo "<p><strong>Why this is helpful:</strong> Error messages reveal database structure</p>";
    echo "</div>";
}
```

### Logging Errors

All errors should be logged to activity_logs:
```php
logActivity($user_id, 'SQL_ERROR', $error_message);
```

### Error Types to Expose

1. **Syntax Errors**: Show exact position of syntax error
2. **Table/Column Not Found**: Reveal database schema information
3. **Type Mismatch**: Show expected vs actual data types
4. **Constraint Violations**: Display foreign key and unique constraints

### Safe Error Handling (For Platform Stability)

While SQL errors should be exposed, PHP errors that could crash the platform should be handled:

```php
try {
    // Database operations
} catch (Exception $e) {
    // Log for admin
    error_log($e->getMessage());
    // Display for trainee (educational)
    displayError($e->getMessage());
}
```

## Testing Strategy

### Dual Testing Approach

This platform requires both unit tests and property-based tests to ensure correctness:

**Unit Tests**: Verify specific examples, edge cases, and error conditions
- Test specific SQL injection payloads work correctly
- Test admin functionality with known inputs
- Test database reset with specific data states
- Test hint system with known challenge IDs

**Property Tests**: Verify universal properties across all inputs
- Test that ANY input remains unsanitized
- Test that ANY SQL injection payload executes
- Test that ANY challenge tracks completion correctly
- Test that ANY error exposes database information

Both testing approaches are complementary and necessary for comprehensive coverage.

### Property-Based Testing Configuration

**Testing Library**: We will use PHPUnit with faker/faker for property-based testing simulation

**Test Configuration**:
- Minimum 100 iterations per property test
- Each property test must reference its design document property
- Tag format: `@group Feature: sql-injection-training, Property {number}: {property_text}`

**Example Property Test Structure**:
```php
/**
 * @group Feature: sql-injection-training, Property 1: No Input Sanitization
 * @test
 */
public function test_no_input_sanitization_across_endpoints() {
    $faker = Faker\Factory::create();
    
    for ($i = 0; $i < 100; $i++) {
        // Generate random input with SQL special characters
        $input = $faker->randomElement([
            "' OR '1'='1",
            "admin'--",
            "'; DROP TABLE users--",
            $faker->word . "' OR 1=1--"
        ]);
        
        // Test login endpoint
        $query = buildLoginQuery($input, 'password');
        $this->assertStringContainsString($input, $query);
        $this->assertStringNotContainsString('mysqli_real_escape_string', $query);
        
        // Test search endpoint
        $query = buildSearchQuery($input);
        $this->assertStringContainsString($input, $query);
        
        // Test profile endpoint
        $query = buildProfileQuery($input);
        $this->assertStringContainsString($input, $query);
    }
}
```

### Unit Testing Strategy

**Test Coverage Areas**:

1. **Authentication Tests**
   - Test login with valid credentials
   - Test login with SQL injection bypass
   - Test login with various injection payloads
   - Test query display in output

2. **Search Functionality Tests**
   - Test basic search with normal input
   - Test UNION SELECT injection
   - Test ORDER BY column enumeration
   - Test hidden data extraction

3. **Profile Page Tests**
   - Test profile retrieval with valid ID
   - Test boolean-based blind injection
   - Test error-based injection
   - Test column enumeration

4. **Challenge System Tests**
   - Test challenge completion marking
   - Test progress tracking
   - Test flag display
   - Test completion certificate

5. **Hint System Tests**
   - Test hint retrieval by level
   - Test hint usage tracking
   - Test progressive hint content
   - Test solution display after max hints

6. **Admin Dashboard Tests**
   - Test trainee statistics calculation
   - Test activity log retrieval
   - Test progress reset
   - Test report generation

7. **Database Reset Tests**
   - Test table recreation
   - Test sample data insertion
   - Test progress preservation
   - Test reset completion time

### Integration Testing

**End-to-End Scenarios**:

1. **Complete Challenge Flow**
   - Register trainee → Access challenge → Request hints → Submit solution → Verify completion

2. **Admin Monitoring Flow**
   - Trainee attempts injection → Admin views logs → Admin checks progress → Admin generates report

3. **Database Reset Flow**
   - Create progress → Reset database → Verify data restored → Verify progress preserved

### Testing Environment

**Database Setup**:
- Use separate test database
- Reset database before each test suite
- Use transactions for test isolation where possible

**Test Data**:
- Predefined test users (trainee, admin)
- Sample challenges with known flags
- Known SQL injection payloads
- Expected error messages

### Manual Testing Checklist

While automated tests cover functionality, manual testing should verify:

- [ ] UI displays correctly in different browsers
- [ ] Educational feedback is clear and helpful
- [ ] Vulnerable code examples are properly highlighted
- [ ] Disclaimer and warnings are prominent
- [ ] Admin dashboard is intuitive
- [ ] Challenge difficulty progression makes sense
- [ ] Hints are actually helpful
- [ ] Error messages are educational but not overwhelming

### Security Testing (Ironic but Important)

Even though this is intentionally vulnerable, we should test:

- [ ] Platform doesn't expose real production data
- [ ] Admin panel requires actual authentication (not vulnerable)
- [ ] Database reset doesn't affect other systems
- [ ] Logging doesn't expose sensitive instructor data
- [ ] Platform is isolated from production networks

## Implementation Notes

### PHP Version Requirements

- PHP 7.4 or higher
- mysqli extension enabled
- error_reporting set to E_ALL for educational purposes
- display_errors = On (for training environment only)

### Database Configuration

**Connection Settings**:
```php
$host = 'localhost';
$username = 'sqli_training';
$password = 'training_password';
$database = 'sqli_training_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
```

### Session Management

```php
session_start();

// Store user info in session after login
$_SESSION['user_id'] = $user_id;
$_SESSION['username'] = $username;
$_SESSION['is_admin'] = $is_admin;

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}
```

### Logging Implementation

```php
function logActivity($user_id, $action, $details) {
    global $conn;
    
    $username = $_SESSION['username'] ?? 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, username, action, query_executed, ip_address, user_agent) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isssss", $user_id, $username, $action, $details, $ip, $user_agent);
    $stmt->execute();
}
```

Note: Logging uses prepared statements because it's not part of the vulnerable training functionality.

### Query Display Helper

```php
function displayQuery($query) {
    echo "<div class='query-display'>";
    echo "<h4>Executed SQL Query:</h4>";
    echo "<pre>" . htmlspecialchars($query) . "</pre>";
    echo "<p class='hint'>Notice how your input is directly inserted into the query!</p>";
    echo "</div>";
}
```

### Vulnerable Query Patterns (Examples)

**Login (Vulnerable)**:
```php
$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $conn->query($query);

displayQuery($query); // Show for educational purposes
```

**Search (Vulnerable)**:
```php
$search = $_GET['search'];

$query = "SELECT id, name, description, price FROM products WHERE name LIKE '%$search%'";
$result = $conn->query($query);

displayQuery($query);
```

**Profile (Vulnerable)**:
```php
$user_id = $_GET['id'];

$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);

displayQuery($query);
```

### Secure Code Examples (For Educational Comparison)

**Login (Secure)**:
```php
$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();
```

**Search (Secure)**:
```php
$search = $_GET['search'];

$stmt = $conn->prepare("SELECT id, name, description, price FROM products WHERE name LIKE ?");
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();
```

### Frontend Styling Guidelines

**CSS Framework**: None (vanilla CSS untuk kesederhanaan)

**Color Scheme**:
- Primary: #2c3e50 (dark blue)
- Danger: #e74c3c (red for warnings)
- Success: #27ae60 (green for completed challenges)
- Warning: #f39c12 (orange for hints)
- Background: #ecf0f1 (light gray)

**Key UI Elements**:
- Prominent disclaimer banner on every page
- Query display boxes with syntax highlighting
- Challenge cards with difficulty badges
- Progress indicators
- Hint accordion panels
- Admin dashboard with statistics cards

### Deployment Considerations

**Environment Isolation**:
- MUST be deployed on isolated network
- MUST NOT be accessible from public internet without VPN
- MUST have clear "TRAINING ENVIRONMENT" labels
- MUST use separate database server from production

**Access Control**:
- Instructor/admin accounts use strong authentication
- Trainee accounts can be simple (part of training)
- IP whitelisting recommended
- Session timeout after 2 hours of inactivity

**Monitoring**:
- All activities logged to database
- Daily backup of activity logs
- Alert on unusual patterns (e.g., actual DROP TABLE attempts)
- Monitor for attempts to escape training environment

## Future Enhancements

Potential additions for future versions:

1. **Additional Challenge Types**
   - Time-based blind SQL injection
   - Second-order SQL injection
   - NoSQL injection examples
   - ORM injection examples

2. **Gamification**
   - Leaderboard based on completion time
   - Points system with badges
   - Team competitions
   - Timed challenges

3. **Advanced Features**
   - Automated payload testing
   - Custom challenge creation by instructors
   - Video tutorials integration
   - Real-time collaboration features

4. **Analytics**
   - Detailed learning analytics
   - Common mistake patterns
   - Recommended learning paths
   - Skill assessment reports

5. **Multi-Language Support**
   - Interface translation (Indonesian, English)
   - Code examples in multiple languages (Python, Node.js, Java)
   - Localized educational content
