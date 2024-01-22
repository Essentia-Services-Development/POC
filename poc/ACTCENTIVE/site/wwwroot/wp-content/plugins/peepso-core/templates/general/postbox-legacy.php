<?php if(TRUE === apply_filters('peepso_permissions_post_create', is_user_logged_in())) {
$PeepSoPostbox = PeepSoPostbox::get_instance();
$PeepSoGeneral = PeepSoGeneral::get_instance();
?>

<?php if (is_user_logged_in() && FALSE === PeepSoActivityShortcode::get_instance()->is_permalink_page()) { ?>
<style>
	.ps-postbox__disabler { display: none }
	.ps-postbox--disabled .ps-postbox__inner { opacity: .5 }
	.ps-postbox--disabled .ps-postbox__disabler { display: block; position: absolute; top: 0; left: 0; right: 0; bottom: 0 }
</style>
<div id="postbox-main" class="ps-postbox ps-postbox--disabled ps-js-postbox">
	<?php $PeepSoPostbox->before_postbox(); ?>
	<div class="ps-postbox__inner">
		<div id="ps-postbox-status" class="ps-postbox__content ps-postbox-content">
			<div class="ps-postbox__views ps-postbox-tabs"><?php $PeepSoPostbox->postbox_tabs(); ?></div>
			<?php PeepSoTemplate::exec_template('general', 'postbox-status'); ?>
		</div>

		<div class="ps-postbox__footer ps-js-postbox-footer ps-postbox-tab ps-postbox-tab-root ps-clearfix" style="display:none">
			<div class="ps-postbox__menu ps-postbox__menu--tabs">
				<?php $PeepSoGeneral->post_types(array('is_current_user' => isset($is_current_user) ? $is_current_user : NULL)); ?>
			</div>
		</div>

		<div class="ps-postbox__footer ps-js-postbox-footer ps-postbox-tab selected interactions" style="display:none">
			<div class="ps-postbox__menu ps-postbox__menu--interactions">
				<?php $PeepSoPostbox->post_interactions(array('is_current_user' => isset($is_current_user) ? $is_current_user : NULL)); ?>
			</div>
			<div class="ps-postbox__actions ps-postbox-action">
				<?php if(PeepSo::is_admin() && PeepSo::is_dev_mode('embeds')) { ?>
				<button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-postbox__action--cancel ps-js-btn-preview"><?php echo __('Fetch URL', 'peepso-core'); ?></button>
				<?php } ?>
				<button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-tip ps-tip--arrow ps-postbox__action--cancel ps-button-cancel"
					aria-label="<?php echo __('Cancel', 'peepso-core'); ?>"
					style="display:none"><i class="gcis gci-times"></i></button>
				<button type="button" class="ps-btn ps-btn--sm ps-btn--action ps-postbox__action ps-postbox__action--post ps-button-action postbox-submit"
					style="display:none"><?php echo __('Post', 'peepso-core'); ?></button>
			</div>
			<div class="ps-loading ps-postbox-loading" style="display: none">
				<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
				<div> </div>
			</div>
		</div>
	</div>
	<div class="ps-postbox__disabler"></div>
	<?php $PeepSoPostbox->after_postbox(); ?>
</div>
<?php } // is_user_logged_in() ?>
<?php } else { PeepSoTemplate::exec_template('general','postbox-permission-denied'); }// peepso_permissions_post_create ?>
