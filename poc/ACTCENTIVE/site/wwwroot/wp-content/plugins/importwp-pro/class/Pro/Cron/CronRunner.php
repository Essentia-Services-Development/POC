<?php

namespace ImportWP\Pro\Cron;

use ImportWP\Common\Model\ExporterModel;
use ImportWP\Common\Model\ImporterModel;
use ImportWP\Common\Util\Logger;

abstract class CronRunner
{
    /**
     * Scheduled event action hook name.
     *
     * @var string
     */
    protected $_cron_main_handle = 'iwp_cron_runner';

    protected $_last_ran_key = '_iwp_cron_last_ran';

    protected $_scheduled_key = '_iwp_cron_scheduled';

    protected $_post_type = 'post';

    protected $_cron_limit = 1;

    /**
     * @var EventHandler
     */
    protected $event_handler;

    abstract function run();

    /**
     * Load Scheduled Model
     * 
     * Return Model with an enabled schedule.
     *
     * @param ExporterModel|ImporterModel|\WP_Post $post
     * @return false|ExporterModel|ImporterModel
     */
    abstract function get_scheduled_item($post);
    abstract function unschedule($id);
    abstract function get_cron_setting($item);
    abstract function get_item_status($id, $scheduled_time, $last_ran);

    /**
     * No scheduled items, attach to running items
     * 
     * @return void 
     */
    abstract function no_scheduled_items();

    /**
     * @param EventHandler $event_handler
     */
    public function __construct($event_handler)
    {
        $this->event_handler = $event_handler;

        add_action('init', [$this, 'register_cron_runner']);
        add_filter('cron_schedules', [$this, 'register_cron_interval']);

        add_action($this->_cron_main_handle, [$this, 'run']);
    }

    public function calculate_scheduled_time($schedule, $day = 0, $hour = 0, $minute = 0, $current_time = null)
    {
        $minute_padded = str_pad($minute, 2, 0, STR_PAD_LEFT);
        $hour_padded = str_pad($hour, 2, 0, STR_PAD_LEFT);
        $day_padded = str_pad($day, 2, 0, STR_PAD_LEFT);
        $time_offset = 0; //$this->calculate_time_offset();
        $current_time = !is_null($current_time) ? $current_time : time();
        $scheduled_time = false;

        switch ($schedule) {
            case 'month':
                // 1-31

                // 31st of feb, should = 28/29
                if (date('t', $current_time) < $day) {
                    $day_padded = str_pad(date('t', $current_time), 2, 0, STR_PAD_LEFT);
                }

                $scheduled_time = $time_offset + strtotime(date('Y-m-' . $day_padded . ' ' . $hour_padded . ':' . $minute_padded . ':00', $current_time));

                if ($scheduled_time < $current_time) {

                    // 31st of feb, should = 28/29
                    $future_time = strtotime('+28 days', $current_time); // 28 days is the shortest month, adding + 1 month can skip feb
                    if (date('t', $future_time) < $day) {
                        $day_padded = str_pad(date('t', $future_time), 2, 0, STR_PAD_LEFT);
                    }

                    $scheduled_time = $time_offset + strtotime(date('Y-m-' . $day_padded . ' ' . $hour_padded . ':' . $minute_padded . ':00', $future_time));
                }
                break;
            case 'week':
                // day 0-6 : 0 = SUNDAY
                $day_str = '';
                switch (intval($day)) {
                    case 0:
                        $day_str =  'sunday';
                        break;
                    case 1:
                        $day_str =  'monday';
                        break;
                    case 2:
                        $day_str =  'tuesday';
                        break;
                    case 3:
                        $day_str =  'wednesday';
                        break;
                    case 4:
                        $day_str =  'thursday';
                        break;
                    case 5:
                        $day_str =  'friday';
                        break;
                    case 6:
                        $day_str =  'saturday';
                        break;
                }
                $scheduled_time = $time_offset + strtotime(date('Y-m-d ' . $hour_padded . ':' . $minute_padded . ':00', strtotime('next ' . $day_str, $current_time)));
                if ($scheduled_time - WEEK_IN_SECONDS > $current_time) {
                    $scheduled_time -= WEEK_IN_SECONDS;
                }
                break;
            case 'day':
                $scheduled_time = $time_offset + strtotime(date('Y-m-d ' . $hour_padded . ':' . $minute_padded . ':00', $current_time));
                if ($scheduled_time <= $current_time) {
                    $scheduled_time += DAY_IN_SECONDS;
                }
                break;
            case 'hour':
                $scheduled_time = strtotime(date('Y-m-d H:' . $minute_padded . ':00', $current_time));
                if ($scheduled_time <= $current_time) {
                    $scheduled_time += HOUR_IN_SECONDS;
                }
                break;
        }

        return $scheduled_time;
    }

    public function calculate_time_offset()
    {
        return time() - current_time('timestamp');
    }

