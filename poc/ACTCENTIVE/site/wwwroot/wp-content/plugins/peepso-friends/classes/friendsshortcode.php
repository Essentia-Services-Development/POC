<?php

class PeepSoFriendsShortcode
{
	const SHORTCODE_PENDING = 'peepsofriends_pending';
	const SHORTCODE_FRIENDS = 'peepsofriends_friends';

	public $url;

	private static $_instance = NULL;

	private $view_user_id = 0;

    public $url_segments;

	private function __construct()
	{
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
	}

	/*
	 * retrieve singleton class instance
	 * @return instance reference to plugin
	 */
	public static function get_instance()
	{
		if (self::$_instance === NULL) {
            self::$_instance = new self();
        }

		return (self::$_instance);
	}

	/**
	 * Enqueues the scripts used in this plugin only.
	 */
	public function enqueue_scripts()
	{
	}

	/**
	 * Registers the shortcodes to wordpress.
	 */
	public static function register_shortcodes()
	{
		add_shortcode(self::SHORTCODE_PENDING, array(self::get_instance(), 'shortcode_pending'));
		add_shortcode(self::SHORTCODE_FRIENDS, array(self::get_instance(), 'shortcode_friends'));
	}

	/**
	 * Displays the main friends page wrapped in the profile
	 */
	public function shortcode_friends()
	{
	    PeepSo::reset_query();

        $this->view_user_id = isset($this->view_user_id) ? $this->view_user_id : get_current_user_id();

		if (FALSE == apply_filters('peepso_access_content', TRUE, PeepSoFriendsShortcode::SHORTCODE_FRIENDS, PeepSoFriendsPlugin::MODULE_ID)) {
            return PeepSoTemplate::do_404();
		}

		$input = new PeepSoInput();
		$search = $input->value('query', NULL, FALSE);

		$peepso_friends = PeepSoFriendsModel::get_instance();

		if (!is_null($search)) {
			$search = stripslashes_deep($search);
			$search_results = new PeepSoUserSearch(array(), $this->view_user_id, $search);
			$peepso_friends->set_friends($search_results->results);
			$num_results = count($search_results->results);
		} else {
			$num_results = $peepso_friends->get_friends($this->view_user_id)->count();
		}

		wp_enqueue_script('peepso-page-friends',
			PeepSo::get_asset('js/page-friends.min.js', __DIR__),
			array('peepso', 'peepso-page-autoload'), PeepSo::PLUGIN_VERSION, TRUE);

		return PeepSoTemplate::exec_template('friends', 'friends', array('search' => $search, 'num_results' => $num_results, 'view_user_id' => $this->view_user_id), TRUE);
	}

	/**
	 * Displays the sent requests page.
	 */
	public function shortcode_pending()
	{
	    PeepSo::reset_query();

		if (FALSE == apply_filters('peepso_access_content', TRUE, PeepSoFriendsShortcode::SHORTCODE_FRIENDS, PeepSoFriendsPlugin::MODULE_ID)) {
            return PeepSoTemplate::do_404();
		}

		return PeepSoTemplate::exec_template('friends', 'pending', NULL, TRUE);
	}

	/**
	 * Displays the friend.
	 * @param  PeepSoUser $friend A PeepSoUser instance of the friend to be displayed.
	 */
	public function show_friend($friend)
	{
		$from_id = get_current_user_id();
		$to_id = $user_id = $friend->get_id();

		$peepso_friends = PeepSoFriendsModel::get_instance();
		$mutual_friends = count($peepso_friends->get_mutual_friends($to_id, $from_id));

		$is_self = ($from_id == $to_id) ? TRUE : FALSE;

		$online = '';
		if (PeepSo3_Mayfly::get('peepso_cache_'.$friend->get_id().'_online')) {
            $online = PeepSoTemplate::exec_template('profile', 'online', array('PeepSoUser'=>$friend), TRUE);
		}

		echo '<div class="ps-member ps-js-member" data-user-id="' . $user_id . '">';
		echo '<div class="ps-member__inner">';
		echo '<div class="ps-member__header">',
		sprintf('<a class="ps-avatar ps-avatar--member" href="' . $friend->get_profileurl() . '"><img alt="%s avatar" title="%s" src="%s">' . $online . '</a>',
			trim(strip_tags($friend->get_fullname())),
			trim(strip_tags($friend->get_fullname())),
			$friend->get_avatar('full')), '
			<div class="ps-member__cover" style="background-image:url(' . $friend->get_cover(750) . ')"></div>','
			<div class="ps-member__options">',

			PeepSoMemberSearch::member_options($to_id),

			'
			</div>
				</div>
				<div class="ps-member__body">
					<div class="ps-member__name">
						<a href="' , $friend->get_profileurl(), '">'
							, do_action('peepso_action_render_user_name_before', $friend->get_id()) , $friend->get_fullname(), do_action('peepso_action_render_user_name_after', $friend->get_id()),'
						</a>
					</div>
					<div class="ps-member__details">
					<div class="ps-friends__mutual">';

					if (FALSE === $is_self) {
						if ($mutual_friends === 0) {
							//echo __('No mutual friends', 'friendso');
						} else {
							echo '<a href="#" onclick="psfriends.show_mutual_friends(' . $from_id . ', ' . $to_id . '); return false;">'. $mutual_friends . ' ' . _n(' mutual friend', ' mutual friends', $mutual_friends, 'friendso') . '</a>';
						}
					}
			echo '</div>';
		echo '</div>';

		if ( FALSE == $is_self) {
			PeepSoMemberSearch::member_buttons_extra($to_id);
		}

		echo '</div>';
		echo '</div>';


		if ( FALSE == $is_self) {
			//PeepSoMemberSearch::member_options($to_id);
			PeepSoMemberSearch::member_buttons($to_id);

			#$this->request_options($from_id, $to_id);
			#$this->request_buttons($from_id, $to_id);
		}

		echo '</div>';
	}


