<?php

class PeepSoForm
{
	private $_access = FALSE;

	public $fieldcontainer = array();
	public $fields = array();
	public $error_messages = array();
	public $args = array();

	// list of allowed template tags
	public $template_tags = array(
			'render',			// output the form
	);

	// @todo this does not need to be a singleton
	public static function get_instance()
	{
		return (new self());
	}

	// @todo constructor first
	public function __construct($args = array())
	{
		// Set default args
		$this->args = array_merge(
				array (
						'name' => '',
						'action' => '',
						'method' => 'post',
						'class' => '',
						'extra' => ''
				),
				$args);
	}

	/**
	 * Generate an open form tag with hidden field for form nonce
	 * @return string generated HTML for form open tag
	 */
	public function form_open()
	{
		$form_open = '';
		// output the form tag
		$form_open .= '<form name="' . $this->args['name'] . '" id="' . $this->args['name'] . '" ';
		$form_open .= ' action="' . $this->args['action'] . '" ';
		$form_open .= ' method="' . $this->args['method'] . '" ';
		if (!empty($this->args['class']))
			$form_open .= ' class="ps-form ' . $this->args['class'] . '" ';
		if (!empty($this->args['extra']))
			$form_open .= ' ' . $this->args['extra'] . ' ';
		$form_open .= '>';

		// TODO: change to 'peepso-form-nonce' - not use just for config form
		$form_open .= '<input type="hidden" name="peepso-config-nonce" value="' . wp_create_nonce('peepso-config-nonce') . '"/>';

		return ($form_open);
	}

	/**
	 * Returns close form tag
	 * @return string Form close tag
	 */
	public function form_close()
	{
		// TODO: need to supply a filter value and a reference to give filter calbacks some context, i.e.: apply_filters('peepso_render_form_close', '', $this);
		// - callbacks can use $this->args['name'] or $this->args['action'] to give context
		$form_close = apply_filters('peepso_render_form_close', '', $this);
		$form_close .= '</form>';
		return ($form_close);
	}

	/*
	 * renders a form with the given fields
	 * @param array $form The definition of a form, including containers, submit data, etc.
	 */
	public function render($form)
	{
		if (isset($form['form']))
			$this->args = $form['form'];

		$container = isset($form['container']) ? $form['container'] : array();
		$this->fieldcontainer = isset($form['fieldcontainer']) ? $form['fieldcontainer'] : $this->fieldcontainer;
		$fields = $form['fields'];
		$this->add_fields($fields);

		if ('POST' === $_SERVER['REQUEST_METHOD']) {
			$this->map_request();
			$this->validate();
		}

		// output the form tag
		echo $this->form_open();

		// output container data
		$this->_render_element($container);

		$this->render_fields($this->fields);

		// output closing container
		$this->_render_close_element($container);

		// close the form
		// TODO: we want to keep this as a function. It could need to be callable from outside and we might want to add a filter
		echo $this->form_close();

		// if there was an access dropdown rendered, make sure the javascript is enqueued
		if ($this->_access)
			wp_enqueue_script('peepso-access-dropdown');
	}

	/*
	 * return text label for access codes
	 */
	private function _access_info( $page = '' )
	{
		$privacy = PeepSoPrivacy::get_instance();

		if( 'profile' == $page) {
			return ($privacy->get_access_settings_profile());
		}

		return ($privacy->get_access_settings());
	}

