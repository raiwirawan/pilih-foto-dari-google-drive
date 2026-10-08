# Plan Integrasi WordPress dan Hardening Production: Pilih Foto

*Disusun dari audit seluruh isi pilih-foto-drive-vanilla.zip (server.js, 5 halaman HTML, app.css, shared.js, data/, .env). 8 Oktober 2026.*

## 1. Ringkasan

Aplikasi saat ini adalah prototipe Node.js tanpa dependensi dengan penyimpanan file JSON. Rekomendasi saya: **jadikan plugin WordPress sendiri**. Backend Node diganti PHP (REST API + tabel database khusus), frontend vanilla dipertahankan tetapi bagian render-nya ditulis ulang agar bebas injeksi.

Tiga prinsip utama:

1. **Server tidak pernah percaya input.** Semua input divalidasi skema, semua output di-escape.
2. **Browser tidak pernah melihat ID atau URL Google Drive.** Hanya ID internal yang buram.
3. **Editor dan klien terpisah total:** role, capability, namespace API, halaman, dan bundel JS berbeda.

Catatan jujur: tidak ada sistem yang bisa dijamin 100% aman atau 100% optimal. Plan ini menghapus semua kerentanan yang ditemukan di audit, menambah pertahanan berlapis, dan menetapkan target terukur serta uji verifikasi (bagian 15) supaya klaim aman dan cepat bisa dibuktikan, bukan sekadar diyakini.

**Tindakan darurat sebelum apa pun:**

- Cabut (revoke) dan ganti Google API key. Key asli ada di file .env di dalam zip.
- Jangan bagikan lagi zip yang berisi .env dan folder data/ (berisi nama klien, nomor WA editor, ID folder Drive asli).
- Ganti semua password akun prototipe. Password demo tertulis di login.html.

## 2. Pemahaman proyek saat ini

**Stack:** Node 20+, satu file server.js (HTTP mentah), file statis di public/, data di data/users.json dan data/selections.json (dimuat ke memori, ditulis atomik via rename).

**Peran:**

- **Klien** membuka client.html, melihat foto dari folder Drive miliknya, mengetuk untuk memilih (autosave 500 ms, batas maxSelect), lalu kirim. Setelah kirim status terkunci. Setelah editor mengunggah hasil, klien melihat result.html.
- **Editor** membuka editor.html: CRUD klien, tempel link folder mentah dan folder hasil (dicek lewat /api/editor/drive/resolve), set batas pilihan, buka kunci (reopen), lihat pilihan klien, unduh CSV, atur nomor WA.

**Status pilihan:** unstarted, draft, submitted, reopened.

**Drive:** daftar file lewat Drive API v3 dengan API key. Thumbnail diproksi dari drive.google.com/thumbnail dan di-cache 50 MB di memori. Folder wajib berstatus *siapa saja yang memiliki link*.

**Endpoint:** /api/login, /api/logout, /api/me, /api/photos, /api/result-photos, /api/selection (GET/PUT), /api/selection/submit, /api/editor/settings, /api/editor/drive/resolve, /api/editor/clients (+ /:id, /:id/reopen), /api/thumb/:id.

## 3. Temuan audit

### Kritis

- **V1. Rahasia ikut di zip.** API key Google asli di .env. data/ berisi data klien nyata. data/ juga tidak ada di .gitignore.
- **V2. Autentikasi lemah.** Password plaintext di users.json, dibandingkan dengan === (bukan constant-time), default password123, akun demo beserta password tampil di login.html, tanpa rate limit atau lockout.
- **V3. Sesi rapuh.** SESSION\_SECRET jatuh ke nilai secret bila kosong. Cookie tanpa flag Secure. Token stateless 30 hari yang tidak bisa dicabut (logout hanya menghapus cookie di browser).
- **V4. XSS di sisi editor.** onclick=viewClient('id') dan tombol Salin Teks menyisipkan ID dan nama file mentah ke atribut JavaScript. folderId masuk innerHTML tanpa escape. Nama file berasal dari Drive. XSS di dashboard editor berarti pengambilalihan akun editor.
- **V5. Injeksi URL dan parameter.** folderId dari editor tidak divalidasi di server dan disisipkan mentah ke query Drive (q = folderId in parents ...) serta ke path URL di /drive/resolve. Nomor WA tidak divalidasi di server tetapi dipakai di https://wa.me/NOMOR. Parameter ?client= dibaca dari URL. ID klien tanpa validasi (titik merusak parsing cookie, **proto** bisa meracuni objek selections).

