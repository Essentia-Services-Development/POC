<?php

namespace FSPoster\App\Providers;

use WP_Async_Request;

class BackgrouondProcess extends WP_Async_Request
{
	/**
	 * @var string
	 */
	protected $action = 'fs_poster_background_process';

	/**
	 * Handle
	 *
	 * Override this method to perform any actions required
	 * during the async request.
	 */
	protected function handle ()
	{
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

		if ( $fb_diff > 43200 && Helper::getOption( 'fetch_fb_comments' ) ) {
			Helper::setOption( 'fb_cron_job_runned_on', Date::epoch(), FALSE, FALSE );
			Helper::setOption( 'fb_real_cron_job_runned_on', Date::epoch(), FALSE, FALSE );

			CommentService::fetchFacebookComments();
		}
	}

	public function getAction ()
	{
		return $this->action;
	}
}