	/**
	 * Echoes fields provided
	 * @param  array $fields The fields to be displayed
	 * @return void Echoes the fields
	 */
	public function render_fields($fields)
	{
		foreach ($fields as $name => $field) {
			if(!isset($field['type']) && !isset($field['label'])) {
				continue;
			}

			// output header/section
			if ('section' == $field['type']) {
				echo '<div class="ps-form__legend">', $field['label'], '</div>', PHP_EOL;

				continue;
			}

			$classes = array_merge(array('ps-form__label'), explode(' ', isset($field['class']) ? $field['class'] : ''));

			if ('hidden' === $field['type']) {
				echo '<input type="hidden" name="', $name, '" value="', esc_attr($field['value']), '" />', PHP_EOL;
			} else if ('title' === $field['type']) {
				echo '<div class="ps-form__legend">', $field['label'], '</div>';
			} else if ('extended_fields' === $field['type']) {
				// additional fields
				do_action('peepso_register_extended_fields');
			} else {
				// output field container
				if ('submit' === $field['type']) {
					// if it's the submit <li> wrapper, add the 'submitel' class to the <li>
					$tmpcontainer = $this->fieldcontainer;
					$tmpcontainer['class'] .= ' ps-form__row--submit';
					$this->_render_element($tmpcontainer);
				} else {
                    $tmpcontainer = $this->fieldcontainer;
                    if(isset($field['row_wrapper_class'])) {
                        $tmpcontainer['class'].=' '.$field['row_wrapper_class'];
                    }
                    $this->_render_element($tmpcontainer);
                }

				// if there's a label and it's not a submit/message type, output the label
				if (isset($field['label']) && !empty($field['label']) && !in_array($field['type'], array('submit', 'message'))) {
					echo '<label id="', $name, 'msg" for="', $name, '" class="', implode(' ', $classes), '">', PHP_EOL;

					echo $field['label'];
					if ($this->_is_required($field))
						echo '<span class="ps-form__required">&nbsp;*</span>';
					if (isset($field['loading']) && $field['loading']) {
						echo ' <span class="ps-form__check ps-js-loading">',
						     '<img src="', PeepSo::get_asset('images/ajax-loader.gif'), '" />',
						     '<i class="gcis gci-check"></i></span>', PHP_EOL;
					}
					echo '</label>', PHP_EOL;
				}

				echo '<', $field['field_wrapper'], ' class="ps-form__field ', $field['field_wrapper_class'],'">';

				echo $this->_render_field_type($name, $field);
				if (!empty($field['validate']))
					echo '<span id="err', $name, '" style="display:none">&nbsp;</span>', PHP_EOL;

				echo '<div class="ps-form__error"', ($field['valid'] ? ' style="display:none"' : ''), '>';
				if (!$field['valid']) {
					foreach ($field['error_messages'] as $error) {
						echo '<div class="ps-form__error-item">', $error , '</div>';
					}
				}
				echo '</div>';

				$this->_render_access($name, $field);
				echo '</', $field['field_wrapper'], '>', PHP_EOL;

				if ('' !== $field['suffix'])
					echo '<div><span class="middle">', esc_html($field['suffix']), '</span></div>', PHP_EOL;
				if ('' !== $field['suffixhtml'])
					echo '<div><span class="middle">', $field['suffixhtml'], '</span></div>', PHP_EOL;

				$this->_render_close_element($this->fieldcontainer);
			}
		}
	}

	/*
	 * Output control for selecting access level to form field
	 */
	private function _render_access($name, $field)
	{
		if (isset($field['access']) && -1 !== intval($field['access'])) {
			$aAccess = $this->_access_info();
			$acc = intval($field['access']);
			if (!isset($aAccess[$acc])) {
				// access value not found in keys, assume value from first access entry
				$keys = array_keys($aAccess);
				$acc = $keys[0];
			}

			$this->_access = TRUE;
			echo '<div class="ps-form-privacy">', PHP_EOL;
			echo	'<div class="ps-privacy-dropdown ps-js-dropdown ps-js-dropdown--privacy">', PHP_EOL;
			echo		'<input type="hidden" name="', $name, '_acc" value="', $field['access'], '" />', PHP_EOL;
			echo		'<button id="acc-', $name, '" type="button" class="ps-btn ps-dropdown__toggle ps-js-dropdown-toggle">', PHP_EOL;
			echo			'<span class="dropdown-value"><i class="ps-icon-', $aAccess[$acc]['icon'], '"></i></span>', PHP_EOL;
			echo            '<span class="ps-privacy-title">', $aAccess[$acc]['label'], '</span>', PHP_EOL;
			echo		'</button>', PHP_EOL;
			echo		'<div class="ps-dropdown__menu ps-js-dropdown-menu" style="display:none">', PHP_EOL;

			foreach ($aAccess as $nAcc => $aAcc) {
				echo '<a id="', $name, '-acc-', $nAcc, '" href="#" data-option-value="', $nAcc, '" onclick="return false;">';
				echo '<i class="ps-icon-', $aAcc['icon'], '"></i>';
				echo '<span>', $aAcc['label'], '</span></a>', PHP_EOL;
			}

			echo		'</div>', PHP_EOL;
			echo	'</div>', PHP_EOL;
			echo '</div>', PHP_EOL;
		}
	}

