<?php $PeepSoActivity = PeepSoActivity::get_instance(); ?>
<div class="ps-comments__edit cstream-edit ps-comment-edit ps-js-comment-edit">
	<div class="ps-comments__input-wrapper ps-textarea-wrapper cstream-form-input">
		<textarea class="ps-comments__input ps-textarea cstream-form-text"
			oninput="return activity.on_commentbox_change(this);"
			onfocus="activity.on_commentbox_focus(this);"
			onblur="activity.on_commentbox_blur(this);"
			placeholder="<?php echo __('Write a comment...', 'peepso-core');?>"><?php echo $data['cont'];?></textarea>
		<?php $PeepSoActivity->show_commentsbox_addons($data['post_id']); ?>
	</div>
	<div class="ps-comments__reply-send">
		<div class="ps-loading ps-edit-loading" style="display: none">
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
			<div> </div>
		</div>
		<div class="ps-comments__reply-actions">
			<button class="ps-btn ps-button-cancel" onclick="return activity.option_canceleditcomment(<?php echo $data['post_id'];?>, this);"><?php echo __('Cancel', 'peepso-core'); ?></button>
			<button class="ps-btn ps-btn--action ps-button-action" onclick="return activity.option_savecomment(<?php echo $data['post_id']; ?>, this);"><?php echo __('Save', 'peepso-core'); ?></button>
		</div>
	</div>
</div>
