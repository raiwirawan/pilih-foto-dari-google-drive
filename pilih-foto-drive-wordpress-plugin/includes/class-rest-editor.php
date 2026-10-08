<?php
namespace PilihFoto;

class Rest_Editor {
    public static function register_routes() {
        // These are accessed via /studio/api/ or wp-json (legacy, redirect handled elsewhere)
        // For simplicity, we just bind to wp-json but with our custom auth for the migration
        register_rest_route( 'studio/v1', '/editor/galleries', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_galleries' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );

        register_rest_route( 'studio/v1', '/editor/galleries', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'create_gallery' ),
            'permission_callback' => array( __CLASS__, 'check_permission' ),
        ) );
    }

    public static function check_permission() {
        $account = Auth::check();
        return $account && $account->role === 'editor';
    }

    public static function get_galleries() {
        $galleries = DB::get_all_galleries();
        foreach ($galleries as $g) {
            $g->selected_count = count( DB::get_selections( $g->id ) );
        }
        return rest_ensure_response( $galleries );
    }

    public static function create_gallery( $request ) {
        $username = sanitize_user( $request['username'] );
        $name = sanitize_text_field( $request['name'] );
        $password = $request['password'] ? $request['password'] : wp_generate_password();
        
        global $wpdb;
        $account_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}pf_accounts WHERE username = %s", $username));
        
        if ( ! $account_id ) {
            $wpdb->insert(
                "{$wpdb->prefix}pf_accounts",
                [
                    'username' => $username,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'client',
                    'display_name' => $name,
                    'created_at' => current_time('mysql', 1)
                ]
            );
            $account_id = $wpdb->insert_id;
        } else {
            $wpdb->update("{$wpdb->prefix}pf_accounts", ['display_name' => $name], ['id' => $account_id]);
        }

        $wpdb->insert(
            "{$wpdb->prefix}pf_galleries",
            [
                'client_user_id' => $account_id,
                'title' => $name,
                'source_folder_id' => sanitize_text_field( $request['folderId'] ?? '' ),
                'result_folder_id' => sanitize_text_field( $request['resultFolderId'] ?? '' ),
                'max_select' => intval( $request['maxSelect'] ?? 0 ),
                'created_by' => Auth::check()->id
            ]
        );

        $gallery_id = $wpdb->insert_id;
        return rest_ensure_response( array( 'success' => true, 'id' => $gallery_id ) );
    }
}
