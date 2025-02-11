# WordPress Site Lock Plugin (Must-Use Plugin)

This must-use plugin provides a robust security mechanism to lock and unlock your WordPress site. It ensures file integrity, version control, and time-based security, preventing unauthorized modifications or rollbacks.

## Features

- **Lock/unlock site** via a secret URL
- **Multi-layer security validation**:
  - File modification detection
  - Version control enforcement
  - Time rollback protection
- **Automatic initialization** on new installations
- **Seamless migration** support for moving to new WordPress setups
- **Self-healing security**: Allows controlled updates without false security alerts
- **Apache-level security** (if running on an Apache server)

## Installation

1. Place `lock-site.php` in `wp-content/mu-plugins/`.
2. Add the following to `wp-config.php`:
   ```php
   define('EXPECTED_HASH', 'your-secure-hash-here');
   ```
   Replace `'your-secure-hash-here'` with a secure SHA-256 hash.
3. If using Apache, place the provided `.htaccess` file in your WordPress root directory.

## Initialization

The plugin **automatically initializes** on first load:
- Generates and stores critical file checksums
- Establishes a versioning and time validation system
- Detects migration and updates security settings

## Usage

### Locking/Unlocking the Site

To toggle site lock state, visit:
```
https://your-site.com/?unlock=your-secure-hash-here
```
Replace `your-site.com` with your domain and `your-secure-hash-here` with your defined hash.

### Approving Legitimate File Changes

If `wp-config.php` or `lock-site.php` is updated, visit:
```
https://your-site.com/?update_checksums=your-secure-hash-here
```
This prevents unnecessary security locks due to intentional modifications.

## Security Features

The plugin locks the site if:
- **Critical files are modified** (`wp-config.php`, `lock-site.php`)
- **Expected security settings are missing**
- **System time manipulation is detected**
- **Version rollback is attempted**

### Apache Security Layer (OBS! ONLY FOR APACHE SERVERS)

The plugin is reinforced by Apache-level security through a `.htaccess` file that provides an additional layer of protection:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    # If the security plugin is missing, show an error message instead of redirecting
    RewriteCond %{DOCUMENT_ROOT}/wp-content/mu-plugins/lock-site.php !-f
    RewriteRule ^(.*)$ - [R=503,L]
</IfModule>

# Custom 503 error message
ErrorDocument 503 "<h1>Site Locked</h1><p>This site is locked due to a security issue.</p><p>Please contact the administrator.</p>"
```

## Anti-Rollback Protection

- **File Integrity Checks**: Monitors SHA-256 hashes of critical files.
- **Version Control**: Prevents unauthorized rollbacks.
- **Time-based Protection**: Detects time manipulation attempts.

## Important Notes

- Keep your `EXPECTED_HASH` **private and secure**.
- The unlock URL works **only when the correct hash is provided**.
- Ensure `LAST_ALLOWED_MODIFICATION_DATE` is correctly set in `lock-site.php`.
- As a **must-use plugin**, it cannot be disabled via the WordPress admin interface.

This plugin is designed for **maximum security with minimal maintenance**, ensuring your WordPress installation remains protected from unauthorized changes.

