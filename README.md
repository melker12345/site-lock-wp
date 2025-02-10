# WordPress Site Lock Plugin (Must-Use Plugin)

This must-use plugin provides a comprehensive security mechanism to lock and unlock your WordPress site. It includes multiple layers of protection including file integrity verification, version control, and Apache-level security. When locked, the site displays a message to visitors indicating that the site is locked.

## Features

- Lock/unlock the site using a secret URL
- Multi-layer security valisation:
  - File modification detection
  - Version control system
  - Time manipulation protection
  - Apache-level security (for if the wp-installation is on a Apache server)
- Self-initializing system that maintains security state

## Installation

1. Place `lock-site.php` in your WordPress `wp-content/mu-plugins/` directory
2. Add the following line to your `wp-config.php`:
   ```php
   define('EXPECTED_HASH', 'your-secure-hash-here');
   ```
   Replace `your-secure-hash-here` with a secure hash of your choice (recommended to use a SHA-256 hash)
3. Place the provided `.htaccess` file in your WordPress root directory (This is only needed if the wp-installation is on a Apache server)

## Initialization

The plugin automatically initializes itself on first run:

1. Creates a version control system in the database
2. Generates and stores checksums of critical files
3. Establishes timestamp verification points

No manual initialization is required - the plugin handles this automatically when first loaded. Simply add the hash to the wp-config.php file 

## Usage

### Locking/Unlocking the Site

To toggle the site's lock state, visit:

`https://your-site.com/?unlock=your-secure-hash-here`

Replace:

- `your-site.com` with your actual domain
- `your-secure-hash-here` with the hash you defined in wp-config.php

### How it Works

1. When the site is unlocked:
   - No hash is stored in the WordPress options table
   - Security checks run on every page load
   - File integrity is continuously monitored
   - Version control system tracks changes

2. When the site is locked:
   - A hash is stored in the WordPress options table
   - All pages show a lock message
   - Apache-level protection remains active
   - Security state is maintained in multiple locations

### Security Features

The plugin locks the site if:

- Any critical files are modified (wp-config.php, plugin files)
- The plugin file is missing or tampered with
- The `EXPECTED_HASH` constant is not defined
- Version control system detects inconsistencies
- System time manipulation is detected
- Database tampering is detected

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

This Apache configuration:

- Detects if the security plugin is missing
- Returns a 503 Service Unavailable response if the plugin is removed
- Provides a custom error message
- Works independently of WordPress, providing protection even if WordPress fails

### Anti-Rollback Protection

The plugin includes several mechanisms to prevent bypass through rollbacks:

1. **File Integrity Verification**
   - Stores SHA-256 checksums of critical files (wp-config.php and the plugin itself)
   - Checksums are stored in multiple locations in the database
   - Any modification to critical files triggers a security lockdown

2. **Version Control**
   - Maintains a version number that increments with each lock/unlock operation
   - Version number verification prevents database rollbacks
   - Any mismatch in version numbers triggers a security lockdown

3. **Time Manipulation Protection**
   - Stores timestamps of critical operations
   - Detects if system time has been rolled back
   - Prevents bypass of time-based security measures

## Important Notes

1. Keep your hash secure and never share it publicly
2. The hash in wp-config.php is used only for the unlock URL
3. Make sure to set an appropriate `LAST_ALLOWED_MODIFICATION_DATE` in the plugin file
4. Remember that this is a must-use plugin, so it cannot be deactivated through the WordPress admin interface
