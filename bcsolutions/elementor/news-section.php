<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class News_Section extends Widget_Base
{
    public function get_name()
    {
        return 'news_section';
    }
    public function get_title()
    {
        return 'News Section';
    }
    public function get_icon()
    {
        return 'eicon-posts-grid';
    }
    public function get_categories()
    {
        return ['general'];
    }

    protected function register_controls()
    {
        // ========================
        // CONTENT
        // ========================
        $this->start_controls_section('content_section', [
            'label' => 'News Settings',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('section_title', [
            'label' => 'Section Title',
            'type' => Controls_Manager::TEXT,
            'default' => 'News',
        ]);

        $this->add_control('view_all_text', [
            'label' => 'View All Text',
            'type' => Controls_Manager::TEXT,
            'default' => 'View all',
        ]);

        $this->add_control('view_all_url', [
            'label' => 'View All URL',
            'type' => Controls_Manager::TEXT,
            'default' => '#',
        ]);

        $this->add_control('category', [
            'label' => 'Category Slug',
            'type' => Controls_Manager::TEXT,
            'default' => 'news',
            'description' => 'Masukkan slug kategori WordPress, contoh: news, berita, blog',
        ]);

        $this->add_control('posts_count', [
            'label' => 'Number of Posts',
            'type' => Controls_Manager::NUMBER,
            'default' => 5,
            'min' => 1,
            'max' => 20,
        ]);

        $this->add_control('first_hero', [
            'label' => 'First Post as Hero (Large)',
            'type' => Controls_Manager::SWITCHER,
            'label_on' => 'Yes',
            'label_off' => 'No',
            'default' => 'yes',
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

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $title = $s['section_title'] ?? 'News';
        $view_text = $s['view_all_text'] ?? 'View all';
        $view_url = !empty($s['view_all_url']) ? esc_url($s['view_all_url']) : '#';
        $cat_slug = $s['category'] ?? 'news';
        $count = intval($s['posts_count'] ?? 5);
        $first_hero = !empty($s['first_hero']) && $s['first_hero'] === 'yes';
        $order = $s['order'] ?? 'DESC';

        // WP_Query to get posts from the category
        $args = [
            'post_type' => 'post',
            'posts_per_page' => $count,
            'order' => $order,
            'orderby' => 'date',
            'post_status' => 'publish',
        ];

        // Support category slug
        if (!empty($cat_slug)) {
            $args['category_name'] = $cat_slug;
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<p style="text-align:center;color:#999;padding:40px;">No posts found in category "<strong>' . esc_html($cat_slug) . '</strong>". Create posts with that category to see them here.</p>';
            }
            wp_reset_postdata();
            return;
        }
        ?>

        <section class="news-post" id="news">
            <div class="post-container">
                <div class="post-header">
                    <h2 class="post-title"><?php echo esc_html($title); ?></h2>
                    <a href="<?php echo $view_url; ?>" class="post-link"><?php echo esc_html($view_text); ?> &rarr;</a>
                </div>

                <div class="post-grid">
                    <div class="post-masonry">
                        <?php
                        $index = 0;
                        while ($query->have_posts()):
                            $query->the_post();
                            // Try featured image first
                            $img = get_the_post_thumbnail_url(get_the_ID(), 'large');

                            // Fallback: get image from Elementor widget data (e.g. Portfolio Hero hero_bg)
                            if (!$img) {
                                $elementor_data = get_post_meta(get_the_ID(), '_elementor_data', true);
                                if ($elementor_data) {
                                    $data = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;
                                    if (is_array($data)) {
                                        $img = bcs_find_elementor_image($data);
                                    }
                                }
                            }

                            // Fallback: get first image from post content
                            if (!$img) {
                                $content = get_the_content();
                                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches)) {
                                    $img = $matches[1];
                                }
                            }
                            $t = get_the_title();
                            $date = get_the_date('d.m.Y');
                            $url = get_permalink();
                            $is_hero = ($first_hero && $index === 0);
                            $cls = $is_hero ? 'post-card post-card--hero' : 'post-card';
                            ?>
                            <a href="<?php echo esc_url($url); ?>" class="<?php echo $cls; ?>">
                                <?php if ($img): ?>
                                    <div class="post-card__image">
                                        <?php
                                        $thumb_id = get_post_thumbnail_id(get_the_ID());
                                        if ($thumb_id && function_exists('bcs_img_tag')):
                                            echo bcs_img_tag($thumb_id, 'medium_large', '', esc_attr($t), true);
                                        else:
                                        ?>
                                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($t); ?>" loading="lazy" decoding="async">
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="post-card__content">
                                    <h3 class="post-heading"><?php echo esc_html($t); ?></h3>
                                    <p class="post-date"><?php echo esc_html($date); ?></p>
                                </div>
                            </a>
                            <?php
                            $index++;
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}
