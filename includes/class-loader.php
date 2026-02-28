<?php
class MIA_Loader {
    public function run() {
        $files = ['identity-generator', 'admin-settings', 'admin-column', 'profile-integration'];
        foreach ($files as $file) {
            require_once MIA_PATH . "includes/class-$file.php";
        }
        new MIA_Identity_Generator();
        new MIA_Admin_Settings();
        new MIA_Admin_Column();
        new MIA_Profile_Integration();
    }
}