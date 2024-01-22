<?php

class PeepSoFieldTextDate extends PeepSoFieldText {

    public static $order = 400;
	public static $admin_label='Date';

    public $date_range_min = 100;
    public $date_range_max = 0;

	public function __construct($post, $user_id)
	{
        $this->field_meta_keys = array_merge($this->field_meta_keys, array('date_range_min','date_range_max'));
		parent::__construct($post, $user_id);

		$this->render_form_methods['_render_form_input'] = __('date picker', 'peepso-core');

		// Remove inherited text area / multiline and Markdown rendering
		unset($this->render_form_methods['_render_form_textarea']);
		unset($this->render_methods['_render_md']);

		// Add an option to render as a relative date
		$this->render_methods['_render'] = __('date (WordPress format)', 'peepso-core');
		$this->render_methods['_render_relative'] = __('relative - time passed (ie 1 month, 5 years)', 'peepso-core');
		$this->render_methods['_render_relative_age'] = __('relative - age (ie 25 years old)', 'peepso-core');

		// Remove inherited length validators
		$this->validation_methods = array_diff($this->validation_methods, array('lengthmax','lengthmin'));

		$this->default_desc = __('When did it happen?', 'peepso-core');
	}

	protected function _render()
	{
		if(empty($this->value) || ($this->is_registration_page)) {
			return $this->_render_empty_fallback();
		}

		$format = get_option('date_format');

		if(2==$this->prop('meta','is_core')) {

		    if(PeepSoUser::get_instance($this->prop('user_id'))->get_hide_birthday_year()) {
		        $format = PeepSo::get_option('date_format_no_year', 'F j');
		        if('custom' == $format) {
		            $format = PeepSo::get_option('date_format_no_year_custom', 'F j');
                }
            }
        }

        $ret = date_i18n($format, strtotime($this->value));

		return $ret;
	}

	protected function _render_relative($suffix=TRUE)
	{
		if(empty($this->value)) {
			return $this->_render_empty_fallback();
		}

		#$render_args = $this->meta->type->render;

		// Grab rounding settings if defined (floor() by default)
		#$round = (isset($render_args->round)) ? $render_args->round : "floor";

		// Run against current date
		$now = date('U', current_time('timestamp', 0));
		$ret = human_time_diff_round_alt(strtotime($this->value), $now);


		$future_or_past = __('ago', 'peepso-core');

		if(strtotime($this->value) > $now) {
		    $future_or_past = __('from now', 'peepso-core');
        }

        if(FALSE == $suffix)  {
		    return $ret;
        }

		return $ret .' ' . $future_or_past;
	}

	protected function _render_relative_age()
	{
		if(empty($this->value)) {
			return $this->_render_empty_fallback();
		}

		$ret =  sprintf(__('%s old', 'peepso-core'), $this->_render_relative(FALSE));
		return $ret;
	}

	protected function _render_form_input( )
	{
		wp_enqueue_style('peepso-datepicker');
		wp_enqueue_script('peepso-datepicker');

		$val = '';

		if(!empty($this->value)) {
			$val = date_i18n(get_option('date_format'), strtotime($this->value));
		}

		$ret  = '<div class="ps-input__wrapper ps-datepicker">';

        $date_range_min = is_numeric($this->prop('meta','date_range_min')) ? $this->prop('meta','date_range_min') : 100;
        $date_range_max = is_numeric($this->prop('meta','date_range_max')) ? $this->prop('meta','date_range_max') : 0;

		// Datepicker input
		$ret .= '<input data-date-range-min="'.$date_range_min.'" data-date-range-max="'.$date_range_max.'" type="text" class="ps-input ps-input--sm datepicker" value="' . $val . '"' . $this->_render_input_args()
		      . ' title="' . __('Set date', 'peepso-core') . '" readonly="readonly"'
		      . ' data-value="' . $this->value . '" readonly="readonly">';

		// Datepicker toggle button
		$ret .= '<button class="ps-btn ps-btn--sm ps-btn--app" type="button"'
		      . ' title="' . __('Toggle datepicker', 'peepso-core') . '"><i class="gcis gci-calendar-alt"></i></button>';
		$ret .= '</div>';

		return $ret;
	}

	protected function _render_form_input_register( )
	{
		wp_enqueue_style('peepso-datepicker');
		wp_enqueue_script('peepso-datepicker');

		$val = '';

		if(!empty($this->value)) {
			$val = date_i18n(get_option('date_format'), strtotime($this->value));
		}

		$ret  = '<div style="position:relative">';

		$date_range_min = is_numeric($this->prop('meta','date_range_min')) ? $this->prop('meta','date_range_min') : 100;
        $date_range_max = is_numeric($this->prop('meta','date_range_max')) ? $this->prop('meta','date_range_max') : 0;

		// Datepicker input
		$ret .= '<input data-date-range-min="'.$date_range_min.'" data-date-range-max="'.$date_range_max.'" type="text" class="'.$this->el_class.' datepicker" value="' . $val . '"' . $this->_render_input_register_args()
		      . ' title="' . __('Set date', 'peepso-core') . '" readonly="readonly"'
		      . ' data-value="' . $this->value . '" readonly="readonly">';

		// Datepicker toggle button
		$ret .= '<button class="ps-btn ps-btn-small ps-icon-calendar" type="button"'
		      . ' title="' . __('Toggle datepicker', 'peepso-core') . '"'
		      . ' style="position:absolute; top:3px; right:3px; bottom:3px"></button>';

		$ret .= '</div>';

		return $ret;
	}
}
