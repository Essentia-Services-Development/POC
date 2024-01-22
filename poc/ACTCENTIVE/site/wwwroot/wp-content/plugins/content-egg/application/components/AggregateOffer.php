<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\WooIntegrator;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\components\ContentProduct;

/**
 * AggregateOffer class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AggregateOffer
{

    public static function initAction()
    {
        if (GeneralConfig::getInstance()->option('aggregate_offer') !== 'enabled')
            return;

        \add_action('woocommerce_structured_data_product', array(__CLASS__, 'addStructuredDataProduct'), 10, 2);
    }

    public static function addStructuredDataProduct($markup, $product)
    {
        $post_id = $product->get_id();

        if (!WooIntegrator::getMetaSyncUniqueId($post_id))
            return $markup;

        $data = ContentManager::getViewProductData($post_id);

        if (!$data)
            return $markup;

        foreach ($data as $i => $d)
        {
            if ($d['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
                unset($data[$i]);
        }
        $data = array_values($data);

        $offer_count = count($data);

        if ($offer_count <= 1)
            return $markup;

        $data = TemplateHelper::sortByPrice($data);
        $min_price_item = TemplateHelper::getMinPriceItem($data);

        if (!$min_price_item)
            return $markup;

        $max_price_item = TemplateHelper::getMaxPriceItem($data);

        $markup['offers'] = array(
            '@type' => 'AggregateOffer',
            'offerCount' => $offer_count,
        );

        if ($min_price_item['price'])
        {
            $markup['offers']['lowPrice'] = sprintf('%0.2f', $min_price_item['price']);
            $markup['offers']['priceCurrency'] = $min_price_item['currencyCode'];
        }

        if ($max_price_item['price'])
        {
            $markup['offers']['highPrice'] = sprintf('%0.2f', $max_price_item['price']);
            $markup['offers']['priceCurrency'] = $min_price_item['currencyCode'];
        }

        return $markup;
    }
}
