<?php
namespace PilihFoto;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

class CLI {
    public static function init() {
        \WP_CLI::add_command( 'pilihfoto import', array( __CLASS__, 'import_data' ) );
    }

    public static function import_data( $args, $assoc_args ) {
        // Skrip untuk mengimpor dari data/users.json dan data/selections.json
        // Akan ditambahkan sesuai kebutuhan migrasi
        \WP_CLI::success( 'Struktur perintah import siap.' );
    }
}
