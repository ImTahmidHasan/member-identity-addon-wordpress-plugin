<?php
class MIA_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'handle_sync_actions']);
    }

    public function add_menu() {
        add_submenu_page('ultimatemember', 'Member Identity', 'Identity Addon', 'manage_options', 'mia-settings', [$this, 'render_page']);
    }

    public function register_settings() {
        register_setting('mia_group', 'mia_role_settings');
    }

    public function handle_sync_actions() {
        if (!isset($_GET['mia_action']) || !current_user_can('manage_options')) return;
        check_admin_referer('mia_sync_action');

        $generator = new MIA_Identity_Generator();
        $mode = $_GET['mia_action']; // 'sync_missing' or 'force_update'
        
        $args = ['number' => 50];
        if ($mode === 'sync_missing') {
            $args['meta_query'] = [['key' => 'member_identity', 'compare' => 'NOT EXISTS']];
        }

        $query = new WP_User_Query($args);
        $processed = 0;

        foreach ($query->get_results() as $u) {
            $force = ($mode === 'force_update');
            if ($generator->assign_identity_to_user($u->ID, $force)) {
                $processed++;
            }
        }

        wp_redirect(add_query_arg(['processed' => $processed, 'page' => 'mia-settings'], admin_url('admin.php')));
        exit;
    }

    public function render_page() {
        $roles = wp_roles()->get_names();
        $settings = get_option('mia_role_settings', []);
        ?>
        <div class="wrap">
            <h1>Member Identity Control Panel</h1>
            <hr>
            
            <form method="post" action="options.php">
                <?php settings_fields('mia_group'); ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Role Name</th><th>Active</th><th>Prefix</th><th>Show Year</th><th>Padding</th><th>Start Seq</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $slug => $name): 
                            $v = $settings[$slug] ?? ['padding'=>5, 'start_number'=>1]; ?>
                            <tr>
                                <td><strong><?php echo esc_html($name); ?></strong></td>
                                <td><input type="checkbox" name="mia_role_settings[<?php echo $slug; ?>][enabled]" <?php checked(!empty($v['enabled'])); ?>></td>
                                <td><input type="text" name="mia_role_settings[<?php echo $slug; ?>][prefix]" value="<?php echo esc_attr($v['prefix'] ?? ''); ?>" placeholder="e.g. EMP-"></td>
                                <td><input type="checkbox" name="mia_role_settings[<?php echo $slug; ?>][include_year]" <?php checked(!empty($v['include_year'])); ?>></td>
                                <td><input type="number" name="mia_role_settings[<?php echo $slug; ?>][padding]" value="<?php echo $v['padding']; ?>" style="width:60px"></td>
                                <td><input type="number" name="mia_role_settings[<?php echo $slug; ?>][start_number]" value="<?php echo $v['start_number']; ?>" style="width:80px"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button('Save Role Configurations'); ?>
            </form>

            <div class="card" style="margin-top:30px; padding:20px; border-left: 4px solid #2271b1;">
                <h2>Maintenance & Synchronization</h2>
                <p>Use these tools after changing prefixes or adding year formats.</p>
                
                <div style="display:flex; gap:10px;">
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=mia-settings&mia_action=sync_missing'), 'mia_sync_action'); ?>" class="button button-primary">
                        Assign Missing IDs (Batch 50)
                    </a>
                    
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=mia-settings&mia_action=force_update'), 'mia_sync_action'); ?>" class="button button-secondary" onclick="return confirm('This will overwrite current IDs with the new Prefix/Year format. Continue?');">
                        Force Update Existing IDs (Batch 50)
                    </a>
                </div>
                <p><small><em>Note: "Force Update" will keep the user's original sequence number but update the Prefix and Year prefix.</em></small></p>
            </div>
        </div>
        <?php
    }
}