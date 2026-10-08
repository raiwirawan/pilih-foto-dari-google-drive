<?php
namespace PilihFoto;

class DB {
    public static function init() {
        // Init hooks if necessary
    }

    public static function get_gallery( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_galleries WHERE id = %d AND deleted_at IS NULL", $id ) );
    }

    public static function get_galleries_by_client( $client_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_galleries WHERE client_user_id = %d AND deleted_at IS NULL ORDER BY created_at DESC", $client_id ) );
    }

    public static function get_all_galleries() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pf_galleries WHERE deleted_at IS NULL ORDER BY updated_at DESC" );
    }

    public static function get_photos( $gallery_id, $kind = 'source' ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pf_photos WHERE gallery_id = %d AND kind = %s ORDER BY position ASC, name ASC", $gallery_id, $kind ) );
    }

    public static function get_selections( $gallery_id ) {
        global $wpdb;
        return $wpdb->get_col( $wpdb->prepare( "SELECT photo_id FROM {$wpdb->prefix}pf_selections WHERE gallery_id = %d", $gallery_id ) );
    }

    public static function update_selections( $gallery_id, $add_ids, $remove_ids ) {
        global $wpdb;
        
        $wpdb->query('START TRANSACTION');
        
        try {
            if ( ! empty( $remove_ids ) ) {
                $ids = implode( ',', array_map( 'intval', $remove_ids ) );
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}pf_selections WHERE gallery_id = %d AND photo_id IN ($ids)", $gallery_id ) );
            }

            if ( ! empty( $add_ids ) ) {
                foreach ( $add_ids as $pid ) {
                    $wpdb->query( $wpdb->prepare( 
                        "INSERT IGNORE INTO {$wpdb->prefix}pf_selections (gallery_id, photo_id) VALUES (%d, %d)", 
                        $gallery_id, intval($pid) 
                    ) );
                }
            }

            $wpdb->query('COMMIT');
            return true;
        } catch ( \Exception $e ) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
}
