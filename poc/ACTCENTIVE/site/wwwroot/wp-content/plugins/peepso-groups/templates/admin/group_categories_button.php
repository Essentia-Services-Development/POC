<div class="ps-settings__bar clearfix">
	<div class="ps-settings__nav">
		<button type="button" class="ps-js-group-categories-expand-all">
			<i class="fa fa-expand"></i> <span><?php echo __('Expand All', 'groupso'); ?></span>
		</button>
		<button type="button" class="ps-js-group-categories-collapse-all">
			<i class="fa fa-compress"></i> <span><?php echo __('Collapse All', 'groupso'); ?></span>
		</button>
	</div>
	<?php
	if(!PeepSo::get_option('groups_categories_enabled', FALSE)) {
		echo '<span class="ps-settings__bar-notice"><a href="'.admin_url('admin.php?page=peepso_config&tab=groups#field_groups_categories_enabled').'">' . __('Group categories are currently disabled. You can enable them in PeepSo Config -> Groups', 'groupso') . '</a></span>';
	}

	?>
	<div class="ps-settings__nav ps-settings__nav--right ps-dropdown">
		<button type="button" class="btn-primary ps-js-group-categories-new">
			<i class="fa fa-plus"></i> <span><?php echo __('Add New', 'groupso'); ?></span>
		</button>
	</div>
</div>

<?php
if(!PeepSo::get_option('groups_categories_enabled', FALSE)) {
	echo '<div class="ps-alert ps-hide--desktop">', sprintf(__('Group categories are currently disabled. You can enable them in PeepSo Config -> Groups', 'groupso')), '</div>';
}

?>
