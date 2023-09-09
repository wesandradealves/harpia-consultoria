<div class="post-card post-<?php echo $post->ID; ?> <?php echo $args['classes']; ?>">
    <div onclick="location.href = '<?php echo get_the_permalink(); ?>';" class="card-inner pb-4">
        <img height="270" width="400" class="img-fluid thumbnail" loading="lazy" src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php echo get_the_title(); ?>" />
        <p class="text">
            <span class="date"><?php the_time('M d') ?></span>, por <?php echo get_the_author(); ?>
        </p>
        <h3 class="title"><?php echo get_the_title(); ?></h3>
    </div>
</div>