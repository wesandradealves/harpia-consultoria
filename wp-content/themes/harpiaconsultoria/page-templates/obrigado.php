<?php /* Template Name: Obrigado */ ?>
<?php get_header(); ?>
    <?php get_template_part('template_parts/_breadcrumbs'); ?>
    <?php get_template_part('template_parts/_page-header'); ?>
    <div id="primary">
        <section class="accordion section">
            <div class="container">
                <div class="d-flex row flex-row-reverse justify-content-between align-items-center">
                    <?php if(get_the_post_thumbnail_url()) : ?>
                        <div class="d-none d-xl-block col-6 ps-4 ps-xl-5 media">
                            <img loading="lazy" height="515" width="720" src="<?php echo get_the_post_thumbnail_url() ?>" alt="<?php echo get_the_title(); ?>" />
                        </div>
                    <?php endif; ?>
                    <div class="flex-fill pe-xl-5">
                        <?php 
                            get_template_part('template_parts/_section-header', null, array( 
                                'classes' => 'pb-0',
                                'subtitle' => get_the_title(),
                                'text' => get_the_content(), 
                                'cta' => [
                                    'link' => site_url(),
                                    'label' => 'Voltar à Página Inicial'
                                ],
                            )); 
                        ?>   
                    </div>
                </div>
            </div>
        </section> 
    </div>
<?php get_footer(); ?>