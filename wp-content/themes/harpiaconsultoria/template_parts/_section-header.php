<div class="section-header <?php echo $args['classes']; ?>">
    <div class="container">
        <?php if($args['title']) : ?>
            <<?php if($args['template'] === 'page-header') : ?>h1<?php else : ?>h2<?php endif; ?> class="d-block">
                <?php if($args['subtitle']) : ?>
                    <small class="d-block subtitle mb-4"><?php echo $args['subtitle']; ?></small>
                <?php endif; ?>
                <span class="<?php if($args['read-more']) : ?> d-flex align-items-end justify-content-between flex-wrap <?php else: ?> d-block <?php endif; ?>">
                    <span class="title d-block <?php if($args['read-more']) : ?> order-2 order-md-1 mt-4 mt-md-0 col-12 col-md-6<?php endif; ?>">
                        <?php echo $args['title']; ?>
                    </span>
                    <?php if($args['read-more']) : ?>
                        <a class="read-more order-1 order-md-2 d-block col-12 col-md-auto" href="<?php echo $args['read-more']['link']; ?>"><?php echo $args['read-more']['label']; ?> <i class="fa-solid fa-angle-right"></i></a>
                    <?php endif; ?>
                </span>  
            </<?php if($args['template'] === 'page-header') : ?>h1<?php else : ?>h2<?php endif; ?>>
        <?php endif; ?>

        <?php if($args['text']) : ?>
            <div class="text mt-4 d-block <?php if($args['template'] === 'page-header' || $args['template'] === 'taxonomy-panel') : ?>col-lg-6<?php endif; ?>">
                <?php echo $args['text']; ?>
            </div>      
        <?php endif; ?>

        <?php if($args['cta']) : ?>
            <a title="<?php echo $args['cta']['label']; ?>" href="<?php echo $args['cta']['link']; ?>" class="btn primary mt-5 d-inline-block me-auto"><?php echo $args['cta']['label']; ?></a>
        <?php endif; ?>                  
    </div>
</div>