	/*
	 * Used to render a single field, usually used when a form is rendered outside  of PeepSoForm::render()
	 * @param array $field The field data
	 */
	public function render_field($field)
	{
		$classes = array_merge(array('ps-form__label'), explode(' ', isset($field['class']) ? $field['class'] : ''));
		$sField = '';

		if('separator'==$field['type']) {
			return "<hr><h4 style='padding:0;margin:0;padding-left:15px;'>".$field['label']."<h4>";
		}

		if('message' == $field['type'] ) {
			return '<p style="color:gray;padding-left:15px;padding-right:15px;">'.$field['label'].'</p>';
		}

		if('warning' == $field['type'] ) {
			return '<p style="color:red;padding-left:15px;padding-right:15px;">'.$field['label'].'</p>';
		}

		if ('hidden' === $field['type'])
			$sField .= '<input type="hidden" name="' . $field['name'] . '" value="' . esc_attr($field['value']) . '" />';
		elseif ('title' === $field['type'])
			$sField .= '<h5>' . $field['label'] . '</h5>';
		else {
			if (!in_array($field['type'], array('submit', 'message','separator','header'))) {
				$sField .= '<label id="' . $field['name'] . 'msg" for="' . $field['name'] .
						'" class="' . implode(' ', $classes) . ' ' . $field['field_label_class'] . '">';
				$sField .= $field['label'];
				if (isset($field['validation']) && in_array('required', $field['validation']))
					$sField .= '<span class="ps-form__required">&nbsp;*</span>';
				$sField .= '</label>';
			}



			$sField .= '<' . $field['field_wrapper'] . ' class="form-field ' . $field['field_wrapper_class'] .'" ';
			// if it's a yes/no field, add the title on the wrapping <div>
			if ('yesno_switch' === $field['type'] && isset($field['descript']))
				$sField .= ' title="' . esc_attr($field['descript']) . '" ';
			$sField .= '>';
			$sField .= $field['prefix'];
			$sField .= $this->_render_field_type($field['name'], $field);

			if (!empty($field['validate']))
				$sField .= '<span id="err' . $field['name'] . '" style="display:none">&nbsp;</span>';

			if (!$field['valid']) {
				$sField .= '<div class="ps-form__error">';

				foreach ($field['error_messages'] as $error)
					$sField .= '<div class="ps-form__error-item">' . esc_html($error) . '</div>';

				$sField .= '</div>';
			}

			$this->_render_access($field['name'], $field);
			$sField .= '</' . $field['field_wrapper'] . '>';
			if ('' !== $field['suffix'])
				$sField .= '<div><span class="middle">' . esc_html($field['suffix']) . '</span></div>';
			if (isset($field['suffixonly']) && '' !== $field['suffixonly'])
				$sField .= $field['suffixonly'] . PHP_EOL;
		}

		return ($sField);
	}

