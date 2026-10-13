# PLAN PERBAIKAN: Studio Editor & Studio Client (Tema `bcsolutions`)

Lanjutan dari `template-plan.md`. Fokus: (1) nama file terpilih tampil sebagai **File ID** bukan **nama file Drive**, (2) performance, (3) bug sisa dari hasil koreksi agent. **Arsitektur keamanan (token klien, kunci editor, URL thumbnail bertanda tangan) tidak diubah.**

> **Catatan metode.** Plan ini hasil **analisis statis** terhadap `bcsolutions.zip` (`functions.php` blok `STUDIO START…END` ±1170 baris, `studio-core.js`, `studio-client.js`, `studio-editor.js`, 2 template). Sandbox saya tidak punya PHP/WordPress dan tidak bisa menghubungi Google Drive, jadi **belum ada yang dijalankan**. Penyebab utama bug #1 di bawah adalah kesimpulan dari alur kode; Fase A sengaja dibuat untuk memastikannya di server live sebelum mengubah apa pun.

---

## 1. Ringkasan temuan

| # | Temuan | Dampak | Prioritas |
|---|--------|--------|-----------|
| B1 | Nama file di-resolve **saat dibaca** dari listing Drive; bila listing gagal, nama jatuh ke File ID | Nama = ID di editor (dan bisa di klien) | **P0** |
| B2 | Listing Drive yang gagal dikembalikan sebagai `[]` (bukan error) | Klien melihat "Belum ada foto", validasi pilihan **terlewati** | **P0** |
| B3 | Resolve nama per-ID memakai download gambar penuh dari `lh3` untuk membaca header (sekuensial, 8 dtk/ID) | Editor lambat; nama hampir pasti tidak didapat | **P0** |
| P1 | URL thumbnail memakai `exp = time()+12h` yang berubah **setiap request** | Cache browser tidak pernah kena; semua gambar diunduh ulang tiap reload | **P0** |
| P2 | Setiap thumbnail = 1 request PHP yang memuat **seluruh WordPress + functions.php 2100 baris** | 200 foto = 200 bootstrap WP; PHP worker habis | **P0** |
| P3 | Rantai fallback proxy sampai 4 fetch × 8–10 dtk, tanpa negative-cache/lock | Worker tersangkut puluhan detik bila Google lambat | P1 |
| P4 | Tiap autosave (juga tiap ketikan catatan) memanggil listing penuh untuk validasi | Query + unserialize 1000 item per simpan | P1 |
| P5 | Metadata disimpan di 3 transient besar per folder (`drive_v3`, `fmeta`, `tmap`) | Beban `wp_options`, tidak persisten bila object cache dibersihkan | P1 |
| P6 | `studio_build_page_url()` memanggil `get_pages()` tanpa memo, termasuk di dalam loop galeri | Query berulang | P2 |
| P7 | `studio_get_galleries` mem-decode `selected_ids` tiap galeri hanya untuk menghitung | Boros | P2 |
| P8 | `wp_next_scheduled()` dipanggil di setiap load file; cron `glob()` tanpa batas ukuran | Overhead kecil, cache bisa membengkak | P2 |
| P9 | `studio_remote_get()` mengulang dengan `sslverify=false` pada **semua** error (termasuk timeout) | Latensi 2× saat error; bypass TLS di produksi | P1 |
| P10 | Frontend: `renderSkeleton` membangun ulang seluruh DOM tiap "Pilih semua/Hapus"; handler per kartu; tanpa `content-visibility` | Lag di galeri 300+ foto | P1 |

---

## 2. Akar masalah #1: nama file tampil sebagai File ID

### 2.1 Alur sekarang

1. Klien memilih foto → `PUT /client/selection` menyimpan **hanya array ID** di kolom `selected_ids`. **Nama tidak ikut disimpan**, padahal server sudah memilikinya saat memvalidasi.
2. Editor membuka galeri → `studio_api_editor_get_gallery` memanggil `studio_get_drive_photos()` lalu mencocokkan ID untuk mengambil `name`.
3. Jika listing gagal/kosong, semua ID masuk cabang "fallback": `studio_resolve_single_file_meta()` → (a) Drive API per ID, (b) unduh gambar `lh3` untuk mencari `Content-Disposition`, (c) akhirnya `'name' => $file_id`.
4. Karena (a) memakai API key yang sama dengan listing, **bila listing gagal, (a) hampir pasti gagal juga**. Header `Content-Disposition` dari `lh3.googleusercontent.com/d/…` pada umumnya tidak memuat nama file. Hasil akhir: nama = ID.

