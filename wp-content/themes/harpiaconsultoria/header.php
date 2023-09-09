<?php $lang = explode("lang=",get_language_attributes()); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <title><?php echo get_bloginfo('title'); ?></title>
    <meta charset="<?php echo bloginfo( 'charset' ); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta content="<?php echo get_bloginfo('blogdescription'); ?>" name=description>
    <meta http-equiv="content-language" content="<?php echo str_replace('"','',$lang[1]); ?>" />
    <meta name="language" content="<?php echo str_replace('"','',$lang[1]); ?>" />
    <meta property="og:locale" content="<?php echo str_replace('"','',$lang[1]); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?php echo get_bloginfo('title').' - '.$title; ?>" />
    <meta property="og:description" content="<?php echo get_bloginfo('blogdescription'); ?>" />
    <meta property="og:url" content="<?php echo site_url(); ?>" />
    <meta property="og:site_name" content="<?php echo get_bloginfo('title'); ?>" />
    <meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/img/screenshot.png" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="true">
    <link rel="canonical" href="<?php echo site_url(); ?>" />
    <?php 
        wp_meta(); 
        wp_head();
    ?>
</head>

<body <?php body_class(); ?> style="display: none">
    <div id="wrap">
        <header class="header">
            <?php get_template_part('template_parts/_topbar'); ?>
            <div class="header-top">
                <div class="container d-flex flex-wrap align-items-center justify-content-between">
                    <?php get_template_part('template_parts/_logo'); ?>
                    <nav class="navigation d-flex align-items-center justify-content-end">
                        <?php 
                            wp_nav_menu( 
                                array( 
                                    'theme_location' => 'main', 
                                    'menu_class' => 'd-none d-lg-flex align-items-center justify-content-end',   
                                    'container' => false,
                                    'walker' => new Walker_Nav_Primary()
                                ) 
                            ); 
                        ?>
                        <?php if(get_page_by_title( 'Contato' )) : ?>
                            <a class="btn primary d-none d-sm-block" href="<?php echo get_permalink( get_page_by_title( 'Contato' ) ); ?>">Fale Conosco</a>
                        <?php endif; ?>  
                        <button class="hamburger hamburger--collapse p-0 m-0 d-lg-none" type="button">
                            <span class="hamburger-box">
                                <span class="hamburger-inner"></span>
                            </span>
                        </button>                          
                    </nav>
                </div>
            </div>
            <div class="header-bottom">
                <nav class="navigation mobile">
                    <?php 
                        wp_nav_menu( 
                            array( 
                                'theme_location' => 'main', 
                                'menu_class' => 'd-flex flex-column',   
                                'container' => false
                            ) 
                        ); 
                    ?>   
                    <div class="navigation-bottom d-flex align-items-center">
                        <?php get_template_part('template_parts/_socialnetworks'); ?>
                    </div>
                </nav>
            </div>
        </header>
        <main>