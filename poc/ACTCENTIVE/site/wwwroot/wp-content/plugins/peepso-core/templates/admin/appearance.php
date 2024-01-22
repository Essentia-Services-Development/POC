<?php

if(!in_array($field->prop('key'), array('peepso_user_field_first_name','peepso_user_field_last_name'))) {

	$params = array(
		'type' => 'checkbox',
		'data' => array(
			'data-prop-type' => 'meta',
			'data-prop-name' => 'user_on_cover',
			'data-disabled-value' => '0',
			'value' => '1',
			'admin_value' => $field->prop('meta', 'user_on_cover'),
			'id' => 'field-' . $field->prop('id') . '-user-on-cover',
		),
		'field' => $field,
		'label' => __('Show on profile cover', 'peepso-core'),
		'label_after' => '',
	);


	if (1 == $field->prop('meta', 'user_on_cover')) {
		$params['data']['checked'] = 'checked';
	}

	#PeepSoTemplate::exec_template('admin', 'profiles_field_config_field', $params);
}