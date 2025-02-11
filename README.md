# WordPress Site Lock Plugin (Must-Use Plugin)

This must-use plugin provides a security mechanism to lock and unlock WordPress sites. It ensures file integrity, version control, and time-based security, preventing unauthorized modifications or rollbacks.

## Features

- **Lock/unlock site** via a secret URL
- **Multi-layer security validation**:
  - File modification detection through checksums
  - Version control enforcement 
  - Time rollback protection 
- **Automatic initialization** on new installations
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

The plugin can be reinforced with Apache-level security through the `.htaccess` file.


