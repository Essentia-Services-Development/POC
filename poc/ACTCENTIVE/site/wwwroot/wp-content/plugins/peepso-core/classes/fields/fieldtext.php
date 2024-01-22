<?php

class PeepSoFieldText extends PeepSoField {

    public static $order = 100;
	public static $admin_label='Text';

	public function __construct($post, $user_id)
	{
		parent::__construct($post, $user_id);

		$render_form_methods = array(
			'_render_form_input' => __('input (single line)', 'peepso-core'),
            '_render_form_textarea' => __('textarea (multiple lines)', 'peepso-core'),
		);

		$this->render_form_methods = array_merge( $this->render_form_methods, $render_form_methods );

		$render_methods = array(
		    '_render_md' => __('Markdown formatted')
        );

		$this->render_methods = array_merge( $this->render_methods,$render_methods);

		$validation_methods = array(
			'lengthmin',
			'lengthmax',
		);

		$this->validation_methods = array_merge( $this->validation_methods, $validation_methods);

		$this->default_desc = __('Tell us about it.', 'peepso-core');
	}

	protected function _render()
    {
        if(strlen($this->value) == 0 || ($this->is_registration_page)) {
            return $this->_render_empty_fallback();
        }

        return (strstr($this->value, PHP_EOL)) ? wpautop($this->value) : $this->value;
    }

    protected function _render_form_textarea()
    {
        $ret  = '<div class="ps-input__wrapper">';
        $ret .= '<textarea class="ps-input ps-input--sm ps-input--count ps-input--textarea" '.$this->_render_input_args() . $this->_render_required_args() . '>' . $this->value . '</textarea>';
        $ret .= '<div class="ps-form__chars-count ps-js-counter" style="display:none"></div>';
        $ret .= '</div>';

        return $ret;
    }

    protected function _render_md() {
        if(empty($this->value) || ($this->is_registration_page)) {
            return $this->_render_empty_fallback();
        }

        return '<div class="peepso-markdown">'.$this->value.' </div>';
    }


	protected function _render_form_textarea_register()
	{
		$this->el_class = 'ps-input--textarea';

		$ret  = '<div class="ps-input__wrapper">';
		$ret .= '<textarea class="ps-input ps-input--count ps-input--textarea" '.$this->_render_input_register_args() . $this->_render_required_args() . '>' . $this->value . '</textarea>';
		$ret .= '<div class="ps-form__chars-count ps-js-counter" style="display:none"></div>';
		$ret .= '</div>';

		return $ret;
	}
}
