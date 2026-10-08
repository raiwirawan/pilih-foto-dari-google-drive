/* =====================================================================
   VILLA PHOTOGRAPHY — Main JS
   ===================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  // Register GSAP plugins
  if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);
  } else {
    return; // Exit if GSAP is not loaded
  }

  // 1. Loading Animation
  const loader = document.getElementById('vp-loader');
  const loaderTextSpans = document.querySelectorAll('.vp-line span');
  const loaderBg = document.querySelector('.vp-loader-bg');

  if (loader) {
    const tlLoader = gsap.timeline({
      onComplete: () => {
        loader.style.display = 'none';
        initScroll();
      }
    });

    if (loaderTextSpans.length > 0) {
      tlLoader.to(loaderTextSpans, {
        y: '0%',
        duration: 0.8,
        ease: 'power4.out',
        stagger: 0.1,
        delay: 0.2
      })
      .to(loaderTextSpans, {
        y: '-100%',
        duration: 0.6,
        ease: 'power3.in',
        stagger: 0.05,
        delay: 0.5
      });
    }
    
    if (loaderBg) {
      tlLoader.to(loaderBg, {
        scaleY: 0,
        transformOrigin: 'top center',
        duration: 0.8,
        ease: 'power4.inOut'
      }, "-=0.2");
    }
  } else {
    // If no loader is present, immediately initialize scroll animations
    initScroll();
  }

  // 2. Main Scroll Animations (called after loader)
  function initScroll() {
    
    // Fade in intro elements
    const fadeEls = document.querySelectorAll('.gs-fade');
    if (fadeEls.length > 0) {
      gsap.to('.gs-fade', {
        y: 0,
        opacity: 1,
        duration: 1,
        stagger: 0.2,
        ease: 'power3.out'
      });
    }

    // Setup Horizontal Scroll using GSAP ScrollTrigger
    const track = document.getElementById('vp-track');
    const projectCards = gsap.utils.toArray('.vp-project-card');
    const isMobile = window.innerWidth <= 768;
    
    // Handle One-Time Splash Intro (jalan di desktop MAUPUN mobile — cuma horizontal-scroll track di bawah yang khusus desktop)
    const splashIntro = document.getElementById('vp-splash-intro');
    let splashActive = !!splashIntro;

    if (splashActive) {
        document.body.style.overflow = "hidden"; // lock native scrolling initially

        const hideSplash = () => {
            if (!splashActive) return;
            splashActive = false;

            gsap.to(splashIntro, {
                x: "-100%",
                duration: 1.2,
                ease: "power3.inOut",
                onComplete: () => {
                    splashIntro.style.display = 'none';
                    document.body.style.overflow = ""; // restore scrolling

                    // Show contact sidebar
                    const contactBtn = document.getElementById('vp-sidebar-contact');
                    if (contactBtn) contactBtn.classList.add('show');
                }
            });
        };

        window.addEventListener('wheel', (e) => {
            if (e.deltaY > 0 && splashActive) hideSplash();
        });

        let touchStartY = 0;
        window.addEventListener('touchstart', e => touchStartY = e.touches[0].clientY);
        window.addEventListener('touchmove', e => {
            if (splashActive && touchStartY > e.touches[0].clientY + 30) hideSplash();
        });

        const splashScrollBtn = document.querySelector('.vp-splash-scroll');
        if (splashScrollBtn) {
            splashScrollBtn.style.cursor = 'pointer';
            splashScrollBtn.addEventListener('click', hideSplash);
        }
    } else {
        // If no splash intro, ensure contact sidebar is shown if it exists
        const contactBtn = document.getElementById('vp-sidebar-contact');
        if (contactBtn) contactBtn.classList.add('show');
    }

    if(!isMobile) {
      // Calculate total width to move
      if (track) {
        const totalWidth = track.scrollWidth - window.innerWidth;
        
        if (totalWidth > 0) {
            const st = gsap.to(track, {
              x: () => -totalWidth,
              ease: "none",
              scrollTrigger: {
                trigger: ".vp-portfolio",
                pin: true,
                scrub: 0.5,
                start: "top top",
                end: () => `+=${totalWidth}`,
                invalidateOnRefresh: true,
                onUpdate: (self) => {
                  const progressEl = document.getElementById('vp-progress');
                  if (progressEl) {
                      gsap.set(progressEl, { width: `${self.progress * 100}%` });
                  }
                }
              }
            });
            
            // Individual Sticky Bookmarks using containerAnimation
            if (projectCards.length > 0) {
                projectCards.forEach((card) => {
                    const bookmark = card.querySelector('.vp-bookmark');
                    if (!bookmark) return;
                    
                    const getDistance = () => card.offsetWidth - bookmark.offsetLeft - bookmark.offsetWidth;
                    
                    if (getDistance() > 0) {
                        gsap.to(bookmark, {
                            x: getDistance,
                            ease: "none",
                            scrollTrigger: {
                                trigger: bookmark,
                                containerAnimation: st,
                                start: "left left+=40", // When the bookmark hits the 40px menu sidebar
                                end: () => "+=" + getDistance(),
                                scrub: true,
                                invalidateOnRefresh: true
                            }
                        });
                    }
                });
            }
        }
      }
    }
    
    // Setup Sidebar Overlays
    const menuBtn = document.getElementById('vp-sidebar-menu');
    const mobileMenuBtn = document.getElementById('vp-menu-toggle');
    const contactBtn = document.getElementById('vp-sidebar-contact');
    const menuOverlay = document.getElementById('vp-menu-overlay');
    const contactOverlay = document.getElementById('vp-contact-overlay');
    const menuClose = document.getElementById('vp-menu-close');
    const contactClose = document.getElementById('vp-contact-close');

    if (menuBtn && menuOverlay && menuClose) {
        menuBtn.addEventListener('click', () => menuOverlay.classList.add('active'));
        menuClose.addEventListener('click', () => menuOverlay.classList.remove('active'));
    }
    if (mobileMenuBtn && menuOverlay) {
        mobileMenuBtn.addEventListener('click', () => menuOverlay.classList.add('active'));
    }
    
    if (contactBtn && contactOverlay && contactClose) {
        contactBtn.addEventListener('click', () => contactOverlay.classList.add('active'));
        contactClose.addEventListener('click', () => contactOverlay.classList.remove('active'));
    }
  }

  // 3. Intro Slideshow (if exists)
  const slideshowImg = document.getElementById('vp-slideshow-img');
  if (slideshowImg) {
    const rawData = slideshowImg.getAttribute('data-images');
    let images = [];
    if (rawData) {
      try {
        images = JSON.parse(rawData);
      } catch (e) {}
    }
    
    // Only run slideshow if more than 1 image is provided
    if (images.length > 1) {
      let currentIndex = 0;
      
      // Preload to prevent flickering
      images.forEach(src => {
        const img = new Image();
        img.src = src;
      });

      setInterval(() => {
        currentIndex = (currentIndex + 1) % images.length;
        slideshowImg.src = images[currentIndex];
      }, 1000);
    }
  }

});
