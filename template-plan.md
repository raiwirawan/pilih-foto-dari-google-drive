# PLAN: Pilih Foto Drive → Page Template WordPress (Tema `bcsolutions`)

Mengubah aplikasi vanilla `pilih-foto-drive` (Node.js) menjadi dua **page template** di tema `bcsolutions`:
**Studio Editor** dan **Studio Client**. Seluruh fungsi masuk ke `functions.php`, aset CSS/JS dipisah dari aset tema, dan tidak ada login (username/password) untuk editor maupun klien.

---

## 1. Tujuan dan batasan

| # | Permintaan | Cara dipenuhi |
|---|-----------|---------------|
| 1 | Vanilla jadi template + `functions.php` | 2 file template + 1 blok kode di akhir `functions.php` |
| 2 | Cukup pilih template saat membuat page baru | `Template Name: Studio Editor` dan `Studio Client` |
| 3 | Aset style/JS terpisah dari aset tema | Folder `wp-content/studio-assets/` (di luar tema) |
| 4 | Tanpa kredensial | Klien: token acak per galeri. Editor: kunci rahasia otomatis. Tidak ada akun/password |
| 5 | Terintegrasi dengan tema, tanpa bentrok CSS/JS | Isolasi berlapis: awalan `st-`, scope `.st-app`, dequeue aset tema hanya di 2 template |

**Catatan penting tentang "tanpa kredensial":** kunci Google API (`STUDIO_GOOGLE_API_KEY`) tetap dibutuhkan karena Drive API tidak bisa membaca isi folder tanpanya. Kunci ini bukan login pengguna; ia disimpan di `wp-config.php` dan tidak pernah dikirim ke browser.

---

## 2. Temuan dari analisis kedua zip

### 2.1 Aplikasi vanilla
- `server.js` (Node, tanpa framework): login berbasis cookie HMAC, data di `users.json` dan `selections.json`, proxy thumbnail Drive, daftar foto dari Drive API (cache 60 detik).
- Halaman: `login.html`, `client.html`, `editor.html`, `result.html`; gaya di `app.css`; utilitas global di `shared.js` (`esc`, `toast`, `extractFolderId`).
- Status pilihan: `unstarted` → `draft` → `submitted` → `reopened`.
- Mode demo bila API key kosong (foto SVG placeholder).
- **Rahasia ikut ter-zip** (`.env`, `users.json` berisi password): rotasi Google API key sebelum dipakai di produksi.

### 2.2 Tema `bcsolutions`
- Sudah ada **draf lama**: `studio-functions.php`, `template-studio-editor.php`, `template-studio-client.php`. Draf belum di-`require` dari `functions.php` dan tidak aman (REST editor terbuka untuk publik, klien via `?gallery_id=123` yang bisa ditebak, proxy gambar memanggil URL bebas, aset di folder tema). **Diganti total**, `studio-functions.php` dihapus.
- `functions.php` memuat di **semua halaman**: `style.css`, `new-style.css`, Swiper (CSS+JS), Three.js, Lucide, `new-main.js`, jQuery (dependensi Swiper), dan beberapa font.
- `functions.php` diawali `ob_start()` global (penjaga output liar). Jika tidak ditangani, output gambar proxy bisa rusak.
- `functions.php` **tidak berakhir dengan `?>`** dan baris terakhirnya adalah `require_once` untuk `inc/bcs-wc-ui.php` (ada komentar "Jangan hapus require_once"). Blok studio ditempel **setelah** baris itu, diawali baris kosong.
- `header.php` minimal (`wp_head()` + `<body>`); `footer.php` mencetak `<footer>` hak cipta. Template studio memakai `get_header()` tetapi **tidak** memakai `get_footer()`.
- Tidak ada nama berawalan `studio` di `functions.php` (aman untuk prefix `studio_`).

### 2.3 Titik bentrok yang sudah teridentifikasi

