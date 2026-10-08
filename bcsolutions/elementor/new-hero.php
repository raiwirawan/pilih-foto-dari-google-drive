<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class New_Hero_Section extends Widget_Base
{

    public function get_name()
    {
        return 'new_hero_section';
    }

    public function get_title()
    {
        return 'New Hero Section';
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

        $repeater = new Repeater();

        $repeater->add_control(
            'tag',
            [
                'label' => 'Tag Text',
                'type' => Controls_Manager::TEXT,
                'default' => 'CREATIVE DIGITAL MARKETING AGENCY',
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label' => 'Title',
                'type' => Controls_Manager::WYSIWYG,
                'default' => 'Elevate your brand<br>with digital excellence!',
            ]
        );

        $repeater->add_control(
            'badge_1',
            [
                'label' => 'Badge 1 (Active)',
                'type' => Controls_Manager::TEXT,
                'default' => 'Digital Marketing',
            ]
        );

        $repeater->add_control(
            'badge_2',
            [
                'label' => 'Badge 2',
                'type' => Controls_Manager::TEXT,
                'default' => 'Social Media',
            ]
        );

        $repeater->add_control(
            'badge_3',
            [
                'label' => 'Badge 3',
                'type' => Controls_Manager::TEXT,
                'default' => 'Visual Contents',
            ]
        );

        $repeater->add_control(
            'description',
            [
                'label' => 'Description',
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'BC Solutions delivers comprehensive digital marketing solutions from social media management to SEO helping brands achieve measurable growth.',
            ]
        );

        $repeater->add_control(
            'button_1_label',
            [
                'label' => 'Button 1 Label',
                'type' => Controls_Manager::TEXT,
                'default' => 'Pricing',
            ]
        );

        $repeater->add_control(
            'button_1_link',
            [
                'label' => 'Button 1 Link',
                'type' => Controls_Manager::TEXT,
                'default' => '#',
            ]
        );

        $repeater->add_control(
            'button_2_label',
            [
                'label' => 'Button 2 Label',
                'type' => Controls_Manager::TEXT,
                'default' => 'See the proof',
            ]
        );

        $repeater->add_control(
            'button_2_link',
            [
                'label' => 'Button 2 Link',
                'type' => Controls_Manager::TEXT,
                'default' => '#',
            ]
        );

        $repeater->add_control(
            'media_type',
            [
                'label' => 'Background Type',
                'type' => Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'image' => 'Image',
                    'video' => 'Video',
                ],
            ]
        );

        $repeater->add_control(
            'background_image',
            [
                'label' => 'Background Image',
                'type' => Controls_Manager::MEDIA,
                'media_types' => ['image'],
                'condition' => [
                    'media_type' => 'image',
                ],
            ]
        );

        $repeater->add_control(
            'background_video',
            [
                'label' => 'Background Video',
                'type' => Controls_Manager::MEDIA,
                'media_types' => ['video'],
                'condition' => [
                    'media_type' => 'video',
                ],
            ]
        );

        $this->add_control(
            'slides',
            [
                'label' => 'Slides',
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'tag' => 'CREATIVE DIGITAL MARKETING AGENCY',
                        'title' => 'Elevate your brand<br>with digital excellence!',
                        'badge_1' => 'Digital Marketing',
                        'badge_2' => 'Social Media',
                        'badge_3' => 'Visual Contents',
                        'description' => 'BC Solutions delivers comprehensive digital marketing solutions from social media management to SEO helping brands achieve measurable growth.',
                        'button_1_label' => 'Pricing',
                        'button_1_link' => '#',
                        'button_2_label' => 'See the proof',
                        'button_2_link' => '#',
                        'media_type' => 'image',
                    ],
                    [
                        'tag' => 'VISUAL CONTENTS & PHOTOGRAPHY',
                        'title' => 'Stunning visuals that<br>captivate audiences!',
                        'badge_1' => 'Pro Photography',
                        'badge_2' => '360 Content',
                        'badge_3' => 'Brand Activation',
                        'description' => 'Professional photography, 360 content, and visual storytelling that brings your brand to life and drives engagement across all platforms.',
                        'button_1_label' => 'View Portfolio',
                        'button_1_link' => '#',
                        'button_2_label' => 'Contact Us',
                        'button_2_link' => '#',
                        'media_type' => 'image',
                    ],
                ],
                'title_field' => '{{{ tag }}}',
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

        <!-- HERO SECTION -->
        <header class="hero swiper mySwiper">
            <div class="swiper-wrapper">
                <?php $idx = 0; foreach ($settings['slides'] as $slide):
                    $media_type = !empty($slide['media_type']) ? $slide['media_type'] : 'image';
                    $bg_url = !empty($slide['background_image']['url']) ? $slide['background_image']['url'] : '';
                    $video_url = !empty($slide['background_video']['url']) ? $slide['background_video']['url'] : '';
                ?>
                    <div class="swiper-slide">
                        <?php if ($media_type === 'video' && $video_url): ?>
                            <div class="hero-bg">
                                <?php if ($idx === 0): ?>
                                <video class="hero-video" autoplay muted loop playsinline
                                    style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover;">
                                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                                </video>
                                <?php else: ?>
                                <video class="hero-video" data-lazy-video muted loop playsinline preload="none"
                                    style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover;">
                                    <source data-src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                                </video>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php if ($idx === 0 && $bg_url): ?>
                            <div class="hero-bg" style="background-image: url('<?php echo esc_url($bg_url); ?>');"></div>
                            <?php elseif ($bg_url): ?>
                            <div class="hero-bg" data-bg="<?php echo esc_url($bg_url); ?>"></div>
                            <?php else: ?>
                            <div class="hero-bg"></div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="hero-content">
                            <?php if (!empty($slide['tag'])): ?>
                                <div class="tag"><?php echo esc_html($slide['tag']); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($slide['title'])): ?>
                                <h1><?php echo $slide['title']; ?></h1>
                            <?php endif; ?>

                            <div class="badges">
                                <?php if (!empty($slide['badge_1'])): ?>
                                    <span class="badge badge-active"><i data-lucide="star" class="badge-star"></i>
                                        <?php echo esc_html($slide['badge_1']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($slide['badge_2'])): ?>
                                    <span class="badge"><?php echo esc_html($slide['badge_2']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($slide['badge_3'])): ?>
                                    <span class="badge"><?php echo esc_html($slide['badge_3']); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($slide['description'])): ?>
                                <p class="hero-desc"><?php echo esc_html($slide['description']); ?></p>
                            <?php endif; ?>

                            <div class="hero-buttons">
                                <?php if (!empty($slide['button_1_label'])): ?>
                                    <a href="<?php echo esc_url($slide['button_1_link']); ?>" class="btn-primary large">
                                        <?php echo esc_html($slide['button_1_label']); ?> <i data-lucide="external-link"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($slide['button_2_label'])): ?>
                                    <a href="<?php echo esc_url($slide['button_2_link']); ?>" class="btn-outline">
                                        <?php echo esc_html($slide['button_2_label']); ?> <i data-lucide="copy"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php $idx++; endforeach; ?>
            </div>

            <!-- Navigation -->
            <div class="swiper-button-next"><i data-lucide="chevron-right"></i></div>
            <div class="swiper-button-prev"><i data-lucide="chevron-left"></i></div>

            <!-- Custom Pagination & volume -->
            <div class="slider-nav">
                <div class="custom-pagination"></div>
                <button class="circle-btn hero-mute-btn" data-muted="true">
                    <i data-lucide="volume-x" class="mute-icon"></i>
                    <i data-lucide="volume-2" class="unmute-icon" style="display:none;"></i>
                </button>
            </div>
        </header>

        <?php
    }
}
