<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Pages\Base\Controllers\Ajax as BaseAjax;
use FSPoster\App\Pages\Logs\Controllers\Ajax as LogsAjax;
use FSPoster\App\Pages\Apps\Controllers\Ajax as AppsAjax;
use FSPoster\App\Pages\Share\Controllers\Ajax as ShareAjax;
use FSPoster\App\Pages\Accounts\Controllers\Ajax as AccountsAjax;
use FSPoster\App\Pages\Settings\Controllers\Ajax as SettingsAjax;
use FSPoster\App\Pages\Schedules\Controllers\Ajax as SchedulesAjax;

Helper::disableDebug();

class Ajax
{
	use BaseAjax, AccountsAjax, SchedulesAjax, ShareAjax, LogsAjax, AppsAjax, SettingsAjax;

	public function __construct ()
	{
		if ( Helper::canLoadPlugin() )
		{
			$methods = get_class_methods( $this );

			foreach ( $methods as $method )
			{
				if ( $method === '__construct' )
				{
					continue;
				}

				add_action( 'wp_ajax_' . $method, function () use ( $method ) {
					$this->$method();

					exit;
				} );
			}
		}
		else
		{
			add_action( 'wp_ajax_reactivate_app', function () {
				$this->reactivate_app();

				exit;
			} );

			add_action( 'wp_ajax_activate_app', function () {
				$this->activate_app();

				exit;
			} );

			add_action( 'wp_ajax_update_app', function () {
				$this->update_app();

				exit;
			} );
		}
	}

	public function activate_app ()
	{

		$code      = Request::post( 'code', '', 'string' );
		$statistic = Request::post( 'statistic', '', 'string' );
		$email     = Request::post( 'email', '', 'string' );

		if ( empty( $code ) )
		{
			Helper::response( FALSE, fsp__( 'Please type purchase key!' ) );
		}

		if ( empty( $statistic ) )
		{
			Helper::response( FALSE, fsp__( 'Please select how did You find us!' ) );
		}

		if ( Helper::getOption( 'poster_plugin_installed', '0', TRUE ) )
		{
			Helper::response( FALSE, fsp__( 'Your plugin is already installed!' ) );
		}

		set_time_limit( 0 );

		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=install&version=" . urlencode( Helper::getVersion() ) . "&purchase_code=" . urlencode( $code ) . "&domain=" . urlencode( network_site_url() ) . "&statistic=" . urlencode( $statistic ) . '&email=' . urlencode( $email );
		$result2              = Curl::getURL( $checkPurchaseCodeURL );

		$result = json_decode( $result2, TRUE );
		if ( ! is_array( $result ) )
		{
			if ( empty( $result2 ) )
			{
				Helper::response( FALSE, fsp__( 'Your server can not access our license server via CURL! Our license server is "%s". Please contact your hosting provider/server administrator and ask them to solve the problem. If you are sure that problem is not your server/hosting side then contact FS Poster administrators.', [ htmlspecialchars( FS_API_URL ) ] ) );
			}

			Helper::response( FALSE, fsp__( 'Installation error! Response error! Response: %s', [ htmlspecialchars( $result2 ) ] ) );
		}

		if ( ! ( $result[ 'status' ] === 'ok' && isset( $result[ 'sql' ] ) ) )
		{
			Helper::response( FALSE, isset( $result[ 'error_msg' ] ) ? $result[ 'error_msg' ] : 'Error! Response: ' . htmlspecialchars( $result2 ) );
		}

		$sql = str_replace( [
			'{tableprefix}',
			'{tableprefixbase}'
		], [
			( DB::DB()->base_prefix . DB::PLUGIN_DB_PREFIX ),
			DB::DB()->base_prefix
		], base64_decode( $result[ 'sql' ] ) );

		if ( empty( $sql ) )
		{
			Helper::response( FALSE, fsp__( 'Error! Please contact the plugin administration! (info@fs-code.com)' ) );
		}

		foreach ( explode( ';', $sql ) as $sqlQueryOne )
		{
			$checkIfEmpty = preg_replace( '/\s/', '', $sqlQueryOne );

			if ( ! empty( $checkIfEmpty ) )
			{
				DB::DB()->query( $sqlQueryOne );
			}
		}

		register_uninstall_hook( FS_ROOT_DIR . '/init.php', [ Helper::class, 'removePlugin' ] );

		Helper::setOption( 'poster_plugin_installed', Helper::getVersion(), TRUE );
		Helper::setOption( 'poster_plugin_purchase_key', $code, TRUE );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Plugin installed!' ) ] );
	}

	public function update_app ()
	{
		$code = Request::post( 'code', '', 'string' );

		if ( empty( $code ) )
		{
			Helper::response( FALSE, fsp__( 'Please type purchase key!' ) );
		}

		if ( Helper::getOption( 'poster_plugin_installed', '0', TRUE ) == Helper::getVersion() )
		{
			Helper::response( FALSE, fsp__( 'Your plugin is already updated!' ) );
		}

		$result = self::updatePlugin( $code );

		if ( $result[ 0 ] )
		{
			Helper::response( TRUE, [ 'msg' => fsp__( 'Plugin updated!' ) ] );
		}
		else
		{
			Helper::response( FALSE, $result[ 1 ] );
		}
	}

