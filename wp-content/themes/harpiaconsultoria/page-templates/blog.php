<?php /* Template Name: Blog */ ?>
<?php get_header(); ?>
    <?php 
        $paged = get_query_var('paged');
        $paged = ($paged) ? $paged : 1;
    ?>
    <?php get_template_part('template_parts/_banner'); ?>
    <?php get_template_part('template_parts/_breadcrumbs'); ?>
    <?php get_template_part('template_parts/_page-header'); ?>
    <section class="blog section">
        <div class="container pt-4 d-flex flex-column flex-md-row flex-wrap align-items-start">
            <div class="flex-fill row d-flex flex-wrap pe-md-4 order-md-1 order-2 content-area justify-content-start align-items-stretch">
                <?php 
                    $args = array(
                        'post_type' => 'post',
                        'order' => 'DESC',
                        'posts_per_page'      => '6', 
                        'paged'          => $paged 
                    );      
                    
                    $query = new WP_Query($args);
                    
                    if ($query->have_posts()) {
                        ?>

                        <?php 
                            while ( $query->have_posts() ) {
                                $query->the_post(); 

                                get_template_part('template_parts/_post-card', null, array( 
                                    'classes' => 'col-12 col-md-6 mb-4',
                                    'data' => $post
                                )); 
                            } 
                        ?>
                        <?php 
                            $total_pages = $query->max_num_pages;
                            if ($total_pages > 1) :
                        ?>
                        <ul class="paginate mt-5 d-flex flex-wrap justify-content-center align-items-center">
                            <?php 
                                $current_page = max(1, get_query_var('paged')); 
                                echo paginate_links(array(
                                    'base' => get_pagenum_link(1).
                                    '%_%',
                                    //'format' => '/page/%#%',
                                    'current' => $current_page,
                                    'total' => $total_pages,
                                    'prev_text' => __('<i class="fa fa-angle-left" aria-hidden="true"></i>
                                    '),
                                    'next_text' => __('<i class="fa fa-angle-right" aria-hidden="true"></i>
                                    '),
                                ));   
                            ?>
                        </ul>
                        <?php endif; ?>
                        <?php 
                    }
                    wp_reset_query();
                    wp_reset_postdata();                       
                ?>
            </div>
            <?php
                get_template_part('sidebar', 'blog', array(
                    'classes' => 'col-md-3 order-md-2 order-1 mb-5 mb-md-0'
                ));
            ?>
        </div>
    </section>
<?php get_footer(); ?>