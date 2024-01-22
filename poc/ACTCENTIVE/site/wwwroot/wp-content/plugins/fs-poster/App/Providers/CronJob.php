<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Libraries\planly\Planly;

class CronJob
{
	private static $reScheduledList = [];
	/**
	 * @var BackgrouondProcess
	 */
	private static $backgroundProcess;

	public static function init ()
	{
		self::$backgroundProcess = new BackgrouondProcess();

		$last_runned_on = Helper::getOption( 'cron_job_runned_on', 0 );
		$last_runned_on = is_numeric( $last_runned_on ) ? $last_runned_on : 0;
		$diff           = Date::epoch() - $last_runned_on;

		if ( $diff < 50 )
		{
			return;
		}

		if ( defined( 'DOING_CRON' ) )
		{
			Helper::setOption( 'cron_job_runned_on', Date::epoch(), FALSE, FALSE );
			Helper::setOption( 'real_cron_job_runned_on', Date::epoch(), FALSE, FALSE );

			add_action( 'init', function () {
				set_time_limit( 0 );

				ShareService::shareQueuedFeeds();
				ShareService::shareSchedules();

				if ( Helper::getOption( 'check_accounts', 1 ) && Helper::getOption( 'check_accounts_last', Date::epoch( 'now', '-2 days' ) ) < Date::epoch( 'now', '-1 day' ) )
				{
					AccountService::checkAccounts();
				}

				$fb_last_runned_on = Helper::getOption( 'fb_cron_job_runned_on', 0 );
				$fb_last_runned_on = is_numeric( $fb_last_runned_on ) ? $fb_last_runned_on : 0;
				$fb_diff           = Date::epoch() - $fb_last_runned_on;

				if ( $fb_diff > 43200 && Helper::getOption( 'fetch_fb_comments' ) )
				{
					Helper::setOption( 'fb_cron_job_runned_on', Date::epoch(), FALSE, FALSE );
					Helper::setOption( 'fb_real_cron_job_runned_on', Date::epoch(), FALSE, FALSE );

					CommentService::fetchFacebookComments();
				}

				$planly_last_runned_on = ( int ) Helper::getOption( 'planly_cron_job_runned_on', 0 );
				$planly_diff           = Date::epoch() - $planly_last_runned_on;

				$processingFeeds = DB::DB()->get_results( DB::DB()->prepare( 'select id, node_id, driver_post_id as schedule_id from `' . DB::table( 'feeds' ) . '` where driver=%s and status=%s', 'planly', 'processing' ), ARRAY_A );

				if ( $planly_diff > 180 && count( $processingFeeds ) > 0 )
				{
					Helper::setOption( 'planly_cron_job_runned_on', Date::epoch(), FALSE, FALSE );

					foreach ( $processingFeeds as $feed )
					{
						$node = DB::DB()->get_row( DB::DB()->prepare( 'select nodes.node_id as channel_id, accounts.options as api_key, accounts.proxy from `' . DB::table( 'account_nodes' ) . '` nodes left join `' . DB::table( 'accounts' ) . '` accounts  on accounts.id=nodes.account_id where nodes.id=%d', $feed[ 'node_id' ] ), ARRAY_A );

						if ( ! empty( $node[ 'channel_id' ] ) )
						{
							$planly = ( new Planly( $node[ 'api_key' ], $node[ 'proxy' ] ) )->getSchedule( $feed[ 'schedule_id' ] );

							if ( isset( $planly[ 'status' ] ) )
							{
								$schedule = $planly[ 'schedule' ];

								if ( $schedule[ 'status' ] == 4 )
								{
									DB::DB()->update( DB::table( 'feeds' ), [
										'status'    => 'error',
										'error_msg' => fsp__( 'Post failed' ),
									], [ 'id' => $feed[ 'id' ] ] );
								}
								else if ( $schedule[ 'status' ] == 3 )
								{
									DB::DB()->update( DB::table( 'feeds' ), [
										'status'          => 'ok',
										'driver_post_id2' => $schedule[ 'url' ]
									], [ 'id' => $feed[ 'id' ] ] );
								}
							}
							else
							{
								DB::DB()->update( DB::table( 'feeds' ), [
									'status'    => 'error',
									'error_msg' => $planly[ 'error_msg' ]
								], [ 'id' => $feed[ 'id' ] ] );
							}
						}
					}
				}
			}, 100000 );
		}
		else if ( Helper::getOption( 'virtual_cron_job_disabled', '0' ) != '1' )
		{
			Helper::setOption( 'cron_job_runned_on', Date::epoch(), FALSE, FALSE );

			if ( ! self::isThisProcessBackgroundTask() )
			{
				self::runBackgroundTaksIfNeeded();
			}
		}
	}

	public static function runBackgroundTaksIfNeeded ()
	{
		$notSendedFeeds = DB::DB()->prepare( 'SELECT COUNT(0) as `feed_count` FROM `' . DB::table( 'feeds' ) . '` WHERE `share_on_background`=1 and `is_sended`=0 and `send_time`<=%s', [ Date::dateTimeSQL() ] );
		$notSendedFeeds = DB::DB()->get_row( $notSendedFeeds, ARRAY_A );

		if ( $notSendedFeeds[ 'feed_count' ] > 0 )
		{
			add_action( 'init', function () {
				self::$backgroundProcess->dispatch();
			}, 100000 );
		}
		else
		{
			$schdules = DB::DB()->prepare( 'SELECT COUNT(0) as `schedule_count` FROM `' . DB::table( 'schedules' ) . '` WHERE `status`=\'active\' and `next_execute_time`<=%s', [ Date::dateTimeSQL() ] );
			$schdules = DB::DB()->get_row( $schdules, ARRAY_A );

			if ( $schdules[ 'schedule_count' ] > 0 )
			{
				add_action( 'init', function () {
					self::$backgroundProcess->dispatch();
				}, 100000 );
			}
			else
			{
				$notCheckedAccounts = DB::DB()->get_row( 'SELECT COUNT(0) as `account_count` FROM ' . DB::table( 'accounts' ) . ' WHERE ((id IN (SELECT account_id FROM ' . DB::table( 'account_status' ) . ')) OR (id IN (SELECT account_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (SELECT node_id FROM ' . DB::table( 'account_node_status' ) . '))))', ARRAY_A );

				if ( $notCheckedAccounts[ 'account_count' ] > 0 )
				{
					add_action( 'init', function () {
						self::$backgroundProcess->dispatch();
					}, 100000 );
				}
			}
		}
	}

	public static function isThisProcessBackgroundTask ()
	{
		$action = Request::get( 'action' );

		return $action === self::$backgroundProcess->getAction();
	}
}
