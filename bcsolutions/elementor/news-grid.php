<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class News_Grid_Section extends Widget_Base
{
    public function get_name() { return 'news_grid_section'; }
    public function get_title() { return 'News Articles Grid'; }
    public function get_icon() { return 'eicon-posts-grid'; }
    public function get_categories() { return ['general']; }

    protected function register_controls()
    {
        $this->start_controls_section('grid_section', [
            'label' => 'Grid Settings',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('category_slug', [
            'label' => 'Category Slug (optional)',
            'type' => Controls_Manager::TEXT,
            'default' => '',
            'description' => 'Filter by category. Leave empty for all.',
        ]);

        $this->add_control('posts_per_page', [
            'label' => 'Posts Per Page',
            'type' => Controls_Manager::NUMBER,
            'default' => 9,
            'min' => 3,
            'max' => 30,
        ]);

        $this->add_control('offset', [
            'label' => 'Skip First N Posts',
            'type' => Controls_Manager::NUMBER,
            'default' => 1,
            'min' => 0,
            'max' => 10,
            'description' => 'Set to 1 to skip the featured article (shown separately).',
        ]);

        $this->add_control('order', [
            'label' => 'Order',
            'type' => Controls_Manager::SELECT,
            'default' => 'DESC',
            'options' => [
                'DESC' => 'Newest First',
                'ASC' => 'Oldest First',
            ],
        ]);

        $this->add_control('show_pagination', [
            'label' => 'Show Pagination',
            'type' => Controls_Manager::SWITCHER,
            'default' => 'yes',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $cat_slug = $s['category_slug'] ?? '';
        $per_page = intval($s['posts_per_page'] ?? 9);
        $offset = intval($s['offset'] ?? 1);
        $show_pagination = ($s['show_pagination'] ?? '') === 'yes';

        // Check URL filter
        $active_cat = isset($_GET['news_cat']) ? sanitize_text_field($_GET['news_cat']) : '';

        $paged = get_query_var('paged') ? get_query_var('paged') : 1;

        $args = [
            'post_type'      => 'post',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'order'          => $s['order'] ?? 'DESC',
            'orderby'        => 'date',
            'post_status'    => 'publish',
        ];

        // Only apply offset on first page
        if ($paged === 1 && $offset > 0) {
            $args['offset'] = $offset;
        }

        if (!empty($active_cat)) {
            $args['category_name'] = $active_cat;
        } elseif (!empty($cat_slug)) {
            $args['category_name'] = $cat_slug;
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<p style="text-align:center;color:#999;padding:60px;">No articles found.</p>';
            }
            wp_reset_postdata();
            return;
        }
        ?>
        <section class="news-grid-section">
            <div class="news-grid">
                <?php while ($query->have_posts()): $query->the_post();
                    $img = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
                    $cats = get_the_category();
                    $cat_name = !empty($cats) ? $cats[0]->name : '';
                    $date = get_the_date('d M Y');
                    $excerpt = get_the_excerpt();
                    $rt = max(1, intval(str_word_count(get_the_content()) / 200));
                ?>
                <a href="<?php echo esc_url(get_permalink()); ?>" class="news-article-card">
                    <?php if ($img): ?>
                    <div class="news-article-img">
                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                    </div>
                    <?php endif; ?>
                    <div class="news-article-info">
                        <?php if ($cat_name): ?>
                        <div class="news-card-category"><?php echo esc_html($cat_name); ?></div>
                        <?php endif; ?>
                        <h3 class="news-article-title"><?php echo esc_html(get_the_title()); ?></h3>
                        <p class="news-article-excerpt"><?php echo esc_html($excerpt); ?></p>
                        <div class="news-card-meta">
                            <span><i data-lucide="calendar"></i> <?php echo esc_html($date); ?></span>
                            <span><i data-lucide="clock"></i> <?php echo $rt; ?> min</span>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>

            <?php
            $total_pages = $query->max_num_pages;
            if ($show_pagination && $total_pages > 1):
            ?>
            <div class="news-pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i === $paged): ?>
                        <span class="news-page-btn active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo esc_url(get_pagenum_link($i)); ?>" class="news-page-btn"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($paged < $total_pages): ?>
                    <a href="<?php echo esc_url(get_pagenum_link($paged + 1)); ?>" class="news-page-btn news-page-next"><i data-lucide="chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
        <?php
        wp_reset_postdata();
        ?>
        <script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
        <?php
    }
}
