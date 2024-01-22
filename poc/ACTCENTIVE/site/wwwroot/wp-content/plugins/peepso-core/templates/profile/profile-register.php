<?php
if( count($fields) ) {
	foreach ($fields as $key => $field) {
		$field_can_edit = !isset($field::$user_disable_edit);
		if(1 == $field->prop('meta', 'user_registration')) {
			$half_width = (1 == $field->prop('meta', 'half_width')) ? " ps-form__row--half" : "";
		?>
			<div class="ps-form__row<?php echo $half_width;?>">
				<?php
				if(!isset($field::$user_hide_title)) :
				?>
				<label id="peepso_user_field_first_namemsg" for="peepso_user_field_first_name" class="ps-form__label"><?php echo __($field->title, 'peepso-core');

					if( 1 == $field->prop('meta','validation','required' ))
					{
					 	echo "<span class=\"ps-form__required\">&nbsp;*</span>";
					}
					?></label>
				<?php endif; ?>
				<div class="ps-form__field">
					<?php
					if (TRUE == $field_can_edit) :
						//$field->render_validation();
                        do_action('peepso_action_render_profile_field_edit_before', $field);
						$field->render_input();
					endif;
					?>
					<div class="ps-form__field-desc lbl-descript"><?php $field->render(); ?></div>
					<?php

					// validation goes here
					$errors = count($field->validation_errors);
					echo '<div class="ps-form__error"' . ($errors > 0 ? '' : ' style="display:none"') . '>';
					if( $errors > 0 ) {
						foreach ($field->validation_errors as $key => $value) {
							echo '<div class="ps-form__error-item">' . $value . '</div>';
						}
					}
					echo '</div>';

					?>
				</div>
			</div>
		<?php
		}
	}
}

// EOF
