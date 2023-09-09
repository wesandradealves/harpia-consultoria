<?php 
    // $terms = get_terms([
    //     'taxonomy' => 'categoria',
    //     'hide_empty' => false,
    //     'meta_query' => array(
    //         array(
    //             'key'       => 'featured',
    //             'value'     => TRUE,
    //             'compare'   => '='
    //         )
    //     )
    // ]);     

    $terms = get_terms([
        'post_type' => 'servicos',
        'taxonomy' => 'categoria',
        'hide_empty' => false
    ]);      

    if($terms) :
?>

<section id="taxonomy-panel" class="section taxonomy-panel d-flex flex-column justify-content-center">
    <?php 
        get_template_part('template_parts/_section-header', null, array( 
            'classes' => 'd-block pb-0', 
            'title' => get_field('taxonomy_panel_title', 'option'),
            'text' => get_field('taxonomy_panel_text', 'option'), 
            'read-more' => [
                'link' => '/servicos',
                'label' => 'Ver todos os serviços'
            ],           
            'template' => 'taxonomy-panel'
        )); 
    ?>
    <div class="d-block taxonomy-panel-content overflow-hidden">
        <nav class="taxonomy-panel-content--inner overflow-hidden">
            <ul class="d-flex taxonomy-panel-list overflow-hidden flex-wrap align-items-md-stretch flex-column flex-md-row">
                <?php 
                    foreach ($terms as $term) :
                    ?>
                    <li class="taxonomy-panel-list-item overflow-hidden flex-fill" style="background-image: url(<?php echo get_field('thumbnail', $term); ?>)">
                        <div class="d-flex taxonomy-panel-list-item--inner overflow-hidden">
                            <div class="d-flex flex-column justify-content-end info">
                                <h2 class="title">
                                    <a href="/servicos/?id=<?php echo $term->slug; ?>">
                                        <?php echo $term->name; ?>
                                    </a>
                                    <?php if(term_description( $term )) : ?>
                                        <span class="description d-block">
                                            <span class="d-block"><?php echo strip_tags(term_description( $term )); ?></span>
                                            <a class="d-block mt-2  " href="/servicos/?id=<?php echo $term->slug; ?>">Ver mais</a>
                                        </span>
                                    <?php endif; ?>
                                </h2>
                                <!-- <?php 
                                    // $terms = get_term_children( $term->term_id, get_term($term->term_id)->taxonomy);
                                    $query = new WP_Query( array(
                                        'post_type' => 'servicos',
                                        'orderby'   => 'menu_order',
                                        'order'     => 'ASC',
                                        'tax_query' => array(
                                            array (
                                                'taxonomy' => get_term($term->term_id)->taxonomy,
                                                'field' => 'id',
                                                'terms' => $term->term_id,
                                            )
                                        ),
                                        // 'meta_query'    => array(
                                        //     'relation'      => 'AND',
                                        //     array(
                                        //         'key'       => 'featured',
                                        //         'value'     => '1',
                                        //         'compare'   => '=',
                                        //     ),
                                        // ),
                                    ) );
                                    
                                    if($query) :
                                        ?>
                                        <ul class="posts overflow-hidden mt-3 d-flex align-items-center flex-wrap">
                                            <?php 
                                                while ( $query->have_posts() ) : 
                                                    $query->the_post();
                                                    ?>
                                                    <li class="posts-item mb-3 me-1">
                                                        <a class="d-flex flex-wrap align-items-center" href="<?php the_permalink(); ?>">
                                                            <span class="flex-fill">
                                                                <?php the_title(); ?> <small class="dot ps-1">.</small>
                                                            </span>
                                                        </a>
                                                    </li>
                                                    <?php 
                                                endwhile;
                                            ?>
                                        </ul>
                                        <?php 
                                    endif;
                                    wp_reset_query();
                                    wp_reset_postdata(); 
                                ?> -->
                            </div>
                        </div>
                    </li>                            
                    <?php 
                    endforeach;
                ?>
            </ul>
        </nav>
    </div> 
</section>
<?php endif; ?>