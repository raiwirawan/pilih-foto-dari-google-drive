<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class News_Filter_Section extends Widget_Base
{
    public function get_name() { return 'news_filter_section'; }
    public function get_title() { return 'News Category Filter'; }
    public function get_icon() { return 'eicon-filter'; }
    public function get_categories() { return ['general']; }

    protected function register_controls()
    {
        $this->start_controls_section('filter_section', [
            'label' => 'Filter Settings',
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('parent_category', [
            'label' => 'Parent Category Slug (optional)',
            'type' => Controls_Manager::TEXT,
            'default' => '',
            'description' => 'Show only children of this category. Leave empty to show all.',
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $s = $this->get_settings_for_display();
        $parent_slug = $s['parent_category'] ?? '';
        $active_cat = isset($_GET['news_cat']) ? sanitize_text_field($_GET['news_cat']) : '';

        $cat_args = ['hide_empty' => false];
        if (!empty($parent_slug)) {
            $parent = get_category_by_slug($parent_slug);
            if ($parent) {
                $cat_args['parent'] = $parent->term_id;
            }
        }
        $categories = get_categories($cat_args);

        if (empty($categories)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<p style="text-align:center;color:#999;padding:20px;">No categories found.</p>';
            }
            return;
        }
        ?>
        <section class="news-filter">
            <div class="news-filter-inner">
                <a href="<?php echo esc_url(remove_query_arg('news_cat')); ?>" class="news-filter-btn <?php echo empty($active_cat) ? 'active' : ''; ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo esc_url(add_query_arg('news_cat', $cat->slug)); ?>" class="news-filter-btn <?php echo ($active_cat === $cat->slug) ? 'active' : ''; ?>"><?php echo esc_html($cat->name); ?></a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }
}
