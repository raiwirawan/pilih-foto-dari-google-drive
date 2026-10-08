<?php
/**
 * Elementor Widget: BC Header
 *
 * Header minimal dengan Logo + View Toggle (Grid 2-kolom / List 1-kolom).
 * Menu navigasi antar halaman opsional.
 * Toggle memengaruhi tampilan .bc-collections-grid di halaman yang sama.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class BC_Header_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'bc_header';
    }

    public function get_title() {
        return 'BC Header (Nav)';
    }

    public function get_icon() {
        return 'eicon-header';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_style_depends() {
        return [ 'bcs-collections-style', 'google-material-symbols' ];
    }

    public function get_script_depends() {
        return [ 'bcs-collections-js' ];
    }

    protected function register_controls() {

        // ─── Logo ───────────────────────────────────────────────────────────
        $this->start_controls_section(
            'section_logo',
            [
                'label' => 'Logo / Title',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'site_title',
            [
                'label'   => 'Site Title',
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'BC PHOTOGRAPHY',
            ]
        );

        $this->add_control(
            'site_url',
            [
                'label'   => 'Site URL',
                'type'    => \Elementor\Controls_Manager::URL,
                'default' => [
                    'url' => home_url(),
                ],
            ]
        );

        $this->end_controls_section();

        // ─── Navigasi Halaman (Opsional) ─────────────────────────────────────
        $this->start_controls_section(
            'section_nav',
            [
                'label' => 'Navigasi Halaman (Opsional)',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_nav',
            [
                'label'        => 'Tampilkan Menu Navigasi',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => 'Ya',
                'label_off'    => 'Tidak',
                'return_value' => 'yes',
                'default'      => 'no',
                'description'  => 'Aktifkan jika ingin menampilkan link antar halaman (misal: About, Contact, Archive).',
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'nav_label',
            [
                'label'   => 'Label',
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => 'About',
            ]
        );

        $repeater->add_control(
            'nav_url',
            [
                'label'   => 'URL',
                'type'    => \Elementor\Controls_Manager::URL,
                'default' => [ 'url' => '#' ],
            ]
        );

        $this->add_control(
            'nav_list',
            [
                'label'       => 'Menu Items',
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [ 'nav_label' => 'About',   'nav_url' => ['url' => '#'] ],
                    [ 'nav_label' => 'Archive', 'nav_url' => ['url' => '#'] ],
                    [ 'nav_label' => 'Contact', 'nav_url' => ['url' => '#'] ],
                ],
                'title_field' => '{{{ nav_label }}}',
                'condition'   => [ 'show_nav' => 'yes' ],
            ]
        );

        $this->end_controls_section();

        // ─── View Toggle ──────────────────────────────────────────────────────
        $this->start_controls_section(
            'section_view_toggle',
            [
                'label' => 'View Toggle',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'default_view',
            [
                'label'   => 'Tampilan Default',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'grid' => 'Grid (2 kolom)',
                    'list' => 'List (1 kolom)',
                ],
                'default'     => 'grid',
                'description' => 'Tampilan awal saat halaman dibuka pertama kali.',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings     = $this->get_settings_for_display();
        $default_view = ! empty( $settings['default_view'] ) ? $settings['default_view'] : 'grid';
        $show_nav     = $settings['show_nav'] === 'yes';
        ?>
        <nav class="bc-header-nav">
            <div class="bc-header-container">

                <!-- Logo -->
                <a class="bc-header-logo" href="<?php echo esc_url( $settings['site_url']['url'] ); ?>">
                    <?php echo esc_html( $settings['site_title'] ); ?>
                </a>

                <!-- Navigasi Halaman (Opsional) -->
                <?php if ( $show_nav && ! empty( $settings['nav_list'] ) ) : ?>
                <ul class="bc-header-menu">
                    <?php foreach ( $settings['nav_list'] as $item ) :
                        $target   = $item['nav_url']['is_external'] ? ' target="_blank"' : '';
                        $nofollow = $item['nav_url']['nofollow']     ? ' rel="nofollow"' : '';
                    ?>
                    <li>
                        <a class="bc-header-link" href="<?php echo esc_url( $item['nav_url']['url'] ); ?>"<?php echo $target . $nofollow; ?>>
                            <?php echo esc_html( $item['nav_label'] ); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <!-- View Toggle Buttons -->
                <div class="bc-view-toggle" role="group" aria-label="View toggle">
                    <button
                        class="bc-view-btn <?php echo $default_view === 'grid' ? 'is-active' : ''; ?>"
                        data-view="grid"
                        aria-label="Grid view"
                        title="Grid View"
                    >
                        <span class="material-symbols-outlined">grid_view</span>
                    </button>
                    <button
                        class="bc-view-btn <?php echo $default_view === 'list' ? 'is-active' : ''; ?>"
                        data-view="list"
                        aria-label="List view"
                        title="List View"
                    >
                        <span class="material-symbols-outlined">view_agenda</span>
                    </button>
                </div>

            </div>
        </nav>
        <?php
    }
}
