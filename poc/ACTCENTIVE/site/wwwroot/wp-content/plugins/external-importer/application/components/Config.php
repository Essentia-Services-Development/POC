<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;

/**
 * Config class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
abstract class Config {

    protected $page_slug;
    protected $option_name;
    protected $option_values = array();
    protected $options = array();
    protected $input = array();
    protected $out = array();
    public $initialized = false;
    private static $_instances = array();

    public static function getInstance($id = null)
    {
        $class = get_called_class();
        if ($id)
            $instance_id = $id;
        else
            $instance_id = $class;

        if (!isset(self::$_instances[$instance_id]))
        {
            self::$_instances[$instance_id] = new $class($id);
        }
        return self::$_instances[$instance_id];
    }

    protected function __construct()
    {
        $values = \get_option($this->option_name());
        // prevent call validators twice for first time. Settings API bug?
        if ($values === false)
            \add_option($this->option_name(), '');

        $this->option_name = $this->option_name();
        $this->options = $this->options();
        $this->option_values = $values;
        $this->page_slug = $this->page_slug();
        $this->initialized = true;
    }

    public function option($opt_name)
    {
        return $this->get_current($opt_name);
    }

    public function adminInit()
    {
        global $pagenow;
        \add_action('admin_menu', array($this, 'add_admin_menu'));

        if ($pagenow == 'options.php' || (!empty($_GET['page']) && $_GET['page'] == $this->page_slug))
        {
            \add_action('admin_init', array($this, 'register_settings'));
        }
    }

    abstract public function page_slug();

    abstract public function option_name();

    abstract protected function options();

    abstract public function add_admin_menu();

    public function get_page_slug()
    {
        return $this->page_slug;
    }

    protected function get_default($option)
    {
        if (isset($this->options[$option]) && isset($this->options[$option]['default']))
            return $this->options[$option]['default'];
        else
            return '';
    }

    protected function get_validator($option)
    {
        if (isset($this->options[$option]) && isset($this->options[$option]['validator']))
            return $this->options[$option]['validator'];
        else
            return null;
    }

    protected function get_current($option)
    {
        if (isset($this->option_values[$option]))
            return $this->option_values[$option];
        elseif ($this->option_values && $this->is_checkbox($option))
        {
            return false;
        } else
            return $this->get_default($option);
    }

    public function register_settings()
    {
        \register_setting(
                $this->page_slug, // group, used for settings_fields()
                $this->option_name, // option name, used as key in database
                array($this, 'validate')      // validation callback
        );

        // reinit options for later plugin binding
        $this->options = $this->options();

        $sections = array();

        foreach ($this->options as $id => $field)
        {
            if (empty($field['title']))
                $field['title'] = '';
            if (empty($field['description']))
                $field['description'] = '';
            $params = array(
                'name' => $id, // value for 'name' attribute
                'title' => $field['title'],
                'description' => $field['description'],
                'value' => $this->get_current($id),
                'option_name' => $this->option_name,
                'label_for' => 'label-' . $id,
            );
            if (!empty($field['dropdown_options']))
                $params['dropdown_options'] = $field['dropdown_options'];
            if (!empty($field['checkbox_options']))
                $params['checkbox_options'] = $field['checkbox_options'];
            if (!empty($field['label']))
                $params['label'] = $field['label'];
            if (!empty($field['class']))
                $params['class'] = $field['class'];
            if (!empty($field['type']))
                $params['type'] = $field['type'];
            if (!empty($field['render_after']))
                $params['render_after'] = $field['render_after'];
            if (empty($field['section']))
                $field['section'] = 'default';

            // section
            if (!isset($sections[$field['section']]))
            {
                if ($field['section'] == 'default')
                    $section_title = '';
                else
                    $section_title = $field['section'];

                \add_settings_section(sanitize_text_field($field['section']), $section_title, null, $this->page_slug);
                $sections[$field['section']] = $field['section'];
            }
            \add_settings_field(
                    $id, $field['title'], $field['callback'], $this->page_slug, // menu slug
                    $field['section'], $params
            );
        }
    }

    public function render_input($args)
    {
        if (!empty($args['class']))
            $class = $args['class'];
        else
            $class = 'regular-text ltr';
        if (!empty($args['type']))
            $type = $args['type'];
        else
            $type = 'text';
        echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($args['option_name']) . '[' . esc_attr($args['name']) . ']" id="' . esc_attr($args['label_for']) . '" value="' . esc_attr($args['value']) . '" class="' . esc_attr($class) . '" />';
        if (!empty($args['render_after']))
            echo $args['render_after'];
        if ($args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function render_textarea($args)
    {
        echo '<textarea name="' . esc_attr($args['option_name']) . '['
        . esc_attr($args['name']) . ']" id="'
        . esc_attr($args['label_for']) . '" rows="2" class="large-text code">' . esc_html($args['value']) . '</textarea>';
        if (!empty($args['render_after']))
            echo $args['render_after'];
        if ($args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function render_checkbox($args)
    {
        if ((bool) $args['value'])
            $checked = ' checked="checked" ';
        else
            $checked = '';
        echo '<label for="' . esc_attr($args['label_for']) . '">';
        echo '<input type="checkbox" name="' . esc_attr($args['option_name']) . '['
        . esc_attr($args['name']) . ']" id="'
        . esc_attr($args['label_for']), '"'
        . $checked . ' value="1" />';
        if (!empty($args['label']))
            echo ' ' . $args['label'];
        elseif ($args['description'])
            echo ' ' . $args['description'];
        echo '</label>';

        if (!empty($args['label']) && $args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function render_dropdown($args)
    {
        if (!empty($args['dropdown_options']))
        {
            echo '<select name="' . esc_attr($args['option_name']) . '['
            . esc_attr($args['name']) . ']" id="'
            . esc_attr($args['label_for']) . '" value="'
            . esc_attr($args['value']) . '" >';
            foreach ($args['dropdown_options'] as $option_value => $option_name)
            {
                if ($option_value === $args['value'])
                    $selected = ' selected="selected" ';
                else
                    $selected = '';
                echo '<option value="' . esc_attr($option_value) . '"' . $selected . '>' . esc_html($option_name) . '</option>';
            }
            echo '</select>';
        } else
            echo ' - ';

        if (!empty($args['render_after']))
            echo $args['render_after'];
        if ($args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function render_checkbox_list($args)
    {
        echo '<div class="cegg-checkboxgroup">';
        foreach ($args['checkbox_options'] as $value => $name)
        {
            if (in_array($value, $args['value']))
                $checked = ' checked="checked" ';
            else
                $checked = '';

            echo '<div class="cegg-checkbox">';
            echo '<label for="' . esc_attr($args['label_for'] . '-' . $value) . '">';
            echo '<input type="checkbox" name="' . esc_attr($args['option_name']) . '['
            . esc_attr($args['name']) . '][' . esc_attr($value) . ']" id="'
            . esc_attr($args['label_for'] . '-' . $value), '"'
            . $checked . ' value="' . esc_attr($value) . '" />';
            echo esc_html($name);
            echo '</label>';
            echo '</div>';
        }
        echo '</div>';
        if ($args['description'])
        {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

    public function render_hidden($args)
    {
        echo '<input type="hidden" name="' . esc_attr($args['option_name']) . '['
        . esc_attr($args['name']) . '] value="'
        . esc_attr($args['value']) . '" />';
    }

    public function render_color_picker($args)
    {
        echo '<input name="' . esc_attr($args['option_name']) . '['
        . esc_attr($args['name']) . ']" id="'
        . esc_attr($args['label_for']) . '" value="'
        . esc_attr($args['value']) . '" />';
        if (!empty($args['render_after']))
            echo $args['render_after'];
        if ($args['description'])
        {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
        \wp_enqueue_style('wp-color-picker');
        \wp_enqueue_script('wp-color-picker', admin_url('js/color-picker.min.js'));
        $script = "jQuery(document).ready(function($){jQuery('#" . esc_attr($args['label_for']) . "').wpColorPicker();});";
        echo '<script type="text/javascript">' . $script . '</script>';
    }

    public function option_exists($option)
    {
        if (array_key_exists($option, $this->options))
            return true;
        else
            return false;
    }

    public function validate($input)
    {
        $this->input = $input;

        if (!is_array($this->input))
            return;

        foreach ($this->input as $option => $value)
        {
            if (!$this->option_exists($option))
                continue;

            if (!is_array($value))
                $value = trim($value);
            if ($validator = $this->get_validator($option))
            {
                if (!is_array($validator))
                    continue;
                foreach ($validator as $v)
                {
                    if (!is_array($v))
                    {
                        if ($v == 'allow_empty')
                        {
                            if ($value === '')
                                break;
                            else
                                continue;
                        }

                        // filter
                        $value = call_user_func($v, $value);
                    } else
                    {
                        // check 'when' condition
                        if (!empty($v['when']))
                        {
                            $when_value = $this->get_submitted_value($v['when']);
                            if (!$when_value)
                                continue;
                        }

                        if (!empty($v['type']) && $v['type'] == 'filter')
                        {
                            // filter                            
                            $value = call_user_func($v['call'], $value);
                        } else
                        {
                            // validator
                            if (empty($v['arg']))
                                $res = call_user_func($v['call'], $value);
                            else
                                $res = call_user_func($v['call'], $value, $v['arg']);
                            if (!$res)
                            {
                                \add_settings_error($option, $option, $v['message']);
                                $value = $this->get_current($option);
                                if (!empty($v['when']))
                                    $this->out[$v['when']] = $this->get_current($v['when']);
                                break;
                            }
                        } // .validator
                    }
                }
            }
            $this->out[$option] = $value;
        }
        return $this->out;
    }

    public function is_checkbox($option)
    {
        if (isset($this->options[$option]) && $this->options[$option]['callback'][1] == 'render_checkbox')
            return true;
        else
            return false;
    }

    /**
     * Current submitted value
     */
    public function get_submitted_value($option, $input = array(), $out = array())
    {
        if (!$input)
            $input = $this->input;
        if (!$out)
            $out = $this->out;

        if (!$this->option_exists($option))
            throw new \Exception('Options "' . $option . '" does not exists.');

        if (!isset($input[$option]) && $this->is_checkbox($option))
            return false;

        if (!isset($input[$option]))
            throw new \Exception('Options "' . $option . '" does not exists.');

        if (isset($out[$option]))
            return $out[$option];
        else
            return $input[$option];
    }

    public function getOptionsList()
    {
        return array_keys($this->options());
    }

    public function getOptionValues()
    {
        $result = array();
        foreach ($this->getOptionsList() as $option_name)
        {
            $result[$option_name] = $this->get_current($option_name);
        }
        return $result;
    }

    public function isAdminPage()
    {
        if (!\is_admin() || !$this->initialized)
            return false;

        global $pagenow;
        if ($pagenow == 'admin.php' && !empty($_GET['page']) && $_GET['page'] == $this->page_slug())
            return true;
        else
            return false;
    }

}
