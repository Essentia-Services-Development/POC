<?php

class PeepSoAdminVideosQueueListTable extends PeepSoListTable 
{
	/**
	 * Defines the query to be used, performs sorting, filtering and calling of bulk actions.
	 * @return void
	 */
	public function prepare_items()
	{
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

		$videomodel = new PeepSoVideosModel();

		$totalItems = count($videomodel->fetch_all_queue());

		$orderby = array(
                    'vid_id'			,
					'vid_album_id'		,
					'vid_post_id'		,
					'vid_acc'			,
					'vid_stored'		,
					'vid_stored_failed'	,
					'vid_title'			,
					'vid_artist'		,
					'vid_album'			,
					'vid_description'	,
					'vid_thumbnail'		,
					'vid_animated'		,
					'vid_animated_webm'	,
					'vid_url'			,
					'vid_embed'			,
					'vid_size'			,
					'vid_created'		,
					'vid_token'			,
					'vid_module_id'		,
					'vid_conversion_status',
					'vid_error_messages',
        );

		$aQueueu = $videomodel->fetch_all_queue($limit, $offset, $input->value('orderby', 'vid_created', $orderby), $input->value('order', 'desc', array('asc','desc')));

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
            'vid_id' => 'ID',
			'post_author' => __('User', 'vidso'),
			'post_title'  => __('Link', 'vidso'),
			'vid_thumbnail'  => __('Thumbnail', 'vidso'),
			'vid_title' => __('Title', 'vidso'),
			'vid_size' => __('File Size', 'vidso'),
			'vid_created' => __('Date', 'vidso'),
			'vid_conversion_status' => __('Status', 'vidso')
		);
	}

	/**
	 * Return and define columns that may be sorted on the Request Data Queue table.
	 * @return array Associative array of columns with the database columns used as keys.
	 */
	public function get_sortable_columns()
	{
		return array(
			'vid_created' => array('vid_created', false)
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
        $user = get_user_by('id',$item['post_author']);

		$fa = $this->fa_conversion_status($item);
		
		return $item[$column_name];
	}

	/**
	 * Returns the HTML for the checkbox column.
	 * @param  array $item The current post item in the loop.
	 * @return string The checkbox cell's HTML.
	 */
	public function column_cb($item)
	{
		if (($item['vid_conversion_status'] == PeepSoVideosUpload::STATUS_PENDING && $item['vid_stored_failed'] == 1) ||
			($item['vid_conversion_status'] == PeepSoVideosUpload::STATUS_PENDING && $item['vid_stored'] == 0 && $item['vid_stored_failed'] == 0) ||
			($item['vid_conversion_status'] == PeepSoVideosUpload::STATUS_FAILED && $item['vid_stored_failed'] == 1)
		) {
			return sprintf('<input type="checkbox" name="videoqueue[]" value="%d" />',
	    		$item['vid_id']
	    	);
		}
	}

	/**
	 * Returns the HTML for the user column.
	 * @param  array $item The current post item in the loop.
	 * @return string The user cell's HTML.
	 */
	public function column_post_author($item)
	{
		$user = PeepSoUser::get_instance($item['post_author']);

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
	public function column_vid_conversion_status($item)
	{
		$error = '<br/> <textarea rows="5" wrap="soft" style="white-space: nowrap;  overflow: auto;width:100%">'.$item['vid_error_messages'].'</textarea>';
	    switch ($item['vid_conversion_status']) {
		case PeepSoGdpr::STATUS_PENDING:
			$ret = __('Waiting', 'vidso');
			
			// handling old status
			if($item['vid_stored'] == 1) {
				$ret = __('Success', 'vidso');
			}
			// handling old status
			if($item['vid_stored_failed'] == 1) {
				$ret = __('Failed', 'vidso');
			}
			break;
		case PeepSoGdpr::STATUS_PROCESSING:
			$ret = __('Processing', 'vidso');
			break;
		case PeepSoGdpr::STATUS_DELAY:
			$ret =  __('Delay', 'vidso');
			break;
		case PeepSoGdpr::STATUS_FAILED:
			$ret = __('Failed', 'vidso');
			$ret .= $error;
			break;
		case PeepSoGdpr::STATUS_RETRY:
			$ret = __('Retry', 'vidso');
            $ret .= $error;
			break;
		case PeepSoGdpr::STATUS_READY:
			$ret = __('Ready', 'vidso');
			break;
		case PeepSoGdpr::STATUS_REJECT:
			$ret = __('Rejected', 'vidso');
			break;
		case PeepSoGdpr::STATUS_SUCCESS:
			$ret = __('Success', 'vidso');
			break;
		default:
			$ret = __('Unknown', 'vidso');
			break;
		}

		return '<p style="color:'.$this->color_conversion_status($item).'">'.$ret.'</p>';
	}

	public function column_post_title($item)
	{
		ob_start();
        ?>
        <a href="<?php echo PeepSo::get_page('activity_status', FALSE) . $item['post_title'] ?>" target="_blank">
            <div style=float:left>
                <?php _e('See post', 'vidso'); ?>
                <i class="fa fa-external-link"></i>
            </div>
        </a>
        <?php
        return ob_get_clean();
	}

	public function column_vid_size($item)
	{
        $filesize = '-';

        if(PeepSoGdpr::STATUS_SUCCESS == $item['vid_conversion_status']) {
            $filesize = $this->format_size_units($item['vid_size']);
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

	private function color_conversion_status($item)
	{
		switch ($item['vid_conversion_status']) {
			case PeepSoVideosUpload::STATUS_FAILED:
			case PeepSoVideosUpload::STATUS_REJECT:
				$color = 'red';
				break;
			case PeepSoVideosUpload::STATUS_DELAY:
			case PeepSoVideosUpload::STATUS_RETRY:
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

		switch ($item['vid_conversion_status']) {
			case PeepSoVideosUpload::STATUS_PENDING:
			case PeepSoVideosUpload::STATUS_DELAY:
				$fa = 'clock-o';
				break;
			case PeepSoVideosUpload::STATUS_PROCESSING:
				$fa = 'hourglass-half';
				break;
			case PeepSoVideosUpload::STATUS_FAILED:
				$fa = 'warning';
				break;
			case PeepSoVideosUpload::STATUS_RETRY:
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
            'waiting' => __('Set to "waiting"', 'peepso-core'),
            'delete' => __('Delete', 'peepso-core')
        );
	}

	/** 
	 * Performs bulk actions based on $this->current_action()
	 * @return void Redirects to the current page.
	 */
	public function process_bulk_action()
	{
		if ('-1' !== $this->current_action() && check_admin_referer('bulk-action', 'videos-queue-nonce')) {
			global $wpdb;

			if ('delete' === $this->current_action()) {
				foreach ($_POST['videoqueue'] as $vidId) {
					$query = 'SELECT * FROM `' . $wpdb->prefix . PeepSoVideosModel::TABLE . '` WHERE `vid_id` = %d';
					$result = $wpdb->get_row($wpdb->prepare($query, $vidId));

					if ( NULL !== $result ) {
						// delete the generated files if exists
						if (in_array($result->vid_conversion_status, array(PeepSoVideosUpload::STATUS_PENDING, PeepSoVideosUpload::STATUS_FAILED))) {
							PeepSoVideosUpload::delete_video($result->vid_id);
						}
					}
				}

				$message = __('deleted', 'vidso');
			} else if ('waiting' === $this->current_action()) {
				foreach ($_POST['videoqueue'] as $vidId) {

					$vid_data = array(
						'vid_conversion_status' => PeepSoVideosUpload::STATUS_PENDING, 
						'vid_stored_failed' => 0, 
						'vid_error_messages' => ''
					);

					$wpdb->update(
						$wpdb->prefix . PeepSoVideosModel::TABLE, 
						$vid_data,
						array('vid_id' => $vidId)
					);

					$do_conversion = PeepSo::get_option('videos_conversion_mode', 'no');
		            if ($do_conversion == 'aws_elastic') {
						$wpdb->update(
							$wpdb->prefix . PeepSoVideosModel::TABLE, 
							array('vid_upload_s3_status' => PeepSoVideosUpload::STATUS_S3_WAITING, 'vid_upload_s3_retry_count' => 0),
							array('vid_id' => $vidId, 'vid_upload_s3_status' => PeepSoVideosUpload::STATUS_S3_FAILED)
						);
					}
				}

				$message = __('updated', 'vidso');
			}

			$count = count($_POST['videoqueue']);

			PeepSoAdmin::get_instance()->add_notice(
				sprintf(_n('%1$d video %2$s', '%1$d videos %2$s', $count, 'vidso'),
					$count,
					$message),
				'note');

			PeepSo::redirect("//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		}
	}

	public function column_vid_thumbnail($item) 
	{
		if (isset($item['vid_thumbnail'])) {
			$image = $item['vid_thumbnail'];
		} else if (isset($item['vid_animated'])) {
			$image = $item['vid_animated'];
		}

		if (isset($image)) {
			return '<img width="100px" src="' . $image . '"/>';
		}
	}

	/**
	 * Adds The 'Process Emails' button and mail queue estimate to the top of the table.
	 * @param  string $which The current position to display the HTML.
	 * @return void Echoes the content.
	 */
	public function extra_tablenav($which)
	{
		if ('top' === $which) {
			// 
		}
	}
}

// EOF