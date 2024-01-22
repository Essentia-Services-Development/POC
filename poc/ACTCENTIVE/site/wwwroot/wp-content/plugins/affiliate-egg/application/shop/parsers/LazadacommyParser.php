<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * LazadacommyParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class LazadacommyParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'MYR';
    protected $_images = array();
    protected $_ld_offers;
    protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        $html = $this->dom->saveHTML();
        if (preg_match_all('/"productUrl":"(.+?)"/', $html, $matches))
            return $matches[1];
        $this->_parseLdOffers();
        $urls = array();
        if ($this->_ld_offers)
        {
            foreach ($this->_ld_offers['itemListElement'] as $offer)
            {
                $urls[] = $offer['url'];
            }
        }
        if (!$urls)
        {
            $urls = array_slice($this->xpathArray(".//*[@class='c-product-card__description']/a/@href"), 0, $max);
            $host = parse_url($this->getUrl(), PHP_URL_HOST);
            foreach ($urls as $i => $url)
            {
                if (!preg_match('/^https?:\/\//', $url))
                    $urls[$i] = 'https://' . $host . $url;
            }
        }
        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
        }
        return $urls;
    }

    public function _parseLdOffers()
    {
        $lds = $this->xpathArray(".//script[@type='application/ld+json']", true);
        foreach ($lds as $ld)
        {
            if (!$data = json_decode($ld, true))
                continue;

            if (isset($data['@type']) && $data['@type'] == 'ItemList')
            {
                $this->_ld_offers = $data;
                break;
            }
        }
    }

    public function parseTitle()
    {
        return html_entity_decode(parent::parseTitle());
    }

    public function parseDescription()
    {
        return html_entity_decode(parent::parseDescription());
    }

    public function parsePrice()
    {
        if ($price = parent::parsePrice())
        {
            if (is_scalar($price))
                return $price;
            elseif (is_array($price) && isset($price['amountFractionNumerator']))
                return $price['amountFractionNumerator'];
        }

        $price = $this->xpathScalar(".//*[@id='special_price_box']");
        if (!$price)
            $price = $this->xpathScalar(".//*[@class='pdp-product-price']/span[1]");
        $price = str_replace(',', '', $price);
        return $price;
    }

    public function parseOldPrice()
    {
        $price = $this->xpathScalar(".//*[@id='price_box']");
        if (!$price)
            $price = $this->xpathScalar(".//*[@class='origin-block']/span");

        if (!$price)
        {
            if (preg_match('/,"pdt_price":"([\d\.]+).+?"/', $this->dom->saveHTML(), $matches))
                $price = $matches[1];
        }
        return str_replace(',', '', $price);
    }

    public function parseImg()
    {
        if ($img = parent::parseImg())
            return str_replace('-catalog.jpg', '-catalog.jpg_720x720q75.jpg', $img);
        else
            return $this->xpathScalar(".//img[@class='pdp-mod-common-image gallery-preview-panel__image']/@src");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//ul[@class='specification-keys']//*[@class='key-title']");
        $values = $this->xpathArray(".//ul[@class='specification-keys']//*[@class='html-content key-value']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        if (!empty($this->ld_json['sku']))
            $extra['sku'] = $this->ld_json['sku'];

        return $extra;
    }

}
