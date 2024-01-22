<?php

namespace FSPoster\App\Providers;

trait PluginMenu
{
	public function initMenu ()
	{
		add_action( 'init', function () {
			$this->getNotifications();
			$res1 = $this->checkLicense();

			if ( FALSE === $res1 )
			{
				return;
			}

			$plgnVer = Helper::getOption( 'poster_plugin_installed', '0', TRUE );

			if ( Helper::isHiddenUser() )
			{
				return;
			}

			if ( empty( $plgnVer ) )
			{
				add_action( 'admin_menu', function () {
					add_menu_page( 'FS Poster', 'FS Poster', 'read', 'fs-poster', function () {
						Pages::controller( 'Base', 'App', 'install' );
					}, Pages::asset( 'Base', 'img/logo_xs.png' ), 90 );
				} );

				return;
			}
			else
			{
				if ( $plgnVer != Helper::getVersion() )
				{
					$fsPurchaseKey = Helper::getOption( 'poster_plugin_purchase_key', '', TRUE );

					if ( $fsPurchaseKey != '' )
					{
						$result = Ajax::updatePlugin( $fsPurchaseKey );
						if ( $result[ 0 ] == FALSE )
						{
							add_action( 'admin_menu', function () {
								add_menu_page( 'FS Poster', 'FS Poster', 'read', 'fs-poster', function () {
									Pages::controller( 'Base', 'App', 'update' );
								}, Pages::asset( 'Base', 'img/logo_xs.png' ), 90 );
							} );

							return;
						}
					}
					else
					{
						add_action( 'admin_menu', function () {
							add_menu_page( 'FS Poster', 'FS Poster', 'read', 'fs-poster', function () {
								Pages::controller( 'Base', 'App', 'update' );
							}, Pages::asset( 'Base', 'img/logo_xs.png' ), 90 );
						} );

						return;
					}
				}
			}

			add_action( 'admin_menu', function () {
				add_menu_page( 'FS Poster', 'FS Poster', 'read', 'fs-poster', [
					Pages::class,
					'load_page'
				], Pages::asset( 'Base', 'img/logo_xs.png' ), 90 );

				add_submenu_page( 'fs-poster', fsp__( 'Dashboard' ), fsp__( 'Dashboard' ), 'read', 'fs-poster', [
					Pages::class,
					'load_page'
				] );

				add_submenu_page( 'fs-poster', fsp__( 'Accounts' ), fsp__( 'Accounts' ), 'read', 'fs-poster-accounts', [
					Pages::class,
					'load_page'
				] );

				add_submenu_page( 'fs-poster', fsp__( 'Schedules' ), fsp__( 'Schedules' ), 'read', 'fs-poster-schedules', [
					Pages::class,
					'load_page'
				] );

				add_submenu_page( 'fs-poster', fsp__( 'Direct Share' ), fsp__( 'Direct Share' ), 'read', 'fs-poster-share', [
					Pages::class,
					'load_page'
				] );

				add_submenu_page( 'fs-poster', fsp__( 'Logs' ), fsp__( 'Logs' ), 'read', 'fs-poster-logs', [
					Pages::class,
					'load_page'
				] );

				add_submenu_page( 'fs-poster', fsp__( 'Apps' ), fsp__( 'Apps' ), 'read', 'fs-poster-apps', [
					Pages::class,
					'load_page'
				] );

				if ( ( current_user_can( 'administrator' ) || defined( 'FS_POSTER_IS_DEMO' ) ) )
				{
					add_submenu_page( 'fs-poster', fsp__( 'Settings' ), fsp__( 'Settings' ), 'read', 'fs-poster-settings', [
						Pages::class,
						'load_page'
					] );
				}
			} );
		} );
	}

	public function app_disable ()
	{
		register_uninstall_hook( FS_ROOT_DIR . '/init.php', [ Helper::class, 'removePlugin' ] );

		Helper::deleteOption( 'poster_plugin_installed', TRUE );

		Pages::controller( 'Base', 'App', 'disable' );
	}

	public function getNotifications ()
	{
		$lastTime = Helper::getOption( 'license_last_checked_time', 0 );

		if ( Date::epoch() - $lastTime < 10 * 60 * 60 )
		{
			return;
		}

		$fsPurchaseKey = Helper::getOption( 'poster_plugin_purchase_key', '', TRUE );

		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=get_notifications&purchase_code=" . $fsPurchaseKey . "&domain=" . network_site_url();
		$result2              = Curl::getURL( $checkPurchaseCodeURL );
		$result               = json_decode( $result2, TRUE );

		if ( empty( $result ) )
		{
			return;
		}

		if ( $result[ 'action' ] === 'empty' )
		{
			Helper::setOption( 'plugin_alert', '', TRUE );
			Helper::setOption( 'plugin_disabled', '0', TRUE );
		}
		else if ( $result[ 'action' ] === 'warning' && ! empty( $result[ 'message' ] ) )
		{
			Helper::setOption( 'plugin_alert', $result[ 'message' ], TRUE );
			Helper::setOption( 'plugin_disabled', '0', TRUE );
		}
		else if ( $result[ 'action' ] === 'disable' )
		{
			if ( ! empty( $result[ 'message' ] ) )
			{
				Helper::setOption( 'plugin_alert', $result[ 'message' ], TRUE );
			}

			Helper::setOption( 'plugin_disabled', '1', TRUE );
		}
		else if ( $result[ 'action' ] === 'error' )
		{
			if ( ! empty( $result[ 'message' ] ) )
			{
				Helper::setOption( 'plugin_alert', $result[ 'message' ], TRUE );
			}

			Helper::setOption( 'plugin_disabled', '2', TRUE );
		}

		if ( ! empty( $result[ 'remove_license' ] ) )
		{
			Helper::deleteOption( 'poster_plugin_purchase_key', TRUE );
		}

		Helper::setOption( 'license_last_checked_time', Date::epoch() );
	}

	public function checkLicense ()
	{
		$alert    = Helper::getOption( 'plugin_alert', '', TRUE );
		$disabled = Helper::getOption( 'plugin_disabled', '0', TRUE );

		if ( $disabled === '1' )
		{
			add_action( 'admin_menu', function () {
				add_menu_page( 'FS Poster (!)', 'FS Poster (!)', 'read', 'fs-poster', [
					$this,
					'app_disable'
				], Pages::asset( 'Base', 'img/logo_xs.png' ), 90 );
			} );

			return FALSE;
		}
		else if ( $disabled === '2' )
		{
			if ( ! empty( $alert ) )
			{
				echo $alert;
			}

			exit();
		}

		if ( ! empty( $alert ) )
		{
			add_action( 'admin_notices', function () use ( $alert ) {
				?>
				<div class="notice notice-error">
					<p><?php fsp__( $alert ); ?></p>
				</div>
				<?php
			} );
		}

		return TRUE;
	}
}
