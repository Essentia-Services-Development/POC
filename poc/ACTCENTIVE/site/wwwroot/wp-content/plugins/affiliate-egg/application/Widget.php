<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

class AffiliateEgg_Widget extends \WP_Widget {

    function __construct()
    {
        parent::__construct(
                'affegg_widget', 'Affiliate Egg', array('description' => __('Products from storefronts', 'affegg'))
        );
    }

    function form($instance)
    {
        $instance = wp_parse_args((array) $instance, array('title' => '', 'limit' => '2', 'sortby' => 'egg_id', 'affegg_ids' => '', 'tpl' => '', 'in_stock' => false));
        $title = esc_attr($instance['title']);
        $limit = absint($instance['limit']);
        $affegg_ids = esc_attr($instance['affegg_ids']);
        $templates = TemplateManager::getInstance()->getWidgetTemplatesList(true);
        $in_stock = isset($instance['in_stock']) ? (bool) $instance['in_stock'] : false;
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>


        <p><label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of products:', 'affegg'); ?></label>
            <input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $limit; ?>" size="3" /></p>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e('Sorting:', 'affegg'); ?></label>
            <select name="<?php echo $this->get_field_name('sortby'); ?>" id="<?php echo $this->get_field_id('sortby'); ?>" class="widefat">
                <option value="egg_id"<?php selected($instance['sortby'], 'egg_id'); ?>><?php _e('Last storefronts', 'affegg'); ?></option>
                <option value="last_update"<?php selected($instance['sortby'], 'last_update'); ?>><?php _e('Last updated products', 'affegg'); ?></option>
                <option value="create_date"<?php selected($instance['sortby'], 'create_date'); ?>><?php _e('Last added products', 'affegg'); ?></option>
                <option value="discount"<?php selected($instance['sortby'], 'discount'); ?>><?php _e('With discount', 'affegg'); ?></option>
                <option value="random"<?php selected($instance['sortby'], 'random'); ?>><?php _e('Random', 'affegg'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('affegg_ids'); ?>"><?php _e('Storefronts:', 'affegg'); ?></label> <input type="text" value="<?php echo $affegg_ids; ?>" name="<?php echo $this->get_field_name('affegg_ids'); ?>" id="<?php echo $this->get_field_id('affegg_ids'); ?>" class="widefat" />
            <br />
            <small><?php _e('ID of storefronts, with commas', 'affegg'); ?></small>
        </p>		
        <p>
            <label for="<?php echo $this->get_field_id('tpl'); ?>"><?php _e('Template:', 'affegg'); ?></label>
            <select name="<?php echo $this->get_field_name('tpl'); ?>" id="<?php echo $this->get_field_id('tpl'); ?>" class="widefat">
                <?php foreach ($templates as $tpl_id => $tpl_name): ?>
                    <option value="<?php echo esc_attr($tpl_id); ?>" <?php selected($instance['tpl'], $tpl_id); ?>><?php _e(esc_attr($tpl_name), 'affegg'); ?></option>
                <?php endforeach; ?>

            </select>
        </p>
        <p><input class="checkbox" type="checkbox" <?php checked($in_stock); ?> id="<?php echo $this->get_field_id('in_stock'); ?>" name="<?php echo $this->get_field_name('in_stock'); ?>" />
            <label for="<?php echo $this->get_field_id('in_stock'); ?>"><?php _e('Only in stock products', 'affegg'); ?></label></p>

        <?php
    }

    function update($new_instance, $old_instance)
    {
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['limit'] = (int) $new_instance['limit'];
        if ($instance['limit'] == 0)
            $instance['limit'] = 1;
        if ($instance['limit'] > 25)
            $instance['limit'] = 25;

        if (in_array($new_instance['sortby'], array('egg_id', 'create_date', 'last_update', 'discount', 'random')))
        {
            $instance['sortby'] = $new_instance['sortby'];
        } else
        {
            $instance['sortby'] = 'egg_id';
        }
        $instance['affegg_ids'] = strip_tags($new_instance['affegg_ids']);
        if ($instance['affegg_ids'] != '')
        {
            $instance['affegg_ids'] = preg_replace("/[^\d,]/", '', $instance['affegg_ids']);
            $ids = preg_split("/,/", $instance['affegg_ids'], NULL, PREG_SPLIT_NO_EMPTY);
            $ids = array_unique($ids);
            $instance['affegg_ids'] = join(',', $ids);
        }

        $instance['tpl'] = strip_tags($new_instance['tpl']);


        $tpls = TemplateManager::getInstance()->getWidgetTemplatesList(true);
        $av = array_values($tpls);

        if (!in_array($instance['tpl'], array_keys($tpls)) && isset($av[0]))
            $instance['tpl'] = strip_tags($av[0]);

        $instance['in_stock'] = isset($new_instance['in_stock']) ? (bool) $new_instance['in_stock'] : false;

        $this->flush_widget_cache();

        return $instance;
    }

    public function flush_widget_cache()
    {
        wp_cache_delete('affegg_widget', 'widget');
    }

    function widget($args, $instance)
    {
        $cache = wp_cache_get('affegg_widget', 'widget');

        if (!is_array($cache))
            $cache = array();

        if (!isset($args['widget_id']))
            $args['widget_id'] = $this->id;

        if (isset($cache[$args['widget_id']]))
            $items = $cache[$args['widget_id']];
        else
        {
            $params = array();
            $params['limit'] = isset($instance['limit']) ? $instance['limit'] : false;
            $params['sortby'] = isset($instance['sortby']) ? $instance['sortby'] : false;
            $params['affegg_ids'] = isset($instance['affegg_ids']) ? $instance['affegg_ids'] : false;
            $params['in_stock'] = isset($instance['in_stock']) ? $instance['in_stock'] : false;
            $items = ProductModel::model()->getEggWidgetProducts($params);
            if ($items)
                $items = Shortcode::getInstance()->prepareItems($items, $args['widget_id']);
            $cache[$args['widget_id']] = $items;
            wp_cache_set('affegg_widget', $cache, 'widget', 10800);
        }

        $output = '';
        $tpls = TemplateManager::getInstance()->getWidgetTemplatesList(true);
        $av = array_values($tpls);

        if (!in_array($instance['tpl'], array_keys($tpls)) && isset($av[0]))
            $instance['tpl'] = strip_tags($av[0]);


        if ($items)
        {
            $res = TemplateManager::getInstance()->render($instance['tpl'], array('items' => $items, 'widget_id' => $args['widget_id']));

            $output .= $args['before_widget'];
            if (!empty($instance['title']))
            {
                $output .= $args['before_title'];
                $output .= esc_html($instance['title']);
                $output .= $args['after_title'];
            }
            $output .= $res;

            $output .= $args['after_widget'];
            echo $output;
        }
    }

}
