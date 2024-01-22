<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Libraries\fb\Facebook;

class CommentService
{
	private static $fbid_to_wpid = [];
	private static $cachedNodes  = [];
	private static $cachedApps   = [];

	public static function fetchFacebookComments ()
	{
		$all_blogs = Helper::getBlogs();

		foreach ( $all_blogs as $blog_id )
		{
			Helper::setBlogId( $blog_id );
			self::_fetchFacebookComments();
			Helper::resetBlogId();
		}
	}

	private static function _fetchFacebookComments ()
	{
		$posts = self::getPosts();

		foreach ( $posts as $post )
		{
			if ( Helper::getCustomSetting( 'fetch_facebook_comments', 1, 'node', $post[ 'node_id' ] ) == 0 )
			{
				continue;
			}

			$nodeInfo = self::getNodeInfo( $post[ 'node_type' ], $post[ 'node_id' ] );

			if ( empty( $nodeInfo[ 'app_id' ] ) )
			{
				continue;
			}

			$appInfo = self::getApp( $nodeInfo[ 'app_id' ] );

			$fb = new Facebook( $appInfo, $nodeInfo[ 'access_token' ], $nodeInfo[ 'proxy' ] );

			$since = get_post_meta( $post[ 'post_id' ], 'fs_fb_last_comment_fetch_date_' . $post[ 'id' ], TRUE );

			$comments = $fb->fetchComments( $nodeInfo[ 'node_id' ], $post[ 'driver_post_id' ], $since );

			if ( ! empty ( $comments ) )
			{
				$dates = [];

				$needsExistenceCheck = TRUE;

				foreach ( $comments as $comment )
				{
					if ( ! empty( $comment[ 'created_time' ] ) )
					{
						$dates[] = $comment[ 'created_time' ];
					}

					if ( empty( $comment[ 'id' ] ) || isset( self::$fbid_to_wpid[ $comment[ 'id' ] ] ) )
					{
						continue;
					}

					if ( $needsExistenceCheck )
					{
						$existingComment = DB::DB()->get_row( DB::DB()->prepare( 'SELECT comment_id FROM ' . DB::WPtable( 'commentmeta', TRUE ) . ' WHERE meta_value=%s AND meta_key=\'_fs_poster_fb_comment_id\'', $comment[ 'id' ] ), ARRAY_A );

						if ( empty( $existingComment ) )
						{
							$needsExistenceCheck = FALSE;
						}
						else
						{
							self::$fbid_to_wpid[ $comment[ 'id' ] ] = $existingComment[ 'comment_id' ];
							continue;
						}
					}

					$message = empty( $comment[ 'message' ] ) ? '' : $comment[ 'message' ];

					$isAttachment = FALSE;

					if ( ! empty( $comment[ 'attachment' ] ) )
					{
						$type = isset( $comment[ 'attachment' ][ 'type' ] ) ? $comment[ 'attachment' ][ 'type' ] : '';

						if ( $type !== 'share' ) //is not link
						{
							$isAttachment = TRUE;

							$attachment = "<br>" . fsp__( 'Media: ' );

							if ( $comment[ 'attachment' ][ 'target' ][ 'url' ] )
							{
								$attachment .= $comment[ 'attachment' ][ 'target' ][ 'url' ];
							}
							else if ( $comment[ 'attachment' ][ 'media' ][ 'source' ] )
							{
								$attachment .= $comment[ 'attachment' ][ 'media' ][ 'source' ];
							}
							else if ( $comment[ 'attachment' ][ 'media' ][ 'img' ][ 'src' ] )
							{
								$attachment .= $comment[ 'attachment' ][ 'media' ][ 'img' ][ 'src' ];
							}
							else
							{
								$attachment = '';
							}

							$message .= $attachment;
						}
					}

					self::insertComment(
						$post[ 'post_id' ],
						$comment[ 'id' ],
						empty( $comment[ 'parent' ][ 'id' ] ) ? 0 : $comment[ 'parent' ][ 'id' ],
						$message,
						$isAttachment || empty( $message ) ? 0 : 1,
						empty( $comment[ 'from' ][ 'name' ] ) ? '' : $comment[ 'from' ][ 'name' ],
                        empty( $comment[ 'created_time' ] ) ? Date::dateTimeSQL() : Date::dateTimeSQL($comment[ 'created_time' ], (get_option('gmt_offset') * 60) . ' minutes')
					);
				}

				uasort( $dates, function ( $a, $b ) {
					return $a == $b ? 0 : ( strtotime( $a ) > strtotime( $b ) ? -1 : 1 );
				} );

				$lastFetchDate = reset( $dates );

				if ( ! empty( $lastFetchDate ) )
				{
					update_post_meta( $post[ 'post_id' ], 'fs_fb_last_comment_fetch_date_' . $post[ 'id' ], $lastFetchDate );
				}
			}
		}
	}

