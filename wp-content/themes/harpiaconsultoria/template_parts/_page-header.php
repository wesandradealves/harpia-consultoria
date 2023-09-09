<?php 
    $title = $text = null;
    if(is_archive(  )) {
        $title = get_queried_object(  )->label;
        if(get_queried_object(  )->taxonomy !== 'category') {
            $text = get_field('archives_'.get_queried_object(  )->name.'_text', 'option');
        } else {
            $title = get_queried_object(  )->name;
        }
    } elseif(is_single(  )) {
        if(get_post_type() === 'post') {
            $blog = get_page_by_title( 'Blog' );
            $title = get_the_title($blog);  
            $text = get_field('header_text', $blog);  
        } else {
            $title = get_the_title();
            $text = get_the_excerpt();
        }
    } elseif(is_search(  )) {
        $title = 'Busca';  
        $text = 'Você está buscando por: '.$_GET['s'];  
    } elseif(is_404()) {
        $title = 'Página não encontrada';
        $text = get_field('404_excerpt', 'option');
    } else {
        $title = get_field('header')['title'] ? get_field('header')['title'] : get_the_title();
        $text = get_field('header')['text'] ? get_field('header')['text'] : get_the_excerpt();
    }

    get_template_part('template_parts/_section-header', null, array( 
        'classes' => 'd-block page-header pb-0', 
        'title' => $title,
        'text' => $text, 
        'template' => 'page-header'
    ));     
?>