### Tinggi

- **V6. Proxy thumbnail terbuka.** Editor (dan siapa pun saat mode demo) bisa memproksi thumbnail file Drive mana pun. Cache-Control: public pada gambar privat berisiko tersimpan di CDN atau proxy dan bocor antar-pengguna. Parameter w bebas 100 sampai 2000 membuat cache key meledak.
- **V7. Tidak ada kontrol akses nyata atas foto.** Folder harus publik via link supaya thumbnail jalan. Siapa pun yang memegang link folder melewati portal. Link bocor lewat tombol Buka Folder Google Drive dan thumbnailLink di /api/photos.
- **V8. Validasi pilihan lemah.** ids tidak dicek tipe maupun kepemilikan folder. maxSelect = 0 berarti tak terbatas (sampai 1 MB). ids non-array bisa tersimpan dan membuat dashboard editor error 500 (data poisoning).
- **V9. Tanpa CSRF token dan tanpa header keamanan.** Hanya SameSite=Lax. /api/logout menerima GET. Tidak ada CSP, X-Frame-Options (clickjacking), nosniff, HSTS, Referrer-Policy.
- **V10. Editor dan klien satu aplikasi.** Satu cookie, satu origin, satu file user, role hanya flag di JSON. File editor.html bisa diambil klien.

### Sedang

- **V11.** Error 500 mengirim e.message ke browser. Tidak ada audit log. Catatan klien tanpa batas panjang.
- **V12.** Penyimpanan JSON di memori: race condition baca-ubah-tulis, tidak bisa scale, tanpa backup.
- **V13.** CSV injection (sel diawali = + - @) dan tanda kutip tidak di-escape. Unduhan hasil lewat uc?export=download hanya jalan untuk file publik.
- **V14.** Font Google eksternal tanpa SRI. Inline script dan inline handler menghalangi CSP ketat.
- **V15.** Pemeriksaan path statis memakai startsWith(PUBLIC) tanpa pemisah direktori.

### Performa

- **P1.** Tiap request thumbnail memanggil listPhotos (dua kali untuk klien) dan fetch ke Drive tanpa timeout dan tanpa batas concurrency. Saat cache 60 detik kedaluwarsa terjadi stampede.
- **P2.** Cache thumbnail di memori 50 MB, eviksi hanya satu entri per tulis, hilang saat restart.
- **P3.** Daftar foto diambil langsung dari Drive di tiap sesi, bukan dari database lokal.
- **P4.** Autosave mengirim seluruh daftar ID tiap perubahan. Dashboard editor polling 15 detik walau tab tersembunyi dan ikut memanggil Drive.
- **P5.** Aset tanpa cache header, ETag, atau kompresi. Font eksternal. CSS satu file global dengan nama kelas generik (.btn, .container, .banner, .toast) yang rawan bentrok dengan tema WordPress.

## 4. Keputusan arsitektur

**Opsi A (direkomendasikan): plugin WordPress native.** Login memakai pengguna WordPress, API memakai WP REST API, data di tabel khusus lewat $wpdb, Drive diakses dari PHP. Satu sistem autentikasi, bisa dipasang di hosting WordPress biasa, memakai fitur keamanan WordPress (nonce, role, sesi yang bisa dicabut, reset password).

**Opsi B: iframe atau reverse proxy ke app Node.** Dua sistem login, cookie lintas origin, hosting harus mendukung Node. Tidak disarankan.

**Opsi C: headless Node + SSO WordPress.** Paling kompleks, hanya masuk akal bila Node tetap harus dipertahankan.

**Asumsi yang perlu Anda konfirmasi:** PHP 8.1+, WordPress 6.4+, MySQL atau MariaDB InnoDB, HTTPS penuh, hosting mengizinkan menyimpan satu file kredensial di luar webroot. Ekstensi Imagick atau GD tersedia.

