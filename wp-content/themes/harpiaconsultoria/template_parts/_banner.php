<?php 
    $custom_bg = null;

    if(is_archive(  )) {
        if(get_queried_object(  )->taxonomy !== 'category') {
            $custom_bg = get_template_directory_uri().'/img/'.get_queried_object(  )->name.'.jpg';
        } else {
            $custom_bg = get_template_directory_uri().'/img/blog.jpg';
        }
    } elseif(is_single(  )) {
        if(get_post_type() === 'cases') {
            $custom_bg = get_template_directory_uri().'/img/'.get_post_type().'.jpg';
        } elseif(get_post_type() === 'post') {
            $blog = get_page_by_title( 'Blog' );
            $custom_bg = get_the_post_thumbnail_url($blog);
        }
    } elseif(is_search(  )) {
        $blog = get_page_by_title( 'Blog' );
        $custom_bg = get_the_post_thumbnail_url($blog);
    }
?>

<section class="banner overflow-hidden" style="background: url(<?php echo $custom_bg ? $custom_bg : get_the_post_thumbnail_url(); ?>) center bottom / cover no-repeat">
    <div class="container">
        <?php if(get_field('banner')) : ?>
            <div class="banner-inner col-lg-10 m-auto">
                    <div class="col-lg-9 col-xl-7">
                        <h1 class="d-block mb-4">
                            <small class="d-block subtitle mb-3"><?php echo strip_tags(get_field('banner')['subtitle']); ?></small>
                            <span class="d-block title">
                                <?php echo strip_tags(get_field('banner')['title']); ?>
                            </span>
                        </h1>  
                        <p class="d-block text">
                            <?php echo strip_tags(get_field('banner')['text']); ?> 
                        </p>
                        <?php if( have_rows('banner') ): ?>
                            <span class="d-flex mt-4 mt-md-auto align-items-center actions flex-column flex-md-row flex-wrap">
                                <?php while( have_rows('banner') ) : 
                                    the_row(); 
                                    while( have_rows('cta') ) : 
                                        the_row(); 
                                        ?>
                                        <a data-text-color="<?php echo get_sub_field('color')['text_color']; ?>" data-background="<?php echo 'rgba('.get_sub_field('color')['color']['red'].','.get_sub_field('color')['color']['green'].','.get_sub_field('color')['color']['blue'].','.get_sub_field('color')['color']['alpha'].')'; ?>" href="<?php echo get_sub_field('link'); ?>" class="btn me-md-4 mb-3 mb-md-0" style="color:<?php echo get_sub_field('color')['text_color']; ?>; background-color:<?php echo 'rgba('.get_sub_field('color')['color']['red'].','.get_sub_field('color')['color']['green'].','.get_sub_field('color')['color']['blue'].','.get_sub_field('color')['color']['alpha'].')'; ?>;">
                                            <?php echo get_sub_field('label'); ?>
                                        </a>
                                        <?php 
                                    endwhile;
                                ?>
                                <?php endwhile; ?>
                            </span>
                        <?php endif; ?>  
                    </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="d-none d-md-block mask"></div>
</section>