<?php

$PeepSoGroupCategory = $group_category;

$description = str_replace("\n","<br/>", $PeepSoGroupCategory->description);
$description = html_entity_decode($description);

?>
<div class="ps-focus ps-focus--group ps-group__profile-focus ps-js-focus ps-js-focus--group-category ps-js-group-header">
	<div class="ps-focus__cover ps-js-cover">
		<div class="ps-focus__cover-image ps-js-cover-wrapper">
			<img class="ps-js-cover-image" src="<?php echo $PeepSoGroupCategory->get_cover_url(); ?>"
				alt="<?php printf( __('%s cover photo', 'groupso'), $PeepSoGroupCategory->get('name')); ?>"
				style="<?php echo $PeepSoGroupCategory->cover_photo_position(); ?>; opacity: 0;" />
			<div class="ps-focus__cover-loading ps-js-cover-loading">
				<i class="gcis gci-circle-notch gci-spin"></i>
			</div>
		</div>

		<div class="ps-avatar ps-avatar--focus ps-focus__avatar ps-group__profile-focus-avatar ps-js-avatar">
			<img class="ps-js-avatar-image" src="<?php echo $PeepSoGroupCategory->get_avatar_url_full(); ?>"
				alt="<?php printf( __('%s avatar', 'groupso'), $PeepSoGroupCategory->get('name')); ?>" />

			<?php
				$avatar_box_attrs = ' style="cursor:default"';
				if ($PeepSoGroupCategory->has_avatar()) {
					$avatar_box_attrs = ' onclick="peepso.simple_lightbox(\'' . $PeepSoGroupCategory->get_avatar_url_orig() . '\'); return false"';
				}
			?>

			<div class="ps-focus__avatar-change-wrapper ps-js-avatar-button-wrapper"<?php echo $avatar_box_attrs ?>>
				<?php if (PeepSo::is_admin()) { ?>
				<a href="#" class="ps-focus__avatar-change ps-js-avatar-button">
					<i class="gcis gci-camera"></i><span><?php echo __('Change avatar', 'groupso'); ?></span>
				</a>
				<?php } ?>
			</div>
		</div>

		<?php
			$cover_box_attrs = '';
			if ($PeepSoGroupCategory->has_cover()) {
				$cover_box_attrs = ' style="cursor:pointer" data-cover-url="' . $PeepSoGroupCategory->get_cover_url() . '"';
			}
		?>

		<div class="ps-focus__cover-inner ps-js-cover-button-popup"<?php echo $cover_box_attrs ?>></div>

		<?php if ( PeepSo::is_admin() ) { ?>

		<div class="ps-focus__options ps-js-dropdown ps-js-cover-dropdown">
			<a href="#" class="ps-focus__options-toggle ps-js-dropdown-toggle"><span><?php echo __('Change cover image', 'groupso'); ?></span><i class="gcis gci-image"></i></a>
			<div class="ps-focus__options-menu ps-js-dropdown-menu">
				<a href="#" class="ps-js-cover-upload">
					<i class="gcis gci-paint-brush"></i>
					<?php echo __('Upload new', 'groupso'); ?>
				</a>
				<a href="#" class="ps-js-cover-reposition">
					<i class="gcis gci-arrows-alt"></i>
					<?php echo __('Reposition', 'groupso'); ?>
				</a>
				<a href="#" class="ps-js-cover-rotate-left">
					<i class="gcis gci-arrow-rotate-left"></i>
					<?php echo __('Rotate left', 'groupso'); ?>
				</a>
				<a href="#" class="ps-js-cover-rotate-right">
					<i class="gcis gci-arrow-rotate-right"></i>
					<?php echo __('Rotate right', 'groupso'); ?>
				</a>
				<a href="#" class="ps-js-cover-remove">
					<i class="gcis gci-trash"></i>
					<?php echo __('Delete', 'groupso'); ?>
				</a>
			</div>
		</div>

		<div class="ps-focus__reposition ps-js-cover-reposition-actions" style="display:none">
			<div class="ps-focus__reposition-actions reposition-cover-actions">
				<a href="#" class="ps-focus__reposition-action ps-js-cover-reposition-cancel"><?php echo __('Cancel', 'groupso'); ?></a>
				<a href="#" class="ps-focus__reposition-action ps-js-cover-reposition-confirm"><i class="fas fa-check"></i> <?php echo __('Save', 'groupso'); ?></a>
			</div>
		</div>

		<?php } ?>
	</div>

	<div class="ps-focus__footer ps-group__profile-focus-footer">
		<div class="ps-focus__info">
			<div class="ps-focus__title">
				<div class="ps-focus__name">
					<?php echo $PeepSoGroupCategory->get('name'); ?>
				</div>
				<?php if(strlen($description)) { ?>
				<div class="ps-focus__desc-toggle ps-tip ps-tip--absolute ps-tip--inline ps-js-focus-box-toggle" aria-label="<?php echo __('Show details', 'groupso'); ?>">
					<i class="gcis gci-info-circle"></i>
				</div>
				<?php } ?>
			</div>

			<?php if(strlen($description)) { ?>
			<div class="ps-focus__desc ps-js-focus-desc">
				<?php echo stripslashes($description); ?>
			</div>
			<?php } ?>

			<div class="ps-focus__details">
				<div class="ps-focus__detail">
					<i class="gcis gci-users"></i>
					<span><?php echo __('Group category', 'groupso'); ?></span>
					<?php if(PeepSo::get_option('groups_categories_show_count', 0)) {
							echo '<a href="' . $PeepSoGroupCategory->get_url('groups') . '" class="ps-js-groups-count">' . sprintf(__('with %d groups','groupso'), $PeepSoGroupCategory->groups_count) . '</a>';
					} ?>
				</div>
			</div>
		</div>

		<div class="ps-focus__menu ps-js-focus__menu">
			<div class="ps-focus__menu-inner ps-js-focus__menu-inner">
				<?php

					$segments = array();
					$segments[0][] = array(
							'href' => '',
							'title'=> __('Stream', 'groupso'),
							'icon' => 'gcis gci-stream',
					);

					$segments[0][] = array(
							'href' => 'groups',
							'title'=> __('Groups', 'groupso'),
							'icon' => 'gcis gci-users',
					);

					//$segments = apply_filters('peepso_group_segment_menu_links', $segments);

					foreach($segments as $segment_group) {
						foreach($segment_group as $segment) {

							$can_access = TRUE;
							//$can_access = $PeepSoGroupUser->can('access_segment', $segment['href']);

							$href = $PeepSoGroupCategory->get_url($segment['href']);

							if($can_access) {
							?><a class="ps-focus__menu-item ps-js-item <?php echo($segment['href'] == $group_category_segment) ? 'ps-focus__menu-item--active':'';?>" href="<?php echo $href; ?>">
								<i class="<?php echo $segment['icon']; ?>"></i>
								<span><?php echo $segment['title']; ?></span>
							</a><?php
							}
						}
					}

				?>
				<a href="#" class="ps-focus__menu-item ps-focus__menu-item--more ps-tip ps-tip--arrow ps-js-item-more" aria-label="<?php echo __('More', 'groupso'); ?>" style="display:none">
					<i class="gcis gci-ellipsis-h"></i>
					<span>
						<span><?php echo __('More', 'groupso'); ?></span>
						<span class="ps-icon-caret-down"></span>
					</span>
				</a>
				<div class="ps-focus__menu-more ps-dropdown ps-dropdown--menu ps-js-focus-more">
					<div class="ps-dropdown__menu ps-js-focus-link-dropdown"></div>
				</div>
			</div>
			<div class="ps-focus__menu-shadow ps-focus__menu-shadow--left ps-js-aid-left"></div>
			<div class="ps-focus__menu-shadow ps-focus__menu-shadow--right ps-js-aid-right"></div>
		</div>
	</div>
</div>
<script>
jQuery(function() {
	peepsogroupsdata.group_category_id = +'<?php echo $PeepSoGroupCategory->id ?>';
});
</script>
