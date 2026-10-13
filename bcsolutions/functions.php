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

// ===== STUDIO START =====

// 5.1 Konstanta, helper umum, dan cache URL halaman
if (!defined('STUDIO_ASSETS_DIR')) {
    define('STUDIO_ASSETS_DIR', get_stylesheet_directory() . '/studio-assets/');
}
if (!defined('STUDIO_ASSETS_URL')) {
    define('STUDIO_ASSETS_URL', get_stylesheet_directory_uri() . '/studio-assets/');
}

function studio_log($message, $context = []) {
    if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $ctx = !empty($context) ? ' ' . wp_json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        error_log('[Studio] ' . $message . $ctx);
    }
}

function studio_is_studio_page() {
    return is_page_template('template-studio-editor.php') || is_page_template('template-studio-client.php');
}

function studio_build_page_url($template_name, $query_args = []) {
    static $memo = [];
    if (!isset($memo[$template_name])) {
        $t_key = 'studio_page_url_' . md5($template_name);
        $cached = get_transient($t_key);
        if (is_string($cached)) {
            $memo[$template_name] = $cached;
        } else {
            $pages = get_pages([
                'meta_key' => '_wp_page_template',
                'meta_value' => $template_name,
                'post_status' => 'publish',
                'number' => 1
            ]);
            $url = !empty($pages) ? (string) get_permalink($pages[0]->ID) : '';
            $memo[$template_name] = $url;
            set_transient($t_key, $url, 12 * HOUR_IN_SECONDS);
        }
    }
    $base_url = $memo[$template_name];
    if (!$base_url) {
        return '';
    }
    return !empty($query_args) ? add_query_arg($query_args, $base_url) : $base_url;
}

function studio_clear_page_url_cache() {
    delete_transient('studio_page_url_' . md5('template-studio-editor.php'));
    delete_transient('studio_page_url_' . md5('template-studio-client.php'));
}
add_action('save_post_page', 'studio_clear_page_url_cache');
add_action('deleted_post', 'studio_clear_page_url_cache');

// 5.2 Database v1.1 (studio_galleries + studio_files)
function studio_ensure_cache_dir() {
    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/studio-cache';
    if (!file_exists($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }
    $index_file = $cache_dir . '/index.html';
    if (!file_exists($index_file)) {
        @file_put_contents($index_file, '');
    }
    $htaccess_file = $cache_dir . '/.htaccess';
    if (!file_exists($htaccess_file)) {
        $ht = "Options -Indexes\n<IfModule mod_headers.c>\n  Header set Cache-Control \"public, max-age=2592000, immutable\"\n</IfModule>\n";
        @file_put_contents($htaccess_file, $ht);
    }
    return $cache_dir;
}

function studio_install_db() {
    global $wpdb;
    $table_galleries = $wpdb->prefix . 'studio_galleries';
    $table_files = $wpdb->prefix . 'studio_files';
    $charset_collate = $wpdb->get_charset_collate();

    $sql_galleries = "CREATE TABLE $table_galleries (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        token char(32) NOT NULL,
        title varchar(150) NOT NULL,
        source_folder_id varchar(100) NOT NULL,
        result_folder_id varchar(100) DEFAULT '',
        max_select int(11) NOT NULL DEFAULT 0,
        status varchar(20) NOT NULL DEFAULT 'unstarted',
        selected_ids longtext NOT NULL,
        selected_meta longtext NULL,
        selected_count int(10) unsigned NOT NULL DEFAULT 0,
        note text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        submitted_at datetime DEFAULT NULL,
        reopened_at datetime DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY token (token),
        KEY status (status),
        KEY updated_at (updated_at)
    ) $charset_collate;";

    $sql_files = "CREATE TABLE $table_files (
        file_id varchar(100) NOT NULL,
        folder_id varchar(100) NOT NULL,
        name varchar(255) NOT NULL,
        mime varchar(100) NOT NULL DEFAULT '',
        thumb_link text NULL,
        position int(10) unsigned NOT NULL DEFAULT 0,
        synced_at datetime NOT NULL,
        PRIMARY KEY  (folder_id, file_id),
        KEY file_id (file_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_galleries);
    dbDelta($sql_files);

    // Hitung selected_count awal untuk baris galeri lama jika masih 0
    $legacy_rows = $wpdb->get_results("SELECT id, selected_ids, selected_count FROM $table_galleries");
    if ($legacy_rows) {
        foreach ($legacy_rows as $r) {
            $ids = json_decode((string) $r->selected_ids, true);
            $cnt = is_array($ids) ? count($ids) : 0;
            if ((int) $r->selected_count !== $cnt) {
                $wpdb->update($table_galleries, ['selected_count' => $cnt], ['id' => $r->id]);
            }
        }
    }

    // E5: Bersihkan transient lama sekali jalan
    $wpdb->query(
        "DELETE FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_studio_drive_v3_%'
            OR option_name LIKE '_transient_timeout_studio_drive_v3_%'
            OR option_name LIKE '_transient_studio_fmeta_%'
            OR option_name LIKE '_transient_timeout_studio_fmeta_%'
            OR option_name LIKE '_transient_studio_tmap_%'
            OR option_name LIKE '_transient_timeout_studio_tmap_%'"
    );

    studio_ensure_cache_dir();
    update_option('studio_db_version', '1.1');
}

add_action('init', function() {
    if (get_option('studio_db_version') !== '1.1') {
        studio_install_db();
    }
    if (!get_option('studio_cron_installed')) {
        if (!wp_next_scheduled('studio_clean_cache_cron')) {
            wp_schedule_event(time(), 'daily', 'studio_clean_cache_cron');
        }
        update_option('studio_cron_installed', 1, 'yes');
    }
    if (!get_option('studio_names_backfilled')) {
        if (!wp_next_scheduled('studio_run_backfill_names')) {
            wp_schedule_single_event(time() + 2, 'studio_run_backfill_names');
        }
    }
});

// 5.3 Akses editor: kunci otomatis
function studio_get_editor_key() {
    $key = get_option('studio_editor_key');
    if ($key) {
        return $key;
    }
    if (defined('STUDIO_EDITOR_KEY') && STUDIO_EDITOR_KEY) {
        return STUDIO_EDITOR_KEY;
    }
    require_once ABSPATH . 'wp-includes/pluggable.php';
    $key = wp_generate_password(40, false, false);
    update_option('studio_editor_key', $key, 'no');
    return $key;
}

function studio_check_rate_limit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $transient_name = 'studio_fail_' . md5($ip);
    $fails = (int) get_transient($transient_name);
    return $fails < 30;
}

function studio_record_fail() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $transient_name = 'studio_fail_' . md5($ip);
    $fails = (int) get_transient($transient_name);
    set_transient($transient_name, $fails + 1, 15 * MINUTE_IN_SECONDS);
}

function studio_verify_editor(WP_REST_Request $request) {
    if (current_user_can('manage_options')) {
        return true;
    }

    if (!studio_check_rate_limit()) {
        return new WP_Error('too_many_requests', 'Terlalu banyak percobaan gagal. Coba lagi nanti.', ['status' => 429]);
    }

    $key = $request->get_header('X-Studio-Key');
    if ($key && hash_equals(studio_get_editor_key(), $key)) {
        return true;
    }

    studio_record_fail();
    return new WP_Error('unauthorized', 'Kunci editor tidak valid atau sudah diganti.', ['status' => 401]);
}

// C9: Header respons REST API studio/v1 (dukung ETag/304 khusus /client/photos dan /client/result, lainnya no-store)
add_filter('rest_post_dispatch', function($response, $server, $request) {
    $route = $request->get_route();
    if (strpos($route, '/studio/v1') === 0 && $response instanceof WP_REST_Response) {
        $headers = $response->get_headers();
        if (($route === '/studio/v1/client/photos' || $route === '/studio/v1/client/result') && !empty($headers['ETag'])) {
            $response->header('Cache-Control', 'private, no-cache, must-revalidate');
        } else {
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        }
    }
    return $response;
}, 10, 3);

