<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\helpers\TextHelper;

/**
 * ModuleApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ModuleApi {

    const API_BASE = '-module-api';

    public function __construct()
    {
        \add_action('wp_ajax_content-egg-module-api', array($this, 'addApiEntry'));
    }

    public static function apiBase()
    {
        return Plugin::slug . self::API_BASE;
    }

    public function addApiEntry()
    {
        if (!\current_user_can('edit_posts'))
        {
            throw new \Exception("Access denied.");
        }

        \check_ajax_referer('contentegg-metabox', '_contentegg_nonce');

        if (empty($_POST['module']))
        {
            die("Module is undefined.");
        }

        $module_id = TextHelper::clear(sanitize_text_field(wp_unslash($_POST['module'])));
        $parser = ModuleManager::getInstance()->parserFactory($module_id);

        if (!$parser || !$parser->isActive())
        {
            die("Parser module " . esc_html($parser->getId()) . " is inactive.");
        }

        if (isset($_POST['query']))
            $query = wp_unslash($_POST['query']); // phpcs:ignore
        else
            $query = '';
        
        $query = json_decode($query, true);

        if (!$query)
        {
            die("Error: 'query' parameter cannot be empty.");
        }

        if (empty($query['keyword']))
        {
            die("Error: 'keyword' parameter cannot be empty.");
        }

        if ($query['keyword'][0] == '[' || filter_var($query['keyword'], FILTER_VALIDATE_URL))
        {            
            $keyword = filter_var($query['keyword'], FILTER_SANITIZE_URL);
        } else
        {
            $keyword = sanitize_text_field($query['keyword']);
        }

        if (!$keyword)
        {
            die("Error: 'keyword' parameter cannot be empty.");
        }

        try
        {
            $data = $parser->doMultipleRequests($keyword, $query);
            foreach ($data as $key => $item)
            {
                if (!$item->unique_id)
                {
                    throw new \Exception('Item data "unique_id" must be specified.');
                }

                if ($item->description)
                {
                    if (!TextHelper::isHtmlTagDetected($item->description))
                    {
                        $item->description = TextHelper::br2nl($item->description);
                    }

                    $item->description = TextHelper::removeExtraBreaks($item->description);
                }

                if (property_exists($item, 'price'))
                {
                    if (!(float) $item->price)
                    {
                        $item->price = 0;
                        $item->priceOld = 0;
                    } elseif (!(float) $item->priceOld)
                    {
                        $item->priceOld = 0;
                    }
                }
            }
            $this->formatJson(array('results' => $data, 'error' => ''));
        } catch (\Exception $e)
        {
            $this->formatJson(array('error' => $e->getMessage()));
        }
    }

    public function formatJson($data)
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        \wp_die();
    }

}
