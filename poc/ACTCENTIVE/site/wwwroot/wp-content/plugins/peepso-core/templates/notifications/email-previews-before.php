<?php if(strlen($title)) { ?>
    <p style="clear:both;margin-top: 15px;margin-bottom:35px;">
    <h2 style="margin-bottom:0;"><?php echo $title;?></h2>
    <?php
    $limit_per_section = (int) PeepSo::get_option('notification_digest_limit_per_section', 5);

    if($count_section > $limit_per_section) {?>

        <small>
            <?php echo sprintf( __("Showing %d of %d", 'peepso-core'),$limit_per_section, $count_section);?>
        </small>

    <?php } ?>
    </p>
<?php } ?>
