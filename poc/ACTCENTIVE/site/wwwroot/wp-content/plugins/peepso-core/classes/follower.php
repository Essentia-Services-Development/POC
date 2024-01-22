<?php

class PeepSoFollower {

    private static $_instance = NULL;

    public function __construct() {
        add_filter('peepso_activity_post_clauses_follow', function ($following) {
            global $wpdb;
            $following['users'] = "( `$wpdb->posts`.`post_author` IN ( SELECT  `uf_passive_user_id` FROM `{$wpdb->prefix}peepso_user_followers` WHERE `uf_follow`=1 AND `uf_active_user_id`=" . get_current_user_id() . "))";
            return $following;
        });

        add_filter('peepso_profile_actions', array(&$this, 'profile_actions'), 10, 2);
        add_filter('peepso_member_buttons', array(&$this, 'member_buttons'), 10, 2);

        add_action('wp_enqueue_scripts', function() {
            wp_enqueue_script(PeepSo::PLUGIN_SLUG, PeepSo::get_asset('js/follower.min.js'), array('peepso'), PeepSo::PLUGIN_VERSION, TRUE );
        });

        add_action('peepso_profile_segment_followers', array(&$this, 'peepso_profile_segment_followers'));
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

    public function get_follower_status($passive_user_id, $active_user_id = NULL)
    {

        $ret = array();

        $PeepSoUserFollower = new PeepSoUserFollower($passive_user_id, $active_user_id);

        $follow_label = __('Not following', 'peepso-core');
        $follow_title = __('Click to follow this user', 'peepso-core');
        $class = 'ps-member__action ps-member__action--follow ps-focus__cover-action ps-js-friend-follow';
        $icon = 'eye-off';
        $extra = ' data-user-id="' . $passive_user_id . '" data-text-hover="' . esc_attr( __('Start following', 'peepso-core') ) . '"';

        if ($PeepSoUserFollower->follow) {
            $follow_label = __('Following', 'peepso-core');
            $follow_title = __('Click to unfollow this user', 'peepso-core');
            $class = 'ps-member__action ps-member__action--following ps-focus__cover-action ps-js-friend-unfollow';
            $icon = 'eye';
            $extra = ' data-user-id="' . $passive_user_id . '" data-text-hover="' . esc_attr( __('Stop following', 'peepso-core') ) . '"';
        }

        $ret['follow'] = array(
            'label' => $follow_label,
            'class' => $class,
            'click' => '',
            'loading' => TRUE,
            'icon' => $icon,
            'title' => $follow_title,
            'li-class' => '',
            'extra' => $extra,
        );

        return $ret;
    }

    /*
     * Add the "Follow User" button to the profile page
     * @parem array $acts The array of Profile Action items
     * @param int $user_id The user id of the Profile being viewed
     * @return array The modiified array of Profile actions
     */
    public function profile_actions($acts, $user_id)
    {
        // TODO: if check_permissions() tests user_id != get_current_user_id() remove it here; otherwise add it to the checks inside check_permissions()
        // TODO: we shouldn't ever need to do any additional tests than check_permissions()
        if ($user_id != get_current_user_id() && PeepSo::check_permissions($user_id, PeepSo::PERM_PROFILE_VIEW, get_current_user_id())) {
            $actions = $this->get_follower_status($user_id);

            foreach ($actions as $key => $action)
                $acts['friends_' . $key] = array(
                    'label' => $action['label'],
                    'class' => $action['class'],
                    'title' => $action['title'],
                    'click' => $action['click'],
                    'extra' => $action['extra'],
                );
        }

        return ($acts);
    }


    /**
     * Add the friend buttons when a user is searching from the members page.
     * @param  array $buttons
     * @param  int $user_id The member in the loop
     * @return array
     */
    public function member_buttons($buttons, $user_id)
    {
        $new_buttons = $this->get_follower_status($user_id);
        foreach ($new_buttons as $i => $value) {
            unset($new_buttons[$i]['icon']);
        }

        $buttons = array_merge($buttons, $new_buttons);

        return ($buttons);
    }

    /**
     * render the Followers profile segment
     *
     * @return void
     */
    public function peepso_profile_segment_followers()
    {
	    PeepSo::reset_query();

        $current = 'followers';
        $url = PeepSoUrlSegments::get_instance();

        if ($url->get(3) == 'following') {
            $current = 'following';
        }
        
        $view_user_id = PeepSoProfileShortcode::get_instance()->get_view_user_id();
        
        wp_register_script('peepso-page-followers',
        PeepSo::get_asset('js/page-followers.js', __DIR__),
        array('peepso', 'peepso-page-autoload'), PeepSo::PLUGIN_VERSION, TRUE);
        
        wp_localize_script('peepso-page-followers', 'peepsofollowers', [
            'current' => $current
        ]);
		wp_enqueue_script('peepso-page-followers');

		echo PeepSoTemplate::exec_template('followers', 'followers', array('view_user_id' => $view_user_id, 'current' => $current), TRUE);
    }
}

PeepSoFollower::get_instance();
