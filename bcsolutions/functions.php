<?php
/**
 * ── Global Output Buffer Guard ──
 * Beberapa file widget/tema mengeluarkan karakter stray (seperti ?>)
 * saat di-require. Ini merusak REST API JSON (Elementor stuck loading)
 * dan muncul sebagai teks ?> di frontend.
 *
 * Fix: tangkap semua output sejak awal, buang sebelum response dikirim.
 */
ob_start();

// Bersihkan stray output sebelum REST API mengirim JSON
add_filter('rest_pre_serve_request', function ($served) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    return $served;
}, 0);

// Bersihkan stray output sebelum frontend render HTML
// PENTING: pakai ob_clean() bukan ob_get_clean() — supaya buffer TETAP aktif
// menangkap stray output dari widget files yang di-load setelah hook ini.
add_action('template_redirect', function () {
    if (ob_get_level() > 0 && ob_get_length() > 0) {
        ob_clean();
    }
}, 0);

// Bersihkan stray output untuk WooCommerce AJAX (admin-ajax.php)
// ob_start() di atas TIDAK di-clean oleh rest_pre_serve_request maupun template_redirect
// karena keduanya tidak fire untuk admin-ajax. Tanpa ini → checkout #order_review stuck.
add_action('admin_init', function () {
    if (!defined('DOING_AJAX') || !DOING_AJAX) return;
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    if (strpos($action, 'woocommerce') === false && strpos($action, 'wc_') === false) return;
    if (ob_get_level() > 0 && ob_get_length() > 0) {
        ob_clean();
    }
}, 1);

function my_theme_setup()
{
    // Enable featured images
    add_theme_support('post-thumbnails');

    // Enable title tag
    add_theme_support('title-tag');

    // Register menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'my-starter-theme'),
    ));
}
add_action('after_setup_theme', 'my_theme_setup');
function mytheme_enqueue_google_fonts()
{
    wp_enqueue_style(
        'boldonse-font',
        'https://fonts.googleapis.com/css2?family=Boldonse&display=swap',
        array(),
        null
    );
}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_google_fonts');
function my_theme_scripts()
{
    wp_enqueue_style(
        'figtree-font',
        'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
        false,
        null
    );
    wp_enqueue_style(
        'swiper-css',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        array(),
        '11.0.0'
    );

    // Swiper JS
    wp_enqueue_script(
        'swiper-js',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        array('jquery'),
        '11.0.0',
        true
    );
    // Enqueue main stylesheet
    wp_enqueue_style('main-style', get_stylesheet_uri(), array(), '2.0.1');

    // Plus Jakarta Sans font
    wp_enqueue_style(
        'plus-jakarta-sans-font',
        'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap',
        array(),
        null
    );

    // New style CSS
    wp_enqueue_style(
        'new-style',
        get_stylesheet_directory_uri() . '/new-style.css',
        array('main-style'),
        filemtime(get_stylesheet_directory() . '/new-style.css')
    );

    // Lucide Icons JS
    wp_enqueue_script(
        'lucide-js',
        'https://unpkg.com/lucide@latest',
        array(),
        null,
        false
    );

    // Lenis JS — DISABLED: menyebabkan scroll terkunci di beberapa browser.
    // Kalau mau re-enable, uncomment kedua block di bawah ini.
    // wp_enqueue_script(
    //     'lenis-js',
    //     'https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.16/bundled/lenis.min.js',
    //     array(),
    //     '1.0.16',
    //     true
    // );
    // wp_enqueue_script(
    //     'main-js',
    //     get_stylesheet_directory_uri() . '/main.js',
    //     array('lenis-js'),
    //     null,
    //     true
    // );

    // Three.js for BCLinnk 3D effects
    wp_enqueue_script(
        'three-js',
        'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js',
        array(),
        'r128',
        true
    );

    // New main JS
    wp_enqueue_script(
        'new-main-js',
        get_stylesheet_directory_uri() . '/new-main.js',
        array('lucide-js', 'three-js'),
        filemtime(get_stylesheet_directory() . '/new-main.js'),
        true
    );

    // Photography Page Widget Dependencies
    wp_register_style(
        'bcs-photography-style',
        get_stylesheet_directory_uri() . '/photography-style.css',
        array(),
        '1.0.0'
    );
    wp_register_script(
        'bcs-photography-js',
        get_stylesheet_directory_uri() . '/photography-main.js',
        array(),
        '1.0.0',
        true
    );

    // Villa Photography Page Widget Dependencies
    wp_register_style(
        'bcs-villa-photography-style',
        get_stylesheet_directory_uri() . '/villa-photography-style.css',
        array(),
        '1.0.0'
    );
    wp_register_script(
        'gsap-js',
        'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js',
        array(),
        null,
        true
    );
    wp_register_script(
        'scrolltrigger-js',
        'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js',
        array('gsap-js'),
        null,
        true
    );
    wp_register_script(
        'bcs-villa-photography-js',
        get_stylesheet_directory_uri() . '/villa-photography-main.js',
        array('jquery', 'gsap-js', 'scrolltrigger-js'),
        '1.0.0',
        true
    );

    // Digital Marketing Page Widget Dependencies
    wp_register_style(
        'bcs-dm-fonts',
        'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Caveat:wght@700&display=swap',
        array(),
        null
    );
    wp_register_style(
        'bcs-dm-style',
        get_stylesheet_directory_uri() . '/dm-style.css',
        array(),
        '1.0.0'
    );
    wp_register_script(
        'bcs-dm-js',
        get_stylesheet_directory_uri() . '/dm-main.js',
        array('jquery'),
        '1.0.0',
        true
    );

    //Photography Collections Style and JS Registration
    wp_register_style(
        'google-material-symbols',
        'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0',
        array(),
        null
    );
    wp_register_style(
        'bcs-collections-style',
        get_stylesheet_directory_uri() . '/collections-style.css',
        array('google-material-symbols'),
        '1.0.0'
    );
    wp_register_script(
        'bcs-collections-js',
        get_stylesheet_directory_uri() . '/collections-script.js',
        array('jquery'),
        '1.0.0',
        true
    );

}
add_action('wp_enqueue_scripts', 'my_theme_scripts');


