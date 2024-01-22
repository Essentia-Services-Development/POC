<?php

class PeepSoFieldTextEmail extends PeepSoFieldText
{
    public static $order = 700;
	public static $admin_label='Email';

	public function __construct($post, $user_id)
	{
		parent::__construct($post, $user_id);

        // Remove inherited text area / multiline and Markdown rendering
		unset($this->render_form_methods['_render_form_textarea']);
        unset($this->render_methods['_render_md']);

		// Add an option to render as <a href>
		$this->render_methods['_render_link'] = __('clickable link','peepso-core');

		// Remove inherited length validators
		$this->validation_methods = array_diff($this->validation_methods, array('lengthmax','lengthmin'));
		$this->validation_methods[] = 'patternemail';

		$this->default_desc = __('What\'s the email address?','peepso-core');
	}

	protected function _render_link()
	{
		if(empty($this->value)) {
			return $this->_render_empty_fallback();
		}

		return '<a href="mailto:' . $this->value . '" target="_blank">' . $this->value . '</a>';
	}

}