	public function reactivate_app ()
	{
		$code = Request::post( 'code', '', 'string' );

		if ( empty( $code ) )
		{
			Helper::response( FALSE, fsp__( 'Please enter the purchase code!' ) );
		}

		if ( Helper::getOption( 'poster_plugin_installed', '0', TRUE ) == Helper::getVersion() )
		{
			Helper::response( FALSE, fsp__( 'Your plugin is already activated!' ) );
		}

		set_time_limit( 0 );

		$check_purchase_code = FS_API_URL . 'api.php?act=reactivate&version=' . urlencode( Helper::getVersion() ) . '&purchase_code=' . urlencode( $code ) . '&domain=' . urlencode( network_site_url() );
		$api_result          = Curl::getURL( $check_purchase_code );

		if ( empty( $api_result ) )
		{
			Helper::response( FALSE, fsp__( 'Your server can not access our license server via CURL! Our license server is "%s". Please contact your hosting provider/server administrator and ask them to solve the problem. If you are sure that problem is not your server/hosting side then contact FS Poster administrators.', [ htmlspecialchars( FS_API_URL ) ] ) );
		}

		$result = json_decode( $api_result, TRUE );

		if ( ! is_array( $result ) || $result[ 'status' ] !== 'ok' )
		{
			Helper::response( FALSE, isset( $result[ 'error_msg' ] ) ? $result[ 'error_msg' ] : fsp__( 'Reactivation failed! Response: %s', [ htmlspecialchars( $api_result ) ] ) );
		}

		Helper::setOption( 'plugin_disabled', '0', TRUE );
		Helper::setOption( 'plugin_alert', '', TRUE );
		Helper::setOption( 'poster_plugin_installed', Helper::getVersion(), TRUE );
		Helper::setOption( 'poster_plugin_purchase_key', $code, TRUE );

		Helper::response( TRUE, [ 'msg' => fsp__( 'Plugin reactivated!' ) ] );
	}

	public static function updatePlugin ( $code )
	{
		set_time_limit( 0 );

		register_uninstall_hook( FS_ROOT_DIR . '/init.php', [ Helper::class, 'removePlugin' ] );

		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=update&version1=" . Helper::getInstalledVersion() . "&version2=" . Helper::getVersion() . "&purchase_code=" . $code . "&domain=" . network_site_url();
		$result2              = Curl::getURL( $checkPurchaseCodeURL );

		$result = json_decode( $result2, TRUE );

		if ( ! is_array( $result ) )
		{
			if ( empty( $result2 ) )
			{
				return [
					FALSE,
					fsp__( 'Your server can not access our license server via CURL! Our license server is "%s". Please contact your hosting provider/server administrator and ask them to solve the problem. If you are sure that problem is not your server/hosting side then contact FS Poster administrators.', [ htmlspecialchars( FS_API_URL ) ] )
				];
			}

			return [
				FALSE,
				fsp__( 'Installation error! Response error! Response: %s', [ htmlspecialchars( $result2 ) ] )
			];
		}

		if ( ! ( $result[ 'status' ] === 'ok' && isset( $result[ 'sql' ] ) ) )
		{
			return [
				FALSE,
				isset( $result[ 'error_msg' ] ) ? $result[ 'error_msg' ] : 'Error! Response: ' . htmlspecialchars( $result2 )
			];
		}

		$sql = str_replace( [
			'{tableprefix}',
			'{tableprefixbase}'
		], [
			( DB::DB()->base_prefix . DB::PLUGIN_DB_PREFIX ),
			DB::DB()->base_prefix
		], base64_decode( $result[ 'sql' ] ) );

		foreach ( explode( ';', $sql ) as $sqlQueryOne )
		{
			$checkIfEmpty = preg_replace( '/\s/', '', $sqlQueryOne );

			if ( ! empty( $checkIfEmpty ) )
			{
				DB::DB()->query( $sqlQueryOne );
			}
		}

		Helper::setOption( 'poster_plugin_installed', Helper::getVersion(), TRUE );
		Helper::setOption( 'poster_plugin_purchase_key', $code, TRUE );

		return [ TRUE ];
	}

	private function getIcon( $driver )
	{
		switch ( $driver )
		{
			case 'fb':
				return 'fab fa-facebook';
			case 'instagram':
				return 'fab fa-instagram';
			case 'twitter':
				return 'fab fa-twitter';
			case 'planly':
				return 'planly-icon planly-icon-12';
			case 'linkedin':
				return 'fab fa-linkedin';
			case 'pinterest':
				return 'fab fa-pinterest';
			case 'telegram':
				return 'fab fa-telegram';
			case 'reddit':
				return 'fab fa-reddit';
			case 'youtube_community':
				return 'fab fa-youtube-square';
			case 'tumblr':
				return 'fab fa-tumblr';
			case 'ok':
				return 'fab fa-odnoklassniki';
			case 'vk':
				return 'fab fa-vk';
			case 'google_b':
				return 'fab fa-google';
			case 'medium':
				return 'fab fa-medium';
			case 'wordpress':
				return 'fab fa-wordpress';
			case 'webhook':
				return 'fas fa-atlas';
			case 'blogger':
				return 'fab fa-blogger';
			case 'plurk':
				return 'fas fa-parking';
			case 'xing':
				return 'fab fa-xing';
			case 'discord':
				return 'fab fa-discord';
			case 'mastodon':
				return 'fab fa-mastodon';
            case 'threads':
                return 'threads-icon threads-icon-12';
			default:
				return '';
		}
	}
}