## 5. Pemisahan editor dan klien (total)

- **Role dan capability terpisah:** pf\_client dengan capability pf\_view\_own\_gallery; pf\_editor dengan pf\_manage\_galleries. Administrator tidak otomatis dipakai untuk operasional.
- **Namespace API terpisah:** /pilihfoto/v1/client/\* dan /pilihfoto/v1/editor/\*, masing-masing dengan permission\_callback sendiri. Endpoint klien selalu memeriksa kepemilikan (gallery.client\_user\_id sama dengan pengguna saat ini). Tidak ada endpoint bersama yang mengembalikan data berbeda tergantung role.
- **Permukaan terpisah:** portal klien di halaman front-end (/foto/). Dashboard editor di menu wp-admin khusus (Pilih Foto), sehingga ikut perlindungan wp-admin dan bisa diwajibkan 2FA.
- **Bundel JS terpisah:** client.js dan editor.js. Kode editor tidak pernah dikirim ke browser klien (dan sebaliknya), di-enqueue hanya di halaman masing-masing.
- **Klien dikunci dari wp-admin:** redirect di admin\_init, sembunyikan admin bar, login\_redirect divalidasi dengan wp\_validate\_redirect, dan blokir enumerasi pengguna lewat /wp/v2/users serta arsip author.
- **Tautan editor dari WhatsApp** memakai ID galeri bilangan bulat, bukan username: admin.php?page=pilihfoto&gallery=123, divalidasi dan tetap melewati cek capability.

## 6. Model data baru (tabel khusus, dbDelta)

- **pf\_galleries:** id, client\_user\_id, title, source\_folder\_id, result\_folder\_id, max\_select, status (unstarted, draft, submitted, reopened), note, submitted\_at, reopened\_at, version (optimistic locking), deleted\_at (soft delete), created\_by, created\_at, updated\_at. Index pada client\_user\_id dan status.
- **pf\_photos:** id, gallery\_id, kind (source atau result), drive\_file\_id, name, mime, drive\_version (md5Checksum atau modifiedTime), position, synced\_at. UNIQUE (gallery\_id, kind, drive\_file\_id).
- **pf\_selections:** gallery\_id, photo\_id, created\_at. Primary key gabungan, foreign key logis ke pf\_photos. Batas maxSelect ditegakkan di dalam transaksi.
- **pf\_audit:** id, gallery\_id, actor\_id, action, meta (JSON kecil), ip\_hash, created\_at. Mencatat buat, ubah, kirim, buka kunci, hapus, sinkron, login gagal.
- **Options (autoload no):** nomor WA editor (digit saja), ID sinkron terakhir, pengaturan retensi.

Pilihan tersimpan sebagai baris relasional, bukan array JSON. Hasilnya tidak bisa diracuni dengan tipe aneh, mudah dihitung, dan konsisten.

## 7. REST API dan kontrak validasi

Semua route mendaftarkan args dengan type, required, validate\_callback, sanitize\_callback, serta menolak properti tak dikenal (additionalProperties false). Tidak ada mass assignment: tiap endpoint punya allowlist field.

**Klien:**

- GET /client/galleries: daftar galeri miliknya.
- GET /client/galleries/{id}: foto (ID internal), pilihan, status, batas.
- PATCH /client/galleries/{id}/selection: payload delta {add:\[int\], remove:\[int\], note?, version}. Konflik versi menghasilkan 409.
- POST /client/galleries/{id}/submit: memvalidasi jumlah dan kepemilikan, lalu mengunci.
- GET /client/galleries/{id}/results: daftar hasil edit.

**Editor:**

- GET dan POST /editor/galleries; GET, PATCH, DELETE /editor/galleries/{id}.
- POST /editor/galleries/{id}/reopen.
- POST /editor/galleries/{id}/sync: sinkron daftar foto dari Drive.
- POST /editor/drive/validate: menerima teks tautan atau ID, mengekstrak dan memverifikasi di server.
- GET /editor/galleries/{id}/export.csv: dibuat di server.
- GET dan PUT /editor/settings: nomor WA.

