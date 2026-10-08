<?php
/**
 * Plugin Name:       Pilih Foto Drive
 * Description:       Sistem pemilihan foto klien berbasis Google Drive, khusus untuk fotografer.
 * Version:           1.0.0
 * Author:            BC Solutions
 * Text Domain:       pilih-foto
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'PILIH_FOTO_VERSION', '1.0.0' );
define( 'PILIH_FOTO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PILIH_FOTO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader sederhana
spl_autoload_register( function ( $class_name ) {
    if ( strpos( $class_name, 'PilihFoto\\' ) !== 0 ) {
        return;
    }
    $class_name = str_replace( 'PilihFoto\\', '', $class_name );
    $class_name = str_replace( '_', '-', strtolower( $class_name ) );
    $file = PILIH_FOTO_PLUGIN_DIR . 'includes/class-' . $class_name . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

// Aktivasi plugin
register_activation_hook( __FILE__, array( 'PilihFoto\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PilihFoto\Activator', 'deactivate' ) );

// Inisialisasi komponen
add_action( 'plugins_loaded', function() {
    // Muat komponen
    \PilihFoto\Roles::init();
    \PilihFoto\DB::init();
    \PilihFoto\Security::init();
    \PilihFoto\Media::init();
    
    // REST API
    add_action( 'rest_api_init', function() {
        \PilihFoto\Rest_Client::register_routes();
        \PilihFoto\Rest_Editor::register_routes();
    });

    // Admin UI
    if ( is_admin() ) {
        \PilihFoto\Admin_Menu::init();
    }
    
    // Portal Klien
    \PilihFoto\Portal::init();
    
    // CLI
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        \PilihFoto\CLI::init();
    }
} );
