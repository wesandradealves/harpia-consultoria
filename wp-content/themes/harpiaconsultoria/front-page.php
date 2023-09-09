<?php 
    global $template; 
    $template = str_replace('.php', '', basename($template));  
    $about = get_page_by_title( 'Sobre' );
    get_header(); 
?>
    <?php get_template_part('template_parts/_banner'); ?>
    <?php get_template_part('template_parts/_taxonomy-panel'); ?>
    <?php get_template_part('template_parts/_about', null, array( 
        'video' => get_field('featured_video', $about),
        'title' => get_field('featured_titulo', $about),
        'subtitle' => get_field('featured_subtitulo', $about),
        'text' => get_field('featured_text', $about),
        'cta' => [
            'link' => get_page_link($about),
            'label' => 'Saiba mais'
        ],
        'template' => $template
    )); ?>
    <?php get_template_part('template_parts/_blog'); ?>
<?php get_footer(); ?>