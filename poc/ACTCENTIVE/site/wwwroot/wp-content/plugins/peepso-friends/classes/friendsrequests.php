<?php

class PeepSoFriendsRequests
{
	const TABLE = 'peepso_friend_requests';
	const STATUS_SENT = 'sent';
	const STATUS_RECEIVED = 'received';

	protected static $_instance = NULL;

	private $_requests = NULL;
	private $_iterator = NULL;

	private $received_friend_requests = array();

	public $template_tags = array(
		'has_sent_requests',
		'has_received_requests',
		'get_next_request',
		'show_request_thumb'
	);

	private function __construct()
	{
		add_action('peepso_friends_requests_after_add', array(&$this, 'after_add'), 10, 2);
		add_action('peepso_friends_requests_after_accept', array(&$this, 'after_accept'), 10, 2);
		add_action('peepso_friends_requests_after_ignore_block', array(&$this, 'after_ignore_block'), 10, 2);

		add_filter('peepso_friends_requests_cancel_request_notice-deny', array(&$this, 'deny_notice'));
		add_filter('peepso_friends_requests_cancel_request_notice-ignore', array(&$this, 'ignore_notice'));
		add_filter('peepso_friends_requests_cancel_request_notice-ignore_block', array(&$this, 'ignore_block_notice'));
	}

	/*
	 * return singleton instance
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return (self::$_instance);
	}

	/**
	 * Adds an entry to the friend_requests table
	 * @param  int $from_id The user sending the request
	 * @param  int $to_id   The user the request was sent to
	 *
	 * @return boolean TRUE or FALSE whether the entry is added.
	 */
	public function add($from_id, $to_id)
	{
		if ($from_id !== $to_id) {
			global $wpdb;

			// If there's already an existing request return false
			if ($this->request_status($from_id, $to_id)) {
                return new PeepSoError(__('[FRIENDS] request already sent', 'friendso'));
            }

			$data = array('freq_user_id' => $from_id, 'freq_friend_id' => $to_id);
			$wpdb->insert($wpdb->prefix . self::TABLE, $data);

			do_action('peepso_friends_requests_after_add', $from_id, $to_id);

			$PeepSoUserFollower = new PeepSoUserFollower($to_id, $from_id, TRUE);

            PeepSoSSEEvents::trigger('get_notifications', $to_id);

			return (TRUE);
		} else {
			$error = __('You do not have enough permissions.', 'friendso');
			return (new WP_Error('insufficient-permissions', $error));
		}
	}

	/**
	 * Removes an entry to the friend_requests table
	 * @param  int $request_id The friend request id
	 * @param  string $action The action to be performed after delete.
	 *
	 * @return boolean TRUE or FALSE whether the entry is added.
	 */
	public function remove($request_id, $action = 'remove')
	{
		$request = $this->get_request($request_id);

		$from_id = $request['freq_user_id'];
		$to_id = $request['freq_friend_id'];

		PeepSoSSEEvents::trigger('get_notifications', $to_id);

		if ($from_id !== $to_id) {
			global $wpdb;

			$wpdb->delete($wpdb->prefix . self::TABLE,
				array('freq_id' => $request_id)
			);

			do_action('peepso_friends_requests_after_' . $action, $from_id, $to_id);

			return (TRUE);
		} else {
			$error = __('You do not have enough permissions.', 'friendso');
			return (new WP_Error('insufficient-permissions', $error));
		}
	}

