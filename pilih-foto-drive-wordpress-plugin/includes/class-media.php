<?php
namespace PilihFoto;

class Media {
    public static function init() {
        // Rewrite rules have been moved to Router (`/studio/img/...`)
        // This class is now used as a helper for image proxying
    }

    public static function serve_image( $gallery_id, $photo_id, $size ) {
        if ( ! in_array( $size, ['400', '1600'] ) ) {
            wp_die( 'Ukuran tidak valid.', 'Error', 400 );
        }

        $account = Auth::check();
        if ( ! $account ) {
            wp_die( 'Akses ditolak.', 'Error', 401 );
        }

        global $wpdb;
        $photo = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_photos WHERE id = %d AND gallery_id = %d", $photo_id, $gallery_id ) );

        if ( ! $photo ) {
            wp_die( 'Foto tidak ditemukan.', 'Error', 404 );
        }

        // Cek otorisasi
        if ( $account->role !== 'editor' ) {
            $gallery = DB::get_gallery( $gallery_id );
            if ( $account->id != $gallery->client_user_id ) {
                wp_die( 'Akses ditolak.', 'Error', 403 );
            }
        }

        $cache_dir = defined('PF_CACHE_DIR') ? PF_CACHE_DIR : (wp_upload_dir()['basedir'] . '/pilihfoto-cache');
        if ( ! file_exists( $cache_dir ) ) {
            wp_mkdir_p( $cache_dir );
            file_put_contents( $cache_dir . '/.htaccess', "deny from all\n" );
            file_put_contents( $cache_dir . '/index.html', "" );
        }

        $salt = defined('AUTH_KEY') ? AUTH_KEY : 'default_salt';
        $cache_key = hash_hmac( 'sha256', $photo->drive_file_id . '_' . $size . '_' . $photo->drive_version, $salt );
        $cache_file = $cache_dir . '/' . $cache_key . '.jpg';

        if ( ! file_exists( $cache_file ) ) {
            // Jika tidak ada di cache, buat dari thumbnail_link jika tersedia atau sinkron ulang
            if ( empty($photo->thumbnail_link) ) {
                wp_die('Thumbnail link tidak tersedia, harus disinkronkan', 'Error', 404);
            }
            
            $thumb_url = preg_replace( '/=s\d+$/', '=s' . $size, $photo->thumbnail_link );
            $img_res = wp_remote_get( $thumb_url, ['timeout' => 15] );
            
            if ( is_wp_error( $img_res ) || wp_remote_retrieve_response_code( $img_res ) !== 200 ) {
                wp_die( 'Failed to fetch image', 'Error', 500 );
            }

            file_put_contents( $cache_file, wp_remote_retrieve_body( $img_res ) );
        }

        $etag = md5( $photo->drive_version );
        $mtime = filemtime($cache_file);

        header( 'Content-Type: image/jpeg' );
        header( 'Cache-Control: private, max-age=31536000, immutable' );
        header( 'Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT' );
        header( "ETag: \"$etag\"" );
        
        if ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && trim( $_SERVER['HTTP_IF_NONE_MATCH'], '"' ) == $etag ) {
            http_response_code( 304 );
            exit;
        }

        if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mtime ) {
            http_response_code( 304 );
            exit;
        }

        // x-accel-redirect or x-sendfile support can be added here
        readfile( $cache_file );
        exit;
    }
}
