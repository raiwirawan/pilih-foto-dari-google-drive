<?php
namespace PilihFoto;

class Media {
    public static function init() {
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
        add_action( 'template_redirect', array( __CLASS__, 'serve_image' ) );
    }

    public static function add_rewrite_rules() {
        add_rewrite_rule(
            '^pf-img/([0-9]+)/([0-9]+)/([0-9]+)/?$',
            'index.php?pf_gallery_id=$matches[1]&pf_photo_id=$matches[2]&pf_size=$matches[3]',
            'top'
        );
    }

    public static function add_query_vars( $vars ) {
        $vars[] = 'pf_gallery_id';
        $vars[] = 'pf_photo_id';
        $vars[] = 'pf_size';
        return $vars;
    }

    public static function serve_image() {
        $gallery_id = get_query_var( 'pf_gallery_id' );
        $photo_id = get_query_var( 'pf_photo_id' );
        $size = get_query_var( 'pf_size' );

        if ( ! $gallery_id || ! $photo_id || ! $size ) {
            return; // Not our request
        }

        if ( ! in_array( $size, ['400', '1600'] ) ) {
            wp_die( 'Ukuran tidak valid.', 'Error', 400 );
        }

        if ( ! is_user_logged_in() ) {
            wp_die( 'Akses ditolak.', 'Error', 401 );
        }

        global $wpdb;
        $photo = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_photos WHERE id = %d AND gallery_id = %d", $photo_id, $gallery_id ) );

        if ( ! $photo ) {
            wp_die( 'Foto tidak ditemukan.', 'Error', 404 );
        }

        // Cek otorisasi
        $gallery = DB::get_gallery( $gallery_id );
        if ( ! current_user_can( 'pf_manage_galleries' ) && get_current_user_id() != $gallery->client_user_id ) {
            wp_die( 'Akses ditolak.', 'Error', 403 );
        }

        $cache_dir = wp_upload_dir()['basedir'] . '/pilihfoto-cache';
        if ( ! file_exists( $cache_dir ) ) {
            wp_mkdir_p( $cache_dir );
            file_put_contents( $cache_dir . '/.htaccess', "deny from all\n" );
            file_put_contents( $cache_dir . '/index.php', "<?php // Silence" );
        }

        $cache_key = hash_hmac( 'sha256', $photo->drive_file_id . '_' . $size . '_' . $photo->drive_version, 'pf_salt_123' );
        $cache_file = $cache_dir . '/' . $cache_key . '.jpg';

        if ( ! file_exists( $cache_file ) ) {
            // Ambil dari Drive (simplifikasi: kita anggap thumbnailLink masih bisa diambil via API)
            // Di implementasi asli, kita akan ambil content atau minta thumbnailLink ulang
            $token = Drive_Client::get_access_token();
            if ( ! $token ) wp_die( 'Drive Auth Failed', 'Error', 500 );

            $url = "https://www.googleapis.com/drive/v3/files/{$photo->drive_file_id}?fields=thumbnailLink&alt=json";
            $res = wp_remote_get( $url, ['headers' => ['Authorization' => 'Bearer ' . $token]] );
            if ( is_wp_error( $res ) ) wp_die( 'Drive API Error', 'Error', 500 );
            
            $data = json_decode( wp_remote_retrieve_body( $res ), true );
            if ( empty( $data['thumbnailLink'] ) ) wp_die( 'No thumbnail link', 'Error', 404 );

            $thumb_url = preg_replace( '/=s\d+$/', '=s' . $size, $data['thumbnailLink'] );
            $img_res = wp_remote_get( $thumb_url, ['timeout' => 15] );
            
            if ( is_wp_error( $img_res ) || wp_remote_retrieve_response_code( $img_res ) !== 200 ) {
                wp_die( 'Failed to fetch image', 'Error', 500 );
            }

            file_put_contents( $cache_file, wp_remote_retrieve_body( $img_res ) );
        }

        header( 'Content-Type: image/jpeg' );
        header( 'Cache-Control: private, max-age=31536000, immutable' );
        if ( isset( $_GET['v'] ) ) {
            $etag = md5( $photo->drive_version );
            header( "ETag: \"$etag\"" );
            if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && trim( $_SERVER['HTTP_IF_NONE_MATCH'], '"' ) == $etag ) {
                http_response_code( 304 );
                exit;
            }
        }
        
        readfile( $cache_file );
        exit;
    }
}