### 2.2 Kandidat penyebab listing gagal (dicek di Fase A)

- `STUDIO_GOOGLE_API_KEY` kosong/salah, atau **Drive API belum diaktifkan** untuk project key tersebut.
- Pembatasan key: *HTTP referrer* (request server tidak punya Referer; kode hanya mencoba Referer saat 403) atau *IP restriction* yang tidak memuat IP hosting.
- Folder bukan "Anyone with the link – Viewer" (error `notFound`).
- Kuota/rate limit atau timeout (listing 15 dtk, tanpa retry yang benar).
- File berekstensi RAW/format khusus yang mimeType-nya bukan `image/*` sehingga tidak masuk query `mimeType contains 'image/'`.
- Hasil listing kosong tidak pernah di-cache (`!empty($cached)`), jadi folder kosong/gagal memanggil API di **setiap** request.

### 2.3 Perbaikan struktural (Fase B)

**Prinsip: nama file dicatat sekali saat dipilih dan disimpan permanen di database, bukan dicari ulang tiap dibaca.**

1. **Snapshot nama saat simpan.** Di `studio_api_client_update_selection` dan `studio_api_client_submit`, server sudah punya peta `id → name` dari listing untuk validasi. Simpan juga ke kolom baru `selected_meta` (JSON `{ "<id>": "<nama>" }`) dan `selected_count`.
2. **Editor membaca dari snapshot.** `studio_api_editor_get_gallery` memakai `selected_meta`; **tanpa panggilan Drive** untuk kasus normal. Urutan mengikuti urutan pilihan klien (atau urut nama, dipilih sekali di UI).
3. **Cache metadata persisten** di tabel `studio_files` (menggantikan transient `fmeta`/`tmap`/`drive_v3`), lihat bagian 4.
4. **Resolve nama paralel + sekali saja** untuk ID yang namanya belum ada (galeri lama): `files.get?fields=id,name` memakai `Requests::request_multiple` (6 paralel), hasilnya **ditulis balik** ke `selected_meta` agar tidak diulang. Hapus Lapis 2 (unduh gambar demi header).
5. **Hentikan fallback diam-diam ke ID.** Bila nama benar-benar tidak tersedia, API mengembalikan `name: null` + `nameMissing: true`; UI menampilkan "(nama belum tersedia)" dan tombol **Muat ulang nama**, bukan File ID. ID tetap ada di CSV ekspor.
6. **Backfill galeri yang sudah berjalan:** endpoint admin `POST /editor/galleries/{id}/resync` + migrasi sekali jalan (Fase B6).

Contoh inti (disederhanakan):

```php
// saat simpan pilihan (client)
$index = studio_get_file_index($g->source_folder_id);      // [id => ['name'=>..]]  dari tabel studio_files
if ($index === null) {                                       // sync gagal & tidak ada cache sama sekali
    return new WP_Error('drive_unavailable', 'Daftar foto belum bisa dimuat. Coba lagi sebentar.', ['status' => 503]);
}
$old  = json_decode($g->selected_meta ?: '{}', true) ?: [];
$ids  = array_values(array_filter($ids, fn($id) => isset($index[$id])));   // validasi SELALU berjalan
$meta = [];
foreach ($ids as $id) { $meta[$id] = $index[$id]['name'] ?? ($old[$id] ?? ''); }

$wpdb->update($table, [
    'selected_ids'   => wp_json_encode($ids),
    'selected_meta'  => wp_json_encode($meta, JSON_UNESCAPED_UNICODE),
    'selected_count' => count($ids),
    // ...note, status
], ['id' => $g->id]);
```

---

## 3. Fase pengerjaan

### Fase A — Diagnostik (30–45 menit, kerjakan dulu)

**Tujuan:** memastikan penyebab listing/nama gagal di server live sebelum menulis ulang.

