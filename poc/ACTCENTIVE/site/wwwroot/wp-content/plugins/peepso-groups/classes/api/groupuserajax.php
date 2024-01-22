<?php

class PeepSoGroupUserAjax extends PeepSoGroupAjaxAbstract
{
	protected $_passive_user_id;// ID of the user being handled (eg invited, banned, kicked)
	protected $_passive_model;	// PeepSoGroupUser instance for the passive_user_id



	protected function __construct()
	{
		parent::__construct();

		if($this->_group_id > 0) {
			$this->_model= new PeepSoGroupUser($this->_group_id, $this->_user_id);
		}

		if($this->_request_method == 'post') {
			$this->_passive_user_id  = $this->_input->int('passive_user_id', 0);
		} else {
			$this->_passive_user_id  = $this->_input->int('passive_user_id', 0);
		}

		if($this->_passive_user_id > 0) {
			$this->_passive_model = new PeepSoGroupUser($this->_group_id, $this->_passive_user_id);
		}
	}

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        return array(
            'member_passive_actions',
        );
    }

	public function init($group_id)
	{
		$this->_group_id = $group_id;
		$this->_model = new PeepSoGroupUser($this->_group_id, $this->_user_id);
	}



    public function can_manage_users() {
        return $this->_model->can_manage_users();
    }

    public function can_pin_posts() {
        return $this->_model->can_pin_posts();
    }

	/**
	 * POST - join a group
	 */
	public function join(PeepSoAjaxResponse $resp)
	{
		if(!$this->_model->can('join')) {
			$resp->error(__('Unable to join this group', 'groupso'));
			return FALSE;
		}

		if($this->_model->is_pending_user) {
			do_action('peepso_action_group_user_invitation_accept', $this->_model);
		}

		$this->_model->member_join();
		$resp->success(1);
		$resp->set('member_actions', $this->_model->get_member_actions());

		$PeepSoGroupUsers = new PeepSoGroupUsers($this->_group_id);
		$resp->set('member_count', $PeepSoGroupUsers->update_members_count());

		do_action('peepso_action_group_user_join', $this->_group_id, get_current_user_id());
	}

	/**
	 * POST - send request to join
	 */
	public function join_request(PeepSoAjaxResponse $resp)
	{
		if(!$this->_model->can('join_request')) {
			$resp->error(__('Unable to join this group', 'groupso'));
			return FALSE;
		}

		$this->_model->member_join();
		$this->_model->member_modify('pending_admin');
		$resp->success(1);
		$resp->set('member_actions', $this->_model->get_member_actions());

		$PeepSoGroupUsers = new PeepSoGroupUsers($this->_group_id);
		$resp->set('pending_admin_member_count', $PeepSoGroupUsers->update_members_count('pending_admin'));

		do_action('peepso_action_group_user_join_request_send', $this->_group_id);
	}

	/**
	 * POST - cancel request to join
	 */
	public function cancel_request_to_join(PeepSoAjaxResponse $resp)
	{
		if(!$this->_model->can('join_request')) {
			$resp->error(__('Unable to cancel request to join this group', 'groupso'));
			return FALSE;
		}

		$this->_model->member_leave();
		$resp->success(1);
		$resp->set('member_actions', $this->_model->get_member_actions());

		$PeepSoGroupUsers = new PeepSoGroupUsers($this->_group_id);
		$resp->set('pending_admin_member_count', $PeepSoGroupUsers->update_members_count('pending_admin'));

		do_action('peepso_action_group_user_cancel_join_request', $this->_group_id, get_current_user_id());
	}

	/**
	 * POST - leave & reject invitation
	 */
	public function leave(PeepSoAjaxResponse $resp)
	{
		if(!$this->_model->can('leave')) {
			$resp->error(__('Unable to leave this group', 'groupso'));
			return FALSE;
		}

		$actions = array();

		if($this->_model->is_pending_user) {
			$actions[] = array(
				'action' 		=> 'block_invites',
				'label'			=> __('Block invites', 'groupso'),
				'confirm' 	=> __('Are you sure you want to block all future invites to this group?', 'groupso'),
			);
		}

		$this->_model->member_leave();
		$resp->success(1);

		do_action('peepso_action_group_user_delete', $this->_group_id, get_current_user_id());

		$actions = array_merge($this->_model->get_member_actions(), $actions);
		$resp->set('member_actions', $actions);

		$PeepSoGroupUsers = new PeepSoGroupUsers($this->_group_id);
		$resp->set('member_count', $PeepSoGroupUsers->update_members_count());
		$resp->set('pending_admin_member_count', $PeepSoGroupUsers->update_members_count('pending_admin'));
	}

	public function block_invites(PeepSoAjaxResponse $resp)
	{
		$this->_model->member_join();
		$this->_model->member_modify('block_invites');
		$resp->set('member_actions', $this->_model->get_member_actions());
		$resp->success(1);
	}

	/**
	 * GET - actions available for this user on this group
	 * @chainable
	 */
	public function member_actions(PeepSoAjaxResponse $resp = NULL)
	{
		$this->_model->context = $this->_input->value('context', NULL, array('cover'));

		$response = $this->_model->get('member_actions');

		if(NULL == $resp) {
			return($response);
		}

		$resp->set('member_actions', $response);
	}


	/**
	 *  GET - what can [user_id] do about [passive_user_id] for [group_id]
	 */
	public function member_passive_actions(PeepSoAjaxResponse $resp)
	{
		if(!$this->_model->can('manage_users')) {
			$resp->error(__('You are not allowed to manage members'));
			return(FALSE);
		}

		$passive_actions = $this->_passive_model->get_member_passive_actions($this->_model->get_manage_user_rights());

		$resp->success(1);
		$resp->set('member_passive_actions', $passive_actions);
	}

	/**
	 * POST - send an invite
	 * @passive
	 */
	public function passive_invite(PeepSoAjaxResponse $resp)
	{
		// @todo can('invite')
        $i18n = __('invited you to join a group', 'groupso');
        $message = 'invited you to join a group';
        $error = __('Unable to invite this user');

        // Configurable: admins might have the power to add users instead of inviting them
        if(PeepSo::is_admin() && 1 == PeepSo::get_option('groups_add_by_admin_directly', 0)) {

           if($success = $this->_passive_model->member_add()) {
               do_action('peepso_action_group_add', $this->_passive_model->group_id, $this->_passive_model->user_id);
           }

            $i18n = __('added you to a group', 'groupso');
            $message = 'added you to a group';

            $action = 'peepso_action_group_add';
            $error = __('Unable to add this user');
        } else {
            if($success = $this->_passive_model->member_invite()) {

				$PeepSoGroupUsers = new PeepSoGroupUsers($this->_group_id);
				$resp->set('member_count', $PeepSoGroupUsers->update_members_count());
				$resp->set('pending_user_member_count', $PeepSoGroupUsers->update_members_count('pending_user'));
                do_action('peepso_action_group_user_invitation_send', $this->_passive_model);
            }
        }

		if($success) {
			$PeepSoNotifications = new PeepSoNotifications();
            $args = ['groupso'];
			$PeepSoNotifications->add_notification_new(get_current_user_id(), $this->_passive_user_id, $message, $args, 'groups_user_invitation_send', PeepSoGroupsPlugin::MODULE_ID, $this->_group_id);


			$resp->success(1);
		} else {
			$resp->error($error, 'groupso');
		}
	}

	/**
	 * POST - administrative - accept, decline, kick, ban
	 * @passive
	 * @roles member, banned, delete
	 * @future member_moderator, member_manager, member_owner
	 */
	public function passive_modify(PeepSoAjaxResponse $resp)
	{
		// response defaults
		$action = NULL;
		$accepted_by 			= NULL;
		$hide					= FALSE;
		$display_role           = '';

		$reload = FALSE;
		$member_passive_actions = array();

		// role to be set + validation
		$role = $this->_input->value('role', 'NULL', FALSE); // SQL safe, validated below


        /* * * VALIDATION * * */

		if(!in_array($role, array('member', 'member_owner','member_manager', 'member_moderator', 'delete', 'banned'))) {
			$resp->error(__('Invalid role', 'groupso'));
			return( FALSE );
		}


		// access check
		if(!$this->_model->can('manage_user_'.$role)) {
			$resp->error(__('Insufficient permissions', 'groupso'));
			return(FALSE);
		}



		/* * * PREPARATION * * */

        // prepare ownership transfer
        if('member_owner' == $role) {
            $PeepSoGroup = new PeepSoGroup($this->_group_id);
            $old_owner_id = $PeepSoGroup->owner_id;
        }

		// prepare join request processing
		if($this->_passive_model->is_pending_admin) {
			$hide = TRUE;
			if('member' == $role) {
				$accepted_by = $this->_user_id;
				$action_user_join_request_accept = TRUE;
			}
		}

		// prepare delete & ban
		if(in_array($role, array('delete', 'banned'))) {
			$hide = TRUE;
            $action_user_delete = TRUE;
		}

		// hide user when unbanning
		if($this->_passive_model->is_banned && 'member' == $role) {
            $hide = TRUE;
        }

        /* * * WRITE DATA * * */


		$this->_passive_model->member_modify($role, NULL, $accepted_by);

        /* * * ADDITIONAL ACTIONS * * */

		// ownership transfer execution
        if('member_owner' == $role) {
		    // Switch the owner ID (post_author)
            $PeepSoGroup->update(array('owner_id' => $this->_passive_user_id));

            // Demote the previous Owner to Manager
            if($old_owner_id != $this->_passive_user_id) {
                $PeepSoGroupUserOldOwner = new PeepSoGroupUser($this->_group_id, $old_owner_id, $PeepSoGroup);
                $PeepSoGroupUserOldOwner->member_modify('member_manager');

                do_action('peepso_action_group_user_role_change_manager', $this->_group_id, $old_owner_id);
            }

            // Notify new owner
            do_action('peepso_action_group_user_role_change_owner', $this->_group_id, $this->_passive_user_id);

            $reload = TRUE;
        }

        // moderator
        if('member_moderator' == $role) {
            do_action('peepso_action_group_user_role_change_moderator', $this->_group_id, $this->_passive_user_id);
        }

        // member (demote & accept)
        if('member' == $role) {
		    if(isset($action_user_join_request_accept)) {
			    do_action('peepso_action_group_user_join_request_accept', $this->_group_id, $this->_passive_user_id);
		    } else{
                do_action('peepso_action_group_user_role_change_member', $this->_group_id, $this->_passive_user_id);
		    }
        }

        // delete
		if(isset($action_user_delete)) {
			do_action('peepso_action_group_user_delete', $this->_group_id, $this->_passive_user_id);
		}


		// don't call for passive_actions if removing the user from the view
		if(FALSE === $hide) {
			$member_passive_actions = $this->_passive_model->get_member_passive_actions($this->_model->get_manage_user_rights());
		}

		// update the role displayed on the list
        if(in_array($role, array('member', 'member_manager', 'member_moderator'))) {
            $display_role = __(str_replace(array('member_','member'), '', $role), 'groupso');
        }

		$resp->success(1);
        $resp->set('hide', $hide);
        $resp->set('reload', $reload);
        $resp->set('display_role', $display_role);
		$resp->set('member_passive_actions', $member_passive_actions);

		// recount user roles
		$PeepSoGroupUsers = new PeepSoGroupUsers($this->_group_id);
        $resp->set('pending_user_member_count', $PeepSoGroupUsers->update_members_count('pending_user'));
        $resp->set('pending_admin_member_count', $PeepSoGroupUsers->update_members_count('pending_admin'));
        $resp->set('banned_member_count', $PeepSoGroupUsers->update_members_count('banned'));
		$resp->set('member_count',				 $PeepSoGroupUsers->update_members_count());

		if($action) {
			do_action($action, $this->_group_id);
		}
	}
}