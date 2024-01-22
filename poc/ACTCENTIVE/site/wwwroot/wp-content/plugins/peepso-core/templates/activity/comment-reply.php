<?php
$PeepSoUser = PeepSoUser::get_instance();
?>
<div id="wall-cmt-<?php echo $act_id; ?>" class="ps-comments ps-comments--nested ps-comment-nested ps-js-comment-reply--<?php echo $act_id; ?>">
	<div class="ps-comments__list ps-comment-container ps-js-comment-container ps-js-comment-container--<?php echo $act_id; ?>" data-act-id="<?php echo $act_id; ?>">
		<?php $PeepSoActivity->show_recent_comments(); ?>
	</div>

	<div id="act-new-comment-<?php echo $act_id; ?>" class="ps-comments__reply ps-comment-reply cstream-form stream-form wallform ps-js-comment-new ps-js-newcomment-<?php echo $act_id; ?>" data-type="stream-newcomment" data-formblock="true" style="display:none;">
		<a class="ps-avatar cstream-avatar cstream-author" href="<?php echo $PeepSoUser->get_profileurl(); ?>">
			<img src="<?php echo $PeepSoUser->get_avatar(); ?>" alt="" />
		</a>
		<div class="ps-comments__input-wrapper ps-textarea-wrapper cstream-form-input">
			<textarea
				data-act-id="<?php echo $act_id; ?>"
				class="ps-comments__input ps-textarea cstream-form-text"
				name="comment"
				oninput="return activity.on_commentbox_change(this);"
				onfocus="activity.on_commentbox_focus(this);"
				onblur="activity.on_commentbox_blur(this);"
				placeholder="<?php echo __('Write a reply...', 'peepso-core'); ?>"></textarea>
				<?php
				// call function to add button addons for comments
				$PeepSoActivity->show_commentsbox_addons();
				?>
		</div>
		<div class="ps-comments__reply-send ps-comment-send cstream-form-submit" style="display:none;">
			<div class="ps-loading ps-comment-loading" style="display: none">
				<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
				<div> </div>
			</div>
			<div class="ps-comments__reply-actions ps-comment-actions" style="display:none;">
				<button onclick="return activity.comment_cancel(<?php echo $act_id; ?>);" class="ps-btn ps-button-cancel"><?php echo __('Clear', 'peepso-core'); ?></button>
				<button onclick="return activity.comment_save(<?php echo $act_id; ?>, this);" class="ps-btn ps-btn--action ps-button-action" disabled><?php echo __('Post', 'peepso-core'); ?></button>
			</div>
		</div>
	</div>
</div>
