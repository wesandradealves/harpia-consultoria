<?php if(get_field('whatsapp_form_id', 'option')) : ?>
<div class="module-whatsapp" id="module-whatsapp" style="visibility: hidden;">
    <div class="module-whatsapp-btn whatsapp-btn btn-whatsapp-pulse" id="module-whatsapp-btn">
        <div class="whatsapp-icon-message">
            <div class="whatsapp-icon-message-close">
                <i class="fas fa-times"></i>
            </div>
            <figure>
                <img src="<?php echo get_field('whatsapp_thumbnail', 'option'); ?>"
                    alt="<?php echo get_bloginfo('title'); ?>" />
            </figure>
            <div class="whatsapp-icon-message-content">
                <p><strong>Olá! Posso ajudar?</strong> Qualquer coisa me chama aqui, ta?</p>
            </div>
        </div>
        <div class="module-whatsapp-btn-icon">
            <i class="fa-solid fa-xmark"></i>
        </div>
    </div>

    <div class="module-whatsapp-container" id="module-whatsapp-container">
        <div class="module-whatsapp-header">
            <div class="module-whatsapp-header-icon fa-brands fa-whatsapp"></div>
            <div class="module-whatsapp-header-title">
                <strong>Comece uma conversa</strong>
                <p>Cadastre-se para começar uma conversa no <b>WhatsApp</b></p>
            </div>
        </div>
        <div class="module-whatsapp-content">
            <div class="module-whatsapp-content-form">
                <?php echo do_shortcode('[contact-form-7 id="'.get_field('whatsapp_form_id', 'option').'" title="WhatsApp Icon – PT-BR"]'); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>