- [ ] Tambah endpoint admin sementara `GET /editor/diagnostics` (hanya `manage_options`, cookie + nonce) yang mengembalikan:
  - `keyDefined` (bool; **jangan** tampilkan isi key).
  - Hasil 1 panggilan `files.list` ke folder sumber galeri uji: **HTTP status, `error.status`, `error.reason`, `error.message`, durasi (ms), jumlah file**.
  - Hasil 1 panggilan `files.get` untuk 1 ID terpilih (nama, mimeType, `capabilities`).
  - Versi PHP, `WP_DEBUG`, ada/tidaknya object cache (`wp_using_ext_object_cache()`), `curl` versi, ukuran folder `uploads/studio-cache`.
- [ ] Tambah log ringan (hanya saat `WP_DEBUG_LOG`): setiap kegagalan `studio_get_drive_photos` menulis `reason` ke `debug.log`. Saat ini kegagalan **ditelan tanpa jejak**.
- [ ] Interpretasi hasil:
  | Hasil | Tindakan |
  |---|---|
  | `API_KEY_HTTP_REFERRER_BLOCKED` / `API_KEY_IP_ADDRESS_BLOCKED` | Ubah pembatasan key (None atau IP server), Application restriction jangan "HTTP referrers" |
  | `accessNotConfigured` | Aktifkan **Google Drive API** di project key |
  | `notFound` / `forbidden` | Folder belum "Anyone with the link" |
  | `rateLimitExceeded` | Lanjut Fase C (cache persisten) |
  | HTTP 200 tetapi file < jumlah asli | Cek mimeType file yang hilang (RAW/HEIC), lihat B8 |

### Fase B — Nama file benar & data tahan gagal (P0)

- [ ] **B1. Skema DB v1.1** (lihat bagian 4): kolom `selected_meta`, `selected_count`; tabel `studio_files`. Naikkan `studio_db_version` ke `1.1`, `dbDelta` tetap di `init`.
- [ ] **B2. `studio_sync_folder($folder_id)`**: ambil seluruh halaman Drive (`pageSize=1000`, `fields=nextPageToken,files(id,name,mimeType,thumbnailLink)`), tulis ke `studio_files` dengan `INSERT … ON DUPLICATE KEY UPDATE` per batch, hapus baris yang sudah tidak ada di folder, set `synced_at`. **Satu fungsi ini** menggantikan `studio_get_drive_photos`, `fmeta`, `tmap`, dan transient listing.
- [ ] **B3. `studio_get_file_index($folder_id)`**: baca dari `studio_files`. Jika tidak ada baris sama sekali → sync sinkron sekali; jika sync gagal → `null`. Jika data ada tapi usang → pakai data lama dan jadwalkan refresh (lihat C3).
- [ ] **B4. Perbaiki B2 (error ditelan):** `/client/photos` dan `/client/result` mengembalikan `503 drive_unavailable` bila tak ada data sama sekali, **bukan** `[]`. Klien menampilkan pesan "Daftar foto belum bisa dimuat" + tombol coba lagi, bukan "Belum ada foto".
- [ ] **B5. Snapshot nama** di `update_selection` dan `submit` (kode di 2.3). Validasi ID **tidak boleh** dilewati saat listing kosong.
- [ ] **B6. Backfill:** `studio_backfill_names()` dijalankan sekali (flag option `studio_names_backfilled`) dan tersedia lewat `POST /editor/galleries/{id}/resync`: untuk setiap galeri, isi `selected_meta` dari `studio_files`; ID yang tidak ada → `files.get` paralel; hasil ditulis balik.
- [ ] **B7. Editor `get_gallery`:** tanpa `refresh` → murni dari `selected_meta` (+ `studio_files` untuk thumbnail). Dengan `refresh=1` → `studio_sync_folder` ulang lalu perbarui nama.
- [ ] **B8. Cakupan tipe file:** ubah query jadi `(mimeType contains 'image/' or mimeType = 'application/octet-stream')` hanya bila diagnostik menunjukkan file RAW/HEIC terlewat. Jangan diaktifkan tanpa bukti.
- [ ] **B9. UI:** `esc(name)`; jika `nameMissing` tampilkan placeholder + tombol "Muat ulang nama". Ekspor CSV memuat `nama_file,id`; tombol "Salin nama file" **mengecualikan** nama yang hilang dan memberi peringatan.

