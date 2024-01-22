<?php

class PeepSoPolls
{
	private static $_instance = NULL;

	const MODULE_ID = 30;

    private function __construct()
	{
        add_action('peepso_init', array(&$this, 'init'));
	}

	public static function get_instance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new self();
		}
		return (self::$_instance);
	}


	public function init()
	{
		if (!is_admin()) {
			add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

			// postbox
			add_filter('peepso_post_types', array(&$this, 'post_types'), 30, 2);
			add_filter('peepso_postbox_tabs', array(&$this, 'postbox_tabs'), 120);
			add_filter('peepso_postbox_interactions', array(&$this, 'postbox_interactions'), 110, 2);
			add_filter('peepso_permissions_polls_upload', array(&$this, 'permissions_polls_upload'));

			// save additional data
			add_filter('peepso_activity_insert_data', array(&$this, 'activity_insert_data'));
            add_action('peepso_activity_after_add_post', array(&$this, 'after_add_post'));
            add_action('peepso_activity_after_save_post', array(&$this, 'after_add_post'), 10, 1);

			// attach poll to post
			add_action('peepso_activity_post_attachment', array(&$this, 'attach_poll'), 30, 1);

			// disable repost
			add_filter('peepso_activity_post_actions', array(&$this, 'activity_post_actions'), 100);

			// stream title
			add_filter('peepso_activity_stream_action', array(&$this, 'activity_stream_action'), 10, 2);

			// post actions filter
            add_filter('peepso_post_filters', array(&$this, 'post_filters'), 20,1);

            // Hooks for getting root post
            add_filter('peepso_root_post_' . self::MODULE_ID, function($root) {
                $activity = new PeepSoActivity();

                $root_activity = $activity->get_activity_data($root->act_comment_object_id, $root->act_comment_module_id);
                $root = $activity->get_activity_post($root_activity->act_id);

                return $root;
            });

            // Hooks into getting root object
            add_filter('peepso_root_object_' . self::MODULE_ID, function($root) {
                $activity = new PeepSoActivity();

                $root_activity = $activity->get_activity_data($root->act_comment_object_id, $root->act_comment_module_id);
                $root = $activity->get_activity($root_activity->act_id);

                return $root;
            });
		}
	}

    /**
     * Adds the Polls tab to the available post type options
     * @param  array $post_types
     * @param  array $params
     * @return array
     */
    public function post_types($post_types, $params = array())
    {

		if (!apply_filters('peepso_permissions_polls_upload', TRUE)) {
            return ($post_types);
        }

        $post_types['polls'] = array(
            'icon' => 'gcis gci-list',
            'name' => __('Poll', 'peepso-core'),
            'class' => 'ps-postbox__menu-item',
        );

        return ($post_types);
    }

	 /**
     * Displays the UI for the polls post type
     * @return string The input html
     */
    public function postbox_tabs($tabs)
    {

		if (!apply_filters('peepso_permissions_polls_upload', TRUE)) {
			return $tabs;
		}

		$data = array(
			'multiselect' => PeepSo::get_option('polls_multiselect', TRUE)
		);

        $tabs['polls'] = PeepSoTemplate::exec_template('polls', 'postbox-polls', $data, TRUE);

        return ($tabs);
    }

    /**
     * This function inserts the polls options on the post box
     * @param array $interactions is the formated html code that get inserted in the postbox
     * @param array $params
     */
    public function postbox_interactions($interactions, $params = array())
    {
        if (isset($params['is_current_user']) && $params['is_current_user'] === FALSE) {
            return ($interactions);
        }

        if (!apply_filters('peepso_permissions_polls_upload', TRUE)) {
            return ($interactions);
        }

        $interactions['poll'] = array(
            'icon' => 'gcis gci-list',
            'id' => 'poll-post',
            'class' => 'ps-postbox__menu-item',
            'click' => 'return;',
            'label' => '',
            'title' => __('Poll', 'peepso-core'),
            'style' => 'display:none'
        );

        return ($interactions);
    }

	/*
     * enqueue scripts for polls
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script('peepsopolls', PeepSo::get_asset('js/polls/bundle.min.js'),
            array('jquery', 'jquery-ui-sortable', 'peepso', 'peepso-postbox'), PeepSo::PLUGIN_VERSION, TRUE);

        add_filter('peepso_data', function($data) {
            $data['polls'] = array(
                'textPostboxPlaceholder' => __('Say something about this poll...', 'peepso-core'),
                'textOptionPlaceholder' => __('Option %d', 'peepso-core')
            );
            return $data;
        });
    }

	/**
     * Change the activity stream item action string
     * @param  string $action The default action string
     * @param  object $post   The activity post object
     * @return string
     */
    public function activity_stream_action($action, $post)
    {
        if (self::MODULE_ID === intval($post->act_module_id)) {
            $action = __(' asked a question', 'peepso-core');
		}
        return ($action);
    }


	/**
	* Sets the activity's module ID to the plugin's module ID
	* @param  array $activity
	* @return array
	*/
    public function activity_insert_data($activity)
    {
        $input = new PeepSoInput();

        // SQL safe
        $type = $input->value('type','',FALSE);

        if ('poll' === $type) {
            $activity['act_module_id'] = self::MODULE_ID;
		}

        return ($activity);
    }

    /**
     * Adds the postmeta to the post, only called when submitting from the polls tab
     * @param  int $post_id The post ID
     */
    public function after_add_post($post_id)
    {
        global $wpdb;

        $input = new PeepSoInput();
        $options = $input->value('options', array(), FALSE); // SQL safe, add_post_meta
        $allow_multiple = $input->int('allow_multiple');

		if (empty($options) || count($options) < 2) {
            return;
		}

		$post_meta = array();
		foreach ($options as $option) {
			$key = substr(md5($option),0,6);
			$post_meta[$key] = array(
				'label' => $option,
				'total_user_poll' => 0
			);
		}

		if ($allow_multiple === 1 && PeepSo::get_option('polls_multiselect', TRUE)) {
			$max_answers = 0;
		} else {
			$max_answers = 1;
		}

		add_post_meta($post_id, 'select_options', serialize($post_meta));
		add_post_meta($post_id, 'total_user_poll', 0);
		add_post_meta($post_id, 'max_answers', $max_answers);
    }


    /**
     * Attach the poll to the post display
     * @param  object $post The post
     */
    public function attach_poll($post)
    {
		if ($post->act_module_id != self::MODULE_ID) {
			return;
		}

		$user_polls = array();
        $is_voted = FALSE;
        $enabled = !get_current_user_id() ? FALSE : TRUE;
		if ( get_current_user_id() ) {
			$polls_model = new PeepSoPollsModel();
			$user_polls = $polls_model->get_user_polls(get_current_user_id(), $post->ID);
            $is_voted = $polls_model->is_voted(get_current_user_id(), $post->ID);

            if (class_exists('PeepSoGroupsPlugin')) {
                $group_id = get_post_meta($post->ID, 'peepso_group_id', true);
                if ($group_id) {
                    $group_user = new PeepSoGroupUser($group_id, get_current_user_id());
                    if (!$group_user->is_member) {
                        $is_voted = TRUE;
                        $enabled = FALSE;
                    }
                }
            }
		}

        $max_answers = (int) get_post_meta($post->ID, 'max_answers', TRUE);
		$options = @unserialize(get_post_meta($post->ID, 'select_options', TRUE));
		$total_user_poll = get_post_meta($post->ID, 'total_user_poll', TRUE);

		$data = array(
			'id' => $post->ID,
			'options' => (is_array($options) && count($options) > 1) ? $options : array(),
			'type' => $max_answers === 0 ? 'checkbox' : 'radio',
			'enabled' => $enabled,
			'is_voted' => $is_voted,
			'total_user_poll' => $total_user_poll ? $total_user_poll : 0,
			'user_polls' => $user_polls
		);

		PeepSoTemplate::exec_template('polls', 'content-media', $data);
    }

	public function permissions_polls_upload($permission)
	{
		$url = PeepSoUrlSegments::get_instance();

        $user_id = get_current_user_id();

		if ($url->get(1)) {
			if ($viewed_user = get_user_by('slug', $url->get(1))) {
				$user_id = $viewed_user->ID;
			}
		}

		// only on own profile
		if ($url->get(0) == 'peepso_profile' && $user_id !== get_current_user_id()) {
		    $permission = FALSE;
		}

		// if in group view and group integration is disabled
		if($url->get(0) == 'peepso_groups' && PeepSo::get_option('polls_group', 0) === 0) {
		    $permission = FALSE;
		}

        return apply_filters('peepso_permissions_polls_create', $permission);
	}

	/**
     * Disable repost on polls
     * @param array $actions The default options per post
     * @return  array
     */
	public function activity_post_actions($actions) {
		if ($actions['post']->act_module_id == self::MODULE_ID) {
			unset($actions['acts']['repost']);
		}
		return $actions;
	}

    /**
     *
     * @param array $options
     * @return array $options
     */
    public function post_filters($options) {
        $post = $options['post'];
        $options_acts = $options['acts'];

        if ( isset($post->act_module_id) && (int) $post->act_module_id === self::MODULE_ID ) {
        	if ( PeepSo::is_admin() || $post->post_author==get_current_user_id() || PeepSo::get_option('polls_changevote', FALSE) ) {
        		$polls_model = new PeepSoPollsModel();
        		$is_voted = $polls_model->is_voted(get_current_user_id(), $post->ID);
        		// Check if already voting.
	            $options_acts['changevote'] = array(
	            	'li-class' => 'ps-js-poll-option-changevote',
	                'label' => __('Change Vote', 'peepso-core'),
	                'icon' => 'gcir gci-check-square',
	                'click' => 'peepso.polls.change_vote(' . $post->ID . ', this); return false;',
	                'extra' => $is_voted ? '' : ' style="display:none"'
	            );
        	}

            unset($options_acts['repost']);
        }

        $options['acts'] = $options_acts;

        return $options;
    }
}

PeepSoPolls::get_instance();

// EOF
