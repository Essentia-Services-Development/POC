<?php

namespace ImportWP\Pro\Importer\Template;

use ImportWP\Common\Importer\ParsedData;

class CustomPostTypeTemplate extends PostTemplate
{
    protected $name = 'Custom Post Type';

    public function register_options()
    {

        $post_types = get_post_types();

        // remove default post types that already have templates.
        $hide_post_types = array('post', 'page', 'attachment', 'revision', 'nav_menu_item', IWP_POST_TYPE);
        foreach ($hide_post_types as $hide_post_type) {
            if (isset($post_types[$hide_post_type])) {
                unset($post_types[$hide_post_type]);
            }
        }

        $output = [];
        foreach ($post_types as $key => $value) {
            $output[] = ['value' => $key, 'label' => $value];
        }

        $options = array_merge([['value' => '', 'label' => 'Choose a Post Type']], $output);

        return [
            $this->register_field('Post Type', 'post_type', [
                'options' => $options
            ])
        ];
    }

    public function get_permission_fields($importer_model)
    {
        $permission_fields = parent::get_permission_fields($importer_model);
        $custom_permission_fields = $this->custom_fields->get_permission_fields($importer_model);

        return array_merge($permission_fields, $custom_permission_fields);
    }
}
