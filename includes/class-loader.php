<?php
class MIA_Loader {
    public function run() {
        $this->load_dependencies();
        $this->init_modules();
    }

    private function load_dependencies() {
        require_once MIA_PATH . 'includes/class-identity-generator.php';
        require_once MIA_PATH . 'includes/class-admin-settings.php';
        require_once MIA_PATH . 'includes/class-admin-column.php';
        require_once MIA_PATH . 'includes/class-profile-integration.php';
    }

    private function init_modules() {
        new MIA_Identity_Generator();
        new MIA_Admin_Settings();
        new MIA_Admin_Column();
        new MIA_Profile_Integration();
    }
}