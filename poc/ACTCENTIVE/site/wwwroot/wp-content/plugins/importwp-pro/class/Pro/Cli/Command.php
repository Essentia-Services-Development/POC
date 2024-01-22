<?php

namespace ImportWP\Pro\Cli;

use ImportWP\Common\Exporter\ExporterManager;
use ImportWP\Common\Exporter\State\ExporterState;
use ImportWP\Common\Importer\ImporterManager;
use ImportWP\Common\Importer\State\ImporterState;
use ImportWP\Common\Util\Logger;
use ImportWP\Container;

class Command
{
    public static function register()
    {
        \WP_CLI::add_command('importwp', 'ImportWP\Pro\Cli\Command');
    }

    public function import($args, $assoc_args)
    {
        Logger::setRequestType('cli');
        $assoc_args['action']      = 'importwp';
        $importer_id = intval(trim($args[0]));

        if ($importer_id <= 0) {
            \WP_CLI::error("You must provide an importer id.");
        }

        $user = uniqid('iwp', true);

        /**
         * @var ImporterManager $importer_manager
         */
        $importer_manager = Container::getInstance()->get('importer_manager');

        $importer_model = $importer_manager->get_importer($importer_id);

        $session = null;

        if (isset($assoc_args['start'])) {

            // Clear existing import
            ImporterState::clear_options($importer_model->getId());

            \WP_CLI::log("Starting Import");

            $download_file = version_compare(IWP_VERSION, '2.7.14', '<=');
            if (!$download_file) {
                /**
                 * Fetching of new file is included in ImportManager::import
                 * @since 2.7.15
                 */
                add_filter('iwp/importer/run_fetch_file',  '__return_true');
            } else {

                $datasource = $importer_model->getDatasource();
                $attachment_id = false;

                switch ($datasource) {
                    case 'remote':
                        $raw_source = $importer_model->getDatasourceSetting('remote_url');
                        $source = apply_filters('iwp/importer/datasource', $raw_source, $raw_source, $importer_model);
                        $source = apply_filters('iwp/importer/datasource/remote', $source, $raw_source, $importer_model);
                        $attachment_id =  $importer_manager->remote_file($importer_model, $source, $importer_model->getParser());
                        break;
                    case 'local':
                        $raw_source = $importer_model->getDatasourceSetting('local_url');
                        $source = apply_filters('iwp/importer/datasource', $raw_source, $raw_source, $importer_model);
                        $source = apply_filters('iwp/importer/datasource/local', $source, $raw_source, $importer_model);
                        $attachment_id =  $importer_manager->local_file($importer_model, $source);
                        break;
                }

                $importer_model =  $importer_manager->get_importer($importer_id);

                if (is_wp_error($attachment_id)) {

                    \WP_CLI::error($attachment_id->get_error_message());
                    return;
                }
            }

            $session = md5($importer_model->getId() . time());
        } else {
            \WP_CLI::log("Resume Import");

            // Get latest session and resume it.
            $config = get_site_option('iwp_importer_config_' . $importer_id);
            $session = $config['id'];
        }

        $progress_bar = null; //\WP_CLI\Utils\make_progress_bar( 'Generating users', $count );
        $current_section = null;
        $progress_counter = 0;

        $update_timestamp = function ($status) use (&$progress_bar, &$current_section, &$progress_counter) {

            $section = $status['section'];

            if (isset($status['progress'][$section]) && $status['progress'][$section]['end'] > 0) {

                $progress = $status['progress'][$section];

                if ($section !== $current_section) {

                    if ($progress_bar) {
                        $progress_bar->finish();
                    }

                    $progress_counter = 0;
                    $progress_bar = \WP_CLI\Utils\make_progress_bar(ucfirst($section) . ' Records', $progress['end'] - $progress['start']);
                    $current_section = $section;
                }

                for ($progress_counter; $progress_counter < $progress['current_row']; $progress_counter++) {
                    $progress_bar->tick();
                }
            }
        };

        // display progress bar for file processing
        $process_bar = null; //
        $process_counter = 0;
        $update_process = function ($current) use (&$process_bar, &$process_counter) {

            if (is_null($process_bar)) {
                $process_bar = \WP_CLI\Utils\make_progress_bar('Processing File', 100);
            }

            for ($process_counter; $process_counter < $current; $process_counter++) {
                $process_bar->tick();
            }
        };

        do {
            add_action('iwp/importer/process', $update_process, 10);
            add_action('iwp/importer/status/save', $update_timestamp);
            $state = $importer_manager->import($importer_model, $user, $session);
            remove_action('iwp/importer/status/save', $update_timestamp);
            remove_action('iwp/importer/process', $update_process, 10);

            // On first run the session is null, if we run it again it will invalidate the session
            if (is_null($session)) {
                $config = get_site_option('iwp_importer_config_' . $importer_id);
                $session = $config['id'];
            }
        } while (in_array($state['status'], ['running', 'timeout']));

        if ($state['status'] === 'error') {
            \WP_CLI::error('Import error: ' . $state['message']);
        }

        Logger::clearRequestType();
        \WP_CLI::success(sprintf("Import Complete - Inserts: %d, Updates: %d, Deletes: %d, Skipped: %d, Errors: %d", $state['stats']['inserts'], $state['stats']['updates'], $state['stats']['deletes'], $state['stats']['skips'], $state['stats']['errors']));
    }

