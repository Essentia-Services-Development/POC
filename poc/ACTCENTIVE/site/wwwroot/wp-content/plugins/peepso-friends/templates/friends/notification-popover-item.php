<?php
$PeepSoUser	= PeepSoUser::get_instance($freq_user_id);
$user_id = $PeepSoUser->get_id();
?>
<div class="ps-notification ps-notification--friend ps-js-notification" data-user-id="<?php echo $user_id; ?>" data-request-id="<?php echo $freq_id; ?>">
	<div class="ps-notification__link">
		<div class="ps-notification__avatar">
			<a class="ps-avatar ps-avatar--notification" href="<?php echo $PeepSoUser->get_profileurl(); ?>">
				<img src="<?php echo $PeepSoUser->get_avatar(); ?>" alt="<?php echo trim(strip_tags($PeepSoUser->get_fullname())); ?>">
			</a>
		</div>
		<div class="ps-notification__body">
			<div class="ps-notification__desc">
				<a href="<?php echo $PeepSoUser->get_profileurl(); ?>">
					<strong>
					<?php

					do_action('peepso_action_render_user_name_before', $user_id);

					echo $PeepSoUser->get_fullname();

					do_action('peepso_action_render_user_name_after', $user_id);

					?>
					</strong>
				</a>
			</div>
			<!-- Add mutual friends
			<div class="ps-notification__meta">
				X mutual friends
			</div>-->
		</div>
		<div class="ps-notification__actions ps-js-actions">
			<button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-friend-reject-request"
				title="<?php echo __('Ignore', 'friendso'); ?>"
				data-user-id="<?php echo $user_id; ?>" data-request-id="<?php echo $freq_id; ?>">
				<i class="gcis gci-times"></i>
			</button>

			<button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-friend-accept-request"
				title="<?php echo __('Approve', 'friendso'); ?>"
				data-user-id="<?php echo $user_id; ?>" data-request-id="<?php echo $freq_id; ?>">
				<i class="gcis gci-check"></i>
			</button>
		</div>
	</div>
</div>