function register_custom_elementor_widgets()
{

    if (defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')) {

        // Hero Section Widget
        require_once(__DIR__ . '/elementor/hero-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Hero_Section()
        );

        // Category Section Widget
        require_once(__DIR__ . '/elementor/category-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Category_Section()
        );

        // destination slider Section Widget
        require_once(__DIR__ . '/elementor/destination-slider-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Destination_Section()
        );

        // thumb slider Section Widget
        require_once(__DIR__ . '/elementor/thumbnail-slider-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Thumbnail_Slider()
        );

        // thumb slider Section Widget
        require_once(__DIR__ . '/elementor/post-slider-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Post_slider()
        );

        // running text Section Widget
        require_once(__DIR__ . '/elementor/running_text.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Running_Text()
        );

        // spotlight slidfer Widget
        require_once(__DIR__ . '/elementor/spotlight-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Spotlight_Section()
        );

        require_once(__DIR__ . '/elementor/igung-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Tabanan_Tourism_Widget()
        );

        // Header Section Widget
        require_once(__DIR__ . '/elementor/new-header.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Header_Section()
        );

        // New Hero Section Widget
        require_once(__DIR__ . '/elementor/new-hero.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \New_Hero_Section()
        );

        // Visual Contents Section Widget
        require_once(__DIR__ . '/elementor/new-visual-contents.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Visual_Contents_Section()
        );

        // Portfolio Section Widget
        require_once(__DIR__ . '/elementor/new-portfolio.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Portfolio_Section()
        );

        // Latest Portfolio Section Widget
        require_once(__DIR__ . '/elementor/new-latest-portfolio.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Latest_Portfolio_Section()
        );

        // CTA Section Widget
        require_once(__DIR__ . '/elementor/new-cta-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \CTA_Section()
        );

        // Services Section Widget
        require_once(__DIR__ . '/elementor/new-services.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Services_Section()
        );

        // Team Section Widget
        require_once(__DIR__ . '/elementor/new-teams.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Team_Section()
        );

        // News Section Widget
        require_once(__DIR__ . '/elementor/news-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \News_Section()
        );

        // Footer Section Widget
        require_once(__DIR__ . '/elementor/new-footer.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Footer_Section()
        );

        // News Page Widgets (separate)
        require_once(__DIR__ . '/elementor/news-hero.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \News_Hero_Section()
        );

        require_once(__DIR__ . '/elementor/news-filter.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \News_Filter_Section()
        );

        require_once(__DIR__ . '/elementor/news-featured.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \News_Featured_Section()
        );

        require_once(__DIR__ . '/elementor/news-grid.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \News_Grid_Section()
        );

        // Portfolio Page Widgets
        require_once(__DIR__ . '/elementor/portfolio-hero.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Portfolio_Hero_Section()
        );

        require_once(__DIR__ . '/elementor/portfolio-detail.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Portfolio_Detail_Section()
        );

        require_once(__DIR__ . '/elementor/portfolio-related.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Portfolio_Related_Section()
        );

        // Photography Page Widget
        require_once(__DIR__ . '/elementor/villa-photography-intro.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Photography_Intro_Widget()
        );

        require_once(__DIR__ . '/elementor/villa-photography-portfolio.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Photography_Portfolio_Widget()
        );

        require_once(__DIR__ . '/elementor/photography-grid.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Photography_Grid_Widget()
        );

        require_once(__DIR__ . '/elementor/villa-photography-about.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \BC_Villa_About_Page_Widget()
        );

        require_once(__DIR__ . '/elementor/villa-photography-footer.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \BC_Villa_Footer_Widget()
        );

        require_once(__DIR__ . '/elementor/villa-photography-header.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \BC_Villa_Header_Widget()
        );

        // Digital Marketing Widget
        require_once(__DIR__ . '/elementor/digital-marketing.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Digital_Marketing_Widget()
        );
        
        // Photography Collections Header
        require_once(__DIR__ . '/elementor/bc-collections-header.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \BC_Header_Widget()
        );
        
        // Photography Collections
        require_once(__DIR__ . '/elementor/bc-collections.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \BC_Collections_Widget()
        );
        
        // Photography Collections Footer
        require_once(__DIR__ . '/elementor/bc-collections-footer.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \BC_Footer_Widget()
        );

        // Social Media Marketing Section
        require_once(__DIR__ . '/elementor/bc-social-media-marketing.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \Social_Media_Marketing_Widget()
        );

        // BClinnk Main Page Widget
        require_once(__DIR__ . '/elementor/bclinnk-section.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new \BClinnk_Main_Page_Widget()
        );
    }

}
/**
 * Recursively search Elementor widget data for the first image URL.
 * Supports hero_bg (Portfolio Hero), image, and background_image controls.
 */
