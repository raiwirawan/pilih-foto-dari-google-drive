<?php
/**
 * Dijalankan saat plugin di-uninstall.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Hapus tabel
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pf_selections" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pf_photos" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pf_galleries" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pf_audit" );

// Hapus opsi
delete_option( 'pf_db_version' );
delete_option( 'pf_editor_wa' );

// Hapus role
remove_role( 'pf_client' );
remove_role( 'pf_editor' );
