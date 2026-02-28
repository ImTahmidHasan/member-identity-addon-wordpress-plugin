<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

delete_option('mia_role_settings');
delete_option('mia_counters');

// We do NOT delete user meta 'member_identity' as it's a permanent record.
// Keeping identity ensures data integrity if the plugin is re-installed.