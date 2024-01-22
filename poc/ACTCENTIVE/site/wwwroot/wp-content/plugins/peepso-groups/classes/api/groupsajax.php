<?php

class PeepSoGroupsAjax extends PeepSoAjaxCallback
{
	/** GROUP SEARCH & LISTING **/

    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        $list_exception = array();
        $allow_guest_access = PeepSo::get_option('groups_allow_guest_access_to_groups_listing', 0);
        if($allow_guest_access) {
            array_push($list_exception, 'search');
        }

        return $list_exception;
    }

	/**
	 * GET
	 * Search for groups matching the query.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function search(PeepSoAjaxResponse $resp)
	{
	    $order_by_default = PeepSo::get_option('groups_default_sorting', 'id');
	    $order_default = PeepSo::get_option('groups_default_sorting_order', 'DESC');

        $order_by = $this->_input->value('order_by', $order_by_default, false); // SQL safe
        $order = strtoupper($this->_input->value('order', $order_default, array('asc','desc')));

        $writable_only = $this->_input->int('writable_only' ,0);

        $open_only = $this->_input->int('open_only' ,0);

        $search_mode = $this->_input->value('search_mode' ,'exact',['exact','any']);

		$page = $this->_input->int('page', 1);
		$query = stripslashes_deep($this->_input->value('query', '', false)); // SQL safe
		$user_id = $this->_input->int('user_id', 0);
		$category = $this->_input->int('category', 0);
		$limit = $this->_input->int('limit', 1);

		if(-1 == $this->_input->value('category',0, FALSE)) { // SQL safe, forced INT
		    $category = -1;
        } else {
		    $category = intval($category);
        }

		if (NULL !== $order_by && strlen($order_by)) {
			if ('ASC' !== $order && 'DESC' !== $order) {
				$order = 'ASC';
			}
		}

		$offset = ($page - 1) * $limit;

		$resp->set('page', $page);

		$PeepSoGroups = new PeepSoGroups();

		// Add "+ 1" to the limit value to detect if next page is available.
		$groups = $PeepSoGroups->get_groups($offset, $limit + 1, $order_by, $order, $query, $user_id, $category, $search_mode);

        if($page == 1) {
            (new PeepSo3_Search_Analytics())->store($query, 'groups');
        }

		if (count($groups) > 0 || $page > 1) {

			// Set next page flag and reset groups count according to the limit value.
			$has_next = false;
			if (count($groups) > $limit) {
				$has_next = true;
				$groups = array_slice($groups, 0, $limit);
			}

			$groups_response = array();

			foreach ($groups as $group) {

                $PeepSoGroupUser = new PeepSoGroupUser($group->get('id'), get_current_user_id());

                if (!$PeepSoGroupUser->can('access')) {
                    continue;
                }

                if ($writable_only && !$PeepSoGroupUser->can('post')) {
                    continue;
                }

                if ($open_only && !$group->is_open) {
                    continue;
                }

				$keys = $this->_input->value('keys', 'id', FALSE); // SQL safe, parsed

                if($PeepSoGroupUser->can('manage_users') && !stristr($keys,'pending_admin_members_count')) {
                    $keys.=',pending_admin_members_count';
                }

                if($PeepSoGroupUser->can('manage_users') && !stristr($keys,'pending_user_members_count')) {
                    $keys.=',pending_user_members_count';
                }

				$groups_response[] = PeepSoGroupAjaxAbstract::format_response($group, PeepSoGroupAjaxAbstract::parse_keys('group', $keys), $group->get('id'));
			}

			$resp->success(TRUE);
			$resp->set('groups', $groups_response);
			$resp->set('has_next', $has_next);
		} else {
			$resp->success(FALSE);

			if($user_id) {
                $message = (get_current_user_id() == $user_id) ? __('You don\'t belong to any groups yet', 'groupso') : sprintf(__('%s doesn\'t belong to any groups yet', 'groupso'), PeepSoUser::get_instance($user_id)->get_firstname());
                $resp->error(PeepSoTemplate::exec_template('profile', 'no-results-ajax', array('message' => $message), TRUE));
            } else {
                $message = __('No groups found', 'groupso');
                $resp->error(PeepSoTemplate::exec_template('general', 'no-results-ajax', array('message' => $message), TRUE));
            }
		}
	}

	public function move_post(PeepSoAjaxResponse $resp) {
	    if(PeepSo::is_admin()) {
            $post_id = $this->_input->int('post_id', 0);
            $group_id = $this->_input->int('group_id', 0);

            delete_post_meta($post_id, 'peepso_group_id');

            if (0 != $group_id) {
                update_post_meta($post_id, 'peepso_group_id', $group_id);

                $PeepSoGroupUser = new PeepSoGroupUser($group_id, $post->post_author);
                if (!$PeepSoGroupUser->is_member) {
                    $invite[] = $PeepSoGroupUser->user_id;
                }


                $resp->set('invite', $invite);

            }

            $resp->success(TRUE);
        }
    }
}
// EOF
