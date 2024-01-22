<?php

namespace ImportWP\Pro\Addon\AdvancedCustomFields\Importer;

use ImportWP\Common\Util\Logger;
use ImportWP\Pro\Addon\AdvancedCustomFields\Util\Helper;
use ImportWP\Pro\Importer\Template\CustomFields;

class Importer
{
    /**
     * List of acf repeater / group field keys
     * @var string[]|null if initialized groups is an array
     */
    public $groups = null;

    /**
     * @var CustomFields $custom_fields
     */
    public $custom_fields;

    /**
     * @var mixed
     */
    public $field_prefix;

    public $permission_groups = [];

    /**
     * List of groups and fields that have been updated to be output in the history log
     * @var array
     */
    public $message_log = [];

    /**
     * @param \ImportWP\EventHandler $event_handler 
     * @return void 
     */
    public function __construct($event_handler)
    {
        $event_handler->listen('importer.custom_fields.init', [$this, 'init']);
        $event_handler->listen('importer.custom_fields.get_fields', [$this, 'get_fields']);
        $event_handler->listen('importer.custom_fields.process_field', [$this, 'process_field']);

        $event_handler->listen('template.fields', [$this, 'register_repeater_fields']);
        $event_handler->listen('template.process', [$this, 'repeater_process']);
        $event_handler->listen('template.pre_process_groups', [$this, 'register_virtual_template_group']);
        $event_handler->listen('importer_manager.import_shutdown', [$this, 'reset']);

        /**
         * Pre populate acf repeater and group fields.
         * 
         * @since 2.7.2
         */
        add_filter('iwp/importer/generate_field_map', [$this, 'generate_field_map'], 9, 3);

        /**
         * Update importer custom field list to use acf key and value.
         * 
         * @since 2.7.2
         */
        add_filter('iwp/importer/generate_field_map/custom_fields', [$this, 'generate_custom_field_map'], 10, 3);
    }

    public function init($result, $custom_fields)
    {
        $this->custom_fields = $custom_fields;
    }

    /**
     * After each import the groups property needs to be reset to null
     * @param \ImportWP\Common\Model\ImporterModel $importer_data 
     * @return \ImportWP\Common\Model\ImporterModel 
     */
    public function reset($importer_data)
    {
        $this->groups = null;
        return $importer_data;
    }

    public function get_fields($fields, $importer_model)
    {

        $tmp_fields = [];

        $acf_fields = $this->iwp_acf_fields($importer_model);
        $acf_fields = array_filter($acf_fields, function ($field) {
            return !in_array($field['type'], ['repeater', 'group']);
        });

        foreach ($acf_fields as $field) {

            switch ($field['type']) {
                case 'image':
                case 'gallery':
                case 'file':
                    $type = 'attachment';
                    break;
                default:
                case 'text':
                    $type = 'text';
                    break;
            }

            $tmp_fields[] = [
                'value' => 'acf_field::' . $type . '::' . $field['key'],
                'label' => 'Advanced Custom Fields - ' . $field['label']
            ];
        }

        return array_merge($tmp_fields, $fields);
    }

    /**
     * Register template fields
     *
     * @param array $fields
     * @param \ImportWP\Common\Importer\Template\Template $template
     * @param \ImportWP\Common\Model\ImporterModel $importer_model
     * @return array
     */
    public function register_repeater_fields($fields, $template, $importer_model)
    {
        Logger::write('register_repeater_fields -start');
        $acf_fields = $this->iwp_acf_fields($importer_model);

        $repeater_fields = array_filter($acf_fields, function ($field) {
            return in_array($field['type'], ['repeater', 'group']);
        });

        foreach ($repeater_fields as $repeater_group) {

            $panel_settings = $repeater_group['type'] === 'repeater' ? [
                'type' => 'repeatable',
                'row_base' => true
            ] : [];

            $sub_fields = [];

            foreach ($repeater_group['sub_fields'] as $field) {

                switch ($field['type']) {
                    case 'image':
                    case 'gallery':
                    case 'file':
                        $sub_fields[] = $template->register_attachment_fields($field['label'], $field['key'], $field['label'] . ' Location', ['type' => 'group']);
                        break;
                    default:
                        $sub_fields[] = $template->register_field($field['label'], $field['key']);
                        break;
                }

                // Add each sub field to permission group
                $this->register_permission_field($repeater_group['key'] . '.' . $field['key'], $field['label'], $repeater_group['label']);
            }

            $fields[] = $template->register_group('ACF - ' . $repeater_group['label'], $repeater_group['key'], $sub_fields, $panel_settings);
        }

        add_filter('iwp/template/permission_fields', [$this, 'register_permission_fields']);

        Logger::write('register_repeater_fields -end');

        return $fields;
    }

