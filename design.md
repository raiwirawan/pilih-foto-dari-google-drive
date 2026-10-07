# DESIGN.md — Photo Catalog (Adobe-inspired)

> Pedoman layout, styling, komponen, dan perilaku UI untuk **photo catalog** (web + mobile),
> diturunkan dari struktur dan gaya adobe.com. Tujuan: foto jadi pusat perhatian, UI minimal dan "menghilang".

---

## 0. Catatan Sumber & Tingkat Keyakinan

Dokumen ini disusun dari isi halaman `https://www.adobe.com/` (struktur section, metadata style, pola gambar) + pengetahuan umum tentang design system Adobe (Spectrum).

| Tingkat | Artinya | Contoh |
|---|---|---|
| **[Observed]** | Terlihat langsung di halaman | Section bergantian putih `#fff` ↔ hitam `#000`, `rounded-corners-top/bottom`, layout `three-up`, `container`, `wide`, gambar `?width=750&format=webp&optimize=medium`, carousel, video autoplay + tombol pause |
| **[Inferred]** | Perkiraan dari Spectrum / praktik umum | Hex warna abu-abu, skala tipografi, nilai radius, breakpoint |

Nilai **[Inferred]** adalah titik awal yang masuk akal. Silakan sesuaikan dengan brand Anda. Jangan menyalin aset/logo/font berlisensi Adobe; gunakan fallback open-source yang disebutkan di bawah.

---

## 1. Prinsip Desain

1. **Foto adalah hero.** UI hanya bingkai. Hindari dekorasi yang bersaing dengan gambar.
2. **Ritme terang–gelap.** Section putih untuk browsing, section hitam untuk momen "sinematik" / showcase. [Observed]
3. **Sudut membulat besar** pada section dan kartu untuk kesan modern dan ramah. [Observed]
4. **Ruang napas lega.** Spacing section besar (3xl–4xl), spacing antar-elemen kecil dan konsisten. [Observed]
5. **Tipografi tegas, pendek.** Headline bold 1 kalimat, deskripsi 1–2 baris. [Observed]
6. **Gerak halus, bisa dimatikan.** Parallax / reveal hanya pemanis; wajib hormati `prefers-reduced-motion`. [Observed: ada kontrol pause]
7. **Cepat dulu.** Gambar responsif, WebP/AVIF, lazy load, tanpa layout shift.

---

## 2. Design Tokens

### 2.1 Warna

```css
:root {
  /* Brand */
  --color-brand-red:        #EB1000;  /* [Inferred] aksen brand, pakai hemat */
  --color-accent-blue:      #3B63FB;  /* [Inferred] CTA utama (mis. "Free trial") */
  --color-accent-blue-hover:#274DEA;

  /* Neutral (terang) */
  --color-bg:               #FFFFFF;  /* [Observed] */
  --color-bg-subtle:        #F8F8F8;  /* [Inferred] */
  --color-surface:          #FFFFFF;
  --color-border:           #E6E6E6;  /* [Inferred] */
  --color-text:             #131313;  /* [Inferred] */
  --color-text-muted:       #505050;  /* [Inferred] */

  /* Neutral (gelap) */
  --color-bg-dark:          #000000;  /* [Observed] */
  --color-surface-dark:     #121212;
  --color-border-dark:      #2C2C2C;
  --color-text-on-dark:     #FFFFFF;
  --color-text-muted-dark:  #B3B3B3;

  /* Overlay untuk teks di atas foto */
  --overlay-gradient: linear-gradient(to top, rgb(0 0 0 / .70), rgb(0 0 0 / 0) 60%);

  /* Status */
  --color-success: #12805C;
  --color-warning: #C76A00;
  --color-danger:  #D31510;
}

/* Dark theme */
:root[data-theme="dark"], .section--dark {
  --color-bg: var(--color-bg-dark);
  --color-surface: var(--color-surface-dark);
  --color-border: var(--color-border-dark);
  --color-text: var(--color-text-on-dark);
  --color-text-muted: var(--color-text-muted-dark);
}
```

