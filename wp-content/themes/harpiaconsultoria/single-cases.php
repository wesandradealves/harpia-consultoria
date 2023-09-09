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
        <?php
            while ( have_posts() ) : the_post(); 
                ?>
                <section class="accordion section">
                    <div class="container">
                        <div class="d-flex row justify-content-between align-items-center">
                            <?php if(get_the_post_thumbnail_url()) : ?>
                                <div class="d-none d-xl-block col-6 pe-4 pe-xl-5 media">
                                    <img loading="lazy" height="515" width="720" src="<?php echo get_the_post_thumbnail_url() ?>" alt="<?php echo get_the_title(); ?>" />
                                </div>
                            <?php endif; ?>
                            <div class="flex-fill ps-xl-5">
                                <?php 
                                    get_template_part('template_parts/_section-header', null, array( 
                                        'title' => get_the_title(),
                                        'text' => get_the_content(), 
                                        'template' => 'single'
                                    )); 
                                ?>   
                            </div>
                        </div>
                    </div>
                </section>     
                <?php 
            endwhile;
        ?>        

        <?php 
            if(get_field('content_content_list')) {
                get_template_part('template_parts/_content-list', null, array( 
                    'data' => get_field('content_content_list'), 
                )); 
            } 
        ?> 

        <?php 
            if(get_field('content_diagnostic')) {
                get_template_part('template_parts/_content-diagnostic', null, array( 
                    'data' => get_field('content_diagnostic_diagnostic'), 
                    'title' => 'Resultados', 
                    'text' => get_field('content_diagnostic_text'),
                ));             
            }
        ?>        

        <?php 
            $query = new WP_Query( array(
                'posts_per_page' => -1,
                'post_type' => 'cases',
                'post__not_in' => array(get_the_id()),
                'post_status' => 'publish',
                'order' => 'DESC'
            ) );

            if ( $query->have_posts() ) {
                ?>
                <section class="relationed-posts section">
                    <div class="container pt-0">
                        <?php 
                            get_template_part('template_parts/_section-header', null, array( 
                                'classes' => '', 
                                'title' => 'Veja também outros Cases', 
                            )); 
                        ?>                             
                        <ul class="relationed-posts-list d-flex row align-items-stretch overflow-auto">
                            <?php 
                                while ( $query->have_posts() ) {
                                    $query->the_post();
                                    ?>
                                    <li onclick="location.href = '<?php the_permalink(); ?>';" class="relationed-posts-list-item d-block overflow-hidden pe-0 ps-0">
                                        <div style="background-image: url(<?php echo get_the_post_thumbnail_url(); ?>)" class="item-inner d-flex flex-column justify-content-end">
                                            <h3 class="title p-4"><?php echo get_the_title(); ?></h3>
                                        </div>
                                    </li>
                                    <?php 
                                }
                                wp_reset_query();
                                wp_reset_postdata();  
                            ?>
                        </ul>
                    </div>
                </section>
                <?php 
            }
        ?>
    </div>
<?php get_footer(); ?>