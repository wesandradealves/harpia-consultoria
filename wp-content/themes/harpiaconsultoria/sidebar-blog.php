<aside class="sidebar blog <?php echo $args['classes']; ?>">
    <div class="sidebar-inner">
        <div class="justify-content-end align-items-center close">
            <a href="javascript:void(0)" class="close-button mb-2 d-flex align-items-center">
                <span class="pe-2">Fechar</span>
                <i class="fa-sharp fa-solid fa-xmark"></i>
            </a>
        </div>
        <?php get_search_form(); ?>

        <?php if ( is_active_sidebar( 'blog' ) ) : ?>
            <?php dynamic_sidebar( 'blog' ); ?>
        <?php endif; ?>   
    </div>
</aside>