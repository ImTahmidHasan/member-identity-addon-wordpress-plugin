<?php
class MIA_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_init', [$this, 'handle_legacy_sync']);
    }

    public function add_menu() {
        add_submenu_page('ultimatemember', 'Member Identity', 'Identity Addon', 'manage_options', 'mia-settings', [$this, 'render_page']);
    }

    public function register_settings() {
        register_setting('mia_group', 'mia_role_settings');
    }

    public function handle_legacy_sync() {
        if (isset($_GET['mia_action']) && $_GET['mia_action'] === 'sync_legacy' && current_user_can('manage_options')) {
            check_admin_referer('mia_sync_action');
            
            $generator = new MIA_Identity_Generator();
            $users = get_users(['meta_query' => [['key' => 'member_identity', 'compare' => 'NOT EXISTS']], 'number' => 100]);
            
            $processed = 0;
            foreach ($users as $u) {
                if ($generator->assign_identity_to_user($u->ID)) $processed++;
            }

            wp_redirect(add_query_arg(['page' => 'mia-settings', 'processed' => $processed], admin_url('admin.php?page=mia-settings')));
            exit;
        }
    }

    public function render_page() {
        $roles = wp_roles()->get_names();
        $settings = get_option('mia_role_settings', []);
        ?>
        <div class="wrap">
            <h1>Member Identity Settings</h1>
            <?php if(isset($_GET['processed'])): ?>
                <div class="updated"><p>Processed <?php echo intval($_GET['processed']); ?> users. Repeat if more remain.</p></div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('mia_group'); ?>
                <table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
                    <thead>
                        <tr>
                            <th>Role</th><th>Enable</th><th>Prefix</th><th>Year</th><th>Padding</th><th>Start No.</th>
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
                                <td><input type="number" name="mia_role_settings[<?php echo $slug; ?>][padding]" value="<?php echo $v['padding']; ?>" style="width:60px"></td>
                                <td><input type="number" name="mia_role_settings[<?php echo $slug; ?>][start_number]" value="<?php echo $v['start_number']; ?>" style="width:80px"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>

            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>Sync Previous Users</h2>
                <p>Click below to assign identities to users registered before this plugin was installed.</p>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=mia-settings&mia_action=sync_legacy'), 'mia_sync_action'); ?>" class="button button-secondary">Process Legacy Users (Batch 100)</a>
            </div>
        </div>
        <?php
    }
}