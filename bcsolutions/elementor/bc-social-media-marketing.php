<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Social_Media_Marketing_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'bcs_social_media_marketing';
	}

	public function get_title() {
		return 'BCS Social Media Marketing';
	}

	public function get_icon() {
		return 'eicon-social-icons';
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_style_depends() {
		return [ 'bcs-smm-style', 'google-material-symbols' ];
	}

	public function get_script_depends() {
		return [ 'swiper-js', 'bcs-smm-js' ];
	}

	/* ================================================================
	   CONTROLS
	   ================================================================ */
	protected function _register_controls() {

		$this->start_controls_section( 'content_section', [
			'label' => 'Content',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'bg_text', [
			'label'   => 'Background Typography',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'VIRAL IMPACT',
		]);

		$this->add_control( 'badge_text', [
			'label'   => 'Badge Text',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Digital Marketing',
		]);

		$this->add_control( 'title_part_1', [
			'label'   => 'Title Part 1 (White)',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Social Narrative.',
		]);

		$this->add_control( 'title_part_2', [
			'label'   => 'Title Part 2 (Dim)',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Data-driven viral engagement.',
		]);

		$this->add_control( 'description', [
			'label'   => 'Description',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Transforming brand presence through high-impact, short-form video content and strategic storytelling across major social platforms.',
		]);

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'image', [
			'label' => 'Background Image',
			'type'  => \Elementor\Controls_Manager::MEDIA,
			'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
		]);
		$rep->add_control( 'platform', [
			'label' => 'Platform Badge',
			'type'  => \Elementor\Controls_Manager::TEXT,
			'default' => 'Instagram Reels',
		]);
		$rep->add_control( 'overlay_icon', [
			'label' => 'Overlay Icon',
			'type'  => \Elementor\Controls_Manager::ICONS,
			'default' => [
				'value' => 'fab fa-instagram',
				'library' => 'fa-brands',
			],
		]);
		$rep->add_control( 'overlay_text', [
			'label' => 'Overlay Hover Text',
			'type'  => \Elementor\Controls_Manager::TEXT,
			'default' => 'VISIT',
		]);
		$rep->add_control( 'overlay_url', [
			'label' => 'Overlay URL',
			'type'  => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => '#' ],
		]);
		$rep->add_control( 'category', [
			'label' => 'Category',
			'type'  => \Elementor\Controls_Manager::TEXT,
			'default' => 'Fashion & Lifestyle',
		]);
		$rep->add_control( 'title', [
			'label' => 'Title',
			'type'  => \Elementor\Controls_Manager::TEXT,
			'default' => 'Maison Aura',
		]);
		$rep->add_control( 'title_url', [
			'label' => 'Title URL',
			'type'  => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => '#' ],
		]);
		$rep->add_control( 'desc', [
			'label' => 'Description',
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Viral launch campaign generating 2M+ organic views in 48 hours.',
		]);
		
		$this->add_control( 'cards', [
			'label'   => 'Portfolio Cards',
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => $rep->get_controls(),
			'default' => [
				[
					'platform' => 'Instagram Reels',
					'overlay_icon' => [ 'value' => 'fab fa-instagram', 'library' => 'fa-brands' ],
					'overlay_text' => 'VISIT',
					'category' => 'Fashion & Lifestyle',
					'title'    => 'Maison Aura',
					'desc'     => 'Viral launch campaign generating 2M+ organic views in 48 hours.',
				],
				[
					'platform' => 'TikTok',
					'overlay_icon' => [ 'value' => 'fab fa-tiktok', 'library' => 'fa-brands' ],
					'overlay_text' => 'VISIT',
					'category' => 'Fintech',
					'title'    => 'NeoBank App',
					'desc'     => 'User acquisition strategy focusing on Gen-Z financial literacy.',
				],
				[
					'platform' => 'YouTube Shorts',
					'overlay_icon' => [ 'value' => 'fab fa-youtube', 'library' => 'fa-brands' ],
					'overlay_text' => 'VISIT',
					'category' => 'Luxury Hospitality',
					'title'    => 'The Zenith Hotel',
					'desc'     => 'Immersive visual storytelling increasing direct bookings.',
				],
			],
			'title_field' => '{{{ title }}}',
		]);
		
		$this->add_control( 'view_all_text', [
			'label'   => 'View All Text',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'View All Cases',
		]);
		$this->add_control( 'view_all_link', [
			'label'   => 'View All Link',
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => '#' ],
		]);

		$this->end_controls_section();
	}

	/* ================================================================
	   RENDER
	   ================================================================ */
	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="bcs-smm">
			<section class="smm-section">
				<!-- Background typographic texture -->
				<div class="smm-bg-text">
					<h2><?php echo esc_html($s['bg_text']); ?></h2>
				</div>
				
				<div class="smm-wrap">
					<!-- Section Header -->
					<div class="smm-header">
						<div>
							<span class="smm-badge">
								<?php echo esc_html($s['badge_text']); ?>
							</span>
							<h2 class="smm-title">
								<?php echo esc_html($s['title_part_1']); ?> <br/>
								<span><?php echo esc_html($s['title_part_2']); ?></span>
							</h2>
						</div>
						<div class="smm-desc-wrapper">
							<p class="smm-desc">
								<?php echo esc_html($s['description']); ?>
							</p>
						</div>
					</div>

					<!-- Horizontal Interactive Slider -->
					<div class="swiper-container">
						<!-- Navigation Arrows -->
						<div class="smm-nav">
							<button class="smm-nav-btn smm-nav-prev" aria-label="Previous">
								<span class="material-symbols-outlined">arrow_left_alt</span>
							</button>
							<button class="smm-nav-btn smm-nav-next" aria-label="Next">
								<span class="material-symbols-outlined">arrow_right_alt</span>
							</button>
						</div>

						<!-- Slider Container -->
						<div class="swiper-wrapper">
							<?php if ( ! empty( $s['cards'] ) ) : ?>
								<?php foreach ( $s['cards'] as $card ) : ?>
									<div class="swiper-slide smm-card">
										<div class="smm-card-img-wrap">
											<div class="smm-card-bg" style="background-image: url('<?php echo esc_url($card['image']['url']); ?>');"></div>
											<div class="smm-card-platform">
												<?php echo esc_html($card['platform']); ?>
											</div>
											<a href="<?php echo esc_url($card['overlay_url']['url']); ?>" class="smm-card-overlay-btn" <?php echo !empty($card['overlay_url']['is_external']) ? 'target="_blank"' : ''; ?> <?php echo !empty($card['overlay_url']['nofollow']) ? 'rel="nofollow"' : ''; ?>>
												<span class="smm-overlay-icon">
													<?php \Elementor\Icons_Manager::render_icon( $card['overlay_icon'], [ 'aria-hidden' => 'true' ] ); ?>
												</span>
												<span class="smm-overlay-text"><?php echo esc_html($card['overlay_text']); ?></span>
											</a>
										</div>
										<div class="smm-card-content">
											<span class="smm-card-cat"><?php echo esc_html($card['category']); ?></span>
											<a href="<?php echo esc_url($card['title_url']['url']); ?>" class="smm-card-title-link" <?php echo !empty($card['title_url']['is_external']) ? 'target="_blank"' : ''; ?> <?php echo !empty($card['title_url']['nofollow']) ? 'rel="nofollow"' : ''; ?>>
												<h3 class="smm-card-title">
													<?php echo esc_html($card['title']); ?>
													<span class="material-symbols-outlined smm-card-icon">arrow_outward</span>
												</h3>
											</a>
											<p class="smm-card-text"><?php echo esc_html($card['desc']); ?></p>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>

							<!-- See All Prompt -->
							<div class="swiper-slide">
								<a class="smm-view-all" href="<?php echo esc_url($s['view_all_link']['url']); ?>">
									<div class="smm-view-icon-wrap">
										<span class="material-symbols-outlined">view_cozy</span>
									</div>
									<span class="smm-view-text"><?php echo esc_html($s['view_all_text']); ?></span>
								</a>
							</div>
						</div>
					</div>
					
					<!-- Bottom Decorative Line -->
					<div class="smm-bottom-line"></div>
				</div>
			</section>
		</div>
		<?php
	}
}