<?php

namespace ImportWP\Pro\Cron;

use ImportWP\Common\Importer\State\ImporterState;
use ImportWP\Common\Model\ImporterModel;
use ImportWP\Common\Util\Logger;
use ImportWP\Common\Util\Util;
use ImportWP\Container;
use ImportWP\Pro\Importer\ImporterManager;

class ImportCronRunner extends CronRunner
{
    /**
     * Scheduled event action hook name.
     *
     * @var string
     */
    protected $_cron_main_handle = 'iwp_cron_runner';

    /**
     * @var ImporterManager
     */
    private $importer_manager;

    protected $_post_type = IWP_POST_TYPE;

    /**
     * @param EventHandler $event_handler
     * @param ImporterManager $importer_manager
     */
    public function __construct($event_handler, ImporterManager $importer_manager)
    {
        parent::__construct($event_handler);

        $this->importer_manager = $importer_manager;

        $this->event_handler->listen('iwp/importer/status/output', [$this, 'update_status_message']);
    }

    /**
     * @param ImporterModel $item 
     */
    public function get_cron_setting($item)
    {
        return $item->getSetting('cron');
    }

    /**
     * Load Scheduled Model
     * 
     * Return Model with an enabled schedule.
     *
     * @param ImporterModel|\WP_Post $post
     * @return false|ImporterModel
     */
    public function get_scheduled_item($post)
    {
        $importer_model = $this->importer_manager->get_importer($post);
        if ('schedule' === $importer_model->getSetting('import_method') && false === $this->is_cron_disabled($importer_model)) {
            return $importer_model;
        }

        return false;
    }

    public function get_item_status($id, $scheduled_time, $last_ran)
    {
        $status = ImporterState::get_state($id);

        if ($status && empty($scheduled_time)) {
            $scheduled_time = $status['updated'];
        }

        return [
            'status' => $status && isset($status['status']) ? $status['status'] : '',
            'time' => $scheduled_time,
            'delta' => $scheduled_time - time()
        ];
    }

    /**
     * No scheduled items, attach to running items
     * 
     * @return void 
     */
    public function no_scheduled_items()
    {
        // TODO: What order should these be ran?
        $query = new \WP_Query([
            'post_type' => $this->_post_type,
            'posts_per_page' => -1,
        ]);

        if (!$query->have_posts()) {
            return;
        }

        $importer = false;

        foreach ($query->posts as $post) {
            $importer_model = $this->importer_manager->get_importer($post);
            if ($importer_model->getSetting('import_method') == 'run') {
                $state = ImporterState::get_state($importer_model->getId());
                if ($state && $state['status'] === 'running') {
                    $importer = $importer_model;
                    break;
                }
            }
        }

        if (!$importer) {
            return;
        }

        $config = get_site_option('iwp_importer_config_' . $importer->getId());
        $session = $config['id'];

        $user = uniqid('iwp', true);
        $this->attach_to_item($importer->getId(), $user);

        $update_timestamp = function ($importer_status) use ($importer, $user) {
            $this->attach_to_item($importer->getId(), $user);
        };

        add_action('iwp/importer/status/save', $update_timestamp);
        $state = $this->importer_manager->import($importer->getId(), $user, $session);
        remove_action('iwp/importer/status/save', $update_timestamp);

        $this->detach_to_item($importer->getId(), $user);
    }

