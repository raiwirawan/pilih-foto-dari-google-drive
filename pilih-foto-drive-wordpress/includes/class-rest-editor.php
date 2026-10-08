<?php
namespace PilihFoto;

class Rest_Editor {
    public static function register_routes() {
        register_rest_route( 'pilihfoto/v1', '/editor/galleries', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_galleries' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );

        register_rest_route( 'pilihfoto/v1', '/editor/galleries', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'create_gallery' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );

        register_rest_route( 'pilihfoto/v1', '/editor/galleries/(?P<id>\d+)', array(
            'methods' => 'PATCH',
            'callback' => array( __CLASS__, 'update_gallery' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );

        register_rest_route( 'pilihfoto/v1', '/editor/galleries/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array( __CLASS__, 'delete_gallery' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );

        register_rest_route( 'pilihfoto/v1', '/editor/galleries/(?P<id>\d+)/reopen', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'reopen_gallery' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );

        register_rest_route( 'pilihfoto/v1', '/editor/galleries/(?P<id>\d+)/sync', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'sync_gallery' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );

        register_rest_route( 'pilihfoto/v1', '/editor/drive/validate', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'validate_drive' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );

        register_rest_route( 'pilihfoto/v1', '/editor/settings', array(
            'methods' => 'PATCH',
            'callback' => array( __CLASS__, 'update_settings' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );
    }

    public static function check_permission() {
        return current_user_can( 'pf_manage_galleries' );
    }

    public static function get_galleries() {
        $galleries = DB::get_all_galleries();
        // Decorate with selection count
        foreach ($galleries as $g) {
            $g->selected_count = count( DB::get_selections( $g->id ) );
        }
        return rest_ensure_response( $galleries );
    }

    public static function create_gallery( $request ) {
        $username = sanitize_user( $request['username'] );
        $name = sanitize_text_field( $request['name'] );
        $password = $request['password'] ? $request['password'] : wp_generate_password();
        
        $user_id = username_exists( $username );
        if ( ! $user_id ) {
            $user_id = wp_create_user( $username, $password );
            if ( is_wp_error( $user_id ) ) return $user_id;
            
            $user = new \WP_User( $user_id );
            $user->set_role( 'pf_client' );
            wp_update_user( array( 'ID' => $user_id, 'display_name' => $name ) );
        }

        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}pf_galleries",
            [
                'client_user_id' => $user_id,
                'title' => $name,
                'source_folder_id' => sanitize_text_field( $request['folderId'] ?? '' ),
                'result_folder_id' => sanitize_text_field( $request['resultFolderId'] ?? '' ),
                'max_select' => intval( $request['maxSelect'] ?? 0 ),
                'created_by' => get_current_user_id()
            ]
        );

        $gallery_id = $wpdb->insert_id;
        
        if ( ! empty( $request['folderId'] ) ) {
            Sync::sync_gallery( $gallery_id, 'source' );
        }
        if ( ! empty( $request['resultFolderId'] ) ) {
            Sync::sync_gallery( $gallery_id, 'result' );
        }

        return rest_ensure_response( array( 'success' => true, 'id' => $gallery_id ) );
    }

    public static function update_gallery( $request ) {
        $id = $request['id'];
        $gallery = DB::get_gallery( $id );
        if ( ! $gallery ) return new \WP_Error( 'not_found', 'Galeri tidak ditemukan.', array('status' => 404) );

        global $wpdb;
        $data = [];
        
        if ( isset( $request['name'] ) ) {
            $data['title'] = sanitize_text_field( $request['name'] );
            wp_update_user( array( 'ID' => $gallery->client_user_id, 'display_name' => $data['title'] ) );
        }
        if ( isset( $request['maxSelect'] ) ) {
            $data['max_select'] = intval( $request['maxSelect'] );
        }
        
        $resync_source = false;
        if ( isset( $request['folderId'] ) ) {
            $new_folder = sanitize_text_field( $request['folderId'] );
            if ( $new_folder !== $gallery->source_folder_id ) {
                $data['source_folder_id'] = $new_folder;
                $resync_source = true;
            }
        }
        
        $resync_result = false;
        if ( isset( $request['resultFolderId'] ) ) {
            $new_result = sanitize_text_field( $request['resultFolderId'] );
            if ( $new_result !== $gallery->result_folder_id ) {
                $data['result_folder_id'] = $new_result;
                $resync_result = true;
            }
        }

        if ( isset( $request['password'] ) && ! empty( $request['password'] ) ) {
            wp_set_password( $request['password'], $gallery->client_user_id );
        }

        if ( ! empty( $data ) ) {
            $wpdb->update( "{$wpdb->prefix}pf_galleries", $data, ['id' => $id] );
        }

        if ( $resync_source && ! empty( $data['source_folder_id'] ) ) {
            Sync::sync_gallery( $id, 'source' );
            // Reset selections if folder changed
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}pf_selections WHERE gallery_id = %d", $id ) );
            $wpdb->update( "{$wpdb->prefix}pf_galleries", ['status' => 'draft', 'note' => ''], ['id' => $id] );
        }
        if ( $resync_result && ! empty( $data['result_folder_id'] ) ) {
            Sync::sync_gallery( $id, 'result' );
        }

        return rest_ensure_response( array( 'success' => true ) );
    }

    public static function delete_gallery( $request ) {
        $id = $request['id'];
        global $wpdb;
        $wpdb->update( "{$wpdb->prefix}pf_galleries", ['deleted_at' => current_time('mysql')], ['id' => $id] );
        return rest_ensure_response( array( 'success' => true ) );
    }

    public static function reopen_gallery( $request ) {
        $id = $request['id'];
        global $wpdb;
        $wpdb->update( "{$wpdb->prefix}pf_galleries", ['status' => 'reopened', 'reopened_at' => current_time('mysql')], ['id' => $id] );
        return rest_ensure_response( array( 'success' => true ) );
    }

    public static function sync_gallery( $request ) {
        $id = $request['id'];
        $kind = $request['kind'] ?? 'source';
        $res = Sync::sync_gallery( $id, $kind );
        if ( is_wp_error( $res ) ) return $res;
        return rest_ensure_response( array( 'success' => true, 'count' => $res ) );
    }

    public static function validate_drive( $request ) {
        $input = sanitize_text_field( $request['input'] );
        // Extract ID if URL
        $folder_id = $input;
        if ( preg_match( '/folders\/([a-zA-Z0-9_-]+)/', $input, $m ) ) {
            $folder_id = $m[1];
        } elseif ( preg_match( '/id=([a-zA-Z0-9_-]+)/', $input, $m ) ) {
            $folder_id = $m[1];
        }
        
        $res = Drive_Client::resolve_folder( $folder_id );
        if ( is_wp_error( $res ) ) return $res;
        
        return rest_ensure_response( array(
            'folderId' => $folder_id,
            'name' => $res['name']
        ) );
    }

    public static function update_settings( $request ) {
        $wa = preg_replace( '/[^0-9]/', '', $request['whatsapp'] ?? '' );
        update_option( 'pf_editor_wa', $wa );
        return rest_ensure_response( array( 'success' => true ) );
    }
}
