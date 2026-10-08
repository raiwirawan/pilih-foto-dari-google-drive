<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class News_Featured_Section extends Widget_Base
{
    public function get_name() { return 'news_featured_section'; }
    public function get_title() { return 'News Featured Article'; }
    public function get_icon() { return 'eicon-post-featured-image'; }
    public function get_categories() { return ['general']; }

    protected function register_controls()
    {
        $this->start_controls_section('featured_section', [
            'label' => 'Featured Settings',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('category_slug', [
            'label' => 'Category Slug (optional)',
            'type' => Controls_Manager::TEXT,
            'default' => '',
            'description' => 'Filter by category slug. Leave empty for latest post.',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $cat_slug = $s['category_slug'] ?? '';

        // Check URL filter
        $active_cat = isset($_GET['news_cat']) ? sanitize_text_field($_GET['news_cat']) : '';

        $args = [
            'post_type'      => 'post',
            'posts_per_page' => 1,
            'order'          => 'DESC',
            'orderby'        => 'date',
            'post_status'    => 'publish',
        ];

        if (!empty($active_cat)) {
            $args['category_name'] = $active_cat;
        } elseif (!empty($cat_slug)) {
            $args['category_name'] = $cat_slug;
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<p style="text-align:center;color:#999;padding:40px;">No featured post found.</p>';
            }
            wp_reset_postdata();
            return;
        }

        $query->the_post();
        $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
        $cats = get_the_category();
        $cat_name = !empty($cats) ? $cats[0]->name : '';
        $date = get_the_date('d F Y');
        $excerpt = get_the_excerpt();
        $reading_time = max(1, intval(str_word_count(get_the_content()) / 200));
        ?>
        <section class="news-featured">
            <a href="<?php echo esc_url(get_permalink()); ?>" class="news-featured-card">
                <?php if ($img): ?>
                <div class="news-featured-img">
                    <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="news-featured-info">
                    <?php if ($cat_name): ?>
                    <div class="news-card-category"><?php echo esc_html($cat_name); ?></div>
                    <?php endif; ?>
                    <h2 class="news-featured-title"><?php echo esc_html(get_the_title()); ?></h2>
                    <p class="news-featured-excerpt"><?php echo esc_html($excerpt); ?></p>
                    <div class="news-card-meta">
                        <span><i data-lucide="calendar"></i> <?php echo esc_html($date); ?></span>
                        <span><i data-lucide="clock"></i> <?php echo $reading_time; ?> min read</span>
                    </div>
                </div>
            </a>
        </section>
        <?php
        wp_reset_postdata();
        ?>
        <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
        <?php
    }
}
