<?php

namespace ImportWP\Pro\Cron;

use ImportWP\Common\Exporter\ExporterManager;
use ImportWP\Common\Exporter\State\ExporterState;
use ImportWP\Common\Model\ExporterModel;
use ImportWP\Common\Util\Logger;

class ExportCronRunner extends CronRunner
{
    /**
     * Scheduled event action hook name.
     *
     * @var string
     */
    protected $_cron_main_handle = 'iwpe_cron_runner';

    /**
     * @var ExporterManager
     */
    private $exporter_manager;

    protected $_post_type = EWP_POST_TYPE;

    /**
     * @param EventHandler $event_handler
     * @param ExporterManager $exporter_manager
     */
    public function __construct($event_handler, ExporterManager $exporter_manager)
    {
        parent::__construct($event_handler);

        $this->exporter_manager = $exporter_manager;

        $this->event_handler->listen('iwp/exporter/status/output', [$this, 'update_status_message']);
    }

    public function run()
    {
        Logger::setRequestType('cron');

        $scheduled_items = $this->get_scheduled_items();
        if (empty($scheduled_items)) {
            return;
        }

        $scheduled_items = $this->order_by_schedule_time($scheduled_items);

        $user = uniqid('iwp', true);

        foreach ($scheduled_items as $scheduled_data) {

            /**
             * @var ExporterModel
             */
            $exporter_model = $scheduled_data['item'];
            $exporter_id = $exporter_model->getId();
            Logger::setId($exporter_id);

            if (class_exists('\ImportWP\Common\Exporter\State\ExporterState')) {

                $init_exporter = $scheduled_data['time'] > (int)get_post_meta($exporter_id, $this->_last_ran_key, true);
                if ($init_exporter) {

                    update_post_meta($exporter_id, $this->_last_ran_key, time());

                    ExporterState::clear_options($exporter_id);

                    // This is used for storing version on imported records
                    $session = md5($exporter_model->getId() . time());
                    update_post_meta($exporter_model->getId(), '_iwp_session', $session);
                } else {
                    $state = ExporterState::get_state($exporter_id);
                    if (!$state) {
                        $this->cleanup($exporter_id);
                        return;
                    }

                    // if cancelled or error, cleanup and exit.
                    switch ($state['status']) {
                        case 'error':
                        case 'cancelled':
                            $this->cleanup($exporter_id);
                            return;
                    }

                    if ($state['status'] !== 'running') {
                        return;
                    }

                    update_post_meta($exporter_id, $this->_last_ran_key, time());
                    Logger::info('cron -resume');

                    $config = get_site_option('iwp_exporter_config_' . $exporter_id);
                    $session = $config['id'];
                }

                $this->attach_to_item($exporter_id, $user);

                $update_timestamp = function ($exporter_status) use ($exporter_id, $user) {
                    $this->attach_to_item($exporter_id, $user);
                };

                add_action('iwp/exporter/status/save', $update_timestamp);
                $state = $this->exporter_manager->export($exporter_id, $user, $session);
                remove_action('iwp/exporter/status/save', $update_timestamp);

                $this->detach_to_item($exporter_id, $user);


                if (in_array($state['status'], ['init', 'running', 'end'])) {
                    break;
                } else {
                    $this->cleanup($exporter_id);
                }
            } else {
                $this->exporter_manager->export($exporter_id);
                $this->cleanup($exporter_id);
            }

            Logger::info('cron -end');
        }
    }

    /**
     * Load Scheduled Model
     * 
     * Return Model with an enabled schedule.
     *
     * @param ExporterModel|\WP_Post $post
     * @return false|ExporterModel
     */
    public function get_scheduled_item($post)
    {
        $exporter_model = $this->exporter_manager->get_exporter($post);

        if ('schedule' === $exporter_model->getExportMethod() && false === $this->is_cron_disabled($exporter_model)) {
            return $exporter_model;
        }

        return false;
    }

    /**
     * @param ExporterModel $item 
     */
    public function get_cron_setting($item)
    {
        return $item->getCron();
    }

    public function unschedule($exporter_id)
    {
        $exporter_model = $this->exporter_manager->get_exporter($exporter_id);

        $user = uniqid('iwp', true);

        if (class_exists('\ImportWP\Common\Exporter\State\ExporterState')) {
            ExporterState::wait_for_lock($exporter_id, $user, function () use ($exporter_id) {
                $state = ExporterState::get_state($exporter_id);
                if (!$state) {
                    return $state;
                }

                if ($state['status'] === 'running') {
                    $state['status'] = 'cancelled';
                }

                ExporterState::set_state($exporter_id, $state);
                do_action('iwp/exporter/status/save', $state);

                return $state;
            });
        }

        $this->cleanup($exporter_model->getId());
        return true;
    }

    /**
     * No scheduled items, attach to running items
     * 
     * @return void 
     */
    public function no_scheduled_items()
    {
    }

    public function get_item_status($id, $scheduled_time, $last_ran)
    {
        $status_msg = '';

        if (class_exists('\ImportWP\Common\Exporter\State\ExporterState')) {
            $status = ExporterState::get_state($id);

            if ($status && empty($scheduled_time)) {
                $scheduled_time = $status['updated'];
            }

            $status_msg = $status && isset($status['status']) ? $status['status'] : '';
        }

        return [
            'status' => $status_msg,
            'time' => $scheduled_time,
            'delta' => $scheduled_time - time()
        ];
    }
}
