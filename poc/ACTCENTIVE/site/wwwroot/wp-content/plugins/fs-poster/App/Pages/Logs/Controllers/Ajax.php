<?php

namespace FSPoster\App\Pages\Logs\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Ajax
{
	public function report1_data ()
	{
		$type    = Request::post( 'type', '', 'string' );
		$user_id = get_current_user_id();

		if ( ! in_array( $type, [
			'dayly',
			'monthly',
			'yearly'
		] ) )
		{
			exit();
		}

		$query = [
			'dayly'   => "SELECT CAST(send_time AS DATE) AS date , COUNT(0) AS c FROM " . DB::table( 'feeds' ) . " tb1 WHERE tb1.blog_id='" . Helper::getBlogId() . "' AND ( (node_type='account' AND (SELECT COUNT(0) FROM " . DB::table( 'accounts' ) . " tb2 WHERE tb2.blog_id='" . Helper::getBlogId() . "' AND tb2.id=tb1.node_id AND (tb2.user_id='$user_id' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$user_id')>0 OR tb2.is_public=1)) ) AND is_sended=1 GROUP BY CAST(send_time AS DATE)",
			'monthly' => "SELECT CONCAT(YEAR(send_time), '-', MONTH(send_time) , '-01') AS date , COUNT(0) AS c FROM " . DB::table( 'feeds' ) . " tb1 WHERE tb1.blog_id='" . Helper::getBlogId() . "' AND ( (node_type='account' AND (SELECT COUNT(0) FROM " . DB::table( 'accounts' ) . " tb2 WHERE tb2.blog_id='" . Helper::getBlogId() . "' AND tb2.id=tb1.node_id AND (tb2.user_id='$user_id' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$user_id')>0 OR tb2.is_public=1)) ) AND is_sended=1 AND send_time > ADDDATE(now(),INTERVAL -1 YEAR) GROUP BY YEAR(send_time), MONTH(send_time)",
			'yearly'  => "SELECT CONCAT(YEAR(send_time), '-01-01') AS date , COUNT(0) AS c FROM " . DB::table( 'feeds' ) . " tb1 WHERE tb1.blog_id='" . Helper::getBlogId() . "' AND ( (node_type='account' AND (SELECT COUNT(0) FROM " . DB::table( 'accounts' ) . " tb2 WHERE tb2.blog_id='" . Helper::getBlogId() . "' AND tb2.id=tb1.node_id AND (tb2.user_id='$user_id' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$user_id')>0 OR tb2.is_public=1)) ) AND is_sended=1 GROUP BY YEAR(send_time)"
		];

		$dateFormat = [
			'dayly'   => 'Y-m-d',
			'monthly' => 'Y M',
			'yearly'  => 'Y',
		];

		$dataSQL = DB::DB()->get_results( $query[ $type ], ARRAY_A );

		$labels = [];
		$datas  = [];
		foreach ( $dataSQL as $dInf )
		{
			$datas[]  = $dInf[ 'c' ];
			$labels[] = Date::format( $dateFormat[ $type ], $dInf[ 'date' ] );
		}

		Helper::response( TRUE, [
			'data'   => $datas,
			'labels' => $labels
		] );
	}

