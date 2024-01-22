<?php

class PeepSoConfigSectionPostbox extends PeepSoConfigSectionAbstract
{
    // Builds the groups array
    public function register_config_groups()
    {
        $this->context='left';
        $this->general();
        $this->links();

        $this->context='right';
        $this->hashtags();
        $this->backgrounds();
        $this->giphy();
        $this->polls();
        $this->moods();
        $this->mentions();

    }

    function links() {

        // # Open Links In New Tab
        $this->args('default', 1);
        $this->args('options', array(
            0 => __('No','peepso-core'),
            1 => __('All links','peepso-core'),
            2 => __('Only links to other domains','peepso-core'),
        ));

        $this->set_field(
            'site_activity_open_links_in_new_tab',
            __('Open links in new tab', 'peepso-core'),
            'select'
        );

        // Link trim
        $this->args('default', 0);
        $this->args('descript', __('If a post contains only an URL and no other visible text, the link will not be displayed. Does not apply to MarkDown links.','peepso-core'));
        $this->set_field(
            'hide_url_only',
            __('Hide URL if there is no other text', 'peepso-core'),
            'yesno_switch'
        );


        // Link trim
        $this->args('descript', __('Visually trim links after the domain name, while the target remains unchanged.','peepso-core'));
        $this->set_field(
            'trim_url',
            __('Trim links to domain name only', 'peepso-core'),
            'yesno_switch'
        );

        // Link remove https(s)
        $this->args('descript', __('Visually hide http(s):// from links, while the target remains unchanged.','peepso-core'));
        $this->set_field(
            'trim_url_https',
            __('Hide http(s)://', 'peepso-core'),
            'yesno_switch'
        );


        $this->set_field('embeds_separator', 'Link Previews', 'separator');

        // Load previews
        $this->args(
            'descript',
            __('ON: links posted by users will be fetched into a preview box (embed)','peepso-core') .
            '<br/>' .
            __('OFF: no attempts to fetch links will be made','peepso-core')
        );

        $this->args('default', 1);

        $this->set_field(
            'allow_embed',
            __('Load link previews', 'peepso-core'),
            'yesno_switch'
        );

        $this->args('descript', __('Show a smaller link preview thumbnail on a wide viewport (more than 480 pixels) to save space','peepso-core'));
        $this->set_field(
            'small_url_preview_thumbnail',
            __('Prefer a smaller thumbnail', 'peepso-core'),
            'yesno_switch'
        );

        // non-SSL embeds
        $this->args('descript', __('Enables non-SSL (http://) link fetching. This can lead to "insecure content" warnings if your site is using SSL','peepso-core'));
        $this->set_field(
            'allow_non_ssl_embed',
            __('Allow non-SSL previews', 'peepso-core'),
            'yesno_switch'
        );


        $this->args('descript', __('If no img tag is detected in the fetched result, PeepSo will attempt alternative methods to obtain a link preview','peepso-core'));
        $this->set_field(
            'prefer_img_embeds',
            __('Prefer previews with images (BETA)', 'peepso-core'),
            'yesno_switch'
        );


        $this->args('descript', __('If no viable thumbnail is provided by the target, PeepSo will attempt to use the first img tag from the page source.','peepso-core'));
        $this->set_field(
            'guess_img_embeds',
            __('Fallback to img tag (BETA)', 'peepso-core'),
            'yesno_switch'
        );

        // Attempt refresh
        $options = array(
            0 => __('No', 'peepso-core'),
        );

        // $i is in minutes, $options are in SECONDS
        for($i=30;$i<=180;$i+=30) {
            $options[$i*60] = sprintf(__('Every %d minutes','peepso-core'), $i);
        }

        if(PeepSo::is_dev_mode('embeds')) {
            $options[5*60] = sprintf(__('Every %d minutes','peepso-core'), 5);
        }

        $options[15*60] = sprintf(__('Every %d minutes','peepso-core'), 15);
        ksort($options);


        $this->args('descript', __('If enabled PeepSo will periodically refresh URL previews. This might cause some posts to load slower when refreshing.<br/>Use this option especially if you recently changed the static images option'));
        $this->args('options', $options);
        $this->set_field(
            'refresh_embeds',
            __('Refresh previews (BETA)', 'peepso-core'),
            'select'
        );

        $this->set_field('embeds_separator_wp', 'WordPress embeds', 'separator');

        $options_sizes = [0,500,1000,1500,2000,2500,3000];
        $options = [];

        foreach($options_sizes as $size) {
            $options[$size] = "{$size} px";
            if($size == 0) {
                $options[$size] = __('Let WordPress decide','peepso-core');
            }

            if($size==1500) {
                $options[$size] = '1500 px ('.__('Default','peepso-core').')';
            }
        }
        $this->args('options', $options);
        $this->args('descript',
            __('PeepSo will attempt to use this resolution (or the closest possible) in the activity streams, which results in sharper images.','peepso-core')
            . '<br/><br/>'
            . __('This will only be attempted on WordPress embeds coming from your website, as we cannot control external embeds.','peepso-core')
            . '<br/><br/>'
            . __('Bigger thumbnails might impact loading speeds.','peepso-core')
        );

        $this->set_field(
            'embeds_wp_thumb_size',
            __('Preferred thumbnail size', 'peepso-core'),
            'select'
        );

        // Build Group
        $this->set_group(
            'links',
            __('Links & Embeds', 'peepso-core')
        );
    }

