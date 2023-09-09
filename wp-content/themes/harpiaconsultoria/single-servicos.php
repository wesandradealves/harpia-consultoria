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
        <section class="section content-area">
            <div class="container">
                <p class="title d-block mb-4"><?php echo get_the_title(); ?></p>
                <div class="content-area-inner d-flex <?php if(!get_field('content_video_lateral')) : ?> align-items-stretch <?php else : ?> align-items-start <?php endif; ?>">
                    <?php
                        while ( have_posts() ) : the_post(); 
                            ?>
                            <div class="<?php if(get_field('content_media')||get_field('content_video_lateral')) : ?> <?php if(get_field('content_video_lateral')) : ?> col-lg-6 <?php else : ?> col-lg-8 <?php endif; ?> pe-lg-5 <?php endif; ?> d-flex flex-column">
                                <?php the_content(); ?>

                                <?php if(get_field('content_cta') && get_field('content_cta_label') && get_field('content_cta_link')) : ?>
                                    <a title="<?php echo get_field('content_cta_label'); ?>" href="<?php echo get_field('content_cta_link'); ?>" class="btn primary mt-5 d-inline-block me-auto"><?php echo get_field('content_cta')['label']; ?></a>
                                <?php endif; ?>
                            </div>
                            <?php if(get_field('content_media')||get_field('content_video_lateral')) : ?>
                                <div class="flex-fill d-flex flex-column align-items-end media <?php if(get_field('content_video_lateral')) : ?> video <?php endif; ?> ps-lg-5">
                                    <?php if(get_field('content_video_lateral')) : ?><a class="yu2fvl" href="https://www.youtube.com/watch?v=<?php echo get_field('content_video_lateral'); ?>"><?php endif; ?>
                                        <?php if(get_field('content_video_lateral')) : ?>
                                            <img height="69" width="69" loading="lazy" class="play d-block" src="<?php echo get_template_directory_uri(); ?>/img/play-btn.png" alt="Play" />
                                        <?php endif; ?>
                                        <img class="img-fluid" height="735" width="480" loading="lazy" src="<?php echo get_field('content_video_lateral') ? "https://i3.ytimg.com/vi/".get_field('content_video_lateral')."/maxresdefault.jpg" : get_field('content_media'); ?>" alt="<?php echo get_the_title(); ?>" />
                                    <?php if(get_field('content_video_lateral')) : ?></a><?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php 
                        endwhile;
                    ?>
                </div>
            </div>
        </section>

        <?php 
            if(get_field('content_diagnostic') && get_field('content_diagnostic_diagnostic')) {
                get_template_part('template_parts/_content-diagnostic', null, array( 
                    'data' => get_field('content_diagnostic_diagnostic'), 
                    'title' => get_field('content_diagnostic_title'), 
                    'text' => get_field('content_diagnostic_text'),
                ));             
            }
        ?>  

        <?php 
            if(get_field('content_video')) {
                get_template_part('template_parts/_about', null, array( 
                    'classes' => 'flex-md-row-reverse align-items-stretch',
                    'video' => get_field('content_video_video'),
                    'thumbnail' => get_field('content_video_thumbnail'),
                    'title' => get_field('content_video_title'),
                    'text' => get_field('content_video_text'),
                    'template' => 'single', 
                    'cta' => [
                        'link' => get_field('content_video_cta_link'),
                        'label' => get_field('content_video_cta_label')
                    ],
                )); 
            }
        ?>

        <?php 
            if(get_field('content_content_list')) {
                get_template_part('template_parts/_content-list', null, array( 
                    'classes' => 'pb-0',
                    'data' => get_field('content_content_list'), 
                )); 
            } 
        ?> 

        <?php if(get_field('accordion') && get_field('accordion_accordion')) : ?>
            <section class="accordion section">
                <div class="container">
                    <div class="d-flex row justify-content-between align-items-stretch">
                        <?php 
                            get_template_part('template_parts/_section-header', null, array( 
                                'classes' => 'col-12 col-lg-6 pe-4 pe-lg-5 mb-5 mb-lg-0',
                                'title' => get_field('accordion_title'), 
                                'text' => get_field('accordion_text'), 
                                'template' => 'single'
                            )); 

                            if(get_field('accordion_accordion')) {
                                get_template_part('template_parts/_accordion', null, array( 
                                    'classes' => 'flex-fill ps-lg-5',
                                    'data' => get_field('accordion_accordion')
                                )); 
                            }                            
                        ?>                           
                    </div>
                </div>
            </section>
        <?php endif; ?>           
        
        <?php if(wp_get_object_terms( $post->ID, 'categoria' )) : ?>
            <?php 
                $terms = wp_get_object_terms( $post->ID, 'categoria' );
                $terms = array_map(function ($term) {
                    return array(
                        'taxonomy' => 'categoria',
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                        'operator' => 'IN',
                    );
                }, $terms);    
                
                $query = new WP_Query( array(
                    'posts_per_page' => -1,
                    'post_type' => 'servicos',
                    'post_status' => 'publish',
                    'order' => 'DESC',
                    'orderby' => 'ID',
                    'tax_query' => $terms,
                ) );

                if ( $query->have_posts() ) {
                    ?>
                    <section class="relationed-posts section">
                        <div class="container pt-0">
                            <?php 
                                get_template_part('template_parts/_section-header', null, array( 
                                    'classes' => '', 
                                    'title' => 'Você também pode se interessar por', 
                                )); 
                            ?>                             
                            <ul class="relationed-posts-list d-flex row align-items-stretch overflow-auto">
                                <?php 
                                    while ( $query->have_posts() ) {
                                        $query->the_post();
                                        ?>
                                        <li onclick="location.href = '<?php the_permalink(); ?>';" class="relationed-posts-list-item d-block overflow-hidden ps-3 pe-3">
                                            <div style="background-image: url(<?php echo get_field('thumbnail'); ?>)" class="item-inner d-flex flex-column justify-content-end align-items-center">
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
        <?php endif; ?>
    </div>
<?php get_footer(); ?>

