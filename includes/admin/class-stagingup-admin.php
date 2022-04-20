<?php

defined( 'ABSPATH' ) || exit;

/**
 * StagingUp_Admin
 */
class StagingUp_Admin {
    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Include any classes needed on the admin side.
     * 
     * @since 1.0
     */
    public function includes() {
        include_once __DIR__ . '/class-stagingup-settings.php';
    }

    /**
     * Initialize admin-related functionality when the WordPress init action is fired.
     * 
     * @since 1.0
     */
    public function init() {
        $this->includes();
    }
}

return new StagingUp_Admin();