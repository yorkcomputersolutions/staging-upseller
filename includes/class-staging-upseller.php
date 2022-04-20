<?php
/**
 * Staging Upseller class.
 */
class Staging_Upseller {
    /**
     * Stores a static instance of this plugin class
     * 
     * @since 1.0
     */
    protected static $_instance = null;

    /**
     * Retrieves instance of the plugin
     * 
     * @since 1.0
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     * 
     * Add actions for methods that define constants and load includes.
     * 
     * @since 1.0
     * @access public
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Checks if the current request is a given type.
     * 
     * @param string $type The type of request ot check.
     * 
     * @since 1.0
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin': {
                return is_admin();
                break;
            }

            case 'ajax': {
                return defined( 'DOING_AJAX' );
                break;
            }

            case 'cron': {
                return defined( 'DOING_CRON' );
                break;
            }

            case 'frontend': {
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
                break;
            }
        }
    }

    /**
	 * Checks if the current request is a REST API request.
	 * 
	 * @since 1.0
	 */
	public function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );

		return apply_filters( 'stagingup_is_rest_api_request', $is_rest_api_request );
	}

    /**
     * Include required core files.
     * 
     * @since 1.0
     */
    public function includes() {
        include_once STAGINGUP_PATH . '/includes/class-stagingup-ajax.php';

        /**
         * Admin requests.
         */
        if ( $this->is_request( 'admin' ) ) {
            include_once STAGINGUP_PATH . '/includes/admin/class-stagingup-admin.php';
        }

        if ( $this->is_request( 'frontend' ) ) {
            $this->frontend_includes();
        }
    }

    /**
     * Include required frontend files.
     * 
     * @since 1.0
     */
    public function frontend_includes() {
        include_once STAGINGUP_PATH . '/includes/class-stagingup-login-page.php';
    }

    /**
     * Registers hooks when the plugin is initialized.
     * 
     * @since 1.0
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ), 0 );
    }

    /**
     * Class method called when the WordPress init action fires.
     * 
     * @since 1.0
     */
    public function init() {
        if ( $this->is_request( 'frontend' ) ) {
            $options = get_option( 'staging_upseller', array() );

            if ( $options['show_login_to_visitors'] ?? false ) {
                global $pagenow;
                //  URL for the homepage. You can set this to the URL of any page you wish to redirect to.
                //  Redirect to the homepage, it is login page. Make sure it is not called to logout 
                $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

                if ( $pagenow == 'wp-login.php' && $action == '')
                {
                    StagingUp_Login_Page::init();
                }

                if ( ! is_user_logged_in() ) {
                    wp_redirect( wp_login_url() );
                    exit();
                }
            }
        }
    }
}