	public function report2_data ()
	{
		$type    = Request::post( 'type', '', 'string' );
		$user_id = get_current_user_id();

		if ( ! in_array( $type, [
			'dayly',
			'monthly',
			'yearly'
		] ) )
		{
			exit();
		}

		$query = [
			'dayly'   => "SELECT CAST(send_time AS DATE) AS date , SUM(visit_count) AS c FROM " . DB::table( 'feeds' ) . " tb1 WHERE tb1.blog_id='" . Helper::getBlogId() . "' AND ( (node_type='account' AND (SELECT COUNT(0) FROM " . DB::table( 'accounts' ) . " tb2 WHERE tb2.blog_id='" . Helper::getBlogId() . "' AND tb2.id=tb1.node_id AND (tb2.user_id='$user_id' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$user_id')>0 OR tb2.is_public=1)) ) AND is_sended=1 GROUP BY CAST(send_time AS DATE)",
			'monthly' => "SELECT CONCAT(YEAR(send_time), '-', MONTH(send_time) , '-01') AS date , SUM(visit_count) AS c FROM " . DB::table( 'feeds' ) . " tb1 WHERE tb1.blog_id='" . Helper::getBlogId() . "' AND ( (node_type='account' AND (SELECT COUNT(0) FROM " . DB::table( 'accounts' ) . " tb2 WHERE tb2.blog_id='" . Helper::getBlogId() . "' AND tb2.id=tb1.node_id AND (tb2.user_id='$user_id' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$user_id')>0 OR tb2.is_public=1)) ) AND send_time > ADDDATE(now(),INTERVAL -1 YEAR) AND is_sended=1 GROUP BY YEAR(send_time), MONTH(send_time)",
			'yearly'  => "SELECT CONCAT(YEAR(send_time), '-01-01') AS date , SUM(visit_count) AS c FROM " . DB::table( 'feeds' ) . " tb1 WHERE tb1.blog_id='" . Helper::getBlogId() . "' AND ( (node_type='account' AND (SELECT COUNT(0) FROM " . DB::table( 'accounts' ) . " tb2 WHERE tb2.blog_id='" . Helper::getBlogId() . "' AND tb2.id=tb1.node_id AND (tb2.user_id='$user_id' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM " . DB::table( 'account_nodes' ) . " tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$user_id')>0 OR tb2.is_public=1)) ) AND is_sended=1 GROUP BY YEAR(send_time)"
		];

		$dateFormat = [
			'dayly'   => 'Y-m-d',
			'monthly' => 'Y M',
			'yearly'  => 'Y',
		];

		$dataSQL = DB::DB()->get_results( $query[ $type ], ARRAY_A );

		$labels = [];
		$datas  = [];
		foreach ( $dataSQL as $dInf )
		{
			$datas[]  = $dInf[ 'c' ];
			$labels[] = Date::format( $dateFormat[ $type ], $dInf[ 'date' ] );
		}

		Helper::response( TRUE, [
			'data'   => $datas,
			'labels' => $labels
		] );
	}

