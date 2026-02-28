<?php
/**
 * Plugin Name: Member Identity Addon
 * Description: Robust identity generation for UM with configurable hyphenation and legacy sync.
 * Version: 1.3.0
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