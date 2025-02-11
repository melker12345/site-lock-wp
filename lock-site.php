<?php
/**
 * Lock Site Plugin (Must Use Plugin)
 * This plugin locks the site if:
 * 1. The `wp-config.php` file is modified after a specific date.
 * 2. The must-use plugin is missing.
 * 3. The SHA-256 hash in `wp-config.php` does not match the one stored in the WordPress options table.
 * 4. The site can be locked/unlocked using a secret URL.
 * 5. Anti-rollback protection through file checksums and version tracking.
 * 
 * Place this file inside `wp-content/mu-plugins/` for it to function.
 */

// Initialize the plugin
function initialize_site_lock_plugin() {
    // Only initialize if not already done
    if (get_option('site_lock_version', 0) === 0) {
        update_option('site_lock_version', 1);
        update_critical_file_checksums();
    }
}

// Run initialization on every load until it's set
add_action('init', 'initialize_site_lock_plugin', 0); // Priority 0 ensures it runs first

function update_critical_file_checksums() {
    $checksums = array(
        'wp-config' => hash_file('sha256', ABSPATH . 'wp-config.php'),
        'lock-site' => hash_file('sha256', __FILE__),
    );
    
    // Store checksums in multiple locations
    update_option('site_lock_checksums', $checksums);
    update_option('site_lock_checksums_backup', $checksums);
    
    // Store the current time from an external source if possible
    $current_time = time();
    update_option('site_lock_last_update', $current_time);
}


function verify_integrity() {
    $version = get_option('site_lock_version', 0);

    // Auto-initialize if first run
    if ($version === 0) {
        initialize_site_lock_plugin();
        return;
    }

    $stored_checksums = get_option('site_lock_checksums', array());
    $backup_checksums = get_option('site_lock_checksums_backup', array());

    // If no checksums exist, assume first run and initialize them
    if (empty($stored_checksums) || empty($backup_checksums)) {
        update_critical_file_checksums();
        return;
    }

    // Current hashes
    $current_wp_config_hash = hash_file('sha256', ABSPATH . 'wp-config.php');
    $current_plugin_hash = hash_file('sha256', __FILE__);

    // If checksums are different, allow safe updates via a URL
    if ($current_wp_config_hash !== $stored_checksums['wp-config'] ||
        $current_plugin_hash !== $stored_checksums['lock-site']) {

        if (isset($_GET['update_checksums']) && $_GET['update_checksums'] === EXPECTED_HASH) {
            // Update checksums to match the new changes
            update_critical_file_checksums();
            die('<h1>Success</h1><p>Checksums updated successfully.</p>');
        }

        // Otherwise, lock the site as a security measure
        show_lock_message('Security violation: Critical files have been modified. <br> 
            If you made legitimate changes, visit: <br> 
            <strong>' . site_url() . '/?update_checksums=' . EXPECTED_HASH . '</strong> to allow the update.');
        exit;
    }

    // Verify time hasn't been rolled back
    if (time() < get_option('site_lock_last_update', 0)) {
        show_lock_message('Security violation: System time manipulation detected.');
        exit;
    }
}

// Define the last allowed modification date in "ddmmyyyy" format (e.g., 10 Feb 2025 -> "10022025").
define('LAST_ALLOWED_MODIFICATION_DATE', '12022025');

// Convert the human-readable date to a Unix timestamp
$last_allowed_timestamp = strtotime(
    substr(LAST_ALLOWED_MODIFICATION_DATE, 0, 2) . '-' .  // Extract day
    substr(LAST_ALLOWED_MODIFICATION_DATE, 2, 2) . '-' .  // Extract month
    substr(LAST_ALLOWED_MODIFICATION_DATE, 4, 4)          // Extract year
);

// Hook into WordPress initialization to check site lock status and unlock functionality
add_action('init', 'check_must_use_plugin_and_file_modification');

// Run integrity check on every request
add_action('init', 'verify_integrity', 1); // Priority 1 ensures it runs before other checks

/**
 * Function to check if the must-use plugin is present, if `wp-config.php` was modified after the allowed date,
 * and if the SHA-256 hash matches. Also handles unlocking via secret URL.
 */
function check_must_use_plugin_and_file_modification() {
    global $last_allowed_timestamp; // Bring the timestamp into function scope

    // Define the path of this must-use plugin
    $plugin_path = WPMU_PLUGIN_DIR . '/lock-site.php';

    // If the must-use plugin is missing, lock the site
    if (!file_exists($plugin_path)) {
        show_lock_message('The site is locked because the must-use plugin is missing.');
        exit; // Stop execution
    }

    // Get the last modification time of `wp-config.php`
    $last_mod_time = filemtime(ABSPATH . 'wp-config.php');

    // If `wp-config.php` was modified after the allowed time, lock the site
    if ($last_mod_time > $last_allowed_timestamp) {
        show_lock_message('The site is locked because `wp-config.php` was recently modified.');
        exit; // Stop execution
    }

    // Ensure the expected hash is defined in wp-config.php
    if (!defined('EXPECTED_HASH')) {
        show_lock_message('Security error: Expected hash is not defined in wp-config.php.');
        exit;
    }

    // Check if the expected hash matches the one in WordPress options
    $stored_hash = get_option('site_lock_hash');

    // Handle unlocking via secret URL
    if (isset($_GET['unlock']) && $_GET['unlock'] === EXPECTED_HASH) {
        toggle_site_lock();
        exit;
    }

    // If there is a stored hash, the site should be locked
    if (!empty($stored_hash)) {
        show_lock_message('The site is locked.');
        exit; // Stop execution
    }
}

/**
 * Function to toggle the lock/unlock state of the site using the secret URL.
 */
function toggle_site_lock() {
    $current_hash = get_option('site_lock_hash', '');

    if (empty($current_hash)) {
        // Site is currently unlocked, so lock it
        update_option('site_lock_hash', EXPECTED_HASH);
        
        // Update security measures
        $version = get_option('site_lock_version', 0);
        update_option('site_lock_version', $version + 1);
        update_critical_file_checksums();
        
        die('<h1>Site Locked</h1><p>The site is now restricted.</p>');
    } else {
        // Site is currently locked, so unlock it
        update_option('site_lock_hash', '');
        
        // Update security measures
        $version = get_option('site_lock_version', 0);
        update_option('site_lock_version', $version + 1);
        update_critical_file_checksums();
        
        die('<h1>Site Unlocked</h1><p>The site is now accessible.</p>');
    }
}

/**
 * Function to display a custom lock message.
 * This function shows a site-wide lock message for all users.
 */
function show_lock_message($message) {
    wp_die('<h1>Site Locked</h1><p>' . esc_html($message) . '</p><p>Please contact the administrator.</p>');
}
?>