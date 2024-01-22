<?php

class PeepSoConfigSectionBlogposts extends PeepSoConfigSectionAbstract
{
	// Builds the groups array
	public function register_config_groups()
	{
		$this->context='left';
        $this->group_profile();


		$this->context='right';
        $this->group_activity();
        // @todo #2157
	}

	/**
	 * General Settings Box
	 */
	private function group_profile()
	{
        $this->args('descript', __('Show "Blog" tab in user profiles','peepso-core'));
        $this->set_field(
            'blogposts_profile_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->args('descript', __('The profile tab will not appear if the user has no blog posts','peepso-core').'<br>'.__('Does not apply to profile owner if User Submissions are enabled','peeps-core'));
        $this->set_field(
            'blogposts_profile_hideempty',
            __('Hide when empty', 'peepso-core'),
            'yesno_switch'
        );


        // Two columns

        $this->set_field(
            'blogposts_columns_header',
            __('Two column layout', 'peepso-core'),
            'separator'
        );
        $this->set_field(
            'blogposts_profile_two_column_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->args('int', TRUE);
        $this->args('default', 350);

        $this->set_field(
            'blogposts_profile_two_column_height',
            __('Box height (px)', 'peepso-core'),
            'text'
        );

        // Featured images

        // Text settings

        $this->set_field(
            'blogposts_images_header',
            __('Featured images', 'peepso-core'),
            'separator'
        );

        $this->set_field(
            'blogposts_profile_featured_image_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->args('descript', __('Display an empty box if an image is not found (to maintain the layout)', 'peepso-core'));
        $this->set_field(
            'blogposts_profile_featured_image_enable_if_empty',
            __('Placeholder', 'peepso-core'),
            'yesno_switch'
        );

        $options = array(
            'top'   => __('Top (rectangle)', 'peepso-core'),
            'left'  => __('Left (square)', 'peepso-core'),
            'right' => __('Right (square)', 'peepso-core'),
        );

        $this->args('options', $options);

        $this->set_field(
            'blogposts_profile_featured_image_position',
            __('Position', 'peepso-core'),
            'select'
        );

        $this->args('int', TRUE);
        $this->args('default', 150);

        // Once again the args will be included automatically. Note that args set before previous field are gone
        $this->set_field(
            'blogposts_profile_featured_image_height',
            __('Height (px)', 'peepso-core'),
            'text'
        );



        // Text settings

        $this->set_field(
            'blogposts_text_header',
            __('Text', 'peepso-core'),
            'separator'
        );

        $this->args('int', TRUE);
        $this->args('default', 50);

        // Once again the args will be included automatically. Note that args set before previous field are gone
        $this->args('descript', __('0 for no content', 'peepso-core'));
        $this->set_field(
            'blogposts_profile_content_length',
            __('Length limit (words)', 'peepso-core'),
            'text'
        );

        $this->args('descript', __('Forced removal of some shortcodes immune to native WP methods (eg Divi Builder and similar). This is an experimental feature, we recommend using plain-text excerpts instead.' ,'peepso-core'));
        $this->set_field(
            'blogposts_profile_content_force_strip_shortcodes',
            __('Aggressive shortcode removal', 'peepso-core'),
            'yesno_switch'
        );

        // Author Box

        $this->set_field(
            'blogposts_author_box_header',
            __('Author box', 'peepso-core'),
            'separator'
        );
        $this->args('descript', __('Adds a small "about the author" box under blog posts','peepso-core'));

        $this->set_field(
            'blogposts_authorbox_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'blogposts_authorbox_author_name_pre_text',
            __('Text before author name', 'peepso-core'),
            'text'
        );


        // USP

        $this->set_field(
            'blogposts_submissions_header_usp',
            __('User Submitted Posts integration', 'peepso-core'),
            'separator'
        );

        if ( !PeepSo::usp_enabled()) {
            $url = '<a href="plugin-install.php?tab=plugin-information&amp;plugin=user-submitted-posts&amp;TB_iframe=true&amp;width=772&amp;height=291" class="thickbox">User Submitted Posts</a>';
            $this->set_field(
                'blogposts_submissions_enable_descript_usp',
                sprintf(__('This feature requires the %s plugin.', 'peepso-core'), $url),
                'message'
            );
        } else {
            $this->set_field(
                'blogposts_submissions_enable_usp',
                __('Enabled', 'peepso-core'),
                'yesno_switch'
            );

            // For USP PRO we need the form ID for shortcode
            if(PeepSo::usp_pro_enabled()) {
                $this->args(
                    'descript',
                    sprintf(
                        __('Defaults to [user-submitted-posts] but you can override it with any %s shortcode','peepso-core'),
                        '<a href="'.admin_url('edit.php?post_type=usp_form').'">'.__('USP Forms', 'usp-pro').'</a>'
                    )
                );
                $this->set_field(
                    'blogposts_submissions_usp_pro_shortcode',
                    __('USP PRO Form shortcode', 'peepso-core'),
                    'text'
                );
            }
        }


        // CM Frontend Submissions
        $this->set_field(
            'blogposts_submissions_header',
            __('CMinds User Submissions integration', 'peepso-core'),
            'separator'
        );

        if ( !class_exists( 'CMUserSubmittedPosts' ) ) {
            $url = '<a href="https://www.cminds.com/wordpress-plugins-library/cm-user-submitted-posts/?af=7789" target="_blank">CMinds User Submitted Posts</a>';
            $this->set_field(
                'blogposts_submissions_enable_descript',
                sprintf(__('This feature requires the %s plugin.', 'peepso-core'), $url),
                'message'
            );
        } else {
            $this->set_field(
                'blogposts_submissions_enable',
                __('Enabled', 'peepso-core'),
                'yesno_switch'
            );
        }



		$this->set_group(
			'blogposts_general',
			__('Author Profiles', 'peepso-core')
		);
	}

	/**
	 * General Settings Box
	 */
	private function group_activity()
	{
		$this->set_field(
			'blogposts_activity_enable',
			__('Post to Activity Stream', 'peepso-core'),
			'yesno_switch'
		);


        // Action text
        $this->args('default', PeepSo::get_option('blogposts_activity_type_post_text_default',''));
        $this->set_field(
            'blogposts_activity_type_post_text',
            'Action text',
            'text'
        );

        $this->args('descript', __('The title of  the post will be displayed after the action text as a link','peepso-core'));
        $this->set_field(
            'blogposts_activity_title_after_action_text',
            __('Append title after action text', 'peepso-core'),
            'yesno_switch'
        );

		$privacy = PeepSoPrivacy::get_instance();
		$privacy_settings =  $privacy->get_access_settings();

		$options = array();

		foreach($privacy_settings as $key => $value) {
		    if(in_array($key, array(30,40))) { continue; }
			$options[$key] = $value['label'];
		}

		$this->args('options', $options);

		$this->set_field(
			'blogposts_activity_privacy',
			__('Default privacy', 'peepso-core'),
			'select'
		);

        if(class_exists('PeepSoBlogPosts')) {

            if(PeepSo::get_option('hashtags_enable',1)) {

                $this->set_field(
                    'blogposts_hashtags_separator',
                    __('Hashtags integration', 'peepso-core'),
                    'separator'
                );

                $options = array(
                    'below_author' => __('Below author name','peepso-core'),
                    'above_embed' => __('Above post preview', 'peepso-core'),
                    'below_embed' => __('Below post preview', 'peepso-core'),
                );

                $this->args('options', $options);
                $this->args('default','below_author');
                $this->set_field(
                    'blogposts_hashtags_peepso_post_location',
                    __('Hashtags in PeepSo stream post', 'peepso-core'),
                    'select'
                );


                $options = array(
                    0 => __('No','peepso-core'),
                    'above_post' => __('Above post', 'peepso-core'),
                    'below_post' => __('Below post', 'peepso-core'),
                    'above_post below_post' => __('Above & below post', 'peepso-core'),

                );

                $this->args('options', $options);

                $this->set_field(
                    'blogposts_hashtags_wp_post_location',
                    __('Hashtags in WordPress post', 'peepso-core'),
                    'select'
                );

                $this->args('descript', __('Applied only when saving a blog post.','peepso-core'));

                $this->args('default',1);
                $this->set_field(
                    'blogposts_hashtags_sort',
                    __('Sort alphabetically', 'peepso-core'),
                    'yesno_switch'
                );

                $this->args('descript', __('Applies only when adding a new post.','peepso-core'));
                $this->set_field(
                    'blogposts_hashtags_always_use_wp_tags',
                    __('Enable "Use WordPress tags" for new posts', 'peepso-core'),
                    'yesno_switch'
                );


                $this->args('descript', __('Applies only when adding a new post.','peepso-core'));
                $this->set_field(
                    'blogposts_hashtags_always_use_wp_cats',
                    __('Enable "Use WordPress categories" for new posts', 'peepso-core'),
                    'yesno_switch'
                );
            }
        }

        // Comment integration

        $this->set_field(
            'blogposts_comments_integration_separator',
            __('Comments integration', 'peepso-core'),
            'separator'
        );

		$this->args('descript', __('Replaces WordPress comments with PeepSo comments and likes/reactions.','peepso-core'));

        $this->set_field(
            'blogposts_comments_enable',
            __('Enabled', 'peepso-core'),
            'yesno_switch'
        );


        $this->args('descript', __('Hides the default PeepSo cover shown to guests.','peepso-core'));
        $this->set_field(
            'blogposts_comments_no_cover',
            __('Hide "join now" cover', 'peepso-core'),
            'yesno_switch'
        );


        // Header - call to action
        $this->args('descript', __('Optional. Displays above the entire comments integration','peepso-core'));
        $this->set_field(
            'blogposts_comments_header_call_to_action',
            'Header (general)',
            'text'
        );


        // Header - likes or reactions
        $this->args('descript', __('Optional. Displays before reactions','peepso-core'));
        $this->set_field(
            'blogposts_comments_header_reactions',
            'Header (reactions)',
            'text'
        );

        // Header - comments
        $this->args('descript', __('Optional. Displays above comments','peepso-core'));
        $this->set_field(
            'blogposts_comments_header_comments',
            'Header (comments)',
            'text'
        );


        $this->set_group(
			'blogposts_general',
			__('Activity Stream', 'peepso-core')
		);
	}
}