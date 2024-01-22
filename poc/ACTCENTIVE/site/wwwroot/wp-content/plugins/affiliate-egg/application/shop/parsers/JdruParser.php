<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * JdruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class JdruParser extends ShopParser {

    protected $charset = 'utf-8';
    private $price_results;

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@class='p-title']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            $urls[$i] = 'https:' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(array(".//*[@class='summary']//h1", ".//*[@class='title']//h1"));
    }

    public function parseDescription()
    {
        
    }

    public function parsePrice()
    {
        if (!preg_match('/(\d+)\.html/', $this->getUrl(), $matches))
            return;
        $sid = $matches[1];
        if (!$sid)
            return;

        $html = $this->dom->saveHTML();
        if (preg_match('/skuId: (\d+),/', $html, $matches))
            $skuId = $matches[1];
        else
            $skuId = '';

        $request_params = array(
            'channel' => 2,
            'site' => 2,
            'sid' => $sid,
            'curList' => array('USD'),
            'skuId' => $skuId,
        );
        try
        {
            $result = $this->requestGet('https://ipromo.joybuy.com/api/promoinfo/getInfo.html?json=' . json_encode($request_params), false);
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

    public function parseManufacturer()
    {
        // seller
        // return $this->xpathScalar(".//*[@id='summary-price']/@vendername");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//*[@id='spec-img']/@src");
        if ($img)
            return 'https:' . $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();
        return $extra;
    }

    public function isInStock()
    {
        if (!$this->parsePrice())
            return false;
        else
            return true;
    }

    public function getCurrency()
    {
        if ($this->price_results && isset($this->price_results['priceList'][0]['currency']))
            return $this->price_results['priceList'][0]['currency'];
        else
            return 'USD';
    }

}
