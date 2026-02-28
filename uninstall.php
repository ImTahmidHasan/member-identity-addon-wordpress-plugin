<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
delete_option('mia_role_settings');
delete_option('mia_counters');