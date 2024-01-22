<div class="peepso">
	<div class="ps-page ps-page--followers">
		<?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div class="ps-followers">
			<?php PeepSoTemplate::exec_template('profile','focus', array('current'=>'followers')); ?>

			<?php
				if (get_current_user_id()) {
					PeepSoTemplate::exec_template('followers', 'submenu', array('current' => $current, 'view_user_id' => $view_user_id));

					?>
					<div class="mb-20"></div>
					<div class="ps-members ps-followers__list ps-js-followers ps-js-followers--<?php echo apply_filters('peepso_user_profile_id', 0); ?>"></div>
					<div class="ps-members__loading ps-js-followers-triggerscroll ps-js-followers-triggerscroll--<?php echo apply_filters('peepso_user_profile_id', 0); ?>">
						<img class="ps-loading post-ajax-loader ps-js-followers-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
					</div>
			<?php } else {
					PeepSoTemplate::exec_template('general','login-profile-tab');
			} ?>
		</div>
	</div>
</div>
<?php PeepSoTemplate::exec_template('activity','dialogs'); ?>