    public function register_permission_fields($permissions)
    {
        return array_merge($permissions, $this->permission_groups);
    }

    public function register_permission_field($field_key, $field_label, $permission_group)
    {
        if (!isset($this->permission_groups[$permission_group])) {
            $this->permission_groups[$permission_group] = [];
        }

        $this->permission_groups[$permission_group][$field_key] = $field_label;
    }

    /**
     * Process repeater group data
     *
     * @param integer $id
     * @param \ImportWP\Common\Importer\ParsedData $data
     * @param \ImportWP\Common\Model\ImporterModel $importer_model
     * @param \ImportWP\Common\Importer\Template\Template $template
     * @return integer
     */
    public function repeater_process($id, $data, $importer_model, $template)
    {
        Logger::write('repeater_process -start');

        foreach ($this->groups as $group_id) {
            Logger::write('repeater_process -start -group=' . $group_id);

            $group_row_id = '' . $group_id;
            $master_field = acf_get_field($group_id);
            $master_allowed_fields = array_reduce($master_field['sub_fields'], function ($carry, $item) use ($data, $group_id) {

                $allowed = $data->permission()->validate([
                    $group_id . '.' . $item['key'] => '',
                ], $data->getMethod(), $group_id);

                if (!empty($allowed)) {
                    $carry[$item['key']] = $item['type'];
                }

                return $carry;
            }, []);

            if (empty($master_allowed_fields)) {
                continue;
            }

            $group_fields = $data->getData($group_row_id);
            $output = [];

            $default_row_count = $master_field['type'] == 'group' ? 1 : 0;
            $row_count = isset($group_fields[$group_row_id . '._index']) ? intval($group_fields[$group_row_id . '._index']) : $default_row_count;

            for ($i = 0; $i < $row_count; $i++) {

                $row = [];

                // group doesn't have multiple rows
                if ($master_field['type'] === 'repeater') {
                    $prefix = $group_row_id . '.' . $i;
                } else {
                    $prefix = $group_row_id;
                }

                $row_fields = array_filter($group_fields, function ($k) use ($prefix) {
                    return strpos($k, $prefix . '.') === 0;
                }, ARRAY_FILTER_USE_KEY);

                // store in a temp variable so can be processed the same as sub rows
                $sub_rows = [$row_fields];

                if (isset($row_fields[$prefix . '.row_base']) && !empty($row_fields[$prefix . '.row_base'])) {
                    $sub_rows = $data->getData('' . $group_id . '.' . $i);
                }

                foreach ($sub_rows as $custom_field_row) {

                    $tmp = [];

                    $field_set = [];
                    foreach ($master_allowed_fields as $field_id => $field_type) {
                        $field_set = [];
                        foreach ($custom_field_row as $k => $v) {


                            if (strpos($k, $prefix . '.' . $field_id) !== 0) {
                                continue;
                            }


                            $field_set[substr($k, strlen($prefix) + 1)] = $v;
                        }

                        $acf_field = $this->get_field_object($field_id);

                        if (in_array($acf_field['type'], ['image', 'file', 'gallery']) && isset($field_set[$field_id . '.location'])) {
                            $value = $field_set[$field_id . '.location'];
                        } elseif (isset($field_set[$field_id])) {
                            $value = $field_set[$field_id];
                        } else {
                            $value = '';
                        }

                        $tmp[$field_id] = $this->set_value($id, $acf_field, $value, $field_set, $field_id . '.');

                        // update history log with group and fields
                        $this->add_log_message($master_field['name'], $acf_field['name']);
                    }

                    $row[] = $tmp;
                }

                $output[] = $row;
            }

            // convert multi to single array
            $rows = array_reduce($output, function ($carry, $item) {
                $carry = array_merge($carry, $item);
                return $carry;
            }, []);

            Logger::write('repeater_process -update -group=' . $group_id . ' -data=' . wp_json_encode($rows));

            update_field($group_id, $master_field['type'] === 'repeater' ? $rows : $rows[0], $id);

            Logger::write('repeater_process -end -group=' . $group_id);
        }

        add_filter('iwp/custom_fields/log_message', function ($message) {

            if (!empty($this->message_log)) {
                foreach ($this->message_log as $group => $fields) {
                    if (empty($fields)) {
                        continue;
                    }

                    $message .= ', ' . $group . ' (' . implode(', ', $fields) . ')';
                }
            }

            $this->message_log = [];

            return $message;
        });

        Logger::write('repeater_process -end');
        return $id;
    }