function bcs_find_elementor_image($elements)
{
    foreach ($elements as $element) {
        // Check hero_bg (used by Portfolio Hero widget)
        if (!empty($element['settings']['hero_bg']['url'])) {
            return $element['settings']['hero_bg']['url'];
        }
        // Check generic image control
        if (!empty($element['settings']['image']['url'])) {
            return $element['settings']['image']['url'];
        }
        // Check background_image
        if (!empty($element['settings']['background_image']['url'])) {
            return $element['settings']['background_image']['url'];
        }
        // Recurse into child elements
        if (!empty($element['elements'])) {
            $found = bcs_find_elementor_image($element['elements']);
            if ($found)
                return $found;
        }
    }
    return '';
}

add_action('elementor/widgets/widgets_registered', 'register_custom_elementor_widgets');

// =============================================================================
//  PERFORMANCE OPTIMIZATIONS (ALL VISITORS + MOBILE-SPECIFIC)
//  -------------------------------------------------------------------------
//  Most optimizations apply to ALL visitors (desktop + mobile) to maximize
//  both scores. Only Lenis removal is mobile-specific.
//
//  Caveat: if a page-cache plugin (WP Rocket / W3TC / LiteSpeed) is active,
//  enable "separate mobile cache" if any mobile-only hooks are re-added.
// =============================================================================

function bcs_is_mobile_request()
{
    return function_exists('wp_is_mobile') && wp_is_mobile();
}

/**
 * Cek apakah halaman saat ini sedang dibuka di Elementor editor/preview.
 * Digunakan sebagai guard di semua performance optimizations supaya
 * tidak mengganggu Elementor live editor (yang jalan di frontend, bukan admin).
 */
function bcs_is_elementor_preview()
{
    // Elementor preview iframe
    if (isset($_GET['elementor-preview']))
        return true;
    // Elementor editor mode
    if (isset($_GET['action']) && $_GET['action'] === 'elementor')
        return true;
    // Elementor API class check (kalau sudah ter-init)
    if (class_exists('\\Elementor\\Plugin')) {
        $instance = \Elementor\Plugin::$instance;
        if ($instance && !empty($instance->preview) && method_exists($instance->preview, 'is_preview_mode') && $instance->preview->is_preview_mode()) {
            return true;
        }
        if ($instance && !empty($instance->editor) && method_exists($instance->editor, 'is_edit_mode') && $instance->editor->is_edit_mode()) {
            return true;
        }
    }
    return false;
}