**Aturan status (state machine di server):** unstarted ke draft ke submitted ke reopened ke submitted. Perubahan pilihan hanya sah pada draft dan reopened. Transisi lain ditolak 409.

## 8. Integrasi Google Drive dan pipeline media

**Akses Drive:** ganti API key dengan **service account** (scope drive.readonly). Folder dibagikan hanya ke email service account sebagai Viewer dan **tidak lagi dipublikkan**. File JSON kredensial disimpan di luar webroot dengan permission 0400, path lewat konstanta di wp-config.php. Token JWT ditandatangani dengan openssl bawaan PHP lewat wp\_remote\_post, tanpa dependensi Composer berat. Klien tidak lagi bisa menembus portal hanya dengan link folder.

**Sinkron daftar foto:** editor menekan Sinkron (atau WP-Cron untuk galeri aktif). Plugin mengambil daftar dari Drive dengan fields minimal dan pageSize 1000, lalu melakukan diff ke pf\_photos (tambah, hapus, ubah) dengan ID internal tetap stabil sehingga pilihan klien tidak hilang. Foto yang dihapus dari Drive otomatis keluar dari pilihan. Klien membaca dari database, bukan dari Drive.

**Thumbnail dan gambar besar:**

- URL gambar berbentuk /pf-img/{gallery}/{photo}/{ukuran}?v={drive\_version} lewat rewrite rule. Ukuran hanya boleh 400 atau 1600 (allowlist).
- Otorisasi per request: pengguna login, galeri miliknya (atau editor), foto milik galeri itu. Gambar adalah GET tanpa efek samping, jadi autentikasi cookie biasa cukup (tidak butuh nonce).
- Pertama kali diminta: ambil dari Drive dengan token service account (thumbnailLink, atau fallback unduh alt=media lalu resize dengan WP\_Image\_Editor), konversi WebP kualitas 75 sampai 80, simpan di cache disk wp-content/uploads/pilihfoto-cache/ dengan nama hash HMAC. Folder ini dilindungi (.htaccess deny, index.php kosong, atau di luar webroot).
- Penyajian: streaming (readfile atau X-Sendfile/X-Accel-Redirect bila ada), header ETag, Last-Modified, **Cache-Control: private, max-age=31536000, immutable** (aman karena URL memuat versi), dukung 304.
- Lock per file (flock) agar tidak ada thundering herd. Timeout 10 detik pada pemanggilan Drive. Batas ukuran file asli yang diproses (misalnya 40 MB). Pre-warm ukuran 400 setelah sinkron lewat batch kecil di cron.
- Verifikasi di spike awal (1 hari): perilaku thumbnailLink dengan token service account bisa berbeda antar-akun. Rencana fallback di atas disiapkan.

**Unduh hasil edit:** lewat endpoint terotorisasi yang streaming dari Drive dengan Content-Disposition yang sudah dibersihkan dari CR/LF dan karakter berbahaya. Tombol Buka Folder Google Drive dihapus. Unduh semua sebagai ZIP bersifat opsional (streaming, fase lanjutan).

## 9. Sanitasi input dan escape output (nol injeksi)

**Input (server, wajib):**

- **Link atau ID folder:** server mengekstrak sendiri. Host harus persis drive.google.com, pola ID ^\[A-Za-z0-9\_-\]{10,100}$, diverifikasi ke Drive sebagai folder. URL dari pengguna tidak pernah di-fetch. Panggilan Drive dibangun dengan add\_query\_arg dan rawurlencode, parameter q dirakit hanya dari ID yang sudah lolos pola.
- **ID foto:** bilangan bulat internal, unik, jumlah dibatasi (max\_select, atau batas keras 2000). Divalidasi dengan satu query: gallery\_id sama, kind source, id dalam daftar, memakai $wpdb->prepare.
- **Catatan klien:** sanitize\_textarea\_field, maksimal 500 karakter, disimpan sebagai teks mentah.
- **Nama tampilan, judul:** sanitize\_text\_field, maksimal 100 karakter.
- **max\_select:** integer 0 sampai 5000. Nilai 0 berarti tanpa batas hanya jika editor memang memilihnya, tetap dengan batas keras 2000.
- **Nomor WA:** hanya digit, 8 sampai 15 digit, divalidasi di server. URL dirakit di server: https://wa.me/ + digit + ?text= + rawurlencode(teks).
- **Pengguna klien:** sanitize\_user (huruf kecil, angka, underscore, tanda hubung, 3 sampai 32 karakter), is\_email, cek username\_exists. Password dibuat acak oleh WordPress, dikirim lewat tautan reset, tidak ada password default.