	/*
	 * Renders the field, based on it's type
	 * @param string $name The name of the field in question
	 * @param array $field The field array data
	 */
	private function _render_field_type($name, $field)
	{
		$sField = '';
		$extra = isset($field['extra']) ? $field['extra'] : '';

		switch ($field['type'])
		{
			case 'html':
				$sField.=$field['html'];
				break;
            case 'text':
            case 'text_readonly':
			case 'email':
			    $readonly = '';
			    if($field['type'] == 'text_readonly') {
			        $field['type'] = 'text';
			        $readonly ='readonly';
                }
				$sField .= '<input '.$readonly.' type="' . $field['type'] . '" name="' . $name . '" id="' . $name . '" ';

				if (!empty($field['value']) || '0' === $field['value']) {
					$sField .= ' value="' . esc_attr($field['value']) . '" ';
				}

				$sField .= ' class="ps-input ' . $this->_field_classes($field) . '" ';

				if (!empty($field['descript'])) {
					$sField .= 'title="' . esc_attr($field['descript']) . '" ';
				}

            if(!empty($field['data'])) {
                $sField .= $this->_field_data($field);
            }

            if(!empty($field['maxlength'])) {
                $sField .= 'maxlength="' . $field['maxlength'] .'" ';
            }

            if(!empty($field['size'])) {
                $sField .= 'size="' . $field['size'] .'" ';
            }

				if ($this->_is_required($field)) {
					$sField .= ' required ';
				}

				$sField .= $extra . ' />';
				break;

			case 'password':
				$sField .= '<input type="password" name="' . $name . '" id="' . $name . '" ';
				$sField .= ' class="ps-input ' . $this->_field_classes($field) . '" ';
				if (!empty($field['descript']))
					$sField .= 'title="' . esc_attr($field['descript']) . '" ';

				if ($this->_is_required($field)) {
					$sField .= ' required ';
				}

				$sField .= $extra . ' />';
				$sField .= '<span id="err' . $name . '" style="display:none;">&nbsp;</span>';
				break;

			case 'datepicker':
				wp_enqueue_style('peepso-datepicker');
				wp_enqueue_script('peepso-datepicker');
				$value = $mon = $day = $year = '';
				$validation = new PeepSoFormValidate('date');

				if ($validation->validate($field['value'])) {
					$dateTime = new PeepSoDate($field['value']);
					$mon = $dateTime->DatePart('mon');
					$day = $dateTime->DatePart('mday');
					$year = $dateTime->DatePart('year');
					$value = $dateTime->ToString(get_option('date_format'));
				}

				if (is_null($value) || empty($value) || '0000-00-00' === $value)
					$input_value = '';
				else
					$input_value = date('Y-m-d', strtotime($value));

				$sField .= '<input type="hidden" id="' . $name . '-d" name="' . $name . '-d" value="' . $day . '" />';
				$sField .= '<input type="hidden" id="' . $name . '-m" name="' . $name . '-m" value="' . $mon . '" />';
				$sField .= '<input type="hidden" id="' . $name . '-y" name="' . $name . '-y" value="' . $year . '" />';
				$sField .= '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . $input_value . '"/>';
				$sField .= '<input type="text" data-input="' . $name . '"';
				if (!empty($value))
					$sField .= ' value="' . esc_attr($value) . '" ';
				$sField .= ' class="ps-input datepicker ' . $this->_field_classes($field) . '" ';
				if (!empty($field['descript']))
					$sField .= 'title="' . esc_attr($field['descript']) . '" ';
				$sField .= '/>';
				break;

			case 'textarea':
				$sField .= '<textarea id="' . $name . '" name="' . $name . '" ';
				$sField .= ' rows="'  . (isset($field['rows']) ? $field['rows'] : '5') . '"';
				$sField .= ' class="ps-textarea ' . $this->_field_classes($field) . '" ';
				if (!empty($field['descript']))
					$sField .= 'title="' . esc_attr($field['descript']) . '" ';

				if ($this->_is_required($field)) {
					$sField .= ' required ';
				}

				$sField .= $extra . ' >' . (!is_null($field['value']) ? stripslashes(esc_textarea($field['value'])) : '') . '</textarea>';
				$sField .= '<div class="validate-init" data-name="' . esc_attr($name) . '" data-length="1000" style="display:none"></div>';
				break;

			case 'select':
				$multiple = '';
				if (isset($field['multiple']) && $field['multiple']) {
					$multiple = 'multiple';
					$name .= '[]';
				}
				$sField .= '<select name="' . $name . '" id="' . $name . '" '  . $multiple;
				if (!empty($field['descript']))
					$sField .= ' title="' . esc_attr($field['descript']) . '" ';

				$sField .= ' class="ps-input ps-input--sm ps-input--select ' . $this->_field_classes($field) . '"';

				if ($this->_is_required($field)) {
					$sField .= ' required ';
				}

				$sField .= '>';

				$value = NULL;
				if (!is_null($field['value']))
					$value = $field['value'];

				foreach ($field['options'] as $val => $data) {
					$sField .= '<option value="' . $val . '"';
					// note: not using equality operator on purpose so integer index values are properly compared
					if ($val == $value || (is_array($value) && in_array($val, $value)))
						$sField .= ' selected';
					$sField .= '>' . $data . '</option>';
				}
				$sField .= '</select>';

				break;

			case 'inline-select':
				$multiple = '';
				if (isset($field['multiple']) && $field['multiple']) {
					$multiple = 'multiple';
					$name .= '[]';
				}
				$sField .= '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $field['value'] . '" />';
				$sField .= '<nav ';
				if (!empty($field['descript']))
					$sField .= ' title="' . esc_attr($field['descript']) . '" ';
				$sField .= ' class="ps-nav ps-nav--select ' . $this->_field_classes($field) . '">';
				$sField .= '<ul>';

				$value = NULL;
				if (!is_null($field['value']))
					$value = $field['value'];

				foreach ($field['options'] as $val => $data) {
					$sField .= '<li';
					// note: not using equality operator on purpose so integer index values are properly compared
					if ($val == $value || (is_array($value) && in_array($val, $value)))
						$selected = ' btn-primary';
					else
						$selected = '';
					$sField .= '><a href="#" class="inline-select' . $selected . '" data-value="' . $val . '">' . $data . '</a></li>';
				}
				$sField .= '</ul>';
				$sField .= '</nav>';

				break;

			case 'radio':
				foreach ($field['options'] as $val => $text) {
					$sField .= '<input type="radio" name="' . $name . '" value="' . $val . '" ';
					if ($val === $field['value'])
						$sField .= ' checked ';

					if (!empty($field['descript']))
						$sField .= 'title="' . esc_attr($field['descript']) . '" ';

					if ($this->_is_required($field)) {
						$sField .= ' required ';
					}

					$sField .= $extra . ' /> ' . $text . '&nbsp;';
				}
				break;

			case 'checkbox':
				$sField .= '<div class="ps-checkbox">';
				$sField .= '<input class="ps-checkbox__input" type="checkbox" name="' . $name . '" id="' . $name . '" ';
				if ('on' === $field['value'])
					$sField .= ' checked ';

				if (!empty($field['descript']))
					$sField .= 'title="' . esc_attr($field['descript']) . '" ';


				if ($this->_is_required($field)) {
					$sField .= ' required ';
				}

				$sField .= $extra . ' />';
				$sField .= '<label class="ps-checkbox__label" for="' . $name . '">';

				if (!empty($field['descript']))
					$sField .= esc_attr($field['descript']) . '';

				$sField .= '</label>';
				$sField .= '</div>';
				break;

			case 'yesno_switch':
				$sField .= '<div class="ps-checkbox ps-checkbox--switch">';
				$sField .= '<input name="' . $name . '" class="ps-checkbox__input ace ace-switch ace-switch-2" id="'. $name .'" type="checkbox" value="1" ';
				if ($field['value'])
					$sField .= 'checked ';
				$sField .= '/>';

				$sField .= '<label class="ps-checkbox__label" for="' . $name .'">';

				if (!empty($field['label-desc']))
							$sField .= esc_html($field['label-desc']);

				if (!isset($field['label']) || !$field['label']) {
					if (isset($field['loading']) && $field['loading'])
						$sField .= ' <span class="ps-form__check ps-js-loading">' .
						           '<img src="' . PeepSo::get_asset('images/ajax-loader.gif') . '" />' .
						           '<i class="gcis gci-check"></i></span>';
				}

				$sField .= '</label>';
				$sField .= '</div>';
				break;

			case 'submit':
				$sField .= '<button type="submit" name="' . $name . '" ';
				if (isset($field['click']))
					$sField .= ' onclick="' . $field['click'] . '" ';
				$sField .= ' class="ps-btn ' . $this->_field_classes($field) . '" value="' . $field['label'] . '" ';
				$sField .= $extra . '>';
				$sField .= $field['label'];
				$sField .= '<img src="' . PeepSo::get_asset('images/ajax-loader.gif') . '" style="margin-left:10px;display:none" />';
				$sField .= '</button>';
				break;

			case 'message':
				$sField .= '<div class="ps-form__message">' . $field['label'] . '</div>';
				break;

			case 'access':
			case 'access-profile':
			case 'access-profile-post':
				$aAccess = $this->_access_info();

				if('access-profile' == $field['type']) {
					$aAccess = $this->_access_info('profile');
				}

				if('access-profile-post' == $field['type']) {
					unset($aAccess[PeepSo::ACCESS_PUBLIC]);
				}

				$acc = intval($field['value']);

				if (!isset($aAccess[$acc])) {
					// access value not found in keys, assume value from first access entry
					$keys = array_keys($aAccess);
					$acc = $keys[0];
				}

				$sField .=	'<div class="ps-dropdown ps-dropdown--menu ps-js-dropdown ps-js-dropdown--privacy">';
				$sField .=		'<input type="hidden" name="' . $name . '" value="' . $field['value'] . '" />';
				$sField .=		'<button id="acc-' . $name . '" type="button" class="ps-btn ps-btn--sm form-control ps-dropdown__toggle ps-js-dropdown-toggle">';
				$sField .=			'<span class="dropdown-value"><i class="' . $aAccess[$acc]['icon'] . '"></i> </span>';
				$sField .=      	'<span class="ps-privacy-title ps-js-dropdown-label">' . $aAccess[$acc]['label'] . '</span>';
				$sField .=		'</button>';
				$sField .=		'<div class="ps-dropdown__menu ps-js-dropdown-menu" style="display:none">';

				foreach ($aAccess as $nAcc => $aAcc) {
					$sField .= '<a id="' . $name . '-acc-' . $nAcc . '" href="#" onclick="return false;" data-option-value="' . $nAcc . '">';
					$sField .= '<i class="' . $aAcc['icon'] . '"></i>';
					$sField .= '<span class="ps-js-dropdown-label">' . $aAcc['label'] . '</span></a>';
				}

				$sField .=		'</div>';
				$sField .=	'</div>';
				break;

			case 'custom':
				return (apply_filters('peepso_render_form_field', $field, $name));
				break;

			case 'separator':
				return "<hr>";
				break;
            case 'avatar':
                ob_start();
                ?>
                <a class="ps-registration__avatar-change ps-js-avatar-button" href="#">
                    <i class="gcis gci-camera"></i><span><?php echo __('Change avatar','peepso-core');?></span>
                </a>
                <?php
            return ob_get_clean();
                break;
		}

		if (!empty($field['descript']))
			$sField .= '<div class="ps-form__field-desc lbl-descript">'.$field['descript'].'</div>';

		return (apply_filters('peepso_render_form_field_type-' . $field['type'], $sField, $this));
	}

