<?php

class PeepSoUserAutofriends extends PeepSoAjaxCallback
{
	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function sort(PeepSoAjaxResponse $resp)
	{
		if (FALSE === PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'friendso'));
			return;
		}

		$peepso_list_table = new PeepSoUserAutoFriendsListTable();
		$peepso_list_table->prepare_items();

		// assign to local variable first since extract() requires pass by reference
		// $_args = $peepso_list_table->_args;
		// $_pagination_args = $peepso_list_table->_pagination_args;
		// extract($_args);
		// extract($_pagination_args, EXTR_SKIP);

		ob_start();
		if (!empty( $_REQUEST['no_placeholder']))
			$peepso_list_table->display_rows();
		else
			$peepso_list_table->display_rows_or_placeholder();
		$rows = ob_get_clean();

		ob_start();
		$peepso_list_table->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$peepso_list_table->pagination('top');
		$pagination_top = ob_get_clean();

		ob_start();
		$peepso_list_table->pagination('bottom');
		$pagination_bottom = ob_get_clean();

		$pagination['top'] = $pagination_top;
		$pagination['bottom'] = $pagination_bottom;

		if (isset($total_items))
			$resp->set('total_items_i18n',
				sprintf(
					_n('1 item', '%s items', $total_items, 'friendso'),
					number_format_i18n($total_items)
				)
			);

		if (isset($total_pages)) {
			$resp->set('total_pages', $total_pages);
			$resp->set('total_pages_i18n', number_format_i18n($total_pages));
		}

		$resp->set('rows', $rows);
		$resp->set('pagination', $pagination);
		$resp->set('column_headers', $headers);
		$resp->success(TRUE);
	}

	/**
	 * AJAX callback - Befriend the selected user identified by $_POST['user_id']
	 * and sets the proper response.
	 *
	 * @param  PeepSoAjaxResponse $resp The response is_object
	 * @return void
	 */
	public function befriend(PeepSoAjaxResponse $resp)
	{
		if (FALSE === PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'friendso'));
			return;
		}

		// SQL injection safe
		if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'bulk-action')) {
			$resp->error(__('Could not verify nonce.', 'friendso'));
			$resp->success(FALSE);
		} else {
			$userfriends = new PeepSoUserAutoFriendsModel();

			$success = $userfriends->befriends($this->_input->int('user_id'));
			$resp->notice(__('The user is now friends with all other users.', 'friendso'));
			$resp->set('count', $success);
			$resp->success($success);
		}
	}

	/**
	 * AJAX callback - Remove the selected user identified by $_POST['user_id']
	 * and sets the proper response.
	 *
	 * @param  PeepSoAjaxResponse $resp The response is_object
	 * @return void
	 */
	public function remove(PeepSoAjaxResponse $resp)
	{
		if (FALSE === PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'friendso'));
			return;
		}

        // SQL injection safe
		if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'bulk-action')) {
			$resp->error(__('Could not verify nonce.', 'friendso'));
			$resp->success(FALSE);
		} else {
			$userfriends = new PeepSoUserAutoFriendsModel();

			$success = $userfriends->remove_user($this->_input->int('user_id'));
			$resp->notice(__('The user has been successfully removed.', 'friendso'));
			$resp->set('count', $success);
			$resp->success($success);
		}
	}

	/**
	 * AJAX callback - Search User
	 * and sets the proper response.
	 *
	 * @param  PeepSoAjaxResponse $resp The response is_object
	 * @return void
	 */
	public function search_user(PeepSoAjaxResponse $resp)
	{
		if (FALSE === PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'friendso'));
			return;
		}

		$userfriends = new PeepSoUserAutoFriendsModel();

		$users = $userfriends->search_user($this->_input->value('user_name', '', FALSE));
		$resp->set('users', $users);
		$resp->success(TRUE);
	}

	/**
	 * AJAX callback - Add User
	 * and sets the proper response.
	 *
	 * @param  PeepSoAjaxResponse $resp The response is_object
	 * @return void
	 */
	public function add_user(PeepSoAjaxResponse $resp)
	{
		if (FALSE === PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'friendso'));
			return;
		}

        // SQL injection safe
		if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce', '', FALSE), 'bulk-action')) {
			$resp->error(__('Could not verify nonce.', 'friendso'));
			$resp->success(FALSE);
		} else {
			$userfriends = new PeepSoUserAutoFriendsModel();

			$success = $userfriends->add_user($this->_input->int('user_id'));
			$resp->notice(__('The user has been successfully added.', 'friendso'));
			$resp->set('count', $success);
			$resp->success($success);
		}
	}
}

// EOF