| Sumber bentrok | Detail | Penanganan |
|---|---|---|
| Class sama | `.btn-primary`, `.btn-outline` (tema & vanilla), `.container`, `.toast` | Semua class studio berawalan `st-` |
| Selector global tema | `body`, `a`, `p`, `h1`, `ul`, `:root`, `body > header:not(.hero)` | Scope `.st-app`; tidak ada `<header>` langsung di bawah `<body>`; nilai tipografi eksplisit |
| Selector global vanilla | `body`, `h1/h2/h3`, `p`, `textarea`, `.container` | Dibuang, diganti aturan berlingkup |
| Variabel CSS | `:root` tema (`--red`, `--bg-background`, …) vs vanilla (`--color-*`) | Variabel `--st-*` didefinisikan di `.st-app`, bukan `:root` |
| ID DOM generik | vanilla memakai `main`, `lb`, `toast`, `grid`, `dlg`, `title` | Semua ID berawalan `st-` |
| Global JS | `window.esc`, `window.toast`, `window.extractFolderId` | IIFE + satu namespace `window.StudioApp` |
| Aset tema berat | Swiper, Three, Lucide, `new-main.js`, 2 CSS besar | Di-dequeue **hanya** di 2 template (prioritas 1000) |
| Tampilan | tema gelap merah vs vanilla terang biru | UI studio mengikuti palet tema (nilai disalin ke `--st-*`) |

---

## 3. Keputusan yang sudah disepakati

1. **Akses klien:** token acak 32 karakter per galeri; link `https://situs/halaman-klien/?k=TOKEN`.
2. **Akses editor:** kunci rahasia **dibuat otomatis**, disimpan di database (`wp_options`, `studio_editor_key`), dan **ditampilkan di halaman Studio Editor khusus untuk admin WordPress yang sedang login**.
3. **Editor tidak perlu login:** membuka link berkunci sekali di sebuah perangkat → kunci tersimpan di browser (localStorage) dan dihapus dari address bar.
4. **Lokasi aset:** `wp-content/studio-assets/` (default; satu konstanta untuk mengubahnya).
5. **Tampilan:** mengikuti tema gelap `bcsolutions`.
6. **Penempatan kode:** langsung di `functions.php` (sesuai permintaan awal). Opsi alternatif tanpa mengubah desain: pindahkan blok ke satu file dan ganti dengan 1 baris `require_once`.

---

## 4. Arsitektur

```
bcsolutions/                        (tema)
├─ functions.php                    + blok "STUDIO" di akhir file
├─ template-studio-editor.php       (tulis ulang)
├─ template-studio-client.php       (tulis ulang)
└─ studio-functions.php             (draf lama → DIHAPUS)

wp-content/studio-assets/           (di luar tema, kamu upload sendiri)
├─ css/
│  ├─ studio-base.css               token, reset berlingkup, tombol, dialog, toast, lightbox, grid
│  ├─ studio-client.css
│  └─ studio-editor.css
└─ js/
   ├─ studio-core.js                API wrapper, esc, toast, lightbox, dialog
   ├─ studio-client.js
   └─ studio-editor.js
```

Alur data:

```
Browser (template shell + JS)
   │  fetch REST  (header X-Studio-Token | X-Studio-Key | cookie+nonce untuk admin)
   ▼
WordPress REST  /wp-json/studio/v1/...   ──►  tabel {prefix}studio_galleries
   │                                      ──►  Google Drive API (daftar foto, cache transient 60 dtk)
   ▼
<img src="/?studio_thumb=FILEID&exp=...&sig=...">  ──►  proxy thumbnail (cek tanda tangan, cache file)
```

---

## 5. Blok kode di `functions.php`

Seluruh blok dibungkus penanda `// ===== STUDIO START =====` … `// ===== STUDIO END =====` agar mudah dicabut. Semua fungsi berawalan `studio_`; konstanta berawalan `STUDIO_`.

### 5.1 Konstanta dan konfigurasi
- `STUDIO_ASSETS_DIR` / `STUDIO_ASSETS_URL` → `WP_CONTENT_DIR . '/studio-assets/'` dan `content_url('studio-assets/')`.
- `STUDIO_GOOGLE_API_KEY` dibaca dari `wp-config.php`. Jika kosong → **mode demo** (foto placeholder), seperti vanilla.
- Template dikenali lewat konstanta: `template-studio-editor.php`, `template-studio-client.php`.
- Helper `studio_is_studio_page()` (true bila halaman memakai salah satu template).

### 5.2 Database
Tabel `{prefix}studio_galleries`:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint unsigned, PK | |
| `token` | char(32), UNIQUE | token klien |
| `title` | varchar(150) | nama klien/galeri |
| `source_folder_id` | varchar(100) | folder Drive foto asli |
| `result_folder_id` | varchar(100) | folder Drive hasil edit (opsional) |
| `max_select` | int | 0 = tanpa batas |
| `status` | varchar(20) | `unstarted` / `draft` / `submitted` / `reopened` |
| `selected_ids` | longtext | JSON array ID file Drive |
| `note` | text | catatan klien |
| `created_at`, `updated_at`, `submitted_at`, `reopened_at` | datetime | |

