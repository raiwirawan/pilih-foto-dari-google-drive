<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Category_Section extends Widget_Base
{

    public function get_name()
    {
        return 'category_slider';
    }

    public function get_title()
    {
        return 'Category Slider';
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
            'sub_title',
            [
                'label' => 'Title',
                'type' => Controls_Manager::WYSIWYG,
                'default' => 'Hero Title',
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

        <section class="category_slider">
            <div class="container">
                <div class="swiper categtoryswipper">
                    <div class="swiper-wrapper">

                        <?php foreach ($settings['slides'] as $slide): ?>
                            <a class="swiper-slide slide_category" href="<?php echo $slide["button_link"] ?>"
                                style="background-image:url('<?php echo esc_url($slide['background_image']['url']); ?>');">

                                <div class="div_content">
                                    <?php if (!empty($slide['title'])): ?>
                                        <div class="category_title">
                                            <?php echo $slide['title']; ?>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </a>
                        <?php endforeach; ?>
                        <?php foreach ($settings['slides'] as $slide): ?>
                            <a class="swiper-slide slide_category" href="<?php echo $slide["button_link"] ?>"
                                style="background-image:url('<?php echo esc_url($slide['background_image']['url']); ?>');">

                                <div class="div_content">
                                    <?php if (!empty($slide['title'])): ?>
                                        <div class="category_title">
                                            <?php echo $slide['title']; ?>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </a>
                        <?php endforeach; ?>
                        <?php foreach ($settings['slides'] as $slide): ?>
                            <a class="swiper-slide slide_category" href="<?php echo $slide["button_link"] ?>"
                                style="background-image:url('<?php echo esc_url($slide['background_image']['url']); ?>');">

                                <div class="div_content">
                                    <?php if (!empty($slide['title'])): ?>
                                        <div class="category_title">
                                            <?php echo $slide['title']; ?>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </a>
                        <?php endforeach; ?>

                    </div>

                    <!-- navigation -->
                    <!-- <div class="swiper-button-prev"></div> -->
                    <div class="swiper-button-next"></div>

                </div>
            </div>
        </section>

        <script>
            jQuery(document).ready(function ($) {
                new Swiper('.categtoryswipper', {
                    slidesPerView: 'auto',
                    loop: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false
                    },
                    spaceBetween: 10,
                    navigation: {
                        nextEl: '.swiper-button-next',
                        // prevEl: '.swiper-button-prev'
                    }
                });
            });
        </script>

        <?php
    }
}
