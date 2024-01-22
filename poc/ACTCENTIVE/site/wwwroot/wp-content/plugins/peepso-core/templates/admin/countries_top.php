<?php

$params = array(
	'type' => 'text',
	'data' => array(
		'data-prop-type' => 'meta',
		'data-prop-name' => 'countries_top',
        'value' => $field->prop('meta', 'countries_top'),
		'admin_value' => $field->prop('meta', 'countries_top'),
		'id' => 'field-' . $field->prop('id') . '-countries-top',
	),
	'field' => $field,
	'label' => __('Put these countries on top', 'peepso-core'),
	'label_after' => sprintf(__('For example: US, GB, DE, based on %s','peepso-core'), '<a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank">ISO-3166-1 alpha-2 <i class="fa fa-external-link"></i></a>')
);


PeepSoTemplate::exec_template('admin', 'profiles_field_config_field', $params);


$params = array(
    'type' => 'text',
    'data' => array(
        'data-prop-type' => 'meta',
        'data-prop-name' => 'countries_exclude',
        'value' => $field->prop('meta', 'countries_exclude'),
        'admin_value' => $field->prop('meta', 'countries_exclude'),
        'id' => 'field-' . $field->prop('id') . '-countries-exclude',
    ),
    'field' => $field,
    'label' => __('Do not show these countries', 'peepso-core'),
    'label_after' => ''
);


PeepSoTemplate::exec_template('admin', 'profiles_field_config_field', $params);
