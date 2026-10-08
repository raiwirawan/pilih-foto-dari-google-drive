# PLAN: Pilih Foto → Studio (standalone, tanpa wp-admin)

Oct 8, 2026 · @Reyfs

## 1. Kondisi sekarang

Plugin saat ini memaksa klien dan editor masuk lewat WordPress, dan tampilannya dibuat untuk halaman standalone tetapi ditempel ke wp-admin. Temuan ini berasal dari membaca ±2.300 baris kode; plugin belum dijalankan di WordPress.

- **Tampilan editor jelek.** `app.css` memakai style global (`body {}`, `* {}`) sehingga bentrok dengan style wp-admin.
- **Fitur editor belum jadi.** Tombol "Detail" hanya menampilkan toast "dalam pengembangan".
- **Halaman klien kemungkinan error.** `client.js` memanggil elemen `progressWrap` dan `progressFill` yang tidak ada di template, sehingga `pfUpdateSel()` melempar error.
- **Klien dan editor adalah user WordPress.** Mereka login lewat `wp-login.php`, mencemari tabel user website perusahaan, dan terkena redirect wp-admin.
- **Celah keamanan:**
  - XSS: nama file Drive dan judul galeri dimasukkan lewat `innerHTML` tanpa escape.
  - Salt cache di-hardcode (`'pf_salt_123'`) dan API key dikirim lewat query string URL.
  - Server tidak mengecek apakah ID foto yang dipilih milik galeri itu, dan `max_select` tidak dipaksa saat menyimpan pilihan.
  - Tidak ada rate limit login; tabel audit ada tetapi tidak pernah diisi.
  - Response API ke klien membocorkan `drive_file_id` dan `result_folder_id`.
- **Performa lambat.** Setiap foto yang belum ter-cache memicu 2 request ke Drive plus 1 download saat itu juga. Hitungan jumlah pilihan di dashboard editor juga N+1 query.

## 2. Keputusan arsitektur

Akun Studio dibuat terpisah dari WordPress: tetap plugin, tetapi punya tabel akun (`pf_accounts`) dan session sendiri. Tidak ada lagi `wp_create_user`, dan role `pf_client` serta `pf_editor` dihapus.

| Hal | Rancangan |
| --- | --- |
| Akun | `username` unik (case-insensitive, `[a-z0-9._-]`, 3–30 karakter, ada daftar nama terlarang), `password_hash` (bcrypt/argon2id), `role` editor atau klien, `display_name`, `is_active`, `last_login` |
| Login | Satu halaman `/studio`: cek username + password, lalu arahkan sesuai role (editor ke dashboard, klien ke galerinya) |
| Session | Token acak 256-bit, hanya hash-nya disimpan di DB. Cookie `HttpOnly`, `Secure`, `SameSite=Strict`, dengan idle timeout dan masa berlaku. Semua session dicabut saat password diganti |
| CSRF | Header `X-PF-CSRF` wajib di setiap request yang mengubah data, di samping cookie `SameSite=Strict` |
| Akun editor pertama | Satu layar kecil di wp-admin, hanya untuk WP Administrator, untuk membuat/reset akun editor dan mengecek status API key. Alternatif: `wp pilihfoto editor create`. Editor tidak pernah menyentuh wp-admin |
| Konfigurasi | `PF_GOOGLE_API_KEY` di wp-config tetap dipakai tanpa perubahan. Tambahan opsional: `PF_BASE_SLUG` (default `studio`) |

**Route baru:**

| Route | Fungsi |
| --- | --- |
| `/studio` | Login |
| `/studio/editor` | Dashboard editor |
| `/studio/galeri` | Pemilihan foto oleh klien |
| `/studio/galeri/hasil` | Hasil edit untuk klien |
| `/studio/api/…` | Endpoint data |
| `/studio/img/{galeri}/{foto}/{ukuran}` | Gambar (proxy) |
| `/foto/*` | Redirect 301 ke route `/studio` yang sesuai |

Sebelum implementasi, pastikan tidak ada halaman WordPress dengan slug "studio"; kalau ada, ganti lewat `PF_BASE_SLUG`. Halaman Studio dirender sendiri oleh plugin tanpa memuat tema, memakai hook awal (`parse_request`) supaya cepat.

## 3. UI/UX baru

Tampilan editor dipindah dari wp-admin ke halaman Studio sendiri, dengan CSS ber-scope dan tanpa style global.

**Dashboard editor** (`/studio/editor`):

- Daftar klien berupa tabel/kartu dengan pencarian dan filter status (Belum mulai, Draft, Terkirim, Dibuka kembali).
- Wizard "Klien Baru" 3 langkah:
  1. Akun: username (dicek unik langsung), nama, password dengan tombol "Generate".
  2. Folder Drive: link mentah dan link hasil, divalidasi langsung dan menampilkan nama folder serta jumlah foto.
  3. Batas pilihan dan ringkasan. Setelah disimpan muncul kartu "Kirim ke klien" berisi template pesan WhatsApp (link, username, password) dengan tombol Salin.
- Halaman detail klien yang sebenarnya: grid foto terpilih, catatan klien, tombol **Salin daftar nama file** dan **Unduh CSV** (untuk mencari foto di Lightroom), Sinkronkan ulang, Buka Kunci, Reset password, Nonaktifkan, Hapus.
- Pengaturan: nomor WhatsApp editor.

**Halaman klien:**

- Grid foto dengan progress bar dan batas pilihan.
- Lightbox dengan navigasi keyboard dan swipe.
- Dialog kirim, serta banner status terkunci / dibuka kembali.
- Halaman hasil edit dengan tombol unduh.
- Ganti password sendiri (opsional).

