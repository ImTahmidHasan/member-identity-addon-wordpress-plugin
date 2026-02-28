<?php
class MIA_Profile_Integration {
    public function __construct() {
        add_action('um_after_profile_fields', [$this, 'um_view']);
        add_action('show_user_profile', [$this, 'wp_view']);
        add_action('edit_user_profile', [$this, 'wp_view']);
    }

    public function um_view() {
        $id = get_user_meta(um_profile_id(), 'member_identity', true);
        if ($id) {
            echo '<div class="um-field" style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">';
            echo '<div class="um-field-label"><label>Member Identity</label></div>';
            echo '<div class="um-field-area"><strong>'.esc_html($id).'</strong></div>';
            echo '</div>';
        }
    }

    public function wp_view($user) {
        $id = get_user_meta($user->ID, 'member_identity', true);
        ?>
        <h2>Member Identity</h2>
        <table class="form-table">
            <tr>
                <th><label>Unique ID</label></th>
                <td><input type="text" value="<?php echo esc_attr($id ?: 'Not Assigned'); ?>" readonly disabled class="regular-text"></td>
            </tr>
        </table>
        <?php
    }
}