**Aturan warna**
- Latar halaman netral (putih/hitam) agar warna foto tampil akurat. **Jangan** beri tint warna pada background galeri.
- Merah brand hanya untuk logo/indikator penting (badge "New", error). Bukan warna tombol utama.
- Kontras teks minimal **4.5:1** (teks kecil), **3:1** (teks besar ≥ 24px / bold ≥ 19px).

### 2.2 Tipografi

```css
:root {
  --font-sans: "Adobe Clean", "Source Sans 3", "Inter", system-ui, -apple-system,
               "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --font-mono: "Source Code Pro", ui-monospace, SFMono-Regular, Menlo, monospace;
}
```

> Adobe Clean berlisensi. Gunakan **Source Sans 3** (open-source, paling mirip) atau **Inter**.

| Token | Desktop | Mobile | Weight | Line-height | Pemakaian |
|---|---|---|---|---|---|
| `display` | 56–72px | 36–40px | 800 | 1.05 | Hero headline |
| `h1` | 44px | 30px | 800 | 1.1 | Judul halaman/katalog |
| `h2` | 32px | 24px | 700 | 1.15 | Judul section |
| `h3` | 22px | 18px | 700 | 1.25 | Judul kartu / kategori |
| `body-l` | 18px | 16px | 400 | 1.55 | Deskripsi section |
| `body` | 16px | 16px | 400 | 1.5 | Teks umum |
| `caption` | 13–14px | 13px | 400 | 1.4 | Caption foto, metadata |
| `label` | 12px | 12px | 600 | 1.2 | Badge, filter chip, uppercase opsional |

- Headline pakai **bold/extra-bold**, letter-spacing `-0.01em` sampai `-0.02em` untuk ukuran besar. [Observed: headline tebal & singkat]
- Gunakan `clamp()` untuk fluid type: `font-size: clamp(2.25rem, 1.2rem + 4vw, 4.5rem)`.
- Maksimal lebar baris teks: **60–70 karakter** (`max-width: 65ch`).

### 2.3 Spacing (skala 4px)

```css
:root {
  --space-1: 4px;   --space-2: 8px;   --space-3: 12px;  --space-4: 16px;
  --space-5: 24px;  --space-6: 32px;  --space-7: 48px;  --space-8: 64px;
  --space-9: 96px;  --space-10: 128px;

  /* Section spacing (mengikuti penamaan Adobe) [Observed: spacing-xs … spacing-4xl] */
  --section-xs:  var(--space-5);   /* 24  */
  --section-xl:  var(--space-8);   /* 64  */
  --section-3xl: var(--space-9);   /* 96  */
  --section-4xl: var(--space-10);  /* 128 */
}
```

Mobile: kurangi spacing section ±40% (mis. 3xl → 56px).

### 2.4 Radius, Border, Elevation

```css
:root {
  --radius-sm:  8px;    /* chip, input */
  --radius-md:  12px;   /* tombol kecil */
  --radius-lg:  16px;   /* kartu foto */
  --radius-xl:  24px;   /* kartu besar / hero media */
  --radius-2xl: 32px;   /* section: rounded-corners-top / bottom [Observed] */
  --radius-pill: 999px; /* tombol CTA, chip */

  --shadow-card:  0 1px 2px rgb(0 0 0 / .06), 0 4px 12px rgb(0 0 0 / .06);
  --shadow-hover: 0 8px 28px rgb(0 0 0 / .16);
  --shadow-modal: 0 24px 64px rgb(0 0 0 / .40);
}
```

- Di section gelap, **jangan pakai shadow**; pakai border `--color-border-dark` atau biarkan flat.
- Kartu foto: shadow hanya saat hover, default flat agar katalog padat tetap bersih.

### 2.5 Motion