// 1) Mobile-only enqueue surgery (priority 100 = runs AFTER my_theme_scripts).
//    Lenis sudah di-disable secara global, jadi hanya Lucide move yang tersisa.
function bcs_mobile_enqueue_surgery()
{
    if (is_admin() || !bcs_is_mobile_request() || bcs_is_elementor_preview())
        return;

    // Move Lucide Icons from <head> to footer on mobile so it doesn't
    // block first paint. Desktop keeps the original head position.
    if (wp_script_is('lucide-js', 'enqueued')) {
        wp_dequeue_script('lucide-js');
        wp_deregister_script('lucide-js');
        wp_register_script('lucide-js', 'https://unpkg.com/lucide@latest', array(), null, true);
        wp_enqueue_script('lucide-js');
    }
}
add_action('wp_enqueue_scripts', 'bcs_mobile_enqueue_surgery', 100);

// 2) ALL VISITORS: resource hints — lets DNS+TLS overlap with HTML parse.
function bcs_resource_hints($urls, $relation_type)
{
    if ('preconnect' !== $relation_type)
        return $urls;
    $urls[] = array('href' => 'https://fonts.googleapis.com', 'crossorigin');
    $urls[] = array('href' => 'https://fonts.gstatic.com', 'crossorigin');
    $urls[] = array('href' => 'https://cdn.jsdelivr.net', 'crossorigin');
    $urls[] = array('href' => 'https://unpkg.com', 'crossorigin');
    return $urls;
}
add_filter('wp_resource_hints', 'bcs_resource_hints', 10, 2);

// 3) ALL VISITORS: WP-core bloat strip — saves ~30KB JS + several render-blocks.
function bcs_strip_bloat()
{
    if (is_admin() || bcs_is_elementor_preview())
        return;

    // Emoji detection script + emoji styles (~15KB, render-blocking)
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    // wp-embed.min.js (~3KB, only used inside Gutenberg block embeds)
    add_action('wp_footer', function () {
        wp_dequeue_script('wp-embed');
    });

    // <head> noise: RSD link, wlwmanifest, generator meta, shortlink, extra feeds
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'feed_links_extra', 3);

    // oEmbed discovery + oEmbed host JS (only used by Gutenberg/external embeds)
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
}
add_action('init', 'bcs_strip_bloat');

