import http from 'node:http';
import { readFile } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const { GOOGLE_API_KEY, DRIVE_FOLDER_ID, CLIENT_NAME = 'Klien', EDITOR_WA = '', PORT = 3000 } = process.env;
const PUBLIC = path.join(path.dirname(fileURLToPath(import.meta.url)), 'public');
const DEMO = !GOOGLE_API_KEY || !DRIVE_FOLDER_ID;
const MIME = { '.html': 'text/html; charset=utf-8', '.js': 'text/javascript', '.css': 'text/css', '.png': 'image/png', '.svg': 'image/svg+xml' };

let cache = { at: 0, files: [] };
async function listPhotos() {
  if (Date.now() - cache.at < 60_000 && cache.files.length) return cache.files;
  const files = [];
  let token = '';
  do {
    const q = `'${DRIVE_FOLDER_ID}' in parents and mimeType contains 'image/' and trashed=false`;
    const url = new URL('https://www.googleapis.com/drive/v3/files');
    url.search = new URLSearchParams({ q, pageSize: '200', orderBy: 'name', fields: 'nextPageToken,files(id,name)', key: GOOGLE_API_KEY, ...(token && { pageToken: token }) });
    const r = await fetch(url);
    const d = await r.json();
    if (!r.ok) throw new Error(d.error?.message || `Drive API ${r.status}`);
    files.push(...d.files);
    token = d.nextPageToken || '';
  } while (token);
  cache = { at: Date.now(), files };
  return files;
}

const json = (res, code, body) => { res.writeHead(code, { 'Content-Type': 'application/json' }); res.end(JSON.stringify(body)); };

http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://localhost');
  try {
    if (url.pathname === '/api/config') {
      return json(res, 200, {
        demo: DEMO, clientName: CLIENT_NAME, editorWa: EDITOR_WA,
        albumKey: createHash('sha1').update(DRIVE_FOLDER_ID || 'demo').digest('hex').slice(0, 8)
      });
    }
    if (url.pathname === '/api/photos') {
      if (DEMO) return json(res, 200, []);
      return json(res, 200, await listPhotos());
    }
    if (url.pathname.startsWith('/api/thumb/')) {
      const id = url.pathname.split('/').pop();
      // hanya izinkan file yang memang ada di folder album
      if (DEMO || !(await listPhotos()).some(f => f.id === id)) return json(res, 404, { error: 'Foto tidak ditemukan' });
      const w = Math.min(Math.max(parseInt(url.searchParams.get('w')) || 400, 100), 2000);
      const r = await fetch(`https://drive.google.com/thumbnail?id=${encodeURIComponent(id)}&sz=w${w}`);
      if (!r.ok) return json(res, 502, { error: 'Gagal mengambil thumbnail' });
      res.writeHead(200, { 'Content-Type': r.headers.get('content-type') || 'image/jpeg', 'Cache-Control': 'public, max-age=86400' });
      return res.end(Buffer.from(await r.arrayBuffer()));
    }
    // file statis
    const rel = url.pathname === '/' ? 'index.html' : url.pathname.slice(1);
    const file = path.join(PUBLIC, path.normalize(rel));
    if (!file.startsWith(PUBLIC)) return json(res, 403, { error: 'Dilarang' });
    const data = await readFile(file);
    res.writeHead(200, { 'Content-Type': MIME[path.extname(file)] || 'application/octet-stream' });
    res.end(data);
  } catch (e) {
    if (e.code === 'ENOENT') return json(res, 404, { error: 'Tidak ditemukan' });
    json(res, 500, { error: e.message });
  }
}).listen(PORT, () => console.log(`Pilih Foto jalan di http://localhost:${PORT}${DEMO ? '  (mode demo: GOOGLE_API_KEY / DRIVE_FOLDER_ID belum diisi)' : ''}`));
