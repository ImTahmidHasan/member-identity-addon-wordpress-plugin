<?php
class MIA_Admin_Column {
    public function __construct() {
        add_filter('manage_users_columns', function($c) { $c['mia_id'] = 'Member Identity'; return $c; });
        add_filter('manage_users_custom_column', [$this, 'val'], 10, 3);
        add_action('pre_get_users', [$this, 'search']);
    }

    public function val($output, $column, $user_id) {
        if ($column === 'mia_id') {
            return '<code>' . (get_user_meta($user_id, 'member_identity', true) ?: '—') . '</code>';
        }
        return $output;
    }

    public function search($query) {
        if (!is_admin() || empty($query->get('search'))) return;
        $meta_query = $query->get('meta_query') ?: [];
        $meta_query[] = ['key' => 'member_identity', 'value' => $query->get('search'), 'compare' => 'LIKE'];
        $query->set('meta_query', $meta_query);
    }
}