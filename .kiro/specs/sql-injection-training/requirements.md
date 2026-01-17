# Requirements Document

## Introduction

Platform training cybersecurity yang sengaja dibuat vulnerable terhadap SQL injection untuk tujuan edukasi. Platform ini akan digunakan untuk melatih peserta memahami, mengidentifikasi, dan mengeksploitasi kerentanan SQL injection dalam lingkungan yang aman dan terkontrol.

## Glossary

- **Training_Platform**: Sistem web berbasis PHP yang menyediakan environment untuk praktik SQL injection
- **Vulnerable_Endpoint**: Halaman atau fitur yang sengaja dibuat rentan terhadap SQL injection
- **Trainee**: Peserta training yang akan belajar tentang SQL injection
- **Admin**: Instruktur atau administrator yang mengelola platform training
- **Challenge**: Skenario atau task yang harus diselesaikan trainee menggunakan teknik SQL injection
- **Hint_System**: Fitur yang memberikan petunjuk kepada trainee
- **Database**: MySQL database yang menyimpan data aplikasi

## Requirements

### Requirement 1: User Authentication System

**User Story:** As a trainee, I want to login to the platform using vulnerable authentication, so that I can practice SQL injection on login forms.

#### Acceptance Criteria

1. WHEN a user submits login credentials, THE Training_Platform SHALL process the input without proper sanitization
2. WHEN a user enters SQL injection payload in username or password field, THE Training_Platform SHALL execute the malicious query against the database
3. WHEN a successful SQL injection bypass occurs, THE Training_Platform SHALL grant access to the user account
4. WHEN login attempt is made, THE Training_Platform SHALL display the SQL query being executed for educational purposes
5. THE Training_Platform SHALL store user credentials in plaintext in the database for demonstration purposes

### Requirement 2: Product Search Functionality

**User Story:** As a trainee, I want to search for products using a vulnerable search feature, so that I can practice SQL injection for data extraction.

#### Acceptance Criteria

1. WHEN a user enters a search term, THE Training_Platform SHALL construct SQL query using string concatenation without parameterization
2. WHEN a user submits search input, THE Training_Platform SHALL display the constructed SQL query on the page
3. WHEN a SQL injection payload is entered, THE Training_Platform SHALL execute the payload and return results
4. WHEN search results are displayed, THE Training_Platform SHALL show all columns returned by the query including hidden data
5. THE Training_Platform SHALL allow UNION-based SQL injection attacks through the search functionality

### Requirement 3: User Profile Display

**User Story:** As a trainee, I want to view user profiles through vulnerable URL parameters, so that I can practice SQL injection via GET requests.

#### Acceptance Criteria

1. WHEN a user accesses profile page with user ID parameter, THE Training_Platform SHALL use the ID directly in SQL query without validation
2. WHEN a malicious ID parameter is provided, THE Training_Platform SHALL execute the injected SQL code
3. WHEN profile data is retrieved, THE Training_Platform SHALL display all database columns for educational visibility
4. THE Training_Platform SHALL expose error messages that reveal database structure information
5. THE Training_Platform SHALL allow boolean-based blind SQL injection through profile endpoints

### Requirement 4: Admin Panel Access

**User Story:** As a trainee, I want to access restricted admin functionality through SQL injection, so that I can understand privilege escalation attacks.

#### Acceptance Criteria

1. WHEN a user successfully exploits SQL injection, THE Training_Platform SHALL allow access to admin panel
2. WHEN admin panel is accessed, THE Training_Platform SHALL display sensitive information like all user accounts
3. THE Training_Platform SHALL have an admin flag in the database that can be manipulated via SQL injection
4. WHEN admin privileges are obtained, THE Training_Platform SHALL show congratulatory message with explanation
5. THE Training_Platform SHALL log all admin access attempts for instructor review

### Requirement 5: Challenge System

**User Story:** As a trainee, I want to complete progressive challenges, so that I can learn different SQL injection techniques systematically.

#### Acceptance Criteria

1. THE Training_Platform SHALL provide at least 5 different SQL injection challenges with varying difficulty
2. WHEN a challenge is accessed, THE Training_Platform SHALL display the objective and target vulnerability
3. WHEN a challenge is completed successfully, THE Training_Platform SHALL mark it as solved and display the flag
4. THE Training_Platform SHALL track trainee progress across all challenges
5. WHEN all challenges are completed, THE Training_Platform SHALL display completion certificate or summary

### Requirement 6: Hint System

**User Story:** As a trainee, I want to request hints for challenges, so that I can learn when I'm stuck.

#### Acceptance Criteria

1. WHEN a trainee requests a hint, THE Training_Platform SHALL display progressive hints without revealing the complete solution
2. THE Training_Platform SHALL provide at least 3 levels of hints per challenge
3. WHEN a hint is viewed, THE Training_Platform SHALL record it in the trainee's progress
4. THE Training_Platform SHALL display SQL injection basics and common payloads in the hint system
5. WHEN maximum hints are used, THE Training_Platform SHALL offer to show the solution

### Requirement 7: Educational Feedback

**User Story:** As a trainee, I want to see detailed feedback on my SQL injection attempts, so that I can understand what works and why.

#### Acceptance Criteria

1. WHEN a SQL injection attempt is made, THE Training_Platform SHALL display the actual SQL query executed
2. WHEN a query fails, THE Training_Platform SHALL show detailed error messages including database structure hints
3. WHEN a query succeeds, THE Training_Platform SHALL explain why the injection worked
4. THE Training_Platform SHALL highlight the vulnerable code snippet for each challenge
5. THE Training_Platform SHALL provide links to documentation about the specific SQL injection technique used

### Requirement 8: Admin Dashboard

**User Story:** As an admin, I want to monitor trainee progress and activities, so that I can track learning outcomes.

#### Acceptance Criteria

1. WHEN an admin logs in, THE Training_Platform SHALL display a dashboard with all trainee activities
2. THE Training_Platform SHALL show completion rates for each challenge
3. WHEN viewing trainee details, THE Training_Platform SHALL display all SQL injection attempts made
4. THE Training_Platform SHALL allow admin to reset trainee progress
5. THE Training_Platform SHALL generate reports on common mistakes and successful techniques

### Requirement 9: Database Setup and Reset

**User Story:** As an admin, I want to reset the database to initial state, so that trainees can restart challenges with clean data.

#### Acceptance Criteria

1. THE Training_Platform SHALL provide a reset button that restores database to initial state
2. WHEN reset is triggered, THE Training_Platform SHALL recreate all tables with sample data
3. THE Training_Platform SHALL preserve trainee accounts and progress during database reset
4. WHEN database is reset, THE Training_Platform SHALL notify all active users
5. THE Training_Platform SHALL complete database reset within 5 seconds

### Requirement 10: Security Warnings and Disclaimers

**User Story:** As a platform owner, I want to display clear warnings about the intentional vulnerabilities, so that users understand this is for educational purposes only.

#### Acceptance Criteria

1. WHEN a user first accesses the platform, THE Training_Platform SHALL display a prominent disclaimer about intentional vulnerabilities
2. THE Training_Platform SHALL show warnings that code should never be used in production
3. WHEN vulnerable code is displayed, THE Training_Platform SHALL highlight why it's vulnerable and how to fix it
4. THE Training_Platform SHALL provide secure code examples alongside vulnerable examples
5. THE Training_Platform SHALL include a section on responsible disclosure and ethical hacking practices