	/**
	 * Removes an entry to the friend_requests table and adds it to the friends table
	 * @param  int $request_id The request ID
	 *
	 * @return boolean TRUE or FALSE whether the entry is added.
	 */
	public function accept($request_id)
	{
		$request = $this->get_request($request_id);

		$from_id = $request['freq_user_id'];
		$to_id = $request['freq_friend_id'];

		if ($from_id !== $to_id) {
			$model = PeepSoFriendsModel::get_instance();

			if ($model->add_friend($from_id, $to_id)) {
				global $wpdb;

				$wpdb->delete($wpdb->prefix . self::TABLE,
					array('freq_id' => $request_id)
				);

				do_action('peepso_friends_requests_after_accept', $from_id, $to_id);

                $PeepSoUserFollower = new PeepSoUserFollower($from_id, $to_id, TRUE);
                $PeepSoUserFollower->set('follow', 1);

				return (TRUE);
			}

			return (FALSE);
		} else {
			$error = __('You do not have enough permissions.', 'friendso');
			return (new WP_Error('insufficient-permissions', $error));
		}
	}

	/**
	 * Returns the current _request object's iterator.
	 * @return ArrayIterator
	 */
	private function get_iterator()
	{
		if (is_null($this->_iterator))
			$this->_iterator = $this->_requests->getIterator();

		return ($this->_iterator);
	}

	/**
	 * Returns the user's sent friend requests and assigns them to $_requests .
	 * @param  int $user_id The user ID
	 * @return array
	 */
	public function get_sent_requests($user_id)
	{
		global $wpdb;

		$sql = 'SELECT * FROM `' . $wpdb->prefix . self::TABLE
			. '` WHERE (`freq_user_id` = %d)';

		$sent = $wpdb->get_results($wpdb->prepare($sql, $user_id), ARRAY_A);
		$this->_requests = new ArrayObject($sent);
		$this->_iterator = NULL;

		return $sent;
	}

	/**
	 * Returns the user's received friend requests and assigns them to $_requests .
	 * @param  int $user_id The user ID
	 * @return array
	 */
	public function get_received_requests($user_id = NULL)
	{
	    if(NULL == $user_id) {
	        $user_id = get_current_user_id();
        }

	    if(!array_key_exists($user_id, $this->received_friend_requests)) {
            global $wpdb;

            $sql = 'SELECT * FROM `' . $wpdb->prefix . self::TABLE
                . '` WHERE (`freq_friend_id` = %d) ORDER BY freq_id DESC';


            $received = $wpdb->get_results($wpdb->prepare($sql, $user_id), ARRAY_A);
            $this->_requests = new ArrayObject($received);
            $this->_iterator = NULL;
            $this->received_friend_requests[$user_id] = $received;
        }

		return $this->received_friend_requests[$user_id];

	}

	/**
	 * Returns TRUE or FALSE whether or not the user has sent requests.
	 * @param  int  $user_id The user ID
	 * @return boolean
	 */
	public function has_sent_requests($user_id)
	{
		$this->get_sent_requests($user_id);

		return ($this->_requests->count() > 0);
	}

	/**
	 * Returns TRUE or FALSE whether or not the user has sent requests.
	 * @param  int  $user_id The user ID
	 * @return boolean
	 */
	public function has_received_requests($user_id)
	{
		$this->get_received_requests($user_id);

		return ($this->_requests->count() > 0);
	}

	/**
	 * Iterates through the $_requests ArrayObject and returns the current
	 * @param  int $user_id
	 * @return PeepSoUser
	 */
	public function get_next_request($user_id = NULL)
	{
		if ($this->get_iterator()->valid()) {
			$current = $this->get_iterator()->current();
			$this->get_iterator()->next();
			return ($current);
		}

		return (FALSE);
	}

