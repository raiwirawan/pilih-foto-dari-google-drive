<?php
namespace PilihFoto;

class Security {
    public static function init() {
        // Prevent clients from accessing wp-admin
        add_action( 'admin_init', array( __CLASS__, 'prevent_client_admin_access' ) );
        
        // Hide admin bar for clients
        add_action( 'after_setup_theme', array( __CLASS__, 'hide_admin_bar' ) );
        
        // Security headers
        add_action( 'send_headers', array( __CLASS__, 'add_security_headers' ) );

        // Shorten session expiration for clients/editors
        add_filter( 'auth_cookie_expiration', array( __CLASS__, 'shorten_session' ), 10, 3 );
    }

    public static function prevent_client_admin_access() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        if ( current_user_can( 'pf_view_own_gallery' ) && ! current_user_can( 'pf_manage_galleries' ) ) {
            wp_safe_redirect( site_url( '/foto/' ) );
            exit;
        }
    }

    public static function hide_admin_bar() {
        if ( current_user_can( 'pf_view_own_gallery' ) && ! current_user_can( 'pf_manage_galleries' ) ) {
            show_admin_bar( false );
        }
    }

    public static function add_security_headers() {
        if ( ! is_admin() ) {
            header( 'X-Content-Type-Options: nosniff' );
            header( 'Referrer-Policy: same-origin' );
            // CSP Report-Only for now
            header( "Content-Security-Policy-Report-Only: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;" );
        }
    }

    public static function shorten_session( $length, $user_id, $remember ) {
        $user = get_userdata( $user_id );
        if ( in_array( 'pf_client', (array) $user->roles ) || in_array( 'pf_editor', (array) $user->roles ) ) {
            return 12 * HOUR_IN_SECONDS; // 12 hours
        }
        return $length;
    }
}