// 4) ALL VISITORS: image attribute hints — loading=lazy + decoding=async.
//    Native browser lazy loading for all WordPress-generated <img> tags.
//    decoding=async is a pure hint — never blocks layout.
function bcs_image_attrs($attr)
{
    if (!isset($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    if (!isset($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'bcs_image_attrs', 10, 1);

// 5) ALL VISITORS: collapse 3 separate Google Fonts requests into 1 + trim weights.
//    Original loads Boldonse + Figtree(6 weights) + Plus Jakarta(4 weights) as 3
//    blocking CSS files. Replace with ONE combined request.
//    Saves ~150KB woff2 + 2 render-blocking CSS + 2 DNS+TLS round-trips.
function bcs_combine_fonts()
{
    if (is_admin() || bcs_is_elementor_preview())
        return;

    wp_dequeue_style('boldonse-font');
    wp_dequeue_style('figtree-font');
    wp_dequeue_style('plus-jakarta-sans-font');

    wp_enqueue_style(
        'bcs-combined-fonts',
        'https://fonts.googleapis.com/css2'
        . '?family=Boldonse'
        . '&family=Figtree:wght@400;600;700'
        . '&family=Plus+Jakarta+Sans:wght@400;600;700;800'
        . '&display=swap',
        array(),
        null
    );
}
add_action('wp_enqueue_scripts', 'bcs_combine_fonts', 101);

// 6) ALL VISITORS: defer non-critical JS so they download in parallel without
//    blocking the parser. Keeps execution order via 'defer' attribute.
//    These scripts are not needed for first paint on ANY device:
//      - lucide-js   : icons (lazy-rendered after DOMContentLoaded)
//      - swiper-js   : sliders (below the fold)
//      - new-main-js : custom widget init (runs after DOMContentLoaded)
function bcs_defer_js($tag, $handle)
{
    if (is_admin() || bcs_is_elementor_preview())
        return $tag;
    static $defer = array('lucide-js', 'swiper-js', 'new-main-js');
    if (in_array($handle, $defer, true) && strpos($tag, ' defer') === false) {
        $tag = str_replace(' src=', ' defer src=', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'bcs_defer_js', 10, 2);

// 7) ALL VISITORS: load Swiper's CSS asynchronously via the media=print swap trick.
//    The browser fetches it without blocking first paint, then promotes it to
//    the real stylesheet on load. Sliders are below-fold, so the brief
//    unstyled flash is invisible.
function bcs_async_css($tag, $handle)
{
    if (is_admin() || bcs_is_elementor_preview())
        return $tag;
    static $async = array('swiper-css');
    if (in_array($handle, $async, true)) {
        $tag = str_replace(
            "rel='stylesheet'",
            "rel='stylesheet' media='print' onload=\"this.media='all'\"",
            $tag
        );
        $tag = str_replace(
            'rel="stylesheet"',
            'rel="stylesheet" media="print" onload="this.media=\'all\'"',
            $tag
        );
    }
    return $tag;
}
add_filter('style_loader_tag', 'bcs_async_css', 10, 2);

// 8) ALL VISITORS: preload the LCP image with fetchpriority=high.
//    Biggest single PSI win because LCP usually IS the hero image.
//    Auto-detection priority:
//      1. Manual override via constant BCS_HERO_PRELOAD_URL (define in wp-config.php)
//      2. Post's featured (thumbnail) image
//      3. First image found by bcs_find_elementor_image() walker over Elementor data
//    Falls back to no-op if nothing found, so it can never make things worse.
function bcs_preload_lcp()
{
    if (!is_singular() || bcs_is_elementor_preview())
        return;

    $img_url = '';

    // 1. Manual override
    if (defined('BCS_HERO_PRELOAD_URL') && BCS_HERO_PRELOAD_URL) {
        $img_url = BCS_HERO_PRELOAD_URL;
    }

    $post_id = get_queried_object_id();
    if (!$post_id)
        return;

    // 2. Featured thumbnail
    if (!$img_url && has_post_thumbnail($post_id)) {
        $img_url = get_the_post_thumbnail_url($post_id, 'large');
    }

    // 3. Walk Elementor data for first hero/image
    if (!$img_url) {
        $raw = get_post_meta($post_id, '_elementor_data', true);
        if ($raw) {
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $img_url = bcs_find_elementor_image($data);
            }
        }
    }

    if (!$img_url)
        return;

    printf(
        '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
        esc_url($img_url)
    );
}
add_action('wp_head', 'bcs_preload_lcp', 1);

// 9) IntersectionObserver lazy loading for data-bg (background-image) and
//    data-lazy-src (video). Injected to footer — does NOT touch jQuery or
//    Lenis so scroll is unaffected. ~800 bytes.
function bcs_lazy_observer_js()
{
    if (is_admin() || bcs_is_elementor_preview())
        return;
    ?>
    <script id="bcs-lazy-observer">
        (function () {
            if (!('IntersectionObserver' in window)) return;

            /* ── Background images with data-bg ────────────────────────── */
            var bgObs = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) {
                        var el = e.target;
                        el.style.backgroundImage = "url('" + el.getAttribute('data-bg') + "')";
                        el.classList.add('bg-loaded');
                        el.removeAttribute('data-bg');
                        obs.unobserve(el);
                    }
                });
            }, { rootMargin: '200px' });

            document.querySelectorAll('[data-bg]').forEach(function (el) {
                bgObs.observe(el);
            });

            /* ── Videos with data-lazy-video ───────────────────────────── */
            var vidObs = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) {
                        var vid = e.target;
                        vid.querySelectorAll('source[data-src]').forEach(function (s) {
                            s.src = s.getAttribute('data-src');
                            s.removeAttribute('data-src');
                        });
                        vid.load();
                        vid.play().catch(function () { });
                        obs.unobserve(vid);
                    }
                });
            }, { rootMargin: '200px' });

            document.querySelectorAll('video[data-lazy-video]').forEach(function (v) {
                vidObs.observe(v);
            });
        })();
    </script>
    <?php
}
add_action('wp_footer', 'bcs_lazy_observer_js', 5);

