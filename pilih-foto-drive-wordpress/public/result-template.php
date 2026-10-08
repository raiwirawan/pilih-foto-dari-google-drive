<?php
$user = wp_get_current_user();
$client_name = esc_html( $user->display_name );
$nonce = wp_create_nonce('wp_rest');
$rest_url = esc_url_raw( rest_url('pilihfoto/v1/') );
$logout_url = esc_url( wp_logout_url( site_url('/foto/') ) );
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Hasil Edit Foto</title>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo esc_url( PILIH_FOTO_PLUGIN_URL . 'assets/css/app.css' ); ?>">
  <style>
    .dl-btn {
       background: rgba(0,0,0,0.5); color: white;
       border: 1px solid rgba(255,255,255,0.3);
       padding: 8px 16px; border-radius: 20px;
       text-decoration: none; font-weight: 600;
       display: inline-flex; align-items: center; gap: 6px;
    }
    .dl-btn:hover { background: rgba(0,0,0,0.8); }
    .photo-card { cursor: pointer; }
    .photo-card:hover { transform: scale(1.02); }
  </style>
</head>
<body class="pf-app">
  <header class="header-section">
    <div class="container">
      <div style="display: flex; flex-direction: column; gap: var(--space-3);">
        <div>
          <h1 id="title">Hasil Edit: <?php echo $client_name; ?></h1>
          <p class="text-muted" style="margin-bottom: 0;">Galeri hasil akhir foto pilihanmu.</p>
        </div>
        <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
          <a href="<?php echo esc_url( site_url('/foto/') ); ?>" class="btn btn-header" style="text-decoration: none;">Kembali</a>
        </div>
      </div>
    </div>
  </header>

  <div class="container" id="gallerySelectorContainer" style="display: none; margin-bottom: 16px;">
    <select id="gallerySelector" class="form-control" style="max-width: 300px;" onchange="pfLoadGallery(this.value)"></select>
  </div>

  <main class="container">
    <div id="errorBox" class="banner" style="display: none; background: #FDE8E8; color: #D31510;"></div>
    <div class="photo-grid" id="grid"></div>
  </main>

  <!-- Lightbox -->
  <div id="lb" class="lightbox" role="dialog" aria-modal="true">
    <div class="lightbox-header">
      <button class="lightbox-nav" style="position: static; transform: none; background: transparent;" id="closeBtn">✕</button>
    </div>
    <div class="lightbox-content" id="lbContent">
      <button class="lightbox-nav lightbox-prev" id="prevBtn">‹</button>
      <img id="lbimg" class="lightbox-img" alt="">
      <button class="lightbox-nav lightbox-next" id="nextBtn">›</button>
    </div>
    <div class="lightbox-footer">
      <span id="lbname" style="font-weight: 600;"></span>
      <a href="#" id="lbdlBtn" class="dl-btn" target="_blank" rel="noopener noreferrer">📥 Unduh Foto Ini</a>
    </div>
  </div>

  <script>
    const pfClientData = {
        nonce: "<?php echo $nonce; ?>",
        restUrl: "<?php echo $rest_url; ?>",
        siteUrl: "<?php echo esc_url( site_url('/') ); ?>"
    };
  </script>
  <script src="<?php echo esc_url( PILIH_FOTO_PLUGIN_URL . 'assets/js/result.js' ); ?>"></script>
</body>
</html>