	private static function getPosts ()
	{
		$dateInterval = Helper::getOption( 'fb_fetch_comments_for_posts_published_at', 30, [ 7, 14, 21, 30 ] );

		global $_wp_post_type_features;

		$postTypes = [];

		foreach ( $_wp_post_type_features as $postType => $info )
		{
			if ( isset( $info[ 'comments' ] ) && $info[ 'comments' ] )
			{
				$postTypes[] = $postType;
			}
		}

		if ( empty( $postType ) )
		{
			return [];
		}

		$accessTokens = DB::DB()->get_col( DB::DB()->prepare( 'SELECT `account_id` FROM `' . DB::table( 'account_access_tokens' ) . '`' ) );

		if ( empty( $accessTokens ) )
		{
			return [];
		}

		$fbAccounts = DB::DB()->get_col( DB::DB()->prepare( 'SELECT `id` FROM `' . DB::table( 'accounts' ) . '` WHERE `driver`=\'fb\' AND `id` IN (' . implode( ',', array_fill( 0, count( $accessTokens ), '%d' ) ) . ')', $accessTokens ) );

		if ( empty( $fbAccounts ) )
		{
			return [];
		}

		$fbNodes = DB::DB()->get_col( DB::DB()->prepare( 'SELECT `id` FROM `' . DB::table( 'account_nodes' ) . '` WHERE `account_id` IN (' . implode( ',', array_fill( 0, count( $fbAccounts ), '%d' ) ) . ')', $fbAccounts ) );

		if ( empty( $fbNodes ) )
		{
			return [];
		}

		$postTypesIn = DB::DB()->prepare( implode( ',', array_fill( 0, count( $postTypes ), '%s' ) ), $postTypes );
		$nodesIn     = DB::DB()->prepare( implode( ',', array_fill( 0, count( $fbNodes ), '%s' ) ), $fbNodes );

		return DB::DB()->get_results( DB::DB()->prepare( 'WITH `available_posts` AS (SELECT `ID` FROM `' . DB::WPtable( 'posts', TRUE ) . '` WHERE `post_type` IN (' . $postTypesIn . ')) SELECT `id`, `post_id`, `driver_post_id`, `node_id`, `node_type` FROM `' . DB::table( 'feeds' ) . '` WHERE post_id IN (SELECT `ID` FROM `available_posts`) AND (`send_time` BETWEEN NOW() - INTERVAL %s DAY AND NOW()) AND `driver` = \'fb\' AND `status` = \'ok\' AND `node_id` IN (' . $nodesIn . ') AND `node_type` IN (\'ownpage\', \'group\', \'node\') AND `blog_id` = %d', $dateInterval, Helper::getBlogId() ), ARRAY_A );
	}

	private static function insertComment ( $postID, $fbCommentID, $fbCommentParentID, $text, $commentApproved, $author = '', $commentDate = '' )
	{
		$commentData = [
			'comment_post_ID'  => $postID,
			'comment_content'  => $text,
			'comment_author'   => $author,
			'comment_date'     => $commentDate,
			'comment_approved' => $commentApproved
		];

		if ( $fbCommentParentID !== 0 )
		{
			if ( isset( self::$fbid_to_wpid[ $fbCommentParentID ] ) )
			{
				$parentWpID = self::$fbid_to_wpid[ $fbCommentParentID ];
			}
			else
			{
				$savedComment = get_comments( [
					'post_id'    => $postID,
					'fields'     => 'ids',
					'number'     => 1,
					'meta_key'   => '_fs_poster_fb_comment_id',
					'meta_value' => $fbCommentParentID
				] );

				$parentWpID = reset( $savedComment );
			}

			if ( ! empty( $parentWpID ) )
			{
				self::$fbid_to_wpid[ $fbCommentParentID ] = $parentWpID;
				$commentData[ 'comment_parent' ]          = $parentWpID;
			}
		}

		$insertID = wp_insert_comment( $commentData );

		if ( $insertID === FALSE || $fbCommentID === 0 )
		{
			return;
		}

		self::$fbid_to_wpid[ $fbCommentID ] = $insertID;

		add_comment_meta( $insertID, '_fs_poster_fb_comment_id', $fbCommentID, TRUE );
	}

	private static function getNodeInfo ( $nodeType, $nodeID )
	{
		$key = $nodeType . $nodeID;

		if ( ! isset( self::$cachedNodes[ $key ] ) )
		{
			self::$cachedNodes[ $key ] = Helper::getAccessToken( $nodeType, $nodeID );
		}

		return self::$cachedNodes[ $key ];
	}

	private static function getApp ( $appID )
	{
		if ( ! isset( self::$cachedApps[ $appID ] ) )
		{
			self::$cachedApps[ $appID ] = DB::fetch( 'apps', [ 'id' => $appID, 'driver' => 'fb' ] );
		}

		return self::$cachedApps[ $appID ];
	}
}