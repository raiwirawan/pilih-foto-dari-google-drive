<?php
namespace PilihFoto;

class Auth {
    private static $current_account = null;

    public static function init() {
        // Init if needed
    }

    public static function login( $username, $password ) {
        global $wpdb;

        $account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_accounts WHERE username = %s AND is_active = 1", $username ) );
        
        if ( ! $account ) {
            return false;
        }

        $valid_password = false;
        if ( ! empty( $account->legacy_hash ) ) {
            if ( wp_check_password( $password, $account->legacy_hash ) ) {
                $valid_password = true;
                // Rehash to new format
                $new_hash = password_hash( $password, PASSWORD_DEFAULT );
                $wpdb->update( 
                    "{$wpdb->prefix}pf_accounts", 
                    [ 'password_hash' => $new_hash, 'legacy_hash' => '' ], 
                    [ 'id' => $account->id ] 
                );
            }
        } else {
            if ( password_verify( $password, $account->password_hash ) ) {
                $valid_password = true;
            }
        }

        if ( $valid_password ) {
            self::create_session( $account->id );
            $wpdb->update( "{$wpdb->prefix}pf_accounts", [ 'last_login' => current_time('mysql', 1) ], [ 'id' => $account->id ] );
            return $account;
        }

        return false;
    }

    private static function create_session( $account_id ) {
        global $wpdb;
        $token = bin2hex( random_bytes( 32 ) );
        $token_hash = hash( 'sha256', $token );
        $expires = current_time('timestamp', 1) + (12 * HOUR_IN_SECONDS);

        $wpdb->insert(
            "{$wpdb->prefix}pf_sessions",
            [
                'account_id' => $account_id,
                'token_hash' => $token_hash,
                'expires_at' => gmdate( 'Y-m-d H:i:s', $expires )
            ]
        );

        setcookie( 'pf_session', $token, [
            'expires' => $expires,
            'path' => '/',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Strict'
        ] );
    }

    public static function logout() {
        if ( isset( $_COOKIE['pf_session'] ) ) {
            global $wpdb;
            $token = $_COOKIE['pf_session'];
            $token_hash = hash( 'sha256', $token );
            $wpdb->delete( "{$wpdb->prefix}pf_sessions", [ 'token_hash' => $token_hash ] );
            
            setcookie( 'pf_session', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => is_ssl(),
                'httponly' => true,
                'samesite' => 'Strict'
            ] );
        }
        self::$current_account = null;
    }

    public static function check() {
        if ( self::$current_account !== null ) {
            return self::$current_account;
        }

        if ( ! isset( $_COOKIE['pf_session'] ) ) {
            return false;
        }

        global $wpdb;
        $token = $_COOKIE['pf_session'];
        $token_hash = hash( 'sha256', $token );

        $session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_sessions WHERE token_hash = %s AND expires_at > %s", $token_hash, current_time('mysql', 1) ) );

        if ( ! $session ) {
            return false;
        }

        $account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_accounts WHERE id = %d AND is_active = 1", $session->account_id ) );

        if ( $account ) {
            self::$current_account = $account;
            return $account;
        }

        return false;
    }

    public static function verify_csrf() {
        if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
            return true;
        }
        // Basic CSRF validation via header and SameSite Strict cookie
        $headers = getallheaders();
        if ( ! isset( $headers['X-PF-CSRF'] ) && ! isset( $_SERVER['HTTP_X_PF_CSRF'] ) ) {
            return false;
        }
        return true;
    }
}
