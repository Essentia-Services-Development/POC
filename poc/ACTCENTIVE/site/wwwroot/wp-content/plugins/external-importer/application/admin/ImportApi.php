<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\WooHelper;
use ExternalImporter\application\helpers\InputHelper;
use ExternalImporter\application\libs\pextractor\parser\ProductProcessor;
use ExternalImporter\application\components\WooImporter;

/**
 * ImportApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class ImportApi
{

    public function __construct()
    {
        if (!WooHelper::isWooInstalled())
            return;

        \add_action('wp_ajax_ei-import-api', array($this, 'addApiEntry'));
    }

    public function addApiEntry()
    {
        if (!\current_user_can('manage_woocommerce'))
            \wp_die("Access denied.");

        \check_ajax_referer('ei-import', '_ei_nonce');

        $data = stripslashes(InputHelper::post('data', ''));
        $data = json_decode($data, true);
        if (!$data || !isset($data['product']) || !isset($data['params']))
            \wp_die("Invalid product data or params.");

        $product_data = $data['product'];
        $params = $data['params'];

        if (!$product_data || !is_array($product_data) || !isset($product_data['_index']))
            \wp_die("Invalid product data.");

        if (!is_array($params))
            \wp_die("Invalid params.");

        $product = ProductProcessor::productFactory($product_data);

        $error = '';
        try
        {
            $product_id = WooImporter::maybeInsert($product, $params);
        }
        catch (\Exception $e)
        {
            $error = $e->getMessage();
        }

        if (!$wooProduct = \wc_get_product($product_id))
            $error = 'Unknown import error';

        if ($error)
            self::jsonError($product_data['_index'], $e->getMessage());
        else
            self::jsonSuccess($product_data['_index']);
    }

    public static function jsonError($product_index, $message = '')
    {
        $data = array();
        $data['index'] = $product_index;
        $data['status'] = 'error';
        $data['message'] = $message;
        self::sendJson($data);
    }

    public static function jsonSuccess($product_index, $message = '')
    {
        $data = array();
        $data['index'] = $product_index;
        $data['status'] = 'success';
        $data['message'] = $message;
        self::sendJson($data);
    }

    public static function sendJson($data)
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        \wp_die();
    }
}