	/**
	 * Displays the friend thumbnail
	 * @param  PeepSoUser $friend
	 */
	public function show_request_thumb($request = NULL)
	{
		if ($request['freq_user_id'] != get_current_user_id()) {
			$from_id = $request['freq_friend_id'];
			$to_id = $user_id = $request['freq_user_id'];
		} else {
		 	$from_id = $request['freq_user_id'];
		 	$to_id = $user_id = $request['freq_friend_id'];
		}
		$model = PeepSoFriendsModel::get_instance();
		$friend = PeepSoUser::get_instance($user_id);
		$mutual_friends = count($model->get_mutual_friends(get_current_user_id(), $user_id));

		echo '<div class="ps-member__header">
						<div class="ps-avatar ps-avatar--member">',
							sprintf('<img alt="%s" title="%s" src="%s">',
								$friend->get_fullname(),
								$friend->get_fullname(),
								$friend->get_avatar()
							),
							'
						</div>
			<div class="ps-member__cover" style="background-image:url(' . $friend->get_cover(750) . ')"></div>
			</div>
			<div class="ps-member__body">
				<div class="ps-member__name">
					<a href="' , $friend->get_profileurl(), '" class="ps-members-item-title">'
						, do_action('peepso_action_render_user_name_before', $friend->get_id()) , $friend->get_fullname(), do_action('peepso_action_render_user_name_after', $friend->get_id()) ,
					'</a>
				</div>
				<div class="ps-member__details">';

				if ($mutual_friends === 0) {
					//echo '<div class="ps-friends__mutual">' . __('No mutual friends', 'pepesofriends') . '</div>';
				} else {
					//echo $mutual_friends . ' ' . _n(' mutual friend', ' mutual friends', $mutual_friends, 'friendso');
					echo '<a class="ps-friends__mutual" href="#" onclick="psfriends.show_mutual_friends(' . $from_id . ', ' . $to_id . '); return false;">'. $mutual_friends .' '. _n(' mutual friend', ' mutual friends', $mutual_friends, 'friendso') . '</a>';
				}

		echo '
				</div>';

		$this->request_buttons($from_id, $to_id);
		echo '</div>';

		// $this->request_options($from_id, $to_id);
		// $this->request_buttons($from_id, $to_id);
	}

	/**
	 * Displays a dropdown menu of options available to perform on a certain user based on their friend status.
	 * @param  int $from_id The user viewing the page.
	 * @param  int $to_id   The user to get actions for.
	 */
	public function request_options($from_id, $to_id)
	{
		$peepso_friends = PeepSoFriendsPlugin::get_instance();

		$options = $peepso_friends->get_friend_status($from_id, $to_id);
		if (0 === count($options)) {
			// if no options to display, exit
			return;
		}

		echo '<div class="ps-members-item-options ps-js-dropdown">', PHP_EOL;
		echo	'<button type="button" class="ps-btn ps-dropdown__toggle ps-js-dropdown-toggle" data-value="">', PHP_EOL;
		echo		'<span class="ps-icon-cog"></span>', PHP_EOL;
		echo	'</button>', PHP_EOL;

		echo	'<div class="ps-dropdown__menu ps-js-dropdown-menu">', PHP_EOL;
		foreach ($options as $name => $data) {
			echo '<a href="#" ';
			if (isset($data['click']))
				echo ' onclick="', esc_js($data['click']), '" ';
			echo ' ">';

			echo '<i class="', $data['icon'], '"></i><span>', $data['label'], '</span>', PHP_EOL;
			echo '</a>', PHP_EOL;
		}
		echo	'</div>', PHP_EOL;
		echo '</div>', PHP_EOL;
	}

