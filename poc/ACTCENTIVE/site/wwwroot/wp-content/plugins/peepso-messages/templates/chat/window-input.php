<div class="ps-chat__window-input-wrapper">
	<textarea class="ps-chat__window-input" placeholder="<?php echo __('Write a message...', 'msgso'); ?>"></textarea>
	<?php if ( count($addons) > 0 ) { ?>
	<div class="ps-chat__window-input-addons ps-chat-input-addons ps-js-addons">
		<?php foreach( $addons as $addon ) { ?>
		<?php echo $addon; ?>
		<?php } ?>
	</div>
	<?php } ?>
</div>
