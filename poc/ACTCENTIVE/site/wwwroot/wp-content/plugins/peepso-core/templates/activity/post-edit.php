<div class="ps-postbox ps-postbox--edit cstream-edit">
	<div class="ps-postbox__inner">
		<div class="ps-postbox__content ps-postbox-content">
			<div class="ps-postbox__status ps-postbox-status">
				<div class="ps-postbox__status-wrapper">
					<div class="ps-postbox__input-wrapper ps-postbox-input ps-inputbox">
						<?php echo (isset($prefix)) ? $prefix : ''; ?>
						<textarea class="ps-postbox__input ps-textarea ps-postbox-textarea" placeholder="<?php echo __(apply_filters('peepso_postbox_message', 'Say what is on your mind...'), 'peepso-core'); ?>"><?php echo $cont; ?></textarea>
						<?php echo (isset($suffix)) ? $suffix : ''; ?>
					</div>
				</div>
				<div class="ps-postbox__chars-count ps-postbox-charcount ps-js-charcount"><?php echo PeepSo::get_option('site_status_limit', 4000) ?></div>
			</div>
		</div>
		<div class="ps-postbox__footer ps-postbox-tab selected">
			<div class="ps-postbox__menu ps-postbox__menu--tabs">
				<div class="ps-postbox__menu-item"><a>&nbsp;</a></div>
			</div>
			<div class="ps-postbox__actions ps-postbox-action">
				<button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-postbox__action--cancel ps-button-cancel" onclick="return activity.option_canceledit(<?php echo $act_id; ?>);"><?php echo __('Cancel', 'peepso-core'); ?></button>
				<button type="button" class="ps-btn ps-btn--sm ps-btn--action ps-postbox__action ps-postbox__action--post ps-button-action postbox-submit" onclick="return activity.option_savepost(<?php echo $act_id; ?>);"><?php echo __('Post', 'peepso-core'); ?></button>
			</div>
			<div class="ps-loading ps-edit-loading" style="display: none">
				<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
				<div> </div>
			</div>
		</div>
	</div>
</div>
