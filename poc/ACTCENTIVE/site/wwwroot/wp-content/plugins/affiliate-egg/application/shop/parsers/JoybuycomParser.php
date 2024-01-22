<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * JoybuycomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
require_once dirname(__FILE__) . '/JdruParser.php';

class JoybuycomParser extends JdruParser {

    public function parsePrice()
    {
        if (!preg_match('/skuId: (\d+),/', $this->dom->saveHtml(), $matches))
            return;
        $sid = $matches[1];
        if (!$sid)
            return;

        $request_params = array(
            'skuId' => $sid,
            'site' => 1,
            'channel' => 2,
            'curList' => array('USD'),
        );
        try
        {
            $result = $this->requestGet('https://ipromo.joybuy.com/api/promoinfo/getPriceInfo.html?json=' . json_encode($request_params), false);
        } catch (\Exception $e)
        {
            return;
        }
        if (!$result || !$this->price_results = json_decode($result, true))
            return;
        if (isset($this->price_results['plummetInfoDto']['priceInfos'][0]['discountPrice']))
            return $this->price_results['plummetInfoDto']['priceInfos'][0]['discountPrice'];
        elseif (isset($this->price_results['plummetInfoDto']['priceInfos'][0]['jdPrice']))
            return $this->price_results['plummetInfoDto']['priceInfos'][0]['jdPrice'];
        else
            return;
    }

    public function parseOldPrice()
    {
        if ($this->price_results && isset($this->price_results['plummetInfoDto']['priceInfos'][0]['jdPrice']))
            return $this->price_results['plummetInfoDto']['priceInfos'][0]['jdPrice'];
    }

}
