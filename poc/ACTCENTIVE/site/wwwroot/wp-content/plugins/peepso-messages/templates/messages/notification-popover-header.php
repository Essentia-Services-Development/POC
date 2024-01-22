<div class="ps-notif__box-title"><?php echo __('Messages', 'msgso'); ?></div>

<?php if(FALSE !== apply_filters('peepso_permissions_messages_create', TRUE)) { ?>
<div class="ps-notif__box-actions">
	<a href="<?php echo PeepSo::get_page('messages'); ?>" 
		onclick="ps_messages.new_message(undefined, 'is_friend'); return false"><?php echo __('New message', 'msgso'); ?></a>
</div>
<?php }