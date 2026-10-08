/* studio-client.js */
(function() {
  const { apiFetch, esc, openDialog, closeDialog, toast } = window.StudioApp;
  
  let gallery = null, photos = [], sel = new Set(), cur = 0, statusData = { status: 'unstarted', selectedIds: [], note: '' };
  let isLocked = false, saveTimeout = null, observer = null;
  
  const $ = id => document.getElementById(id);
  
  function getUrlToken() {
    const params = new URLSearchParams(window.location.search);
    const k = params.get('k');
    if (k) {
      sessionStorage.setItem('st_token', k);
      window.history.replaceState({}, document.title, window.location.pathname);
    }
    return sessionStorage.getItem('st_token');
  }

  async function init() {
    const token = getUrlToken();
    if (!token) return renderError('Link tidak valid. Pastikan Anda membuka link dari editor.');
    
    try {
      gallery = await apiFetch('/client/gallery');
      statusData = { status: gallery.status, note: gallery.note, selectedIds: gallery.selectedIds || [] };
      sel = new Set(statusData.selectedIds);
      isLocked = statusData.status === 'submitted';
      
      renderApp();
      
      photos = await apiFetch('/client/photos');
      $('st-status').textContent = photos.length ? `${photos.length} foto ditemukan.` : 'Belum ada foto di folder ini.';
      renderSkeleton();
      
    } catch (err) {
      renderError(err.message);
    }
  }

  function renderError(msg) {
    $('st-app').innerHTML = `<div style="padding: 50px; text-align: center; color: red;">${esc(msg)}</div>`;
  }

  function renderApp() {
    $('st-app').innerHTML = `
      <header class="st-header-section">
        <div class="st-container" style="display: flex; flex-direction: column; gap: var(--st-space-3);">
          <h1 id="st-title">${esc(gallery.title)}</h1>
          <p class="st-text-muted" style="margin-bottom: 0;">Ketuk foto yang ingin diedit. Pilihan tersimpan otomatis.</p>
        </div>
      </header>

      ${gallery.hasResult ? `
      <div class="st-container" id="st-resultBanner" style="margin-bottom: 16px;">
        <div style="background: rgba(37, 211, 102, 0.1); border: 1px solid rgba(37, 211, 102, 0.3); color: #4ade80; padding: 16px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
          <div style="display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 15px;">
            <span style="font-size: 20px;">🎉</span> Foto hasil edit sudah tersedia!
          </div>
          <button class="st-btn st-btn-primary" id="st-showResultBtn" style="background: #25D366; border-color: #25D366; color: #111; font-weight: 700;">Lihat Hasil Edit</button>
        </div>
      </div>
      ` : ''}

      <div class="st-container" id="st-bannerContainer" style="display: none;">
        <div class="st-banner" id="st-bannerContent"></div>
      </div>

      <div class="st-container">
        <div id="st-status" class="st-text-muted st-mb-4" role="status">Memuat data foto...</div>
      </div>

      <main class="st-container st-photo-grid" id="st-grid"></main>

      <div class="st-bottom-bar">
        <div class="st-bottom-bar-title">
          <span id="st-count" aria-live="polite">0 foto dipilih</span>
          <div class="st-bottom-bar-progress" id="st-progressWrap" style="display: none;"><div class="st-bottom-bar-progress-fill" id="st-progressFill"></div></div>
        </div>
        <div class="st-bottom-bar-actions">
          <button class="st-btn st-btn-outline" id="st-allBtn">Pilih semua</button>
          <button class="st-btn st-btn-outline" id="st-resetBtn">Hapus pilihan</button>
          <button class="st-btn st-btn-primary" id="st-sendBtn" disabled>Kirim ke editor</button>
        </div>
      </div>

      <!-- Lightbox -->
      <div id="st-lb" class="st-lightbox" role="dialog">
        <div class="st-lightbox-header">
          <button class="st-lightbox-nav" style="position: static; transform: none; background: transparent;" id="st-closeBtn">✕</button>
        </div>
        <div class="st-lightbox-content" id="st-lbContent">
          <button class="st-lightbox-nav st-lightbox-prev" id="st-prevBtn">‹</button>
          <img id="st-lbimg" class="st-lightbox-img" alt="">
          <button class="st-lightbox-nav st-lightbox-next" id="st-nextBtn">›</button>
        </div>
        <div class="st-lightbox-footer">
          <span id="st-lbname" style="font-weight: 600;"></span>
          <button class="st-btn st-btn-primary" id="st-lbselBtn"></button>
          <a id="st-lbDownloadBtn" target="_blank" class="st-btn st-btn-outline" style="display: none;">Unduh</a>
        </div>
      </div>

      <!-- Dialog Submit -->
      <div id="st-dlg-backdrop" class="st-dialog-backdrop"></div>
      <div id="st-dlg" class="st-dialog">
        <div id="st-dlgForm">
          <h2>Kirim Pilihan ke Editor</h2>
          <p class="st-text-muted" style="font-size: 14px;">Kamu memilih <span id="st-dlgCount" style="font-weight:bold; color:var(--st-text);"></span> foto.</p>
          <textarea id="st-note" placeholder="Catatan tambahan untuk editor (opsional)..."></textarea>
          <div style="display: flex; gap: var(--st-space-2); justify-content: flex-end; flex-wrap: wrap;">
            <button class="st-btn st-btn-outline" id="st-closeDlgBtn">Batal</button>
            <button class="st-btn st-btn-primary" id="st-confirmSubmitBtn">Kirim Sekarang</button>
          </div>
        </div>
        <div id="st-dlgSuccess" style="display: none; text-align: center;">
          <h2>Berhasil Terkirim! 🎉</h2>
          <p class="st-text-muted" style="margin-bottom: 24px;">Pilihan kamu sudah dikunci. Beri tahu editor agar pesanan segera diproses.</p>
          <a href="#" id="st-waBtn" target="_blank" class="st-btn st-btn-primary" style="background: #25D366; border-color: #25D366; color: white; width: 100%; display: none; align-items: center; justify-content: center;">
            💬 Hubungi Editor via WhatsApp
          </a>
          <button class="st-btn st-btn-outline" style="margin-top: 16px; width: 100%;" id="st-closeSuccessBtn">Tutup</button>
        </div>
      </div>
    `;

    bindEvents();
    checkStatus();
  }

  function checkStatus() {
    isLocked = statusData.status === 'submitted';
    const bC = $('st-bannerContainer');
    const bText = $('st-bannerContent');
    const maxSelect = gallery.maxSelect;
    let overLimit = false;

    if (maxSelect && sel.size > maxSelect && !isLocked) {
      overLimit = true;
      bC.style.display = 'block';
      bText.className = 'st-banner st-locked';
      bText.innerHTML = `<span>⚠️ Batas diubah menjadi ${maxSelect}. Kurangi ${sel.size - maxSelect} foto sebelum mengirim.</span>`;
    } else if (isLocked) {
      bC.style.display = 'block';
      bText.className = 'st-banner st-locked';
      bText.innerHTML = `<span>🔒 Pilihan terkirim dan saat ini dikunci. Hubungi editor untuk mengubah.</span>`;
      $('st-note').value = statusData.note || '';
    } else if (statusData.status === 'reopened') {
      bC.style.display = 'block';
      bText.className = 'st-banner';
      bText.innerHTML = `<span>✨ Editor telah membuka kembali akses. Kamu bisa mengubah dan mengirim ulang.</span>`;
    } else {
      bC.style.display = 'none';
    }

    $('st-allBtn').style.display = (maxSelect && photos.length > maxSelect) ? 'none' : 'inline-flex';
    $('st-allBtn').disabled = isLocked || overLimit;
    $('st-resetBtn').disabled = isLocked;
    $('st-sendBtn').disabled = isLocked || sel.size === 0 || overLimit;
    
    if (maxSelect) {
      $('st-progressWrap').style.display = 'block';
      $('st-count').textContent = `${sel.size} / ${maxSelect} foto dipilih`;
      $('st-progressFill').style.width = Math.min((sel.size / maxSelect) * 100, 100) + '%';
      $('st-progressFill').style.background = sel.size > maxSelect ? '#D31510' : 'var(--st-accent-blue)';
    } else {
      $('st-progressWrap').style.display = 'none';
      $('st-count').textContent = `${sel.size} foto dipilih`;
    }
  }

  function autoSave() {
    if (isLocked) return;
    clearTimeout(saveTimeout);
    statusData.selectedIds = [...sel];
    saveTimeout = setTimeout(() => {
      apiFetch('/client/selection', {
        method: 'PUT',
        body: JSON.stringify({ ids: statusData.selectedIds, note: statusData.note })
      }).catch(e => console.error(e));
    }, 500);
  }

  function renderSkeleton(forceResultPhotos = null) {
    const list = forceResultPhotos || photos;
    $('st-grid').innerHTML = '';
    list.forEach((p, i) => {
      const c = document.createElement('div');
      c.className = 'st-photo-card' + (sel.has(p.id) ? ' st-selected' : '');
      c.dataset.i = i;
      c.innerHTML = `
        <div class="st-photo-card__overlay"></div>
        ${forceResultPhotos ? '' : '<div class="st-badge-check">✓</div>'}
        <div class="st-badge-name">${esc(p.name)}</div>
        <button class="st-zoom-btn" aria-label="Perbesar" tabindex="-1">⤢</button>
      `;
      c.onclick = (e) => {
        if (e.target.closest('.st-zoom-btn') || forceResultPhotos) openLb(i, list, !!forceResultPhotos);
        else toggle(p.id, c);
      };
      $('st-grid').append(c);
    });
    
    if (observer) observer.disconnect();
    observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(ent => {
        if (ent.isIntersecting) {
          const card = ent.target;
          const idx = parseInt(card.dataset.i);
          if (!card.querySelector('img')) {
            const img = document.createElement('img');
            img.src = list[idx].src;
            card.prepend(img);
          }
          obs.unobserve(card);
        }
      });
    }, { rootMargin: '600px' });
    
    Array.from($('st-grid').children).forEach(el => observer.observe(el));
    if (!forceResultPhotos) {
      checkStatus();
      updateOpacity();
    }
  }

  function updateOpacity() {
     if (!gallery.maxSelect) return;
     const over = sel.size >= gallery.maxSelect;
     Array.from($('st-grid').children).forEach((c, i) => {
        const id = photos[i].id;
        if (over && !sel.has(id)) {
          c.style.opacity = '0.4';
          c.style.pointerEvents = 'none';
        } else {
          c.style.opacity = '1';
          c.style.pointerEvents = 'auto';
        }
     });
  }

  function toggle(id, el) {
    if (isLocked) return;
    if (!sel.has(id) && gallery.maxSelect && sel.size >= gallery.maxSelect) return;
    sel.has(id) ? sel.delete(id) : sel.add(id);
    if (el) el.classList.toggle('st-selected', sel.has(id));
    updateOpacity();
    autoSave();
    checkStatus();
    if ($('st-lb').classList.contains('st-open')) updateLbBtn();
  }

  let currentLbList = [];
  let currentLbResultMode = false;

  function openLb(i, list, isResult = false) { 
    cur = i; currentLbList = list; currentLbResultMode = isResult;
    $('st-lb').classList.add('st-open'); 
    showLb(); 
  }
  
  function showLb() {
    const p = currentLbList[cur];
    $('st-lbimg').src = p.src.replace('w=400', 'w=1600');
    $('st-lbname').textContent = p.name;
    
    if (currentLbResultMode) {
      $('st-lbselBtn').style.display = 'none';
      $('st-lbDownloadBtn').style.display = 'inline-flex';
      $('st-lbDownloadBtn').href = `https://drive.google.com/uc?export=download&id=${p.id}`;
    } else {
      $('st-lbselBtn').style.display = 'inline-flex';
      $('st-lbDownloadBtn').style.display = 'none';
      updateLbBtn();
    }
  }
  
  function updateLbBtn() {
    $('st-lbselBtn').textContent = sel.has(currentLbList[cur].id) ? 'Batal pilih' : 'Pilih foto ini';
    $('st-lbselBtn').disabled = isLocked;
  }
  
  function bindEvents() {
    $('st-allBtn').onclick = () => { if(isLocked) return; photos.forEach(p => sel.add(p.id)); autoSave(); renderSkeleton(); };
    $('st-resetBtn').onclick = () => { if(isLocked) return; if(sel.size && confirm('Hapus semua?')) { sel.clear(); autoSave(); renderSkeleton(); } };
    
    const step = d => { cur = (cur + d + currentLbList.length) % currentLbList.length; showLb(); };
    $('st-prevBtn').onclick = () => step(-1);
    $('st-nextBtn').onclick = () => step(1);
    $('st-closeBtn').onclick = () => $('st-lb').classList.remove('st-open');
    $('st-lbselBtn').onclick = () => toggle(currentLbList[cur].id, $('st-grid').children[cur]);
    
    document.addEventListener('keydown', e => {
      if (!$('st-lb').classList.contains('st-open')) return;
      if (e.key === 'ArrowLeft') step(-1);
      if (e.key === 'ArrowRight') step(1);
      if (e.key === 'Escape') $('st-closeBtn').click();
    });

    $('st-sendBtn').onclick = () => {
      $('st-dlgCount').textContent = sel.size;
      $('st-dlgForm').style.display = 'block';
      $('st-dlgSuccess').style.display = 'none';
      openDialog('st-dlg');
    };
    
    $('st-closeDlgBtn').onclick = () => closeDialog('st-dlg');
    $('st-closeSuccessBtn').onclick = () => closeDialog('st-dlg');
    
    $('st-confirmSubmitBtn').onclick = async () => {
      $('st-confirmSubmitBtn').disabled = true;
      try {
        const note = $('st-note').value;
        await apiFetch('/client/submit', {
          method: 'POST',
          body: JSON.stringify({ ids: [...sel], note })
        });
        statusData.status = 'submitted';
        statusData.note = note;
        checkStatus();
        renderSkeleton();
        
        $('st-dlgForm').style.display = 'none';
        $('st-dlgSuccess').style.display = 'block';
        
        if (gallery.editorWa) {
          const linkWeb = window.location.href.split('?')[0]; // URL without token (or with, doesn't matter for WA)
          const teksWa = `Halo, saya *${gallery.title}* sudah selesai memilih *${sel.size}* foto.\n\nCatatan: ${note || '-'}`;
          $('st-waBtn').href = `https://wa.me/${gallery.editorWa}?text=${encodeURIComponent(teksWa)}`;
          $('st-waBtn').style.display = 'flex';
        }
      } catch(e) {
        toast(e.message);
      } finally {
        $('st-confirmSubmitBtn').disabled = false;
      }
    };
    
    if ($('st-showResultBtn')) {
      $('st-showResultBtn').onclick = async () => {
        try {
          const res = await apiFetch('/client/result');
          $('st-title').textContent = 'Hasil Edit: ' + gallery.title;
          $('st-status').innerHTML = `<a href="https://drive.google.com/drive/folders/${res.folderId}" target="_blank" class="st-btn st-btn-secondary st-mb-4">Buka Folder di Drive</a>`;
          document.querySelector('.st-bottom-bar').style.display = 'none';
          $('st-resultBanner').style.display = 'none';
          renderSkeleton(res.files);
        } catch(e) {
          toast(e.message);
        }
      };
    }
  }

  init();
})();