    public function cleanup($id)
    {
        delete_post_meta($id, $this->_scheduled_key);
        $this->clear_attached_cron_runners($id);
    }

    /**
     * Check to see if all schedules are disabled
     *
     * @param mixed $model
     * @return boolean
     */
    public function is_cron_disabled($model)
    {
        $settings = $this->get_cron_setting($model);
        if (!empty($settings)) {
            foreach ($settings as $setting) {
                if ($setting['setting_cron_disabled'] === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Generate list of scheduled items with dates.
     * 
     * @return array List of scheduled items
     */
    public function get_scheduled_items()
    {
        $query = new \WP_Query([
            'post_type' => $this->_post_type,
            'posts_per_page' => -1,
        ]);

        if (!$query->have_posts()) {
            return false;
        }

        $scheduled_items = [];

        /**
         * @var mixed[] $items
         */
        $items = [];

        foreach ($query->posts as $post) {
            $model = $this->get_scheduled_item($post);
            if ($model) {
                $items[] = $model;
            }
        }

        if (empty($items)) {

            $enabled_background_processing = version_compare(IWP_VERSION, '2.10.0', '<');
            if (apply_filters('iwp/cron_runner/enable_background_processing', $enabled_background_processing)) {
                $this->no_scheduled_items();
            }
            return $scheduled_items;
        }

        foreach ($items as $model) {

            $id = $model->getId();

            $next_schedule = get_post_meta($id, $this->_scheduled_key, true);
            if (!$next_schedule) {

                $next_schedule = $this->spawn_importer($model);
                if (!$next_schedule) {
                    continue;
                }

                update_post_meta($id, $this->_scheduled_key, $next_schedule);
                Logger::write('spawner -wp_schedule_event=' . date('Y-m-d H:i:s', $next_schedule), $id);
            }

            if (!$next_schedule) {
                continue;
            }

            if (time() >= $next_schedule) {

                $scheduled_items[] = [
                    'time' => $next_schedule,
                    'item' => $model,
                    'last_ran' => (int)get_post_meta($id, $this->_last_ran_key, true)
                ];
            }
        }

        if (empty($scheduled_items)) {
            $this->no_scheduled_items();
        }

        return $scheduled_items;
    }

    public function order_by_schedule_time($scheduled_items)
    {
        // Order by scheduled time, prioritising where last_run is greater than time
        // check to see if there are any running imports?
        usort($scheduled_items, function ($item1, $item2) {

            if ($item1['time'] <= $item1['last_ran'] && $item2['time'] > $item2['last_ran']) {
                return -1;
            } elseif ($item2['time'] <= $item2['last_ran'] && $item1['time'] > $item1['last_ran']) {
                return 1;
            }

            if ($item1['time'] == $item2['time']) return 0;
            return $item1['time'] < $item2['time'] ? -1 : 1;
        });

        return $scheduled_items;
    }

    /**
     * Register custom 60 second cron schedule.
     *
     * @param array $schedules
     * @return void
     */
    public function register_cron_interval($schedules)
    {
        $schedules['iwp_spawner'] = [
            'interval' => MINUTE_IN_SECONDS,
            'display' => __('Every minutes.', 'importwp')
        ];
        return $schedules;
    }

    /**
     * Schedule WordPress action hook to use custom 60 second schedule.
     *
     * @return void
     */
    public function register_cron_runner()
    {
        if (!empty(IWP_CRON_TOKEN)) {

            // if cron runner is scheduled, unschedule it
            if (wp_next_scheduled($this->_cron_main_handle)) {
                wp_unschedule_hook($this->_cron_main_handle);
            }

            if (isset($_GET['iwp_cron_token']) && $_GET['iwp_cron_token'] === IWP_CRON_TOKEN) {

                ignore_user_abort(true);

                if (!headers_sent()) {
                    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
                    header('Cache-Control: no-cache, must-revalidate, max-age=0');
                }

                // Don't run cron until the request finishes, if possible.
                if (PHP_VERSION_ID >= 70016 && function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } elseif (function_exists('litespeed_finish_request')) {
                    litespeed_finish_request();
                }

                wp_raise_memory_limit('cron');

                $this->run();
            }
        } else {

            // if cron runner is not scheduled, schedule it
            if (!wp_next_scheduled($this->_cron_main_handle)) {
                wp_schedule_event(time(), 'iwp_spawner', $this->_cron_main_handle);
            }
        }

        // Remove deprecated cron actions
        if (wp_next_scheduled('iwp_scheduler')) {
            wp_unschedule_hook('iwp_scheduler');
        }
        if (wp_next_scheduled('iwp_schedule_runner')) {
            wp_unschedule_hook('iwp_schedule_runner');
        }
    }

    /**
     * @param ImporterModel|ExporterModel $model
     */
    public function spawn_importer($model)
    {
        $schedule_settings = $this->get_cron_setting($model);
        if (empty($schedule_settings)) {
            return false;
        }

        $schedule_next = -1;

        foreach ($schedule_settings as $schedule_setting) {

            if (!isset($schedule_setting['setting_cron_schedule'], $schedule_setting['setting_cron_day'], $schedule_setting['setting_cron_minute'], $schedule_setting['setting_cron_schedule'], $schedule_setting['setting_cron_disabled'])) {
                continue;
            }

            $schedule = $schedule_setting['setting_cron_schedule'];
            $day = $schedule_setting['setting_cron_day'];
            $hour = $schedule_setting['setting_cron_hour'];
            $minute = $schedule_setting['setting_cron_minute'];
            $disabled = $schedule_setting['setting_cron_disabled'];

            if ($disabled === true) {
                continue;
            }

            $scheduled_time = $this->calculate_scheduled_time($schedule, $day, $hour, $minute);
            if (false === $scheduled_time) {
                continue;
            }

            if ($schedule_next === -1 || $schedule_next > $scheduled_time) {
                $schedule_next = $scheduled_time;
            }
        }

        if ($schedule_next === -1) {
            return false;
        }

        return $schedule_next;
    }

    private $_cron_runner_key = '_iwp_cron_runner_%s';

    public function attach_to_item($id, $user)
    {
        update_post_meta($id, sprintf($this->_cron_runner_key, $user), time());
        update_post_meta($id, $this->_last_ran_key, time());
    }

    public function detach_to_item($id, $user)
    {
        delete_post_meta($id, sprintf($this->_cron_runner_key, $user));
        update_post_meta($id, $this->_last_ran_key, time());
    }

    public function clear_attached_cron_runners($id)
    {
        /**
         * @var \WPDB $wpdb
         */
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id={$id} AND meta_key LIKE '\_iwp\_cron\_runner\_%'");
    }

    public function generate_cron_message($id, $status)
    {
        /**
         * @var \WPDB $wpdb
         */
        global $wpdb;

        $last_ran = intval(get_post_meta($id, $this->_last_ran_key, true));
        $active_crons = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE post_id={$id} AND meta_key LIKE '\_iwp\_cron\_runner\_%'");
        if ($last_ran > 0) {
            if (empty($active_crons)) {
                $delta = time() - $last_ran;
                return '(Waiting on WP Cron: ' . $delta . 's) ';
            }
        }

        return '';
    }

    /**
     * @param int $id 
     * @return string
     */
    public function get_cron_status($id)
    {
        // start | stopped | running | error
        $last_ran = (int)get_post_meta($id, $this->_last_ran_key, true);
        $scheduled_time = (int)get_post_meta($id, $this->_scheduled_key, true);

        if (empty($scheduled_time)) {
            // spawner
            $spawner_time = wp_next_scheduled($this->_cron_main_handle);
            return [
                'status' => 'cron_spawner',
                'time' => $spawner_time,
                'delta' => $spawner_time - time()
            ];
        }

        if ($scheduled_time > $last_ran) {

            // scheduled but not started
            return [
                'status' => 'cron_start',
                'time' => $scheduled_time,
                'delta' => $scheduled_time - time()
            ];
        }

        return $this->get_item_status($id, $scheduled_time, $last_ran);
    }

    /**
     * Modify the status message
     *
     * @param array $output
     * @param ImporterModel|ExporterModel $model
     * @return array
     */
    public function update_status_message($output, $model)
    {

        $id = $model->getId();
        $status = $this->get_cron_status($id);
        if (!$status) {
            return $output;
        }

        if ($this->get_scheduled_item($model)) {
            $output['cron'] = $status;
        }

        // Statuses prefixed with cron_ are specific to schedule events
        // Statuses without prefix are displayed on both scheduled and worker events

        switch ($status['status']) {
            case 'cron_start':
                $offset = $this->calculate_time_offset();
                $output['message'] = '(Scheduled at ' . date(get_site_option('date_format') . ' ' . get_site_option('time_format'), $status['time'] - $offset) . ') ' . $output['message'];
                break;
            case 'cron_spawner':
                if (isset($output['cron'])) {
                    if (IWP_CRON_TOKEN) {

                        // custom cron cant get time till next check.
                        $output['message'] = '(Scheduling due) ' . $output['message'];
                    } else {
                        $output['message'] = '(Scheduling due ' . $status['delta'] . 's) ' . $output['message'];
                    }
                }
                break;
            case 'running':

                /**
                 * @var \WPDB $wpdb
                 */
                global $wpdb;

                $last_ran = intval(get_post_meta($id, $this->_last_ran_key, true));
                $active_crons = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE post_id={$id} AND meta_key LIKE '\_iwp\_cron\_runner\_%'");
                if ($last_ran > 0) {
                    if (empty($active_crons)) {
                        $delta = time() - $last_ran;
                        $output['message'] =  '(Waiting on WP Cron: ' . $delta . 's) '  . $output['message'];
                    }
                }
                break;
        }


        return $output;
    }
}
