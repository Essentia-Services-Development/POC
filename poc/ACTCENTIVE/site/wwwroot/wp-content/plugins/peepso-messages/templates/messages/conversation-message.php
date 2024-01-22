<?php
$PeepSoActivity = PeepSoActivity::get_instance();
$PeepSoUser		= PeepSoUser::get_instance($post_author);
$PeepSoMessages = PeepSoMessages::get_instance();
$content_extra  = apply_filters('peepso_post_extras', array());
?>

<div class="ps-chat__message ps-js-message ps-js-message-<?php echo $ID ?> <?php echo $post_author == get_current_user_id() ? 'ps-chat__message--me' : '' ?>" data-id="<?php echo $ID ?>">
	<a class="ps-chat__message-avatar ps-avatar ps-tip ps-tip--arrow ps-tip--left" href="<?php echo $PeepSoUser->get_profileurl(); ?>" aria-label="<?php echo $PeepSoUser->get_fullname(); ?>">
		<img src="<?php echo $PeepSoUser->get_avatar(); ?>" alt="">
	</a>
	<div class="ps-chat__message-body">
		<div class="ps-chat__message-user">
			<a href="<?php echo $PeepSoUser->get_profileurl(); ?>"><?php

			//[peepso]_[action]_[WHICH_PLUGIN]_[WHERE]_[WHAT]_[BEFORE/AFTER]
			do_action('peepso_action_render_user_name_before', $PeepSoUser->get_id());

			echo $PeepSoUser->get_fullname();

			//[peepso]_[action]_[WHICH_PLUGIN]_[WHERE]_[WHAT]_[BEFORE/AFTER]
			do_action('peepso_action_render_user_name_after', $PeepSoUser->get_id());

			?></a>
		</div>

		<div class="ps-chat__message-content-wrapper ps-js-conversation-content">
			<div class="ps-chat__message-content"><?php

					if (is_array($content_extra) && !empty($content_extra)) {
						echo '<span>' . implode(' ', $content_extra) . '</span>';
					}

				?><?php $PeepSoActivity->content(); ?></div>
		</div>

		<div class="ps-chat__message-attachments"><?php $PeepSoActivity->post_attachment(); ?></div>

		<div class="ps-chat__message-time">
			<a class="ps-chat__message-delete" aria-label="<?php echo __('Delete', 'msgso'); ?>" href="<?php echo $PeepSoMessages->get_delete_message_url();?>" onclick="return ps_messages.delete_single_message('<?php echo $ID ?>');">
				<i class="gcis gci-trash-alt"></i><?php echo __('Delete', 'msgso'); ?>
			</a>
			<?php if (( 1 === intval(PeepSo::get_option('messages_read_notification', 1)) ) && ( $post_author == get_current_user_id() )) : ?>
				<i class="gcir gci-check-circle"></i>
			<?php endif; ?>
			<span><?php $PeepSoActivity->post_age(); ?></span>
		</div>
	</div>
</div>
