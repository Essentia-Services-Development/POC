<?php

/**
 * Class PeepSoGroupUser
 *
 * This class is used to define and modify the relationships between Group and User
 * We chose the name "PeepSoGroupUser" and NOT "PeepSoGroupMember", becasue not all users are members
 * Check/modify User access to the Group
 * Check/modify User priviledges in the Group
 * Check/modify User role/membership in the Group
 */
class PeepSoGroupUser
{
    public $user_id;
    public $group_id;

    public $user				= FALSE;
    public $group				= FALSE;

    public $role				= FALSE;
    public $role_l8n 			= FALSE;
    public $role_desc          = FALSE;
    public $joined_date			= FALSE;
    public $invited_by_id		= FALSE;
    public $accepted_by_id		= FALSE;

    // Flags
    public $context				= NULL; // "cover" for group cover
    public $is_member			= FALSE;
    public $is_moderator		= FALSE;
    public $is_manager			= FALSE;
    public $is_owner			= FALSE;

    public $is_banned			= FALSE;

    public $is_pending_user		= FALSE;
    public $is_pending_admin	= FALSE;

    public $block_invites	= FALSE;
    public $is_blocking_invites;

    private $_table;

    public function __construct($group_id, $user_id = NULL, $group_instance = NULL)
    {
        global $wpdb;
        $this->_table = $wpdb->prefix.PeepSoGroupUsers::TABLE;

        // default to logged in user
        if( NULL === $user_id ) {
            $user_id = get_current_user_id();
        }

        $this->group_id = intval($group_id);
        $this->user_id  = intval($user_id);

        if( NULL !== $group_instance) {
            $this->group = $group_instance;
        }

        if( $this->group_id > 0) {
            $this->_init();
        }

        return(FALSE);
    }

    /**
     * Set class flags based on the database values
     */
    private function _init()
    {
        // Reset all flags
        $this->role					= FALSE;
        $this->role_l8n 			= FALSE;

        $this->is_member			= FALSE;
        $this->is_moderator			= FALSE;
        $this->is_manager			= FALSE;
        $this->is_owner				= FALSE;

        $this->is_banned			= FALSE;
        $this->is_pending_user		= FALSE;
        $this->is_pending_admin		= FALSE;
        $this->is_blocking_invites 	= FALSE;

        // Calculate flags based on the database state
        global $wpdb;

        $query = "SELECT * FROM $this->_table WHERE `gm_group_id`=%d AND `gm_user_id`=%d LIMIT 1";
        $query = $wpdb->prepare($query, array($this->group_id, $this->user_id));

        $member = $wpdb->get_row($query);

        if (NULL !== $member) {
            $this->role 			= $member->gm_user_status;
            $this->joined_date 		= $member->gm_joined;
            $this->invited_by_id	= is_numeric($member->gm_invited_by_id)  ? $member->gm_invited_by_id  : FALSE;
            $this->accepted_by_id 	= is_numeric($member->gm_accepted_by_id) ? $member->gm_accepted_by_id : FALSE;

            if ('member' == substr($this->role, 0, 6)) {
                $this->is_member = TRUE;
                $this->role_l8n = __('member', 'groupso');
            }

            switch ($this->role) {
                case 'member_moderator':
                    $this->is_moderator = TRUE;
                    $this->role_l8n = __('moderator', 'groupso');
                    $this->role_desc = __('As a moderator you can edit or delete all posts and comments in this group', 'groupso');
                    break;
                case 'member_manager':
                    $this->is_manager = TRUE;
                    $this->role_l8n = __('manager', 'groupso');
                    $this->role_desc = __('As a manager you can manage the group members and edit or delete all posts and comments in this group', 'groupso');
                    break;
                case 'member_owner':
                    $this->is_owner = TRUE;
                    $this->role_l8n = __('owner', 'groupso');
                    $this->role_desc = __('As an owner you can manage all aspects of the group and its content', 'groupso');
                    break;
                case 'pending_user':
                    $this->is_pending_user	= TRUE;
                    break;
                case 'pending_admin':
                    $this->is_pending_admin	= TRUE;
                    break;
                case 'block_invites':
                    $this->block_invites	= TRUE;
                    break;
                case 'banned':
                    $this->is_banned        = TRUE;
                    break;
            }

            if(strlen($this->role_desc) && PeepSo::is_admin() && !$this->is_owner) {
                $this->role_desc .= '<br/><br/>' . __('As community administrator, you have the same control as group owner.','groupso');
            }
        }
    }

