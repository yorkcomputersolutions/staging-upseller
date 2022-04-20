<?php

defined( 'ABSPATH' ) || exit;

/**
 * Output the login page header.
 *
 * @since 2.1.0
 *
 * @global string      $error         Login error message set by deprecated pluggable wp_login() function
 *                                    or plugins replacing it.
 * @global bool|string $interim_login Whether interim login modal is being displayed. String 'success'
 *                                    upon successful login.
 * @global string      $action        The action that brought the visitor to the login page.
 *
 * @param string   $title    Optional. WordPress login Page title to display in the `<title>` element.
 *                           Default 'Log In'.
 * @param string   $message  Optional. Message to display in header. Default empty.
 * @param WP_Error $wp_error Optional. The error to pass. Default is a WP_Error instance.
 */
function stagingup_login_header( $title = 'Log In', $message = '', $wp_error = null ) {
	global $error, $interim_login, $action;

	// Don't index any of these forms.
	add_filter( 'wp_robots', 'wp_robots_sensitive_page' );
	add_action( 'login_head', 'wp_strict_cross_origin_referrer' );

	add_action( 'login_head', 'wp_login_viewport_meta' );

	if ( ! is_wp_error( $wp_error ) ) {
		$wp_error = new WP_Error();
	}

	// Shake it!
	$shake_error_codes = array( 'empty_password', 'empty_email', 'invalid_email', 'invalidcombo', 'empty_username', 'invalid_username', 'incorrect_password', 'retrieve_password_email_failure' );
	/**
	 * Filters the error codes array for shaking the login form.
	 *
	 * @since 3.0.0
	 *
	 * @param string[] $shake_error_codes Error codes that shake the login form.
	 */
	$shake_error_codes = apply_filters( 'shake_error_codes', $shake_error_codes );

	if ( $shake_error_codes && $wp_error->has_errors() && in_array( $wp_error->get_error_code(), $shake_error_codes, true ) ) {
		add_action( 'login_footer', 'wp_shake_js', 12 );
	}

	$login_title = get_bloginfo( 'name', 'display' );

	/* translators: Login screen title. 1: Login screen name, 2: Network or site name. */
	$login_title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), $title, $login_title );


	/**
	 * Filters the title tag content for login page.
	 *
	 * @since 4.9.0
	 *
	 * @param string $login_title The page title, with extra context added.
	 * @param string $title       The original page title.
	 */
	$login_title = apply_filters( 'login_title', $login_title, $title );

	?><!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
	<title><?php echo $login_title; ?></title>
	<?php

	wp_enqueue_style( 'login' );

	/*
	 * Remove all stored post data on logging out.
	 * This could be added by add_action('login_head'...) like wp_shake_js(),
	 * but maybe better if it's not removable by plugins.
	 */
	if ( 'loggedout' === $wp_error->get_error_code() ) {
		?>
		<script>if("sessionStorage" in window){try{for(var key in sessionStorage){if(key.indexOf("wp-autosave-")!=-1){sessionStorage.removeItem(key)}}}catch(e){}};</script>
		<?php
	}

	/**
	 * Enqueue scripts and styles for the login page.
	 *
	 * @since 3.1.0
	 */
	do_action( 'login_enqueue_scripts' );

	/**
	 * Fires in the login page header after scripts are enqueued.
	 *
	 * @since 2.1.0
	 */
	do_action( 'login_head' );

    $classes = array( 'login-action-' . $action, 'wp-core-ui' );

    if ( is_rtl() ) {
        $classes[] = 'rtl';
    }

    $classes[] = ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_locale() ) ) );

    /**
     * Filters the login page body classes.
     *
     * @since 3.5.0
     *
     * @param string[] $classes An array of body classes.
     * @param string   $action  The action that brought the visitor to the login page.
     */
    $classes = apply_filters( 'login_body_class', $classes, $action );


    $options = get_option( 'staging_upseller', array() );
    $login_body_color = isset( $options['login_body_color'] ) ? $options['login_body_color'] : '#fff';
    ?>
    </head>
    <body class="login no-js <?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="background-color: <?php echo esc_attr( $login_body_color ); ?>" >
    <script type="text/javascript">
        document.body.className = document.body.className.replace('no-js','js');
    </script>
    <?php
    /**
     * Fires in the login page header after the body tag is opened.
     *
     * @since 4.6.0
     */
    do_action( 'login_header' );
} // End of login_header().


