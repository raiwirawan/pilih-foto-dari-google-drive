<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class CTA_Section extends Widget_Base
{
    public function get_name()
    {
        return 'cta_section';
    }
    public function get_title()
    {
        return 'CTA Section';
    }
    public function get_icon()
    {
        return 'eicon-call-to-action';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        // ========================
        // BACKGROUND
        // ========================
        $this->start_controls_section('bg_section', [
            'label' => 'Background',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('bg_image', [
            'label' => 'Background Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);

        $this->end_controls_section();

        // ========================
        // TEXT CONTENT
        // ========================
        $this->start_controls_section('content_section', [
            'label' => 'CTA Content',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('heading', [
            'label' => 'Heading',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'Proven track record: BC Solutions drives real results for brands.',
        ]);

        $this->add_control('description', [
            'label' => 'Description',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'From social media management to paid ads and SEO, BC Solutions delivers data-driven strategies that generate millions of impressions and measurable business growth.',
        ]);

        $this->end_controls_section();

        // ========================
        // BUTTONS
        // ========================
        $this->start_controls_section('buttons_section', [
            'label' => 'Buttons',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('primary_btn_label', [
            'label' => 'Primary Button Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'View Our Services',
        ]);

        $this->add_control('primary_btn_url', [
            'label' => 'Primary Button URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->add_control('show_plus_btn', [
            'label' => 'Show Plus Button',
            'type' => Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->end_controls_section();

        // ========================
        // SLIDER IMAGES
        // ========================
        $this->start_controls_section('slider_section', [
            'label' => 'Slider Images',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $repeater = new Repeater();

        $repeater->add_control('slide_image', [
            'label' => 'Slide Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);

        $this->add_control('slides', [
            'label' => 'Slides',
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [],
            'title_field' => 'Slide',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $bg_url = !empty($s['bg_image']['url']) ? $s['bg_image']['url'] : '';
        $heading = $s['heading'] ?? '';
        $desc = $s['description'] ?? '';
        $btn_label = $s['primary_btn_label'] ?? 'View Our Services';
        $btn_url = $s['primary_btn_url'] ?? '#';
        $show_plus = ($s['show_plus_btn'] ?? '') === 'yes';
        $slides = $s['slides'] ?? [];
        ?>

        <section class="cta-section" <?php if ($bg_url): ?>data-bg="<?php echo esc_url($bg_url); ?>"
            <?php endif; ?>>
            <div class="cta-overlay"></div>
            <div class="cta-container">
                <div class="cta-text">
                    <h2><?php echo esc_html($heading); ?></h2>
                    <p><?php echo esc_html($desc); ?></p>
                    <div class="cta-buttons">
                        <a href="<?php echo esc_url($btn_url); ?>" class="btn-primary large"><i data-lucide="play-circle"></i>
                            <?php echo esc_html($btn_label); ?></a>
                        <?php if ($show_plus): ?>
                            <button class="square-btn"><i data-lucide="plus"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($slides)): ?>
                    <div class="cta-slider-wrap">
                        <!-- Main large image -->
                        <div class="swiper swiper-cta-main">
                            <div class="swiper-wrapper">
                                <?php foreach ($slides as $slide):
                                    $img = !empty($slide['slide_image']['url']) ? $slide['slide_image']['url'] : '';
                                    $img_id = !empty($slide['slide_image']['id']) ? $slide['slide_image']['id'] : 0;
                                    if (!$img)
                                        continue;
                                    ?>
                                    <div class="swiper-slide">
                                        <?php if ($img_id && function_exists('bcs_img_tag')): ?>
                                            <?php echo bcs_img_tag($img_id, 'large', '', '', true); ?>
                                        <?php else: ?>
                                            <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy" decoding="async">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Vertical thumbnails + nav buttons -->
                        <div class="cta-thumbs-col">
                            <button class="cta-thumb-nav cta-thumb-prev"><i data-lucide="chevron-up"></i></button>
                            <div class="swiper swiper-cta-thumbs">
                                <div class="swiper-wrapper">
                                    <?php foreach ($slides as $slide):
                                        $img = !empty($slide['slide_image']['url']) ? $slide['slide_image']['url'] : '';
                                        $img_id = !empty($slide['slide_image']['id']) ? $slide['slide_image']['id'] : 0;
                                        if (!$img)
                                            continue;
                                        ?>
                                        <div class="swiper-slide">
                                            <?php if ($img_id && function_exists('bcs_img_tag')): ?>
                                                <?php echo bcs_img_tag($img_id, 'medium', '', '', true); ?>
                                            <?php else: ?>
                                                <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy" decoding="async">
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <button class="cta-thumb-nav cta-thumb-next"><i data-lucide="chevron-down"></i></button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <script>
            if (typeof lucide !== 'undefined') lucide.createIcons();
        </script>
        <?php
    }
}
