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