### Fase C — Performance backend (P0/P1)

- [ ] **C1. URL thumbnail stabil (P1 di tabel).** `exp` dibulatkan ke jendela harian agar URL identik sepanjang hari, dan cache browser benar-benar bekerja:
  ```php
  function studio_sign_url($file_id, $w) {
      $w   = studio_snap_width($w);                                   // 200/400/800/1600
      $exp = (intdiv(time(), DAY_IN_SECONDS) + 3) * DAY_IN_SECONDS;  // sama seharian, berlaku 2–3 hari
      $sig = hash_hmac('sha256', "$file_id|$w|$exp", wp_salt('auth'));
      return add_query_arg(['studio_thumb'=>$file_id,'w'=>$w,'exp'=>$exp,'sig'=>$sig], home_url('/'));
  }
  ```
  Tanda tangan kini mencakup `w`; lebar yang tidak termasuk daftar putih ditolak (mencegah cache-flooding dan menaikkan hit-rate).
- [ ] **C2. Thumbnail sebagai file statis (P2 di tabel).** Hilangkan bootstrap WordPress untuk gambar yang sudah ada:
  - Nama file cache = `hash_hmac('sha256', "$id|$w", wp_salt('auth'))` (32 hex pertama), disimpan di `uploads/studio-cache/ab/…jpg` (shard 2 karakter).
  - Respons REST memberi **URL statis langsung** (`/wp-content/uploads/studio-cache/ab/xxxx.jpg`) bila file sudah ada; bila belum, memberi URL proxy bertanda tangan seperti sekarang. Server web (Nginx/LiteSpeed/Apache) menyajikan file statis tanpa PHP, dengan `Cache-Control: public, max-age=2592000, immutable`.
  - Keamanan tetap setara token: nama file tak dapat ditebak (HMAC dengan salt situs).
  - Tambahkan `index.html` kosong dan matikan directory listing di folder cache.
- [ ] **C2b (opsional, jika C2 belum cukup).** Pindahkan handler `?studio_thumb` ke `wp-content/mu-plugins/studio-thumb.php` yang jalan di `muplugins_loaded` dan `exit` **sebelum** tema/plugin dimuat. Hanya butuh konstanta salt dan `wp_remote_get`. Butuh upload 1 file di luar tema.
- [ ] **C3. Listing stale-while-revalidate.** `synced_at` + soft-TTL 5 menit: bila lewat TTL, layani data lama **seketika**, lalu jadwalkan `wp_schedule_single_event(time()+1, 'studio_resync_folder', [$folder_id])` dengan kunci anti-duplikat (`add_option` sebagai lock, kedaluwarsa 60 dtk). Pengguna tidak pernah menunggu Drive API kecuali sync pertama.
- [ ] **C4. Warm-up thumbnail.** Setelah galeri dibuat atau folder di-sync, jadwalkan `studio_prefetch_thumbs($folder_id)` yang mengunduh w=400 secara paralel (6 sekaligus) ke cache statis. Tombol editor "Siapkan thumbnail" memicu hal yang sama. Klien pertama langsung mendapat file statis.
- [ ] **C5. Proxy lebih aman terhadap lambat (P3).**
  - Timeout per tier 4 dtk (bukan 8–10), maksimum 2 tier sinkron: `thumbnailLink` dari `studio_files` → `lh3/d/{id}=w{w}`. Tier Drive API dan `drive.google.com/thumbnail` hanya dipakai bila dua pertama gagal **dan** belum ada negative-cache.
  - **Negative-cache** `studio_thumbfail_{hash}` 2 menit: gagal → langsung 302 ke CDN Google tanpa mengulang rantai.
  - **Lock** per file (`add_option`/transient 15 dtk) agar 20 request bersamaan untuk gambar yang sama tidak menyerbu Google.
  - ETag + `If-None-Match` → `304`.
- [ ] **C6. Validasi ringan (P4).** Ganti `studio_get_drive_photos` pada autosave dengan satu query:
  ```php
  $in = implode(',', array_fill(0, count($ids), '%s'));
  $ok = $wpdb->get_col($wpdb->prepare(
      "SELECT file_id FROM {$wpdb->prefix}studio_files WHERE folder_id=%s AND file_id IN ($in)",
      array_merge([$g->source_folder_id], $ids)));
  ```