/**
 * Outputs the footer for the login page.
 *
 * @since 3.1.0
 *
 * @global bool|string $interim_login Whether interim login modal is being displayed. String 'success'
 *                                    upon successful login.
 */
function stagingup_login_footer() {
	/**
	 * Fires in the login page footer.
	 *
	 * @since 3.1.0
	 */
	do_action( 'login_footer' );

	?>
	<div class="clear"></div>
	</body>
	</html>
    <?php
}

//
// Main.
//

$action = 'login';

nocache_headers();

header( 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=' . get_bloginfo( 'charset' ) );

if ( defined( 'RELOCATE' ) && RELOCATE ) { // Move flag is set.
	if ( isset( $_SERVER['PATH_INFO'] ) && ( $_SERVER['PATH_INFO'] !== $_SERVER['PHP_SELF'] ) ) {
		$_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );
	}

	$url = dirname( set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ) );

	if ( get_option( 'siteurl' ) !== $url ) {
		update_option( 'siteurl', $url );
	}
}

// Set a cookie now to see if they are supported by the browser.
$secure = ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) );
setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN, $secure );

if ( SITECOOKIEPATH !== COOKIEPATH ) {
	setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN, $secure );
}

if ( isset( $_GET['wp_lang'] ) ) {
	setcookie( 'wp_lang', sanitize_text_field( $_GET['wp_lang'] ), 0, COOKIEPATH, COOKIE_DOMAIN, $secure );
}

/**
 * Fires when the login form is initialized.
 */
do_action( 'login_init' );


// Login specific
$secure_cookie   = '';
$customize_login = isset( $_REQUEST['customize-login'] );

if ( $customize_login ) {
    wp_enqueue_script( 'customize-base' );
}

if ( isset( $_REQUEST['redirect_to'] ) ) {
    $redirect_to = $_REQUEST['redirect_to'];
    // Redirect to HTTPS if user wants SSL.
    if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) ) {
        $redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
    }
} else {
    $redirect_to = admin_url();
}

$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

$user = wp_signon( array(), $secure_cookie );

if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
    if ( headers_sent() ) {
        $user = new WP_Error(
            'test_cookie',
            sprintf(
                /* translators: 1: Browser cookie documentation URL, 2: Support forums URL. */
                __( '<strong>Error</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.' ),
                __( 'https://wordpress.org/support/article/cookies/' ),
                __( 'https://wordpress.org/support/forums/' )
            )
        );
    } elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) {
        // If cookies are disabled, we can't log in even with a valid user and password.
        $user = new WP_Error(
            'test_cookie',
            sprintf(
                /* translators: %s: Browser cookie documentation URL. */
                __( '<strong>Error</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.' ),
                __( 'https://wordpress.org/support/article/cookies/#enable-cookies-in-your-browser' )
            )
        );
    }
}

$requested_redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
/**
 * Filters the login redirect URL.
 *
 * @since 3.0.0
 *
 * @param string           $redirect_to           The redirect destination URL.
 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
 */
$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );

