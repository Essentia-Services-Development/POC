<?php

namespace FSPoster\App\Pages\Schedules\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Popup
{
	private function load_assets ()
	{
		wp_enqueue_script( 'fsp-logs' );

		wp_enqueue_style( 'fsp-logs', Pages::asset( 'Logs', 'css/fsp-logs.css' ), [ 'fsp-ui' ], NULL );
	}

	public function add_node_to_group ()
	{
		$group_id = Request::post( 'group_id', NULL, 'num' );
		$nodes    = Pages::action( 'Accounts', 'get_nodes', $group_id );

		$data = [
			'nodes'    => $nodes,
			'group_id' => $group_id,
		];

		Pages::modal( 'Accounts', 'groups/add_node_to_group', $data );
	}

	public function add_schedule ()
	{
		$is_direct_share_tab = Request::post( 'is_direct_share_tab', 0, 'int' ) === 1;
		$account_and_nodes   = Request::post( 'nodes', NULL, 'array' );
		$group_id            = Request::post( 'group_id', NULL, 'num' );
		$instagramPinThePost = Request::post( 'instagram_pin_the_post', 0, 'num', [ 0, 1 ] );

		$group_ids = is_null( $group_id ) ? [] : [ $group_id ];

		$account_ids = [];
		$node_ids    = [];

		if ( ! is_null( $account_and_nodes ) )
		{
			foreach ( $account_and_nodes as $accountNodeInf )
			{
				if ( empty( $accountNodeInf ) )
				{
					continue;
				}

				$accountNodeInf = explode( ':', $accountNodeInf );

				if ( ! isset( $accountNodeInf[ 2 ] ) )
				{
					continue;
				}

				if ( $accountNodeInf[ 0 ] === 'fsp' )
				{
					$group_ids[] = (int) $accountNodeInf[ 2 ];
				}
				else if ( $accountNodeInf[ 1 ] === 'account' )
				{
					$account_ids[] = (int) $accountNodeInf[ 2 ];
				}
				else
				{
					$node_ids[] = (int) $accountNodeInf[ 2 ];
				}
			}
		}

		$group_nodes    = [];
		$group_accounts = [];
		$active_nodes   = [];
		$accounts       = [];

		if ( ! empty( $group_ids ) )
		{
			$group_ids = array_unique( $group_ids );

			$in = '(' . implode( ',', $group_ids ) . ')';

			$group_accounts = DB::DB()->get_results( DB::DB()->prepare( "
					SELECT 
						tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type 
					FROM " . DB::table( 'accounts' ) . " tb2
					LEFT JOIN " . DB::table( 'account_status' ) . " tb1 ON tb2.id=tb1.account_id AND tb1.user_id=%d
					WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d AND tb2.id IN (SELECT gdt.node_id FROM `" . DB::table( 'account_groups_data' ) . "` gdt WHERE gdt.group_id IN $in AND gdt.node_type='account')
					ORDER BY name", [ get_current_user_id(), get_current_user_id(), Helper::getBlogId() ] ), ARRAY_A );

			$group_accounts = empty( $group_accounts ) ? [] : $group_accounts;

			$group_nodes = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT 
					tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name 
				FROM " . DB::table( 'account_nodes' ) . " tb2
				LEFT JOIN " . DB::table( 'account_node_status' ) . " tb1 ON tb2.id=tb1.node_id AND tb1.user_id=%d
				WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d AND tb2.id IN (SELECT gdt.node_id FROM `" . DB::table( 'account_groups_data' ) . "` gdt WHERE gdt.group_id=$in AND gdt.node_type='node')
				ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name", [
				get_current_user_id(),
				get_current_user_id(),
				Helper::getBlogId()
			] ), ARRAY_A );

			$group_nodes = empty( $group_nodes ) ? [] : $group_nodes;
		}

