<?php

function slugify($text, string $divider = '-'){
    // replace non letter or digits by divider
    $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
  
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
  
    // trim
    $text = trim($text, $divider);
  
    // remove duplicate divider
    $text = preg_replace('~-+~', $divider, $text);
  
    // lowercase
    $text = strtolower($text);
  
    if (empty($text)) {
      return 'n-a';
    }
  
    return $text;
  }  

function wp_before_admin_bar_render() {

    echo '

        <style type="text/css">

            #acf-group_64a3f3903b6fa {

                display: none !important;

            }

        </style>

    ';

}

function remove_menus()
{
    global $post;

    remove_menu_page("index.php"); //Dashboard

    remove_menu_page("jetpack"); //Jetpack*

    // remove_menu_page("edit.php"); //Posts;

    // remove_menu_page( 'upload.php' );                 //Media

    // remove_menu_page( 'edit.php?post_type=page' );    //Pages

    // remove_menu_page( 'edit-comments.php' );          //Comments

    //remove_menu_page( 'themes.php' );                 //Appearance

    // remove_menu_page( 'plugins.php' );                //Plugins

    // remove_menu_page( 'users.php' );                  //Users

    // remove_menu_page( 'tools.php' );                  //Tools

    // remove_menu_page( 'options-general.php' );        //Settings
}

function prefix_add_footer_styles()
{
    wp_enqueue_style(
        "overwrites",
        get_stylesheet_directory_uri() . "/css/overwrites.css",
        [],
        null
    );
}

function regScripts()
{
    wp_deregister_script("jquery");
    wp_enqueue_script(
        "jquery",
        "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js",
        [],
        false,
        true
    );
    wp_enqueue_script(
        "jqueryyu2fvl",
        get_template_directory_uri() . "/js/jquery.yu2fvl.min.js",
        [],
        false,
        true
    );    
    wp_enqueue_script(
        "mCustomScrollbar",
        "//malihu.github.io/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js",
        [],
        false,
        true
    );    
    wp_enqueue_script('commons', get_template_directory_uri()."/js/main.js", array(), filemtime( get_template_directory().'/js/main.js' ), true);
    wp_enqueue_style(
        "bootstrap-grid",
        "https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap-grid.min.css",
        [],
        null,
        "all"
    );
    wp_enqueue_style(
        "bootstrap-reboot",
        "https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap-reboot.min.css",
        [],
        null,
        "all"
    );
    wp_enqueue_style(
        "bootstrap-utilities",
        "https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap-utilities.min.css",
        [],
        null,
        "all"
    );
    wp_enqueue_style(
        "fontawesome",
        "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css",
        [],
        null,
        "all"
    );
    wp_enqueue_style(
        "hamburgers",
        get_stylesheet_directory_uri() . "/css/hamburgers.min.css",
        [],
        null,
        "all"
    );
    wp_enqueue_style(
        "jqueryyu2fvl",
        get_stylesheet_directory_uri() . "/css/jquery.yu2fvl.css",
        [],
        null,
        "all"
    );       
    wp_enqueue_style(
        "mCustomScrollbar",
        "//malihu.github.io/custom-scrollbar/jquery.mCustomScrollbar.min.css",
        [],
        null,
        "all"
    );     
    wp_enqueue_style( 'style', get_template_directory_uri().'/style.css', array(), filemtime( get_template_directory().'/style.css' ) );    
}

function disable_default_dashboard_widgets()
{
    remove_meta_box("dashboard_right_now", "dashboard", "core");

    remove_meta_box("dashboard_recent_comments", "dashboard", "core");

    remove_meta_box("dashboard_incoming_links", "dashboard", "core");

    remove_meta_box("dashboard_plugins", "dashboard", "core");

    remove_meta_box("dashboard_quick_press", "dashboard", "core");

    remove_meta_box("dashboard_recent_drafts", "dashboard", "core");

    remove_meta_box("dashboard_primary", "dashboard", "core");

    remove_meta_box("dashboard_secondary", "dashboard", "core");
}

if (function_exists("acf_add_options_page")) {
    acf_add_options_page([
        "page_title" => "Theme General Settings",
        "menu_title" => "Theme Settings",
        "menu_slug" => "theme-general-settings",
        "capability" => "edit_posts",
        "redirect" => true,
    ]);
}

function wpb_custom_new_menu()
{
    register_nav_menu("main", __("Main"));
    register_nav_menu("footer", __("Footer"));
}

