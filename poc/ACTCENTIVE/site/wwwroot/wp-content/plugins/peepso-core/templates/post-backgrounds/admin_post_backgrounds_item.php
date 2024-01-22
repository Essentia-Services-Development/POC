<?php
// If the details are not open, adjust CSS
$open_pref = get_user_meta(get_current_user_id(), 'peepso_admin_post_backgrounds_open_' . $post_backgrounds->post_id, true);

// Force opening of the newly added post_backgrounds
if (isset($force_open)) {
    $open_pref = 1;
}

// get_user_meta might return an empty string
$open = (strlen($open_pref) && 1 == $open_pref) ? false : 'display:none';

// if not published, dim the container
$postbox_muted = (0 == $post_backgrounds->published) ? 'postbox-muted' : false;

// core - indeletable
// like - icon cannot be changed
$title_after = __('Built-in preset', 'peepso-core');

if (1 == $post_backgrounds->custom) {
    $title_after = __('Custom', 'peepso-core');
}
?>
<div class="postbox ps-postbox--settings no-padd <?php echo $postbox_muted; ?>" data-id="<?php echo $post_backgrounds->post_id; ?>">

	<h3 class="hndle ps-postbox__title ui-sortable-handle ps-js-handle">

		<div class="postbox-sorting">
			<span class="fa fa-arrows"></span>
			<span class="fa fa-<?php echo ($open) ? 'expand' : 'compress' ?> ps-js-post-backgrounds-toggle"></span>
		</div>

		<div class="ps-postbox__title-label ps-js-post-backgrounds-title">

			<span id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-box-title" class="ps-postbox__title-text ps-js-post-backgrounds-title-text">
				<?php echo $post_backgrounds->title; ?>
			</span>

			<span class="fa fa-edit"></span>

			<small>
				<?php echo $title_after; ?>
			</small>
		</div>

		<div class="ps-postbox__title-editor">
			<input type="text" value="<?php echo $post_backgrounds->title; ?>"
				   data-parent-id="<?php echo $post_backgrounds->post_id; ?>"
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

	<div class="ps-js-post-backgrounds" data-id="<?php echo $post_backgrounds->post_id; ?>" style="<?php echo $open; ?>">
		<div class="ps-settings">

			<div id="cpf<?php echo $post_backgrounds->post_id; ?>-tab-1" class="ps-tab__content">

				<!-- Published -->
				<div class="ps-settings__row ps-js-post-backgroundsconf" id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-published-container">
					<div class="ps-settings__label">
						<?php echo __('Published', 'peepso-core'); ?>
						<div class="ps-settings__progress ps-js-progress">
							<img src="images/wpspin_light.gif" style="display:none">
							<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
						</div>
					</div>

					<div class="ps-settings__controls">
						<input type="checkbox" data-prop-name="post_status" data-disabled-value="private" value="publish" admin_value="1" id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-published" <?php echo (1 == $post_backgrounds->published) ? 'checked="checked"' : ''; ?> data-parent-id="<?php echo $post_backgrounds->post_id; ?>" class="ace ace-switch ace-switch-2">
						<label class="lbl" for="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-published"></label>	</div>
					</div>

				<!-- Image -->
				<div class="ps-settings__row ps-js-post-backgroundsconf" style="" id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-desc-container">
					<div class="ps-settings__label">
						<?php echo __('Image', 'peepso-core'); ?>
						<div class="ps-settings__progress ps-js-progress">
							<img src="images/wpspin_light.gif" style="display:none">
							<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
						</div>
					</div>

					<div class="ps-settings__controls">
						<div class="psa-post__backgrounds-preview" style="background-image: url('<?php echo $post_backgrounds->image_url; ?>')" id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-image">
							<span class="psa-post__backgrounds-preview-text ps-js-preview-text" style="color:<?php echo $post_backgrounds->content->text_color ?>">
								Your Community. Your Way. ®
							</span>
						</div>
						<p>
							<a class="btn btn-sm btn-info btn-img-post-backgrounds" href="#" style="margin-top:10px" data-id="<?php echo $post_backgrounds->post_id; ?>"><?php echo __('Change image', 'peepso-core');?></a>

							<?php if (!$post_backgrounds->custom) {?>
								<a class="btn btn-sm btn-danger ps-js-post-backgrounds-reset" style="margin-top:10px" onclick="return confirm('<?php echo __('Are you sure?', 'peepso-core'); ?>')" href="<?php echo admin_url('admin.php?page=peepso-manage&tab=post-backgrounds&action=reset-post-backgrounds&id=' . $post_backgrounds->post_id . '&_wpnonce=' . wp_create_nonce('reset-post-backgrounds-nonce')); ?>" >
									<?php echo __('Reset to default', 'peepso-core'); ?>
								</a>
							<?php } ?>

						</p>
						<p>The default proportions are <code>16:10</code>, and the recommended dimensions are  <code>1280x800</code>.</p>
					</div>
				</div>

				<!-- Text Color -->
				<div class="ps-settings__row ps-js-post-backgroundsconf" style="" id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-text-color-container">
					<div class="ps-settings__label">
						<?php echo __('Text Color', 'peepso-core'); ?>
						<div class="ps-settings__progress ps-js-progress">
							<img src="images/wpspin_light.gif" style="display:none">
							<i class="ace-icon fa fa-check bigger-110" style="display:none"></i>
						</div>
					</div>

					<div class="ps-settings__controls">
						<input type="text" value="<?php echo $post_backgrounds->content->text_color;?>" data-prop-name="post_content|text_color" id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-text-color" data-parent-id="<?php echo $post_backgrounds->post_id; ?>" class="color-picker" data-default-color="<?php echo $post_backgrounds->content->text_color;?>" data-alpha-enabled="true"/>
            <button class="button button-primary ps-js-save"><?php echo __('Save', 'peepso-core'); ?></button>
					</div>
				</div>


			</div>

			<div class="ps-settings__action">
				<?php if (1 == $post_backgrounds->custom) {?>
					<a data-id="<?php echo $post_backgrounds->post_id; ?>" href="#" class="ps-js-post-backgrounds-delete"><i class="fa fa-trash"></i></a>
				<?php } ?>
			</div>

			<input type="hidden" id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-id" value="<?php echo $post_backgrounds->post_id; ?>">
			<input type="hidden" id="post-backgrounds-<?php echo $post_backgrounds->post_id; ?>-order" value="<?php echo $post_backgrounds->order; ?>">
		</div>
	</div>
</div>
