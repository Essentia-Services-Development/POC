<?php
$PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();
$PeepSoGroupUser = new PeepSoGroupUser($group->id);
$small_thumbnail = PeepSo::get_option('small_url_preview_thumbnail', 0);
?>

<div class="peepso">
	<div class="ps-page ps-page--group">
		<?php PeepSoTemplate::exec_template('general','navbar'); ?>
		<?php //PeepSoTemplate::exec_template('general', 'register-panel'); ?>

		<?php if($PeepSoGroupUser->can('access')) { ?>
			<div class="ps-profile ps-profile--group">
				<?php PeepSoTemplate::exec_template('groups', 'group-header', array('group'=>$group, 'group_segment'=>$group_segment)); ?>

				<div class="ps-activity">
					<?php
						if ($PeepSoGroupUser->can('post')) {
							PeepSoTemplate::exec_template('general', 'postbox-legacy');
						} else {
							// default message for non-members
							$message = __('You must join the group to be able to create new posts.' ,'groupso');

							if($group->is_readonly) {
								$message = __('This is an announcement group, only the Owners and Managers can create new posts.', 'groupso');
							}

                            switch ($group->is_allowed_non_member_actions) {
                                case 1:
                                    $message .= '<br/>' . __('You can still react to the posts in this group.','groupso');
                                    break;
                                case 2:
                                    $message .= '<br/>' . __('You can still comment on posts in this group.','groupso');
                                    break;
                                case 3:
                                    $message .= '<br/>' . __('You can still react & comment on posts in this group.','groupso');
                                    break;
                                default:
                                    break;
                            }


							// optional message for unpublished groups
							if(!$group->published) {
								$message = __('Currently group is unpublished.', 'groupso');
							}

							if(get_current_user_id()) {
							?>
								<div class="ps-alert ps-alert--warning" >
									<i class="gcis gci-user-lock"></i> 
									<span><?php echo $message;?></span>
								</div>
							<?php
							} else {
								PeepSoTemplate::exec_template('general','login-profile-tab');
							}
						}

						if(PeepSo::is_admin() || $PeepSoGroupUser->is_member) {
								PeepSoTemplate::exec_template('activity', 'activity-stream-filters-simple', array());
						}
					?>

					<?php if(PeepSo::is_admin() || $group->is_open || $PeepSoGroupUser->is_member) { ?>
						<!-- stream activity -->
						<input type="hidden" id="peepso_context" value="group" />
						<div class="ps-activity__container">
							<div id="ps-activitystream-recent" class="ps-posts <?php echo $small_thumbnail ? '' : 'ps-posts--narrow' ?>" style="display:none"></div>
							<div id="ps-activitystream" class="ps-posts <?php echo $small_thumbnail ? '' : 'ps-posts--narrow' ?>" style="display:none"></div>

							<div id="ps-activitystream-loading" class="ps-posts__loading">
								<?php PeepSoTemplate::exec_template('activity', 'activity-placeholder'); ?>
							</div>

							<div id="ps-no-posts" class="ps-posts__empty"><?php echo __('No posts found.', 'groupso'); ?></div>
							<div id="ps-no-posts-match" class="ps-posts__empty"><?php echo __('No posts found.', 'groupso'); ?></div>
							<div id="ps-no-more-posts" class="ps-posts__empty"><?php echo __('Nothing more to show.', 'groupso'); ?></div>

							<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<?php
if(get_current_user_id()) {
	PeepSoTemplate::exec_template('activity' ,'dialogs');
}
