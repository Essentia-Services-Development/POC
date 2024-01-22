<?php

namespace FSPoster\App\Pages\Schedules\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Providers\ShareService;

trait Ajax
{
	public function schedule_save ()
	{
		$id               = Request::post( 'id', '0', 'int' );
		$title            = Request::post( 'title', '', 'string' );
		$start_date       = Request::post( 'start_date', '', 'string' );
		$start_time       = Request::post( 'start_time', '', 'string' );
		$interval         = Request::post( 'interval', '0', 'num' );
		$post_type_filter = Request::post( 'post_type_filter', '', 'string' );

		$filter_posts_date_range_from = Request::post( 'filter_posts_date_range_from', '1000-00-00', 'string' );
		$filter_posts_date_range_to   = Request::post( 'filter_posts_date_range_to', '9999-12-31', 'string' );

		$filter_posts_date_range_from = ! empty( $filter_posts_date_range_from ) ? $filter_posts_date_range_from : '1000-00-00';
		$filter_posts_date_range_to   = ! empty( $filter_posts_date_range_to ) ? $filter_posts_date_range_to : '9999-12-31';

		$dont_post_out_of_stock_products = Request::post( 'dont_post_out_of_stock_products', '0', 'string', [
			'0',
			'1'
		] );
		$category_filter                 = Request::post( 'category_filter', '0', 'int' );
		$post_sort                       = Request::post( 'post_sort', 'random', 'string', [
			'random',
			'random2',
			'old_first',
			'new_first'
		] );
		$post_freq                       = Request::post( 'post_freq', 'once', 'string', [
			'once',
			'repeat'
		] );
		$post_ids_p                      = Request::post( 'post_ids', '', 'string' );
		$custom_messages                 = Request::post( 'custom_messages', '', 'string' );

		$instagram_pin_the_post = Request::post( 'instagram_pin_the_post', 0, 'num', [ 0, 1 ] );
		$autoRescheduleCount    = Request::post( 'autoRescheduleCount', 1, 'num' );
		$autoRescheduleEnabled  = Request::post( 'autoRescheduleEnabled', 0, 'num', [ 0, 1 ] );

		$accounts_list    = Request::post( 'accounts_list', '', 'string' );
		$sleep_time_start = Request::post( 'sleep_time_start', '', 'string' );
		$sleep_time_end   = Request::post( 'sleep_time_end', '', 'string' );

		if ( ! ( $interval > 0 && $interval <= 1440000 ) )
		{
			Helper::response( FALSE, fsp__( 'Interval is not correct!' ) );
		}

		if ( $id > 0 )
		{
			$schedule_info = DB::fetch( 'schedules', $id );

			if ( ! $schedule_info )
			{
				Helper::response( FALSE );
			}
		}

		$post_ids   = [];
		$post_ids_p = explode( ',', str_replace( ' ', '', $post_ids_p ) );

		foreach ( $post_ids_p as $post_id )
		{
			if ( is_numeric( $post_id ) && $post_id > 0 )
			{
				$post_ids[] = (int) $post_id;
			}
		}

		$post_ids_count = count( $post_ids );

		if ( $post_ids_count > 200 )
		{
			Helper::response( FALSE, fsp__( 'Too many posts are selected! You can select maximum 200 posts!' ) );
		}

		if ( $post_ids_count > 1 && $post_freq !== 'once' )
		{
			Helper::response( FALSE, fsp__( 'If you want to share repeatedly, you should schedule only a post!' ) );
		}

		if ( $post_freq === 'repeat' )
		{
			$post_sort = 'random';
		}

		$post_ids = implode( ',', $post_ids );
		$post_ids = empty( $post_ids ) ? NULL : $post_ids;

		if ( empty( $sleep_time_start ) || empty( $sleep_time_end ) )
		{
			$sleep_time_start = NULL;
			$sleep_time_end   = NULL;
		}
		else
		{
			$sleep_time_start = Date::timeSQL( $sleep_time_start );
			$sleep_time_end   = Date::timeSQL( $sleep_time_end );
		}

		$_custom_messages = [];

		if ( ! empty( $custom_messages ) )
		{
			$custom_messages = json_decode( $custom_messages, TRUE );
			$custom_messages = is_array( $custom_messages ) ? $custom_messages : [];

			foreach ( $custom_messages as $socialNetwork => $message1 )
			{
				if ( in_array( $socialNetwork, [
						'fb',
						'fb_h',
						'instagram',
						'instagram_h',
						'linkedin',
						'threads',
						'twitter',
						'pinterest',
						'vk',
						'ok',
						'tumblr',
						'reddit',
						'google_b',
						'blogger',
						'telegram',
						'medium',
						'wordpress',
						'plurk',
						'planly',
						'discord',
						'mastodon',
					] ) && is_string( $message1 ) )
				{
					$_custom_messages[ $socialNetwork ] = $message1;
				}
			}
		}

		$_custom_messages = empty( $_custom_messages ) ? NULL : json_encode( $_custom_messages );

		$_accounts_list = [];

		if ( ! empty( $accounts_list ) )
		{
			$accounts_list = json_decode( $accounts_list, TRUE );
			$accounts_list = Pages::action( 'Base', 'groups_to_nodes', [ 'node_list' => $accounts_list ] );
			$accounts_list = is_array( $accounts_list ) ? $accounts_list : [];

			foreach ( $accounts_list as $social_account )
			{
				if ( is_string( $social_account ) )
				{
					$social_account = explode( ':', $social_account );

					if ( count( $social_account ) !== 5 )
					{
						continue;
					}

					$_accounts_list[] = ( $social_account[ 1 ] === 'account' ? 'account' : 'node' ) . ':' . $social_account[ 2 ];
				}
			}
		}

		if ( empty( $_accounts_list ) )
		{
			Helper::response( FALSE, fsp__( 'No account or community is selected.' ) );
		}

		if ( strtotime( $filter_posts_date_range_from ) > strtotime( $filter_posts_date_range_to ) )
		{
			Helper::response( FALSE, fsp__( 'The date range is not correct.' ) );
		}

		$_accounts_list = implode( ',', $_accounts_list );

		$allowedPostTypes = explode( '|', Helper::getOption( 'allowed_post_types', 'post|page|attachment|product' ) );

		if ( ! in_array( $post_type_filter, $allowedPostTypes ) )
		{
			$post_type_filter = '';
		}

		if ( empty( $title ) )
		{
			Helper::response( FALSE, fsp__( 'The name can\'t be empty!' ) );
		}

		if ( empty( $start_date ) || empty( $start_time ) )
		{
			Helper::response( FALSE, fsp__( 'Please select the start date and time!' ) );
		}

		if ( ! is_numeric( $interval ) | $interval <= 0 )
		{
			Helper::response( FALSE, fsp__( 'Please type the interval!' ) );
		}

		$start_date = Date::dateSQL( $start_date );
		$start_time = Date::timeSQL( $start_time );

		$cronStartTime = $start_date . ' ' . $start_time;

		if ( ! empty( $schedule_info[ 'id' ] ) )
		{
			$old_start_datetime = $schedule_info[ 'start_date' ] . ' ' . $schedule_info[ 'share_time' ];

			if ( $old_start_datetime === $cronStartTime || Date::epoch( $cronStartTime ) < Date::epoch( $schedule_info[ 'next_execute_time' ] ) )
			{
				$expected_last_share  = Date::epoch( $schedule_info[ 'next_execute_time' ] ) - $schedule_info[ 'interval' ] * 60;
				$last_share_timestamp = $expected_last_share >= Date::epoch( $old_start_datetime ) ? $expected_last_share : Date::epoch( $old_start_datetime );

				if ( $old_start_datetime !== $schedule_info[ 'next_execute_time' ] )
				{
					$next_execute_timestamp = $last_share_timestamp + $interval * 60;
					$cronStartTime          = Date::dateTimeSQL( $next_execute_timestamp );
				}

				if ( Date::epoch( $cronStartTime ) < Date::epoch( Date::dateTimeSQL() ) )
				{
					$cronStartTime = $schedule_info[ 'next_execute_time' ];
				}
			}
		}

		$data = isset( $schedule_info[ 'data' ] ) ? json_decode( $schedule_info[ 'data' ], TRUE ) : [];
		$data = empty( $data ) ? [] : $data;

		$data[ 'instagram_pin_the_post' ] = $instagram_pin_the_post;
		$data[ 'autoRescheduleEnabled' ]  = $autoRescheduleEnabled;
		$data[ 'autoRescheduleCount' ]    = $autoRescheduleCount;
		$data[ 'autoReschdulesDone' ]     = empty( $data[ 'autoReschdulesDone' ] ) ? 0 : $data[ 'autoReschdulesDone' ];

		$sql_arr = [
			'blog_id'                         => Helper::getBlogId(),
			'title'                           => $title,
			'start_date'                      => $start_date,
			'interval'                        => $interval,
			'status'                          => 'active',
			'insert_date'                     => Date::dateTimeSQL(),
			'user_id'                         => get_current_user_id(),
			'share_time'                      => $start_time,
			'next_execute_time'               => $cronStartTime,
			'post_ids'                        => $post_ids,
			'save_post_ids'                   => $post_ids,
			'post_type_filter'                => $post_type_filter,
			'dont_post_out_of_stock_products' => $dont_post_out_of_stock_products,
			'category_filter'                 => $category_filter > 0 ? $category_filter : NULL,
			'post_sort'                       => $post_sort,
			'post_freq'                       => $post_ids_count > 1 ? 'once' : $post_freq,
			'post_date_filter'                => 'custom',

			'filter_posts_date_range_from' => $filter_posts_date_range_from,
			'filter_posts_date_range_to'   => $filter_posts_date_range_to,

			'custom_post_message' => $_custom_messages,
			'share_on_accounts'   => $_accounts_list,
			'sleep_time_start'    => $sleep_time_start,
			'sleep_time_end'      => $sleep_time_end,
			'data'                => json_encode( $data )
		];

		if ( $id > 0 && $schedule_info[ 'status' ] != 'finished' )
		{
			unset( $sql_arr[ 'status' ] );

			DB::DB()->update( DB::table( 'schedules' ), $sql_arr, [ 'id' => $id ] );
		}
		else
		{
			DB::DB()->insert( DB::table( 'schedules' ), $sql_arr );
		}

		Helper::response( TRUE );
	}