    function general() {

        /** PRIVACY **/
        if(TRUE) {
            // Separator
            $this->set_field(
                'separator_profile',
                __('Privacy', 'peepso-core'),
                'separator'
            );

            // # Hide Activity Stream From Guests
            $this->set_field(
                'site_activity_hide_stream_from_guest',
                __('Hide the activity stream from guests', 'peepso-core'),
                'yesno_switch'
            );

            // # Default privacy
            $privacy = PeepSoPrivacy::get_instance();
            $privacy_settings = $privacy->get_access_settings();

            $options = array();

            foreach ($privacy_settings as $key => $value) {
                $options[$key] = $value['label'];
            }

            $this->args('options', $options);
            $this->args('descript', __('Defines the default starting privacy level for new posts. Users can change it, and the postbox will always remember their last choice.', 'peepso-core'));

            $this->set_field(
                'activity_privacy_default',
                __('Default post privacy', 'peepso-core'),
                'select'
            );


            // # Who can post on "my profile" page
            $privacy = PeepSoPrivacy::get_instance();
            $privacy_settings = $privacy->get_access_settings();

            $options = array();

            foreach ($privacy_settings as $key => $value) {
                $options[$key] = $value['label'];
            }

            // Remove site guests & rename "only me"
            unset($options[PeepSo::ACCESS_PUBLIC]);
            $options[PeepSo::ACCESS_PRIVATE] .= __(' (profile owner)', 'peepso-core');

            $this->args('options', $options);

            $this->set_field(
                'site_profile_posts',
                __('Who can post on "my profile" page', 'peepso-core'),
                'select'
            );

            $this->args('default', 1);
            $this->set_field(
                'site_profile_posts_override',
                __('Let users override this setting', 'peepso-core'),
                'yesno_switch'
            );
        }

        /** COMMENT / LENGTH LIMITS & READ MORE **/
        if(TRUE) {

            // Separator
            $this->set_field(
                'separator_readmore',
                __('Length & comment limits', 'peepso-core'),
                'separator'
            );

            // Max length
            $this->args('validation', array('numeric', 'minval:50'));
            $this->args('validation', array('numeric', 'maxval:100000'));
            $this->args('int', TRUE);
            $this->args('descript', __('It is generally recommended to keep this value below 10000 (ten thousand). Extremely long posts might run into performance issues.', 'peepso-core'));

            $this->set_field(
                'site_status_limit',
                __('Post length limit', 'peepso-core'),
                'text'
            );

            // Readmore threshold
            $this->args('default', 1000);
            $this->args('validation', array('numeric'));

            $this->set_field(
                'site_activity_readmore',
                __("Show 'read more' after: [n] characters", 'peepso-core'),
                'text'
            );


            // Readmore single post vieew
            $this->args('default', 2000);
            $this->args('validation', array('numeric'));

            $this->set_field(
                'site_activity_readmore_single',
                __('Redirect to single post view when post is longer than: [n] characters', 'peepso-core'),
                'text'
            );

            $options = [];
            for ($i = 0; $i <= 20; $i++) {
                $options[$i] = $i;
            }

            $options_loadmore = $options;
            unset($options_loadmore[0]);

            // # Number Of Comments To Display
            $this->args('options', $options);
            $this->set_field(
                'site_activity_comments',
                __('Display comments', 'peepso-core'),
                'select'
            );

            // Show comments in batches
            $this->args('options', $options_loadmore);
            $this->set_field(
                'activity_comments_batch',
                __('Load more comments', 'peepso-core'),
                'select'
            );

            if (PeepSo::get_option_new('pinned_posts_enable')) {

                // # Number Of Comments To Display
                $this->args('options', $options);
                $this->args('default', 2);
                $this->set_field(
                    'site_activity_pinned_post_comments',
                    __('Display comments', 'peepso-core').' ('.__('pinned','peepso-core').')',
                    'select'
                );

                // Show comments in batches
                $this->args('options', $options_loadmore);
                $this->args('default', 5);
                $this->set_field(
                    'activity_comments_pinned_post_batch',
                    __('Load more comments', 'peepso-core').' ('.__('pinned','peepso-core').')',
                    'select'
                );
            }

            $this->args('descript', __('Enabled: regular users (non-admins) can disable and enable comments on their own posts', 'peepso-core'));
            $this->set_field(
                'post_owner_can_disable_comments',
                __('Post owner can disable comments', 'peepso-core'),
                'yesno_switch'
            );
        }


        /** ADVANCED **/
        if(TRUE) {

            // Separator
            $this->set_field(
                'separator_pinned_posts',
                __('Advanced', 'peepso-core'),
                'separator'
            );

            /** VIEW COUNT **/
            if (TRUE) {
                $this->args('default', 0);
                $this->args('descript',
                    __('General count - counts every time the post is rendered to anybody', 'peepso-core')
                    . '<br/>' .
                    __('Unique members - counts unique views by users who are logged in', 'peepso-core')
                );

                $options = array(
                    0 => __('No', 'peepso-core'),
                    1 => __('Only general count', 'peepso-core'),
                    2 => __('Only unique members', 'peepso-core'),
                    3 => __('Both', 'peepso-core'),
                );
                $this->args('options', $options);
                $this->set_field(
                    'post_view_count_show',
                    __('Display view count', 'peepso-core'),
                    'select'
                );
            }

            /** PINNED **/
            $this->args('descript', __('Might result in a slight stream performance drop.', 'peepso-core'));
            $this->set_field(
                'pinned_posts_enable',
                __('Pinned Posts', 'peepso-core'),
                'yesno_switch'
            );

            /** SAVED **/
            $this->args('descript', __('Allows users to save (favorite / bookmark) posts into a private "saved posts" collection.', 'peepso-core'));
            $this->set_field(
                'post_save_enable',
                __('Saved Posts', 'peepso-core'),
                'yesno_switch'
            );

            /** MARK EDITS **/
            $this->args('default', 1);
            $this->args('descript', __('Enabled: a small icon will be added to edited posts and comments, informing the user when was the last time the content was edited.', 'peepso-core'));
            $this->set_field(
                'post_edit_notice_show',
                __('Mark edited content', 'peepso-core'),
                'yesno_switch'
            );

            /** SCHEDULED **/
            $this->args('default', 0);
            $this->set_field(
                'scheduled_posts_enable',
                __('Allow non-admins to schedule posts', 'peepso-core'),
                'yesno_switch'
            );

            /** MORE - HOOKS */
            $stream_config = apply_filters('peepso_activity_stream_config', array());

            if (count($stream_config) > 0) {

                foreach ($stream_config as $option) {
                    if (isset($option['descript'])) {
                        $this->args('descript', $option['descript']);
                    }
                    if (isset($option['int'])) {
                        $this->args('int', $option['int']);
                    }
                    if (isset($option['default'])) {
                        $this->args('default', $option['default']);
                    }

                    $this->set_field($option['name'], $option['label'], $option['type']);
                }
            }

        }

        // Build Group
        $this->set_group(
            'general',
            __('General', 'peepso-core')
        );
    }