// 5.4 REST API studio/v1
add_action('rest_api_init', function() {
    // Editor - Khusus Admin
    register_rest_route('studio/v1', '/editor/key', [
        'methods' => 'GET',
        'callback' => function() {
            $key = studio_get_editor_key();
            $url = studio_build_page_url('template-studio-editor.php', ['key' => $key]);
            return rest_ensure_response(['key' => $key, 'url' => $url]);
        },
        'permission_callback' => function() { return current_user_can('manage_options'); }
    ]);

    register_rest_route('studio/v1', '/editor/key/rotate', [
        'methods' => 'POST',
        'callback' => function() {
            require_once ABSPATH . 'wp-includes/pluggable.php';
            $key = wp_generate_password(40, false, false);
            update_option('studio_editor_key', $key, 'no');
            $url = studio_build_page_url('template-studio-editor.php', ['key' => $key]);
            return rest_ensure_response(['key' => $key, 'url' => $url]);
        },
        'permission_callback' => function() { return current_user_can('manage_options'); }
    ]);

    // Fase A: Endpoint Diagnostik Drive API (Khusus Admin)
    register_rest_route('studio/v1', '/editor/diagnostics', [
        'methods' => 'GET',
        'callback' => 'studio_api_editor_diagnostics',
        'permission_callback' => function() { return current_user_can('manage_options'); }
    ]);

    // Editor Endpoints
    register_rest_route('studio/v1', '/editor/galleries', [
        'methods' => 'GET',
        'callback' => 'studio_api_editor_get_galleries',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/galleries', [
        'methods' => 'POST',
        'callback' => 'studio_api_editor_create_gallery',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/galleries/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'studio_api_editor_get_gallery',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/galleries/(?P<id>\d+)', [
        'methods' => 'PATCH',
        'callback' => 'studio_api_editor_update_gallery',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/galleries/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'studio_api_editor_delete_gallery',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/galleries/(?P<id>\d+)/reopen', [
        'methods' => 'POST',
        'callback' => 'studio_api_editor_reopen_gallery',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/galleries/(?P<id>\d+)/regenerate-token', [
        'methods' => 'POST',
        'callback' => 'studio_api_editor_regenerate_token',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/galleries/(?P<id>\d+)/resync', [
        'methods' => 'POST',
        'callback' => 'studio_api_editor_resync_gallery',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/galleries/(?P<id>\d+)/warm-thumbs', [
        'methods' => 'POST',
        'callback' => 'studio_api_editor_warm_thumbs',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/drive/resolve', [
        'methods' => 'POST',
        'callback' => 'studio_api_editor_drive_resolve',
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/settings', [
        'methods' => 'GET',
        'callback' => function() { return rest_ensure_response(['whatsapp' => get_option('studio_whatsapp', '')]); },
        'permission_callback' => 'studio_verify_editor'
    ]);
    register_rest_route('studio/v1', '/editor/settings', [
        'methods' => 'PATCH',
        'callback' => function(WP_REST_Request $request) {
            $wa = preg_replace('/[^0-9]/', '', (string) $request->get_param('whatsapp'));
            update_option('studio_whatsapp', $wa);
            return rest_ensure_response(['ok' => true, 'whatsapp' => $wa]);
        },
        'permission_callback' => 'studio_verify_editor'
    ]);

    // Client Endpoints
    register_rest_route('studio/v1', '/client/gallery', [
        'methods' => 'GET',
        'callback' => 'studio_api_client_get_gallery',
        'permission_callback' => 'studio_verify_client'
    ]);
    register_rest_route('studio/v1', '/client/photos', [
        'methods' => 'GET',
        'callback' => 'studio_api_client_get_photos',
        'permission_callback' => 'studio_verify_client'
    ]);
    register_rest_route('studio/v1', '/client/result', [
        'methods' => 'GET',
        'callback' => 'studio_api_client_get_result',
        'permission_callback' => 'studio_verify_client'
    ]);
    register_rest_route('studio/v1', '/client/selection', [
        'methods' => ['PUT', 'POST'],
        'callback' => 'studio_api_client_update_selection',
        'permission_callback' => 'studio_verify_client'
    ]);
    register_rest_route('studio/v1', '/client/submit', [
        'methods' => 'POST',
        'callback' => 'studio_api_client_submit',
        'permission_callback' => 'studio_verify_client'
    ]);
});

// Client verification
function studio_verify_client(WP_REST_Request $request) {
    global $wpdb;
    $token = $request->get_header('X-Studio-Token');
    if (!$token) return new WP_Error('unauthorized', 'Missing token.', ['status' => 401]);

    $gallery = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}studio_galleries WHERE token = %s", $token));
    if (!$gallery) return new WP_Error('unauthorized', 'Invalid token.', ['status' => 401]);

    $request->set_param('gallery', $gallery);
    return true;
}

// Paksa IPv4 (CURL_IPRESOLVE_V4) untuk domain Google guna mencegah timeout IPv6 (25s -> 0.5s)
add_action('http_api_curl', function($handle, $parsed_args, $url) {
    if (!is_string($url)) return;
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return;
    if (
        stripos($host, 'googleapis.com') !== false ||
        stripos($host, 'googleusercontent.com') !== false ||
        stripos($host, 'google.com') !== false
    ) {
        if (defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($handle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    }
}, 10, 3);

// C7: Helper HTTP GET ke Google tanpa retry sslverify=false di produksi dan tanpa retry saat timeout
function studio_remote_get($url, $args = []) {
    $default_args = [
        'timeout' => 15,
        'redirection' => 5,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8'
        ]
    ];
    $req_args = wp_parse_args($args, $default_args);
    if (!empty($args['headers']) && is_array($args['headers'])) {
        $req_args['headers'] = array_merge($default_args['headers'], $args['headers']);
    }

    $resp = wp_remote_get($url, $req_args);

    // Retry sslverify=false HANYA di environment local dan bukan error timeout
    if (is_wp_error($resp) && function_exists('wp_get_environment_type') && wp_get_environment_type() === 'local') {
        $err_msg = $resp->get_error_message();
        if (stripos($err_msg, 'timed out') === false && stripos($err_msg, 'timeout') === false) {
            $req_args['sslverify'] = false;
            $resp = wp_remote_get($url, $req_args);
        }
    }

    // Jika ditolak 403 dan belum menyertakan Referer, coba ulang dengan Referer situs
    if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 403 && empty($req_args['headers']['Referer'])) {
        $req_args['headers']['Referer'] = home_url('/');
        $resp = wp_remote_get($url, $req_args);
    }

    return $resp;
}

// Helper validasi apakah byte awal file/respons benar-benar binary image (JPEG/PNG/WebP/GIF)
function studio_is_valid_image_bytes($bytes) {
    if (!is_string($bytes) || strlen($bytes) <= 100) return false;
    if (strncmp($bytes, "\xFF\xD8\xFF", 3) === 0) return true; // JPEG
    if (strncmp($bytes, "\x89PNG\r\n\x1a\n", 8) === 0) return true; // PNG
    if (strncmp($bytes, "RIFF", 4) === 0 && substr($bytes, 8, 4) === "WEBP") return true; // WebP
    if (strncmp($bytes, "GIF87a", 6) === 0 || strncmp($bytes, "GIF89a", 6) === 0) return true; // GIF
    return false;
}

// Helper pembuat SVG Demo
function studio_make_demo_photo($i, $folder_id) {
    $name = 'IMG_' . str_pad(1200 + $i * 7, 4, '0', STR_PAD_LEFT) . '.jpg';
    $h = ($i * 37) % 360;
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="hsl(' . $h . ',55%,62%)"/><stop offset="1" stop-color="hsl(' . ($h + 50) . ',60%,38%)"/></linearGradient></defs><rect width="600" height="600" fill="url(#g)"/><text x="300" y="316" font-size="40" text-anchor="middle" fill="#fff" font-family="sans-serif">' . $name . '</text></svg>';
    return [
        'id' => 'demo' . $i . $folder_id,
        'name' => $name,
        'mime' => 'image/svg+xml',
        'thumbnailLink' => 'data:image/svg+xml,' . rawurlencode($svg)
    ];
}

// C1 & C2: Daftar putih lebar thumbnail, path cache statis, dan URL bertanda tangan stabil harian
function studio_allowed_widths() {
    return [200, 400, 800, 1600];
}

function studio_snap_width($w) {
    $w = (int) $w;
    $allowed = studio_allowed_widths();
    $best = 400;
    $min_diff = PHP_INT_MAX;
    foreach ($allowed as $aw) {
        $diff = abs($w - $aw);
        if ($diff < $min_diff) {
            $min_diff = $diff;
            $best = $aw;
        }
    }
    return $best;
}

function studio_thumb_cache_info($file_id, $w) {
    $w = studio_snap_width($w);
    $hash = substr(hash_hmac('sha256', "$file_id|$w", wp_salt('auth')), 0, 32);
    $shard = substr($hash, 0, 2);
    $upload_dir = wp_upload_dir();
    $rel = "/studio-cache/{$shard}/{$hash}.jpg";
    return [
        'w' => $w,
        'hash' => $hash,
        'shard' => $shard,
        'dir' => $upload_dir['basedir'] . "/studio-cache/{$shard}",
        'path' => $upload_dir['basedir'] . $rel,
        'url' => $upload_dir['baseurl'] . $rel,
    ];
}

function studio_sign_url($file_id, $w, $folder_id = '') {
    if (strpos($file_id, 'demo') === 0) return '';
    $w = studio_snap_width($w);
    $exp = (intdiv(time(), DAY_IN_SECONDS) + 3) * DAY_IN_SECONDS;
    $sig = hash_hmac('sha256', "$file_id|$w|$exp", wp_salt('auth'));
    $args = [
        'studio_thumb' => $file_id,
        'w' => $w,
        'exp' => $exp,
        'sig' => $sig
    ];
    if (!empty($folder_id)) {
        $args['f'] = $folder_id;
    }
    return add_query_arg($args, home_url('/'));
}

function studio_get_thumb_url($file_id, $w, $folder_id = '') {
    if (strpos($file_id, 'demo') === 0) return '';
    $info = studio_thumb_cache_info($file_id, $w);
    if (file_exists($info['path']) && filesize($info['path']) > 100) {
        return $info['url'];
    }
    return studio_sign_url($file_id, $info['w'], $folder_id);
}