	/**
	 * renders the element that surrounds the form or fields
	 * @param array $container Container definition/information for an element
	 */
	private function _render_element($container)
	{
		if (!empty($container['element'])) {
			echo '<', $container['element'];
			if (!empty($container['class']))
				echo ' class="', $container['class'], '"';
			echo '>';
		}
	}

	/**
	 * renders the closing element tag for forms or fields
	 * @param array $container Container definition/information for an element
	 */
	private function _render_close_element($container)
	{
		if (!empty($container['element']))
			echo '</', $container['element'], '>';
	}

	/*
	 * Determine if a field is required
	 * @param array $field The field array data
	 * @return Boolean TRUE if the field is required, otherwise FALSE
	 */
	private function _is_required($field)
	{
		// look for the ['required'] element in the field data
		if (!empty($field['required']))
			return (TRUE);

		// look for the 'required' validation rule in the field data
		if (isset($field['validation']) && in_array('required', $field['validation']) !== FALSE)
			return (TRUE);

		return (FALSE);
	}

	/*
	 * Generate content for class attribute of form field
	 * @param array $field The field array data
	 * @return string The classes associated with the form field, separated with spaces
	 */
	private function _field_classes($field)
	{
		$aClasses = array();

			if(count($field['validation'])) {
				foreach($field['validation'] as $val) {
					$aClasses[] = $val;
				}
			}



		if (isset($field['class']))
			$aClasses = array_merge($aClasses, explode(' ', $field['class']));

		return (implode(' ', $aClasses));
	}

