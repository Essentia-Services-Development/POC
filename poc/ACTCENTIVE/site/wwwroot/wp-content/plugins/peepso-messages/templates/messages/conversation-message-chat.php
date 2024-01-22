<?php
$PeepSoActivity = PeepSoActivity::get_instance();
$PeepSoUser		= PeepSoUser::get_instance($post_author);
?>
<div class="ps-chat__message ps-conversation-item ps-js-message ps-js-message-<?php echo $ID ?> <?php echo $post_author == get_current_user_id() ? 'ps-chat__message--me my-message' : '' ?>" data-id="<?php echo $ID ?>">
  <a class="ps-chat__message-avatar ps-avatar ps-tip ps-tip--arrow ps-tip--left" href="<?php echo $PeepSoUser->get_profileurl(); ?>" aria-label="<?php echo $PeepSoUser->get_fullname(); ?>">
    <img src="<?php echo $PeepSoUser->get_avatar(); ?>" alt="<?php echo $PeepSoUser->get_fullname(); ?>">
  </a>
  <div class="ps-chat__message-body ps-conversation-body">
    <div class="ps-chat__message-user ps-conversation-user">
      <a href="<?php echo $PeepSoUser->get_profileurl(); ?>"><?php
        //[peepso]_[action]_[WHICH_PLUGIN]_[WHERE]_[WHAT]_[BEFORE/AFTER]
        do_action('peepso_action_render_user_name_before', $PeepSoUser->get_id());

        echo $PeepSoUser->get_fullname();

        //[peepso]_[action]_[WHICH_PLUGIN]_[WHERE]_[WHAT]_[BEFORE/AFTER]
        do_action('peepso_action_render_user_name_after', $PeepSoUser->get_id());
        ?>
      </a>
    </div>

    <?php
    $content_extra = apply_filters('peepso_post_extras', array());
    ?>

    <div class="ps-chat__message-content ps-js-conversation-content"><?php if(count($content_extra)) { echo '<span class="ps-chat__message-extra">'.implode('<br/>', $content_extra)."</span> ";}?><?php $PeepSoActivity->content(); ?></div>
    <div class="ps-chat__message-attachments ps-conversation-attachment"><?php $PeepSoActivity->post_attachment(); ?></div>

    <div class="ps-chat__message-time ps-conversation-time">
      <?php if (( 1 === intval(PeepSo::get_option('messages_read_notification', 1)) ) && ( $post_author == get_current_user_id() )) { ?>
        <i class="gcir gci-check-circle"></i>
      <?php } ?>
      <span><?php $PeepSoActivity->post_age(); ?></span>
    </div>
  </div>
</div>
