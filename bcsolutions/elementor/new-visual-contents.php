<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Visual_Contents_Section extends Widget_Base
{
    public function get_name()
    {
        return 'visual_contents_section';
    }
    public function get_title()
    {
        return 'Visual Contents Section';
    }
    public function get_icon()
    {
        return 'eicon-slider-album';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => 'Visual Contents',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('section_title', [
            'label' => 'Section Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC Solutions Visual Contents',
        ]);

        $this->add_control('data_source', [
            'label' => 'Data Source',
            'type' => Controls_Manager::SELECT,
            'default' => 'manual',
            'options' => [
                'manual' => 'Manual (Repeater)',
                'dynamic' => 'Dynamic (Posts)',
            ],
        ]);

        $this->end_controls_section();

        // DYNAMIC QUERY
        $this->start_controls_section('query_section', [
            'label' => 'Query Settings',
            'tab' => Controls_Manager::TAB_CONTENT,
            'condition' => ['data_source' => 'dynamic'],
        ]);

        $this->add_control('category_slug', [
            'label' => 'Category Slug',
            'type' => Controls_Manager::TEXT,
            'default' => 'portfolio',
            'description' => 'Slug of the portfolio category',
        ]);

        $this->add_control('posts_count', [
            'label' => 'Number of Posts',
            'type' => Controls_Manager::NUMBER,
            'default' => 6,
            'min' => 1,
            'max' => 20,
        ]);

        $this->add_control('default_tint', [
            'label' => 'Default Tint',
            'type' => Controls_Manager::SELECT,
            'default' => 'bg-red-tint',
            'options' => [
                'bg-red-tint' => 'Red Tint',
                'bg-black-tint' => 'Black Tint',
                'alternate' => 'Alternate Red/Black',
            ],
        ]);

        $this->add_control('button_label', [
            'label' => 'Button Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'See More',
        ]);

        $this->end_controls_section();

        // MANUAL REPEATER
        $this->start_controls_section('manual_section', [
            'label' => 'Manual Slides',
            'tab' => Controls_Manager::TAB_CONTENT,
            'condition' => ['data_source' => 'manual'],
        ]);

        $repeater = new Repeater();
        $repeater->add_control('image', [
            'label' => 'Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);
        $repeater->add_control('tint_color', [
            'label' => 'Tint Style',
            'type' => Controls_Manager::SELECT,
            'default' => 'bg-red-tint',
            'options' => [
                'bg-red-tint' => 'Red Tint',
                'bg-black-tint' => 'Black Tint',
            ],
        ]);
        $repeater->add_control('button_label', [
            'label' => 'Button Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'See More',
        ]);
        $repeater->add_control('button_link', [
            'label' => 'Button Link',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);
        $this->add_control('slides', [
            'label' => 'Slides',
            'type' => Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                ['tint_color' => 'bg-red-tint', 'button_label' => 'See More'],
                ['tint_color' => 'bg-black-tint', 'button_label' => 'See More'],
            ],
            'title_field' => 'Slide',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $title = $settings['section_title'] ?? 'BC Solutions Visual Contents';
        $source = $settings['data_source'] ?? 'manual';

        // Build slides array
        $slides = [];

        if ($source === 'dynamic') {
            $cat = $settings['category_slug'] ?? 'portfolio';
            $count = intval($settings['posts_count'] ?? 6);
            $default_tint = $settings['default_tint'] ?? 'bg-red-tint';
            $btn_label = $settings['button_label'] ?? 'See More';

            $query = new \WP_Query([
                'post_type' => 'post',
                'category_name' => $cat,
                'posts_per_page' => $count,
                'order' => 'DESC',
                'orderby' => 'date',
                'post_status' => 'publish',
            ]);

            $i = 0;
            while ($query->have_posts()) {
                $query->the_post();
                $img = get_the_post_thumbnail_url(get_the_ID(), 'large');

                if ($default_tint === 'alternate') {
                    $tint = ($i % 2 === 0) ? 'bg-red-tint' : 'bg-black-tint';
                } else {
                    $tint = $default_tint;
                }

                $slides[] = [
                    'img_url' => $img ?: '',
                    'tint' => $tint,
                    'btn_label' => $btn_label,
                    'btn_link' => get_permalink(),
                ];
                $i++;
            }
            wp_reset_postdata();
        } else {
            $manual = $settings['slides'] ?? [];
            foreach ($manual as $slide) {
                $slides[] = [
                    'img_url' => !empty($slide['image']['url']) ? $slide['image']['url'] : '',
                    'tint' => !empty($slide['tint_color']) ? $slide['tint_color'] : 'bg-red-tint',
                    'btn_label' => !empty($slide['button_label']) ? $slide['button_label'] : 'See More',
                    'btn_link' => !empty($slide['button_link']) ? $slide['button_link'] : '#',
                ];
            }
        }

        if (empty($slides)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<p style="text-align:center;color:#999;padding:40px;">No portfolio posts found. Create posts in the "' . esc_html($settings['category_slug'] ?? 'portfolio') . '" category.</p>';
            }
            return;
        }
        ?>

        <section class="portfolio bg-black">
            <div class="section-header">
                <h2 class="text-white"><?php echo esc_html($title); ?></h2>
            </div>

            <div class="swiper-container-wrapper">
                <button class="slider-nav-btn prev-btn swiper-prev-film"><i data-lucide="chevron-left"></i></button>
                <button class="slider-nav-btn next-btn swiper-next-film"><i data-lucide="chevron-right"></i></button>

                <div class="swiper swiper-film">
                    <div class="swiper-wrapper">
                        <?php foreach ($slides as $slide): ?>
                            <div class="swiper-slide port-card landscape">
                                <div class="port-img <?php echo esc_attr($slide['tint']); ?>" <?php if ($slide['img_url']): ?>data-bg="<?php echo esc_url($slide['img_url']); ?>" <?php endif; ?>>
                                </div>
                                <div class="port-stats right-align">
                                    <a href="<?php echo esc_url($slide['btn_link']); ?>"
                                        class="btn-primary small"><?php echo esc_html($slide['btn_label']); ?></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <script>
            jQuery(document).ready(function ($) {
                new Swiper('.swiper-film', {
                    slidesPerView: 'auto',
                    spaceBetween: 16,
                    navigation: {
                        nextEl: '.swiper-next-film',
                        prevEl: '.swiper-prev-film'
                    },
                    on: {
                        init: function () { if (typeof lucide !== 'undefined') lucide.createIcons(); },
                        slideChange: function () { if (typeof lucide !== 'undefined') lucide.createIcons(); }
                    }
                });
            });
        </script>
        <?php
    }
}
