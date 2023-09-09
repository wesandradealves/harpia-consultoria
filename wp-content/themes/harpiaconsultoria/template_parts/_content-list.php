<section class="content-list section">
    <div class="container <?php echo $args['classes']; ?>">
        <ul class="content-list-items">
            <?php 
                foreach ($args['data'] as $item) {
                    ?>
                        <li class="content-list-items-item d-flex flex-column flex-lg-row pb-5 mb-5">
                            <div class="col-lg-4">
                                <h3 class="title">
                                    <span class="d-block">
                                        <?php echo $item['title']; ?>
                                    </span>
                                    <small class="d-block subtitle mt-3"><?php echo $item['subtitle']; ?></small>
                                </h3>
                            </div>
                            <div class="flex-fill ps-lg-5 mt-5 mt-lg-0">
                                <div class="text">
                                    <?php echo $item['text']; ?>
                                </div>
                            </div>
                        </li>
                    <?php 
                }
            ?>
        </ul>
    </div>
</section>