<?php
namespace PilihFoto;

class Activator {
    public static function activate() {
        self::create_tables();
        self::add_roles();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
        CREATE TABLE {$wpdb->prefix}pf_galleries (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            client_user_id bigint(20) UNSIGNED NOT NULL,
            title varchar(100) NOT NULL,
            source_folder_id varchar(100) NOT NULL,
            result_folder_id varchar(100) DEFAULT '' NOT NULL,
            max_select int(11) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'unstarted',
            note text NOT NULL,
            submitted_at datetime DEFAULT NULL,
            reopened_at datetime DEFAULT NULL,
            version int(11) NOT NULL DEFAULT 1,
            deleted_at datetime DEFAULT NULL,
            created_by bigint(20) UNSIGNED NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY client_user_id (client_user_id),
            KEY status (status)
        ) $charset_collate;

        CREATE TABLE {$wpdb->prefix}pf_photos (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            gallery_id bigint(20) UNSIGNED NOT NULL,
            kind varchar(20) NOT NULL DEFAULT 'source',
            drive_file_id varchar(100) NOT NULL,
            name varchar(255) NOT NULL,
            mime varchar(100) NOT NULL,
            drive_version varchar(100) NOT NULL,
            position int(11) NOT NULL DEFAULT 0,
            synced_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY gallery_kind_drive (gallery_id, kind, drive_file_id),
            KEY gallery_id (gallery_id)
        ) $charset_collate;

        CREATE TABLE {$wpdb->prefix}pf_selections (
            gallery_id bigint(20) UNSIGNED NOT NULL,
            photo_id bigint(20) UNSIGNED NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (gallery_id, photo_id)
        ) $charset_collate;

        CREATE TABLE {$wpdb->prefix}pf_audit (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            gallery_id bigint(20) UNSIGNED DEFAULT NULL,
            actor_id bigint(20) UNSIGNED DEFAULT NULL,
            action varchar(50) NOT NULL,
            meta text,
            ip_hash varchar(64),
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY gallery_id (gallery_id)
        ) $charset_collate;
        ";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        update_option( 'pf_db_version', PILIH_FOTO_VERSION );
    }

    private static function add_roles() {
        Roles::init();
    }
}
