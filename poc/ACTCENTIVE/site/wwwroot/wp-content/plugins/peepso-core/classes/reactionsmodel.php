<?php

class PeepSoReactionsModel
{
	const TABLE = 'peepso_reactions';

	public $reactions;
    public $reactions_published;
	public $my_reaction;

	public $activity;			// PeepSoActivity instance
	public $act_id;
	public $act_module_id;
	public $act_external_id;

	public $has_default_title;

	private $_user_id;

	public function __construct($all = TRUE)
	{
		$post_status = (TRUE == $all) ? "any" : "publish";

		$this->_user_id = get_current_user_id();
		$this->reactions = array();
		$args = array(
			'post_type' 		=> array('peepso_reaction', 'peepso_reaction_user'),
			'orderby'			=> 'menu_order',
			'order'				=> 'ASC',
			'posts_per_page' 	=> -1,
			'post_status'		=> $post_status,
		);

		$posts = new WP_Query($args);

		if(!count($posts->posts)) {
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../install' . DIRECTORY_SEPARATOR . 'activate.php');
            $install = new PeepSoActivate();
		    $install->plugin_activation();
		    $posts = new WP_Query($args);
		}

		foreach($posts->posts as $post) {

			$reaction = array(
				'id'				=> $post->post_parent,
				'post_id'			=> $post->ID,
				'published'			=> intval(('publish' == $post->post_status)),
				'title' 			=> __($post->post_title,'peepso-core'),
				'content' 			=> __($post->post_content,'peepso-core'),
				'icon'				=> $post->post_excerpt,
				'icon_url'			=> str_ireplace(['https://','http://'],'//',plugin_dir_url(dirname(__FILE__)).'assets/images/svg/'.$post->post_excerpt),
				'custom'			=> intval(('peepso_reaction_user' == $post->post_type)),
				'order'				=> intval($post->menu_order),
				'has_default_title' => FALSE,
				'emotion'           => 1,
			);

            $emotion = $emotion = get_post_meta($post->ID,'reaction_emotion',TRUE);
            if(strlen($emotion)) {
                $reaction['emotion'] = $emotion;
            }

			if(1==$reaction['custom']) {
				$reaction['id'] = $post->ID;
				$reaction['has_default_title'] = intval((1 == get_post_meta($post->ID,'default_title',TRUE)));
			}

			if(strstr($post->post_excerpt, 'peepsocustom-')) {
				$reaction['icon_url'] = str_replace('peepsocustom-','', $post->post_excerpt);
			}

			$reaction['class']	='ps-reaction-emoticon-'.$reaction['id'];

            $reaction = (object) $reaction;
			$this->reactions[$reaction->id] = $reaction;

            if($reaction->published) {
                $this->reactions_published[$reaction->id] = $reaction;
            }
		}
	}

	public function init($act_id = NULL)
	{
		// Do not init for NULL act_id or the same act_ic
		if(NULL == $act_id || $this->act_id == $act_id) {
			return;
		}

		$this->act_id = $act_id;
		$my_reaction_id = FALSE;
		$this->my_reaction = FALSE;

		// has my like?
		$this->activity  		= new PeepSoActivity();
		$act					= $this->activity->get_activity($this->act_id);
		$this->act_module_id 	= $act->act_module_id;
		$this->act_external_id  = $act->act_external_id;

		$like = new PeepSoLike();
		$like = $like->user_liked($this->act_external_id, $this->act_module_id, $this->_user_id);

		if( TRUE === $like ) {
			$my_reaction_id =  0;
		} else {
			global $wpdb;

			$sql = "SELECT `reaction_type` FROM `{$wpdb->prefix}" . self::TABLE . "` "
				. " WHERE `reaction_act_id`=%d AND `reaction_user_id`=%d ";

			$sql = $wpdb->prepare($sql, $this->act_id, $this->_user_id);

			$res = $wpdb->get_var($sql);

			if (is_numeric($res)) {
				$my_reaction_id = intval($res);
			}
		}

		if(is_numeric($my_reaction_id)) {
			$this->my_reaction = $this->reaction($my_reaction_id);
			$this->my_reaction->class = 'liked ' . $this->my_reaction->class;
		}
	}

