<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BC_Villa_About_Page_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'bc_villa_about_page'; }
    public function get_title() { return __( 'BC About Page Content', 'bc-villa' ); }
    public function get_icon() { return 'eicon-code'; }
    public function get_categories() { return [ 'general' ]; }

    public function get_style_depends() {
        return [ 'bcs-villa-photography-style' ];
    }

    public function get_script_depends() {
        return [ 'bcs-villa-photography-js' ];
    }

    protected function register_controls() {
        // --- HERO SECTION ---
        $this->start_controls_section('section_global', [ 'label' => 'Global Elements', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]);
        $this->add_control('preloader_text', [ 'label' => 'Preloader Text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "BC VILLA\nSTUDIOS." ]);
        $this->add_control('cursor_text', [ 'label' => 'Cursor Hover Text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'VIEW' ]);
        $this->end_controls_section();

        // --- HERO SECTION ---
        $this->start_controls_section('section_hero', [ 'label' => 'Hero Section', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]);
        $this->add_control('hero_headline', [ 'label' => 'Headline', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "VISUAL\nPREMIUM." ]);
        $this->add_control('hero_desc_top', [ 'label' => 'Description Top', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Capturing the true essence of your property\'s uniqueness gracefully and authentically.' ]);
        $this->add_control('hero_desc_sub', [ 'label' => 'Sub Description', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'EST. 2024 — STUDIO' ]);
        $this->add_control('hero_image', [ 'label' => 'Hero Image', 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => ['url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1920&q=80'] ]);
        $this->end_controls_section();

        // --- ABOUT SECTION ---
        $this->start_controls_section('section_about', [ 'label' => 'About Section', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]);
        $this->add_control('about_overline', [ 'label' => 'Overline', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '01 — Aesthetic Approach' ]);
        $this->add_control('about_title', [ 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Capturing the Soul<br/>of a Space.' ]);
        $this->add_control('about_text', [ 'label' => 'Text', 'type' => \Elementor\Controls_Manager::WYSIWYG, 'default' => '<p>Every detail of your villa is crafted to please the eye, yet ordinary visuals often fail to convey this meaning to potential guests. BC Villa Photography is here as a premium visual solution that captures the true essence of your property gracefully and authentically.</p><p>Through a blend of precise lighting, mature architectural composition, and high-end magazine-style editing touches, we bring the exclusive atmosphere of every corner to life. These visually pleasing photos not only captivate potential guests but also elevate <strong>perceived value</strong> and convert first glances into actual reservations.</p>' ]);
        $this->add_control('about_image', [ 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => ['url' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1200&q=80'] ]);
        $this->end_controls_section();

        // --- TEAM SECTION ---
        $this->start_controls_section('section_team', [ 'label' => 'Team Section', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]);
        $this->add_control('team_overline', [ 'label' => 'Overline', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '02 — Meet Our Team' ]);
        $this->add_control('team_title', [ 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Behind the Lens' ]);
        
        $repeater = new \Elementor\Repeater();
        $repeater->add_control('member_name', [ 'label' => 'Name', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Member Name' ]);
        $repeater->add_control('member_role', [ 'label' => 'Role', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Role' ]);
        $repeater->add_control('member_image', [ 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => ['url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=800&q=80'] ]);
        
        $this->add_control('team_members', [
            'label' => 'Team Members',
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                [ 'member_name' => 'B.C. Villa', 'member_role' => 'Founder & Lead Photographer', 'member_image' => ['url' => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?auto=format&fit=crop&w=800&q=80'] ],
                [ 'member_name' => 'Anya K.', 'member_role' => 'Creative Director', 'member_image' => ['url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=800&q=80'] ],
                [ 'member_name' => 'Raditya', 'member_role' => 'Drone Specialist', 'member_image' => ['url' => 'https://images.unsplash.com/photo-1473968512647-3e447244af8f?auto=format&fit=crop&w=800&q=80'] ],
                [ 'member_name' => 'Elena', 'member_role' => 'Lead Retoucher', 'member_image' => ['url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80'] ]
            ]
        ]);
        $this->end_controls_section();

        // --- PORTFOLIO SECTION ---
        $this->start_controls_section('section_portfolio', [ 'label' => 'Portfolio Section', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]);
        $this->add_control('portfolio_overline', [ 'label' => 'Overline', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '03 — Portfolio Highlights' ]);
        $this->add_control('portfolio_title', [ 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Visual Works' ]);
        $this->add_control('portfolio_btn_text', [ 'label' => 'Button Text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View All Works' ]);
        $this->add_control('portfolio_btn_link', [ 'label' => 'Button Link', 'type' => \Elementor\Controls_Manager::URL, 'default' => ['url' => '#'] ]);
        
        $this->add_control('portfolio_img_1', [ 'label' => 'Image 1 (Large)', 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80'] ]);
        $this->add_control('portfolio_cap_1', [ 'label' => 'Caption 1', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'NATURAL LIGHT' ]);
        
        $this->add_control('portfolio_img_2', [ 'label' => 'Image 2 (Small Top)', 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => ['url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80'] ]);
        $this->add_control('portfolio_cap_2', [ 'label' => 'Caption 2', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'CAPTURING VIBES' ]);
        
        $this->add_control('portfolio_img_3', [ 'label' => 'Image 3 (Small Bottom)', 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => ['url' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=800&q=80'] ]);
        $this->add_control('portfolio_cap_3', [ 'label' => 'Caption 3', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'DRONE ANGLES' ]);
        $this->end_controls_section();

        // --- TESTIMONIAL SECTION ---
        $this->start_controls_section('section_testimonial', [ 'label' => 'Testimonial Section', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]);
        $this->add_control('testimonial_overline', [ 'label' => 'Overline', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '04 — Track Record' ]);
        $this->add_control('testimonial_title', [ 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Credibility &<br/>Trust.' ]);
        $this->add_control('testimonial_desc', [ 'label' => 'Description', 'type' => \Elementor\Controls_Manager::WYSIWYG, 'default' => '<p>With a portfolio of over <strong>150+ luxury properties and villas</strong>, we have extensive experience collaborating with premium hotel <em>brands</em>, Airbnb Superhosts, and major property agents to create visuals that make a real impact on sales.</p>' ]);
        $testi_repeater = new \Elementor\Repeater();
        $testi_repeater->add_control('quote', [ 'label' => 'Quote', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '"Incredible visual results..."' ]);
        $testi_repeater->add_control('author', [ 'label' => 'Author', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '— Client Name' ]);

        $this->add_control('testimonials', [
            'label' => 'Testimonials List',
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $testi_repeater->get_controls(),
            'default' => [
                [ 'quote' => '"Since using visuals from BC Villa Photography, our property looks incredibly luxurious and lively. The results were far beyond expectations, and most importantly: the photos successfully captivated guests and drastically increased our villa booking conversions by up to 40%!"', 'author' => '— The Azure Villa Management, Seminyak' ],
                [ 'quote' => '"The captured aesthetics truly represent the soul of our architecture. Highly professional, efficient, and world-class results. A very worthwhile visual investment."', 'author' => '— Architect & Co. Studio' ],
                [ 'quote' => '"Their approach to natural light makes our villa look spacious and warm. Our Airbnb conversions skyrocketed after updating the photos from the BC team."', 'author' => '— Airbnb Superhost Bali' ]
            ]
        ]);
        $this->end_controls_section();

        // --- MARQUEE SECTION ---
        $this->start_controls_section('section_marquee', [ 'label' => 'Marquee Section', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]);
        $this->add_control('marquee_text', [ 'label' => 'Marquee Text', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'PREMIUM VISUALS — ARCHITECTURAL AESTHETICS — ACTUAL RESERVATIONS — ' ]);
        $this->end_controls_section();

        // --- CTA SECTION ---
        $this->start_controls_section('section_cta', [ 'label' => 'CTA Section', 'tab' => \Elementor\Controls_Manager::TAB_CONTENT ]);
        $this->add_control('cta_overline', [ 'label' => 'Overline', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'COLLABORATION' ]);
        $this->add_control('cta_title', [ 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Secure Your Villa<br/>Photo Schedule.' ]);
        $this->add_control('cta_btn_text', [ 'label' => 'Button Text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'View Our Pricing Packages' ]);
        $this->add_control('cta_btn_link', [ 'label' => 'Button Link', 'type' => \Elementor\Controls_Manager::URL, 'default' => ['url' => '#'] ]);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<div style="padding: 50px; background: #121212; color: #dac769; text-align: center; border: 1px solid #dac769; border-radius: 8px;">';
            echo '<h2>' . __('BC Main Page Content (Edit Mode)', 'bc-villa') . '</h2>';
            echo '<p>' . __('Tampilan live dinonaktifkan di Editor agar scroll tidak rusak. Klik tombol Preview / View Page untuk melihat perubahan konten Anda.', 'bc-villa') . '</p>';
            echo '</div>';
            return;
        }
        ?>
<div class="bc-villa-widget-scope">
<div id="preloader">
    <div class="overflow-hidden">
        <h2 id="preloader-text" class="font-display-lg" style="color: var(--tertiary);"><?php echo wp_kses_post( nl2br( $settings['preloader_text'] ) ); ?></h2>
    </div>
</div>

<div id="cursor-dot" class="hidden md-block"></div>
<div id="cursor-circle" class="hidden md-block">
    <span class="cursor-text"><?php echo esc_html( $settings['cursor_text'] ); ?></span>
</div>

<div class="noise-overlay"></div>

<div id="scroll-container">
    <div id="scroll-content">
        <main class="main-content">
            <!-- 1. Hero Section -->
            <div class="hero-section anti-mainstream-hero" id="hero-section">
                <!-- Top Right Description -->
                <div class="hero-content-top">
                    <p class="font-body-md" style="margin-bottom: 24px;">
                        <?php echo wp_kses_post( $settings['hero_desc_top'] ); ?>
                    </p>
                    <div class="font-label-caps" style="color: var(--tertiary); display: flex; align-items: center; gap: 8px;">
                        <span style="display:inline-block; width:8px; height:8px; background:var(--tertiary); border-radius:50%;"></span>
                        <?php echo esc_html( $settings['hero_desc_sub'] ); ?>
                    </div>
                </div>

                <!-- Centerpiece Image -->
                <div class="hero-image-center reveal-scale parallax-wrapper" style="transition-delay: 0.6s;">
                    <img alt="Professional Bali villa photographer hero image" class="parallax-image js-parallax" data-speed="0.15" src="<?php echo esc_url( $settings['hero_image']['url'] ); ?>"/>
                </div>

                <!-- Bottom Left Typography Overlap -->
                <div class="hero-content-bottom">
                    <h1 class="hero-headline font-display-lg">
                        <div id="typing-text-source" style="display: none;"><?php echo wp_kses_post( nl2br( $settings['hero_headline'] ) ); ?></div>
                        <span id="typing-text"></span><span class="typing-cursor">|</span>
                    </h1>
                </div>
            </div>

            <div class="sections-wrapper">
                <!-- 2. The Story / Philosophy (Text Mask Reveal) -->
                <section class="section section-high" id="about-section">
                    <div class="content-grid w-full">
                        <!-- Top Column -->
                        <div class="col-10 col-start-2 text-center" style="display: flex; flex-direction: column; align-items: center;">
                            <span class="section-overline font-label-caps line-mask-target" style="color: var(--tertiary);"><?php echo esc_html( $settings['about_overline'] ); ?></span>
                            <h2 class="font-display-md mt-16 mb-48 text-center line-mask-target"><?php echo wp_kses_post( $settings['about_title'] ); ?></h2>
                        
                            <!-- Bottom Column Text -->
                            <div class="awwwards-narrative text-center w-full line-mask-target">
                                <?php echo wp_kses_post( strip_tags( $settings['about_text'], '<strong><b><em><i><span><a>' ) ); ?>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- 3. Roster / Team Section -->
                <section class="section">
                    <div class="mb-12 reveal-up">
                        <span class="section-overline font-label-caps"><?php echo esc_html( $settings['team_overline'] ); ?></span>
                        <h2 class="font-headline-lg"><?php echo esc_html( $settings['team_title'] ); ?></h2>
                    </div>

                    <div class="roster-container reveal-up" style="transition-delay: 0.2s;">
                        <ul class="roster-list" id="roster-list">
                            <?php foreach ( $settings['team_members'] as $member ) : ?>
                            <li class="roster-item" data-image="<?php echo esc_url( $member['member_image']['url'] ); ?>">
                                <img class="roster-mobile-img" src="<?php echo esc_url( $member['member_image']['url'] ); ?>" alt="Bali villa photographer team member" />
                                <h3 class="roster-name font-display-md"><?php echo esc_html( $member['member_name'] ); ?></h3>
                                <span class="roster-role font-label-caps"><?php echo esc_html( $member['member_role'] ); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="roster-hover-image" id="roster-hover-wrapper">
                            <div class="roster-img-container" id="roster-img-container">
                                <img src="" alt="Bali villa photographer team member" id="roster-img" />
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- 4. Articles/Journal Section -->
                <section class="section section-high">
                    <div class="md-flex-between hairline-border-b reveal-up">
                        <div>
                            <span class="section-overline font-label-caps"><?php echo esc_html( $settings['portfolio_overline'] ); ?></span>
                            <h2 class="font-headline-lg reveal-stagger"><?php echo esc_html( $settings['portfolio_title'] ); ?></h2>
                        </div>
                        <div>
                            <a class="btn-outline magnetic btn-hover-fx font-label-caps" href="<?php echo esc_url( $settings['portfolio_btn_link']['url'] ); ?>"><?php echo esc_html( $settings['portfolio_btn_text'] ); ?></a>
                        </div>
                    </div>
                    <div class="content-grid">
                        <div class="col-8 journal-image-large reveal-scale parallax-wrapper">
                            <img alt="Bali villa photographer large portfolio" class="parallax-image js-parallax" data-speed="0.12" src="<?php echo esc_url( $settings['portfolio_img_1']['url'] ); ?>"/>
                            <div class="caption-overlay-bottom">
                                <p class="font-label-caps" style="color: var(--tertiary);"><?php echo esc_html( $settings['portfolio_cap_1'] ); ?></p>
                            </div>
                        </div>
                        <div class="col-4 flex-col-gap">
                            <div class="journal-image-small reveal-up parallax-wrapper" style="transition-delay: 0.1s;">
                                <img alt="Bali villa photographer interior shot" class="parallax-image js-parallax" data-speed="0.18" src="<?php echo esc_url( $settings['portfolio_img_2']['url'] ); ?>"/>
                                <div class="caption-hover-x">
                                    <p class="font-label-caps caption-mix-blend"><?php echo esc_html( $settings['portfolio_cap_2'] ); ?></p>
                                </div>
                            </div>
                            <div class="journal-image-small reveal-up parallax-wrapper" style="transition-delay: 0.2s;">
                                <img alt="Bali villa photographer drone shot" class="parallax-image js-parallax" data-speed="0.15" src="<?php echo esc_url( $settings['portfolio_img_3']['url'] ); ?>"/>
                                <div class="caption-hover-y">
                                    <p class="font-label-caps caption-mix-blend"><?php echo esc_html( $settings['portfolio_cap_3'] ); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- 5. Kredibilitas & Testimoni (Awwwards Slider) -->
                <section class="section">
                    <div class="content-grid">
                        <div class="col-4 reveal-up">
                            <span class="section-overline font-label-caps" style="color: var(--tertiary);"><?php echo esc_html( $settings['testimonial_overline'] ); ?></span>
                            <h2 class="font-headline-md reveal-stagger mt-16"><?php echo wp_kses_post( $settings['testimonial_title'] ); ?></h2>
                            <div class="font-body-md mt-16" style="color: var(--on-surface-variant); margin-bottom: 40px;">
                                <?php echo wp_kses_post( $settings['testimonial_desc'] ); ?>
                            </div>
                            
                            <!-- Slider Controls Desktop -->
                            <div class="testi-controls hidden md-flex reveal-up" style="transition-delay: 0.3s;">
                                <button class="testi-btn testi-prev magnetic"><span class="material-symbols-outlined">west</span></button>
                                <div class="testi-counter font-label-caps"><span id="testi-current">1</span> / <span id="testi-total"><?php echo count($settings['testimonials']); ?></span></div>
                                <button class="testi-btn testi-next magnetic"><span class="material-symbols-outlined">east</span></button>
                            </div>
                        </div>
                        
                        <div class="col-7 col-start-6 reveal-up" style="transition-delay: 0.2s;">
                            <div class="testi-slider-wrapper">
                                <div class="testi-quote-mark">"</div>
                                <div class="testi-slider" id="testi-slider">
                                    <?php foreach ( $settings['testimonials'] as $testi ) : ?>
                                    <div class="testi-slide">
                                        <p class="testi-quote">
                                            <?php echo wp_kses_post( $testi['quote'] ); ?>
                                        </p>
                                        <p class="font-label-caps mt-16" style="color: var(--tertiary);"><?php echo esc_html( $testi['author'] ); ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Slider Controls Mobile -->
                            <div class="testi-controls-mobile md-none mt-32">
                                <div class="testi-progress-bar"><div class="testi-progress-fill" id="testi-progress"></div></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Marquee Section -->
                <section class="marquee-section">
                    <div class="marquee-inner">
                        <span class="marquee-text"><?php echo esc_html( $settings['marquee_text'] ); ?></span>
                        <span class="marquee-text"><?php echo esc_html( $settings['marquee_text'] ); ?></span>
                    </div>
                </section>

                <!-- 6. CTA Section -->
                <section class="cta-section section">
                    <div class="cta-bg"></div>
                    <span class="section-overline font-label-caps"><?php echo esc_html( $settings['cta_overline'] ); ?></span>
                    <h2 class="cta-title font-display-lg mb-12"><?php echo wp_kses_post( $settings['cta_title'] ); ?></h2>
                    <a class="btn-solid magnetic font-label-caps" href="<?php echo esc_url( $settings['cta_btn_link']['url'] ); ?>">
                        <span class="btn-solid-text"><?php echo esc_html( $settings['cta_btn_text'] ); ?></span>
                        <div class="btn-solid-hover"></div>
                    </a>
                </section>
            </div>
        </main>
    </div>
</div>

<div id="scroll-progress"></div>
</div>
        <?php
    }
}