- [ ] **C7. `studio_remote_get` (P9).** Hapus retry `sslverify=false` di produksi (hanya jika `wp_get_environment_type() === 'local'`), jangan mengulang pada timeout, pertahankan paksa IPv4 + `CONNECTTIMEOUT 5`. Pertahankan retry Referer pada 403.
- [ ] **C8. Overhead kecil (P6–P8).**
  - `studio_build_page_url()`: memo `static $cache[]` + transient 12 jam, dihapus di `save_post_page`/`deleted_post`.
  - `get_galleries`: pilih kolom `selected_count`, **tanpa** membaca `selected_ids`/`selected_meta`; hitung `link` dengan basis URL yang sekali dihitung.
  - Cron: `wp_next_scheduled` dipindah ke `init` dengan guard option; pembersihan cache berbasis **ukuran** (batas ±300 MB, hapus yang paling lama diakses) dengan `touch()` saat cache hit, bukan hanya umur 7 hari.
  - Tambah indeks `status`, `updated_at` pada tabel galeri.
- [ ] **C9. Header respons klien.** `/client/photos` dan `/client/result`: tetap tidak di-cache bersama, tetapi beri `ETag` (hash `synced_at` + jumlah) agar reload mendapat `304`. Endpoint lain tetap `no-store`.

### Fase D — Performance frontend (P1)

Klien (`studio-client.js`):
- [ ] **D1.** Satu **event delegation** pada `#st-grid` (klik kartu, klik zoom), hapus `c.onclick` per kartu.
- [ ] **D2.** `renderSkeleton` hanya dipanggil sekali. "Pilih semua/Hapus pilihan/toggle" cukup `classList.toggle('st-selected')` pada kartu yang berubah (simpan peta `id → element`). `updateOpacity` memakai satu kelas pada `#st-grid` (`st-limit-reached`) dengan CSS `.st-limit-reached .st-photo-card:not(.st-selected){opacity:.45}`, bukan loop semua anak.
- [ ] **D3.** Hapus `IntersectionObserver` ganda: cukup `<img loading="lazy" decoding="async" fetchpriority="low">` + observer hanya untuk 12 kartu pertama (`fetchpriority="high"` untuk baris pertama). Tambahkan `width/height` atau `aspect-ratio` pada kartu agar tidak ada layout shift.
- [ ] **D4.** CSS: `.st-photo-card { content-visibility: auto; contain-intrinsic-size: 220px; }` untuk galeri 300+ foto.
- [ ] **D5.** **Autosave pintar:** ID dikirim debounce 400 ms; catatan debounce 1500 ms; kirim hanya bila ada perubahan (flag `dirty`); jangan kirim ulang `note` saat hanya ID berubah. Flush dengan `fetch(..., {keepalive:true})` pada `visibilitychange`/`pagehide`. Tampilkan indikator "Tersimpan ✓ / Menyimpan… / Gagal, mencoba lagi" dengan retry eksponensial.
- [ ] **D6.** Lightbox: prefetch foto sebelum/sesudah (`new Image().src`) pada w=1600; bangun URL lebar lewat `p.src` yang sudah bertanda tangan (server mengirim `srcLg`) alih-alih `replace('w=400','w=1600')` yang rapuh.
- [ ] **D7.** Tangani error `503 drive_unavailable` dengan layar coba-lagi (B4); hapus rantai `onerror` tiga tingkat yang memuat gambar dari tiga host berbeda, cukup satu fallback ke `fallbackSrc` lalu tampilkan kartu error dengan tombol muat ulang per gambar.

Editor (`studio-editor.js`):
- [ ] **D8.** Daftar galeri memakai `selected_count` dari server; detail galeri tidak memanggil Drive (B7). Tombol "↻ Muat Ulang Foto" menjadi satu-satunya jalur yang memicu sync.
- [ ] **D9.** Thumbnail detail dengan `loading="lazy"` + `decoding="async"`, kartu bernama (nama atau placeholder, bukan ID).
- [ ] **D10.** Tombol "Siapkan thumbnail" (C4) dan indikator `synced_at` ("Terakhir sinkron 3 menit lalu").

