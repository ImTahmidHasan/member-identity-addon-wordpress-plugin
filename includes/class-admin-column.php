<?php
class MIA_Admin_Column {
    public function __construct() {
        add_filter('manage_users_columns', [$this, 'add_column']);
        add_filter('manage_users_custom_column', [$this, 'render_column'], 10, 3);
        add_action('pre_get_users', [$this, 'make_searchable']);
    }

    public function add_column($columns) {
        $columns['member_identity'] = __('Member Identity', 'member-identity-addon');
        return $columns;
    }

    public function render_column($output, $column_name, $user_id) {
        if ($column_name === 'member_identity') {
            $id = get_user_meta($user_id, 'member_identity', true);
            return $id ? '<strong>' . esc_html($id) . '</strong>' : '—';
        }
        return $output;
    }

    public function make_searchable($query) {
        if (!is_admin()) return;
        $search = $query->get('search');
        if (!$search) return;

        $meta_query = $query->get('meta_query') ?: [];
        $meta_query[] = [
            'key'     => 'member_identity',
            'value'   => ltrim($search, '*'),
            'compare' => 'LIKE'
        ];
        $query->set('meta_query', $meta_query);
    }
}