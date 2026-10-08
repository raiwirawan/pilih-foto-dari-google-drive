<?php
if (!defined('ABSPATH')) {
    exit;
}

class Photography_Intro_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'photography_intro';
    }

    public function get_title() {
        return 'Photography Intro';
    }

    public function get_icon() {
        return 'eicon-image-rollover';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_style_depends() {
        return ['bcs-villa-photography-style'];
    }

    public function get_script_depends() {
        return ['bcs-villa-photography-js'];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => 'Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'word_1',
            [
                'label' => 'Word 1',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'VILLA',
            ]
        );

        $this->add_control(
            'word_2',
            [
                'label' => 'Word 2',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'PHOTOGRAPHY',
            ]
        );

        $this->add_control(
            'word_3',
            [
                'label' => 'Word 3',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'BC',
            ]
        );

        $this->add_control(
            'word_4',
            [
                'label' => 'Word 4',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'SOLUTIONS',
            ]
        );

        $this->add_control(
            'gallery',
            [
                'label' => 'Center Image(s) / Slideshow',
                'type' => \Elementor\Controls_Manager::GALLERY,
                'default' => [],
            ]
        );

        $this->add_control(
            'description',
            [
                'label' => 'Description',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Capturing the essence of luxury living across Bali. We provide high-end architectural and interior photography services to elevate your marketing and showcase the true beauty of your property.',
            ]
        );

        $this->add_control(
            'scroll_text',
            [
                'label' => 'Scroll Text',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'SCROLL & DISCOVER',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $gallery = !empty($settings['gallery']) ? $settings['gallery'] : [];
        $image_urls = [];
        foreach ($gallery as $item) {
            if (!empty($item['url'])) {
                $image_urls[] = $item['url'];
            }
        }
        $data_images = !empty($image_urls) ? wp_json_encode($image_urls) : '[]';
        $first_image = !empty($image_urls) ? $image_urls[0] : '';
        $alt_text = (!empty($gallery) && !empty($gallery[0]['id'])) ? \Elementor\Control_Media::get_image_alt($gallery[0]) : 'Intro Image';
        ?>
        <!-- Loading Intro -->
        <div id="vp-loader" class="vp-loader">
            <div class="vp-loader-bg"></div>
            <div class="vp-loader-text">
                <?php if (!empty($settings['word_1'])): ?><div class="vp-line"><span><?php echo esc_html($settings['word_1']); ?></span></div><?php endif; ?>
                <?php if (!empty($settings['word_2'])): ?><div class="vp-line"><span><?php echo esc_html($settings['word_2']); ?></span></div><?php endif; ?>
            </div>
        </div>

        <!-- One-Time Splash Intro -->
        <div class="vp-splash-intro" id="vp-splash-intro">
            <div class="vp-splash-bg"></div>
            <div class="vp-splash-content">
                <div class="vp-wordmark-top gs-fade">
                    <?php if (!empty($settings['word_1'])): ?><span><?php echo esc_html($settings['word_1']); ?></span><?php endif; ?>
                    <?php if (!empty($settings['word_2'])): ?><span><?php echo esc_html($settings['word_2']); ?></span><?php endif; ?>
                    <?php if (!empty($settings['word_3'])): ?><span><?php echo esc_html($settings['word_3']); ?></span><?php endif; ?>
                    <?php if (!empty($settings['word_4'])): ?><span><?php echo esc_html($settings['word_4']); ?></span><?php endif; ?>
                </div>
                
                <div class="vp-splash-middle">
                    <div class="vp-splash-photo gs-fade">
                        <?php if ($first_image): ?>
                            <img id="vp-slideshow-img" src="<?php echo esc_url($first_image); ?>" alt="<?php echo esc_attr($alt_text); ?>" data-images='<?php echo esc_attr($data_images); ?>'>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="vp-splash-footer">
                    <p class="vp-desc gs-fade">
                        <?php echo esc_html($settings['description']); ?>
                    </p>
                    <div class="vp-splash-scroll gs-fade">
                        <?php echo esc_html($settings['scroll_text']); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