```css
:root {
  --ease-out: cubic-bezier(.22, 1, .36, 1);
  --dur-fast: 150ms;  --dur-base: 250ms;  --dur-slow: 500ms;
}
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation: none !important; transition-duration: 0.01ms !important; scroll-behavior: auto !important; }
}
```

---

## 3. Layout

### 3.1 Breakpoint [Inferred]

| Nama | Min width | Kolom grid katalog |
|---|---|---|
| `xs` | 0 | 2 |
| `sm` | 600px | 2–3 |
| `md` | 900px | 3 |
| `lg` | 1200px | 4 |
| `xl` | 1440px | 4–5 |

### 3.2 Container [Observed: `container`, `wide`]

```css
.container      { width: min(100% - 2*var(--gutter), 1200px); margin-inline: auto; }
.container--wide{ width: min(100% - 2*var(--gutter), 1440px); margin-inline: auto; }
:root { --gutter: 16px; }
@media (min-width: 900px) { :root { --gutter: 32px; } }
```

- **`container`** untuk teks + grid standar.
- **`wide`** untuk galeri foto, supaya gambar punya ruang lebih.
- **Full-bleed** hanya untuk hero/showcase (media mepet tepi, teks tetap di dalam container).

### 3.3 Struktur Halaman Katalog

```
┌─ Global Nav (sticky, tipis) ───────────────────────────┐
├─ Hero (carousel/autoplay, 1 foto/serial unggulan) ─────┤  ← dark / full-bleed
├─ Intro statement + CTA                                 │  ← putih, rounded-top
├─ Filter / Category bar (sticky di bawah nav)           │
├─ Photo Grid (container--wide)                          │  ← putih
├─ Featured Showcase (three-up / mosaic)                 │  ← hitam (ritme gelap)
├─ Collections (kartu kategori)                          │  ← putih
├─ Testimonial / Quote carousel                          │
├─ News / Stories (3 kartu teks)                         │
├─ CTA penutup                                           │  ← hitam, rounded-bottom
└─ Footer                                                ┘
```

Setiap section: `padding-block: var(--section-3xl)` (desktop). Sambungan antar-section memakai sudut membulat atas/bawah agar terkesan "kartu bertumpuk". [Observed]

```css
.section              { padding-block: var(--section-3xl); background: var(--color-bg); color: var(--color-text); }
.section--round-top   { border-top-left-radius: var(--radius-2xl); border-top-right-radius: var(--radius-2xl); }
.section--round-bottom{ border-bottom-left-radius: var(--radius-2xl); border-bottom-right-radius: var(--radius-2xl); }
.section--overlap     { margin-top: calc(var(--radius-2xl) * -1); position: relative; }
```

### 3.4 Variasi Grid [Observed: `three-up`, `product-grid`]

| Layout | Kapan dipakai | Spesifikasi |
|---|---|---|
| **three-up** | Showcase/fitur utama | 3 kolom, gap 24px, kartu tinggi seragam; mobile → 1 kolom swipe/stack |
| **product-grid → photo-grid** | Katalog utama | CSS grid `repeat(auto-fill, minmax(240px, 1fr))`, gap 16–24px |
| **masonry** | Foto beragam rasio | CSS columns atau `grid-template-rows: masonry` + fallback; gap 16px |
| **mosaic** | Hero koleksi | 1 foto besar (2×2) + 4 kecil, rasio dikunci |
| **carousel** | Testimoni, koleksi unggulan | Scroll-snap, panah + dots, swipe di mobile |

---

## 4. Panduan Gambar (Inti Photo Catalog)

### 4.1 Rasio Aspek

| Rasio | Pemakaian |
|---|---|
| **4:5** | Default kartu katalog portrait (mobile-friendly) |
| **1:1** | Grid seragam, thumbnail koleksi |
| **3:2** | Foto lanskap standar (kamera) |
| **16:9** | Hero, banner, video |
| **Asli (masonry)** | Galeri bebas; simpan `width` & `height` di data |