	/**
	 * Displays available buttons to perform on a certain user based on their friend status.
	 * @param  int $from_id The user viewing the page.
	 * @param  int $to_id   The user to get actions for.
	 */
	public function request_buttons($from_id, $to_id)
	{
		$peepso_friends = PeepSoFriendsPlugin::get_instance();

		$buttons = $peepso_friends->get_friend_status($from_id, $to_id);
		if (0 === count($buttons)) {
			// if no buttons to display, exit
			return;
		}

		$request_buttons = '';

		foreach ($buttons as $name => $data) {
			$request_buttons .= '<a';

			if (isset($data['class']))
				$request_buttons .= ' class="' . $data['class'] . '"';
			if (isset($data['extra']))
				$request_buttons .= ' ' . $data['extra'];
			if (isset($data['click']))
				$request_buttons .= ' onclick="' . esc_js($data['click']) . '" ';

			$request_buttons .= ' ">';
			// if (isset($data['icon']))
			// 	$request_buttons .= '<i class="' . $data['icon'] . '"></i> ';
			if (isset($data['label']))
				$request_buttons .= '<span>' . $data['label'] . '</span>';

			if (isset($data['loading']))
				$request_buttons .= ' <img class="ps-loading" src="' . PeepSo::get_asset('images/ajax-loader.gif') .'" alt="" style="display: none"></span>';

			$request_buttons .= '</a>' . PHP_EOL;
		}

		$request_buttons = PeepSoTemplate::exec_template('friends', 'request-buttons', array('request_buttons' => $request_buttons, 'user_id' => $to_id), TRUE);
		echo $request_buttons;

		// echo '<div class="ps-members-item-buttons ps-js-dropdown">', PHP_EOL;
		// echo	'<button type="button" class="ps-btn ps-dropdown__toggle ps-js-dropdown-toggle" data-value="">', PHP_EOL;
		// echo		'<span class="ps-icon-cog"></span>', PHP_EOL;
		// echo	'</button>', PHP_EOL;

		// echo	'<div class="ps-dropdown__menu ps-js-dropdown-menu">', PHP_EOL;
		// foreach ($buttons as $name => $data) {
		// 	echo '<a href="#" ';
		// 	if (isset($data['click']))
		// 		echo ' onclick="', esc_js($data['click']), '" ';
		// 	echo ' ">';

		// 	echo '<i class="ps-icon-', $data['icon'], '"></i><span>', $data['label'], '</span>', PHP_EOL;
		// 	echo '</a>', PHP_EOL;
		// }
		// echo	'</div>', PHP_EOL;
		// echo '</div>', PHP_EOL;
	}

	/**
	 * Check if a request is sent or received
	 * @param  int $from_id The user sending the request
	 * @param  int $to_id   The user the request was sent to
	 * @return mixed FALSE if no request is sent between the two users.
	 */
	public static function request_status($from_id, $to_id)
	{
		global $wpdb;

		$sql = 'SELECT COUNT(`freq_id`) AS `count` FROM `' . $wpdb->prefix . self::TABLE
			. '` WHERE (`freq_user_id` = %d AND `freq_friend_id` = %d)';

		$requests = $wpdb->get_var($wpdb->prepare($sql, $from_id, $to_id));

		if ($requests > 0)
			return (self::STATUS_SENT);

		$requests = $wpdb->get_var($wpdb->prepare($sql, $to_id, $from_id));

		if ($requests > 0)
			return (self::STATUS_RECEIVED);

		return (FALSE);
	}

	/**
	 * Get a friend request ID from the database, matching $from_id and $to_id.
	 * @param  int $from_id The user sending the request.
	 * @param  int $to_id   The user being friended.
	 * @return mixed Returns FALSE if no request exists, the request ID if there is.
	 */
	public static function get_request_id($from_id, $to_id)
	{
		global $wpdb;

		$sql = 'SELECT `freq_id` FROM `' . $wpdb->prefix . self::TABLE
			. '` WHERE (`freq_user_id` = %d AND `freq_friend_id` = %d)';

		$requests = $wpdb->get_var($wpdb->prepare($sql, $from_id, $to_id));

		if ($requests > 0)
			return ($requests);

		$requests = $wpdb->get_var($wpdb->prepare($sql, $to_id, $from_id));

		if ($requests > 0)
			return ($requests);

		return (FALSE);
	}

	/**
	 * Fetches a row from the table.
	 * @param  int $request_id The request ID.
	 * @return array
	 */
	public function get_request($request_id)
	{
		global $wpdb;
		$sql = 'SELECT * FROM `' . $wpdb->prefix . self::TABLE
			. '` WHERE (`freq_id` = %d)';

		$request = $wpdb->get_row($wpdb->prepare($sql, $request_id), ARRAY_A);

		if (!is_null($request))
			return ($request);

		return (FALSE);
	}

