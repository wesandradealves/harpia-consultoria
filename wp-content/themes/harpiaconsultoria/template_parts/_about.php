<section class="section quem-somos">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-center <?php echo $args['classes']; ?>">
            <?php 
                get_template_part('template_parts/_section-header', null, array( 
                    'classes' => 'col-12 col-md-6', 
                    'title' => $args['title'], 
                    'subtitle' => $args['subtitle'],
                    'text' => $args['text'], 
                    'cta' => $args['cta'],
                    'template' => $args['template']
                )); 
            ?>       
            <div class="col-12 col-md-6 media">
                <?php if($args['thumbnail'] && !$args['video']) : ?>
                    <img class="img-fluid thumbnail" src="<?php echo $args['thumbnail']; ?>" loading="lazy" alt="<?php echo $args['title']; ?>" />
                <?php else : ?>
                    <a class="yu2fvl" href="https://www.youtube.com/watch?v=<?php echo $args['video']; ?>">
                        <img height="69" width="69" loading="lazy" class="play d-block" src="<?php echo get_template_directory_uri(); ?>/img/play-btn.png" alt="Play" />
                        <img height="720" width="1280" class="img-fluid d-block" loading="lazy" src="https://i3.ytimg.com/vi/<?php echo $args['video']; ?>/maxresdefault.jpg" alt="" />
                    </a>    
                <?php endif; ?>
            </div> 
        </div>
    </div>
</section>