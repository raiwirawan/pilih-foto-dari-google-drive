<?php
namespace PilihFoto;

class Drive_Client {
    private static function get_api_key() {
        if ( defined( 'PF_GOOGLE_API_KEY' ) ) {
            return PF_GOOGLE_API_KEY;
        }
        return false;
    }

    public static function list_files( $folder_id, $page_token = '' ) {
        $api_key = self::get_api_key();
        if ( ! $api_key ) {
            return new \WP_Error( 'missing_api_key', 'API Key Google belum dikonfigurasi di wp-config.php.' );
        }

        $url = 'https://www.googleapis.com/drive/v3/files';
        $query = "('" . sanitize_text_field( $folder_id ) . "' in parents) and (mimeType contains 'image/') and (trashed=false)";
        
        $params = [
            'q' => $query,
            'fields' => 'nextPageToken, files(id, name, mimeType, md5Checksum, modifiedTime, thumbnailLink, imageMediaMetadata(width,height))',
            'pageSize' => 1000,
            'orderBy' => 'name_natural'
        ];

        if ( $page_token ) {
            $params['pageToken'] = $page_token;
        }

        $url = add_query_arg( $params, $url );

        $response = wp_remote_get( $url, [
            'timeout' => 15,
            'headers' => [
                'X-Goog-Api-Key' => $api_key
            ]
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $code !== 200 ) {
            return new \WP_Error( 'drive_api_error', $data['error']['message'] ?? 'Error dari Drive API (Pastikan folder berstatus publik/Anyone with link)' );
        }

        return $data;
    }

    public static function resolve_folder( $folder_id ) {
        $api_key = self::get_api_key();
        if ( ! $api_key ) {
            return new \WP_Error( 'missing_api_key', 'API Key Google belum dikonfigurasi.' );
        }

        $url = "https://www.googleapis.com/drive/v3/files/" . sanitize_text_field( $folder_id ) . "?fields=id,name,mimeType";
        
        $response = wp_remote_get( $url, [
            'timeout' => 10,
            'headers' => [
                'X-Goog-Api-Key' => $api_key
            ]
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $code !== 200 ) {
            return new \WP_Error( 'drive_api_error', $data['error']['message'] ?? 'Gagal akses API Drive (Pastikan folder berstatus publik/Anyone with link)' );
        }

        if ( $data['mimeType'] !== 'application/vnd.google-apps.folder' ) {
            return new \WP_Error( 'not_a_folder', 'ID tersebut bukan sebuah folder' );
        }

        return $data;
    }
}