	public function reaction($id) {
		if(!isset($this->reactions[$id])) {
			// Default to a like to avoid fatals in unlikely case of missing reaction
			return clone $this->reactions[0];
		}

		return clone $this->reactions[$id];
	}

	/**
	 * Remove my Reaction
	 * @return bool
	 */
	public function user_reaction_reset($is_delete = true)
	{
		global $wpdb;

		if ($is_delete) {
			$like = new PeepSoLike();
			$user_liked = $like->user_liked($this->act_external_id, $this->act_module_id, $this->_user_id);
			if ($user_liked) {
				$like->remove_like($this->act_external_id, $this->act_module_id, $this->_user_id);
			}
		}

		// remove reaction
		$sql = "DELETE FROM `{$wpdb->prefix}" . self::TABLE . "` "
			. " WHERE `reaction_act_id`=%d "
			. " AND `reaction_user_id`=%d ";

		$sql = $wpdb->prepare($sql, $this->act_id, $this->_user_id);
		$wpdb->query($sql);

		// fire the action
		do_action('peepso_action_react_remove', (object) array(
			'react_external_id' => $this->act_external_id,
			'react_user_id' => $this->_user_id,
			'react_module_id' => $this->act_module_id
		));

		$this->my_reaction = FALSE;
	}

	/**
	 * Set my Reaction, fire notifications
	 * @param int $react_id
	 * @return void
	 */
	public function user_reaction_set( $react_id )
	{
		$this->user_reaction_reset($react_id);

		$react_id = intval($react_id);

		$act_post = $this->activity->get_activity_post($this->act_id);
		$post_id = $act_post->ID;
		$owner_id = $this->activity->get_author_id($post_id);

		$user = PeepSoUser::get_instance($this->_user_id);
		$user_owner = PeepSoUser::get_instance($owner_id);

		// fire the action
		do_action('peepso_action_react_add', (object) array(
			'react_external_id' => $this->act_external_id,
			'react_act_id' => $this->act_id,
			'react_user_id' => $this->_user_id,
			'react_module_id' => $this->act_module_id
		));

		// activity_followers fallback for post owner
        $act_notif = new PeepSo3_Activity_Notifications($post_id, $owner_id);
		if (!$act_notif->is_exists()) {
			$act_notif->set(1);
		}

		$do_notify = FALSE;

		$note = new PeepSoNotifications();

		if( $owner_id != $this->_user_id ) {
			$do_notify = TRUE;
			// notification data
			$mailq_data = array(
				'permalink' => PeepSo::get_page('activity', FALSE) . '?status/' . $act_post->post_title,
				'post_content' => $act_post->post_content,
			);

			$mailq_data = array_merge($mailq_data, $user->get_template_fields('from'), $user_owner->get_template_fields('user'));

			// the post/activity type
			$post_type = get_post_type($post_id);
			$post_type_object = get_post_type_object($post_type);
			$activity_type = $post_type_object->labels->activity_type;

			if (PeepSo::get_option_new('post_auto_follow_react')) {
				// activity_followers fallback for reactions author
				$act_notif = new PeepSo3_Activity_Notifications($post_id, $this->_user_id);
				if (!$act_notif->is_exists()) {
					$act_notif->set(1);
				}
			}
		}

		// perform like
		if( 0 == $react_id ) {
			$like = new PeepSoLike();
			$like->add_like($this->act_external_id, $this->act_module_id, $this->_user_id);

			if( TRUE === $do_notify ) {
				// send LIKE email
				$i18n = __('Someone liked your post', 'peepso-core');
				$message = 'Someone liked your post';
				$args = ['peepso-core'];
				PeepSoMailQueue::add_notification_new($owner_id, $mailq_data, $message, $args, 'like_post', 'like_post', PeepSoActivity::MODULE_ID);

				# 2499 notify if only user is following
				$act_notif = new PeepSo3_Activity_Notifications($post_id, $owner_id);
				if ($act_notif->is_following()) {
					// add LIKE notification
					$i18n = __('liked your post', 'peepso-core');
					$message = 'liked your post';
					$args = ['peepso-core'];

					$note->add_notification_new($this->_user_id, $owner_id, $message, $args, 'like_post', $this->act_module_id, $post_id, $this->act_id);
				}
			}

			// #2499 send notification for post followers ?
			$i18n = __('liked a post you are following', 'peepso-core');
			$message = 'liked a post you are following';
			$args = ['peepso-core'];

			$followers = $act_notif->get_followers();
			$skip = array($this->_user_id, $owner_id);
			foreach ($followers as $follower) {
				if (!in_array($follower, $skip)) {
					$act_notif = new PeepSo3_Activity_Notifications($post_id, $follower);
					if ($act_notif->is_following() && PeepSo::get_option_new('post_follow_notify_react')) {
						$note->add_notification_new($this->_user_id, $follower, $message, $args, 'like_post', $this->act_module_id, $post_id, $this->act_id);
					}
				}
			}

            $this->my_reaction = $this->reaction($react_id);
			return TRUE;
		}

		// perform reaction
		global $wpdb;

		$data = array(
			'reaction_user_id' => $this->_user_id,			// user_id adding the like
			'reaction_act_id' => $this->act_id,							// id of peepso_activities item
			'reaction_type' => $react_id,
		);

		$wpdb->insert($wpdb->prefix . self::TABLE, $data);

		if( TRUE == $do_notify ) {
			// send REACT email
			$reaction_text = $this->reaction($react_id)->content;

			$i18n = __('Someone %s your post', 'peepso-core');
			$message = 'Someone %s your post';

			$args = [
				'peepso-core',
				$reaction_text,
			];

			// If admin leaves reaction text empty, default to something easier to translate
			if(!strlen($reaction_text)) {
				$i18n = __('Someone reacted to your post', 'peepso-core');
				$message = 'Someone reacted to your post';

				$args = ['peepso-core'];
			}

			PeepSoMailQueue::add_notification_new($owner_id, $mailq_data, $message, $args, 'like_post', 'like_post', PeepSoActivity::MODULE_ID);

			# 2499 notify if only user is following
			$act_notif = new PeepSo3_Activity_Notifications($post_id, $owner_id);
			if ($act_notif->is_following()) {
				// add REACT notification
				$reaction_text = $this->reaction($react_id)->content;

				$i18n = __('%s your post', 'peepso-core');
				$message = '%s your post';

				$args = [
					'peepso-core',
					$reaction_text,
				];

				// If admin leaves reaction text empty, default to something easier to translate
				if(!strlen($reaction_text)) {
					$i18n = __('reacted to your post', 'peepso-core');
					$message = 'reacted to your post';

					$args = ['peepso-core'];
				}

				$note->add_notification_new($this->_user_id, $owner_id, $message, $args, 'like_post', $this->act_module_id, $post_id, $this->act_id);
			}
		}

		// #2499 send notification for post followers ?
		$i18n = __('reacted to a post you are following', 'peepso-core');
		$message = 'reacted to a post you are following';
		$args = ['peepso-core'];

		$followers = $act_notif->get_followers();
		$skip = array($this->_user_id, $owner_id);
		foreach ($followers as $follower) {
			if (!in_array($follower, $skip)) {
				$act_notif = new PeepSo3_Activity_Notifications($post_id, $follower);
				if ($act_notif->is_following() && PeepSo::get_option_new('post_follow_notify_react') ) {
					$note->add_notification_new($this->_user_id, $follower, $message, $args, 'like_post', $this->act_module_id, $post_id, $this->act_id);
				}
			}
		}

		$this->my_reaction = $this->reaction($react_id);

		return( TRUE );
	}

