    </main>
    <footer class="footer">
        <?php get_template_part('template_parts/_contact-bar'); ?>
        <div class="footer-top">
            <div class="container d-flex flex-wrap align-items-center">
                <?php get_template_part('template_parts/_logo', null, array( 'class' => 'me-auto col-4 col-lg-2 col-xxl-3')); ?>
                <div class="col-12 col-sm-auto d-flex mt-3 mb-1 m-sm-0 flex-wrap align-items-center justify-content-sm-end">
                    <?php if(get_field('address', 'option')) : ?>
                        <p class="contact-item mb-2 mb-xl-0 col-12 d-flex flex-column align-items-sm-end col-xl-auto pe-xl-4">
                            <a href="<?php echo get_field('google_maps_link', 'option'); ?>" class="contact-info d-flex address align-items-start ">
                                <i class="fa-solid fa-location-pin"></i>
                                <span class="ps-3 d-none d-lg-inline-flex"><?php echo get_field('address', 'option') ?></span>
                            </a>
                        </p>
                    <?php endif; ?>
                    <?php if(get_field('phone', 'option')) : ?>
                        <p class="contact-item mb-2 mb-xl-0 col-12 d-flex flex-column align-items-sm-end col-xl-auto pe-lg-4">
                            <a href="tel:+55<?php echo str_replace([':', '\\', '/', '*', '-', ' ', '(', ')'], '', get_field('phone', 'option')); ?>" class="contact-info d-flex phone align-items-start ">
                                <i class="fa-solid fa-phone"></i>
                                <span class="ps-3 d-none d-lg-inline-flex">
                                    <?php echo get_field('phone', 'option'); ?>
                                </span>
                            </a>  
                        </p>
                    <?php endif; ?>
                    <?php if(get_field('contact', 'option')) : ?>
                        <p class="contact-item  mb-2 mb-xl-0 pe-lg-4">
                            <a href="mailto:<?php echo get_field('contact', 'option') ?>" class="contact-info d-flex contact align-items-start ">
                                <i class="fa-solid fa-envelope"></i>
                                <span class="ps-3 d-none d-lg-inline-flex">
                                    <?php echo get_field('contact', 'option') ?>
                                </span>
                            </a>  
                        </p>
                    <?php endif; ?>   
                </div>
                <?php get_template_part('template_parts/_socialnetworks', null, array( 'class' => 'd-flex flex-column align-items-sm-end col-12 pe-lg-4 col-xl-auto pe-xl-0')); ?>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <nav class="navigation">
                    <?php 
                        wp_nav_menu( 
                            array( 
                                'theme_location' => 'footer', 
                                'menu_class' => 'd-flex flex-wrap align-items-stretch',   
                                'container' => false,
                                'walker' => new Walker_Nav_Primary()
                            ) 
                        ); 
                    ?>                    
                </nav>
            </div>
        </div>
        <div class="copyright">
            <div class="container d-flex flex-column justify-content-center align-items-center">
                <p>Desenvolvido a mão por <a href="https://904.ag/" target="_blank">Agência 9ZERO4</a> © Copyright <?php echo date('Y'); ?> - Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
    <?php get_template_part('template_parts/_whatsapp'); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>