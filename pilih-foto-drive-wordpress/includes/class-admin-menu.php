<?php
namespace PilihFoto;

class Admin_Menu {
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    public static function add_menu() {
        add_menu_page(
            'Pilih Foto',
            'Pilih Foto',
            'pf_manage_galleries',
            'pilih-foto',
            array( __CLASS__, 'render_page' ),
            'dashicons-images-alt2',
            30
        );
    }

    public static function enqueue_assets( $hook ) {
        if ( $hook !== 'toplevel_page_pilih-foto' ) {
            return;
        }

        wp_enqueue_style( 'pf-editor-style', PILIH_FOTO_PLUGIN_URL . 'assets/css/app.css', array(), PILIH_FOTO_VERSION );
        wp_enqueue_script( 'pf-editor-script', PILIH_FOTO_PLUGIN_URL . 'assets/js/editor.js', array(), PILIH_FOTO_VERSION, true );

        wp_localize_script( 'pf-editor-script', 'pfEditorData', array(
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'restUrl' => esc_url_raw( rest_url( 'pilihfoto/v1/' ) ),
            'wa' => get_option( 'pf_editor_wa', '' )
        ) );
    }

    public static function render_page() {
        // Render the editor HTML view here. We will load it from a template file.
        $template = PILIH_FOTO_PLUGIN_DIR . 'admin/editor-template.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo "Template not found.";
        }
    }
}