    /**
     * Singleton - if the Group instance is needed, try to load it only once
     * @return bool|PeepSoGroup
     */
    private function get_group_instance()
    {
        if(FALSE === $this->group) {
            $this->group = new PeepSoGroup($this->group_id);
        }

        return $this->group;
    }

    /**
     * Singleton - if the User instance is needed, try to load it only once
     * @return bool|PeepSoGroup
     */
    private function get_user_instance()
    {
        if(FALSE === $this->user) {
            $this->user= PeepSoUser::get_instance($this->user_id);
        }

        return $this->user;
    }

    /**
     * Get a property or use a getter
     * @param $prop
     * @return mixed
     */
    public function get($prop)
    {
        if(property_exists($this, $prop)) {
            return $this->$prop;
        }

        $method = "get_$prop";
        if(method_exists($this, $method)) {
            return $this->$method();
        }

        $this->get_user_instance();

        if(method_exists($this->user, $method)) {
            return $this->user->$method();
        }

        trigger_error("Unknown property/method $prop/$method");
    }


    /**
     * Create a group membership record
     */
    public function member_join()
    {
        global $wpdb;

        $query = "SELECT * FROM $this->_table WHERE `gm_group_id`=%d AND `gm_user_id`=%d";
        $query = $wpdb->prepare($query, array($this->group_id, $this->user_id));

        $member = $wpdb->get_row($query);

        // @todo in the future initial role will depend on grouptype/member flow (eg admin accept)
        $role = 'member';

        // Creating a fresh record in the database
        if( NULL == $member) {

            $data = array(
                'gm_user_id'	=> $this->user_id,
                'gm_group_id'	=> $this->group_id,
                'gm_user_status'=> $role,
            );

            // write to DB
            $success = $wpdb->insert($this->_table, $data);

            // recaulculate inner state
            $this->_init();

            // recalculate group members
            $PeepSoGroupUsers = new PeepSoGroupUsers($this->group_id);
            $PeepSoGroupUsers->update_members_count();

            // $success (int) success (FALSE) failure
            return((FALSE === $success) ? FALSE : TRUE);
        }

        // Modifying an existing record (ie accepting an invitation)
        return $this->member_modify($role);
    }

    /**
     * Delete a group membership record
     */
    public function member_leave()
    {
        global $wpdb;

        $where = array(
            'gm_user_id'	=> $this->user_id,
            'gm_group_id'	=> $this->group_id,
        );

        $success = $wpdb->delete($this->_table, $where);

        // recaulculate inner state
        $this->_init();

        // recalculate group members
        $PeepSoGroupUsers = new PeepSoGroupUsers($this->group_id);
        $PeepSoGroupUsers->update_members_count();

        // $success (int) success (FALSE) failure
        return((FALSE === $success) ? FALSE : TRUE);
    }

    /**
     * Create invitation record if possible
     * @return bool
     */
    public function member_invite()
    {
        if($this->can('be_invited')) {
            $this->member_join();
            $this->member_modify('pending_user', get_current_user_id());
            return(TRUE);
        }

        return(FALSE);
    }