	/**
	 * Count the number of Reactions of given type on current activity
	 * @param int $react_id
	 * @return int
	 */
	public function get_reactions_count( $react_id )
	{
	    if (!isset($this->activity) || !$this->activity instanceof PeepSoActivity) {
		    return;
		}

		if (empty($this->act_module_id)) {
		#	return; // 2207
		}



		if( 0 == $react_id) {
			$like = $this->activity->get_like_status($this->act_external_id, $this->act_module_id);
			return $like['count'];
		}

		global $wpdb;
		$sql = "SELECT COUNT(*) FROM `{$wpdb->prefix}" . self::TABLE . "` " .
			" WHERE `reaction_act_id`=%d ";

		$sql .= " AND `reaction_type`=%d ";
		$sql = $wpdb->prepare($sql, $this->act_id, $react_id);

		$res = $wpdb->get_var($sql);
		return (intval($res));
	}

	/*****************************************//* @TODO THIS IS AWFUL *//******************************************/

	public function html_reactions()
	{
		$total_reactions = 0;

		foreach($this->reactions as $react_id => $reaction) {

			$count = $this->get_reactions_count( $react_id );

			if( $count > 0) {
				$reactions[$react_id] = $count;
				$total_reactions += $count;
			}
		}

		if( 0 === $total_reactions ) {
			return FALSE;
		}

		ob_start();
		$i=0;
		arsort($reactions);

		foreach($reactions as $react_id => $reaction_count) {

			$class = array();
			$class[]='ps-reaction__like';
			$class[]='ps-reaction-emoticon-'.$react_id;

			if(0==$i) {
				$class[] = 'ps-reaction__like--first';
				$i++;
			}
			$class=implode(' ', $class);

			$title=array();
			$title[]=$this->reaction($react_id)->title;
			$title[]='('.$reaction_count.')';

			$title=implode(' ', $title);
			?>
			<span class="<?php echo $class;?>" title="<?php echo $title;?>"></span>
			<?php
		}
		?>
		<a title="<?php echo $title;?>" href="#"
		onclick="reactions.action_html_reactions_details(this, <?php echo $this->act_id; ?>); return false;">

		<?php

		if( FALSE !== $this->my_reaction) {

			echo __('You', 'peepso-core');
			$total_reactions--;

			if($total_reactions> 0) {
				echo " + " , $total_reactions , ' ';
				echo _n('other','others',$total_reactions,'peepso-core');
			}

		} else {
			echo $total_reactions , ' ';
			echo _n('person','people',$total_reactions,'peepso-core');
		}

		echo '</a>';
		$res = ob_get_clean();
		return $res;
	}

