<?php
/**
 * Plugin Name: Member Identity Addon
 * Description: Generates permanent, non-editable unique IDs for Ultimate Member roles with legacy user support.
 * Version: 1.1.0
 * Author: Senior Architect
 * Text Domain: member-identity-addon
 */

if (!defined('ABSPATH')) exit;

define('MIA_VERSION', '1.1.0');
define('MIA_PATH', plugin_dir_path(__FILE__));

require_once MIA_PATH . 'includes/class-loader.php';

add_action('plugins_loaded', function() {
    if (class_exists('UM')) {
        $plugin = new MIA_Loader();
        $plugin->run();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>Member Identity Addon requires <strong>Ultimate Member</strong>.</p></div>';
        });
    }
});