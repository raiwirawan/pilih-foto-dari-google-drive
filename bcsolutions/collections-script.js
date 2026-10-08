document.addEventListener("DOMContentLoaded", () => {

    // ─── Fade-in on scroll ───────────────────────────────────────────────────
    const observerOptions = {
        root: null,
        rootMargin: "0px",
        threshold: 0.1,
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll(".fade-in-section").forEach((section) => {
        observer.observe(section);
    });

    // ─── Collection filter helpers ───────────────────────────────────────────
    const articles = document.querySelectorAll(".collection-item");

    function updateGridOffsets() {
        // Stagger offsets only make sense in grid mode
        const grid = document.querySelector(".bc-collections-grid");
        if (grid && grid.getAttribute("data-view") === "list") {
            articles.forEach((a) => a.classList.remove("md-mt-32"));
            return;
        }
        let visibleCount = 0;
        articles.forEach((article) => {
            if (article.style.display !== "none") {
                if (visibleCount % 2 === 1) {
                    article.classList.add("md-mt-32");
                } else {
                    article.classList.remove("md-mt-32");
                }
                visibleCount++;
            }
        });
    }

    updateGridOffsets();

    // ─── Category filter links ───────────────────────────────────────────────
    const filterLinks = document.querySelectorAll(".filter-link");

    filterLinks.forEach((link) => {
        link.addEventListener("click", (e) => {
            e.preventDefault();
            const category = link.getAttribute("data-filter");

            // Update active state
            filterLinks.forEach((f) => f.classList.remove("active"));
            link.classList.add("active");

            // Show / hide articles
            articles.forEach((article) => {
                const cat = article.getAttribute("data-category");
                if (category === "all" || cat === category) {
                    article.style.display = "";
                    article.classList.remove("is-visible");
                    setTimeout(() => article.classList.add("is-visible"), 50);
                } else {
                    article.style.display = "none";
                    article.classList.remove("is-visible");
                }
            });

            updateGridOffsets();
        });
    });

    // ─── View Toggle (Grid ↔ List) ───────────────────────────────────────────
    const viewBtns = document.querySelectorAll(".bc-view-btn");
    const grid     = document.querySelector(".bc-collections-grid");

    if (viewBtns.length && grid) {
        viewBtns.forEach((btn) => {
            btn.addEventListener("click", () => {
                const view = btn.getAttribute("data-view");

                // Update button active state
                viewBtns.forEach((b) => b.classList.remove("is-active"));
                btn.classList.add("is-active");

                // Apply view to grid
                grid.setAttribute("data-view", view);

                // In list mode disable stagger; in grid mode re-apply
                updateGridOffsets();

                // Re-trigger fade-in for visible items so they animate smoothly
                const visible = [...articles].filter((a) => a.style.display !== "none");
                visible.forEach((a) => a.classList.remove("is-visible"));
                setTimeout(() => {
                    visible.forEach((a) => a.classList.add("is-visible"));
                }, 50);

                // Persist preference in sessionStorage
                try { sessionStorage.setItem("bc-view", view); } catch(e) {}
            });
        });

        // Restore persisted view preference on page load
        try {
            const saved = sessionStorage.getItem("bc-view");
            if (saved && (saved === "grid" || saved === "list")) {
                grid.setAttribute("data-view", saved);
                viewBtns.forEach((b) => {
                    b.classList.toggle("is-active", b.getAttribute("data-view") === saved);
                });
                updateGridOffsets();
            }
        } catch(e) {}
    }

    // ─── Lightbox Modal ───────────────────────────────────────────────────────
    if (!document.getElementById("bc-image-modal")) {
        const modal = document.createElement("div");
        modal.id = "bc-image-modal";
        modal.innerHTML = `
            <button id="bc-modal-close">
                <span class="material-symbols-outlined" style="font-size:24px;">close</span>
            </button>
            <div class="bc-modal-content-wrapper">
                <img id="bc-modal-image" src="" alt="" />
                <div id="bc-modal-caption-container">
                    <p id="bc-modal-caption"></p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    const modal                 = document.getElementById("bc-image-modal");
    const modalImage            = document.getElementById("bc-modal-image");
    const modalCaptionContainer = document.getElementById("bc-modal-caption-container");
    const modalCaption          = document.getElementById("bc-modal-caption");

    function openModal(imgSrc, imgAlt) {
        modalImage.src = imgSrc;
        if (imgAlt) {
            modalCaption.textContent = imgAlt;
            modalCaptionContainer.classList.add("has-caption");
        } else {
            modalCaptionContainer.classList.remove("has-caption");
        }
        modal.classList.add("is-open");
        // Guard: hanya kunci overflow jika belum dikunci script lain (mis. ph-lightbox)
        // Tandai dengan attribute supaya closeModal tahu dialah yang mengunci
        if (document.body.style.overflow !== "hidden") {
            document.body.setAttribute("data-bc-overflow", "locked");
            document.body.style.overflow = "hidden";
        }
    }

    function closeModal() {
        modal.classList.remove("is-open");
        // Hanya reset overflow jika kita yang menguncinya
        if (document.body.getAttribute("data-bc-overflow") === "locked") {
            document.body.style.overflow = "";
            document.body.removeAttribute("data-bc-overflow");
        }
        setTimeout(() => {
            modalImage.src = "";
            modalCaptionContainer.classList.remove("has-caption");
        }, 500);
    }

    modal.addEventListener("click", (e) => {
        if (e.target === modal || e.target.closest("#bc-modal-close")) closeModal();
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal.classList.contains("is-open")) {
            // stopPropagation agar listener Escape di new-main.js (ph-lightbox)
            // tidak ikut terpicu saat bc-image-modal yang aktif
            e.stopPropagation();
            closeModal();
        }
    });

    articles.forEach((article) => {
        article.addEventListener("click", (e) => {
            if (e.target.closest("a")) return;
            const img = article.querySelector("img");
            if (img) {
                let src = img.getAttribute("src");
                if (src && src.includes("w=")) src = src.replace(/w=\d+/, "w=2000");
                openModal(src, img.getAttribute("data-alt"));
            }
        });
    });

});
