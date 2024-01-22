<?php

$params = array(
	'type' => 'checkbox',
	'data' => array(
		'data-prop-type' => 'meta',
		'data-prop-name' => 'user_nofollow',
		'data-disabled-value' => '0',
		'value' => '1',
		'admin_value' => $field->prop('meta', 'user_nofollow'),
		'id' => 'field-' . $field->prop('id') . '-user-nofollow',
	),
	'field' => $field,
	'label' => __('Add "nofollow"', 'peepso-core'),
	'label_after' => '',
);


if (1 == $field->prop('meta', 'user_nofollow')) {
	$params['data']['checked'] = 'checked';
}

PeepSoTemplate::exec_template('admin', 'profiles_field_config_field', $params);