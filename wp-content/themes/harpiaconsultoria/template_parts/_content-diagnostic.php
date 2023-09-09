<section class="diagnostics section">
    <div class="d-flex flex-column <?php if(get_post_type() !== 'servicos') : ?> flex-lg-row <?php else : ?> pb-0 <?php endif; ?> align-items-stretch  container pt-0">
        <div <?php if($args['data']) : ?> class="col-12 <?php if(get_post_type() !== 'servicos') : ?> col-lg-5 pe-lg-5 <?php endif; ?>" <?php endif; ?>>
            <?php if($args['title']) : ?>
                <h2 class="title mb-4"><?php echo $args['title']; ?></h2>
            <?php endif; ?>
            <?php if($args['text']) : ?>
            <div class="text">
                <?php echo $args['text']; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php if($args['data'] && get_post_type() !== 'servicos') : ?>
            <ul class="diagnostics-list flex-fill media ps-lg-5 mt-5 mt-lg-0">
                <?php 
                    foreach ($args['data'] as $item) {
                        ?>
                        <li class="diagnostics-list-item mb-1 p-3 p-md-5">
                            <div class="text d-flex align-items-start">
                                <i class="fa-solid fa-angle-right"></i>
                                <div>
                                    <?php echo $item['text']; ?>
                                </div>
                            </div>
                        </li>                                
                        <?php 
                    }
                ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php if($args['data']  && get_post_type() === 'servicos') : ?>
        <div class="diagnostics-list mCustomScrollbar d-flex mb-5 pt-5">
            <?php 
                foreach ($args['data'] as $item) {
                    ?>
                    <div class="diagnostics-list-item ">
                        <div class="text d-flex align-items-start pe-lg-5">
                            <i class="fa-solid fa-angle-right"></i>
                            <div class="text-inner mCustomScrollbar">
                                <?php echo $item['text']; ?>
                            </div>
                        </div>
                    </div>                                
                    <?php 
                }
            ?>
        </div>
    <?php endif; ?>
</section>