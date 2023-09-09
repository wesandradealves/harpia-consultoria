<aside class="sidebar <?php echo $args['classes']; ?>">
    <div class="sidebar-inner">
        <?php if(get_field('address', 'option')) : ?>
            <p class="contact-info d-block">
                <a href="<?php echo get_field('google_maps_link', 'option'); ?>" class="contact-info d-flex address align-items-start ">
                    <i class="fa-solid fa-location-pin"></i>
                    <span class="ps-3 d-inline-flex"><?php echo get_field('address', 'option') ?></span>
                </a>
            </p>
        <?php endif; ?>
        <?php if(get_field('phone', 'option')) : ?>
            <p class="contact-info d-block mt-4">
                <a href="tel:+55<?php echo str_replace([':', '\\', '/', '*', '-', ' ', '(', ')'], '', get_field('phone', 'option')); ?>" class="contact-info d-flex phone align-items-start ">
                    <i class="fa-solid fa-phone"></i>
                    <span class="ps-3 d-inline-flex">
                        <?php echo get_field('phone', 'option'); ?>
                    </span>
                </a>  
            </p>
        <?php endif; ?>
        <?php if(get_field('contact', 'option')) : ?>
            <p class="contact-info d-block mt-4">
                <a href="mailto:<?php echo get_field('contact', 'option') ?>" class="contact-info d-flex contact align-items-start ">
                    <i class="fa-solid fa-envelope"></i>
                    <span class="ps-3 d-inline-flex">
                        <?php echo get_field('contact', 'option') ?>
                    </span>
                </a>  
            </p>
        <?php endif; ?>   
        <?php get_template_part('template_parts/_socialnetworks', null, array(
            'class' => 'mt-5'
        )); ?>
        <?php if ( is_active_sidebar( 'contact' ) ) : ?>
            <?php dynamic_sidebar( 'contact' ); ?>
        <?php endif; ?>    
    </div>
</aside>