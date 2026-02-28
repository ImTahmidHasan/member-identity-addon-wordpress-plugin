<?php
class MIA_Identity_Generator {
    public function __construct() {
        add_action('user_register', [$this, 'handle_new_registration'], 30);
    }

    public function handle_new_registration($user_id) {
        $this->assign_identity_to_user($user_id);
    }

    public function assign_identity_to_user($user_id, $force = false) {
        if (!$force && get_user_meta($user_id, 'member_identity', true)) return false;

        $user = get_userdata($user_id);
        if (!$user) return false;

        $role = get_user_meta($user_id, 'role', true); 
        if (!$role && !empty($user->roles)) $role = $user->roles[0];

        $settings = get_option('mia_role_settings', []);
        if (!isset($settings[$role]) || empty($settings[$role]['enabled'])) return false;

        $config = $settings[$role];
        $all_counters = get_option('mia_counters', []);
        
        // Handle Numbering
        if ($force) {
            $current_val = get_user_meta($user_id, 'member_identity_num', true);
            $num = $current_val ? intval($current_val) : (isset($all_counters[$role]) ? $all_counters[$role] : $config['start_number']);
        } else {
            $num = isset($all_counters[$role]) ? intval($all_counters[$role]) + 1 : intval($config['start_number']);
            $all_counters[$role] = $num;
            update_option('mia_counters', $all_counters);
            update_user_meta($user_id, 'member_identity_num', $num);
        }

        // Formatting Logic
        $prefix = isset($config['prefix']) ? trim($config['prefix']) : '';
        $year   = !empty($config['include_year']) ? date('Y') : '';
        $dash   = !empty($config['use_dash']) ? '-' : '';
        $padded = str_pad($num, absint($config['padding']), '0', STR_PAD_LEFT);

        // Result: PREFIX + YEAR + (DASH if yes) + PADDED_NUMBER
        $final_id = $prefix . $year . $dash . $padded;

        return update_user_meta($user_id, 'member_identity', $final_id);
    }
}