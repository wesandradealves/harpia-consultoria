<?php /* Template Name: Sobre */ ?>
<?php 
    global $template; 
    $diferentials = get_field('diferentials');
    $accordion = get_field('accordion');
    $template = str_replace('.php', '', basename($template));  
    get_header(); 
?>
    <?php get_template_part('template_parts/_banner'); ?>
    <?php get_template_part('template_parts/_breadcrumbs'); ?>
    <?php get_template_part('template_parts/_page-header'); ?>
    <?php get_template_part('template_parts/_about', null, array( 
        'classes' => 'flex-md-row-reverse mt-md-5',
        'video' => get_field('featured_video'),
        'text' => get_the_content(),
        'template' => $template
    )); ?>
    <?php if($diferentials) : ?>
        <section class="diferentials section">
            <div class="container">
                <div class="d-flex row justify-content-between align-items-stretch">
                    <?php 
                        get_template_part('template_parts/_section-header', null, array( 
                            'classes' => 'col-12 col-xl-6 pe-4 pe-xl-5',
                            'title' => $diferentials['title'], 
                            'text' => $diferentials['text'], 
                            'read-more' => null,
                            'cta' => $args['template'] === 'about' ? null : [
                                'link' => $diferentials['cta']['link'],
                                'label' => $diferentials['cta']['label']
                            ],
                            'template' => $args['template']
                        )); 
                    ?>   
                    <div class="flex-fill ps-4 ps-xl-5">
                        <?php if($diferentials['diferentials']) : ?>
                            <?php if( have_rows('diferentials_diferentials') ): ?>
                                <ul class="diferentials-list row d-flex flex-row flex-xl-column flex-wrap">
                                    <?php while( have_rows('diferentials_diferentials') ) : 
                                        the_row(); 
                                        ?>
                                        <li class="d-flex align-items-center diferentials-list-item mb-5 col-12 col-md-6 col-xl-12">
                                            <?php if(get_sub_field('icon')) : ?>
                                                <img class="img-fluid icon" src="<?php echo get_sub_field('icon'); ?>" alt="<?php echo get_sub_field('title'); ?>" />
                                            <?php endif; ?>
                                            <span class="flex-fill ps-5">
                                                <h3 class="title mb-4 d-block"><?php echo get_sub_field('title'); ?></h3>
                                                <p class="text d-block"><?php echo get_sub_field('text'); ?></p>
                                            </span>
                                        </li>
                                        <?php 
                                    ?>
                                    <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>  
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <?php if($accordion) : ?>
        <section class="accordion section">
            <div class="container">
                <div class="d-flex row justify-content-between align-items-center">
                    <?php if($accordion['media']) : ?>
                        <div class="d-none d-xl-block col-6 pe-4 pe-xl-5 media">
                            <img loading="lazy" height="515" width="720" src="<?php echo $accordion['media'] ?>" alt="<?php echo $accordion['title'] ?>" />
                        </div>
                    <?php endif; ?>
                    <div class="flex-fill ps-xl-5">
                        <?php 
                            get_template_part('template_parts/_section-header', null, array( 
                                'title' => $accordion['title'], 
                                'template' => $args['template']
                            )); 

                            if($accordion['accordion']) {
                                get_template_part('template_parts/_accordion', null, array( 
                                    'classes' => 'mt-5',
                                    'data' => $accordion['accordion']
                                )); 
                            }
                        ?>   
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>    
    <?php get_template_part('template_parts/_taxonomy-panel'); ?>
    <?php get_template_part('template_parts/_blog'); ?>
<?php get_footer(); ?>