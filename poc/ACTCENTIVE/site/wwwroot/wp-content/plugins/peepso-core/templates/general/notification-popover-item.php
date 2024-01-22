<?php
$PeepSoProfile	= PeepSoProfile::get_instance();
$user_id = $PeepSoProfile->notification_user();
$PeepSoUser		= PeepSoUser::get_instance($user_id);

$notification_id = $PeepSoProfile->notification_id(FALSE);
$readstatus = $PeepSoProfile->notification_readstatus();

$className = 'ps-notification';
if ($readstatus === FALSE) {
	$className .= ' ps-notification--unread';
}
$className .= ' ps-js-notification ps-js-notification--' . $notification_id;

?>

<div class="ps-notification__wrapper">
	<div class="<?php echo $className; ?>" data-id="<?php echo $notification_id; ?>"
		<?php echo $readstatus === FALSE ? 'data-unread="1"' : '' ?>>
		<a class="ps-notification__link" href="<?php echo $PeepSoProfile->notification_link(false); ?>">
			<div class="ps-notification__avatar">
				<div class="ps-avatar ps-avatar--notification">
					<img src="<?php echo $PeepSoUser->get_avatar(); ?>" alt="<?php echo trim(strip_tags($PeepSoUser->get_fullname())); ?>">
				</div>
			</div>

			<div class="ps-notification__body">
				<div class="ps-notification__desc">
					<strong>
					<?php

					//[peepso]_[action]_[WHICH_PLUGIN]_[WHERE]_[WHAT]_[BEFORE/AFTER]
					do_action('peepso_action_render_user_name_before', $PeepSoUser->get_id());

					echo $PeepSoUser->get_firstname();

					//[peepso]_[action]_[WHICH_PLUGIN]_[WHERE]_[WHAT]_[BEFORE/AFTER]
					do_action('peepso_action_render_user_name_after', $PeepSoUser->get_id());

					?>
					</strong>
					<span>
						<?php $PeepSoProfile->notification_message(); ?><?php $PeepSoProfile->notification_link(); ?><br/>
	        	<?php $PeepSoProfile->notification_human_friendly();?>
					</span>
				</div>

				<div class="ps-notification__meta">
					<span
					class="activity-post-age"
					data-timestamp="<?php $PeepSoProfile->notification_timestamp(); ?>"><?php $PeepSoProfile->notification_age(); ?></span>

					<?php if (!$readstatus) { ?>
					<span class="ps-notification__status ps-tooltip ps-tooltip--notification ps-js-mark-as-read"
							data-tooltip="<?php echo __('Mark as read', 'peepso-core');?>"
							style="cursor:pointer;">
						<i class="gcis gci-eye"></i>
						<span><?php echo __('Mark as read', 'peepso-core');?></span>
					</span>
					<?php } ?>
				</div>
			</div>
		</a>
	</div>
</div>
