<?php

namespace ImportWP\Pro\Importer\Template;

use ImportWP\Common\Attachment\Attachment;
use ImportWP\Common\Importer\ParsedData;
use ImportWP\Common\Importer\Template\Template;
use ImportWP\Common\Model\ImporterModel;
use ImportWP\Container;
use ImportWP\EventHandler;

class CustomFields
{
    /**
     * @var Template $template
     */
    private $template;

    /**
     * Keep track of custom fields for log that have already been processed
     *
     * @var array
     */
    public $virtual_fields;

    /**
     * @var EventHandler $event_handler
     */
    private $event_handler;

    /**
     * @var boolean
     */
    private $featured_set = false;

    public function __construct(Template $template, EventHandler $event_handler)
    {
        $this->template = $template;
        $this->event_handler = $event_handler;

        $this->event_handler->run('importer.custom_fields.init', [null, $this]);

        add_filter('iwp/importer/generate_field_map', [$this, 'generate_field_map'], 10, 3);
    }

    public function register_field_callbacks()
    {
        return [
            'custom_fields.*.key' => [$this, 'get_fields']
        ];
    }

    /**
     * Get list of posts
     * 
     * @param ImporterModel $importer_model
     *
     * @return array
     */
    public function get_fields($importer_model)
    {
        $options = $this->populate_custom_fields($importer_model);
        $options = $this->event_handler->run('importer.custom_fields.get_fields', [$options, $importer_model]);

        // remove duplicate fields
        $unique_fields = [];
        $tmp = [];
        foreach ($options as $field) {
            if (in_array($field['value'], $unique_fields, true)) {
                continue;
            }

            $unique_fields[] = $field['value'];
            $tmp[] = $field;
        }

        return $tmp;
    }

    public function template()
    {
        return $this->template;
    }

    /**
     * Find list of custom fields based on importer type: user, term, post
     * 
     * @param ImporterModel $importer_model
     *
     * @return array
     */
    private function populate_custom_fields($importer_model)
    {
        /**
         * @var \WPDB $wpdb
         */
        global $wpdb;

        $fields = [];
        $template = $importer_model->getTemplate();
        switch ($template) {
            case 'user':
                $results = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->usermeta}");
                break;
            case 'term':
                $taxonomies = (array)$importer_model->getSetting('taxonomy');
                $results = $wpdb->get_col("SELECT DISTINCT tm.meta_key FROM {$wpdb->term_taxonomy} as tt INNER JOIN {$wpdb->termmeta} as tm ON tt.term_id = tm.term_id WHERE tt.taxonomy IN ('" . implode("','", $taxonomies) . "') ");
                break;
            default:
                // Handle templates with multiple post_types
                $post_types = (array)$importer_model->getSetting('post_type');
                $results = $wpdb->get_col("SELECT DISTINCT pm.meta_key FROM {$wpdb->posts} as p INNER JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id AND p.post_type IN ('" . implode("','", $post_types) . "')");
                break;
        }

        $fields = array_reduce($results, function ($carry, $item) {

            // skip iwp custom meta
            if (substr($item, 0, 5) === '_iwp_') {
                return $carry;
            }

            $carry[] = [
                'value' => $item,
                'label' => 'Raw Field: ' . $item
            ];
            return $carry;
        }, []);

        // allow for plugins to remove fields from list
        $fields = apply_filters('iwp/importer/custom_fields/raw_fields', $fields, $importer_model);

