<div class="peepso">
	<div class="ps-page ps-page--category-groups">
		<?php PeepSoTemplate::exec_template('general','navbar'); ?>
		<div class="ps-groups">
			<?php PeepSoTemplate::exec_template('groups', 'group-category-header', array('group_category'=>$group_category, 'group_category_segment'=>$group_category_segment)); ?>

			<div class="ps-groups__header">
				<div class="ps-groups__header-inner">
					<div class="ps-groups__list-view">
						<div class="ps-btn__group">
							<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-groups-viewmode" data-mode="grid" aria-label="<?php echo __('Grid', 'groupso');?>"><i class="gcis gci-th-large"></i></a>
							<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-groups-viewmode" data-mode="list" aria-label="<?php echo __('List', 'groupso');?>"><i class="gcis gci-th-list"></i></a>
						</div>
					</div>

					<?php if(PeepSoGroupUser::can_create()) { ?>
					<div class="ps-groups__actions">
						<a class="ps-btn ps-btn--sm ps-btn--action" href="#" onclick="peepso.groups.dlgCreate(); return false;">
							<?php echo __('Create Group', 'groupso');?>
						</a>
					</div>
					<?php } ?>
				</div>
			</div>

			<input type="hidden" class="ps-js-groups-category" value="<?php echo $group_category->id ?>">
			<input type="hidden" class="ps-js-groups-sortby" value="post_title">
			<input type="hidden" class="ps-js-groups-sortby-order" value="ASC">

			<div class="mb-20"></div>
			<?php $single_column = PeepSo::get_option( 'groups_single_column', 0 ); ?>
			<div class="ps-groups__list <?php echo $single_column ? 'ps-groups__list--single' : '' ?> ps-js-groups" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>"></div>
			<div class="ps-groups__loading ps-js-groups-triggerscroll">
				<img class="ps-loading post-ajax-loader ps-js-groups-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
			</div>
		</div>
	</div>
</div>
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