if ( ! is_wp_error( $user ) && ! $reauth ) {
    // Check if it is time to add a redirect to the admin email confirmation screen.
    if ( is_a( $user, 'WP_User' ) && $user->exists() && $user->has_cap( 'manage_options' ) ) {
        $admin_email_lifespan = (int) get_option( 'admin_email_lifespan' );

        // If `0` (or anything "falsey" as it is cast to int) is returned, the user will not be redirected
        // to the admin email confirmation screen.
        /** This filter is documented in wp-login.php */
        $admin_email_check_interval = (int) apply_filters( 'admin_email_check_interval', 6 * MONTH_IN_SECONDS );

        if ( $admin_email_check_interval > 0 && time() > $admin_email_lifespan ) {
            $redirect_to = add_query_arg(
                array(
                    'action'  => 'confirm_admin_email',
                    'wp_lang' => get_user_locale( $user ),
                ),
                wp_login_url( $redirect_to )
            );
        }
    }

    if ( ( empty( $redirect_to ) || 'wp-admin/' === $redirect_to || admin_url() === $redirect_to ) ) {
        // If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
        if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) ) {
            $redirect_to = user_admin_url();
        } elseif ( is_multisite() && ! $user->has_cap( 'read' ) ) {
            $redirect_to = get_dashboard_url( $user->ID );
        } elseif ( ! $user->has_cap( 'edit_posts' ) ) {
            $redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();
        }

        wp_redirect( $redirect_to );
        exit;
    }

    wp_safe_redirect( $redirect_to );
    exit;
}

$errors = $user;
// Clear errors if loggedout is set.
if ( ! empty( $_GET['loggedout'] ) || $reauth ) {
    $errors = new WP_Error();
}

if ( empty( $_POST ) && $errors->get_error_codes() === array( 'empty_username', 'empty_password' ) ) {
    $errors = new WP_Error( '', '' );
}

// Some parts of this script use the main login form to display a message.
if ( isset( $_GET['loggedout'] ) && $_GET['loggedout'] ) {
    $errors->add( 'loggedout', __( 'You are now logged out.' ), 'message' );
} elseif ( isset( $_GET['registration'] ) && 'disabled' === $_GET['registration'] ) {
    $errors->add( 'registerdisabled', __( '<strong>Error</strong>: User registration is currently not allowed.' ) );
} elseif ( strpos( $redirect_to, 'about.php?updated' ) ) {
    $errors->add( 'updated', __( '<strong>You have successfully updated WordPress!</strong> Please log back in to see what&#8217;s new.' ), 'message' );
} elseif ( isset( $_GET['redirect_to'] ) && false !== strpos( $_GET['redirect_to'], 'wp-admin/authorize-application.php' ) ) {
    $query_component = wp_parse_url( $_GET['redirect_to'], PHP_URL_QUERY );
    $query           = array();
    if ( $query_component ) {
        parse_str( $query_component, $query );
    }

    if ( ! empty( $query['app_name'] ) ) {
        /* translators: 1: Website name, 2: Application name. */
        $message = sprintf( 'Please log in to %1$s to authorize %2$s to connect to your account.', get_bloginfo( 'name', 'display' ), '<strong>' . esc_html( $query['app_name'] ) . '</strong>' );
    } else {
        /* translators: %s: Website name. */
        $message = sprintf( 'Please log in to %s to proceed with authorization.', get_bloginfo( 'name', 'display' ) );
    }

    $errors->add( 'authorize_application', $message, 'message' );
}

/**
 * Filters the login page errors.
 *
 * @since 3.6.0
 *
 * @param WP_Error $errors      WP Error object.
 * @param string   $redirect_to Redirect destination URL.
 */
$errors = apply_filters( 'wp_login_errors', $errors, $redirect_to );

// Clear any stale cookies.
if ( $reauth ) {
    wp_clear_auth_cookie();
}

stagingup_login_header( __( 'Log In' ), '', $errors );

$user_login = '';
if ( isset( $_POST['log'] ) ) {
    $user_login = ( 'incorrect_password' === $errors->get_error_code() || 'empty_password' === $errors->get_error_code() ) ? esc_attr( wp_unslash( $_POST['log'] ) ) : '';
}

$rememberme = ! empty( $_POST['rememberme'] );

wp_enqueue_script( 'user-profile' );


