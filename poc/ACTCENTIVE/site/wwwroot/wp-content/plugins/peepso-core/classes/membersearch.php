<?php

class PeepSoMemberSearch extends PeepSoAjaxCallback
{
    private $_member_query = NULL;

    public $template_tags = array(
        'found_members',
        'get_next_member',
        'show_member',
        'show_online_member',
        'show_latest_member'
    );

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        $list_exception = array();
        $allow_guest_access = PeepSo::get_option('allow_guest_access_to_members_listing', 0);
        if($allow_guest_access) {
            array_push($list_exception, 'search');
        }

        return $list_exception;
    }

    /**
     * GET
     * Search for users matching the query.
     * @param  PeepSoAjaxResponse $resp
     */
    public function search(PeepSoAjaxResponse $resp)
    {
        $args = array();
        $args_pagination = array();
        $page = $this->_input->int('page', 1);

        // Sorting
        $column = (PeepSo::get_option('system_display_name_style', 'real_name') == 'real_name' ? 'display_name' : 'username');

        $order_by	= $this->_input->value('order_by', $column, FALSE); // SQL Safe
        $order		= $this->_input->value('order', ($order_by == $column ? 'ASC' : NULL), array('asc','desc'));

        if( NULL !== $order_by && strlen($order_by) ) {
            if('ASC' !== $order && 'DESC' !== $order) {
                $order = 'DESC';
            }

            $args['orderby']= $order_by;
            $args['order']	= $order;
        }

        // Additional peepso specific filters

        // Avatar only
        $peepso_args['avatar_custom'] = $this->_input->int('peepso_avatar', 0);
        if ( 1 !== $peepso_args['avatar_custom'] ) {
            unset( $peepso_args['avatar_custom'] );
        }

        // Followed only
        $peepso_args['following'] = $this->_input->value('peepso_following', -1, FALSE); // SQL Safe
        if ( !in_array($peepso_args['following'], array(0,1))) {
            unset( $peepso_args['following'] );
        }


        // Blocked only
        $peepso_args['blocked'] = $this->_input->int('blocked', 0);
        if ( 1 !== $peepso_args['blocked'] ) {
            unset( $peepso_args['blocked'] );
        }

        // Gender filter
        $peepso_args['meta_gender'] = strtolower($this->_input->value('peepso_gender', '', FALSE)); // SQL Safe
        if ( !in_array( $peepso_args['meta_gender'], array('m','f') ) && strpos($peepso_args['meta_gender'], 'option_') === FALSE) {
            unset( $peepso_args['meta_gender'] );
        }

        $peepso_args = apply_filters('peepso_member_search_args', $peepso_args, $this->_input);

        if( is_array($peepso_args) && count($peepso_args)) {
            $args['_peepso_args'] = $peepso_args;
        }

        // default limit is 1 (NewScroll)
        $limit = $this->_input->int('limit', 1);

        $resp->set('page', $page);
        $args_pagination['offset'] = ($page-1)*$limit;
        $args_pagination['number'] = $limit;

        // Merge pagination args and run the query to grab paged results
        $args = array_merge($args, $args_pagination);
        $query = stripslashes_deep($this->_input->value('query', '', FALSE)); // SQL Safe
        $query_results = new PeepSoUserSearch($args, get_current_user_id(), $query);
        $members_page = count($query_results->results);
        $members_found = $query_results->total;

        $no_html_data = [];

        if (count($query_results->results) > 0) {

            foreach ($query_results->results as $user_id) {

                // REST search workaround
                if($this->_input->int('no_html',0)) {
                    $members = '';
                    $no_html_data[] = PeepSouser::get_instance($user_id);
                    continue;
                }

                // @todo this seems to be unused
                // $buttons = apply_filters('peepso_member_notification_buttons', array(), $user_id);

                ob_start();

                if( $user_id == get_current_user_id() ) {
                  $member_class = ' ps-member--me';
                } else {
                  $member_class = '';
                }

                echo '<div class="ps-member' . $member_class . ' ps-js-member" data-user-id="' . $user_id . '">';
                echo '<div class="ps-member__inner">';
                $this->show_member(PeepSoUser::get_instance($user_id));
                echo '</div>';
                echo '</div>';

                $members[] = ob_get_contents();

                ob_end_clean();

            }

            if($members_found > 0)
            {
                $resp->success(TRUE);
                $resp->set('members', $members);
            }
            else
            {
                $resp->success(FALSE);
                $resp->error(__('No users found.', 'peepso-core'));
            }



        } else {
            $resp->success(FALSE);
            $resp->error(__('No users found.', 'peepso-core'));
        }

        // REST search workaround
        if($this->_input->int('no_html',0)) {
            $resp->set('no_html_data', $no_html_data);
            return;
        }

        if($page == 1) {
            (new PeepSo3_Search_Analytics())->store($query, 'members');
        }

        $resp->set('members_page', $members_page);
        $resp->set('members_found', $members_found);
    }

    /**
     * Sets the _member_query variable to use is template tags
     * @param PeepSoUserSearch $query
     */
    public function set_member_query(PeepSoUserSearch $query)
    {
        $this->_member_query = $query;
    }

    /**
     * Return TRUE/FALSE if the user has friends
     * @return boolean
     */
    public function found_members()
    {
        if (is_null($this->_member_query))
            return FALSE;

        return (count($this->_member_query) > 0);
    }

    /**
     * Iterates through the $_member_query and returns the current member in the loop.
     * @return PeepSoUser A PeepSoUser instance of the current member in the loop.
     */
    public function get_next_member()
    {
        if (is_null($this->_member_query))
            return FALSE;

        return $this->_member_query->get_next();
    }

    /**
     * Displays the member.
     * @param  PeepSoUser $member A PeepSoUser instance of the member to be displayed.
     */
    public function show_member($member)
    {
        PeepSoTemplate::exec_template('members', 'member-item', array('member' => $member));

        $this->member_buttons($member->get_id());
    }

    /**
     * Displays the online member.
     * @param  PeepSoUser $member A PeepSoUser instance of the member to be displayed.
     */
    public function show_online_member($member)
    {
        echo '<a class="ps-avatar ps-avatar--member ps-tip ps-tip--inline ps-tip--arrow" href="' . $member->get_profileurl() . '" aria-label="' . strip_tags($member->get_fullname()) . '">
				<img alt="' . strip_tags($member->get_fullname()) . ' avatar"
				src="' . $member->get_avatar() . '"></a>';


        //$this->member_options($member->get_id());
        //$this->member_buttons($member->get_id());
    }

    /**
     * Displays the latest member.
     * @param  PeepSoUser $member A PeepSoUser instance of the member to be displayed.
     */
    public function show_latest_member($member)
    {
        $online = '';
        if (PeepSo3_Mayfly::get('peepso_cache_'.$member->get_id().'_online')) {
            $online = PeepSoTemplate::exec_template('profile', 'online', array('PeepSoUser'=>$member,'class'=>'ps-online--static ps-user__status--member'), TRUE);
        }

        echo '<a class="ps-avatar ps-avatar--member ps-tip ps-tip--inline ps-tip--arrow" href="' . $member->get_profileurl() . '" aria-label="' . strip_tags($member->get_fullname()) . '">
				<img alt="' . strip_tags($member->get_fullname()) . ' avatar"
				src="' . $member->get_avatar() . '"> ' . $online . '</a>';

        //$this->member_options($member->get_id());
        //$this->member_buttons($member->get_id());
    }

    /**
     * Displays a dropdown menu of options available to perform on a certain user based on their member status.
     * @param int $user_id The current member in the loop.
     */
    public static function member_options($user_id, $profile = FALSE)
    {
        if( get_current_user_id() == $user_id ) {
            return;
        }

        $options = array();

        $blk = new PeepSoBlockUsers();

        if ($blk->is_user_blocking(get_current_user_id(), $user_id)) {

            if (PeepSo::get_option_new('user_blocking_enable')) {
                $options['unblock'] = array(
                    'label' => __('Unblock User', 'peepso-core'),
                    'click' => 'ps_member.unblock_user(' . $user_id . ', this);',
                    'title' => __('Allow this user to see all of your activities', 'peepso-core'),
                    'icon' => 'lock',        // @todo icon
                );
            }

        } else {

            if (PeepSo::get_option_new('user_blocking_enable')) {
                $options['block'] = array(
                    'label' => __('Block User', 'peepso-core'),
                    'click' => 'ps_member.block_user(' . $user_id . ', this);',
                    'title' => __('This user will be blocked from all of your activities', 'peepso-core'),
                    'icon' => 'remove',
                );
            }

            if ($user_id !== get_current_user_id() && 1 === PeepSo::get_option('site_reporting_enable')) {
                $options['report'] = array(
                    'label' => __('Report User', 'peepso-core'),
                    'title' => __('Report this Profile', 'peepso-core'),
                    // 'click' => 'ps_member.report_user(' . $user_id . ', this);',
                    'click' => 'peepso.user(' . $user_id . ').doReport();',
                    'icon' => 'warning-sign',
                );
            }

            // ban/unban only available for admin role
	        // only if the target is not an administrator
            if ( PeePso::is_admin() && !PeepSo::is_admin($user_id) ) {
                // ban
                $options['ban'] = array(
                    'label' => __('Ban', 'peepso-core'),
                    'click' => 'ps_member.ban_user(' . $user_id . ', this);',
                    'icon' => 'minus-sign',
                );

                // "unban" is only available from profile page
                if (FALSE !== $profile) {
                    $options['unban'] = array(
                        'label' => __('Unban', 'peepso-core'),
                        'click' => 'ps_member.unban_user(' . $user_id . ', this);',
                        'icon' => 'plus-sign',
                    );

                    // check ban status
                    $user = PeepSoUser::get_instance($user_id);
                    if ('ban' == $user->get_user_role()) {
                        unset($options['ban']);
                        $ban_date = get_user_meta( $user_id, 'peepso_ban_user_date', true );
                        if($ban_date) {
                            $ban_date = date(get_option('date_format'), $ban_date);
                            $options['unban']['label'] = sprintf(__('Banned until %s', 'peepso-core'), $ban_date);
                        } else {
                            $options['unban']['label'] = __('Banned indefinitely', 'peepso-core');
                        }

                        $options['unban']['label'].='. <b>'.__('Unban', 'peepso-core').'</b>?';

                    } else {
                        unset($options['unban']);
                    }
                }

                wp_enqueue_style('peepso-datepicker');
                wp_enqueue_script('peepso-datepicker');
            }

            $options = apply_filters('peepso_member_options', $options, $user_id);
        }

        if (0 === count($options)) {
            return;
        }

        $member_options = '';
        foreach ($options as $name => $data) {
            $member_options .= '<a href="#"';
            if (isset($data['li-class']))
                $member_options .= ' class="' . $data['li-class'] . '"';
            if (isset($data['extra']))
                $member_options .= ' ' . $data['extra'];
            if (isset($data['click']))
                $member_options .= ' onclick="' . rtrim( esc_js($data['click']), ';' ) . '; return false"';
            $member_options .= '>';
            $member_options .= '<i class="' . $data['icon'] . '"></i><span>' . $data['label'] . '</span>' . PHP_EOL;
            $member_options .= '</a>' . PHP_EOL;
        }

        if( FALSE === $profile) {
            echo PeepSoTemplate::exec_template('members', 'member-options', array('member_options' => $member_options), TRUE);
        } else {
            echo PeepSoTemplate::exec_template('profile', 'profile-options', array('profile_options' => $member_options), TRUE);
        }
    }

    /**
     * Displays a available buttons to perform on a certain user based on their member status.
     * @param int $user_id The current member in the loop.
     */
    public static function member_buttons($user_id)
    {
        if( $user_id == get_current_user_id() ) {
            return;
        }

        $PeepSoBlockUsers = new PeepSoBlockUsers();
        if($PeepSoBlockUsers->is_user_blocking(get_current_user_id(), $user_id)) {
            return;
        }

        $buttons = apply_filters('peepso_member_buttons', array(), $user_id);

        if (0 === count($buttons)) {
            // if no buttons to display, exit
            return;
        }

        $member_buttons = '';
        foreach ($buttons as $name => $data) {
            $member_buttons .= '<a href="#"';


            if (isset($data['class']))
                $member_buttons .= ' class="' . $data['class'] . '"';
            if (isset($data['extra']))
                $member_buttons .= ' ' . $data['extra'];
            if (isset($data['click']))
                $member_buttons .= ' onclick="' . esc_js($data['click']) . '" ';

            $member_buttons .= '>';

            if (isset($data['label']))
                $member_buttons .= '<span>' . $data['label'] . '</span>';

            if (isset($data['loading']))
                $member_buttons .= ' <img class="ps-loading" src="' . PeepSo::get_asset('images/ajax-loader.gif') .'" alt="" style="display: none"></span>';

            $member_buttons .= '</a>' . PHP_EOL;
        }

        echo PeepSoTemplate::exec_template('members', 'member-buttons', array('member_buttons' => $member_buttons, 'user_id' => $user_id), TRUE);
    }

    /**
     * @param int $user_id The current member in the loop.
     */
    public static function member_buttons_extra($user_id)
    {
        if( $user_id == get_current_user_id() ) {
            return;
        }

        $PeepSoBlockUsers = new PeepSoBlockUsers();
        if($PeepSoBlockUsers->is_user_blocking(get_current_user_id(), $user_id)) {
            return;
        }

        $buttons = apply_filters('peepso_member_buttons_extra', array(), $user_id);

        if (0 === count($buttons)) {
            // if no buttons to display, exit
            return;
        }

        $member_buttons = '';
        foreach ($buttons as $name => $data) {
            $member_buttons .= '<a href="#"';


            if (isset($data['class']))
                $member_buttons .= ' class="' . $data['class'] . '"';
            if (isset($data['extra']))
                $member_buttons .= ' ' . $data['extra'];
            if (isset($data['click']))
                $member_buttons .= ' onclick="' . esc_js($data['click']) . '" ';

            $member_buttons .= '>';

            if (isset($data['label']))
                $member_buttons .= '<span>' . $data['label'] . '</span>';

            if (isset($data['loading']))
                $member_buttons .= ' <img class="ps-loading" src="' . PeepSo::get_asset('images/ajax-loader.gif') .'" alt="" style="display: none"></span>';

            $member_buttons .= '</a>' . PHP_EOL;
        }

        echo PeepSoTemplate::exec_template('members', 'member-buttons-extra', array('member_buttons' => $member_buttons, 'user_id' => $user_id), TRUE);
    }

    /**
     * Get member actions.
     *
     * @param int $user_id
     * @param bool $profile
     * @return array
     */
    public static function get_actions($user_id, $profile = FALSE)
    {
        ob_start();
        self::member_buttons($user_id);
        $primary = ob_get_clean();

        ob_start();
        self::member_buttons_extra($user_id);
        $secondary = ob_get_clean();

        ob_start();
        self::member_options($user_id, $profile);
        $dropdown = ob_get_clean();

        $PeepSoProfile = PeepSoProfile::get_instance();
        $PeepSoProfile->init($user_id);

        ob_start();
        $PeepSoProfile->profile_actions();
        $primary_profile = ob_get_clean();

        ob_start();
        $PeepSoProfile->profile_actions_extra();
        $secondary_profile = ob_get_clean();

        return array(
            'primary' => $primary,
            'secondary' => $secondary,
            'dropdown' => $dropdown,
            'primary_profile' => $primary_profile,
            'secondary_profile' => $secondary_profile,
        );
    }
}

// EOF