function atg_menu_classes($classes, $item, $args)
{
    // if($args->theme_location == 'main') {
    //     $classes[] = 'nav-item p-0 ps-5';
    // } elseif($args->theme_location == 'footer') {
    //     $classes[] = 'nav-item nav-col col-6 mb-5 mb-lg-0 pe-5';
    // }
    $classes[] = "nav-item";
    return $classes;
}

function add_menu_link_class($atts, $item, $args)
{
    $atts["class"] = "nav-link";
    return $atts;
}

function custom_navigation($menu_name)
{
    $locations = get_nav_menu_locations();
    $menu_id = $locations[$menu_name];
    $menuObject = wp_get_nav_menu_object($menu_id);
    $array_menu = wp_get_nav_menu_items($menuObject->slug);
    $menu = [];

    foreach ($array_menu as $key => $item) {
        $menu[$item->ID] = [];
    }

    foreach ($menu as $key => $item) {
        $menu[$key]["data"] = [];

        foreach ($array_menu as $menu_item) {
            if ($menu_item->object_id == $key) {
                $menu[$key]["key"] = $menu_item->post_title;
                $menu[$key]["url"] = $menu_item->url;
                $menu[$key]["target"] = get_field("target", $menu_item->ID);
            }
        }

        foreach ($array_menu as $item) {
            $o = new \stdClass();

            if ($key == $item->menu_item_parent) {
                $o->id = $item->ID;
                $o->title = $item->post_title
                    ? $item->post_title
                    : $item->title;
                $o->url = $item->url;
                $o->target = $item->target;
                $o->data = [];

                foreach ($array_menu as $item) {
                    $_o = new \stdClass();

                    if ($o->id == $item->menu_item_parent) {
                        $_o->id = $item->ID;
                        $_o->title = $item->post_title
                            ? $item->post_title
                            : $item->title;
                        $_o->url = $item->url;
                        $_o->target = get_field("target", $item->ID);

                        array_push($o->data, $_o);
                    }
                }

                array_push($menu[$key]["data"], $o);
            }
        }
    }

    return $menu;
}

// function qirolab_posts_where($where, &$wp_query)
// {
//     global $wpdb;
//     if ($title = $wp_query->get("search_title")) {
//         $where .=
//             " AND " .
//             $wpdb->posts .
//             ".post_title LIKE '" .
//             esc_sql($wpdb->esc_like($title)) .
//             "%'";
//     }
//     return $where;
// }

function my_mce4_options($init)
{
    $custom_colours = '
        "EA5428", "primary",
        "2C4C59", "secondary",
        "18495D", "secondarylight",
        "051B2B", "secondarydark",
        "363940", "gray",
        "6A818B", "graymedium",
        "42C412", "green",
        "FFFFFF", "F5F5F5",
        "000000", "black",
    ';

    // build colour grid default+custom colors
    $init["textcolor_map"] = "[" . $custom_colours . "]";

    // change the number of rows in the grid if the number of colors changes
    // 8 swatches per row
    $init["textcolor_rows"] = 1;

    return $init;
}

function mycustom_wp_redirect()
{
    ?>
    <script type="text/javascript">
       document.addEventListener( 'wpcf7mailsent', function( event ) {
           event.preventDefault();
           
        //    var phone = event.detail.inputs[4].value;
        //    var text = event.detail.inputs[5].value;
   
           setTimeout(function() { 
               console.log(event.detail.inputs);
               // window.location.href = `https://api.whatsapp.com/send/?phone=${phone}&text=${text}`;
           }, 1000);
      
       }, false );
    </script>
   <?php
}