// B2: Sinkronisasi penuh folder Google Drive ke tabel persisten studio_files
function studio_sync_folder($folder_id) {
    global $wpdb;
    if (empty($folder_id)) {
        return new WP_Error('empty_folder', 'ID folder kosong.');
    }

    $table_files = $wpdb->prefix . 'studio_files';
    $sync_time = current_time('mysql');

    // Mode Demo bila STUDIO_GOOGLE_API_KEY belum diset
    if (!defined('STUDIO_GOOGLE_API_KEY') || empty(STUDIO_GOOGLE_API_KEY)) {
        $index = [];
        for ($i = 0; $i < 24; $i++) {
            $d = studio_make_demo_photo($i, $folder_id);
            $wpdb->replace($table_files, [
                'file_id' => $d['id'],
                'folder_id' => $folder_id,
                'name' => $d['name'],
                'mime' => $d['mime'],
                'thumb_link' => $d['thumbnailLink'],
                'position' => $i,
                'synced_at' => $sync_time
            ]);
            $index[$d['id']] = [
                'id' => $d['id'],
                'name' => $d['name'],
                'mime' => $d['mime'],
                'thumb_link' => $d['thumbnailLink'],
                'position' => $i,
                'synced_at' => $sync_time
            ];
        }
        update_option('studio_sync_' . md5($folder_id), [
            'time' => time(),
            'synced_at' => $sync_time,
            'count' => count($index)
        ], 'no');
        return $index;
    }

    $include_octet = (bool) apply_filters('studio_include_octet_stream', (bool) get_option('studio_include_octet_stream', false));
    $mime_clause = $include_octet
        ? "(mimeType contains 'image/' or mimeType = 'application/octet-stream')"
        : "mimeType contains 'image/'";

    $raw_files = [];
    $page_token = '';

    do {
        $q = "'" . $folder_id . "' in parents and {$mime_clause} and trashed=false";
        $query_args = [
            'q' => $q,
            'pageSize' => 1000,
            'orderBy' => 'name',
            'fields' => 'nextPageToken,files(id,name,mimeType,thumbnailLink)',
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
            'key' => STUDIO_GOOGLE_API_KEY,
        ];
        if (!empty($page_token)) {
            $query_args['pageToken'] = $page_token;
        }

        $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($query_args, '', '&', PHP_QUERY_RFC3986);
        $response = studio_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response)) {
            studio_log('Drive files.list WP_Error', [
                'folder_id' => $folder_id,
                'error' => $response->get_error_message()
            ]);
            return new WP_Error('drive_unavailable', 'Gagal menghubungi Google Drive: ' . $response->get_error_message(), ['status' => 503]);
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($http_code !== 200 || !is_array($body) || isset($body['error'])) {
            $err_reason = $body['error']['errors'][0]['reason'] ?? ($body['error']['status'] ?? 'unknown');
            $err_msg = $body['error']['message'] ?? ('HTTP ' . $http_code);
            studio_log('Drive files.list HTTP/API error', [
                'folder_id' => $folder_id,
                'http_code' => $http_code,
                'reason' => $err_reason,
                'message' => $err_msg
            ]);
            return new WP_Error('drive_unavailable', 'Daftar foto belum bisa dimuat (' . $err_reason . ').', ['status' => 503]);
        }

        foreach (($body['files'] ?? []) as $f) {
            if (empty($f['id']) || empty($f['name'])) continue;
            $tUrl = !empty($f['thumbnailLink']) ? preg_replace('/=s\d+.*$/', '', $f['thumbnailLink']) : '';
            $raw_files[] = [
                'id' => (string) $f['id'],
                'name' => (string) $f['name'],
                'mime' => (string) ($f['mimeType'] ?? ''),
                'thumb_link' => $tUrl
            ];
        }

        $page_token = $body['nextPageToken'] ?? '';
    } while ($page_token);

    // Tulis ke studio_files per batch (INSERT ... ON DUPLICATE KEY UPDATE)
    $index = [];
    $chunks = array_chunk($raw_files, 200);
    $pos = 0;

    foreach ($chunks as $chunk) {
        $values_sql = [];
        $params = [];
        foreach ($chunk as $item) {
            $values_sql[] = '(%s, %s, %s, %s, %s, %d, %s)';
            $params[] = $item['id'];
            $params[] = $folder_id;
            $params[] = $item['name'];
            $params[] = $item['mime'];
            $params[] = $item['thumb_link'];
            $params[] = $pos;
            $params[] = $sync_time;

            $index[$item['id']] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'mime' => $item['mime'],
                'thumb_link' => $item['thumb_link'],
                'position' => $pos,
                'synced_at' => $sync_time
            ];
            $pos++;
        }

        $sql = "INSERT INTO {$table_files} (file_id, folder_id, name, mime, thumb_link, position, synced_at)
                VALUES " . implode(', ', $values_sql) . "
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    mime = VALUES(mime),
                    thumb_link = IF(VALUES(thumb_link) != '', VALUES(thumb_link), thumb_link),
                    position = VALUES(position),
                    synced_at = VALUES(synced_at)";
        $wpdb->query($wpdb->prepare($sql, $params));
    }

    // Hapus baris file yang sudah tidak ada di folder Drive
    if (empty($raw_files)) {
        $wpdb->delete($table_files, ['folder_id' => $folder_id]);
    } else {
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_files} WHERE folder_id = %s AND synced_at < %s",
            $folder_id,
            $sync_time
        ));
    }

    update_option('studio_sync_' . md5($folder_id), [
        'time' => time(),
        'synced_at' => $sync_time,
        'count' => count($index)
    ], 'no');

    // Perbarui snapshot selected_meta pada galeri yang memakai folder ini
    if (!empty($index)) {
        $galleries = $wpdb->get_results($wpdb->prepare(
            "SELECT id, selected_ids, selected_meta FROM {$wpdb->prefix}studio_galleries WHERE source_folder_id = %s",
            $folder_id
        ));
        foreach ((array) $galleries as $g_row) {
            $s_ids = studio_sanitize_selected_ids(json_decode((string) $g_row->selected_ids, true) ?: []);
            if (empty($s_ids)) continue;
            $s_meta = json_decode((string) ($g_row->selected_meta ?: '{}'), true) ?: [];
            $changed = false;
            foreach ($s_ids as $sid) {
                if (isset($index[$sid]) && (!isset($s_meta[$sid]) || $s_meta[$sid] !== $index[$sid]['name'])) {
                    $s_meta[$sid] = $index[$sid]['name'];
                    $changed = true;
                }
            }
            if ($changed || $g_row->selected_meta === null) {
                $wpdb->update($wpdb->prefix . 'studio_galleries', [
                    'selected_meta' => wp_json_encode($s_meta, JSON_UNESCAPED_UNICODE),
                    'selected_count' => count($s_ids)
                ], ['id' => $g_row->id]);
            }
        }
    }

    // C4: Jadwalkan warm-up thumbnail di background
    studio_schedule_prefetch_thumbs($folder_id);

    return $index;
}

// C3: Background resync dengan anti-duplikat lock
function studio_schedule_resync_folder($folder_id) {
    $lock_key = 'studio_lock_sync_' . md5($folder_id);
    $locked_at = (int) get_option($lock_key, 0);
    if ($locked_at && (time() - $locked_at) < 60) {
        return;
    }
    update_option($lock_key, time(), 'no');
    if (!wp_next_scheduled('studio_resync_folder', [$folder_id])) {
        wp_schedule_single_event(time() + 1, 'studio_resync_folder', [$folder_id]);
    }
}

add_action('studio_resync_folder', function($folder_id) {
    $lock_key = 'studio_lock_sync_' . md5($folder_id);
    studio_sync_folder($folder_id);
    delete_option($lock_key);
});

// B3 & C3: Ambil indeks file folder dari tabel studio_files (stale-while-revalidate 5 menit)
function studio_get_file_index($folder_id, $force_refresh = false, &$is_stale = false) {
    global $wpdb;
    $is_stale = false;
    if (empty($folder_id)) return [];

    $table_files = $wpdb->prefix . 'studio_files';
    $sync_meta = get_option('studio_sync_' . md5($folder_id));

    if ($force_refresh) {
        $synced = studio_sync_folder($folder_id);
        if (!is_wp_error($synced)) {
            return $synced;
        }
    }

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT file_id, name, mime, thumb_link, position, synced_at
         FROM {$table_files}
         WHERE folder_id = %s
         ORDER BY position ASC, name ASC",
        $folder_id
    ));

    $has_cached_rows = !empty($rows);
    $has_known_empty_sync = is_array($sync_meta) && isset($sync_meta['count']) && (int) $sync_meta['count'] === 0 && !empty($sync_meta['time']);

    if ($has_cached_rows || $has_known_empty_sync) {
        $index = [];
        foreach ((array) $rows as $r) {
            $index[$r->file_id] = [
                'id' => $r->file_id,
                'name' => $r->name,
                'mime' => $r->mime,
                'thumb_link' => $r->thumb_link,
                'position' => (int) $r->position,
                'synced_at' => $r->synced_at
            ];
        }

        $last_time = is_array($sync_meta) && !empty($sync_meta['time']) ? (int) $sync_meta['time'] : 0;
        if (!$force_refresh && (time() - $last_time) > 5 * MINUTE_IN_SECONDS) {
            $is_stale = true;
            studio_schedule_resync_folder($folder_id);
        }

        return $index;
    }

    // Sync sinkron sekali jika belum ada data sama sekali di database
    $synced = studio_sync_folder($folder_id);
    if (is_wp_error($synced)) {
        return null;
    }
    return $synced;
}

// B6: Resolve nama paralel (6 sekaligus) via files.get untuk ID yang belum punya nama (tanpa download gambar)
function studio_resolve_missing_names_parallel(array $file_ids, $folder_id = '') {
    global $wpdb;
    $file_ids = array_values(array_unique(array_filter($file_ids, function($id) {
        return is_string($id) && $id !== '' && strpos($id, 'demo') !== 0;
    })));
    if (empty($file_ids)) return [];

    $resolved = [];
    $table_files = $wpdb->prefix . 'studio_files';

    // Cek dulu di tabel studio_files
    $placeholders = implode(',', array_fill(0, count($file_ids), '%s'));
    $db_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT file_id, name FROM {$table_files} WHERE file_id IN ($placeholders)",
        $file_ids
    ));
    foreach ((array) $db_rows as $r) {
        if (!empty($r->name) && $r->name !== $r->file_id) {
            $resolved[$r->file_id] = $r->name;
        }
    }

    $missing = array_values(array_diff($file_ids, array_keys($resolved)));
    if (empty($missing) || !defined('STUDIO_GOOGLE_API_KEY') || empty(STUDIO_GOOGLE_API_KEY)) {
        return $resolved;
    }

    $sync_time = current_time('mysql');
    $batches = array_chunk($missing, 6);

    foreach ($batches as $batch) {
        if (class_exists('WpOrg\Requests\Requests') || class_exists('Requests')) {
            $requests = [];
            foreach ($batch as $fid) {
                $url = 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fid) . '?' . http_build_query([
                    'fields' => 'id,name,mimeType,thumbnailLink',
                    'supportsAllDrives' => 'true',
                    'key' => STUDIO_GOOGLE_API_KEY
                ], '', '&', PHP_QUERY_RFC3986);
                $requests[$fid] = [
                    'url' => $url,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Referer' => home_url('/')
                    ],
                    'type' => 'GET'
                ];
            }

            $options = ['timeout' => 6, 'connect_timeout' => 4];
            $responses = class_exists('WpOrg\Requests\Requests')
                ? \WpOrg\Requests\Requests::request_multiple($requests, $options)
                : \Requests::request_multiple($requests, $options);

            foreach ($responses as $fid => $resp) {
                if (is_object($resp) && isset($resp->status_code) && (int) $resp->status_code === 200 && !empty($resp->body)) {
                    $body = json_decode($resp->body, true);
                    if (!empty($body['name']) && $body['name'] !== $fid) {
                        $resolved[$fid] = (string) $body['name'];
                        if ($folder_id) {
                            $tUrl = !empty($body['thumbnailLink']) ? preg_replace('/=s\d+.*$/', '', $body['thumbnailLink']) : '';
                            $wpdb->replace($table_files, [
                                'file_id' => $fid,
                                'folder_id' => $folder_id,
                                'name' => (string) $body['name'],
                                'mime' => (string) ($body['mimeType'] ?? ''),
                                'thumb_link' => $tUrl,
                                'position' => 999999,
                                'synced_at' => $sync_time
                            ]);
                        }
                    }
                }
            }
        } else {
            foreach ($batch as $fid) {
                $url = 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($fid) . '?' . http_build_query([
                    'fields' => 'id,name,mimeType,thumbnailLink',
                    'supportsAllDrives' => 'true',
                    'key' => STUDIO_GOOGLE_API_KEY
                ], '', '&', PHP_QUERY_RFC3986);
                $resp = studio_remote_get($url, ['timeout' => 6]);
                if (!is_wp_error($resp) && wp_remote_retrieve_response_code($resp) === 200) {
                    $body = json_decode(wp_remote_retrieve_body($resp), true);
                    if (!empty($body['name']) && $body['name'] !== $fid) {
                        $resolved[$fid] = (string) $body['name'];
                    }
                }
            }
        }
    }

    return $resolved;
}