	public function html_reactions_details()
	{
		ob_start();
		$reactions = array();
		?>
		<div class="ps-reactions__likes-list">
		<a id="ps-reaction-details-close" class="ps-reactions__likes-close ps-tip ps-tip--arrow ps-tip--inline" href="#"
		   onclick="reactions.action_html_reactions(this, <?php echo $this->act_id; ?>); return false;" aria-label="<?php echo __('Close reactions','peepso-core');?>">
			<i class="gcis gci-times"></i>
		</a>
		<?php
		// User ids for  likes
		$reactions_count = array(0=>0);
		$like = new PeepSoLike();
		$names = $like->get_like_names($this->act_external_id, $this->act_module_id);

		if (count($names) > 0) {
			foreach ($names as $name) {
				$reactions[0][]=$name->ID;
				$reactions_count[0]++;
			}
		}

		// User ids for each reactions
		global $wpdb;
		foreach($this->reactions as $react_id => $reaction) {

			if(0==$react_id) {
				continue;
			}

			$reactions_count[$react_id] = 0;// = array_merge($reactions_count, array("$react_id"=>0));

			$sql = "SELECT reaction_user_id  FROM `{$wpdb->prefix}" . self::TABLE . "` "
				. " WHERE `reaction_act_id`=%d "
				. " AND `reaction_type`=%d ";

			$sql = $wpdb->prepare($sql, $this->act_id, $react_id);

			$result = $wpdb->get_results($sql);

			if(count($result)) {
				foreach($result as $user) {
					$reactions[$react_id][]=$user->reaction_user_id;
					$reactions_count[$react_id]++;
				}
			}
		}

		// Most pop[ular reactions on top
		arsort($reactions_count);

		foreach($reactions_count as $react_id=>$count) {
			// array is sorted descending - abort the loop when encountering the first zero
			if(0==$count) {
				break;
			}

			$class = array();
			$class[]='ps-reaction__like';
			$class[]='ps-reaction-emoticon-'.$react_id;
			$class[] = 'ps-reaction__like--first';
			$class=implode(' ', $class);
			?>

			<div class="ps-reactions__likes-list-item">
			<span class="<?php echo $class;?>"><?php echo $this->reaction($react_id)->title;?> (<?php echo $count;?>):</span>
			<span class="ps-reactions__likes-list-users">
			<?php


			$html_names = array();

			if(in_array($this->_user_id, $reactions[$react_id])) {
				$user = PeepSoUser::get_instance($this->_user_id);
				$html_names[] = '<a class="ps-comment-user" href="' . $user->get_profileurl() . '" data-hover-card="' . $user->get_id() . '">' . __('You','peepso-core') . '</a>';

			}

			foreach($reactions[$react_id] as $user_id) {
				if ($user_id == $this->_user_id) {
					continue;
				}
				$user = PeepSoUser::get_instance($user_id);
				$html_names[] = '<a class="ps-comment-user" href="' . $user->get_profileurl() . '" data-hover-card="' . $user->get_id() . '">' . $user->get_fullname() . '</a>';
			}

			echo implode(', ', $html_names);
			echo '</span>';
			echo '</div>';
		}


		$res = ob_get_clean();
		$res .="</div>";
		return $res;
	}

