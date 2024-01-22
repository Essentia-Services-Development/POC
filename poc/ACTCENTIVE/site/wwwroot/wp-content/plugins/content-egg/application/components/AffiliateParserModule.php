<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TextHelper;

/**
 * AffiliateParserModule abstract class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
abstract class AffiliateParserModule extends ParserModule {

    final public function isAffiliateParser()
    {
        return true;
    }

    public function isCouponParser()
    {
        if (strpos($this->getName(), 'Coupon') !== false || $this->getName() == 'CJ Links')
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function isProductParser()
    {
        return !$this->isCouponParser();
    }

    public function isAeParser()
    {
        if ($this->getIdStatic() == 'AE')
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function isItemsUpdateAvailable()
    {
        return false;
    }

    public function doRequestItems(array $items)
    {
        throw new \Exception('doRequestItems method not implemented yet');
    }

    public function presavePrepare($data, $post_id)
    {
        $data = parent::presavePrepare($data, $post_id);
        foreach ($data as $key => $item)
        {
            $data[$key]['percentageSaved'] = 0;
            if (!isset($data[$key]['priceOld']))
            {
                $data[$key]['priceOld'] = $item['priceOld'] = 0;
            }
            if (!empty($item['priceOld']) && $item['priceOld'] <= $item['price'])
            {
                $data[$key]['priceOld'] = 0;
            }

            if (!isset($data[$key]['priceOld']))
            {
                $data[$key]['priceOld'] = 0;
            }
            if ($data[$key]['priceOld'] && $data[$key]['price'] && $data[$key]['price'] < $data[$key]['priceOld'])
            {
                $data[$key]['percentageSaved'] = floor(( (float) $data[$key]['priceOld'] - (float) $data[$key]['price'] ) / (float) $data[$key]['priceOld'] * 100);
            }

            if (empty($data[$key]['currency']) && !empty($item['currencyCode']))
            {
                $data[$key]['currency'] = TextHelper::currencyTyping($item['currencyCode']);
            }

            if (!empty($data[$key]['domain']))
            {
                $data[$key]['merchant'] = \apply_filters('content_egg_custom_merchant', $data[$key]['merchant'], $data[$key]['domain']);
            }
        }

        return $data;
    }

    public function renderUpdatePanel()
    {
        
    }

}
