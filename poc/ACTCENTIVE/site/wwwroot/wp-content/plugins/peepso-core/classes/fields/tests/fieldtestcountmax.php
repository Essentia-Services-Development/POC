<?php

class PeepSoFieldTestCountmax extends PeepSoFieldTestAbstract
{

	public function __construct($value, $args)
	{
		parent::__construct($value, $args);
		$this->admin_label				= __('Require a selection of maximum', 'peepso-core');

		$this->admin_value				= 'int';
		$this->admin_value_label_after 	= __('option(s)', 'peepso-core');

		$this->message 					= __('You cannot select more than %s option(s)', 'peepso-core');
	}

	public function test()
	{
		if( count($this->value) > $this->args) {

			$this->error = sprintf( $this->message, $this->args);

			return FALSE;
		}

		return TRUE;
	}
}