class Walker_Nav_Primary extends Walker_Nav_Menu {

    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n<ul class=\"submenu flex-fill\">\n";
    }

    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "</ul></div></div></div>\n";
    }

    function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );

        $class_names = $class_names ? ' '.(! empty( get_field('item_media', $item) )        ? ' data-img="'   . esc_attr( get_field('item_media', $item) ) .'"' : '').' class="' . esc_attr( $class_names ) . '"' : '';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $term_name = slugify($item->title);
        $term = get_term_by('slug', $term_name, 'categoria');

        $output .= $indent . '';
        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->classes ) ? ' class="nav-link '  . esc_attr( implode(' ', $item->classes) ) .'" ' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
        $attributes .= ! empty( get_field('item_media', $item) )        ? ' data-img="'   . esc_attr( get_field('item_media', $item) ) .'"' : '';

        $item_output = $args->before;
        $item_output .= '<li'. $class_names .' '.(! empty( $term )        ? ' data-img="'   . esc_attr( get_field('thumbnail', $term) ) .'"' : '').'>';
        
        if($item->menu_item_parent) {
            $item_output .= '<a'. $attributes .'>';
                $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
            $item_output .= '</a>';
        } else {
            $item_output .= '<a'. $attributes .'>';
                $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
            $item_output .= '</a>';
        }

        $item_output .= '<div data-depth="'.$depth.'" class="submenu-wrapper id-'.$item->ID.'"><div class="submenu-inner"><div class="container '.($item->post_name === 'servicos' ? ' d-flex flex-wrap align-items-stretch' : '').'">';
            
            if(get_field('item_media', $item)) {
                $item_output .= '<div class="d-block col-lg-3 pe-lg-5">';
                    $item_output .= '<img loading="lazy" width="280" height="240" class="thumbnail menu-thumbnail img-fluid" src="'.get_field('item_media', $item).'" />';
                    if(get_post_type_archive_link('servicos')) {
                        $item_output .= '<a class="btn primary d-block mt-4" href="'.get_post_type_archive_link('servicos').'">Ver todos os serviços</a>';
                    }
                $item_output .= '</div>';
            }
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }


    function end_el( &$output, $item, $depth = 0, $args = array() ) {
        $output .= "\n".( get_field('item_media', $item) ? "<input type='hidden' value='".get_field('item_media', $item)."' name='default_thumbnail' />" : "");
    }
}

if (function_exists("register_sidebar")) {
    register_sidebar([
        "id" => "contact",
        "name" => __("Contato"),
        "before_widget" => '<aside id="%1$s" class="widget %2$s">',
        "after_widget" => "</aside>",
        "before_title" => "",
        "after_title" => "",
    ]);
}

/**
 * Change posts per page by post type
 */
function bb_change_posts_per_page( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
       return;
    }
    if ( is_post_type_archive( 'cases' ) ) {
        $query->set( 'posts_per_page', 8 );
    }
    if ( is_post_type_archive( 'clientes' ) ) {
        $query->set( 'posts_per_page', -1 );
    }    
    if ( is_search() ) {
        $query->set( 'posts_per_page', 6 );
    }
}

function my_search_form($form)
{
    $form =
        '
    <form class="d-block" id="search" action="' .
        home_url("/") .
        '" method="GET">
        <div class="d-flex flex-wrap mb-4 searchbar">
            <button class="pe-3">
                <i class="d-none d-md-block fa-solid fa-magnifying-glass"></i>
                <i class="d-block d-md-none fa-solid fa-filter"></i>
            </button>
            <input type="hidden" name="post_type" value="post" />
            <input class="d-none d-md-block" placeholder="Busca" id="s" name="s" type="text" value="' . get_search_query() . '" />
            <input class="d-md-none" readonly placeholder="Busca" id="s" name="s" type="text" value="' . get_search_query() . '" />
        </div>
        <ul class="filters d-block">';
            $categories = get_terms([
                "taxonomy" => "category",
                "hide_empty" => true,
            ]);

            if ($categories) {
                $form .= '
                    <li class=" mb-4">
                        <h3 class="title">Categorias</h3>
                    </li> 
                    <li>
                        <ul>'
                ;
                foreach ($categories as $term) {
                    $form .=
                        '<li class="mb-4 d-flex flex-wrap align-items-center">';
                    $form .=
                        '<a class="d-flex align-items-center cat" href="' .
                        get_term_link($term) .
                        '"><i class="fas me-3 fa-solid fa-angle-right"></i> <span>' .
                        $term->name .
                        "</span></a>";
                    // $form .= '<span class="custom-checkbox">';
                    //     $form .= '<input '.(isset($_GET['mod']) && $_GET['mod'] === $term->name ? 'checked' : '').' name="modalidade[]" value="'.$term->slug.'" type="radio">';
                    //     $form .= '<label for="modalidade"></label>';
                    // $form .= '</span>';
                    // $form .= '<label class="ps-3" for="filter">'.$term->name.'</label>';
                    $form .= "</li>";
                }
                $form .= '</ul>
                </li>';
            }


            $noticias = new WP_Query([
                "post_type" => "post",
                "order" => "DESC",
                "posts_per_page" => 4,
            ]);

            if ($noticias->have_posts()) {
                $form .= '
                    <li class=" mb-4"><h3 class="title">POSTS RECENTES</h3></li>
                    <li>
                ';
                $form .= '<ul class="recent-posts mb-4">';
                while ($noticias->have_posts()) {
                    $noticias->the_post();
                    $form .=
                        '<li class="d-flex align-items-center justify-content-between" onclick="location.href = ' .
                            get_the_permalink() .
                        ';">';
                    $form .=
                        '<div class="thumbnail" style="background-image: url(' .
                        (get_the_post_thumbnail_url()
                            ? get_the_post_thumbnail_url()
                            : "https://www.charlotteathleticclub.com/assets/camaleon_cms/image-not-found-4a963b95bf081c3ea02923dceaeb3f8085e1a654fc54840aac61a57a60903fef.png") .
                        ');" class="thumbnail"></div>';
                    $form .= '<div class="flex-fill ps-4">';
                    $form .=
                        '<h3 class="title"><a href="' .
                        get_the_permalink() .
                        '">' .
                        substr(get_the_title(), 0, 45) . '...' .
                        "</a></h3>";
                    $form .= "</div>";
                    $form .= "</li>";
                }
                wp_reset_query();
                wp_reset_postdata();
                $form .= "</ul>
                </li>";
            }
        $form .= '</ul>
    </form>';

    $form .= '<div class="d-block newsletter filters-group">';
    $form .= '<h3 class="title mb-3">RECEBA DICAS SOBRE GESTÃO DE EMPRESAS</h3>';
    $form .=
        '<p class="text mb-4">Que tal receber as últimas novidades do mundo empresarial no seu e-mail? Cadastre-se agora mesmo.</p>';
    $form .= do_shortcode('[contact-form-7 id="430" title="Newsletter"]');
    $form .= "</div>";

    return $form;
}