Selalu set `aspect-ratio` atau atribut `width`/`height` agar **tidak ada layout shift (CLS ≈ 0)**.

### 4.2 Format & Ukuran (meniru pola Adobe: `?width=750&format=webp&optimize=medium`) [Observed]

- Format: **AVIF → WebP → JPEG** (fallback), gunakan `<picture>`.
- Sediakan varian lebar: **320, 480, 750, 1080, 1440, 2048**.
- Dukungan HiDPI: DPR 1x/2x/3x (Adobe memakai `images: 3x`). [Observed]
- Kualitas default: `optimize=medium` ≈ quality 75–80. Thumbnail 70–75, detail/lightbox 85.
- Parameter CDN yang disarankan (Cloudinary/imgix/Cloudflare/self-host Sharp):

```
https://cdn.example.com/photos/{id}.jpg?width=750&format=webp&quality=78
```

```html
<picture>
  <source type="image/avif" srcset="/p/{id}-480.avif 480w, /p/{id}-750.avif 750w, /p/{id}-1080.avif 1080w" sizes="(min-width:1200px) 25vw, (min-width:900px) 33vw, 50vw">
  <source type="image/webp" srcset="/p/{id}-480.webp 480w, /p/{id}-750.webp 750w, /p/{id}-1080.webp 1080w" sizes="(min-width:1200px) 25vw, (min-width:900px) 33vw, 50vw">
  <img src="/p/{id}-750.jpg" width="750" height="938" alt="Deskripsi foto yang bermakna"
       loading="lazy" decoding="async" style="aspect-ratio:4/5;object-fit:cover">
</picture>
```

### 4.3 Loading Strategy

- **Hero / 4 foto pertama di atas fold**: `loading="eager"`, `fetchpriority="high"`, preload gambar LCP.
- Sisanya: `loading="lazy"` + `decoding="async"`.
- **Placeholder**: warna dominan (`background-color`) atau **BlurHash/LQIP** 20–32px yang di-blur (`filter: blur(20px)`), lalu cross-fade 250ms saat gambar penuh siap.
- Infinite scroll / pagination: lebih baik **"Load more" + URL state** (bisa di-share, lebih baik untuk a11y) atau infinite scroll dengan sentinel `IntersectionObserver` (rootMargin ±600px).
- Virtualisasi (react-window / TanStack Virtual) jika > 300 item di DOM.

### 4.4 Pemrosesan Foto

- `object-fit: cover` untuk kartu; `object-fit: contain` di lightbox (tidak pernah crop foto asli di detail).
- `object-position` bisa diatur per foto (focal point) agar wajah/subjek tidak terpotong.
- Strip EXIF sensitif (GPS) di versi publik; simpan metadata kamera (ISO, aperture, lensa) untuk tampilan detail.
- Jangan upscale. Jangan beri filter/tint UI di atas foto kecuali overlay gradient untuk teks.
- Watermark (bila perlu) di versi preview saja, bukan pada file master.

### 4.5 Alt Text & Caption

- `alt` = deskripsi objek/aksi, 80–125 karakter, tanpa "foto dari…".
- Foto dekoratif: `alt=""`.
- Caption: `caption` style, 1–2 baris: judul · fotografer · lokasi/tahun.

---

## 5. Komponen

### 5.1 Global Navigation [Observed]

- Tinggi **56–64px**, sticky, background putih (atau hitam di tema gelap), garis bawah 1px `--color-border`.
- Kiri: logo; tengah: menu utama (Products / Use Cases / Solutions / Quick Actions / Learn); kanan: Plans, search, sign in, app switcher.
- **Mega menu** (desktop): panel lebar penuh, kolom per kategori, kartu promo dengan gambar di kanan.
- **Mobile**: hamburger → drawer full-screen, accordion per kategori.
- Untuk katalog: tambahkan **search bar** dengan saran visual (thumbnail kecil di dropdown hasil).

