<?php get_header(); ?>
    <?php get_template_part('template_parts/_banner'); ?>
    <?php get_template_part('template_parts/_breadcrumbs'); ?>
    <?php get_template_part('template_parts/_page-header'); ?>

    <?php 
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post(); 
            endwhile;
            
            $total_pages = $wp_query->max_num_pages;

            if ($total_pages > 1) {
                $current_page = max(1, get_query_var('paged')); 
                echo paginate_links(array(
                    'base' => get_pagenum_link(1).
                    '%_%',
                    //'format' => '/page/%#%',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'prev_text' => __('<i class="fas fa-chevron-left page-prev"></i>'),
                    'next_text' => __('<i class="fas fa-chevron-right page-next"></i>'),
                ));   
            }    

            wp_reset_query();
            wp_reset_postdata();  
        endif;
    ?>
<?php get_footer(); ?>