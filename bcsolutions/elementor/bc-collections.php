<?php
/**
 * Elementor Widget: BC Collections
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class BC_Collections_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'bc_collections';
    }

    public function get_title() {
        return 'BC Collections Grid';
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_style_depends() {
        return [ 'bcs-collections-style' ];
    }

    public function get_script_depends() {
        return [ 'bcs-collections-js' ];
    }

    protected function register_controls() {

        // Content Tab: Header
        $this->start_controls_section(
            'section_header',
            [
                'label' => 'Header Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => 'Title',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Collections',
            ]
        );

        $this->add_control(
            'description',
            [
                'label' => 'Description',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'A curated selection of defining series exploring light, form, and narrative across varied environments. Each collection represents a distinct visual language.',
            ]
        );

        $this->end_controls_section();

        // Content Tab: Filters
        $this->start_controls_section(
            'section_filters',
            [
                'label' => 'Category Filters',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater_filters = new \Elementor\Repeater();

        $repeater_filters->add_control(
            'filter_label',
            [
                'label' => 'Filter Label',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Category Name',
            ]
        );

        $repeater_filters->add_control(
            'filter_slug',
            [
                'label' => 'Filter Slug (no spaces)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'category-slug',
            ]
        );

        $this->add_control(
            'filters_list',
            [
                'label' => 'Filters',
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater_filters->get_controls(),
                'default' => [
                    [ 'filter_label' => 'Collections', 'filter_slug' => 'all' ],
                    [ 'filter_label' => 'Landscape', 'filter_slug' => 'landscape' ],
                    [ 'filter_label' => 'Portrait', 'filter_slug' => 'portrait' ],
                    [ 'filter_label' => 'Street', 'filter_slug' => 'street' ],
                    [ 'filter_label' => 'Abstract', 'filter_slug' => 'abstract' ],
                ],
                'title_field' => '{{{ filter_label }}}',
            ]
        );

        $this->end_controls_section();

        // Content Tab: Collection Items
        $this->start_controls_section(
            'section_items',
            [
                'label' => 'Collection Items',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater_items = new \Elementor\Repeater();

        $repeater_items->add_control(
            'item_title',
            [
                'label' => 'Title',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Collection Title',
            ]
        );

        $repeater_items->add_control(
            'item_subtitle',
            [
                'label' => 'Subtitle / Works Count',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Category / 0 Works',
            ]
        );

        $repeater_items->add_control(
            'item_category',
            [
                'label' => 'Category Slug (for filter)',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'landscape',
            ]
        );

        $repeater_items->add_control(
            'item_image',
            [
                'label' => 'Image',
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $repeater_items->add_control(
            'item_alt',
            [
                'label' => 'Image Alt Text / Caption',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'A photo description.',
            ]
        );

        $repeater_items->add_control(
            'item_link_text',
            [
                'label' => 'Link Text',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'View Collection',
            ]
        );

        $repeater_items->add_control(
            'item_link_url',
            [
                'label' => 'Link URL',
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => 'https://your-link.com',
                'default' => [
                    'url' => '#',
                ],
            ]
        );

        $this->add_control(
            'items_list',
            [
                'label' => 'Items',
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater_items->get_controls(),
                'default' => [
                    [
                        'item_title' => 'Silent Geometries',
                        'item_subtitle' => 'Landscape / 24 Works',
                        'item_category' => 'landscape',
                        'item_image' => [ 'url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?q=80&w=1600&auto=format&fit=crop' ],
                        'item_alt' => 'A striking black and white landscape photograph...',
                    ],
                    [
                        'item_title' => 'Shadows & Light',
                        'item_subtitle' => 'Portrait / 18 Works',
                        'item_category' => 'portrait',
                        'item_image' => [ 'url' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=1600&auto=format&fit=crop' ],
                        'item_alt' => 'A moody, high-contrast black and white portrait...',
                    ],
                    [
                        'item_title' => 'Urban Nocturne',
                        'item_subtitle' => 'Street / 32 Works',
                        'item_category' => 'street',
                        'item_image' => [ 'url' => 'https://images.unsplash.com/photo-1519501025264-65ba15a82390?q=80&w=1600&auto=format&fit=crop' ],
                        'item_alt' => 'A gritty, high-contrast monochrome street photography scene...',
                    ],
                    [
                        'item_title' => 'Constructs',
                        'item_subtitle' => 'Abstract / 12 Works',
                        'item_category' => 'abstract',
                        'item_image' => [ 'url' => 'https://images.unsplash.com/photo-1487958449943-2429e8be8625?q=80&w=1600&auto=format&fit=crop' ],
                        'item_alt' => 'An abstract black and white photograph...',
                    ]
                ],
                'title_field' => '{{{ item_title }}}',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="bc-collections-container">
            <header class="bc-collections-header fade-in-section">
                <h1 class="font-display-lg bc-collections-title"><?php echo esc_html( $settings['title'] ); ?></h1>
                <p class="font-body-lg bc-collections-desc"><?php echo esc_html( $settings['description'] ); ?></p>
            </header>
            
            <?php if ( ! empty( $settings['filters_list'] ) ) : ?>
            <ul class="bc-category-filters font-label-sm">
                <?php $i = 0; foreach ( $settings['filters_list'] as $filter ) : ?>
                <li>
                    <a href="#" class="filter-link <?php echo $i === 0 ? 'active' : ''; ?>" data-filter="<?php echo esc_attr( $filter['filter_slug'] ); ?>">
                        <?php echo esc_html( $filter['filter_label'] ); ?>
                    </a>
                </li>
                <?php $i++; endforeach; ?>
            </ul>
            <?php endif; ?>

            <div class="bc-collections-grid">
                <?php if ( ! empty( $settings['items_list'] ) ) : ?>
                    <?php foreach ( $settings['items_list'] as $item ) : ?>
                        <article class="collection-item fade-in-section" data-category="<?php echo esc_attr( $item['item_category'] ); ?>">
                            <div class="collection-image-wrapper">
                                <div class="collection-image-bg">
                                    <img 
                                        src="<?php echo esc_url( $item['item_image']['url'] ); ?>" 
                                        data-alt="<?php echo esc_attr( $item['item_alt'] ); ?>" 
                                        alt="<?php echo esc_attr( $item['item_title'] ); ?>" 
                                        loading="lazy" 
                                    />
                                </div>
                            </div>
                            <div class="collection-meta">
                                <div>
                                    <h2 class="font-headline-lg collection-title"><?php echo esc_html( $item['item_title'] ); ?></h2>
                                    <p class="font-label-sm collection-category-text"><?php echo esc_html( $item['item_subtitle'] ); ?></p>
                                </div>
                                <?php
                                $target = $item['item_link_url']['is_external'] ? ' target="_blank"' : '';
                                $nofollow = $item['item_link_url']['nofollow'] ? ' rel="nofollow"' : '';
                                ?>
                                <a class="font-label-sm collection-view-link" href="<?php echo esc_url( $item['item_link_url']['url'] ); ?>" <?php echo $target . $nofollow; ?>>
                                    <?php echo esc_html( $item['item_link_text'] ); ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <script>
            // Ensure Elementor re-runs our scripts when editing in preview
            if ( window.elementorFrontend ) {
                // If Elementor is loaded, we can re-trigger our JS functions or just dispatch a resize/scroll event
                // This is a basic approach. A more robust way is binding to elementor/frontend/init in main.js.
                window.dispatchEvent(new Event('DOMContentLoaded'));
            }
        </script>
        <?php
    }
}
