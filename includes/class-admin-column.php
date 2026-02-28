<?php
class MIA_Admin_Column {
    public function __construct() {
        add_filter('manage_users_columns', function($c) { $c['mia_id'] = 'Identity'; return $c; });
        add_filter('manage_users_custom_column', [$this, 'display'], 10, 3);
    }

    public function display($output, $column, $user_id) {
        if ($column === 'mia_id') {
            $id = get_user_meta($user_id, 'member_identity', true);
            return $id ? '<strong>'.esc_html($id).'</strong>' : '<span style="color:#ccc">—</span>';
        }
        return $output;
    }
}