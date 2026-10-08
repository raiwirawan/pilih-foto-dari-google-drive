const pf_id = id => document.getElementById(id);

let pf_galleries = [];
let pf_gallery = null;
let pf_photos = [];
let pf_sel = new Set();
let pf_maxSelect = 0;
let pf_statusData = { status: 'unstarted' };
let pf_saveTimeout = null;
let pf_observer = null;

async function pfFetch(endpoint, options = {}) {
    options.headers = options.headers || {};
    options.headers['X-WP-Nonce'] = pfClientData.nonce;
    options.headers['Content-Type'] = 'application/json';
    const url = pfClientData.restUrl + endpoint;
    const res = await fetch(url, options);
    if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        throw new Error(data.message || 'Error API');
    }
    return res.json();
}

async function pfInitClient() {
    try {
        const gals = await pfFetch('client/galleries');
        pf_galleries = gals;
        
        if (gals.length === 0) {
            pf_id('title').textContent = 'Belum ada galeri';
            pf_id('errorBox').textContent = 'Editor belum membagikan galeri ke akunmu.';
            pf_id('errorBox').style.display = 'block';
            return;
        }

        if (gals.length > 1) {
            pf_id('gallerySelectorContainer').style.display = 'block';
            const sel = pf_id('gallerySelector');
            sel.innerHTML = gals.map(g => `<option value="${g.id}">${g.title}</option>`).join('');
        }

        await pfLoadGallery(gals[0].id);
    } catch (e) {
        pf_id('errorBox').textContent = e.message;
        pf_id('errorBox').style.display = 'block';
    }
}

async function pfLoadGallery(id) {
    try {
        const data = await pfFetch(`client/galleries/${id}`);
        pf_gallery = data.gallery;
        pf_photos = data.photos;
        pf_statusData.status = pf_gallery.status;
        pf_maxSelect = parseInt(pf_gallery.max_select);
        
        pf_sel = new Set(data.selections.map(i => parseInt(i)));
        
        if (pf_gallery.result_folder_id) {
            pf_id('resultBanner').style.display = 'block';
        } else {
            pf_id('resultBanner').style.display = 'none';
        }

        pfRenderStatus();
        pfRenderGrid();
        pfUpdateSel();
    } catch (e) {
        pf_id('errorBox').textContent = e.message;
        pf_id('errorBox').style.display = 'block';
    }
}

function pfRenderStatus() {
    const stat = pf_statusData.status;
    const box = pf_id('bannerContent');
    const bcont = pf_id('bannerContainer');
    if (stat === 'submitted') {
        bcont.style.display = 'block';
        box.className = 'banner locked';
        box.innerHTML = '🔒 Pilihan terkirim dan saat ini dikunci. Hubungi editor untuk mengubah.';
    } else if (stat === 'reopened') {
        bcont.style.display = 'block';
        box.className = 'banner';
        box.innerHTML = '✨ Editor telah membuka kembali akses. Kamu bisa mengubah dan mengirim ulang.';
    } else {
        bcont.style.display = 'none';
    }
}

function pfUpdateSel() {
    pf_id('selCount').textContent = pf_sel.size;
    if (pf_maxSelect > 0) {
        pf_id('maxLabel').textContent = `/ ${pf_maxSelect} batas`;
        const pct = Math.min(100, (pf_sel.size / pf_maxSelect) * 100);
        pf_id('progressWrap').style.display = 'block';
        pf_id('progressFill').style.width = pct + '%';
        if (pf_sel.size > pf_maxSelect) {
            pf_id('progressFill').style.background = '#D31510';
        } else {
            pf_id('progressFill').style.background = 'var(--color-accent-blue)';
        }
    } else {
        pf_id('maxLabel').textContent = '';
        pf_id('progressWrap').style.display = 'none';
    }
    
    const isLocked = pf_statusData.status === 'submitted';
    pf_id('sendBtn').disabled = isLocked || pf_sel.size === 0 || (pf_maxSelect > 0 && pf_sel.size > pf_maxSelect);
    pf_id('allBtn').disabled = isLocked;
    pf_id('resetBtn').disabled = isLocked;

    document.querySelectorAll('.photo-card').forEach(c => {
        const i = parseInt(c.dataset.i);
        const pid = pf_photos[i].id;
        if (pf_sel.has(pid)) {
            c.classList.add('selected');
        } else {
            c.classList.remove('selected');
        }
        
        if (isLocked) {
            c.style.pointerEvents = 'none';
            c.style.opacity = '0.7';
        } else if (pf_maxSelect > 0 && pf_sel.size >= pf_maxSelect && !pf_sel.has(pid)) {
            c.style.pointerEvents = 'none';
            c.style.opacity = '0.4';
        } else {
            c.style.pointerEvents = 'auto';
            c.style.opacity = '1';
        }
    });
}

function pfRenderGrid() {
    const grid = pf_id('grid');
    grid.innerHTML = '';
    
    if (!pf_photos.length) {
        grid.innerHTML = '<p class="text-muted">Tidak ada foto.</p>';
        return;
    }
    
    pf_photos.forEach((p, i) => {
        const card = document.createElement('div');
        card.className = 'photo-card';
        card.dataset.i = i;
        card.tabIndex = 0;
        card.innerHTML = `
            <div class="photo-card__overlay"></div>
            <div class="badge-check">✓</div>
            <div class="badge-name">${p.name}</div>
            <button class="zoom-btn" onclick="event.stopPropagation(); pfOpenLb(${i})">🔍</button>
        `;
        card.onclick = () => pfToggleSel(p.id);
        grid.appendChild(card);
    });

    pf_observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(ent => {
            if (ent.isIntersecting) {
                const c = ent.target;
                const idx = parseInt(c.dataset.i);
                const p = pf_photos[idx];
                if (!c.querySelector('img')) {
                    const img = document.createElement('img');
                    img.src = `${pfClientData.siteUrl}pf-img/${pf_gallery.id}/${p.id}/400?v=${p.drive_version}`;
                    img.alt = p.name;
                    c.prepend(img);
                }
                obs.unobserve(c);
            }
        });
    }, { rootMargin: '600px' });

    Array.from(grid.children).forEach(el => pf_observer.observe(el));
}

