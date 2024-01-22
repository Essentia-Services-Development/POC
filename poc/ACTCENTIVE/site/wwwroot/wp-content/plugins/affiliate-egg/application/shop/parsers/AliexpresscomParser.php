<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

use Keywordrush\AffiliateEgg\TextHelper;

/**
 * AliexpresscomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AliexpresscomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    protected $_c;
    protected $user_agent = array('ia_archiver');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );
    protected $_product = array();

    public function parseCatalog($max)
    {
        // login required for search
        $catalog = $this->xpathArray(".//h3//a[contains(@class, 'product')]/@href");
        if (!$catalog)
            $catalog = array_slice($this->xpathArray(".//*[@id='hs-below-list-items']/li//h3/a[@class]/@href"), 0, $max);
        if (!$catalog)
            $catalog = array_slice($this->xpathArray(".//ul[contains(@class,'items-list')]//a/@href"), 0, $max);
        if (!$catalog)
            $catalog = array_slice($this->xpathArray(".//ul[@id='list-items']/li//h3/a/@href"), 0, $max);
        if (!$catalog)
            $catalog = array_slice($this->xpathArray(".//*[self::ul[@id='hs-list-items'] or self::div[@id='list-items']]/..//h3/a/@href"), 0, $max);
        if (!$catalog)
            $catalog = array_slice($this->xpathArray(".//ul[contains(@class,'switch-box')]/li/a[@class='pro-name']/@href"), 0, $max);
        if (!$catalog)
            $catalog = array_slice($this->xpathArray(".//div[contains(@class,'list-box')]//h4/a/@href"), 0, $max);
        if (!$catalog)
            $catalog = array_slice($this->xpathArray(".//a[@class='item-title']/@href"), 0, $max);
        // group.aliexpress.com
        if (!$catalog)
            $catalog = array_slice($this->xpathArray(".//*[@class='group-pro-main']/a[@class='pro-name']/@href"), 0, $max);
        if (!$catalog && strstr($this->getUrl(), 'flashdeals.aliexpress.com'))
            $catalog = $this->_parseFleshDeals();

        if (!$catalog)
        {
            if (preg_match_all('/"productDetailUrl":"(.+?)"/', $this->dom->saveHTML(), $matches))
                $catalog = $matches[1];
        }

        return $catalog;
    }

    private function _parseFleshDeals()
    {
        $request_url = 'https://gpsfront.aliexpress.com/queryGpsProductAjax.do?widget_id=5547572&platform=pc&limit=12&offset=0&phase=1';
        $response = \wp_remote_get($request_url);
        if (\is_wp_error($response))
            return array();

        $body = \wp_remote_retrieve_body($response);
        if (!$body)
            return array();
        $js_data = json_decode($body, true);

        if (!$js_data || !isset($js_data['gpsProductDetails']))
            return array();

        $urls = array();
        foreach ($js_data['gpsProductDetails'] as $hit)
        {
            $urls[] = $hit['productDetailUrl'];
        }
        return $urls;
    }

    public function parseTitle()
    {
        $this->_product = array();
        $this->_parseProduct();

        if ($this->_product && isset($this->_product['pageModule']['title']))
        {
            $p = explode('-in', $this->_product['pageModule']['title']);
            return html_entity_decode($p[0]);
        }

        $xpath = array(
            ".//h1[@itemprop='name']",
            ".//div[@class='product-title']",
            ".//h1",
        );
        
        return $this->xpathScalar($xpath);
    }

    // short URL page like https://aliexpress.com/item/32934619441.html
    public function _parseProduct()
    {
        if (!preg_match('/window\.runParams = .+?data: ({.+?reqHost.+?"}}),/ims', $this->dom->saveHTML(), $matches))
            return;
        
        $result = json_decode($matches[1], true);
        if (!$result || !isset($result['priceModule']) || !isset($result['pageModule']))
            return false;

        $this->_product['priceModule'] = $result['priceModule'];
        $this->_product['pageModule'] = $result['pageModule'];
        if (isset($result['specsModule']))
            $this->_product['specsModule'] = $result['specsModule'];
        if (isset($result['titleModule']))
            $this->_product['titleModule'] = $result['titleModule'];
    }

    public function parseDescription()
    {
        $xpath = array(
            ".//div[contains(@class, 'ProductDescription-module_content')]",
        );
        
        if ($d = $this->xpathScalar($xpath, true))
            return $d;

    }

    public function parsePrice()
    {
        if ($this->_product && isset($this->_product['priceModule']['minActivityAmount']['value']))
            return $this->_product['priceModule']['minActivityAmount']['value'];

        if ($this->_product && isset($this->_product['priceModule']['minAmount']['value']))
            return $this->_product['priceModule']['minAmount']['value'];

        $xpath = array(
            ".//div[contains(@class, 'Product_Price__container')]//span[contains(@class, 'product-price-current')]",
            ".//*[@id='j-multi-currency-price']//*[@itemprop='lowPrice']",
            ".//dl[@class='product-info-current']//span[@itemprop='price' or @itemprop='lowPrice']",
            ".//div[@class='cost-box']//b",
            ".//*[@id='j-sku-discount-price']",
            ".//*[@id='j-sku-price']//*[@itemprop='lowPrice']",
            ".//*[@id='j-sku-price']",
            ".//div/span[contains(@class, 'uniformBannerBoxPrice')]",
        );

        $price = $this->xpathScalar($xpath);

        if ($price)
        {
            $parts = explode('-', $price);
            $price = $parts[0];
        }
        
        $price = str_replace(' ', '', $price);
        if (strstr($price, 'руб.'))
        {
            $price = str_replace(',', '.', $price);
            $price = str_replace('руб.', '', $price);
            $this->_c = 'RUB';
        }
        return $price;
    }

    public function parseOldPrice()
    {
        if ($this->_product && isset($this->_product['priceModule']['minAmount']['value']))
            return $this->_product['priceModule']['minAmount']['value'];

        $xpath = array(
            ".//div[contains(@class, 'Product_Price__container')]//span[contains(@class, 'product-price-origin')]",
            ".//dl[@class='product-info-original']//span[@id='sku-price']",
            ".//dl[@class='product-info-current']//span[@itemprop='price' or @itemprop='lowPrice']",
            ".//*[@id='j-sku-price']",
        );


        $price = $this->xpathScalar($xpath);

        if ($price)
        {
            $parts = explode('-', $price);
            $price = $parts[0];
        }
        
        $price = str_replace(' ', '', $price);
        if (strstr($price, 'руб.'))
        {
            $price = str_replace(',', '.', $price);
            $price = str_replace('руб.', '', $price);
        }
        
        return $price;
    }

    public function parseManufacturer()
    {
        return trim($this->xpathScalar(".//div[contains(@class,'product-params')]//dl/dt/span[normalize-space(text())='Brand Name:']/../parent::dl/dd"));
    }

    public function parseImg()
    {
        if ($this->_product && isset($this->_product['pageModule']['imagePath']))
            return $this->_product['pageModule']['imagePath'];

        $xpath = array(
            ".//figure[contains(@class, 'Product_Gallery')]//img/@src",
            ".//div[@id='img']//div[@class='ui-image-viewer-thumb-wrap']/a/img/@src",
            ".//*[@id='j-detail-gallery-main']//img/@src",
        );

        $img = $this->xpathScalar($xpath);
        $img = str_replace('.jpg_640x640', '.jpg_350x350', $img);

        return $img;
    }

    public function parseImgLarge()
    {
        if ($this->item['orig_img'])
            return str_replace('.jpg_350x350', '', $this->item['orig_img']);
    }

    public function parseExtra()
    {
        $extra = array();

        if (isset($this->_product['titleModule']['feedbackRating']['averageStar']))
            $extra['rating'] = TextHelper::ratingPrepare($this->_product['titleModule']['feedbackRating']['averageStar']);

        if (isset($this->_product['specsModule']['props']))
        {
            $extra['features'] = array();
            foreach ($this->_product['specsModule']['props'] as $prop)
            {
                $feature = array();
                $feature['name'] = sanitize_text_field(html_entity_decode($prop['attrName']));
                $feature['value'] = sanitize_text_field(html_entity_decode($prop['attrValue']));
                $extra['features'][] = $feature;
            }
        }

        if (empty($extra['features']))
        {
            $extra['features'] = array();
            $names = $this->xpathArray(".//ul[contains(@class,'product-property-list')]//span[@class='propery-title']");
            $values = $this->xpathArray(".//ul[contains(@class,'product-property-list')]//span[@class='propery-des']");
            if (!$names)
            {
                $names = $this->xpathArray(".//div[@class='ui-box-body']//dt");
                $values = $this->xpathArray(".//div[@class='ui-box-body']//dd");
            }
            $feature = array();
            for ($i = 0; $i < count($names); $i++)
            {
                if (!empty($values[$i]) && trim($names[$i]) != "Brand Name")
                {
                    $feature['name'] = sanitize_text_field(str_replace(':', '', html_entity_decode($names[$i])));
                    $feature['value'] = explode(',', sanitize_text_field(html_entity_decode($values[$i])));
                    $feature['value'] = join(', ', $feature['value']);
                    $extra['features'][] = $feature;
                }
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//*[@id='j-image-thumb-list']//img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            $extra['images'][] = str_replace('.jpg_50x50', '.jpg_640x640', $res);
        }

        if (empty($extra['rating']))
            $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']"));

        return $extra;
    }

    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;
    }

    public function getCurrency()
    {
        if ($this->_c)
            return $this->_c;
        
        if ($this->_product && isset($this->_product['priceModule']['minAmount']['currency']))
            return $this->_product['priceModule']['minAmount']['currency'];

        $price = $this->xpathScalar(".//div/span[contains(@class, 'uniformBannerBoxPrice')]");
        if (strstr($price, 'руб.'))
            return 'RUB';

        $currency = $this->xpathScalar(".//*[@itemprop='priceCurrency']/@content");
        if (!$currency)
            $currency = 'USD';
        return $currency;
    }

}
