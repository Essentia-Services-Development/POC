<?php

$PeepSoMessages = PeepSoMessages::get_instance();

// Conversation flags.
$read_notification = isset($read_notification) && $read_notification;
$muted = isset($muted) && $muted;

?>
<?php if ($show_blockuser) { ?>
<a href="#" data-menu="block-user" data-user-id="<?php echo $show_blockuser_id; ?>"
	data-warning-text="<?php echo esc_attr(__('Are you sure want to block this user?', 'msgso')); ?>">
	<i class="gcis gci-ban"></i>
	<span><?php echo __('Block this user', 'msgso'); ?></span>
</a>
<?php } ?>
<a href="#" data-menu="add-recipients">
	<i class="gcis gci-user-plus"></i>
	<span><?php echo __('Add People to the conversation', 'msgso'); ?></span>
</a>
<?php if ($read_notification) { ?>
<a href="#" class="<?php echo $notif ? '' : ' disabled' ?>" data-menu="toggle-read-receipt"
		data-send="<?php echo $notif ? 1 : 0 ?>"
		data-send-text="<?php echo esc_attr(__('Send read receipt', 'msgso')) ?>"
		data-dont-send-text="<?php echo esc_attr(__("Don't send read receipt", 'msgso')) ?>">
	<i class="gcir gci-check-circle"></i>
	<span><?php echo $notif ? __("Don't send read receipt", 'msgso') : __('Send read receipt', 'msgso'); ?></span>
</a>
<?php } ?>
<a href="#" data-menu="toggle-mute" data-muted="<?php echo $muted ? 1 : 0 ?>"
		data-muted-text="<?php echo esc_attr(__('Unmute conversation', 'msgso')) ?>"
		data-unmuted-text="<?php echo esc_attr(__('Mute conversation', 'msgso')) ?>">
	<i class="<?php echo $muted ? 'gcis gci-bell-slash' : 'gcir gci-bell'; ?>"></i>
	<span><?php echo $muted ? __('Unmute conversation', 'msgso') : __('Mute conversation', 'msgso'); ?></span>
</a>
<a href="<?php echo $PeepSoMessages->get_leave_conversation_url(); ?>" data-menu="leave-conversation"
		data-warning-text="<?php echo esc_attr(__('Are you sure you want to leave this conversation?', 'msgso')); ?>">
	<i class="gcis gci-times"></i>
	<span><?php echo __('Leave this conversation', 'msgso'); ?></span>
</a>
