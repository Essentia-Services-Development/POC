<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Providers\v7\Post;

class ShareService
{
	public static function insertFeeds ( $wpPostId, $userId, $nodes_list, $custom_messages, $categoryFilter = TRUE, $schedule_date = NULL, $sharedFrom = NULL, $shareOnBackground = NULL, $scheduleId = NULL, $disableStartInterval = FALSE, $instagramPinThePost = 0, $cycle = 0 )
	{
		/**
		 * Accounts, communications list array
		 */
		$nodes_list = is_array( $nodes_list ) ? $nodes_list : [];

		/**
		 * Instagram, share on:
		 *  - 1: Profile only
		 *  - 2: Story only
		 *  - 3: Profile and Story
		 */
		$igPostType = Helper::getOption( 'instagram_post_in_type', '1' );
		$fbPostType = Helper::getOption( 'fb_post_in_type', '1' );

		/**
		 * Interval for each publication (sec.)
		 */
		$postInterval        = (int) Helper::getOption( 'post_interval', '0' );
		$postIntervalType    = (int) Helper::getOption( 'post_interval_type', '1' );
		$sendDateTime        = Date::dateTimeSQL( is_null( $schedule_date ) ? 'now' : $schedule_date );
		$intervalForNetworks = [];

		/**
		 * Time interval before start
		 */
		if ( ! $disableStartInterval )
		{
			$timer = (int) Helper::getOption( 'share_timer', '0' );

			if ( $timer > 0 )
			{
				$sendDateTime = Date::dateTimeSQL( $sendDateTime, '+' . $timer . ' minutes' );
			}
		}

		$feedsCount = 0;

		if ( is_null( $shareOnBackground ) )
		{
			$shareOnBackground = (int) Helper::getOption( 'share_on_background', '1' );
		}

		foreach ( $nodes_list as $nodeId )
		{
			if ( is_string( $nodeId ) && strpos( $nodeId, ':' ) !== FALSE )
			{
				$parse         = explode( ':', $nodeId );
				$driver        = $parse[ 0 ];
				$nodeType      = $parse[ 1 ];
				$nodeId        = $parse[ 2 ];
				$filterType    = isset( $parse[ 3 ] ) ? $parse[ 3 ] : 'no';
				$categoriesStr = isset( $parse[ 4 ] ) ? $parse[ 4 ] : '';

				if ( $categoryFilter && ! empty( $categoriesStr ) && $filterType != 'no' )
				{
					$categoriesFilter = [];

					foreach ( explode( ',', $categoriesStr ) as $termId )
					{
						if ( is_numeric( $termId ) && $termId > 0 )
						{
							$categoriesFilter[] = (int) $termId;
						}
					}

					$result = DB::DB()->get_row( "SELECT count(0) AS r_count FROM `" . DB::WPtable( 'term_relationships', TRUE ) . "` WHERE object_id='" . (int) $wpPostId . "' AND `term_taxonomy_id` IN (SELECT `term_taxonomy_id` FROM `" . DB::WPtable( 'term_taxonomy', TRUE ) . "` WHERE `term_id` IN ('" . implode( "' , '", $categoriesFilter ) . "'))", ARRAY_A );

					if ( ( $filterType == 'in' && $result[ 'r_count' ] == 0 ) || ( $filterType == 'ex' && $result[ 'r_count' ] > 0 ) )
					{
						continue;
					}
				}

				if ( $nodeType == 'account' && in_array( $driver, [
						'tumblr',
						'google_b',
						'telegram',
						'discord',
						'planly'
					] ) )
				{
					continue;
				}

				if ( ! ( in_array( $nodeType, [
						'account',
						'ownpage',
						'page',
						'group',
						'event',
						'blog',
						'company',
						'community',
						'subreddit',
						'location',
						'chat',
						'board',
						'publication',
						'channel',
						'instagram',//planly
						'facebook',//planly
						'tiktok',//planly
						'tiktok_business',//planly
						'twitter',//planly
						'linkedin',//planly
						'pinterest',//planly
						'webhook',
						'request',
					] ) && is_numeric( $nodeId ) && $nodeId > 0 )
				)
				{
					continue;
				}
				if ( $postInterval > 0 )
				{
					$driver2ForArr = $postIntervalType == 1 ? $driver : 'all';
					$dataSendTime  = isset( $intervalForNetworks[ $driver2ForArr ] ) ? $intervalForNetworks[ $driver2ForArr ] : $sendDateTime;
				}
				else
				{
					$dataSendTime = $sendDateTime;
				}

				$feedSQL = [
					'blog_id'             => Helper::getBlogId(),
					'user_id'             => $userId,
					'driver'              => $driver,
					'post_id'             => $wpPostId,
					'wp_post_date'        => Date::dateTimeSQL(get_post_time( 'Y-m-d H:i:s', false, get_post($wpPostId), false )),
					'node_type'           => $nodeType,
					'node_id'             => (int) $nodeId,
					'interval'            => $postInterval,
					'send_time'           => $dataSendTime,
					'share_on_background' => $shareOnBackground ? 1 : 0,
					'schedule_id'         => $scheduleId,
					'is_seen'             => 0,
					'shared_from'         => $sharedFrom,
					'schedule_cycle'      => $cycle
				];

				if ( ! ( $driver == 'instagram' && $igPostType == '2' ) && ! ( $driver == 'fb' && $nodeType != 'group' && $fbPostType == '2' ) )
				{
					$customMessage = Helper::getCustomSetting( 'account_post_message', '', $nodeType, $nodeId );
					$customMessage = empty( $customMessage ) ? ( isset( $custom_messages[ $driver ] ) ? $custom_messages[ $driver ] : NULL ) : $customMessage;

					if ( $customMessage == Helper::getOption( 'post_text_message_' . $driver, "{title}" ) )
					{
						$customMessage = NULL;
					}

					$feedSQL[ 'custom_post_message' ] = $customMessage;

					if ( $instagramPinThePost != 0 )
					{
						$feedSQL[ 'data' ] = json_encode( [ 'instagram_pin_the_post' => $instagramPinThePost ] );
					}

					DB::DB()->insert( DB::table( 'feeds' ), $feedSQL );

					$feedsCount++;
				}

				if ( ( $driver == 'instagram' && ( $igPostType == '2' || $igPostType == '3' ) ) || ( $driver == 'fb' && $nodeType != 'group' && ( $fbPostType == '2' || $fbPostType == '3' ) ) )
				{
					$customMessage = isset( $custom_messages[ $driver . '_h' ] ) ? $custom_messages[ $driver . '_h' ] : NULL;

					if ( $customMessage == Helper::getOption( 'post_text_message_' . $driver . '_h', "{title}" ) )
					{
						$customMessage = NULL;
					}

					$feedSQL[ 'custom_post_message' ] = $customMessage;
					$feedSQL[ 'feed_type' ]           = 'story';

					DB::DB()->insert( DB::table( 'feeds' ), $feedSQL );

					$feedsCount++;
				}

				if ( $postInterval > 0 )
				{
					$intervalForNetworks[ $driver2ForArr ] = Date::dateTimeSQL( $dataSendTime, '+' . $postInterval . ' second' );
				}
			}
		}

		return $feedsCount;
	}

