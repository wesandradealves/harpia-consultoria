<?php 
    global $template; 
    $template = str_replace('.php', '', basename($template));  
    get_header(); 
?>
    <?php get_template_part('template_parts/_banner'); ?>
    <?php get_template_part('template_parts/_breadcrumbs'); ?>
    <?php get_template_part('template_parts/_page-header'); ?>
    <?php the_content(); ?>
    <?php get_template_part('template_parts/_taxonomy-panel'); ?>
    <?php get_template_part('template_parts/_blog'); ?>
<?php get_footer(); ?>