    public function add_log_message($group, $field)
    {
        if (!isset($this->message_log[$group])) {
            $this->message_log[$group] = [];
        }

        if (!in_array($field, $this->message_log[$group])) {
            $this->message_log[$group][] = $field;
        }
    }

    /**
     * @param \ImportWP\Common\Model\ImporterModel $importer_model
     * 
     * @return array
     */
    function iwp_acf_fields($importer_model)
    {
        switch ($importer_model->getTemplate()) {
            case 'user':
                $fields = $this->iwp_acf_get_fields('user', 'user');
                break;
            case 'term':
                $taxonomy = $importer_model->getSetting('taxonomy');
                $fields = $this->iwp_acf_get_fields($taxonomy, 'taxonomy');
                break;
            default:
                $post_type = $importer_model->getSetting('post_type');
                $fields = $this->iwp_acf_get_fields($post_type, 'post');
                break;
        }

        return $fields;
    }

    function iwp_acf_get_fields($section, $section_type = 'post')
    {
        $options = [];

        if (is_array($section)) {
            foreach ($section as $item) {
                $options = array_merge($options, $this->iwp_acf_get_fields($item, $section_type));
            }
            return $options;
        }

        switch ($section_type) {
            case 'user':
                $args = ['user_form' => 'all'];
                break;
            case 'taxonomy':
                $args = ['taxonomy' => $section];
                break;
            default:
                $args = ['post_type' => $section];
                break;
        }

        return Helper::get_acf_fields($args);
    }

    /**
     * Register virtual groups
     *
     * @param string[] $groups
     * @param \ImportWP\Common\Importer\ParsedData $data
     * @param \ImportWP\Common\Importer\Template\Template $template
     * @return void
     */
    public function register_virtual_template_group($groups, $data, $template)
    {
        if (is_null($this->groups)) {

            $this->groups = [];

            $importer_model = $template->get_importer();

            $acf_fields = $this->iwp_acf_fields($importer_model);

            foreach ($acf_fields as $field) {
                if (!in_array($field['type'], ['repeater', 'group'])) {
                    continue;
                }

                $this->groups[] = $field['key'];
            }

            Logger::write('register_virtual_template_group -groups=' . implode(', ', $groups));
            $groups = array_merge($groups, $this->groups);
        }

        return $groups;
    }