	public static function shareQueuedFeeds ()
	{
		$all_blogs = Helper::getBlogs();

		foreach ( $all_blogs as $blog_id )
		{
			Helper::setBlogId( $blog_id );

			$feed_ids = [];
			$now      = Date::dateTimeSQL();
			$feeds    = DB::DB()->get_results( DB::DB()->prepare( 'SELECT id FROM `' . DB::table( 'feeds' ) . '` tb1 WHERE `blog_id`=%d AND `share_on_background`=1 and `is_sended`=0 and `send_time`<=%s AND (SELECT count(0) FROM `' . DB::WPtable( 'posts', TRUE ) . '` WHERE `id`=tb1.`post_id` AND (`post_status`=\'publish\' OR `post_type`=\'attachment\'))>0 LIMIT 15', [
				$blog_id,
				$now
			] ), ARRAY_A );

			foreach ( $feeds as $feed )
			{
				$feed_ids[] = intval( $feed[ 'id' ] );
			}

			if ( ! empty( $feed_ids ) )
			{
				DB::DB()->query( 'UPDATE `' . DB::table( 'feeds' ) . '` SET `is_sended`=2 WHERE id IN (\'' . implode( "','", $feed_ids ) . '\')' );

				foreach ( $feeds as $feed )
				{
					if ( ! empty( $feed[ 'schedule_id' ] ) )
					{
						$schedule = DB::DB()->get_row( DB::DB()->prepare( 'SELECT * FROM `' . DB::table( 'schedules' ) . '` WHERE `id` = %d', [ $feed[ 'schedule_id' ] ] ), ARRAY_A );

						if ( ! empty( $schedule ) && self::isSleepTime( $schedule ) )
						{
							continue;
						}
					}

					ShareService::post( $feed[ 'id' ], TRUE );
				}
			}

			$pendingPosts = DB::DB()->get_row( DB::DB()->prepare( 'SELECT COUNT(0) AS `count` FROM `' . DB::table( 'feeds' ) . '` tb1 WHERE `blog_id`=%d AND `share_on_background`=1 and `is_sended`=0 and `send_time`<=%s AND (SELECT count(0) FROM `' . DB::WPtable( 'posts', TRUE ) . '` WHERE `id`=tb1.`post_id` AND (`post_status`=\'publish\' OR `post_type`=\'attachment\'))>0', [
				$blog_id,
				$now
			] ), ARRAY_A );

			if ( ! empty( $pendingPosts[ 'count' ] ) && $pendingPosts[ 'count' ] > 1 )
			{
				wp_remote_get( site_url() . '/wp-cron.php?doing_wp_cron', [ 'blocking' => FALSE ] );
			}

			Helper::resetBlogId();
		}
	}

