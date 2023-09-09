<?php /* Template Name: Contato */ ?>
<?php get_header(); ?>
    <?php get_template_part('template_parts/_banner'); ?>
    <?php get_template_part('template_parts/_breadcrumbs'); ?> 
    <?php get_template_part('template_parts/_page-header'); ?>
    <div id="primary">
        <section class="contact section">
            <div class="container d-flex flex-wrap align-items-stretch">
                <?php
                    get_template_part('sidebar', 'contact', array(
                        'classes' => 'pe-md-5 d-none d-md-block col-4'
                    ));
                ?>
                <div class="flex-fill forms-group">
                    <div class="form-group form-switcher d-block align-items-center mb-4">
                        <span class="group-item">
                            <span class="custom-radio">
                                <span class="wpcf7-form-control-wrap" data-name="switcher">
                                    <span class="wpcf7-form-control wpcf7-radio">
                                        <span class="wpcf7-list-item first">
                                            <input type="radio" name="switcher" value="wpcf7-f650-o2" checked="checked">
                                            <span class="wpcf7-list-item-label">Fale Conosco</span>
                                        </span>
                                        <span class="wpcf7-list-item last">
                                            <input type="radio" name="switcher" value="wpcf7-f408-o1">
                                            <span class="wpcf7-list-item-label">Trabalhe Conosco</span>
                                        </span>
                                    </span>
                                </span>
                            </span>
                        </span>
                    </div>   
                    <div class="d-block" data-form="wpcf7-f650-o2">
                        <?php echo do_shortcode('[contact-form-7 id="650" title="Fale Conosco"]'); ?>
                    </div>  
                    <div class="d-none" data-form="wpcf7-f408-o1"> 
                        <?php echo do_shortcode('[contact-form-7 id="408" title="Trabalhe Conosco"]'); ?>
                    </div>                             
                </div>
            </div>
        </section>
        <section class="map section">
            <div class="container d-flex flex-column justify-content-center">
                <div class="col-10 col-lg-4">
                    <?php
                        get_template_part('sidebar', 'contact', array(
                            'classes' => 'p-3 ps-0 p-lg-5'
                        ));
                    ?>                
                </div>
            </div>
            <div id="map">
                <iframe src="<?php echo get_field('google_maps_embed', 'option'); ?>" frameborder="0"></iframe>
            </div>
        </section>
    </div>
<?php get_footer(); ?>