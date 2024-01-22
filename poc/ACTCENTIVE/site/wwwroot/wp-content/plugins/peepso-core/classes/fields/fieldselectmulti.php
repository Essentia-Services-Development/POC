<?php

class PeepSoFieldSelectMulti extends PeepSoFieldSelectSingle {

    public static $order = 300;
	public static $admin_label='Select - Multiple';

	public $data_type = 'array';

	public function __construct($post, $user_id)
	{
		$this->field_meta_keys = array_merge($this->field_meta_keys, $this->field_meta_keys_extra);
		parent::__construct($post, $user_id);

		$this->render_form_methods = array(
			'_render_form_checklist' => __('checklist', 'peepso-core'),
		);

		if(!is_array($this->value)) {
			$this->value = array();
		}
		$this->validation_methods[] = 'countmin';
		$this->validation_methods[] = 'countmax';

		$this->el_class = '';

		$this->default_desc = __('Select as many as you like.','peepso-core');
	}

	// Renderers

	protected function _render($echo = false)
	{
		$options = $this->get_options();

		if (!is_countable($this->value) || !count($this->value) || ($this->is_registration_page)) {
			return $this->_render_empty_fallback();
		}

		if(!count($options)) {
			return FALSE;
		}

		ob_start();

		foreach ($options as $k => $v) {

			if (is_array($this->value) && in_array($k, $this->value)) {
				$option = '<span id="%1$s" class="ps-profile__field-%1$s">%2$s</span>';

				echo sprintf($option, $k, $v);
			}
		}

		return "<div class='ps-list ps-list--dots'>" . ob_get_clean() . "</div>";

	}

	protected function _render_input_checklist_args()
	{
		ob_start();

		echo ' name="'.$this->input_args['name'].'"',
			' id="'.$this->input_args['id'].'"',
			' data-id="'.$this->id.'"';

		return ob_get_clean();
	}

	protected function _render_input_checklist_register_args()
	{
		ob_start();

		echo ' name="'.$this->input_args['name'].'"',
			' data-id="'.$this->id.'"';

		if (!empty($this->el_class )) {
			echo ' class="'.$this->el_class.'"';
		}

		return ob_get_clean();
	}

	protected function _render_form_checklist()
	{
		$options = $this->get_options();

		if(!count($options)) {
			return FALSE;
		}

		ob_start();

		foreach ($options as $k => $v) {

			$checked = '';

			if (is_array($this->value) && in_array($k, $this->value)) {
				$checked = 'checked';
			}

			$option = '<div class="ps-checkbox"><input class="ps-checkbox__input" id="%4$s-%1$s" name="%4$s" type="checkbox" %3$s value="%1$s" ' . $this->_render_input_checklist_args() . ' /> <label class="ps-checkbox__label" for="%4$s-%1$s">%2$s</label></div>';

			echo sprintf($option, $k, $v, $checked, 'profile_field_' . $this->id);
		}

		$ret = ob_get_clean();
		return $ret;
	}

	protected function _render_form_checklist_register()
	{
		$options = $this->get_options();

		if(!count($options)) {
			return FALSE;
		}

		ob_start();

		foreach ($options as $k => $v) {

			$checked = '';

			if (is_array($this->value) && in_array($k, $this->value)) {
				$checked = 'checked';
			}

			// in registration page set name to `name[]` so we can get as an array
			$option = '<div class="ps-checkbox"><input class="ps-checkbox__input" name="%4$s[]" type="checkbox" %3$s value="%1$s" id="%1$s" ' . $this->_render_input_checklist_register_args(). ' /> <label class="ps-checkbox__label" for="%1$s">%2$s</label></div>';

			echo sprintf($option, $k, $v, $checked, 'profile_field_' . $this->id);
		}

		$ret = ob_get_clean();
		return $ret;
	}
}
