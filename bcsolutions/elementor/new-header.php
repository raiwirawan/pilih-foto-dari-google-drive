<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Header_Section extends Widget_Base
{

    public function get_name()
    {
        return 'header_section';
    }

    public function get_title()
    {
        return 'Header Section';
    }

    public function get_icon()
    {
        return 'eicon-header';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {

        // ========================
        // LOGO SECTION
        // ========================
        $this->start_controls_section(
            'logo_section',
            [
                'label' => 'Logo',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'logo_image',
            [
                'label' => 'Logo Image',
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => '',
                ],
            ]
        );

        $this->add_control(
            'logo_link',
            [
                'label' => 'Logo URL',
                'type' => Controls_Manager::TEXT,
                'default' => '/',
                'placeholder' => 'https://bcsolutions.id',
            ]
        );

        $this->end_controls_section();

        // ========================
        // SERVICES DROPDOWN SECTION
        // ========================
        $this->start_controls_section(
            'services_section',
            [
                'label' => 'Services Dropdown',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'services_dropdown_label',
            [
                'label' => 'Dropdown Label',
                'type' => Controls_Manager::TEXT,
                'default' => 'Services',
            ]
        );

        $services_repeater = new Repeater();

        $services_repeater->add_control(
            'service_label',
            [
                'label' => 'Service Name',
                'type' => Controls_Manager::TEXT,
                'default' => 'Service',
            ]
        );

        $services_repeater->add_control(
            'service_url',
            [
                'label' => 'URL',
                'type' => Controls_Manager::TEXT,
                'default' => '#',
            ]
        );

        $this->add_control(
            'services_links',
            [
                'label' => 'Services',
                'type' => Controls_Manager::REPEATER,
                'fields' => $services_repeater->get_controls(),
                'default' => [
                    ['service_label' => 'Social Media Management', 'service_url' => '#'],
                    ['service_label' => 'Profesionals Photography', 'service_url' => '#'],
                    ['service_label' => '360 Photo', 'service_url' => '#'],
                    ['service_label' => 'ADS Management', 'service_url' => '#'],
                    ['service_label' => 'Website', 'service_url' => '#'],
                    ['service_label' => 'SEO Optimize', 'service_url' => '#'],
                ],
                'title_field' => '{{{ service_label }}}',
            ]
        );

        $this->end_controls_section();

        // ========================
        // NAV LINKS SECTION
        // ========================
        $this->start_controls_section(
            'nav_links_section',
            [
                'label' => 'Navigation Links',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'about_us_label',
            [
                'label' => 'About Us Label',
                'type' => Controls_Manager::TEXT,
                'default' => 'About Us',
            ]
        );

        $this->add_control(
            'about_us_url',
            [
                'label' => 'About Us URL',
                'type' => Controls_Manager::TEXT,
                'default' => '#',
            ]
        );

        $this->add_control(
            'news_label',
            [
                'label' => 'News Label',
                'type' => Controls_Manager::TEXT,
                'default' => 'News',
            ]
        );

        $this->add_control(
            'news_url',
            [
                'label' => 'News URL',
                'type' => Controls_Manager::TEXT,
                'default' => '#',
            ]
        );

        $this->end_controls_section();

        // ========================
        // NAV ACTIONS SECTION
        // ========================
        $this->start_controls_section(
            'actions_section',
            [
                'label' => 'Nav Actions',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'search_placeholder',
            [
                'label' => 'Search Text',
                'type' => Controls_Manager::TEXT,
                'default' => 'Search',
            ]
        );



        $this->add_control(
            'contact_label',
            [
                'label' => 'Contact Button Label',
                'type' => Controls_Manager::TEXT,
                'default' => 'Contact BC Solutions',
            ]
        );

        $this->add_control(
            'contact_url',
            [
                'label' => 'Contact Button URL',
                'type' => Controls_Manager::TEXT,
                'default' => '#',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $logo_url = !empty($settings['logo_image']['url']) ? $settings['logo_image']['url'] : '';
        $logo_link = $settings['logo_link'] ?? '/';
        $services_links = $settings['services_links'] ?? [];
        $services_dropdown_label = $settings['services_dropdown_label'] ?? 'Services';
        $about_us_label = $settings['about_us_label'] ?? 'About Us';
        $about_us_url = $settings['about_us_url'] ?? '#';
        $news_label = $settings['news_label'] ?? 'News';
        $news_url = $settings['news_url'] ?? '#';
        $search_placeholder = $settings['search_placeholder'] ?? 'Search';
        $contact_label = $settings['contact_label'] ?? 'Contact BC Solutions';
        $contact_url = $settings['contact_url'] ?? '#';
        ?>

        <!-- Header -->
        <nav class="navbar">
            <a href="<?php echo esc_url($logo_link); ?>" class="logo">
                <?php if ($logo_url): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="logo">
                <?php endif; ?>
            </a>
            <div class="nav-links">
                <?php if (!empty($services_links)): ?>
                    <div class="nav-dropdown">
                        <a href="#"><?php echo esc_html($services_dropdown_label); ?> <i data-lucide="chevron-down"></i></a>
                        <div class="dropdown-menu">
                            <?php foreach ($services_links as $service): ?>
                                <a href="<?php echo esc_url($service['service_url']); ?>">
                                    <?php echo esc_html($service['service_label']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="<?php echo esc_url($about_us_url); ?>"><?php echo esc_html($about_us_label); ?></a>
                <a href="<?php echo esc_url($news_url); ?>"><?php echo esc_html($news_label); ?></a>
            </div>
            <div class="nav-actions">
                <button type="button" class="search-bar" id="search-toggle-btn"><i data-lucide="search"></i> <?php echo esc_html($search_placeholder); ?></button>
                <!-- Search Overlay -->
                <div class="search-overlay" id="search-overlay">
                    <form class="search-overlay-form" action="<?php echo esc_url(home_url('/')); ?>" method="get">
                        <i data-lucide="search"></i>
                        <input type="text" name="s" placeholder="<?php echo esc_attr($search_placeholder); ?>..." autocomplete="off" autofocus />
                        <button type="button" class="search-close-btn" id="search-close-btn"><i data-lucide="x"></i></button>
                    </form>
                </div>
                <a href="<?php echo esc_url($contact_url); ?>" class="btn-primary"><?php echo esc_html($contact_label); ?></a>
                <!-- Burger Menu (Mobile) -->
                <button class="burger-menu" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </nav>

        <!-- Mobile Nav Overlay -->
        <div class="mobile-nav-overlay">
            <ul class="mobile-nav-links">
                <?php if (!empty($services_links)): ?>
                    <?php foreach ($services_links as $service): ?>
                        <li><a href="<?php echo esc_url($service['service_url']); ?>"><?php echo esc_html($service['service_label']); ?></a></li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <li><a href="<?php echo esc_url($about_us_url); ?>"><?php echo esc_html($about_us_label); ?></a></li>
                <li><a href="<?php echo esc_url($news_url); ?>"><?php echo esc_html($news_label); ?></a></li>
                <li><a href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html($contact_label); ?></a></li>
            </ul>
        </div>

        <script>
            // Initialize Lucide icons for this widget
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Search overlay toggle
            (function() {
                var toggleBtn = document.getElementById('search-toggle-btn');
                var overlay = document.getElementById('search-overlay');
                var closeBtn = document.getElementById('search-close-btn');
                if (toggleBtn && overlay) {
                    toggleBtn.addEventListener('click', function() {
                        overlay.classList.add('active');
                        var input = overlay.querySelector('input[name="s"]');
                        if (input) input.focus();
                    });
                }
                if (closeBtn && overlay) {
                    closeBtn.addEventListener('click', function() {
                        overlay.classList.remove('active');
                    });
                }
                // Close on Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && overlay && overlay.classList.contains('active')) {
                        overlay.classList.remove('active');
                    }
                });
            })();

            // Burger menu toggle
            (function() {
                const burger = document.querySelector('.burger-menu');
                if (burger) {
                    burger.addEventListener('click', function() {
                        document.body.classList.toggle('nav-open');
                    });
                    document.querySelectorAll('.mobile-nav-links a').forEach(function(a) {
                        a.addEventListener('click', function() {
                            document.body.classList.remove('nav-open');
                        });
                    });
                }
            })();
        </script>

        <?php
    }
}
