/* studio-core.js */
(function() {
  window.StudioApp = window.StudioApp || {};
  
  const esc = (str) => {
    if (!str) return '';
    return String(str).replace(/[&<>"'/]/g, (s) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#x2F;'
    }[s]));
  };
  
  const toast = (text) => {
    let el = document.getElementById('st-toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'st-toast';
      el.className = 'st-toast';
      document.body.appendChild(el);
    }
    el.textContent = text;
    el.classList.add('st-show');
    setTimeout(() => el.classList.remove('st-show'), 3000);
  };
  
  const extractFolderId = (input) => {
    if (!input) return null;
    input = input.trim();
    if (/^[A-Za-z0-9_-]{10,}$/.test(input)) return input;
    try {
      const url = new URL(input);
      if (url.hostname.includes('drive.google.com')) {
        const m = url.pathname.match(/\/folders\/([A-Za-z0-9_-]+)/);
        if (m) return m[1];
        const idParam = url.searchParams.get('id');
        if (idParam && /^[A-Za-z0-9_-]{10,}$/.test(idParam)) return idParam;
      }
    } catch (e) {}
    return null;
  };

  const apiFetch = async (path, options = {}) => {
    const config = window.StudioConfig;
    const url = config.apiBase + path;
    const headers = { 'Content-Type': 'application/json' };
    
    if (config.role === 'client') {
      const token = sessionStorage.getItem('st_token');
      if (token) headers['X-Studio-Token'] = token;
    } else {
      if (config.isAdmin) {
        headers['X-WP-Nonce'] = config.nonce;
      } else {
        const key = localStorage.getItem('st_editor_key');
        if (key) headers['X-Studio-Key'] = key;
      }
    }

    try {
      const res = await fetch(url, {
        ...options,
        headers: { ...headers, ...(options.headers || {}) }
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || data.error || 'Terjadi kesalahan');
      return data;
    } catch (err) {
      throw err;
    }
  };
  
  const openDialog = (id) => {
    const d = document.getElementById(id);
    const bd = document.getElementById(id + '-backdrop');
    if (d) d.classList.add('st-open');
    if (bd) bd.classList.add('st-open');
  };
  
  const closeDialog = (id) => {
    const d = document.getElementById(id);
    const bd = document.getElementById(id + '-backdrop');
    if (d) d.classList.remove('st-open');
    if (bd) bd.classList.remove('st-open');
  };

  StudioApp.esc = esc;
  StudioApp.toast = toast;
  StudioApp.extractFolderId = extractFolderId;
  StudioApp.apiFetch = apiFetch;
  StudioApp.openDialog = openDialog;
  StudioApp.closeDialog = closeDialog;
})();
