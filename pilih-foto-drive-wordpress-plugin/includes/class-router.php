<?php
namespace PilihFoto;

class Router {
    public static function init() {
        add_action( 'parse_request', array( __CLASS__, 'handle_request' ), 1 );
        
        // Setup redirect for legacy /foto routes
        add_action( 'init', array( __CLASS__, 'add_legacy_redirect' ) );
    }

    public static function add_legacy_redirect() {
        add_rewrite_rule( '^foto/?(.*)', 'index.php?pf_legacy_redirect=$matches[1]', 'top' );
        add_filter( 'query_vars', function( $vars ) {
            $vars[] = 'pf_legacy_redirect';
            return $vars;
        } );
    }

    public static function handle_request( $wp ) {
        // Handle legacy redirect
        if ( isset( $wp->query_vars['pf_legacy_redirect'] ) ) {
            $path = $wp->query_vars['pf_legacy_redirect'];
            wp_redirect( home_url( '/' . PF_BASE_SLUG . '/' . $path ), 301 );
            exit;
        }

        $request_path = trim( $_SERVER['REQUEST_URI'], '/' );
        $base = trim( home_url( '', 'relative' ), '/' );
        if ( $base ) {
            $request_path = preg_replace( '#^' . preg_quote( $base, '#' ) . '/#', '', $request_path );
        }

        $parts = explode( '?', $request_path );
        $path = trim( $parts[0], '/' );

        // Cek apakah diawali dengan PF_BASE_SLUG (default: 'studio')
        if ( strpos( $path, PF_BASE_SLUG ) === 0 ) {
            $subpath = trim( substr( $path, strlen( PF_BASE_SLUG ) ), '/' );
            
            // Set security headers
            header( 'X-Content-Type-Options: nosniff' );
            header( 'X-Frame-Options: DENY' );
            header( 'X-Robots-Tag: noindex, nofollow' );
            header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
            
            if ( $subpath === 'api' || strpos( $subpath, 'api/' ) === 0 ) {
                self::handle_api( substr( $subpath, 4 ) );
            } elseif ( strpos( $subpath, 'img/' ) === 0 ) {
                self::handle_img( substr( $subpath, 4 ) );
            } else {
                self::handle_ui( $subpath );
            }
            exit;
        }
    }

    private static function handle_ui( $subpath ) {
        $account = Auth::check();

        if ( $subpath === '' ) {
            if ( $account ) {
                if ( $account->role === 'editor' ) {
                    wp_redirect( home_url( '/' . PF_BASE_SLUG . '/editor' ) );
                } else {
                    wp_redirect( home_url( '/' . PF_BASE_SLUG . '/galeri' ) );
                }
                exit;
            }
            include PILIH_FOTO_PLUGIN_DIR . 'public/studio/login.php';
            exit;
        }

        if ( ! $account ) {
            wp_redirect( home_url( '/' . PF_BASE_SLUG ) );
            exit;
        }

        if ( strpos( $subpath, 'editor' ) === 0 && $account->role === 'editor' ) {
            include PILIH_FOTO_PLUGIN_DIR . 'public/studio/editor.php';
            exit;
        }

        if ( strpos( $subpath, 'galeri' ) === 0 && $account->role === 'client' ) {
            if ( $subpath === 'galeri/hasil' ) {
                include PILIH_FOTO_PLUGIN_DIR . 'public/studio/galeri-hasil.php';
            } else {
                include PILIH_FOTO_PLUGIN_DIR . 'public/studio/galeri.php';
            }
            exit;
        }

        wp_die( 'Akses ditolak atau halaman tidak ditemukan.', 'Not Found', [ 'response' => 404 ] );
    }

    private static function handle_api( $endpoint ) {
        header('Content-Type: application/json');
        
        // Validate CSRF for mutations
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' && ! Auth::verify_csrf() ) {
            http_response_code(403);
            echo wp_json_encode(['error' => 'CSRF verification failed']);
            exit;
        }

        // Basic router api mock
        $account = Auth::check();
        
        if ( $endpoint === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $input = json_decode(file_get_contents('php://input'), true);
            $user = Auth::login($input['username'] ?? '', $input['password'] ?? '');
            if ($user) {
                echo wp_json_encode(['success' => true, 'role' => $user->role]);
            } else {
                http_response_code(401);
                echo wp_json_encode(['error' => 'Login gagal']);
            }
            exit;
        }

        if ( ! $account ) {
            http_response_code(401);
            echo wp_json_encode(['error' => 'Unauthorized']);
            exit;
        }

        if ( $endpoint === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            Auth::logout();
            echo wp_json_encode(['success' => true]);
            exit;
        }
        
        // Pass to specialized controllers if needed...
        echo wp_json_encode(['success' => true, 'endpoint' => $endpoint]);
        exit;
    }

    private static function handle_img( $path ) {
        // img/{gallery_id}/{photo_id}/{size}
        $parts = explode('/', $path);
        if ( count($parts) >= 3 ) {
            $gallery_id = intval($parts[0]);
            $photo_id = intval($parts[1]);
            $size = sanitize_text_field($parts[2]);
            Media::serve_image($gallery_id, $photo_id, $size);
        }
        
        http_response_code(404);
        exit;
    }
}