**Output:**

- PHP: esc\_html, esc\_attr, esc\_url, wp\_json\_encode. Tidak ada echo data mentah.
- JavaScript: **dilarang** innerHTML, outerHTML, insertAdjacentHTML, dan inline handler (onclick=...). Pakai createElement, textContent, setAttribute, dan event delegation. Aturan ESLint (no-unsanitized, no-inline-handlers) menjaga hal ini.
- **CSV dibuat di server** dengan fputcsv. Sel yang diawali = + - @ tab atau CR diberi awalan tanda kutip tunggal. Nama file unduhan dibersihkan.
- Pesan error ke pengguna bersifat generik. Detail hanya ke log server.

**Daftar penghapusan injeksi dari kode lama:**

- Parameter ?client= di editor diganti ID bulat yang divalidasi dan dicek capability.
- Tidak ada ID Drive, folderId, thumbnailLink, atau URL Drive di respons ke browser.
- Tidak ada konkatenasi query Drive dari input mentah.
- Tidak ada redirect dari parameter tanpa wp\_validate\_redirect.
- Tidak ada ID atau nama file yang masuk ke atribut JS atau HTML mentah.

## 10. Hardening keamanan (berlapis)

1. **Autentikasi:** pengguna WordPress dengan hash bawaan, reset password bawaan, throttle login per IP dan username (transient), 2FA wajib untuk editor (plugin Two-Factor resmi). Masa sesi: klien pendek, editor lebih pendek, lewat filter auth\_cookie\_expiration. FORCE\_SSL\_ADMIN dan cookie Secure.
2. **CSRF:** nonce wp\_rest di header X-WP-Nonce untuk semua request tulis. Logout hanya lewat tautan bernonce (wp\_logout\_url).
3. **Otorisasi:** setiap endpoint dan setiap query memeriksa capability dan kepemilikan. Uji IDOR otomatis (bagian 15). Respons 404 (bukan 403) untuk objek milik orang lain agar keberadaan tidak bocor.
4. **Header di halaman plugin:** Content-Security-Policy berbasis nonce (script-src self + nonce, img-src self data, connect-src self, frame-ancestors none, base-uri none, object-src none), X-Content-Type-Options nosniff, Referrer-Policy same-origin, Permissions-Policy minimal, Cache-Control private no-store pada API. Rilis awal dengan CSP Report-Only, lalu ditegakkan. HSTS diset di level server.
5. **Rate limiting** pada endpoint tulis dan media (per pengguna, transient atau object cache).
6. **Rahasia:** kredensial service account di luar webroot dan di wp-config.php. Tidak ada rahasia di database atau repo. Rotasi terdokumentasi.
7. **Berkas:** tidak ada fitur unggah dari pengguna sama sekali (mengurangi permukaan serangan). Cache gambar bernama hash dan terlindungi.
8. **Pencegahan kebocoran lewat cache:** halaman portal dan endpoint gambar dikecualikan dari page cache dan CDN (DONOTCACHEPAGE, Cache-Control private). Ini wajib dicek bila memakai WP Rocket, LiteSpeed, atau Cloudflare.
9. **Audit log** untuk semua aksi penting dan login gagal, dengan retensi terbatas dan IP di-hash.
10. **Privasi data:** nama dan nomor telepon adalah data pribadi (relevan dengan UU PDP No. 27 Tahun 2022). Sediakan kebijakan retensi (hapus otomatis galeri N hari setelah selesai) dan fitur hapus data klien.
11. **Uninstall bersih:** opsi menghapus tabel, cache, dan opsi saat plugin dihapus.
12. **Rantai pasok:** tanpa dependensi Composer atau npm di runtime. Font di-host sendiri (WOFF2 subset), tidak ada CDN pihak ketiga.