// B6: Backfill satu baris galeri dan tulis balik ke selected_meta
function studio_backfill_gallery_row($g, $force_sync = false) {
    global $wpdb;
    $selected_ids = studio_sanitize_selected_ids(json_decode((string) $g->selected_ids, true) ?: []);
    $old_meta = json_decode((string) ($g->selected_meta ?: '{}'), true) ?: [];

    if (empty($selected_ids)) {
        if ($g->selected_meta === null || (int) $g->selected_count !== 0) {
            $wpdb->update($wpdb->prefix . 'studio_galleries', [
                'selected_meta' => '{}',
                'selected_count' => 0
            ], ['id' => $g->id]);
        }
        return [];
    }

    if ($force_sync && !empty($g->source_folder_id)) {
        studio_sync_folder($g->source_folder_id);
    } else {
        studio_get_file_index($g->source_folder_id);
    }

    $meta = [];
    $missing_ids = [];
    foreach ($selected_ids as $sid) {
        if (strpos($sid, 'demo') === 0 && preg_match('/^demo(\d+)/', $sid, $m)) {
            $d = studio_make_demo_photo((int) $m[1], $g->source_folder_id);
            $meta[$sid] = $d['name'];
        } elseif (!empty($old_meta[$sid]) && $old_meta[$sid] !== $sid) {
            $meta[$sid] = $old_meta[$sid];
        } else {
            $missing_ids[] = $sid;
        }
    }

    if (!empty($missing_ids)) {
        $resolved = studio_resolve_missing_names_parallel($missing_ids, $g->source_folder_id);
        foreach ($missing_ids as $sid) {
            if (!empty($resolved[$sid])) {
                $meta[$sid] = $resolved[$sid];
            }
        }
    }

    $wpdb->update($wpdb->prefix . 'studio_galleries', [
        'selected_meta' => wp_json_encode($meta, JSON_UNESCAPED_UNICODE),
        'selected_count' => count($selected_ids)
    ], ['id' => $g->id]);

    return $meta;
}

function studio_backfill_names() {
    global $wpdb;
    $galleries = $wpdb->get_results("SELECT id, source_folder_id, selected_ids, selected_meta, selected_count FROM {$wpdb->prefix}studio_galleries");
    foreach ((array) $galleries as $g) {
        $ids = studio_sanitize_selected_ids(json_decode((string) $g->selected_ids, true) ?: []);
        $meta = json_decode((string) ($g->selected_meta ?: '{}'), true) ?: [];
        $needs_backfill = ($g->selected_meta === null);
        if (!$needs_backfill && !empty($ids)) {
            foreach ($ids as $sid) {
                if (empty($meta[$sid]) || $meta[$sid] === $sid) {
                    $needs_backfill = true;
                    break;
                }
            }
        }
        if ($needs_backfill) {
            studio_backfill_gallery_row($g, false);
        }
    }
    update_option('studio_names_backfilled', 1, 'yes');
}
add_action('studio_run_backfill_names', 'studio_backfill_names');

// C4: Warm-up / prefetch thumbnail w=400 secara paralel (6 sekaligus) ke cache statis
function studio_schedule_prefetch_thumbs($folder_id) {
    if (empty($folder_id)) return;
    if (!wp_next_scheduled('studio_warm_thumbs_event', [$folder_id])) {
        wp_schedule_single_event(time() + 2, 'studio_warm_thumbs_event', [$folder_id]);
    }
}

function studio_prefetch_thumbs($folder_id, $max_files = 48) {
    global $wpdb;
    if (empty($folder_id)) {
        return ['total' => 0, 'cached' => 0, 'remaining' => 0];
    }

    studio_ensure_cache_dir();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT file_id, thumb_link FROM {$wpdb->prefix}studio_files WHERE folder_id = %s ORDER BY position ASC",
        $folder_id
    ));

    $total = count($rows);
    $cached = 0;
    $to_fetch = [];

    foreach ((array) $rows as $r) {
        if (strpos($r->file_id, 'demo') === 0) {
            $cached++;
            continue;
        }
        $info = studio_thumb_cache_info($r->file_id, 400);
        if (file_exists($info['path']) && filesize($info['path']) > 100) {
            $cached++;
        } else {
            $to_fetch[] = [
                'id' => $r->file_id,
                'thumb_link' => $r->thumb_link,
                'info' => $info
            ];
        }
    }

    if (empty($to_fetch)) {
        return ['total' => $total, 'cached' => $cached, 'remaining' => 0];
    }

    $slice = array_slice($to_fetch, 0, $max_files);
    $batches = array_chunk($slice, 6);

    foreach ($batches as $batch) {
        if (class_exists('WpOrg\Requests\Requests') || class_exists('Requests')) {
            $requests = [];
            foreach ($batch as $idx => $item) {
                $url = !empty($item['thumb_link'])
                    ? ($item['thumb_link'] . '=s400')
                    : ('https://lh3.googleusercontent.com/d/' . rawurlencode($item['id']) . '=w400');
                $requests[$idx] = [
                    'url' => $url,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                        'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8'
                    ],
                    'type' => 'GET'
                ];
            }
            $options = ['timeout' => 5, 'connect_timeout' => 4];
            $responses = class_exists('WpOrg\Requests\Requests')
                ? \WpOrg\Requests\Requests::request_multiple($requests, $options)
                : \Requests::request_multiple($requests, $options);

            foreach ($responses as $idx => $resp) {
                if (is_object($resp) && isset($resp->status_code) && (int) $resp->status_code === 200 && !empty($resp->body)) {
                    if (studio_is_valid_image_bytes($resp->body) && strlen($resp->body) < 5 * 1024 * 1024) {
                        $info = $batch[$idx]['info'];
                        if (!file_exists($info['dir'])) {
                            wp_mkdir_p($info['dir']);
                        }
                        @file_put_contents($info['path'], $resp->body);
                        $cached++;
                    }
                }
            }
        }
    }

    $remaining = max(0, $total - $cached);
    if ($remaining > 0 && count($to_fetch) > $max_files) {
        studio_schedule_prefetch_thumbs($folder_id);
    }

    return [
        'total' => $total,
        'cached' => $cached,
        'remaining' => $remaining
    ];
}
add_action('studio_warm_thumbs_event', 'studio_prefetch_thumbs');

// Helper sanitasi daftar ID pilihan klien
function studio_sanitize_selected_ids($ids) {
    if (!is_array($ids)) return [];
    $clean = [];
    foreach ($ids as $id) {
        if (is_scalar($id)) {
            $s = sanitize_text_field((string) $id);
            if ($s !== '') {
                $clean[] = $s;
            }
        }
    }
    return array_values(array_unique($clean));
}

