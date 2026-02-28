<?php
class MIA_Identity_Generator {
    public function __construct() {
        add_action('user_register', [$this, 'generate_identity_on_registration'], 20);
    }

    public function generate_identity_on_registration($user_id) {
        if (get_user_meta($user_id, 'member_identity', true)) return;

        $user = get_userdata($user_id);
        $role = !empty($user->roles) ? $user->roles[0] : 'subscriber';
        
        $settings = get_option('mia_role_settings', []);
        if (empty($settings[$role]) || empty($settings[$role]['enabled'])) return;

        $config = $settings[$role];
        $id = $this->get_next_sequence($role, $config);

        $formatted_id = $this->format_id($id, $config);
        update_user_meta($user_id, 'member_identity', $formatted_id);
    }

    private function get_next_sequence($role, $config) {
        // Atomic increment using WP options API
        $all_counters = get_option('mia_counters', []);
        $current = isset($all_counters[$role]) ? intval($all_counters[$role]) : intval($config['start_number']) - 1;
        
        $next = $current + 1;
        $all_counters[$role] = $next;
        update_option('mia_counters', $all_counters);
        
        return $next;
    }

    private function format_id($num, $config) {
        $prefix = $config['prefix'] ?? '';
        $year = !empty($config['include_year']) ? date('Y') . '-' : '';
        $padded = str_pad($num, (int)$config['padding'], '0', STR_PAD_LEFT);
        
        return $prefix . $year . $padded;
    }
}