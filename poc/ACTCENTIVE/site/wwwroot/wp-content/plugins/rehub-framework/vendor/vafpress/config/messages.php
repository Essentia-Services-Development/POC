<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

return array(

	////////////////////////////////////////
	// Localized JS Message Configuration //
	////////////////////////////////////////

	/**
	 * Validation Messages
	 */
	'validation' => array(
		'alphabet'     => esc_html__('Value needs to be Alphabet', 'rehub-framework'),
		'alphanumeric' => esc_html__('Value needs to be Alphanumeric', 'rehub-framework'),
		'numeric'      => esc_html__('Value needs to be Numeric', 'rehub-framework'),
		'email'        => esc_html__('Value needs to be Valid Email', 'rehub-framework'),
		'url'          => esc_html__('Value needs to be Valid URL', 'rehub-framework'),
		'maxlength'    => esc_html__('Length needs to be less than {0} characters', 'rehub-framework'),
		'minlength'    => esc_html__('Length needs to be more than {0} characters', 'rehub-framework'),
		'maxselected'  => esc_html__('Select no more than {0} items', 'rehub-framework'),
		'minselected'  => esc_html__('Select at least {0} items', 'rehub-framework'),
		'required'     => esc_html__('This is required', 'rehub-framework'),
	),

	/**
	 * Import / Export Messages
	 */
	'util' => array(
		'import_success'    => esc_html__('Import succeed, option page will be refreshed..', 'rehub-framework'),
		'import_failed'     => esc_html__('Import failed', 'rehub-framework'),
		'export_success'    => esc_html__('Export succeed, copy the JSON formatted options', 'rehub-framework'),
		'export_failed'     => esc_html__('Export failed', 'rehub-framework'),
		'restore_success'   => esc_html__('Restoration succeed, option page will be refreshed..', 'rehub-framework'),
		'restore_nochanges' => esc_html__('Options identical to default', 'rehub-framework'),
		'restore_failed'    => esc_html__('Restoration failed', 'rehub-framework'),
	),

	/**
	 * Control Fields String
	 */
	'control' => array(
		// select2 select box
		'select2_placeholder' => esc_html__('Select option(s)', 'rehub-framework'),
		// fontawesome chooser
		'fac_placeholder'     => esc_html__('Select an Icon', 'rehub-framework'),
	),

);

/**
 * EOF
 */