<?php

namespace ImportWP\Pro\Addon\AdvancedCustomFields\Util;

class Helper
{
    public static function get_fields($template, $template_arg = [])
    {
        switch ($template) {
            case 'user':
                $fields = self::get_acf_fields(['user_form' => 'all']);
                break;
            case 'term':
                $taxonomies = (array)$template_arg;
                foreach ($taxonomies as $taxonomy) {
                    $fields = self::get_acf_fields(['taxonomy' => $taxonomy]);
                }
                break;
            default:
                // Handle templates with multiple post_types
                $post_types = (array)$template_arg;
                foreach ($post_types as $post_type) {
                    $fields = self::get_acf_fields(['post_type' => $post_type]);
                }
                break;
        }
        return $fields;
    }

    public static function get_acf_fields($args)
    {
        $options = [];
        $args = apply_filters('iwp_acf/get_fields_filter', $args);

        $groups = acf_get_field_groups($args);
        foreach ($groups as $group) {

            $fields = acf_get_fields($group['key']);
            foreach ($fields as $field) {
                $options[] = $field;
            }
        }
        return $options;
    }
}