	public function report3_data ()
	{
		$page           = Request::post( 'page', '1', 'num' );
		$scheduleId     = Request::post( 'schedule_id', '0', 'num' );
		$rows_count     = Request::post( 'rows_count', '4', 'int', [ '4', '8', '15' ] );
		$filter_results = Request::post( 'filter_results', 'all', 'string', [ 'all', 'error', 'ok' ] );
		$sn             = Request::post( 'sn', 'all', 'string', [
			'all',
			'fb',
			'instagram',
			'threads',
			'twitter',
			'planly',
			'linkedin',
			'pinterest',
			'telegram',
			'reddit',
			'youtube_community',
			'tumblr',
			'ok',
			'vk',
			'google_b',
			'medium',
			'wordpress',
			'webhook',
			'blogger',
			'plurk',
			'xing',
			'discord',
			'mastodon'
		] );
		$show_logs_of   = Request::post( 'show_logs_of', 'own', 'string', [ 'all', 'own' ] );

		Helper::setOption( 'show_logs_of', $show_logs_of );

		$feedId = Request::post( 'feed_id', 0, 'num' );

		$page = empty( $feedId ) ? $page : 1;

		if ( ! ( $page > 0 ) )
		{
			Helper::response( FALSE );
		}

		$query_add = '';

		if ( ! empty( $feedId ) )
		{
			$query_add = DB::DB()->prepare( 'AND id = %d', [
				$feedId
			] );
		}

		if ( $scheduleId > 0 )
		{
			$query_add = DB::DB()->prepare( ' AND schedule_id=%d', [
				( int ) $scheduleId
			] );
		}

		if ( $filter_results === 'error' || $filter_results === 'ok' )
		{
			$query_add .= DB::DB()->prepare( ' AND status = %s', [
				$filter_results
			] );
		}

		if ( ! empty( $sn ) && $sn !== 'all' )
		{
			$query_add .= DB::DB()->prepare( ' AND driver = %s', [
				$sn
			] );
		}

		$userId    = get_current_user_id();
		$user_sort = '';

		if ( $show_logs_of !== 'all' || ! current_user_can( 'administrator' ) )
		{
			$user_sort = DB::DB()->prepare( ' AND is_sended=1 AND ( user_id=%d OR ( user_id IS NULL AND ( ( node_type=\'account\' AND ( (SELECT COUNT(0) FROM ' . DB::table( 'accounts' ) . ' tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id=%d OR tb2.is_public=1))>0 OR node_id NOT IN (SELECT id FROM ' . DB::table( 'accounts' ) . ') ) ) OR (node_type<>\'account\' AND ( (SELECT COUNT(0) FROM ' . DB::table( 'account_nodes' ) . ' tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id=%d)>0 OR tb2.is_public=1) OR node_id NOT IN (SELECT id FROM ' . DB::table( 'account_nodes' ) . ') ) ) ) ) )', [
				$userId,
				$userId,
				$userId
			] );
		}

		$allCountQuery = DB::DB()->prepare( "SELECT COUNT(0) AS c FROM " . DB::table( 'feeds' ) . " tb1 WHERE blog_id=%d AND send_time < %s ", [
			Helper::getBlogId(),
			Date::dateTimeSQL()
		] );

		$allCount = DB::DB()->get_row( $allCountQuery . $user_sort . $query_add, ARRAY_A );
		$pages    = ceil( $allCount[ 'c' ] / $rows_count );

		Helper::setOption( 'logs_rows_count_' . get_current_user_id(), $rows_count );

		$offset     = ( $page - 1 ) * $rows_count;
		$feeds      = DB::DB()->get_results( DB::DB()->prepare( 'SELECT * FROM ' . DB::table( 'feeds' ) . ' tb1 WHERE blog_id=\'' . Helper::getBlogId() . '\'' . $user_sort . $query_add . " AND send_time < %s ORDER BY send_time DESC LIMIT %d , %d", [
			Date::dateTimeSQL(),
			$offset,
			$rows_count
		] ), ARRAY_A );
		$resultData = [];

		foreach ( $feeds as $feed )
		{
			$postInf = get_post( $feed[ 'post_id' ] );
			$node    = Helper::getNode( $feed );

			if ( $node && $feed[ 'node_type' ] === 'account' )
			{
				$node[ 'node_type' ] = 'account';
			}

			if ( $feed[ 'driver' ] === 'wordpress' )
			{
				$feed[ 'node_type' ] = 'website';
			}

			$icon = $this->getIcon( $feed[ 'driver' ] );

			if ( $feed[ 'driver' ] === 'google_b' )
			{
				$username = $node[ 'node_id' ];
			}
			else if ( $feed[ 'driver' ] === 'blogger' )
			{
				$username = $feed[ 'driver_post_id2' ];
			}
			else if ( $feed[ 'driver' ] === 'wordpress' )
			{
				//todo://bu yoxdu
				$username = isset( $node_info2[ 'options' ] ) ? $node_info2[ 'options' ] : '';
			}
			else
			{
				$username = isset( $node[ 'screen_name' ] ) ? $node[ 'screen_name' ] : ( isset( $node[ 'username' ] ) ? $node[ 'username' ] : '-' );
			}

			$hide_stats = in_array( $feed[ 'driver' ], [
				'planly',
				'linkedin',
				'telegram',
				'reddit',
				'youtube_community',
				'tumblr',
				'google_b',
				'medium',
				'wordpress',
				'blogger',
				'xing',
				'discord',
				'atlas'
			] );

			$hide_stats = $hide_stats || ( $feed[ 'driver' ] == 'instagram' && ! empty( $node[ 'account_id' ] ) ) || ( $feed[ 'driver' ] == 'pinterest' && empty( $options ) );

			$sharedFrom = $feed[ 'shared_from' ];

			if ( ! empty( $sharedFrom ) )
			{
				$sharedFromArray = [
					'manual_share'         => fsp__( 'Shared Manually' ),
					'direct_share'         => fsp__( 'Shared by the Direct Share' ),
					'schedule'             => fsp__( 'Shared by the Schedule Module' ),
					'auto_post'            => fsp__( 'Auto-posted' ),
					'manual_share_retried' => fsp__( 'Shared Manually (Retried)' ),
					'direct_share_retried' => fsp__( 'Shared by the Direct Share (Retried)' ),
					'schedule_retried'     => fsp__( 'Shared by the Schedule Module (Retried)' ),
					'auto_post_retried'    => fsp__( 'Auto-posted (Retried)' ),
				];

				if ( array_key_exists( $sharedFrom, $sharedFromArray ) )
				{
					$sharedFrom = $sharedFromArray[ $sharedFrom ];
				}
			}

			$wp_post_link = ! ( $postInf->post_type == 'fs_post' || $postInf->post_type == 'fs_post_tmp' ) ? ( site_url() . '/?p=' . $feed[ 'post_id' ] ) : ( trim( get_admin_url(), '/' ) . '/admin.php?page=fs-poster-share&post_id=' . $feed[ 'post_id' ] );

			//if driver is discord or driver is planly and driver_post_id2 is not empty set driver_post_id2 else driver_post_id to variable
			$postLinkParam_post_id = $feed[ 'driver' ] == 'discord' || ( $feed[ 'driver' ] == 'planly' && ! empty( $feed[ 'driver_post_id2' ] ) ) ? $feed[ 'driver_post_id2' ] : $feed[ 'driver_post_id' ];

			if ( $feed[ 'driver' ] == 'google_b' )
			{
				$account                = DB::fetch( 'account_nodes', $node[ 'account_id' ] );
				$postLinkParam_username = ! empty( $account[ 'options' ] ) ? NULL : $node[ 'node_id' ];
			}

			$postLinkParam_username = $feed[ 'driver' ] == 'google_b' && empty( $node[ 'options' ] ) ? NULL : $username;
			$postLinkParam_username = $feed[ 'driver' ] == 'wordpress' ? $node[ 'options' ] : $postLinkParam_username;

			if ( $feed[ 'driver' ] == 'mastodon' && isset( $node[ 'options' ] ) )
			{
				$mastodonServer         = json_decode( $node[ 'options' ], TRUE )[ 'server' ];
				$mastodonServer         = trim( $mastodonServer, '/' );
				$postLinkParam_username = $mastodonServer . '/@' . $username;
			}
			$postLink = Helper::postLink( $postLinkParam_post_id, $feed[ 'driver' ] . ( $feed[ 'driver' ] === 'instagram' ? $feed[ 'feed_type' ] : '' ), $postLinkParam_username );

			$resultData[] = [
				'id'           => $feed[ 'id' ],
				'name'         => $node ? htmlspecialchars( $node[ 'name' ] ) : fsp__( 'Account deleted' ),
				'username'     => $node[ 'username' ],
				'post_id'      => htmlspecialchars( $feed[ 'driver_post_id' ] ),
				'post_title'   => htmlspecialchars( isset( $postInf->post_title ) ? $postInf->post_title : 'Deleted' ),
				'cover'        => Helper::profilePic( $node ),
				'profile_link' => Helper::profileLink( $node ),
				'is_sended'    => $feed[ 'is_sended' ],
				'post_link'    => $postLink,
				'wp_post_link' => $wp_post_link,
				'status'       => $feed[ 'status' ],
				'error_msg'    => $feed[ 'error_msg' ],
				'driver'       => $feed[ 'driver' ],
				'icon'         => $icon,
				'node_type'    => $feed[ 'node_type' ],
				'feed_type'    => ucfirst( (string) $feed[ 'feed_type' ] ),
				'date'         => Date::dateTimeSQL( $feed[ 'send_time' ] ),
				'wp_post_id'   => $feed[ 'post_id' ],
				'hide_stats'   => $hide_stats,
				'shared_from'  => $sharedFrom,
				'is_deleted'   => ! $node
			];
		}

		$showPages = Helper::calculateShowPages( $page, $pages );

		Helper::response( TRUE, [
			'data'  => $resultData,
			'pages' => [
				'page_number'  => $showPages,
				'current_page' => $page,
				'count'        => $pages
			],
			'total' => $allCount[ 'c' ] ?: 0
		] );
	}