- Dibuat dengan `dbDelta` pada hook `init` bila `get_option('studio_db_version')` belum sama dengan versi kode (tema sudah aktif, jadi `after_switch_theme` tidak akan jalan).
- Tidak ada tabel foto/seleksi terpisah: daftar foto selalu dari Drive (transient `studio_drive_{md5(folderId)}`, 60 detik), pilihan disimpan sebagai JSON.

### 5.3 Akses editor: kunci otomatis
- `studio_get_editor_key()` → baca option `studio_editor_key`; bila belum ada, buat `wp_generate_password(40, false, false)` dan simpan (autoload = no).
- Validasi: `hash_equals($key_tersimpan, $header['X-Studio-Key'])`.
- **Admin WordPress otomatis lolos** tanpa kunci (`current_user_can('manage_options')`), asalkan request membawa nonce REST (`X-WP-Nonce`). Tanpa nonce, WordPress memperlakukan request sebagai tamu, jadi JS wajib mengirim nonce ketika `isAdmin` true.
- Endpoint khusus admin (cookie + nonce, **tidak** menerima kunci biasa):
  - `GET /editor/key` → mengembalikan kunci + link editor lengkap.
  - `POST /editor/key/rotate` → buat kunci baru (semua perangkat editor lain keluar sampai membuka link baru).
- Opsional override: bila `STUDIO_EDITOR_KEY` didefinisikan di `wp-config.php`, konstanta itu dipakai menggantikan option.
- Rate-limit kegagalan: transient per IP (`REMOTE_ADDR`, bisa difilter untuk Cloudflare); lebih dari 30 gagal / 15 menit → HTTP 429.

### 5.4 REST API `studio/v1`
Semua `permission_callback` memakai pemeriksaan token/kunci (tidak ada `__return_true`). Semua respons memakai `Cache-Control: no-store`.

**Klien** (header `X-Studio-Token`):

| Method | Route | Fungsi |
|---|---|---|
| GET | `/client/gallery` | nama, `maxSelect`, status, ID terpilih, catatan, WA editor, ada/tidaknya folder hasil |
| GET | `/client/photos` | daftar foto folder sumber (`id`, `name`, `src` bertanda tangan) |
| GET | `/client/result` | daftar foto hasil + ID folder hasil |
| PUT | `/client/selection` | autosave `ids` + `note` |
| POST | `/client/submit` | kirim final (mengunci galeri) |

Validasi server: status `submitted` → 409; jumlah > `max_select` → 422; setiap ID harus ada di listing folder sumber; catatan dipotong panjangnya.

**Editor** (header `X-Studio-Key`, atau admin + nonce):

| Method | Route | Fungsi |
|---|---|---|
| GET / POST | `/editor/galleries` | daftar (jumlah terpilih, status, link klien) / buat galeri baru |
| GET | `/editor/galleries/{id}` | detail + foto terpilih |
| PATCH | `/editor/galleries/{id}` | ubah judul, folder, batas, folder hasil (ganti folder sumber → pilihan direset) |
| DELETE | `/editor/galleries/{id}` | hapus |
| POST | `/editor/galleries/{id}/reopen` | `submitted` → `reopened` |
| POST | `/editor/galleries/{id}/regenerate-token` | buat link klien baru (opsional) |
| POST | `/editor/drive/resolve` | cek folder Drive (nama, jumlah foto) |
| GET / PATCH | `/editor/settings` | nomor WhatsApp editor |
| GET | `/editor/key`, POST `/editor/key/rotate` | khusus admin (lihat 5.3) |

Sanitasi: folder ID harus cocok `^[A-Za-z0-9_-]{10,}$`; WhatsApp hanya angka; semua query memakai `$wpdb->prepare`.

