<?php

defined( 'ABSPATH' ) || exit;

/**
 * StagingUp_AJAX
 */
class StagingUp_AJAX {
    public static function init() {
        add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
        add_action( 'template_redirect', array( __CLASS__, 'do_stagingup_ajax' ), 0 );
        self::add_ajax_events();
    }

    private static function stagingup_ajax_headers() {
        if ( ! headers_sent() ) {
            send_origin_headers();
            send_nosniff_header();
            header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
            header( 'X-Robots-Tag: noindex' );
            status_header( 200 );
        }
        else if ( WP_DEBUG ) {
            headers_sent( $file, $line );
            trigger_error( "stagingup_ajax_headers cannot set headers - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
        }
    }

    public static function do_stagingup_ajax() {
        global $wp_query;

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if ( ! empty( $_GET['stagingup-ajax'] ) ) {
            $wp_query->set( 'stagingup-ajax', sanitize_text_field( wp_unslash( $_GET['esh-ajax'] ) ) );
        }

        $action = $wp_query->get( 'stagingup-ajax' );

        if ( $action ) {
            self::stagingup_ajax_headers();
            $action = sanitize_text_field( 'stagingup_ajax_' . $action );
            wp_die();
        }
        // phpcs:enable
    }

    /**
     * Admin AJAX
     */
    public static function add_ajax_events() {
        $ajax_events_nopriv = array(
        );

		foreach ( $ajax_events_nopriv as $ajax_event ) {
		}


        $ajax_events = array(
            'import_settings',
            'export_settings',
        );

        foreach ( $ajax_events as $ajax_event ) {
            add_action( 'wp_ajax_stagingup_' . $ajax_event, array( __CLASS__, $ajax_event ) );
        }
    }

    public static function import_settings() {
        if ( ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'stagingup_import_settings_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_send_json_error( 'bad_nonce' );
			wp_die();
        }

        $user = wp_get_current_user();
        if ( $user === 0 ) {
            wp_send_json_error(false);
        }
        
        if ( $user->has_cap( 'manage_options' ) ) {
            $options = isset( $_POST['options'] ) ? sanitize_text_field( wp_unslash( $_POST['options'] ) ) : '';

            if ( empty( $options ) ) {
                wp_send_json_error(false);
                die;
            }

            update_option( 'staging_upseller', json_decode( $options, true ) );

            wp_send_json_success($options);
            die;
        }
    }


    public static function export_settings() {
        if ( ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'stagingup_export_settings_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			wp_send_json_error( 'bad_nonce' );
			wp_die();
        }

        $user = wp_get_current_user();
        if ( $user === 0 ) {
            wp_send_json_error(false);
            die;
        }
        
        if ( $user->has_cap( 'manage_options' ) ) {
            $options = get_option( 'staging_upseller', array() );
            
            wp_send_json_success($options);
            die;
        }
    }
}

StagingUp_AJAX::init();