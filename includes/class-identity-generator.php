<?php
class MIA_Identity_Generator {
    public function __construct() {
        add_action('user_register', [$this, 'handle_new_registration'], 30);
    }

    public function handle_new_registration($user_id) {
        $this->assign_identity_to_user($user_id);
    }

    public function assign_identity_to_user($user_id, $force = false, $manual_num = null) {
        // If not forcing and ID exists, stop.
        if (!$force && get_user_meta($user_id, 'member_identity', true)) return false;

        $user = get_userdata($user_id);
        if (!$user) return false;

        $role = get_user_meta($user_id, 'role', true); 
        if (!$role && !empty($user->roles)) $role = $user->roles[0];

        $settings = get_option('mia_role_settings', []);
        if (!isset($settings[$role]) || empty($settings[$role]['enabled'])) return false;
        $config = $settings[$role];

        // Logic: Use manual number (from Sync) OR Increment Global Counter
        if ($manual_num !== null) {
            $num = $manual_num;
        } else {
            $all_counters = get_option('mia_counters', []);
            $num = isset($all_counters[$role]) ? intval($all_counters[$role]) + 1 : intval($config['start_number']);
            
            $all_counters[$role] = $num;
            update_option('mia_counters', $all_counters);
        }
        
        update_user_meta($user_id, 'member_identity_num', $num);

        // Formatting
        $prefix = isset($config['prefix']) ? trim($config['prefix']) : '';
        $year   = !empty($config['include_year']) ? date('Y') : '';
        $dash   = !empty($config['use_dash']) ? '-' : '';
        $padded = str_pad($num, absint($config['padding']), '0', STR_PAD_LEFT);

        $final_id = $prefix . $year . $dash . $padded;

        return update_user_meta($user_id, 'member_identity', $final_id);
    }
}