	public static function postSaveEvent ( $new_status, $old_status, $post )
	{
		global $wp_version;

		$post_id = $post->ID;
		$userId  = $post->post_author;

		if ( $old_status === 'new' && $new_status === 'auto-draft' )
		{
			add_post_meta( $post_id, '_fs_is_new_post', 1, TRUE );
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return;
		}

		if ( ! in_array( $new_status, [ 'publish', 'future', 'draft', 'pending' ] ) )
		{
			return;
		}

		/**
		 * Gutenberg bug...
		 * https://github.com/WordPress/gutenberg/issues/15094
		 */
		if ( version_compare( $wp_version, '5.0', '>=' ) && isset( $_GET[ '_locale' ] ) && $_GET[ '_locale' ] == 'user' && empty( $_POST ) )
		{
			delete_post_meta( $post_id, '_fs_poster_post_old_status_saved' );
			add_post_meta( $post_id, '_fs_poster_post_old_status_saved', $old_status, TRUE );

			return;
		}

		if ( in_array( $post->post_type, [ 'fs_post', 'fs_post_tmp' ] ) )
		{
			return;
		}

		if ( ! in_array( $post->post_type, explode( '|', Helper::getOption( 'allowed_post_types', 'post|page|attachment|product' ) ) ) )
		{
			return;
		}

		$metaBoxLoader            = (int) Request::get( 'meta-box-loader', 0, 'num', [ '1' ] );
		$original_post_old_status = Request::post( 'original_post_status', '', 'string' );

		if ( $metaBoxLoader === 1 && ! empty( $original_post_old_status ) )
		{
			// Gutenberg bug!
			$meta_old_status = get_post_meta( $post_id, '_fs_poster_post_old_status_saved', TRUE );

			$old_status = empty( $meta_old_status ) ? $old_status : $meta_old_status;

			delete_post_meta( $post_id, '_fs_poster_post_old_status_saved' );
		}

		$isYoastDuplicateRewrite = isset( $_POST[ 'post_status' ] ) && $_POST[ 'post_status' ] == 'dp-rewrite-republish';

		if ( $old_status === 'publish' && ! metadata_exists( 'post', $post_id, '_fs_is_new_post' ) && ! $isYoastDuplicateRewrite )
		{
			return;
		}

		delete_post_meta( $post_id, '_fs_is_new_post' );

		if ( $old_status == 'future' && $new_status == 'publish' )
		{
			delete_post_meta( $post_id, '_fs_instagram_pin_the_post' );
		}

		if ( $old_status === 'future' && ( $new_status === 'future' || $new_status === 'publish' ) )
		{
			$oldScheduleDate = Date::epoch( get_post_meta( $post_id, '_fs_poster_schedule_datetime', TRUE ) );
			$newDateTime     = $new_status == 'publish' ? Date::epoch() : Date::epoch( $post->post_date );
			$diff            = (int) ( ( $newDateTime - $oldScheduleDate ) / 60 );

			if ( $diff != 0 && abs( $diff ) < 60 * 24 * 90 )
			{
				$schedule_date = Date::dateTimeSQL( $post->post_date, '+1 minute' );
				DB::DB()->query( 'UPDATE `' . DB::table( 'feeds' ) . '` SET `send_time`=\'' . $schedule_date . '\' WHERE blog_id=\'' . Helper::getBlogId() . '\' AND is_sended=0 and post_id=\'' . (int) $post_id . '\'' );
			}

			delete_post_meta( $post_id, '_fs_poster_schedule_datetime' );

			if ( $new_status == 'future' )
			{
				add_post_meta( $post_id, '_fs_poster_schedule_datetime', $post->post_date, TRUE );
			}

			return;
		}

		if ( $old_status === 'future' )
		{
			$nodes_list        = [];
			$post_text_message = [
				'fb'                => Helper::getOption( 'post_text_message_fb', "{title}" ),
				'fb_h'              => Helper::getOption( 'post_text_message_fb_h', "{title}" ),
				'instagram'         => Helper::getOption( 'post_text_message_instagram', "{title}" ),
				'instagram_h'       => Helper::getOption( 'post_text_message_instagram_h', "{title}" ),
				'threads'           => Helper::getOption( 'post_text_message_threads', "{title}" ),
				'twitter'           => Helper::getOption( 'post_text_message_twitter', "{title}" ),
				'planly'            => Helper::getOption( 'post_text_message_planly', "{content_full}" ),
				'linkedin'          => Helper::getOption( 'post_text_message_linkedin', "{title}" ),
				'pinterest'         => Helper::getOption( 'post_text_message_pinterest', "{content_short_497}" ),
				'telegram'          => Helper::getOption( 'post_text_message_telegram', "{title}\n\n<img src='{featured_image_url}'>\n\n{content_full}{link}" ),
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
				'discord'           => Helper::getOption( 'post_text_message_discord', "{content_full}" ),
				'mastodon'          => Helper::getOption( 'post_text_message_discord', "{title}" ),
			];

			$getScheduledFeeds = DB::DB()->get_results( DB::DB()->prepare( "
					SELECT tb1.node_id AS id, tb1.driver, tb1.node_type, tb2.filter_type, tb2.categories, tb1.custom_post_message FROM `" . DB::table( 'feeds' ) . "` tb1 LEFT JOIN `" . DB::table( 'account_status' ) . "` tb2 ON tb2.account_id=tb1.node_id AND tb2.user_id=%d WHERE tb1.post_id=%d AND node_type='account'
					UNION 
					SELECT tb1.node_id AS id, tb1.driver, tb1.node_type, tb2.filter_type, tb2.categories, tb1.custom_post_message FROM `" . DB::table( 'feeds' ) . "` tb1 LEFT JOIN `" . DB::table( 'account_node_status' ) . "` tb2 ON tb2.node_id=tb1.node_id AND tb2.user_id=%d WHERE tb1.post_id=%d AND node_type<>'account'
					", [ $userId, $post_id, $userId, $post_id ] ), ARRAY_A );

			foreach ( $getScheduledFeeds as $nodeInf )
			{
				$nodes_list[] = $nodeInf[ 'driver' ] . ':' . $nodeInf[ 'node_type' ] . ':' . $nodeInf[ 'id' ] . ':' . htmlspecialchars( $nodeInf[ 'filter_type' ] ?: '' ) . ':' . htmlspecialchars( $nodeInf[ 'categories' ] ?: '' );

				$post_text_message[ $nodeInf[ 'driver' ] ] = $nodeInf[ 'custom_post_message' ];
			}

			add_post_meta( $post_id, '_fs_poster_share', ( empty( $nodes_list ) ? Helper::getOption( 'auto_share_new_posts', '1' ) : 1 ), TRUE );
			add_post_meta( $post_id, '_fs_poster_node_list', $nodes_list, TRUE );

			foreach ( $post_text_message as $dr => $cmtxt )
			{
				add_post_meta( $post_id, '_fs_poster_cm_' . $dr, $cmtxt, TRUE );
			}

			DB::DB()->delete( DB::table( 'feeds' ), [
				'blog_id'   => Helper::getBlogId(),
				'post_id'   => $post_id,
				'is_sended' => '0'
			] );

			return;
		}

		// if the request is from real user
		if ( metadata_exists( 'post', $post_id, '_fs_is_manual_action' ) )
		{
			$post_text_message[ 'fb' ]          = get_post_meta( $post_id, '_fs_poster_cm_fb', TRUE );
			$post_text_message[ 'fb_h' ]        = get_post_meta( $post_id, '_fs_poster_cm_fb_h', TRUE );
			$post_text_message[ 'threads' ]     = get_post_meta( $post_id, '_fs_poster_cm_threads', TRUE );
			$post_text_message[ 'twitter' ]     = get_post_meta( $post_id, '_fs_poster_cm_twitter', TRUE );
			$post_text_message[ 'instagram' ]   = get_post_meta( $post_id, '_fs_poster_cm_instagram', TRUE );
			$post_text_message[ 'instagram_h' ] = get_post_meta( $post_id, '_fs_poster_cm_instagram_h', TRUE );
			$post_text_message[ 'linkedin' ]    = get_post_meta( $post_id, '_fs_poster_cm_linkedin', TRUE );
			$post_text_message[ 'vk' ]          = get_post_meta( $post_id, '_fs_poster_cm_vk', TRUE );
			$post_text_message[ 'pinterest' ]   = get_post_meta( $post_id, '_fs_poster_cm_pinterest', TRUE );
			$post_text_message[ 'reddit' ]      = get_post_meta( $post_id, '_fs_poster_cm_reddit', TRUE );
			$post_text_message[ 'tumblr' ]      = get_post_meta( $post_id, '_fs_poster_cm_tumblr', TRUE );
			$post_text_message[ 'ok' ]          = get_post_meta( $post_id, '_fs_poster_cm_ok', TRUE );
			$post_text_message[ 'google_b' ]    = get_post_meta( $post_id, '_fs_poster_cm_google_b', TRUE );
			$post_text_message[ 'blogger' ]     = get_post_meta( $post_id, '_fs_poster_cm_blogger', TRUE );
			$post_text_message[ 'telegram' ]    = get_post_meta( $post_id, '_fs_poster_cm_telegram', TRUE );
			$post_text_message[ 'medium' ]      = get_post_meta( $post_id, '_fs_poster_cm_medium', TRUE );
			$post_text_message[ 'wordpress' ]   = get_post_meta( $post_id, '_fs_poster_cm_wordpress', TRUE );
			$post_text_message[ 'plurk' ]       = get_post_meta( $post_id, '_fs_poster_cm_plurk', TRUE );
			$post_text_message[ 'discord' ]     = get_post_meta( $post_id, '_fs_poster_cm_discord', TRUE );
			$post_text_message[ 'mastodon' ]    = get_post_meta( $post_id, '_fs_poster_cm_mastodon', TRUE );
		}
		else
		{
			$post_text_message[ 'fb' ]          = Helper::getOption( 'post_text_message_fb', '{title}' );
			$post_text_message[ 'fb_h' ]        = Helper::getOption( 'post_text_message_fb_h', '{title}' );
			$post_text_message[ 'threads' ]     = Helper::getOption( 'post_text_message_threads', '{title}' );
			$post_text_message[ 'twitter' ]     = Helper::getOption( 'post_text_message_twitter', '{title}' );
			$post_text_message[ 'instagram' ]   = Helper::getOption( 'post_text_message_instagram', '{title}' );
			$post_text_message[ 'instagram_h' ] = Helper::getOption( 'post_text_message_instagram_h', '{title}' );
			$post_text_message[ 'linkedin' ]    = Helper::getOption( 'post_text_message_linkedin', '{title}' );
			$post_text_message[ 'vk' ]          = Helper::getOption( 'post_text_message_vk', '{title}' );
			$post_text_message[ 'pinterest' ]   = Helper::getOption( 'post_text_message_pinterest', "{content_short_497}" );
			$post_text_message[ 'reddit' ]      = Helper::getOption( 'post_text_message_reddit', '{title}' );
			$post_text_message[ 'tumblr' ]      = Helper::getOption( 'post_text_message_tumblr', "<img src='{featured_image_url}'>\n\n{content_full}" );
			$post_text_message[ 'ok' ]          = Helper::getOption( 'post_text_message_ok', '{title}' );
			$post_text_message[ 'google_b' ]    = Helper::getOption( 'post_text_message_google_b', '{title}' );
			$post_text_message[ 'blogger' ]     = Helper::getOption( 'post_text_message_blogger', "<img src='{featured_image_url}'>\n\n{content_full} \n\n<a href='{link}'>{link}</a>" );
			$post_text_message[ 'telegram' ]    = Helper::getOption( 'post_text_message_telegram', '{title}' );
			$post_text_message[ 'medium' ]      = Helper::getOption( 'post_text_message_medium', "<img src='{featured_image_url}'>\n\n{content_full}\n\n<a href='{link}'>{link}</a>" );
			$post_text_message[ 'wordpress' ]   = Helper::getOption( 'post_text_message_wordpress', '{content_full}' );
			$post_text_message[ 'discord' ]     = Helper::getOption( 'post_text_message_discord', '{content_full}' );
			$post_text_message[ 'mastodon' ]    = Helper::getOption( 'post_text_message_mastodon', '{title}' );
			$post_text_message[ 'plurk' ]       = Helper::getOption( 'post_text_message_plurk', "{title}\n\n{featured_image_url}\n\n{content_short_200}" );
		}

		// if the request is from real user
		if ( metadata_exists( 'post', $post_id, '_fs_is_manual_action' ) )
		{
			$share_checked_input = get_post_meta( $post_id, '_fs_poster_share', TRUE );
		}
		else
		{
			$share_checked_input = Helper::getOption( 'auto_share_new_posts', '1' );
		}

		if ( $new_status == 'future' )
		{
			$backgroundShare = 1;

			add_post_meta( $post_id, '_fs_poster_schedule_datetime', $post->post_date, TRUE );
		}
		else
		{
			$backgroundShare = (int) Helper::getOption( 'share_on_background', '1' );
		}

		if ( $share_checked_input != 1 )
		{
			DB::DB()->delete( DB::table( 'feeds' ), [
				'blog_id'   => Helper::getBlogId(),
				'post_id'   => $post_id,
				'is_sended' => '0'
			] );

			return;
		}

		// if the request is from real user
		if ( metadata_exists( 'post', $post_id, '_fs_is_manual_action' ) )
		{
			$nodes_list = get_post_meta( $post_id, '_fs_poster_node_list', TRUE );
			$nodes_list = Pages::action( 'Base', 'groups_to_nodes', [ 'node_list' => $nodes_list ] );
		}
		else
		{
			$nodes_list = [];

			$accounts = DB::DB()->get_results( DB::DB()->prepare( "
					SELECT tb2.id, tb2.driver, tb1.filter_type, tb1.categories, 'account' AS node_type 
					FROM " . DB::table( 'account_status' ) . " tb1
					INNER JOIN " . DB::table( 'accounts' ) . " tb2 ON tb2.id=tb1.account_id
					WHERE tb1.user_id=%d AND (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d", [
				$userId,
				$userId,
				Helper::getBlogId()
			] ), ARRAY_A );

			$active_nodes = DB::DB()->get_results( DB::DB()->prepare( "
					SELECT tb2.id, tb2.driver, tb2.node_type, tb1.filter_type, tb1.categories FROM " . DB::table( 'account_node_status' ) . " tb1
					LEFT JOIN " . DB::table( 'account_nodes' ) . " tb2 ON tb2.id=tb1.node_id
					WHERE tb1.user_id=%d AND (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d", [
				$userId,
				$userId,
				Helper::getBlogId()
			] ), ARRAY_A );

			$active_nodes = array_merge( $accounts, $active_nodes );

			foreach ( $active_nodes as $nodeInf )
			{
				$nodes_list[] = $nodeInf[ 'driver' ] . ':' . $nodeInf[ 'node_type' ] . ':' . $nodeInf[ 'id' ] . ':' . htmlspecialchars( $nodeInf[ 'filter_type' ] ?: '' ) . ':' . htmlspecialchars( $nodeInf[ 'categories' ] ?: '' );
			}
		}

		if ( $new_status === 'draft' || $new_status === 'pending' )
		{
			add_post_meta( $post_id, '_fs_poster_share', 1, TRUE );
			add_post_meta( $post_id, '_fs_poster_node_list', $nodes_list, TRUE );

			foreach ( $post_text_message as $dr => $custom_message )
			{
				add_post_meta( $post_id, '_fs_poster_cm_' . $dr, $custom_message, TRUE );
			}

			return;
		}

		$schedule_date = NULL;

		if ( $new_status == 'future' )
		{
			$schedule_date = Date::dateTimeSQL( $post->post_date, '+1 minute' );
		}

		$instagramPinThePost = get_post_meta( $post_id, '_fs_instagram_pin_the_post', TRUE );
		$instagramPinThePost = empty( $instagramPinThePost ) ? 0 : 1;

		self::insertFeeds( $post_id, $userId, $nodes_list, $post_text_message, TRUE, $schedule_date, 'auto_post', $backgroundShare, NULL, FALSE, $instagramPinThePost );

		delete_post_meta( $post_id, '_fs_instagram_pin_the_post' );

		if ( $new_status == 'publish' )
		{
			add_filter( 'redirect_post_location', function ( $location ) use ( $backgroundShare ) {
				return $location . '&share=1&background=' . $backgroundShare;
			} );
		}
	}

	public static function deletePostFeeds ( $post_id )
	{
		DB::DB()->delete( DB::table( 'feeds' ), [
			'blog_id'   => Helper::getBlogId(),
			'post_id'   => $post_id,
			'is_sended' => 0
		] );
	}

	public static function shareSchedules ()
	{
		$nowDateTime = Date::dateTimeSQL();

		$getSchedules = DB::DB()->prepare( 'SELECT * FROM `' . DB::table( 'schedules' ) . '` WHERE `status`=\'active\' and `next_execute_time`<=%s', [ $nowDateTime ] );

		$getSchedules = DB::DB()->get_results( $getSchedules, ARRAY_A );

		$preventDublicates = DB::DB()->prepare( 'UPDATE `' . DB::table( 'schedules' ) . '` SET `next_execute_time`=DATE_ADD(`next_execute_time`, INTERVAL ((TIMESTAMPDIFF(MINUTE, `next_execute_time`, %s) DIV `interval` ) + 1) * `interval` MINUTE) WHERE `status`=\'active\' and `next_execute_time`<=%s', [
			$nowDateTime,
			$nowDateTime
		] );
		DB::DB()->query( $preventDublicates );

		$result = FALSE;

		foreach ( $getSchedules as $schedule_info )
		{
			if ( self::scheduledPost( $schedule_info ) === TRUE )
			{
				$result = TRUE;
			}
		}

		if ( $result )
		{
			self::shareQueuedFeeds();
		}
	}

	public static function doAutoReschedule ( $scheduleInfo )
	{
		$data = isset( $scheduleInfo[ 'data' ] ) ? json_decode( $scheduleInfo[ 'data' ], TRUE ) : [];
		$data = empty( $data ) ? [] : $data;

		if ( ! empty( $data[ 'parentScheduleID' ] ) )
		{
			$scheduleInfo = DB::fetch( 'schedules', $data[ 'parentScheduleID' ] );
		}

		if ( empty( $scheduleInfo ) )
		{
			return;
		}

		$data = isset( $scheduleInfo[ 'data' ] ) ? json_decode( $scheduleInfo[ 'data' ], TRUE ) : [];
		$data = empty( $data ) ? [] : $data;

		if ( $data[ 'autoRescheduleCount' ] !== 0 )
		{
			if ( $data[ 'autoReschdulesDone' ] === $data[ 'autoRescheduleCount' ] || $data[ 'autoRescheduleEnabled' ] === 0 )
			{
				return;
			}
		}

		$data[ 'autoReschdulesDone' ] += 1;

		$scheduleInfo[ 'status' ]   = 'active';
		$scheduleInfo[ 'post_ids' ] = $scheduleInfo[ 'save_post_ids' ];
		$scheduleInfo[ 'data' ]     = json_encode( $data );

		DB::DB()->update( DB::table( 'schedules' ), $scheduleInfo, [ 'id' => $scheduleInfo[ 'id' ] ] );
	}

	public static function scheduledPost ( $schedule )
	{
		$scheduleId = $schedule[ 'id' ];
		$userId     = $schedule[ 'user_id' ];
		$blogId     = $schedule[ 'blog_id' ];

		Helper::setBlogId( $blogId );

		if ( self::isSleepTime( $schedule ) )
		{
			Helper::resetBlogId();

			return FALSE;
		}

		$filterQuery = Helper::scheduleFilters( $schedule );

		/* End post_sort */
		$getRandomPost = DB::DB()->get_row( "SELECT * FROM `" . DB::WPtable( 'posts', TRUE ) . "` tb1 WHERE (post_status='publish' OR post_type='attachment') {$filterQuery} LIMIT 1", ARRAY_A );

		$post_id = ! empty( $getRandomPost[ 'ID' ] ) ? $getRandomPost[ 'ID' ] : 0;

		if ( ! ( $post_id > 0 ) && $schedule[ 'post_sort' ] !== 'random' && ( $schedule[ 'post_sort' ] !== 'old_first' || ! empty( $schedule[ 'save_post_ids' ] ) ) )
		{
			DB::DB()->update( DB::table( 'schedules' ), [ 'status' => 'finished' ], [ 'id' => $scheduleId ] );

			self::doAutoReschedule( $schedule );

			Helper::resetBlogId();

			return FALSE;
		}
		else if ( empty( $getRandomPost ) )
		{
			Helper::resetBlogId();

			return FALSE;
		}

		if ( $schedule[ 'post_freq' ] === 'once' && $schedule[ 'post_sort' ] !== 'random' && ! empty( $schedule[ 'post_ids' ] ) )
		{
			DB::DB()->query( DB::DB()->prepare( "UPDATE `" . DB::table( 'schedules' ) . "` SET `post_ids`=TRIM(BOTH ',' FROM replace(concat(',',`post_ids`,','), ',%d,',',')), status=IF( `post_ids`='' , 'finished', `status`) WHERE `id`=%d", [
				$post_id,
				$scheduleId
			] ) );

			$postIDS = explode( ',', $schedule[ 'post_ids' ] );

			if ( is_array( $postIDS ) && count( $postIDS ) == 1 && $postIDS[ 0 ] == $post_id )
			{
				self::doAutoReschedule( $schedule );
			}

		}

		$accountList = explode( ',', $schedule[ 'share_on_accounts' ] );

		$activeAccounts = [];
		$activeNodes    = [];

		if ( ! empty( $schedule[ 'share_on_accounts' ] ) && is_array( $accountList ) && ! empty( $accountList ) && count( $accountList ) > 0 )
		{
			$_accountsList = [];
			$_nodeList     = [];

			foreach ( $accountList as $account )
			{
				$account = explode( ':', $account );

				if ( ! isset( $account[ 1 ] ) )
				{
					continue;
				}

				if ( $account[ 0 ] == 'account' )
				{
					$_accountsList[] = (int) $account[ 1 ];
					continue;
				}

				$_nodeList[] = (int) $account[ 1 ];
			}

			if ( ! empty( $_accountsList ) )
			{
				$activeAccounts = DB::DB()->get_results( DB::DB()->prepare( "
						SELECT tb1.*, IFNULL(filter_type,'no') AS filter_type, categories
						FROM " . DB::table( 'accounts' ) . " tb1
						LEFT JOIN " . DB::table( 'account_status' ) . " tb2 ON tb1.id=tb2.account_id AND tb2.user_id=%d
						WHERE (tb1.is_public=1 OR tb1.user_id=%d) AND tb1.blog_id=%d AND tb1.id in (" . implode( ',', $_accountsList ) . ")", [
					$userId,
					$userId,
					Helper::getBlogId()
				] ), ARRAY_A );
			}

			if ( ! empty( $_nodeList ) )
			{
				$activeNodes = DB::DB()->get_results( DB::DB()->prepare( "
						SELECT tb1.*, IFNULL(filter_type,'no') AS filter_type, categories
						FROM " . DB::table( 'account_nodes' ) . " tb1
						LEFT JOIN " . DB::table( 'account_node_status' ) . " tb2 ON tb1.id=tb2.node_id AND tb2.user_id=%d
						WHERE (tb1.is_public=1 OR tb1.user_id=%d) AND tb1.blog_id=%d AND tb1.id in (" . implode( ',', $_nodeList ) . ")", [
					$userId,
					$userId,
					Helper::getBlogId()
				] ), ARRAY_A );
			}
		}

		$customPostMessages = json_decode( $schedule[ 'custom_post_message' ], TRUE );
		$customPostMessages = is_array( $customPostMessages ) ? $customPostMessages : [];
		$nodeList           = [];

		foreach ( $activeAccounts as $accountInf )
		{
			$nodeList[] = $accountInf[ 'driver' ] . ':account:' . (int) $accountInf[ 'id' ] . ':' . $accountInf[ 'filter_type' ] . ':' . $accountInf[ 'categories' ];
		}

		foreach ( $activeNodes as $nodeInf )
		{
			$nodeList[] = $nodeInf[ 'driver' ] . ':' . $nodeInf[ 'node_type' ] . ':' . (int) $nodeInf[ 'id' ] . ':' . $nodeInf[ 'filter_type' ] . ':' . $nodeInf[ 'categories' ];
		}

		if ( empty( $nodeList ) )
		{
			Helper::resetBlogId();

			return FALSE;
		}

		$scheduleData = empty( $schedule[ 'data' ] ) ? [] : json_decode( $schedule[ 'data' ], TRUE );

		$instagramPinThePost = empty( $scheduleData[ 'instagram_pin_the_post' ] ) ? 0 : 1;
		$cycle               = empty( $scheduleData[ 'autoReschdulesDone' ] ) ? 0 : $scheduleData[ 'autoReschdulesDone' ];

		self::insertFeeds( $post_id, $userId, $nodeList, $customPostMessages, FALSE, NULL, 'schedule', 1, $scheduleId, TRUE, $instagramPinThePost, $cycle );

		Helper::resetBlogId();

		return TRUE;
	}

	private static function isSleepTime ( $schedule )
	{
		if ( ! empty( $schedule[ 'sleep_time_start' ] ) && ! empty( $schedule[ 'sleep_time_end' ] ) )
		{
			$currentTimestamp = Date::epoch();
			$sleepTimeStart   = Date::epoch( Date::dateSQL() . ' ' . $schedule[ 'sleep_time_start' ] );
			$sleepTimeEnd     = Date::epoch( Date::dateSQL() . ' ' . $schedule[ 'sleep_time_end' ] );

			return Helper::isBetweenDates( $currentTimestamp, $sleepTimeStart, $sleepTimeEnd );
		}

		return FALSE;
	}

	/**
	 * @param $feedId int
	 * @param $secureShare boolean
	 *
	 * @return array
	 */
	public static function post ( $feedId, $secureShare = FALSE )
	{
		$post = new Post( $feedId );
		$err  = $post->init( $secureShare );

		if ( $err !== NULL )
		{
			return $err;
		}

		//Share to Social Network
		$post->share();

		WPPostThumbnail::clearCache();

		$post->handleLogs();

		return $post->result();
	}
}
