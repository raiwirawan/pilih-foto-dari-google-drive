/* studio-client.js */
(function() {
  const { apiFetch, esc, openDialog, closeDialog, toast } = window.StudioApp;

  let gallery = null, photos = [], sel = new Set(), cur = 0;
  let statusData = { status: 'unstarted', selectedIds: [], note: '' };
  let isLocked = false, saveTimeout = null, observer = null, inResultView = false;

  const $ = id => document.getElementById(id);

  function getUrlToken() {
    const params = new URLSearchParams(window.location.search);
    const k = params.get('k');
    if (k) {
      sessionStorage.setItem('st_token', k);
      try { localStorage.setItem('st_token', k); } catch (e) {}
      params.delete('k');
      const qs = params.toString();
      const newUrl = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
      window.history.replaceState({}, document.title, newUrl);
    }
    let token = sessionStorage.getItem('st_token');
    if (!token) {
      try {
        token = localStorage.getItem('st_token');
        if (token) sessionStorage.setItem('st_token', token);
      } catch (e) {}
    }
    return token;
  }

  function buildWaUrl(noteText) {
    if (!gallery || !gallery.editorWa) return '';
    const teksWa = `Halo, saya *${gallery.title}* sudah selesai memilih *${sel.size}* foto.\n\nCatatan: ${noteText || '-'}`;
    return `https://wa.me/${gallery.editorWa}?text=${encodeURIComponent(teksWa)}`;
  }

  async function init() {
    const token = getUrlToken();
    if (!token) return renderError('Link tidak valid. Pastikan Anda membuka link lengkap yang diberikan oleh editor.');

    try {
      gallery = await apiFetch('/client/gallery');
      statusData = {
        status: gallery.status || 'unstarted',
        note: gallery.note || '',
        selectedIds: Array.isArray(gallery.selectedIds) ? gallery.selectedIds : []
      };
      sel = new Set(statusData.selectedIds);
      isLocked = statusData.status === 'submitted';

      renderApp();

      photos = await apiFetch('/client/photos');
      $('st-status').textContent = photos.length ? `${photos.length} foto ditemukan.` : 'Belum ada foto di folder ini.';
      renderSkeleton();

    } catch (err) {
      renderError(err.message || 'Gagal memuat galeri.');
    }
  }

  function renderError(msg) {
    $('st-app').innerHTML = `
      <div class="st-container" style="padding: 64px 16px; text-align: center;">
        <div style="background: var(--st-surface); border: 1px solid var(--st-border); border-radius: var(--st-radius-lg); padding: 32px; max-width: 480px; margin: 0 auto;">
          <h2 style="color: #ff4d4f; margin-bottom: 12px;">Akses Galeri Gagal</h2>
          <p class="st-text-muted" style="margin: 0 auto;">${esc(msg)}</p>
        </div>
      </div>
    `;
  }

  function renderApp() {
    $('st-app').innerHTML = `
      <header class="st-header-section">
        <div class="st-container" style="display: flex; flex-direction: column; gap: var(--st-space-3);">
          <h1 id="st-title">${esc(gallery.title)}</h1>
          <p id="st-subtitle" class="st-text-muted" style="margin-bottom: 0;">Ketuk foto yang ingin diedit. Pilihan tersimpan otomatis.</p>
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

      <div class="st-bottom-bar" id="st-bottomBar">
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
      <div id="st-lb" class="st-lightbox" role="dialog" aria-modal="true">
        <div class="st-lightbox-header">
          <button class="st-lightbox-nav" style="position: static; transform: none; background: transparent;" id="st-closeBtn" aria-label="Tutup">✕</button>
        </div>
        <div class="st-lightbox-content" id="st-lbContent">
          <button class="st-lightbox-nav st-lightbox-prev" id="st-prevBtn" aria-label="Foto sebelumnya">‹</button>
          <img id="st-lbimg" class="st-lightbox-img" alt="">
          <button class="st-lightbox-nav st-lightbox-next" id="st-nextBtn" aria-label="Foto berikutnya">›</button>
        </div>
        <div class="st-lightbox-footer">
          <span id="st-lbname" style="font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
          <div style="display: flex; gap: 8px; align-items: center;">
            <button class="st-btn st-btn-primary" id="st-lbselBtn"></button>
            <a id="st-lbDownloadBtn" target="_blank" rel="noopener noreferrer" class="st-btn st-btn-outline" style="display: none;">Unduh</a>
          </div>
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
          <a href="#" id="st-waBtn" target="_blank" rel="noopener noreferrer" class="st-btn st-btn-primary" style="background: #25D366; border-color: #25D366; color: white; width: 100%; display: none; align-items: center; justify-content: center;">
            💬 Hubungi Editor via WhatsApp
          </a>
          <button class="st-btn st-btn-outline" style="margin-top: 16px; width: 100%;" id="st-closeSuccessBtn">Tutup</button>
        </div>
      </div>
    `;

    $('st-note').value = statusData.note || '';
    bindEvents();
    checkStatus();
  }

  function checkStatus() {
    if (inResultView) return;
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
      const waUrl = buildWaUrl(statusData.note);
      bText.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; flex-wrap: wrap; gap: 12px;">
          <span>🔒 Pilihan terkirim dan saat ini dikunci. Hubungi editor jika ingin mengubah pilihan.</span>
          ${waUrl ? `<a href="${esc(waUrl)}" target="_blank" rel="noopener noreferrer" class="st-btn st-btn-primary" style="background: #25D366; border-color: #25D366; color: #111; height: 36px; padding: 0 14px; font-size: 13px; font-weight: 700;">💬 Hubungi Editor</a>` : ''}
        </div>
      `;
      $('st-note').value = statusData.note || '';
    } else if (statusData.status === 'reopened') {
      bC.style.display = 'block';
      bText.className = 'st-banner';
      bText.innerHTML = `<span>✨ Editor telah membuka kembali akses. Kamu bisa mengubah dan mengirim ulang pilihan foto.</span>`;
    } else {
      bC.style.display = 'none';
    }

    const hideAllBtn = (maxSelect && photos.length > maxSelect) || photos.length === 0;
    $('st-allBtn').style.display = hideAllBtn ? 'none' : 'inline-flex';
    $('st-allBtn').disabled = isLocked || overLimit;
    $('st-resetBtn').disabled = isLocked || sel.size === 0;
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
      if (isLocked) return;
      apiFetch('/client/selection', {
        method: 'PUT',
        body: JSON.stringify({ ids: statusData.selectedIds, note: statusData.note })
      }).catch(e => console.error(e));
    }, 500);
  }

  function renderSkeleton(forceResultPhotos = null) {
    const isResult = Array.isArray(forceResultPhotos);
    const list = isResult ? forceResultPhotos : photos;
    $('st-grid').innerHTML = '';

    list.forEach((p, i) => {
      const c = document.createElement('div');
      c.className = 'st-photo-card' + (!isResult && sel.has(p.id) ? ' st-selected' : '');
      c.dataset.i = i;
      c.innerHTML = `
        <div class="st-photo-card__overlay"></div>
        ${isResult ? '' : '<div class="st-badge-check">✓</div>'}
        <div class="st-badge-name" title="${esc(p.name)}">${esc(p.name)}</div>
        <button class="st-zoom-btn" type="button" aria-label="Perbesar" tabindex="-1">⤢</button>
      `;
      c.onclick = (e) => {
        if (e.target.closest('.st-zoom-btn') || isResult) {
          openLb(i, list, isResult);
        } else {
          toggle(p.id, c);
        }
      };
      $('st-grid').append(c);
    });

    if (observer) observer.disconnect();
    observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(ent => {
        if (ent.isIntersecting) {
          const card = ent.target;
          const idx = parseInt(card.dataset.i, 10);
          if (!card.querySelector('img') && list[idx]) {
            const pItem = list[idx];
            const img = document.createElement('img');
            img.referrerPolicy = 'no-referrer';
            img.alt = pItem.name || '';
            img.loading = 'lazy';
            img.onload = () => {
              card.classList.remove('st-img-error');
              card.classList.add('st-img-loaded');
            };
            img.onerror = () => {
              const isDemo = String(pItem.id).startsWith('demo');
              if (!isDemo && !img.dataset.fallbackStage) {
                img.dataset.fallbackStage = '1';
                img.src = pItem.fallbackSrc || `https://lh3.googleusercontent.com/d/${encodeURIComponent(pItem.id)}=w400`;
              } else if (!isDemo && img.dataset.fallbackStage === '1') {
                img.dataset.fallbackStage = '2';
                img.src = `https://drive.google.com/thumbnail?id=${encodeURIComponent(pItem.id)}&sz=w400`;
              } else {
                card.classList.add('st-img-error');
              }
            };
            img.src = pItem.src;
            card.prepend(img);
          }
          obs.unobserve(card);
        }
      });
    }, { rootMargin: '600px' });

    Array.from($('st-grid').children).forEach(el => observer.observe(el));
    if (!isResult) {
      checkStatus();
      updateOpacity();
    }
  }

  function updateOpacity() {
    if (inResultView) return;
    if (!gallery.maxSelect) {
      Array.from($('st-grid').children).forEach(c => {
        c.style.opacity = '1';
      });
      return;
    }
    const over = sel.size >= gallery.maxSelect;
    Array.from($('st-grid').children).forEach((c, i) => {
      if (!photos[i]) return;
      const id = photos[i].id;
      if (over && !sel.has(id)) {
        c.style.opacity = '0.45';
      } else {
        c.style.opacity = '1';
      }
    });
  }

  function toggle(id, el) {
    if (isLocked) {
      toast('Pilihan sudah dikunci oleh sistem');
      return;
    }
    if (!sel.has(id) && gallery.maxSelect && sel.size >= gallery.maxSelect) {
      toast(`Maksimal pilihan adalah ${gallery.maxSelect} foto`);
      return;
    }
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
    if (!list || !list.length) return;
    cur = i;
    currentLbList = list;
    currentLbResultMode = isResult;
    $('st-lb').classList.add('st-open');
    showLb();
  }

  function showLb() {
    const p = currentLbList[cur];
    if (!p) return;
    const lbImg = $('st-lbimg');
    lbImg.referrerPolicy = 'no-referrer';
    delete lbImg.dataset.fallbackStage;
    lbImg.onerror = () => {
      const isDemo = String(p.id).startsWith('demo');
      if (!isDemo && !lbImg.dataset.fallbackStage) {
        lbImg.dataset.fallbackStage = '1';
        lbImg.src = `https://lh3.googleusercontent.com/d/${encodeURIComponent(p.id)}=w1600`;
      } else if (!isDemo && lbImg.dataset.fallbackStage === '1') {
        lbImg.dataset.fallbackStage = '2';
        lbImg.src = `https://drive.google.com/thumbnail?id=${encodeURIComponent(p.id)}&sz=w1600`;
      }
    };
    lbImg.src = p.src.replace('w=400', 'w=1600');
    $('st-lbname').textContent = p.name;

    if (currentLbResultMode) {
      $('st-lbselBtn').style.display = 'none';
      const isDemo = String(p.id).startsWith('demo_');
      $('st-lbDownloadBtn').style.display = 'inline-flex';
      $('st-lbDownloadBtn').href = isDemo
        ? p.src.replace('w=400', 'w=1600')
        : `https://drive.google.com/uc?export=download&id=${encodeURIComponent(p.id)}`;
    } else {
      $('st-lbselBtn').style.display = 'inline-flex';
      $('st-lbDownloadBtn').style.display = 'none';
      updateLbBtn();
    }
  }

  function updateLbBtn() {
    const p = currentLbList[cur];
    if (!p) return;
    const isSelected = sel.has(p.id);
    const atLimit = !isSelected && gallery.maxSelect && sel.size >= gallery.maxSelect;
    $('st-lbselBtn').textContent = isSelected
      ? 'Batal pilih'
      : (atLimit ? `Batas penuh (${gallery.maxSelect})` : 'Pilih foto ini');
    $('st-lbselBtn').disabled = isLocked || atLimit;
  }

  function exitResultView() {
    inResultView = false;
    $('st-title').textContent = gallery.title;
    $('st-subtitle').textContent = 'Ketuk foto yang ingin diedit. Pilihan tersimpan otomatis.';
    if ($('st-resultBanner')) $('st-resultBanner').style.display = 'block';
    $('st-bottomBar').style.display = 'flex';
    $('st-status').textContent = photos.length ? `${photos.length} foto ditemukan.` : 'Belum ada foto di folder ini.';
    renderSkeleton();
  }

  function bindEvents() {
    $('st-allBtn').onclick = () => {
      if (isLocked) return;
      if (gallery.maxSelect && photos.length > gallery.maxSelect) {
        toast(`Maksimal pilihan adalah ${gallery.maxSelect} foto`);
        return;
      }
      photos.forEach(p => sel.add(p.id));
      autoSave();
      renderSkeleton();
    };

    $('st-resetBtn').onclick = () => {
      if (isLocked) return;
      if (sel.size && confirm('Hapus semua pilihan foto?')) {
        sel.clear();
        autoSave();
        renderSkeleton();
      }
    };

    const step = d => {
      if (!currentLbList.length) return;
      cur = (cur + d + currentLbList.length) % currentLbList.length;
      showLb();
    };
    $('st-prevBtn').onclick = () => step(-1);
    $('st-nextBtn').onclick = () => step(1);
    $('st-closeBtn').onclick = () => {
      $('st-lb').classList.remove('st-open');
      $('st-lbimg').src = '';
    };
    $('st-lbselBtn').onclick = () => toggle(currentLbList[cur].id, $('st-grid').children[cur]);

    let touchStartX = 0;
    $('st-lbContent').addEventListener('touchstart', e => {
      if (e.changedTouches && e.changedTouches[0]) {
        touchStartX = e.changedTouches[0].screenX;
      }
    }, { passive: true });
    $('st-lbContent').addEventListener('touchend', e => {
      if (e.changedTouches && e.changedTouches[0]) {
        const dx = e.changedTouches[0].screenX - touchStartX;
        if (Math.abs(dx) > 40) step(dx < 0 ? 1 : -1);
      }
    }, { passive: true });

    document.addEventListener('keydown', e => {
      if (!$('st-lb').classList.contains('st-open')) return;
      if (e.key === 'ArrowLeft') step(-1);
      if (e.key === 'ArrowRight') step(1);
      if (e.key === 'Escape') $('st-closeBtn').click();
    });

    $('st-note').addEventListener('input', () => {
      statusData.note = $('st-note').value;
      autoSave();
    });

    $('st-sendBtn').onclick = () => {
      $('st-dlgCount').textContent = sel.size;
      $('st-note').value = statusData.note || '';
      $('st-dlgForm').style.display = 'block';
      $('st-dlgSuccess').style.display = 'none';
      openDialog('st-dlg');
    };

    $('st-closeDlgBtn').onclick = () => closeDialog('st-dlg');
    $('st-closeSuccessBtn').onclick = () => closeDialog('st-dlg');

    $('st-confirmSubmitBtn').onclick = async () => {
      clearTimeout(saveTimeout);
      $('st-confirmSubmitBtn').disabled = true;
      try {
        const note = $('st-note').value;
        await apiFetch('/client/submit', {
          method: 'POST',
          body: JSON.stringify({ ids: [...sel], note })
        });
        statusData.status = 'submitted';
        statusData.note = note;
        isLocked = true;
        checkStatus();
        renderSkeleton();

        $('st-dlgForm').style.display = 'none';
        $('st-dlgSuccess').style.display = 'block';

        const waUrl = buildWaUrl(note);
        if (waUrl) {
          $('st-waBtn').href = waUrl;
          $('st-waBtn').style.display = 'flex';
        }
      } catch (e) {
        toast(e.message);
      } finally {
        $('st-confirmSubmitBtn').disabled = false;
      }
    };

    if ($('st-showResultBtn')) {
      $('st-showResultBtn').onclick = async () => {
        const btn = $('st-showResultBtn');
        const origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Memuat...';
        try {
          const res = await apiFetch('/client/result');
          inResultView = true;
          $('st-title').textContent = 'Hasil Edit: ' + gallery.title;
          $('st-subtitle').textContent = 'Ketuk foto untuk memperbesar atau mengunduh hasil edit.';
          const isDemoFolder = !res.folderId || String(res.folderId).startsWith('demo');
          const filesCount = Array.isArray(res.files) ? res.files.length : 0;

          $('st-status').innerHTML = `
            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 8px;">
              <button type="button" class="st-btn st-btn-outline" id="st-backToSelectBtn">← Kembali ke Pilihan Foto</button>
              ${!isDemoFolder ? `<a href="https://drive.google.com/drive/folders/${encodeURIComponent(res.folderId)}" target="_blank" rel="noopener noreferrer" class="st-btn st-btn-secondary">📂 Buka Folder di Google Drive</a>` : ''}
            </div>
            <div class="st-text-muted">${filesCount ? `${filesCount} foto hasil edit tersedia.` : 'Belum ada foto di folder hasil edit ini.'}</div>
          `;
          if ($('st-backToSelectBtn')) {
            $('st-backToSelectBtn').onclick = exitResultView;
          }
          $('st-bottomBar').style.display = 'none';
          $('st-bannerContainer').style.display = 'none';
          if ($('st-resultBanner')) $('st-resultBanner').style.display = 'none';
          renderSkeleton(res.files || []);
        } catch (e) {
          toast(e.message);
        } finally {
          btn.disabled = false;
          btn.textContent = origText;
        }
      };
    }
  }

  init();
})();
