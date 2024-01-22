<?php

namespace FSPoster\App\Pages\Accounts\Controllers;

use FSPoster\App\Providers\Curl;
use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

trait Popup
{
    public function edit_fb_account ()
    {
        $id = Request::post( 'account_id' );

        Pages::modal( 'Accounts', 'fb/edit_account', $id );
    }

    public function add_fb_account ()
    {
        $data = Pages::action( 'Accounts', 'get_fb_apps' );

        Pages::modal( 'Accounts', 'fb/add_account', $data );
    }

    public function add_threads_account ()
    {
        Pages::modal( 'Accounts', 'threads/add_account' );
    }

    public function edit_threads_account ()
    {
        $id = Request::post( 'account_id' );

        Pages::modal( 'Accounts', 'threads/edit_account', $id );
    }

    public function add_twitter_account ()
    {
        $data = Pages::action( 'Accounts', 'get_twitter_apps' );

        Pages::modal( 'Accounts', 'twitter/add_account', $data );
    }

    public function add_plurk_account ()
    {
        $data = Pages::action( 'Accounts', 'get_plurk_apps' );

        Pages::modal( 'Accounts', 'plurk/add_account', $data );
    }

    public function add_youtube_community_account ()
    {
        Pages::modal( 'Accounts', 'youtube_community/add_account' );
    }

    public function edit_youtube_community_account ()
    {
        Pages::modal( 'Accounts', 'youtube_community/add_account', Request::post( 'account_id' ) );
    }

   public function add_xing_account ()
    {
        Pages::modal( 'Accounts', 'xing/add_account' );
    }

    public function edit_xing_account ()
    {
        Pages::modal( 'Accounts', 'xing/add_account', Request::post( 'account_id' ) );
    }

    public function add_linkedin_account ()
    {
        $data = Pages::action( 'Accounts', 'get_linkedin_apps' );

        Pages::modal( 'Accounts', 'linkedin/add_account', $data );
    }

    public function add_ok_account ()
    {
        $data = Pages::action( 'Accounts', 'get_ok_apps' );

        Pages::modal( 'Accounts', 'ok/add_account', $data );
    }

    public function add_pinterest_account ()
    {
        $data = Pages::action( 'Accounts', 'get_pinterest_apps' );

        Pages::modal( 'Accounts', 'pinterest/add_account', $data );
    }

    public function edit_pinterest_account ()
    {
        $id = Request::post( 'account_id' );

        Pages::modal( 'Accounts', 'pinterest/edit_account', $id );
    }

    public function add_reddit_account ()
    {
        $data = Pages::action( 'Accounts', 'get_reddit_apps' );

        Pages::modal( 'Accounts', 'reddit/add_account', $data );
    }

    public function add_tumblr_account ()
    {
        $data = Pages::action( 'Accounts', 'get_tumblr_apps' );

        Pages::modal( 'Accounts', 'tumblr/add_account', $data );
    }

    public function reddit_add_subreddit ()
    {
        $data = Pages::action( 'Accounts', 'get_subreddit_info' );

        Pages::modal( 'Accounts', 'reddit/add_subreddit', $data );
    }

    public function add_vk_account ()
    {
        $data = Pages::action( 'Accounts', 'get_vk_apps' );

        Pages::modal( 'Accounts', 'vk/add_account', $data );
    }

    public function add_instagram_account ()
    {
        $data = Pages::action( 'Accounts', 'get_instagram_apps' );

        Pages::modal( 'Accounts', 'instagram/add_account', $data );
    }

    public function edit_instagram_account ()
    {
        $id = Request::post( 'account_id' );

        Pages::modal( 'Accounts', 'instagram/edit_account', $id );
    }

    public function edit_twitter_account ()
    {
        $id = Request::post( 'account_id' );

        Pages::modal( 'Accounts', 'twitter/edit_account', $id );
    }

    public function edit_webhook_account ()
    {
        $id = Request::post( 'account_id' );

        $accountInfo = DB::fetch( 'accounts', [
            'id'     => $id,
            'driver' => 'webhook'
        ] );

        if ( ! isset( $accountInfo[ 'options' ] ) )
        {
            Helper::response( FALSE );
        }

        $fsp_params = json_decode( $accountInfo[ 'options' ], TRUE );

        $fsp_params[ 'id' ] = $id;

        Pages::modal( 'Accounts', 'webhook/edit_account', $fsp_params );
    }

	public function add_mastodon_account ()
	{
		$data = Pages::action( 'Accounts', 'get_mastodon_apps' );

		Pages::modal( 'Accounts', 'mastodon/add_account', $data );
	}

