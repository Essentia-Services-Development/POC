<div class="ps-dialog--mute">
	<?php echo __('How long do you want to mute this conversation?', 'msgso'); ?>
	<form>
		<div class="ps-padding">
			<div id="mute_test" class="ps-checkbox" style="display: none">
				<input type="radio" id="mute_conv" name="mute_conv" value="0.00833" checked="checked">
				<label for="mute_conv"><?php echo __('30 seconds', 'msgso'); ?></label>
			</div>
			<div class="ps-checkbox">
				<input type="radio" id="mute_conv1" name="mute_conv" value="1" checked="checked">
				<label for="mute_conv1"><?php echo __('an hour', 'msgso'); ?></label>
			</div>
			<div class="ps-checkbox">
				<input type="radio" id="mute_conv2" name="mute_conv" value="24">
				<label for="mute_conv2"><?php echo __('a day', 'msgso'); ?></label>
			</div>
			<div class="ps-checkbox">
				<input type="radio" id="mute_conv3" name="mute_conv" value="168">
				<label for="mute_conv3"><?php echo __('a week', 'msgso'); ?></label>
			</div>
			<div class="ps-checkbox">
				<input type="radio" id="mute_conv4" name="mute_conv" value="9999">
				<label for="mute_conv4"><?php echo __('until I unmute it', 'msgso'); ?></label>
			</div>
		</div>
		<div class="ps-text--center">
			<input type="button" class="ps-btn ps-btn-action" value="<?php echo __('Mute', 'msgso'); ?>" onclick="ps_messages.confirm_mute_conversation({msg_id}, this);">
		</div>
	</form>
</div>
