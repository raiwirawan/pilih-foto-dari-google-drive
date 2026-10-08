import http from 'node:http';
import { readFile, writeFile, rename } from 'node:fs/promises';
import { createHash, createHmac, timingSafeEqual } from 'node:crypto';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const { GOOGLE_API_KEY, SESSION_SECRET, PORT = 3000 } = process.env;
if (!SESSION_SECRET) {
  console.warn('⚠️ WARNING: SESSION_SECRET di .env kosong! Login tidak aman.');
}
const SECRET = SESSION_SECRET || 'secret';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const PUBLIC = path.join(__dirname, 'public');
const DATA_DIR = path.join(__dirname, 'data');
const USERS_FILE = path.join(DATA_DIR, 'users.json');
const SELECTIONS_FILE = path.join(DATA_DIR, 'selections.json');

const MIME = { '.html': 'text/html; charset=utf-8', '.js': 'text/javascript', '.css': 'text/css', '.png': 'image/png', '.svg': 'image/svg+xml' };

// --- Data Layer ---
let users = [];
let selections = {};

async function loadData() {
  try { users = JSON.parse(await readFile(USERS_FILE, 'utf-8')); } catch (e) { console.log('users.json belum valid/ada.'); }
  try { selections = JSON.parse(await readFile(SELECTIONS_FILE, 'utf-8')); } catch (e) { selections = {}; }
}
async function saveSelections() {
  const tmp = SELECTIONS_FILE + '.tmp';
  await writeFile(tmp, JSON.stringify(selections, null, 2));
  await rename(tmp, SELECTIONS_FILE);
}
async function saveUsers() {
  const tmp = USERS_FILE + '.tmp';
  await writeFile(tmp, JSON.stringify(users, null, 2));
  await rename(tmp, USERS_FILE);
}

