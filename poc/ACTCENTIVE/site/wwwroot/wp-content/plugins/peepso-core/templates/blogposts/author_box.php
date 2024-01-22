<div class="ps-blogposts__authorbox">
    <div class="ps-avatar">
        <a href="<?php echo $author->get_profileurl();?>">
            <img alt="<?php echo sprintf(__('%s - avatar', 'peepso-core'), $author->get_fullname());?>" title="<?php echo $author->get_profileurl();?>" src="<?php echo $author->get_avatar();?>">
        </a>
    </div>
    <?php

    if(strlen($author_name_pre_text = PeepSo::get_option('blogposts_authorbox_author_name_pre_text',''))) {
        echo '<span>' . $author_name_pre_text . '</span>';
    }

    ob_start();
    do_action('peepso_action_render_user_name_before', $author->get_id());
    $before_fullname = ob_get_clean();

    ob_start();
    do_action('peepso_action_render_user_name_after', $author->get_id());
    $after_fullname = ob_get_clean();

    ?>

    <?php echo $before_fullname; ?>
    <a href="<?php echo $author->get_profileurl();?>" data-hover-card="<?php echo $author->get_id() ?>">
        <?php echo $author->get_fullname(); ?>
    </a>
    <?php echo $after_fullname; ?>

    <div class="ps-blogposts__authorbox-desc">
        <?php

        $PeepSoFieldAbout = PeepSoField::get_field_by_id('description', $author->get_id());
        if($PeepSoFieldAbout instanceof PeepSoField && $PeepSoFieldAbout->published) {
            $PeepSoFieldAbout->render();
        }

        ?>
    </div>
</div>
