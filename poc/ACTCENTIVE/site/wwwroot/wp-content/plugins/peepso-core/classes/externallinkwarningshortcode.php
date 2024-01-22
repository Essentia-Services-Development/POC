<?php

class PeepSoExternalLinkWarningShortcode
{
	public function __construct()
	{
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
	}

	/**
	 * Enqueues the scripts used in this shortcode only.
	 */
	public function enqueue_scripts()
	{

	}

	public static function description() {
        return __('Depending on your configuration, when clicking a link posted within your community users will be warned if the link clicked leads out of your website.','peepso-core');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'peepso-core') . ' - ' . __('External link warning', 'peepso-core');
    }

	/**
	 * Displays the member search page.
	 */
	public function do_shortcode()
	{
	    if(PeepSo::is_api_request()) {
	        return;
        }

	    PeepSo::do_not_cache();

	    PeepSo::reset_query();
		PeepSo::set_current_shortcode('peepso_external_link_warning');

		ob_start();
		echo PeepSoTemplate::get_before_markup();
        PeepSoTemplate::exec_template('general', 'external-link-warning');
		echo PeepSoTemplate::get_after_markup();

		return ob_get_clean();
	}
}

// EOF
