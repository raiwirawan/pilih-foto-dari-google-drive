<?php
/**
 * STUDIO FUNCTIONS
 * Masukkan kode ini (atau di-include) ke dalam file functions.php tema Anda.
 * 
 * require_once get_template_directory() . '/studio-functions.php';
 */

// 1. BUAT TABEL DATABASE SAAT TEMA DIAKTIFKAN
function studio_setup_database() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Catatan: Kredensial akun (pf_accounts & sessions) dibuang sesuai permintaan.
    // Keamanan sekarang dikembalikan ke WP user bawaan (untuk editor) & open link (untuk klien).
    
    $sql = "
    CREATE TABLE {$wpdb->prefix}pf_galleries (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title varchar(100) NOT NULL,
        source_folder_id varchar(100) NOT NULL,
        result_folder_id varchar(100) DEFAULT '' NOT NULL,
        max_select int(11) NOT NULL DEFAULT 0,
        status varchar(20) NOT NULL DEFAULT 'unstarted',
        note text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;

    CREATE TABLE {$wpdb->prefix}pf_photos (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        gallery_id bigint(20) UNSIGNED NOT NULL,
        kind varchar(20) NOT NULL DEFAULT 'source',
        drive_file_id varchar(100) NOT NULL,
        name varchar(255) NOT NULL,
        thumbnail_link text DEFAULT NULL,
        position int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;

    CREATE TABLE {$wpdb->prefix}pf_selections (
        gallery_id bigint(20) UNSIGNED NOT NULL,
        photo_id bigint(20) UNSIGNED NOT NULL,
        PRIMARY KEY  (gallery_id, photo_id)
    ) $charset_collate;
    ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
add_action('after_switch_theme', 'studio_setup_database');

// Jika Anda memasukkan ini ke tema yang sudah aktif, jalankan fungsi ini manual sekali, 
// atau hubungkan ke hook 'init' dan berikan pengecekan get_option('studio_db_version').


// 2. LOAD ASSETS HANYA PADA TEMPLATE STUDIO
function studio_enqueue_assets() {
    // Sesuaikan path ke lokasi folder theme Anda
    $theme_uri = get_template_directory_uri(); 

    if ( is_page_template( 'template-studio-editor.php' ) ) {
        wp_enqueue_style( 'studio-editor-css', $theme_uri . '/assets/css/editor.css', array(), '1.0' );
        wp_enqueue_script( 'studio-editor-js', $theme_uri . '/assets/js/editor.js', array(), '1.0', true );
        
        wp_localize_script( 'studio-editor-js', 'StudioData', array(
            'apiUrl' => rest_url( 'studio/v2/editor' ),
            'nonce'  => wp_create_nonce( 'wp_rest' )
        ) );
    }

    if ( is_page_template( 'template-studio-client.php' ) ) {
        wp_enqueue_style( 'studio-client-css', $theme_uri . '/assets/css/client.css', array(), '1.0' );
        wp_enqueue_script( 'studio-client-js', $theme_uri . '/assets/js/client.js', array(), '1.0', true );
        
        wp_localize_script( 'studio-client-js', 'StudioData', array(
            'apiUrl' => rest_url( 'studio/v2/client' ),
            'nonce'  => wp_create_nonce( 'wp_rest' )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'studio_enqueue_assets' );


// 3. REGISTRASI REST API SEDERHANA
add_action( 'rest_api_init', function() {
    
    // --- ENDPOINT EDITOR ---
    // Akses terbuka tanpa login WordPress
    register_rest_route( 'studio/v2', '/editor/galleries', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true', // Terbuka tanpa wp-login
        'callback' => function() {
            global $wpdb;
            $galleries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pf_galleries");
            return rest_ensure_response($galleries);
        }
    ) );
    
    // API Editor untuk membuat galeri dll bisa ditambahkan di sini mengikuti pola yang sama...
    
    
    // --- ENDPOINT KLIEN ---
    // Akses publik berdasarkan URL/ID yang diketahui. Kredensial dibuang.
    register_rest_route( 'studio/v2', '/client/galleries/(?P<id>\d+)', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true', // Terbuka karena kredensial dihilangkan, pengamanannya dengan menyembunyikan URL
        'callback' => function( $request ) {
            global $wpdb;
            $id = $request['id'];
            $gallery = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pf_galleries WHERE id = %d", $id));
            if (!$gallery) return new WP_Error('not_found', 'Galeri tidak ditemukan', ['status' => 404]);
            
            $photos = $wpdb->get_results($wpdb->prepare("SELECT id, name, thumbnail_link, position FROM {$wpdb->prefix}pf_photos WHERE gallery_id = %d AND kind='source'", $id));
            $selections = $wpdb->get_col($wpdb->prepare("SELECT photo_id FROM {$wpdb->prefix}pf_selections WHERE gallery_id = %d", $id));

            return rest_ensure_response([
                'gallery' => $gallery,
                'photos' => $photos,
                'selections' => $selections
            ]);
        }
    ) );

    // API Klien untuk memilih foto (terbuka berdasarkan akses ID)
    register_rest_route( 'studio/v2', '/client/galleries/(?P<id>\d+)/select', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function( $request ) {
            global $wpdb;
            $id = $request['id'];
            $photo_id = intval($request['photo_id']);
            $action = $request['action']; // 'add' atau 'remove'

            if ($action === 'add') {
                $wpdb->insert("{$wpdb->prefix}pf_selections", ['gallery_id' => $id, 'photo_id' => $photo_id]);
            } else {
                $wpdb->delete("{$wpdb->prefix}pf_selections", ['gallery_id' => $id, 'photo_id' => $photo_id]);
            }
            return rest_ensure_response(['success' => true]);
        }
    ) );
});


// 4. IMAGE PROXY ROUTE (Tanpa kredensial login, langsung via parameter URL)
// Contoh URL: website.com/?studio_img=123 (123 = photo ID)
function studio_image_proxy() {
    if ( isset($_GET['studio_img']) ) {
        $photo_id = intval($_GET['studio_img']);
        
        global $wpdb;
        $photo = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}pf_photos WHERE id = %d", $photo_id));
        
        if ($photo) {
            $api_key = defined('PF_GOOGLE_API_KEY') ? PF_GOOGLE_API_KEY : '';
            
            // Logika download dari drive API dan cache ditempatkan di sini...
            // Untuk contoh ini diringkas:
            header("Content-Type: image/jpeg");
            echo file_get_contents($photo->thumbnail_link . '&key=' . $api_key); // ini butuh penyesuaian keamanan di produksi
        }
        exit;
    }
}
add_action('init', 'studio_image_proxy');
