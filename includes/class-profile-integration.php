<?php
class MIA_Profile_Integration {
    public function __construct() {
        // Prevent editing from profile
        add_action('um_after_profile_fields', [$this, 'display_identity_field']);
        add_action('show_user_profile', [$this, 'display_admin_field']);
        add_action('edit_user_profile', [$this, 'display_admin_field']);
    }

    public function display_identity_field($args) {
        $user_id = um_profile_id();
        $identity = get_user_meta($user_id, 'member_identity', true);
        if ($identity) {
            echo '<div class="um-field"><div class="um-field-label"><label>' . __('Member Identity', 'member-identity-addon') . '</label></div>';
            echo '<div class="um-field-area"><input type="text" value="' . esc_attr($identity) . '" readonly disabled /></div></div>';
        }
    }

    public function display_admin_field($user) {
        $identity = get_user_meta($user->ID, 'member_identity', true);
        ?>
        <h3><?php _e('Identity Information', 'member-identity-addon'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label><?php _e('Member Identity', 'member-identity-addon'); ?></label></th>
                <td>
                    <input type="text" value="<?php echo esc_attr($identity ?: 'Not assigned'); ?>" class="regular-text" readonly disabled />
                    <p class="description"><?php _e('This ID is permanent and cannot be changed.', 'member-identity-addon'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
}