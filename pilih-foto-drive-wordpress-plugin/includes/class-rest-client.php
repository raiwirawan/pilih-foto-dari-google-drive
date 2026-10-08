<?php
namespace PilihFoto;

class Rest_Client {
    public static function register_routes() {
        register_rest_route( 'studio/v1', '/client/galleries', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_galleries' ),
            'permission_callback' => array( __CLASS__, 'check_client_permission' ),
        ) );

        register_rest_route( 'studio/v1', '/client/galleries/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_gallery_details' ),
            'permission_callback' => array( __CLASS__, 'check_gallery_permission' ),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) { return is_numeric( $param ); }
                ),
            ),
        ) );

        register_rest_route( 'studio/v1', '/client/galleries/(?P<id>\d+)/selection', array(
            'methods' => 'PATCH',
            'callback' => array( __CLASS__, 'update_selection' ),
            'permission_callback' => array( __CLASS__, 'check_gallery_permission' ),
            'args' => array(
                'id' => array( 'validate_callback' => function($param) { return is_numeric( $param ); } ),
                'add' => array( 'type' => 'array', 'items' => array('type' => 'integer'), 'default' => array() ),
                'remove' => array( 'type' => 'array', 'items' => array('type' => 'integer'), 'default' => array() ),
            ),
        ) );

        register_rest_route( 'studio/v1', '/client/galleries/(?P<id>\d+)/submit', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'submit_selection' ),
            'permission_callback' => array( __CLASS__, 'check_gallery_permission' ),
            'args' => array(
                'id' => array( 'validate_callback' => function($param) { return is_numeric( $param ); } ),
                'note' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
            ),
        ) );
    }

    public static function check_client_permission() {
        $account = Auth::check();
        return $account && ($account->role === 'client' || $account->role === 'editor');
    }

    public static function check_gallery_permission( $request ) {
        $account = Auth::check();
        if ( ! $account ) return false;
        if ( $account->role === 'editor' ) return true;
        
        $gallery = DB::get_gallery( $request['id'] );
        if ( ! $gallery ) return false;

        if ( $account->id != $gallery->client_user_id ) {
            return false;
        }

        return true;
    }

    public static function get_galleries( $request ) {
        $account = Auth::check();
        $galleries = DB::get_galleries_by_client( $account->id );
        return rest_ensure_response( $galleries );
    }

    public static function get_gallery_details( $request ) {
        $id = $request['id'];
        $gallery = DB::get_gallery( $id );
        
        $photos = DB::get_photos( $id, 'source' );
        $results = DB::get_photos( $id, 'result' );
        $selections = DB::get_selections( $id );

        // Bersihkan data sesuai security requirement
        foreach($photos as &$p) {
            unset($p->drive_file_id);
        }
        foreach($results as &$r) {
            unset($r->drive_file_id);
        }
        unset($gallery->source_folder_id);
        unset($gallery->result_folder_id);

        return rest_ensure_response( array(
            'gallery' => $gallery,
            'photos' => $photos,
            'results' => $results,
            'selections' => $selections,
            'editor_wa' => get_option('pf_editor_wa', '')
        ) );
    }

    public static function update_selection( $request ) {
        $id = $request['id'];
        $gallery = DB::get_gallery( $id );
        
        if ( ! in_array( $gallery->status, ['draft', 'unstarted', 'reopened'] ) ) {
            return new \WP_Error( 'locked', 'Galeri sudah dikunci.', array( 'status' => 409 ) );
        }

        $add = $request['add'];
        $remove = $request['remove'];

        DB::update_selections( $id, $add, $remove );

        // Update status ke draft jika unstarted
        if ( $gallery->status === 'unstarted' ) {
            global $wpdb;
            $wpdb->update( "{$wpdb->prefix}pf_galleries", ['status' => 'draft'], ['id' => $id] );
        }

        return rest_ensure_response( array( 'success' => true ) );
    }

    public static function submit_selection( $request ) {
        $id = $request['id'];
        $gallery = DB::get_gallery( $id );
        
        if ( ! in_array( $gallery->status, ['draft', 'unstarted', 'reopened'] ) ) {
            return new \WP_Error( 'locked', 'Galeri sudah dikunci.', array( 'status' => 409 ) );
        }

        $selections = DB::get_selections( $id );
        if ( $gallery->max_select > 0 && count( $selections ) > $gallery->max_select ) {
            return new \WP_Error( 'limit_exceeded', 'Melebihi batas pilihan foto.', array( 'status' => 422 ) );
        }

        global $wpdb;
        $wpdb->update( 
            "{$wpdb->prefix}pf_galleries", 
            [
                'status' => 'submitted', 
                'note' => $request['note'] ?? $gallery->note,
                'submitted_at' => current_time('mysql')
            ], 
            ['id' => $id] 
        );

        return rest_ensure_response( array( 'success' => true ) );
    }
}