**Teknis UI:** font Source Sans 3 di-host sendiri (folder `assets/fonts` sekarang kosong), tanpa `onclick` inline, semua teks dinamis di-escape, dark mode dan mobile first dipertahankan.

## 4. Keamanan

Sembilan perubahan menutup semua celah di bagian 1 dan menambah lapisan yang belum ada.

1. **Login:** rate limit per username dan per IP dengan lockout bertahap, pesan error generik, dan waktu respons konstan agar username tidak bisa ditebak.
2. **Otorisasi di setiap endpoint:** klien hanya mengakses galerinya sendiri, editor melihat semua. Setiap `photo_id` yang dikirim divalidasi milik galeri tersebut.
3. **Batas pilihan dan kunci galeri dipaksa di server.** Update status atomik (`UPDATE … WHERE status IN (…)`), dan submit bersifat idempotent.
4. **Data minimal ke klien:** `drive_file_id`, `source_folder_id`, dan `result_folder_id` tidak dikirim. Hasil edit baru terlihat setelah editor menekan "Rilis".
5. **API key:** dikirim lewat header `X-Goog-Api-Key`, bukan query string. Disarankan membatasinya di Google Cloud Console (hanya Drive API, hanya IP server).
6. **Cache gambar:** nama file memakai HMAC dari salt WordPress (`wp_salt`), bukan string hardcode. Host thumbnail divalidasi hanya `*.googleusercontent.com`, Content-Type dan ukuran dicek. Lokasi cache bisa dipindah ke luar webroot lewat `PF_CACHE_DIR`.
7. **Header:** CSP yang benar-benar diberlakukan di halaman Studio, `X-Frame-Options: DENY`, `noindex`, dan `Cache-Control: no-store` untuk halaman dan API.
8. **Audit log benar-benar ditulis:** login, gagal login, buat/ubah/hapus akun, submit, buka kunci, sync. IP disimpan sebagai hash.
9. **Migrasi akun lama:** user WP berperan `pf_client` atau `pf_editor` dipindah ke `pf_accounts` dengan hash lama yang tetap valid, lalu di-rehash saat login pertama.

## 5. Performa

Perubahan terbesar: thumbnail disiapkan sebelum klien membuka galeri, bukan saat gambar diminta.

- **Thumbnail:** `thumbnailLink` disimpan saat sync (sekarang sudah diambil lalu dibuang), sehingga tidak ada request metadata per gambar. Link yang kedaluwarsa di-refresh per foto saat gagal. Setelah sync, thumbnail 400px dihangatkan di background lewat WP-Cron dalam batch paralel.
- **Penyajian gambar:** selalu ada ETag, Last-Modified, dan 304; pakai `X-Accel-Redirect` / `X-Sendfile` / LiteSpeed bila server mendukung, `readfile` sebagai cadangan. Simpan `width`/`height` dari Drive agar grid tidak melompat; tambah `loading=lazy` dan `decoding=async`.
- **Database:** hitungan pilihan jadi satu query `GROUP BY` (menghilangkan N+1). Index ditambah di `pf_photos(gallery_id, kind, position)` dan `pf_selections(photo_id)`. Insert massal saat sync; sync folder besar jalan di background dengan indikator progres, plus sync malam otomatis untuk galeri yang belum dikirim.
- **Penyimpanan pilihan:** tetap debounce, ditambah retry dan `fetch keepalive` supaya pilihan tidak hilang saat tab ditutup.
- **Aset:** versi berdasarkan `filemtime`, tidak memuat aset wp-admin, tidak ada request font eksternal.

## 6. Urutan pengerjaan

Pekerjaan dibagi delapan tahap berurutan; tahap 1–3 adalah fondasi yang harus selesai sebelum UI dibangun.

1. **Fondasi:** skema DB baru + migrasi, kelas auth/session/CSRF, router `/studio` + redirect `/foto`, layar bootstrap di wp-admin, WP-CLI.
2. **API:** namespace `studio/v1` dengan otorisasi ketat, audit log, dan validasi server-side.
3. **Pipeline Drive dan gambar:** klien Drive via header, simpan thumbnail link, hangatkan cache, proxy gambar baru, sync background.
4. **UI editor:** login, dashboard, wizard, detail klien, pengaturan.
5. **UI klien:** galeri, lightbox, kirim, hasil, ganti password.
6. **Hardening dan pembersihan:** CSP, header, rate limit, uninstall (termasuk menghapus cache), perbaikan semua bug di bagian 1.
7. **Pengujian:** unit test untuk auth, rate limit, otorisasi, dan validasi pilihan, ditambah daftar uji manual di staging.
8. **Rilis dan rollback:** backup DB dan folder plugin, jalankan migrasi, `flush_rewrite_rules`, uji ujung ke ujung. Tabel lama tidak dihapus sampai semuanya dikonfirmasi berjalan.

## 7. Hal yang perlu diketahui dan default

- **Plugin cache dan CDN.** Jika website memakai WP Rocket, LiteSpeed Cache, atau Cloudflare, `/studio/*` harus dikecualikan dari page cache; kalau tidak, halaman login atau data klien bisa ter-cache.
- **Drive.** Pendekatan API key mengharuskan folder berstatus "Anyone with the link". Itu tidak berubah. Folder yang tetap privat butuh perubahan lain (service account) dan tidak masuk plan ini.
- **Pengujian.** Belum ada instance WordPress untuk menjalankan plugin; uji ujung ke ujung perlu di staging, atau lewat wp-env yang bisa disiapkan.

**Default yang dipakai (bisa diubah):**

1. Satu klien bisa punya banyak galeri, seperti struktur sekarang.
2. Editor boleh lebih dari satu orang, semuanya setara.
3. Slug `studio` bisa diganti lewat konstanta.
4. Hasil edit baru terlihat oleh klien setelah editor menekan tombol "Rilis hasil".