function stagingup_login_form_header( $data = array() ) {
    $data['title'] = isset( $data['title'] ) ? $data['title'] : 'Log In';
    $data['message'] = isset( $data['message'] ) ? $data['message'] : '';
    $data['wp_error'] = isset( $data['wp_error'] ) ? $data['wp_error'] : null;
    
    $options = get_option( 'staging_upseller', array() );

    // LOGIN FORM HEADER
    $login_header_url = $options['login_headerurl'] ?? '';

    /**
     * Filters link URL of the header logo above login form.
     *
     * @since 2.1.0
     *
     * @param string $login_header_url Login header logo URL.
     */
    $login_header_url = apply_filters( 'login_headerurl', $login_header_url );

    $login_header_title = $options['login_headertext'] ?? '';

    /**
     * Filters the title attribute of the header logo above login form.
     *
     * @since 2.1.0
     * @deprecated 5.2.0 Use {@see 'login_headertext'} instead.
     *
     * @param string $login_header_title Login header logo title attribute.
     */
    $login_header_title = apply_filters_deprecated(
        'login_headertitle',
        array( $login_header_title ),
        '5.2.0',
        'login_headertext',
        __( 'Usage of the title attribute on the login logo is not recommended for accessibility reasons. Use the link text instead.' )
    );

    $login_header_text = $login_header_title;

    /**
     * Filters the link text of the header logo above the login form.
     *
     * @since 5.2.0
     *
     * @param string $login_header_text The login header logo link text.
     */
    $login_header_text = apply_filters( 'login_headertext', $login_header_text );
    ?>
    <div id="login">
        <?php
        $options = get_option( 'staging_upseller' );
        $login_logo = $options['login_logo'] ?? get_site_url() . '/wp-admin/images/wordpress-logo.svg';

        if ( isset( $login_logo ) ) {
            ?>
            <div class="logo-wrap">
                <?php
                if ( ! empty( $login_header_url ) ) {
                    ?>
                    <a class="logo-anchor" href="<?php echo esc_url( $login_header_url ); ?>">
                    <?php
                }
                ?>
                <img class="logo" src="<?php echo esc_attr( $login_logo ); ?>" />
                <?php
                if ( ! empty( $login_header_url ) ) {
                    ?>
                    </a>
                    <?php
                }
                ?>
            </div>
            <?php
        }

        if ( ! empty( $login_header_text ) ) {
            ?>
            <h1>
                <?php if ( ! empty( $login_header_url ) ) { ?>

                <a href="<?php echo esc_url( $login_header_url ); ?>"> <!-- opening anchor -->

                <?php } ?>

                    <?php echo esc_html( $login_header_text ); ?>

                <?php if ( ! empty( $login_header_url ) ) { ?>

                </a> <!-- closing anchor -->

                <?php } ?>
            </h1>
            <?php
        }



        /**
         * Filters the message to display above the login form.
         *
         * @since 2.1.0
         *
         * @param string $message Login message text.
         */
        $message = apply_filters( 'login_message', $data['message'] );

        if ( ! empty( $message ) ) {
            echo $message . "\n";
        }

        // In case a plugin uses $error rather than the $wp_errors object.
        if ( ! empty( $error ) ) {
            $wp_error->add( 'error', $error );
            unset( $error );
        }

        if ( $data['wp_error']->has_errors() ) {
            $errors   = '';
            $messages = '';

            foreach ( $data['wp_error']->get_error_codes() as $code ) {
                $severity = $data['wp_error']->get_error_data( $code );
                foreach ( $data['wp_error']->get_error_messages( $code ) as $error_message ) {
                    if ( 'message' === $severity ) {
                        $messages .= '	' . $error_message . "<br />\n";
                    } else {
                        $errors .= '	' . $error_message . "<br />\n";
                    }
                }
            }

            if ( ! empty( $errors ) ) {
                $options = get_option( 'staging_upseller' );

                /**
                 * Filters the error messages displayed above the login form.
                 *
                 * @since 2.1.0
                 *
                 * @param string $errors Login error message.
                 */
                echo '<div id="login_error">' . apply_filters( 'login_errors', $errors ) . "</div>\n";
            }

            if ( ! empty( $messages ) ) {
                /**
                 * Filters instructional messages displayed above the login form.
                 *
                 * @since 2.5.0
                 *
                 * @param string $messages Login messages.
                 */
                echo '<p class="message">' . apply_filters( 'login_messages', $messages ) . "</p>\n";
            }
        }
}

