<?php

class PeepSoGdprListTable extends PeepSoListTable 
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

		$totalItems = count(PeepSoGdpr::fetch_all());

		// SQL safe, admin only
		$aQueueu = PeepSoGdpr::fetch_all($limit, $offset, $input->value('orderby', NULL, FALSE), $input->value('order', NULL, array('asc','desc')));

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
			'request_id' => 'ID',
			'request_user_id' => __('User', 'peepso-core'),
            'request_filesize' => __('File Size', 'peepso-core'),
			'request_created_at' => __('Date', 'peepso-core'),
			'request_status' => __('Status', 'peepso-core')
		);
	}

	/**
	 * Return and define columns that may be sorted on the Request Data Queue table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_sortable_columns()
	{
		return array(
			'request_created_at' => array('request_created_at', false),
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
		// Try to get by ID
		$user = get_user_by('id',$item['request_user_id']);

		$fa = $this->fa_request_status($item);
		
		return $item[$column_name];
	}

	/**
	 * Returns the HTML for the checkbox column.
	 * @param  array $item The current post item in the loop.
	 * @return string The checkbox cell's HTML.
	 */
	public function column_cb($item)
	{
		return sprintf('<input type="checkbox" name="requestdata[]" value="%d" />',
    		$item['request_id']
    	);
	}

	/**
	 * Returns the HTML for the user column.
	 * @param  array $item The current post item in the loop.
	 * @return string The user cell's HTML.
	 */
	public function column_request_user_id($item)
	{
		$user = PeepSoUser::get_instance($item['request_user_id']);
        ob_start();
        ?>
        <a href="<?php echo $user->get_profileurl(); ?>" target="_blank">
            <img src="<?php echo $user->get_avatar(); ?>" width="24" height="24" alt=""
                 style="float:left;margin-right:10px;"/>

            <div style=float:left>
                <?php echo $user->get_fullname(); ?>
                <i class="fa fa-external-link"></i>
            </div>
        </a>
        <?php
        return ob_get_clean();
	}

	/**
	 * Returns the output for the status column.
	 * @param  array $item The current post item in the loop.
	 * @return string The status cell's HTML.
	 */
	public function column_request_status($item)
	{
	    $error = '<br/> <textarea rows="5" wrap="soft" style="white-space: nowrap;  overflow: auto;width:100%">'.$item['request_error_log'].'</textarea>';
		switch ($item['request_status']) {
		case PeepSoGdpr::STATUS_PENDING:
			$ret = __('Waiting', 'peepso-core');
			break;
		case PeepSoGdpr::STATUS_PROCESSING:
			$ret = __('Processing', 'peepso-core');
			break;
		case PeepSoGdpr::STATUS_DELAY:
			$ret =  __('Delay', 'peepso-core');
			break;
		case PeepSoGdpr::STATUS_FAILED:
			$ret = __('Failed', 'peepso-core');
			$ret .= $error;
			break;
		case PeepSoGdpr::STATUS_RETRY:
			$ret = __('Retry', 'peepso-core');
            $ret .= $error;
			break;
		case PeepSoGdpr::STATUS_READY:
			$ret = __('Waiting', 'peepso-core');
			break;
		case PeepSoGdpr::STATUS_REJECT:
			$ret = __('Rejected', 'peepso-core');
			break;
		case PeepSoGdpr::STATUS_SUCCESS:
			$ret = __('Success', 'peepso-core');
			break;
		default:
			$ret = __('Unknown', 'peepso-core');
			break;
		}

		return '<p style="color:'.$this->color_request_status($item).'">'.$ret.'</p>';
	}

	public function column_request_filesize($item)
	{
		$filesize = 0;
		if (!empty($item['request_file_path'])) {
			if (file_exists($item['request_file_path'])) {
				$filesize = $this->format_size_units(filesize($item['request_file_path']));
			}
		}

		return $filesize;
	}

	private function format_size_units($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . __(' GB', 'peepso-core');
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . __(' MB', 'peepso-core');
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . __(' KB', 'peepso-core');
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . __(' bytes', 'peepso-core');
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . __(' byte', 'peepso-core');
        }
        else
        {
            $bytes = __('0 bytes', 'peepso-core');
        }

        return $bytes;
	}

	private function color_request_status($item)
	{
		switch ($item['request_status']) {
			case PeepSoGdpr::STATUS_FAILED:
			case PeepSoGdpr::STATUS_REJECT:
				$color = 'red';
				break;
			case PeepSoGdpr::STATUS_DELAY:
			case PeepSoGdpr::STATUS_RETRY:
				$color = 'orange';
				break;
			default:
				$color="black";
				break;
		}

		return $color;
	}

	private function fa_request_status($item)
	{

		switch ($item['request_status']) {
			case PeepSoGdpr::STATUS_PENDING:
			case PeepSoGdpr::STATUS_DELAY:
				$fa = 'clock-o';
				break;
			case PeepSoGdpr::STATUS_PROCESSING:
				$fa = 'hourglass-half';
				break;
			case PeepSoGdpr::STATUS_FAILED:
				$fa = 'warning';
				break;
			case PeepSoGdpr::STATUS_RETRY:
				$fa = 'refresh';
				break;
			default:
				$fa = 'question';
				break;
		}

		return $fa;
	}

	/**
	 * Define bulk actions available
	 * @return array Associative array of bulk actions, keys are used in self::process_bulk_action().
	 */
	public function get_bulk_actions() 
	{
		return array(
			'delete' => __('Delete', 'peepso-core')
		);
	}

	/** 
	 * Performs bulk actions based on $this->current_action()
	 * @return void Redirects to the current page.
	 */
	public function process_bulk_action()
	{
//		if ('-1' !== $this->current_action() && isset($_POST['mailqueue-nonce']) &&
//			wp_verify_nonce($_POST['mailqueue-nonce'], 'mailqueue-nonce')) {
		if ('-1' !== $this->current_action() && check_admin_referer('bulk-action', 'request-data-nonce')) {
			global $wpdb;

			if ('delete' === $this->current_action()) {
				foreach ($_POST['requestdata'] as $requestId) {
					$query = 'SELECT * FROM `' . PeepSoGdpr::get_table_name() . '` WHERE `request_id` = %d';
					$result = $wpdb->get_row($wpdb->prepare($query, $requestId));

					if ( NULL !== $result ) {
						// delete the generated files if exists
						PeepSoGdpr::delete_request($result->request_user_id);
					}
				}

				$message = __('deleted', 'peepso-core');
			}
			$count = count($_POST['requestdata']);

			PeepSoAdmin::get_instance()->add_notice(
				sprintf('%1$d %2$s %3$s',
					$count,
					_n('request', 'requests', $count, 'peepso-core'),
					$message),
				'note');

			PeepSo::redirect("//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		}
	}

	/**
	 * Adds The 'Process Emails' button and mail queue estimate to the top of the table.
	 * @param  string $which The current position to display the HTML.
	 * @return void Echoes the content.
	 */
	public function extra_tablenav($which)
	{
		// if ('top' === $which) {
		// 	$nonce = wp_create_nonce('process-request-data-nonce');
		// 	echo '
		// 	<div class="alignleft actions">
		// 		<a href="', admin_url('admin.php?page=peepso-gdpr-request-data&action=process-request-data&_wpnonce=' . $nonce), '">
		// 			<input type="button" class="button" value="', __('Process Requests', 'peepso-core'), '" />
		// 		</a>
		// 	</div>';
		// }
	}
}

// EOF