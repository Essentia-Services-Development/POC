<?php

class PeepSoFieldTextURLPreset extends PeepSoFieldTextURL
{
    protected $field_meta_keys_extra = array(
        'user_nofollow',
        'preseturl',
        'userprefix',
    );

    public static $order = 900;
    public static $admin_label = 'URL - Preset';

    public function __construct($post, $user_id)
    {
        $this->field_meta_keys = array_merge($this->field_meta_keys, $this->field_meta_keys_extra);
        parent::__construct($post, $user_id);


// Remove inherited text area / multiline and Markdown rendering
        unset($this->render_form_methods['_render_form_textarea']);
        unset($this->render_methods['_render_md']);

// Add an option to render as <a href>
        $this->render_methods['_render'] = __('username', 'peepso-core');
        $this->render_methods['_render_link'] = __('full link', 'peepso-core');

        // Remove inherited length validators
        $this->validation_methods = array_diff($this->validation_methods, array('lengthmax', 'lengthmin'));
        $this->validation_methods[] = 'texturlpreset';
        $this->default_desc = __('What\'s your username?', 'peepso-core');
    }

    protected function _render()
    {
        if (empty($this->value)) {
            return $this->_render_empty_fallback();
        }

        // nofollow attribute
        $display_nofollow = (1 == $this->prop('meta', 'user_nofollow')) ? 'nofollow="nofollow"' : '';

        $link = $this->get_preset_url().'/'.$this->value;
        $username = $this->prop('meta','userprefix').$this->value;

        return sprintf('<a href="https://%s" %s target="_blank">%s</a>', $link, $display_nofollow, $username);
    }

    protected function _render_link()
    {
        if (empty($this->value)) {
            return $this->_render_empty_fallback();
        }

        // nofollow attribute
        $display_nofollow = (1 == $this->prop('meta', 'user_nofollow')) ? 'nofollow="nofollow"' : '';

        $link = $this->get_preset_url().'/'.$this->value;

        return sprintf('<a href="https://%s" %s target="_blank">%s</a>', $link, $display_nofollow, $link);
    }

    protected function _render_form_input()
    {
        $ret = '<div class="ps-input__wrapper">'. $this->get_preset_url().'/ ';
        $ret .= '<input style="width:250px;" class="ps-input ps-input--sm ps-input--count" type="text" value="' . $this->value . '"' . $this->_render_input_args() . $this->_render_required_args() . '>';
        $ret .= '<div class="ps-form__chars-count ps-js-counter" style="display:none"></div>';
        $ret .= '</div>';

        return $ret;
    }

    public function get_preset_url() {
        $preset = strlen($this->prop('meta', 'preseturl')) ? $this->prop('meta', 'preseturl') : 'instagram.com';
        $preset = str_ireplace(['https://','http://'], '', $preset);
        $preset = trim($preset, '/');

        return $preset;
    }

}

class PeepSoFieldTextURLPresetPatternUsername extends PeepSoFieldTestAbstract
{

    public function __construct($value)
    {
        parent::__construct($value);

        $this->admin_label = __('Force valid usernames', 'peepso-core');
        $this->admin_type = 'checkbox';

        $this->message = __('Must be a valid username', 'peepso-core');
    }


    public function test()
    {

        if (strlen($this->value) && !preg_match('/^[a-zA-Z0-9._]+$/', $this->value)) {

            $this->error = $this->message;

            return FALSE;
        }

        return TRUE;
    }
}