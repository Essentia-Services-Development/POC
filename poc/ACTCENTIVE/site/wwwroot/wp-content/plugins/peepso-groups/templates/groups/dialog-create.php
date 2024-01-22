<div class="ps-group__create">
	<div class="ps-form ps-form--vertical ps-form--group-create">
		<div class="ps-form__row">
			<label class="ps-form__label"><?php echo __('Name', 'groupso'); ?> <span class="ps-text--danger">*</span></label>
			<div class="ps-form__field ps-form__field--limit">
				<div class="ps-input__wrapper">
					<input type="text" name="group_name" class="ps-input ps-input--sm ps-input--count ps-js-name-input" value=""
							placeholder="<?php echo __("Enter your group's name...", 'groupso'); ?>" data-maxlength="<?php echo PeepSoGroup::$validation['name']['maxlength'];?>" />
					<div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline"><?php echo PeepSoGroup::$validation['name']['maxlength'];?></span></div>
				</div>
				<div class="ps-form__field-desc ps-form__required ps-js-error-name" style="display:none"></div>
			</div>
		</div>

		<div class="ps-form__row">
			<label class="ps-form__label"><?php echo __('Description', 'groupso'); ?> <span class="ps-text--danger">*</span></label>
			<div class="ps-form__field ps-form__field--limit">
				<div class="ps-input__wrapper">
					<textarea name="group_desc" class="ps-input ps-input--sm ps-input--textarea ps-input--count ps-js-desc-input"
								placeholder="<?php echo __("Enter your group's description...", 'groupso'); ?>" data-maxlength="<?php echo PeepSoGroup::$validation['description']['maxlength'];?>"></textarea>
					<div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline" aria-label="<?php echo __('Characters left', 'groupso'); ?>"><?php echo PeepSoGroup::$validation['description']['maxlength'];?></span></div>
				</div>
				<div class="ps-form__field-desc ps-form__required ps-js-error-desc" style="display:none"></div>
			</div>
		</div>

		<?php do_action('peepso_action_render_group_create_form_before'); ?>

		<?php

		if(PeepSo::get_option('groups_categories_enabled', FALSE)) {

			$multiple_enabled = (PeepSo::get_option_new('groups_categories_multiple_max') > 1);
			$input_type = ($multiple_enabled) ?  'checkbox' : 'radio';

			$PeepSoGroupCategories = new PeepSoGroupCategories(FALSE, TRUE);
			$categories = $PeepSoGroupCategories->categories;

			if (count($categories)) {

			?>
			<div class="ps-form__row">
				<label class="ps-form__label"><?php echo __('Category', 'groupso'); ?> <span class="ps-text--danger">*</span></label>
				<div class="ps-form__field">
					<div class="ps-checkbox__grid">
						<?php
							foreach($categories as $id=>$category) {
								echo sprintf('<div class="ps-checkbox"><input type="%s" id="category_'.$id.'" name="category_id" value="%d" class="ps-checkbox__input"><label class="ps-checkbox__label" for="category_'.$id.'">%s</label></div>', $input_type, $id, $category->name);
							}
						?>
					</div>
					<div class="ps-form__field-desc ps-form__required ps-js-error-category_id" style="display:none"></div>
				</div>
			</div>
		<?php

			}
		} // groups_categories_enabled

		?>

		<?php do_action('peepso_action_render_group_create_form_after'); ?>

		<div class="ps-form__row">
			<label class="ps-form__label"><?php echo __('Privacy', 'groupso'); ?></label>
			<div class="ps-form__field">
				<?php
				$privacySettings = PeepSoGroupPrivacy::_();
                $privacyDefaultSetting = PeepSoGroupPrivacy::_default();
				$privacyDefaultValue = $privacyDefaultSetting['id'];

				?>
				<div class="ps-dropdown ps-dropdown--privacy ps-group__profile-privacy ps-js-dropdown ps-js-dropdown--privacy">
					<button data-value="" class="ps-btn ps-btn--sm ps-dropdown__toggle ps-js-dropdown-toggle">
						<span class="dropdown-value">
							<i class="<?php echo $privacyDefaultSetting['icon']; ?>"></i>
							<span><?php echo $privacyDefaultSetting['name']; ?></span>
						</span>
					</button>
					<input type="hidden" name="group_privacy" value="<?php echo $privacyDefaultValue; ?>" />
					<?php echo PeepSoGroupPrivacy::render_dropdown(); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php

// Additional popup options (optional).
$opts = array(
	'title' => __('Create Group', 'groupso'),
	'actions' => array(
		array(
			'label' => __('Cancel', 'groupso'),
			'class' => 'ps-js-cancel'
		),
		array(
			'label' => __('Create Group', 'groupso'),
			'class' => 'ps-js-submit',
			'loading' => true,
			'primary' => true
		)
	)
);

?>
<script type="text/template" data-name="opts"><?php echo json_encode($opts); ?></script>
