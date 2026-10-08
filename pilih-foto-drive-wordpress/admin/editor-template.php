<div class="wrap pf-app">
  <header class="header-section">
    <div class="container">
      <div style="display: flex; flex-direction: column; gap: var(--space-3);">
        <div>
          <h1>Dashboard Editor</h1>
          <p class="text-muted" style="margin: 0;">Pantau dan kelola pilihan foto dari klien.</p>
        </div>
        <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
          <button class="btn" style="background: #25D366; color: white; border: none;" onclick="pfOpenSettings()">💬 Pengaturan WA</button>
        </div>
      </div>
    </div>
  </header>

  <main class="container" id="pf-main">
    <div id="listPanel">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-3);">
        <h2 style="margin: 0;">Daftar Klien</h2>
        <div style="display: flex; gap: var(--space-2);">
          <button class="btn btn-primary" style="height: 36px; font-size: 14px;" onclick="pfOpenManageDialog()">+ Klien Baru</button>
          <button class="btn btn-outline" style="height: 36px; font-size: 14px;" onclick="pfLoadClients()">↻ Refresh</button>
        </div>
      </div>
      <div id="clientsList">Memuat...</div>
    </div>

    <div id="detailPanel">
      <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-4);">
        <button class="btn btn-outline back-btn" style="height: 36px; font-size: 14px; padding: 0 12px;" onclick="pfCloseDetail()">← Kembali</button>
        <h2 id="detailTitle" style="margin: 0;">Detail Klien</h2>
        <span id="detailBadge"></span>
      </div>
      <div id="detailContent"></div>
    </div>
  </main>

  <!-- Dialog Atur Klien -->
  <dialog id="manageDlg">
    <h2 id="manageTitle">Atur Klien</h2>
    <form id="manageForm">
      <input type="hidden" id="mId">
      <div class="form-group">
        <label>ID Klien (Username)</label>
        <input type="text" id="mUsername" class="form-control" required placeholder="mis. andi_wedding">
        <small class="text-muted">Hanya bisa diisi saat membuat baru</small>
      </div>
      <div class="form-group">
        <label>Nama Tampilan</label>
        <input type="text" id="mName" class="form-control" required placeholder="mis. Andi & Siska Wedding">
      </div>
      <div class="form-group" id="mPasswordGroup">
        <label>Password</label>
        <input type="text" id="mPassword" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
      </div>
      <div class="form-group">
        <label>Link Folder Google Drive (Mentah)</label>
        <input type="text" id="mLink" class="form-control" placeholder="Paste link folder mentah...">
        <div style="margin-top: 8px; font-size: 14px;" id="mLinkResult"></div>
      </div>
      <div class="form-group">
        <label>Link Folder Hasil Edit (Opsional)</label>
        <input type="text" id="mResultLink" class="form-control" placeholder="Paste link folder hasil akhir...">
        <div style="margin-top: 8px; font-size: 14px;" id="mResultLinkResult"></div>
      </div>
      <div class="form-group">
        <label>Batas Pilihan Foto</label>
        <input type="number" id="mLimit" class="form-control" min="0" placeholder="0 = tanpa batas">
      </div>
      <div style="display: flex; gap: var(--space-2); justify-content: flex-end; margin-top: var(--space-5);">
        <button type="button" class="btn btn-outline" style="color: #D31510; border-color: #D31510; margin-right: auto; display: none;" id="mDeleteBtn" onclick="pfDeleteClient()">Hapus Klien</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('manageDlg').close()">Batal</button>
        <button type="submit" class="btn btn-primary" id="mSubmit">Simpan</button>
      </div>
    </form>
  </dialog>

  <!-- Dialog Pengaturan WA Editor -->
  <dialog id="settingsDlg">
    <h2>Pengaturan WhatsApp</h2>
    <form id="settingsForm">
      <div class="form-group">
        <label>Nomor WA Editor</label>
        <input type="text" id="sWa" class="form-control" placeholder="mis. 6281234567890">
        <small class="text-muted">Awali dengan kode negara tanpa + atau 0. Kosongkan untuk menonaktifkan tombol WA di klien.</small>
      </div>
      <div style="display: flex; gap: var(--space-2); justify-content: flex-end; margin-top: var(--space-4);">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('settingsDlg').close()">Batal</button>
        <button type="submit" class="btn btn-primary" id="sSubmit">Simpan</button>
      </div>
    </form>
  </dialog>

  <div id="pf-toast" class="toast"></div>
</div>