## 11. Rencana performa dan target

**Langkah:**

- Daftar foto dari database (satu query berindeks), bukan Drive.
- Thumbnail WebP di cache disk dengan header immutable. Peramban hampir tidak pernah mengunduh ulang.
- Hanya dua ukuran gambar (400 dan 1600). Gambar 1600 dimuat saat lightbox dibuka, plus prefetch foto berikutnya.
- Autosave delta (add dan remove) dengan debounce 500 ms, batalkan request yang tertunda, optimistic locking untuk mencegah tabrakan antar-tab.
- Polling editor diganti 30 sampai 60 detik, hanya saat tab terlihat (visibilitychange), dan memakai ETag atau 304. Alternatif: WordPress Heartbeat.
- Aset hanya dimuat di halaman portal, versi dari filemtime, diminifikasi, atribut defer, tanpa font eksternal (WOFF2 sendiri, font-display swap).
- width dan height atau aspect-ratio pada kartu foto (mencegah CLS), loading lazy, content-visibility auto untuk grid besar.
- Indeks database dan UNIQUE yang sesuai, opsi autoload no, object cache bila tersedia.
- Sinkron Drive dan pre-warm dalam batch kecil di cron, tidak pernah di request pengguna.

**Target terukur (diuji di staging):**

- API galeri 500 foto: p95 di bawah 300 ms di server.
- Thumbnail dari cache: TTFB di bawah 50 ms.
- LCP di bawah 2,5 detik pada 4G, CLS di bawah 0,1, INP di bawah 200 ms.
- JS portal klien di bawah 60 KB gzip, editor di bawah 90 KB gzip.
- Nol request ke domain pihak ketiga dari portal.

## 12. Port frontend

- Pertahankan UI dan alur sekarang (grid, lightbox, bar bawah, dialog kirim, galeri hasil, dashboard dua panel).
- Pecah menjadi modul: common.js (helper DOM aman, fetch dengan nonce), client.js, editor.js. Hapus shared.js lama (fungsi esc tidak lagi dibutuhkan karena memakai textContent).
- Semua kelas CSS diberi awalan pf- dan dicakup di bawah .pf-app. Variabel CSS dicakup ke wrapper itu, bukan :root.
- Portal klien dirender lewat template halaman milik plugin (minim, tanpa CSS dan JS tema) agar cepat dan bebas bentrok. Opsi: shortcode \[pilih\_foto\] bila ingin memakai tema.
- Hapus login.html dan tombol akun demo. Login memakai halaman login WordPress (atau form \[pf\_login\] yang memakai wp\_signon dengan throttle).
- Tombol WhatsApp: URL dirakit oleh server dan dikirim dalam respons. Tautan eksternal memakai rel noopener noreferrer.
- Aksesibilitas dipertahankan (aria-live, fokus dialog, navigasi keyboard) dan diuji ulang.

## 13. Struktur plugin

```
pilih-foto/
├── pilih-foto.php              (bootstrap, konstanta, autoload sederhana)
├── uninstall.php
├── includes/
│   ├── class-activator.php     (dbDelta, role, capability, rewrite)
│   ├── class-roles.php
│   ├── class-db.php            (query terpreparasi, repository)
│   ├── class-drive-client.php  (service account, JWT, panggilan Drive)
│   ├── class-sync.php
│   ├── class-media.php         (endpoint gambar, cache disk)
│   ├── class-rest-client.php
│   ├── class-rest-editor.php
│   ├── class-validator.php     (aturan sanitasi terpusat)
│   ├── class-security.php      (header, CSP, rate limit, redirect admin)
│   ├── class-audit.php
│   └── class-cli.php           (wp pilihfoto import, purge)
├── admin/                      (halaman editor, menu wp-admin)
├── public/                     (template portal klien)
├── assets/{css,js,fonts}/
└── tests/                      (PHPUnit, Playwright)
```

## 14. Migrasi data

