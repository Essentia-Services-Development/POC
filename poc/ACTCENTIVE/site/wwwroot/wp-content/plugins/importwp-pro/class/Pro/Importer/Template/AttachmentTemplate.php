<?php

namespace ImportWP\Pro\Importer\Template;

use ImportWP\Common\Importer\ParsedData;
use ImportWP\Common\Model\ImporterModel;
use ImportWP\EventHandler;

class AttachmentTemplate extends \ImportWP\Common\Importer\Template\AttachmentTemplate
{
    /**
     * @var CustomFields
     */
    public $custom_fields;

    public function __construct(EventHandler $event_handler)
    {
        parent::__construct($event_handler);
        $this->groups[] = 'custom_fields';
        $this->custom_fields = new CustomFields($this, $event_handler);
        $this->field_options = array_merge($this->field_options, $this->custom_fields->register_field_callbacks());
    }


    public function register()
    {
        $groups = parent::register();
        $groups[] = $this->custom_fields->register_fields();
        return $groups;
    }

    public function process($post_id, ParsedData $data, ImporterModel $importer_model)
    {
        parent::process($post_id, $data, $importer_model);

        $data = $this->custom_fields->process($post_id, $data, $importer_model);
        return $data;
    }

    /**
     * @param string $message
     * @param int $id
     * @param ParsedData $data
     * @return $string
     */
    public function display_record_info($message, $id, $data)
    {
        $message = parent::display_record_info($message, $id, $data);
        $message .= $this->custom_fields->log_message($id, $data);
        return $message;
    }

    public function get_permission_fields($importer_model)
    {
        $permission_fields = parent::get_permission_fields($importer_model);
        $custom_permission_fields = $this->custom_fields->get_permission_fields($importer_model);

        return array_merge($permission_fields, $custom_permission_fields);
    }
}
