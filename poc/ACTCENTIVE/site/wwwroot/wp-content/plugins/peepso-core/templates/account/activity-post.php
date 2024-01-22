<?php
$PeepSoActivity = PeepSoActivity::get_instance();
$PeepSoUser= PeepSoUser::get_instance($post_author);
$PeepSoPrivacy	= PeepSoPrivacy::get_instance();

?>
<p>
    <div class="meta"><?php echo $post_date; ?></div>
    <?php 
    ob_start();
    $PeepSoActivity->post_action_title();
    $action_title = ob_get_clean();
    echo str_replace('<i class="ps-icon-caret-right"></i>', ' > ', $action_title)
    ?>
    <div class="comment">
    	<?php $PeepSoActivity->content(); ?>
    	<?php //$PeepSoActivity->post_attachment(); ?>
    	<br>
    	<?php if ($likes = $PeepSoActivity->has_likes($act_id)) { ?>
    	<?php $PeepSoActivity->show_like_count($likes); ?>		
    	<?php } ?>
    </div>
  </p>