    function set_value($post_id, $field, $value, $custom_field_record, $prefix)
    {

        $delimiter = apply_filters('iwp/value_delimiter', ',');
        $delimiter = apply_filters('iwp/acf/value_delimiter', $delimiter);
        $delimiter = apply_filters('iwp/acf/' . trim($field['id']) . '/value_delimiter', $delimiter);

        switch ($field['type']) {
            case 'select':

                $value = explode($delimiter, $value);
                $value = array_filter(array_map('trim', $value));

                // only save the first value
                if (!$field['multiple'] && count($value) > 1) {
                    $value = $value[0];
                }

                break;
            case 'file':
            case 'image':

                // TODO: set attachment defaults
                $custom_field_record[$prefix . '_return'] = 'id-serialize';
                $serialized_id = $this->custom_fields->processAttachmentField($value, $post_id, $custom_field_record, $prefix);
                $id_array = maybe_unserialize($serialized_id);
                if (!empty($id_array)) {
                    $value = is_array($id_array) ? intval($id_array[0]) : intval($id_array);
                }

                break;
            case 'gallery':

                // TODO: set attachment defaults
                $custom_field_record[$prefix . '_return'] = 'id-serialize';
                $serialized_id = $this->custom_fields->processAttachmentField($value, $post_id, $custom_field_record, $prefix);
                $value = maybe_unserialize($serialized_id);
                break;
            case 'link':

                $value = $this->parse_serialized_value($value, [
                    'title' => '',
                    'url' => '',
                    'target' => ''
                ]);

                break;
            case 'google_map':

                $value = $this->parse_serialized_value($value, [
                    'address' => '',
                    'lat' => '',
                    'lng' => '',
                    'zoom' => ''
                ]);

                break;
            case 'checkbox':
                $value = explode($delimiter, $value);
                break;
            case 'true_or_false':
                if (strtolower($value) == 'yes' || strtolower($value) == 'true') {
                    $value = 1;
                } elseif (strtolower($value) == 'no' || strtolower($value) == 'false') {
                    $value = 0;
                }
                break;
            case 'date_picker':
                // 20220218
                if (!empty($value)) {
                    $value = date('Ymd', strtotime($value));
                }
                break;
            case 'date_time_picker':
                // 2022-02-18 00:00:00
                if (!empty($value)) {
                    $value = date('Y-m-d H:i:s', strtotime($value));
                }
                break;
            case 'time_picker':
                // 00:00:00
                if (!empty($value)) {
                    $value = date('H:i:s', strtotime($value));
                }
                break;
            case 'post_object':
                // object_id
                break;
            case 'relationship':
                // [object_id]
                break;
            case 'taxonomy':
                // [term_id]
                break;
            case 'user':
                // user_id
                break;
        }

        return apply_filters('iwp/acf/value', $value, $field, $post_id);
    }

    function parse_serialized_value($value, $defaults = [])
    {
        return array_reduce(explode('|', $value), function ($carry, $item) {

            $parts = explode('=', $item);
            if (count($parts) == 2) {
                $k = trim($parts[0]);
                $v = trim($parts[1]);
                if (isset($carry[$k])) {
                    $carry[$k] = $v;
                }
            }

            return $carry;
        }, $defaults);
    }

    public function process_field($result, $post_id, $key, $value, $custom_field_record, $prefix, $importer_model, $custom_field)
    {
        if (strpos($key, 'acf_field::') !== 0) {
            return $result;
        }

        switch ($importer_model->getTemplate()) {
            case 'user':
                $this->set_field_prefix('user_');
                break;
            case 'term':
                $taxonomy = $importer_model->getSetting('taxonomy');
                $this->set_field_prefix($taxonomy . '_');
                break;
            default:
                $this->set_field_prefix(null);
                break;
        }

        $field_key = substr($key, strrpos($key, '::') + strlen('::'));

        $processed = $this->process($post_id, $field_key, $value, $custom_field_record, $prefix);
        if ($processed) {
            $custom_field->virtual_fields[$key] = $processed;
        }

        return $result;
    }

    public function process($post_id, $field_key, $value, $custom_field_record, $prefix)
    {
        $field = $this->get_field_object($field_key);
        $value = $this->set_value($post_id, $field, $value, $custom_field_record, $prefix);

        if (update_field($field_key, $value, $this->prefix($post_id))) {
            return $value;
        }

        return false;
    }

    public function set_field_prefix($prefix)
    {
        $this->field_prefix = $prefix;
    }

    public function prefix($object)
    {
        if (is_null($this->field_prefix)) {
            return $object;
        }

        return $this->field_prefix . $object;
    }

    public function get_field_object($field_key)
    {
        return get_field_object($field_key);
    }

