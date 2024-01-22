<?php

class PeepSoConfigSectionCPT extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context='left';
        $this->group_activity();
        // @todo #2157
    }

    /**
     * General Settings Box
     */
    private function group_activity()
    {
        $args = array(
            'public'   => true,
            '_builtin' => false,
        );

        $output = 'objects'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $post_types = get_post_types( $args, $output, $operator );

        if(count($post_types)) {

            foreach ($post_types as $post_type) {

                $name = 'cpt_' . $post_type->name . '_';

                $this->set_field(
                    $name . 'activity_enable',
                    __('Post to Activity Stream', 'peepso-core'),
                    'yesno_switch'
                );


                // Action text
                $this->args('default', PeepSo::get_option('blogposts_activity_type_post_text_default', ''));
                $this->set_field(
                    $name . 'activity_type_post_text',
                    'Action text',
                    'text'
                );

                $this->args('descript', __('The title of  the post will be displayed after the action text as a link', 'peepso-core'));
                $this->set_field(
                    $name . 'activity_title_after_action_text',
                    __('Append title after action text', 'peepso-core'),
                    'yesno_switch'
                );

                $privacy = PeepSoPrivacy::get_instance();
                $privacy_settings = $privacy->get_access_settings();

                $options = array();

                foreach ($privacy_settings as $key => $value) {
                    if (in_array($key, array(30, 40))) {
                        continue;
                    }
                    $options[$key] = $value['label'];
                }

                $this->args('options', $options);

                $this->set_field(
                    $name . 'blogposts_activity_privacy',
                    __('Default privacy', 'peepso-core'),
                    'select'
                );
                $this->set_group(
                    $name . 'general',
                    $post_type->label
                );
            }
        } else {
            $this->set_group(
                 'cpt_general',
                'No custom post types'
            );
        }
    }
}