// Horizontal Drag to Scroll logic for containers
const scrollContainers = document.querySelectorAll(".scroll-container");

scrollContainers.forEach((container) => {
  let isDown = false;
  let startX;
  let scrollLeft;

  container.addEventListener("mousedown", (e) => {
    isDown = true;
    container.style.cursor = "grabbing";
    startX = e.pageX - container.offsetLeft;
    scrollLeft = container.scrollLeft;
  });

  container.addEventListener("mouseleave", () => {
    isDown = false;
    container.style.cursor = "grab";
  });

  container.addEventListener("mouseup", () => {
    isDown = false;
    container.style.cursor = "grab";
  });

  container.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - container.offsetLeft;
    const walk = (x - startX) * 2; // Scroll speed multiplier
    container.scrollLeft = scrollLeft - walk;
  });

  // Set initial cursor state
  container.style.cursor = "grab";
});

// --- NAVBAR SCROLL EFFECT ---
const navbar = document.querySelector(".navbar");
if (navbar) {
  window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }
  });
}

// Back to top button logic
const backToTopBtn = document.querySelector(".back-to-top");
if (backToTopBtn) {
  backToTopBtn.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });
}

// --- SWIPER INIT ---
const swiper = new Swiper(".mySwiper", {
  loop: true,
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
  pagination: {
    el: ".custom-pagination",
    type: "custom",
    renderCustom: function (swiper, current, total) {
      return `<span style="color:white; font-size:1.1rem;">${current}</span> <div style="width: 50px; height: 1px; background-color: var(--gray-line); position:relative;"><div style="position:absolute; left:0; top:0; height:100%; width:${(current / total) * 100}%; background-color:var(--red);"></div></div> <span style="color:var(--gray-text); font-size:1.1rem;">${total}</span>`;
    },
  },
  on: {
    init: function () {
      if (typeof lucide !== "undefined") lucide.createIcons();
    },
    slideChange: function () {
      if (typeof lucide !== "undefined") lucide.createIcons();
    },
  },
});

// --- HERO MUTE / UNMUTE TOGGLE ---
var muteBtn = document.querySelector(".hero-mute-btn");
if (muteBtn) {
  muteBtn.addEventListener("click", function () {
    var isMuted = this.getAttribute("data-muted") === "true";
    var videos = document.querySelectorAll(".hero-video");
    var muteIcon = this.querySelector(".mute-icon");
    var unmuteIcon = this.querySelector(".unmute-icon");

    if (isMuted) {
      videos.forEach(function (v) {
        v.muted = false;
      });
      this.setAttribute("data-muted", "false");
      muteIcon.style.display = "none";
      unmuteIcon.style.display = "inline-block";
    } else {
      videos.forEach(function (v) {
        v.muted = true;
      });
      this.setAttribute("data-muted", "true");
      muteIcon.style.display = "inline-block";
      unmuteIcon.style.display = "none";
    }

    if (typeof lucide !== "undefined") lucide.createIcons();
  });
}

// --- BURGER MENU TOGGLE ---
// Use event delegation since Elementor may render the header widget after this script loads
document.body.addEventListener("click", function (e) {
  // Burger button click (or its child spans)
  if (e.target.closest(".burger-menu")) {
    document.body.classList.toggle("nav-open");
    return;
  }
  // Mobile nav link click — close menu
  if (e.target.closest(".mobile-nav-links a")) {
    document.body.classList.remove("nav-open");
  }

  // --- Mobile dropdown toggle (Services in bottom bar) ---
  var dropdownLink = e.target.closest(".nav-dropdown > a");
  if (dropdownLink && window.innerWidth <= 900) {
    e.preventDefault();
    var dropdown = dropdownLink.closest(".nav-dropdown");
    var isOpen = dropdown.classList.contains("dropdown-open");
    // Close all dropdowns first
    document
      .querySelectorAll(".nav-dropdown.dropdown-open")
      .forEach(function (d) {
        d.classList.remove("dropdown-open");
      });
    // Toggle the clicked one
    if (!isOpen) {
      dropdown.classList.add("dropdown-open");
    }
    return;
  }

  // Click outside dropdown — close it
  if (!e.target.closest(".nav-dropdown")) {
    document
      .querySelectorAll(".nav-dropdown.dropdown-open")
      .forEach(function (d) {
        d.classList.remove("dropdown-open");
      });
  }
});