    /**
     * Create member record (forced by site/community admin)
     * @return bool
     */
    public function member_add()
    {
        if(PeepSo::is_admin() && 1 == PeepSo::get_option('groups_add_by_admin_directly', 0)) {
            $this->member_join();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Modify a group membership record (change role)
     */
    public function member_modify( $role, $invited_by = NULL, $accepted_by = NULL )
    {
        if('delete' == $role) {
            return($this->member_leave() );
        }

        global $wpdb;
        $data = array(
            'gm_user_status'=> $role,
        );

        if(NULL != $invited_by) {
            $data['gm_invited_by_id'] = $invited_by;
        }

        if(NULL != $accepted_by) {
            $data['gm_invited_by_id'] = $accepted_by;
        }

        $where = array(
            'gm_user_id'	=> $this->user_id,
            'gm_group_id'	=> $this->group_id,
        );

        // write to database
        $success = $wpdb->update($this->_table, $data, $where);

        // recaulculate inner state
        $this->_init();

        // recalculate group members
        $PeepSoGroupUsers = new PeepSoGroupUsers($this->group_id);
        $PeepSoGroupUsers->update_members_count();

        // $success (int) success (FALSE) failure
        return((FALSE === $success) ? FALSE : TRUE);
    }

    /** GETTERS **/

    /**
     * Utility - returns available actions
     */
    public function get_actions()
    {
        return array("action"=>"test1");
    }

    /**
     * Utility - returns available membership actions
     */
    public function get_member_actions()
    {
        $actions = array();
        $this->get_group_instance();

        // invite button
        if('cover' == $this->context) {

            if($this->group->is_invitable || PeepSo::is_admin() || $this->is_owner || $this->is_manager) {

                $label = __('Invite', 'groupso');
                $force_add = FALSE;
                if (PeepSo::is_admin() && 1 == PeepSo::get_option('groups_add_by_admin_directly', 0)) {
                    $force_add = TRUE;
                    $label = __('Add users', 'groupso');
                }

                if ($force_add || $this->can('invite')) {
                    $actions[] = array(
                        'action' => 'groupusersajax.search_to_invite',
                        'label' => $label,
                    );
                }

            }

        }

        if($this->is_pending_user) {
            // Invited
            // PeepSoGroupAjax::join
            $actions[] = array(
                'action' 		=> 'join',
                'label'			=> __('Accept invite', 'groupso'),
            );

            // PeepSoGroupAjax::leave
            $actions[] = array(
                'action' 		=> 'leave',
                'label'			=> __('Reject invite', 'groupso'),
            );
        } elseif ($this->is_pending_admin) {
            // Awaiting approval
            // NULL
            $actions[] = array(
                'action' 		=> NULL,
                'label'			=> __('Pending approval', 'groupso'),
                'message'		=> __('You need to be approved', 'groupso'),
            );

            // Cancel Join Request
            $actions[] = array(
                'action'        => 'cancel_request_to_join',
                'label'         => __('Cancel Request To Join', 'groupso'),
            );
        } elseif ($this->can('join')) {
            // Can join
            // PeepSoGroupAjax::join
            $actions[] = array(
                'action' 		=> 'join',
                'label'			=> __('Join', 'groupso'),
            );
        } elseif($this->can('join_request')) {
            // Can't join, but can request
            // PeepSoGroupsAjax::join_request
            $actions[] = array(
                'action' 		=> 'join_request',
                'label'			=> __('Request To Join', 'groupso'),
            );
        }

        // existing member
        if($this->is_member) {

            if ($this->can('leave')) {
                // PeepSoGroupAjax::leave

                $confirm = __('Are you sure you want to leave this group?', 'groupso');

                if($this->group->is_closed && !$this->group->is_auto_accept_join_request) {
                    $confirm .= " " . __('To join again you will have to be approved or invited.', 'groupso');
                }

                if($this->group->is_secret) {
                    $confirm .= " " . __('To join again a member will have to be invited.', 'groupso');
                }

                $child_actions = array(
                    0 => array(
                        'action'    => 'groupuserajax.leave',
                        'label'     => __('Leave', 'groupso'),
                        'confirm' 	=> $confirm,
                        'icon'      => 'ps-icon-exit',

                    ),
                );

                if($this->is_moderator || $this->is_manager) {
                    $child_actions[] = array(
                        'action'    => NULL,
                        'label'     => sprintf(__('You are a group %s', 'groupso'), $this->role_l8n),
                        'icon'      => 'ps-icon-info-circled',
                        'desc'      => $this->role_desc,
                    );
                }

                $actions[] = array(
                    'action' 	=> $child_actions,
                    'label' 	=> __($this->role_l8n, 'groupso'),
                    'class'		=> 'ps-js-btn-membership',
                );


            } else {
                // Some users can never leave a group (eg owners)
                $actions[] = array(
                    'action' 	=> array(
                        0 => array(
                            'action'    => NULL,
                            'label'     => sprintf(__('You are the group %s', 'groupso'), $this->role_l8n),
                            'icon'      => 'ps-icon-info-circled',
                            'desc'      => $this->role_desc . '<br/><br/>' . __('You can\'t leave this group.', 'groupso') . ' '. __('To be able to leave, you need to transfer ownership to another user first.','groupso'),
                        ),
                    ),
                    'label' 	=> __($this->role_l8n, 'groupso'),
                    'class'		=> 'ps-js-btn-membership',
                );
            }
        }

        $actions = apply_filters('peepso_group_member_actions', $actions, $this->group_id);
        return $actions;
    }

    /**
     * Utility - returns actions that can be performed by others
     */
    public function get_member_passive_actions( $permissions = array('manage_user_member') )
    {
        $actions = array();
        $user_firstname = $this->get_user_instance()->get_firstname();

        if(!in_array('manage_user_member', $permissions)) {
            return $actions;
        }

        // existing member - kick, later: ban, promote, degrade
        if($this->is_member) {

            $child_actions = array();
            $this->get_group_instance();

            // Can't do anything to a Group Owner
            if(!$this->is_owner) {

                // Turn into Owner
                if( in_array('manage_user_member_owner', $permissions) ) {

                    if ($this->user_id == get_current_user_id()) {
                        $user_firstname = __('yourself', 'groupso');
                    }

                    $confirm = sprintf(__('Are you sure you want to make %s a new Group Owner? There can be only one owner in the group.', 'groupso'), $user_firstname);
                    $child_actions[] = array(
                        'action' => 'groupuserajax.passive_modify',
                        'label' => __('Transfer ownership', 'groupso'),
                        'confirm' => $confirm,
                        'args' => array('role' => 'member_owner'),
                    );
                }

                // Turn into Manager
                if( in_array('manage_user_member_manager', $permissions) ) {
                    if (!$this->is_manager) {
                        $confirm = sprintf(__('Are you sure you want to make %s a Group Manager?', 'groupso'), $user_firstname);
                        $child_actions[] = array(
                            'action' => 'groupuserajax.passive_modify',
                            'label' => __('Turn into Manager', 'groupso'),
                            'confirm' => $confirm,
                            'args' => array('role' => 'member_manager'),
                        );
                    }
                }

                // Turn into Moderator
                if( in_array('manage_user_member_moderator', $permissions) ) {
                    if (!$this->is_moderator) {
                        $confirm = sprintf(__('Are you sure you want to make %s a Group Moderator?', 'groupso'), $user_firstname);
                        $child_actions[] = array(
                            'action' => 'groupuserajax.passive_modify',
                            'label' => __('Turn into Moderator', 'groupso'),
                            'confirm' => $confirm,
                            'args' => array('role' => 'member_moderator'),
                        );
                    }
                }

                // Turn into Member
                if( in_array('manage_user_member', $permissions) ) {
                    if ($this->is_manager || $this->is_moderator) {
                        $confirm = sprintf(__('Are you sure you want to make %s a regular Group Member?', 'groupso'), $user_firstname);
                        $child_actions[] = array(
                            'action' => 'groupuserajax.passive_modify',
                            'label' => __('Turn into regular member', 'groupso'),
                            'confirm' => $confirm,
                            'args' => array('role' => 'member'),
                        );
                    }
                }

                // Kick & Ban

                if ($this->can('leave')) {

                    // Kick
                    if( in_array('manage_user_delete', $permissions) ) {

                        $confirm = __('Are you sure you want to remove this user?', 'groupso');

                        if ($this->group->is_closed || $this->group->is_secret) {
                            $confirm .= " " . __('To join again the user will have to be invited and/or approved.', 'groupso');
                        }

                        $label = __('Remove', 'groupso');

                        if ($this->user_id == get_current_user_id()) {
                            $label = __('Leave', 'groupso');

                            $confirm = __('Are you sure you want to leave this group?', 'groupso');

                            if ($this->group->is_closed || $this->group->is_secret) {
                                $confirm .= " " . __('To join again you will have to be approved or invited.', 'groupso');
                            }
                        }

                        $child_actions[] = array(
                            'action' => 'groupuserajax.passive_modify',
                            'label' => $label,
                            'confirm' => $confirm,
                            'args' => array('role' => 'delete'),
                        );
                    }

                    // Ban
                    if( in_array('manage_user_banned', $permissions) ) {

                        if($this->can('be_banned')) {

                            $confirm = __('Are you sure you want to ban this user?', 'groupso');
                            $confirm .= " " . __('This user will be unable to join or be invited.', 'groupso');

                            $child_actions[] = array(
                                'action' => 'groupuserajax.passive_modify',
                                'label' => __('Ban', 'groupso'),
                                'confirm' => $confirm,
                                'args' => array('role' => 'banned'),
                            );

                        }

                    }
                }

            }

            // Attach the cog dropdown
            if(count($child_actions)) {
                $actions[] = array(
                    'action' => $child_actions,
                    'class' => 'gcis gci-cog',
                );
            }
        }

        // banned
        if( $this->is_banned && in_array('manage_user_banned', $permissions) )  {
            $confirm = sprintf(__('Are you sure you want to unban %s and restore as a regular Group Member?', 'groupso'), $this->get_user_instance()->get_firstname());
            $actions[] = array(
                'action' => 'groupuserajax.passive_modify',
                'label' => __('Unban', 'groupso'),
                'confirm' => $confirm,
                'args' => array('role' => 'member'),
            );
        }

        // pending invite
        if($this->is_pending_user && in_array('manage_user_member', $permissions) ) {
            $actions[] = array(
                'action'    => 'groupuserajax.passive_modify',
                'label'     => __('Cancel Invitation', 'groupso'),
                'args'      => array('role'=>'delete'),
            );
        }

        // requested to join
        if($this->is_pending_admin && in_array('manage_user_member', $permissions) ) {
            $actions[] = array(
                'action' 	=> 'groupuserajax.passive_modify',
                'label' 	=> __('Approve', 'groupso'),
                'args'		=> array('role'=>'member'),
            );

            $actions[] = array(
                'action' 	=> 'groupuserajax.passive_modify',
                'label' 	=> __('Reject', 'groupso'),
                'args'		=> array('role'=>'delete'),
            );
        }

        return $actions;
    }

    public function get_role()
    {
        return $this->role;
    }

    public static function can_create($user_id = NULL)
    {
        if(!get_current_user_id()) { return FALSE; }

        $can = (PeepSo::is_admin() || (1==PeepSo::get_option('groups_creation_enabled', 1)));

        return apply_filters('peepso_permissions_groups_create', $can);
    }

    /** ACCESS CONTROL  */
    public function can( $action, $args = NULL )
    {
        $allow_guest_access = PeepSo::get_option('groups_allow_guest_access_to_groups_listing', 0);
        if(!get_current_user_id() && (!$allow_guest_access)) { return FALSE; }

        $method = "can_$action";

        // Don't attempt to access a method that doesn't exist
        if(!method_exists($this, $method)) {
            trigger_error("Unknown method " . __CLASS__ ."::$method()", E_USER_NOTICE);
            return(FALSE);
        }

        if( NULL === $args ) {
            $can = $this->$method();
            return(apply_filters('peepso_permissions_groups_'.$action, $can));
        }

        $can = $this->$method($args);
        return(apply_filters('peepso_permissions_groups_'.$action, $can));
    }

    /**
     * Ability to see the group
     * @return bool
     */
    private function can_access()
    {
        // WP Admin
        if( PeepSo::is_admin($this->user_id) ){	return(TRUE);	}

        $group = $this->get_group_instance();

        // If group is not published, only owners & managers
        if(!$group->published) {
            if( $this->is_owner ) 			{	return(TRUE); 	}
            if( $this->is_manager ) 			{	return(TRUE); 	}

            return( FALSE );
        }

        // If group is open, guest can be access it
        if ($group->is_open) {
            return (TRUE);
        }

        if($group->is_secret) {
            if( $this->is_member )        { return(TRUE);     }
            if( $this->is_pending_user )  { return(TRUE);     }

            return( FALSE );
        }

        if($this->is_banned) {
            return( FALSE );
        }

        // allow group listing
        if($group->published && (!get_current_user_id())) {
            $allow_guest_access = PeepSo::get_option('groups_allow_guest_access_to_groups_listing', 0);
            if($allow_guest_access) {
                return(TRUE);
            } else {
                return(FALSE);
            }
        }

        return(TRUE);
    }

    /**
     * Ability to access a particular group segment
     * Does NOT limit the menu visibility
     * @return bool
     */
    private function can_access_segment($segment)
    {
        $group = $this->get_group_instance();

        if( $this->group->is_open)                      {   return(TRUE);   }

        if( PeepSo::is_admin($this->user_id) )          {	return(TRUE);	}

        if('settings' == $segment && !$this->is_owner)  {   return(FALSE);  }

        if( $this->is_member ) 				            { 	return(TRUE); 	}

        if($this->group->is_closed || $this->group->is_secret) {
            if("" != $segment) 		{	return(FALSE);	}
        }

        return(TRUE);
    }

    /**
     * Ability to join
     * @return bool
     */
    private function can_join()
    {
        if( $this->is_member ) 				    { 	return(FALSE); 	}
        if( $this->is_banned ) 				    { 	return(FALSE); 	}
        if( PeepSo::is_admin($this->user_id) )  { 	return(TRUE); 	}
        if( $this->is_pending_user )		    { 	return(TRUE);	}

        $this->get_group_instance();
        if( $this->group->is_auto_accept_join_request )		    { 	return(TRUE);	}
        if ( ! $this->group->is_joinable )      {   return(FALSE);  }
        if ( $this->group->is_closed ) 		    {	return(FALSE);	}
        if ( $this->group->is_secret ) 		    {	return(FALSE);	}


        return(TRUE);
    }

    /**
     * Ability to send a membership request
     * @return bool
     */
    private function can_join_request()
    {
        if( PeepSo::is_admin($this->user_id) )  { 	return(FALSE); 	}		// super admins join all groups instantly
        if( $this->is_member ) 				    { 	return(FALSE); 	}
        if( $this->is_banned ) 				    { 	return(FALSE); 	}

        $this->get_group_instance();
        if ( $this->group->is_joinable && $this->group->is_closed ) 	{	return(TRUE);	}		// open and secret can't be requested

        return FALSE;
    }

    /**
     * Ability to invite users
     * @return bool
     */
    private function can_invite()
    {
        if( PeepSo::is_admin($this->user_id) )  { 	return(TRUE); 	}
        if(!$this->is_member) 				    {	return(FALSE);	}
        if( $this->is_owner ) 				    { 	return(TRUE); 	}
        if( $this->is_manager ) 			    { 	return(TRUE); 	}

        $this->get_group_instance();
        if($this->group->is_invitable) 		    {	return(TRUE);	}

        return(FALSE);
    }

    /**
     * Ability to be invited
     * @return bool
     */
    private function can_be_invited()
    {
        if($this->is_member) 				{ 	return(FALSE); 	}
        if($this->is_banned) 				{ 	return(FALSE); 	}
        if($this->is_pending_user) 			{ 	return(FALSE); 	}
        if($this->block_invites) 			{ 	return(FALSE); 	}

        return(TRUE);
    }

    private function can_be_banned() {
        if( PeepSo::is_admin($this->user_id) )      {	return(FALSE);	}
        if($this->is_owner) 				        { 	return(FALSE); 	}
        if($this->user_id == get_current_user_id()) {   return(FALSE);  }

        return(TRUE);
    }

    private function can_leave()
    {
        if( $this->is_owner ) 				{ 	return(FALSE); 	}
        if( $this->is_pending_user ) 		{	return(TRUE);	}
        if( !$this->is_member ) 			{ 	return(FALSE);	}

        return(TRUE);
    }



    /**
     * Ability to create new posts
     * @return bool
     */
    private function can_post()
    {
        $group = $this->get_group_instance();

        if( !$group->published ) 			                                    {	return(FALSE);	}
        if($this->is_owner)                                                     {   return TRUE;    }
        if($this->is_manager)                                                   {   return TRUE;    }
        if( $group->is_readonly && !$this->is_member)                           {	return(FALSE);	}
        if( $group->is_readonly && !PeepSo::is_admin())                         {	return(FALSE);	}
        if( $this->is_member ) 				                                    {	return(TRUE);	}

        return(FALSE);
    }

    /**
     * Ability to create new likes/comments
     * @return bool
     */
    private function can_post_interact()
    {
        $group = $this->get_group_instance();

        if( !$group->published ) 			                                    {	return(FALSE);	}
        if( PeepSo::is_admin() )                                                {   return TRUE;    }
        if($this->is_owner)                                                     {   return TRUE;    }
        if($this->is_manager)                                                   {   return TRUE;    }
        if( $group->is_interactable )                                           {   return(FALSE);  }
        if( $this->is_member ) 				                                    {	return(TRUE);	}

        return(FALSE);
    }

    private function can_post_comments_non_members()
    {
        return $this->can_post_interact_non_members('comments');
    }

    private function can_post_likes_non_members()
    {
        return $this->can_post_interact_non_members('likes');
    }

    private function can_post_interact_non_members($action)
    {
        $group = $this->get_group_instance();

        if( !$group->published ) 			                                    {	return(FALSE);	}
        if( PeepSo::is_admin() )                                                {   return TRUE;    }
        if( $this->is_owner )                                                   {   return TRUE;    }
        if( $this->is_manager )                                                 {   return TRUE;    }
        if( $group->is_interactable )                                           {   return(FALSE);  }
        if( $this->is_member )                                                  {	return TRUE;	}
        if( !$group->is_open )                                                  {	return FALSE;	}

        switch ($group->is_allowed_non_member_actions) {
            case 1:
                if ($action == 'likes') {
                    return TRUE;
                }
                break;
            case 2:
                if ($action == 'comments') {
                    return TRUE;
                }
                break;
            case 3:
                return TRUE;
                break;
            
            default:
                return FALSE;
                break;
        }

        return(FALSE);
    }

    /**
     * Ability to manage content (post & comment deletion)
     * @return bool
     */
    private function can_manage_content()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    { 	return(TRUE);	}
        if( $this->is_manager ) 				{ 	return(TRUE);	}
        if( $this->is_moderator ) 				{ 	return(TRUE);	}

        return FALSE;
    }

    /**
     * Ability to manage content (post & comment edition)
     * @return bool
     */
    private function can_edit_content()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}

