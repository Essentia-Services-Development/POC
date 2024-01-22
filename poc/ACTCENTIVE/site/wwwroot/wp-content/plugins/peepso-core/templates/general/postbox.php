<?php if(TRUE === apply_filters('peepso_permissions_post_create', is_user_logged_in())) {
$PeepSoGeneral = PeepSoGeneral::get_instance();
$PeepSoPostbox = PeepSoPostbox::get_instance();
?>
<div class="ps-postbox ps-postbox--edit">
	<div class="ps-postbox__inner">
		<div class="ps-postbox__content ps-postbox-content">
			<div class="ps-postbox__status ps-postbox-status">
				<div class="ps-postbox__status-wrapper">
					<div class="ps-postbox__status-inner">
						<span class="ps-postbox__status-mirror ps-postbox-mirror ps-js-mirror"></span>
						<span class="ps-postbox__status-addons ps-postbox-addons ps-js-addons"></span>
					</div>
					<div class="ps-postbox__input-wrapper ps-postbox-input ps-inputbox">
						<?php // echo (isset($prefix)) ? $prefix : ''; ?>
						<textarea class="ps-postbox__input ps-textarea ps-postbox-textarea" placeholder="<?php echo __(apply_filters('peepso_postbox_message', ''), 'peepso-core'); ?>"></textarea>
						<?php // echo (isset($suffix)) ? $suffix : ''; ?>
					</div>
				</div>
				<div class="ps-postbox__chars-count ps-postbox-charcount ps-js-charcount"></div>
			</div>
		</div>
		<div class="ps-postbox__footer ps-postbox-tab ps-postbox-tab-root ps-sclearfix" style="display:none">
			<div class="ps-postbox__menu ps-postbox__menu--tabs">
				<?php // $PeepSoGeneral->post_types(array('is_current_user' => isset($is_current_user) ? $is_current_user : NULL)); ?>
			</div>
		</div>
		<div class="ps-postbox__footer ps-postbox-tab selected">
			<div class="ps-postbox__menu ps-postbox__menu--interactions">
				<?php $PeepSoPostbox->post_interactions(array('is_current_user' => isset($is_current_user) ? $is_current_user : NULL)); ?>
			</div>
			<div class="ps-postbox__actions ps-postbox-action">
				<button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-postbox__action--cancel ps-tip ps-tip--arrow ps-button-cancel" aria-label="<?php echo __('Cancel', 'peepso-core'); ?>"><i class="gcis gci-times"></i></button>
				<button type="button" class="ps-btn ps-btn--sm ps-btn--action ps-postbox__action ps-postbox__action--post ps-button-action postbox-submit"><?php echo __('Post', 'peepso-core'); ?></button>
			</div>
			<div class="ps-loading ps-postbox-loading" style="display:none">
				<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
				<div></div>
			</div>
		</div>
	</div>
</div>
<?php } else { PeepSoTemplate::exec_template('general','postbox-permission-denied'); }// peepso_permissions_post_create ?>