// --- PORTFOLIO SWIPER FILM ---
const swiperFilm = new Swiper(".swiper-film", {
  slidesPerView: "auto",
  spaceBetween: 10,
  freeMode: true,
  navigation: {
    nextEl: ".swiper-next-film",
    prevEl: ".swiper-prev-film",
  },
});

// --- SERVICES SWIPER ---
if (document.querySelector(".swiper-services")) {
  new Swiper(".swiper-services", {
    slidesPerView: "auto",
    spaceBetween: 10,
    navigation: {
      nextEl: ".swiper-next-services",
      prevEl: ".swiper-prev-services",
    },
  });
}

// --- CTA SECTION SWIPER ---
function initCtaSwiper() {
  if (
    document.querySelector(".swiper-cta-main") &&
    !document.querySelector(".swiper-cta-main").swiper
  ) {
    var isMobile = window.innerWidth <= 900;
    const ctaThumbs = new Swiper(".swiper-cta-thumbs", {
      direction: isMobile ? "horizontal" : "vertical",
      slidesPerView: isMobile ? 4 : 3,
      spaceBetween: 8,
      watchSlidesProgress: true,
      navigation: {
        nextEl: ".cta-thumb-next",
        prevEl: ".cta-thumb-prev",
      },
    });
    const ctaMain = new Swiper(".swiper-cta-main", {
      slidesPerView: 1,
      loop: true,
      autoplay: { delay: 4000, disableOnInteraction: false },
      thumbs: { swiper: ctaThumbs },
    });
  }
}
// Try immediately, and also on window load (for Elementor late-render)
initCtaSwiper();
window.addEventListener("load", initCtaSwiper);

// --- STACKED CARD SCROLL (Portfolio Reels & Team) ---
document.querySelectorAll(".stacked-scroll").forEach((scrollContainer) => {
  const cards = scrollContainer.querySelectorAll(".stacked-card");

  // Assign increasing z-index so each later card stacks ON TOP
  cards.forEach((card, i) => {
    card.style.zIndex = i + 1;
  });

  // Navigation buttons
  const wrapper = scrollContainer.closest(".swiper-container-wrapper");
  if (wrapper) {
    const prevBtn = wrapper.querySelector(".prev-btn");
    const nextBtn = wrapper.querySelector(".next-btn");
    const scrollAmount = 350; // scroll step in pixels

    if (nextBtn) {
      nextBtn.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: scrollAmount, behavior: "smooth" });
      });
    }
    if (prevBtn) {
      prevBtn.addEventListener("click", () => {
        scrollContainer.scrollBy({ left: -scrollAmount, behavior: "smooth" });
      });
    }
  }

  // Drag to scroll
  let isDown = false,
    startX,
    scrollLeft;
  scrollContainer.addEventListener("mousedown", (e) => {
    isDown = true;
    scrollContainer.style.cursor = "grabbing";
    startX = e.pageX - scrollContainer.offsetLeft;
    scrollLeft = scrollContainer.scrollLeft;
  });
  scrollContainer.addEventListener("mouseleave", () => {
    isDown = false;
    scrollContainer.style.cursor = "grab";
  });
  scrollContainer.addEventListener("mouseup", () => {
    isDown = false;
    scrollContainer.style.cursor = "grab";
  });
  scrollContainer.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - scrollContainer.offsetLeft;
    scrollContainer.scrollLeft = scrollLeft - (x - startX) * 2;
  }); // <-- FIXED: Added missing closing brace

  // Hover & Mobile Auto-play Video logic
  const isMobile = window.matchMedia("(max-width: 900px)").matches;

  if (isMobile) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        const video = entry.target.querySelector(".port-hover-video");
        if (!video) return;

        if (entry.isIntersecting) {
          video.muted = true;
          let playPromise = video.play();
          if (playPromise !== undefined) {
            playPromise.catch((e) => console.log("Mobile video play prevented:", e));
          }
        } else {
          video.pause();
        }
      });
    }, { threshold: 0.5 }); // Trigger when 50% of the card is visible

    cards.forEach((card) => observer.observe(card));
  } else {
    cards.forEach((card) => {
      const video = card.querySelector(".port-hover-video");
      if (video) {
        let playPromise;
        
        card.addEventListener("mouseenter", () => {
          video.muted = true; // Force mute to allow autoplay
          playPromise = video.play();
          if (playPromise !== undefined) {
            playPromise.catch((e) => console.log("Video play prevented:", e));
          }
        });
        
        card.addEventListener("mouseleave", () => {
          if (playPromise !== undefined) {
            playPromise.then(() => {
              video.pause();
              video.currentTime = 0;
            }).catch((e) => {
              // Play was prevented, so we can't pause
            });
          } else {
            video.pause();
            video.currentTime = 0;
          }
        });
      }
    });
  }
});

