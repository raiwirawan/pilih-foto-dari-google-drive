<?php
namespace PilihFoto;

class Activator {
    public static function activate() {
        self::create_tables();
        self::migrate_old_users();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
        CREATE TABLE {$wpdb->prefix}pf_accounts (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            username varchar(30) NOT NULL,
            password_hash varchar(255) NOT NULL,
            role varchar(20) NOT NULL,
            display_name varchar(100) NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            last_login datetime DEFAULT NULL,
            legacy_hash varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY username (username)
        ) $charset_collate;

        CREATE TABLE {$wpdb->prefix}pf_sessions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            account_id bigint(20) UNSIGNED NOT NULL,
            token_hash varchar(64) NOT NULL,
            expires_at datetime NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY account_id (account_id),
            UNIQUE KEY token_hash (token_hash)
        ) $charset_collate;

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
            thumbnail_link text DEFAULT NULL,
            width int(11) DEFAULT NULL,
            height int(11) DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY gallery_kind_drive (gallery_id, kind, drive_file_id),
            KEY gallery_id (gallery_id),
            KEY gallery_kind_position (gallery_id, kind, position)
        ) $charset_collate;

        CREATE TABLE {$wpdb->prefix}pf_selections (
            gallery_id bigint(20) UNSIGNED NOT NULL,
            photo_id bigint(20) UNSIGNED NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (gallery_id, photo_id),
            KEY photo_id (photo_id)
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
        
        // Hapus role lama
        remove_role('pf_client');
        remove_role('pf_editor');
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('pf_manage_galleries');
        }
    }

    private static function migrate_old_users() {
        global $wpdb;
        $users = get_users(['role__in' => ['pf_client', 'pf_editor']]);
        foreach ($users as $user) {
            $role = in_array('pf_editor', $user->roles) ? 'editor' : 'client';
            
            // Cek apakah sudah ada
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}pf_accounts WHERE username = %s", $user->user_login));
            if (!$exists) {
                $wpdb->insert(
                    "{$wpdb->prefix}pf_accounts",
                    [
                        'username' => $user->user_login,
                        'password_hash' => '', // Akan diisi saat login pertama jika kosong tapi kita pakai legacy_hash
                        'legacy_hash' => $user->user_pass,
                        'role' => $role,
                        'display_name' => $user->display_name,
                        'created_at' => current_time('mysql', 1)
                    ]
                );
            }
        }
    }
}