    private function giphy()
    {
        // API Key
        $this->set_field(
            'giphy_api_key',
            __('GIPHY API Key', 'peepso-core'),
            'text'
        );

        // Limit
        $options = array();
        for ($i = 5; $i <= 100; $i+=5) {
            $options[$i] = $i;
        }

        $this->args('options', $options);
        $this->set_field(
            'giphy_display_limit',
            __('Limit', 'peepso-core'),
            'select'
        );

        // Limit
        $options = array(
            'y'     => 'Y       - '.__('Illustrated content only', 'peepso-core'),
            'g'     => 'G       - '.__('Suitable for everyone', 'peepso-core'),
            'pg'    => 'PG      - '.__('May be inappropriate for children', 'peepso-core'),
            'pg-13' => 'PG-13   - '.__('May be inappropriate under the age of 13', 'peepso-core'),
            'r'     => 'R       - '.__('Inappropriate under the age of 17', 'peepso-core'),
            ''      => __('No limit','peepso-core'),
        );

        $this->args('options', $options);

        $this->set_field(
            'giphy_rating',
            __('Max content rating', 'peepso-core'),
            'select'
        );


        // List of available renditions. This list only includes renditions that have GIF filetype.
        // https://developers.giphy.com/docs/optional-settings/#rendition-guide
        $options = array(
            'fixed_width'              => 'fixed_width - '.__('Width set to 200px. Good for mobile use.', 'peepso-core'),
            'fixed_width_still'        => 'fixed_width_still - '.__('Static preview image for fixed_width', 'peepso-core'),
            'fixed_width_downsampled'  => 'fixed_width_downsampled - '.__('Width set to 200px. Reduced to 6 frames. Works well for unlimited scroll on mobile and as animated previews.', 'peepso-core'),
            'fixed_width_small'        => 'fixed_width_small - '.__('Width set to 100px. Good for mobile keyboards', 'peepso-core'),
            'fixed_width_small_still'  => 'fixed_width_small_still - '.__('Static preview image for fixed_width_small', 'peepso-core'),
            'fixed_height'             => 'fixed_height - '.__('Height set to 200px. Good for mobile use.', 'peepso-core'),
            'fixed_height_still'       => 'fixed_height_still - '.__('Static preview image for fixed_height', 'peepso-core'),
            'fixed_height_downsampled' => 'fixed_height_downsampled - '.__('Height set to 200px. Reduced to 6 frames to minimize file size to the lowest. Works well for unlimited scroll on mobile and as animated previews. See GIPHY.com on mobile web as an example.', 'peepso-core'),
            'fixed_height_small'       => 'fixed_height_small - '.__('Height set to 100px. Good for mobile keyboards.', 'peepso-core'),
            'fixed_height_small_still' => 'fixed_height_small_still - '.__('Static preview image for fixed_height_small', 'peepso-core'),
            'downsized'                => 'downsized - '.__('Resized and downsampled to meet 2MB limit.', 'peepso-core'),
            'downsized_still'          => 'downsized_still - '.__('A still version of the first frame of the downsized rendition for previews and pre-loading.', 'peepso-core'),
            'downsized_large'          => 'downsized_large - '.__('Resized and downsampled to meet 8MB limit.', 'peepso-core'),
            'downsized_medium'         => 'downsized_medium - '.__('Resized and downsampled to meet 5MB limit.', 'peepso-core'),
            'preview_gif'              => 'preview_gif - '.__('File size under 50kb. Duration may be truncated to meet file size requirements. Good for thumbnails and previews.', 'peepso-core'),
            'original'                 => 'original - '.__('Original file size and file dimensions. Good for desktop use.', 'peepso-core'),
            'original_still'           => 'original_still - '.__('Preview image for original', 'peepso-core'),
        );

        /** POSTS */
        $this->set_field(
            'giphy_posts_sep',
            __('Posts','peepso-core'). ' (BETA)',
            'separator'
        );

        $this->set_field(
            'giphy_posts_enable',
            __('Enabled','peepso-core'),
            'yesno_switch'
        );

        $this->args('options', $options);
        $this->args('descript', __('Only includes GIF rendition types. See the official <a href="https://developers.giphy.com/docs/optional-settings/#rendition-guide" target="_blank">rendition guide</a> to help you select the best rendition type that suits your site.', 'peepso-core'));

        $this->set_field(
            'giphy_rendition_posts',
            __('Render method', 'peepso-core'),
            'select'
        );

        /** COMMENTS */
        $this->set_field(
            'giphy_comments_sep',
            __('Comments','peepso-core'),
            'separator'
        );

        $this->set_field(
            'giphy_comments_enable',
            __('Enabled','peepso-core'),
            'yesno_switch'
        );

        $this->args('options', $options);
        $this->args('descript', __('Only includes GIF rendition types. See the official <a href="https://developers.giphy.com/docs/optional-settings/#rendition-guide" target="_blank">rendition guide</a> to help you select the best rendition type that suits your site.', 'peepso-core'));

        $this->set_field(
            'giphy_rendition_comments',
            __('Render method', 'peepso-core'),
            'select'
        );

        if(class_exists('PeepSoMessagesPlugin')) {

            /** COMMENTS */
            $this->set_field(
                'giphy_chat_sep',
                __('Chat','peepso-core'),
                'separator'
            );

            $this->set_field(
                    'giphy_chat_enable',
                    __('Enabled','peepso-core'),
                'yesno_switch'
            );


            $this->args('options', $options);
            $this->args('descript', __('Only includes GIF rendition types. See the official <a href="https://developers.giphy.com/docs/optional-settings/#rendition-guide" target="_blank">rendition guide</a> to help you select the best rendition type that suits your site.', 'peepso-core'));

            $this->set_field(
                'giphy_rendition_messages',
                __('Render method', 'peepso-core'),
                'select'
            );
        }


        $general_config = apply_filters('peepso_giphy_integration_general_config', array());

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
        # General description
        $this->set_field(
            'giphy_info_description',
            '<small>'.__('PeepSo integrates world-famous <a href="https://giphy.com/" target="_blank">GIPHY</a>. Thanks to their service, your community now has the possibility to share all of their amazing gifs in posts, comments and chat.','peepso-core').'</small>',
            'message'
        );


        $this->set_field(
            'giphy_info_logo',
            '<a href="https://giphy.com/" target="_blank"><img src="'.plugin_dir_url(__FILE__).'../assets/images/powered-by-giphy.png" /></a>',
            'message'
        );

        // Build Group
        $this->set_group(
            'giphy',
            __('GIPHY', 'peepso-core')
        );
    }