// --- PORTFOLIO RELATED CARDS NAV ---
document.body.addEventListener("click", function (e) {
  var prevBtn = e.target.closest(".btn-prev-related");
  var nextBtn = e.target.closest(".btn-next-related");
  if (prevBtn) {
    var track = prevBtn
      .closest(".related-cards-wrapper")
      .querySelector(".related-cards-track");
    if (track) track.scrollBy({ left: -300, behavior: "smooth" });
  }
  if (nextBtn) {
    var track = nextBtn
      .closest(".related-cards-wrapper")
      .querySelector(".related-cards-track");
    if (track) track.scrollBy({ left: 300, behavior: "smooth" });
  }
});

// --- LIGHTBOX / IMAGE POPUP ---
// Intercepts .btn-film-primary links that point directly to image files
// AND any button with [data-lightbox-img] attribute
(function () {
  var IMAGE_EXT = /\.(webp|jpg|jpeg|png|gif|avif|svg)(\?.*)?$/i;

  // Create shared overlay
  var overlay = document.createElement("div");
  overlay.className = "ph-lightbox-overlay";
  overlay.setAttribute("role", "dialog");
  overlay.setAttribute("aria-modal", "true");
  overlay.innerHTML =
    '<button class="ph-lightbox-close" aria-label="Tutup">&times;</button>' +
    '<img class="ph-lightbox-img" src="" alt="Price List" />';
  document.body.appendChild(overlay);

  var img = overlay.querySelector(".ph-lightbox-img");
  var closeBtn = overlay.querySelector(".ph-lightbox-close");

  // Collect all image URLs to preload
  function getImageSrc(el) {
    if (el.getAttribute("data-lightbox-img")) {
      return el.getAttribute("data-lightbox-img");
    }
    if (el.tagName === "A" && IMAGE_EXT.test(el.href)) {
      return el.href;
    }
    return null;
  }

  // Preload all lightbox images silently in background
  function preloadAll() {
    document.querySelectorAll(".btn-film-primary, [data-lightbox-img]").forEach(function (el) {
      var src = getImageSrc(el);
      if (src) {
        var pre = new Image();
        pre.src = src;
        // Store resolved src on element for fast open
        el.setAttribute("data-lightbox-resolved", src);
      }
    });
  }

  // Preload ASAP — right after page resources finish
  if (document.readyState === "complete") {
    preloadAll();
  } else {
    window.addEventListener("load", preloadAll);
  }

  function openLightbox(src) {
    // Image already in cache from preload → shows instantly
    img.src = src;
    overlay.classList.add("is-open");
    document.body.style.overflow = "hidden";
  }

  function closeLightbox() {
    overlay.classList.remove("is-open");
    document.body.style.overflow = "";
    // Keep img.src — stays cached for instant re-open
  }

  // Intercept all clicks — check for image links AND data-lightbox-img buttons
  document.body.addEventListener("click", function (e) {
    var el = e.target.closest(".btn-film-primary, [data-lightbox-img]");
    if (!el) return;

    var src = el.getAttribute("data-lightbox-resolved") || getImageSrc(el);
    if (!src) return;

    e.preventDefault();
    e.stopPropagation(); // Block Elementor's lightbox handler
    openLightbox(src);
  }, true); // useCapture: true — runs BEFORE Elementor's listener

  closeBtn.addEventListener("click", closeLightbox);

  overlay.addEventListener("click", function (e) {
    if (e.target === overlay) closeLightbox();
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && overlay.classList.contains("is-open")) closeLightbox();
  });

/**
 * BCS Social Media Marketing - Widget JS
 */
(function($) {
    "use strict";

    var SMMWidgetHandler = function($scope, $) {
        var $sliderContainer = $scope.find('.swiper-container');
        if (!$sliderContainer.length) {
            return;
        }
        
        var $prevBtn = $scope.find('.smm-nav-prev');
        var $nextBtn = $scope.find('.smm-nav-next');
        
        // Initialize Swiper
        var smmSwiper = new Swiper($sliderContainer[0], {
            slidesPerView: 'auto',
            spaceBetween: 32,
            grabCursor: true,
            freeMode: true,
            navigation: {
                nextEl: $nextBtn[0],
                prevEl: $prevBtn[0],
            },
            breakpoints: {
                0: {
                    spaceBetween: 16
                },
                768: {
                    spaceBetween: 32
                }
            }
        });
    };

    $(window).on('elementor/frontend/init', function() {
        // Register the widget handler with Elementor frontend
        elementorFrontend.hooks.addAction('frontend/element_ready/bcs_social_media_marketing.default', SMMWidgetHandler);
    });

})(jQuery);

})();

