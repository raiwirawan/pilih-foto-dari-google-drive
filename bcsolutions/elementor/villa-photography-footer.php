<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BC_Villa_Footer_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'bc_villa_footer'; }
    public function get_title() { return __( 'BC Footer', 'bc-villa' ); }
    public function get_icon() { return 'eicon-code'; }
    public function get_categories() { return [ 'general' ]; }

    public function get_style_depends() {
        return [ 'bcs-villa-photography-style' ];
    }

    public function get_script_depends() {
        return [ 'bcs-villa-photography-js' ];
    }
    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __( 'Footer Content', 'bc-villa' ),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        
        $this->add_control('footer_heading', [
            'label' => __( 'Footer Heading', 'bc-villa' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'BC Villa Photography',
        ]);

        $repeater = new \Elementor\Repeater();
        $repeater->add_control('link_text', [
            'label' => __( 'Link Text', 'bc-villa' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'Link',
        ]);
        $repeater->add_control('link_url', [
            'label' => __( 'Link URL', 'bc-villa' ),
            'type' => \Elementor\Controls_Manager::URL,
            'default' => ['url' => '#'],
        ]);

        $this->add_control('footer_links', [
            'label' => __( 'Footer Links', 'bc-villa' ),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                [ 'link_text' => 'Instagram', 'link_url' => ['url' => '#'] ],
                [ 'link_text' => 'Vimeo', 'link_url' => ['url' => '#'] ],
                [ 'link_text' => 'Journal', 'link_url' => ['url' => '#'] ],
                [ 'link_text' => 'Privacy Policy', 'link_url' => ['url' => '#'] ],
            ]
        ]);

        $this->add_control('footer_copy', [
            'label' => __( 'Copyright Text', 'bc-villa' ),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => '© ' . date('Y') . ' BC VILLA PHOTOGRAPHY. CONVERTING FIRST GLANCES INTO ACTUAL RESERVATIONS.',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
<footer class="site-footer section section-lowest">
    <h2 class="font-headline-lg mb-12"><?php echo esc_html( $settings['footer_heading'] ); ?></h2>
    <div class="footer-links">
        <?php foreach ( $settings['footer_links'] as $link ) : ?>
        <a class="footer-link magnetic font-body-md" href="<?php echo esc_url( $link['link_url']['url'] ); ?>"><?php echo esc_html( $link['link_text'] ); ?></a>
        <?php endforeach; ?>
    </div>
    <p class="footer-copy font-label-caps"><?php echo wp_kses_post( $settings['footer_copy'] ); ?></p>
</footer>
<?php
    }
}
