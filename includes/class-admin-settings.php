<?php
class MIA_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'handle_sync']);
    }

    public function add_menu() {
        add_submenu_page('ultimatemember', 'Member Identity', 'Identity Addon', 'manage_options', 'mia-settings', [$this, 'render_page']);
    }

    public function register_settings() {
        register_setting('mia_group', 'mia_role_settings');
    }

    public function handle_sync() {
        if (!isset($_GET['mia_action']) || !current_user_can('manage_options')) return;
        check_admin_referer('mia_sync_action');

        $gen = new MIA_Identity_Generator();
        $mode = $_GET['mia_action'];
        $args = ['number' => 50];
        
        if ($mode === 'sync_missing') {
            $args['meta_query'] = [['key' => 'member_identity', 'compare' => 'NOT EXISTS']];
        }

        $query = new WP_User_Query($args);
        $processed = 0;
        foreach ($query->get_results() as $u) {
            if ($gen->assign_identity_to_user($u->ID, ($mode === 'force_update'))) $processed++;
        }

        wp_redirect(add_query_arg(['processed' => $processed, 'page' => 'mia-settings'], admin_url('admin.php')));
        exit;
    }

    public function render_page() {
        $roles = wp_roles()->get_names();
        $settings = get_option('mia_role_settings', []);
        ?>
        <div class="wrap">
            <h1>Identity Configuration</h1>
            <form method="post" action="options.php">
                <?php settings_fields('mia_group'); ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Role</th><th>Active</th><th>Prefix</th><th>Year</th><th>Dash (-)</th><th>Padding</th><th>Start</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $slug => $name): 
                            $v = $settings[$slug] ?? ['padding'=>5, 'start_number'=>1]; ?>
                            <tr>
                                <td><strong><?php echo esc_html($name); ?></strong></td>
                                <td><input type="checkbox" name="mia_role_settings[<?php echo $slug; ?>][enabled]" <?php checked(!empty($v['enabled'])); ?>></td>
                                <td><input type="text" name="mia_role_settings[<?php echo $slug; ?>][prefix]" value="<?php echo esc_attr($v['prefix'] ?? ''); ?>"></td>
                                <td><input type="checkbox" name="mia_role_settings[<?php echo $slug; ?>][include_year]" <?php checked(!empty($v['include_year'])); ?>></td>
                                <td><input type="checkbox" name="mia_role_settings[<?php echo $slug; ?>][use_dash]" <?php checked(!empty($v['use_dash'])); ?>></td>
                                <td><input type="number" name="mia_role_settings[<?php echo $slug; ?>][padding]" value="<?php echo $v['padding']; ?>" style="width:50px"></td>
                                <td><input type="number" name="mia_role_settings[<?php echo $slug; ?>][start_number]" value="<?php echo $v['start_number']; ?>" style="width:70px"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>

            <div class="card" style="margin-top:20px; padding:15px;">
                <h2>Legacy Synchronization</h2>
                <div style="display:flex; gap:10px;">
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=mia-settings&mia_action=sync_missing'), 'mia_sync_action'); ?>" class="button">Assign Missing IDs</a>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=mia-settings&mia_action=force_update'), 'mia_sync_action'); ?>" class="button-secondary button" onclick="return confirm('Update all IDs to current Prefix/Dash settings?');">Force Update Format</a>
                </div>
            </div>
        </div>
        <?php
    }
}