        if( $this->is_owner         && PeepSo::get_option('groups_post_edits_owner', 1)) 				        { 	return(TRUE);	}
        if( $this->is_manager       && PeepSo::get_option('groups_post_edits_manager', 1)) 				    { 	return(TRUE);	}
        if( $this->is_moderator     && PeepSo::get_option('groups_post_edits_moderator', 1)) 				    { 	return(TRUE);	}

        return FALSE;
    }

    /**
     * Ability to manage content (post & comment edition)
     * @return bool
     */
    private function can_edit_file()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}

        if( $this->is_owner         && PeepSo::get_option('groups_file_edits_owner', 1)) 				        { 	return(TRUE);	}
        if( $this->is_manager       && PeepSo::get_option('groups_file_edits_manager', 1)) 				    { 	return(TRUE);	}
        if( $this->is_moderator     && PeepSo::get_option('groups_file_edits_moderator', 1)) 				    { 	return(TRUE);	}

        return FALSE;
    }

    /**
     * Ability to manage group settings
     * @return bool
     */
    private function can_manage_group()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return TRUE;	}

        return FALSE;
    }


    /**
     * ROLE CHANGE: Ability to accept members and run other membership tasks
     * @return bool
     */
    public function can_manage_users()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return(TRUE);	}
        if( $this->is_manager ) 			    {	return(TRUE);	}

        return(FALSE);
    }

    public function can_view_users()
    {
        $global = PeepSo::get_option_new('groups_members_tab');
        $override = PeepSo::get_option_new('groups_members_tab_override');

        if($this->can_manage_users())           return TRUE;

        if(!$override)                          return $global;


        $this->get_group_instance();
                                                return $this->group->members_tab;
    }

    public function can_pin_posts()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if(PeepSo::get_option_new('groups_pin_allow_managers')) {
            if ($this->is_owner) {
                return (TRUE);
            }
            if ($this->is_manager) {
                return (TRUE);
            }
        }

        return(FALSE);
    }

    /**
     * ROLE CHANGE: member
     * @return bool
     */
    private function can_manage_user_member()
    {
        return $this->can('manage_users');
    }

    /**
     * ROLE CHANGE: ban
     * @return bool
     */
    private function can_manage_user_banned()
    {
        return $this->can('manage_users');
    }

    /**
     * ROLE CHANGE: kick
     * @return bool
     */
    private function can_manage_user_delete()
    {
        return $this->can('manage_users');
    }


    /**
     * ROLE CHANGE: moderator
     * @return bool
     */
    private function can_manage_user_member_moderator()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return(TRUE);	}
        if( $this->is_manager ) 			    {	return(TRUE);	}

        return FALSE;
    }

    /**
     * ROLE CHANGE: manager
     * @return bool
     */
    private function can_manage_user_member_manager()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return(TRUE);	}

        return FALSE;
    }

    /**
     * ROLE CHANGE: owner
     * @return bool
     */
    private function can_manage_user_member_owner()
    {
        if( PeepSo::is_admin($this->user_id) )  {	return(TRUE);	}
        if( $this->is_owner ) 				    {	return(TRUE);	}

        return FALSE;
    }

    public function get_manage_user_rights() {
        $rights = array();

        if($this->can_manage_user_member()) {
            $rights[]='manage_user_member';
        }

        if($this->can_manage_user_member_moderator()) {
            $rights[]='manage_user_member_moderator';
        }

        if($this->can_manage_user_member_manager()) {
            $rights[]='manage_user_member_manager';
        }

        if($this->can_manage_user_member_owner()) {
            $rights[]='manage_user_member_owner';
        }

        if($this->can_manage_user_banned()) {
            $rights[]='manage_user_banned';
        }

        if($this->can_manage_user_delete()) {
            $rights[]='manage_user_delete';
        }

        return $rights;
    }
}

// EOF
