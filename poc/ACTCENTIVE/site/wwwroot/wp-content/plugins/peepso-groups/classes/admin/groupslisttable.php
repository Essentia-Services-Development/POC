<?php

class PeepSoGroupsListTable extends PeepSoListTable
{
	/**
	 * Defines the query to be used, performs sorting, filtering and calling of bulk actions.
	 * @return void
	 */
	public function prepare_items()
	{
		global $wpdb;
		$input = new PeepSoInput();
		if (isset($_POST['action'])){
			$this->process_bulk_action();
		}

		$limit = 20;
		$offset = ($this->get_pagenum() - 1) * $limit;

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);

		$search = '';
		if (isset($_REQUEST['s'])) {
			$search = $_REQUEST['s'];
		}

		$show = 'all';
		if (isset($_REQUEST['show'])) {
			$show = $_REQUEST['show'];
		}

		$totalItems = PeepSoGroups::admin_count_groups($search, $show);

		// SQL safe, admin only
		$aGroups = PeepSoGroups::admin_get_groups($offset, $limit, $input->value('orderby', NULL, FALSE), $input->value('order', 'desc',array('desc','asc')), $search, $show);

		$this->set_pagination_args(array(
				'total_items' => $totalItems,
				'per_page' => $limit
			)
		);
		$this->items = $aGroups;
	}

	/**
	 * Return and define columns to be displayed on the List Groups table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_columns()
	{
		return array(
			'cb' 					=> '<input type="checkbox" />',
			'group' 				=> __('Group', 'groupso'),
			'description' 			=> __('Description', 'groupso'),
			'categories'			=> __('Categories', 'groupso'),
			'admins' 				=> __('Owner', 'groupso'),
			'status'				=> __('Status', 'groupso'),
			'members_count'			=> __('Members', 'groupso')
		);
	}

	/**
	 * Return and define columns that may be sorted on the List Groups table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_sortable_columns()
	{
		return array(
			'group' 				=> array('post_title', false),
			'description' 			=> array('post_content', false),
			'status' 				=> array('post_status', false),
			'members_count' 		=> array('members_count', false)
		);
	}

	/**
	 * Return default values to be used per column
	 * @param  array $item The post item.
	 * @param  string $column_name The column name, must be defined in get_columns().
	 * @return string The value to be displayed.
	 */
	public function column_default($item, $column_name)
	{
		//var_dump($item);
		return $item->$column_group;
	}

	/**
	 * Returns the HTML for the checkbox column.
	 * @param  array $item The current post item in the loop.
	 * @return string The checkbox cell's HTML.
	 */
	public function column_cb($item)
	{
		return sprintf('<input type="checkbox" name="groups[]" value="%d" />',
    		$item->id
    	);
	}

	/**
	 * Returns the HTML for the group description column.
	 * @param  array $item The current post item in the loop.
	 * @return string The name cell's HTML.
	 */
	public function column_description($item)
	{
		$group = new PeepSoGroup($item->id);

		return sprintf('<div class="description-text">%s</div><a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a>',
    		$item->description,
    		$group->get_url(),
    		__('Read more', 'groupso')
    	);
	}

	/**
	 * Returns the Group name, avatar and privacy.
	 * @param  array $item The current post item in the loop.
	 * @return string The Avatar HTML.
	 */
	public function column_group($group)
	{
		return sprintf('<a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a><a href="%s" target="_blank"><img src="%s" class="ps-avatar"></a> <span>'
			. __('Privacy', 'groupso') . ': <i class="'.$group->privacy['icon'].'"></i> <strong>'.$group->privacy['name'] . '</strong></span><br>Group id: <code>'.$group->get('id').' </code></b>',
			$group->get_url(),
			$group->get('name'),
			$group->get_url(),
			$group->get_avatar_url_orig()
    	);
	}

	/**
	 * Returns the Owner for the owner column.
	 * @param  array $item The current post item in the loop.
	 * @return string The Group owner.
	 */
	public function column_admins($item)
	{
		$group_users = new PeepSoGroupUsers($item->id);
		$list_owners = $group_users->get_owners();
		$owners = array();
		if(count($list_owners) > 0) {
			foreach($list_owners as $groupuser) {
				$owners[] = sprintf('<a href="%s" target="_blank">%s <i class="fa fa-external-link"></a>',
					$groupuser->get('profileurl'),
					$groupuser->get('fullname')
					);
			}
		}

		return implode(', ', $owners);
	}

	public function column_categories($group)
	{
		$categories = PeepSoGroupCategoriesGroups::get_categories_for_group($group->id);
		foreach($categories as $PeepSoCategory) {
			echo "{$PeepSoCategory->name}<br/>";
		}
	}

	/**
	 * Returns the Group Status for the status column.
	 * @param  array $item The current post item in the loop.
	 * @return string The Group status.
	 */
	public function column_status($item)
	{
		return ($item->published === TRUE) ? __('published', 'groupso') : __('unpublished', 'groupso');
	}

	/**
	 * Returns the HTML for the group name column.
	 * @param  array $item The current post item in the loop.
	 * @return string The name cell's HTML.
	 */
	public function column_members_count($item)
	{
		$group = new PeepSoGroup($item->id);

		return sprintf('<a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a>',
			$group->get_url() . 'members',
    		$item->members_count
    	);
	}

	/**
	 * Define bulk actions available
	 * @return array Associative array of bulk actions, keys are used in self::process_bulk_action().
	 */
	public function get_bulk_actions()
	{
		return array(
			'publish' 	=> __('Publish', 'groupso'),
            'unpublish' => __('Unpublish', 'groupso'),
            'delete' => __('Delete', 'groupso'),
		);
	}

	/**
	 * Performs bulk actions based on $this->current_action()
	 * @return void Redirects to the current page.
	 */
	public function process_bulk_action()
	{
		if ($this->current_action() && check_admin_referer('bulk-action', 'groups-nonce')) {
			$input = new PeepSoInput();
			$count = 0;

			// SQL safe, forced int
			$posts = array_map('intval', $input->value('groups', array(), FALSE));

			$post = array();
			if ('unpublish' === $this->current_action() || 'publish' === $this->current_action()) {
				$notif = new PeepSoNotifications();

				foreach ($posts as $id) {
					$the_post = get_post($id);

					$post['ID'] = intval($id);
					$post['post_status'] = $this->current_action();

					wp_update_post($post);

					$user_id = get_current_user_id();

                    $args = ['groupso'];

					if('publish' === $this->current_action()) {
						if($this->current_action() !== $the_post->post_status){

                            $i18n = __('published your group', 'groupso');
                            $message = 'published your group';

							$notif->add_notification_new($user_id, $the_post->post_author, $message, $args, 'groups_publish', PeepSoGroupsPlugin::MODULE_ID, $id);
						}
					} elseif('unpublish' === $this->current_action()) {
						if($this->current_action() !== $the_post->post_status){
                            $i18n = __('unpublished your group', 'groupso');
                            $message = 'unpublished your group';

							$notif->add_notification_new($user_id, $the_post->post_author, $message, $args, 'groups_unpublish', PeepSoGroupsPlugin::MODULE_ID, $id);
						}
					}
				}

				$message = __('updated', 'groupso');
			}

			if('delete' === $this->current_action()) {

			    global $wpdb;

                foreach ($posts as $id) {
                    // Delete group uploads
                    $PeepSoGroup = new PeepSoGroup($id);
                    $dir = $PeepSoGroup->get_image_dir();

                    require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
                    require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
                    $filesystem = new WP_Filesystem_Direct(array());
                    $filesystem->rmdir($dir, TRUE);

                    wp_delete_post($id);

                    $message = __('deleted', 'groupso');
                }
            }

			$count = count($posts);

			PeepSoAdmin::get_instance()->add_notice(
				sprintf('%1$d %2$s %3$s',
					$count,
					_n('group', 'groups', $count, 'groupso'),
					$message),
				'note');

			ob_start();
            PeepSoMaintenanceGroups::deletePostsForDeletedGroups();
            PeepSoMaintenanceGroups::deleteNotificationsForDeletedGroups();
            $debug = ob_get_clean();

			PeepSo::redirect("//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		}
	}

	/**
	 * Adds The navigation to the top of the table.
	 * @param  string $which The current position to display the HTML.
	 * @return void Echoes the content.
	 */
	public function extra_tablenav($which)
	{
		if ('top' === $which) {
			$nonce = wp_create_nonce('process-groups-nonce');

			$show = (isset($_REQUEST['show'])) ? $_REQUEST['show'] : 'all';

			$link_all = '<a href="' . admin_url('admin.php?page=peepso-manage&tab=groups&show=all&_wpnonce=' . $nonce) . '">
					' . __('All', 'groupso') . '</a>';

			$link_published = '<a href="' . admin_url('admin.php?page=peepso-manage&tab=groups&show=publish&_wpnonce=' . $nonce) . '">
					' . __('Published', 'groupso') . '</a>';

			$link_unpublished = '<a href="' . admin_url('admin.php?page=peepso-manage&tab=groups&show=unpublish&_wpnonce=' . $nonce). '">' . __('Unpublished', 'groupso') . '</a>';

			switch ($show) {
				case 'publish':
					$link_published = '<strong>' . $link_published . '</strong>';
					break;

				case 'unpublish':
					$link_unpublished = '<strong>' . $link_unpublished . '</strong>';
					break;

				default:
					$link_all = '<strong>' . $link_all . '</strong>';
					break;
			}

			echo '
			<div class="alignleft actions filteractions">
				' . __('Show', 'groupso') . ' : ' .
				$link_all . ' | ' .
				$link_published . ' | ' .
				$link_unpublished .
			'</div>';
		}
	}
}

// EOF