// --- Drive API ---
let driveCache = {}; // { folderId: { at: timestamp, files: [] } }
async function listPhotos(folderId, forceRefresh = false) {
  if (!GOOGLE_API_KEY) {
    // Mode Demo
    return Array.from({ length: 24 }, (_, i) => {
      const n = 'IMG_' + String(1200 + i * 7).padStart(4, '0') + '.jpg';
      const h = (i * 37) % 360;
      const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="hsl(${h},55%,62%)"/><stop offset="1" stop-color="hsl(${h + 50},60%,38%)"/></linearGradient></defs><rect width="600" height="600" fill="url(#g)"/><text x="300" y="316" font-size="40" text-anchor="middle" fill="#fff" font-family="sans-serif">${n}</text></svg>`;
      return { id: 'demo' + i + folderId, name: n, src: 'data:image/svg+xml,' + encodeURIComponent(svg) };
    });
  }
  if (!folderId) return [];
  const cache = driveCache[folderId];
  if (!forceRefresh && cache && Date.now() - cache.at < 60_000) return cache.files;
  
  const files = [];
  let token = '';
  do {
    const q = `'${folderId}' in parents and mimeType contains 'image/' and trashed=false`;
    const url = new URL('https://www.googleapis.com/drive/v3/files');
    // Using thumbnailLink if we can, else just name and id
    url.search = new URLSearchParams({ q, pageSize: '200', orderBy: 'name', fields: 'nextPageToken,files(id,name,thumbnailLink)', key: GOOGLE_API_KEY, ...(token && { pageToken: token }) });
    const r = await fetch(url);
    const d = await r.json();
    if (!r.ok) throw new Error(d.error?.message || `Drive API Error ${r.status}`);
    files.push(...d.files.map(f => {
      // Strip size param from thumbnailLink if exists to allow our proxy to set it
      let tUrl = f.thumbnailLink || '';
      if (tUrl) tUrl = tUrl.replace(/=s\d+$/, '');
      return { id: f.id, name: f.name, thumbnailLink: tUrl };
    }));
    token = d.nextPageToken || '';
  } while (token);
  
  driveCache[folderId] = { at: Date.now(), files };
  return files;
}

// --- Session & Auth ---
function signCookie(val) {
  const hmac = createHmac('sha256', SECRET).update(val).digest('base64url');
  return `${val}.${hmac}`;
}
function verifyCookie(cookieStr) {
  const match = cookieStr?.match(/sid=([^;]+)/);
  if (!match) return null;
  const parts = match[1].split('.');
  if (parts.length !== 3) return null;
  const val = `${parts[0]}.${parts[1]}`;
  const timestamp = parseInt(parts[1], 10);
  if (Date.now() - timestamp > 30 * 24 * 60 * 60 * 1000) return null; // 30 hari expiry
  
  const expected = createHmac('sha256', SECRET).update(val).digest('base64url');
  const bufExpected = Buffer.from(expected);
  const bufActual = Buffer.from(parts[2]);
  if (bufExpected.length !== bufActual.length || !timingSafeEqual(bufExpected, bufActual)) return null;
  
  return users.find(u => u.id === parts[0]) || null;
}

const json = (res, code, body, headers = {}) => { 
  res.writeHead(code, { 'Content-Type': 'application/json', ...headers }); 
  res.end(JSON.stringify(body)); 
};

const readJson = async (req) => {
  let body = '';
  for await (const chunk of req) {
    body += chunk;
    if (body.length > 1024 * 1024) throw new Error('Payload too large');
  }
  return JSON.parse(body);
};

// --- Cache Thumbnails ---
// Sederhana: in-memory cache max 50MB (buffer array)
const thumbCache = new Map();
let thumbCacheSize = 0;

// --- Server Main ---
const startServer = async () => {
  await loadData();
  
  http.createServer(async (req, res) => {
    const url = new URL(req.url, 'http://localhost');
    const user = verifyCookie(req.headers.cookie);

    try {
      // --- API Routes ---
      if (url.pathname === '/api/login' && req.method === 'POST') {
        const { username, password } = await readJson(req);
        const u = users.find(x => x.username === username && x.password === password);
        if (!u) return json(res, 401, { error: 'Username atau password salah' });
        const cookieVal = signCookie(`${u.id}.${Date.now()}`);
        return json(res, 200, { id: u.id, role: u.role }, {
          'Set-Cookie': `sid=${cookieVal}; HttpOnly; Path=/; SameSite=Lax; Max-Age=2592000`
        });
      }

      if (url.pathname === '/api/logout') {
        return json(res, 200, { ok: true }, { 'Set-Cookie': 'sid=; HttpOnly; Path=/; Max-Age=0' });
      }

      if (url.pathname === '/api/me') {
        if (!user) return json(res, 401, { error: 'Unauthorized' });
        let editorWa = '';
        if (user.role === 'client') {
           const ed = users.find(u => u.role === 'editor');
           if (ed) editorWa = ed.whatsapp || '';
        }
        return json(res, 200, { 
          id: user.id, name: user.name, role: user.role, 
          maxSelect: user.maxSelect, whatsapp: user.whatsapp, editorWa,
          resultFolderId: user.resultFolderId || ''
        });
      }

      // Klien API
      if (url.pathname === '/api/photos') {
        if (!user || user.role !== 'client') return json(res, 403, { error: 'Forbidden' });
        try {
          const files = await listPhotos(user.folderId);
          return json(res, 200, files);
        } catch(e) {
          return json(res, 400, { error: 'Gagal memuat folder Drive. ' + e.message });
        }
      }

      if (url.pathname === '/api/result-photos') {
        if (!user || user.role !== 'client') return json(res, 403, { error: 'Forbidden' });
        if (!user.resultFolderId) return json(res, 404, { error: 'Folder hasil belum tersedia' });
        try {
          const files = await listPhotos(user.resultFolderId);
          return json(res, 200, { folderId: user.resultFolderId, files });
        } catch(e) {
          return json(res, 400, { error: 'Gagal memuat folder hasil. ' + e.message });
        }
      }

      if (url.pathname === '/api/selection') {
        if (!user || user.role !== 'client') return json(res, 403, { error: 'Forbidden' });
        
        if (req.method === 'GET') {
          const sel = selections[user.id] || { status: 'draft', ids: [], note: '' };
          return json(res, 200, { ...sel, maxSelect: user.maxSelect || 0 });
        }
        
        if (req.method === 'PUT') {
          const data = await readJson(req);
          const sel = selections[user.id] || { status: 'draft', ids: [], note: '' };
          if (sel.status === 'submitted') return json(res, 409, { error: 'Locked' });
          if (user.maxSelect && Array.isArray(data.ids) && data.ids.length > user.maxSelect) {
            return json(res, 422, { error: `Maksimal pilihan adalah ${user.maxSelect} foto.` });
          }
          
          selections[user.id] = { ...sel, ids: data.ids || [], note: data.note || sel.note, updatedAt: Date.now() };
          await saveSelections();
          return json(res, 200, selections[user.id]);
        }
      }

      if (url.pathname === '/api/selection/submit' && req.method === 'POST') {
        if (!user || user.role !== 'client') return json(res, 403, { error: 'Forbidden' });
        const data = await readJson(req);
        
        const sel = selections[user.id] || { status: 'draft', ids: [], note: '' };
        if (sel.status === 'submitted') return json(res, 409, { error: 'Locked' });
        
        // Memastikan tidak submit pilihan kosong kecuali kalau memang disengaja. Di sini bisa kosong.
        const ids = data.ids || sel.ids || [];
        if (user.maxSelect && ids.length > user.maxSelect) {
            return json(res, 422, { error: `Maksimal pilihan adalah ${user.maxSelect} foto.` });
        }

        selections[user.id] = { ...sel, ids, note: data.note !== undefined ? data.note : sel.note, status: 'submitted', submittedAt: Date.now(), updatedAt: Date.now() };
        await saveSelections();
        return json(res, 200, selections[user.id]);
      }

      // --- Editor API ---
      if (url.pathname.startsWith('/api/editor/')) {
        if (!user || user.role !== 'editor') return json(res, 403, { error: 'Forbidden' });
        
        if (url.pathname === '/api/editor/settings' && req.method === 'PATCH') {
          const { whatsapp } = await readJson(req);
          user.whatsapp = whatsapp || '';
          await saveUsers();
          return json(res, 200, { ok: true });
        }

        // Cek Drive Folder
        if (url.pathname === '/api/editor/drive/resolve' && req.method === 'POST') {
          const { input } = await readJson(req);
          if (!input) return json(res, 400, { error: 'Input kosong' });
          if (!GOOGLE_API_KEY) return json(res, 400, { error: 'GOOGLE_API_KEY tidak dikonfigurasi (Mode Demo)' });
          
          // Extracted folder ID logic is also in frontend. Let's assume input is already extracted if it comes from frontend, or just valid folder ID.
          const folderId = input; 
          
          try {
            // Verify it's a folder
            const u = `https://www.googleapis.com/drive/v3/files/${folderId}?fields=id,name,mimeType&key=${GOOGLE_API_KEY}`;
            const r = await fetch(u);
            const d = await r.json();
            if (!r.ok) throw new Error(d.error?.message || 'Gagal akses API Drive');
            if (d.mimeType !== 'application/vnd.google-apps.folder') throw new Error('ID tersebut bukan sebuah folder');
            
            // Get count
            const photos = await listPhotos(folderId, true);
            return json(res, 200, { folderId, name: d.name, photoCount: photos.length });
          } catch(e) {
            return json(res, 400, { error: e.message });
          }
        }

        if (url.pathname === '/api/editor/clients') {
          if (req.method === 'GET') {
            const clients = users.filter(u => u.role === 'client').map(u => {
              const sel = selections[u.id] || { status: 'unstarted', ids: [] };
              return { 
                id: u.id, name: u.name, folderId: u.folderId, maxSelect: u.maxSelect || 0,
                resultFolderId: u.resultFolderId || '',
                selectedCount: sel.ids.length, status: sel.status, updatedAt: sel.updatedAt 
              };
            });
            return json(res, 200, clients);
          }
          if (req.method === 'POST') {
             const data = await readJson(req);
             if(!data.id || !data.name) return json(res, 400, {error: 'ID dan Nama wajib diisi'});
             if(users.find(u => u.id === data.id)) return json(res, 400, {error: 'ID Klien sudah dipakai'});
             users.push({
               id: data.id, role: 'client',
               username: data.username || data.id,
               password: data.password || 'password123',
               name: data.name,
               folderId: data.folderId || '',
               resultFolderId: data.resultFolderId || '',
               maxSelect: data.maxSelect || 0
             });
             await saveUsers();
             return json(res, 200, {ok:true});
          }
        }
        
        const clientMatch = url.pathname.match(/^\/api\/editor\/clients\/([^/]+)$/);
        if (clientMatch) {
          const clientId = clientMatch[1];
          const clientUser = users.find(u => u.id === clientId);
          if (!clientUser) return json(res, 404, { error: 'Not found' });
          
          if (req.method === 'GET') {
            const sel = selections[clientId] || { status: 'unstarted', ids: [], note: '' };
            let photos = [];
            if (clientUser.folderId) {
               try { photos = await listPhotos(clientUser.folderId); } catch(e){}
            }
            return json(res, 200, { ...sel, photos: photos.filter(p => sel.ids.includes(p.id)) });
          }
          
          if (req.method === 'PATCH') {
            const data = await readJson(req);
            let folderChanged = false;
            if (data.folderId !== undefined && data.folderId !== clientUser.folderId) {
               clientUser.folderId = data.folderId;
               folderChanged = true;
            }
            if (data.resultFolderId !== undefined) clientUser.resultFolderId = data.resultFolderId;
            if (data.maxSelect !== undefined) clientUser.maxSelect = data.maxSelect;
            if (data.name !== undefined) clientUser.name = data.name;
            if (data.password !== undefined) clientUser.password = data.password;
            
            await saveUsers();
            
            // Reset selection if folder changed
            if (folderChanged) {
              selections[clientId] = { status: 'draft', ids: [], note: '', updatedAt: Date.now() };
              await saveSelections();
              delete driveCache[clientUser.folderId]; // remove old
            }
            
            return json(res, 200, { ok: true, folderChanged });
          }

          if (req.method === 'DELETE') {
            users = users.filter(u => u.id !== clientId);
            await saveUsers();
            
            delete selections[clientId];
            await saveSelections();
            
            delete driveCache[clientUser.folderId];
            return json(res, 200, { ok: true });
          }
        }

        const matchReopen = url.pathname.match(/^\/api\/editor\/clients\/([^/]+)\/reopen$/);
        if (matchReopen && req.method === 'POST') {
          const clientId = matchReopen[1];
          if (selections[clientId] && selections[clientId].status === 'submitted') {
            selections[clientId].status = 'reopened';
            selections[clientId].reopenedAt = Date.now();
            selections[clientId].updatedAt = Date.now();
            await saveSelections();
          }
          return json(res, 200, { ok: true });
        }
      }

      // --- Thumbnail Proxy ---
      if (url.pathname.startsWith('/api/thumb/')) {
        const id = url.pathname.split('/').pop();
        if (!user) return json(res, 401, { error: 'Unauthorized' });
        
        // Verifikasi Akses Thumbnail (Keamanan)
        let allowed = false;
        if (user.role === 'editor') {
          for (const key in driveCache) {
             if (driveCache[key].files.some(f => f.id === id)) { allowed = true; break; }
          }
          if (!allowed && GOOGLE_API_KEY) allowed = true;
        } else if (user.role === 'client') {
          const photos = user.folderId ? await listPhotos(user.folderId) : [];
          const resultPhotos = user.resultFolderId ? await listPhotos(user.resultFolderId) : [];
          allowed = photos.some(f => f.id === id) || resultPhotos.some(f => f.id === id);
        }
        
        if (!allowed && GOOGLE_API_KEY) return json(res, 404, { error: 'Not found or forbidden' });
        
        const w = Math.min(Math.max(parseInt(url.searchParams.get('w')) || 400, 100), 2000);
        const cacheKey = `${id}-${w}`;
        
        if (thumbCache.has(cacheKey)) {
           const cached = thumbCache.get(cacheKey);
           res.writeHead(200, { 'Content-Type': cached.type, 'Cache-Control': 'public, max-age=86400' });
           return res.end(cached.buffer);
        }

        const driveUrl = `https://drive.google.com/thumbnail?id=${encodeURIComponent(id)}&sz=w${w}`;
        const r = await fetch(driveUrl);
        if (!r.ok) return json(res, 502, { error: 'Gagal mengambil thumbnail' });
        
        const buffer = Buffer.from(await r.arrayBuffer());
        const type = r.headers.get('content-type') || 'image/jpeg';
        
        // Simpan ke cache jika < 5MB per file dan total < 50MB
        if (buffer.length < 5_000_000) {
          if (thumbCacheSize > 50_000_000) {
            const first = thumbCache.keys().next().value;
            thumbCacheSize -= thumbCache.get(first).buffer.length;
            thumbCache.delete(first);
          }
          thumbCache.set(cacheKey, { buffer, type });
          thumbCacheSize += buffer.length;
        }

        res.writeHead(200, { 'Content-Type': type, 'Cache-Control': 'public, max-age=86400' });
        return res.end(buffer);
      }

      // --- Static Files ---
      let rel = url.pathname === '/' ? 'index.html' : url.pathname.slice(1);
      
      // Auth redirect
      if (url.pathname === '/' || url.pathname === '/index.html') {
        if (!user) rel = 'login.html';
        else if (user.role === 'editor') rel = 'editor.html';
        else rel = 'client.html';
      } else if (rel === 'login.html' && user) {
         res.writeHead(302, { Location: '/' }); return res.end();
      } else if ((rel === 'client.html' || rel === 'editor.html') && !user) {
         res.writeHead(302, { Location: '/login.html' }); return res.end();
      }

      const file = path.join(PUBLIC, path.normalize(rel));
      if (!file.startsWith(PUBLIC)) return json(res, 403, { error: 'Dilarang' });
      const data = await readFile(file);
      res.writeHead(200, { 'Content-Type': MIME[path.extname(file)] || 'application/octet-stream' });
      res.end(data);
    } catch (e) {
      if (e.message === 'Payload too large') return json(res, 413, { error: e.message });
      if (e instanceof SyntaxError) return json(res, 400, { error: 'Bad Request JSON' });
      if (e.code === 'ENOENT') return json(res, 404, { error: 'Tidak ditemukan' });
      console.error(e);
      json(res, 500, { error: e.message });
    }
  }).listen(PORT, () => console.log(`Server jalan di http://localhost:${PORT}`));
};

startServer();
