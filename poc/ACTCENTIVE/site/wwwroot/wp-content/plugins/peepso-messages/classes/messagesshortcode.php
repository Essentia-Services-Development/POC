<?php

class PeepSoMessagesShortcode
{
	const SHORTCODE_MESSAGES = 'peepso_messages';

	public $template_tags = array();

	private static $_instance = NULL;
	private $_current_message_id = NULL;

    public $page;
    public $extra;

	private function __construct()
    {
    }

	/*
	 * retrieve singleton class instance
	 * @return instance reference to plugin
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
		return (self::$_instance);
	}

    public static function description() {
        return __('Displays the list of all chats (messages) and handles the single conversation views.','msgso');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'msgso') . ' - ' . __('User messages', 'msgso');
    }

    /*
     * Sets up the page for viewing. The combination of page and exta information
     * specifies which message to view.
     * @param string $page The 'root' of the page, i.e. 'profile'
     * @param string $extra Optional specifier of extra data, i.e. 'username'
     */
	public function set_page($page, $extra = '')
    {
		do_action('peepso_profile_completeness_redirect');

		$this->page = $page;
		$this->extra = $extra;

		global $wp_query;

		//if ($wp_query->is_404) {
//			$virt = new PeepSoVirtualPage($this->page, $this->extra);
		//}

		$parts = explode('/', $extra);
		$action = isset($parts[0]) ? $parts[0] : '';
		if (!empty($this->extra) && in_array($action, array('view', 'leave', 'delete'))) {
			foreach($parts as &$part) {
				$part = str_replace(array('?','&'),'',$part);
			}

			$input = new PeepSoInput();
			$peepso_messages_participants = new PeepSoMessageParticipants();
			$peepso_messages_recipients = new PeepSoMessageRecipients();
			$peepso_messages = PeepSoMessagesPlugin::get_instance();

			$this->_current_message_id = intval(sanitize_key($parts[1]));

			switch ($action)
			{
			case 'view':
				$model = new PeepSoMessagesModel();
				$parent_id = $model->get_root_conversation($this->_current_message_id);

				$wp_query->is_404 = (FALSE === $peepso_messages_participants->in_conversation(get_current_user_id(), $parent_id));

				if (TRUE === $wp_query->is_404)
					add_filter('peepso_messages_available_recipients', array(&$this, 'available_recipients'));
				else
					$peepso_messages_recipients->mark_as_viewed(get_current_user_id(), $this->_current_message_id);

				// Remove interactions
				add_filter('peepso_postbox_message', array(&$peepso_messages, 'textarea_placeholder'));
				break;

			case 'leave':
			    // SQL safe, WP sanitizes it
				if (wp_verify_nonce($input->value('_wpnonce','',FALSE), 'leave-conversation'))
					$peepso_messages_participants->remove_participant(get_current_user_id(), $this->_current_message_id);

				$model = new PeepSoMessagesModel();
				$parent_id = $model->get_root_conversation($this->_current_message_id);
				$user = PeepSoUser::get_instance(get_current_user_id());
				// Create the inline notification
				//$notification = sprintf(__('%s has left the conversation', 'msgso'), $user->get_fullname());
				$model->add_inline_notification(get_current_user_id(), $parent_id, PeepSoMessagesPlugin::MESSAGE_INLINE_LEFT_CONVERSATION);

				wp_safe_redirect(PeepSo::get_page('messages'));
				exit();
				break;

			case 'delete':
			    // SQL safe, WP sanitizes it
				if (wp_verify_nonce($input->value('_wpnonce','',FALSE), 'delete-message')) {
					$conversation_url = PeepSoMessages::get_instance()->get_message_url($this->_current_message_id);
					$peepso_messages_recipients->delete_from_conversation(get_current_user_id(), $this->_current_message_id);
					wp_safe_redirect($conversation_url);
					exit();
				}
				break;
			}
		}
	}

	/**
	 * Registers the callback function for the peepso_messages shortcode.
	 */
	public static function register_shortcodes()
	{
		add_shortcode(self::SHORTCODE_MESSAGES, array(self::get_instance(), 'shortcode_messages'));
	}