	/**
	 * Send an email when a friend request is added.
	 * @param  int $from_id The user sending the request
	 * @param  int $to_id   The user the request was sent to
	 */
	public function after_add($from_id, $to_id)
	{
		$sender = PeepSoUser::get_instance($from_id);
		$recepient = PeepSoUser::get_instance($to_id);

		$title = sprintf(__('New Friend Request from %s', 'friendso'), $sender->get_fullname());

		$data = array_merge($recepient->get_template_fields('recepient'), $sender->get_template_fields('sender'));
		$data['useremail'] = $recepient->get_email();
		$data['friendslink'] = PeepSoFriendsPlugin::get_url(get_current_user_id(), 'requests');
		$data['profileurl'] = $recepient->get_profileurl().'friends/requests/';

        $i18n = __('New Friend Request from %s', 'friendso');
        $message = 'New Friend Request from %s';
        $args = [
            'friendso',
            $sender->get_fullname()
        ];

		PeepSoMailQueue::add_notification_new($to_id, $data, $message, $args, 'friend_request_send', 'friends_requests', PeepSoFriendsPlugin::MODULE_ID);
	}

	/**
	 * Send an email and notification when a friend request is accepted.
	 * @param  int $from_id The user sending the request
	 * @param  int $to_id   The user the request was sent to
	 */
	public function after_accept($from_id, $to_id)
	{
		$sender = PeepSoUser::get_instance($from_id);
		$recepient = PeepSoUser::get_instance($to_id);

		$title = sprintf(__('%s accepted your friend request', 'friendso'), $recepient->get_fullname());

		$data = array_merge($recepient->get_template_fields('recepient'), $sender->get_template_fields('sender'));
		$data['useremail'] = $sender->get_email();
		$data['friendslink'] = PeepSoFriendsPlugin::get_url(get_current_user_id(), 'friends');
		$data['recepientprofile'] = $recepient->get_profileurl();

        $i18n = __('%s accepted your friend request', 'friendso');
        $message = '%s accepted your friend request';
        $args = [
            'friendso',
            $recepient->get_fullname()
        ];

		PeepSoMailQueue::add_notification_new($from_id, $data, $message, $args, 'friend_request_accept', 'friends_requests', PeepSoFriendsPlugin::MODULE_ID);

		$note = new PeepSoNotifications();

		$i18n = __('accepted your friend request', 'friendso');
		$message = 'accepted your friend request';
        $args = ['friendso'];

		$note->add_notification_new($to_id, $from_id, $message, $args, 'friends_requests', PeepSoFriendsPlugin::MODULE_ID);
	}

	/**
	 * Send an email when a friend request is denied and adds the requesting user to the user’s block list.
	 * @param  int $from_id The user sending the request
	 * @param  int $to_id   The user the request was sent to
	 */
	public function after_ignore_block($from_id, $to_id)
	{
		$peepso_block = new PeepSoBlockUsers();
		$peepso_block->block_user_from_user($from_id, $to_id);
	}

	/**
	 * Returns the notice after denying a request.
	 * @param  string $notice The default notice
	 * @return string
	 */
	public function deny_notice($notice)
	{
		return __('Friend Request Denied', 'friendso');
	}

	/**
	 * Returns the notice after ignoring a request.
	 * @param  string $notice The default notice
	 * @return string
	 */
	public function ignore_notice($notice)
	{
		return __('Friend Request Ignored', 'friendso');
	}

	/**
	 * Returns the notice after ignoring and blocking a request.
	 * @param  string $notice The default notice
	 * @return string
	 */
	public function ignore_block_notice($notice)
	{
		return __('Friend Request Ignored', 'friendso');
	}
}

// EOF
