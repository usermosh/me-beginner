# Copilot Instructions for ITC127-CS2A-2025-Acobado

## Project Overview

A beginner PHP learning project for an ITC127 CS2A course. Contains lecture demonstrations (basic concepts like variable declaration) and practical activities (e.g., triangle calculator). Also includes a technical management system with user authentication and account management features that interacts with a MySQL database.

## Setup & Running

### Local Development (XAMPP)

1. Place folder in `C:\xampp\htdocs\`
2. Start Apache and MySQL in XAMPP Control Panel
3. Access via `http://localhost/ITC127-CS2A-2025-Acobado/lecture1.php` or individual files
4. Database requires MySQL connection to `itc127-cs2a-2026` database with credentials in `database/config.php`

### Database Configuration

- **Location**: `database/config.php`
- **Connection Method**: MySQLi (procedural)
- **Timezone**: Asia/Manila (hardcoded in config.php)
- **Credentials**: User `mark` with password `acobado` for database `itc127-cs2a-2026`

## Project Structure

```
/
├── lecture[1-5].php          # Lecture demonstrations (standalone HTML/PHP files)
├── activities/               # Practical exercises
│   ├── activity1.php         # Triangle calculator
│   └── activity2.php
├── database/                 # Database operations and auth system
│   ├── config.php            # DB connection setup
│   ├── login.php             # Authentication entry point
│   ├── sessionChecker.php    # Session validation helper
│   ├── accountManagement.php # Main dashboard (protected)
│   ├── createAccount.php
│   ├── deleteAccount.php
│   ├── updateAccount.php
│   └── logout.php
└── README.md
```

## Key Conventions

### File Organization

- **Lecture files** (`lecture*.php`): Self-contained HTML+PHP files demonstrating single concepts. No external dependencies.
- **Database module** (`database/`): Handles all auth, session management, and account CRUD operations.
- **Activities** (`activities/`): Standalone calculator/tool files with embedded HTML and PHP.

### PHP Patterns

1. **MySQLi Prepared Statements**: Used for all database queries to prevent SQL injection (see `database/login.php` for pattern)
   ```php
   $stmt = mysqli_prepare($link, $sql);
   mysqli_stmt_bind_param($stmt, "ss", $param1, $param2);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
   ```

2. **Session-Based Authentication**: Protected pages check `$_SESSION['username']` via `sessionChecker.php`
   - Must include at top of protected files: `require_once "database/sessionChecker.php";`
   - Session messages stored in `$_SESSION['success']` and `$_SESSION['error']`

3. **HTML Output Escaping**: Use `htmlspecialchars()` for user-controlled output (see `lecture2.php`)
   - Pattern: `<?= htmlspecialchars($variable) ?>`

4. **Form Handling**: POST forms use submit button names to distinguish actions
   - Pattern: `if (isset($_POST['btnsubmit'])) { ... }`
   - Always use `htmlspecialchars($_SERVER["PHP_SELF"])` in form action attributes

5. **Inline Styling**: CSS embedded in `<style>` tags in HTML files (no external stylesheets)

### Database Schema

Table: `tblaccounts`
- `username` (string)
- `password` (string)
- `usertype` (string)
- `status` (enum: 'ACTIVE' or other)

### Security Notes

- Passwords stored in plain text in config.php (development only - not production-safe)
- No password hashing in auth system (educational project)
- Session-based auth with redirect to login on failed session check

## Common Tasks

### Adding a New Lecture

1. Create `lectureN.php` as standalone HTML+PHP file
2. Include embedded CSS in `<style>` tag
3. Use `<?= ?>` short echo tags for output
4. No database dependencies required

### Adding Database Operations

1. Create file in `database/` directory
2. Include `config.php` at top: `require_once "config.php";`
3. Use MySQLi prepared statements for queries
4. Set session variables for messaging: `$_SESSION['success']` or `$_SESSION['error']`
5. Redirect with `header("location: targetpage.php"); exit;`

### Creating Protected Pages

1. Add to `database/` directory
2. Start with: `session_start();` then `require_once "sessionChecker.php";`
3. sessionChecker.php will redirect to login if user not authenticated