	/*
	 * Generate content for class attribute of form field
	 * @param array $field The field array data
	 * @return string The classes associated with the form field, separated with spaces
	 */
	private function _field_data($field)
	{
		$aData = '';

		foreach($field['data'] as $k=>$v) {
			$aData.=" data-$k=\"$v\" ";
		}

		return $aData;
	}

	/**
	 * Add multiple fields at once
	 * @param array $fields The array of fields to be added
	 */
	public function add_fields($fields)
	{
		foreach ($fields as $name => $field) {
			$field['name'] = $name;
			if (isset($this->fields[$field['name']]))
				continue;

			$this->add_field($field);
		}
	}

	/**
	 * Add a single field, initialize params and define validation
	 * @param array $field The field
	 */
	public function add_field($field)
	{
		$field = array_merge(
				array(
						'int' => FALSE, // Set to true if you want to use int() when retreiving form value
						'descript' => '',
						'label-desc' => '',
						'prefix' => '',
						'suffix' => '',
						'suffixhtml' => '',
						'class' => '',
						'field_wrapper' => 'div',
						'field_wrapper_class' => '',
						'field_label_class' => '',
						'validation' => array(),
						'validation_classes' => array(),
						'valid' => TRUE,
						'required' => FALSE,
						'raw' => FALSE, // Whether to get raw form data, without applying any sanitation
				),
				$field
		);

		if ($field['required'])
			$field['validation_classes']['required'] = new PeepSoFormValidate('required', array());

		foreach ($field['validation'] as &$validation) {
			$options = array();

			$name = $validation;
			if ('custom' === $validation  && isset($field['validation_options']) && is_array($field['validation_options'])) {
                foreach($field['validation_options'] as $opt) {
                    $name = $validation.md5(json_encode($opt));
					$options = $opt;
                }
            }

			$field['validation_classes'][$name] = new PeepSoFormValidate($validation, $options);
		}

		$this->fields[$field['name']] = $field;

		return ($field);
	}

