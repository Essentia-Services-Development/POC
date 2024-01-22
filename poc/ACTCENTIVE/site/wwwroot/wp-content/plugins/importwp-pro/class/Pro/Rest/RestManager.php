<?php

namespace ImportWP\Pro\Rest;

use ImportWP\Common\Exporter\ExporterManager;
use ImportWP\Common\Filesystem\Filesystem;
use ImportWP\Common\Http\Http;
use ImportWP\Common\Importer\Template\TemplateManager;
use ImportWP\Common\Properties\Properties;
use ImportWP\Common\Util\Logger;
use ImportWP\Pro\Cron\CronManager;
use ImportWP\Pro\Importer\ImporterManager;

class RestManager extends \ImportWP\Common\Rest\RestManager
{
    /**
     * @var CronManager
     */
    private $cron_manager;

    public function __construct(ImporterManager $importer_manager, ExporterManager $exporter_manager, Properties $properties, Http $http, Filesystem $filesystem, TemplateManager $template_manager, CronManager $cron_manager, $event_handler)
    {
        parent::__construct($importer_manager, $exporter_manager, $properties, $http, $filesystem, $template_manager, $event_handler);
        $this->cron_manager = $cron_manager;
    }

    /**
     * Before importer is saved, find existing cron schedule and remove it
     */
    public function save_importer(\WP_REST_Request $request)
    {
        Logger::setRequestType('save_importer');

        $post_data = $request->get_body_params();
        $id = isset($post_data['id']) ? intval($post_data['id']) : null;

        if (isset($post_data['setting_import_method'])) {
            $this->cron_manager->unschedule($id);
        }

        return parent::save_importer($request);
    }

    public function save_exporter(\WP_REST_Request $request = null)
    {
        Logger::setRequestType('save_exporter');

        $post_data = $request->get_body_params();
        $id = isset($post_data['id']) ? intval($post_data['id']) : null;

        if (isset($post_data['cron'])) {
            $this->cron_manager->unschedule($id);
        }

        return parent::save_exporter($request);
    }

    public function delete_exporter(\WP_REST_Request $request = null)
    {
        Logger::setRequestType('delete_exporter');

        $id = intval($request->get_param('id'));
        $this->cron_manager->unschedule($id);

        return parent::delete_exporter($request);
    }

    public function delete_importer(\WP_REST_Request $request = null)
    {
        Logger::setRequestType('delete_importer');

        $id = intval($request->get_param('id'));
        $this->cron_manager->unschedule($id);

        return parent::delete_importer($request);
    }
}
