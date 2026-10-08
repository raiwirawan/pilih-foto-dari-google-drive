<?php
/**
 * Template Name: Studio Client
 * 
 * Template untuk halaman klien memilih foto. 
 * Kredensial telah dibuang, klien mengakses melalui URL unik/parameter, misalnya ?gallery_id=123
 */

// Mengambil ID galeri dari URL
$gallery_id = isset($_GET['gallery_id']) ? intval($_GET['gallery_id']) : 0;

if ( ! $gallery_id ) {
    wp_die('Galeri tidak ditemukan. Pastikan link yang Anda buka benar.');
}

get_header(); ?>

<!-- Menyimpan data galeri ke window agar bisa dibaca oleh JS -->
<script>
    window.StudioGalleryID = <?php echo json_encode($gallery_id); ?>;
</script>

<div id="studio-client-app" class="studio-container">
    <!-- UI Klien akan di-render di sini oleh JavaScript -->
    <div style="padding: 50px; text-align: center;">
        <h2>Memuat Galeri Foto Anda...</h2>
    </div>
</div>

<?php get_footer(); ?>
