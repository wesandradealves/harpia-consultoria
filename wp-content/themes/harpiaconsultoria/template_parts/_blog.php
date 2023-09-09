<?php 
    $query = new WP_Query( array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'order'     => 'DESC',
    ) );    

    if($query) :
?>
<section class="dicas-e-novidades section">
    <div class="container ps-4 pe-4">
        <?php 
            get_template_part('template_parts/_section-header', null, array( 
                'title' => 'Dicas e novidades do mundo empresarial',
                'template' => 'blog'
            )); 
        ?>
        <ul class="grid d-flex align-items-stretch justify-content-between">
            <?php 
                $i = 0;
                $blog = get_page_by_title( 'Blog' );
                while ( $query->have_posts() ) : $i++;
                    $query->the_post();
                    ?>
                    <li onclick="location.href = '<?php the_permalink(); ?>';" class="grid-item <?php if($i > 1) : ?> flex-fill ms-4 ps-2 <?php endif; ?>">
                        <div class="grid-item-inner overflow-hidden" <?php if($i === 1) : ?> style="background-image: url(<?php echo get_the_post_thumbnail_url($post->ID, 'medium'); ?>)" <?php endif; ?>>
                            <div class="thumbnail <?php if($i === 1) : ?> d-block d-lg-none <?php endif; ?>">
                                <img width="300" height="300" class="d-block img-fluid" loading="lazy" src="<?php echo get_the_post_thumbnail_url($post->ID, 'medium'); ?>" alt="<?php the_title(); ?>">
                            </div>
                            <h3 class="title d-block mt-3 mb-3"><?php the_title(); ?></h3>
                            <p class="text d-block"><?php the_time('M d'); ?></p>
                        </div>
                        <?php if($i === $query->post_count) : ?>
                            <a class="read-more d-none d-lg-block mt-auto ms-auto" href="<?php the_permalink($blog); ?>">Ver todos <i class="fa-solid fa-angle-right"></i></a>
                        <?php endif; ?>
                    </li>
                    <?php 
                endwhile;
                wp_reset_query();
                wp_reset_postdata();                         
            ?>
        </ul>
    </div>
</section>
<?php endif; ?>