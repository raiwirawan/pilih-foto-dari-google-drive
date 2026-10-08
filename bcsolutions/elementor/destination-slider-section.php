<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Destination_Section extends Widget_Base
{

    public function get_name()
    {
        return 'destination_slider';
    }

    public function get_title()
    {
        return 'Destination Slider';
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
                'type' => Controls_Manager::TEXT,
                'default' => 'Title',
            ]
        );
        $repeater->add_control(
            'sub_title',
            [
                'label' => 'sub_title',
                'type' => Controls_Manager::TEXT,
                'default' => 'Sub Title',
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
        ?>

        <section class="destination_slider_sec">
            <div class="container">
                <div class="swiper destinationslider">
                    <div class="swiper-wrapper">

                        <?php foreach ($settings['slides'] as $slide): ?>
                            <div class="swiper-slide slide_destination"
                                style="background-image:url('<?php echo esc_url($slide['background_image']['url']); ?>');">

                                <div class="div_content">
                                    <?php if (!empty($slide['title'])): ?>
                                        <div class="destination_subtitle">
                                            <?php echo $slide['sub_title']; ?>
                                        </div>
                                        <div class="destination_title">
                                            <?php echo $slide['title']; ?>
                                        </div>
                                        <div class="destination_desc">
                                            <?php echo $slide['desc']; ?>
                                        </div>
                                        <a href='<?php  echo $slide['button_link']["url"]?>' class="b_transparent"><?php echo $slide['button_label']; ?></a>
                                    <?php endif; ?>
                                </div>

                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
                <div class="swiper thumbnail_destination_slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($settings['slides'] as $slide): ?>
                            <img class='swiper-slide slide_thumbnail_destination'
                                src="<?php echo esc_url($slide['background_image']['url']); ?>"></img>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <script>
            jQuery(document).ready(function ($) {
                const thumbSlider = new Swiper('.thumbnail_destination_slider', {
                    slidesPerView: 'auto',
                    spaceBetween: 10,
                    loop: true,
                    watchSlidesProgress: true,
                    slideToClickedSlide: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false
                    }
                });

                const mainSlider = new Swiper('.destinationslider', {
                    slidesPerView: 'auto',
                    loop: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false
                    },
                    thumbs: {
                        swiper: thumbSlider
                    }
                });
            });
        </script>

        <?php
    }
}
