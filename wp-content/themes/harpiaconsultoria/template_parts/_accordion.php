<?php if($args['data']) : ?>
    <?php if( have_rows('accordion_accordion') ): ?>
        <ul class="accordion-list <?php echo $args['classes']; ?>">
            <?php 
                $i = 0;
                foreach ($args['data'] as $item) {
                    $i++;
                    ?>
                    <li class="accordion-list-item <?php if($i < count($args['data'])): ?> mb-5 <?php endif; ?> pb-5">
                        <h3 class="title d-flex align-items-center justify-content-between"> <?php echo $item['title']; ?> <i class="fa-solid fa-plus"></i></h3>
                        <div class="text d-none mt-4">
                            <?php echo $item['text']; ?>
                        </div>
                    </li>
                    <?php 
                }
            ?>
        </ul>
    <?php endif; ?>  
<?php endif; ?>