//============== BCLINNK SECTION JS =================//
// WebGL Background
(function() {
    const canvases = document.querySelectorAll('.bclinnk-shader-canvas');
    if (!canvases.length) return;
  
    canvases.forEach(canvas => {
        function syncSize() {
          const w = canvas.clientWidth  || window.innerWidth;
          const h = canvas.clientHeight || window.innerHeight;
          if (canvas.width !== w || canvas.height !== h) {
            canvas.width  = w;
            canvas.height = h;
          }
        }
        if (typeof ResizeObserver !== 'undefined') {
          new ResizeObserver(syncSize).observe(canvas);
        }
        syncSize();
      
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        if (!gl) return;
        const vs = `attribute vec2 a_position;
      varying vec2 v_texCoord;
      void main() {
        v_texCoord = a_position * 0.5 + 0.5;
        gl_Position = vec4(a_position, 0.0, 1.0);
      }`;
        const fs = `precision highp float;
      varying vec2 v_texCoord;
      uniform float u_time;
      uniform vec2 u_resolution;
      
      void main() {
          vec2 uv = v_texCoord;
          float noise = sin(uv.x * 10.0 + u_time * 0.5) * cos(uv.y * 10.0 + u_time * 0.5);
          noise += sin(uv.x * 20.0 - u_time * 0.2) * cos(uv.y * 5.0 + u_time * 0.3);
          vec3 color = vec3(0.07, 0.07, 0.07);
          vec3 glowColor = vec3(1.0, 0.0, 0.0);
          float glowMask = pow(1.0 - distance(uv, vec2(0.5)), 3.0);
          color += glowColor * glowMask * (0.05 + 0.02 * sin(u_time));
          float line = smoothstep(0.99, 1.0, sin(uv.y * 50.0 + u_time * 2.0 + noise));
          color += vec3(1.0, 0.0, 0.0) * line * 0.03;
          gl_FragColor = vec4(color, 1.0);
      }`;
        function cs(type, src) {
          const s = gl.createShader(type);
          gl.shaderSource(s, src);
          gl.compileShader(s);
          return s;
        }
        const prog = gl.createProgram();
        gl.attachShader(prog, cs(gl.VERTEX_SHADER, vs));
        gl.attachShader(prog, cs(gl.FRAGMENT_SHADER, fs));
        gl.linkProgram(prog);
        gl.useProgram(prog);
        const buf = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, buf);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1, 1,-1, -1,1, 1,1]), gl.STATIC_DRAW);
        const pos = gl.getAttribLocation(prog, 'a_position');
        gl.enableVertexAttribArray(pos);
        gl.vertexAttribPointer(pos, 2, gl.FLOAT, false, 0, 0);
        const uTime = gl.getUniformLocation(prog, 'u_time');
        const uRes = gl.getUniformLocation(prog, 'u_resolution');
        const uMouse = gl.getUniformLocation(prog, 'u_mouse');
      
        let mouse = { x: canvas.width / 2, y: canvas.height / 2 };
        window.addEventListener('mousemove', (event) => {
          const rect = canvas.getBoundingClientRect();
          if (rect.width && rect.height) {
            const nx = (event.clientX - rect.left) / rect.width;
            const ny = 1.0 - (event.clientY - rect.top) / rect.height;
            mouse.x = nx * canvas.width;
            mouse.y = ny * canvas.height;
          }
        });
      
        function render(t) {
          if (typeof ResizeObserver === 'undefined') syncSize();
          gl.viewport(0, 0, canvas.width, canvas.height);
          if (uTime) gl.uniform1f(uTime, t * 0.001);
          if (uRes) gl.uniform2f(uRes, canvas.width, canvas.height);
          if (uMouse) gl.uniform2f(uMouse, mouse.x, mouse.y);
          gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
          requestAnimationFrame(render);
        }
        render(0);
    });
  })();
  
  // Three.js Scene
  (function() {
    const containers = document.querySelectorAll('.bclinnk-three-container');
    if (!containers.length) return;
  
    containers.forEach(container => {
        const width = container.clientWidth || window.innerWidth;
        const height = container.clientHeight || window.innerHeight;
      
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(width, height);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);
      
        const group = new THREE.Group();
        scene.add(group);
      
        const loader = new THREE.TextureLoader();
        // Using dynamic dashboard image or stable abstract tech placeholder
        const textureUrl = container.getAttribute('data-image') || 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=800&q=80';
        const texture = loader.load(textureUrl);
        const geometry = new THREE.PlaneGeometry(5, 2.8);
        const material = new THREE.MeshBasicMaterial({ 
            map: texture, 
            transparent: true,
            side: THREE.DoubleSide
        });
        const dashboard = new THREE.Mesh(geometry, material);
        group.add(dashboard);
      
        const frameGeom = new THREE.PlaneGeometry(5.1, 2.9);
        const frameMat = new THREE.MeshBasicMaterial({ color: 0xff0000, transparent: true, opacity: 0.1 });
        const frame = new THREE.Mesh(frameGeom, frameMat);
        frame.position.z = -0.01;
        group.add(frame);
      
        camera.position.z = 6;
      
        let mouseX = 0;
        let mouseY = 0;
        let targetX = 0;
        let targetY = 0;
      
        window.addEventListener('mousemove', (event) => {
            mouseX = (event.clientX / window.innerWidth) - 0.5;
            mouseY = (event.clientY / window.innerHeight) - 0.5;
        });
      
        window.addEventListener('resize', () => {
            const w = container.clientWidth || window.innerWidth;
            const h = container.clientHeight || window.innerHeight;
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
        });
      
        function animate() {
            requestAnimationFrame(animate);
            targetX += (mouseX - targetX) * 0.05;
            targetY += (mouseY - targetY) * 0.05;
            group.rotation.y = targetX * 0.5;
            group.rotation.x = -targetY * 0.5;
            group.position.y = Math.sin(Date.now() * 0.001) * 0.1;
            renderer.render(scene, camera);
        }
        animate();
    });
  })();
