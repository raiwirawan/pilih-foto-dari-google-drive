<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Thumbnail_Slider extends Widget_Base
{

    public function get_name()
    {
        return 'thumbnail_slider';
    }

    public function get_title()
    {
        return 'Thumbnail Slider';
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
                'label' => 'Thumbnail Slides',
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
                'label' => 'Title',
                'type' => Controls_Manager::WYSIWYG,
                'default' => 'Hero Title',
            ]
        );
        $repeater->add_control(
            'text_label',
            [
                'label' => 'Text Label',
                'type' => Controls_Manager::TEXT,
            ]
        );
        $repeater->add_control(
            'price',
            [
                'label' => 'Button Link',
                'type' => Controls_Manager::TEXT,
            ]
        );

        $repeater->add_control(
            'background_image',
            [
                'label' => 'Background Image',
                'type' => Controls_Manager::MEDIA,
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

        // Unique ID for each widget instance
        $uid = $this->get_id();
        ?>

        <section class="thumbnail_slider_sec">
            <div class="container">
                <div class="swiper thumbnail_slider_<?php echo $uid; ?>">
                    <div class="swiper-wrapper">

                        <?php foreach ($settings['slides'] as $slide): ?>
                            <a  class="swiper-slide slide_thumbnail"href="<?php echo $slide["button_link"]["url"] ?>">
                                <!-- <div> -->
                                    <div class="div_content">
                                        <img class="thumb" src="<?php echo esc_url($slide['background_image']['url']); ?>">

                                        <?php if (!empty($slide['title'])): ?>
                                            <div class="thumbnail_titile"><?php echo $slide['title']; ?></div>
                                            <div class="thumb_desc"><?php echo $slide['desc']; ?></div>
                                            <div class="label"><?php echo $slide['text_label']; ?></div>
                                            <div class="price"><?php echo $slide['price']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                <!-- </div> -->
                            </a>

                        <?php endforeach; ?>
                        <?php foreach ($settings['slides'] as $slide): ?>
                            <a class="swiper-slide slide_thumbnail" href="<?php echo $slide["button_link"]["url"] ?>">
                                <!-- <div > -->
                                    <div class="div_content">
                                        <img class="thumb" src="<?php echo esc_url($slide['background_image']['url']); ?>">

                                        <?php if (!empty($slide['title'])): ?>
                                            <div class="thumbnail_titile"><?php echo $slide['title']; ?></div>
                                            <div class="thumb_desc"><?php echo $slide['desc']; ?></div>
                                            <div class="label"><?php echo $slide['text_label']; ?></div>
                                            <div class="price"><?php echo $slide['price']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                <!-- </div> -->
                            </a>

                        <?php endforeach; ?>

                    </div>

                    <!-- Optional: pagination + nav -->
                    <!-- <div class="swiper-pagination swiper-pagination-<?php echo $uid; ?>"></div> -->
                    <!-- <div class="swiper-button-prev swiper-prev-<?php echo $uid; ?>"></div> -->
                    <!-- <div class="swiper-button-next swiper-next-<?php echo $uid; ?>"></div> -->

                </div>
            </div>
        </section>

        <script>
            jQuery(document).ready(function ($) {

                new Swiper('.thumbnail_slider_<?php echo $uid; ?>', {
                    slidesPerView: 'auto',
                    spaceBetween: 15,
                    loop: true,
                    slidesPerGroup: 1,
                    autoplay: {
                        delay: 3500,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.swiper-pagination-<?php echo $uid; ?>',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-next-<?php echo $uid; ?>',
                        prevEl: '.swiper-prev-<?php echo $uid; ?>',
                    },
                });

            });
        </script>

        <?php
    }
}