Font: pasang `<link rel="preconnect">` untuk `fonts.googleapis.com` dan `fonts.gstatic.com` (atau host font lokal) agar render awal tidak menunggu.

### Fase E — Bug dan kerapian lain

- [ ] **E1.** Pesan "Memuat…" di `studio_render_shell` memakai gaya inline; pindahkan ke CSS studio.
- [ ] **E2.** `studio_api_editor_update_gallery`: ubah `source_folder_id` mereset pilihan, `selected_meta`, `selected_count`, dan menghapus baris `studio_files` folder lama bila tak dipakai galeri lain.
- [ ] **E3.** `delete_gallery`: hapus data galeri; hapus cache thumbnail folder hanya bila folder tidak dipakai galeri lain.
- [ ] **E4.** `reopen` hanya valid dari `submitted` (kembalikan 409 selain itu), konsisten dengan plan awal.
- [ ] **E5.** Pastikan `studio_resolve_single_file_meta` (versi lama) **dihapus**, juga transient lama (`studio_drive_v3_*`, `studio_fmeta_*`, `studio_tmap_*`) dibersihkan sekali lewat migrasi.
- [ ] **E6.** Bersihkan salinan lama di tema: `functions copy 1.php`, `villa-photography-main copy 1.js`, `old-*.php/js/css`, dan `__MACOSX` bila ikut ter-upload ke hosting (menambah ukuran backup dan risiko salah sunting).

---

## 4. Perubahan skema DB (v1.1)

```sql
-- tabel galeri: kolom baru
ALTER TABLE {prefix}studio_galleries
  ADD COLUMN selected_meta  longtext     NULL        AFTER selected_ids,   -- {"<fileId>":"<nama>"}
  ADD COLUMN selected_count int unsigned NOT NULL DEFAULT 0 AFTER selected_meta,
  ADD KEY status (status),
  ADD KEY updated_at (updated_at);

-- cache metadata Drive persisten (menggantikan 3 transient per folder)
CREATE TABLE {prefix}studio_files (
  file_id     varchar(100)  NOT NULL,
  folder_id   varchar(100)  NOT NULL,
  name        varchar(255)  NOT NULL,
  mime        varchar(100)  NOT NULL DEFAULT '',
  thumb_link  text          NULL,
  position    int unsigned  NOT NULL DEFAULT 0,      -- urutan di folder (orderBy name)
  synced_at   datetime      NOT NULL,
  PRIMARY KEY (folder_id, file_id),
  KEY file_id (file_id)
);

-- opsional: waktu sync per folder (soft-TTL)
-- cukup option 'studio_sync_{md5(folder)}' berisi timestamp, atau baris sentinel di tabel di atas
```

Semua lewat `dbDelta` (bukan `ALTER` manual) di `studio_install_db()`, dengan `studio_db_version = '1.1'`. Data lama tetap berlaku: `selected_meta` boleh `NULL` dan diisi oleh backfill (B6).

---

## 5. Urutan kerja yang disarankan

| Urutan | Isi | Hasil yang terlihat | Estimasi |
|---|---|---|---|
| 1 | Fase A (diagnostik) | Tahu persis kenapa nama = ID di server Anda | 0,5 jam |
| 2 | B1–B7 (+ C6) | Nama file benar, tidak bergantung Drive saat dibaca; error jelas | 3–4 jam |
| 3 | C1, C5, C7 | Cache browser bekerja, proxy tidak menggantung | 2 jam |
| 4 | C2 (+ C4) | Thumbnail disajikan statis, beban PHP turun drastis | 3 jam |
| 5 | C3, C8, C9 | Listing tidak pernah menahan pengguna; query berkurang | 2–3 jam |
| 6 | D1–D10 | Galeri besar lancar; autosave hemat | 3–4 jam |
| 7 | B6 backfill, E1–E6, QA | Data lama rapi; tema bersih | 2 jam |

Rilis bertahap boleh: langkah 1–3 saja sudah menyelesaikan bug yang Anda laporkan dan cache browser.

---

## 6. Target performance dan kriteria lulus

