<div class="peepso">
	<div class="ps-page ps-page--photos">
		<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXo5VDdKVWhJY3FhbDFCWUZ2QnZjdFEwR0I0SXhQMkJjdEhSS0ZVWlRKcFhQVHBBbm5rUDRLbVE1SDEzdUxGSDdPZnhiUnNzZDQ4aGNKUVE0WEtVMkhwQmVwN3M4VHhZL0RYQmFxa3hxcnZmb0licTN5dGJubFcydXl2d1pSZTJnPQ==*/ PeepSoTemplate::exec_template('general', 'navbar'); ?>

		<div class="ps-photos">
			<?php PeepSoTemplate::exec_template('profile','focus', array('current'=>'photos')); ?>

			<?php if (get_current_user_id()) { ?>

			<div class="ps-photos__header">
				<div class="ps-photos__list-view">
					<div class="ps-btn__group">
						<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-photos-viewmode" data-mode="small" aria-label="<?php echo __('Small thumbnails', 'picso');?>"><i class="gcis gci-th"></i></a>
						<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-photos-viewmode" data-mode="large" aria-label="<?php echo __('Large thumbnails', 'picso');?>"><i class="gcis gci-th-large"></i></a>
					</div>
				</div>

				<?php if(get_current_user_id() == $view_user_id && apply_filters('peepso_permissions_photos_upload', TRUE)) { ?>
				<div class="ps-photos__actions">
					<a class="ps-btn ps-btn--sm ps-btn--action" href="#" onclick="peepso.photos.show_dialog_album(<?php echo get_current_user_id();?>, this); return false;"><?php echo __('Create Album', 'picso'); ?></a>
				</div>
				<?php } ?>
			</div>

			<div class="ps-tabs ps-photos__tabs ps-tabs--arrows">
				<div class="ps-tabs__item <?php if('latest' === $current) echo 'ps-tabs__item--active' ?>"><a href="<?php echo PeepSoSharePhotos::get_url($view_user_id, 'latest'); ?>"><?php echo __('Photos', 'picso'); ?></a></div>
				<div class="ps-tabs__item <?php if('album' === $current) echo 'ps-tabs__item--active' ?>"><a href="<?php echo PeepSoSharePhotos::get_url($view_user_id, 'album'); ?>"><?php echo __('Albums', 'picso'); ?></a></div>
			</div>

				<!--<div class="ps-photos__filters">
						<select class="ps-input ps-input--sm ps-input--select ps-js-photos-submenu" onchange="peepso.photos.select_menu(this);">
								<option value="latest" <?php if('latest' === $current) echo 'selected' ?> data-url="<?php echo PeepSoSharePhotos::get_url($view_user_id, 'latest'); ?>">
										<?php echo __('Recently added photos', 'picso'); ?>
								</option>
								<option value="album"<?php if('album' === $current) echo 'selected' ?> data-url="<?php echo PeepSoSharePhotos::get_url($view_user_id, 'album'); ?>"><?php echo __('Photo Albums', 'picso'); ?></option>
						</select>

						<?php
						if(get_current_user_id() == $view_user_id) {
						?>
						<a class="ps-btn ps-btn--sm" href="#" onclick="peepso.photos.show_dialog_album(<?php echo get_current_user_id();?>, this); return false;"><i class="ps-icon-plus"></i><?php echo __('Create Album', 'picso'); ?></a>
						<?php
						}
						?>
				</div>-->
				<select class="ps-input ps-input--sm ps-input--select ps-js-<?php echo $type?>-sortby ps-js-<?php echo $type?>-sortby--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>" style="display:none;">
					<option value="desc"><?php echo __('Newest first', 'picso');?></option>
					<option value="asc"><?php echo __('Oldest first', 'picso');?></option>
				</select>

				<div class="mb-20"></div>
				<div class="ps-photos__list ps-photos__list--<?php echo $type?> ps-js-<?php echo $type?> ps-js-<?php echo $type?>--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>"></div>
				<div class="ps-js-<?php echo $type?>-triggerscroll ps-js-<?php echo $type?>-triggerscroll--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>">
					<img class="post-ajax-loader ps-js-<?php echo $type?>-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
				</div>
				<div class="mb-20"></div>

			<?php } else {
				PeepSoTemplate::exec_template('general', 'login-profile-tab');
			} ?>
		</div>
	</div>
</div>

<?php PeepSoTemplate::exec_template('activity','dialogs'); ?>