    public function run()
    {
        Logger::setRequestType('cron');

        $scheduled_importers = $this->get_scheduled_items();
        if (empty($scheduled_importers)) {
            return;
        }

        $scheduled_importers = $this->order_by_schedule_time($scheduled_importers);

        $user = uniqid('iwp', true);

        foreach ($scheduled_importers as $importer) {

            $importer_model = $importer['item'];
            $importer_id = $importer_model->getId();
            $this->importer_manager->set_current_user($importer_model);
            Logger::setId($importer_id);

            $init_importer = $importer['time'] > (int)get_post_meta($importer_id, $this->_last_ran_key, true);

            if ($init_importer) {

                update_post_meta($importer_id, $this->_last_ran_key, time());

                Logger::info('cron -start');

                // Clear existing import
                ImporterState::clear_options($importer_model->getId());

                $download_file = version_compare(IWP_VERSION, '2.7.14', '<=');
                if (!$download_file) {
                    /**
                     * Fetching of new file is included in ImportManager::import
                     * @since 2.7.15
                     */
                    add_filter('iwp/importer/run_fetch_file',  '__return_true');
                } else {

                    /**
                     * @deprecated deprecated since ImportWP 2.7.15
                     */
                    $datasource = $importer_model->getDatasource();
                    switch ($datasource) {
                        case 'remote':
                            $raw_source = $importer_model->getDatasourceSetting('remote_url');
                            $source = apply_filters('iwp/importer/datasource', $raw_source, $raw_source, $importer_model);
                            $source = apply_filters('iwp/importer/datasource/remote', $source, $raw_source, $importer_model);
                            $attachment_id = $this->importer_manager->remote_file($importer_model, $source, $importer_model->getParser());
                            break;
                        case 'local':
                            $raw_source = $importer_model->getDatasourceSetting('local_url');
                            $source = apply_filters('iwp/importer/datasource', $raw_source, $raw_source, $importer_model);
                            $source = apply_filters('iwp/importer/datasource/local', $source, $raw_source, $importer_model);
                            $attachment_id = $this->importer_manager->local_file($importer_model, $source);
                            break;
                        default:
                            // TODO: record error 
                            $attachment_id = new \WP_Error('IWP_CRON_1', 'Unable to get new file using datasource: ' . $datasource);
                            break;
                    }

                    $importer_model = $this->importer_manager->get_importer($importer_id);
                }

                // This is used for storing version on imported records
                $session = md5($importer_model->getId() . time());
                update_post_meta($importer_model->getId(), '_iwp_session', $session);

                /**
                 * @deprecated deprecated since ImportWP 2.7.15
                 */
                if ($download_file && is_wp_error($attachment_id)) {

                    $state = ImporterState::wait_for_lock($importer_model->getId(), $user, function () use ($importer_model, $attachment_id, $session) {
                        $state = ImporterState::get_state($importer_model->getId());
                        $state['status'] = 'error';
                        $state['id'] = $session;
                        $state['message'] = $attachment_id->get_error_message();
                        return $state;
                    });

                    $tmp = new ImporterState($importer_model->getId(), $user);
                    $tmp->populate($state);

                    Util::write_status_session_to_file($importer_model->getId(), $tmp);
                    $this->cleanup($importer_model->getId());
                    return;
                }
            } else {

                // Importer status must be running
                $state = ImporterState::get_state($importer_id);
                if (!$state) {
                    $this->cleanup($importer_id);
                    return;
                }

                // if cancelled or error, cleanup and exit.
                switch ($state['status']) {
                    case 'error':
                    case 'cancelled':
                        $this->cleanup($importer_id);
                        return;
                }

                // resume stalled imports
                if ($state['status'] == 'processing') {

                    /**
                     * @var Properties $properties
                     */
                    $properties = Container::getInstance()->get('properties');
                    $time_limit = intval($properties->get_setting('timeout'));
                    if ($time_limit <= 0) {
                        $time_limit = 300;
                    } else {
                        $time_limit *= 2;
                    }

                    $time_delta = time() - $state['updated'];
                    if ($time_delta > $time_limit) {
                        Logger::write('cron stalled -delta=' . $time_delta . 's');
                        $state['status'] = 'timeout';
                    }
                }

                if (!in_array($state['status'], ['running', 'timeout'])) {
                    return;
                }

                update_post_meta($importer_id, $this->_last_ran_key, time());
                Logger::info('cron -resume');

                $config = get_site_option('iwp_importer_config_' . $importer_id);
                $session = $config['id'];
            }

            $this->attach_to_item($importer_id, $user);

            $update_timestamp = function ($importer_status) use ($importer_id, $user) {
                $this->attach_to_item($importer_id, $user);
            };

            add_action('iwp/importer/status/save', $update_timestamp);
            $state = $this->importer_manager->import($importer_id, $user, $session);
            remove_action('iwp/importer/status/save', $update_timestamp);

            $this->detach_to_item($importer_id, $user);

            Logger::info('cron -end');

            if (isset($state['status']) && in_array($state['status'], ['init', 'running', 'timeout'])) {
                break;
            } else {
                $this->cleanup($importer_id);
            }
        }
    }

    public function unschedule($exporter_id)
    {
        $importer_model = $this->importer_manager->get_importer($exporter_id);

        $user = uniqid('iwp', true);

        ImporterState::wait_for_lock($exporter_id, $user, function () use ($exporter_id) {
            $state = ImporterState::get_state($exporter_id);
            if (!$state) {
                return $state;
            }

            if (isset($state['status']) && $state['status'] === 'running') {
                $state['status'] = 'cancelled';
            }

            ImporterState::set_state($exporter_id, $state);
            do_action('iwp/importer/status/save', $state);

            return $state;
        });

        $this->cleanup($importer_model->getId());

        return true;
    }
}