        return $fields;
    }

    public function register_fields()
    {
        $attachment_condition = [
            'relation' => 'OR',
            [
                'relation' => 'AND',
                ['_field_type', '==', 'attachment'], // field_type = attachment
                ['key', '!*', '::'] // key not contains ::
            ],
            [
                'relation' => 'AND',
                ['key', '*=', '::attachment::'] // key contains ::attachment::
            ]
        ];

        $serialized_condition = [
            'relation' => 'OR',
            [
                'relation' => 'AND',
                ['_field_type', '==', 'serialized'], // field_type = serialized
                ['key', '!*', '::'] // key not contains ::
            ],
            [
                'relation' => 'AND',
                ['key', '*=', '::serialized::'] // key contains ::serialized::
            ]
        ];

        $mapped_condition = [
            'relation' => 'OR',
            [
                'relation' => 'AND',
                ['_field_type', '==', 'mapped'], // field_type = mapped
                ['key', '!*', '::'] // key not contains ::
            ],
            [
                'relation' => 'AND',
                ['key', '*=', '::mapped::'] // key contains ::mapped::
            ]
        ];

        return $this->template->register_group('Custom Fields', 'custom_fields', [
            $this->template->register_field('Name', 'key', [
                'options' => 'callback'
            ]),
            $this->template->register_field('Value', 'value', [
                'condition' => ['_field_type', '!=', 'serialized'],
            ]),
            $this->template->register_field('Field Type', '_field_type', [
                'default' => 'text',
                'options' => [
                    ['value' => 'text', 'label' => 'Text'],
                    ['value' => 'attachment', 'label' => 'Attachment'],
                    ['value' => 'mapped', 'label' => 'Mapped'],
                    ['value' => 'serialized', 'label' => 'Serialized'],
                ],
                'type' => 'select',
                'tooltip' => __('Select how the custom field should be handled', 'importwp'),
                'condition' => ['key', '!*', '::'],
            ]),
            $this->template->register_field('Serialized Values', '_serialized', [
                'type' => 'serialized',
                'condition' => $serialized_condition,
            ]),
            $this->template->register_field('Mapped Values', '_mapped', [
                'type' => 'mapped',
                'condition' => $mapped_condition,
            ]),
            $this->template->_register_attachment_setting_fields($attachment_condition, [
                $this->template->register_field('Return Value', '_return', [
                    'defualt' => 'url',
                    'options' => [
                        ['value' => 'url', 'label' => 'Attachment URL - Single Record'],
                        ['value' => 'id', 'label' => 'Attachment ID - Single Record'],
                        ['value' => 'id-csv', 'label' => 'Attachment ID - Single Record joining ids with ","'],
                        ['value' => 'url-raw', 'label' => 'Attachment URL - Multiple Records'],
                        ['value' => 'id-raw', 'label' => 'Attachment ID - Multiple Records'],
                        ['value' => 'url-serialize', 'label' => 'Attachment URL - Serialized'],
                        ['value' => 'id-serialize', 'label' => 'Attachment ID - Serialized'],
                    ],
                    'type' => 'select'
                ])
            ]),
        ], ['type' => 'repeatable', 'row_base' => true, 'link' => 'https://www.importwp.com/docs/how-to-import-wordpress-custom-fields/']);
    }

    public function process($post_id, ParsedData $data, ImporterModel $importer_model)
    {
        // Handle Sub Query
        $this->featured_set = false;
        $group_name = 'custom_fields';
        $custom_fields = $data->getData($group_name);
        $row_count = isset($custom_fields[$group_name . '._index']) ? intval($custom_fields[$group_name . '._index']) : 0;
        $output = [];
        $this->virtual_fields = [];
        $default_keys = [];

        for ($i = 0; $i < $row_count; $i++) {
            $prefix = $group_name . '.' . $i . '.';

            // store in a temp variable so can be processed the same as sub rows
            $sub_rows = [$custom_fields];

            if (isset($custom_fields[$prefix . 'row_base']) && !empty($custom_fields[$prefix . 'row_base'])) {
                $sub_group_id = $group_name . '.' . $i;
                $sub_rows = $data->getData($sub_group_id);
            }

            foreach ($sub_rows as $custom_field_record) {
                $key = $custom_field_record[$prefix . 'key'];

                $permission_key = $group_name . '.' . apply_filters('iwp/custom_field_key', $key);
                $allowed = $data->permission()->validate([$permission_key => ''], $data->getMethod(), $group_name);
                $is_allowed = isset($allowed[$permission_key]) ? true : false;

                if (!$is_allowed || empty($key)) {
                    continue 2;
                }

                $value = $custom_field_record[$prefix . 'value'];
                $value = apply_filters('iwp/template/process_field', $value, $key, $importer_model);

                $field_type = $custom_field_record[$prefix . '_field_type'];

                if (strpos($key, '::') !== false) {

                    // process prefixed fields
                    $custom_field_result = $this->event_handler->run('importer.custom_fields.process_field', [null, $post_id, $key, $value, $custom_field_record, $prefix, $importer_model, $this]);

                    // allow for process to return a list of custom fields to be updated
                    if (is_array($custom_field_result)) {
                        $output = array_merge($output, $custom_field_result);
                    }
                } else {
                    $default_keys[] = $key;
                    switch ($field_type) {
                        case 'serialized':
                            $output[$key][] = $this->processSerializedField($value, $post_id, $custom_fields, $prefix);
                            break;
                        case 'mapped':

                            $output[$key][] = $this->processMappedField($value, $post_id, $custom_fields, $prefix);
                            break;
                        case 'attachment':
                            $output[$key][] = $this->processAttachmentField($value, $post_id, $custom_fields, $prefix);
                            break;
                        case 'text':
                        default:
                            $output[$key][] = $this->processTextField($value);
                            break;
                    }
                }
            }
        }

        if (!empty($default_keys)) {
            foreach ($default_keys as $key) {
                if (count($output[$key]) == 1) {
                    $output[$key] = $output[$key][0];
                }
            }
        }

        $output = $this->event_handler->run('importer.custom_fields.post_process', [$output, $post_id, $importer_model, $this]);

        $data->replace($output, $group_name);

        return $data;
    }

    public function log_message($id, $data)
    {
        $fields = $data->getData('custom_fields');
        $fields = array_merge($fields, $this->virtual_fields);
        $message = '';

        if (!empty($fields)) {

            $custom_field_labels = [];
            foreach (array_keys($fields) as $field_key) {
                $custom_field_labels[] = apply_filters('iwp/custom_field_label', $field_key);
            }

            $message = ', Custom Fields (' . implode(', ', $custom_field_labels) . ')';
        }

        $message .= apply_filters('iwp/custom_fields/log_message', '');

        return $message;
    }

    public function processTextField($value)
    {
        return $value;
    }

    public function processSerializedField($value, $post_id, $custom_fields, $prefix)
    {
        $prefix .= '_serialized.';
        $max_rows =
            isset($custom_fields[$prefix . '_index']) ? $custom_fields[$prefix . '_index'] : 0;

        $output = [];

        for ($i = 0; $i < $max_rows; $i++) {
            // TODO: Process serialized fields
            $row_key =
                isset($custom_fields[$prefix . $i . '.key']) ? $custom_fields[$prefix . $i . '.key'] : '';
            $row_value =
                isset($custom_fields[$prefix . $i . '.value']) ? $custom_fields[$prefix . $i . '.value'] : '';

            $output[$row_key] = $row_value;
        }

        return serialize($output);
    }

    public function processMappedField($value, $post_id, $custom_fields, $prefix)
    {
        $prefix .= '_mapped.';
        $max_rows =
            isset($custom_fields[$prefix . '_index']) ? $custom_fields[$prefix . '_index'] : 0;

        for ($i = 0; $i < $max_rows; $i++) {

            $row_condition =
                isset($custom_fields[$prefix . $i . '._condition']) ? $custom_fields[$prefix . $i . '._condition'] : 'equal';
            $row_key =
                isset($custom_fields[$prefix . $i . '.key']) ? $custom_fields[$prefix . $i . '.key'] : '';
            $row_value =
                isset($custom_fields[$prefix . $i . '.value']) ? $custom_fields[$prefix . $i . '.value'] : '';

            switch ($row_condition) {
                case 'equal':
                    if (trim($row_key) === trim($value)) {
                        return $row_value;
                    }
                    break;
                case 'contains':
                    if (stripos($value, trim($row_key)) !== false) {
                        return $row_value;
                    }
                    break;
                case 'in':
                    if (in_array($value, explode(',', $row_key))) {
                        return $row_value;
                    }
                    break;
                case 'not-equal':
                    if (trim($row_key) !== trim($value)) {
                        return $row_value;
                    }
                    break;
                case 'not-contains':
                    if (stripos($value, trim($row_key)) === false) {
                        return $row_value;
                    }
                    break;
                case 'not-in':
                    if (!in_array($value, explode(',', $row_key))) {
                        return $row_value;
                    }
                    break;
            }
        }
        return $value;
    }

    public function processAttachmentField($value, $post_id, $custom_fields, $prefix)
    {
        /**
         * @var Filesystem $filesystem
         */
        $filesystem = Container::getInstance()->get('filesystem');

        /**
         * @var Ftp $ftp
         */
        $ftp = Container::getInstance()->get('ftp');

        /**
         * @var Attachment $attachment
         */
        $attachment = Container::getInstance()->get('attachment');


        $attachment_keys = [
            '_meta._title',
            '_meta._alt',
            '_meta._caption',
            '_meta._description',
            '_enable_image_hash',
            '_download',
            '_featured',
            '_remote_url',
            '_ftp_user',
            '_ftp_host',
            '_ftp_pass',
            '_ftp_path',
            '_local_url',
            '_meta._enabled',
            '_delimiter',
        ];
        $attachment_data = [
            'location' => $value
        ];

        foreach ($attachment_keys as $attachment_key) {
            if (isset($custom_fields[$prefix . 'settings.' . $attachment_key])) {
                $attachment_data['settings.' . $attachment_key] = $custom_fields[$prefix . 'settings.' . $attachment_key];
            } elseif (isset($custom_fields[$prefix . $attachment_key])) {
                $attachment_data['settings.' . $attachment_key] = $custom_fields[$prefix . $attachment_key];
            } else {
                $attachment_data['settings.' . $attachment_key] = '';
            }
        }

        $result = $this->template->process_attachment($post_id, $attachment_data, '', $filesystem, $ftp, $attachment);
        if (empty($result)) {
            return '';
        }

        if (isset($custom_fields[$prefix . 'settings._return'])) {
            $return = $custom_fields[$prefix . 'settings._return'];
        } elseif (isset($custom_fields[$prefix . '_return'])) {
            $return = $custom_fields[$prefix . '_return'];
        } else {
            $return = null;
        }

        $attachment_output = [];

        switch ($return) {
            case 'url-serialize':
            case 'url-raw':
                $attachment_output = array_map('wp_get_attachment_url', $result);
                break;
            case 'url':
                $attachment_output[] = wp_get_attachment_url($result[0]);
                break;
            case 'id-serialize':
            case 'id-csv':
            case 'id-raw':
                $attachment_output = $result;
            default:
                $attachment_output[] = $result[0];
                break;
        }

        if (strpos($return, 'serialize') !== false) {
            return serialize($attachment_output);
        } elseif (strpos($return, 'raw') !== false) {
            return $attachment_output;
        } else {
            return implode(',', $attachment_output);
        }
    }

    public function generate_field_map($field_map, $fields, $importer_model)
    {
        $counter = 0;
        $defaults = [
            "row_base" => "",
            "key" => "",
            "value" => "",
            "_field_type" => "text",
            "_serialized" => "",
            "_mapped" => "",
            "settings._featured" => "no",
            "settings._return" => "",
            "settings._download" => "remote",
            "settings._ftp_host" => "",
            "settings._ftp_user" => "",
            "settings._ftp_pass" => "",
            "settings._ftp_path" => "",
            "settings._remote_url" => "",
            "settings._local_url" => "",
            "settings._enable_image_hash" => "yes",
            "_enabled" => "no",
            "_alt" => "",
            "_title" => "",
            "_caption" => "",
            "_description" => "",
        ];

        $custom_fields = [];
        foreach ($fields as $index => $field) {
            if (preg_match('/^custom_fields\.(.*?)$/', $field, $matches) === 1) {
                $custom_fields[$matches[1]] = sprintf('{%d}', $index);
            }
        }

        $custom_fields = apply_filters('iwp/importer/generate_field_map/custom_fields', $custom_fields, $fields, $importer_model);

        foreach ($custom_fields as $field_id => $field_data) {

            $data = wp_parse_args([
                'key' => $field_id,
                'value' => $field_data
            ], $defaults);

            $field_map['map'] = array_merge($field_map['map'], array_reduce(array_keys($data), function ($carry, $key) use ($data, $counter) {
                $carry[sprintf('custom_fields.%d.%s', $counter, $key)] = $data[$key];
                return $carry;
            }, []));

            $counter++;
        }

        $field_map['map']['custom_fields._index'] = $counter;

        return $field_map;
    }

    public function get_permission_fields($importer_model)
    {
        $permission_fields = [];
        $field_map = $importer_model->getMap();
        if (isset($field_map['custom_fields._index']) && $field_map['custom_fields._index'] > 0) {
            $permission_fields['custom_fields'] = [];
            for ($i = 0; $i < $field_map['custom_fields._index']; $i++) {
                $key = $field_map['custom_fields.' . $i . '.key'];
                $key = apply_filters('iwp/custom_field_key', $key);
                $permission_fields['custom_fields']['custom_fields.' . $key] = apply_filters('iwp/custom_field_label', $key);
            }
        }

        return $permission_fields;
    }
}
