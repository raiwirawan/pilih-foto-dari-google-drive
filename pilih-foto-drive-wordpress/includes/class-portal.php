<?php
namespace PilihFoto;

class Portal {
    public static function init() {
        add_action( 'template_redirect', array( __CLASS__, 'handle_portal_route' ) );
        
        // Add rewrite rule for /foto/
        add_action( 'init', function() {
            add_rewrite_rule( '^foto/?$', 'index.php?pf_portal=1', 'top' );
            add_rewrite_rule( '^foto/hasil/?$', 'index.php?pf_portal_result=1', 'top' );
        } );
        
        add_filter( 'query_vars', function( $vars ) {
            $vars[] = 'pf_portal';
            $vars[] = 'pf_portal_result';
            return $vars;
        } );
    }

    public static function handle_portal_route() {
        $is_portal = get_query_var( 'pf_portal' );
        $is_result = get_query_var( 'pf_portal_result' );

        if ( ! $is_portal && ! $is_result ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            auth_redirect();
            exit;
        }

        if ( ! current_user_can( 'pf_view_own_gallery' ) && ! current_user_can( 'pf_manage_galleries' ) ) {
            wp_die( 'Anda tidak memiliki akses ke halaman ini.' );
        }

        // Prevent theme loading, load our own template
        if ( $is_result ) {
            $template = PILIH_FOTO_PLUGIN_DIR . 'public/result-template.php';
        } else {
            $template = PILIH_FOTO_PLUGIN_DIR . 'public/client-template.php';
        }

        if ( file_exists( $template ) ) {
            include $template;
            exit;
        } else {
            wp_die( 'Template portal tidak ditemukan.' );
        }
    }
}