	public function fs_clear_logs ()
	{
		$scheduleId       = Request::post( 'schedule_id', '0', 'num' );
		$selectedAccounts = Request::post( 'selected_accounts', [], 'array' );
		$type             = Request::post( 'type', 'all', 'string', [
			'all',
			'only_successful_logs',
			'only_errors',
			'only_selected_logs'
		] );

		$deleteBySchedule = $scheduleId > 0 ? ' AND schedule_id=\'' . (int) $scheduleId . '\'' : '';

		$userId = get_current_user_id();

		$deleteQuery = "DELETE FROM " . DB::table( 'feeds' ) . ' WHERE blog_id=\'' . Helper::getBlogId() . '\' AND (is_sended=1 OR (send_time+INTERVAL 1 DAY)<NOW()) AND ( user_id=\'' . $userId . '\' OR ( user_id IS NULL AND ( (node_type=\'account\' AND (SELECT COUNT(0) FROM ' . DB::table( 'accounts' ) . ' tb2 WHERE tb2.blog_id=\'' . Helper::getBlogId() . '\' AND tb2.id=' . DB::table( 'feeds' ) . '.node_id AND (tb2.user_id=\'' . $userId . '\' OR tb2.is_public=1))>0) OR (node_type<>\'account\' AND (SELECT COUNT(0) FROM ' . DB::table( 'account_nodes' ) . ' tb2 WHERE tb2.id=' . DB::table( 'feeds' ) . '.node_id AND (tb2.user_id=\'' . $userId . '\')>0 OR tb2.is_public=1)) ))) ' . $deleteBySchedule;

		if ( $type === 'all' )
		{
			DB::DB()->query( $deleteQuery . ' OR user_id IS NULL OR user_id=0' );
		}
		else if ( $type === 'only_successful_logs' )
		{
			DB::DB()->query( $deleteQuery . ' AND status="ok" ' );
		}
		else if ( $type === 'only_errors' )
		{
			DB::DB()->query( $deleteQuery . ' AND status="error" ' );
		}
		else if ( $type === 'only_selected_logs' )
		{
			if ( ! empty( $selectedAccounts ) )
			{
				$set = implode( ', ', $selectedAccounts );

				DB::DB()->query( $deleteQuery . ' AND id IN(' . $set . ') ' );
			}
			else
			{
				Helper::response( FALSE, fsp__( 'No logs are selected!' ) );
			}
		}
		else
		{
			Helper::response( FALSE );
		}

		Helper::response( TRUE );
	}