let pf_saveQueue = { add: [], remove: [] };

function pfToggleSel(pid) {
    if (pf_statusData.status === 'submitted') return;
    if (pf_maxSelect > 0 && pf_sel.size >= pf_maxSelect && !pf_sel.has(pid)) return;

    if (pf_sel.has(pid)) {
        pf_sel.delete(pid);
        pf_saveQueue.remove.push(pid);
    } else {
        pf_sel.add(pid);
        pf_saveQueue.add.push(pid);
    }
    pfUpdateSel();

    if (pf_saveTimeout) clearTimeout(pf_saveTimeout);
    pf_saveTimeout = setTimeout(pfSaveSelection, 500);
}

async function pfSaveSelection() {
    if (pf_saveQueue.add.length === 0 && pf_saveQueue.remove.length === 0) return;
    const payload = { add: pf_saveQueue.add, remove: pf_saveQueue.remove };
    pf_saveQueue = { add: [], remove: [] };

    try {
        await pfFetch(`client/galleries/${pf_gallery.id}/selection`, {
            method: 'PATCH',
            body: JSON.stringify(payload)
        });
        if (pf_statusData.status === 'unstarted') {
            pf_statusData.status = 'draft';
        }
    } catch(e) {
        pfToast('Gagal simpan otomatis: ' + e.message);
    }
}

function pfToast(msg) {
    const t = pf_id('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}

pf_id('allBtn').onclick = () => {
    if (pf_statusData.status === 'submitted') return;
    if (pf_maxSelect > 0 && pf_photos.length > pf_maxSelect) {
        return pfToast(`Tidak bisa pilih semua, batas maksimal ${pf_maxSelect} foto.`);
    }
    pf_photos.forEach(p => {
        if (!pf_sel.has(p.id)) {
            pf_sel.add(p.id);
            pf_saveQueue.add.push(p.id);
        }
    });
    pfUpdateSel();
    if (pf_saveTimeout) clearTimeout(pf_saveTimeout);
    pfSaveSelection();
};

pf_id('resetBtn').onclick = () => {
    if (pf_statusData.status === 'submitted') return;
    pf_photos.forEach(p => {
        if (pf_sel.has(p.id)) {
            pf_saveQueue.remove.push(p.id);
        }
    });
    pf_sel.clear();
    pfUpdateSel();
    if (pf_saveTimeout) clearTimeout(pf_saveTimeout);
    pfSaveSelection();
};

pf_id('sendBtn').onclick = () => {
    pf_id('dlgCount').textContent = pf_sel.size;
    pf_id('dlgForm').style.display = 'block';
    pf_id('dlgSuccess').style.display = 'none';
    pf_id('dlg').showModal();
};

pf_id('closeDlgBtn').onclick = () => pf_id('dlg').close();

pf_id('confirmSubmitBtn').onclick = async () => {
    const btn = pf_id('confirmSubmitBtn');
    btn.disabled = true;
    try {
        await pfSaveSelection(); // pastikan antrean kosong
        await pfFetch(`client/galleries/${pf_gallery.id}/submit`, {
            method: 'POST',
            body: JSON.stringify({ note: pf_id('note').value })
        });
        pf_statusData.status = 'submitted';
        pfUpdateSel();
        pfRenderStatus();
        
        pf_id('dlgForm').style.display = 'none';
        pf_id('dlgSuccess').style.display = 'block';
    } catch (e) {
        pfToast('Gagal kirim: ' + e.message);
    } finally {
        btn.disabled = false;
    }
};

let pf_cur = 0;
function pfOpenLb(i) { pf_cur = i; pf_id('lb').classList.add('open'); pfShowLb(); }
function pfShowLb() {
    const p = pf_photos[pf_cur];
    pf_id('lbimg').src = `${pfClientData.siteUrl}pf-img/${pf_gallery.id}/${p.id}/1600?v=${p.drive_version}`;
    pf_id('lbname').textContent = p.name;
    const sb = pf_id('lbselBtn');
    if (pf_sel.has(p.id)) {
        sb.textContent = 'Batal Pilih';
        sb.classList.remove('btn-primary');
        sb.classList.add('btn-outline');
    } else {
        sb.textContent = 'Pilih Foto Ini';
        sb.classList.add('btn-primary');
        sb.classList.remove('btn-outline');
    }
    sb.disabled = (pf_statusData.status === 'submitted') || (pf_maxSelect > 0 && pf_sel.size >= pf_maxSelect && !pf_sel.has(p.id));
}
pf_id('lbselBtn').onclick = () => { pfToggleSel(pf_photos[pf_cur].id); pfShowLb(); };
pf_id('prevBtn').onclick = () => { pf_cur = (pf_cur - 1 + pf_photos.length) % pf_photos.length; pfShowLb(); };
pf_id('nextBtn').onclick = () => { pf_cur = (pf_cur + 1) % pf_photos.length; pfShowLb(); };
pf_id('closeBtn').onclick = () => pf_id('lb').classList.remove('open');

document.addEventListener('DOMContentLoaded', pfInitClient);
