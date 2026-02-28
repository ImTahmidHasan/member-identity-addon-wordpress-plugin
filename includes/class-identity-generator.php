<?php
class MIA_Identity_Generator {
    public function __construct() {
        add_action('user_register', [$this, 'handle_new_registration'], 30);
    }

    public function handle_new_registration($user_id) {
        $this->assign_identity_to_user($user_id);
    }

    public function assign_identity_to_user($user_id, $force = false) {
        // Only skip if not forcing an update
        if (!$force && get_user_meta($user_id, 'member_identity', true)) return false;

        $user = get_userdata($user_id);
        if (!$user) return false;

        $role = get_user_meta($user_id, 'role', true); 
        if (!$role && !empty($user->roles)) {
            $role = $user->roles[0];
        }

        $settings = get_option('mia_role_settings', []);
        if (!isset($settings[$role]) || empty($settings[$role]['enabled'])) return false;

        $config = $settings[$role];

        // COUNTER LOGIC
        $all_counters = get_option('mia_counters', []);
        
        if ($force) {
            // If forcing, we use a temporary count to regenerate without breaking the global sequence
            // unless you want to reset the whole sequence. Usually, we just re-format.
            // We'll fetch the current ID's numeric part if possible, otherwise use global.
            $current_val = get_user_meta($user_id, 'member_identity_num', true);
            $num = $current_val ? intval($current_val) : (isset($all_counters[$role]) ? $all_counters[$role] : $config['start_number']);
        } else {
            $num = isset($all_counters[$role]) ? intval($all_counters[$role]) + 1 : intval($config['start_number']);
            $all_counters[$role] = $num;
            update_option('mia_counters', $all_counters);
            // Save the raw number for future formatting updates
            update_user_meta($user_id, 'member_identity_num', $num);
        }

        $prefix = isset($config['prefix']) ? trim($config['prefix']) : '';
        $year = !empty($config['include_year']) ? date('Y') . '-' : '';
        $padding = isset($config['padding']) ? absint($config['padding']) : 5;
        
        $final_id = $prefix . $year . str_pad($num, $padding, '0', STR_PAD_LEFT);

        return update_user_meta($user_id, 'member_identity', $final_id);
    }
}