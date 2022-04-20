<?php

defined( 'ABSPATH' ) || exit;

class StagingUp_Login_Page {
    public static function init() {
        add_action( 'login_head', array( __CLASS__, 'inject_custom_css' ), 99 );

        add_action( 'login_enqueue_scripts', array( __CLASS__, 'login_enqueue_scripts' ) );

        self::render_login_page();
    }

    /**
     * Register and enqueue login page scripts and stylesheets.
     * 
     * @since 1.0
     */
    public static function login_enqueue_scripts() {
        wp_dequeue_style( 'login' );
        wp_deregister_style( 'login' );

        wp_register_style(
            'login',
            STAGINGUP_URL . '/assets/css/login.css'
        );

        wp_enqueue_style( 'login' );
    }

    /**
     * Injects custom CSS into the login page, just before the closing </head> tag.
     * 
     * @since 1.0
     */
    public static function inject_custom_css() {
        $options = get_option( 'staging_upseller' );
        ?>
        <style type="text/css">
            <?php echo $options['login_css']; ?>
        </style>
        <?php
    }

    /**
     * Renders the login page using the login page PHP file in this plugin.
     * 
     * @since 1.0 
     */
    public static function render_login_page() {
        include_once STAGINGUP_PATH . '/includes/stagingup-login-template.php';

        die();
    }
}
