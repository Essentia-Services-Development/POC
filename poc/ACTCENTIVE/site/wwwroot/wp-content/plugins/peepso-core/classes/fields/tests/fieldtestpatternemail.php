<?php

class PeepSoFieldTestPatternEmail extends PeepSoFieldTestAbstract
{

	public function __construct($value)
	{
		parent::__construct($value);

		$this->admin_label = __('Force valid email addresses', 'peepso-core');
		$this->admin_type = 'checkbox';
	}

	public function test()
	{

		if ( strlen($this->value) && FALSE === filter_var($this->value, FILTER_VALIDATE_EMAIL) ) {

			$this->error = __('Must be a valid eMail address', 'peepso-core');

			return FALSE;
		}

		return TRUE;
	}

}