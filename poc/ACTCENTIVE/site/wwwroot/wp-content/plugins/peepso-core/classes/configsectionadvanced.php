<?php

class PeepSoConfigSectionAdvanced extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context='full';
        if(TRUE) {
            $this->filesystem();
            $this->uninstall();
        }

        $this->context = 'left';
        if(TRUE) {
            $this->opengraph();
            $this->reactions();
            $this->socialsharing();
            $this->gdpr();
            $this->compatibility();
        }

        $this->context = 'right';
        if(TRUE) {
            $this->stats();
            $this->performance();
            $this->cron();
            $this->cache();
            $this->storage();
            $this->security();
            $this->debug();
        }
    }

    private function stats() {
        $desc = Peepso3_Stats::$desc . '<br><br><a target="_blank" href="' . admin_url('admin.php?page=peepso_config&tab=advanced&peepso_stats_details') . '">What is being tracked?</a>';

        if(PeepSo3_Helper_Addons::license_is_free_bundle(FALSE)) {
            PeepSoConfigSettings::get_instance()->set_option('optin_stats', 1);
            $this->set_field(
                'optin_stats_disabled',
                PeepSo3_Helper_Remote_Content::get('free_bundle_disabled_text'),
                'message'
            );

            $this->set_field(
                'optin_stats_disabled_desc',
                $desc,
                'message'
            );


        } else {
            $this->args('descript', $desc);
            $this->set_field(
                'optin_stats',
                __('Enable usage tracking', 'peepso-core'),
                'yesno_switch'
            );
        }

        $this->set_group(
            'stats',
            __('Help us improve PeepSo!', 'peepso-core')
        );
    }

    private function gdpr() {
        $section = 'gdpr_';


        $message = __('The EU General Data Protection Regulation (GDPR, or EUGDPR for short) is a regulation in European Union law on data protection and privacy for all individuals within the European Union. All businesses and websites processing personal information of EU citizens must abide by this law, including the right to be forgotten (data deletion), the right to full data download (export) etc. You can read more about it ', 'peepso-core');
        $message .= '<a href="http://peep.so/gdpr" target="_blank">';
        $message .= __('here', 'peepso');
        $message .= '</a>';

        $this->set_field(
            $section, $message, 'message'
        );

        $this->set_field(
            $section . 'enable',
            __('Enable GDPR Compliance', 'peepso-core'),
            'yesno_switch'
        );

        // # Full HTML
        // # Move to stage 2
        // $this->args('raw', TRUE);
        // $this->args('validation', array('custom'));
        // $this->args('validation_options',
        //     array(
        //     'error_message' => __('Missing variable {data_contents} or {data_title} or {data_name} or {data_sidebar}', 'peepso-core'),
        //     'function' => array($this, 'check_gdpr_template_layout')
        //     )
        // );

        // $this->set_field(
        //     $section . 'personal_data_template_html',
        //     __('Override entire HTML Template', 'peepso-core'),
        //     'textarea'
        // );

        // Build Group
        $this->set_group(
            'gdpr', __('GDPR Compliance', 'peepso-core')
        );
    }

    private function compatibility() {

        $this->args('descript', __('Attempts to fix some issues specific to WordPress.com and JetPack','peepso-core'));
        $this->set_field(
            'compatibility_wordpress_com',
            __('WordPress.com / JetPack', 'peepso-core'),
            'yesno_switch'
        );


        $this->args('descript', __('Attempts to fix Divi Builder styles that are known to conflict with PeepSo','peepso-core'));
        $this->set_field(
            'compatibility_divi',
            __('Divi Builder', 'peepso-core'),
            'yesno_switch'
        );

        // Build Group
        $this->set_group(
            'compatibility', __('Compatibility', 'peepso-core')
        );
    }

    private function filesystem()
    {

        // Message Filesystem
        $this->set_field(
            'system_filesystem_warning',
            __('This setting is to be changed upon very first PeepSo activation or in case of site migration. If changed in any other case it will result in missing content including user avatars, covers, photos etc. (error 404).', 'peepso-core'),
            'warning'
        );

        // Message Filesystem
        $this->set_field(
            'system_filesystem_description',
            sprintf(__('PeepSo allows users to upload images that are stored on your server. Enter a location where these files are to be stored.<br/>This must be a directory that is writable by your web server and and is accessible via the web. If the directory specified does not exist, it will be created.<br/>When empty, PeepSo uses following directory: <b>%s</b>', 'peepso-core'), WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'peepso'),
            'message'
        );

        $this->args('class','col-xs-12');
        $this->args('field_wrapper_class','controls col-sm-10');
        $this->args('field_label_class', 'control-label col-sm-2');

        $this->args('validation', array('custom'));
        $this->args('validation_options',
            [
                [
                    'error_message' => __('Can not write to directory', 'peepso-core'),
                    'function' => array($this, 'check_wp_filesystem')
                ],
            ]
        );
        // Uploads
        $this->set_field(
            'site_peepso_dir',
            __('Uploads Directory', 'peepso-core'),
            'text'
        );

        $this->set_group(
            'filesystem',
            __('File System Override', 'peepso-core')
        );
    }

    private function debug()
    {
        // Logging
        $this->args('descript', __('ON: various debug information is written to a log file.','peepso-core').'<br/>'.__('This can impact website speed and should ONLY be enabled when someone is debugging PeepSo.', 'peepso-core'));
        $this->set_field(
            'system_enable_logging',
            __('PeepSo debug log', 'peepso-core'),
            'yesno_switch'
        );

        // Logging
        $this->args('descript', __('ON: a Developer Tools are added to the WordPress admin menu.','peepso-core'));
        $this->set_field(
            'show_developer_tools',
            __('Show Developer Tools', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_group(
            'advanced_debug',
            __('Maintenance & debugging', 'peepso-core')
        );
    }

    private function performance()
    {
        // User blocking
        $this->args( 'descript', __( 'Users will be able to block each other. This feature might degrade performance for users with many blocks.', 'peepso-core' ) );
        $this->args('default', 0);
        $this->set_field(
            'user_blocking_enable',
            __('User blocking', 'peepso-core'),
            'yesno_switch'
        );

        // Infinite scroll
        $options = array(
            0 => __('Disable', 'peepso-core'),
            1 => __('Always', 'peepso-core'),
            2 => __('Mobile', 'peepso-core'),
            3 => __('Desktop', 'peepso-core'),
        );

        $this->args('options', $options);
        $this->args('descript', __('Disables infinite loading of activity stream posts, member listings etc. To load more content users have to press "load more" button.', 'peepso-core'));
        $this->set_field(
            'loadmore_enable',
            __('Enable "load more:"', 'peepso-core'),
            'select'
        );

        // Repeat "load more" button?
        $this->args('default', 0);

        $options=array();
        for($i = 0; $i<=50; $i+=2){
            $options[$i]= sprintf(__('Every %d items', 'peepso-core'), $i);
            if($i == 0) {
                $options[$i] = __('No', 'peepso-core');
            }
        }

        $this->args('options', $options);

        $this->args('descript', __('By default all posts load in an "infinite scroll".','peepso-core').'<br>'.__('You can choose to have a specified batch of posts to load before showing the "load button" again.','peepso-core'));
        $this->set_field(
            'loadmore_repeat',
            __('Repeat "load more" button?', 'peepso-core'),
            'select'
        );

        // Build Group
        $this->set_group(
            'performance',
            __('Performance', 'peepso-core')
        );
    }

    private function cron()
    {
        $docs = '<br/><a href="https://peep.so/docs_cron" target="_blank">' . __('Documentation','peepso-core') . '</a>';
        $descript = 'Disabled: use WP cron. Enabled: use external cron.' . $docs;

        // MailQueue
        $this->args('descript', $descript);
        $this->set_field(
            'disable_mailqueue',
            __('Email Queue', 'peepso-core'),
            'yesno_switch',
            'Disabled: use WP cron. Enabled: use external cron'
        );

        // Maintenance
        $this->args('descript', $descript);
        $this->set_field(
            'disable_maintenance',
            __('Maintenance scripts', 'peepso-core'),
            'yesno_switch'
        );

        // GDPR
        $this->args('descript', $descript);
        $this->set_field(
            'gdpr_external_cron',
            __('GDPR Exports', 'peepso-core'),
            'yesno_switch'
        );
        // Build Group
        $this->set_group(
            'cronn',
            __('Cron jobs', 'peepso-core'),
            'Some tasks - called cron jobs - need to be performed automatically on a schedule. By default, PeepSo uses the WordPress cron, which runs while the page is loaded. For better performance and stability, you can enable external cron jobs for the PeepSo tasks.'.$docs
        );
    }

    private function cache()
    {

        // Cache busting
        $this->args(
            'descript',
            __('Enables cache-busting query args for all PeepSo assets (JavaScript and CSS). Users PHP to detect the last file modification time.', 'peepso-core')
            . '<br/>' . __('Might cause longer PHP execution times.', 'peepso-core')
        );

        $this->set_field(
            'cache_busting',
            __('Cache Busting (BETA)', 'peepso-core'),
            'yesno_switch'
        );


        // Build Group
        $this->set_group(
            'cache',
            __('Cache', 'peepso-core')
        );
    }

    private function storage()
    {
        // Avatar size
        $default = 250;

        $this->args('default', $default);

        $options=array();
        for($i = 100; $i<=500; $i+=50){
            $options[$i]= sprintf(__('%d pixels', 'peepso-core'), $i);
            if($i == $default) {
                $options[$i] .= ' ('.__('default', 'peepso-core').')';
            }
        }

        $this->args('options', $options);

        $this->args('descript', __('Bigger images use more storage, but will look better - especially on high resolution screens.','peepso-core'));
        $this->set_field(
            'avatar_size',
            __('Avatar size', 'peepso-core'),
            'select'
        );

        // Avatar quality
        $default = 85;
        $this->args('default', $default);

        $options=array();
        for($i = 50; $i<=100; $i+=5){
            $options[$i]= sprintf(__('%d%%', 'peepso-core'), $i);
            if($i == $default) {
                $options[$i] .= ' ('.__('default', 'peepso-core').')';
            }
        }

        $this->args('options', $options);

        $this->args('descript', __('Higher quality will use more storage, but the images will look better','peepso-core'));
        $this->set_field(
            'avatar_quality',
            __('Avatar quality', 'peepso-core'),
            'select'
        );

        // Cover width
        $default = 3000;

        $this->args('default', $default);

        $options=array();
        for($i = 1000; $i<=5000; $i+=500){
            $options[$i]= sprintf(__('%d pixels', 'peepso-core'), $i);
            if($i == $default) {
                $options[$i] .= ' ('.__('default', 'peepso-core').')';
            }
        }

        $this->args('options', $options);

        $this->args('descript', __('Bigger images use more storage, but will look better - especially on high resolution screens.','peepso-core'));
        $this->set_field(
            'cover_width',
            __('Cover width', 'peepso-core'),
            'select'
        );


        // Cover quality
        $default = 85;
        $this->args('default', $default);

        $options=array();
        for($i = 50; $i<=100; $i+=5){
            $options[$i]= sprintf(__('%d%%', 'peepso-core'), $i);
            if($i == $default) {
                $options[$i] .= ' ('.__('default', 'peepso-core').')';
            }
        }

        $this->args('options', $options);

        $this->args('descript', __('Higher quality will use more storage, but the images will look better','peepso-core'));
        $this->set_field(
            'cover_quality',
            __('Cover quality', 'peepso-core'),
            'select'
        );

        // Build Group
        $this->set_group(
            'storage',
            __('Storage', 'peepso-core'),
            __('These settings control the dimensions and compression levels, and will only be applied to newly uploaded images.', 'peepso-core')
        );
    }

    private function security()
    {


        // external link warning
        $this->args('descript', __('ON: users will be shown a warning page when clicking an external link inside any PeepSo page. The warning page is the one containing peepso_external_link_warning shortcode.','peepso-core'));
        $this->set_field(
            'external_link_warning',
            __('Enable "external link warning" page', 'peepso-core'),
            'yesno_switch'
        );

        // external link warning
        $this->args('descript', __('Turn ON to force the warning page even for configured social sharing providers.','peepso-core'));
        $this->set_field(
            'external_link_warning_social_sharing',
            __('Include social sharing links', 'peepso-core'),
            'yesno_switch'
        );

        // external link whitelist
        $this->args('raw', TRUE);
        $this->args('descript', __('Domains that do not require a warning page, without "www" or "http(s)". One domain name per line. Your website is excluded by default. ','peepso-core').'<br/>'.__('Example domains:','peepso-core').'<br/>google.com<br/>yahoo.com');

        $this->set_field(
            'external_link_whitelist',
            __('Excluded domains', 'peepso-core'),
            'textarea'
        );

        // Build Group
        $this->set_group(
            'security',
            __('Security', 'peepso-core')
        );
    }

    private function uninstall()
    {
        // # Delete Posts and Comments
        $this->args('field_wrapper_class', 'controls col-sm-8 danger');

        $this->set_field(
            'delete_post_data',
            __('Delete Post and Comment data', 'peepso-core'),
            'yesno_switch'
        );

        // # Delete All Data And Settings
        $this->args('field_wrapper_class', 'controls col-sm-8 danger');

        $this->set_field(
            'delete_on_deactivate',
            __('Delete all data and settings', 'peepso-core'),
            'yesno_switch'
        );

        // Build Group
        $summary= __('When set to "YES", all <em>PeepSo</em> data will be deleted upon plugin Uninstall (but not Deactivation).<br/>Once deleted, <u>all data is lost</u> and cannot be recovered.', 'peepso-core');
        $this->args('summary', $summary);

        $this->set_group(
            'peepso_uninstall',
            __('PeepSo Uninstall', 'peepso-core'),
            __('Control behavior of PeepSo when uninstalling / deactivating', 'peepso-core')
        );
    }

    private function opengraph()
    {
        $this->set_field(
            'opengraph_enable',
            __('Enable Open Graph', 'peepso-core'),
            'yesno_switch'
        );

        // Open Graph Title
        $this->set_field(
            'opengraph_title',
            __('Title (og:title)', 'peepso-core'),
            'text'
        );

        // Open Graph Title
        $this->set_field(
            'opengraph_description',
            __('Description (og:description)', 'peepso-core'),
            'textarea'
        );

        // Open Graph Image
        $this->set_field(
            'opengraph_image',
            __('Image (og:image)', 'peepso-core'),
            'text'
        );


        // # Separator
        $this->set_field(
            'separator_advanced_seo',
            __('Advanced SEO', 'peepso-core'),
            'separator'
        );

        // Disable "?" in Profile / Group / Activity URLs
        $this->args('descript', __('This feature might not work with some SEO plugins and server setups. It will remove "?" from certain PeepSo URLs, such as "profile/?username/about".', 'peepso-core'));
        $this->set_field(
            'disable_questionmark_urls',
            __('Enable SEO Friendly links', 'peepso-core').'<br>(BETA)',
            'yesno_switch'
        );

        $frontpage = get_post(get_option('page_on_front'));

        if (1 == PeepSo::get_option('disable_questionmark_urls', 0) && 'page' == get_option( 'show_on_front' ) && has_shortcode($frontpage->post_content, 'peepso_activity')) {
            $this->set_field(
                'activity_homepage_warning',
                __('You are currently using [peepso_activity] as your home page. Because of that, single activity URLs will have to contain "?" no matter what the above setting is.', 'peepso-core'),
                'message'
            );
        }


        $this->args('descript', __('Prevents WordPress from redirecting certain URLs like test?lorem/ipsum to test?lorem%2Fipsum', 'peepso-core'));
        $this->set_field(
            'fix_redirect_canonical',
            __('Prevent variable encoding/redirects', 'peepso-core').'<br>(BETA)',
            'yesno_switch'
        );

        // PeepSo::reset_query()
        $this->args('descript', __('This advanced feature causes PeepSo pages to override the global WP_Query for better SEO.','peepso-core').'<br>'.__('This can interfere with SEO plugins, so use with caution.', 'peepso-core'));
        $this->set_field(
            'force_reset_query',
            __('PeepSo can reset WP_Query', 'peepso-core').'<br>(BETA)',
            'yesno_switch'
        );


        $this->set_group(
            'opengraph',
            __('SEO & Open Graph', 'peepso-core'),
            __("The Open Graph protocol enables links shared to Facebook (and others) carry information that render shared URLs in a great way. Having a photo, title and description. You can learn more about it in our documentation. Just search for 'Open Graph'.", 'peepso-core')
        );
    }

    private function reactions()
    {
        // # Enable Repost
        $this->args('descript', "Reactions \"emotions\" will be used in the future to calculate popularity of posts. It's generally not needed to change this option unless you clearly configured your Reactions as \"up\" and \"down\" triggers.");
        $this->set_field(
            'reactions_emotions',
            __( 'Reactions Emotions', 'peepso-core' ) . ' (BETA)',
            'yesno_switch'
        );

        $this->set_group(
            'reactions',
            __('Reactions', 'peepso-core')
        );
    }

    private function socialsharing()
    {
        // # Enable Repost
        $this->set_field(
            'site_repost_enable',
            __( 'Enable Repost', 'peepso-core' ),
            'yesno_switch'
        );

        // Profile Sharing
        $this->args('descript',__('User profiles are shareable to social networks', 'peepso-core'));
        $this->set_field(
            'profile_sharing',
            __('Profiles Social Sharing', 'peepso-core'),
            'yesno_switch'
        );

        // Activity Social Sharing
        $this->set_field(
            'activity_social_sharing_enable',
            __('Activity Social Sharing', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'separator_social_sharing_providers',
            __('Enabled social networks', 'peepso-core'),
            'separator'
        );

        $links = PeepSoShare::get_instance();
        $links = $links->get_links(TRUE);

        foreach($links as $key=>$link) {
            if(is_array($link)) {
                $this->args('default', 1);
                if (isset($link['desc'])) {
                    $this->args('descript', $link['desc']);
                }
                $this->set_field(
                    'activity_social_sharing_provider_' . $key,
                    $link['label'],
                    'yesno_switch'
                );
            } else {
                $this->set_field($key,'','separator');
            }
        }


        $this->set_group(
            'socialsharing',
            __('Sharing', 'peepso-core')
        );
    }

    /**
     * Checks if the directory has been created, if not use WP_Filesystem to create the directories.
     * @param  string $value The peepso upload directory
     * @return boolean
     */
    public function check_wp_filesystem($value)
    {
        $form_fields = array('site_peepso_dir');
        $url = wp_nonce_url('admin.php?page=peepso_config&tab=advanced', 'peepso-config-nonce', 'peepso-config-nonce');

        if (FALSE === ($creds = request_filesystem_credentials($url, '', false, false, $form_fields))) {
            return FALSE;
        }

        // now we have some credentials, try to get the wp_filesystem running
        if (!WP_Filesystem($creds)) {
            // our credentials were no good, ask the user for them again
            request_filesystem_credentials($url, '', true, false, $form_fields);
            return FALSE;
        }

        global $wp_filesystem;

        if(!empty($value)) {
            if (!preg_match('/^\S.*\S$/', $value)) {
                return FALSE;
            }
        }

        if (!$wp_filesystem->is_dir($value) || !$wp_filesystem->is_dir($value . DIRECTORY_SEPARATOR . 'users')) {
            $wp_filesystem->mkdir($value);
            $wp_filesystem->mkdir($value . DIRECTORY_SEPARATOR . 'users');
            return TRUE;
        }

        return $wp_filesystem->is_writable($value);
    }

    public function check_gdpr_template_layout($value)
    {
        if (!empty($value)) {
            if (strpos($value, 'data_contents') === false || strpos($value, 'data_sidebar') === false || strpos($value, 'data_name') === false || strpos($value, 'data_title') === false) {
                return FALSE;
            }
        }

        return TRUE;
    }

}