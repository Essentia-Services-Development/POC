<div class="peepso">
	<div class="ps-page ps-page--friends">
		<?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div class="ps-friends">
			<?php PeepSoTemplate::exec_template('profile','focus', array('current'=>'friends')); ?>

			<?php
				if(get_current_user_id()) {
					if ($view_user_id == get_current_user_id()) {
						PeepSoTemplate::exec_template('friends', 'submenu', array('current'=>'friends'));
					}

					?>
					<div class="mb-20"></div>
					<div class="ps-members ps-friends__list ps-js-friends ps-js-friends--<?php echo apply_filters('peepso_user_profile_id', 0); ?>"></div>
					<div class="ps-members__loading ps-js-friends-triggerscroll ps-js-friends-triggerscroll--<?php echo apply_filters('peepso_user_profile_id', 0); ?>">
						<img class="ps-loading post-ajax-loader ps-js-friends-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
					</div>
			<?php } else {
					PeepSoTemplate::exec_template('general','login-profile-tab');
			} ?>
		</div>
	</div>
</div>
<?php PeepSoTemplate::exec_template('activity','dialogs'); ?>
