<?php

$time_format = get_option('time_format');
$ampm = preg_match('/[gh]/', $time_format);

?><form class="ps-form ps-form--vertical ps-form--pin-until">
	<div class="ps-form__row">
		<label class="ps-form__label">
			<?php echo __('Date', 'peepso-core'); ?>
		</label>
		<div class="ps-form__field" style="display:flex">
			<select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-dd"></select>
			<select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-mm"></select>
			<select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-yy"></select>
		</div>
	</div>
	<div class="ps-form__row">
		<label class="ps-form__label">
			<?php echo __('Time', 'peepso-core'); ?>
		</label>
		<div class="ps-form__field" style="display:flex">
			<select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-hh"></select>
			<select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-mm" data-interval="<?php echo apply_filters('peepso_postbox_pin_interval_mm', 15); ?>"></select>
			<?php if ($ampm) { ?>
			<select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-ampm"></select>
			<?php } ?>
		</div>
	</div>
</form>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Pin This Post Until &hellip;', 'peepso-core'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'peepso-core'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Pin', 'peepso-core'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
