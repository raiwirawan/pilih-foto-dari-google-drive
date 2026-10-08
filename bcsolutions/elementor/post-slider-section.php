<?php
if (!defined('ABSPATH'))
    exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Post_slider extends Widget_Base
{

    public function get_name()
    {
        return 'post_slider';
    }

    public function get_title()
    {
        return 'post slider';
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

        // $this->start_controls_section(
        //     'content_section',
        //     [
        //         'label' => 'Hero Slides',
        //         'tab' => Controls_Manager::TAB_CONTENT,
        //     ]
        // );

        // // repeater
        // $repeater = new Repeater();

        // $repeater->add_control(
        //     'title',
        //     [
        //         'label' => 'Title',
        //         'type' => Controls_Manager::TEXT,
        //         'default' => 'Title',
        //     ]
        // );
        // $repeater->add_control(
        //     'sub_title',
        //     [
        //         'label' => 'sub_title',
        //         'type' => Controls_Manager::TEXT,
        //         'default' => 'Sub Title',
        //     ]
        // );
        // $repeater->add_control(
        //     'desc',
        //     [
        //         'label' => 'Title',
        //         'type' => Controls_Manager::WYSIWYG,
        //         'default' => 'Hero Title',
        //     ]
        // );
        // $repeater->add_control(
        //     'button_label',
        //     [
        //         'label' => 'Button Label',
        //         'type' => Controls_Manager::TEXT,
        //         'default' => [
        //             'url' => '/',
        //         ],
        //     ]
        // );
        // $repeater->add_control(
        //     'button_link',
        //     [
        //         'label' => 'Button Link',
        //         'type' => Controls_Manager::URL,
        //         'default' => [
        //             'url' => '/',
        //         ],
        //     ]
        // );

        // $repeater->add_control(
        //     'background_image',
        //     [
        //         'label' => 'Background Image',
        //         'type' => Controls_Manager::MEDIA,
        //     ]
        // );

        // $this->add_control(
        //     'slides',
        //     [
        //         'label' => 'Slides',
        //         'type' => Controls_Manager::REPEATER,
        //         'fields' => $repeater->get_controls(),
        //         'default' => [],
        //         'title_field' => '{{{ title }}}',
        //     ]
        // );

        // $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        ?>
        <section class="post_slider_section">
            <div class="container">

                <?php
                // 1️⃣ Highlight query
                $highlight = new WP_Query(array(
                    'post_type' => 'post',
                    'posts_per_page' => 1,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'ignore_sticky_posts' => true,
                ));

                $highlight_id = 0;
                ?>

                <div class="d1">
                    <?php if ($highlight->have_posts()):
                        while ($highlight->have_posts()):
                            $highlight->the_post();
                            $highlight_id = get_the_ID();
                            ?>
                            <a href="<?php the_permalink(); ?>" class="higlhgt_box">
                                <?php the_post_thumbnail('large'); ?>

                                <p class="post_date">
                                    <?php echo get_the_date('F j, Y'); ?>
                                </p>
                                <p class="highlt"><?php the_title(); ?></p>

                            </a>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>

                <div class="d2">
                    <?php
                    $posts = new WP_Query(array(
                        'post_type' => 'post',
                        'posts_per_page' => 8, // ✅ as many as you want
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'post__not_in' => array($highlight_id), // 🔥 KEY FIX
                        'ignore_sticky_posts' => true,
                    ));

                    if ($posts->have_posts()):
                        while ($posts->have_posts()):
                            $posts->the_post();
                            ?>
                            <a href="<?php the_permalink(); ?>" class="post_box">
                                <?php the_post_thumbnail('medium'); ?>
                                <div class="post_content">
                                    <p class="post_title"><?php the_title(); ?></p>
                                    <p class="post_date">
                                        <?php echo get_the_date('F j, Y'); ?>
                                    </p>
                                </div>

                            </a>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>

            </div>
        </section>

        <?php
    }
}
