<?php get_header(); ?>

<main class="site-main">
    <div class="container">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) : the_post(); ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    
                  
                    
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    // Optional: Pagination for multi-page content
                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . __('Pages:', 'your-theme'),
                        'after'  => '</div>',
                    ));
                    ?>

                </article>

            <?php endwhile;
        else :
            echo '<p>No content found.</p>';
        endif;
        ?>
    </div>
</main>

<?php get_footer(); ?>
