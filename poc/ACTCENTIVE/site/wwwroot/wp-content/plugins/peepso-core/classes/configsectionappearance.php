<?php

class PeepSoConfigSectionAppearance extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context='left';
        $this->profiles();
        $this->profile_details();

        $this->context='right';
        $this->template();
        $this->general();
        $this->members();
        $this->registration();
        $this->branding();


    }

    private function profiles()
    {
        // Display name style
        $options = apply_filters('peepso_filter_display_name_styles', []);

        $this->args('options', $options);
        $this->set_field(
            'system_display_name_style',
            __('Display name style', 'peepso-core'),
            'select'
        );

        // Allow User To Override Name Setting
        $this->set_field(
            'system_override_name',
            __('Users can override this setting', 'peepso-core'),
            'yesno_switch'
        );

        // Allow Profile Deletion
        $this->args('descript', __('Users can permanently delete their profiles'));
        $this->set_field(
            'site_registration_allowdelete',
            __('Profile deletion', 'peepso-core'),
            'yesno_switch'
        );

        // Profile Likes
        $this->args('descript',__('Users can "like" each other\'s  profiles', 'peepso-core'));
        $this->set_field(
            'site_likes_profile',
            __('Profile likes', 'peepso-core'),
            'yesno_switch'
        );


        // Always link to PeepSo Profile
        $this->set_field(
            'always_link_to_peepso_profile',
            __('Always link to PeepSo profile', 'peepso-core'),
            'yesno_switch'
        );

        $options = [
            0 => 'No',
            1 => 'Yes - classic mode (using filters)',
            2 => 'Yes - aggressive mode (attempt  to force WP "display name" to the PeepSo name)',
        ];
        // Use Peepso names
        $this->args('options', $options);
        $this->set_field(
            'use_name_everywhere',
            __('Use PeepSo names everywhere', 'peepso-core'),
            'select'
        );


        $this->set_field(
            'profile_completeness_hide_no_required_missing',
            __('Hide profile completeness if no required fields are missing', 'peepso-core'),
            'yesno_switch'
        );

        // Do not minimize cover

        /** AVATARS & Covers **/
        // # Separator Avatars
        $this->set_field(
            'separator_avatars',
            __('Avatars', 'peepso-core'),
            'separator'
        );

        // Use Peepso Avatars
        $this->set_field(
            'avatars_wordpress_only',
            __('Use WordPress avatars', 'peepso-core'),
            'yesno_switch'
        );

        // Use Peepso Avatars
        $this->set_field(
            'avatars_wordpress_only_desc',
            __('The users are unable to upload avatars via PeepSo interface. PeepSo will inherit the avatars from your WordPress site.', 'peepso-core'),
            'message'
        );

        // Use Peepso Avatars
        $this->set_field(
            'avatars_peepso_only',
            __('Use PeepSo avatars everywhere', 'peepso-core'),
            'yesno_switch'
        );

        // Use Gravatar Avatars
        $this->set_field(
            'avatars_gravatar_enable',
            __('Allow Gravatar avatars', 'peepso-core'),
            'yesno_switch'
        );

        // # Separator
        $this->set_field(
            'separator_avatars_default',
            __('Default avatars', 'peepso-core'),
            'separator'
        );

        // Use SVG avatars
        $this->args('descript', 'If no avatar is provided, PeepSo will generate an avatar based on user initials or username. The colors will be randomized, unless you decide to use grayscale');
        $this->set_field(
            'avatars_name_based',
            __('Generate name based avatars', 'peepso-core'),
            'yesno_switch'
        );


        $options = [
            0 => 'Black',
            50 => 'Very dark',
            100 => 'Dark',
            125 => 'Medium',
            150 => 'Light (default)',
            200 => 'Very light',
            255 => 'White',
        ];

        $this->args('options', $options);
        $this->set_field(
            'avatars_name_based_background_color',
            __('Background', 'peepso-core'),
            'select'
        );

        $this->set_field(
            'avatars_name_based_background_grayscale',
            __('Grayscale background', 'peepso-core'),
            'yesno_switch'
        );

        $options = [
            '0' => 'Black',
            '50' => 'Very dark (default)',
            '100' => 'Dark',
            '125' => 'Medium',
            '150' => 'Light',
            '200' => 'Very light',
            '255' => 'White',
        ];

        $this->args('options', $options);
        $this->set_field(
            'avatars_name_based_font_color',
            __('Font color', 'peepso-core'),
            'select'
        );

        $this->set_field(
            'avatars_name_based_preview',
            'Preview',
            'yesno_switch'
        );


        /** COVERS **/
        $this->set_field(
            'separator_covers',
            __('Covers', 'peepso-core'),
            'separator'
        );

        $this->args('descript', __('By default the full cover displays only in the header of the "Stream" section'));
        $this->set_field(
            'always_full_cover',
            __('Always use full covers', 'peepso-core'),
            'yesno_switch'
        );

        /** VIP ICONS **/
        $this->set_field(
            'separator_vip',
            __('VIP icons', 'peepso-core'),
            'separator'
        );
        // How to render
        $options = array(
            PeepSoVIP::VIP_ICON_BEFORE_FULLNAME => __('Left','peepso-core'),
            PeepSoVIP::VIP_ICON_AFTER_FULLNAME => __('Right','peepso-core')
        );
        $this->args('options', $options);
        $this->args('default', 1);
        $this->set_field(
            'vipso_where_to_display',
            __('Icon position relative to user name', 'peepso-core'),
            'select'
        );

        $general_config = apply_filters('peepso_vip_general_config', array());

        if(count($general_config) > 0 ) {

            foreach ($general_config as $option) {
                if(isset($option['descript'])) {
                    $this->args('descript', $option['descript']);
                }
                if(isset($option['int'])) {
                    $this->args('int', $option['int']);
                }
                if(isset($option['default'])) {
                    $this->args('default', $option['default']);
                }

                $this->set_field($option['name'], $option['label'], $option['type']);
            }
        }

        $options = array();

        for($i=0; $i<=100;$i++) {
            $options[$i]=$i;
        }

        $this->args('options', $options);
        $this->args('default', 10);

        $this->args('descript', __('Defines how many icons show next to user name in stream, widgets etc.','peepso-core'));
        $this->set_field(
            'vipso_display_how_many',
            __('How many icons to show', 'peepso-core'),
            'select'
        );

        $this->args('default', '0');
        $this->args('descript', __('if user has more icons assigned than the limit, a small indicator with the amount of remaining icons will show. If there is only one remaining icon, it will be simply displayed.', 'peepso-core'));
        $this->set_field(
            'vipso_display_more_icons_count',
            __('Show "more icons" indicator', 'vidso'),
            'yesno_switch'
        );

        $this->args('default', '0');
        $this->args('descript', __('Allow searching for community members based on their VIP icon.', 'peepso-core'));
        $this->set_field(
            'vipso_member_search',
            __('Allow member search by icon', 'vidso'),
            'yesno_switch'
        );

        $this->set_field(
            'vipso_guide1',
            '<b>Managing the icons</b>: please go to  <a href="admin.php?page=peepso-manage&tab=vip-icons"><i>VIP Icons</i></a>. You can  also find it inside <i>PeepSo</i> option in the side menu.',
            'message'
        );

        $this->set_field(
            'vipso_guide2',
            '<b>Assigning icons</b>: edit a particular user and configure their <i>VIP</i> section. Go to <a href="users.php">users</a> or try it on <a href="profile.php">yourself</a>',
            'message'
        );

        $this->set_field(
            'vipso_guide4',
            '<b>Default icons</b>: made by <a href="http://www.flaticon.com/authors/roundicons" title="Roundicons" target="_blank">Roundicons</a> from <a href="http://www.flaticon.com" title="Flaticon" target="_blank">www.flaticon.com</a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a>',
            'message'
        );


        $this->set_field(
            'separator_adv',
            __('Advanced', 'peepso-core'),
            'separator'
        );

        $links = implode(', ',array_keys(apply_filters('peepso_navigation_profile',[])));


        $this->args('descript',
            __('One entry per line. If you skip any items, they will follow their default order.', 'peepso-core').'<br/>'.
            sprintf(__('Current order: %s', 'peepso-core'), $links)
        );
        $this->set_field(
            'profile_navigation_order',
            __('Profile tabs order', 'peepso-core'),
            'textarea'
        );



        // Build Group
        $this->set_group(
            'profiles',
            __('User profiles', 'peepso-core')
        );
    }

    private function profile_details() {
        /** PROFILE DETAILS */

        $privacy = PeepSoPrivacy::get_instance();
        $privacy_settings = $privacy->get_access_settings();

        $options = array();

        foreach($privacy_settings as $key => $value) {
            $options[$key] = $value['label'];
        }
        $options = [99 => __('Hidden', 'peepso-core')] + $options;


        $details = PeepSoProfile::get_instance()->interactions(TRUE, TRUE);

        foreach($details as $section => $config) {
            if(isset($config['is_details']) && $config['is_details']) {

                $this->set_field(
                    "separator_$section",
                    $config['title'],
                    'separator'
                );

                $this->args('options', $options);
                $this->args('default', 10);

                $this->set_field(
                    'profile_'.$section.'_privacy_default',
                    __('Default privacy', 'peepso-core'),
                    'select'
                );
            }
        }

        // Build Group
        $this->set_group(
            'profile_details',
            __('Profile details', 'peepso-core'),
            ' "Profile details" are displayed in the profile view under the cover. '
            .' Admins will always see the details unless they are set to "hidden". '
            .' <br><br> '
            .' These settings do not control the privacy of the profile tabs. '
        );
    }

    private function registration()
    {
        /** CUSTOM TEXT **/

        // # Separator Callout
        $this->set_field(
            'separator_callout',
            __('Customize text', 'peepso-core'),
            'separator'
        );

        // # Callout Header
        $this->set_field(
            'site_registration_header',
            __('Callout header', 'peepso-core'),
            'text'
        );

        // # Callout Text
        $this->set_field(
            'site_registration_callout',
            __('Callout text', 'peepso-core'),
            'text'
        );

        // # Button Text
        $this->set_field(
            'site_registration_buttontext',
            __('Button text', 'peepso-core'),
            'text'
        );

        /** LANDING PAGE IMAGE **/
        // # Separator Landing Page
        $this->set_field(
            'separator_landing_page',
            __('Landing page image', 'peepso-core'),
            'separator'
        );

        // # Message Logging Description
        $this->set_field(
            'suggested_message_landing_page',
            // todo: filter for landing page image size
            __('Suggested size is: 1140px x 469px.', 'peepso-core'),
            'message'
        );

        // Landing Page Image
        $default = PeepSo::get_option('landing_page_image', PeepSo::get_asset('images/landing/register-bg.jpg'));
        $landing_page = !empty($default) ? $default : PeepSo::get_asset('images/landing/register-bg.jpg');
        $this->args('value', $landing_page);
        $this->set_field(
            'landing_page_image',
            __('Selected Image', 'peepso-core'),
            'text'
        );

        $default = PeepSo::get_option('landing_page_image_default', PeepSo::get_asset('images/landing/register-bg.jpg'));
        $this->args('value', $default);
        $this->set_field(
            'landing_page_image_default',
            '',
            'text'
        );
        // Build Group
        $this->set_group(
            'registration',
            __('Registration', 'peepso-core')
        );
    }

    private function branding() {

        if(PeepSo3_Helper_Addons::license_is_free_bundle(FALSE)) {
            $this->set_field(
                'system_show_peepso_link_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text').'<br/>'.PeepSo3_Helper_Addons::get_upsell('box'),
                'message'
            );
        } else {
            // Show "Powered By Peepso" Link
            $this->set_field(
                'system_show_peepso_link',
                __('Enabled', 'peepso-core'),
                'yesno_switch'
            );
        }

        $this->set_group(
            'branding',
            __('"Powered by PeepSo" in the front-end and email footers', 'peepso-core')
        );
    }

    private function template() {
        $override_message = apply_filters('peepso_theme_override', false);
        if (is_string($override_message)) {
            $this->set_field(
                'site_css_template_override',
                $override_message,
                'message'
            );
        } else {

            // Primary CSS Template
            $options = array(
                '' => __('Light', 'peepso-core'),
            );

            $dir = plugin_dir_path(__FILE__) . '/../templates/css';

            $dir = scandir($dir);
            $from_key = array('template-', '.css');
            $to_key = array('');

            $from_name = array('_', '-');
            $to_name = array(' ', ' ');

            foreach ($dir as $file) {
                if ('template-' == substr($file, 0, 9) && !strpos($file, 'rtl') && !strpos($file, 'round')) {

                    $key = str_replace($from_key, $to_key, $file);
                    $name = str_replace($from_name, $to_name, $key);
                    $options[$key] = ucwords($name);
                }
            }

            $this->args('options', $options);
            $this->args('descript', sprintf(
                __('Pick a color from the list that suits your site best. If the list doesn’t contain the color you’re looking for you can always use %s.', 'peepso-core'),
                '<a target="_blank" href="https://peep.so/docs_css_overrides">' . __('CSS overrides', 'peepso-core') . ' <i class="fa fa-external-link"></i></a>'
            ));
            $this->set_field(
                'site_css_template',
                __('Color scheme', 'peepso-core'),
                'select'
            );

        }
        // Build Group
        $this->set_group(
            'appearance_general',
            __('Color scheme', 'peepso-core')
        );
    }
    private function general()
    {
        // Disable PeepSo navbar
        $this->set_field(
            'disable_navbar',
            __('Disable PeepSo navigation bar', 'peepso-core'),
            'yesno_switch'
        );



        // Post age preferences
        $options = array(
            0 => __('never', 'peepso-core'),
            -1 => __('always', 'peepso-core'),
            // Days
            24*1 => sprintf(__('if older than %d %s', 'peepso-core'), 1,__('day','peepso-core')),
            24*2 => sprintf(__('if older than %d %s', 'peepso-core'), 2,__('days','peepso-core')),
            24*3 => sprintf(__('if older than %d %s', 'peepso-core'), 3,__('days','peepso-core')),
            24*4 => sprintf(__('if older than %d %s', 'peepso-core'), 4,__('days','peepso-core')),
            24*5 => sprintf(__('if older than %d %s', 'peepso-core'), 5,__('days','peepso-core')),
            24*6 => sprintf(__('if older than %d %s', 'peepso-core'), 6,__('days','peepso-core')),
            // Weeks
            24*7*1 => sprintf(__('if older than %d %s', 'peepso-core'), 1,__('week','peepso-core')),
            24*7*2 => sprintf(__('if older than %d %s', 'peepso-core'), 2,__('weeks','peepso-core')),
            24*7*3 => sprintf(__('if older than %d %s', 'peepso-core'), 3,__('weeks','peepso-core')),
            24*7*4 => sprintf(__('if older than %d %s', 'peepso-core'), 4,__('weeks','peepso-core')),
        );

        $this->args('options', $options);

        $this->args('descript', __('"Relative" date is a human readable date, for example "3 days ago"','peepso-core').'<br/>'.sprintf(__('"Absolute" date is a simple datestamp such as %s','peepso-core'), date(get_option('date_format').' '.get_option('time_format'))));
        $this->set_field(
            'absolute_dates',
            __('Use absolute dates', 'peepso-core'),
            'select'
        );

        $this->args('descript', __('PeepSo needs a date format without a year to let users hide their birthday year','peepso-core'));
        $this->args('default', 'F j');
        $options = array(
            'F j' => date_i18n('F j'),
            'M j' => date_i18n('M j'),
            'm d' => date_i18n('m d'),
            'm/d' => date_i18n('m/d'),
            'd/m' => date_i18n('d/m'),
            'custom' => __('Custom', 'peepso-core'),
        );
        $this->args('options', $options);

        $this->set_field(
            'date_format_no_year',
            __('Date format (no year)', 'peepso-core'),
            'select'
        );

        // Custom date format
        $this->args('default', 'F j');

        // NO LANGUAGE DOMAIN, this is a WordPress string
        $this->args('descript', str_replace('href','target="blank" href', __('<a href="https://wordpress.org/support/article/formatting-date-and-time/">Documentation on date and time formatting</a>')));

        $this->set_field(
            'date_format_no_year_custom',
            __('Custom format', 'peepso-core'),
            'text'
        );

        $this->set_field(
            'hovercards_enable',
            __('Hover Cards', 'peepso-core'),
            'yesno_switch'
        );

        /*** WordPress toolbar **/
        $this->set_field(
            'adminbar_separator',
        'WordPress Toolbar',
        'separator'
        );

        $options = [
            '1' => __('Always', 'peepso-theme-gecko'),
            '2' => __('Only for Administrators', 'peepso-theme-gecko'),
            '3' => __('Never', 'peepso-theme-gecko'),
            '4' => __('Let WordPress decide', 'peepso-theme-gecko'),
        ];
        $this->args('options', $options);
        $this->args('descript', __('Applies to front-end only','peepso-core'));
        $this->set_field(
            'wp_toolbar_enable',
            __('Enabled', 'peepso-core'),
            'select'
        );

        // Show notification icons on WP Toolbar
        $this->set_field(
            'site_show_notification_on_navigation_bar',
            __('Notifications', 'peepso-core'),
            'yesno_switch'
        );

        // Disable PeepSo navbar
        $this->set_field(
            'override_admin_navbar',
            __('Use PeepSo profile navigation', 'peepso-core'),
            'yesno_switch'
        );


        // Build Group
        $this->set_group(
            'appearance_general',
            __('General', 'peepso-core')
        );
    }

    private function members()
    {
        // Default Sorting
        $options = array(
            '' => __('Alphabetical', 'peepso-core'),
            'peepso_last_activity' => __('Recently online', 'peepso-core'),
            'registered' => __('Latest members', 'peepso-core')
        );

        if (PeepSo::get_option('site_likes_profile', TRUE)) {
            $options['most_liked'] = __('Most liked', 'peepso-core');
        }

        $options['most_followers'] = __('Most followers', 'peepso-core');

        $this->args('options', $options);

        $this->set_field(
            'site_memberspage_default_sorting',
            __('Default Sorting', 'peepso-core'),
            'select'
        );

        // Allow users to hide themselves from all user listings
        $this->args('descript', __('Users can hide from Members Page, Widgets etc', 'peepso-core'));
        $this->set_field(
            'allow_hide_user_from_user_listing',
            __('Users can hide from user listings', 'peepso-core'),
            'yesno_switch'
        );

        // allow guest access to Members listing
        $this->args('default', 0);
        $this->set_field(
            'allow_guest_access_to_members_listing',
            __('Allow guest access to members listing', 'peepso-core'),
            'yesno_switch'
        );

        // Show "Powered By Peepso" Link
        $this->set_field(
            'members_hide_before_search',
            __('Only show members when something is searched', 'peepso-core'),
            'yesno_switch'
        );

        // Show "Powered By Peepso" Link
        $this->set_field(
            'members_email_searchable',
            __('Allow searching user emails', 'peepso-core'),
            'yesno_switch'
        );


        // Build Group
        $this->set_group(
            'appearance_members',
            __('Member listings', 'peepso-core')
        );
    }
}
