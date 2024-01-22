<?php

class PeepSoConfigSectionTutorLMS extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context='left';
        $this->profiles();
        $this->profiles_two_column();
        $this->profiles_text();
        $this->profiles_featured_images();

        $this->context='right';
        $this->activity_enroll();
        $this->activity_complete();
        // $this->activity_review();
        $this->chat();
        $this->advanced();

    }

    private function profiles() {

        // Enable Profiles Tab
        $this->args('descript', __('Show "TutorLMS" tab in user profiles.','peepsotutorlms'));
        $this->set_field(
            'tutor_profile_enable',
            __('Enabled', 'peepsotutorlms'),
            'yesno_switch'
        );

        // Tag
        $this->args('descript', __('Optional. Comma separated list of course IDs you want to hide from the profiles.','peepsotutorlms'));
        $this->set_field(
            'tutor_profile_hide_courses',
            __('Hide courses', 'peepsotutorlms'),
            'text'
        );

        /** Dashboard separator */
        $this->set_field(
            'tutor_profile_enable_dashboard_seperator',
            __( 'Dashboard', 'peepsotutorlms' ),
            'separator'
        );

        /** Dashboard on/off */
        $this->args('descript',__('When enabled, show link to TutorLMS dashboard page.', 'peepsotutorlms'));
        $this->set_field(
            'tutor_profile_enable_dashboard',
            __('Enabled', 'peepsotutorlms'),
            'yesno_switch'
        );

        /** Dashboard label */
        $this->args('descript','Leave empty for default');
        $this->set_field(
            'tutor_profile_dashboard_label',
            __( 'Label', 'peepsotutorlms' ),
            'text'
        );

        // Set group
        $this->set_group(
            'tutor_profiles',
            __('Profiles', 'peepsotutorlms')
        );
    }

    private function profiles_two_column()
    {
        $this->set_field(
            'tutor_profile_two_column_enable',
            __('Enabled', 'peepsotutorlms'),
            'yesno_switch'
        );

        $this->args('int', TRUE);
        $this->args('default', 350);

        $this->set_field(
            'tutor_profile_two_column_height',
            __('Box height (px)', 'peepsotutorlms'),
            'text'
        );


        $this->set_group(
            'tutor_two_column',
            __('Two column layout', 'peepsotutorlms')
        );

    }

    /**
     * Text Parsing
     */
    private function profiles_text()
    {
        $this->args('default', 1);
        $this->set_field(
            'tutor_profile_titles',
            __('Course titles', 'peepsotutorlms'),
            'yesno_switch'
        );

        $this->args('int', TRUE);
        $this->args('default', 50);
        $this->args('descript', __('0 for no content', 'peepsotutorlms'));
        $this->set_field(
            'tutor_profile_content_length',
            __('Description length (words)', 'peepsotutorlms'),
            'text'
        );

        $this->args('descript', __('Forced removal of some shortcodes immune to native WP methods (eg Divi Builder and similar). This is an experimental feature, we recommend using plain-text excerpts instead.' ,'peepsotutorlms'));
        $this->set_field(
            'tutor_profile_content_force_strip_shortcodes',
            __('Aggressive shortcode removal', 'peepsotutorlms'),
            'yesno_switch'
        );

        $this->set_group(
            'tutor_text',
            __('Text', 'peepsotutorlms')
        );
    }

    private function profiles_featured_images()
    {
        $this->set_field(
            'tutor_profile_featured_image_enable',
            __('Show featured images', 'peepsotutorlms'),
            'yesno_switch'
        );

        $this->args('descript', __('Display an empty box if an image is not found (to maintain the layout)', 'peepsotutorlms'));
        $this->set_field(
            'tutor_profile_featured_image_enable_if_empty',
            __('Placeholder', 'peepsotutorlms'),
            'yesno_switch'
        );

        $options = array(
            'top'   => __('Top (rectangle)', 'peepsotutorlms'),
            'left'  => __('Left (square)', 'peepsotutorlms'),
            'right' => __('Right (square)', 'peepsotutorlms'),
        );

        $this->args('options', $options);

        $this->set_field(
            'tutor_profile_featured_image_position',
            __('Position', 'peepsotutorlms'),
            'select'
        );

        $this->args('int', TRUE);
        $this->args('default', 150);

        // Once again the args will be included automatically. Note that args set before previous field are gone
        $this->set_field(
            'tutor_profile_featured_image_height',
            __('Height (px)', 'peepsotutorlms'),
            'text'
        );


        $this->set_group(
            'tutor_featured_image',
            __('Featured Images', 'peepsotutorlms')
        );

    }

    private function activity_enroll() {

        // Enable Profiles Tab
        $this->args('descript', __('Creates an activity entry when user enrolls in a course.','peepsotutorlms'));
        $this->set_field(
            'tutor_activity_enroll',
            __('Enabled', 'peepsotutorlms'),
            'yesno_switch'
        );


        // Enrolled Header Text
        $this->args('descript', __('Leave empty for default','peepsotutorlms'));
        $this->set_field(
            'tutor_activity_enroll_action_text',
            __('Action text', 'peepsotutorlms'),
            'text'
        );

        // Privacy
        $privacy = PeepSoPrivacy::get_instance();
        $privacy_settings = $privacy->get_access_settings();

        $options = array();

        foreach($privacy_settings as $key => $value) {
            if(in_array($key, array(30,40))) { continue; }
            $options[$key] = $value['label'];
        }

        $this->args('options', $options);

        $this->set_field(
            'tutor_activity_enroll_privacy',
            __('Default privacy', 'peepsotutorlms'),
            'select'
        );

        // Set group
        $this->set_group(
            'tutor_activity_enroll_group',
            __('Action - user enrolled in a course', 'peepsotutorlms')
        );
    }

    private function activity_review() {

        $this->args('descript', __('Creates an activity entry when user reviews a course.','peepsotutorlms'));
        $this->set_field(
            'tutor_activity_review',
            __('Enabled', 'peepsotutorlms'),
            'yesno_switch'
        );


        // Enrolled Header Text
        $this->args('descript', __('Leave empty for default','peepsotutorlms'));
        $this->set_field(
            'tutor_activity_review_action_text',
            __('Action text', 'peepsotutorlms'),
            'text'
        );

        // Privacy
        $privacy = PeepSoPrivacy::get_instance();
        $privacy_settings = $privacy->get_access_settings();

        $options = array();

        foreach($privacy_settings as $key => $value) {
            if(in_array($key, array(30,40))) { continue; }
            $options[$key] = $value['label'];
        }

        $this->args('options', $options);

        $this->set_field(
            'tutor_activity_review_privacy',
            __('Default privacy', 'peepsotutorlms'),
            'select'
        );

        // Set group
        $this->set_group(
            'tutor_activity_review_group',
            __('Action - user reviewed a course', 'peepsotutorlms')
        );
    }

    private function activity_complete() {

        // Enable Profiles Tab
        $this->args('descript', __('Creates an activity entry when user completes a course.','peepsotutorlms'));
        $this->set_field(
            'tutor_activity_complete',
            __('Enabled', 'peepsotutorlms'),
            'yesno_switch'
        );


        // Action text
        $this->args('descript', __('Leave empty for default','peepsotutorlms'));
        $this->set_field(
            'tutor_activity_complete_action_text',
            __('Action text', 'peepsotutorlms'),
            'text'
        );

        // Privacy
        $privacy = PeepSoPrivacy::get_instance();
        $privacy_settings = $privacy->get_access_settings();

        $options = array();

        foreach($privacy_settings as $key => $value) {
            if(in_array($key, array(30,40))) { continue; }
            $options[$key] = $value['label'];
        }

        $this->args('options', $options);

        $this->set_field(
            'tutor_activity_complete_privacy',
            __('Default privacy', 'peepsotutorlms'),
            'select'
        );

        // Set group
        $this->set_group(
            'tutor_activity_complete_group',
            __('Action - user completed a course', 'peepsotutorlms')
        );
    }

    private function chat()
    {
        if(!class_exists('PeepSoMessagesPlugin')) {
            $url = '<a href="https://peepso.com/pricing" target="_blank">PeepSo Core: Chat</a>';
            $this->set_field(
                'tutor_submissions_enable_descript',
                sprintf(__('This feature requires the %s plugin.', 'peepsotutorlms'), $url),
                'message'
            );
        } else {
            // Enable Chat button
            $this->args('descript', __('Enable "Chat" Button in instructor widgets', 'peepsotutorlms'));
            $this->set_field(
                'tutor_chat_enable',
                __('Enable "Chat" Button', 'peepsotutorlms'),
                'yesno_switch'
            );
        }
        // Build Group
        $this->set_group(
            'chat',
            __('Chat Integration', 'peepsotutorlms')
        );
    }

    private function advanced() {

        // Profile segment label
        $this->args('descript', __('Leave empty for default value', 'peepsotutorlms'));
        $this->set_field(
            'tutor_navigation_profile_label',
            __('Profile label', 'peepsotutorlms'),
            'text'
        );

        // Profile segment slug
        $this->args('descript', __('Leave empty for default value', 'peepsotutorlms') . '. Example: /profile/?' . PeepSoUser::get_instance()->get_username() . '/' . PeepSo::get_option('tutor_navigation_profile_slug', 'courses', TRUE));
        $this->set_field(
            'tutor_navigation_profile_slug',
            __('Profile slug', 'peepsotutorlms'),
            'text'
        );

        // Profile segment icon
        $this->args('descript', __('FontAwesome (or similar). Leave empty for default value', 'peepsotutorlms'));
        $this->set_field(
            'tutor_navigation_profile_icon',
            __('Custom icon CSS class', 'peepsotutorlms'),
            'text'
        );

        // Set group
        $this->set_group(
            'tutor_advanced',
            __('Advanced', 'peepsotutorlms'),
            sprintf(__('This section is for <b>advanced users only</b>.<br/>When modifying the profile slug, please do NOT use any keyword already in use (eg %s).', 'peepso-wpadverts'), '"photos", "videos", "groups"')
        );
    }

}