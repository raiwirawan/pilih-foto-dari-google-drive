<?php
namespace PilihFoto;

class Sync {
    public static function sync_gallery( $gallery_id, $kind = 'source' ) {
        $gallery = DB::get_gallery( $gallery_id );
        if ( ! $gallery ) {
            return new \WP_Error( 'not_found', 'Galeri tidak ditemukan.' );
        }

        $folder_id = $kind === 'source' ? $gallery->source_folder_id : $gallery->result_folder_id;
        if ( empty( $folder_id ) ) {
            return new \WP_Error( 'no_folder', 'Folder ID kosong.' );
        }

        $all_files = [];
        $page_token = '';

        do {
            $response = Drive_Client::list_files( $folder_id, $page_token );
            if ( is_wp_error( $response ) ) {
                return $response;
            }
            if ( isset( $response['files'] ) ) {
                $all_files = array_merge( $all_files, $response['files'] );
            }
            $page_token = $response['nextPageToken'] ?? '';
        } while ( $page_token );

        // Lakukan diffing dengan DB
        global $wpdb;
        $existing_photos = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_photos WHERE gallery_id = %d AND kind = %s", $gallery_id, $kind ), ARRAY_A );
        
        $existing_map = [];
        foreach ( $existing_photos as $p ) {
            $existing_map[ $p['drive_file_id'] ] = $p;
        }

        $wpdb->query('START TRANSACTION');
        try {
            $position = 0;
            $seen_drive_ids = [];

            foreach ( $all_files as $file ) {
                $drive_id = $file['id'];
                $seen_drive_ids[] = $drive_id;
                $version = $file['md5Checksum'] ?? $file['modifiedTime'] ?? 'v1';

                $width = isset($file['imageMediaMetadata']['width']) ? intval($file['imageMediaMetadata']['width']) : null;
                $height = isset($file['imageMediaMetadata']['height']) ? intval($file['imageMediaMetadata']['height']) : null;
                $thumbnail_link = $file['thumbnailLink'] ?? null;

                if ( isset( $existing_map[ $drive_id ] ) ) {
                    // Update if needed
                    $p = $existing_map[ $drive_id ];
                    if ( $p['drive_version'] !== $version || $p['position'] != $position || $p['name'] !== $file['name'] || $p['thumbnail_link'] !== $thumbnail_link ) {
                        $wpdb->update(
                            "{$wpdb->prefix}pf_photos",
                            [ 
                                'name' => $file['name'], 
                                'drive_version' => $version, 
                                'position' => $position, 
                                'synced_at' => current_time('mysql'),
                                'thumbnail_link' => $thumbnail_link,
                                'width' => $width,
                                'height' => $height
                            ],
                            [ 'id' => $p['id'] ]
                        );
                    }
                } else {
                    // Insert
                    $wpdb->insert(
                        "{$wpdb->prefix}pf_photos",
                        [
                            'gallery_id' => $gallery_id,
                            'kind' => $kind,
                            'drive_file_id' => $drive_id,
                            'name' => $file['name'],
                            'mime' => $file['mimeType'],
                            'drive_version' => $version,
                            'position' => $position,
                            'synced_at' => current_time('mysql'),
                            'thumbnail_link' => $thumbnail_link,
                            'width' => $width,
                            'height' => $height
                        ]
                    );
                }
                $position++;
            }

            // Hapus yang tidak ada di Drive lagi
            $to_delete = array_diff( array_keys( $existing_map ), $seen_drive_ids );
            if ( ! empty( $to_delete ) ) {
                $in_placeholders = implode( ',', array_fill( 0, count( $to_delete ), '%s' ) );
                $sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}pf_photos WHERE gallery_id = %d AND kind = %s AND drive_file_id IN ($in_placeholders)", array_merge( [ $gallery_id, $kind ], $to_delete ) );
                $wpdb->query( $sql );
                
                // Pilihan akan otomatis yatim jika tidak ada constraint, tapi query SELECT JOIN nanti hanya menampilkan foto yang ada.
                // Lebih baik bersihkan pilihan yatim:
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}pf_selections WHERE gallery_id = %d AND photo_id NOT IN (SELECT id FROM {$wpdb->prefix}pf_photos WHERE gallery_id = %d)", $gallery_id, $gallery_id ) );
            }

            $wpdb->query('COMMIT');
            return count( $all_files );
        } catch ( \Exception $e ) {
            $wpdb->query('ROLLBACK');
            return new \WP_Error( 'db_error', 'Gagal menyimpan hasil sinkronisasi.' );
        }
    }
}