### 5.2 Hero / Marquee [Observed: `starting-marquee`, autoplay]

- Full-bleed, tinggi `min(80vh, 760px)`; headline `display` bold di kiri-bawah, CTA pill di bawah headline.
- Latar: foto/video + `--overlay-gradient` agar teks terbaca.
- **Carousel hero**: 3–5 slide, tab pill di atas ("Creativity and design", "Content creation", …) sebagai navigasi + indikator progres. [Observed]
- **Wajib**: tombol **Pause/Play** untuk autoplay (aksesibilitas) dan berhenti saat `prefers-reduced-motion`. [Observed]
- Video: `muted playsinline loop`, poster = frame pertama, jangan autoplay di jaringan hemat data (`navigator.connection.saveData`).

### 5.3 Tombol

| Varian | Style |
|---|---|
| **Primary** | Pill, bg `--color-accent-blue`, teks putih, tinggi 40–48px, padding-x 24px, bold 16px |
| **Secondary** | Pill, border 2px `--color-text`, transparan, teks sama; hover → fill |
| **Tertiary / link** | Teks + panah `→`, underline saat hover |
| **Icon button** | 40×40, lingkaran, `--color-bg-subtle`, untuk like/download/share |

- Fokus: outline **2px solid** `--color-accent-blue` + offset 2px (jangan dihilangkan).
- Touch target minimal **44×44px** di mobile.
- Tombol di atas foto: gunakan versi "glass" (`backdrop-filter: blur(12px)` + bg `rgb(255 255 255 / .2)`).

### 5.4 Section Header [Observed]

```
[eyebrow label]            ← opsional, 12px uppercase
Judul pendek bold (h2).    ← berakhir titik, gaya Adobe
Deskripsi 1–2 baris.       ← body-l, muted
[CTA link →]
```

### 5.5 Photo Card (komponen utama)

```
┌──────────────────────┐
│                      │
│        FOTO          │  ← aspect-ratio dikunci, radius-lg, overflow hidden
│                      │
│ ░░ gradient ░░░░░░░░ │  ← muncul saat hover/focus (desktop) / selalu tipis (mobile)
│ Judul        ♡  ⬇   │
└──────────────────────┘
 Caption · Fotografer      ← opsional, di bawah gambar
```

**Spesifikasi**
- Radius `--radius-lg`, `overflow: hidden`, `background: var(--color-bg-subtle)` (placeholder).
- **Hover (desktop)**: gambar `transform: scale(1.04)` 400ms `--ease-out`, shadow `--shadow-hover`, overlay + aksi (like, save, download) fade-in.
- **Focus-visible**: ring 2px accent + offset.
- **Mobile**: tidak ada hover; tampilkan aksi sebagai ikon kecil permanen atau di bottom sheet saat long-press.
- Seluruh kartu adalah **satu link** ke halaman/lightbox detail; aksi sekunder tidak boleh bersarang dalam `<a>` (pakai tombol terpisah dengan `z-index`).
- Badge (New / Featured / Free): pojok kiri-atas, `label` style, pill, bg hitam 70% + teks putih.

```css
.photo-card { position: relative; border-radius: var(--radius-lg); overflow: hidden; background: var(--color-bg-subtle); }
.photo-card img { width: 100%; height: 100%; object-fit: cover; transition: transform var(--dur-slow) var(--ease-out); }
.photo-card:hover img { transform: scale(1.04); }
.photo-card__overlay { position: absolute; inset: 0; background: var(--overlay-gradient); opacity: 0; transition: opacity var(--dur-base); }
.photo-card:hover .photo-card__overlay, .photo-card:focus-within .photo-card__overlay { opacity: 1; }
```

### 5.6 Photo Grid

