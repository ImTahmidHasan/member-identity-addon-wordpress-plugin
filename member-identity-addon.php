<?php
/**
 * Plugin Name: Member Identity Addon
 * Description: Generates permanent, non-editable unique IDs for Ultimate Member roles.
 * Version: 1.0.0
 * Author: Senior Architect
 * Text Domain: member-identity-addon
 */

if (!defined('ABSPATH')) exit;

// Constants
define('MIA_VERSION', '1.0.0');
define('MIA_PATH', plugin_dir_path(__FILE__));
define('MIA_URL', plugin_dir_url(__FILE__));

// Autoloader (Simplified for this context)
require_once MIA_PATH . 'includes/class-loader.php';

function run_member_identity_addon() {
    $plugin = new MIA_Loader();
    $plugin->run();
}

// Check for Ultimate Member dependency
add_action('plugins_loaded', function() {
    if (class_exists('UM')) {
        run_member_identity_addon();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>Member Identity Addon requires <strong>Ultimate Member</strong> to be active.</p></div>';
        });
    }
});