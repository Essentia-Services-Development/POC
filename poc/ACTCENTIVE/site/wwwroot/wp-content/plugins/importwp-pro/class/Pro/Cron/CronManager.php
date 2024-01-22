<?php

namespace ImportWP\Pro\Cron;

use ImportWP\Common\Model\ImporterModel;
use ImportWP\EventHandler;
use ImportWP\Pro\Importer\ImporterManager;

/**
 * CronManager hooks into the WordPress cron to run scheduled Imports.
 */
class CronManager
{
    /**
     * @var EventHandler
     */
    protected $event_handler;

    /**
     * @var ImportCronRunner
     */
    protected $import_cron_runner;

    /**
     * @var ExportCronRunner
     */
    protected $export_cron_runner;

    /**
     * @param ImporterManager $importer_manager
     * @param EventHandler $event_handler
     */
    public function __construct($event_handler, ImportCronRunner $import_cron_runner, ExportCronRunner $export_cron_runner)
    {
        $this->event_handler = $event_handler;

        $this->import_cron_runner = $import_cron_runner;
        $this->export_cron_runner = $export_cron_runner;

        add_action('iwp/importer/init', [$this, 'on_importer_init']);

        // define 'IWP_CRON_TOKEN'
        if (!defined('IWP_CRON_TOKEN')) {
            define('IWP_CRON_TOKEN', false);
        }
    }

    public function unschedule($id)
    {

        switch (get_post_type($id)) {
            case EWP_POST_TYPE:
                $this->export_cron_runner->unschedule($id);
                break;
            case IWP_POST_TYPE:
                $this->import_cron_runner->unschedule($id);
                break;
        }
    }

    /**
     * @param ImporterModel $importer_model 
     * @return void 
     */
    public function on_importer_init($importer_model)
    {
        $id = $importer_model->getId();

        delete_post_meta($id, '_iwp_cron_last_ran');

        switch (get_post_type($id)) {
            case EWP_POST_TYPE:
                $this->export_cron_runner->clear_attached_cron_runners($id);
                break;
            case IWP_POST_TYPE:
                $this->import_cron_runner->clear_attached_cron_runners($id);
                break;
        }
    }
}
