<?php

namespace ImportWP\Pro\Addon\AdvancedCustomFields\Exporter\Mapper;

use ImportWP\Pro\Addon\AdvancedCustomFields\Util\Helper;

class Mapper
{
    /**
     * @var \ImportWP\EventHandler
     */
    protected $event_handler;

    /**
     * @var string
     */
    protected $acf_type;

    /**
     * @var string
     */
    protected $filter_type;

    protected $fields;

    /**
     * @param \ImportWP\EventHandler $event_handler
     */
    public function __construct($event_handler, $filter_type)
    {
        $this->event_handler = $event_handler;

        add_filter('iwp/exporter/' . $filter_type . '/fields', [$this, 'modify_fields'], 10, 2);
        add_filter('iwp/exporter/' . $filter_type . '/setup_data', [$this, 'load_data'], 10, 2);
    }

    function process_field_list($acf_fields)
    {
        $tmp = [];

        foreach ($acf_fields as $acf) {
            switch ($acf['type']) {
                case 'google_map':
                    $tmp[] = $acf['name'];
                    $tmp[] = $acf['name'] . '::address';
                    $tmp[] = $acf['name'] . '::lat';
                    $tmp[] = $acf['name'] . '::lng';
                    $tmp[] = $acf['name'] . '::zoom';
                    break;
                case 'link':
                    $tmp[] = $acf['name'];
                    $tmp[] = $acf['name'] . '::title';
                    $tmp[] = $acf['name'] . '::url';
                    $tmp[] = $acf['name'] . '::target';
                    break;
                case 'file':
                case 'image':
                case 'gallery':
                    $tmp[] = $acf['name'];
                    $tmp[] = $acf['name'] . '::id';
                    $tmp[] = $acf['name'] . '::url';
                    break;
                default:
                    if (!empty($acf['name'])) {
                        $tmp[] = $acf['name'];
                    }
                    break;
            }
        }

        return $tmp;
    }

    function modify_fields($fields, $template_args)
    {
        $acf_fields = $this->fields = Helper::get_fields($this->acf_type, $template_args);

        $default_fields = array_filter($acf_fields, function ($item) {
            return $item['type'] !== 'repeater';
        });

        foreach ($default_fields as $field) {
            $fields['children']['custom_fields']['fields'] = array_filter($fields['children']['custom_fields']['fields'], function ($item) use ($field) {
                return $item !== $field['name'] && $item !== '_' . $field['name'];
            });
        }

        $fields['children']['acf'] = [
            'key' => 'acf',
            'label' => 'Advanced Custom Fields',
            'loop' => false,
            'fields' => $this->process_field_list($default_fields),
            'children' => []
        ];

        $repeater_fields = array_filter($acf_fields, function ($item) {
            return $item['type'] === 'repeater';
        });

        if (!empty($repeater_fields)) {
            // TODO: add field groups for repeaters
            foreach ($repeater_fields as $repeater) {
                $fields['children'][$repeater['name']] = [
                    'key' => $repeater['name'],
                    'label' => $repeater['label'],
                    'loop' => true,
                    'fields' => $this->process_field_list($repeater['sub_fields']),
                    'loop_fields' => $this->process_field_list($repeater['sub_fields']),
                    'children' => []
                ];

                $fields['children']['custom_fields']['fields'] = array_filter($fields['children']['custom_fields']['fields'], function ($item) use ($repeater) {
                    return $item !== $repeater['name'] && $item !== '_' . $repeater['name'] && strpos($item, $repeater['name'] . '_') !== 0 && strpos($item, '_' . $repeater['name'] . '_') !== 0;
                });
            }
        }

        // will map the value in json as array and not object
        $fields['children']['custom_fields']['fields'] = array_values($fields['children']['custom_fields']['fields']);

        return $fields;
    }

