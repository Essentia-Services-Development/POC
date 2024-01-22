<?php
PeepSoTemplate::exec_template('general', 'js-unavailable');
$PeepSoActivity = PeepSoActivity::get_instance();
$PeepSoShare 	= PeepSoShare::get_instance();
?>
<div id="ps-dialogs" style="display:none">
	<div id="ajax-loader-gif" style="display:none;">
		<div class="ps-loading-image">
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="">
			<div> </div>
		</div>
	</div>
	<div id="ps-dialog-comment">
		<div data-type="stream-newcomment" class="cstream-form stream-form wallform " data-formblock="true" style="display: block;">
			<form class="reset-gap">
				<div class="cstream-form-submit">
					<a href="#" data-action="cancel" onclick="return activity.comment_cancel(); return false;" class="ps-btn ps-btn-small cstream-form-cancel"><?php echo __('Cancel', 'peepso-core'); ?></a>
					<button data-action="save" onclick="return activity.comment_save();" class="ps-btn ps-btn-small ps-btn-primary"><?php echo __('Post Comment', 'peepso-core'); ?></button>
				</div>
			</form>
		</div>
	</div>

	<div id="ps-report-dialog">
		<div id="activity-report-title"><?php echo __('Report Content to Admin', 'peepso-core'); ?></div>
		<div id="activity-report-content">
			<div id="postbox-report-popup">
				<div><?php echo __('Reason for Report:', 'peepso-core'); ?></div>
				<div class="ps-text--danger"><?php $PeepSoActivity->report_reasons(); ?></div>
				<div class="ps-alert" style="display:none"></div>
				<input type="hidden" id="postbox-post-id" name="post_id" value="{post-id}" />
			</div>
		</div>
		<div id="activity-report-actions">
			<button type="button" name="rep_cacel" class="ps-btn ps-btn-small ps-button-cancel" onclick="pswindow.hide(); return false;"><?php echo __('Cancel', 'peepso-core'); ?></button>
			<button type="button" name="rep_submit" class="ps-btn ps-btn-small ps-button-action" onclick="activity.submit_report(); return false;"><?php echo __('Submit Report', 'peepso-core'); ?></button>
		</div>
	</div>

	<span id="report-error-select-reason"><?php echo __('ERROR: Please select Reason for Report.', 'peepso-core'); ?></span>
	<span id="report-error-empty-reason"><?php echo __('ERROR: Please fill Reason for Report.', 'peepso-core'); ?></span>

	<div id="ps-share-dialog">
		<div id="share-dialog-title"><?php echo __('Share...', 'peepso-core'); ?></div>
		<div id="share-dialog-content">
			<?php $PeepSoShare->show_links();?>
		</div>
	</div>

	<div id="default-delete-dialog">
		<div id="default-delete-title"><?php echo __('Confirm Delete', 'peepso-core'); ?></div>
		<div id="default-delete-content">
			<?php echo __('Are you sure you want to delete this?', 'peepso-core'); ?>
		</div>
		<div id="default-delete-actions">
			<button type="button" class="ps-btn ps-btn-small ps-button-cancel" onclick="pswindow.hide(); return false;"><?php echo __('Cancel', 'peepso-core'); ?></button>
			<button type="button" class="ps-btn ps-btn-small ps-button-action" onclick="pswindow.do_delete();"><?php echo __('Delete', 'peepso-core'); ?></button>
		</div>
	</div>

	<div id="default-acknowledge-dialog">
		<div id="default-acknowledge-title"><?php echo __('Confirm', 'peepso-core'); ?></div>
		<div id="default-acknowledge-content">
			<div>{content}</div>
		</div>
		<div id="default-acknowledge-actions">
			<button type="button" class="ps-btn ps-btn-small ps-button-action" onclick="return pswindow.hide();"><?php echo __('Okay', 'peepso-core'); ?></button>
		</div>
	</div>

	<div id="ps-profile-delete-dialog">
		<div id="profile-delete-title"><?php echo __('Confirm Delete', 'peepso-core'); ?></div>
		<div id="profile-delete-content">
			<div>
				<h4 class="ps-page__body-title"><?php echo __('Are you sure you want to delete your Profile?', 'peepso-core'); ?></h4>

				<p><?php echo __('This will remove all of your posts, saved information and delete your account.', 'peepso-core'); ?></p>

				<p><em class="ps-text--danger"><?php echo __('This cannot be undone.', 'peepso-core'); ?></em></p>

				<button type="button" name="rep_cacel" class="ps-btn ps-button-cancel" onclick="pswindow.hide(); return false;"><?php echo __('Cancel', 'peepso-core'); ?></button>
				&nbsp;
				<button type="button" name="rep_submit" class="ps-btn ps-button-action" onclick="profile.delete_profile_action(); return false;"><?php echo __('Delete My Profile', 'peepso-core'); ?></button>
			</div>
		</div>
	</div>

	<?php PeepSoTemplate::exec_template('activity', 'dialog-repost'); ?>
	<?php PeepSoTemplate::exec_template('members', 'search-popover-input'); ?>

	<?php $PeepSoActivity->dialogs(); // give add-ons a chance to output some HTML ?>
</div>