```css
.photo-grid { display: grid; gap: var(--space-4); grid-template-columns: repeat(2, 1fr); }
@media (min-width: 600px)  { .photo-grid { grid-template-columns: repeat(3, 1fr); gap: var(--space-5); } }
@media (min-width: 1200px) { .photo-grid { grid-template-columns: repeat(4, 1fr); } }

/* Masonry (kolom) */
.photo-masonry { column-count: 2; column-gap: var(--space-4); }
@media (min-width: 900px)  { .photo-masonry { column-count: 3; } }
@media (min-width: 1440px) { .photo-masonry { column-count: 4; } }
.photo-masonry > * { break-inside: avoid; margin-bottom: var(--space-4); }
```

- Mobile: **2 kolom** (gap 8–12px) agar banyak foto terlihat; opsi toggle 1 kolom besar.
- Jumlah item awal: 24 (desktop) / 12 (mobile), lalu muat bertahap.
- Empty state: ilustrasi sederhana + tombol "Reset filter".

### 5.7 Filter & Kategori

- **Category bar**: chip pill horizontal, scrollable, `scroll-snap-type: x proximity`, sticky di bawah nav.
- Chip aktif: bg `--color-text`, teks `--color-bg`. Chip default: border 1px.
- **Filter lanjutan** (drawer/side panel): orientasi (portrait/landscape/square), warna dominan (swatch), tag, fotografer, tanggal, lisensi.
- **Sort**: Terbaru · Populer · Acak · A–Z.
- State filter disimpan di **URL query** (`?cat=landscape&sort=new`) agar shareable.
- Tampilkan jumlah hasil (`128 foto`) dan chip filter aktif yang bisa dihapus.

### 5.8 Lightbox / Detail Foto

- Overlay full-screen `#000` 96% opacity; foto `object-fit: contain`, maksimal tinggi `100vh - 120px`.
- Kontrol: prev/next (←/→), close (Esc), zoom (klik/pinch/double-tap), fullscreen.
- Panel info (kanan di desktop, bottom sheet di mobile): judul, fotografer, tag, EXIF, lisensi, tombol **Download / Save / Share**.
- Preload foto sebelah (prev/next). Transisi shared-element (FLIP) dari thumbnail → lightbox, 300ms.
- Mobile: swipe horizontal = navigasi, swipe ke bawah = tutup.
- Fokus terkunci di dalam dialog (`role="dialog" aria-modal="true"`), kembalikan fokus ke thumbnail saat ditutup.

### 5.9 Kartu Showcase / Three-Up [Observed]

- 3 kartu tinggi sama; gambar penuh, radius `--radius-xl`, teks di atas gradient atau di bawah gambar.
- Struktur: logo/ikon produk kecil · headline · deskripsi singkat · CTA.
- Stagger reveal kiri → kanan (delay 80ms per kartu). [Observed: `parallax stagger ltr`]
- Mobile: horizontal scroll-snap, 85% lebar kartu agar kartu berikutnya "mengintip".

### 5.10 Testimonial Carousel [Observed]

- Foto potret kiri/atas (radius-xl), kutipan `h3` bold, nama bold + peran muted, tautan CTA.
- Panah prev/next + keyboard, `aria-roledescription="carousel"`, jeda otomatis saat hover/fokus.

### 5.11 Footer

- Latar gelap atau abu-abu sangat terang; 4–5 kolom link; baris legal; pemilih bahasa/wilayah.
- Mobile: kolom jadi accordion.

---

## 6. Gerak & Interaksi [Observed: parallax-*]

| Efek | Pemakaian | Aturan |
|---|---|---|
| `parallax-move-up-fast` | Teks section intro naik sedikit saat scroll | Pergeseran ≤ 40px |
| `parallax-scale-down-grid` | Grid gambar mengecil halus ke ukuran normal saat masuk viewport | Skala 1.08 → 1 |
| `parallax-stagger-ltr` | Kartu muncul berurutan | Delay 60–100ms |
| `parallax-garage-door-reveal` | Section baru "menutupi" section sebelumnya (sticky + z-index) | Hanya desktop |
| Hover zoom | Foto kartu | scale 1.03–1.05 |

