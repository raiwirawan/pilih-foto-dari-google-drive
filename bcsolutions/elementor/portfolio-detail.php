<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Portfolio_Detail_Section extends Widget_Base
{
    public function get_name()
    {
        return 'portfolio_detail_section';
    }
    public function get_title()
    {
        return 'Portfolio Detail';
    }
    public function get_icon()
    {
        return 'eicon-single-post';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        // POSTER
        $this->start_controls_section('poster_section', [
            'label' => 'Poster',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('poster_image', [
            'label' => 'Poster Image',
            'type' => Controls_Manager::MEDIA,
            'media_types' => ['image'],
        ]);
        $this->add_control('poster_brand', [
            'label' => 'Brand',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC SOLUTIONS',
        ]);
        $this->add_control('poster_title', [
            'label' => 'Poster Title',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'Visual Contents Portfolio',
        ]);
        $this->add_control('poster_sub', [
            'label' => 'Poster Subtitle',
            'type' => Controls_Manager::TEXT,
            'default' => 'WATCH ON ?? BC SOLUTIONS',
        ]);
        $this->add_control('watch_url', [
            'label' => 'Watch Button URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);
        $this->add_control('watch_label', [
            'label' => 'Watch Button Label',
            'type' => Controls_Manager::TEXT,
            'default' => 'Watch Now',
        ]);
        $this->end_controls_section();

        // INFO
        $this->start_controls_section('info_section', [
            'label' => 'Info',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('info_title', [
            'label' => 'Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'Visual Contents Portfolio',
        ]);

        $this->add_control('year', [
            'label' => 'Year',
            'type' => Controls_Manager::TEXT,
            'default' => '2025',
        ]);
        $this->add_control('month', [
            'label' => 'Month',
            'type' => Controls_Manager::TEXT,
            'default' => 'Agustus',
        ]);
        $this->add_control('badge_text', [
            'label' => 'Badge Text',
            'type' => Controls_Manager::TEXT,
            'default' => 'BC Solutions Portfolio',
        ]);
        $this->add_control('genres', [
            'label' => 'Genres (comma separated)',
            'type' => Controls_Manager::TEXT,
            'default' => 'Visual Contents, Brand Activation, Social Media',
        ]);
        $this->add_control('synopsis', [
            'label' => 'Synopsis',
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'A showcase of our creative digital marketing expertise � delivering impactful visual content, social media strategies, professional photography, and brand activation campaigns that drive real results for our clients.',
        ]);
        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $poster_img = !empty($s['poster_image']['url']) ? $s['poster_image']['url'] : '';
        $genres = array_map('trim', explode(',', $s['genres'] ?? ''));
?>
        <section class="film-detail-wrapper">
            <!-- Left: Sticky Poster -->
            <div class="film-poster-sticky">
                <div class="film-poster">
                    <?php if ($poster_img): ?>
                    <img src="<?php echo esc_url($poster_img); ?>" alt="<?php echo esc_attr($s['poster_title']); ?>">
                    <?php
        endif; ?>
                    <div class="film-poster-overlay">
                        <p class="film-poster-brand"><?php echo esc_html($s['poster_brand']); ?></p>
                        <h3 class="film-poster-title"><?php echo nl2br(esc_html($s['poster_title'])); ?></h3>
                        <p class="film-poster-sub"><?php echo esc_html($s['poster_sub']); ?></p>
                    </div>
                </div>
                <a href="<?php echo esc_url($s['watch_url']); ?>" class="btn-film-watch"><?php echo esc_html($s['watch_label']); ?></a>
            </div>

            <!-- Right: Details -->
            <div class="film-right-content">
                <div class="film-info-wrap">
                    <h2 class="film-info-title"><?php echo esc_html($s['info_title']); ?></h2>

                    <div class="film-info-meta">
                        <span><?php echo esc_html($s['year']); ?></span>
                        <span class="film-meta-sep">-</span>
                        <span><?php echo esc_html($s['month']); ?></span>
                        <?php if (!empty($s['badge_text'])): ?>
                        <span class="film-tag film-tag-highlight"><?php echo esc_html($s['badge_text']); ?></span>
                        <?php
        endif; ?>
                    </div>
                    <?php if (!empty($genres)): ?>
                    <div class="film-info-genres">
                        <?php foreach ($genres as $i => $g):
                if ($i > 0): ?><span class="film-meta-sep">-</span><?php
                endif; ?>
                        <span><?php echo esc_html($g); ?></span>
                        <?php
            endforeach; ?>
                    </div>
                    <?php
        endif; ?>
                    <p class="film-info-synopsis"><?php echo esc_html($s['synopsis']); ?></p>

                </div>
            </div>
        </section>
        <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
        <?php
    }
}
