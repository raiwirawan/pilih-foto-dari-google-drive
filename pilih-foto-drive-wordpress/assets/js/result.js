const pf_id = id => document.getElementById(id);

let pf_galleries = [];
let pf_gallery = null;
let pf_photos = [];
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

async function pfInitResult() {
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
        pf_photos = data.results;
        
        if (!pf_gallery.result_folder_id) {
            window.location.href = pfClientData.siteUrl + 'foto/';
            return;
        }

        pfRenderGrid();
    } catch (e) {
        pf_id('errorBox').textContent = e.message;
        pf_id('errorBox').style.display = 'block';
    }
}

function pfRenderGrid() {
    const grid = pf_id('grid');
    grid.innerHTML = '';
    
    if (!pf_photos.length) {
        grid.innerHTML = '<p class="text-muted" style="grid-column: 1/-1;">Belum ada foto hasil edit yang diunggah.</p>';
        return;
    }
    
    pf_photos.forEach((p, i) => {
        const card = document.createElement('div');
        card.className = 'photo-card';
        card.dataset.i = i;
        card.tabIndex = 0;
        card.innerHTML = `
            <div class="photo-card__overlay" style="opacity:0;"></div>
            <div class="badge-name" style="opacity:1;">${p.name}</div>
        `;
        card.onclick = () => pfOpenLb(i);
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

let pf_cur = 0;
function pfOpenLb(i) { pf_cur = i; pf_id('lb').classList.add('open'); pfShowLb(); }
function pfShowLb() {
    const p = pf_photos[pf_cur];
    pf_id('lbimg').src = `${pfClientData.siteUrl}pf-img/${pf_gallery.id}/${p.id}/1600?v=${p.drive_version}`;
    pf_id('lbname').textContent = p.name;
    // For now we don't have direct download proxy, link to drive uc?export=download
    pf_id('lbdlBtn').href = `https://drive.google.com/uc?export=download&id=${p.drive_file_id}`;
}
pf_id('prevBtn').onclick = () => { pf_cur = (pf_cur - 1 + pf_photos.length) % pf_photos.length; pfShowLb(); };
pf_id('nextBtn').onclick = () => { pf_cur = (pf_cur + 1) % pf_photos.length; pfShowLb(); };
pf_id('closeBtn').onclick = () => pf_id('lb').classList.remove('open');

document.addEventListener('DOMContentLoaded', pfInitResult);