    public function export($args, $assoc_args)
    {
        Logger::setRequestType('cli');
        $assoc_args['action']      = 'importwp';
        $exporter_id = intval(trim($args[0]));

        if ($exporter_id <= 0) {
            \WP_CLI::error("You must provide an exporter id.");
        }

        $user = uniqid('iwp', true);

        /**
         * @var ExporterManager $exporter_manager
         */
        $exporter_manager = Container::getInstance()->get('exporter_manager');

        $exporter_model = $exporter_manager->get_exporter($exporter_id);

        $session = null;

        if (isset($assoc_args['start'])) {

            // Clear existing import
            ExporterState::clear_options($exporter_model->getId());

            \WP_CLI::log("Starting Export");

            $session = md5($exporter_model->getId() . time());
        } else {
            \WP_CLI::log("Resume Export");

            // Get latest session and resume it.
            $config = get_site_option('iwp_exporter_config_' . $exporter_id);
            $session = $config['id'];
        }

        $progress_bar = null; //\WP_CLI\Utils\make_progress_bar( 'Generating users', $count );
        $current_section = null;
        $progress_counter = 0;

        $update_timestamp = function ($status) use (&$progress_bar, &$current_section, &$progress_counter) {

            $section = $status['section'];

            if (isset($status['progress'][$section]) && $status['progress'][$section]['end'] > 0) {

                $progress = $status['progress'][$section];

                if ($section !== $current_section) {

                    if ($progress_bar) {
                        $progress_bar->finish();
                    }

                    $progress_counter = 0;
                    $progress_bar = \WP_CLI\Utils\make_progress_bar(ucfirst($section) . ' Records', $progress['end'] - $progress['start']);
                    $current_section = $section;
                }

                for ($progress_counter; $progress_counter < $progress['current_row']; $progress_counter++) {
                    $progress_bar->tick();
                }
            }
        };

        do {
            add_action('iwp/exporter/status/save', $update_timestamp);
            $state = $exporter_manager->export($exporter_model->getId(), $user, $session);
            remove_action('iwp/exporter/status/save', $update_timestamp);

            // On first run the session is null, if we run it again it will invalidate the session
            if (is_null($session)) {
                $config = get_site_option('iwp_exporter_config_' . $exporter_id);
                $session = $config['id'];
            }
        } while (in_array($state['status'], ['running', 'timeout', 'end']));

        if ($state['status'] === 'error') {
            \WP_CLI::error('Export error: ' . $state['message']);
        }

        Logger::clearRequestType();
        \WP_CLI::success(sprintf("Export Complete - Records: %d, Skipped: %d, Errors: %d", $state['stats']['rows'], $state['stats']['skips'], $state['stats']['errors']));
    }
}
