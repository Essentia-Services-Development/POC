<form id="form_ban_user" class="ps-form--ban">
	<div class="ps-form__row">
		<div class="ps-form__label">
			<div class="ps-checkbox">
				<div class="ps-checkbox ps-checkbox--radio">
					<input class="ps-checkbox__input" type="radio" name="ban_type" id="ban-period" value="ban_period" checked="checked">
					<label class="ps-checkbox__label" for="ban-period"><?php echo __('Ban until', 'peepso-core'); ?></label>
				</div>
			</div>
		</div>
		<div class="ps-form__field">
			<input type="text" class="ps-input ps-input--sm" width="auto" name="ban_period_date"
				data-date-range-min="0" data-date-range-max="100" data-value="<?php echo $start_date; ?>"
				value="<?php echo $start_date_str; ?>" readonly="readonly">
			<div id="ban-period-empty" class="ps-text--danger ps-form__helper" style="display:none"><?php echo __('Please fill in the date', 'peepso-core'); ?></div>
		</div>
	</div>
	<div class="ps-form__row">
		<div class="ps-form__label ps-full">
			<div class="ps-checkbox ps-checkbox--radio">
				<input class="ps-checkbox__input" type="radio" name="ban_type" id="ban-forever" value="ban_forever">
				<label class="ps-checkbox__label" for="ban-forever"><?php echo __('Ban indefinitely', 'peepso-core'); ?></label>
			</div>
		</div>
	</div>
</form>