- Implementasi: CSS `animation-timeline: view()` (jika didukung) atau `IntersectionObserver` + `transform/opacity` saja (hindari animasi `top/left/width`).
- **Nonaktifkan semua parallax** saat `prefers-reduced-motion: reduce` dan di perangkat low-end.
- Target 60fps; gunakan `will-change: transform` hanya selama animasi.

---

## 7. Aksesibilitas (WCAG 2.2 AA)

- Semua gambar punya `alt` bermakna (atau kosong jika dekoratif).
- Kontrol media: pause/play untuk carousel & video autoplay, mudah ditemukan. [Observed]
- Navigasi keyboard penuh: Tab, Enter, Space, Esc, panah di carousel/lightbox.
- Tautan "Skip to main content" di awal halaman. [Observed]
- Fokus selalu terlihat; urutan fokus mengikuti urutan visual.
- Teks di atas foto: wajib gradient/scrim, uji kontras di sisi **terang** foto.
- Jangan mengandalkan warna saja untuk status (tambahkan ikon/teks).
- Target sentuh ≥ 44px; jarak antar-target ≥ 8px.
- Gunakan `aria-live="polite"` untuk update jumlah hasil filter.
- Dukungan zoom 200% tanpa kehilangan fungsi; jangan kunci orientasi.

---

## 8. Performa (target Core Web Vitals)

| Metrik | Target |
|---|---|
| LCP | ≤ 2.5s (hero/foto pertama di-preload) |
| CLS | ≤ 0.05 (aspect-ratio / width+height selalu diset) |
| INP | ≤ 200ms |
| Total bobot gambar halaman awal | ≤ 1.2 MB (mobile) |

Checklist:
- [ ] CDN gambar dengan on-the-fly resize + format negotiation (`Accept: image/avif,image/webp`)
- [ ] `srcset` + `sizes` akurat pada setiap `<img>`
- [ ] Preload hero + font utama (`font-display: swap`, subset Latin)
- [ ] Cache `immutable` untuk gambar bernama-hash, `stale-while-revalidate` untuk API katalog
- [ ] Cursor-based pagination di API (`?after=<cursor>&limit=24`)
- [ ] Video: `preload="none"` kecuali hero; sediakan `.mp4` (H.264) + `.webm`
- [ ] Hindari JS carousel berat; pakai scroll-snap native bila bisa

---

## 9. Responsif: Web vs Mobile App

| Aspek | Web (desktop) | Web (mobile) | Mobile app (RN / Flutter) |
|---|---|---|---|
| Kolom grid | 4–5 | 2 | 2 (3 di tablet) |
| Navigasi | Mega menu | Drawer | Bottom tab bar (Home · Cari · Koleksi · Profil) |
| Filter | Sidebar / bar | Bottom sheet | Bottom sheet |
| Detail foto | Lightbox + panel | Halaman penuh / sheet | Shared-element transition + pinch zoom |
| Hover | Ada | Tidak ada | Tidak ada → long-press untuk aksi |
| Gambar | `<picture>` AVIF/WebP | sama | `expo-image` / `cached_network_image` + BlurHash |

**Mapping token ke mobile native**
- React Native: simpan token di `theme.ts` (warna, spacing, radius sama persis); gunakan `FlashList` untuk grid + `expo-image` (cache disk, blurhash).
- Flutter: `ThemeData` + `ThemeExtension` untuk token; `SliverMasonryGrid`/`flutter_staggered_grid_view`; `CachedNetworkImage` + BlurHash.
- Safe area: hormati notch/home indicator (`env(safe-area-inset-*)` di web, `SafeAreaView` di RN).

---

