<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\InputHelper;
use ExternalImporter\application\components\TaskProcessor;
use ExternalImporter\application\components\ParserTask;

/**
 * ExtractorApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ExtractorApi {

    public function __construct()
    {
        \add_action('wp_ajax_ei-extractor-api', array($this, 'addApiEntry'));
    }

    public function addApiEntry()
    {
        if (!\current_user_can('publish_posts'))
            \wp_die("Access denied.");

        \check_ajax_referer('ei-import', '_ei_nonce');

        $params = stripslashes(InputHelper::post('params', ''));
        $params = json_decode($params, true);
        if (!$params)
            self::jsonError("Params variable cannot be empty.");

        try
        {
            if (!empty($params['listingProcessor']))
                $init_data = $params['listingProcessor'];
            elseif (!empty($params['productProcessor']))
                $init_data = $params['productProcessor'];
            else
                self::jsonError("Unknown source");

            $processor = new TaskProcessor($init_data);
            $processor->run();
        } catch (\Exception $e)
        {
            self::jsonError($e->getMessage());
        }

        self::jsonError("Processor did not return the result.");
    }

    public static function jsonError($messages, $cmd = 'stop')
    {
        if (!is_array($messages))
            $messages = array($messages);
        $data = array();
        $data['log'] = array();
        foreach ($messages as $message)
        {
            $log = array(
                'message' => $message,
                'type' => 'error'
            );
            $data['log'][] = $log;
        }
        $data['cmd'] = $cmd;
        self::formatJsonDataError($data);
    }

    public static function formatJsonDataError(array $data)
    {
        header('HTTP/1.0' . ' ' . 500 . ' ' . 'Internal Server Error');
        self::formatJsonData($data);
    }

    public static function formatJsonData(array $data, ParserTask $parserTask = null)
    {
        if (isset($data['message']))
        {
            $messages = $data['message'];
            unset($data['message']);
            if (!is_array($messages))
                $messages = array($messages);
            $data['log'] = array();
            foreach ($messages as $message)
            {
                $log = array(
                    'message' => $message,
                );
                $data['log'][] = $log;
            }
        }

        if (isset($data['log']) && isset($data['log']['message']))
            $data['log'] = array($data['log']);

        if ($parserTask)
        {
            $data['stat'] = array();
            list($data['stat']['new'], $data['stat']['success'], $data['stat']['errors']) = $parserTask->getStat();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        \wp_die();
    }

}
