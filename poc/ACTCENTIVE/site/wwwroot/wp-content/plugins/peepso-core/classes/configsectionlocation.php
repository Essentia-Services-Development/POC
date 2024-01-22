<?php

class PeepSoConfigSectionLocation extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context='left';
        $this->location();

        $this->context='right';
        //$this->user_seach();
    }

    private function location()
    {

        // Enable Location
        $this->set_field(
            'location_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );


        ob_start();
        echo __('A Google maps API key is required for the Location suggestions to work properly','peepso-core') . '<br/>' . __('You can get the API key', 'peepso-core'); ?>
        <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
            <?php echo __('here', 'peepso-core');?>
        </a>.

        <?php
        $this->args('descript', ob_get_clean());
        $this->set_field(
            'location_gmap_api_key',
            __('Google Maps API Key (v3)', 'peepso-core'),
            'text'
        );

        $this->set_group(
            'location',
            __('Location', 'peepso-core')
        );
    }

    private function user_seach()
    {
        $this->set_field(
            'location_user_search_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->args('options',['mi'=> __('Miles','peepso-core'),'km'=> __('Kilometres','peepso-core')]);
        $this->set_field(
            'location_user_search_units',
            __('Default units', 'peepso-core'),
            'select'
        );

        $PeepSoUser = PeepSoUser::get_instance(0);
        $profile_fields = new PeepSoProfileFields($PeepSoUser);
        $fields = $profile_fields->load_fields();

        $options = [0=>'-- '.__('Select a field','peepso-core').' --'];

        foreach($fields as $id=>$field) {
            // Remove fields that are not of type Location
            if(!$field instanceof PeepSoFieldLocation) {
                continue;
            }

            $label = $field->title . " (ID: {$field->id})";
            $options[$field->id] = $label;
        }


        $this->args('options', $options);
        $this->set_field(
            'location_user_search_field',
            __('Profile field', 'peepso-core'),
            'select'
        );

        $this->set_group(
            'user_search',
            __('User search', 'peepso-core')
        );
    }
}
?>