// Fase A: Callback Diagnostik Drive API (Khusus Admin)
function studio_api_editor_diagnostics(WP_REST_Request $request) {
    global $wpdb;
    $key_defined = defined('STUDIO_GOOGLE_API_KEY') && !empty(STUDIO_GOOGLE_API_KEY);
    $folder_id = sanitize_text_field((string) $request->get_param('folder_id'));
    $sample_file_id = '';

    if (!$folder_id) {
        $latest = $wpdb->get_row("SELECT source_folder_id, selected_ids FROM {$wpdb->prefix}studio_galleries ORDER BY updated_at DESC LIMIT 1");
        if ($latest) {
            $folder_id = (string) $latest->source_folder_id;
            $s_ids = studio_sanitize_selected_ids(json_decode((string) $latest->selected_ids, true) ?: []);
            if (!empty($s_ids)) {
                $sample_file_id = $s_ids[0];
            }
        }
    }

    $list_test = null;
    if ($key_defined && $folder_id) {
        $t0 = microtime(true);
        $q = "'" . $folder_id . "' in parents and mimeType contains 'image/' and trashed=false";
        $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query([
            'q' => $q,
            'pageSize' => 10,
            'fields' => 'nextPageToken,files(id,name,mimeType)',
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
            'key' => STUDIO_GOOGLE_API_KEY
        ], '', '&', PHP_QUERY_RFC3986);
        $resp = studio_remote_get($url, ['timeout' => 10]);
        $dur_ms = (int) round((microtime(true) - $t0) * 1000);

        if (is_wp_error($resp)) {
            $list_test = [
                'httpStatus' => 0,
                'errorStatus' => 'WP_ERROR',
                'errorReason' => $resp->get_error_code(),
                'errorMessage' => $resp->get_error_message(),
                'durationMs' => $dur_ms,
                'fileCount' => 0
            ];
        } else {
            $code = wp_remote_retrieve_response_code($resp);
            $body = json_decode(wp_remote_retrieve_body($resp), true);
            $files = $body['files'] ?? [];
            if (!$sample_file_id && !empty($files[0]['id'])) {
                $sample_file_id = $files[0]['id'];
            }
            $list_test = [
                'httpStatus' => $code,
                'errorStatus' => $body['error']['status'] ?? null,
                'errorReason' => $body['error']['errors'][0]['reason'] ?? null,
                'errorMessage' => $body['error']['message'] ?? null,
                'durationMs' => $dur_ms,
                'fileCount' => count($files),
                'hasMorePages' => !empty($body['nextPageToken'])
            ];
        }
    }

    $get_test = null;
    if ($key_defined && $sample_file_id && strpos($sample_file_id, 'demo') !== 0) {
        $t0 = microtime(true);
        $url = 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($sample_file_id) . '?' . http_build_query([
            'fields' => 'id,name,mimeType,capabilities(canDownload)',
            'supportsAllDrives' => 'true',
            'key' => STUDIO_GOOGLE_API_KEY
        ], '', '&', PHP_QUERY_RFC3986);
        $resp = studio_remote_get($url, ['timeout' => 8]);
        $dur_ms = (int) round((microtime(true) - $t0) * 1000);

        if (is_wp_error($resp)) {
            $get_test = [
                'fileId' => $sample_file_id,
                'httpStatus' => 0,
                'errorReason' => $resp->get_error_code(),
                'errorMessage' => $resp->get_error_message(),
                'durationMs' => $dur_ms
            ];
        } else {
            $code = wp_remote_retrieve_response_code($resp);
            $body = json_decode(wp_remote_retrieve_body($resp), true);
            $get_test = [
                'fileId' => $sample_file_id,
                'httpStatus' => $code,
                'name' => $body['name'] ?? null,
                'mimeType' => $body['mimeType'] ?? null,
                'capabilities' => $body['capabilities'] ?? null,
                'errorReason' => $body['error']['errors'][0]['reason'] ?? null,
                'errorMessage' => $body['error']['message'] ?? null,
                'durationMs' => $dur_ms
            ];
        }
    }

    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/studio-cache';
    $cache_bytes = 0;
    $cache_files = 0;
    if (is_dir($cache_dir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $fi) {
            if ($fi->isFile() && substr($fi->getFilename(), -4) === '.jpg') {
                $cache_bytes += $fi->getSize();
                $cache_files++;
            }
        }
    }

    $curl_ver = function_exists('curl_version') ? (curl_version()['version'] ?? 'unknown') : 'none';

    return rest_ensure_response([
        'keyDefined' => $key_defined,
        'testedFolderId' => $folder_id,
        'filesListTest' => $list_test,
        'filesGetTest' => $get_test,
        'environment' => [
            'phpVersion' => PHP_VERSION,
            'dbVersion' => get_option('studio_db_version', 'none'),
            'wpDebug' => defined('WP_DEBUG') && WP_DEBUG,
            'wpDebugLog' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
            'extObjectCache' => function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache(),
            'curlVersion' => $curl_ver,
            'cacheFileCount' => $cache_files,
            'cacheSizeMb' => round($cache_bytes / (1024 * 1024), 2)
        ]
    ]);
}

// API Callbacks
function studio_api_editor_get_galleries() {
    global $wpdb;
    $galleries = $wpdb->get_results(
        "SELECT id, title, token, source_folder_id, result_folder_id, max_select, status, selected_count, updated_at
         FROM {$wpdb->prefix}studio_galleries
         ORDER BY updated_at DESC"
    );

    $base_client_url = studio_build_page_url('template-studio-client.php');
    foreach ($galleries as &$g) {
        $g->selected_count = (int) $g->selected_count;
        $g->link = $base_client_url ? add_query_arg(['k' => $g->token], $base_client_url) : '';
    }
    return rest_ensure_response($galleries);
}

function studio_api_editor_create_gallery(WP_REST_Request $request) {
    global $wpdb;
    $title = sanitize_text_field($request->get_param('title'));
    $folder_id = sanitize_text_field($request->get_param('folder_id'));
    if (!$title) return new WP_Error('invalid_title', 'Nama klien/galeri wajib diisi', ['status' => 400]);
    if (!preg_match('/^[A-Za-z0-9_-]{10,}$/', $folder_id)) return new WP_Error('invalid_folder', 'ID Folder sumber tidak valid', ['status' => 400]);

    $result_folder_id = sanitize_text_field((string) $request->get_param('result_folder_id'));
    if ($result_folder_id !== '' && !preg_match('/^[A-Za-z0-9_-]{10,}$/', $result_folder_id)) {
        return new WP_Error('invalid_result_folder', 'ID Folder hasil edit tidak valid', ['status' => 400]);
    }

    require_once ABSPATH . 'wp-includes/pluggable.php';
    $wpdb->insert($wpdb->prefix . 'studio_galleries', [
        'token' => wp_generate_password(32, false, false),
        'title' => $title,
        'source_folder_id' => $folder_id,
        'max_select' => max(0, (int) $request->get_param('max_select')),
        'result_folder_id' => $result_folder_id,
        'selected_ids' => '[]',
        'selected_meta' => '{}',
        'selected_count' => 0,
        'status' => 'unstarted'
    ]);
    $new_id = $wpdb->insert_id;

    // Pastikan indeks file & warm-up thumbnail terjadwal
    studio_get_file_index($folder_id);
    studio_schedule_prefetch_thumbs($folder_id);
    if ($result_folder_id !== '') {
        studio_get_file_index($result_folder_id);
        studio_schedule_prefetch_thumbs($result_folder_id);
    }

    return rest_ensure_response(['ok' => true, 'id' => $new_id]);
}

function studio_build_editor_gallery_detail($g, $force_refresh = false) {
    global $wpdb;
    $selected_ids = studio_sanitize_selected_ids(json_decode((string) $g->selected_ids, true) ?: []);

    if ($force_refresh) {
        $meta = studio_backfill_gallery_row($g, true);
    } elseif ($g->selected_meta === null && !empty($selected_ids)) {
        $meta = studio_backfill_gallery_row($g, false);
    } else {
        $meta = json_decode((string) ($g->selected_meta ?: '{}'), true) ?: [];
    }

    // Ambil urutan dan cadangan nama dari tabel lokal studio_files (0 panggilan ke Google Drive)
    $db_files = [];
    if (!empty($selected_ids) && !empty($g->source_folder_id)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '%s'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT file_id, name, thumb_link, position
             FROM {$wpdb->prefix}studio_files
             WHERE folder_id = %s AND file_id IN ($placeholders)",
            array_merge([$g->source_folder_id], $selected_ids)
        ));
        foreach ((array) $rows as $r) {
            $db_files[$r->file_id] = $r;
        }
    }

    $selected_photos = [];
    $missing_count = 0;
    $meta_updated = false;

    foreach ($selected_ids as $sid) {
        $is_demo = strpos($sid, 'demo') === 0;
        if ($is_demo && preg_match('/^demo(\d+)/', $sid, $m)) {
            $demo = studio_make_demo_photo((int) $m[1], $g->source_folder_id);
            $selected_photos[] = [
                'id' => $sid,
                'name' => $demo['name'],
                'nameMissing' => false,
                'src' => $demo['thumbnailLink'],
                'srcLg' => $demo['thumbnailLink'],
                'fallbackSrc' => $demo['thumbnailLink'],
                'fallbackSrcLg' => $demo['thumbnailLink']
            ];
            continue;
        }

        $name = isset($meta[$sid]) && $meta[$sid] !== '' && $meta[$sid] !== $sid ? (string) $meta[$sid] : null;
        if ($name === null && isset($db_files[$sid]) && $db_files[$sid]->name !== '' && $db_files[$sid]->name !== $sid) {
            $name = (string) $db_files[$sid]->name;
            $meta[$sid] = $name;
            $meta_updated = true;
        }

        $name_missing = ($name === null);
        if ($name_missing) {
            $missing_count++;
        }

        $selected_photos[] = [
            'id' => $sid,
            'name' => $name,
            'nameMissing' => $name_missing,
            'src' => studio_get_thumb_url($sid, 400, $g->source_folder_id),
            'srcLg' => studio_get_thumb_url($sid, 1600, $g->source_folder_id),
            'fallbackSrc' => 'https://lh3.googleusercontent.com/d/' . rawurlencode($sid) . '=w400',
            'fallbackSrcLg' => 'https://lh3.googleusercontent.com/d/' . rawurlencode($sid) . '=w1600'
        ];
    }

    if ($meta_updated) {
        $wpdb->update($wpdb->prefix . 'studio_galleries', [
            'selected_meta' => wp_json_encode($meta, JSON_UNESCAPED_UNICODE),
            'selected_count' => count($selected_ids)
        ], ['id' => $g->id]);
    }

    $sync_info = get_option('studio_sync_' . md5($g->source_folder_id));
    $synced_at = is_array($sync_info) && !empty($sync_info['synced_at']) ? $sync_info['synced_at'] : null;

    return [
        'id' => (int) $g->id,
        'title' => $g->title,
        'status' => $g->status,
        'note' => $g->note,
        'syncedAt' => $synced_at,
        'missingNameCount' => $missing_count,
        'photos' => $selected_photos
    ];
}

function studio_api_editor_get_gallery(WP_REST_Request $request) {
    global $wpdb;
    $id = (int) $request->get_param('id');
    $g = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}studio_galleries WHERE id = %d", $id));
    if (!$g) return new WP_Error('not_found', 'Galeri tidak ditemukan', ['status' => 404]);

    $force_refresh = (bool) $request->get_param('refresh');
    return rest_ensure_response(studio_build_editor_gallery_detail($g, $force_refresh));
}