1. Buat staging WordPress. Pasang plugin dan buat service account.
2. Perintah WP-CLI wp pilihfoto import ./data (sekali jalan, dry-run dulu): membuat pengguna klien dengan password acak dan mengirim tautan reset, **tidak** memigrasikan password plaintext. Membuat galeri, menyinkron foto dari Drive, lalu memetakan ID Drive lama di selections.json ke ID foto internal. Menyalin status, catatan, dan stempel waktu.
3. Bagikan folder Drive ke service account, lalu **cabut akses publik** pada folder.
4. Validasi hitungan (galeri, pilihan, status) antara data lama dan baru, simpan laporan.
5. Cutover: freeze app lama, migrasi final, ganti DNS atau tautan, pantau 48 jam. Arsipkan data lama terenkripsi, lalu hapus dari server.

## 15. Pengujian dan verifikasi

- **Statis:** PHPCS dengan WordPress Coding Standards (sniff Security: nonce, escaping, SQL preparation), PHPStan level 6 atau lebih, ESLint dengan aturan no-unsanitized dan larangan inline handler.
- **Unit dan integrasi (PHPUnit):** validator tiap field, state machine, batas pilihan, sinkron diff, penyajian media.
- **Matriks otorisasi (otomatis):** setiap endpoint dicoba oleh anonim, klien A, klien B, editor, administrator. Ekspektasi 401, 404 atau 200 tertulis dan harus lolos. Termasuk uji IDOR (ID galeri dan foto milik klien lain).
- **Uji injeksi:** payload XSS, SQL, quote, CRLF, nama file jahat (lewat folder Drive uji), ID Drive palsu, tautan Drive jahat, nomor WA berisi simbol, CSV dengan sel diawali =.
- **E2E (Playwright):** alur klien lengkap (login, pilih, batas, kirim, terkunci), alur editor (buat klien, tautkan folder, sinkron, lihat, buka kunci), halaman hasil.
- **Keamanan dinamis:** OWASP ZAP baseline di staging, pengecekan header (securityheaders), pengecekan cache (halaman privat tidak tercache CDN).
- **Performa:** Lighthouse CI dan uji beban k6 (misalnya 50 klien membuka galeri 500 foto) terhadap target di bagian 11.
- **Tinjauan manual** satu kali sebelum go-live (pen-test ringan oleh pihak independen bila anggaran ada).

## 16. Roadmap fase dan kriteria selesai

Estimasi kasar untuk satu developer berpengalaman WordPress, perlu disesuaikan setelah spike.

- **Fase 0 (hari ini): darurat.** Cabut API key, ganti password, hentikan distribusi zip berisi .env dan data/. *Selesai bila:* key lama tidak berlaku lagi.
- **Fase 1 (1 sampai 2 hari): spike.** Service account, uji thumbnailLink dengan token, ukuran file nyata, ketersediaan Imagick, kompatibilitas cache plugin hosting. *Selesai bila:* jalur thumbnail (utama atau fallback) terbukti.
- **Fase 2 (2 sampai 3 hari): fondasi.** Plugin, tabel, role dan capability, audit log, aturan validator, CI statis. *Selesai bila:* aktivasi dan uninstall bersih, tes validator lolos.
- **Fase 3 (4 sampai 5 hari): backend.** REST klien dan editor, sinkron Drive, state machine, media pipeline, CSV, unduh. *Selesai bila:* matriks otorisasi 100% lolos.
- **Fase 4 (3 sampai 4 hari): frontend.** Port UI ke modul aman, template portal, wp-admin editor. *Selesai bila:* E2E lolos, nol innerHTML dengan data.
- **Fase 5 (2 sampai 3 hari): hardening.** CSP (Report-Only lalu enforce), throttle, header, 2FA editor, uji injeksi, ZAP. *Selesai bila:* tidak ada temuan Tinggi atau Kritis.
- **Fase 6 (2 sampai 3 hari): performa.** Cache disk, pre-warm, polling hemat, Lighthouse dan k6. *Selesai bila:* semua target bagian 11 tercapai.
- **Fase 7 (2 hari): migrasi dan go-live.** Import, validasi, cutover, pemantauan. *Selesai bila:* data cocok dan 48 jam tanpa insiden.
- **Fase 8 (berkelanjutan):** pembaruan WordPress dan PHP, tinjauan log audit, rotasi kredensial berkala, backup dan uji pemulihan.
