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
                    if ( have_posts() ) {
                        ?>

                        <?php 
                            while ( have_posts() ) {
                                the_post(); 

                                get_template_part('template_parts/_post-card', null, array( 
                                    'classes' => 'col-12 col-md-6 mb-4',
                                    'data' => $post
                                )); 
                            } 
                        ?>
                        <?php get_template_part('template_parts/_paginate'); ?>
                        <?php 
                    } else {
                        ?>
                        <p class="text-center p-5 d-block">Nenhum resultado encontrado.</p>
                        <?php 
                    }
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