    function load_field_data($default_fields, $meta)
    {
        $output = [];
        foreach ($default_fields as $acf_field) {

            $field_id = $acf_field['name'];

            switch ($acf_field['type']) {
                case 'google_map':

                    // TODO: allow inside repeater using get_field data.
                    $output[$acf_field['name']] = isset($meta[$field_id], $meta[$field_id][0]) ? $meta[$field_id][0] : '';

                    $data = (array)maybe_unserialize($output[$acf_field['name']]);
                    $output[$acf_field['name'] . '::address'] = isset($data['address']) ? $data['address'] : '';
                    $output[$acf_field['name'] . '::lat'] = isset($data['lat']) ? $data['lat'] : '';
                    $output[$acf_field['name'] . '::lng'] = isset($data['lng']) ? $data['lng'] : '';
                    $output[$acf_field['name'] . '::zoom'] = isset($data['zoom']) ? $data['zoom'] : '';

                    break;
                case 'link':

                    $output[$acf_field['name']] = isset($meta[$field_id], $meta[$field_id][0]) ? $meta[$field_id][0] : '';

                    $data = (array)maybe_unserialize($output[$acf_field['name']]);
                    $output[$acf_field['name'] . '::title'] = isset($data['title']) ? $data['title'] : '';
                    $output[$acf_field['name'] . '::url'] = isset($data['url']) ? $data['url'] : '';
                    $output[$acf_field['name'] . '::target'] = isset($data['target']) ? $data['target'] : '';

                    break;
                case 'file':
                case 'image':


                    if (isset($meta[$field_id], $meta[$field_id]['ID'])) {

                        $output[$acf_field['name']] = $meta[$field_id]['ID'];
                        $output[$acf_field['name'] . '::id'] = $meta[$field_id]['ID'];
                        $output[$acf_field['name'] . '::url'] = $meta[$field_id]['url'];
                    } else {

                        $output[$acf_field['name']] = isset($meta[$field_id], $meta[$field_id][0]) ? $meta[$field_id][0] : '';

                        $data = (array)maybe_unserialize($output[$acf_field['name']]);
                        $output[$acf_field['name'] . '::id'] = $output[$acf_field['name']];
                        $output[$acf_field['name'] . '::url'] = wp_get_attachment_url($output[$acf_field['name']]);
                    }

                    break;
                case 'gallery':

                    // TODO: allow inside repeater using get_field data.
                    if (isset($meta[$field_id], $meta[$field_id][0])) {

                        $output[$acf_field['name']] = $meta[$field_id][0];
                        $output[$acf_field['name'] . '::id'] = $output[$acf_field['name']];

                        $images = (array)maybe_unserialize($output[$acf_field['name']]);

                        if (!empty($images)) {

                            $tmp = [];
                            foreach ($images as $image) {
                                $img_url =  wp_get_attachment_url($image);
                                if ($img_url) {
                                    $tmp[] = $img_url;
                                }
                            }
                            $output[$acf_field['name'] . '::url'] = $tmp;
                        }
                    } else {
                        $output[$acf_field['name']] = '';
                        $output[$acf_field['name'] . '::id'] = '';
                        $output[$acf_field['name'] . '::url'] = '';
                    }

                    break;
                default:
                    if (isset($meta[$field_id]) && is_array($meta[$field_id])) {
                        $output[$acf_field['name']] = $meta[$field_id][0];
                    } elseif (isset($meta[$field_id])) {
                        $output[$acf_field['name']] = $meta[$field_id];
                    } else {
                        $output[$acf_field['name']] = '';
                    }
                    break;
            }
        }

        return $output;
    }

    function load_data($record, $template_args)
    {
        $acf_fields = $this->fields = Helper::get_fields($this->acf_type, $template_args);

        $default_fields = array_filter($acf_fields, function ($item) {
            return $item['type'] !== 'repeater';
        });

        $record['acf'] = $this->load_field_data($default_fields, $record['custom_fields']);

        $repeater_fields = array_filter($acf_fields, function ($item) {
            return $item['type'] === 'repeater';
        });

        foreach ($repeater_fields as $repeater_field) {

            $tmp = [];
            $repeater_data = get_field($repeater_field['key'], $record['ID']);
            if (!empty($repeater_data)) {
                foreach ($repeater_data as $repeater_row) {
                    $tmp[] = $this->load_field_data($repeater_field['sub_fields'], $repeater_row);
                }
            }

            $record[$repeater_field['name']] = $tmp;
        }

        return $record;
    }
}