	// Used to hook the shortcode methods into profile pages
	function profile_segment($view_user_id, $url_segments)
	{
		$this->url_segments = $url_segments;
		$this->view_user_id = $view_user_id;

		if ('requests' == $this->url_segments->get(3)) {
			return $this->shortcode_pending();
		}

		return $this->shortcode_friends();
	}

	/**
	 * Displays a dropdown menu of options available to perform on a certain user based on their friend status.
	 * @param  int $from_id The user viewing the page.
	 * @param  int $to_id   The user to get actions for.
	 * @param  boolean $echo Whether or not to echo the list in a template or return it as string. Used in friendsajax
	 */
	public function request_options($from_id, $to_id, $echo = TRUE)
	{
		$peepso_friends = PeepSoFriendsPlugin::get_instance();

		$options = apply_filters('peepso_friends_friend_options', $peepso_friends->get_friend_status($from_id, $to_id), $to_id, $from_id);

		if (0 === count($options)) {
            // if no options to display, exit
            return;
        }

		$request_options = '';

		foreach ($options as $name => $data) {
			$request_options .= '<li';

			if (isset($data['li-class'])) {
                $request_options .= ' class="' . $data['li-class'] . '"';
            }

			if (isset($data['extra'])) {
                $request_options .= ' ' . $data['extra'];
            }

			$request_options .= '><a href="#" ';

			if (isset($data['click'])) {
                $request_options .= ' onclick="' . esc_js($data['click']) . '" ';
            }

			$request_options .= ' ">';

			$request_options .= '<i class="' . $data['icon'] . '"></i><span>' . $data['label'] . '</span>' . PHP_EOL;
			$request_options .= '</a></li>' . PHP_EOL;
		}

		if ($echo) {
            echo PeepSoTemplate::exec_template('friends', 'request-options', array('request_options' => $request_options), TRUE);
        } else {
            return ($request_options);
        }
	}

	/**
	 * Displays available buttons to perform on a certain user based on their friend status.
	 * @param  int $from_id The user viewing the page.
	 * @param  int $to_id   The user to get actions for.
	 * @param  boolean $echo Whether or not to echo the list in a template or return it as string. Used in friendsajax
	 */
	public function request_buttons($from_id, $to_id, $echo = TRUE)
	{
		$peepso_friends = PeepSoFriendsPlugin::get_instance();

		$buttons = apply_filters('peepso_friends_friend_buttons', $peepso_friends->get_friend_status($from_id, $to_id), $to_id, $from_id);

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
			if (isset($data['icon']))
				$request_buttons .= '<i class="' . $data['icon'] . '"></i> ';
			if (isset($data['label']))
				$request_buttons .= '<span>' . $data['label'] . '</span>';

			if (isset($data['loading']))
				$request_buttons .= ' <img class="ps-loading" src="' . PeepSo::get_asset('images/ajax-loader.gif') .'" alt="" style="display: none"></span>';

			$request_buttons .= '</a>' . PHP_EOL;
		}

		$request_buttons = PeepSoTemplate::exec_template('friends', 'request-buttons', array('request_buttons' => $request_buttons, 'user_id' => $to_id), TRUE);

		if ($echo) {
			echo $request_buttons;
		} else {
			return ($request_buttons);
		}
	}
}
