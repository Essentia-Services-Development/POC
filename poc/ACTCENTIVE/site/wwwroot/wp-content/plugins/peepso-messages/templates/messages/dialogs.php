<?php
$PeepSoPostbox = PeepSoPostbox::get_instance();
$PeepSoGeneral = PeepSoGeneral::get_instance();

add_filter('peepso_permissions_post_create', array('PeepSoMessagesPlugin', 'peepso_permission_message_create'), 99);
?>
<div id="new-message-dialog">
	<div class="dialog-title"><?php echo __('Write Message', 'msgso'); ?></div>
	<div class="dialog-content">
		<form class="ps-form ps-form--messages-new ps-message-form" role="form" onsubmit="return false;">
			<div class="ps-message__recipients ps-js-recipient-single" style="display:none">
				<div class="ps-message__recipients-label">
					<?php echo __('Recipient', 'msgso'); ?>
				</div>
				<div class="ps-message__recipient">
					<div class="ps-avatar">
						<a href=""><img class="cavatar" src="" alt=""></a>
					</div>
					<div class="ps-message__recipient-info ps-comment-body">
						<span class="ps-message__recipient-name ps-comment-user"></span>
					</div>
				</div>
			</div>
			<div class="ps-message__recipients ps-js-recipient-multiple" style="display:none">
				<div class="ps-message__recipients-label">
					<?php echo __('Recipients', 'msgso'); ?>
				</div>
				<div class="ps-message__recipients-select">
					<select name="recipients" class="recipients-search"
						data-placeholder="<?php echo __('Select Recipients', 'msgso');?>"
						data-loading="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>"
						multiple></select>
				</div>
			</div>
			<div class="ps-message__recipients ps-js-recipient-loading" style="display:none">
				<div class="ps-message__recipients-label">
					<?php echo __('Recipient', 'msgso'); ?>
				</div>
				<div class="ps-messages-label">
					<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
				</div>
			</div>
			<div class="ps-postbox ps-message__postbox ps-postbox-message" style="">
				<?php $PeepSoPostbox->before_postbox(); ?>
				<div class="ps-postbox__inner">
					<div id="ps-postbox-status" class="ps-postbox__content ps-postbox-content">
						<div class="ps-postbox__views ps-postbox-tabs"><?php $PeepSoPostbox->postbox_tabs('messages'); ?></div>
						<?php PeepSoTemplate::exec_template('general', 'postbox-status',['placeholder' => __('Write a message...','msgso')]); ?>
					</div>
					<div class="ps-postbox__footer ps-postbox-tab ps-postbox-tab-root ps-clearfix" style="display:none">
						<div class="ps-postbox__menu ps-postbox__menu--tabs">
							<?php $PeepSoGeneral->post_types(array('postbox_message' => TRUE)); ?>
						</div>
					</div>
					<div class="ps-postbox__footer ps-postbox-tab selected interactions">
						<div class="ps-postbox__menu ps-postbox__menu--interactions">
							<?php $PeepSoPostbox->post_interactions(array('postbox_message' => TRUE)); ?>
						</div>
						<div class="ps-postbox__actions ps-postbox-action">
							<button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-postbox__action--cancel ps-button-cancel" style="display:none"><?php echo __('Cancel', 'msgso'); ?></button>
							<button type="button" class="ps-btn ps-btn--sm ps-btn--action ps-postbox__action ps-postbox__action--post ps-button-action postbox-submit" style="display:none"><?php echo __('Send Message', 'msgso'); ?></button>
						</div>
						<div class="ps-loading ps-edit-loading" style="display: none;">
							<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
							<div> </div>
						</div>
					</div>
				</div>
				<?php $PeepSoPostbox->after_postbox(); ?>
			</div>
			<div class="ps-alert ps-alert--abort" style="display:none"></div>
		</form>
	</div>
</div>

<?php remove_filter('peepso_permissions_post_create', array('PeepSoMessagesPlugin', 'peepso_permission_message_create'), 99); ?>
