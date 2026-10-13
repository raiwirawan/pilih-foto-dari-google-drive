/* studio-core.js */
(function() {
  window.StudioApp = window.StudioApp || {};

  let toastTimer = null;

  const esc = (str) => {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, (s) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
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
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('st-show'), 3000);
  };

  const copyText = async (text, msg = 'Disalin!') => {
    if (!text) return false;
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        if (msg) toast(msg);
        return true;
      }
    } catch (e) {}

    try {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.left = '-9999px';
      ta.style.top = '0';
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      if (msg) toast(msg);
      return true;
    } catch (err) {
      toast('Gagal menyalin teks');
      return false;
    }
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
    const config = window.StudioConfig || {};
    const url = (config.apiBase || '') + path;
    const headers = { 'Content-Type': 'application/json' };

    if (config.role === 'client') {
      const token = sessionStorage.getItem('st_token') || localStorage.getItem('st_token');
      if (token) headers['X-Studio-Token'] = token;
    } else {
      if (config.isAdmin && config.nonce) {
        headers['X-WP-Nonce'] = config.nonce;
      }
      const key = localStorage.getItem('st_editor_key');
      if (key) headers['X-Studio-Key'] = key;
    }

    const res = await fetch(url, {
      cache: 'no-store',
      ...options,
      headers: { ...headers, ...(options.headers || {}) }
    });

    let data = {};
    try {
      data = await res.json();
    } catch (e) {
      throw new Error('Respons server tidak valid');
    }

    if (!res.ok) {
      const err = new Error(data.message || data.error || 'Terjadi kesalahan');
      err.status = res.status;
      err.code = data.code || '';
      throw err;
    }
    return data;
  };

  const openDialog = (id) => {
    const d = document.getElementById(id);
    const bd = document.getElementById(id + '-backdrop');
    if (d) d.classList.add('st-open');
    if (bd) {
      bd.classList.add('st-open');
      bd.onclick = () => closeDialog(id);
    }
  };

  const closeDialog = (id) => {
    const d = document.getElementById(id);
    const bd = document.getElementById(id + '-backdrop');
    if (d) d.classList.remove('st-open');
    if (bd) bd.classList.remove('st-open');
  };

  StudioApp.esc = esc;
  StudioApp.toast = toast;
  StudioApp.copyText = copyText;
  StudioApp.extractFolderId = extractFolderId;
  StudioApp.apiFetch = apiFetch;
  StudioApp.openDialog = openDialog;
  StudioApp.closeDialog = closeDialog;
})();