    private function polls()
    {
        // Enable Moods
        $this->set_field(
            'polls_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );
        
        // Multi Select
        $this->args('descript', __('Enabled: users can cast more than one vote in each poll','peepso-core') .'<br>' .__('Disabled: users can only vote for one option in each poll','peepso-core'));
        $this->set_field(
            'polls_multiselect',
            __('Multi select polls', 'peepso-core'),
            'yesno_switch'
        );

        if(class_exists('PeepSoGroupsPlugin')) {
            // Polls In Groups
            $this->args('descript', __('Enabled: polls are available in group posts', 'peepso-core') . '<br>' . __('Disabled: polls are only available on community and profile streams', 'peepso-core'));
            $this->set_field(
                'polls_group',
                __('Polls in groups', 'peepso-core'),
                'yesno_switch'
            );
        }

        // Vote Changes
        $this->args('descript', __('Does not apply to poll author and administrators','peepso-core') . '<br>' . __('Enabled: users can change or delete their votes','peepso-core') .'<br>' .__('Disabled: votes are final','peepso-core'));
        $this->set_field(
            'polls_changevote',
            __('Allow vote changes', 'peepso-core'),
            'yesno_switch'
        );

        // Show Results Before Vote
        $this->args('descript', __('Does not apply to poll author and administrators','peepso-core') . '<br>' . __('Enabled: users can see poll results before voting','peepso-core') .'<br>' .__('Disabled: results are hidden from users who haven\'t voted yet','peepso-core'));
        $this->set_field(
            'polls_show_result_before_vote',
            __('Always show results', 'peepso-core'),
            'yesno_switch'
        );

        $options = [
            0 => __('No','peepso-core'),
            3 => __('Descending','peepso-core'), // SORT_DESC
            4 => __('Ascending','peepso-core'), // SORT_ASC
        ];

        $this->args('options', $options);
        $this->set_field(
            'polls_sort_result_by_votes',
            __('Sort results by votes', 'peepso-core'),
            'select'
        );

		// Build Group
		$this->set_group(
            'polls', __('Polls', 'peepso-core')
		);
    }

    private function moods()
    {

        // Enable Moods
        $this->set_field(
            'moods_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_group(
            'moods',
            __('Moods', 'peepso-core')
        );
    }

    private function mentions()
    {

        // Enable Mentionss
        $this->set_field(
            'tags_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'mentions_auto_on_comment_reply',
            __( "Add a mention when replying to a comment", 'peepso-core' ),
            'yesno_switch'
        );

        $this->set_group(
            'mentions',
            __('Mentions', 'peepso-core')
        );
    }

    private function hashtags()
    {
        // Enable Tags
        $this->args('default', 1);
        $this->set_field(
            'hashtags_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );


        $this->set_field(
            'hashtags_performance_separator',
            __('Maintenance cron','peepso-core'),
            'separator'
        );

        // Hashtag  post count refresh rate
        $options = array();
        for($i=5;$i<=60;$i+=5) {
            $options[$i]= "$i " . __('minutes', 'peepso-core');
            if(60==$i) { $options[$i].= ' ' . __('(default)','peepso-core'); }
        }

        $this->args('options', $options);
        $this->args('default', 60);
        $this->args('descript', __('Deleted and edited posts are checked periodically to update post counts for each hashtag.', 'peepsohashtag').'<br/>'.__('Smaller delay means more database load.', 'peepso-core'));

        $this->set_field(
            'hashtags_post_count_interval',
            __('Update post count in tags every', 'peepso-core'),
            'select'
        );


        // Hashtag  post count refresh rate
        $options = array();

        for($i=5;$i<=100;$i+=5) {
            $options[$i]= "$i " . __('entries', 'peepso-core');
            if(5==$i) { $options[$i].= ' ' . __('(default)','peepso-core'); }
        }

        $this->args('options', $options);
        $this->args('default', 5);
        $this->args('descript', __('How many posts and hashtags to process when the maintenance scripts are ran.', 'peepsohashtag').'<br/>'.__('Bigger batches mean faster updates, but generate higher load.', 'peepso-core'));

        $this->set_field(
            'hashtags_post_count_batch_size',
            __('Process', 'peepso-core'),
            'select'
        );

        // Delete empty hashtags
        $this->args('default', 1);
        $this->args('descript', __('When enabled, hashtags with zero posts will be deleted and not shown in the widget or suggestions. ', 'peepso-core').'<br/>'.__('Hashtags with zero posts can occur, when posts are deleted or edited.','peepso-core'));

        $this->set_field(
            'hashtags_delete_empty',
            __('Delete empty hashtags', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'hashtags_advanced_separator',
            __('Advanced','peepso-core'),
            'separator'
        );

        $this->args('default', 0);
        $this->args('descript',
            __('Enables hashtags of any length, with any content, including non-alphanumeric characters (Arabic, Japanese, Korean, Cyrillic, Emoji, etc). Hashtags MUST end with a space, line break or another hashtag.','peepso-core')
        );

        $this->set_field(
            'hashtags_everything',
            __('Allow non-alphanumeric hashtags', 'peepso-core'),
            'yesno_switch'
        );


        $options = array();
        for($i=1;$i<=5;$i++) {
            $options[$i]= "$i " . _n('character','characters', $i,'peepso-core');
            if(3==$i) { $options[$i].= ' ' . __('(default)','peepso-core'); }
        }

        $this->args('options', $options);
        $this->args('default', 3);
        $this->args('descript', __('Shorter hashtags will be ignored', 'peepsohashtag'));

        $this->set_field(
            'hashtags_min_length',
            __('Minimum hashtag length', 'peepso-core'),
            'select'
        );


        // Hashtag  post count refresh rate
        $options = array();

        for($i=5;$i<=64;$i++) {
            $options[$i]= "$i " . __('characters','peepso-core');
            if(16==$i) { $options[$i] .= ' ' . __('(default)','peepso-core'); }
        }

        $this->args('options', $options);
        $this->args('default', 16);
        $this->args('descript', __('Longer hashtags will be ignored', 'peepso-core'));

        $this->set_field(
            'hashtags_max_length',
            __('Maximum hashtag length', 'peepso-core'),
            'select'
        );

        // Start with letter
        $this->args('default', 0);
        $this->args('descript', __('ON: hashtags beginning with a number will be ignored','peepso-core'));

        $this->set_field(
            'hashtags_must_start_with_letter',
            __('Hashtags must start with a letter', 'peepso-core'),
            'yesno_switch'
        );

        // Rebuild
        $this->args('default', 0);
        $this->args('descript', __('Enable and click "save" to force a hashtag cache rebuild.','peepso-core').'<br/>'.__('It will also happen automatically after changing any of the settings above.','peepso-core'));

        $this->set_field(
            'hashtags_rebuild',
            __('Reset and rebuild the hashtags cache', 'peepso-core'),
            'yesno_switch'
        );


        $this->set_group(
            'peepso_hashtags_performance',
            __('Hashtags', 'peepso-core')
        );
    }


    private function backgrounds()
    {
        $this->set_field(
            'post_backgrounds_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->args('validation', ['numeric','minval:10'] );
        $this->args('validation', ['numeric','maxval:'.apply_filters('peepso_config_post_backgrounds_max_length_maxval',200)]);
        $this->args( 'int', TRUE );
        $this->set_field(
            'post_backgrounds_max_length',
            __('Maximum post length', 'peepso-core'),
            'text'
        );

        $this->args('descript','You can allow some linebreaks in this type of post, but it\'s possible to disrupt the layout within the box.');
        $this->args('options',apply_filters('peepso_config_post_backgrounds_max_linebreaks_options',[0,1,2,3,4,5,6]));
        $this->set_field(
            'post_backgrounds_max_linebreaks',
            __('Allow linebreaks', 'peepso-core'),
            'select'
        );

        $desc = 'Users will be able to create short posts with big, colorful backgrounds.';

        $desc .= ' <span class="ps-js-link-manage-backgrounds" style="' . (PeepSo::get_option('post_backgrounds_enable') ? '' : 'display:none') . '">'
            . 'The backgrounds can be managed <a href="' . admin_url('admin.php?page=peepso-manage&tab=post-backgrounds') . '">here</a>.'
            . '</span>';

        $this->set_group(
            'post_backgrounds',
            __('Post Backgrounds', 'peepso-core'),
            $desc
        );
    }

}
?>
