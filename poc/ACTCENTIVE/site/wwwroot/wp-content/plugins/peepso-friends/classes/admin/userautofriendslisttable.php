<?php

if (!function_exists('convert_to_screen')) {
	// Including a few files to work with ajax, errors are thrown
	require_once(ABSPATH . 'wp-admin/includes/template.php');
}

if (!function_exists('add_screen_option')) {
	require_once(ABSPATH . 'wp-admin/includes/screen.php');
}

class PeepSoUserAutoFriendsListTable extends PeepSoListTable 
{
	/**
	 * Set options for WP_List_Table.
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'singular' => 'report',
				'plural' => 'reports',
				'ajax' => true,
				'screen' => 'interval-list' 
			));
	}

	/**
	 * Defines the query to be used, performs sorting, filtering and calling of bulk actions.
	 * @return void
	 */
	public function prepare_items()
	{
		global $wpdb;

		$input = new PeepSoInput();

		add_filter('peepso_user_autofriends_columns', array(&$this, 'prepare_columns'));
		add_filter('peepso_user_autofriends_column_data', array(&$this, 'get_column_data'), 10, 2);

		if ($input->exists('action')) {
			$this->process_bulk_action();
		}

		$limit = 20;
		$offset = ($this->get_pagenum() - 1) * $limit;

		$this->_column_headers = array(
			$this->get_columns(),
			array('rep_external_id', 'rep_module_id'),
			$this->get_sortable_columns()
		);

		$users = new PeepSoUserAutoFriendsModel();
		$orderby = '';
		if (isset($_GET['orderby']) && array_key_exists($_GET['orderby'], $this->get_sortable_columns())) {
			$orderby = $_GET['orderby'];

//			if (isset($_GET['order']))
//				$order = strtoupper($_GET['order']);
//			$order = ($order === 'ASC') ? 'ASC' : 'DESC';
		} else {
			$orderby = 'af_user_id';
		}

		$order = 'ASC';
		if (isset($_GET['order']))
			$order = strtoupper($_GET['order']);
		$order = ('ASC' === $order) ? 'ASC' : 'DESC';

		$items = $users->get_users($orderby, $order, $offset, $limit);

		$this->set_pagination_args(array(
				'total_items' => $users->get_num_reported_items(),
				'per_page' => $limit
			));
		$this->items = $items;
	}

	/**
	 * Return and define columns to be displayed on the Report table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_columns()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'fullname' => __('Name', 'friendso'),
			'friends' => __('Friends', 'friendso'),
			'actions' => __('Actions', 'friendso')
		);

		return (apply_filters('peepso_user_autofriends_columns', $columns));
	}

	public function prepare_columns($columns)
	{
		return ($columns);
	}


	/**
	 * Return and define columns that may be sorted on the Report table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_sortable_columns()
	{
		return (array(
			'af_id' => array('af_id', true),
		));
	}

	/**
	 * Return default values to be used per column
	 * @param  array $item The post item.
	 * @param  string $column_name The column name, must be defined in get_columns().
	 * @return string The value to be displayed.
	 */
	public function column_default($item, $column_name)
	{
		return apply_filters('peepso_user_autofriends_column_data', $item, $column_name);
	}

	/**
	 * Get the data from the $item based on the given $column_name
	 * @param array $item An array of single report row/item
	 * @param string $column_name The field or column name
	 * @return string
	 */
	public function get_column_data($item, $column_name)
	{
		switch ($column_name)
		{
			case 'fullname':
				$user = $this->get_user($item['af_user_id']);
				if(strpos($user->get_fullname(), '(')) {
					return ('<a href="' . $user->get_profileurl() . '">' . $user->get_fullname() . ' <i class="fa fa-external-link"></i></a>');
				} else {
					return ('<a href="' . $user->get_profileurl() . '">' . $user->get_fullname() . ' (' . $user->get_username() . ') <i class="fa fa-external-link"></i></a>');
				}
				break;


			case 'friends':
				return PeepSoFriendsModel::get_instance()->get_num_friends($item['af_user_id']);
				break;


		}
		return ($item[$column_name]);
	}

	/**
	 * Generate row actions div
	 * @param array $item An array of single report row/item
	 * @return array $actions The list of actions
	 */
	public function column_actions($item)
	{
		$actions = array(
            'befriend'   => '<a href="#" onclick="list.befriend(' . $item['af_user_id'] . '); return false;">
            	<input type="button" value="' . __('Befriend All Users', 'friendso') . '" ' .
				' title="' . __('Befriend All users.', 'friendso') . '" class="button action"/></a>',

			'remove'   => '<a href="#" onclick="list.remove(' . $item['af_user_id'] . '); return false;">
            	<input type="button" value="' . __('Remove', 'friendso') . '" ' .
				' title="' . __('Remove from list autofriends creation.', 'friendso') . '" class="button action"/></a>',
        );

  		return ($this->row_actions($actions, true));
	}

	/**
	 * Returns the HTML for the checkbox column.
	 * @param  array $item The current post item in the loop.
	 * @return string The checkbox cell's HTML.
	 */
	public function column_cb($item)
	{
		return (sprintf('<input type="checkbox" name="users[]" value="%d" />',
    		$item['af_user_id']));
	}

	/**
	 * Define bulk actions available
	 * @return array Associative array of bulk actions, keys are used in self::process_bulk_action().
	 */
	public function get_bulk_actions() 
	{
		return (array(
			'befriend' => __('Befriend All Users', 'friendso'),
			'remove' => __('Remove', 'friendso')
		));
	}

	/** 
	 * Performs bulk actions based on $this->current_action()
	 * @return void Redirects to the current page.
	 */
	public function process_bulk_action()
	{
		if ($this->current_action() && check_admin_referer('bulk-action', 'autofriends-nonce')) {
			global $wpdb;
			$count = 0;
			$oAutofriends = new PeepSoUserAutoFriendsModel();

			if ('befriend' === $this->current_action()) {
				foreach ($_POST['users'] as $userId){
					$count += $oAutofriends->befriends(intval($userId));
				}

				$message = __('befriend', 'friendso');
			} else if ('remove' === $this->current_action()) {
				foreach ($_POST['users'] as $userId){
					$count += $oAutofriends->remove_user(intval($userId));
				}

				$message = __('removed', 'friendso');
			}

			PeepSoAdmin::get_instance()->add_notice(
				sprintf('%1$d %2$s %3$s',
					$count,
					_n('user', 'users', $count, 'friendso'),
					$message),
				'note');

			PeepSo::redirect("//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		}
	}


	/**
	 * Gets a PeepSoUser object based on the given user id
	 * @param int $user_id User ID
	 * @return object An instance of PeepSoUser class populated with user data based on the given $user_id
	 */
	public function get_user($user_id)
	{
		static $users = array();

		if (!isset($users[$user_id])) {
			// $users[$user_id] = new PeepSoUser($user_id);
			$users[$user_id] = PeepSoUser::get_instance($user_id);
		}

		return ($users[$user_id]);
	}
}

// EOF
