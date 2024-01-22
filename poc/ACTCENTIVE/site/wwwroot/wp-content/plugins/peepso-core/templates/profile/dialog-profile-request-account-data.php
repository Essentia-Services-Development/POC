<p><?php echo __('Please enter your password to continue.', 'peepso-core');?></p>
<form class="ps-form--profile-request-account-data">
	<div class="ps-form__row">
		<label class="ps-form__label" for="ps-js-export-data-request-pass">
			<?php echo __('Password', 'peepso-core'); ?>
		</label>
		<div class="ps-form__field">
			<input type="password" class="ps-input <?php echo PeepSo::get_option_new('password_preview_enable') ? 'ps-js-password-preview' : '' ?>"
				value="" id="ps-js-export-data-request-pass" />
			<span class="ps-text--danger ps-form__helper ps-js-error" style="display:none"></span>
		</div>
	</div>
</form>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Export my Community information', 'peepso-core'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'peepso-core'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Export', 'peepso-core'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
