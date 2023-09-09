<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage harpiaconsultoria
 */
 
get_header(); ?>
    <?php get_template_part('template_parts/_banner'); ?>
    <?php get_template_part('template_parts/_breadcrumbs'); ?> 
    <?php get_template_part('template_parts/_page-header'); ?>
    <div id="primary" >
        <section class="blog section">
            <div class="container d-flex flex-wrap align-items-stretch">
                <div class="flex-fill row d-flex flex-wrap pe-md-4 order-md-1 order-2 content-area justify-content-start align-items-start flex-column">
                    <div class="post-header">
                        <img class="img-fluid d-block thumbnail mb-4" width="861" height="250" loading="lazy" src="<?php echo get_the_post_thumbnail_url($post->ID); ?>" alt="<?php echo get_the_title(); ?>">
                        <h1 class="title mb-3"><?php echo get_the_title(); ?></h1>
                        <p class="text mb-4">
                            <span class="date"><?php the_time('M d') ?></span>, por <?php the_author_meta('display_name', get_post_field('post_author')); ?>
                        </p>                        
                    </div>
                    <div class="post-content">
                        <?php
                            while ( have_posts() ) : the_post(); 
                                the_content();
                            endwhile;
                        ?>  
                    </div>
                </div>  
                <?php
                    get_template_part('sidebar', 'blog', array(
                        'classes' => 'col-md-3 order-md-2 order-1 mb-5 mb-md-0'
                    ));
                ?>                  
            </div>
        </section>
    </div>
<?php get_footer(); ?>