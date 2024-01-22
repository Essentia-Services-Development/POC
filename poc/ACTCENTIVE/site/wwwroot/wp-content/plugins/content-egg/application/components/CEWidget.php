<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

/**
 * ProductSearchWidget class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
abstract class CEWidget extends \WP_Widget {

    public $name;
    protected $slug;
    protected $description;
    protected $classname;
    protected $settings;

    public function __construct()
    {
        \add_action('widgets_init', function () {
            \register_widget(get_called_class());
        });

        $this->slug = $this->slug();
        $this->name = $this->name();
        $this->description = $this->description();
        $this->classname = $this->classname();
        $this->settings = $this->settings();

        parent::__construct(
                $this->slug, \esc_html($this->name), array(
            'description' => \esc_html($this->description),
            'classname' => $this->classname
                )
        );

        \add_action('save_post', array($this, 'flushСache'));
        \add_action('deleted_post', array($this, 'flushСache'));
        \add_action('switch_theme', array($this, 'flushСache'));
        \add_action('content_egg_price_history_save', array($this, 'flushСache'));
    }

    abstract public function slug();

    abstract public function description();

    abstract protected function name();

    abstract public function classname();

    public function settings()
    {
        return array();
    }

    public function setCache($data, $key = 0, $expire = 0)
    {
        if (!$key)
        {
            $key = 0;
        }
        $cache = \wp_cache_get($this->slug, 'widget');
        if (!$cache || !is_array($cache))
        {
            $cache = array();
        }
        $cache[$key] = $data;
        \wp_cache_set($this->slug, $cache, 'widget', $expire);
    }

    public function getCache($key = 0)
    {
        $cache = \wp_cache_get($this->slug, 'widget');
        if (!$key)
        {
            $key = 0;
        }
        $cache = \wp_cache_get($this->slug, 'widget');

        if (!$cache || !is_array($cache))
        {
            $cache = array();
        }

        if (isset($cache[$key]))
        {
            return $cache[$key];
        } else
        {
            return null;
        }
    }

    public function flushСache()
    {
        \wp_cache_delete($this->slug, 'widget');
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();

        if (!$this->settings || !is_array($this->settings))
        {
            return array();
        }

        foreach ($this->settings as $key => $setting)
        {
            switch ($setting['type'])
            {
                case 'number':
                    $instance[$key] = absint($new_instance[$key]);
                    if (isset($setting['min']) && $instance[$key] < $setting['min'])
                    {
                        $instance[$key] = $setting['min'];
                    }
                    if (isset($setting['max']) && $instance[$key] > $setting['max'])
                    {
                        $instance[$key] = $setting['max'];
                    }
                    break;
                case 'textarea':
                    $instance[$key] = \wp_kses(trim(\wp_unslash($new_instance[$key])), \wp_kses_allowed_html('post'));
                    break;
                case 'checkbox':
                    $instance[$key] = empty($new_instance[$key]) ? 0 : 1;
                    break;
                default:
                    $instance[$key] = (!empty($new_instance[$key]) ) ? \sanitize_text_field($new_instance[$key]) : '';
                    break;
            }
        }

        $this->flushСache();

        return $instance;
    }

    public function form($instance)
    {
        if (!$this->settings || !is_array($this->settings))
        {
            return array();
        }

        foreach ($this->settings as $key => $setting)
        {
            $value = isset($instance[$key]) ? $instance[$key] : $setting['default'];
            switch ($setting['type'])
            {
                case 'number' :
                    ?>
                    <p>
                        <label for="<?php echo \esc_attr($this->get_field_id($key)); ?>"><?php echo \esc_attr($setting['title']); ?>
                            :</label>
                        <input class="widefat" id="<?php echo \esc_attr($this->get_field_id($key)); ?>"
                               name="<?php echo \esc_attr($this->get_field_name($key)); ?>" type="number"
                               min="<?php echo \esc_attr($setting['min']); ?>"
                               max="<?php echo \esc_attr($setting['max']); ?>"
                               value="<?php echo \esc_attr($value); ?>"/>
                    </p>
                    <?php
                    break;

                case 'select' :
                    ?>
                    <p>
                        <label for="<?php echo \esc_attr($this->get_field_id($key)); ?>"><?php echo \esc_attr($setting['title']); ?>
                            :</label>
                        <select class="widefat" id="<?php echo \esc_attr($this->get_field_id($key)); ?>"
                                name="<?php echo \esc_attr($this->get_field_name($key)); ?>">
                                    <?php foreach ($setting['options'] as $option_key => $option_value) : ?>
                                <option value="<?php echo \esc_attr($option_key); ?>" <?php \selected($option_key, $value); ?>><?php echo \esc_html($option_value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    <?php
                    break;

                case 'textarea' :
                    ?>
                    <p>
                        <label for="<?php echo esc_attr($this->get_field_id($key)); ?>"><?php echo esc_html($setting['title']); ?>
                            :</label>
                        <textarea class="widefat <?php echo esc_attr($class); ?>"
                                  id="<?php echo esc_attr($this->get_field_id($key)); ?>"
                                  name="<?php echo esc_attr($this->get_field_name($key)); ?>" cols="20"
                                  rows="3"><?php echo esc_textarea($value); ?></textarea>
                                  <?php if (isset($setting['desc'])) : ?>
                            <small><?php echo esc_html($setting['desc']); ?></small>
                        <?php endif; ?>
                    </p>
                    <?php
                    break;

                case 'checkbox' :
                    ?>
                    <p>
                        <input class="checkbox" id="<?php echo \esc_attr($this->get_field_id($key)); ?>"
                               name="<?php echo \esc_attr($this->get_field_name($key)); ?>" type="checkbox"
                               value="1" <?php checked($value, 1); ?> />
                        <label for="<?php echo \esc_attr($this->get_field_id($key)); ?>"><?php echo \esc_attr($setting['title']); ?></label>
                    </p>
                    <?php
                    break;
                default :
                    ?>
                    <p>
                        <label for="<?php echo \esc_attr($this->get_field_id($key)); ?>"><?php echo \esc_attr($setting['title']); ?>
                            :</label>
                        <input class="widefat" id="<?php echo \esc_attr($this->get_field_id($key)); ?>"
                               name="<?php echo \esc_attr($this->get_field_name($key)); ?>" type="text"
                               value="<?php echo \esc_attr($value); ?>">
                    </p>
                    <?php
                    break;
            }
        }
    }

    public function beforeWidget($args, $instance)
    {
        $title = \apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
        echo wp_kses_post($args['before_widget']);
        if ($title)
        {
            echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
        }
    }

    public function afterWidget($args, $instance)
    {

        echo wp_kses_post($args['after_widget']);
    }

}
