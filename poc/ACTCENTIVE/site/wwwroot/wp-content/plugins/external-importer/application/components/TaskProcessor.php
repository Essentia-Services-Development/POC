<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\admin\ExtractorApi;
use ExternalImporter\application\components\ParserTask;
use ExternalImporter\application\models\TaskModel;
use ExternalImporter\application\helpers\ParserHelper;
use ExternalImporter\application\Plugin;

/**
 * CatalogProcessor class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class TaskProcessor {

    const STOP_TASK_ERROR_COUNT = 5;

    protected $init_data;

    public function __construct(array $init_data)
    {
        if (empty($init_data['url']) && empty($init_data['urls']))
            throw new \Exception("Listing URL or Product URLs must be specified.");

        if (isset($init_data['url']) && !TextHelper::isValidUrl($init_data['url']))
            throw new \Exception(__('Incorrect listing URL. Please verify the URL and try again.', 'external-importer'));

        if (isset($init_data['urls']))
        {
            if (!$init_data['urls'] = self::prepareProductUrls($init_data['urls']))
                throw new \Exception(__('Incorrect product URLs. Please verify the URLs and try again.', 'external-importer'));
        }

        $this->init_data = $init_data;
    }

    public function run()
    {
        if (!$task = TaskModel::model()->getTask($this->init_data))
            $task = $this->createTask();

        $parserTask = $task['data'];

        if ($parserTask->setNextStep() === false)
            ExtractorApi::jsonError(__('All products were parsed for this task.', 'external-importer'));

        if ($parserTask->isLimitProductReached())
            ExtractorApi::jsonError(__('Required product limit reached.', 'external-importer'));

        if ($parserTask->getStep() == ParserTask::STEP_PARSE_LISING)
            $this->parseListing($task);
        elseif ($parserTask->getStep() == ParserTask::STEP_PARSE_PRODUCTS)
            $this->parseProducts($task);
        else
            throw new \Exception("Unknown task step.");
    }

    private function parseListing(array $task)
    {
        $parserTask = $task['data'];
        if (!$url = $parserTask->getNextPaginationUri())
            throw new \Exception("No pagination URL found.");

        $pagination_count = $parserTask->getPaginationCount();

        $error = '';
        try
        {
            $listing = ParserHelper::parseListing($url);
        } catch (\Exception $e)
        {
            $error = $e->getMessage();
        }

        if ($parsed = $parserTask->getPaginationParsedCount())
            $message = sprintf(__('Page #%d:', 'external-importer'), $parsed + 1) . ' ';
        else
            $message = '';

        if ($error)
        {
            // update task
            $parserTask->setPaginationStatusError();
            TaskModel::model()->save($task);

            $message .= sprintf(__('The <a target="_blank" href="%s">listing URL</a> can not be parsed.', 'external-importer'), \esc_url($url));
            $message .= ' ' . sprintf(__('Error: %s.', 'external-importer'), $error);

            $this->maybeStopTask($parserTask, $message);
        }

        // success
        $parserTask->setPaginationStatusSuccess();
        $parserTask->addLinks($listing->links);

        if (isset($task['init_data']['automatic_pagination']) && $task['init_data']['automatic_pagination'])
            $parserTask->addPagination($listing->pagination);

        TaskModel::model()->save($task);
        $message .= sprintf(__('The <a target="_blank" href="%s">listing URL</a> was parsed successfully.', 'external-importer'), \esc_url($url));
        $message .= ' ' . sprintf(__('Product URLs found: %d.', 'external-importer'), count($listing->links));

        if (isset($task['init_data']['automatic_pagination']) && $task['init_data']['automatic_pagination'])
            $message .= ' ' . sprintf(__('Listing URLs found: %d.', 'external-importer'), $parserTask->getPaginationCount() - $pagination_count);

        $return['products'] = array();
        $return['cmd'] = 'next';
        $return['log'] = array(
            'message' => $message,
            'type' => 'info',
        );

        if (Plugin::isDevEnvironment())
        {
            $return['debug'] = 'Used parsers for listing: ' . join(', ', ParserHelper::getLastExtractor()->getLastUsedParsers());
            $return['debug'] .= "\r\n";
            $return['debug'] .= "Product URLs:\r\n";
            $return['debug'] .= join("\r\n", $listing->links);
            $return['debug'] .= "\r\n";
            $return['debug'] .= "Listing URLs:\r\n";
            $return['debug'] .= join("\r\n", $listing->pagination);
        }

        ExtractorApi::formatJsonData($return, $parserTask);
    }

    private function parseProducts(array $task)
    {
        $parserTask = $task['data'];
        if (!$url = $parserTask->getNextLinkUri())
            throw new \Exception("No product URL found.");

        $error = '';
        try
        {
            $product = ParserHelper::parseProduct($url);
        } catch (\Exception $e)
        {
            $error = $e->getMessage();
            $error_code = $e->getCode();
        }

        if ($error)
        {
            // update task
            $parserTask->setLinkStatusError();
            TaskModel::model()->save($task);

            $message = sprintf(__('The <a target="_blank" href="%s">product URL</a> can not be parsed.', 'external-importer'), \esc_url($url));
            $message .= ' ' . sprintf(__('Error: %s (%d).', 'external-importer'), $error, $error_code);

            $this->maybeStopTask($parserTask, $message);
        }

        // success
        $parserTask->setLinkStatusSuccess();
        TaskModel::model()->save($task);

        $return['products'] = array($product);
        $log = array(
            'message' => sprintf(__('The <a target="_blank" href="%s">product URL</a> was parsed successfully: <b>%s</b>.', 'external-importer'), \esc_url($url), TextHelper::truncate($product->title, 40)),
            'type' => 'success',
        );
        $return['log'] = array($log);

        if ($parserTask->setNextStep() === false)
        {
            $return['log'][] = array(
                'message' => __('Done.', 'external-importer') . ' ' . __('All products were parsed.', 'external-importer'),
                'type' => 'info'
            );
            $return['cmd'] = 'stop';
        } elseif ($parserTask->isLimitProductReached())
        {

            $return['log'][] = array(
                'message' => __('Done.', 'external-importer') . ' ' . __('Required product limit reached.', 'external-importer'),
                'type' => 'info'
            );
            $return['cmd'] = 'stop';
        } else
            $return['cmd'] = 'next';

        if (Plugin::isDevEnvironment())
            $return['debug'] = 'Used parsers for product: ' . join(', ', ParserHelper::getLastExtractor()->getLastUsedParsers());

        ExtractorApi::formatJsonData($return, $parserTask);
    }

    private function createTask()
    {
        if (!empty($this->init_data['url']))
            $parserTask = $this->createListingParserTask();
        elseif (!empty($this->init_data['urls']))
            $parserTask = $this->createProductsParserTask();
        else
            throw new \Exception("Unknown task type.");

        if (!$id = TaskModel::model()->createOrUpdate($this->init_data, $parserTask))
            throw new \Exception("Task cannot be created. Unknown error.");

        return TaskModel::model()->getTask($this->init_data);
    }

    private function createListingParserTask()
    {
        $parserTask = new ParserTask($this->init_data);
        // init pagination with listing URL
        $parserTask->addPagination($this->init_data['url']);
        return $parserTask;
    }

    private function createProductsParserTask()
    {
        $parserTask = new ParserTask($this->init_data);
        $parserTask->addLinks($this->init_data['urls']);
        //$parserTask->setStepParseProducts();
        return $parserTask;
    }

    private function maybeStopTask(ParserTask $parserTask, $message)
    {
        if ($parserTask->setNextStep() === false)
        {
            $cmd = 'stop';
            $message .= ' ' . __('The task is stopped:', 'external-importer') . ' ' . __('No parsing products found.', 'external-importer');
        } elseif ($parserTask->getConsecutiveErrors() == self::STOP_TASK_ERROR_COUNT)
        {
            $cmd = 'stop';
            $message .= ' ' . __('The task is stopped:', 'external-importer') . ' ' . __('Too many consecutive errors.', 'external-importer');
        } else
            $cmd = 'next';

        ExtractorApi::jsonError($message, $cmd);
    }

    public static function prepareProductUrls($urls)
    {
        if (!is_array($urls))
            $urls = explode("\n", $urls);

        $results = array();
        foreach ($urls as $url)
        {
            $url = trim($url);

            if (!$url || !TextHelper::isValidUrl($url))
                continue;

            if (in_array($url, $results))
                continue;

            $results[] = $url;
        }

        return $results;
    }

}
