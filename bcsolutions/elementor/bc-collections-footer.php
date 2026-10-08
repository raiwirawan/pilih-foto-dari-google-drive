<?php
/**
 * Elementor Widget: BC Footer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class BC_Footer_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'bc_footer';
    }

    public function get_title() {
        return 'BC Footer';
    }

    public function get_icon() {
        return 'eicon-footer';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_style_depends() {
        return [ 'bcs-collections-style' ];
    }

    public function get_script_depends() {
        return [ 'bcs-collections-js' ];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_footer_content',
            [
                'label' => 'Footer Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'footer_title',
            [
                'label' => 'Footer Title',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'BC PHOTOGRAPHY',
            ]
        );

        $this->add_control(
            'copyright_text',
            [
                'label' => 'Copyright Text',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '© 2026 BC PHOTOGRAPHY. ALL RIGHTS RESERVED.',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_footer_links',
            [
                'label' => 'Footer Links',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();
        $repeater->add_control(
            'link_label',
            [
                'label' => 'Label',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Link',
            ]
        );
        $repeater->add_control(
            'link_url',
            [
                'label' => 'URL',
                'type' => \Elementor\Controls_Manager::URL,
                'default' => [ 'url' => '#' ],
            ]
        );

        $this->add_control(
            'footer_links',
            [
                'label' => 'Links',
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [ 'link_label' => 'Instagram', 'link_url' => ['url' => '#'] ],
                    [ 'link_label' => 'Archive', 'link_url' => ['url' => '#'] ],
                    [ 'link_label' => 'Terms', 'link_url' => ['url' => '#'] ],
                ],
                'title_field' => '{{{ link_label }}}',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <footer class="bc-footer">
            <div class="bc-footer-container">
                <div class="font-display-lg bc-footer-title">
                    <?php echo esc_html( $settings['footer_title'] ); ?>
                </div>
                
                <?php if ( ! empty( $settings['footer_links'] ) ) : ?>
                <ul class="bc-footer-links font-label-sm">
                    <?php foreach ( $settings['footer_links'] as $item ) : ?>
                    <li>
                        <?php
                        $target = $item['link_url']['is_external'] ? ' target="_blank"' : '';
                        $nofollow = $item['link_url']['nofollow'] ? ' rel="nofollow"' : '';
                        ?>
                        <a href="<?php echo esc_url( $item['link_url']['url'] ); ?>" class="bc-footer-link" <?php echo $target . $nofollow; ?>>
                            <?php echo esc_html( $item['link_label'] ); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div class="font-label-sm bc-footer-copyright">
                    <?php echo esc_html( $settings['copyright_text'] ); ?>
                </div>
            </div>
        </footer>
        <?php
    }
}
