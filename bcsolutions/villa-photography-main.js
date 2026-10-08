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

    // Custom Smooth Scroll Logic
    const body = document.body;
    const scrollContainer = document.getElementById('scroll-container');
    const scrollContent = document.getElementById('scroll-content');
    
    let currentScroll = 0;
    let targetScroll = 0;
    let ease = 0.08; // Smoothing factor
    
    // Marquee State
    const marqueeInner = document.querySelector('.marquee-inner');
    let marqueeOffset = 0;
    
    // Set initial height
    // Native scroll takes over - Body height calculation removed for widget portability
    
    // Trigger animations on load
    function onPageLoad() {

    // Elementor footer hijacker removed - widget is now portable and uses native scroll flow.

        // Native sizing
        
        // Preloader Logic
        const preloaderText = document.getElementById('preloader-text');
        const preloader = document.getElementById('preloader');
        
        if (preloaderText && preloader) {
            preloaderText.classList.add('revealed');
            setTimeout(() => {
                preloader.classList.add('loaded');
                
                // Trigger hero text and typing effect AFTER preloader
                setTimeout(initHeroAnimations, 400);
            }, 800);
        } else {
            initHeroAnimations();
        }
        
        function initHeroAnimations() {
            const heroContent = document.querySelector('.hero-content');
            if (heroContent) {
                heroContent.classList.add('is-visible');
            }
            
            // Typing Effect
            const typingElement = document.getElementById('typing-text');
            const sourceElement = document.getElementById('typing-text-source');
            if (typingElement && sourceElement) {
                const textToType = sourceElement.innerHTML || "VISUAL<br/>PREMIUM.";
                let i = 0;
                let isTag = false;
                let text = "";
                
                function typeWriter() {
                    if (i < textToType.length) {
                        let char = textToType.charAt(i);
                        text += char;
                        typingElement.innerHTML = text;
                        
                        if (char === '<') isTag = true;
                        if (char === '>') isTag = false;
                        
                        i++;
                        
                        if (isTag) {
                            typeWriter();
                        } else {
                            setTimeout(typeWriter, 100); // 100ms per character
                        }
                    }
                }
                // Delay typing effect slightly for visual appeal after load
                setTimeout(typeWriter, 500);
            }
        }
    }

    if (document.readyState === 'complete') {
        onPageLoad();
    } else {
        window.addEventListener('load', onPageLoad);
    }

    
    // --- TESTIMONIAL SLIDER LOGIC ---
    function initTestimonialSlider() {
        const slider = document.getElementById('testi-slider');
        const btnPrev = document.querySelector('.testi-prev');
        const btnNext = document.querySelector('.testi-next');
        const counterCurrent = document.getElementById('testi-current');
        const progressFill = document.getElementById('testi-progress');
        
        if (!slider) return;

        const slides = Array.from(slider.children);
        const totalSlides = slides.length;
        if (totalSlides === 0) return;

        function updateSliderState() {
            const slideWidth = slider.clientWidth;
            const scrollPos = slider.scrollLeft;
            const currentIndex = Math.round(scrollPos / slideWidth);
            
            if (counterCurrent) counterCurrent.innerText = currentIndex + 1;
            
            if (progressFill) {
                const progressPct = totalSlides > 1 ? (currentIndex / (totalSlides - 1)) * 100 : 100;
                progressFill.style.width = `${progressPct}%`;
            }
            
            slides.forEach((slide, idx) => {
                if (idx === currentIndex) slide.classList.add('is-active');
                else slide.classList.remove('is-active');
            });
        }

        updateSliderState();

        let isScrolling;
        slider.addEventListener('scroll', () => {
            window.cancelAnimationFrame(isScrolling);
            isScrolling = window.requestAnimationFrame(updateSliderState);
        });

        // -------------------------
        // Mouse Drag (Grab) Logic
        // -------------------------
        let isDown = false;
        let startX;
        let scrollLeft;

        slider.style.cursor = 'grab';

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.style.cursor = 'grabbing';
            slider.style.scrollSnapType = 'none';
            slider.style.scrollBehavior = 'auto'; // Disable smooth for 1:1 instant drag mapping
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });

        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.style.cursor = 'grab';
            slider.style.scrollSnapType = 'x mandatory';
            slider.style.scrollBehavior = 'smooth';
            startAutoplay();
        });

        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.style.cursor = 'grab';
            slider.style.scrollSnapType = 'x mandatory';
            slider.style.scrollBehavior = 'smooth';
        });

        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 1.5; // Drag sensitivity
            slider.scrollLeft = scrollLeft - walk;
        });

        // -------------------------
        // Auto-Play Logic (2 Seconds)
        // -------------------------
        let autoplayInterval;
        
        function startAutoplay() {
            stopAutoplay();
            autoplayInterval = setInterval(() => {
                const slideWidth = slider.clientWidth;
                // If reached the end, rewind smoothly to 0, else next slide
                if (slider.scrollLeft + slideWidth >= slider.scrollWidth - 10) {
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    slider.scrollBy({ left: slideWidth, behavior: 'smooth' });
                }
            }, 2000);
        }

        function stopAutoplay() {
            clearInterval(autoplayInterval);
        }

        // Start autoplay on init
        startAutoplay();

        // Pause autoplay on user interaction
        slider.addEventListener('mouseenter', stopAutoplay);
        slider.addEventListener('touchstart', stopAutoplay, {passive: true});
        slider.addEventListener('touchend', startAutoplay);

        // Desktop Arrow Clicks
        if (btnNext) btnNext.addEventListener('click', () => {
            const slideWidth = slider.clientWidth;
            if (slider.scrollLeft + slideWidth >= slider.scrollWidth - 10) {
                slider.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                slider.scrollBy({ left: slideWidth, behavior: 'smooth' });
            }
        });
        if (btnPrev) btnPrev.addEventListener('click', () => {
            const slideWidth = slider.clientWidth;
            if (slider.scrollLeft <= 10) {
                slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
            } else {
                slider.scrollBy({ left: -slideWidth, behavior: 'smooth' });
            }
        });
    }
    
    initTestimonialSlider();

    // --- AWWWARDS LINE MASK REVEAL LOGIC (USING SPLIT-TYPE) ---
    function initLineMaskReveal() {
        const targets = document.querySelectorAll('.line-mask-target');
        if (targets.length === 0) return;

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/split-type';
        script.onload = () => {
            document.fonts.ready.then(() => {
                let globalDelay = 0;

                targets.forEach(target => {
                    const text = new SplitType(target, { types: 'words' });
                    
                    text.words.forEach((wordEl) => {
                        wordEl.style.overflow = 'hidden';
                        wordEl.style.display = 'inline-block';
                        wordEl.style.verticalAlign = 'bottom';
                        
                        const innerHTML = wordEl.innerHTML;
                        wordEl.innerHTML = '';
                        
                        const inner = document.createElement('span');
                        inner.className = 'line-mask-inner';
                        inner.style.display = 'inline-block';
                        inner.innerHTML = innerHTML;
                        
                        inner.style.transitionDelay = `${globalDelay}s`;
                        globalDelay += 0.015; 
                        
                        wordEl.appendChild(inner);
                    });
                    globalDelay += 0.05;
                });

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const inners = entry.target.querySelectorAll('.line-mask-inner');
                            inners.forEach(inner => inner.classList.add('is-revealed'));
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                targets.forEach(target => observer.observe(target));
            });
        };
        // Add error handling just in case
        script.onerror = () => console.error('Failed to load SplitType');
        document.body.appendChild(script);
    }
    
    initLineMaskReveal();


    // Parallax Elements
    const parallaxImages = document.querySelectorAll('.js-parallax');

    function smoothScroll() {
        targetScroll = window.scrollY;
        
        // Calculate velocity for parallax and marquees (no page hijack)
        const scrollDelta = targetScroll - currentScroll;
        const velocity = scrollDelta * ease;
        currentScroll += velocity;
        
        // No container transform! We use native scrolling.
        // Sticky hero is now handled via CSS position: sticky.

        // Apply Parallax, Velocity Skew, and Hover Depth
        parallaxImages.forEach(img => {
            const speed = img.getAttribute('data-speed') || 0.1;
            const wrapper = img.closest('.parallax-wrapper');
            if (wrapper) {
                const rect = wrapper.getBoundingClientRect();
                const viewportOffset = (rect.top - window.innerHeight/2) / window.innerHeight;
                
                if(rect.top < window.innerHeight + 200 && rect.bottom > -200) {
                     const yPos = viewportOffset * speed * 200; 
                     // Subtle skew based on scroll velocity
                     const skew = Math.min(Math.max(velocity * 0.15, -2.5), 2.5);
                     
                     // Hover Depth Scale
                     if (!img.currentHoverScale) img.currentHoverScale = 1;
                     const targetHoverScale = wrapper.matches(':hover') ? 1.08 : 1;
                     img.currentHoverScale += (targetHoverScale - img.currentHoverScale) * 0.1;
                     
                     const scale = img.currentHoverScale + Math.abs(velocity * 0.0005);
                     img.style.transform = `translate3d(0, ${yPos}px, 0) scale(${scale}) skewY(${skew}deg)`;
                }
            }
        });

        // Marquee Update
        if (marqueeInner) {
            marqueeOffset -= (0.04 + Math.abs(velocity * 0.015));
            // Assuming 2 identical spans, each takes 50% width of the container
            if (marqueeOffset <= -50) {
                marqueeOffset = 0; 
            }
            marqueeInner.style.transform = `translate3d(${marqueeOffset}%, 0, 0)`;
        }

        // Navbar bg change
        const navbar = document.getElementById('navbar');
        if (navbar) {
            if (currentScroll > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }

        // Scroll Progress
        const scrollProgress = document.getElementById('scroll-progress');
        if (scrollProgress && scrollContent) {
            const maxScroll = Math.max(1, scrollContent.getBoundingClientRect().height - window.innerHeight);
            const progress = Math.max(0, Math.min(1, currentScroll / maxScroll));
            scrollProgress.style.transform = `scaleX(${progress})`;
        }

        requestAnimationFrame(smoothScroll);
    }
    
    requestAnimationFrame(smoothScroll);

    // Scroll Reveal Logic with Intersection Observer
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -10% 0px', // Trigger slightly before element comes into view
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // Run once
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-up, .reveal-scale, .reveal-stagger').forEach((el) => {
        observer.observe(el);
    });
    
    // Force observer check on load for elements already in viewport
    setTimeout(() => {
        window.dispatchEvent(new Event('scroll'));
    }, 100);

    // Custom Cursor Logic
    const cursorDot = document.getElementById('cursor-dot');
    const cursorCircle = document.getElementById('cursor-circle');
    
    if (window.matchMedia("(pointer: fine)").matches) {
        document.body.classList.add('custom-cursor-enabled');
        
        let mouseX = window.innerWidth / 2;
        let mouseY = window.innerHeight / 2;
        let circleX = mouseX;
        let circleY = mouseY;
        
        window.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            
            // Dot follows instantly
            if (cursorDot) {
                cursorDot.style.left = `${mouseX}px`;
                cursorDot.style.top = `${mouseY}px`;
            }
        });

        // Magnetic Interactive Elements
        const magnetics = document.querySelectorAll('.magnetic');
        magnetics.forEach(el => {
            el.addEventListener('mousemove', (e) => {
                const rect = el.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                el.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = `translate(0px, 0px)`;
            });
        });
        
        // Smooth follow for circle
        function animateCursor() {
            circleX += (mouseX - circleX) * 0.15;
            circleY += (mouseY - circleY) * 0.15;
            
            if (cursorCircle) {
                cursorCircle.style.left = `${circleX}px`;
                cursorCircle.style.top = `${circleY}px`;
            }
            requestAnimationFrame(animateCursor);
        }
        animateCursor();
        
        // Interactive Hover Effects
        const interactives = document.querySelectorAll('a, button, .btn-hover-fx');
        interactives.forEach(el => {
            el.addEventListener('mouseenter', () => {
                if (cursorCircle) {
                    cursorCircle.style.width = '60px';
                    cursorCircle.style.height = '60px';
                    cursorCircle.style.backgroundColor = '#dac769';
                    cursorCircle.style.opacity = '0.2';
                }
            });
            el.addEventListener('mouseleave', () => {
                if (cursorCircle) {
                    cursorCircle.style.width = '40px';
                    cursorCircle.style.height = '40px';
                    cursorCircle.style.backgroundColor = 'transparent';
                    cursorCircle.style.opacity = '1';
                }
            });
        });
    }

    // Cursor hover effects on images
    const viewables = document.querySelectorAll('.parallax-wrapper');
    const cursorCircleHover = document.getElementById('cursor-circle');
    viewables.forEach(el => {
        el.addEventListener('mouseenter', () => {
            if (cursorCircleHover) cursorCircleHover.classList.add('hover-image');
        });
        el.addEventListener('mouseleave', () => {
            if (cursorCircleHover) cursorCircleHover.classList.remove('hover-image');
        });
    });

    // Setup Staggered Text Reveal
    document.querySelectorAll('.reveal-stagger').forEach(el => {
        // Skip if it's the preloader text
        if (el.id === 'preloader-text') return;
        
        let html = el.innerHTML;
        let lines = html.split(/<br\s*\/?>/i);
        el.innerHTML = '';
        lines.forEach((line, lineIndex) => {
            line.trim().split(' ').forEach((word, wordIndex) => {
                if (word.trim() === '') return;
                const outer = document.createElement('span');
                outer.className = 'stagger-word-outer';
                const inner = document.createElement('span');
                inner.className = 'stagger-word-inner';
                inner.innerHTML = word;
                inner.style.transitionDelay = `${(lineIndex * 0.1) + (wordIndex * 0.05)}s`;
                outer.appendChild(inner);
                el.appendChild(outer);
                // Add a regular space after the outer span
                el.appendChild(document.createTextNode(' '));
            });
            if (lineIndex < lines.length - 1) {
                el.appendChild(document.createElement('br'));
            }
        });
    });

    // Roster Hover Reveal Logic
    const rosterItems = document.querySelectorAll('.roster-item');
    const rosterHoverWrapper = document.getElementById('roster-hover-wrapper');
    const rosterImg = document.getElementById('roster-img');
    
    if (rosterItems.length > 0 && rosterHoverWrapper) {
        let targetX = window.innerWidth / 2;
        let targetY = window.innerHeight / 2;
        let currentX = targetX;
        let currentY = targetY;
        let isHovering = false;

        rosterItems.forEach(item => {
            item.addEventListener('mouseenter', () => {
                const imgSrc = item.getAttribute('data-image');
                if(imgSrc) rosterImg.src = imgSrc;
                rosterHoverWrapper.classList.add('is-active');
                isHovering = true;
            });
            item.addEventListener('mouseleave', () => {
                rosterHoverWrapper.classList.remove('is-active');
                isHovering = false;
            });
            item.addEventListener('mousemove', (e) => {
                targetX = e.clientX;
                targetY = e.clientY;
            });
        });

        // Smooth Lerp for Cursor Follow
        function renderRosterImage() {
            if (isHovering) {
                currentX += (targetX - currentX) * 0.1;
                currentY += (targetY - currentY) * 0.1;
                rosterHoverWrapper.style.transform = `translate3d(${currentX}px, ${currentY}px, 0)`;
            }
            requestAnimationFrame(renderRosterImage);
        }
        renderRosterImage();
    }


    // --- AUTO HARD RELOAD ON SCREEN RESIZE (Mobile <-> Desktop) ---
    function initResizeReload() {
        // Detect initial screen type based on common breakpoints
        function getScreenType() {
            const width = window.innerWidth;
            if (width < 768) return 'mobile';
            if (width >= 768 && width < 1024) return 'tablet';
            return 'desktop';
        }

        let currentScreenType = getScreenType();

        window.addEventListener('resize', () => {
            const newScreenType = getScreenType();
            if (newScreenType !== currentScreenType) {
                currentScreenType = newScreenType;
                // Hard reload the page
                window.location.reload();
            }
        });
    }
    
    initResizeReload();