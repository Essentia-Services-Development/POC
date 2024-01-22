<div class="ps-postbox ps-postbox--edit cstream-edit">
	<div class="ps-postbox__inner">
		<div class="ps-postbox__content ps-postbox-content">
			<div class="ps-postbox__status ps-postbox-status">
				<div class="ps-postbox__status-wrapper">
					<div class="ps-postbox__input-wrapper ps-postbox-input ps-inputbox">
						<textarea class="ps-postbox__input ps-textarea ps-postbox-textarea"><?php echo esc_textarea($cont); ?></textarea>
					</div>
				</div>
			</div>
		</div>
		<div class="ps-postbox__footer ps-postbox-tab selected">
			<div class="ps-postbox__menu">
				<div class="ps-postbox__menu-item"><a>&nbsp;</a></div>
			</div>
			<div class="ps-postbox__actions ps-postbox-action">
				<button class="ps-btn ps-btn--sm ps-postbox__action ps-postbox__action--cancel ps-button-cancel" onclick="return activity.option_cancel_edit_description('<?php echo $act_id; ?>');"><?php echo __('Cancel', 'peepso-core'); ?></button>
				<button class="ps-btn ps-btn--sm ps-btn--action ps-postbox__action ps-postbox__action--post ps-button-action" onclick="return activity.option_save_description('<?php echo $act_id; ?>', '<?php echo $type; ?>', '<?php echo $act_external_id; ?>');"><?php echo __('Save', 'peepso-core'); ?></button>
			</div>
			<div class="ps-loading ps-edit-loading" style="display: none;">
				<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
				<div> </div>
			</div>
		</div>
	</div>
</div>
