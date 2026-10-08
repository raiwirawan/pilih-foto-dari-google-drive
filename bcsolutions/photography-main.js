// =====================================================================
//  BC PHOTOGRAPHY — script landing page (bcsolutions.id/photography-bali)
//  Dipisah dari photography-bali.html — isi identik dengan versi inline:
//  loading tumpuk-menyebar, kolase grid loop tanpa ujung + smooth scroll,
//  menu "+", mode fokus (crossfade + thumbnail rail).
// =====================================================================
(function () {
  'use strict';
  document.body.classList.add('bcs-photography-page');
  var isTouch = window.matchMedia('(hover: none), (pointer: coarse)').matches;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var photoDataEl = document.getElementById('bcs-photo-data');
  var PHOTOS = [];
  if (photoDataEl) {
    try {
      PHOTOS = JSON.parse(photoDataEl.textContent);
    } catch (e) {}
  }

  // Fallback default photos
  if (!PHOTOS || PHOTOS.length === 0) {
    var UP = 'https://www.bcsolutions.id/wp-content/uploads/';
    PHOTOS = [
      { src: UP + '2026/03/photography-scaled.webp',        cap: 'Brand Campaign',            alt: 'Brand campaign photography Bali — BC Solutions' },
      { src: UP + '2026/03/photo15-scaled.webp',            cap: 'Product Series',            alt: 'Product photography Bali — studio series' },
      { src: UP + '2026/04/BS-Kitchen.webp',                cap: 'BS Kitchen — Kuta',         alt: 'Restaurant photography Bali — BS Kitchen Kuta' },
      { src: UP + '2026/03/photography-4-scaled.webp',      cap: 'Corporate',                 alt: 'Corporate photography Bali — BC Solutions' },
      { src: UP + '2026/04/The-Amerta-Jungle-Reterat.webp', cap: 'The Amerta Jungle Retreat', alt: 'Villa photography Bali — The Amerta Jungle Retreat' },
      { src: UP + '2026/03/photography-7-scaled.webp',      cap: 'Event Coverage',            alt: 'Event photography Bali — BC Solutions' },
      { src: UP + '2026/04/Bella-Belly.webp',               cap: 'Bella Belly',               alt: 'Food photography Bali — Bella Belly' },
      { src: UP + '2026/03/photography-32-scaled.jpg',      cap: 'Portrait',                  alt: 'Portrait photography Bali — BC Solutions' },
      { src: UP + '2026/03/photography-11-scaled.webp',     cap: 'Lifestyle',                 alt: 'Lifestyle photography Bali — BC Solutions' },
      { src: UP + '2026/04/Rasa-Pupuan-Restaurant.webp',    cap: 'Rasa Pupuan Restaurant',    alt: 'Restaurant photography Bali — Rasa Pupuan' },
      { src: UP + '2026/03/photography-16-scaled.webp',     cap: 'Commercial',                alt: 'Commercial photography Bali — BC Solutions' }
    ];
  }

  var WL = 300, HL = 195;
  var WA = 170, HP = 280;
  var WC = 210;
  var GAP_ROW = 30;
  var GAP_PERIOD = 200;
  var PERIOD = HL + GAP_ROW + HP + GAP_PERIOD;

  var DESKTOP = {
    tileH: PERIOD * 4,
    tracks: { A: -4, B: 18, C: 'C', D: 66, E: 91 },
    slots: [
      [1, 'B',  0, WL, HL],   [4, 'D',  0, WL, HL],
      [0, 'A',  HL+GAP_ROW, WA, HP],   [3, 'C',  HL+GAP_ROW, WC, HP],   [8, 'E',  HL+GAP_ROW, WA, HP],
      [5, 'B',  PERIOD, WL, HL],   [9, 'D',  PERIOD, WL, HL],
      [2, 'A',  PERIOD+HL+GAP_ROW, WA, HP],   [7, 'C',  PERIOD+HL+GAP_ROW, WC, HP],   [6, 'E',  PERIOD+HL+GAP_ROW, WA, HP],
      [10, 'B', PERIOD*2, WL, HL],   [0, 'D',  PERIOD*2, WL, HL],
      [4, 'A',  PERIOD*2+HL+GAP_ROW, WA, HP],   [1, 'C',  PERIOD*2+HL+GAP_ROW, WC, HP],
      [8, 'B',  PERIOD*3, WL, HL],   [2, 'D',  PERIOD*3, WL, HL],
      [6, 'C',  PERIOD*3+HL+GAP_ROW, WC, HP],   [3, 'E',  PERIOD*3+HL+GAP_ROW, WA, HP]
    ]
  };

  // MOBILE: sistem baris seragam yang sama, diskalakan untuk layar sempit.
  // Baris L = HANYA kolom tengah (landscape 200x130); baris P = HANYA tepi
  // kiri & kanan (100x160, atas & tinggi sama). Tidak ada kolom yang
  // tumpang-tindih: C bottom (130) < A/E top (170); A/E bottom (330) <
  // C berikutnya (450). gapY = 390 (celah kosong antar baris, tempat
  // wordmark terlihat saat load).
  var MOBILE = {
    tileH: 1800,                                  // 4 periode x 450
    gapY: 390,
    tracks: { A: -5, C: 'C', E: 79 },
    slots: [
      [1, 'C',    0, 200, 130],
      [0, 'A',  170, 100, 160], [8, 'E',  170, 100, 160],
      [5, 'C',  450, 200, 130],
      [2, 'A',  620, 100, 160], [7, 'E',  620, 100, 160],
      [4, 'C',  900, 200, 130],
      [3, 'A', 1070, 100, 160], [9, 'E', 1070, 100, 160],
      [10, 'C', 1350, 200, 130],
      [6, 'A', 1520, 100, 160], [0, 'E', 1520, 100, 160]
    ]
  };

  var world = document.getElementById('world');
  var spacer = document.getElementById('spacer');
  var cfg, tileH;

  function findBestGap() {
    var gaps = [];
    for (var p = 0; p < 4; p++) {
      var endP = p * PERIOD + HL + GAP_ROW + HP;
      var startL = (p + 1) * PERIOD;
      if (p < 3) gaps.push((endP + startL) / 2);
    }
    var endPLast = 3 * PERIOD + HL + GAP_ROW + HP;
    gaps.push((endPLast + tileH) / 2);
    return gaps[0];
  }

  function render() {
    cfg = window.innerWidth < 700 ? MOBILE : DESKTOP;
    tileH = cfg.tileH;
    world.innerHTML = '';

    var vh = window.innerHeight;
    var vw = window.innerWidth;
    var centerX = vw / 2;
    var centerY = vh / 2;

    // gapY = titik tengah CELAH KOSONG antar baris (bukan tileH/2 yang bisa
    // jatuh tepat di atas foto tengah) — wordmark terlihat saat load.
    var gapY = (cfg.gapY !== undefined) ? cfg.gapY : findBestGap();
    var scrollInit = tileH + gapY - vh / 2;

    for (var t = 0; t < 3; t++) {
      cfg.slots.forEach(function (s, slotIdx) {
        var d = document.createElement('div');
        d.className = 'ph';
        var track = cfg.tracks[s[1]];
        var leftVal = (track === 'C') ? 'calc(50% - ' + (s[3] / 2) + 'px)' : track + '%';
        d.style.left = leftVal;
        d.style.top = (t * tileH + s[2]) + 'px';
        d.style.width = s[3] + 'px';
        d.style.height = s[4] + 'px';
        // Foto diambil BERDASARKAN URUTAN SLOT (bukan indeks hardcode s[0]
        // yang cuma mengenal foto 0-10) — jadi SEMUA foto dari widget ikut
        // tampil, maks 18 unik di desktop / 12 di mobile (jumlah slot per
        // ubin). Harus sama untuk ketiga ubin agar loop scroll tetap mulus.
        var pIdx = slotIdx % PHOTOS.length;
        d.dataset.idx = pIdx;

        var fotoWorldTop = t * tileH + s[2];
        var fotoWorldLeft = (track === 'C') ? (vw / 2 - s[3] / 2) : ((track / 100) * vw);
        var tx = centerX - (fotoWorldLeft + s[3] / 2);
        var ty = centerY - ((fotoWorldTop - scrollInit) + s[4] / 2);

        d.style.setProperty('--tx', tx + 'px');
        d.style.setProperty('--ty', ty + 'px');

        var im = document.createElement('img');
        im.src = PHOTOS[pIdx].src;
        im.alt = PHOTOS[pIdx].alt;
        im.loading = (t === 1) ? 'eager' : 'lazy';
        d.appendChild(im);
        world.appendChild(d);
      });
    }
    spacer.style.height = (tileH * 3) + 'px';
    return scrollInit;
  }

  history.scrollRestoration = 'manual';
  var scrollInit = render();
  window.scrollTo(0, scrollInit);

  var initialLoadingDone = false;
  var photos = Array.from(world.querySelectorAll('.ph'));

  photos.forEach(function(ph) {
    var tx = parseFloat(ph.style.getPropertyValue('--tx'));
    var ty = parseFloat(ph.style.getPropertyValue('--ty'));
    ph.dataset.dist = tx * tx + ty * ty;
  });
  photos.sort(function(a, b) { return a.dataset.dist - b.dataset.dist; });

  var stackPhotos = photos.slice(0, 9);

  stackPhotos.forEach(function(ph, i) {
    var rot = (Math.random() - 0.5) * 16;
    ph.style.setProperty('--rot', rot + 'deg');
    ph.style.zIndex = 10 + i;
  });

  var stackCount = 0;
  function stackNext() {
    if (stackCount < stackPhotos.length) {
      stackPhotos[stackCount].style.opacity = '1';
      stackCount++;
      setTimeout(stackNext, 200);
    } else {
      setTimeout(spreadOut, 350);
    }
  }

  function spreadOut() {
    stackPhotos.forEach(function(ph) { ph.style.zIndex = ''; });
    document.body.classList.add('loaded');
    setTimeout(function () {
      document.body.classList.add('finished');
      initialLoadingDone = true;
    }, 1100);
  }

  if (!reduced) {
    setTimeout(stackNext, 150);
  } else {
    document.body.classList.add('loaded', 'finished');
    initialLoadingDone = true;
  }

  var current = scrollInit;
  var SMOOTH = (isTouch || reduced) ? 1 : 0.09;
  function scrollLoopFrame() {
    var target = window.scrollY;
    if (target >= tileH * 1.75) {
      window.scrollTo(0, target - tileH);
      current -= tileH;
      target -= tileH;
    } else if (target < tileH * 0.25) {
      window.scrollTo(0, target + tileH);
      current += tileH;
      target += tileH;
    }
    current += (target - current) * SMOOTH;
    if (Math.abs(target - current) < 0.1) current = target;
    world.style.transform = 'translate3d(0,' + (-current) + 'px,0)';
    requestAnimationFrame(scrollLoopFrame);
  }
  requestAnimationFrame(scrollLoopFrame);

  var resizeT;
  window.addEventListener('resize', function () {
    if (!initialLoadingDone) return;
    clearTimeout(resizeT);
    resizeT = setTimeout(function () {
      document.body.classList.add('no-anim');
      document.body.classList.remove('loaded', 'finished');
      scrollInit = render();
      window.scrollTo(0, scrollInit);
      current = scrollInit;
      void document.body.offsetHeight;
      requestAnimationFrame(function () {
        document.body.classList.remove('no-anim');
        document.body.classList.add('loaded', 'finished');
      });
    }, 200);
  });

  var plus = document.getElementById('plus');
  var menu = document.getElementById('menu');
  function toggleMenu(force) {
    var open = force !== undefined ? force : !menu.classList.contains('open');
    menu.classList.toggle('open', open);
    plus.classList.toggle('open', open);
  }
  plus.addEventListener('click', function () {
    if (focusEl.classList.contains('open')) { closeFocus(); return; }
    toggleMenu();
  });
  document.getElementById('menu-work').addEventListener('click', function (e) {
    e.preventDefault();
    toggleMenu(false);
  });

  // ---------- MODE FOKUS ----------
  var focusEl = document.getElementById('focus');
  var rail = document.getElementById('rail');
  var stageA = document.getElementById('stageA');
  var stageB = document.getElementById('stageB');
  var frontIsA = true;
  var currentIdx = -1;
  var animating = false;

  PHOTOS.forEach(function (p, i) {
    var th = document.createElement('div');
    th.className = 'thumb';
    th.innerHTML = '<img src="' + p.src + '" alt="" loading="lazy">';
    th.addEventListener('click', function () { showPhoto(i); });
    rail.appendChild(th);
  });
  var thumbs = rail.querySelectorAll('.thumb');

  function showPhoto(i, instant) {
    i = (i + PHOTOS.length) % PHOTOS.length;
    if (i === currentIdx || animating) return;
    currentIdx = i;
    var incoming = frontIsA ? stageB : stageA;
    var outgoing = frontIsA ? stageA : stageB;
    frontIsA = !frontIsA;
    incoming.src = PHOTOS[i].src;
    incoming.alt = PHOTOS[i].alt;
    if (instant) {
      incoming.classList.add('show');
      outgoing.classList.remove('show');
    } else {
      animating = true;
      requestAnimationFrame(function () {
        incoming.classList.add('show');
        outgoing.classList.remove('show');
        setTimeout(function () { animating = false; }, 480);
      });
    }
    document.getElementById('caption').textContent = PHOTOS[i].cap || '';
    thumbs.forEach(function (t, j) {
      t.classList.remove('active', 'adj1');
      if (j === i) t.classList.add('active');
      else if (Math.abs(j - i) === 1) t.classList.add('adj1');
    });
    var t = thumbs[i];
    if (t) {
      if (window.innerWidth < 700) {
        // mobile: rel horizontal di bawah — geser ke samping
        rail.scrollTo({ left: t.offsetLeft - rail.clientWidth / 2 + t.clientWidth / 2, behavior: 'smooth' });
      } else {
        rail.scrollTo({ top: t.offsetTop - rail.clientHeight / 2 + t.clientHeight / 2, behavior: 'smooth' });
      }
    }
  }

  function openFocus(i) {
    toggleMenu(false);
    focusEl.classList.add('open');
    focusEl.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    stageA.classList.remove('show');
    stageB.classList.remove('show');
    currentIdx = -1;
    showPhoto(i, true);
  }
  function closeFocus() {
    focusEl.classList.remove('open');
    focusEl.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  world.addEventListener('click', function (e) {
    var ph = e.target.closest('.ph');
    if (ph) openFocus(+ph.dataset.idx);
  });
  document.getElementById('back').addEventListener('click', closeFocus);

  var wheelLock = 0;
  focusEl.addEventListener('wheel', function (e) {
    e.preventDefault();
    var now = Date.now();
    if (now - wheelLock < 550 || Math.abs(e.deltaY) < 12) return;
    wheelLock = now;
    showPhoto(currentIdx + (e.deltaY > 0 ? 1 : -1));
  }, { passive: false });

  // Geser vertikal ATAU horizontal = ganti foto (geser di atas rel
  // thumbnail dibiarkan — itu untuk scroll rel-nya sendiri).
  var touchY = null, touchX = null, touchOnRail = false;
  focusEl.addEventListener('touchstart', function (e) {
    touchY = e.touches[0].clientY;
    touchX = e.touches[0].clientX;
    touchOnRail = !!(e.target.closest && e.target.closest('#rail'));
  }, { passive: true });
  focusEl.addEventListener('touchend', function (e) {
    if (touchY === null || touchOnRail) { touchY = touchX = null; return; }
    var dy = touchY - e.changedTouches[0].clientY;
    var dx = touchX - e.changedTouches[0].clientX;
    touchY = touchX = null;
    if (Math.abs(dy) >= Math.abs(dx)) {
      if (Math.abs(dy) > 45) showPhoto(currentIdx + (dy > 0 ? 1 : -1));
    } else {
      if (Math.abs(dx) > 45) showPhoto(currentIdx + (dx > 0 ? 1 : -1));
    }
  }, { passive: true });

  document.addEventListener('keydown', function (e) {
    if (focusEl.classList.contains('open')) {
      if (e.key === 'Escape') closeFocus();
      if (e.key === 'ArrowDown' || e.key === 'ArrowRight') showPhoto(currentIdx + 1);
      if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') showPhoto(currentIdx - 1);
    } else if (e.key === 'Escape') {
      toggleMenu(false);
    }
  });
})();
