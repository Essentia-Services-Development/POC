<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UVJGN1JEY1JOZTBVWVgrRDRhT0ZnOFhPQ1hqZTJjWFZvRHNDeTBqMWxLbisvVjN2TEtwc1NjVjg0ZDcvK2t2SDlmWWFxNzhZdFZrZ0RNY09FVHpzZEI2TWZZRktoWU5QMHVDUGRpTkNYc2Z1QUc5L25YVG9yWmh5cS9WblBROFhsL3dmK2hMZXAzSm9yempkR2dpZU4z*/

	$PeepSoPhotos = PeepSoPhotos::get_instance();

?>
<div class="ps-form ps-form--vertical ps-form--album-create">
	<div class="ps-form__grid">
		<div class="ps-form__row ps-form__row--half">
			<label class="ps-form__label"><?php echo __('Album name', 'picso'); ?> <span class="ps-text--danger">*</span></label>
			<div class="ps-form__field">
				<input type="text" name="album_name" maxlength="50" class="ps-input ps-input--sm" value="" />
			</div>
			<span class="ps-form__helper ps-text--danger ps-js-error-name" style="display:none"><?php echo __('Album name can\'t be empty', 'picso'); ?></span>
		</div>

	<?php
	$privacy = apply_filters('peepso_photos_create_album_privacy_hide', false);
	if(!$privacy) {
	?>
		<div class="ps-form__row ps-form__row--half">
			<label class="ps-form__label"><?php echo __('Album privacy', 'picso'); ?></label>
			<div class="ps-form__field">
				<select name="album_privacy" class="ps-input ps-input--sm ps-input--select"><?php
					foreach ($access_settings as $key => $value) {
						echo '<option value="' . $key . '">' . $value['label'] . '</option>';
					}
				?></select>
			</div>
		</div>
	<?php
	}
	// adding capability to extends fields for other plugins
	$PeepSoPhotos->photo_album_extra_fields();
	?>
		<div class="ps-form__row">
			<label class="ps-form__label"><?php echo __('Album description', 'picso'); ?></label>
			<div class="ps-form__field"><textarea name="album_desc" class="ps-input ps-input--sm ps-input--textarea"></textarea></div>
		</div>
	</div>
</div>

<div class="ps-album__upload ps-js-photos-container" style="display:none"></div>
<div class="ps-album__upload-area ps-js-photos-upload">
	<span class="ps-btn ps-js-photos-upload-button">
		<i class="gcis gci-upload"></i>
		<?php echo __('Upload photos to album', 'picso'); ?>
	</span>
</div>
<span class="ps-js-error-photo ps-text--danger ps-form__helper" style="display:none"><?php echo __('Please select at least one photo to be uploaded', 'picso'); ?></span>
<?php wp_nonce_field('photo-create-album', '_wpnonce'); ?>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Create Album', 'picso'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'picso'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Create Album', 'picso'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