## 10. Tailwind Config (opsional)

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        brand:  { red: '#EB1000' },
        accent: { DEFAULT: '#3B63FB', hover: '#274DEA' },
        ink:    { DEFAULT: '#131313', muted: '#505050' },
        canvas: { DEFAULT: '#FFFFFF', subtle: '#F8F8F8', dark: '#000000' },
        line:   { DEFAULT: '#E6E6E6', dark: '#2C2C2C' },
      },
      fontFamily: { sans: ['"Adobe Clean"', '"Source Sans 3"', 'Inter', 'system-ui', 'sans-serif'] },
      borderRadius: { lg: '16px', xl: '24px', '2xl': '32px' },
      screens: { sm: '600px', md: '900px', lg: '1200px', xl: '1440px' },
      maxWidth: { container: '1200px', wide: '1440px' },
      transitionTimingFunction: { out: 'cubic-bezier(.22,1,.36,1)' },
    },
  },
};
```

---

## 11. Do & Don't

| Do | Don't |
|---|---|
| Biarkan foto mengisi kartu dengan radius konsisten | Beri border tebal / frame dekoratif di sekitar foto |
| Latar netral putih/hitam | Latar berwarna yang mengubah persepsi warna foto |
| Headline bold, singkat, berakhir titik | Paragraf panjang di atas foto |
| Lazy load + placeholder blur | Memuat semua foto sekaligus |
| Simpan filter di URL | Filter yang hilang saat refresh |
| Sediakan pause pada autoplay | Autoplay tanpa kontrol |
| Aksi sekunder muncul saat hover/fokus | Menumpuk 4+ tombol permanen di tiap kartu |
| Pakai `aspect-ratio` untuk cegah layout shift | Membiarkan gambar "melompat" saat dimuat |
| Variasikan ritme terang/gelap antar-section | Semua section gelap atau semua putih tanpa jeda |

---

## 12. Checklist Implementasi

**Fondasi**
- [ ] Token CSS (warna, tipografi, spacing, radius, motion) terpasang
- [ ] Dark section class + kontras diverifikasi
- [ ] Container `container` & `wide` + breakpoint

**Katalog**
- [ ] `PhotoCard` (hover, focus, badge, aksi)
- [ ] `PhotoGrid` + varian masonry
- [ ] Filter bar sticky + chip + drawer + URL state
- [ ] Pagination / infinite scroll + skeleton
- [ ] Lightbox (keyboard, swipe, zoom, preload tetangga)
- [ ] Empty / error / loading state

**Halaman**
- [ ] Hero carousel (pause, reduced-motion)
- [ ] Showcase three-up, testimonial, news, footer
- [ ] Mega menu + mobile drawer + search visual

**Kualitas**
- [ ] Lighthouse: Performance ≥ 90, A11y ≥ 95
- [ ] Uji keyboard & screen reader (VoiceOver / TalkBack / NVDA)
- [ ] Uji di jaringan lambat (Slow 4G) dan perangkat low-end
- [ ] Uji `prefers-reduced-motion` & `prefers-color-scheme`

---

## 13. Referensi Pola dari adobe.com [Observed]

- Struktur section: marquee → "Everything you need to make anything." → Explore what's new → testimonial carousel → Adobe News → "Tools that work for you." (product grid gelap).
- Metadata style per section: `rounded-corners-top`, `rounded-corners-bottom`, `spacing-xl-top`, `spacing-xs-bottom`, `spacing-3xl-top/bottom`, `spacing-4xl-top`, `container`, `wide`, `three-up`, `dark`, `carousel`, `product-grid`, `parallax-*`.
- Latar section: `#fff` / `#ffffff` / `white` untuk konten terang, `#000` untuk grid produk gelap.
- Gambar: WebP/PNG `?width=750`, `optimize=medium`, flag `images: 3x`.
- Aksesibilitas: tombol pause autoplay, link skip to main content.
- Copywriting: headline pendek + titik, deskripsi 1–2 kalimat, CTA jelas ("Free trial", "Explore", "Read story").