function studio_api_editor_resync_gallery(WP_REST_Request $request) {
    global $wpdb;
    $id = (int) $request->get_param('id');
    $g = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}studio_galleries WHERE id = %d", $id));
    if (!$g) return new WP_Error('not_found', 'Galeri tidak ditemukan', ['status' => 404]);

    return rest_ensure_response(studio_build_editor_gallery_detail($g, true));
}

function studio_api_editor_warm_thumbs(WP_REST_Request $request) {
    global $wpdb;
    $id = (int) $request->get_param('id');
    $g = $wpdb->get_row($wpdb->prepare("SELECT source_folder_id FROM {$wpdb->prefix}studio_galleries WHERE id = %d", $id));
    if (!$g) return new WP_Error('not_found', 'Galeri tidak ditemukan', ['status' => 404]);

    studio_get_file_index($g->source_folder_id);
    $stats = studio_prefetch_thumbs($g->source_folder_id, 48);
    return rest_ensure_response(array_merge(['ok' => true], $stats));
}

function studio_cleanup_unused_folder_files($folder_id, $exclude_gallery_id = 0) {
    global $wpdb;
    if (empty($folder_id)) return;
    $in_use = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}studio_galleries
         WHERE id != %d AND (source_folder_id = %s OR result_folder_id = %s)",
        (int) $exclude_gallery_id,
        $folder_id,
        $folder_id
    ));
    if ($in_use === 0) {
        $wpdb->delete($wpdb->prefix . 'studio_files', ['folder_id' => $folder_id]);
        delete_option('studio_sync_' . md5($folder_id));
    }
}

function studio_api_editor_update_gallery(WP_REST_Request $request) {
    global $wpdb;
    $id = (int) $request->get_param('id');
    $g = $wpdb->get_row($wpdb->prepare("SELECT source_folder_id, result_folder_id FROM {$wpdb->prefix}studio_galleries WHERE id = %d", $id));
    if (!$g) return new WP_Error('not_found', 'Galeri tidak ditemukan', ['status' => 404]);

    $data = [];
    if ($request->has_param('title')) {
        $data['title'] = sanitize_text_field($request->get_param('title'));
    }
    if ($request->has_param('max_select')) {
        $data['max_select'] = max(0, (int) $request->get_param('max_select'));
    }
    if ($request->has_param('result_folder_id')) {
        $res_folder = sanitize_text_field((string) $request->get_param('result_folder_id'));
        if ($res_folder === '' || preg_match('/^[A-Za-z0-9_-]{10,}$/', $res_folder)) {
            $data['result_folder_id'] = $res_folder;
        } else {
            return new WP_Error('invalid_result_folder', 'ID Folder hasil edit tidak valid', ['status' => 400]);
        }
    }

    $old_source_folder = $g->source_folder_id;
    if ($request->has_param('source_folder_id')) {
        $new_folder = sanitize_text_field($request->get_param('source_folder_id'));
        if (preg_match('/^[A-Za-z0-9_-]{10,}$/', $new_folder)) {
            $data['source_folder_id'] = $new_folder;
            if ($new_folder !== $old_source_folder) {
                $data['selected_ids'] = '[]';
                $data['selected_meta'] = '{}';
                $data['selected_count'] = 0;
                $data['status'] = 'draft';
            }
        }
    }

    if (!empty($data)) {
        $wpdb->update($wpdb->prefix . 'studio_galleries', $data, ['id' => $id]);
        if (isset($data['source_folder_id']) && $data['source_folder_id'] !== $old_source_folder) {
            studio_cleanup_unused_folder_files($old_source_folder, $id);
            studio_get_file_index($data['source_folder_id']);
            studio_schedule_prefetch_thumbs($data['source_folder_id']);
        }
    }
    return rest_ensure_response(['ok' => true]);
}

function studio_api_editor_delete_gallery(WP_REST_Request $request) {
    global $wpdb;
    $id = (int) $request->get_param('id');
    $g = $wpdb->get_row($wpdb->prepare("SELECT source_folder_id, result_folder_id FROM {$wpdb->prefix}studio_galleries WHERE id = %d", $id));
    $wpdb->delete($wpdb->prefix . 'studio_galleries', ['id' => $id]);

    if ($g) {
        if ($g->source_folder_id) {
            studio_cleanup_unused_folder_files($g->source_folder_id, $id);
        }
        if ($g->result_folder_id) {
            studio_cleanup_unused_folder_files($g->result_folder_id, $id);
        }
    }
    return rest_ensure_response(['ok' => true]);
}

function studio_api_editor_reopen_gallery(WP_REST_Request $request) {
    global $wpdb;
    $id = (int) $request->get_param('id');
    $g = $wpdb->get_row($wpdb->prepare("SELECT status FROM {$wpdb->prefix}studio_galleries WHERE id = %d", $id));
    if (!$g) return new WP_Error('not_found', 'Galeri tidak ditemukan', ['status' => 404]);
    if ($g->status !== 'submitted') {
        return new WP_Error('invalid_status', 'Hanya galeri berstatus Terkirim yang dapat dibuka kembali.', ['status' => 409]);
    }
    $wpdb->update($wpdb->prefix . 'studio_galleries', ['status' => 'reopened', 'reopened_at' => current_time('mysql')], ['id' => $id]);
    return rest_ensure_response(['ok' => true]);
}

function studio_api_editor_regenerate_token(WP_REST_Request $request) {
    global $wpdb;
    $id = (int) $request->get_param('id');
    require_once ABSPATH . 'wp-includes/pluggable.php';
    $token = wp_generate_password(32, false, false);
    $wpdb->update($wpdb->prefix . 'studio_galleries', ['token' => $token], ['id' => $id]);

    $url = studio_build_page_url('template-studio-client.php', ['k' => $token]);
    return rest_ensure_response(['link' => $url]);
}

function studio_api_editor_drive_resolve(WP_REST_Request $request) {
    $folder_id = sanitize_text_field($request->get_param('input'));
    if (!preg_match('/^[A-Za-z0-9_-]{10,}$/', $folder_id)) {
        return new WP_Error('invalid', 'Format Link / ID Folder tidak valid', ['status' => 400]);
    }

    // Jika STUDIO_GOOGLE_API_KEY belum diset, dukung Mode Demo agar alur buat/edit galeri tetap jalan
    if (!defined('STUDIO_GOOGLE_API_KEY') || empty(STUDIO_GOOGLE_API_KEY)) {
        $index = studio_sync_folder($folder_id);
        return rest_ensure_response([
            'folderId' => $folder_id,
            'name' => 'Mode Demo (' . substr($folder_id, 0, 8) . '...)',
            'photoCount' => is_array($index) ? count($index) : 24,
            'isDemo' => true
        ]);
    }

    $url = "https://www.googleapis.com/drive/v3/files/" . rawurlencode($folder_id) . "?" . http_build_query([
        'fields' => 'id,name,mimeType',
        'supportsAllDrives' => 'true',
        'key' => STUDIO_GOOGLE_API_KEY
    ], '', '&', PHP_QUERY_RFC3986);
    $resp = studio_remote_get($url, ['timeout' => 15]);
    if (is_wp_error($resp)) return new WP_Error('api_error', 'Gagal menghubungi Google Drive API', ['status' => 500]);

    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (isset($body['error'])) return new WP_Error('api_error', $body['error']['message'] ?? 'Google Drive API Error', ['status' => 400]);
    if (($body['mimeType'] ?? '') !== 'application/vnd.google-apps.folder') {
        return new WP_Error('not_folder', 'ID tersebut bukan sebuah folder Google Drive', ['status' => 400]);
    }

    $index = studio_sync_folder($folder_id);
    if (is_wp_error($index)) {
        return $index;
    }
    return rest_ensure_response([
        'folderId' => $folder_id,
        'name' => $body['name'] ?? $folder_id,
        'photoCount' => count($index)
    ]);
}

function studio_api_client_get_gallery(WP_REST_Request $request) {
    $g = $request->get_param('gallery');
    $ids = studio_sanitize_selected_ids(json_decode((string) $g->selected_ids, true) ?: []);
    return rest_ensure_response([
        'title' => $g->title,
        'maxSelect' => (int) $g->max_select,
        'status' => $g->status,
        'selectedIds' => $ids,
        'note' => $g->note,
        'submittedAt' => $g->submitted_at,
        'hasResult' => !empty($g->result_folder_id),
        'editorWa' => get_option('studio_whatsapp', '')
    ]);
}

function studio_format_client_photo_item($p, $folder_id) {
    $fid = $p['id'];
    $is_demo = strpos($fid, 'demo') === 0;
    return [
        'id' => $fid,
        'name' => $p['name'],
        'src' => $is_demo ? $p['thumb_link'] : studio_get_thumb_url($fid, 400, $folder_id),
        'srcLg' => $is_demo ? $p['thumb_link'] : studio_get_thumb_url($fid, 1600, $folder_id),
        'fallbackSrc' => $is_demo ? $p['thumb_link'] : ('https://lh3.googleusercontent.com/d/' . rawurlencode($fid) . '=w400'),
        'fallbackSrcLg' => $is_demo ? $p['thumb_link'] : ('https://lh3.googleusercontent.com/d/' . rawurlencode($fid) . '=w1600')
    ];
}

function studio_api_client_get_photos(WP_REST_Request $request) {
    $g = $request->get_param('gallery');
    $is_stale = false;
    $index = studio_get_file_index($g->source_folder_id, false, $is_stale);

    if ($index === null) {
        return new WP_Error('drive_unavailable', 'Daftar foto belum bisa dimuat dari Google Drive. Silakan coba lagi sebentar.', ['status' => 503]);
    }

    $sync_meta = get_option('studio_sync_' . md5($g->source_folder_id));
    $synced_at = is_array($sync_meta) && !empty($sync_meta['synced_at']) ? $sync_meta['synced_at'] : '';
    $etag = 'W/"st-p-' . md5($g->source_folder_id . '|' . count($index) . '|' . $synced_at) . '"';

    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim((string) $_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
        return new WP_REST_Response(null, 304, ['ETag' => $etag]);
    }

    $photos = [];
    foreach ($index as $p) {
        $photos[] = studio_format_client_photo_item($p, $g->source_folder_id);
    }

    $resp = new WP_REST_Response($photos, 200);
    $resp->header('ETag', $etag);
    if ($is_stale) {
        $resp->header('X-Studio-Stale', '1');
    }
    return $resp;
}

