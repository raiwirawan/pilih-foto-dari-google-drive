<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Spotlight_Section extends Widget_Base
{

    public function get_name()
    {
        return 'spotligh_slider';
    }

    public function get_title()
    {
        return 'Spotlight Slider';
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
                'label' => 'Spotlight Slides',
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        // repeater
        $repeater = new Repeater();

        $repeater->add_control(
            'title',
            [
                'label' => 'Title',
                'type' => Controls_Manager::TEXT,
                'default' => 'Title',
            ]
        );
        $repeater->add_control(
            'desc',
            [
                'label' => 'desc',
                'type' => Controls_Manager::TEXT,
                'default' => 'Sub Title',
            ]
        );
        $repeater->add_control(
            'location',
            [
                'label' => 'Location',
                'type' => Controls_Manager::TEXT,
                'default' => 'Sub Title',
            ]
        );
        $repeater->add_control(
            'time',
            [
                'label' => 'Time',
                'type' => Controls_Manager::TEXT,
                'default' => 'Sub Title',
            ]
        );
        $repeater->add_control(
            'button_label',
            [
                'label' => 'Button Label',
                'type' => Controls_Manager::TEXT,
            ]
        );
        $repeater->add_control(
            'button_link',
            [
                'label' => 'Button Link',
                'type' => Controls_Manager::URL,
                'default' => [
                    'url' => '/',
                ],
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

        $uid = $this->get_id();
        ?>

        <section class="spotlight_section spotlight_<?php echo esc_attr($uid); ?>">
            <div class="swiper spotlight_slider_<?php echo esc_attr($uid); ?>">
                <div class="swiper-wrapper">

                    <?php foreach ($settings['slides'] as $slide):
                        $bg = $slide['background_image']['url'] ?? '';
                        $btn_url = $slide['button_link']['url'] ?? '#';
                        ?>
                        <div class="swiper-slide slide_spotlight">
                            <img class="spotlight_img" src="<?php echo esc_url($bg); ?>"></img>
                            <div class="box_spotlight">
                                <div>
                                    <p class="title"><?php echo esc_html($slide['title']); ?></p>
                                    <p class="desc"><?php echo esc_html($slide['desc']); ?></p>
                                </div>

                                <div class="sub_desc">
                                    <div class="locandtime">
                                        <p class="time"><?php echo esc_html($slide['time']); ?></p>
                                        <p class="location"><?php echo esc_html($slide['location']); ?></p>

                                    </div>

                                    <?php if (!empty($slide['button_label'])): ?>
                                        <a class="spotlight_btn" href="<?php echo esc_url($btn_url); ?>">
                                            <?php echo esc_html($slide['button_label']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <!-- Optional controls -->
                <!-- <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div> -->
            </div>
        </section>

        <script>
            jQuery(document).ready(function ($) {
                new Swiper('.spotlight_slider_<?php echo esc_js($uid); ?>', {
                    slidesPerView: 'auto',
                    loop: true,
                    speed: 800,
                   spaceBetween: 30,
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.spotlight_<?php echo esc_js($uid); ?> .swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.spotlight_<?php echo esc_js($uid); ?> .swiper-button-next',
                        prevEl: '.spotlight_<?php echo esc_js($uid); ?> .swiper-button-prev',
                    },
                });
             });
        </script>

    <?php }
}