function stagingup_login_form_body( $data = array() ) {
    $data['title'] = isset( $data['title'] ) ? $data['title'] : 'Log In';
    $data['message'] = isset( $data['message'] ) ? $data['message'] : '';
    $data['wp_error'] = isset( $data['wp_error'] ) ? $data['wp_error'] : null;

    if ( $data['wp_error']->has_errors() ) {
        $aria_describedby_error = ' aria-describedby="login_error"';
    } else {
        $aria_describedby_error = '';
    }

    $options = get_option( 'staging_upseller' );
    $login_input_label_placement = isset( $options['login_input_label_placement'] ) ? $options['login_input_label_placement'] : 'above_input';

    ?>
    <form class="stagingup-login-form" name="loginform" id="loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
        <p>
            <?php
            if ( $login_input_label_placement == 'above_input' ) {
                ?>
                <label for="user_login"><?php echo esc_html( $options['login_username_label'] ); ?></label>
                <?php
            }
            ?>
            <input type="text" name="log" id="user_login"<?php echo $aria_describedby_error; ?> class="input" value="<?php echo esc_attr( $data['user_login'] ); ?>" size="20" autocapitalize="off" <?php echo $login_input_label_placement === 'placeholder' ? 'placeholder="' . esc_html( $options['login_username_label'] ?? 'Username or Email Address' ) . '"' : ''; ?> />
        </p>

        <div class="user-pass-wrap">
            <?php
            if ( $login_input_label_placement == 'above_input' ) {
                ?>
                <label for="user_pass" style="color: <?php echo esc_attr( $login_input_label_color ); ?>" ><?php echo esc_html( $options['login_password_label'] ); ?></label>
                <?php
            }
            ?>
            <div class="wp-pwd">
                <input type="password" name="pwd" id="user_pass"<?php echo $aria_describedby_error; ?> class="input password-input" value="" size="20" <?php echo $login_input_label_placement === 'placeholder' ? 'placeholder="' . esc_html( $options['login_password_label'] ?? 'Password' ) . '"' : ''; ?> />
            </div>
        </div>
        <?php

        /**
         * Fires following the 'Password' field in the login form.
         *
         * @since 2.1.0
         */
        do_action( 'login_form' );

        $login_rememberme_label = $options['login_rememberme_label'] ?? 'Remember Me';
        ?>
        <p class="forgetmenot"><input name="rememberme" type="checkbox" id="rememberme" value="forever" <?php checked( $data['rememberme'] ); ?> /> <label for="rememberme"><?php esc_html_e( $login_rememberme_label ); ?></label></p>
        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( $options['login_log_in_button_label'] ?? 'Log In' ); ?>" />

            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $data['redirect_to'] ); ?>" />

            <input type="hidden" name="testcookie" value="1" />
        </p>
    </form>
    <?php
}

function stagingup_login_form_footer() {
        if ( apply_filters( 'login_display_language_dropdown', true ) ) {
            $languages = get_available_languages();

            if ( ! empty( $languages ) ) {
                ?>
                <div class="language-switcher">
                    <form id="language-switcher" action="" method="get">

                        <label for="language-switcher-locales">
                            <span class="dashicons dashicons-translation" aria-hidden="true"></span>
                            <span class="screen-reader-text"><?php _e( 'Language' ); ?></span>
                        </label>

                        <?php
                        $args = array(
                            'id'                          => 'language-switcher-locales',
                            'name'                        => 'wp_lang',
                            'selected'                    => determine_locale(),
                            'show_available_translations' => false,
                            'explicit_option_en_us'       => true,
                            'languages'                   => $languages,
                        );

                        /**
                         * Filters default arguments for the Languages select input on the login screen.
                         *
                         * @since 5.9.0
                         *
                         * @param array $args Arguments for the Languages select input on the login screen.
                         */
                        wp_dropdown_languages( apply_filters( 'login_language_dropdown_args', $args ) );
                        ?>

                        <?php if ( isset( $_GET['redirect_to'] ) && '' !== $_GET['redirect_to'] ) { ?>
                            <input type="hidden" name="redirect_to" value="<?php echo esc_url_raw( $_GET['redirect_to'] ); ?>" />
                        <?php } ?>

                        <?php if ( isset( $_GET['action'] ) && '' !== $_GET['action'] ) { ?>
                            <input type="hidden" name="action" value="<?php echo esc_attr( $_GET['action'] ); ?>" />
                        <?php } ?>

                            <input type="submit" class="button" value="<?php esc_attr_e( 'Change' ); ?>">

                    </form>
                </div>
            <?php } ?>
        <?php } ?>


        <?php
        $options = get_option( 'staging_upseller', array() );

        $login_log_in_button_after_text = $options['login_log_in_button_after_text'] ?? '';
        $login_log_in_button_after_url = $options['login_log_in_button_after_url'] ?? '';

        if ( ! empty( $login_log_in_button_after_text ) ) {
            ?>
            <p class="log-in-button-after-text">
                <?php if ( ! empty( $login_log_in_button_after_url ) ) { ?><a href="<?php echo esc_url( $login_log_in_button_after_url ); ?>"><?php } ?>
                    <?php echo esc_html( $login_log_in_button_after_text ); ?></p>
                <?php if ( ! empty( $login_log_in_button_after_url ) ) { ?></a><?php } ?>
            <?php
        }
        ?>
    </div> <!-- #login -->
    <?php
}