	/**
	 * Prepares the messages page
	 * @return string
	 */
	public function shortcode_messages($args)
	{
        PeepSo::reset_query();

		if (!get_current_user_id() || FALSE == apply_filters('peepso_access_content', TRUE, self::SHORTCODE_MESSAGES, PeepSoMessagesPlugin::MODULE_ID)) {

		    if(isset($args['guest_behavior'])) {

                if( $args['guest_behavior']=='silent') {
                    return;
                }

                if( $args['guest_behavior']=='login') {
                    return PeepSoTemplate::exec_template('general','login',NULL, TRUE);
                }



            }

            return PeepSoTemplate::do_404();
		}

		ob_start();
		echo PeepSoTemplate::get_before_markup();

		if (is_null($this->_current_message_id)) {
			$input = new PeepSoInput();
			$search = $input->value('search', NULL, FALSE);
			$num_results = 0;

			echo PeepSoTemplate::exec_template('messages', 'messages', array('search' => $search, 'num_results' => $num_results), TRUE);

		} else {
			echo $this->show_conversation($this->_current_message_id);
		}

		echo PeepSoTemplate::get_after_markup();



		return ob_get_clean();
	}

	/**
	 * Prepares the conversation page
	 * @param  int $message_id The post ID
	 * @return string             The HTML for the page
	 */
	public function show_conversation($message_id)
	{
		global $post;

		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation(intval($message_id));

		$post = get_post($parent_id);
		setup_postdata($post);

		$peepso_participants = new PeepSoMessageParticipants();
		$muted = $peepso_participants->mute_get($parent_id, get_current_user_id());

		#695 @todo : use model or not instead using get_user_meta
		$read_notification = intval(PeepSo::get_option('messages_read_notification',1));
		$notif = $read_notification == 1 ? $peepso_participants->read_notification_get($parent_id, get_current_user_id()) : 0;

		$participants = $peepso_participants->get_participants($parent_id, get_current_user_id());
		$show_blockuser = count($participants) < 3;
		if ($show_blockuser) {
			$current_user_id = get_current_user_id();
			foreach ($participants as $participant_user_id) {
				if ($current_user_id !== intval($participant_user_id)) {
					$show_blockuser_id = $participant_user_id;
					break;
				}
			}
		}

		$is_user_blocking_enable = PeepSo::get_option('user_blocking_enable', 0);

		$data = array(
			'parent' => $post,
			'muted' => isset($muted) && $muted ? TRUE : FALSE,
			'read_notification' => isset($read_notification) && $read_notification ? TRUE : FALSE,
			'notif' => isset($notif) && intval($notif) ? TRUE : FALSE,
			'show_blockuser' => $is_user_blocking_enable ? (isset($show_blockuser) && $show_blockuser ? TRUE : FALSE) : FALSE,
			'show_blockuser_id' => isset($show_blockuser_id) ? $show_blockuser_id : FALSE
		);

		// If photos add-on is present, disable modal comments for message images
		// TODO: change filter to 'peepso_photos_photo_click'
		add_filter('peepso_photos_photo_click', array(&$this, 'photo_click'), 10, 2);
		add_filter('peepso_photos_max_visible_photos', array(&$this, 'max_visible_photos'), 10, 2);

		$ret = PeepSoTemplate::exec_template('messages', 'view-message', $data, TRUE);

		remove_filter('peepso_photos_max_visible_photos', array(&$this, 'max_visible_photos'));
		remove_filter('peepso_photos_photo_click', array(&$this, 'photo_click'));

		return ($ret);
	}

	/**
	 * Empties the available_recipients, so that they may be refreshed via ajax.
	 */
	public function available_recipients()
	{
		wp_localize_script('msgso', 'available_recipients', array());
	}

	/**
	 * Unsets privacy from the postbox when on the message page.
	 // TODO: fully document parameter and return value
	 * @param  array $interactions
	 * @return array
	 */
	public function postbox_interactions($interactions)
	{
		if (isset($interactions['privacy']))
			unset($interactions['privacy']);

		if (count($interactions) > 0)
			return ($interactions);
		return (array());
	}

	/**
	 * Disables the modal comments for message photos
	 * @param  string $onclick The onclick string to be used for the photo link
	 * @param  object $photo The photo to use
	 // TODO: document return value
	 * @return string
	 */
	public function photo_click($onclick, $photo)
	{
		// TODO: needs escaping? What is $photo->location? A url? A string? use esc_url() or esc_js() as appropriate
		return 'return (ps_messages.open_image(\'' . $photo->location . '\'));';
	}

	/**
	 * Re-set maximum visible photos in message item
	 * @param  int $max_photos Current maximum visible photos setting
	 * @return int
	 */
	public function max_visible_photos($max_photos)
	{
		return 1000;
	}
}

// EOF
