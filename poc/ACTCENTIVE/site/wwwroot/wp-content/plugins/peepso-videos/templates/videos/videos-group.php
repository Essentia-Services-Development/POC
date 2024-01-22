<div class="peepso ps-page-profile ps-page--group">
	<?php PeepSoTemplate::exec_template('general','navbar'); ?>
	<?php //PeepSoTemplate::exec_template('general', 'register-panel'); ?>

	<?php $PeepSoGroupUser = new PeepSoGroupUser($group->id, get_current_user_id());?>
	<?php if($PeepSoGroupUser->can('access')) { ?>

	<?php PeepSoTemplate::exec_template('groups', 'group-header', array('group'=>$group, 'group_segment'=>$group_segment)); ?>

	<div class="ps-media__page">
		<?php if (! get_current_user_id()) { PeepSoTemplate::exec_template('general','login-profile-tab'); } ?>

		<div class="ps-media__page-header">
			<div class="ps-media__page-list-view">
				<div class="ps-btn__group">
				<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-media-viewmode" data-mode="small" aria-label="<?php echo __('Small thumbnails', 'vidso');?>"><i class="gcis gci-th"></i></a>
				<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-media-viewmode" data-mode="large" aria-label="<?php echo __('Large thumbnails', 'vidso');?>"><i class="gcis gci-th-large"></i></a>
				</div>
			</div>

			<select class="ps-input ps-input--sm ps-input--select ps-js-videos-sortby">
				<option value="desc"><?php echo __('Newest first', 'vidso');?></option>
				<option value="asc"><?php echo __('Oldest first', 'vidso');?></option>
			</select>
		</div>

		<div class="mb-20"></div>
		<div class="ps-media__page-list ps-js-videos"></div> &nbsp;
		<div class="ps-js-videos-triggerscroll">
			<img class="post-ajax-loader ps-js-videos-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
		</div>
	</div>

	<?php } ?>

</div><!--end row-->
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
