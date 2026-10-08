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
  <title>Pilih Foto untuk Diedit</title>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo esc_url( PILIH_FOTO_PLUGIN_URL . 'assets/css/app.css' ); ?>">
</head>
<body class="pf-app">
  <header class="header-section">
    <div class="container">
      <div style="display: flex; flex-direction: column; gap: var(--space-3);">
        <div>
          <h1 id="title">Klien: <?php echo $client_name; ?></h1>
          <p class="text-muted" style="margin-bottom: 0;">Ketuk foto yang ingin diedit. Pilihan tersimpan otomatis.</p>
        </div>
        <div style="display: flex;">
          <a href="<?php echo $logout_url; ?>" class="btn btn-header">Keluar</a>
        </div>
      </div>
    </div>
  </header>

  <div class="container" id="resultBanner" style="display: none; margin-top: var(--space-4); margin-bottom: 16px;">
    <div style="background: rgba(37, 211, 102, 0.1); border: 1px solid rgba(37, 211, 102, 0.3); color: #4ade80; padding: 16px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
      <div style="display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 15px;">
        <span style="font-size: 20px;">🎉</span> Foto hasil edit sudah tersedia!
      </div>
      <a href="<?php echo esc_url( site_url('/foto/hasil/') ); ?>" class="btn btn-primary" style="background: #25D366; border-color: #25D366; color: #111; font-weight: 700; white-space: nowrap; flex-shrink: 0;">Lihat Hasil Edit</a>
    </div>
  </div>

  <div class="container" id="bannerContainer" style="display: none;">
    <div class="banner" id="bannerContent"></div>
  </div>

  <div class="container" id="gallerySelectorContainer" style="display: none; margin-bottom: 16px;">
    <select id="gallerySelector" class="form-control" style="max-width: 300px;" onchange="pfLoadGallery(this.value)"></select>
  </div>

  <main class="container">
    <div id="errorBox" class="banner" style="display: none; background: #FDE8E8; color: #D31510;"></div>
    
    <div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-4);">
      <button class="btn btn-primary" id="allBtn">Pilih Semua</button>
      <button class="btn btn-outline" id="resetBtn">Hapus Pilihan</button>
    </div>

    <div class="photo-grid" id="grid"></div>
  </main>

  <div class="bottom-bar">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <strong><span id="selCount">0</span></strong> terpilih
        <span id="maxLabel" class="text-muted" style="font-size: 14px; margin-left: 8px;"></span>
      </div>
      <button class="btn btn-primary" id="sendBtn" disabled>Kirim ke Editor</button>
    </div>
  </div>

  <!-- Dialog Submit -->
  <dialog id="dlg">
    <div id="dlgForm">
      <h2>Kirim Pilihan ke Editor</h2>
      <p class="text-muted" style="font-size: 14px;">Kamu memilih <span id="dlgCount" style="font-weight:bold; color:var(--color-text);"></span> foto.</p>
      <textarea id="note" placeholder="Catatan tambahan untuk editor (opsional)..."></textarea>
      <div style="display: flex; gap: var(--space-2); justify-content: flex-end; flex-wrap: wrap;">
        <button class="btn btn-outline" id="closeDlgBtn">Batal</button>
        <button class="btn btn-primary" id="confirmSubmitBtn">Kirim Sekarang</button>
      </div>
    </div>
    <div id="dlgSuccess" style="display: none; text-align: center;">
      <h2>Berhasil Terkirim! 🎉</h2>
      <p class="text-muted" style="margin-bottom: 24px;">Pilihan kamu sudah dikunci. Beri tahu editor agar pesanan segera diproses.</p>
      <a href="#" id="waBtn" target="_blank" class="btn btn-primary" style="background: #25D366; border-color: #25D366; color: white; width: 100%; display: none; align-items: center; justify-content: center;">
        💬 Hubungi Editor via WhatsApp
      </a>
      <button class="btn btn-outline" style="margin-top: 16px; width: 100%;" onclick="document.getElementById('dlg').close();">Tutup</button>
    </div>
  </dialog>

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
      <button class="btn btn-primary" id="lbselBtn">Pilih foto ini</button>
    </div>
  </div>

  <div id="toast" class="toast"></div>

  <script>
    const pfClientData = {
        nonce: "<?php echo $nonce; ?>",
        restUrl: "<?php echo $rest_url; ?>",
        siteUrl: "<?php echo esc_url( site_url('/') ); ?>"
    };
  </script>
  <script src="<?php echo esc_url( PILIH_FOTO_PLUGIN_URL . 'assets/js/client.js' ); ?>"></script>
</body>
</html>
