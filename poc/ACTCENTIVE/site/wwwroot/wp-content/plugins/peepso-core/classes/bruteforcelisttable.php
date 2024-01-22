<?php

class PeepSoBruteForceListTable extends PeepSoListTable 
{
	/**
	 * Defines the query to be used, performs sorting, filtering and calling of bulk actions.
	 * @return void
	 */
	public function prepare_items()
	{
		global $wpdb;
		$input = new PeepSoInput();
		if (isset($_POST['action']))
			$this->process_bulk_action();

		$limit = 20;
		$offset = ($this->get_pagenum() - 1) * $limit;

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);

		$totalItems = count(PeepSoBruteForce::fetch_all());

		// SQL safe, admin only
		$aQueueu = PeepSoBruteForce::fetch_all($limit, $offset, $input->value('orderby', NULL, FALSE), $input->value('order', NULL, array('asc','desc')));

		$this->set_pagination_args(array(
				'total_items' => $totalItems,
				'per_page' => $limit
			)
		);
		$this->items = $aQueueu;
	}

	/**
	 * Return and define columns to be displayed on the Request Data Queue table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_columns()
	{
		return array(
			'cb' => '<input type="checkbox" />',
			'attempts_ip' => __('IP', 'peepso-core'),
			'attempts_username' => __('Attempted Username', 'peepso-core'),
			'attempts_time' => __('Last Failed Attempt ', 'peepso-core'),
			'attempts_count' => __('Failed Attempts Count', 'peepso-core'),
			'attempts_lockout' => __('Lockouts Count', 'peepso-core'),
			'attempts_url' => __('URL Attacked', 'peepso-core'),
			'attempts_type' => __('Attempts Type', 'peepso-core'),
			'actions' => __('Actions', 'peepso-core')
		);
	}

	/**
	 * Return and define columns that may be sorted on the Request Data Queue table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_sortable_columns()
	{
		return array(
			'attempts_ip' => array('attempts_ip', false), 
			'attempts_username' => array('attempts_username', false), 
			'attempts_time' => array('attempts_time', false), 
			'attempts_count' => array('attempts_count', false),
			'attempts_lockout' => array('attempts_lockout', false),
			'attempts_url' => array('attempts_url', false),
			'attempts_type' => array('attempts_type', false)
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
		return $item[$column_name];
	}

	/**
	 * Returns the output for the type column.
	 * @param  array $item The current post item in the loop.
	 * @return string The type cell's HTML.
	 */
	public function column_attempts_type($item)
	{
	    switch ($item['attempts_type']) {
		case PeepSoBruteForce::TYPE_LOGIN:
			$ret = __('Login', 'peepso-core');
			break;
		case PeepSoBruteForce::TYPE_RESET_PASSWORD:
			$ret = __('Reset Password', 'peepso-core');
			break;
		default:
			$ret = __('Unknown', 'peepso-core');
			break;
		}

		return $ret;
	}

	public function column_attempts_time($item)
	{
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');

		return date($date_format . ' ' . $time_format, $item['attempts_time']);
	}

	/**
	 * Generate row actions div
	 * @param array $item An array of single report row/item
	 * @return array $actions The list of actions
	 */
	public function column_actions($item)
	{
		$actions = array(
			'delete'   => '<a href="#" onclick="list_brute_force.delete(' . $item['attempts_id'] . '); return false;">
				<input type="button" value="' . __('Delete', 'peepso-core') . '" ' .
				' title="' . __('Delete this item, leave the post alone.', 'peepso-core') . '" class="button action"/></a>',
		);

  		return ($this->row_actions($actions));
	}


	/**
	 * Returns the HTML for the checkbox column.
	 * @param  array $item The current post item in the loop.
	 * @return string The checkbox cell's HTML.
	 */
	public function column_cb($item)
	{
		return (sprintf('<input type="checkbox" name="attempts[]" value="%d" />',
			$item['attempts_id']));
	}

	/**
	 * Define bulk actions available
	 * @return array Associative array of bulk actions, keys are used in self::process_bulk_action().
	 */
	public function get_bulk_actions()
	{
		return (array(
			'delete' => __('Delete', 'peepso-core')
		));
	}

	/**
	 * Performs bulk actions based on $this->current_action()
	 * @return void Redirects to the current page.
	 */
	public function process_bulk_action()
	{
		if ($this->current_action() && check_admin_referer('bulk-action', 'brute-force-nonce')) {
			global $wpdb;
			$count = 0;
			$oBruteForce = new PeepSoBruteForce();
			
			if ('delete' === $this->current_action()) {
				foreach ($_POST['attempts'] as $logId)
					$count += $oBruteForce->delete_logs(intval($logId));

				$message = __('deleted', 'peepso-core');
			}

			PeepSoAdmin::get_instance()->add_notice(
				sprintf('%1$d %2$s %3$s',
					$count,
					_n('login attempt', 'login attempts', $count, 'peepso-core'),
					$message),
				'note');

			PeepSo::redirect("//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		}
	}

	/**
	 * Adds The 'Clear logs' button and mail queue estimate to the top of the table.
	 * @param  string $which The current position to display the HTML.
	 * @return void Echoes the content.
	 */
	public function extra_tablenav($which)
	{
		if ('top' === $which) {
			$nonce = wp_create_nonce('clear-brute-force-logs-nonce');
			echo '
			<div class="alignleft actions">
				<a onclick="return confirm(\''. __('Are you sure?', 'peepso-core') . '\');" href="', admin_url('admin.php?page=peepso-manage&tab=brute-force&action=clear-brute-force-logs&_wpnonce=' . $nonce), '">
					<input type="button" class="button" value="', __('Clear logs', 'peepso-core'), '" />
				</a>
			</div>';
		}
	}
}

// EOF