function studio_api_client_get_result(WP_REST_Request $request) {
    $g = $request->get_param('gallery');
    if (empty($g->result_folder_id)) return new WP_Error('no_result', 'Folder hasil edit belum tersedia', ['status' => 404]);

    $is_stale = false;
    $index = studio_get_file_index($g->result_folder_id, false, $is_stale);
    if ($index === null) {
        return new WP_Error('drive_unavailable', 'Daftar foto hasil edit belum bisa dimuat. Silakan coba lagi sebentar.', ['status' => 503]);
    }

    $sync_meta = get_option('studio_sync_' . md5($g->result_folder_id));
    $synced_at = is_array($sync_meta) && !empty($sync_meta['synced_at']) ? $sync_meta['synced_at'] : '';
    $etag = 'W/"st-r-' . md5($g->result_folder_id . '|' . count($index) . '|' . $synced_at) . '"';

    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim((string) $_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
        return new WP_REST_Response(null, 304, ['ETag' => $etag]);
    }

    $photos = [];
    foreach ($index as $p) {
        $photos[] = studio_format_client_photo_item($p, $g->result_folder_id);
    }

    $resp = new WP_REST_Response(['folderId' => $g->result_folder_id, 'files' => $photos], 200);
    $resp->header('ETag', $etag);
    return $resp;
}

// B5 & C6: Validasi ringan via SQL IN (...) & snapshot nama ke selected_meta
function studio_validate_and_snapshot_selection($g, array $raw_ids) {
    global $wpdb;
    $ids = studio_sanitize_selected_ids($raw_ids);
    if ($g->max_select > 0 && count($ids) > $g->max_select) {
        return new WP_Error('limit', "Maksimal pilihan adalah {$g->max_select} foto.", ['status' => 422]);
    }

    $table_files = $wpdb->prefix . 'studio_files';
    $has_folder_rows = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_files} WHERE folder_id = %s",
        $g->source_folder_id
    ));

    if ($has_folder_rows === 0) {
        $index = studio_get_file_index($g->source_folder_id);
        if ($index === null) {
            return new WP_Error('drive_unavailable', 'Daftar foto belum bisa dimuat. Coba lagi sebentar.', ['status' => 503]);
        }
    }

    $old_meta = json_decode((string) ($g->selected_meta ?: '{}'), true) ?: [];
    $valid_ids = [];
    $meta = [];

    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '%s'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT file_id, name FROM {$table_files} WHERE folder_id = %s AND file_id IN ($placeholders)",
            array_merge([$g->source_folder_id], $ids)
        ), OBJECT_K);

        foreach ($ids as $id) {
            if (isset($rows[$id])) {
                $valid_ids[] = $id;
                $db_name = (string) $rows[$id]->name;
                $meta[$id] = ($db_name !== '' && $db_name !== $id) ? $db_name : ($old_meta[$id] ?? '');
            }
        }
    }

    return [
        'ids' => $valid_ids,
        'meta' => $meta,
        'count' => count($valid_ids)
    ];
}

function studio_api_client_update_selection(WP_REST_Request $request) {
    global $wpdb;
    $g = $request->get_param('gallery');
    if ($g->status === 'submitted') return new WP_Error('locked', 'Pilihan sudah dikunci', ['status' => 409]);

    $has_ids = $request->has_param('ids');
    $has_note = $request->has_param('note');
    $new_status = ($g->status === 'reopened') ? 'reopened' : 'draft';

    $update_data = ['status' => $new_status];
    $final_ids = studio_sanitize_selected_ids(json_decode((string) $g->selected_ids, true) ?: []);

    if ($has_ids) {
        $validated = studio_validate_and_snapshot_selection($g, (array) ($request->get_param('ids') ?: []));
        if (is_wp_error($validated)) {
            return $validated;
        }
        $final_ids = $validated['ids'];
        $update_data['selected_ids'] = wp_json_encode($final_ids);
        $update_data['selected_meta'] = wp_json_encode($validated['meta'], JSON_UNESCAPED_UNICODE);
        $update_data['selected_count'] = $validated['count'];
    }

    if ($has_note) {
        $note = sanitize_textarea_field((string) $request->get_param('note'));
        $update_data['note'] = mb_substr($note, 0, 5000);
    }

    $wpdb->update($wpdb->prefix . 'studio_galleries', $update_data, ['id' => $g->id]);

    return rest_ensure_response(['ok' => true, 'selectedIds' => $final_ids, 'status' => $new_status]);
}

function studio_api_client_submit(WP_REST_Request $request) {
    global $wpdb;
    $g = $request->get_param('gallery');
    if ($g->status === 'submitted') return new WP_Error('locked', 'Pilihan sudah dikunci', ['status' => 409]);

    $raw_ids = $request->has_param('ids') ? (array) $request->get_param('ids') : (json_decode((string) $g->selected_ids, true) ?: []);
    $validated = studio_validate_and_snapshot_selection($g, $raw_ids);
    if (is_wp_error($validated)) {
        return $validated;
    }

    if (empty($validated['ids'])) {
        return new WP_Error('empty_selection', 'Pilih minimal 1 foto sebelum mengirim.', ['status' => 422]);
    }

    $note = $request->has_param('note') ? sanitize_textarea_field((string) $request->get_param('note')) : $g->note;
    $submitted_at = current_time('mysql');
    $wpdb->update($wpdb->prefix . 'studio_galleries', [
        'selected_ids' => wp_json_encode($validated['ids']),
        'selected_meta' => wp_json_encode($validated['meta'], JSON_UNESCAPED_UNICODE),
        'selected_count' => $validated['count'],
        'note' => mb_substr((string) $note, 0, 5000),
        'status' => 'submitted',
        'submitted_at' => $submitted_at
    ], ['id' => $g->id]);

    return rest_ensure_response(['ok' => true, 'submittedAt' => $submitted_at, 'selectedIds' => $validated['ids']]);
}