    public function activate_with_condition ()
    {
        $id   = Request::post( 'id', 0, 'num' );
        $ids  = Request::post( 'ids', 0, 'array' );
        $type = Request::post( 'type', '', 'string' );

        if ( $id > 0 && ! empty( $type ) )
        {
            if ( $type === 'node' )
            {
                $ajaxUrl   = 'settings_node_activity_change';
                $tableName = 'account_node_status';
                $fieldName = 'node_id';

                $forAll = DB::DB()->get_row( DB::DB()->prepare( 'SELECT for_all FROM ' . DB::table( 'account_nodes' ) . ' WHERE id = %d',
                    [
                        $id
                    ] ) )->for_all;
            }
            else
            {
                $ajaxUrl   = 'account_activity_change';
                $tableName = 'account_status';
                $fieldName = 'account_id';

                $forAll = DB::DB()->get_row( DB::DB()->prepare( 'SELECT for_all FROM ' . DB::table( 'accounts' ) . ' WHERE id = %d',
                    [
                        $id
                    ] ) )->for_all;
            }

            $info        = DB::fetch( $tableName, [ $fieldName => $id, 'user_id' => get_current_user_id() ] );
            $filter_type = $info ? $info[ 'filter_type' ] : 'in';
            $categories  = $info && ! empty( $info[ 'categories' ] ) ? explode( ',', $info[ 'categories' ] ) : [];
        }
        else
        {
            $ajaxUrl     = 'bulk_activate_conditionally';
            $filter_type = '';
            $categories  = [];
            $forAll      = 0;
        }

        Pages::modal( 'Accounts', 'activate_with_condition', [
            'id'          => $id,
            'ids'         => $ids,
            'ajaxUrl'     => $ajaxUrl,
            'filter_type' => $filter_type,
            'categories'  => $categories,
            'for_all'     => $forAll
        ] );
    }

    public function add_google_b_account ()
    {
        $data = Pages::action( 'Accounts', 'get_google_b_apps' );

        Pages::modal( 'Accounts', 'google_b/add_account', $data );
    }

    public function edit_google_b_account ()
    {
        $id = Request::post( 'account_id' );

        Pages::modal( 'Accounts', 'google_b/edit_account', $id );
    }

    public function add_blogger_account ()
    {
        $data = Pages::action( 'Accounts', 'get_blogger_apps' );

        Pages::modal( 'Accounts', 'blogger/add_account', $data );
    }

    public function add_telegram_bot ()
    {
        Pages::modal( 'Accounts', 'telegram/add_bot' );
    }

    public function telegram_add_chat ()
    {
        Pages::modal( 'Accounts', 'telegram/add_chat', [
            'accountId' => (int)Request::post( 'account_id', '0', 'num' )
        ] );
    }

    public function discord_add_channel ()
    {
        Pages::modal( 'Accounts', 'discord/add_channel', [
            'accountId' => (int)Request::post( 'account_id', '0', 'num' )
        ] );
    }

    public function add_medium_account ()
    {
        $data = Pages::action( 'Accounts', 'get_medium_apps' );

        Pages::modal( 'Accounts', 'medium/add_account', $data );
    }

    public function add_discord_account ()
    {
        $data = Pages::action( 'Accounts', 'get_discord_apps' );

        Pages::modal( 'Accounts', 'discord/add_account', $data );
    }

    public function add_wordpress_site ()
    {
        Pages::modal( 'Accounts', 'wordpress/add_site' );
    }

    public function add_webhook_account ()
    {
        $data = Pages::action( 'Accounts', 'fetch_remote_webhooks' );

        Pages::modal( 'Accounts', 'webhook/add_account', $data );
    }

    public function add_planly_account ()
    {
        Pages::modal( 'Accounts', 'planly/add_account');
    }

    public function change_fb_group_poster ()
    {
        $account_id = Request::post( 'account_id', 0, 'int' );
        $group_id   = Request::post( 'group_id', 0, 'int' );
        $data       = Pages::action( 'Accounts', 'get_fb_pages', [
            'account_id' => $account_id,
            'group_id'   => $group_id
        ] );

        Pages::modal( 'Accounts', 'fb/change_group_poster', [ 'pages' => $data, 'group_id' => $group_id ] );
    }