// 10) CSS placeholder for data-bg elements — dark bg until image loads.
function bcs_lazy_placeholder_css()
{
    if (is_admin() || bcs_is_elementor_preview())
        return;
    ?>
    <style id="bcs-lazy-css">
        [data-bg] {
            background-color: #111;
            background-image: none !important
        }

        .bg-loaded {
            animation: bcsReveal .35s ease-out
        }

        @keyframes bcsReveal {
            from {
                opacity: .65
            }

            to {
                opacity: 1
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'bcs_lazy_placeholder_css', 99);

// 11) Helper: generate <img> with srcset, sizes, width, height, loading, decoding.
//     Usage: echo bcs_img_tag($attachment_id, 'large', 'my-class', 'Alt text');
function bcs_img_tag($attachment_id, $size = 'large', $class = '', $alt = '', $lazy = true)
{
    if (!$attachment_id)
        return '';

    $attrs = array(
        'class' => $class,
        'decoding' => 'async',
        'loading' => $lazy ? 'lazy' : 'eager',
    );
    if (!$lazy) {
        $attrs['fetchpriority'] = 'high';
    }
    if ($alt) {
        $attrs['alt'] = $alt;
    }

    return wp_get_attachment_image($attachment_id, $size, false, $attrs);
}

// 12) ALL VISITORS: strip unused CSS from non-shop pages.
//     PageSpeed flagged 144 KiB of unused CSS — dashicons, WooCommerce,
//     YITH icons, and FontAwesome are only needed on WooCommerce pages.
function bcs_dequeue_unused_css()
{
    if (is_admin() || bcs_is_elementor_preview())
        return;

    // Dashicons: only needed when admin bar is showing (logged-in users).
    // For non-logged-in visitors it's pure waste (34 KiB).
    if (!is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }

    // WooCommerce + YITH + FontAwesome: only needed on shop/product/cart/checkout.
    $is_shop_page = false;
    if (function_exists('is_woocommerce')) {
        $is_shop_page = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
    }
    if (!$is_shop_page) {
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_style('wc-blocks-style');
        wp_dequeue_style('yith-icon-font');
        wp_dequeue_style('yith-plugin-fw-icon-font');
        wp_dequeue_style('font-awesome');

        // WooCommerce JS also not needed on non-shop pages
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('wc-cart-fragments');
        wp_dequeue_script('wc-add-to-cart');
    }
}
add_action('wp_enqueue_scripts', 'bcs_dequeue_unused_css', 999);

// 13) ALL VISITORS: add fetchpriority=high to the first attachment image on
//     singular pages (the hero). This tells the browser to prioritize the
//     LCP image over other resources.
function bcs_hero_fetchpriority($attr, $attachment, $size)
{
    static $first = true;
    if ($first && !is_admin() && !bcs_is_elementor_preview() && is_singular()) {
        // Only the very first image on the page gets priority
        $attr['fetchpriority'] = 'high';
        $attr['loading'] = 'eager';
        $first = false;
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'bcs_hero_fetchpriority', 5, 3);


// Search by TITLE only — prevents irrelevant pages from appearing
// e.g. searching "Drone" will only match pages/posts with "Drone" in their title
add_action('pre_get_posts', function ($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        // Allow all post types (pages + posts)
        $query->set('post_type', array('post', 'page'));

        // Search title only (remove content matching)
        add_filter('posts_search', function ($search, $wp_query) {
            global $wpdb;
            if (empty($search) || !$wp_query->is_search() || !$wp_query->is_main_query()) {
                return $search;
            }
            $q = $wp_query->query_vars;
            $search_terms = isset($q['search_terms']) ? $q['search_terms'] : array();
            if (empty($search_terms)) {
                return $search;
            }
            $searchand = '';
            $search = ' AND (';
            foreach ($search_terms as $term) {
                $like = '%' . $wpdb->esc_like($term) . '%';
                $search .= $searchand . $wpdb->prepare("($wpdb->posts.post_title LIKE %s)", $like);
                $searchand = ' AND ';
            }
            $search .= ')';
            return $search;
        }, 10, 2);
    }
});

// Hide default post title on Elementor pages
add_filter('the_title', function ($title, $id) {
    // Only hide the title for the main queried page/post, not for posts from custom WP_Query (e.g. news widget)
    if (is_singular() && in_the_loop() && is_main_query() && did_action('elementor/loaded') && $id === get_queried_object_id()) {
        $document = \Elementor\Plugin::$instance->documents->get($id);
        if ($document && $document->is_built_with_elementor()) {
            return '';
        }
    }
    return $title;
}, 10, 2);


// =============================================================================
//  MODULAR INCLUDES
//  Kode dipecah ke file terpisah supaya functions.php tetap ringan.
//  Jangan hapus require_once di bawah ini!
// =============================================================================
require_once __DIR__ . '/inc/bcs-booking.php';   // Amelia + Sheets + WhatsApp booking
require_once __DIR__ . '/inc/bcs-wc-ui.php';     // WC order UI + store coming soon + status poll