// 5.5 Proxy thumbnail dengan Cache Statis Sharded, Negative-Cache, Lock, ETag, & Multi-Tier Cepat (C1, C2, C5)
add_action('init', function() {
    if (!isset($_GET['studio_thumb'])) return;

    $id = sanitize_text_field($_GET['studio_thumb']);
    $raw_w = isset($_GET['w']) ? (int) $_GET['w'] : 400;
    $exp = (int) ($_GET['exp'] ?? 0);
    $sig = (string) ($_GET['sig'] ?? '');

    if (!preg_match('/^[A-Za-z0-9_-]{5,}$/', $id)) {
        wp_die('Invalid file ID', 'Error', ['response' => 400]);
    }

    // C1: Tolak lebar yang tidak ada di daftar putih
    if (!in_array($raw_w, studio_allowed_widths(), true)) {
        wp_die('Invalid width', 'Error', ['response' => 400]);
    }
    $w = $raw_w;

    $expected_sig = hash_hmac('sha256', "$id|$w|$exp", wp_salt('auth'));
    $legacy_sig = hash_hmac('sha256', "$id|$exp", wp_salt('auth'));
    if (time() > $exp || (!hash_equals($expected_sig, $sig) && !hash_equals($legacy_sig, $sig))) {
        wp_die('Invalid or expired signature', 'Error', ['response' => 403]);
    }

    $info = studio_thumb_cache_info($id, $w);
    $etag = '"' . $info['hash'] . '"';

    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim((string) $_SERVER['HTTP_IF_NONE_MATCH']) === $etag && file_exists($info['path'])) {
        @touch($info['path']);
        while (ob_get_level()) ob_end_clean();
        status_header(304);
        header('ETag: ' . $etag);
        header('Cache-Control: public, max-age=2592000, immutable');
        exit;
    }

    $upload_dir = wp_upload_dir();
    $legacy_v2 = $upload_dir['basedir'] . '/studio-cache/v2_' . md5($id . '_' . $w) . '.jpg';

    foreach ([$info['path'], $legacy_v2] as $cf) {
        if (file_exists($cf) && filesize($cf) > 100) {
            $head_bytes = file_get_contents($cf, false, null, 0, 16);
            if (studio_is_valid_image_bytes($head_bytes . str_repeat("\0", 100))) {
                if ($cf === $legacy_v2 && !file_exists($info['path'])) {
                    if (!file_exists($info['dir'])) wp_mkdir_p($info['dir']);
                    @rename($legacy_v2, $info['path']);
                    $cf = file_exists($info['path']) ? $info['path'] : $legacy_v2;
                }
                @touch($cf);
                while (ob_get_level()) ob_end_clean();
                header('Content-Type: image/jpeg');
                header('ETag: ' . $etag);
                header('Cache-Control: public, max-age=2592000, immutable');
                readfile($cf);
                exit;
            }
        }
    }

    $cdn_redirect_url = 'https://lh3.googleusercontent.com/d/' . rawurlencode($id) . '=w' . $w;
    $fail_key = 'studio_thumbfail_' . $info['hash'];

    // C5: Negative-cache 2 menit — langsung redirect 302 tanpa mengulang rantai
    if (get_transient($fail_key)) {
        while (ob_get_level()) ob_end_clean();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Location: ' . $cdn_redirect_url, true, 302);
        exit;
    }

    // C5: Lock per file (15 dtk) agar request paralel untuk gambar yang sama tidak menyerbu Google
    $lock_key = 'studio_tlock_' . $info['hash'];
    $lock_time = (int) get_option($lock_key, 0);
    if ($lock_time && (time() - $lock_time) < 15) {
        // Tunggu sebentar jika worker lain sedang mengunduh
        for ($i = 0; $i < 4; $i++) {
            usleep(250000);
            if (file_exists($info['path']) && filesize($info['path']) > 100) {
                while (ob_get_level()) ob_end_clean();
                header('Content-Type: image/jpeg');
                header('ETag: ' . $etag);
                header('Cache-Control: public, max-age=2592000, immutable');
                readfile($info['path']);
                exit;
            }
        }
        while (ob_get_level()) ob_end_clean();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Location: ' . $cdn_redirect_url, true, 302);
        exit;
    }
    update_option($lock_key, time(), 'no');

    $try_fetch_image = function($target_url) {
        if (empty($target_url)) return null;
        $resp = studio_remote_get($target_url, ['timeout' => 4]);
        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
            return null;
        }
        $type = (string) wp_remote_retrieve_header($resp, 'content-type');
        $body = wp_remote_retrieve_body($resp);
        if ((stripos($type, 'image/') !== 0 && !studio_is_valid_image_bytes($body)) || strlen($body) <= 100) {
            return null;
        }
        return ['body' => $body, 'type' => stripos($type, 'image/') === 0 ? $type : 'image/jpeg'];
    };

    global $wpdb;
    $img_result = null;

    // Tier 1: thumbnailLink dari tabel studio_files
    $db_tlink = $wpdb->get_var($wpdb->prepare(
        "SELECT thumb_link FROM {$wpdb->prefix}studio_files WHERE file_id = %s AND thumb_link != '' LIMIT 1",
        $id
    ));
    if (!empty($db_tlink)) {
        $img_result = $try_fetch_image($db_tlink . '=s' . $w);
    }

    // Tier 2: lh3.googleusercontent.com/d/{id}=w{w}
    if (!$img_result) {
        $img_result = $try_fetch_image($cdn_redirect_url);
    }

    // Tier 3 & 4 hanya dicoba jika Tier 1 & 2 gagal
    if (!$img_result && defined('STUDIO_GOOGLE_API_KEY') && !empty(STUDIO_GOOGLE_API_KEY)) {
        $meta_url = 'https://www.googleapis.com/drive/v3/files/' . rawurlencode($id) . '?' . http_build_query([
            'fields' => 'id,name,thumbnailLink',
            'supportsAllDrives' => 'true',
            'key' => STUDIO_GOOGLE_API_KEY
        ], '', '&', PHP_QUERY_RFC3986);
        $meta_resp = studio_remote_get($meta_url, ['timeout' => 4]);
        if (!is_wp_error($meta_resp) && wp_remote_retrieve_response_code($meta_resp) === 200) {
            $meta_body = json_decode(wp_remote_retrieve_body($meta_resp), true);
            if (!empty($meta_body['thumbnailLink'])) {
                $base_turl = preg_replace('/=s\d+.*$/', '', $meta_body['thumbnailLink']);
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}studio_files SET thumb_link = %s WHERE file_id = %s",
                    $base_turl,
                    $id
                ));
                $img_result = $try_fetch_image($base_turl . '=s' . $w);
            }
        }
    }

    if (!$img_result) {
        $img_result = $try_fetch_image("https://drive.google.com/thumbnail?id=" . rawurlencode($id) . "&sz=w{$w}");
    }

    delete_option($lock_key);

    if ($img_result) {
        $body = $img_result['body'];
        $type = $img_result['type'];

        if (strlen($body) < 5 * 1024 * 1024) {
            studio_ensure_cache_dir();
            if (!file_exists($info['dir'])) {
                wp_mkdir_p($info['dir']);
            }
            @file_put_contents($info['path'], $body);
        }

        while (ob_get_level()) ob_end_clean();
        header("Content-Type: $type");
        header('ETag: ' . $etag);
        header('Cache-Control: public, max-age=2592000, immutable');
        echo $body;
        exit;
    }

    set_transient($fail_key, 1, 2 * MINUTE_IN_SECONDS);
    while (ob_get_level()) ob_end_clean();
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Location: ' . $cdn_redirect_url, true, 302);
    exit;
}, 1);

// C8: Cron pembersihan cache gambar berbasis umur (30 hari) & batas ukuran LRU (±300 MB)
add_action('studio_clean_cache_cron', function() {
    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/studio-cache';
    if (!is_dir($cache_dir)) return;

    $now = time();
    $max_age = 30 * DAY_IN_SECONDS;
    $max_bytes = 300 * 1024 * 1024;
    $target_bytes = 250 * 1024 * 1024;
    $total_bytes = 0;
    $entries = [];

    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $fi) {
        if (!$fi->isFile() || substr($fi->getFilename(), -4) !== '.jpg') continue;
        $path = $fi->getPathname();
        $mtime = (int) $fi->getMTime();
        $size = (int) $fi->getSize();

        if (($now - $mtime) >= $max_age) {
            @unlink($path);
            continue;
        }
        $total_bytes += $size;
        $entries[] = ['path' => $path, 'mtime' => $mtime, 'size' => $size];
    }

    if ($total_bytes > $max_bytes && !empty($entries)) {
        usort($entries, function($a, $b) {
            return $a['mtime'] <=> $b['mtime'];
        });
        foreach ($entries as $ent) {
            if ($total_bytes <= $target_bytes) break;
            if (@unlink($ent['path'])) {
                $total_bytes -= $ent['size'];
            }
        }
    }
});

// 5.6 Enqueue dan isolasi aset
add_filter('show_admin_bar', function($show) {
    if (function_exists('studio_is_studio_page') && studio_is_studio_page()) {
        return false;
    }
    return $show;
});

add_action('template_redirect', function() {
    if (!studio_is_studio_page()) return;
    remove_action('wp_head', 'bcs_preload_lcp', 1);
    remove_action('wp_head', 'bcs_lazy_placeholder_css', 99);
    remove_action('wp_footer', 'bcs_lazy_observer_js', 5);
    remove_action('wp_head', 'bcs_hide_amelia_payment_step');
}, 1);

// Fase D: Preconnect Google Fonts untuk mempercepat render awal
add_action('wp_head', function() {
    if (!studio_is_studio_page()) return;
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

add_action('wp_enqueue_scripts', function() {
    if (!studio_is_studio_page()) return;

    $css_dir = STUDIO_ASSETS_DIR . 'css/';
    $js_dir = STUDIO_ASSETS_DIR . 'js/';
    $css_url = STUDIO_ASSETS_URL . 'css/';
    $js_url = STUDIO_ASSETS_URL . 'js/';

    wp_enqueue_style('studio-font', 'https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap', [], null);

    if (file_exists($css_dir . 'studio-base.css')) {
        wp_enqueue_style('studio-base', $css_url . 'studio-base.css', [], filemtime($css_dir . 'studio-base.css'));
    }

    $role = is_page_template('template-studio-editor.php') ? 'editor' : 'client';
    if (file_exists($css_dir . "studio-{$role}.css")) {
        wp_enqueue_style("studio-{$role}", $css_url . "studio-{$role}.css", ['studio-base'], filemtime($css_dir . "studio-{$role}.css"));
    }

    if (file_exists($js_dir . 'studio-core.js')) {
        wp_enqueue_script('studio-core', $js_url . 'studio-core.js', [], filemtime($js_dir . 'studio-core.js'), true);

        $config = [
            'apiBase' => esc_url_raw(rest_url('studio/v1')),
            'role' => $role,
            'clientPageUrl' => studio_build_page_url('template-studio-client.php'),
            'isAdmin' => current_user_can('manage_options')
        ];
        if ($config['isAdmin']) {
            $config['nonce'] = wp_create_nonce('wp_rest');
        }

        wp_add_inline_script('studio-core', 'window.StudioConfig = ' . wp_json_encode($config) . ';', 'before');
    }

    if (file_exists($js_dir . "studio-{$role}.js")) {
        wp_enqueue_script("studio-{$role}", $js_url . "studio-{$role}.js", ['studio-core'], filemtime($js_dir . "studio-{$role}.js"), true);
    }
}, 20);

add_filter('script_loader_tag', function($tag, $handle) {
    if (strpos($handle, 'studio-') === 0 && strpos($tag, ' defer') === false) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}, 10, 2);

add_action('wp_enqueue_scripts', function() {
    if (!studio_is_studio_page()) return;

    $handles_to_remove = apply_filters('studio_dequeue_handles', [
        'main-style',
        'new-style',
        'swiper-css',
        'swiper-js',
        'three-js',
        'lucide-js',
        'new-main-js',
        'boldonse-font',
        'figtree-font',
        'plus-jakarta-sans-font',
        'bcs-combined-fonts'
    ]);

    foreach ($handles_to_remove as $handle) {
        wp_dequeue_style($handle);
        wp_dequeue_script($handle);
    }
}, 1000);

// 5.7 Header halaman dan privasi
add_filter('wp_robots', function($robots) {
    if (studio_is_studio_page()) {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
    }
    return $robots;
});

add_action('send_headers', function() {
    if (studio_is_studio_page()) {
        header('X-Robots-Tag: noindex, nofollow');
        header('Referrer-Policy: no-referrer');
        nocache_headers();
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
    }
});

add_filter('body_class', function($classes) {
    if (studio_is_studio_page()) {
        $classes[] = 'st-page';
    }
    return $classes;
});

// 5.8 Pembantu template (E1: gunakan class CSS, tanpa inline style)
function studio_render_shell($role) {
    if ($role === 'editor' && current_user_can('manage_options')) {
        $client_url = studio_build_page_url('template-studio-client.php');
        if (!$client_url) {
            echo '<div class="st-admin-warning">⚠️ Peringatan Admin: Buat dan Publish sebuah Page dengan template "Studio Client" agar link klien dapat bekerja.</div>';
        }
    }

    echo '<div id="st-app" class="st-app st-' . esc_attr($role) . '">';
    echo '<div class="st-shell-loading">Memuat...</div>';
    echo '<noscript><div class="st-shell-noscript">JavaScript harus diaktifkan.</div></noscript>';
    echo '</div>';
}

// ===== STUDIO END =====
