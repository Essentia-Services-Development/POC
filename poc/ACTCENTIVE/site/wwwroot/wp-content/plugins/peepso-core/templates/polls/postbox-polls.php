<div class="ps-postbox__poll ps-js-polls">
	<div class="ps-postbox__fetched ps-postbox-fetched"></div>
	<div class="ps-postbox__poll-inner">
		<div class="ps-postbox__poll-container">
			<div class="ps-postbox__poll-options ui-sortable">
				<div class="ps-postbox__poll-option ps-poll__option">
					<a class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-js-handle ui-sortable-handle" title="<?php echo __('Move', 'peepso-core'); ?>" href="#"><i class="gcis gci-arrows-alt"></i></a>
					<input class="ps-input ps-input--sm" type="text" placeholder="<?php echo __('Option 1', 'peepso-core'); ?>">
					<a id="ps-delete-option" class="ps-btn ps-btn--sm ps-btn--cp ps-btn--delete ps-tip ps-tip--arrow" aria-label="<?php echo __('Delete', 'peepso-core'); ?>" href="#"><i class="gcis gci-trash"></i></a>
				</div>
				<div class="ps-postbox__poll-option ps-poll__option">
					<a class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-js-handle ui-sortable-handle" title="<?php echo __('Move', 'peepso-core'); ?>" href="#"><i class="gcis gci-arrows-alt"></i></a>
					<input class="ps-input ps-input--sm" type="text" placeholder="<?php echo __('Option 2', 'peepso-core'); ?>">
					<a id="ps-delete-option" class="ps-btn ps-btn--sm ps-btn--cp ps-btn--delete ps-tip ps-tip--arrow" aria-label="<?php echo __('Delete', 'peepso-core'); ?>" href="#"><i class="gcis gci-trash"></i></a>
				</div>
			</div>

			<div class="ps-postbox__poll-actions">
				<button class="ps-btn ps-btn--action ps-btn--sm ps-button-action" id="ps-add-new-option"><?php echo __('Add new option', 'peepso-core');?></button>

				<?php if (isset($multiselect) && $multiselect) : ?>
					<div class="ps-checkbox">
						<input type="checkbox" id="allow-multiple" class="ps-checkbox__input ace ace-switch ace-switch-2 allow-multiple" />
						<label class="ps-checkbox__label lbl" for="allow-multiple"><?php echo __('Allow multiple options selection', 'peepso-core'); ?></label>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
