<?php
class MIA_Identity_Generator {
    public function __construct() {
        add_action('user_register', [$this, 'handle_new_registration'], 20);
    }

    public function handle_new_registration($user_id) {
        $this->assign_identity_to_user($user_id);
    }

    public function assign_identity_to_user($user_id) {
        // 1. Check if identity already exists
        if (get_user_meta($user_id, 'member_identity', true)) return false;

        // 2. Get User Role
        $user = get_userdata($user_id);
        if (!$user || empty($user->roles)) return false;
        $role = $user->roles[0];

        // 3. Load Role Config
        $settings = get_option('mia_role_settings', []);
        if (empty($settings[$role]) || empty($settings[$role]['enabled'])) return false;

        $config = $settings[$role];

        // 4. Atomic Increment
        $all_counters = get_option('mia_counters', []);
        $current_num = isset($all_counters[$role]) ? intval($all_counters[$role]) : intval($config['start_number']) - 1;
        $next_num = $current_num + 1;
        
        $all_counters[$role] = $next_num;
        update_option('mia_counters', $all_counters);

        // 5. Format and Save
        $prefix = $config['prefix'] ?? '';
        $year = !empty($config['include_year']) ? date('Y') . '-' : '';
        $padded = str_pad($next_num, (int)$config['padding'], '0', STR_PAD_LEFT);
        $final_id = $prefix . $year . $padded;

        return update_user_meta($user_id, 'member_identity', $final_id);
    }
}