	public function html_before_comments()
	{
		global $post;
		?>
			<div id="act-reactions-<?php echo $this->act_id; ?>"
			 class="ps-reactions__dropdown ps-post__action cstream-reactions-options ps-js-reaction-options ps-js-act-reactions-options--<?php echo $this->act_id; ?>"
			 data-count="">
			<div class="ps-reactions__list">
				<?php
				foreach ($this->reactions as $react_id => $reaction) :

					// skip unpublished
					if (1 != $reaction->published) 			{ continue; }

					// skip unconfigured (default titles)
					if (1 == $reaction->has_default_title) 	{ continue; }

					$title = trim($this->reaction($react_id)->title);

					// build CSS
					$class = array(
						$reaction->class,
						'ps-reaction-option',
						'ps-reaction-option--'.$this->act_id,
						'ps-reaction-option-'.$react_id.'--'.$this->act_id,
					);

					if (!empty($title)) {
						$class[] = 'ps-tooltip';
						$class[] = 'ps-tooltip--reaction';
					}

					if( $this->my_reaction && $this->my_reaction->id === $react_id ) {
						$class[]='ps-reaction-option-selected';
					}

					$class = implode(' ', $class);
					?>
					<span class="ps-reactions__list-item">
						<a href="#" class="<?php echo $class; ?>" aria-label="<?php echo $title;?>" data-tooltip="<?php echo $title; ?>"
						   onclick="reactions.action_react(this, <?php echo $this->act_id; ?>, <?php echo $post->ID;?>, <?php echo $react_id; ?>); return false;">
						</a>
					</span>
				<?php endforeach; ?>

				<?php
				$class = array(
					'ps-reaction-option',
					'ps-reaction-option-delete',
					'ps-reaction-option--'.$this->act_id,
					'ps-reaction-option-delete--'.$this->act_id,
				);

				$class = implode (' ', $class);
				?>

				<span class="ps-reactions__list-item ps-reactions__list-item--delete ps-reaction-option-delete--<?php echo $this->act_id;?>">
					<a class="<?php echo $class;?>" href="#"
					   data-tooltip="<?php echo __('Remove','peepso-core'); ?>"
					   onclick="reactions.action_react_delete(this, <?php echo $this->act_id; ?>, <?php echo $post->ID;?>); return false;">
					   <i class="gcir gci-times-circle"></i>
					</a>
				</span>

				</div>
			</div>
			<?php
			$class = array(
				'ps-post__action ps-stream-status ps-post__status',
				'cstream-reactions',
				'ps-js-act-reactions--'.$this->act_id,
			);

			$html_reactions = $this->html_reactions();

			if (FALSE === $html_reactions) {
				$class []='ps-reactions__likes--hide';
			}

			$class = implode(' ', $class);

			?>
			<div id="act-react-<?php echo $this->act_id; ?>"
				 class="ps-reactions__likes <?php echo $class;?>  " data-count="">
				<?php echo $html_reactions; ?>
			</div>
		<?php
	}
}