function save_my_form_data_to_my_cpt($contact_form)
{
    if ($contact_form->id === 430) {
        $submission = WPCF7_Submission::get_instance();
        if (!$submission) {
            return;
        }
        $posted_data = $submission->get_posted_data();
        //The Sent Fields are now in an array
        //Let's say you got 4 Fields in your Contact Form
        //my-email, my-name, my-subject and my-message
        //you can now access them with $posted_data['my-email']
        //Do whatever you want like:
        $new_post = [];
        if (isset($posted_data["email"]) && !empty($posted_data["email"])) {
            $new_post["post_title"] = $posted_data["email"];
        }
        $new_post["post_type"] = "newsletter"; //insert here your CPT
        $new_post["post_status"] = "publish";
        //you can also build your post_content from all of the fields of the form, or you can save them into some meta fields
        // if(isset($posted_data['my-email']) && !empty($posted_data['my-email'])){
        //     $new_post['meta_input']['sender_email_address'] = $posted_data['my-email'];
        // }
        // if(isset($posted_data['my-name']) && !empty($posted_data['my-name'])){
        //     $new_post['meta_input']['sender_name'] = $posted_data['my-name'];
        // }
        //When everything is prepared, insert the post into your Wordpress Database
        if ($post_id = wp_insert_post($new_post)) {
            //Everything worked, you can stop here or do whatever
        } else {
            //The post was not inserted correctly, do something (or don't ;) )
        }
    }
    return;
}

add_theme_support("post-thumbnails");
add_action("wpcf7_mail_sent", "save_my_form_data_to_my_cpt");
add_action("wpcf7_mail_failed", "save_my_form_data_to_my_cpt");
add_filter("get_search_form", "my_search_form");
add_filter( 'pre_get_posts', 'bb_change_posts_per_page' );
add_filter("tiny_mce_before_init", "my_mce4_options");
// add_filter("posts_where", "qirolab_posts_where", 10, 2);
add_filter("nav_menu_link_attributes", "add_menu_link_class", 1, 3);
add_filter("nav_menu_css_class", "atg_menu_classes", 1, 3);
add_action("get_footer", "prefix_add_footer_styles");
add_action("init", "wpb_custom_new_menu");
add_action("wp_enqueue_scripts", "regScripts");
add_action("admin_menu", "remove_menus");
add_action("admin_menu", "disable_default_dashboard_widgets");
add_action("wp_footer", "mycustom_wp_redirect");
add_action('wp_before_admin_bar_render', 'wp_before_admin_bar_render');