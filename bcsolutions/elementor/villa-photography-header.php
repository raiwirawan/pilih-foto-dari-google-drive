<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BC_Villa_Header_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'bc_villa_header'; }
    public function get_title() { return __( 'BC Header', 'bc-villa' ); }
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
            'label' => __( 'Konten Statis', 'bc-villa' ),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('info', [
            'type' => \Elementor\Controls_Manager::RAW_HTML,
            'raw' => __( 'Widget ini dirender langsung dari source code HTML statis sesuai instruksi.', 'bc-villa' ),
            'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
        ]);
        $this->end_controls_section();
    }

    protected function render() {
        ?>
<nav class="navbar" id="navbar">
    <a class="brand-logo magnetic" href="#">BC Villa Photography</a>
    <div class="nav-link magnetics">
        <a class="nav-link magnetic font-label-caps nav-link-hover" href="#">Portfolio</a>
        <a class="nav-link magnetic font-label-caps active" href="#">About</a>
        <a class="nav-link magnetic font-label-caps nav-link-hover" href="#">Portofolio</a>
        <a class="nav-link magnetic font-label-caps nav-link-hover" href="#">Connect</a>
    </div>
    <a class="btn-primary magnetic font-label-caps" href="#">Inquire</a>
    <button class="mobile-menu-btn">
        <span class="material-symbols-outlined">menu</span>
    </button>
</nav>
        <?php
    }
}