		if ( ! empty( $account_ids ) )
		{
			$account_ids = implode( ',', $account_ids );
			$accounts    = DB::DB()->get_results( DB::DB()->prepare( "
					SELECT 
						tb2.*, IFNULL(tb1.filter_type, 'no') AS filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type 
					FROM " . DB::table( 'accounts' ) . " tb2
					LEFT JOIN " . DB::table( 'account_status' ) . " tb1 ON tb2.id=tb1.account_id AND tb1.user_id=%d
					WHERE (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d AND tb2.id IN ({$account_ids})
					ORDER BY name", [ get_current_user_id(), get_current_user_id(), Helper::getBlogId() ] ), ARRAY_A );
		}

		if ( ! empty( $node_ids ) )
		{
			$node_ids     = implode( ',', $node_ids );
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

		if ( is_null( $account_and_nodes ) && empty( $group_ids ) )
		{
			$accounts = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type 
				FROM " . DB::table( 'account_status' ) . " tb1
				INNER JOIN " . DB::table( 'accounts' ) . " tb2 ON tb2.id=tb1.account_id
				WHERE tb1.user_id=%d AND tb2.blog_id=%d
				ORDER BY name", [ get_current_user_id(), Helper::getBlogId() ] ), ARRAY_A );

			$active_nodes = DB::DB()->get_results( DB::DB()->prepare( "
				SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM " . DB::WPtable( 'terms', TRUE ) . " WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name FROM " . DB::table( 'account_node_status' ) . " tb1
				LEFT JOIN " . DB::table( 'account_nodes' ) . " tb2 ON tb2.id=tb1.node_id
				WHERE tb1.user_id=%d AND tb2.blog_id=%d
				ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name", [
				get_current_user_id(),
				Helper::getBlogId()
			] ), ARRAY_A );
		}

		$active_nodes = array_merge( $accounts, $active_nodes, $group_nodes, $group_accounts );

		foreach ( $active_nodes as $aKey => $node_info )
		{
			if ( $node_info[ 'filter_type' ] === 'no' )
			{
				$titleText = '';
			}
			else
			{
				$titleText = ( $node_info[ 'filter_type' ] === 'in' ? fsp__( 'Only the posts of the selected categories, tags, etc. will be shared:' ) : fsp__( 'The posts of the selected categories, tags, etc. will not be shared:' ) ) . "\n";
				$titleText .= str_replace( ',', ', ', $node_info[ 'categories_name' ] );
			}

			$active_nodes[ $aKey ][ 'title_text' ] = $titleText;
		}

		$post_types       = [];
		$allowedPostTypes = explode( '|', Helper::getOption( 'allowed_post_types', 'post|page|attachment|product' ) );

		foreach ( get_post_types( [], 'object' ) as $post_type )
		{
			if ( ! in_array( $post_type->name, $allowedPostTypes ) || in_array( $post_type->name, [ 'fs_post', 'fs_post_tmp', 'attachment' ] ) )
			{
				continue;
			}

			$post_types[ $post_type->name ] = $post_type->label;
		}

		$custom_messages = [
			'fb'                => Helper::getOption( 'post_text_message_fb', "{title}" ),
			'fb_h'              => Helper::getOption( 'post_text_message_fb_h', "{title}" ),
			'instagram'         => Helper::getOption( 'post_text_message_instagram', "{title}" ),
			'instagram_h'       => Helper::getOption( 'post_text_message_instagram_h', "{title}" ),
			'threads'           => Helper::getOption( 'post_text_message_threads', "{title}" ),
			'twitter'           => Helper::getOption( 'post_text_message_twitter', "{title}" ),
			'planly'            => Helper::getOption( 'post_text_message_planly', "{content_full}" ),
			'linkedin'          => Helper::getOption( 'post_text_message_linkedin', "{title}" ),
			'pinterest'         => Helper::getOption( 'post_text_message_pinterest', "{content_short_497}" ),
			'telegram'          => Helper::getOption( 'post_text_message_telegram', "{title}" ),
			'reddit'            => Helper::getOption( 'post_text_message_reddit', "{title}" ),
			'youtube_community' => Helper::getOption( 'post_text_message_youtube_community', "{content_full}" ),
			'tumblr'            => Helper::getOption( 'post_text_message_tumblr', "<img src='{featured_image_url}'>\n\n{content_full}" ),
			'ok'                => Helper::getOption( 'post_text_message_ok', "{title}" ),
			'vk'                => Helper::getOption( 'post_text_message_vk', "{title}" ),
			'google_b'          => Helper::getOption( 'post_text_message_google_b', "{title}" ),
			'medium'            => Helper::getOption( 'post_text_message_medium', "<img src='{featured_image_url}'>\n\n{content_full}\n\n<a href='{link}'>{link}</a>" ),
			'wordpress'         => Helper::getOption( 'post_text_message_wordpress', "{content_full}" ),
			'blogger'           => Helper::getOption( 'post_text_message_blogger', "<img src='{featured_image_url}'>\n\n{content_full} \n\n<a href='{link}'>{link}</a>" ),
			'plurk'             => Helper::getOption( 'post_text_message_plurk', "{title}\n\n{featured_image_url}\n\n{content_short_200}" ),
			'xing'              => Helper::getOption( 'post_text_message_xing', "{content_full}" ),
			'discord'           => Helper::getOption( 'post_text_message_discord', "{title}" ),
			'mastodon'          => Helper::getOption( 'post_text_message_mastodon', "{title}" ),
		];
		$post_ids        = Request::post( 'post_ids', '', 'string' );
		$schedule_name   = '';
		$post_ids_count  = 0;

		if ( ! empty( $post_ids ) )
		{
			$post_ids       = explode( ',', $post_ids );
			$post_ids_count = count( $post_ids );

			if ( $post_ids_count == 1 )
			{
				$onePostId  = reset( $post_ids );
				$onePostInf = get_post( $onePostId, ARRAY_A );

				$schedule_name = ! empty( $onePostInf[ 'post_title' ] ) ? fsp__('Scheduled post: "%s"', [ Helper::cutText( $onePostInf[ 'post_title' ] ) ] ) : '';
			}
			else
			{
				$schedule_name = 'Schedule ( ' . $post_ids_count . ' posts )';
			}

			$post_ids = implode( ',', $post_ids );
		}

		$term_id = (int) Request::post( 'term_id', '', 'num' );

		if ( ! empty( $term_id ) )
		{
			$term          = get_term( $term_id );
			$schedule_name = 'Scheduled category: "' . $term->name . '"';
		}

		if ( ! empty( $group_id ) )
		{
			$account_group_info = DB::fetch( 'account_groups', [ 'id' => $group_id ] );
			$group_name         = isset( $account_group_info[ 'name' ] ) ? $account_group_info[ 'name' ] : '';
			$schedule_name      = 'Scheduled account group: "' . $group_name . '"';
		}

		foreach ( $active_nodes  as &$active_node )
		{
			$subName = ucfirst( $active_node[ 'driver' ] ) . ' > ';

			if( $active_node['driver'] === 'webhook' )
			{
				$active_node['subName'] = $subName . $active_node[ 'username' ];
			}
			else if( $active_node['driver'] === 'mastodon' )
			{
				$active_node['subName'] = $subName . json_decode($active_node['options'], TRUE)['server'];
			}
			else
			{
				$active_node['subName'] = $subName . $active_node[ 'node_type' ];
			}
		}

		Pages::modal( 'Schedules', 'add', [
			'is_direct_share_tab'     => $is_direct_share_tab,
			'name'                    => $schedule_name,
			'activeNodes'             => $active_nodes,
			'postTypes'               => $post_types,
			'customMessages'          => $custom_messages,
			'instagramPinThePost'     => $instagramPinThePost,
			'isAutoRescheduled'       => 0,
			'isAutoRescheduleEnabled' => 0,
			'autoRescheduleCount'     => 1,
			'title'                   => fsp__( 'ADD A NEW SCHEDULE' ),
			'btn_title'               => fsp__( 'ADD A SCHEDULE' ),
			'post_ids'                => $post_ids,
			'term_id'                 => $term_id,
			'post_ids_count'          => $post_ids_count,
			'sn_list'                 => [
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
				'mastodon',
			]
		] );
	}

	public function edit_schedule ()
	{
		$scheduleId = Request::post( 'schedule_id', 0, 'int' );

		$schedule_info = DB::fetch( 'schedules', $scheduleId );

		if ( ! $schedule_info )
		{
			Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
		}

		if ( $schedule_info[ 'interval' ] % 1440 === 0 )
		{
			$schedule_info[ 'interval' ]      = $schedule_info[ 'interval' ] / 1440;
			$schedule_info[ 'interval_type' ] = 1440;
		}
		else if ( $schedule_info[ 'interval' ] % 60 === 0 )
		{
			$schedule_info[ 'interval' ]      = $schedule_info[ 'interval' ] / 60;
			$schedule_info[ 'interval_type' ] = 60;
		}
		else
		{
			$schedule_info[ 'interval_type' ] = 1;
		}

		$account_and_nodes = explode( ',', $schedule_info[ 'share_on_accounts' ] );
		$account_ids       = [];
		$node_ids          = [];
		foreach ( $account_and_nodes as $accountNodeInf )
		{
			if ( empty( $accountNodeInf ) )
			{
				continue;
			}

			$accountNodeInf = explode( ':', $accountNodeInf );

			if ( ! isset( $accountNodeInf[ 1 ] ) )
			{
				continue;
			}

			if ( $accountNodeInf[ 0 ] === 'account' )
			{
				$account_ids[] = (int) $accountNodeInf[ 1 ];
			}
			else
			{
				$node_ids[] = (int) $accountNodeInf[ 1 ];
			}
		}

		$account_ids = implode( ',', $account_ids );
		$node_ids    = implode( ',', $node_ids );

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

		$active_nodes = array_merge( $accounts, $active_nodes );
		foreach ( $active_nodes as $aKey => $active_node )
		{
			if ( $active_node[ 'filter_type' ] === 'no' )
			{
				$titleText = '';
			}
			else
			{
				$titleText = ( $active_node[ 'filter_type' ] === 'in' ? fsp__( 'Only the posts of the selected categories, tags, etc. will be shared:' ) : fsp__( 'The posts of the selected categories, tags, etc. will not be shared:' ) ) . "\n";
				$titleText .= str_replace( ',', ', ', $active_node[ 'categories_name' ] );
			}

			$active_nodes[ $aKey ][ 'title_text' ] = $titleText;
		}

		/*
		 * Fetch all Custom Post types...
		 */
		$post_types       = [];
		$allowedPostTypes = explode( '|', Helper::getOption( 'allowed_post_types', 'post|page|attachment|product' ) );

		foreach ( get_post_types( [], 'object' ) as $post_type )
		{
			if ( ! in_array( $post_type->name, $allowedPostTypes ) || in_array( $post_type->name, [ 'fs_post', 'fs_post_tmp', 'attachment' ] ) )
			{
				continue;
			}

			$post_types[ $post_type->name ] = $post_type->label;
		}

		$default_custom_messages = [
			'fb'          => Helper::getOption( 'post_text_message_fb', "{title}" ),
			'fb_h'        => Helper::getOption( 'post_text_message_fb_h', "{title}" ),
			'instagram'   => Helper::getOption( 'post_text_message_instagram', "{title}" ),
			'instagram_h' => Helper::getOption( 'post_text_message_instagram_h', "{title}" ),
			'threads'     => Helper::getOption( 'post_text_message_threads', "{title}" ),
			'twitter'     => Helper::getOption( 'post_text_message_twitter', "{title}" ),
			'linkedin'    => Helper::getOption( 'post_text_message_linkedin', "{title}" ),
			'tumblr'      => Helper::getOption( 'post_text_message_tumblr', "<img src='{featured_image_url}'>\n\n{content_full}" ),
			'reddit'      => Helper::getOption( 'post_text_message_reddit', "{title}" ),
			'vk'          => Helper::getOption( 'post_text_message_vk', "{title}" ),
			'ok'          => Helper::getOption( 'post_text_message_ok', "{title}" ),
			'pinterest'   => Helper::getOption( 'post_text_message_pinterest', "{content_short_497}" ),
			'plurk'       => Helper::getOption( 'post_text_message_plurk', "{title}\n\n{featured_image_url}\n\n{content_short_200}" ),
			'google_b'    => Helper::getOption( 'post_text_message_google_b', "{title}" ),
			'blogger'     => Helper::getOption( 'post_text_message_blogger', "<img src='{featured_image_url}'>\n\n{content_full} \n\n<a href='{link}'>{link}</a>" ),
			'telegram'    => Helper::getOption( 'post_text_message_telegram', "{title}" ),
			'medium'      => Helper::getOption( 'post_text_message_medium', "<img src='{featured_image_url}'>\n\n{content_full}\n\n<a href='{link}'>{link}</a>" ),
			'discord'     => Helper::getOption( 'post_text_message_discord', "{title}" ),
			'mastodon'    => Helper::getOption( 'post_text_message_mastodon', "{title}" ),
		];

		$custom_messages = array_merge( $default_custom_messages, json_decode( $schedule_info[ 'custom_post_message' ], TRUE ) );

		if ( $schedule_info[ 'status' ] === 'finished' )
		{
			$schedule_info[ 'title' ] = 'Re: ' . $schedule_info[ 'title' ];
		}

		$post_ids_count = empty( $schedule_info[ 'save_post_ids' ] ) ? 0 : count( explode( ',', $schedule_info[ 'save_post_ids' ] ) );

		$isAllTimes = $schedule_info[ 'filter_posts_date_range_from' ] < Date::dateTimeSQL( '-995 years' ) && $schedule_info[ 'filter_posts_date_range_to' ] > Date::dateTimeSQL( '+ 995 years' );

		$scheduleData = empty( $schedule_info[ 'data' ] ) ? '{}' : $schedule_info[ 'data' ];
		$scheduleData = json_decode( $scheduleData, TRUE );

		$instagramPinThePost     = empty( $scheduleData[ 'instagram_pin_the_post' ] ) ? 0 : 1;
		$isAutoRescheduled       = empty( $scheduleData[ 'parentScheduleID' ] ) ? 0 : 1;
		$isAutoRescheduleEnabled = empty( $scheduleData[ 'autoRescheduleEnabled' ] ) ? 0 : 1;
		$autoRescheduleCount     = !isset( $scheduleData[ 'autoRescheduleCount' ] ) ? 1 : $scheduleData[ 'autoRescheduleCount' ];

		Pages::modal( 'Schedules', 'add', [
			'id'                      => $scheduleId,
			'info'                    => $schedule_info,
			'activeNodes'             => $active_nodes,
			'is_all_times'            => $isAllTimes,
			'postTypes'               => $post_types,
			'customMessages'          => $custom_messages,
			'title'                   => $schedule_info[ 'status' ] === 'finished' ? fsp__( 'RE-SCHEDULE' ) : fsp__( 'EDIT SCHEDULE' ),
			'btn_title'               => $schedule_info[ 'status' ] === 'finished' ? fsp__( 'RE-SCHEDULE' ) : fsp__( 'SAVE THE SCHEDULE' ),
			'post_ids_count'          => $post_ids_count,
			'instagramPinThePost'     => $instagramPinThePost,
			'isAutoRescheduled'       => $isAutoRescheduled,
			'isAutoRescheduleEnabled' => $isAutoRescheduleEnabled,
			'autoRescheduleCount'     => $autoRescheduleCount,
			'sn_list'                 => [
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
				'mastodon',
			]
		] );
	}

	public function posts_list ()
	{
		$this->load_assets();
		$data = Pages::action( 'Logs', 'get_logs' );

		Pages::modal( 'Schedules', 'posts_list', $data );
	}

	public function edit_wp_native_schedule ()
	{
		$post_id = Request::post( 'post_id', 0, 'int' );

		if ( $post_id > 0 )
		{
			$feeds = DB::fetchAll( 'feeds', [
				'post_id'   => $post_id,
				'is_sended' => 0,
				'blog_id'   => Helper::getBlogId()
			] );

			$info               = [
				'post_id'   => $post_id,
				'send_time' => NULL
			];
			$account_ids        = [];
			$node_ids           = [];
			$customPostMessages = [];

			if ( $feeds )
			{
				foreach ( $feeds as $feed )
				{
					if ( is_null( $info[ 'send_time' ] ) )
					{
						$info[ 'send_time' ] = $feed[ 'send_time' ];
					}

					if ( $feed[ 'node_type' ] === 'account' )
					{
						$account_ids[] = $feed[ 'node_id' ];
					}
					else
					{
						$node_ids[] = $feed[ 'node_id' ];
					}

					if ( ! array_key_exists( $feed[ 'driver' ], $customPostMessages ) )
					{
						$customPostMessages[ $feed[ 'driver' ] ] = $feed[ 'custom_post_message' ];
					}
				}

				if ( is_null( $info[ 'send_time' ] ) )
				{
					Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
				}
			}
			else
			{
				$post = get_post( $post_id );

				if ( $post )
				{
					$info[ 'send_time' ] = Date::dateTimeSQL( $post->post_date, '+1 minute' );
				}
				else
				{
					Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
				}
			}

			$account_ids = implode( ',', $account_ids );
			$node_ids    = implode( ',', $node_ids );

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

			$active_nodes = array_merge( $accounts, $active_nodes );

			foreach ( $active_nodes as $aKey => $active_node )
			{
				if ( $active_node[ 'filter_type' ] === 'no' )
				{
					$titleText = '';
				}
				else
				{
					$titleText = ( $active_node[ 'filter_type' ] === 'in' ? fsp__( 'Only the posts of the selected categories, tags, etc. will be shared:' ) : fsp__( 'The posts of the selected categories, tags, etc. will not be shared:' ) ) . "\n";
					$titleText .= str_replace( ',', ', ', $active_node[ 'categories_name' ] );
				}

				$active_nodes[ $aKey ][ 'title_text' ] = $titleText;
			}

			$customPostMessages      = array_filter( $customPostMessages, function ( $message ) {
				return ! empty( $message );
			} );
			$default_custom_messages = [
				'fb'          => Helper::getOption( 'post_text_message_fb', "{title}" ),
				'fb_h'        => Helper::getOption( 'post_text_message_fb_h', "{title}" ),
				'instagram'   => Helper::getOption( 'post_text_message_instagram', "{title}" ),
				'instagram_h' => Helper::getOption( 'post_text_message_instagram_h', "{title}" ),
				'threads'     => Helper::getOption( 'post_text_message_threads', "{title}" ),
				'twitter'     => Helper::getOption( 'post_text_message_twitter', "{title}" ),
				'linkedin'    => Helper::getOption( 'post_text_message_linkedin', "{title}" ),
				'tumblr'      => Helper::getOption( 'post_text_message_tumblr', "<img src='{featured_image_url}'>\n\n{content_full}" ),
				'reddit'      => Helper::getOption( 'post_text_message_reddit', "{title}" ),
				'vk'          => Helper::getOption( 'post_text_message_vk', "{title}" ),
				'ok'          => Helper::getOption( 'post_text_message_ok', "{title}" ),
				'plurk'       => Helper::getOption( 'post_text_message_plurk', "{title}\n\n{featured_image_url}\n\n{content_short_200}" ),
				'pinterest'   => Helper::getOption( 'post_text_message_pinterest', "{content_short_497}" ),
				'google_b'    => Helper::getOption( 'post_text_message_google_b', "{title}" ),
				'blogger'     => Helper::getOption( 'post_text_message_blogger', "<img src='{featured_image_url}'>\n\n{content_full} \n\n<a href='{link}'>{link}</a>" ),
				'telegram'    => Helper::getOption( 'post_text_message_telegram', "{title}" ),
				'medium'      => Helper::getOption( 'post_text_message_medium', "<img src='{featured_image_url}'>\n\n{content_full}\n\n<a href='{link}'>{link}</a>" ),
				'discord'     => Helper::getOption( 'post_text_message_discord', "{title}" ),
				'mastodon'    => Helper::getOption( 'post_text_message_mastodon', "{title}" ),
			];
			$custom_messages         = array_merge( $default_custom_messages, $customPostMessages );

			Pages::modal( 'Schedules', 'add', [
				'is_native'      => TRUE,
				'info'           => $info,
				'activeNodes'    => $active_nodes,
				'customMessages' => $custom_messages,
				'title'          => fsp__( 'EDIT SCHEDULE' ),
				'btn_title'      => fsp__( 'SAVE CHANGES' ),
				'post_ids_count' => 1,
				'sn_list'        => [
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
					'mastodon',
				]
			] );
		}

		Helper::response( FALSE, fsp__( 'There isn\'t a schedule.' ) );
	}
}