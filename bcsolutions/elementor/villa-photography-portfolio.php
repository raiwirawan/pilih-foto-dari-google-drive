<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Photography_Portfolio_Widget extends Widget_Base {

    public function get_name() {
        return 'photography_portfolio';
    }

    public function get_title() {
        return 'Photography Portfolio (Horizontal)';
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_style_depends() {
        return ['bcs-villa-photography-style'];
    }

    public function get_script_depends() {
        return ['bcs-villa-photography-js'];
    }

    protected function register_controls() {
        // Add Repeater Control for Cards
        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Portfolio Cards',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'number',
            [
                'label' => 'Number',
                'type' => Controls_Manager::TEXT,
                'default' => '01',
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label' => 'Title',
                'type' => Controls_Manager::TEXT,
                'default' => 'PROJECT NAME',
            ]
        );

        $repeater->add_control(
            'image',
            [
                'label' => 'Image',
                'type' => Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater->add_control(
            'show_intro',
            [
                'label' => 'Show Intro Text Block (Usually 1st card)',
                'type' => Controls_Manager::SWITCHER,
                'label_on' => 'Yes',
                'label_off' => 'No',
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $repeater->add_control(
            'intro_title',
            [
                'label' => 'Intro Title',
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'show_intro' => 'yes',
                ],
                'default' => 'BC Solutions',
            ]
        );

        $repeater->add_control(
            'intro_desc',
            [
                'label' => 'Intro Description',
                'type' => Controls_Manager::TEXTAREA,
                'condition' => [
                    'show_intro' => 'yes',
                ],
                'default' => 'Capturing the essence of luxury living across Bali. We provide high-end architectural and interior photography services to elevate your marketing and showcase the true beauty of your property.',
            ]
        );

        $this->add_control(
            'cards',
            [
                'label' => 'Cards List',
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'number' => '01',
                        'title' => 'PASIH SESEH VILLA',
                        'show_intro' => 'yes',
                        'intro_title' => 'BC Solutions',
                        'intro_desc' => 'Capturing the essence of luxury living across Bali. We provide high-end architectural and interior photography services to elevate your marketing and showcase the true beauty of your property.',
                    ],
                    [
                        'number' => '02',
                        'title' => 'GREEN OASIS RETREATS',
                        'show_intro' => '',
                    ],
                    [
                        'number' => '03',
                        'title' => 'THE LOTUS RESIDENCE',
                        'show_intro' => '',
                    ],
                ],
                'title_field' => '{{{ number }}} - {{{ title }}}',
            ]
        );

        $this->end_controls_section();

        // Menu Settings
        $this->start_controls_section(
            'menu_section',
            [
                'label' => 'Menu Settings',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $menu_repeater = new Repeater();
        $menu_repeater->add_control('title', ['label' => 'Title', 'type' => Controls_Manager::TEXT]);
        $menu_repeater->add_control('url', ['label' => 'URL', 'type' => Controls_Manager::URL]);

        $this->add_control(
            'menu_links',
            [
                'label' => 'Menu Links',
                'type' => Controls_Manager::REPEATER,
                'fields' => $menu_repeater->get_controls(),
                'default' => [
                    ['title' => 'Work', 'url' => ['url' => '#']],
                    ['title' => 'About', 'url' => ['url' => '#']],
                    ['title' => 'Services', 'url' => ['url' => '#']],
                ],
                'title_field' => '{{{ title }}}',
            ]
        );
        $this->end_controls_section();

        // Contact Settings
        $this->start_controls_section(
            'contact_section',
            [
                'label' => 'Contact Settings',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $contact_repeater = new Repeater();
        $contact_repeater->add_control('title', ['label' => 'Title', 'type' => Controls_Manager::TEXT]);
        $contact_repeater->add_control('url', ['label' => 'URL', 'type' => Controls_Manager::URL]);

        $this->add_control(
            'contact_links',
            [
                'label' => 'Contact Links',
                'type' => Controls_Manager::REPEATER,
                'fields' => $contact_repeater->get_controls(),
                'default' => [
                    ['title' => 'Instagram', 'url' => ['url' => '#']],
                    ['title' => 'LinkedIn', 'url' => ['url' => '#']],
                    ['title' => 'hello@bcsolutions.com', 'url' => ['url' => 'mailto:hello@bcsolutions.com']],
                ],
                'title_field' => '{{{ title }}}',
            ]
        );

        $this->add_control(
            'whatsapp_link',
            [
                'label' => 'WhatsApp Link (Bottom Right)',
                'type' => Controls_Manager::URL,
                'default' => ['url' => 'https://wa.me/6283854168480'],
            ]
        );
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <!-- Horizontal Scroll Section -->
        <section id="vp-portfolio" class="vp-portfolio">
            <!-- Scrollable Track -->
            <div class="vp-track" id="vp-track">
                <?php foreach ($settings['cards'] as $item): ?>
                    <article class="vp-card vp-project-card elementor-repeater-item-<?php echo esc_attr($item['_id']); ?>" data-num="<?php echo esc_attr($item['number']); ?>" data-title="<?php echo esc_attr($item['title']); ?>">
                        <?php if (!empty($item['show_intro'])): ?>
                        <!-- Intro Text (Only Card 1 usually) -->
                        <div class="vp-card-intro-text">
                            <h2 class="vp-cit-title"><?php echo esc_html($item['intro_title']); ?></h2>
                            <p class="vp-cit-desc"><?php echo esc_html($item['intro_desc']); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Sticky Bookmark -->
                        <div class="vp-bookmark">
                            <span class="vp-bm-num"><?php echo esc_html($item['number']); ?></span>
                            <span class="vp-bm-title"><?php echo esc_html($item['title']); ?></span>
                        </div>

                        <div class="vp-card-img">
                            <?php 
                            $image_url = !empty($item['image']['url']) ? $item['image']['url'] : '';
                            $alt = \Elementor\Control_Media::get_image_alt($item['image']);
                            if ($image_url): 
                            ?>
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($alt ? $alt : $item['title']); ?>">
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>

                <!-- Spacer at the end so last card can be seen clearly -->
                <div class="vp-track-spacer" style="width: 10vw; flex-shrink: 0;"></div>
            </div>
        </section>
        
        <!-- Fixed UI Elements -->
        <div class="vp-progress-bar" id="vp-progress"></div>
        
        <!-- Sidebar Overlays -->
        <div class="vp-overlay vp-menu-overlay" id="vp-menu-overlay">
            <div class="vp-overlay-content">
                <h2 class="vp-overlay-title">Menu</h2>
                <ul class="vp-overlay-links">
                    <?php if (!empty($settings['menu_links'])) {
                        foreach ($settings['menu_links'] as $link) { ?>
                        <li><a href="<?php echo esc_url($link['url']['url']); ?>"><?php echo esc_html($link['title']); ?></a></li>
                    <?php } } ?>
                </ul>
                <button class="vp-overlay-close" id="vp-menu-close">Close</button>
            </div>
        </div>

        <div class="vp-overlay vp-contact-overlay" id="vp-contact-overlay">
            <div class="vp-overlay-content">
                <h2 class="vp-overlay-title">Contact Us</h2>
                <ul class="vp-overlay-links">
                    <?php if (!empty($settings['contact_links'])) {
                        foreach ($settings['contact_links'] as $link) { ?>
                        <li><a href="<?php echo esc_url($link['url']['url']); ?>"><?php echo esc_html($link['title']); ?></a></li>
                    <?php } } ?>
                </ul>
                <button class="vp-overlay-close" id="vp-contact-close">Close</button>
            </div>
        </div>

        <!-- Sidebars -->
        <aside class="vp-sidebar vp-sidebar-left" id="vp-sidebar-menu">
            <div class="vp-sidebar-text">Menu</div>
        </aside>

        <aside class="vp-sidebar vp-sidebar-right" id="vp-sidebar-contact">
            <div class="vp-sidebar-text">Contact</div>
        </aside>

        <!-- Mobile Toggles -->
        <button class="vp-menu-toggle" id="vp-menu-toggle">Menu</button>
        <a href="<?php echo esc_url(!empty($settings['whatsapp_link']['url']) ? $settings['whatsapp_link']['url'] : '#'); ?>" class="vp-contact-toggle" target="_blank">Contact</a>
        <?php
    }
}
