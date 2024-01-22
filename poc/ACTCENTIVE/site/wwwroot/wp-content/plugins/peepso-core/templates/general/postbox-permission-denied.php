<?php if(strlen($reason = apply_filters('peepso_permissions_post_create_denied_reason', ''))) { ?>

    <div class='ps-alert ps-alert--warning'>
        <?php echo $reason;?>
    </div>

<?php }
