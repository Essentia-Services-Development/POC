<?php
// If the details are not open, adjust CSS
$open_pref = get_user_meta(get_current_user_id(), 'peepso_admin_vipicon_open_'.$icon->post_id,TRUE);

// Force opening of the newly added vip icon
if(isset($force_open)) {
	$open_pref = 1;
}

// get_user_meta might return an empty string
$open = (strlen($open_pref) && 1 == $open_pref) ? FALSE : 'display:none';

// if not published, dim the container
$postbox_muted 			= (0 == $icon->published) ? 'postbox-muted' : FALSE;
$title_after = __('PeepSo-VIP Default', 'peepso-core');

if(1 == $icon->custom) {
	$title_after = __('PeepSo-VIP Custom', 'peepso-core');
}
?>
<div class="postbox ps-postbox--settings no-padd <?php echo $postbox_muted;?>" data-id="<?php echo $icon->post_id;?>">

	<h3 class="hndle ps-postbox__title ui-sortable-handle ps-js-handle">

		<div class="postbox-sorting">
			<span class="fa fa-arrows"></span>
			<span class="fa fa-<?php echo ($open) ? 'expand' : 'compress' ?> ps-js-vipicon-toggle"></span>
		</div>

		<div class="ps-postbox__title-label ps-js-vipicon-title">

			<?php ob_start(); ?>

			<img src="<?php echo $icon->icon_url;?>" id="ps-js-vipicon-<?php echo $icon->post_id;?>-icon">

			<?php
			// trying to avoid whitespace around img tag
			echo sprintf('<a data-id="%s" href="#" class="ps-js-vipicon-icon" onclick="return false;">%s</a>', $icon->post_id, trim(ob_get_clean()));
			?>
			<span id="vipicon-<?php echo $icon->post_id;?>-box-title" class="ps-postbox__title-text ps-js-vipicon-title-text">
				<?php echo $icon->title; ?>
			</span>

			<span class="fa fa-edit"></span>

			<small>
				<?php echo $title_after;?>
			</small>
		</div>

		<div class="ps-postbox__title-editor">
			<input type="text" value="<?php echo $icon->title; ?>"
				   data-parent-id="<?php echo $icon->post_id; ?>"
				   data-prop-type="prop"
				   data-prop-name="post_title">

			<button class="button ps-js-btn ps-js-cancel"><?php echo __('Cancel', 'peepso-core'); ?></button>
			<button class="button button-primary ps-js-btn ps-js-save"><?php echo __('Save', 'peepso-core'); ?></button>
			<span class="ps-settings__progress ps-js-progress">
				<img src="images/wpspin_light.gif" style="display:none">
				<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
			</span>
		</div>
	</h3>

	<div class="ps-js-vipicon" data-id="<?php echo $icon->post_id;?>" style="<?php echo $open;?>">
		<div class="ps-settings">

			<div id="cpf<?php echo $icon->post_id;?>-tab-1" class="ps-tab__content">

				<!-- Published -->
				<div class="ps-settings__row ps-js-vipiconconf" id="vipicon-<?php echo $icon->post_id;?>-published-container">
					<div class="ps-settings__label">
						<?php echo __('Published', 'peepso-core');?>
						<div class="ps-settings__progress ps-js-progress">
							<img src="images/wpspin_light.gif" style="display:none">
							<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
						</div>
					</div>

					<div class="ps-settings__controls">
						<input type="checkbox" data-prop-name="post_status" data-disabled-value="private" value="publish" admin_value="1" id="vipicon-<?php echo $icon->post_id;?>-published" <?php echo(1==$icon->published)? 'checked="checked"':'';?> data-parent-id="<?php echo $icon->post_id;?>" class="ace ace-switch ace-switch-2">
						<label class="lbl" for="vipicon-<?php echo $icon->post_id;?>-published"></label>	</div>
					</div>

				<!-- Notification -->
				<div class="ps-settings__row ps-js-vipiconconf" style="" id="vipicon-<?php echo $icon->post_id;?>-desc-container">
					<div class="ps-settings__label">
						<?php echo __('Icon Description', 'peepso-core');?>
						<div class="ps-settings__progress ps-js-progress">
							<img src="images/wpspin_light.gif" style="display:none">
							<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
						</div>
					</div>

					<div class="ps-settings__controls">
						<input type="text" class="ps-vipicon-notification" data-prop-name="post_content" value="<?php echo $icon->content;?>" id="vipicon-<?php echo $icon->post_id;?>-desc" data-parent-id="<?php echo $icon->post_id;?>">
						<button class="button ps-js-btn ps-js-cancel" style="display:none"><?php echo __('Cancel', 'peepso-core');?></button>
						<button class="button button-primary ps-js-btn ps-js-save" style="display:none"><?php echo __('Save', 'peepso-core');?></button>
					</div>


				</div>
			</div>

			<?php if(1 == $icon->custom) { ?>

			<div class="ps-settings__action">
				<a data-id="<?php echo $icon->post_id; ?>" href="#" class="ps-js-vipicon-delete"><i class="fa fa-trash"></i></a>
			</div>
			<?php } ?>

			<input type="hidden" id="vipicon-<?php echo $icon->post_id;?>-id" value="<?php echo $icon->post_id;?>">
			<input type="hidden" id="vipicon-<?php echo $icon->post_id;?>-order" value="<?php echo $icon->order;?>">
		</div>
	</div>
</div>