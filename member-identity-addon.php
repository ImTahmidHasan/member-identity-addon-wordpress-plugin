<?php
/**
 * Plugin Name: Member Identity Addon (Refined)
 * Description: Robust identity generation for UM. Assigns unique IDs to new and legacy users.
 * Version: 1.2.0
 * Author: Senior Architect
 */

if (!defined('ABSPATH')) exit;

define('MIA_PATH', plugin_dir_path(__FILE__));

require_once MIA_PATH . 'includes/class-loader.php';

add_action('plugins_loaded', function() {
    if (class_exists('UM')) {
        $plugin = new MIA_Loader();
        $plugin->run();
    }
});