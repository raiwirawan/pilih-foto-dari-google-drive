<?php
namespace PilihFoto;

class Roles {
    public static function init() {
        add_role( 'pf_client', 'Klien Foto', array(
            'read' => true,
            'pf_view_own_gallery' => true,
        ) );

        add_role( 'pf_editor', 'Editor Foto', array(
            'read' => true,
            'pf_manage_galleries' => true,
        ) );

        // Tambah capability ke administrator agar admin juga bisa mengelola
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->add_cap( 'pf_manage_galleries' );
        }
    }
}
