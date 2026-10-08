<?php
namespace PilihFoto;

class Drive_Client {
    private static $token = null;
    private static $token_expires = 0;

    private static function get_service_account_credentials() {
        if ( defined( 'PF_GDRIVE_CREDS_PATH' ) && file_exists( PF_GDRIVE_CREDS_PATH ) ) {
            $json = file_get_contents( PF_GDRIVE_CREDS_PATH );
            return json_decode( $json, true );
        }
        return false;
    }

    private static function base64url_encode( $data ) {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    }

    private static function generate_jwt() {
        $creds = self::get_service_account_credentials();
        if ( ! $creds || ! isset( $creds['private_key'] ) ) {
            return false;
        }

        $header = json_encode( ['typ' => 'JWT', 'alg' => 'RS256'] );
        $now = time();
        $claim = json_encode( [
            'iss' => $creds['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ] );

        $base64_header = self::base64url_encode( $header );
        $base64_claim = self::base64url_encode( $claim );
        $signature_input = $base64_header . '.' . $base64_claim;

        $signature = '';
        openssl_sign( $signature_input, $signature, $creds['private_key'], 'SHA256' );
        $base64_signature = self::base64url_encode( $signature );

        return $signature_input . '.' . $base64_signature;
    }

    public static function get_access_token() {
        if ( self::$token && time() < self::$token_expires ) {
            return self::$token;
        }

        $jwt = self::generate_jwt();
        if ( ! $jwt ) {
            return false;
        }

        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]
        ] );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $body['access_token'] ) ) {
            self::$token = $body['access_token'];
            self::$token_expires = time() + $body['expires_in'] - 60;
            return self::$token;
        }

        return false;
    }

    public static function list_files( $folder_id, $page_token = '' ) {
        $token = self::get_access_token();
        if ( ! $token ) {
            return new \WP_Error( 'drive_auth_failed', 'Gagal mendapatkan token Drive.' );
        }

        $url = 'https://www.googleapis.com/drive/v3/files';
        $query = "('" . sanitize_text_field( $folder_id ) . "' in parents) and (mimeType contains 'image/') and (trashed=false)";
        
        $params = [
            'q' => $query,
            'fields' => 'nextPageToken, files(id, name, mimeType, md5Checksum, modifiedTime, thumbnailLink)',
            'pageSize' => 1000,
            'orderBy' => 'name_natural',
        ];

        if ( $page_token ) {
            $params['pageToken'] = $page_token;
        }

        $url = add_query_arg( $params, $url );

        $response = wp_remote_get( $url, [
            'headers' => [ 'Authorization' => 'Bearer ' . $token ],
            'timeout' => 15
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $code !== 200 ) {
            return new \WP_Error( 'drive_api_error', $data['error']['message'] ?? 'Error dari Drive API' );
        }

        return $data;
    }

    public static function resolve_folder( $folder_id ) {
        $token = self::get_access_token();
        if ( ! $token ) {
            return new \WP_Error( 'drive_auth_failed', 'Gagal mendapatkan token Drive.' );
        }

        $url = "https://www.googleapis.com/drive/v3/files/" . sanitize_text_field( $folder_id ) . "?fields=id,name,mimeType";
        
        $response = wp_remote_get( $url, [
            'headers' => [ 'Authorization' => 'Bearer ' . $token ],
            'timeout' => 10
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $code !== 200 ) {
            return new \WP_Error( 'drive_api_error', $data['error']['message'] ?? 'Gagal akses API Drive' );
        }

        if ( $data['mimeType'] !== 'application/vnd.google-apps.folder' ) {
            return new \WP_Error( 'not_a_folder', 'ID tersebut bukan sebuah folder' );
        }

        return $data;
    }
}
