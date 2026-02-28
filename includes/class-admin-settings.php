<?php
class MIA_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'handle_sync_reset']);
    }

    public function add_menu() {
        add_submenu_page('ultimatemember', 'Member Identity', 'Identity Addon', 'manage_options', 'mia-settings', [$this, 'render_page']);
    }

    public function register_settings() {
        register_setting('mia_group', 'mia_role_settings');
    }

    public function handle_sync_reset() {
        if (!isset($_GET['mia_action']) || !current_user_can('manage_options')) return;
        check_admin_referer('mia_sync_action');

        $gen = new MIA_Identity_Generator();
        $mode = $_GET['mia_action'];
        $settings = get_option('mia_role_settings', []);
        $all_counters = get_option('mia_counters', []);

        // We process ALL users to ensure the sequence matches the "Start Seq"
        $users = get_users(['fields' => 'ID', 'orderby' => 'ID', 'order' => 'ASC']);
        
        // Reset the counters in memory for this operation
        $temp_counters = [];
        foreach($settings as $role_slug => $cfg) {
            $temp_counters[$role_slug] = intval($cfg['start_number']) - 1;
        }

        $processed = 0;
        foreach ($users as $user_id) {
            $user = get_userdata($user_id);
            $role = get_user_meta($user_id, 'role', true) ?: (!empty($user->roles) ? $user->roles[0] : '');

            if (isset($settings[$role]) && $settings[$role]['enabled']) {
                $temp_counters[$role]++;
                $new_num = $temp_counters[$role];
                
                // Force update with the NEW sequential number
                if ($gen->assign_identity_to_user($user_id, true, $new_num)) {
                    $processed++;
                }
                
                // Update the global counter to stay in sync for future new registrations
                $all_counters[$role] = $new_num;
            }
        }

        update_option('mia_counters', $all_counters);
        
        wp_redirect(add_query_arg(['page' => 'mia-settings', 'synced' => $processed], admin_url('admin.php')));
        exit;
    }

    public function render_page() {
        $roles = wp_roles()->get_names();
        $settings = get_option('mia_role_settings', []);
        ?>
        <div class="wrap">
            <h1>Identity Control Panel</h1>

            <?php if(isset($_GET['synced'])): ?>
                <div class="updated notice is-dismissible">
                    <p>Success! <?php echo intval($_GET['synced']); ?> users have been re-indexed starting from your "Start No." settings.</p>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('mia_group'); ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Role</th><th>Active</th><th>Prefix</th><th>Year</th><th>Dash (-)</th><th>Padding</th><th>Start Seq</th>
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
                <?php submit_button('Save Configuration'); ?>
            </form>

            <div class="card" style="margin-top:20px; padding:20px; border-left: 4px solid #d63638;">
                <h2 style="color: #d63638;">Full Reset & Re-index</h2>
                <p>Use this if you changed the <strong>Start Seq</strong> or <strong>Prefix</strong> and want all existing users to follow the new order.</p>
                <p><em>Warning: This will change the ID of every existing user to match the new sequence.</em></p>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=mia-settings&mia_action=force_sync_all'), 'mia_sync_action'); ?>" 
                   class="button button-secondary" 
                   onclick="return confirm('This will re-calculate every user ID starting from your Start Seq. Are you sure?');">
                   Reset & Force Sync All Users
                </a>
            </div>
        </div>
        <?php
    }
}