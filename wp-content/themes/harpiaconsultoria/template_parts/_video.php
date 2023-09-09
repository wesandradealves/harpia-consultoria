<div class="col-12 col-lg-7 <?php if($args['template'] === 'about') : ?> pe-lg-5 <?php else : ?> ps-lg-5 <?php endif; ?> mt-5 mt-lg-0">
    <a class="yu2fvl" href="https://www.youtube.com/watch?v=<?php echo $args['video']; ?>">
        <img height="69" width="69" loading="lazy" class="play d-block" src="<?php echo get_template_directory_uri(); ?>/img/play-btn.png" alt="Play" />
        <img height="720" width="1280" class="img-fluid d-block" loading="lazy" src="https://i3.ytimg.com/vi/<?php echo $args['video']; ?>/maxresdefault.jpg" alt="" />
    </a>
</div>  