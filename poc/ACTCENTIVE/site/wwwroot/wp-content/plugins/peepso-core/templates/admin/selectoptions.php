<?php
//$options = $field->prop('meta','select_options');
$options = $field->get_options();
?>

<h3 class="ps-settings__title"><?php echo __('Select Options');?></h3>
<div style="position:relative">
	<div class="ps-js-options">
		<a href="#" class="ps-js-focusguard" data-tag="first"></a>
	<?php
	if(is_array($options) && count($options)) {

		foreach($options as $k => $v) {
			ob_start();
			?>

			<!-- DRAG & DELETE HTML -->
			<div class="ps-settings__drag ps-js-option-handle">
				<i class="fa fa-arrows"></i>

				<?php

                if(is_string($k) && in_array($k, ['option_0','option_1'])) {
                    // Special case: PeepSo Yes/No
                    echo "<small>";
                    echo ('option_0'==$k) ? __('No','peepso-core') : __('Yes','peepso-core');
                    echo "</small>";
                }elseif(is_string($k) && in_array($k,['m','f'])) {
                    // Special case: PeepSo Gender
					echo "<small>";
					echo __('Default PeepSo Gender:', 'peepso-core');
					echo ' ' ;
					echo ('m' == $k) ? __('male','peepso-core') : __('female','peepso-core');
					echo "</small>";
				}
				// EOF Special case: PeepSo Gender
				?>
			</div>

			<!-- EOF DRAG & DELETE HTML -->
			<?php
			if(!in_array($k,array('m','f'))) {
				$delete_option = '<a class="ps-btn ps-input__icon ps-js-option-delete" title="'. __('Delete') .'" href="#" tabindex="-1"><i class="fa fa-trash"></i></a>';
			} else {
				$delete_option = '';
			}

			if(!$field->admin_can_add_delete_options) {
                $delete_option = '';
            }

			$label = ob_get_clean();

			$params = array(
				'type'			=> 'text',
				'data'			=> array(
					'data-prop-type' 		=> 'meta',
					'data-prop-name' 		=> 'select_options',
					'data-prop-key'			=> $k,
					'class'					=> 'ps-input',
					'value'					=> $v,
					'id'					=> 'field-' . $k .'-title',
				),
				'field'			=> $field,
				'label'			=> $label,
				'label_after'	=> $delete_option,
			);

			PeepSoTemplate::exec_template('admin','profiles_field_config_field', $params);
		 }

	}

	?>

	<div class="ps-js-option-template" style="display:none"><?php


	$delete_option = '<a class="ps-btn ps-input__icon ps-js-option-delete" title="'. __('Delete') .'" href="#" tabindex="-1"><i class="fa fa-trash"></i></a>';

	$label = '<div class="ps-settings__drag">'
	       . '<i class="fa fa-arrows ps-js-option-handle"></i>'
	       . '</div>';

	$params = array(
		'type'			=> 'text',
		'data'			=> array(
			'data-prop-type'		=> 'meta',
			'data-prop-name'		=> 'select_options',
			'data-prop-key'			=> '___key___',
			'class'					=> 'ps-input',
			'value'					=> '___val___',
			'id'					=> 'field-' . '___key___' .'-title',
		),
		'field'			=> $field,
		'label'			=> $label,
		'label_after'	=> $delete_option,
	);

	PeepSoTemplate::exec_template('admin','profiles_field_config_field', $params);

	?></div>
		<a href="#" class="ps-js-focusguard" data-tag="last"></a>
	</div>
    <?php if($field->admin_can_add_delete_options) { ?>
	<button class="button ps-js-option-new"><?php echo __('Add new option', 'peepso-core'); ?></button>
    <?php } ?>
	<div class="ps-settings__loading ps-js-loading"></div>
</div>
