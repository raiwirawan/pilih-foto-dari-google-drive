/* studio-editor.js */
(function() {
  const { apiFetch, esc, openDialog, closeDialog, toast, extractFolderId } = window.StudioApp;
  
  let galleries = [], currentPhotos = [], currentCur = 0;
  
  const $ = id => document.getElementById(id);

  function getEditorKey() {
    if (window.StudioConfig.isAdmin) return null; // Admin uses nonce
    const params = new URLSearchParams(window.location.search);
    const key = params.get('key');
    if (key) {
      localStorage.setItem('st_editor_key', key);
      window.history.replaceState({}, document.title, window.location.pathname);
    }
    return localStorage.getItem('st_editor_key');
  }

  async function init() {
    const key = getEditorKey();
    if (!key && !window.StudioConfig.isAdmin) {
      return renderError('Link tidak valid atau sesi berakhir. Mintalah link editor terbaru dari admin.');
    }
    
    renderApp();
    await loadGalleries();
    
    if (window.StudioConfig.isAdmin) {
      loadAdminPanel();
    }
    
    setInterval(loadGalleries, 15000);
  }

  function renderError(msg) {
    $('st-app').innerHTML = `<div style="padding: 50px; text-align: center; color: red;">${esc(msg)}</div>`;
  }

  function renderApp() {
    $('st-app').innerHTML = `
      <header class="st-header-section">
        <div class="st-container" style="display: flex; flex-direction: column; gap: var(--st-space-3);">
          <h1 id="st-title">Dashboard Editor</h1>
          <p class="st-text-muted" style="margin-bottom: 0;">Pantau dan kelola pilihan foto dari klien.</p>
          <div style="display: flex; gap: var(--st-space-2); margin-top: 16px;">
            <button class="st-btn st-btn-primary" id="st-settingsBtn">💬 Pengaturan WA</button>
          </div>
        </div>
      </header>

      <div class="st-container" id="st-adminPanel" style="display: none; margin-bottom: 24px;"></div>

      <main class="st-container" id="st-main" style="display: flex; flex-wrap: wrap; gap: var(--st-space-4);">
        <div id="st-listPanel" style="flex: 1; min-width: 300px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--st-space-4); flex-wrap: wrap; gap: var(--st-space-3);">
            <h2 style="margin: 0;">Daftar Galeri</h2>
            <div style="display: flex; gap: var(--st-space-2);">
              <button class="st-btn st-btn-primary" style="height: 36px; font-size: 14px;" id="st-newGalleryBtn">+ Galeri Baru</button>
              <button class="st-btn st-btn-outline" style="height: 36px; font-size: 14px;" id="st-refreshBtn">↻ Refresh</button>
            </div>
          </div>
          <div id="st-clientsList">Memuat...</div>
        </div>

        <div id="st-detailPanel" style="flex: 2; min-width: 300px; display: none;">
          <div style="display: flex; align-items: center; gap: var(--st-space-3); margin-bottom: var(--st-space-4);">
            <button class="st-btn st-btn-outline" id="st-closeDetailBtn" style="height: 36px; font-size: 14px;">← Kembali</button>
            <h2 id="st-detailTitle" style="margin: 0;">Detail Galeri</h2>
          </div>
          <div id="st-detailContent"></div>
        </div>
      </main>

      <!-- Dialog Atur Galeri -->
      <div id="st-manageDlg-backdrop" class="st-dialog-backdrop"></div>
      <div id="st-manageDlg" class="st-dialog">
        <h2 id="st-manageTitle">Atur Galeri</h2>
        <form id="st-manageForm">
          <input type="hidden" id="st-mId">
          <div class="st-form-group">
            <label>Nama Klien / Judul Galeri</label>
            <input type="text" id="st-mName" class="st-form-control" required placeholder="mis. Andi & Siska Wedding">
          </div>
          <div class="st-form-group">
            <label>Link / ID Folder Mentah (Google Drive)</label>
            <input type="text" id="st-mLink" class="st-form-control" required placeholder="Paste link folder mentah...">
            <div style="margin-top: 8px; font-size: 14px;" id="st-mLinkResult"></div>
          </div>
          <div class="st-form-group">
            <label>Link / ID Folder Hasil Edit (Opsional)</label>
            <input type="text" id="st-mResultLink" class="st-form-control" placeholder="Paste link folder hasil akhir...">
            <div style="margin-top: 8px; font-size: 14px;" id="st-mResultLinkResult"></div>
          </div>
          <div class="st-form-group">
            <label>Batas Maksimal Foto</label>
            <input type="number" id="st-mLimit" class="st-form-control" min="0" placeholder="0 = tanpa batas">
          </div>
          <div style="display: flex; gap: var(--st-space-2); justify-content: flex-end; margin-top: var(--st-space-5);">
            <button type="button" class="st-btn st-btn-outline" style="color: #e60000; border-color: #e60000; margin-right: auto; display: none;" id="st-mDeleteBtn">Hapus</button>
            <button type="button" class="st-btn st-btn-outline" id="st-mCancelBtn">Batal</button>
            <button type="submit" class="st-btn st-btn-primary" id="st-mSubmit">Simpan</button>
          </div>
        </form>
      </div>

      <!-- Dialog Pengaturan WA Editor -->
      <div id="st-settingsDlg-backdrop" class="st-dialog-backdrop"></div>
      <div id="st-settingsDlg" class="st-dialog">
        <h2>Pengaturan WhatsApp</h2>
        <form id="st-settingsForm">
          <div class="st-form-group">
            <label>Nomor WA Editor</label>
            <input type="text" id="st-sWa" class="st-form-control" placeholder="mis. 6281234567890">
            <small class="st-text-muted">Awali dengan kode negara tanpa + atau 0. Kosongkan untuk menonaktifkan tombol WA di klien.</small>
          </div>
          <div style="display: flex; gap: var(--st-space-2); justify-content: flex-end; margin-top: var(--st-space-4);">
            <button type="button" class="st-btn st-btn-outline" id="st-sCancelBtn">Batal</button>
            <button type="submit" class="st-btn st-btn-primary" id="st-sSubmit">Simpan</button>
          </div>
        </form>
      </div>

      <!-- Lightbox Editor -->
      <div id="st-lb" class="st-lightbox" role="dialog">
        <div class="st-lightbox-header">
          <button class="st-lightbox-nav" style="position: static; transform: none; background: transparent;" id="st-lbCloseBtn">✕</button>
        </div>
        <div class="st-lightbox-content" id="st-lbContent">
          <button class="st-lightbox-nav st-lightbox-prev" id="st-lbPrevBtn">‹</button>
          <img id="st-lbimg" class="st-lightbox-img" alt="">
          <button class="st-lightbox-nav st-lightbox-next" id="st-lbNextBtn">›</button>
        </div>
        <div class="st-lightbox-footer">
          <span id="st-lbname" style="font-weight: 600;"></span>
        </div>
      </div>
    `;
    
    bindEvents();
  }

  async function loadAdminPanel() {
    try {
      const res = await apiFetch('/editor/key');
      const box = $('st-adminPanel');
      box.style.display = 'block';
      box.innerHTML = `
        <div style="background: var(--st-surface); border: 1px solid var(--st-border); border-radius: var(--st-radius-md); padding: 16px;">
          <h3 style="margin-top:0; margin-bottom:8px;">Akses Editor (Khusus Admin)</h3>
          <p class="st-text-muted" style="margin-bottom:12px;">Berikan link ini kepada editor. Mereka tidak perlu login WordPress.</p>
          <div class="st-link-box">
            <input type="text" readonly value="${res.url}" style="width:100%; background:transparent; border:none; color:var(--st-text); outline:none;" id="st-editorLinkInput">
          </div>
          <div style="display:flex; gap:8px;">
            <button class="st-btn st-btn-outline" style="height:32px; font-size:13px;" id="st-copyEditorLinkBtn">Salin Link Editor</button>
            <button class="st-btn st-btn-outline" style="height:32px; font-size:13px; color:#e60000; border-color:#e60000;" id="st-rotateKeyBtn">Rotasi Kunci</button>
          </div>
        </div>
      `;
      
      $('st-copyEditorLinkBtn').onclick = () => {
        $('st-editorLinkInput').select();
        document.execCommand('copy');
        toast('Link editor disalin!');
      };
      
      $('st-rotateKeyBtn').onclick = async () => {
        if (!confirm('Perhatian: Melakukan rotasi kunci akan membuat link lama hangus. Semua editor yang sedang login akan terkeluar kecuali mereka membuka link yang baru. Lanjutkan?')) return;
        try {
          const resRotate = await apiFetch('/editor/key/rotate', { method: 'POST' });
          toast('Kunci berhasil dirotasi. Silakan salin link yang baru.');
          loadAdminPanel();
        } catch (e) {
          toast(e.message);
        }
      };
    } catch (e) {
      console.error(e);
    }
  }

  async function loadGalleries() {
    try {
      galleries = await apiFetch('/editor/galleries');
      renderGalleries();
    } catch (e) {
      $('st-clientsList').textContent = e.message;
    }
  }

  function renderGalleries() {
    const list = $('st-clientsList');
    if (!galleries.length) {
      list.innerHTML = '<p class="st-text-muted">Belum ada galeri.</p>';
      return;
    }
    
    list.innerHTML = '';
    galleries.forEach(g => {
      const card = document.createElement('div');
      card.className = 'st-client-card';
      
      let statusBadge = '';
      if (g.status === 'submitted') statusBadge = '<span class="st-badge-status st-status-submitted">Terkirim 🔒</span>';
      else if (g.status === 'draft') statusBadge = '<span class="st-badge-status st-status-draft">Draft</span>';
      else if (g.status === 'reopened') statusBadge = '<span class="st-badge-status st-status-reopened">Dibuka Kembali</span>';
      else statusBadge = '<span class="st-badge-status st-status-unstarted">Belum Mulai</span>';

      let dateStr = g.updated_at ? new Date(g.updated_at).toLocaleString('id-ID') : '-';
      const limitStr = g.max_select > 0 ? ` / ${g.max_select}` : '';

      card.innerHTML = `
        <div class="st-client-card-header">
          <h3 class="st-client-card-title">${esc(g.title)} ${g.result_folder_id ? '✅' : ''}</h3>
          ${statusBadge}
        </div>
        <div class="st-text-muted" style="font-size: 14px; display: grid; gap: 4px;">
          <span>Terpilih: <strong>${g.selected_count}${limitStr}</strong> foto</span>
          <span>Update: ${dateStr}</span>
        </div>
        <div style="display: flex; gap: var(--st-space-2); margin-top: var(--st-space-2); flex-wrap: wrap;">
          <button class="st-btn st-btn-primary st-view-btn" data-id="${g.id}" ${g.selected_count === 0 ? 'disabled' : ''}>Lihat Pilihan</button>
          <button class="st-btn st-btn-outline st-edit-btn" data-id="${g.id}">Atur</button>
          <button class="st-btn st-btn-outline st-copy-btn" data-link="${esc(g.link)}">Salin Link Klien</button>
          ${g.status === 'submitted' ? `<button class="st-btn st-btn-outline st-reopen-btn" data-id="${g.id}">Buka Akses</button>` : ''}
        </div>
      `;
      list.appendChild(card);
    });

    list.querySelectorAll('.st-view-btn').forEach(b => b.onclick = (e) => viewGallery(e.target.dataset.id));
    list.querySelectorAll('.st-edit-btn').forEach(b => b.onclick = (e) => openManageDialog(e.target.dataset.id));
    list.querySelectorAll('.st-copy-btn').forEach(b => b.onclick = (e) => {
      const link = e.target.dataset.link;
      if (!link) return toast('Link tidak tersedia');
      navigator.clipboard.writeText(link).then(() => toast('Link klien disalin!'));
    });
    list.querySelectorAll('.st-reopen-btn').forEach(b => b.onclick = (e) => reopenGallery(e.target.dataset.id));
  }

  // --- Manage Gallery ---
  let validatedFolderId = null;
  let validatedResultFolderId = null;

  function openManageDialog(id) {
    validatedFolderId = null;
    validatedResultFolderId = null;
    $('st-mLinkResult').innerHTML = '';
    $('st-mLink').value = '';
    $('st-mResultLinkResult').innerHTML = '';
    $('st-mResultLink').value = '';
    
    if (id && id !== 'new') {
      const g = galleries.find(x => x.id == id);
      $('st-manageTitle').textContent = 'Atur Galeri: ' + g.title;
      $('st-mId').value = g.id;
      $('st-mName').value = g.title;
      $('st-mLimit').value = g.max_select || 0;
      $('st-mDeleteBtn').style.display = 'block';
      
      if (g.source_folder_id) {
        validatedFolderId = g.source_folder_id;
        $('st-mLink').value = `https://drive.google.com/drive/folders/${g.source_folder_id}`;
        $('st-mLinkResult').innerHTML = `✅ ID Folder: <strong>${g.source_folder_id}</strong>`;
      }
      if (g.result_folder_id) {
        validatedResultFolderId = g.result_folder_id;
        $('st-mResultLink').value = `https://drive.google.com/drive/folders/${g.result_folder_id}`;
        $('st-mResultLinkResult').innerHTML = `✅ ID Folder: <strong>${g.result_folder_id}</strong>`;
      }
    } else {
      $('st-manageTitle').textContent = 'Buat Galeri Baru';
      $('st-mId').value = '';
      $('st-mName').value = '';
      $('st-mLimit').value = 0;
      $('st-mDeleteBtn').style.display = 'none';
    }
    openDialog('st-manageDlg');
  }

  async function checkDriveLink(val, outEl, onValid) {
    const ext = extractFolderId(val);
    if (!ext) {
      outEl.innerHTML = '<span style="color:#e60000">❌ Link/ID tidak valid</span>';
      return onValid(null);
    }
    outEl.innerHTML = '⏳ Mengecek folder...';
    try {
      const d = await apiFetch('/editor/drive/resolve', { method: 'POST', body: JSON.stringify({ input: ext }) });
      outEl.innerHTML = `✅ <strong>${esc(d.name)}</strong> — ${d.photoCount} foto`;
      onValid(d.folderId);
    } catch (err) {
      outEl.innerHTML = `<span style="color:#e60000">❌ Error: ${esc(err.message)}</span>`;
      onValid(null);
    }
  }

  async function reopenGallery(id) {
    if (!confirm('Buka kembali akses untuk klien ini agar bisa mengubah pilihannya?')) return;
    try {
      await apiFetch(`/editor/galleries/${id}/reopen`, { method: 'POST' });
      toast('Akses dibuka kembali');
      loadGalleries();
    } catch (e) {
      toast(e.message);
    }
  }

  async function deleteGallery() {
    const id = $('st-mId').value;
    if (!id) return;
    if (!confirm(`Yakin ingin menghapus galeri ini?`)) return;
    try {
      await apiFetch(`/editor/galleries/${id}`, { method: 'DELETE' });
      toast('Galeri dihapus');
      closeDialog('st-manageDlg');
      loadGalleries();
      $('st-detailPanel').style.display = 'none';
      $('st-listPanel').style.display = 'block';
    } catch (e) {
      toast(e.message);
    }
  }

  // --- Detail View ---
  async function viewGallery(id) {
    if (window.innerWidth < 900) {
      $('st-listPanel').style.display = 'none';
    }
    $('st-detailPanel').style.display = 'block';
    $('st-detailContent').innerHTML = '<p>Memuat...</p>';
    
    try {
      const data = await apiFetch(`/editor/galleries/${id}`);
      $('st-detailTitle').textContent = data.title;
      currentPhotos = data.photos;
      
      let noteHtml = data.note ? `<div class="st-banner">📝 <strong>Catatan Klien:</strong>&nbsp; ${esc(data.note)}</div>` : '';
      
      let gridHtml = '<div class="st-photo-grid" style="margin-top: 16px;">';
      data.photos.forEach((p, i) => {
        gridHtml += `
          <div class="st-photo-card" onclick="window.StudioApp.editorOpenLb(${i})">
            <img src="${p.src}" loading="lazy">
            <div class="st-badge-name" style="opacity: 1; bottom: 8px;">${esc(p.name)}</div>
          </div>
        `;
      });
      gridHtml += '</div>';

      let csv = 'nama_file,id\n' + data.photos.map(p => `"${p.name}","${p.id}"`).join('\n');
      const csvBlob = new Blob([csv], { type: 'text/csv' });
      const csvUrl = URL.createObjectURL(csvBlob);

      $('st-detailContent').innerHTML = `
        ${noteHtml}
        <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
          <a href="${csvUrl}" download="pilihan_${id}.csv" class="st-btn st-btn-outline" style="height: 32px; font-size: 13px;">📥 Unduh CSV</a>
          <button class="st-btn st-btn-outline st-copy-csv-btn" style="height: 32px; font-size: 13px;" data-csv="${esc(csv.replace(/\n/g, '\\n'))}">📋 Salin Teks</button>
        </div>
        ${gridHtml}
      `;
      
      document.querySelector('.st-copy-csv-btn').onclick = (e) => {
        navigator.clipboard.writeText(e.target.dataset.csv.replace(/\\n/g, '\n')).then(() => toast('Disalin'));
      };
    } catch (e) {
      $('st-detailContent').innerHTML = `<span style="color:#e60000">Error: ${e.message}</span>`;
    }
  }
  
  window.StudioApp.editorOpenLb = (i) => { currentCur = i; $('st-lb').classList.add('st-open'); showLb(); };

  function showLb() {
    const p = currentPhotos[currentCur];
    $('st-lbimg').src = p.src.replace('w=400', 'w=1600').replace('w=800', 'w=1600');
    $('st-lbname').textContent = p.name;
  }

  function bindEvents() {
    $('st-refreshBtn').onclick = () => { toast('Memuat...'); loadGalleries(); };
    $('st-newGalleryBtn').onclick = () => openManageDialog('new');
    
    $('st-mLink').addEventListener('input', e => checkDriveLink(e.target.value, $('st-mLinkResult'), (id) => validatedFolderId = id));
    $('st-mResultLink').addEventListener('input', e => checkDriveLink(e.target.value, $('st-mResultLinkResult'), (id) => validatedResultFolderId = id));
    
    $('st-mCancelBtn').onclick = () => closeDialog('st-manageDlg');
    $('st-mDeleteBtn').onclick = deleteGallery;
    
    $('st-manageForm').onsubmit = async (e) => {
      e.preventDefault();
      $('st-mSubmit').disabled = true;
      try {
        const id = $('st-mId').value;
        const body = {
          title: $('st-mName').value,
          max_select: parseInt($('st-mLimit').value) || 0
        };
        if (validatedFolderId) body.folder_id = validatedFolderId; // create API expects folder_id
        if (validatedFolderId) body.source_folder_id = validatedFolderId; // patch API expects source_folder_id
        if (validatedResultFolderId !== null) body.result_folder_id = validatedResultFolderId;

        if (id) {
          await apiFetch(`/editor/galleries/${id}`, { method: 'PATCH', body: JSON.stringify(body) });
          toast('Berhasil disimpan');
        } else {
          if (!validatedFolderId) throw new Error('Folder sumber wajib diisi dan valid');
          await apiFetch(`/editor/galleries`, { method: 'POST', body: JSON.stringify(body) });
          toast('Galeri dibuat');
        }
        closeDialog('st-manageDlg');
        loadGalleries();
      } catch (err) {
        toast(err.message);
      } finally {
        $('st-mSubmit').disabled = false;
      }
    };
    
    $('st-settingsBtn').onclick = async () => {
      try {
        const res = await apiFetch('/editor/settings');
        $('st-sWa').value = res.whatsapp || '';
        openDialog('st-settingsDlg');
      } catch (e) { toast(e.message); }
    };
    
    $('st-sCancelBtn').onclick = () => closeDialog('st-settingsDlg');
    
    $('st-settingsForm').onsubmit = async (e) => {
      e.preventDefault();
      $('st-sSubmit').disabled = true;
      try {
        const wa = $('st-sWa').value.replace(/[^0-9]/g, '');
        await apiFetch('/editor/settings', { method: 'PATCH', body: JSON.stringify({ whatsapp: wa }) });
        toast('Pengaturan WA disimpan');
        closeDialog('st-settingsDlg');
      } catch (err) {
        toast(err.message);
      } finally {
        $('st-sSubmit').disabled = false;
      }
    };
    
    $('st-closeDetailBtn').onclick = () => {
      $('st-detailPanel').style.display = 'none';
      if (window.innerWidth < 900) $('st-listPanel').style.display = 'block';
    };
    
    const stepLb = d => { currentCur = (currentCur + d + currentPhotos.length) % currentPhotos.length; showLb(); };
    $('st-lbPrevBtn').onclick = () => stepLb(-1);
    $('st-lbNextBtn').onclick = () => stepLb(1);
    $('st-lbCloseBtn').onclick = () => $('st-lb').classList.remove('st-open');
    
    document.addEventListener('keydown', e => {
      if (!$('st-lb').classList.contains('st-open')) return;
      if (e.key === 'ArrowLeft') stepLb(-1);
      if (e.key === 'ArrowRight') stepLb(1);
      if (e.key === 'Escape') $('st-lb').classList.remove('st-open');
    });
  }

  init();
})();
