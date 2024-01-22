<?php

class PeepSoActivityShortcode
{
	private static $_instance = NULL;

	private $page = NULL;
	private $extra = NULL;
	private $permalink = NULL;
	private $post_id = NULL;
	private $act_access = NULL;
	private $act_owner_id = NULL;

    public $url_segments;

	// private $title_from_status = NULL;

	// TODO: shortcodes should not have template callbacks; this needs to be moved to PeepSoActivity
	public $template_tags = array(
		'is_permalink_page'
	);

	public function __construct()
	{
		add_shortcode('peepso_activity', array(&$this, 'do_shortcode'));
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

		add_filter('peepso_page_title', array(&$this,'peepso_page_title'));
		add_filter('peepso_page_title_check', array(&$this, 'peepso_page_title_check'));

		// change page title for single status
		// add_filter( 'pre_get_document_title', array(&$this, 'peepso_change_page_title'), 200, 2);
	}

	/*
	 * return singleton instance of the plugin
	 */
	public static function get_instance()
	{
		if (self::$_instance === NULL)
			self::$_instance = new self();
		return (self::$_instance);
	}

    public static function description() {
        return __('Displays the community activity stream. It\'s an overview of the entire community. It also doubles as a Landing Page / Login Screen for not-logged in users.', 'peepso-core');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'peepso-core') . ' - ' . __('Home', 'peepso-core');
    }

	/**
	 * Check if author has permission to see the owner post
	 * @return boolean return as TRUE or FALSE depending on permission
	 */
	private function is_accessible()
	{
		if (! $this->is_permalink_page())
			return (TRUE);

		if (empty($this->permalink))
			return FALSE;

		if (NULL === $this->act_access) {
			global $wpdb;

			/**
			 * #6046 somehow, when status pending, the post_name is empty.
			 * But, we can use value of post_title to get the act_access and act_owner_id
			 * based on permalink.
			 */
			$sql = 'SELECT `ID`, `act_access`, `act_owner_id`, `act_external_id`, `post_status` ' .
				" FROM `{$wpdb->posts}` " .
				" LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` ON `act_external_id`=`{$wpdb->posts}`.`ID` " .
				' WHERE (`post_name`=%s OR `post_title`=%s) AND `post_type`=%s ' .
				' LIMIT 1 ';
			$ret = $wpdb->get_row($wpdb->prepare($sql, $this->permalink, $this->permalink, PeepSoActivityStream::CPT_POST));


			if (NULL !== $ret) {
				if ($ret->post_status != 'publish') {
					$post_meta = get_post_meta($ret->act_external_id, 'peepso_photo_type', TRUE);
					if (!$post_meta) {
						return FALSE;
					}
				}
				$this->post_id = $ret->ID;
				$this->act_access = $ret->act_access;
				$this->act_owner_id = $ret->act_owner_id;
			} else {
                return FALSE;
            }
		}

		// look up the post so check_permissions() knows which post we're talking about
		$args = array('page_id' => $this->post_id, 'post_type' => PeepSoActivityStream::CPT_POST, 'post_status' => array('publish', 'pending'));
		$query = new WP_Query($args);
		global $post;
		if ($query->have_posts()) {
			$query->the_post();
			// fix up the post values
			$post->act_access = $this->act_access;
			$post->act_owner_id = $this->act_owner_id;

			// $this->title_from_status = $post->post_content;
            // use check_permissions() to see if current user van view this post
            return (PeepSo::check_permissions(intval($post->post_author), PeepSo::PERM_POST_VIEW, get_current_user_id(), TRUE));
		}

		return FALSE;
	}

	/*
	 * shortcode callback for the Activity Stream
	 * @param array $atts Shortcode attributes
	 * @param string $content Contents of the shortcode
	 * @return string output of the shortcode
	 */
	public function do_shortcode($atts, $content)
	{
		PeepSo::set_current_shortcode('peepso_activity');
        PeepSo::reset_query();

		if (FALSE == $this->is_accessible() || FALSE == apply_filters('peepso_access_content', TRUE, 'peepso_activity', PeepSoActivity::MODULE_ID)) {
		    // Give the activity class a chance to run redirects, but do not output anything
            $do_not_output = PeepSoTemplate::exec_template('activity', 'activity', NULL, TRUE);

            return PeepSoTemplate::do_404();
		}

		wp_enqueue_style('peepso-activity');
		wp_enqueue_script('peepso-activity');
		if (!is_user_logged_in() && PeepSo::get_option('recaptcha_login_enable', 0)) {
			wp_enqueue_script('peepso-recaptcha');
		}

        return PeepSoTemplate::get_before_markup() . PeepSoTemplate::exec_template('activity', 'activity', NULL, TRUE) .  PeepSoTemplate::get_after_markup();
	}

	/*
	 * enqueues the scripts needed by the Activity Stream
	 */
	public function enqueue_scripts()
	{
		wp_register_script('peepso-comment', PeepSo::get_asset('js/comment.min.js'),
			array('peepso', 'peepso-npm'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_localize_script('peepso-comment', 'peepsocommentdata', array(
			'label_reply' => __('Reply', 'peepso-core'),
			'label_view' => __('View Replies', 'peepso-core'),
			'icon_reply' => 'ps-icon-plus',
			'icon_view' => 'ps-icon-eye'
		));

		wp_register_script('peepso-form', PeepSo::get_asset('js/form.min.js'),
			array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_register_script('peepso-activitystream', PeepSo::get_asset('js/activitystream.min.js'),
			array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);

		wp_register_style('peepso-activity', FALSE, array('peepso-datepicker'), PeepSo::PLUGIN_VERSION, 'all');
		wp_register_script('peepso-activity', PeepSo::get_asset('js/activity.min.js'),
			array('peepso', 'peepso-activitystream', 'peepso-window', 'peepso-comment', 'peepso-form', 'peepso-datepicker'), PeepSo::PLUGIN_VERSION, TRUE);

		add_filter( 'peepso_data_elements', function( $data ) {
			if ( ! isset( $data['post'] ) ) {
				$data['post'] = array();
			}

			$data['post']['text_save'] = __('Save', 'peepso-core');
			$data['post']['text_saved'] = __('Saved', 'peepso-core');

			$text_saved_notice = sprintf(
				__('Post saved! See your saved posts <a href="%s" onclick="%s">here</a>', 'peepso-core'),
				PeepSo::get_page('activity') . '#saved',
				'window.location = \'' . PeepSo::get_page('activity') . '#saved\'; setTimeout(window.location.reload(), 500);'
			);

			// FOR MATTHEW: Edit the HTML tooltip as you prefer...
			$data['post']['html_saved_notice'] = '
				<div class="ps-stream__save-tooltip"><span>' . $text_saved_notice . '</span></div>
			';


			// Subscribe / Unsubscribe
			$data['post']['text_subscribe'] = __('Follow', 'peepso-core');
			$data['post']['text_unsubscribe'] = __('Following', 'peepso-core');

			$text_subscribed_notice = sprintf(
				__('Post followed! See the followed content <a href="%s" onclick="%s">here</a>', 'peepso-core'),
				PeepSo::get_page('activity') . '#following',
				'window.location = \'' . PeepSo::get_page('activity') . '#following\'; setTimeout(window.location.reload(), 500);'
			);

			// FOR MATTHEW: Edit the HTML tooltip as you prefer...
			$data['post']['html_subscribed_notice'] = '
				<div class="ps-stream__follow-tooltip"><span>' . $text_subscribed_notice . '</span></div>
			';

			return $data;
		} );

		add_filter( 'peepso_data', function( $data ) {
			if ( ! isset( $data['activity'] ) ) {
				$data['activity'] = array();
			}

			$data['activity']['template_pin'] = PeepSoTemplate::exec_template('activity', 'dialog-pin', NULL, TRUE);
			$data['activity']['template_repost'] = PeepSoTemplate::exec_template('activity', 'dialog-repost', NULL, TRUE);
			return $data;
		} );

		wp_enqueue_style('peepso-activity');
		wp_enqueue_script('peepso-activity');

		wp_register_script('peepso-resize', PeepSo::get_asset('js/jquery.autosize.min.js'),
			array('jquery'), PeepSo::PLUGIN_VERSION, TRUE);
		wp_enqueue_script('peepso-resize');

		wp_enqueue_script('peepso-postbox');
		wp_enqueue_script('peepso-posttabs');
	}


	/*
	 * Sets up the page for viewing. The combination of page and exta information
	 * specifies which post's permalink to view.
	 * @param string $page The 'root' of the page, i.e. 'activity'
	 * @param string $extra Optional specifier of extra data, i.e. '?status/{permalink}'
	 */
	public function set_page($url_segments)
	{
        if(!$url_segments instanceof PeepSoUrlSegments) {
            $url_segments = PeepSoUrlSegments::get_instance();
        }

		$this->url_segments = $url_segments;

		global $wp_query;

		if ($wp_query->is_404) {
			echo "<h1>404</h1>";
			# $virt = new PeepSoVirtualPage($this->url_segments->get(0), $this->url_segments->get(1));
		}

		if ($this->url_segments->get(1)) {

			switch ($this->url_segments->get(1))
			{
			case 'status':
				$this->permalink = sanitize_key($this->url_segments->get(2));
				if(PeepSo::is_dev_mode('iframe_embeds') && isset($_GET['peepso_embed'])) {
                    echo "<!--PEEPSO_IS_POST-->";
				    echo "single post view should show here";
				    die();
                }
				break;
			}
		}
	}

	/*
	 * Return the permalink stored in the ActivityShortcode that indicates what content to display
	 * @return string post_title value to be shown as activity
	 */
	public function get_permalink()
	{
		return ($this->permalink);
	}

	/**
	 * Returns TRUE or FALSE whether the current page is from a permalink.
	 * @return boolean
	 */
	public function is_permalink_page()
	{
		return (!is_null($this->permalink));
	}

	public function peepso_change_page_title($title, $sep=''){

		if( $this->is_accessible()) {
			if ($this->is_permalink_page() && !empty($this->title_from_status)) {
				$title = (strlen($this->title_from_status) > 50) ?
					substr($this->title_from_status, 0, 50) :
					$this->title_from_status;
			}
		}

		return $title;
	}

	public function peepso_page_title( $title )
	{
		if( 'peepso_activity' == $title['title']) {
			global $post;
			$title['newtitle'] = $title['title'] = $post->post_title;
		}

		return $title;
	}

	public function peepso_page_title_check($post) {
		if (isset($post->post_content) && strpos($post->post_content, '[peepso_activity]') !== FALSE) {
			return TRUE;
		}

		return $post;
	}
}

// EOF