### 5.5 Proxy thumbnail (URL bertanda tangan)
- Gambar dimuat dengan `<img>` sehingga tidak bisa mengirim header. Solusinya: REST mengembalikan `src` berbentuk `/?studio_thumb=FILEID&exp=UNIX&sig=HMAC`.
- `sig = hash_hmac('sha256', FILEID|exp, wp_salt('auth'))`; masa berlaku 12 jam. Token klien/kunci editor **tidak pernah** muncul di URL gambar.
- Handler di hook `init` (prioritas awal): validasi `FILEID` dengan regex, validasi `exp` + `sig`, lebar `w` dibatasi 100–2000.
- Pengambilan dari `https://drive.google.com/thumbnail?id=…&sz=w…` via `wp_remote_get` (host tetap, timeout, wajib `Content-Type: image/*`).
- Cache file di `wp-content/uploads/studio-cache/` (batas total ± 200 MB, pembersihan lewat WP-Cron) + header `Cache-Control`/`ETag`.
- **Sebelum mengirim gambar:** `while (ob_get_level()) ob_end_clean();` untuk menembus `ob_start()` global tema, lalu `exit`.
- Mode demo: thumbnail berupa data-URI SVG, tanpa proxy.

### 5.6 Enqueue dan isolasi aset
- Hook `wp_enqueue_scripts` (prioritas 20), **hanya** bila `studio_is_studio_page()`:
  - CSS: `studio-font` (Plus Jakarta Sans, milik studio sendiri), `studio-base`, lalu `studio-editor` atau `studio-client`.
  - JS: `studio-core` → `studio-editor` / `studio-client`. Versi memakai `filemtime`. `defer` ditambahkan lewat filter `script_loader_tag` khusus handle berawalan `studio-` (tidak menyentuh daftar defer milik tema).
  - Konfigurasi dikirim via `wp_add_inline_script(..., 'before')` sebagai `window.StudioConfig` (JSON): alamat REST, role, `clientPageUrl` (editor), `isAdmin` + nonce (hanya bila admin), alamat aset. **Token klien tidak ditanam di HTML** (dibaca JS dari `?k=`), sehingga HTML shell aman di-cache.
- Hook yang sama pada **prioritas 1000** (setelah `bcs_dequeue_unused_css` di 999) men-dequeue aset tema di halaman studio saja: `main-style`, `new-style`, `swiper-css`, `swiper-js`, `three-js`, `lucide-js`, `new-main-js`, font tema. Daftar handle final ditentukan dari audit (Fase 1) dan bisa diubah lewat filter `studio_dequeue_handles`.
- Jika folder aset atau file tidak ditemukan, halaman menampilkan pesan jelas (hanya untuk admin).

### 5.7 Header halaman dan privasi
- `wp_robots` → `noindex, nofollow` di 2 template; `send_headers` → `X-Robots-Tag`, `Referrer-Policy: no-referrer` (agar token tidak bocor ke Drive/WhatsApp), `nocache_headers()`.
- `define('DONOTCACHEPAGE', true)` di halaman studio agar plugin cache (WP Rocket/LiteSpeed dll.) tidak menyimpan halaman.
- Class `st-page` ditambahkan lewat `body_class` hanya di 2 template.

### 5.8 Pembantu template
- `studio_render_shell($role)` mencetak root `<div id="st-app" class="st-app st-{role}">` + pesan "memuat" + `<noscript>`.
- Link klien: `clientPageUrl` dideteksi otomatis (page terbit pertama dengan template `template-studio-client.php`). Jika belum ada, editor menampilkan peringatan "Buat page dengan template Studio Client".

---

## 6. Dua template

`template-studio-editor.php` dan `template-studio-client.php` identik strukturnya:

```php
<?php
/**
 * Template Name: Studio Editor   // atau: Studio Client
 */
get_header();
studio_render_shell('editor');   // atau 'client'
wp_footer();
?>
</body>
</html>
```

- `get_footer()` sengaja tidak dipanggil (menghindari footer hak cipta tema), `wp_footer()` tetap dipanggil agar script ter-load.
- Konten page dari editor WordPress/Elementor **tidak ditampilkan** oleh template ini. Jangan membangun page ini dengan Elementor.
- Tidak ada elemen `<header>` langsung di bawah `<body>` (menghindari aturan `body > header:not(.hero)` milik tema).

---

## 7. Aset (porting dari vanilla)

