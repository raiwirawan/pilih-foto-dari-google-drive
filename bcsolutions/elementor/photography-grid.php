<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Photography_Grid_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'bcs_photography_grid';
	}

	public function get_title() {
		return 'BCS Photography Grid';
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'basic' ]; // Or a custom category if BCS uses one
	}

	public function get_style_depends() {
		return [ 'bcs-photography-style' ];
	}

	public function get_script_depends() {
		return [ 'bcs-photography-js' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => 'Gallery Photos',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'image',
			[
				'label' => 'Image',
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'caption',
			[
				'label' => 'Caption (Title)',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Photography Caption',
				'label_block' => true,
			]
		);

		$this->add_control(
			'photos',
			[
				'label' => 'Photos',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[ 'caption' => 'Brand Campaign' ],
					[ 'caption' => 'Product Series' ],
					[ 'caption' => 'Food & Beverage' ],
					[ 'caption' => 'Hotel & Villa' ],
					[ 'caption' => 'Event Coverage' ],
				],
				'title_field' => '{{{ caption }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'menu_section',
			[
				'label' => 'Menu Items',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$menu_repeater = new \Elementor\Repeater();

		$menu_repeater->add_control(
			'menu_text',
			[
				'label' => 'Text',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Menu Item',
				'label_block' => true,
			]
		);

		$menu_repeater->add_control(
			'menu_link',
			[
				'label' => 'Link',
				'type' => \Elementor\Controls_Manager::URL,
				'placeholder' => 'https://your-link.com',
				'default' => [
					'url' => '#',
				],
			]
		);

		$menu_repeater->add_control(
			'menu_id',
			[
				'label' => 'CSS ID (Optional)',
				'type' => \Elementor\Controls_Manager::TEXT,
				'description' => 'Add an ID like "menu-work" for custom interactions.',
			]
		);

		$this->add_control(
			'menu_items',
			[
				'label' => 'Menu Items',
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $menu_repeater->get_controls(),
				'default' => [
					[ 
						'menu_text' => 'Overview', 
						'menu_link' => [ 'url' => 'https://www.bcsolutions.id/' ]
					],
					[ 
						'menu_text' => 'Work', 
						'menu_link' => [ 'url' => '#' ],
						'menu_id' => 'menu-work'
					],
					[ 
						'menu_text' => 'Contact', 
						'menu_link' => [ 'url' => 'https://wa.me/6283854168480?text=Hi%20BC%20Solutions%2C%20I%27m%20interested%20in%20your%20photography%20service', 'is_external' => true, 'nofollow' => true ]
					],
				],
				'title_field' => '{{{ menu_text }}}',
			]
		);
		
		$this->add_control(
			'menu_sub_text',
			[
				'label' => 'Sub Text',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'BC SOLUTIONS — CREATIVE STUDIO, BALI · info@bcsolutions.id',
				'label_block' => true,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$photo_data = [];
		if ( ! empty( $settings['photos'] ) ) {
			foreach ( $settings['photos'] as $item ) {
				$photo_data[] = [
					'src' => $item['image']['url'],
					'cap' => $item['caption'],
					'alt' => get_post_meta( $item['image']['id'] ?? 0, '_wp_attachment_image_alt', true ) ?: $item['caption']
				];
			}
		}

		?>
		<script id="bcs-photo-data" type="application/json">
			<?php echo wp_json_encode($photo_data); ?>
		</script>

		<div id="blob1" class="blob"></div>
		<div id="blob2" class="blob"></div>

		<h1 id="wordmark">BC PHOTOGRAPHY</h1>
		<p class="sr-only">BC Photography by BC Solutions — professional photography in Bali.</p>

		<button id="plus" aria-label="Menu">
		  <svg viewBox="0 0 44 44" fill="none" stroke="currentColor" stroke-width="2.2">
		    <line x1="22" y1="6" x2="22" y2="38"/><line x1="6" y1="22" x2="38" y2="22"/>
		  </svg>
		</button>

		<div id="menu">
			<?php if ( ! empty( $settings['menu_items'] ) ) : ?>
				<?php foreach ( $settings['menu_items'] as $index => $item ) : 
					$link_key = 'menu_link_' . $index;
					if ( ! empty( $item['menu_link']['url'] ) ) {
						$this->add_link_attributes( $link_key, $item['menu_link'] );
					}
					
					if ( ! empty( $item['menu_id'] ) ) {
						$this->add_render_attribute( $link_key, 'id', $item['menu_id'] );
					}
					?>
					<a <?php $this->print_render_attribute_string( $link_key ); ?>><?php echo esc_html( $item['menu_text'] ); ?></a>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if ( ! empty( $settings['menu_sub_text'] ) ) : ?>
				<div class="sub"><?php echo esc_html( $settings['menu_sub_text'] ); ?></div>
			<?php endif; ?>
		</div>

		<div id="world" aria-label="Photography portfolio"></div>
		<div id="spacer"></div>

		<!-- MODE FOKUS -->
		<div id="focus" aria-hidden="true">
		  <div id="rail"></div>
		  <div id="stage">
		    <img id="stageA" src="" alt="">
		    <img id="stageB" src="" alt="">
		    <div id="caption"></div>
		  </div>
		  <button id="back">BACK</button>
		</div>
		<?php
	}
}