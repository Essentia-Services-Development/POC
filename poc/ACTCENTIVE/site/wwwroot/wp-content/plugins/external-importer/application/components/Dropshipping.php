<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\DropshippingConfig;
use ExternalImporter\application\helpers\CurrencyHelper;

/**
 * Dropshipping class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */

class Dropshipping {

    public static function calculatePrice($price, $domain, $objProduct)
    {
        if (!$price)
            return $price;

        $product_type = DropshippingConfig::getInstance()->option('product_type');
        if ($product_type != 'any' && $objProduct->get_type() != $product_type)
            return \apply_filters('ei_calculate_dropshipping_price', $price, $domain, $objProduct);

        if (!$price_rule = self::findPriceRule($price, $domain))
            return \apply_filters('ei_calculate_dropshipping_price', $price, $domain, $objProduct);

        if ($price_rule['margin_type'] == 'percent')
            $price = $price + $price * (float) $price_rule['margin'] / 100;
        elseif ($price_rule['margin_type'] == 'flat')
            $price += (float) $price_rule['margin'];

        $round = DropshippingConfig::getInstance()->option('round');
        $precision = (int) DropshippingConfig::getInstance()->option('round_precision');

        if ($round == 'round_up')
            $price = CurrencyHelper::roundUp($price, $precision);
        elseif ($round == 'round_down')
            $price = CurrencyHelper::roundDown($price, $precision);
        elseif ($round == 'round')
            $price = round($price, $precision);
        elseif ($round == 'round_up_50')
            $price = CurrencyHelper::ceiling($price, 50);
        elseif ($round == 'round_up_100')
            $price = CurrencyHelper::ceiling($price, 100);
        else
            $price = (float) $price;
        
        return \apply_filters('ei_calculate_dropshipping_price', $price, $domain, $objProduct);
    }

    public static function findPriceRule($price, $domain)
    {
        if (!$price_rules = DropshippingConfig::getInstance()->option('price_rules'))
            return false;

        foreach ($price_rules as $price_rule)
        {
            if ($price_rule['domain'] && $price_rule['domain'] != $domain)
                continue;

            if ((float) $price_rule['price_from'] && (float) $price < (float) $price_rule['price_from'])
                continue;

            if ((float) $price_rule['price_to'] && (float) $price > (float) $price_rule['price_to'])
                continue;

            if (!(float) $price_rule['margin'])
                continue;

            return $price_rule;
        }

        return false;
    }

}
