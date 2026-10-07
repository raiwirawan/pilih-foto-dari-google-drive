// shared.js - Fungsi utilitas untuk frontend klien dan editor

const esc = (str) => {
  if (!str) return '';
  return String(str).replace(/[&<>"'/]/g, (s) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#x2F;'
  }[s]));
};

const toast = (text) => {
  let el = document.getElementById('toast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'toast';
    el.className = 'toast';
    document.body.appendChild(el);
  }
  el.textContent = text;
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 3000);
};

const extractFolderId = (input) => {
  if (!input) return null;
  input = input.trim();
  // Format 1: Cuma ID mentah yang panjangnya 10+ karakter alfanumerik
  if (/^[A-Za-z0-9_-]{10,}$/.test(input)) return input;
  // Format 2: URL
  try {
    const url = new URL(input);
    if (url.hostname.includes('drive.google.com')) {
      // /folders/ID
      const m = url.pathname.match(/\/folders\/([A-Za-z0-9_-]+)/);
      if (m) return m[1];
      // ?id=ID
      const idParam = url.searchParams.get('id');
      if (idParam && /^[A-Za-z0-9_-]{10,}$/.test(idParam)) return idParam;
    }
  } catch (e) {}
  return null;
};

window.esc = esc;
window.toast = toast;
window.extractFolderId = extractFolderId;
