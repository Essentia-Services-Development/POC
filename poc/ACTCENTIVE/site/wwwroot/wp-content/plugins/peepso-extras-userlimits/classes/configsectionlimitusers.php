<?php

class PeepSoConfigSectionLimitUsers extends PeepSoConfigSectionAbstract
{
	public $all_roles;

// Builds the groups array
	public function register_config_groups()
	{
        if(isset($_GET['limitusers_tutorial_reset'])) {
            delete_user_meta(get_current_user_id(), 'peepso_limitusers_admin_tutorial_hide');
            PeepSo::redirect(admin_url().'admin.php?page=peepso_config&tab=limitusers');
        }

        if(isset($_GET['limitusers_tutorial_hide'])) {
            add_user_meta(get_current_user_id(), 'peepso_limitusers_admin_tutorial_hide', 1, TRUE);
            PeepSo::redirect(admin_url().'admin.php?page=peepso_config&tab=limitusers');
        }

        // display the admin tutorial unless this user has already hidden it
        if(1 != get_user_meta(get_current_user_id(), 'peepso_limitusers_admin_tutorial_hide', TRUE)) {
            ob_start();
            PeepSoTemplate::exec_template('limitusers', 'admin_tutorial');

            $peepso_admin = PeepSoAdmin::get_instance();
            $peepso_admin->add_notice(ob_get_clean(), '');
        }


		global $wp_roles;
		$this->all_roles = $wp_roles->roles;

		// LEFT
        $this->context = 'left';

       $this->set_field(
            'limitusers_roles_sep',
            __('Role based limits','peepsolimitusers'),
            'separator'
        );


        // Reverse logic
        $this->args('options',[
            0=>__('Blocklist: limit users with a matching role', 'peepsolimitusers'),
            1=>__('Allowlist (BETA): require at least one matching role', 'peepsolimitusers'),
        ]);

        $this->set_field(
            'limitusers_roles_mode',
            __('Logic mode','peepsolimitusers'),
            'select'
        );

        $this->set_field(
            'limitusers_roles_show',
            'Show role based warnings to users',
            'yesno_switch'
        );

        // Message
        $this->args('descript',__('Leave empty for the default value','peepsolimitusers'));
        $this->set_field(
            'limitusers_roles_message',
            __('Message','peepsolimitusers'),
            'text'
        );

        // URL
        $this->args('descript',__('Page where users can change their role/membership (if applicable)','peepsolimitusers'));
        $this->set_field(
            'limitusers_roles_url',
            __('URL','peepsolimitusers'),
            'text'
        );


        $this->set_group(
            'peepso_limitusers_config_roles',
            __('Configuration', 'peepsolimitusers')
        );

        // hide
        $this->box(
            'hide',
            __('Users Listings', 'peepsolimitusers'),
            __('Controls which users are shown in the listings.<br/>Administrators will always see everyone.', 'peepsolimitusers')
        );


        // posts
        $this->box(
            'posts',
            __('New posts', 'peepsolimitusers'),
            __('Controls creating new posts.<br/>Includes posting in groups.', 'peepsolimitusers')
        );

        // repost
        if (PeepSo::get_option('site_repost_enable', TRUE)) {
            $this->box(
                'repost',
                __('RePost', 'peepsolimitusers'),
                FALSE
            );
        }

        // posts
        $this->box(
            'comments',
            __('Comments', 'peepsolimitusers'),
            __('Controls writing comments.<br/>Includes commenting in groups.', 'peepsolimitusers')
        );

        // reactions
        $this->box(
            'reactions',
            __('Disable reactions', 'peepsolimitusers'),
            __('Controls adding reactions.<br/>Includes reactions in groups.', 'peepsolimitusers'));
        
        // polls
        if(class_exists('PeepSoPolls') || class_exists('PeepSoPollsPlugin')) {
            $this->box(
                'polls',
                __('Polls', 'peepsolimitusers'),
                __('Controls adding polls.<br/>Does not affect voting.', 'peepsolimitusers')
            );
        }
        
        // background post
        if (PeepSo::get_option_new('post_backgrounds_enable')) {
            $this->box(
                'post_backgrounds',
                __('Post Backgrounds', 'peepsolimitusers'),
                FALSE
            );
        }

		// RIGHT
		$this->context = 'right';

		// events
        if (class_exists('PeepSo_WPEM_Plugin')) {
            $this->box(
                'wpem_create',
                __('Events - create / manage', 'peepsolimitusers'),
                FALSE
            );
            $this->box(
                'wpem_rsvp',
                __('Events - RSVP', 'peepsolimitusers'),
                FALSE
            );
        }

        // Job Manager
        if (class_exists('PeepSoWPJM')) {
            $this->box(
                'wpjm_create',
                __('Jobs', 'peepsolimitusers'),
                FALSE
            );
        }

        // friends
        if(class_exists('PeepSoFriends')) {
            $this->box(
                'friends',
                __('Friend requests', 'peepsolimitusers'),
                __('Controls sending friend requests.<br/>Does not affect receiving friend requests.', 'peepsolimitusers')
            );
        }

        // groups
        if(class_exists('PeepSoGroups')) {
            $this->box(
                'groups',
                __('Groups - join', 'peepsolimitusers'),
                __('Controls joining groups and being invited to groups.<br/>Does not affect groups created earlier.', 'peepsolimitusers')
            );
        }

        // groups_create
        if(class_exists('PeepSoGroups')) {
            $this->box(
                'groups_create',
                __('Groups - create', 'peepsolimitusers'),
                __('Controls group creation.<br/>Does not affect groups created earlier.', 'peepsolimitusers')
            );
        }

        // messages
        if(class_exists('PeepSoMessagesPlugin')) {
            $this->box(
                'messages',
                __('Chat - new threads', 'peepsolimitusers'),
                __('Controls creating new message threads.<br/>Does not affect pre-existing threads.', 'peepsolimitusers')
            );
        }

        // photos
        if(class_exists('PeepSoSharePhotos')) {
            $this->box(
                'photos',
                __('Photos', 'peepsolimitusers'),
                __('Controls album creation and uploading photos in posts, comments, chats.<br/>Does not affect avatars and covers.', 'peepsolimitusers')
            );
        }

        // videos
        if(class_exists('PeepSoVideos')) {
            $this->box(
                'videos_embed',
                __('Videos - embed', 'peepsolimitusers'),
                __('Controls embedding videos in their posts.<br/>Does not affect video links in text posts.', 'peepsolimitusers')

            );

            if (PeepSo::get_option_new('videos_upload_enable')) {
                $this->box(
                    'videos',
                    __('Videos - upload', 'peepsolimitusers'),
                    __('Controls video uploads.<br/>Does not affect embeds & links.', 'peepsolimitusers')

                );
            }

            $this->box(
                'audio_embed',
                __('Audio - embed', 'peepsolimitusers'),
                __('Controls embedding audios in their posts.<br/>Does not affect audio links in text posts.', 'peepsolimitusers')

            );

            if (PeepSo::get_option_new('videos_audio_enable')) {
                $this->box(
                    'audio',
                    __('Audio - upload', 'peepsolimitusers'),
                    __('Controls audio uploads.<br/>Does not affect embeds & links.', 'peepsolimitusers')
                );
            }
        }

        // files
        if(class_exists('PeepSoFileUploads') || class_exists('PeepSoFileUploads')) {
            $this->box(
                'files',
                __('Files', 'peepsolimitusers'),
                __('Controls file uploads in posts, comments, and chats.<br/>Does not affect Photos, Videos, Audio plugins.', 'peepsolimitusers')
            );
        }

	}