    public function generate_field_map($field_map, $headings, $importer_model)
    {

        $acf_fields = $this->iwp_acf_fields($importer_model);

        $acf_repeater_fields = array_filter($acf_fields, function ($field) {
            return in_array($field['type'], ['repeater', 'group']);
        });


        $defaults = [
            "location" => "",
            "settings._featured" => "no",
            "settings._download" => "remote",
            "settings._ftp_host" => "",
            "settings._ftp_user" => "",
            "settings._ftp_pass" => "",
            "settings._ftp_path" => "",
            "settings._remote_url" => "",
            "settings._local_url" => "",
            "settings._enable_image_hash" => "yes",
            "settings._delimiter" => "",
            "settings._meta._enabled" => "no",
            "settings._meta._alt" => "",
            "settings._meta._title" => "",
            "settings._meta._caption" => "",
            "settings._meta._description" => ""
        ];

        foreach ($acf_repeater_fields as $repeater_group) {

            $fields = $repeater_group['sub_fields'];
            $counter = 0;
            $data = [];

            foreach ($fields as $field) {

                $value = '';

                switch ($field['type']) {
                    case 'image':
                    case 'gallery':
                    case 'file':

                        $index = array_search($repeater_group['name'] . '.' . $field['name'] . '::url', $headings);
                        if ($index !== false) {
                            $value = sprintf('{%d}', $index);
                        } else {
                            break;
                        }

                        $tmp = wp_parse_args([
                            'location' => $value
                        ], $defaults);

                        $data = array_merge($data, array_reduce(array_keys($tmp), function ($carry, $key) use ($field, $tmp) {
                            $carry[$field['key'] . '.' . $key] = $tmp[$key];
                            return $carry;
                        }, []));

                        $value = '';
                        break;
                    default:
                    case 'text':

                        $index = array_search($repeater_group['name'] . '.' . $field['name'], $headings);
                        if ($index !== false) {
                            $value = sprintf('{%d}', $index);
                        }
                        break;
                }

                if ($value !== '') {
                    $data[$field['key']] = $value;
                }
            }

            $row_data = array_reduce(array_keys($data), function ($carry, $key) use ($data, $counter, $repeater_group) {
                $carry[sprintf('%s.%d.%s', $repeater_group['key'], $counter, $key)] = $data[$key];
                return $carry;
            }, []);

            $field_map['map'] = array_merge($field_map['map'], $row_data);

            if (!empty($row_data)) {
                $counter++;
                $field_map['map'][$repeater_group['key'] . '._index'] = $counter;
            }
        }

        return $field_map;
    }

    public function generate_custom_field_map($custom_fields, $fields, $importer_model)
    {

        $acf_fields = $this->iwp_acf_fields($importer_model);

        $acf_custom_fields = array_filter($acf_fields, function ($field) {
            return !in_array($field['type'], ['repeater', 'group']);
        });

        foreach ($acf_custom_fields as $field) {

            $value = '';
            $type = 'text';

            switch ($field['type']) {
                case 'image':
                case 'gallery':
                case 'file':

                    $type = 'attachment';
                    $index = array_search('acf.' . $field['name'] . '::url', $fields);
                    if ($index !== false) {
                        $value = sprintf('{%d}', $index);
                    }

                    break;
                case 'link':

                    $parts = [
                        'title' => '',
                        'url' => '',
                        'target' => '',
                    ];
                    $value = $this->process_field_map_parts($field, $parts, $fields);
                    break;
                case 'google_map':

                    $parts = [
                        'address' => '',
                        'lat' => '',
                        'lng' => '',
                        'zoom' => ''
                    ];
                    $value = $this->process_field_map_parts($field, $parts, $fields);
                    break;
                default:
                case 'text':

                    $index = array_search('acf.' . $field['name'], $fields);
                    if ($index !== false) {
                        $value = sprintf('{%d}', $index);
                    }
                    break;
            }

            if ($value !== '') {
                $custom_fields['acf_field::' . $type . '::' . $field['key']] = $value;
            }
        }

        return $custom_fields;
    }

    function process_field_map_parts($field, $parts, $fields)
    {
        $value = '';

        foreach (array_keys($parts) as $field_id) {
            $index = array_search('acf.' . $field['name'] . '::' . $field_id, $fields);
            if ($index !== false) {
                $parts[$field_id] = sprintf('{%d}', $index);
            }
        }

        $parts = array_reduce(array_keys($parts), function ($carry, $key) use ($parts) {
            if (!empty($parts[$key])) {
                $carry[] = $key . '=' . $parts[$key];
            }
            return $carry;
        }, []);

        if (!empty($parts)) {
            $value = implode('|', $parts);
        } else {
            $index = array_search('acf.' . $field['name'], $fields);
            if ($index !== false) {
                $value = sprintf('{%d}', $index);
            }
        }

        return $value;
    }
}
