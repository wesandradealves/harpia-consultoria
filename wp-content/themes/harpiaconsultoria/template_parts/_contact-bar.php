<?php 
    global $template;
    $template = str_replace('.php', '', basename($template));
?>
<?php 
    if(!is_404() && 
    $template !== 'contato' &&
    $template !== 'obrigado') :
?>
    <?php if(get_field('contact_bar', 'option')) : ?>
        <section class="contact-bar">
            <div class="container d-flex flex-wrap flex-column justify-content-center align-items-center">
                <div class="contact-bar-inner d-flex flex-wrap align-items-center justify-content-center justify-content-lg-between">
                    <div class="col-md-6 pe-0 pe-md-5 info">
                        <h2 class="title"><?php echo get_field('contact_bar', 'option')['title']; ?></h2>
                        <p class="text"><?php echo get_field('contact_bar', 'option')['text']; ?></p>
                    </div>
                    <a href="#" title="FALAR POR WHATS" class="btn mt-4 mt-md-0 whatsapp-btn d-flex align-items-center">
                        FALAR POR WHATS
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                </div>
                <!-- -->
                <div class="bg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1439">
                        <path id="Base" d="M1439,450,296.044,195.077,0,73.732V3.352L1439,0Z" fill="#ea5428"/>
                    </svg>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>