<?php

class PeepSoNotificationsShortcode
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
        return __('Shows recent notifications.','peepso-core');
    }

    public static function post_state() {
        return _x('PeepSo', 'Page listing', 'peepso-core') . ' - ' . __('Notifications', 'peepso-core');
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
        PeepSo::set_current_shortcode('peepso_notifications');

        wp_enqueue_script('peepso-page-notifications',
            PeepSo::get_asset('js/page-notifications.min.js'),
            array('peepso'),
            PeepSo::PLUGIN_VERSION, TRUE);

        ob_start();
        echo PeepSoTemplate::get_before_markup();
        PeepSoTemplate::exec_template('general', 'notifications');
        echo PeepSoTemplate::get_after_markup();

        return ob_get_clean();
    }
}

// EOF
