<?php 
    /**
     * Template Name: Homepage Layout
     * Template Post Type: page
     */
    get_header();
?>

<main class="container mx-auto">
    <?php
        //while(have_posts()) : the_post();
            //the_content();
        //endwhile;       
        //wp_reset_query();
    ?>
    <!-- the custom advanced fields way -->
    <section class="masthead" style="background-image: url('<?php the_field('masthead_background_image'); ?>')">
        <div>
            <h2> <?php the_field('masthead_title'); ?> </h2>
        </div>
    </section>
    <section>
        <h3> <?php the_field('row_one_title'); ?> </h3>
        <?php the_field('row_one_text'); ?>
    </section>
    <!-- display post by category -->
    <section>
        <?php
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'category_name' => 'ontario',
                'post_per_page' => 3
            );
            $arr_posts = new WP_QUERY($args);
            if ($arr_posts->have_posts()) :
                while ($arr_posts->have_posts()) :
                    $arr_posts->the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <?php
                            if(has_post_thumbnail()) :
                                the_post_thumbnail();
                            endif;
                        ?>
                        <header>
                            <h3><?php the_title(); ?></h3>
                        </header>
                        <div>
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile;
            endif;
            ?>

    </section>

</main>

<?php 
    get_footer();
?>