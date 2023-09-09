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
                <div class="content-area-inner d-flex align-items-stretch">
                    <?php
                        while ( have_posts() ) : the_post(); 
                            ?>
                            <div class="<?php if(get_field('content_media')) : ?>col-lg-7 pe-lg-5<?php endif; ?> d-flex flex-column">
                                <?php the_content(); ?>
                                <?php if(get_field('content_cta')) : ?>
                                    <a title="<?php echo get_field('content_cta')['label']; ?>" href="<?php echo get_field('content_cta')['link']; ?>" class="btn primary mt-5 mt-lg-auto d-inline-block me-auto"><?php echo get_field('content_cta')['label']; ?></a>
                                <?php endif; ?>
                            </div>
                            <?php if(get_field('content_media')) : ?>
                                <div class="flex-fill d-flex flex-column align-items-end media ps-lg-5">
                                    <img class="img-fluid" height="735" width="480" loading="lazy" src="<?php echo get_field('content_media'); ?>" alt="<?php echo get_the_title(); ?>" />
                                </div>
                            <?php endif; ?>
                            <?php 
                        endwhile;
                    ?>
                </div>
            </div>
        </section>

        <?php if(get_field('content_diagnostic')) : ?>
            <section class="diagnostics section">
                <div class="d-flex flex-column flex-lg-row align-items-stretch  container pt-0">
                    <div <?php if(get_field('content_diagnostic_diagnostic')) : ?> class="col-12 col-lg-5 pe-lg-5" <?php endif; ?>>
                        <h2 class="title mb-4">DIAGNÓSTICO</h2>
                        <p class="text">
                            <?php echo get_field('content_diagnostic_text'); ?>
                        </p>
                    </div>
                    <?php if(get_field('content_diagnostic_diagnostic')) : ?>
                    <ul class="diagnostics-list flex-fill media ps-lg-5 mt-5 mt-lg-0">
                        <?php 
                            foreach (get_field('content_diagnostic_diagnostic') as $item) {
                                ?>
                                <li class="diagnostics-list-item mb-1 p-5">
                                    <p class="text d-flex align-items-start">
                                        <i class="fa-solid fa-angle-right"></i>
                                        <span>
                                            <?php echo $item['text']; ?>
                                        </span>
                                    </p>
                                </li>                                
                                <?php 
                            }
                        ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>   

        <?php if(get_field('content_video')) : ?>
            <section class="video section">
                <div class="container ps-0 pe-0 d-flex flex-column flex-lg-row align-items-stretch">
                    <div class="video-wrapper col-12 col-lg-6 order-2 order-lg-1">
                        <?php if(get_field('content_video_video')) : ?>
                            <a class="yu2fvl" href="https://www.youtube.com/watch?v=<?php echo get_field('content_video_video'); ?>">
                                <img height="69" width="69" loading="lazy" class="play d-block" src="<?php echo get_template_directory_uri(); ?>/img/play-btn.png" alt="Play" />
                                <img height="720" width="1280" class="img-fluid d-block" loading="lazy" src="https://i3.ytimg.com/vi/<?php echo get_field('content_video_video'); ?>/maxresdefault.jpg" alt="" />
                            </a>                        
                        <?php endif; ?>
                    </div>
                    <?php 
                        get_template_part('template_parts/_section-header', null, array( 
                            'classes' => 'flex-fill mb-5 mb-lg-0 order-1 order-lg-2 pb-0 pe-lg-5 '.(get_field('content_video_video') ? 'ps-lg-5' : ''), 
                            'title' => get_field('content_video_title'), 
                            'text' => get_field('content_video_text'), 
                            'cta' => [
                                'link' => '/contato',
                                'label' => 'Fale com um especialista'
                            ]
                        )); 
                    ?> 
                </div>
            </section>
        <?php endif; ?>  

        <?php if(get_field('content_content_list')) : ?> 
            <section class="content-list section">
                <div class="container">
                    <ul class="content-list-items">
                        <?php 
                            foreach (get_field('content_content_list') as $item) {
                                ?>
                                    <li class="content-list-items-item d-flex flex-column flex-lg-row pb-5 mb-5">
                                        <div class="col-lg-4">
                                            <h3 class="title">
                                                <span class="d-block">
                                                    <?php echo $item['title']; ?>
                                                </span>
                                                <small class="d-block subtitle mt-3"><?php echo $item['subtitle']; ?></small>
                                            </h3>
                                        </div>
                                        <div class="flex-fill ps-lg-5 mt-5 mt-lg-0">
                                            <p class="text"><?php echo $item['text']; ?></p>
                                        </div>
                                    </li>
                                <?php 
                            }
                        ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>  

        <?php if(get_field('accordion')) : ?>
            <section class="accordion section">
                <div class="container pt-0 ps-0 pe-0 d-flex flex-column flex-lg-row align-items-stretch">
                    <?php 
                        get_template_part('template_parts/_section-header', null, array( 
                            'classes' => 'col-lg-6 mb-5 mb-lg-0 pb-0 '.(get_field('accordion_accordion') ? 'pe-lg-5' : ''), 
                            'title' => get_field('accordion_title'), 
                            'text' => get_field('accordion_text')
                        )); 

                        if(get_field('accordion_accordion')) {
                            get_template_part('template_parts/_accordion', null, array( 
                                'classes' => 'flex-fill ps-lg-5',
                                'data' => get_field('accordion_accordion')
                            )); 
                        }
                    ?> 
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