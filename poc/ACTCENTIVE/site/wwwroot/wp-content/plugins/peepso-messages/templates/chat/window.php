<div class="ps-chat__window-wrapper ps-chat-window-{id} ps-js-chat-window" data-id="{id}">
	<div class="ps-chat__window">
		<div class="ps-chat__window-inner">
			<div class="ps-chat__window-header ps-js-chat-window-header">
				<div class="ps-chat__window-header-user">
					<div class="ps-js-chat-window-status"></div>
					<div class="ps-chat__window-header-notif ps-js-chat-window-notif">0</div>
					<div class="ps-chat__window-header-name ps-js-chat-window-caption"><?php echo __('Loading', 'msgso');?>&hellip;</div>
				</div>
				<div class="ps-chat__window-header-actions">
					<div class="ps-chat__window-header-action ps-tip ps-tip--arrow ps-js-chat-options" data-id="{id}" aria-label="<?php echo __('Options', 'msgso');?>">
						<i class="gcis gci-cog"></i>
					</div>
					<div class="ps-chat__window-header-action ps-chat__window-header-action--minimize ps-tip ps-tip--arrow" aria-label="<?php echo __('Minimize', 'msgso');?>">
						<i class="gcis gci-minus"></i>
					</div>
					<div class="ps-chat__window-header-action ps-tip ps-tip--arrow ps-js-chat-close" data-id="{id}" aria-label="<?php echo __('Close', 'msgso');?>">
						<i class="gcis gci-times"></i>
					</div>
				</div>
				<div class="ps-chat__window-header-dropdown ps-js-chat-window-dropdown">
					<?php if (isset($read_notification) && (TRUE == $read_notification)) { ?>
					<a href="#" class="ps-js-chat-checkmark">
						<span><?php echo __("Don't send read receipt", 'msgso'); ?></span>
						<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" style="display:none">
					</a>
					<?php } ?>
					<a href="#" class="ps-js-chat-disable">
						<span><?php echo __('Turn on chat', 'msgso'); ?></span>
						<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" style="display:none">
					</a>
					<a href="#" class="ps-js-chat-mute">
						<span><?php echo __('Mute conversation', 'msgso'); ?></span>
						<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" style="display:none">
					</a>
					<a href="#" class="ps-js-chat-fullscreen"><?php echo __('View full conversation', 'msgso'); ?></a>
					<?php if (PeepSo::get_option('user_blocking_enable', 0) === 1) : ?>
					<a href="#" class="ps-js-chat-blockuser">
						<span><?php echo __('Block this user', 'msgso'); ?></span>
						<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" style="display:none">
					</a>
					<?php endif; ?>
				</div>
			</div>
			<div class="ps-chat__window-body">
				<div class="ps-chat__window-messages ps-js-chat-window-content">
					<div class="ps-chat__messages ps-js-chat-window-messages"></div>
					<div class="ps-chat__messages ps-chat__messages--temp ps-js-chat-window-tmpchat"></div>
					<div class="ps-chat__typing ps-js-chat-window-typing"></div>
				</div>
				<div class="ps-chat__window-notice ps-js-chat-window-muted"><?php echo __('This conversation is muted. New chat tabs will not pop up and you will not receive notifications.','msgso');?> <a href="#" class="ps-js-chat-mute"><?php echo __('Unmute','msgso');?></a></div>
				<div class="ps-chat__window-notice ps-js-chat-window-turned-off"><?php echo __('You turned off chat for this conversation but you can still send a message.', 'msgso'); ?></div>
			</div>
			<div class="ps-chat__window-footer ps-js-chat-window-input"></div>
		</div>
	</div>
</div>
