<div class="topbar">
    <div class="container d-flex flex-wrap justify-content-between">
        <?php get_template_part('template_parts/_socialnetworks'); ?>
        <?php if( get_field('phone', 'option') ): ?>
            <div>
                <p class="d-flex flex-wrap align-items-center">
                    <i class="fa-solid fa-phone"></i>
                    <a href="tel:+55<?php echo str_replace([':', '\\', '/', '*', '-', ' ', '(', ')'], '', get_field('phone', 'option')); ?>"><?php echo get_field('phone', 'option'); ?></a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>