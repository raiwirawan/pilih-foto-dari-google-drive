<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class BClinnk_Main_Page_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'bclinnk_main_page';
	}

	public function get_title() {
		return esc_html__( 'BCLinnk Epic Hero & Showcase', 'bclinnk' );
	}

	public function get_icon() {
		return 'eicon-star-o';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function register_controls() {

		// ================= HERO SECTION =================
		$this->start_controls_section(
			'hero_content_section',
			[
				'label' => esc_html__( 'Hero Content', 'bclinnk' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'hero_bg_image',
			[
				'label' => esc_html__( 'Hero Background Image', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => '',
				],
			]
		);

		$this->add_control(
			'dashboard_image',
			[
				'label' => esc_html__( '3D Dashboard Image', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=800&q=80',
				],
			]
		);

		$this->add_control(
			'brand_text',
			[
				'label' => esc_html__( 'Brand Text', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Innovation // 2024', 'bclinnk' ),
			]
		);

		$this->add_control(
			'hero_title_1',
			[
				'label' => esc_html__( 'Hero Title Line 1', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'BCLinnk:', 'bclinnk' ),
			]
		);

		$this->add_control(
			'hero_title_2',
			[
				'label' => esc_html__( 'Hero Title Line 2', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'The Future', 'bclinnk' ),
			]
		);

		$this->add_control(
			'hero_title_3',
			[
				'label' => esc_html__( 'Hero Title Line 3 (Hollow)', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'of Booking.', 'bclinnk' ),
			]
		);

		$this->add_control(
			'hero_description',
			[
				'label' => esc_html__( 'Hero Description', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'A relentless, high-performance platform designed to simplify room bookings and elevate management precision for the modern hospitality industry.', 'bclinnk' ),
			]
		);

		$this->add_control(
			'hero_cta_text',
			[
				'label' => esc_html__( 'Hero CTA Text', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Explore', 'bclinnk' ),
			]
		);

		$this->add_control(
			'hero_cta_link',
			[
				'label' => esc_html__( 'Hero CTA Link', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'bclinnk' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$repeater_tags = new \Elementor\Repeater();
		$repeater_tags->add_control(
			'tag_text',
			[
				'label' => esc_html__( 'Tag Text', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Real-time Analytics' , 'bclinnk' ),
				'label_block' => true,
			]
		);
		$repeater_tags->add_control(
			'has_pulse',
			[
				'label' => esc_html__( 'Show Pulse Dot?', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'bclinnk' ),
				'label_off' => esc_html__( 'No', 'bclinnk' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);
		$this->add_control(
			'hero_tags',
			[
				'label' => esc_html__( 'Detail Tags', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater_tags->get_controls(),
				'default' => [
					[ 'tag_text' => esc_html__( 'Real-time Analytics', 'bclinnk' ), 'has_pulse' => 'yes' ],
					[ 'tag_text' => esc_html__( 'Seamless Booking', 'bclinnk' ), 'has_pulse' => 'no' ],
				],
				'title_field' => '{{{ tag_text }}}',
			]
		);

		$repeater_stats = new \Elementor\Repeater();
		$repeater_stats->add_control(
			'stat_val',
			[
				'label' => esc_html__( 'Value', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( '99.9%' , 'bclinnk' ),
			]
		);
		$repeater_stats->add_control(
			'stat_label',
			[
				'label' => esc_html__( 'Label', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Uptime' , 'bclinnk' ),
			]
		);
		$this->add_control(
			'hero_stats',
			[
				'label' => esc_html__( 'Hero Stats', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater_stats->get_controls(),
				'default' => [
					[ 'stat_val' => '99.9%', 'stat_label' => 'Uptime' ],
					[ 'stat_val' => '0.5s', 'stat_label' => 'Latency' ],
					[ 'stat_val' => 'Global', 'stat_label' => 'Scale' ],
				],
				'title_field' => '{{{ stat_label }}}',
			]
		);

		$this->end_controls_section();


		// ================= SHOWCASE SECTION =================
		$this->start_controls_section(
			'showcase_content_section',
			[
				'label' => esc_html__( 'Showcase Content', 'bclinnk' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'marquee_prefix',
			[
				'label' => esc_html__( 'Marquee Text Prefix', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'KAMI JUGA PUNYA', 'bclinnk' ),
			]
		);

		$this->add_control(
			'marquee_highlight',
			[
				'label' => esc_html__( 'Marquee Highlight', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'BCLINNK', 'bclinnk' ),
			]
		);

		$this->add_control(
			'showcase_title_1',
			[
				'label' => esc_html__( 'Showcase Title 1 (Outline)', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Next-Gen', 'bclinnk' ),
			]
		);

		$this->add_control(
			'showcase_title_2',
			[
				'label' => esc_html__( 'Showcase Title 2', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Ecosystem.', 'bclinnk' ),
			]
		);

		$this->add_control(
			'showcase_description',
			[
				'label' => esc_html__( 'Showcase Description', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Terintegrasi penuh dengan ekosistem kami. BCLinnk memberikan solusi menyeluruh untuk sistem reservasi dan manajemen bisnis Anda tanpa hambatan. Sebuah mahakarya digital.', 'bclinnk' ),
			]
		);

		$this->add_control(
			'showcase_cta_text',
			[
				'label' => esc_html__( 'Showcase CTA Text', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Pelajari BCLinnk', 'bclinnk' ),
			]
		);

		$this->add_control(
			'showcase_cta_link',
			[
				'label' => esc_html__( 'Showcase CTA Link', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'bclinnk' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$repeater_cards = new \Elementor\Repeater();
		$repeater_cards->add_control(
			'card_icon',
			[
				'label' => esc_html__( 'Icon (Material Symbols)', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'language' , 'bclinnk' ),
			]
		);
		$repeater_cards->add_control(
			'card_title',
			[
				'label' => esc_html__( 'Title', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Terhubung' , 'bclinnk' ),
			]
		);
		$repeater_cards->add_control(
			'card_desc',
			[
				'label' => esc_html__( 'Description', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Konektivitas tanpa batas ke berbagai platform.' , 'bclinnk' ),
			]
		);
		$repeater_cards->add_control(
			'card_animation',
			[
				'label' => esc_html__( 'Animation Type', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'float-up',
				'options' => [
					'float-up' => esc_html__( 'Float Up', 'bclinnk' ),
					'float-down' => esc_html__( 'Float Down', 'bclinnk' ),
				],
			]
		);

		$this->add_control(
			'showcase_cards',
			[
				'label' => esc_html__( 'Showcase Cards', 'bclinnk' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater_cards->get_controls(),
				'default' => [
					[ 'card_icon' => 'language', 'card_title' => 'Terhubung', 'card_desc' => 'Konektivitas tanpa batas ke berbagai platform.', 'card_animation' => 'float-up' ],
					[ 'card_icon' => 'bolt', 'card_title' => 'Kilat', 'card_desc' => 'Kecepatan pemrosesan data real-time.', 'card_animation' => 'float-down' ],
				],
				'title_field' => '{{{ card_title }}}',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		
		<div class="bclinnk-epic-wrapper">
			<?php 
			$bg_image_url = ! empty( $settings['hero_bg_image']['url'] ) ? $settings['hero_bg_image']['url'] : '';
			?>
			<section class="hero-awwwards" style="<?php echo $bg_image_url ? 'background-image: url(' . esc_url( $bg_image_url ) . '); background-size: cover; background-position: center;' : ''; ?>">
				<!-- Abstract Background Layers -->
				<div class="webgl-bg">
					<canvas class="bclinnk-shader-canvas"></canvas>
				</div>
				<div class="grid-overlay"></div>
				<div class="noise-overlay"></div>

				<!-- Central 3D Canvas wrapper -->
				<div class="canvas-wrapper">
					<div class="bclinnk-three-container" data-image="<?php echo esc_url( $settings['dashboard_image']['url'] ); ?>"></div>
				</div>

				<!-- UI / HUD Layer -->
				<div class="ui-layer">
					<!-- Decorative Framing Lines -->
					<div class="deco-line line-v"></div>
					<div class="deco-line line-v-right"></div>
					<div class="deco-line line-h"></div>
					<div class="deco-line line-h-top"></div>

					<!-- Top Header -->
					<header class="hud-header fade-in-down delay-100">
						<div class="bclinnk-brand"><?php echo esc_html( $settings['brand_text'] ); ?></div>
						<div class="detail-tags">
							<?php foreach ( $settings['hero_tags'] as $index => $tag ) : ?>
								<span class="bclinnk-tag">
									<?php if ( 'yes' === $tag['has_pulse'] ) : ?>
										<span class="pulse-dot"></span>
									<?php endif; ?>
									<?php echo esc_html( $tag['tag_text'] ); ?>
								</span>
							<?php endforeach; ?>
						</div>
					</header>

					<!-- Massive Epic Typography -->
					<div class="bclinnk-title-container">
						<h1 class="epic-title">
							<span class="bclinnk-title-line"><span class="delay-200"><?php echo esc_html( $settings['hero_title_1'] ); ?></span></span>
							<span class="bclinnk-title-line"><span class="delay-300"><?php echo esc_html( $settings['hero_title_2'] ); ?></span></span>
							<span class="bclinnk-title-line"><span class="hollow-text kinetic-line delay-400"><?php echo esc_html( $settings['hero_title_3'] ); ?></span></span>
						</h1>
					</div>

					<!-- Bottom UI Area -->
					<div class="hud-bottom">
						<!-- Left Glass Panel -->
						<div class="hud-bottom-left hud-glass-panel slide-up-glass delay-500">
							<p class="bclinnk-description">
								<?php echo wp_kses_post( $settings['hero_description'] ); ?>
							</p>
							<div class="stats-mini">
								<?php foreach ( $settings['hero_stats'] as $index => $stat ) : ?>
									<div class="bclinnk-stat">
										<span class="bclinnk-stat-val"><?php echo esc_html( $stat['stat_val'] ); ?></span>
										<span class="bclinnk-stat-label"><?php echo esc_html( $stat['stat_label'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<!-- Right Circular CTA -->
						<div class="hud-bottom-right slide-up-glass delay-600">
							<a class="creative-btn" href="<?php echo esc_url( $settings['hero_cta_link']['url'] ); ?>">
								<span class="btn-bg"></span>
								<span class="btn-content">
									<?php echo esc_html( $settings['hero_cta_text'] ); ?>
									<span class="material-symbols-outlined">north_east</span>
								</span>
							</a>
						</div>
					</div>
				</div>
			</section>

			<!-- New Section: BCLinnk Showcase -->
			<section class="bclinnk-showcase">
				<!-- Running Marquee Banner -->
				<div class="marquee-container">
					<div class="marquee-track">
						<?php for ( $i = 0; $i < 2; $i++ ) : // Duplicate for infinite scroll ?>
							<div class="marquee-content">
								<?php for ( $j = 0; $j < 3; $j++ ) : // Repeat within track ?>
									<?php echo esc_html( $settings['marquee_prefix'] ); ?> 
									<span class="marquee-highlight"><?php echo esc_html( $settings['marquee_highlight'] ); ?></span> 
									<span class="marquee-dot">&bull;</span>
								<?php endfor; ?>
							</div>
						<?php endfor; ?>
					</div>
				</div>

				<div class="showcase-inner">
					<div class="showcase-text">
						<h2 class="showcase-title">
							<span class="outline-text"><?php echo esc_html( $settings['showcase_title_1'] ); ?></span><br>
							<?php echo esc_html( $settings['showcase_title_2'] ); ?>
						</h2>
						<p class="showcase-desc">
							<?php echo wp_kses_post( $settings['showcase_description'] ); ?>
						</p>
						<a class="arrow-link group" href="<?php echo esc_url( $settings['showcase_cta_link']['url'] ); ?>">
							<div class="arrow-circle">
								<span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">east</span>
							</div>
							<span class="link-text"><?php echo esc_html( $settings['showcase_cta_text'] ); ?></span>
						</a>
					</div>
					
					<div class="showcase-visual">
						<!-- Abstract Floating Layout -->
						<div class="visual-grid">
							<?php foreach ( $settings['showcase_cards'] as $index => $card ) : ?>
								<div class="glass-card <?php echo esc_attr( $card['card_animation'] ); ?>">
									<span class="material-symbols-outlined icon-large text-accent"><?php echo esc_html( $card['card_icon'] ); ?></span>
									<h4><?php echo esc_html( $card['card_title'] ); ?></h4>
									<p><?php echo wp_kses_post( $card['card_desc'] ); ?></p>
								</div>
							<?php endforeach; ?>
							<div class="glowing-orb"></div>
						</div>
					</div>
				</div>
			</section>
		</div>

		<?php
	}
}