	public function export_logs_to_csv ()
	{
		$scheduleId     = Request::post( 'schedule_id', '0', 'num' );
		$filter_results = Request::post( 'filter_results', 'all', 'string', [ 'all', 'error', 'ok' ] );
		$sn             = Request::post( 'sn', 'all', 'string', [
			'all',
			'fb',
			'instagram',
			'threads',
			'twitter',
			'planly',
			'linkedin',
			'pinterest',
			'telegram',
			'reddit',
			'youtube_community',
			'tumblr',
			'ok',
			'vk',
			'google_b',
			'medium',
			'wordpress',
			'webhook',
			'blogger',
			'plurk',
			'xing',
			'discord',
			'mastodon'
		] );
		$show_logs_of   = Helper::getOption( 'show_logs_of', 'own' );

		$query_add = '';

		if ( $scheduleId > 0 )
		{
			$query_add = ' AND schedule_id=\'' . (int) $scheduleId . '\'';
		}

		if ( $filter_results === 'error' || $filter_results === 'ok' )
		{
			$query_add .= ' AND status = \'' . $filter_results . '\'';
		}

		if ( ! empty( $sn ) && $sn !== 'all' )
		{
			$query_add .= ' AND driver = \'' . $sn . '\'';
		}

		$userId = get_current_user_id();

		if ( $show_logs_of === 'all' && current_user_can( 'administrator' ) )
		{
			$user_sort = '';
		}
		else
		{
			$user_sort = ' AND is_sended=1 AND ( user_id=\'' . $userId . '\' OR ( user_id IS NULL AND ( ( node_type=\'account\' AND ( (SELECT COUNT(0) FROM ' . DB::table( 'accounts' ) . ' tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id=\'' . $userId . '\' OR tb2.is_public=1))>0 OR node_id NOT IN (SELECT id FROM ' . DB::table( 'accounts' ) . ') ) ) OR (node_type<>\'account\' AND ( (SELECT COUNT(0) FROM ' . DB::table( 'account_nodes' ) . ' tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id=\'' . $userId . '\')>0 OR tb2.is_public=1) OR node_id NOT IN (SELECT id FROM ' . DB::table( 'account_nodes' ) . ') ) ) ) ) )';
		}

		$feeds = DB::DB()->get_results( 'SELECT * FROM ' . DB::table( 'feeds' ) . ' tb1 WHERE blog_id=\'' . Helper::getBlogId() . '\'' . $user_sort . $query_add . " ORDER BY send_time DESC", ARRAY_A );

		$f         = fopen( 'php://memory', 'w' );
		$delimiter = ',';
		$filename  = 'FS-Poster_logs_' . date( 'Y-m-d' ) . '.csv';
		$fields    = [
			//fsp__( 'ID' ),
			fsp__( 'Account Name' ),
			fsp__( 'Account Link' ),
			fsp__( 'Date' ),
			fsp__( 'Post Link' ),
			fsp__( 'Publication Link' ),
			fsp__( 'Social Network' ),
			fsp__( 'Share Method' ),
			fsp__( 'Status' ),
			fsp__( 'Error Message' )
		];

		fputcsv( $f, $fields, $delimiter );

		$networks = [
			'fb'                => 'Facebook',
			'instagram'         => 'Instagram',
			'threads'           => 'Threads',
			'twitter'           => 'Twitter',
			'planly'            => 'Planly',
			'linkedin'          => 'LinkedIn',
			'pinterest'         => 'Pinterest',
			'telegram'          => 'Telegram',
			'reddit'            => 'Reddit',
			'youtube_community' => 'Youtube Community',
			'tumblr'            => 'Tumblr',
			'ok'                => 'Odnoklassniki',
			'vk'                => 'VKontakte',
			'google_b'          => 'Google Business Profile',
			'medium'            => 'Medium',
			'wordpress'         => 'WordPress',
			'webhook'           => 'Webhook',
			'plurk'             => 'Plurk',
			'xing'              => 'Xing',
			'discord'           => 'Discord',
			'mastodon'          => 'Mastodon',
		];

		foreach ( $feeds as $feed )
		{
			$postType      = get_post_type( $feed[ 'post_id' ] );
			$nodeInfoTable = $feed[ 'node_type' ] === 'account' ? 'accounts' : 'account_nodes';
			$nodeInfo      = DB::fetch( $nodeInfoTable, $feed[ 'node_id' ] );
			$nodeInfo2     = Helper::getAccessToken( $feed[ 'node_type' ], $feed[ 'node_id' ] );

			if ( $feed[ 'driver' ] === 'google_b' )
			{
				$username = $nodeInfo[ 'node_id' ];
			}
			else if ( $feed[ 'driver' ] === 'blogger' )
			{
				$username = $feed[ 'driver_post_id2' ];
			}
			else if ( $feed[ 'driver' ] === 'wordpress' )
			{
				$username = isset( $nodeInfo2[ 'options' ] ) ? $nodeInfo2[ 'options' ] : '';
			}
			else
			{
				$username = isset( $nodeInfo[ 'screen_name' ] ) ? $nodeInfo[ 'screen_name' ] : ( isset( $nodeInfo[ 'username' ] ) ? $nodeInfo[ 'username' ] : '-' );
			}

			if ( $feed[ 'status' ] === 'ok' )
			{
				$status = fsp__( 'SUCCESS' );
			}
			else if ( $feed[ 'status' ] === 'error' )
			{
				$status = fsp__( 'ERROR' );
			}
			else if ( $feed[ 'status' ] === 'processing' )
			{
				$status = fsp__( 'PROCESSING' );
			}
			else
			{
				$status = fsp__( 'NOT SENT' );
			}

			$postLinkParam_post_id  = $feed[ 'driver' ] == 'discord' || ( $feed[ 'driver' ] == 'planly' && ! empty( $feed[ 'driver_post_id2' ] ) ) ? $feed[ 'driver_post_id2' ] : $feed[ 'driver_post_id' ];
			$postLinkParam_username = $feed[ 'driver' ] == 'google_b' && empty( $nodeInfo[ 'options' ] ) ? NULL : $username;
			$postLinkParam_username = $feed[ 'driver' ] == 'wordpress' ? $nodeInfo[ 'options' ] : $postLinkParam_username;
			$postLink               = Helper::postLink( $postLinkParam_post_id, $feed[ 'driver' ] . ( $feed[ 'driver' ] === 'instagram' ? $feed[ 'feed_type' ] : '' ), $postLinkParam_username );

			$arr = [
				//$feed[ 'id' ],
				$nodeInfo ? htmlspecialchars( $nodeInfo[ 'name' ] ) : fsp__( 'Account deleted' ),
				$nodeInfo ? Helper::profileLink( $nodeInfo ) : '',
				$feed[ 'send_time' ],
				$postType !== 'fs_post_tmp' ? site_url() . '/?p=' . $feed[ 'post_id' ] : '',
				$postLink,
				$networks[ $feed[ 'driver' ] ],
				$feed[ 'shared_from' ],
				$status,
				$feed[ 'error_msg' ]
			];

			fputcsv( $f, $arr, $delimiter );
		}

		fseek( $f, 0 );

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		ob_start();
		fpassthru( $f );

		$file = ob_get_clean();
		$data = [
			'file'     => 'data:application/vnd.ms-excel;base64,' . base64_encode( $file ),
			'filename' => $filename
		];

		fclose( $f );

		Helper::response( TRUE, $data );
	}