### 7.1 Fitur Klien (`studio-client.js`)
- Ambil token dari `?k=`, simpan di `sessionStorage`, bersihkan URL, kirim sebagai header.
- Grid foto + pilih/batal, penghitung `n / batas`, progress bar, "pilih semua" (sampai batas), reset.
- Lightbox (prev/next, tombol pilih di dalam lightbox, keyboard).
- Autosave berdebounce ke `PUT /client/selection`.
- Dialog kirim: ringkasan jumlah + catatan → `POST /client/submit` → layar sukses + tombol WhatsApp ke editor (`https://wa.me/NOMOR?text=…`).
- Status `submitted`: UI terkunci. Status `reopened`: bisa mengedit lagi.
- Banner "foto hasil sudah tersedia" → tampilan hasil di template yang sama (menggantikan `result.html`): grid hasil, lightbox, tombol **Unduh** (`drive.google.com/uc?export=download&id=…`) dan **Buka folder di Drive**.
- Halaman error ramah untuk token tidak valid/tidak ada.

### 7.2 Fitur Editor (`studio-editor.js`)
- Ambil kunci dari `?key=` (disimpan di `localStorage`, URL dibersihkan); admin memakai nonce.
- Kotak "Link editor" untuk admin: tampil/sembunyi kunci, salin link, tombol "Buat kunci baru" (dengan konfirmasi).
- Daftar galeri dengan lencana status (`unstarted`/`draft`/`submitted`/`reopened`), jumlah terpilih, waktu update.
- Panel detail: foto terpilih (thumbnail + lightbox), catatan klien.
- Tambah/ubah/hapus galeri: judul, tempel link/ID folder sumber (`extractFolderId` + cek lewat `drive/resolve`), folder hasil, batas pilih. Tanpa kolom username/password.
- Salin link klien, buka kembali (reopen), buat ulang link (opsional), pengaturan WhatsApp editor.
- Layar "Link editor tidak valid" bila 401 (tanpa membocorkan apa pun).

### 7.3 Aturan penulisan anti-bentrok
**CSS**
- Semua class berawalan `st-`; semua selector berada di bawah `.st-app`.
- Variabel `--st-*` didefinisikan pada `.st-app`. Karena CSS tema di-dequeue di halaman ini, **nilai warna disalin dari palet tema** (mis. `#e60000`, `#0a0a0a`, `#a3a3a3`) sebagai nilai tetap, tidak bergantung pada `--red` dll.
- Tidak ada selector `body`, `html`, `h1`, `p`, `a`, `:root` global. Tipografi, margin, dan warna elemen di dalam `.st-app` diberi nilai eksplisit.
- Responsif mobile-first; target sentuh ≥ 44px; mendukung `prefers-reduced-motion`.

**JS**
- Satu IIFE per file; satu namespace `window.StudioApp`; tanpa jQuery dan tanpa global lain.
- Semua ID/atribut data berawalan `st-`.
- Semua data dari server dirender dengan `textContent` atau fungsi `esc()` (tidak ada `innerHTML` dengan data mentah).

---

## 8. Fase pengerjaan

### Fase 0 — Persiapan (kamu)
- [ ] Backup `functions.php` dan database; siapkan staging bila ada.
- [ ] Rotasi Google API key lama (karena `.env` ikut ter-zip) dan batasi key ke Drive API.
- [ ] Pastikan folder Drive berstatus "Siapa saja yang memiliki link" (Viewer).
- [ ] Tambahkan di `wp-config.php`: `define('STUDIO_GOOGLE_API_KEY', '...');`

### Fase 1 — Audit tema
- [ ] Dump `$wp_styles->queue` dan `$wp_scripts->queue` pada halaman dengan template studio (sementara, hanya admin).
- [ ] Tetapkan daftar final `studio_dequeue_handles`.
- [ ] Konfirmasi versi WordPress/PHP di hosting (`wp_robots` butuh WP ≥ 5.7).

### Fase 2 — Blok `functions.php`
- [ ] Konstanta + helper (5.1).
- [ ] Skema DB + versi (5.2).
- [ ] Kunci editor otomatis + rate limit (5.3).
- [ ] REST klien dan editor (5.4).
- [ ] Proxy thumbnail + cache file + cron pembersihan (5.5).
- [ ] Enqueue + isolasi + header privasi (5.6, 5.7).

### Fase 3 — Template
- [ ] Tulis ulang 2 template (6); hapus `studio-functions.php` lama.

### Fase 4 — Aset
- [ ] `studio-base.css`, `studio-client.css`, `studio-editor.css`.
- [ ] `studio-core.js`, `studio-client.js`, `studio-editor.js`.

### Fase 5 — Integrasi dan QA
- [ ] Lihat checklist di bagian 9.

