<?php 
    $src = get_field('alt_logo', 'option') && did_action( 'get_footer' ) ? get_field('alt_logo', 'option') : (get_field('logo', 'option') && !did_action( 'get_footer' ) ? get_field('logo', 'option') : '');
?>

<span class="logo <?php echo isset($args['class']) ? $args['class'] : ''; ?>">
    <a href="<?php echo site_url(); ?>">
        <img width="152" height="61" loading="lazy" class="img-fluid" src="<?php echo $src; ?>" alt="<?php echo get_bloginfo('title'); ?>">
    </a>
</span>