	public function get_insights ()
	{
		$feedId = Request::post( 'feed_id', 0, 'num' );

		if ( empty( $feedId ) )
		{
			Helper::response( FALSE );
		}

		$feed = DB::DB()->get_row( DB::DB()->prepare( "SELECT * FROM " . DB::table( 'feeds' ) . ' tb1 WHERE blog_id= %d AND id = %d', [
			Helper::getBlogId(),
			$feedId
		] ), ARRAY_A );
		$node = Helper::getNode( $feed );

		if ( $node && $feed[ 'node_type' ] === 'account' )
		{
			$node[ 'node_type' ] = 'account';
		}

		if ( $feed[ 'driver' ] === 'wordpress' )
		{
			$feed[ 'node_type' ] = 'website';
		}

		$insights = [
			'like'     => 0,
			'details'  => '',
			'comments' => 0,
			'shares'   => 0,
		];

		if ( ! empty( $feed[ 'driver_post_id' ] ) )
		{
			//todo://bu method-un adi duzgun deyil
			$node2 = Helper::getAccessToken( $feed[ 'node_type' ], $feed[ 'node_id' ] );

			$insights = apply_filters( 'fsp_get_insights_' . $feed[ 'driver' ], $insights, $feed, $node2 );

			$insights[ 'hits' ] = $feed[ 'visit_count' ];
		}

		Helper::response( TRUE, [
			'insights' => $insights
		] );
	}
}
