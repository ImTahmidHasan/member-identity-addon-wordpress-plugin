<?php
class MIA_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_submenu'], 20);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_submenu() {
        add_submenu_page(
            'ultimatemember',
            __('Member Identity Settings', 'member-identity-addon'),
            __('Identity Addon', 'member-identity-addon'),
            'manage_options',
            'mia-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('mia_settings_group', 'mia_role_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings']
        ]);
    }

    public function sanitize_settings($input) {
        foreach ($input as $role => $data) {
            $input[$role]['enabled'] = isset($data['enabled']);
            $input[$role]['prefix'] = sanitize_text_field($data['prefix']);
            $input[$role]['padding'] = absint($data['padding']);
            $input[$role]['start_number'] = absint($data['start_number']);
        }
        return $input;
    }

    public function render_settings_page() {
        $roles = wp_roles()->get_names();
        $settings = get_option('mia_role_settings', []);
        ?>
        <div class="wrap">
            <h1><?php _e('Member Identity Configuration', 'member-identity-addon'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('mia_settings_group'); ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Role', 'member-identity-addon'); ?></th>
                            <th><?php _e('Enable', 'member-identity-addon'); ?></th>
                            <th><?php _e('Prefix', 'member-identity-addon'); ?></th>
                            <th><?php _e('Padding', 'member-identity-addon'); ?></th>
                            <th><?php _e('Start No.', 'member-identity-addon'); ?></th>
                            <th><?php _e('Preview', 'member-identity-addon'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $slug => $name) : 
                            $val = $settings[$slug] ?? ['padding' => 5, 'start_number' => 1];
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($name); ?></strong></td>
                                <td><input type="checkbox" name="mia_role_settings[<?php echo $slug; ?>][enabled]" <?php checked(!empty($val['enabled'])); ?>></td>
                                <td><input type="text" name="mia_role_settings[<?php echo $slug; ?>][prefix]" value="<?php echo esc_attr($val['prefix'] ?? ''); ?>" placeholder="e.g. MEM-"></td>
                                <td><input type="number" name="mia_role_settings[<?php echo $slug; ?>][padding]" value="<?php echo esc_attr($val['padding']); ?>" style="width:60px"></td>
                                <td><input type="number" name="mia_role_settings[<?php echo $slug; ?>][start_number]" value="<?php echo esc_attr($val['start_number']); ?>" style="width:80px"></td>
                                <td><code><?php echo ($val['prefix'] ?? '') . (date('Y-')) . str_pad($val['start_number'], $val['padding'], '0', STR_PAD_LEFT); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}