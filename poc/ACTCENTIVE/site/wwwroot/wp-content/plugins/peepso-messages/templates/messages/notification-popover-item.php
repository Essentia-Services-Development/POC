<?php
$PeepSoMessages = PeepSoMessages::get_instance();
$PeepSoUser = PeepSoUser::get_instance($post_author);
?>
<div class="ps-notification ps-notification--message ps-js-notification-message <?php echo ($mrec_viewed) ? '' : 'ps-notification--unread'; ?>" data-id="<?php echo $PeepSoMessages->get_root_conversation();?>" data-url="<?php echo $PeepSoMessages->get_message_url(); ?>">
	<div class="ps-notification__link">
		<div class="ps-notification__avatar">
			<div class="ps-avatar ps-avatar--notification">
				<?php echo $PeepSoMessages->get_message_avatar(array('post_author' => $post_author, 'post_id' => $ID)); ?>
			</div>
		</div>
		<div class="ps-notification__body">
			<div class="ps-notification__desc ps-js-conversation-excerpt">
				<strong>
					<?php
					$args = array(
						'post_author' => $post_author, 'post_id' => $ID
					);
					$PeepSoMessages->get_recipient_name($args);
					?>
				</strong>
				<?php
					$PeepSoMessages->get_last_author_name($args);
					echo $PeepSoMessages->get_conversation_title(); ?>
			</div>
			<div class="ps-notification__meta activity-post-age" data-timestamp="<?php echo strtotime($post_date); ?>">
				<?php #echo human_time_diff(strtotime($post_date), current_time('timestamp')) . ' ago'; ?>
        <?php echo sprintf(__('%s ago', 'peepso-core'), human_time_diff(strtotime($post_date), current_time('timestamp')));?>
			</div>
		</div>
	</div>
</div>