### Fase 6 — Pemasangan (kamu)
- [ ] Upload folder `studio-assets/` ke `wp-content/`.
- [ ] Upload 2 template; tempel blok ke akhir `functions.php` (setelah baris `require_once` terakhir).
- [ ] Buat 2 page: judul bebas (mis. "Studio Editor" dan "Pilih Foto"), pilih template masing-masing, **Publish**.
- [ ] Login admin → buka page editor → salin link editor → kirim ke editor.
- [ ] Buat galeri uji → buka link klien di HP.

---

## 9. Checklist pengujian

**Bentrok CSS/JS**
- [ ] Konsol browser bersih (tidak ada error / warning dari studio maupun tema) di 2 halaman studio.
- [ ] Tab Network: tidak ada request ke Swiper, Three.js, Lucide, `style.css`, `new-style.css` di halaman studio.
- [ ] Halaman lain (beranda, portfolio, WooCommerce, Elementor editor) **tidak berubah** dan tidak memuat aset studio.
- [ ] Tidak ada `id` ganda di DOM; tidak ada variabel global baru selain `StudioApp` dan `StudioConfig`.

**Fungsional**
- [ ] Alur penuh: buat galeri → link klien → pilih → autosave → kirim → editor melihat → reopen → klien mengubah → kirim ulang.
- [ ] Batas pilih ditegakkan di server (coba kirim lewat konsol).
- [ ] Ganti folder sumber mereset pilihan; hapus galeri menghapus data.
- [ ] Banner/tampilan hasil + unduh berfungsi.
- [ ] Mode demo bekerja saat API key kosong.

**Keamanan**
- [ ] Token salah/tanpa token → ditolak; tidak bisa membaca galeri lain.
- [ ] Endpoint editor tanpa kunci → 401; kunci salah berulang → 429.
- [ ] ID foto dari galeri lain → ditolak; tanda tangan thumbnail kedaluwarsa/dimodifikasi → ditolak.
- [ ] Setelah rotasi kunci, link lama berhenti berlaku.
- [ ] Kunci editor tidak muncul untuk pengunjung non-admin (cek sumber HTML dan respons REST).
- [ ] `noindex` dan `no-referrer` aktif; halaman tidak tersimpan di cache plugin.

**Perangkat**
- [ ] iPhone Safari, Android Chrome, desktop Chrome/Firefox.
- [ ] Galeri 200+ foto: scroll dan lazy-load lancar.

---

## 10. Risiko dan mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Salah ketik di `functions.php` | Seluruh situs blank | Backup + staging; tempel utuh; kembalikan file lama via FTP bila perlu. (Sandbox pengembangan tidak punya PHP, jadi uji sintaks dilakukan di staging/`php -l` di hosting) |
| Link klien/editor bocor | Pihak lain bisa melihat/mengubah | Token/kunci panjang, rotasi satu klik, `no-referrer`, tanpa token di URL gambar |
| Handle aset tema ada yang terlewat | Gaya tema ikut termuat | Audit Fase 1 + scope `.st-app` sebagai lapisan kedua |
| Plugin cache menyimpan respons | Data basi | `DONOTCACHEPAGE` + `no-store` pada REST + minta exclude `/wp-json/studio/` |
| Kuota Drive API | Daftar foto gagal | Cache 60 detik, pesan error jelas |
| Output buffer tema merusak gambar | Thumbnail rusak | Bersihkan semua level `ob` sebelum stream |
| Thumbnail Drive berubah/dibatasi Google | Gambar gagal tampil | Cache file + placeholder + tampilkan pesan |
| Edit page dengan Elementor | Konten tidak tampil | Dicatat di panduan: page hanya wadah template |

---

## 11. Di luar cakupan (bisa ditambah nanti)
- Impor otomatis data klien `budi` dari `selections.json` (tanpa password) — bisa dibuat manual lewat UI editor.
- Notifikasi email saat klien mengirim pilihan.
- Akun terpisah per editor / jejak siapa mengubah apa.
- Ekspor daftar nama file terpilih (CSV/teks) untuk editor.

---

## 12. Hasil akhir yang akan diserahkan
1. Blok kode siap tempel untuk `functions.php`.
2. `template-studio-editor.php` dan `template-studio-client.php`.
3. Folder `studio-assets/` berisi 3 CSS dan 3 JS.
4. Panduan pemasangan singkat + checklist uji.