	private function box($section, $title, $description, $roles = TRUE)
	{
        if($description) {
            $this->set_field(
                'message_' . $section,
                $description,
                'message'
            );
        }
		if(TRUE == $roles) {

            $this->set_field(
                'separatorroles',
                __('Roles', 'peepsolimitusers'),
                'separator'
            );

			foreach ($this->all_roles as $key => $role) {

				$type = 'yesno_switch';
				$name = __($role['name']);

				if ('administrator' == $key && 'hide' != $section) {
					$type = 'message';
					$name = __('Administrator can\'t be limited', 'peepsolimitusers');
				}

				$this->set_field(
					'limitusers_' . $section . '_role_' . $key,
					$name,
					$type
				);
			}
		}

		$this->set_field(
			'separator_' . $section . '_completeness',
			__('Based on profile completeness', 'peepsolimitusers'),
			'separator'
		);


		for($i=0;$i<=100;$i+=10)
        {
            $options [$i] = "$i%";
        }

        $options[0] = __('-- no limit --', 'peepsolimitusers');

        $this->args('options', $options);

		$this->set_field(
			'limitusers_' . $section . '_completeness_min',
			__('Require', 'peepsolimitusers'),
            'select'
		);
        // usr_avatar_custom
		$this->set_field(
            'separator_' . $section . '_avatar',
            __('Based on avatar', 'peepsolimitusers'),
            'separator'
        );

        $this->set_field(
            'limitusers_' . $section . '_avatar',
            'Require a profile avatar',
            'yesno_switch'
        );
        
		$this->set_field(
            'separator_' . $section . '_cover',
            __('Based on cover', 'peepsolimitusers'),
            'separator'
        );

        $this->set_field(
            'limitusers_' . $section . '_cover',
            'Require a profile cover',
            'yesno_switch'
        );

		$this->set_group(
			'peepso_limitusers_' . $section,
			__($title, 'peepsolimitusers')
		);
	}
}