	public function wp_native_schedule_save ()
	{
		$post_id                = 0;
		$info                   = Request::post( 'info', '', 'string' );
		$custom_messages        = Request::post( 'custom_messages', '', 'string' );
		$accounts_list          = Request::post( 'accounts_list', '', 'string' );
		$instagram_pin_the_post = Request::post( 'instagram_pin_the_post', 0, 'num', [ 0, 1 ] );

		if ( ! empty( $info ) )
		{
			$info    = json_decode( $info, TRUE );
			$info    = is_array( $info ) ? $info : [];
			$post_id = $info[ 'post_id' ];
		}

		if ( ! ( $post_id > 0 ) )
		{
			Helper::response( FALSE );
		}

		$_custom_messages = [];

		if ( ! empty( $custom_messages ) )
		{
			$custom_messages = json_decode( $custom_messages, TRUE );
			$custom_messages = is_array( $custom_messages ) ? $custom_messages : [];

			foreach ( $custom_messages as $socialNetwork => $message1 )
			{
				if ( in_array( $socialNetwork, [
						'fb',
						'fb_h',
						'instagram',
						'instagram_h',
						'linkedin',
						'threads',
						'twitter',
						'pinterest',
						'vk',
						'ok',
						'tumblr',
						'reddit',
						'google_b',
						'blogger',
						'telegram',
						'medium',
						'wordpress',
						'plurk',
						'planly',
						'discord',
						'mastodon',
					] ) && is_string( $message1 ) )
				{
					$_custom_messages[ $socialNetwork ] = $message1;
				}
			}
		}

		$_accounts_list = [
			'accounts' => [],
			'nodes'    => []
		];

		if ( ! empty( $accounts_list ) )
		{
			$accounts_list = json_decode( $accounts_list, TRUE );
			$accounts_list = Pages::action( 'Base', 'groups_to_nodes', [ 'node_list' => $accounts_list ] );
			$accounts_list = is_array( $accounts_list ) ? $accounts_list : [];

			foreach ( $accounts_list as $social_account )
			{
				if ( is_string( $social_account ) )
				{
					$social_account = explode( ':', $social_account );

					if ( ! is_numeric( $social_account[ 2 ] ) )
					{
						continue;
					}

					if ( $social_account[ 1 ] === 'account' )
					{
						$_accounts_list[ 'accounts' ][] = $social_account[ 2 ];
					}
					else
					{
						$_accounts_list[ 'nodes' ][] = $social_account[ 2 ];
					}
				}
			}
		}

		$account_ids = implode( ',', $_accounts_list[ 'accounts' ] );
		$node_ids    = implode( ',', $_accounts_list[ 'nodes' ] );

		if ( ! empty( $account_ids ) )
		{
			$accounts = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT 
					tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type 
				FROM " . DB::table( 'accounts' ) . " tb2
				LEFT JOIN " . DB::table( 'account_status' ) . " tb1 ON tb2.id=tb1.account_id AND tb1.user_id=%d
				WHERE (tb2.user_id=%d OR is_public=1) AND tb2.blog_id=%d AND tb2.id IN ({$account_ids})
				ORDER BY name", [ get_current_user_id(), get_current_user_id(), Helper::getBlogId() ] ), ARRAY_A );
		}
		else
		{
			$accounts = [];
		}

		if ( ! empty( $node_ids ) )
		{
			$active_nodes = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT 
					tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name 
				FROM " . DB::table( 'account_nodes' ) . " tb2
				LEFT JOIN " . DB::table( 'account_node_status' ) . " tb1 ON tb2.id=tb1.node_id AND tb1.user_id=%d
				WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d AND tb2.id IN ({$node_ids})
				ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name", [
				get_current_user_id(),
				get_current_user_id(),
				Helper::getBlogId()
			] ), ARRAY_A );
		}
		else
		{
			$active_nodes = [];
		}

		$nodes_list = [];

		foreach ( $accounts as $accountInf )
		{
			$nodes_list[] = $accountInf[ 'driver' ] . ':account:' . (int) $accountInf[ 'id' ] . ':' . $accountInf[ 'filter_type' ] . ':' . $accountInf[ 'categories' ];
		}

		foreach ( $active_nodes as $nodeInf )
		{
			$nodes_list[] = $nodeInf[ 'driver' ] . ':' . $nodeInf[ 'node_type' ] . ':' . (int) $nodeInf[ 'id' ] . ':' . $nodeInf[ 'filter_type' ] . ':' . $nodeInf[ 'categories' ];
		}

		if ( empty( $nodes_list ) )
		{
			Helper::response( FALSE, fsp__( 'No account or community is selected.' ) );
		}

		DB::DB()->query( DB::DB()->prepare( 'DELETE FROM ' . DB::table( 'feeds' ) . ' WHERE post_id = %d AND is_sended = 0 AND blog_id = %d', [
			$post_id,
			Helper::getBlogId()
		] ) );

		$post      = get_post( $post_id );
		$shareTime = Date::dateTimeSQL( $post->post_date, '+1 minute' );

		$userID = NULL;

		if ( ! empty( get_current_user_id() ) )
		{
			$userID = get_current_user_id();
		}
		else
		{
			if ( ! empty( $post->post_author ) && is_numeric( $post->post_author ) )
			{
				$userID = $post->post_author;
			}
		}

		ShareService::insertFeeds( $post_id, $userID, $nodes_list, $_custom_messages, TRUE, $shareTime, NULL, 1, NULL, FALSE, $instagram_pin_the_post );

		Helper::response( TRUE );
	}

	public function wp_native_schedule_delete ()
	{
		$info = Request::post( 'info', '', 'string' );

		if ( empty( $info ) )
		{
			Helper::response( FALSE );
		}

		$info    = json_decode( $info, TRUE );
		$info    = is_array( $info ) ? $info : [];
		$post_id = $info[ 'post_id' ];

		if ( ! ( $post_id > 0 ) )
		{
			Helper::response( FALSE );
		}

		DB::DB()->query( DB::DB()->prepare( 'DELETE FROM ' . DB::table( 'feeds' ) . ' WHERE post_id = %d AND is_sended = 0 AND blog_id = %d', [
			$post_id,
			Helper::getBlogId()
		] ) );

		Helper::response( TRUE );
	}

	public function delete_schedule ()
	{
		$id = Request::post( 'id', 0, 'num' );
		if ( $id <= 0 )
		{
			Helper::response( FALSE );
		}

		$checkSchedule = DB::fetch( 'schedules', $id );
		if ( ! $checkSchedule )
		{
			Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
		}
		else
		{
			if ( $checkSchedule[ 'user_id' ] != get_current_user_id() )
			{
				Helper::response( FALSE, fsp__( 'You don\'t have permission to delete the schedule!' ) );
			}
		}

		DB::DB()->delete( DB::table( 'schedules' ), [ 'id' => $id ] );

		Helper::response( TRUE );
	}

	public function delete_schedules ()
	{
		$ids = Request::post( 'ids', [], 'array' );
		if ( count( $ids ) == 0 )
		{
			Helper::response( FALSE, fsp__( 'No schedule selected!' ) );
		}

		foreach ( $ids as $id )
		{
			if ( is_numeric( $id ) && $id > 0 )
			{
				$checkSchedule = DB::fetch( 'schedules', $id );
				if ( ! $checkSchedule )
				{
					Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
				}

				else
				{
					if ( $checkSchedule[ 'user_id' ] != get_current_user_id() )
					{
						Helper::response( FALSE, fsp__( 'You don\'t have permission to delete the schedule!' ) );
					}
				}

				DB::DB()->delete( DB::table( 'schedules' ), [ 'id' => $id ] );
			}
		}

		Helper::response( TRUE );
	}

	public function schedule_change_status ()
	{
		$id = Request::post( 'id', 0, 'num' );

		if ( $id <= 0 )
		{
			Helper::response( FALSE );
		}

		$checkSchedule = DB::fetch( 'schedules', $id );
		if ( ! $checkSchedule )
		{
			Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
		}
		else
		{
			if ( $checkSchedule[ 'user_id' ] != get_current_user_id() )
			{
				Helper::response( FALSE, fsp__( 'You don\'t have permission to Pause/Play the schedule!' ) );
			}
		}

		if ( $checkSchedule[ 'status' ] != 'paused' && $checkSchedule[ 'status' ] != 'active' )
		{
			Helper::response( FALSE, fsp__( 'This schedule has finished!' ) );
		}

		$newStatus = $checkSchedule[ 'status' ] === 'active' ? 'paused' : 'active';

		$update_arr = [ 'status' => $newStatus ];

		if ( $newStatus != 'paused' )
		{
			$locTime         = Date::epoch();
			$scheduleStarted = Date::epoch( $checkSchedule[ 'start_date' ] . ' ' . $checkSchedule[ 'share_time' ] );

			$dif = $locTime - $scheduleStarted;

			$interval = $checkSchedule[ 'interval' ] * 60;

			$nextExecTime = ( $dif % $interval ) === 0 ? $locTime : $locTime + $interval - ( $dif % $interval );

			$update_arr[ 'next_execute_time' ] = Date::dateTimeSQL( $nextExecTime );
		}

		DB::DB()->update( DB::table( 'schedules' ), $update_arr, [ 'id' => $id ] );

		Helper::response( TRUE );
	}

	public function schedule_get_calendar ()
	{
		$month = (int) Request::post( 'month', Date::month(), 'int', [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ] );
		$year  = (int) Request::post( 'year', Date::year(), 'int' );

		if ( $year > Date::year() + 4 || $year < Date::year() - 4 )
		{
			Helper::response( FALSE, fsp__( 'Loooooooooooooooolll :)' ) );
		}

		$firstDate = Date::datee( "{$year}-{$month}-01" );
		$lastDate  = Date::lastDateOfMonth( $year, $month );
		$myId      = get_current_user_id();

		if ( Date::epoch( $firstDate ) < Date::epoch( Date::dateSQL() ) )
		{
			$firstDate = Date::datee();
		}

		$getPlannedDays = DB::DB()->get_results( "SELECT * FROM `" . DB::table( 'schedules' ) . "` WHERE `start_date` <= '$lastDate' AND `status` = 'active' AND `user_id` = '$myId' AND `blog_id` = '" . Helper::getBlogId() . "'", ARRAY_A );

		$days = [];

		foreach ( $getPlannedDays as $planInf )
		{
			$scheduleId = (int) $planInf[ 'id' ];
			//$planStart  = Date::epoch( $planInf[ 'start_date' ] );
			$planStart        = Date::epoch( $planInf[ 'next_execute_time' ] );
			$planEnd          = Date::epoch( $lastDate );
			$interval         = (int) $planInf[ 'interval' ] > 0 ? (int) $planInf[ 'interval' ] : 1;
			$postCount        = empty( $planInf[ 'post_ids' ] ) || $planInf[ 'post_freq' ] === 'repeat' ? -1 : count( explode( ',', $planInf[ 'post_ids' ] ) );
			$isRandomSchedule = $planInf[ 'post_sort' ] == 'random' || $planInf[ 'post_sort' ] == 'random2';

			$savePostIDs          = empty( $planInf[ 'save_post_ids' ] ) ? [] : explode( ',', $planInf[ 'save_post_ids' ] );
			$isSinglePostSchedule = is_array( $savePostIDs ) && count( $savePostIDs ) === 1;

			if ( $postCount > 0 && $planInf[ 'post_freq' ] === 'once' )
			{
				$duration      = $interval * ( $postCount - 1 );
				$last_run_date = $planStart + $duration * 60;

				if ( Date::epoch( $firstDate ) > $last_run_date )
				{
					continue;
				}
			}

			if ( $planStart < Date::epoch( $firstDate ) )
			{
				while ( $planStart < Date::epoch( $firstDate ) )
				{
					$planStart += 60 * $interval;
				}
			}

			if ( ! $isRandomSchedule || $isSinglePostSchedule )
			{
				$filterQuery = Helper::scheduleFilters( $planInf );
				$calcLimit   = 1 + (int) ( ( $planEnd - $planStart ) / 60 / $interval );

				$calcLimit = $calcLimit > 0 ? $calcLimit : 1;

				$getRandomPost = DB::DB()->get_results( "SELECT * FROM " . DB::WPtable( 'posts', TRUE ) . " tb1 WHERE post_status='publish' {$filterQuery} LIMIT " . $calcLimit, ARRAY_A );
			}

			if ( empty( $planInf[ 'share_time' ] ) )
			{
				$getLastShareTime        = DB::DB()->get_row( "SELECT MAX(send_time) AS max_share_time FROM " . DB::table( 'feeds' ) . " WHERE schedule_id='$scheduleId'", ARRAY_A );
				$planInf[ 'share_time' ] = Date::timeSQL( $getLastShareTime[ 'max_share_time' ] );
			}

			//$cursorDayTimestamp = Date::epoch( Date::dateSQL( $planStart ) . ' ' . $planInf[ 'share_time' ] );
			$cursorDayTimestamp = Date::epoch( Date::dateTimeSQL( $planStart ) );
			$planEnd            = Date::epoch( Date::dateSQL( $planEnd ) . ' 23:59:59' );

			while ( $cursorDayTimestamp <= $planEnd )
			{
				$currentDate = Date::dateSQL( $cursorDayTimestamp );
				$time        = Date::time( $cursorDayTimestamp );

				if ( ! empty( $planInf[ 'sleep_time_start' ] ) && ! empty( $planInf[ 'sleep_time_end' ] ) )
				{
					$sleepTimeStart = Date::epoch( $currentDate . ' ' . $planInf[ 'sleep_time_start' ] );
					$sleepTimeEnd   = Date::epoch( $currentDate . ' ' . $planInf[ 'sleep_time_end' ] );

					if ( Helper::isBetweenDates( $cursorDayTimestamp, $sleepTimeStart, $sleepTimeEnd ) )
					{
						$cursorDayTimestamp += 60 * $interval;

						continue;
					}
				}

				$cursorDayTimestamp += 60 * $interval;

				if ( Date::epoch( $currentDate . ' ' . $time ) < Date::epoch() )
				{
					continue;
				}

				if ( $postCount === 0 )
				{
					break;
				}

				if ( $isRandomSchedule && ! $isSinglePostSchedule )
				{
					$postDetails = 'Will select randomly';
					$post_id     = NULL;
				}
				else
				{
					$thisPostInf = current( $getRandomPost );
					next( $getRandomPost );

					if ( $thisPostInf )
					{
						$postDetails = '<b>Post ID:</b> ' . $thisPostInf[ 'ID' ] . "<br><b>Title:</b> " . htmlspecialchars( Helper::cutText( $thisPostInf[ 'post_title' ] ) . '<br><br><i>Click to get the post page</i>' );
						$post_id     = $thisPostInf[ 'ID' ];
					}
					else
					{
						$postDetails = 'Post not found with your filters for this date!';
						$post_id     = NULL;
					}
				}

				$days[] = [
					'id'        => $planInf[ 'id' ],
					'title'     => htmlspecialchars( Helper::cutText( $planInf[ 'title' ], 22 ) ),
					'post_data' => $postDetails,
					'post_id'   => $post_id,
					'date'      => $currentDate,
					'time'      => $time
				];

				$postCount--;
			}
		}

		Helper::response( TRUE, [ 'days' => $days ] );
	}

	public function recalculate_filtered_posts_count ()
	{
		$post_type_filter                = Request::post( 'post_type_filter', '', 'string' );
		$dont_post_out_of_stock_products = Request::post( 'dont_post_out_of_stock_products', '0', 'string', [
			'1',
			'0'
		] );
		$category_filter                 = Request::post( 'category_filter', '', 'int' );

		$filter_posts_date_range_from = Request::post( 'filter_posts_date_range_from', '', 'string' );
		$filter_posts_date_range_to   = Request::post( 'filter_posts_date_range_to', '', 'string' );

		$filter_posts_date_range_from = ! empty( $filter_posts_date_range_from ) ? $filter_posts_date_range_from : '1000-00-00';
		$filter_posts_date_range_to   = ! empty( $filter_posts_date_range_to ) ? $filter_posts_date_range_to : '9999-12-31';

		$post_ids = Request::post( 'post_ids', '', 'string' );

		$schedule_info = [
			'id'                              => 0,
			'post_type_filter'                => $post_type_filter,
			'dont_post_out_of_stock_products' => $dont_post_out_of_stock_products,
			'category_filter'                 => $category_filter,
			'post_date_filter'                => 'custom',
			'post_ids'                        => $post_ids,
			'filter_posts_date_range_from'    => $filter_posts_date_range_from,
			'filter_posts_date_range_to'      => $filter_posts_date_range_to
		];

		$filterQuery = Helper::scheduleFilters( $schedule_info );

		$getRandomPost = DB::DB()->get_row( "SELECT count(0) AS `post_count` FROM `" . DB::WPtable( 'posts', TRUE ) . "` tb1 WHERE (`post_status`='publish' OR `post_type`='attachment') {$filterQuery}", ARRAY_A );
		$postsCount    = (int) $getRandomPost[ 'post_count' ];

		Helper::response( TRUE, [
			'count' => $postsCount
		] );
	}

	public function fsp_fetch_schedule_list ()
	{
		$page       = Request::post( 'page', '1', 'int' );
		$rows_count = Request::post( 'rows_count', '4', 'int', [ '4', '8', '15' ] );
		$search     = Request::post( 'search', '', 'string' );

		Helper::setOption( 'schedules_rows_count_' . get_current_user_id(), $rows_count );

		if ( ! ( $page > 0 ) )
		{
			Helper::response( FALSE );
		}

		$all_count = DB::DB()->get_row( DB::DB()->prepare( "SELECT COUNT(0) AS c FROM " . DB::table( "schedules" ) . " WHERE user_id = %d AND blog_id = %d AND title LIKE %s", [
			get_current_user_id(),
			Helper::getBlogId(),
			"%$search%"
		] ), ARRAY_A );

		$pages  = ceil( $all_count[ 'c' ] / $rows_count );
		$offset = ( $page - 1 ) * $rows_count;

		$schedules = DB::DB()->get_results( DB::DB()->prepare( 'SELECT *, (SELECT COUNT(0) FROM `' . DB::table( 'feeds' ) . '` WHERE `schedule_id`=tb1.id and `is_sended`=1) AS `shares_count` FROM `' . DB::table( 'schedules' ) . '` tb1 WHERE `user_id`=%d AND `blog_id`=%d AND `title` LIKE %s ORDER BY `id` DESC LIMIT %d, %d', [
			get_current_user_id(),
			Helper::getBlogId(),
			"%$search%",
			$offset,
			$rows_count
		] ), ARRAY_A );

		$names_array1 = [
			'random2'   => fsp__( 'Randomly without dublicates' ),
			'random'    => fsp__( 'Randomly' ),
			'old_first' => fsp__( 'Start from the oldest to new posts' ),
			'new_first' => fsp__( 'Start from the latest to old posts' )
		];

		$names_array2 = [
			'all'              => fsp__( 'All posts' ),
			'this_week'        => fsp__( 'This week added posts' ),
			'previously_week'  => fsp__( 'Previous week added posts' ),
			'this_month'       => fsp__( 'This month added posts' ),
			'previously_month' => fsp__( 'Previous month added posts' ),
			'this_year'        => fsp__( 'This year added posts' ),
			'last_30_days'     => fsp__( 'Last 30 days' ),
			'last_60_days'     => fsp__( 'Last 60 days' ),
			'custom'           => fsp__( 'Custom date range' )
		];

		foreach ( $schedules as $key => $schedule )
		{

			if ( isset( $schedule[ 'sleep_time_start' ] ) and isset( $schedule[ 'sleep_time_end' ] ) )
			{
				$schedules[ $key ][ 'sleep_time_start' ] = Date::time( $schedule[ 'sleep_time_start' ] );
				$schedules[ $key ][ 'sleep_time_end' ]   = Date::time( $schedule[ 'sleep_time_end' ] );
			}

			$categoryFilter     = (int) $schedule[ 'category_filter' ];
			$categoryFiltersTxt = '';

			if ( ! empty( $categoryFilter ) )
			{
				$getCategoryNames   = get_term( $categoryFilter );
				$categoryFiltersTxt = ' , Category filter: <u>' . htmlspecialchars( $getCategoryNames->name ) . '</u>';
			}

			$addTxt = ( isset( $names_array1[ $schedule[ 'post_sort' ] ] ) ? ' , Order post by: ' . '<u>' . $names_array1[ $schedule[ 'post_sort' ] ] . '</u>' : '' );
			$addTxt .= ( isset( $names_array2[ $schedule[ 'post_date_filter' ] ] ) ? ' , Select posts added in: ' . '<u>' . $names_array2[ $schedule[ 'post_date_filter' ] ] . '</u>' : '' );

			$post_ids = $schedule[ 'save_post_ids' ];
			$post_ids = empty( $post_ids ) ? [] : explode( ',', $post_ids );
			if ( count( $post_ids ) == 1 )
			{
				$post_permalink                        = esc_html( get_permalink( reset( $post_ids ) ) );
				$schedules[ $key ][ 'post_permalink' ] = $post_permalink;
				$schedules[ $key ][ 'the_post_title' ] = esc_html( Helper::cutText( get_the_title( reset( $post_ids ) ) ) );
			}
			else
			{
				$schedules[ $key ][ 'post_type_text' ] = esc_html( ucfirst( $schedule[ 'post_type_filter' ] ) ) . '</u>' . $categoryFiltersTxt . $addTxt;
			}

			$selectedAccounts = explode( ',', $schedule[ 'share_on_accounts' ] );
			$accountList      = [];
			$nodeList         = [];

			foreach ( $selectedAccounts as $account )
			{
				$accountData = explode( ':', $account );

				if ( ! isset( $accountData[ 1 ] ) )
				{
					continue;
				}

				if ( $accountData[ 0 ] === 'account' )
				{
					$accountList[] = $accountData[ 1 ];
					continue;
				}

				$nodeList[] = $accountData[ 1 ];
			}

			$userId = get_current_user_id();
			$count1 = ! empty( $accountList ) ? DB::DB()->get_row( 'SELECT COUNT(0) AS c FROM ' . DB::table( 'accounts' ) . ' WHERE (is_public=1 OR user_id=' . $userId . ') AND id IN (' . implode( ',', $accountList ) . ')', ARRAY_A ) : [ 'c' => 0 ];
			$count2 = ! empty( $nodeList ) ? DB::DB()->get_row( 'SELECT COUNT(0) AS c FROM ' . DB::table( 'account_nodes' ) . ' WHERE (is_public=1 OR user_id=' . $userId . ') AND id IN (' . implode( ',', $nodeList ) . ')', ARRAY_A ) : [ 'c' => 0 ];

			$schedules[ $key ][ 'accounts_count' ] = $count1[ 'c' ] + $count2[ 'c' ];
		}

		$show_pages = Helper::calculateShowPages( $page, $pages );

		Helper::response( TRUE, [
			'schedules'     => $schedules,
			'namesArray1'   => $names_array1,
			'namesArray2'   => $names_array2,
			'pages'         => [
				'page_number'  => $show_pages,
				'current_page' => $page,
				'count'        => $pages
			],
			'scheduleCount' => (int) $all_count[ 'c' ]
		] );
	}
}
