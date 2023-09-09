<?php get_header(); ?>
    <?php 
        $paged = get_query_var('paged');
        $paged = ($paged) ? $paged : 1;
    ?>
    <?php get_template_part('template_parts/_banner'); ?>
    <?php get_template_part('template_parts/_breadcrumbs'); ?>
    <?php get_template_part('template_parts/_page-header'); ?>

    <?php 
        if(get_queried_object(  )->name === 'servicos') {
            $terms = get_terms([
                'post_type' => get_queried_object(  )->name,
                'taxonomy' => 'categoria',
                'hide_empty' => false
            ]);     

            if($terms) {
                ?>
                <section class="taxonomies section">
                    <ul class="taxonomies-filter d-flex flex-wrap align-items-stretch justify-content-between">
                        <?php 
                            $i = 0;
                            foreach ($terms as $term) {
                                $i++;
                                ?>
                                <li data-slug="<?php echo $term->slug; ?>" style="background-image:url(<?php echo get_field('thumbnail', $term); ?>)" class="col-6 col-lg-auto overflow-hidden taxonomies-filter-item d-flex flex-column justify-content-center align-items-center <?php echo $i === 1 ? 'active' : ''; ?>">
                                    <h2 class="title d-flex flex-column justify-content-center align-items-center">
                                        <a class="d-flex flex-column justify-content-center align-items-center" href="javascript:void(0)" title="<?php echo $term->name; ?>" data-cat="<?php echo $term->term_id; ?>">
                                            <?php echo $term->name; ?>
                                        </a>
                                    </h2>
                                </li>
                                <?php 
                            }
                        ?>
                    </ul>
                    <div id="filtered-terms" class="container d-block pt-0">
                        <ul class="taxonomies-terms">
                            <?php 
                                $i = 0;
                                foreach ($terms as $term) {
                                    $i++;
                                    ?>
                                    <li class="taxonomies-terms-item <?php echo $i === 1 ? 'd-block' : 'd-none'; ?>" data-slug="<?php echo $term->slug; ?>" data-cat="<?php echo $term->term_id; ?>">
                                        <?php
                                            // $term->term_id
                                            $_query = new WP_Query( array(
                                                'post_type' => 'servicos',
                                                'posts_per_page' => -1,
                                                'tax_query' => array(
                                                    array (
                                                        'taxonomy' => get_term($term->term_id)->taxonomy,
                                                        'field' => 'id',
                                                        'terms' => $term->term_id,
                                                    )
                                                )
                                            ) );

                                            if($_query->post_count) {
                                                ?>
                                                <ul  class="d-flex taxonomies-filter-item-inner align-items-stretch flex-wrap">
                                                    <?php 
                                                        while ( $_query->have_posts() ) : 
                                                            $_query->the_post();
                                                            ?>
                                                            <li onclick="location.href = '<?php echo get_the_permalink(); ?>';" class="col-12 col-md-6 col-lg-4 mb-5 pb-5">
                                                                <h2 class="title d-block"><?php the_title(); ?></h2>
                                                                <p class="text d-block mt-4">
                                                                    <?php echo get_the_excerpt(); ?>
                                                                </p>
                                                            </li>
                                                            <?php 
                                                        endwhile;
                                                        wp_reset_query();
                                                        wp_reset_postdata();                                             
                                                    ?>
                                                </ul>
                                                <?php 
                                            }
                                        ?>
                                    </li>
                                    <?php 
                                }
                            ?>
                        </ul>
                    </div>
                </section>                
                <?php 
            } 
            

        } elseif(get_queried_object(  )->name === 'cases') {
            if ( have_posts() ) :
                ?>
                <section class="cases section">
                    <ul class="cases-list d-flex flex-wrap align-items-stretch">
                        <?php 
                            while ( have_posts() ) :
                                the_post(); 
                                ?>
                                <li class="cases-list-item col-12 col-md-6">
                                    <div onclick="location.href = '<?php echo get_the_permalink(); ?>';" style="background-image: url(<?php echo get_the_post_thumbnail_url(); ?>)" class="item-inner p-5 pt-4 pb-4 d-flex flex-column justify-content-end">
                                        <h2 class="title"><?php echo get_the_title(); ?></h2>
                                        <p class="text mt-5"><?php echo get_the_excerpt(); ?></p>
                                    </div>
                                </li>
                                <?php 
                            endwhile;
                        ?>
                    </ul>
                    <?php get_template_part('template_parts/_paginate'); ?>
                </section>
            <?php endif; ?>
        <?php get_template_part('template_parts/_taxonomy-panel'); ?>
        <?php get_template_part('template_parts/_blog'); ?>
        <?php 
        } elseif(get_queried_object(  )->name === 'clientes') {
            if ( have_posts() ) :
                ?>
                <section class="clientes section">
                    <div class="container">
                        <ul class="clientes-list row d-flex flex-wrap align-items-stretch">
                            <?php 
                                while ( have_posts() ) :
                                    the_post(); 
                                    ?>
                                    <li class="clientes-list-item col-6 col-md-3">
                                        <div onclick="location.href = '<?php echo get_the_permalink(); ?>';" class="item-inner d-flex justify-content-center align-items-center">
                                            <img class="img-fluid ps-3 pe-3 p-md-0" loading="lazy" src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php echo get_the_title(); ?>" />
                                        </div>
                                    </li>
                                    <?php 
                                endwhile;
                            ?>
                        </ul>
                        <?php get_template_part('template_parts/_paginate'); ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php 
        }
    ?>
<?php get_footer(); ?>