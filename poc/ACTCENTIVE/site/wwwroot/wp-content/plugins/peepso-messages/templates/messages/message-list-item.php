<?php

$PeepSoActivity = PeepSoActivity::get_instance();
$PeepSoMessages = PeepSoMessages::get_instance();
$PeepSoUser = PeepSoUser::get_instance($post_author);

?><div class="ps-messages__list-item <?php echo ($mrec_viewed) ? '' : 'ps-messages__list-item--unread'; ?> ps-js-messages-list-item"
	data-id="<?php echo $ID ?>"
	data-conversation-id="<?php echo $mrec_parent_id ?>"
	data-conversation-url="<?php echo $PeepSoMessages->get_message_url(); ?>">

	<a class="ps-avatar ps-avatar--md ps-messages__list-item-avatar">
		<?php echo $PeepSoMessages->get_message_avatar(array('post_author' => $post_author, 'post_id' => $ID)); ?>
	</a>

	<div class="ps-messages__list-item-details">
		<div class="ps-messages__list-item-author"><?php
			$args = array('post_author' => $post_author, 'post_id' => $ID);
			$PeepSoMessages->get_recipient_name($args);
		?></div>

		<div class="ps-messages__list-item-excerpt ps-js-conversation-excerpt"><?php
			$PeepSoMessages->get_last_author_name($args);
			echo $PeepSoMessages->get_conversation_title(); ?>
		</div>

		<div class="ps-messages__list-item-meta">
			<?php $PeepSoActivity->post_age(); ?>
		</div>
	</div>
</div>
