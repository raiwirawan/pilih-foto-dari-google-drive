<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Digital_Marketing_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'bcs_digital_marketing';
	}

	public function get_title() {
		return 'BCS Digital Marketing';
	}

	public function get_icon() {
		return 'eicon-marketing';
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_style_depends() {
		return [ 'bcs-dm-style', 'bcs-dm-fonts' ];
	}

	public function get_script_depends() {
		return [ 'bcs-dm-js' ];
	}

	/* ================================================================
	   CONTROLS
	   ================================================================ */
	protected function _register_controls() {

		/* ── HERO ── */
		$this->start_controls_section( 'hero_section', [
			'label' => 'Hero',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$this->add_control( 'hero_title_1', [
			'label'   => 'Title Line 1 (highlighted)',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Digital',
			'label_block' => true,
		]);
		$this->add_control( 'hero_title_2', [
			'label'   => 'Title Line 2',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'marketing',
			'label_block' => true,
		]);
		$this->add_control( 'hero_subtitle', [
			'label'   => 'Subtitle',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'We create world-class digital campaigns and content that communicate clearly.',
		]);
		$this->add_control( 'hero_cta_label', [
			'label'   => 'CTA Label',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Discover BC Solutions',
		]);
		$this->add_control( 'hero_cta_link', [
			'label'   => 'CTA Link',
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => '#services' ],
		]);
		$this->add_control( 'hero_image', [
			'label' => 'Hero Image',
			'type'  => \Elementor\Controls_Manager::MEDIA,
			'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
		]);
		$this->add_control( 'hero_card_text', [
			'label'   => 'Hey Card Text',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'World-class digital media agency.',
			'label_block' => true,
		]);
		$this->add_control( 'hero_vertical_tag', [
			'label'   => 'Vertical Tag Text',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Award winning agency',
		]);
		$this->end_controls_section();

		/* ── CLIENTS ── */
		$this->start_controls_section( 'clients_section', [
			'label' => 'Clients',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$rep_c = new \Elementor\Repeater();
		$rep_c->add_control( 'name', [
			'label'   => 'Client Name',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Client Name',
			'label_block' => true,
		]);
		$this->add_control( 'clients', [
			'label'   => 'Client Names',
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => $rep_c->get_controls(),
			'default' => [
				[ 'name' => 'Uno Pizza and Roasted Chicken' ],
				[ 'name' => 'Rida Farm and Diary' ],
				[ 'name' => 'Surya Adi Guna Solutions' ],
				[ 'name' => 'Kawiya Healing Sanctuary' ],
				[ 'name' => 'Green Oasis Retreats Bali' ],
				[ 'name' => 'Pasih Seseh Villa and Apartments' ],
				[ 'name' => 'Bali Summer Hotel and BS Kitchen Kuta' ],
				[ 'name' => 'Umadhatu Resorts and Umadhatu Waterpark' ],
				[ 'name' => 'Bali Jungle Camping' ],
				[ 'name' => 'The Lotus Residence Bali' ],
				[ 'name' => 'The Amerta Jungle Retreats' ],
			],
			'title_field' => '{{{ name }}}',
		]);
		$this->end_controls_section();

		/* ── ABOUT ── */
		$this->start_controls_section( 'about_section', [
			'label' => 'About',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$this->add_control( 'about_heading', [
			'label'   => 'About Heading',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'We are a creative bold digital agency based in',
			'label_block' => true,
		]);
		$this->add_control( 'about_heading_hl', [
			'label'   => 'Highlighted Word',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Bali.',
		]);
		$this->add_control( 'about_years', [
			'label'   => 'Years Count',
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 7,
		]);
		$this->add_control( 'about_exp_title', [
			'label'   => 'Experience Title',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '7+ Years of experience',
			'label_block' => true,
		]);
		$this->add_control( 'about_exp_desc', [
			'label'   => 'Experience Description',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'We are dedicated to providing outstanding digital marketing and design services that meet the functional and aesthetic.',
		]);
		$rep_s = new \Elementor\Repeater();
		$rep_s->add_control( 'number', [
			'label'   => 'Stat Number',
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
		]);
		$rep_s->add_control( 'text', [
			'label'   => 'Stat Text',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'very satisfied clients.',
			'label_block' => true,
		]);
		$rep_s->add_control( 'icon_svg', [
			'label'   => 'Icon SVG (inner paths only)',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
		]);
		$this->add_control( 'stats', [
			'label'   => 'Stats',
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => $rep_s->get_controls(),
			'default' => [
				[ 'number' => 50,  'text' => 'very satisfied clients across Bali and beyond.', 'icon_svg' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>' ],
				[ 'number' => 300, 'text' => 'successful campaigns for our digital media clients.', 'icon_svg' => '<path d="M3 11l18-8-8 18-2-8-8-2z"/>' ],
				[ 'number' => 750, 'text' => 'pieces of content produced in one year.', 'icon_svg' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>' ],
			],
			'title_field' => '{{{ number }}}+ {{{ text }}}',
		]);
		$this->end_controls_section();

		/* ── SERVICES ── */
		$this->start_controls_section( 'services_section', [
			'label' => 'Services',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$this->add_control( 'svc_heading', [
			'label'   => 'Services Heading',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "It's so challenging to find a good team to do great things. But we can provide you the best one.",
		]);
		$rep_sv = new \Elementor\Repeater();
		$rep_sv->add_control( 'title', [
			'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => 'Social Media', 'label_block' => true,
		]);
		$rep_sv->add_control( 'desc', [
			'label' => 'Description', 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Service description here.',
		]);
		$rep_sv->add_control( 'icon_svg', [
			'label' => 'Icon SVG (inner paths)', 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '<path d="M3 11l18-8-8 18-2-8-8-2z"/>',
		]);
		$rep_sv->add_control( 'badge', [
			'label' => 'Badge Text (optional)', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		]);
		$this->add_control( 'services', [
			'label'  => 'Services',
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $rep_sv->get_controls(),
			'default' => [
				[ 'title' => 'Social Media', 'badge' => 'Popular', 'desc' => 'Consistent content, community management and growth strategy for Instagram, TikTok and Facebook — handled end to end.', 'icon_svg' => '<path d="M3 11l18-8-8 18-2-8-8-2z"/>' ],
				[ 'title' => 'Digital Advertising', 'desc' => 'Meta Ads and Google Ads that reach the right audience with the right budget — planned, launched and optimised for conversions.', 'icon_svg' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/>' ],
				[ 'title' => 'SEO Optimization', 'desc' => 'Data-driven search engine optimisation to boost your organic rankings, increase visibility and drive targeted traffic.', 'icon_svg' => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/>' ],
				[ 'title' => 'Content Creation', 'desc' => 'Photography, video, reels and copywriting produced by our in-house creative studio to make your brand impossible to ignore.', 'icon_svg' => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>' ],
			],
			'title_field' => '{{{ title }}}',
		]);
		$this->add_control( 'svc_foot_text', [
			'label'   => 'Footer Text',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Save your precious time and effort spent for finding a solution.',
			'label_block' => true,
		]);
		$this->add_control( 'svc_foot_link_text', [
			'label'   => 'Footer Link Text',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Contact us now',
		]);
		$this->add_control( 'svc_foot_link', [
			'label'   => 'Footer Link',
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => 'https://wa.me/6283854168480?text=Hi%20BC%20Solutions%2C%20I%20need%20a%20digital%20marketing%20solution' ],
		]);
		$this->end_controls_section();

		/* ── CASE STUDIES ── */
		$this->start_controls_section( 'work_section', [
			'label' => 'Case Studies',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$rep_w = new \Elementor\Repeater();
		$rep_w->add_control( 'image', [
			'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA,
			'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
		]);
		$rep_w->add_control( 'title', [
			'label' => 'Project Title', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => 'Project Name', 'label_block' => true,
		]);
		$rep_w->add_control( 'category', [
			'label' => 'Category Slug', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => 'hotel', 'description' => 'e.g. hotel, villa, restaurant, resort, glamping',
		]);
		$rep_w->add_control( 'category_label', [
			'label' => 'Category Label', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => 'Social Media',
		]);
		$this->add_control( 'works', [
			'label'  => 'Projects',
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $rep_w->get_controls(),
			'default' => [
				[ 'title' => 'Uno Pizza and Roasted Chicken',         'category' => 'restaurant',   'category_label' => 'Restaurant' ],
				[ 'title' => 'Rida Farm and Diary',                   'category' => 'umkm',         'category_label' => 'UMKM' ],
				[ 'title' => 'Surya Adi Guna Solutions',              'category' => 'it-solutions', 'category_label' => 'IT Solutions' ],
				[ 'title' => 'Kawiya Healing Sanctuary',              'category' => 'wellness',     'category_label' => 'Wellness' ],
				[ 'title' => 'Green Oasis Retreats Bali',             'category' => 'retreat',      'category_label' => 'Retreat' ],
				[ 'title' => 'Pasih Seseh Villa and Apartments',      'category' => 'villa',        'category_label' => 'Villa' ],
				[ 'title' => 'Bali Summer Hotel and BS Kitchen Kuta', 'category' => 'hotel',        'category_label' => 'Hotel' ],
				[ 'title' => 'Umadhatu Resorts and Umadhatu Waterpark', 'category' => 'resort',    'category_label' => 'Resort' ],
				[ 'title' => 'Bali Jungle Camping',                  'category' => 'glamping',     'category_label' => 'Glamping' ],
				[ 'title' => 'The Lotus Residence Bali',              'category' => 'villa',        'category_label' => 'Villa' ],
				[ 'title' => 'The Amerta Jungle Retreats',            'category' => 'retreat',      'category_label' => 'Retreat' ],
			],
			'title_field' => '{{{ title }}} — {{{ category_label }}}',
		]);
		$this->end_controls_section();

		/* ── METRICS ── */
		$this->start_controls_section( 'metrics_section', [
			'label' => 'Metrics',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$this->add_control( 'metrics_heading', [
			'label'   => 'Heading',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Intuition and strategy integrate the research methodology that we also apply to',
		]);
		$this->add_control( 'metrics_heading_hl', [
			'label'   => 'Highlighted Word',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'digital',
		]);
		$this->add_control( 'metrics_desc', [
			'label'   => 'Description',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'We combine human empathy and intelligent data to provide the highest level of satisfaction.',
		]);
		$this->add_control( 'metrics_cta_text', [
			'label'   => 'CTA Text',
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => "Let's talk now ✉",
		]);
		$this->add_control( 'metrics_cta_link', [
			'label'   => 'CTA Link',
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => 'https://wa.me/6283854168480?text=Hi%20BC%20Solutions%2C%20let%27s%20talk%20about%20digital%20marketing' ],
		]);
		$rep_m = new \Elementor\Repeater();
		$rep_m->add_control( 'desc', [
			'label' => 'Description', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => 'Metric description.', 'label_block' => true,
		]);
		$rep_m->add_control( 'number', [
			'label' => 'Number', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 100,
		]);
		$rep_m->add_control( 'suffix', [
			'label' => 'Suffix', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'K+',
		]);
		$this->add_control( 'metrics', [
			'label'  => 'Metrics',
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $rep_m->get_controls(),
			'default' => [
				[ 'desc' => 'Audience reached by campaigns we ran in 2026.', 'number' => 850, 'suffix' => 'K+' ],
				[ 'desc' => 'Successfully finished campaigns with creativity.', 'number' => 300, 'suffix' => '+' ],
				[ 'desc' => 'Monthly visitors on accounts we manage.', 'number' => 120, 'suffix' => 'K+' ],
				[ 'desc' => 'Average engagement growth increased.', 'number' => 45, 'suffix' => '%' ],
			],
			'title_field' => '↑{{{ number }}}{{{ suffix }}}',
		]);
		$this->end_controls_section();

		/* ── CTA DARK ── */
		$this->start_controls_section( 'cta_section', [
			'label' => 'CTA Dark',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$this->add_control( 'cta_headline', [
			'label'   => 'Headline',
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Forward thinking team of strategists, creators and marketers.',
		]);
		$this->add_control( 'cta_bg_image', [
			'label' => 'Background Spiral Image',
			'type'  => \Elementor\Controls_Manager::MEDIA,
			'default' => [ 'url' => '' ],
		]);
		$rep_aw = new \Elementor\Repeater();
		$rep_aw->add_control( 'logo', [
			'label' => 'Award Title', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => 'Meta Ads', 'label_block' => true,
		]);
		$rep_aw->add_control( 'label', [
			'label' => 'Award Label', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => 'Campaigns that convert',
		]);
		$this->add_control( 'awards', [
			'label'  => 'Awards',
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $rep_aw->get_controls(),
			'default' => [
				[ 'logo' => 'Meta Ads',   'label' => 'Campaigns that convert' ],
				[ 'logo' => 'Google Ads', 'label' => 'Search & display experts' ],
			],
			'title_field' => '{{{ logo }}}',
		]);
		$this->end_controls_section();

		/* ── JOURNAL (otomatis dari Posts WordPress) ── */
		$this->start_controls_section( 'journal_section', [
			'label' => 'Journal',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$this->add_control( 'journal_read_all_link', [
			'label'   => 'Read All Posts Link',
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => 'https://www.bcsolutions.id/bc-solutions-news-page' ],
		]);
		$this->add_control( 'journal_count', [
			'label'   => 'Jumlah Post Ditampilkan',
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 9,
		]);
		$cat_options = [ '' => 'Semua Kategori' ];
		foreach ( get_categories( [ 'hide_empty' => false ] ) as $jcat ) {
			$cat_options[ $jcat->term_id ] = $jcat->name;
		}
		$this->add_control( 'journal_category', [
			'label'   => 'Filter Kategori (opsional)',
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => $cat_options,
			'default' => '',
		]);
		$this->end_controls_section();

		/* ── MARQUEE ── */
		$this->start_controls_section( 'marquee_section', [
			'label' => 'Marquee',
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);
		$rep_mq = new \Elementor\Repeater();
		$rep_mq->add_control( 'word', [
			'label' => 'Word', 'type' => \Elementor\Controls_Manager::TEXT,
			'default' => 'strategy.', 'label_block' => true,
		]);
		$rep_mq->add_control( 'filled', [
			'label' => 'Filled (solid)?',
			'type'  => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
		]);
		$this->add_control( 'marquee_words', [
			'label'  => 'Marquee Words',
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $rep_mq->get_controls(),
			'default' => [
				[ 'word' => 'strategy.' ],
				[ 'word' => 'growth.', 'filled' => 'yes' ],
				[ 'word' => 'content.' ],
				[ 'word' => 'advertising.' ],
			],
			'title_field' => '{{{ word }}}',
		]);
		$this->end_controls_section();
	}

	/* ================================================================
	   RENDER
	   ================================================================ */
	protected function render() {
		$s = $this->get_settings_for_display();
		$uid = 'bcs-dm-' . $this->get_id();

		// Collect filter categories from works
		$cats = [];
		if ( ! empty( $s['works'] ) ) {
			foreach ( $s['works'] as $w ) {
				$slug  = sanitize_title( $w['category'] );
				$label = $w['category_label'];
				if ( $slug && ! isset( $cats[ $slug ] ) ) {
					$cats[ $slug ] = $label;
				}
			}
		}
		?>
		<div class="bcs-dm" id="<?php echo esc_attr( $uid ); ?>">

		<!-- HERO -->
		<div class="dm-hero">
			<div class="dm-hero-left">
				<div class="dm-hero-copy">
					<h1 class="au"><span class="dm-hl"><?php echo esc_html( $s['hero_title_1'] ); ?></span><br><?php echo esc_html( $s['hero_title_2'] ); ?></h1>
					<p class="dm-lead au"><?php echo esc_html( $s['hero_subtitle'] ); ?></p>
					<a class="dm-circle-cta au" href="<?php echo esc_url( $s['hero_cta_link']['url'] ?? '#' ); ?>">
						<span class="dm-circle">→</span>
						<span class="dm-label"><?php echo esc_html( $s['hero_cta_label'] ); ?></span>
					</a>
				</div>
				<div class="dm-vertical-tag" aria-hidden="true"><?php echo esc_html( $s['hero_vertical_tag'] ); ?></div>
			</div>
			<div class="dm-hero-right">
				<img src="<?php echo esc_url( $s['hero_image']['url'] ); ?>" alt="<?php echo esc_attr( $s['hero_title_1'] . ' ' . $s['hero_title_2'] ); ?>">
				<div class="dm-hey-card au">
					<span class="dm-go">↗</span>
					<span class="dm-hey">hey!</span>
					<h4><?php echo esc_html( $s['hero_card_text'] ); ?></h4>
				</div>
			</div>
		</div>

		<!-- CLIENTS -->
		<?php if ( ! empty( $s['clients'] ) ) : ?>
		<div class="dm-clients" aria-label="Our clients">
			<div class="dm-clients-track">
				<?php foreach ( $s['clients'] as $c ) : ?>
					<span class="dm-name"><?php echo esc_html( $c['name'] ); ?></span>
					<span class="dm-name-dot" aria-hidden="true">·</span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<!-- ABOUT -->
		<div class="dm-about" id="dm-about">
			<div class="dm-wrap dm-grid">
				<h2 class="au"><?php echo esc_html( $s['about_heading'] ); ?> <span class="dm-ul"><?php echo esc_html( $s['about_heading_hl'] ); ?></span></h2>
				<div class="dm-exp au">
					<div class="dm-big-circle"><span class="dm-count" data-to="<?php echo intval( $s['about_years'] ); ?>">0</span><sup>+</sup></div>
					<div class="dm-exp-text">
						<h4><?php echo esc_html( $s['about_exp_title'] ); ?></h4>
						<p><?php echo esc_html( $s['about_exp_desc'] ); ?></p>
					</div>
				</div>
			</div>
			<?php if ( ! empty( $s['stats'] ) ) : ?>
			<div class="dm-wrap dm-stats-row">
				<?php foreach ( $s['stats'] as $st ) : ?>
				<div class="dm-stat au">
					<p><b><span class="dm-count" data-to="<?php echo intval( $st['number'] ); ?>">0</span>+</b> <?php echo esc_html( $st['text'] ); ?></p>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><?php echo $st['icon_svg']; ?></svg>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<!-- SERVICES -->
		<div class="dm-services" id="dm-services">
			<div class="dm-wrap">
				<h2 class="dm-big au"><?php echo esc_html( $s['svc_heading'] ); ?></h2>
				<?php if ( ! empty( $s['services'] ) ) :
					foreach ( $s['services'] as $i => $sv ) :
						$num = str_pad( $i + 1, 2, '0', STR_PAD_LEFT );
				?>
				<div class="dm-svc-row au">
					<span class="dm-no"><?php echo $num; ?></span>
					<h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><?php echo $sv['icon_svg']; ?></svg> <?php echo esc_html( $sv['title'] ); ?><?php if ( ! empty( $sv['badge'] ) ) : ?> <span class="dm-badge"><?php echo esc_html( $sv['badge'] ); ?></span><?php endif; ?></h3>
					<p><?php echo esc_html( $sv['desc'] ); ?></p>
				</div>
				<?php endforeach; endif; ?>
				<p class="dm-svc-foot au"><?php echo esc_html( $s['svc_foot_text'] ); ?> <a href="<?php echo esc_url( $s['svc_foot_link']['url'] ?? '#' ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $s['svc_foot_link_text'] ); ?></a></p>
			</div>
		</div>

		<!-- CASE STUDIES -->
		<div class="dm-work" id="dm-work">
			<div class="dm-wrap">
				<div class="dm-head">
					<h2 class="au">Case studies</h2>
					<div class="dm-filters au">
						<button class="active" data-f="all">All</button>
						<?php foreach ( $cats as $slug => $label ) : ?>
							<button data-f="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="dm-work-grid">
					<?php if ( ! empty( $s['works'] ) ) : foreach ( $s['works'] as $w ) : ?>
					<div class="dm-work-item au" data-cat="<?php echo esc_attr( sanitize_title( $w['category'] ) ); ?>">
						<div class="dm-im"><img src="<?php echo esc_url( $w['image']['url'] ); ?>" alt="<?php echo esc_attr( $w['title'] ); ?>" loading="lazy"></div>
						<div class="dm-cap"><b><?php echo esc_html( $w['title'] ); ?></b><span><?php echo esc_html( $w['category_label'] ); ?></span></div>
					</div>
					<?php endforeach; endif; ?>
				</div>
			</div>
		</div>

		<!-- METRICS -->
		<div class="dm-metrics">
			<div class="dm-wrap">
				<h2 class="au"><?php echo esc_html( $s['metrics_heading'] ); ?> <span class="dm-ul"><?php echo esc_html( $s['metrics_heading_hl'] ); ?></span> media.</h2>
				<div class="dm-grid">
					<div class="dm-left au">
						<p><?php echo esc_html( $s['metrics_desc'] ); ?></p>
						<a class="dm-btn dm-dark" href="<?php echo esc_url( $s['metrics_cta_link']['url'] ?? '#' ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $s['metrics_cta_text'] ); ?></a>
					</div>
					<?php if ( ! empty( $s['metrics'] ) ) : ?>
					<div class="dm-cells">
						<?php foreach ( $s['metrics'] as $mt ) : ?>
						<div class="dm-cell au">
							<p><?php echo esc_html( $mt['desc'] ); ?></p>
							<div class="dm-num">↑<span class="dm-count" data-to="<?php echo intval( $mt['number'] ); ?>">0</span><?php echo esc_html( $mt['suffix'] ); ?></div>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- CTA DARK -->
		<div class="dm-cta-dark"<?php if ( ! empty( $s['cta_bg_image']['url'] ) ) : ?> style="background-image:url('<?php echo esc_url( $s['cta_bg_image']['url'] ); ?>')"<?php endif; ?>>
			<div class="dm-wrap">
				<div class="au dm-headline"><?php echo esc_html( $s['cta_headline'] ); ?></div>
				<?php if ( ! empty( $s['awards'] ) ) : ?>
				<div class="dm-awards">
					<?php foreach ( $s['awards'] as $aw ) : ?>
					<div class="dm-award au">
						<span class="dm-aw-logo"><?php echo esc_html( $aw['logo'] ); ?></span>
						<hr>
						<span class="dm-aw-label"><?php echo esc_html( $aw['label'] ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- JOURNAL -->
		<div class="dm-journal" id="dm-journal">
			<div class="dm-wrap">
				<div class="dm-head">
					<h2 class="au">Our journal</h2>
					<a class="dm-readall au" href="<?php echo esc_url( $s['journal_read_all_link']['url'] ?? '#' ); ?>">Read all posts →</a>
				</div>
				<?php
				$args = [
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => ! empty( $s['journal_count'] ) ? intval( $s['journal_count'] ) : 3,
				];
				if ( ! empty( $s['journal_category'] ) ) {
					$args['cat'] = $s['journal_category'];
				}
				$journal_query = new \WP_Query( $args );

				if ( $journal_query->have_posts() ) :
				?>
				<div class="dm-grid">
					<?php
					while ( $journal_query->have_posts() ) : $journal_query->the_post();
						$bg = get_the_post_thumbnail_url( get_the_ID(), 'large' );
						if ( ! $bg ) {
							// Fallback placeholder jika post tidak punya Featured Image
							$bg = \Elementor\Utils::get_placeholder_image_src();
						}
						// Paksa HTTPS untuk mencegah error Mixed Content (terlihat di console Anda)
						$bg = set_url_scheme( $bg, 'https' );

						$categories = get_the_category();
						$chip = ! empty( $categories ) ? $categories[0]->name : 'News';
					?>
					<a class="dm-post au" href="<?php echo esc_url( get_permalink() ); ?>">
						<div class="dm-hover-bg" style="background-image:url('<?php echo esc_url( $bg ); ?>')"></div>
						<div class="dm-content">
							<span class="dm-chip"><?php echo esc_html( $chip ); ?></span>
							<div class="dm-body">
								<span class="dm-author"><?php echo esc_html( get_the_author() ); ?></span>
								<h3><?php echo wp_kses_post( get_the_title() ); ?></h3>
							</div>
						</div>
					</a>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- MARQUEE -->
		<?php if ( ! empty( $s['marquee_words'] ) ) : ?>
		<div class="dm-marquee" aria-hidden="true">
			<div class="dm-track">
				<?php foreach ( $s['marquee_words'] as $mw ) : ?>
					<span class="dm-word<?php echo $mw['filled'] === 'yes' ? ' dm-fill' : ''; ?>"><?php echo esc_html( $mw['word'] ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		</div><!-- /.bcs-dm -->
		<?php
	}
}
