<?php
	$PeepSoGroupUser= new PeepSoGroupUser($group->id);
	#$PeepSoGroup = new PeepSoGroup($group->id);
	$PeepSoGroup = $group;
	$coverUrl = $PeepSoGroup->get_cover_url();
	$has_cover = false;

	if (FALSE !== stripos($coverUrl, 'peepso/groups/'))
		$has_cover = true;

	if (FALSE === $PeepSoGroupUser->can('manage_group') || (FALSE === $has_cover)) {
		$reposition_style = 'display:none;';
		$cover_class = 'default';
	} else {
		$reposition_style = '';
		$cover_class = 'has-cover';
	}

	$description = str_replace("\n","<br/>", $group->description);
	$description = html_entity_decode($description);

	$group_categories = PeepSoGroupCategoriesGroups::get_categories_for_group($group->id);
	$group_categories_html = array();

?>
<div class="ps-focus ps-focus--group ps-group__profile-focus ps-js-focus ps-js-focus--group ps-js-group-header">
	<div class="ps-focus__cover ps-js-cover">
		<div class="ps-focus__cover-image ps-js-cover-wrapper">
			<img class="ps-js-cover-image" src="<?php echo $PeepSoGroup->get_cover_url(); ?>"
				alt="<?php printf( __('%s cover photo', 'groupso'), $PeepSoGroup->get('name')); ?>"
				style="<?php echo $PeepSoGroup->cover_photo_position(); ?>; opacity: 0;" />
			<div class="ps-focus__cover-loading ps-js-cover-loading">
				<i class="gcis gci-circle-notch gci-spin"></i>
			</div>
		</div>

		<div class="ps-avatar ps-avatar--focus ps-focus__avatar ps-group__profile-focus-avatar ps-js-avatar">
			<img class="ps-js-avatar-image" src="<?php echo $PeepSoGroup->get_avatar_url_full(); ?>"
				alt="<?php printf( __('%s avatar', 'groupso'), $PeepSoGroup->get('name')); ?>" />

			<?php
				$avatar_box_attrs = ' style="cursor:default"';
				if ($PeepSoGroup->has_avatar()) {
					$avatar_box_attrs = ' onclick="peepso.simple_lightbox(\'' . $PeepSoGroup->get_avatar_url_orig() . '\'); return false"';
				}
			?>

			<div class="ps-focus__avatar-change-wrapper ps-js-avatar-button-wrapper"<?php echo $avatar_box_attrs ?>>
				<?php if ($PeepSoGroupUser->can('manage_group')) { ?>
				<a href="#" class="ps-focus__avatar-change ps-js-avatar-button">
					<i class="gcis gci-camera"></i><span><?php echo __('Change avatar', 'groupso'); ?></span>
				</a>
				<?php } ?>
			</div>
		</div>

		<?php
			$cover_box_attrs = '';
			if ($PeepSoGroup->has_cover()) {
				$cover_box_attrs = ' style="cursor:pointer" data-cover-url="' . $PeepSoGroup->get_cover_url() . '"';
			}
		?>

		<div class="ps-focus__cover-inner ps-js-cover-button-popup"<?php echo $cover_box_attrs ?>>
			<div class="ps-focus__cover-actions ps-js-group-header-actions ps-js-loading">
				<button class="ps-focus__cover-action">
					<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
				</button>
			</div>
		</div>

		<?php if ($PeepSoGroupUser->can('manage_group')) { ?>

		<div class="ps-focus__options ps-js-dropdown ps-js-cover-dropdown">
			<a href="#" class="ps-focus__options-toggle ps-js-dropdown-toggle"><span><?php echo __('Change cover image', 'groupso'); ?></span><i class="gcis gci-image"></i></a>
			<div class="ps-focus__options-menu ps-js-dropdown-menu">
				<a href="#" class="ps-js-cover-upload">
					<i class="gcis gci-paint-brush"></i>
					<?php echo __('Upload', 'groupso'); ?>
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
					<?php echo $group->name; ?>
				</div>
				<div class="ps-focus__desc-toggle ps-tip ps-tip--absolute ps-tip--inline ps-tip--bottom ps-js-focus-box-toggle" aria-label="<?php echo __('Show details', 'groupso'); ?>">
					<i class="gcis gci-info-circle"></i>
				</div>
			</div>

			<div class="ps-focus__desc ps-js-focus-desc">
				<!-- Description -->
				<?php

					$description = stripslashes($description);
					if (PeepSo::get_option_new('md_groups_about', 0)) {
						$description = PeepSo::do_parsedown($description);
					}

					echo $description;

				?>

				<!-- Categories -->
				<?php if(PeepSo::get_option('groups_categories_enabled', FALSE)) { ?>
				<div class="ps-focus__desc-details">
					<?php if(count($group_categories) > 1) { ?><i class="gcis gci-tags"></i> <?php echo __('Group categories', 'groupso'); ?>:<?php } else { ?><i class="gcis gci-tag"></i> <?php echo __('Group category', 'groupso'); ?>:<?php } ?>
					<?php

						foreach ($group_categories as $PeepSoGroupCategory) {
							echo "<a href=\"{$PeepSoGroupCategory->get_url()}\">{$PeepSoGroupCategory->name}</a>";
						}

					?>
				</div>
				<?php } ?>
			</div>

			<div class="ps-focus__details">
				<!-- DETAILS -->

				<!-- Privacy -->
				<div class="ps-focus__detail">
					<?php if($PeepSoGroupUser->can('manage_group') && strlen($group_segment) && 'settings' == $group_segment) { ?>
						<div class="ps-group__profile-privacy ps-dropdown ps-dropdown--privacy ps-js-dropdown ps-js-privacy ps-js-privacy--<?php echo $group->id; ?>">
							<a href="javascript:" data-value="" class="ps-btn ps-btn--sm ps-btn--dropdown ps-dropdown__toggle ps-js-dropdown-toggle">
								<span class="dropdown-value">
									<i class="<?php echo $group->privacy['icon']; ?>"></i><span><?php echo $group->privacy['name']; ?></span>
								</span>
								<img class="ps-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
								<div class="ps-btn__icon"><span class="gcis gci-chevron-down"></span></div>
							</a>

							<?php echo PeepSoGroupPrivacy::render_dropdown(); ?>
						</div>
					<?php } else { ?>
						<span class="ps-btn ps-btn--sm ps-btn--app ps-tip ps-tip--bottom ps-tip--md ps-tip--arrow ps-tip--left" aria-label="<?php echo $group->privacy['desc'];?>">
							<i class="<?php echo $group->privacy['icon'];?>"></i><?php echo sprintf(__('%s Group','groupso'), $group->privacy['name']);?>
						</span>
					<?php } ?>
				</div>

				<!-- Members -->
				<a class="ps-focus__detail" href="<?php echo $group->get_url() . 'members/'; ?>">
					<i class="gcis gci-user-friends"></i>
					<span class="ps-js-member-count"><?php printf( _n( '%s member', '%s members', $group->members_count, 'groupso' ), number_format_i18n( $group->members_count ) ); ?></span>
				</a>

				<!-- Pending members -->
				<?php if($group->pending_admin_members_count > 0 && $PeepSoGroupUser->can('manage_users')) { ?>
				<a class="ps-focus__detail ps-js-pending-label" href="<?php echo $group->get_url() . 'members/pending'; ?>">
					<i class="gcis gci-user-clock"></i>
					<?php echo sprintf(__('<span class="ps-js-pending-count" data-id="%d">%s</span> pending', 'groupso'), $group->id, $group->pending_admin_members_count); ?>
				</a>
				<?php } ?>
			</div>
			<div class="ps-focus__mobile-actions ps-js-group-header-actions ps-js-loading">
				<button class="ps-focus__cover-action">
					<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
				</button>
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

				if($PeepSoGroupUser->can('manage_group')) {
					$segments[0][] = array(
						'href' => 'settings',
						'title' => __('Settings', 'groupso'),
						'icon' => 'gcis gci-cog',
					);
				}

				$title = __('Members', 'groupso');

				if($PeepSoGroupUser->can('manage_users') && $pending = $group->pending_admin_members_count) {
						$title .= ' <span class="ps-js-pending-label">(' . sprintf(__('<span class="ps-js-pending-count" data-id="%d">%s</span> pending', 'groupso'), $group->id, $pending) . ')</span>';
				}

				if($PeepSoGroupUser->can('view_users')) {
					$segments[0][] = array(
						'href' => 'members',
						'title'=> $title,
						'icon' => 'gcis gci-user-friends',
					);
				}

                $segments['_PeepSoGroup'] = $PeepSoGroup;
                $segments['_PeepSoGroupUser'] = $PeepSoGroupUser;

				$segments = apply_filters('peepso_group_segment_menu_links', $segments);

                unset($segments['_PeepSoGroup']);
                unset($segments['_PeepSoGroupUser']);

				foreach($segments as $segment_group) {
					foreach($segment_group as $segment) {

						$can_access = $PeepSoGroupUser->can('access_segment', $segment['href']);

						$href = $group->get_url();

						if(strlen($segment['href'])) {
							$href .= $segment['href'].'/';

                            // If passing an external link, treat it as such
                            if('http'==substr($segment['href'],0, 4)) {
                                $href = $segment['href'];
                            }
						}

						if($can_access) {
						?>
						<a class="ps-focus__menu-item ps-js-item <?php echo($segment['href'] == $group_segment) ? 'ps-focus__menu-item--active':'';?>" href="<?php echo $href; ?>">
							<i class="<?php echo $segment['icon']; ?>"></i>
							<span><?php echo $segment['title']; ?></span>
						</a>
						<?php
						}
					}
				}

				?>
				<a href="#" class="ps-focus__menu-item ps-focus__menu-item--more ps-tip ps-tip--arrow ps-js-item-more" aria-label="<?php echo __('More', 'groupso'); ?>" style="display:none">
					<i class="gcis gci-ellipsis-h"></i>
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
	peepsogroupsdata.group_id = +'<?php echo $group->id ?>';
});
</script>
