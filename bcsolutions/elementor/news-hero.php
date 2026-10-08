<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class News_Hero_Section extends Widget_Base
{
    public function get_name() { return 'news_hero_section'; }
    public function get_title() { return 'News Hero'; }
    public function get_icon() { return 'eicon-archive-title'; }
    public function get_categories() { return ['general']; }

    protected function register_controls()
    {
        $this->start_controls_section('hero_section', [
            'label' => 'Hero',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('hero_bg', [
            'label' => 'Background Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);

        $this->add_control('hero_label', [
            'label' => 'Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC SOLUTIONS INSIGHTS',
        ]);

        $this->add_control('hero_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'News & Insights',
        ]);

        $this->add_control('hero_desc', [
            'label' => 'Description',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'Stay updated with the latest trends in digital marketing, social media strategies, and creative content production from BC Solutions.',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $hero_bg = !empty($s['hero_bg']['url']) ? $s['hero_bg']['url'] : '';
        ?>
        <section class="news-hero">
            <?php if ($hero_bg): ?>
            <div class="news-hero-bg" style="background-image: url('<?php echo esc_url($hero_bg); ?>');"></div>
            <?php endif; ?>
            <div class="news-hero-overlay"></div>
            <div class="news-hero-content">
                <span class="film-label"><?php echo esc_html($s['hero_label']); ?></span>
                <h1 class="news-hero-title"><?php echo esc_html($s['hero_title']); ?></h1>
                <p class="news-hero-desc"><?php echo esc_html($s['hero_desc']); ?></p>
            </div>
        </section>
        <?php
    }
}
