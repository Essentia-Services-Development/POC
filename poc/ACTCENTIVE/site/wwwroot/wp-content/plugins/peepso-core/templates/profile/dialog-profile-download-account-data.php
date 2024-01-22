<form class="ps-form--profile-download-account-data">
	<div class="ps-form__row">
		<label class="ps-form__label" for="ps-js-export-data-download-pass">
			<?php echo __('Password', 'peepso-core'); ?>
		</label>
		<div class="ps-form__field">
			<input type="password" class="ps-input <?php echo PeepSo::get_option_new('password_preview_enable') ? 'ps-js-password-preview' : '' ?>"
				value="" id="ps-js-export-data-download-pass" />
			<span class="ps-text--danger ps-form__helper ps-js-error" style="display:none"></span>
		</div>
	</div>
</form>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Download Archive', 'peepso-core'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'peepso-core'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Download Archive', 'peepso-core'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
