<?php
$force_add = FALSE;
if(PeepSo::is_admin() && 1 == PeepSo::get_option('groups_add_by_admin_directly', 0)) {
		$force_add = TRUE;
}
?>

<div class="ps-group__invite">
	<div class="ps-group__invite-search">
		<input type="text" class="ps-input ps-full" value="" placeholder="<?php echo __('Start typing to search...', 'groupso'); ?>" />
	</div>

	<div class="ps-group__invite-list ps-js-scrollable">
		<div class="ps-members ps-js-member-items"></div>
		<div class="ps-loading ps-js-loading"><img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" /></div>
		<button class="ps-btn ps-btn--full ps-btn--action ps-js-loadmore" style="margin-top:var(--PADD--MD)"><?php echo __('Load more', 'groupso'); ?></button>
		<div class="ps-alert ps-js-nomore"><?php echo __('Nothing more to show.', 'groupso'); ?></div>
	</div>

	<div class="ps-alert ps-alert--neutral">
		<p>
			<?php echo __('Please note: Users who are either banned, already invited, members or blocked receiving invitations to this group will not show in this listing.', 'groupso'); ?>
		</p>
	</div>
</div>

<script type="text/template" class="ps-js-member-item">
	<div class="ps-member">
		<div class="ps-member__inner">
			<div class="ps-member__header">
				<a href="{{= data.profileurl }}" class="ps-avatar ps-avatar--member">
					<img src="{{= data.avatar }}" title="{{= data.fullname }}" alt="{{= data.fullname }} avatar">
				</a>
			</div>

			<div class="ps-member__body">
				<div class="ps-member__name">
					<a href="{{= data.profileurl }}" class="ps-members-item-title" title="{{= data.fullname }}">
						{{= data.fullname_with_addons }}
					</a>
				</div>
			</div>

			<div class="ps-member__actions">
				<a class="ps-member__action ps-js-invite" data-id="{{= data.id }}" href="javascript:">
					<span data-invited="<?php echo $force_add ? __('Added', 'groupso') : __('Invited', 'groupso'); ?>"><?php echo $force_add ? __('Add to group', 'groupso') : __('Invite to Group', 'groupso'); ?></span>
					<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
				</a>
			</div>
		</div>
	</div>
</script>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => $force_add ? __('Add users to group', 'groupso') : __('Invite users to group', 'groupso'),
	'actions' => false,
	'class' => 'ps-modal--group-invite',
	'reloadOnClose' => isset($reload_on_close) ? $reload_on_close : false
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
