<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Hero_Section extends Widget_Base
{

    public function get_name()
    {
        return 'hero_slider';
    }

    public function get_title()
    {
        return 'Hero Slider';
    }

    public function get_icon()
    {
        return 'eicon-slider-push';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Hero Slides',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        // repeater
        $repeater = new Repeater();

        $repeater->add_control(
            'title',
            [
                'label' => 'Title',
                'type' => Controls_Manager::WYSIWYG,
                'default' => 'Hero Title',
            ]
        );

        $repeater->add_control(
            'subtitle',
            [
                'label' => 'Subtitle',
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Hero subtitle goes here',
            ]
        );

        $repeater->add_control(
            'button_label',
            [
                'label' => 'Button Label',
                'type' => Controls_Manager::TEXT,
                'default' => 'Try Now',
            ]
        );

        $repeater->add_control(
            'button_link',
            [
                'label' => 'Button Link',
                'type' => Controls_Manager::TEXT,
                'default' => '/',
            ]
        );

        $repeater->add_control(
            'background_image',
            [
                'label' => 'Background Image',
                'type' => Controls_Manager::MEDIA,
            ]
        );

        $this->add_control(
            'slides',
            [
                'label' => 'Slides',
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ title }}}',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        if (empty($settings['slides']))
            return;
        ?>

        <section class="hero_slider">
            <div class="container">
                <div class="swiper heroSwiper">
                    <div class="swiper-wrapper">

                        <?php foreach ($settings['slides'] as $slide): ?>
                            <div class="swiper-slide"
                                style="background-image:url('<?php echo esc_url($slide['background_image']['url']); ?>');">

                                <div class="div_content">
                                    <?php if (!empty($slide['subtitle'])): ?>
                                        <p><?php echo $slide['subtitle']; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($slide['title'])): ?>
                                        <div class="heroheading">
                                            <?php echo $slide['title']; ?>
                                        </div>
                                    <?php endif; ?>



                                    <?php if (!empty($slide['button_label']) && !empty($slide['button_link'])): ?>
                                        <a class="b_orange" href="<?php echo esc_url($slide['button_link']); ?>">
                                            <?php echo esc_html($slide['button_label']); ?>
                                        </a>
                                    <?php endif; ?>

                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>

                    <!-- navigation -->
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>

                    <!-- pagination -->
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>

        <script>
            jQuery(document).ready(function ($) {
                new Swiper('.heroSwiper', {
                    loop: true,
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev'
                    }
                });
            });
        </script>

        <?php
    }
}