function stagingup_login_form( $data = array() ) {
    $data['title'] = isset( $data['title'] ) ? $data['title'] : 'Log In';
    $data['message'] = isset( $data['message'] ) ? $data['message'] : '';
    $data['wp_error'] = isset( $data['wp_error'] ) ? $data['wp_error'] : null;

    stagingup_login_form_header( $data );

    stagingup_login_form_body( $data );

    stagingup_login_form_footer();
}


$options = get_option( 'staging_upseller' );

$login_page_areas = array();

function stagingup_2d_to_1d($width, $x, $y) {
    return $width * $y + $x;
}

// Let's group alike grid cells together
$area_spans = array();
$indexes_to_not_make_boxes_at = array();
$area_rows = 3;
$area_cols = 3;
for ( $x = 0; $x < $area_cols; $x++ ) {
    for ( $y = 0; $y < $area_rows; $y++ ) {
        $this_area_index = stagingup_2d_to_1d($area_cols, $x, $y);

        // If this cell isn't already ignored
        if ( ! in_array( $this_area_index, $indexes_to_not_make_boxes_at ) ) {
            $width_found = 1;
            $height_found = 1;

            for ( $ox = $x; $ox < $area_cols; $ox++ ) {

                for ( $oy = $y; $oy < $area_rows; $oy++ ) {
                    $other_area_index = stagingup_2d_to_1d( $area_cols, $ox, $oy);

                    if ( $this_area_index != $other_area_index &&
                        $options['login_layout_area_' . $other_area_index] == $options['login_layout_area_' . $this_area_index] ) {
                        if ( $ox > $x ) {
                            $width_found = ($ox - $x) + 1;
                        }
    
                        if ( $oy > $y ) {
                            $height_found = ($oy - $y) + 1;
                        }    

                        $indexes_to_not_make_boxes_at[] = $other_area_index;
                    }
                }
            }

            $area_spans[] = array(
                'index'  => $this_area_index,
                'width'  => $width_found,
                'height' => $height_found
            );
        }
    }
}

