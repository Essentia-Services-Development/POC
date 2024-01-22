<?php

$PeepSoURLSegments = PeepSoUrlSegments::get_instance();

if($PeepSoURLSegments->get(1) == 'hashtag') {
    $hashtag = $PeepSoURLSegments->get(2);
    $activity_page = PeepSo::get_page('activity');
    ?>
    <div class="ps-stream-breadcrumbs">
        <a class="ps-stream-breadcrumbs__home" href="<?php echo $activity_page;?>">
            <i class="gcis gci-home"></i>
        </a>
        <span class="ps-stream-breadcrumbs__arrow">
            <i class="gcis gci-chevron-right"></i>
        </span>
        <a class="ps-stream-breadcrumbs__item" href="<?php echo $activity_page;?>?hashtag/<?php echo $hashtag;?>">
            #<?php echo $hashtag;?>
        </a>
    </div>
    <?php
}