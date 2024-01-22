<div class="peepso">
	<div class="ps-page ps-page--blogposts">
		<?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div class="ps-blogposts">
			<?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'blogposts')); ?>

			<?php
			$submissions = FALSE;

			if(class_exists( 'CMUserSubmittedPosts' ) && PeepSo::get_option('blogposts_submissions_enable')) { $submissions = TRUE; }
			if(PeepSo::usp_enabled() && PeepSo::get_option('blogposts_submissions_enable_usp'))                { $submissions = TRUE; }

			if($submissions) {
					PeepSoTemplate::exec_template('blogposts', 'blogposts_tabs', array('create_tab'=>FALSE));
			}
			?>

			<div class="ps-blogposts__filters">
				<select class="ps-input ps-input--sm ps-input--select ps-js-blogposts-sortby ps-js-blogposts-sortby--<?php echo apply_filters('peepso_user_profile_id', 0); ?>">
					<option value="desc"><?php echo __('Newest first', 'peepso-core');?></option>
					<option value="asc"><?php echo __('Oldest first', 'peepso-core');?></option>
				</select>
			</div>

			<div class="mb-20"></div>
			<div class="ps-blogposts__list <?php echo PeepSo::get_option('blogposts_profile_two_column_enable', 0) ? 'ps-blogposts__list--grid': '' ?>
					ps-js-blogposts ps-js-blogposts--<?php echo apply_filters('peepso_user_profile_id', 0); ?>"></div>
			<div class="ps-blogposts__loading ps-js-blogposts-triggerscroll">
				<img class="ps-loading post-ajax-loader ps-js-blogposts-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
			</div>
		</div>
	</div>
</div>
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