    public function node_custom_settings ()
    {
        $node_id   = Request::post( 'node_id', 0, 'int' );
        $node_type = Request::post( 'node_type', '', 'string', [ 'account', 'node' ] );

        $table = $node_type === 'account' ? 'accounts' : 'account_nodes';

        $node_driver = DB::DB()->get_row( DB::DB()->prepare( 'SELECT driver FROM `' . DB::table( $table ) . '` WHERE id=%d',
            [
                $node_id
            ] ), 'ARRAY_A' );

        $node_data = Action::getNodeCustomPostingTypeSettings( $node_driver[ 'driver' ] );

        $supportsInstagramBioLink = FALSE;

        if ( $node_driver[ 'driver' ] == 'instagram' && $node_type == 'account' )
        {
            $password = DB::DB()->get_row( DB::DB()->prepare( 'SELECT `password` FROM `' . DB::table( $table ) . '` WHERE id=%d',
                [
                    $node_id
                ] ), 'ARRAY_A' );

            $supportsInstagramBioLink = $password[ 'password' ] != '#####';
        }

        $supportsFbFetchingComments = FALSE;
        if ( $node_driver[ 'driver' ] === 'fb' && $node_type === 'node' )
        {
            $options                    = DB::DB()->get_row( DB::DB()->prepare( 'SELECT access_token from ' . DB::table( 'account_nodes' ) . ' where id = %d LIMIT 1', [
                $node_id
            ] ), ARRAY_A );
            $supportsFbFetchingComments = ! empty( $options[ 'access_token' ] ) ? 1 : 0;
        }

        $supportsFirstComment     = false;
        $firstCommentSupportedSNs = [ 'instagram', 'reddit', 'twitter' ];

        if ( in_array( $node_driver[ 'driver' ], $firstCommentSupportedSNs ) )
        {
            $supportsFirstComment = true;
        }


        $account_custom_post_message = Helper::getCustomSetting( 'account_post_message', '', $node_type, $node_id );
        if ( empty( $account_custom_post_message ) )
        {
            $default_post_messages       = [
                'fb'                => "{title}",
                'fb_h'              => "{title}",
                'instagram'         => "{title}",
                'instagram_h'       => "{title}",
                'twitter'           => "{title}",
                'planly'            => "{content_full}",
                'linkedin'          => "{title}",
                'pinterest'         => "{content_short_497}",
                'telegram'          => "{title}",
                'reddit'            => "{title}",
                'youtube_community' => "{content_full}",
                'tumblr'            => "<img src='{featured_image_url}'>\n\n{content_full}",
                'ok'                => "{title}",
                'vk'                => "{title}",
                'google_b'          => "{title}",
                'medium'            => "<img src='{featured_image_url}'>\n\n{content_full}\n\n<a href='{link}'>{link}</a>",
                'wordpress'         => "{content_full}",
                'blogger'           => "<img src='{featured_image_url}'>\n\n{content_full} \n\n<a href='{link}'>{link}</a>",
                'plurk'             => "{title}\n\n{featured_image_url}\n\n{content_short_200}",
                'xing'              => "{content_full}",
                'discord'           => "{title}",
                'mastodon'          => "{title}",
            ];
            $account_custom_post_message = Helper::getOption( 'post_text_message_' . $node_driver[ 'driver' ] ) ?: $default_post_messages[ $node_driver[ 'driver' ] ];
        }

        Pages::modal( 'Accounts', 'settings', [
            'supportsInstagramBioLink' => $supportsInstagramBioLink,
            'supportsFirstComment'     => $supportsFirstComment,
            'supportsFetchingComments' => $supportsFbFetchingComments,
            'driver'                   => $node_driver[ 'driver' ],
            'node_type'                => $node_type,
            'node_id'                  => $node_id,
            'node_data'                => $node_data,
            'post_message'             => $account_custom_post_message
        ] );
    }

    public function create_group ()
    {
        Pages::modal( 'Accounts', 'groups/add' );
    }

    //Edits what groups a node belongs
    public function edit_node_groups ()
    {
        $data = Pages::action( 'Accounts', 'get_node_groups' );
        Pages::modal( 'Accounts', 'groups/edit_node_groups', $data );
    }

    public function edit_account_group ()
    {
        $group_id = Request::post( 'group_id', '', 'num' );

        $group = DB::fetch( 'account_groups', [
            'id'      => $group_id,
            'user_id' => get_current_user_id(),
            'blog_id' => Helper::getBlogId()
        ] );

        if ( empty( $group ) )
        {
            Helper::response( FALSE );
        }

        Pages::modal( 'Accounts', 'groups/edit', [
            'id'   => $group[ 'id' ],
            'name' => $group[ 'name' ]
        ] );
    }
}