| Metrik | Sekarang (perkiraan dari kode) | Target |
|---|---|---|
| Editor membuka detail galeri (50 foto terpilih) | 1 listing Drive + hingga 50 resolve sekuensial | **< 500 ms**, 0 panggilan Drive (kasus normal) |
| Request PHP per muat ulang halaman klien (200 foto) | 1 + 200 (proxy) | **1–3** (REST) + 0 PHP untuk gambar yang sudah di-cache |
| Reload kedua halaman klien | Semua gambar diunduh ulang (URL berubah) | Gambar dari cache browser (**0 byte** jaringan) |
| Autosave | Listing penuh + update | 1 query `IN (...)` + 1 update, **< 150 ms** server |
| Respons `/client/photos` (cache hangat, 300 foto) | transient besar + unserialize | **< 200 ms**, `304` pada reload |
| Folder baru, kunjungan pertama | Tak terukur (bisa 15+ dtk bila API lambat) | Sync sinkron sekali (**< 3 dtk** untuk 300 foto), sisanya background |
| Gagal Drive saat klien aktif | Daftar kosong / nama = ID | Data cache lama tetap tampil, banner "data mungkin belum terbaru" |

### Checklist QA
- [ ] Pilih 5 foto di klien → buka editor → **semua nama file asli tampil** (tanpa ID) dan sama persis dengan nama di Drive.
- [ ] Putuskan Drive (ganti API key sementara jadi salah di staging) → editor tetap menampilkan nama dari snapshot; klien melihat pesan error yang jelas, **bukan** "Belum ada foto".
- [ ] Galeri lama (sudah `submitted`) → setelah backfill, namanya muncul; tombol "Muat ulang nama" bekerja.
- [ ] Nama berisi spasi, tanda kutip, huruf non-Latin, emoji tampil benar dan CSV ekspor valid.
- [ ] Reload klien dua kali: tab Network menunjukkan gambar `(disk cache)` pada reload kedua.
- [ ] Galeri 300+ foto di Android kelas menengah: scroll mulus, tanpa jank saat "Pilih semua".
- [ ] Autosave: ketik catatan panjang → ≤ 1 request per jeda 1,5 dtk; tutup tab langsung setelah memilih → pilihan tetap tersimpan.
- [ ] Token salah → 401; ID foto dari folder lain → ditolak; URL thumbnail dengan `w` di luar daftar putih → ditolak (keamanan awal tetap utuh).
- [ ] Halaman non-studio (beranda, WooCommerce, Elementor) tidak memuat aset studio dan tidak berubah.

---

## 7. Yang sengaja tidak diubah

- Token klien 32 karakter per galeri, kunci editor otomatis + akses admin via nonce, rate-limit kegagalan editor, `noindex`, `no-referrer`, `DONOTCACHEPAGE`.
- URL thumbnail tetap berupa capability URL (bertanda tangan atau berhash HMAC), token klien/kunci editor tidak pernah masuk URL gambar.
- Struktur dua template + blok di `functions.php` + aset di `studio-assets/`.

---

## 8. Instruksi singkat untuk agent (salin ke Antigravity)

> Kerjakan `plan-perbaikan-studio.md` secara berurutan: **Fase A → B → C1/C5/C7 → C2/C4 → C3/C8/C9 → D → E**. Satu fase = satu commit/checkpoint. Jangan ubah keamanan token/kunci. Sebelum menyentuh `functions.php`, buat backup. Setelah setiap fase, jalankan `php -l` dan checklist QA yang relevan di staging. Jangan hapus fungsi lama sebelum fungsi pengganti lolos uji. Laporkan hasil Fase A (status/`reason` Drive API) sebelum lanjut ke Fase B.

---

## 9. Risiko

| Risiko | Mitigasi |
|---|---|
| Migrasi DB gagal di hosting | Backup DB; `dbDelta` idempoten; `selected_meta` boleh `NULL` sehingga kode lama tetap hidup |
| Cache statis memenuhi disk | Batas ukuran + pembersihan LRU (C8), thumbnail hanya w yang masuk daftar putih |
| Server web tidak menyajikan `uploads/` publik atau memblokir hotlink | Tetap ada URL proxy sebagai jalur cadangan (C2) |
| Backfill memanggil Drive terlalu banyak | Paralel 6, jeda antar-batch, hanya ID yang namanya belum ada |
| Nama berubah di Drive setelah dipilih | Snapshot diperbarui saat "Muat ulang foto"/resync; tampilkan `synced_at` |