	/**
	 * Sets values to all fields
	 */
	public function map_request()
	{
		$input = new PeepSoInput();
		foreach ($this->fields as $name => &$field) {

			if ($field['int']) {
                $field['value'] = $input->int($field['name']);
            } elseif (isset($field['raw']) && $field['raw']) {
                $field['value'] = $input->raw($field['name']);
            } else {
                $field['value'] = $input->value($field['name'],'',FALSE);
            }
		}
	}

	/*
	 * Performs validation operations on $_POST data with information
	 * @return Boolean TRUE indicates that form contents are valid; otherwise FALSE
	 */
	public function validate()
	{
		// give plugins a chance to circumvent the validation
		$valid = apply_filters('peepso_form_validate_before', TRUE, $this);
		if (FALSE === $valid)
			return (FALSE);

		foreach ($this->fields as &$field) {

			if (!($this->validate_field($field))) {
				$valid = FALSE;
				$this->error_messages[] = $field['error_messages'];
			}
		}

		// give plugins a chance to do some final checking
		$valid = apply_filters('peepso_form_validate_after', $valid, $this);

		return ($valid);
	}

	/**
	 * Sets error messages per field if any
	 * @return boolean
	 */
	public function validate_field(&$field)
	{
		$field['valid'] = TRUE;

		// check ReCaptcha
		if('recaptcha' == $field['name'] && PeepSo::get_option('site_registration_recaptcha_enable', 0)) {

			$mayfly = 'peepso_recaptcha_' . get_current_user_id();

			if( 1 == PeepSo3_Mayfly::get($mayfly)) {
				// looks like we just ran validation so we should not be doing it again
			} else {
				$input = new PeepSoInput();
				$recaptcha_response = $input->value('g-recaptcha-response','', FALSE); //SQL safe

				$args = array(
						'body' => array(
								'response' => $recaptcha_response,
								'secret' => PeepSo::get_option('site_registration_recaptcha_secretkey', 0),
						)
				);

				$request = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', $args);

				if ($response_json = json_decode(wp_remote_retrieve_body($request), true)) {
					$field['valid'] = $response_json['success'];
					PeepSo3_Mayfly::set($mayfly, 1, 5);
				} else {
					$field['valid'] = FALSE;
				}

				if (FALSE == $field['valid']) {
					PeepSo3_Mayfly::del($mayfly);
					$field['class'] .= ' error';
					$field['error_messages'] = array(__('ReCaptcha security check failed', 'peepso-core'));
				}
			}
		} else {
			foreach ($field['validation_classes'] as $rule) {
				if (!$rule->validate($field['value'])) {
					// To make sure .error is added only once
					if ($field['valid']) {
						$field['valid'] = FALSE;
						$field['class'] .= ' error';
					}

					$field['error_messages'][] = $rule->get_error_message();
				}
			}
		}

		return ($field['valid']);
	}

	/**
	 * Return error messages from validation
	 * @return array
	 */
	public function get_error_messages()
	{
		return $this->error_messages;
	}
}
