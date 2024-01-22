<?php

class PeepSoFieldSelectBool extends PeepSoFieldSelectSingle {

    public static $order = 250;
	public static $admin_label='Select - Yes/No';


	public function __construct($post, $user_id)
	{
        $this->field_meta_keys = array_merge($this->field_meta_keys, $this->field_meta_keys_extra);
        parent::__construct($post, $user_id);

        $this->admin_can_add_delete_options = FALSE;
	}

    // Utils
    public function get_options()
    {
        $options = $this->meta->select_options;
        if(!is_array($options) || count($options) != 2) {
            $options = ['option_0' => __('No', 'peepso-core'), 'option_1' => __('Yes', 'peepso-core')];
            update_post_meta($this->id, 'select_options', $options);
        }

        return $options;
    }
}