?>
<div class="stagingup-login-page">
    <?php
    for ( $i = 0; $i < 9; $i++ ) {
        if ( ! in_array( $i, $indexes_to_not_make_boxes_at ) ) {
            $area_span_data = null;
            foreach ( $area_spans as $area_span ) {
                if ( $area_span['index'] == $i ) {
                    $area_span_data = $area_span;
                }
            }

            
            $area_classes = '';
            if ( $options[ 'login_layout_area_' . $i ] == 'login_sidebar' ) {
                $area_classes .= 'login-sidebar ';
            }

            $ad_area_index = null;
            if ( strpos( $options[ 'login_layout_area_' . $i ], 'ad_area_' ) !== false ) {
                $area_classes .= 'login-ad-area ';
                $ad_area_index = str_replace( 'ad_area_', '', $options[ 'login_layout_area_' . $i ] );

                $area_classes .= 'login-ad-area-' . $ad_area_index . ' ';
            }


            $area_styles = '';
            if ( strpos( $area_classes, 'login-sidebar' ) !== false ) {
                $area_styles .= 'background-color: ' . $options['login_sidebar_color'] . ';';
            }
            else if ( strpos( $area_classes, 'login-ad-area' ) !== false ) {
                $area_background_color = isset( $options['login_ad_area_' . $ad_area_index . '_background_color'] ) ? $options['login_ad_area_' . $ad_area_index . '_background_color'] : 'transparent';

                $area_styles .= 'background-color: ' . $area_background_color . ';';
            }

            $area_styles = trim( $area_styles );
            $area_classes = trim( $area_classes );
            ?>
            <div class="stagingup-login-page-area span-width-<?php echo esc_attr( $area_span_data['width'] ); ?> span-height-<?php echo esc_attr( $area_span_data['height'] ); ?> <?php echo esc_attr( $area_classes ); ?>" style="<?php echo esc_attr( $area_styles ); ?>" >
                <?php                
                if ( $options[ 'login_layout_area_' . $i ] === 'login_sidebar' ) {
                    echo stagingup_login_form( array(
                        'title'    => __( 'Log In' ),
                        'message'  => '',
                        'wp_error' => $errors,
                        'action'   => $action,
                        'user_login' => $user_login,
                        'rememberme' => $rememberme,
                        'redirect_to' => $redirect_to
                    ) );
                }
                else if ( strpos( $options[ 'login_layout_area_' . $i ], 'ad_area_' ) !== false ) {
                    $area_scrolling_enabled = isset( $options['login_ad_area_' . $ad_area_index . '_scrolling_enabled'] ) ? ( $options['login_ad_area_' . $ad_area_index . '_scrolling_enabled'] === 'on' ? true : false ) : false;

                    $iframe_classes = 'stagingup-login-iframe ';
                    $iframe_classes .= ! $area_scrolling_enabled ? 'scrolling-disabled' : '';
                    $iframe_classes = trim( $iframe_classes );
                    ?>
                    <iframe class="<?php echo esc_attr( $iframe_classes ); ?>" src="<?php echo esc_url( $options['login_ad_area_' . $ad_area_index . '_iframe_url'] ); ?>" frameborder="0" scrolling="<?php echo $area_scrolling_enabled ? 'yes' : 'no'; ?>"></iframe>
                    <?php
                    if ( ! $area_scrolling_enabled ) {
                        ?>
                        <div class="stagingup-login-iframe-overlay"></div>
                        <?php
                    }
                }
                ?>
            </div>
            <?php
        }
    }
    ?>
</div>
<?php


$login_script  = 'function wp_attempt_focus() {';
$login_script .= 'setTimeout( function() {';
$login_script .= 'try {';

if ( isset( $user_login ) ) {
    if ( $user_login ) {
        $login_script .= 'd = document.getElementById( "user_pass" ); d.value = "";';
    }
} else {
    $login_script .= 'd = document.getElementById( "user_login" );';

    if ( $errors->get_error_code() === 'invalid_username' ) {
        $login_script .= 'd.value = "";';
    }
}

$login_script .= 'd.focus(); d.select();';
$login_script .= '} catch( er ) {}';
$login_script .= '}, 200);';
$login_script .= "}\n"; // End of wp_attempt_focus().

/**
 * Filters whether to print the call to `wp_attempt_focus()` on the login screen.
 *
 * @since 4.8.0
 *
 * @param bool $print Whether to print the function call. Default true.
 */
if ( apply_filters( 'enable_login_autofocus', true ) ) {
    $login_script .= "wp_attempt_focus();\n";
}

// Run `wpOnload()` if defined.
$login_script .= "if ( typeof wpOnload === 'function' ) { wpOnload() }";

?>
<script type="text/javascript">
    <?php echo $login_script; ?>
</script>
<?php

stagingup_login_footer();
