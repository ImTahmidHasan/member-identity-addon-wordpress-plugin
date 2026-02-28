<?php
class MIA_Profile_Integration {
    public function __construct() {
        add_action('um_after_profile_fields', [$this, 'show_um']);
        add_action('show_user_profile', [$this, 'show_wp']);
        add_action('edit_user_profile', [$this, 'show_wp']);
    }

    public function show_um() {
        $id = get_user_meta(um_profile_id(), 'member_identity', true);
        if ($id) echo '<div class="um-field"><strong>Member Identity:</strong> '.esc_html($id).'</div>';
    }

    public function show_wp($user) {
        $id = get_user_meta($user->ID, 'member_identity', true);
        ?>
        <table class="form-table">
            <tr><th>Member Identity</th><td><input type="text" value="<?php echo esc_attr($id); ?>" readonly disabled class="regular-text"></td></tr>
        </table>
        <?php
    }
}