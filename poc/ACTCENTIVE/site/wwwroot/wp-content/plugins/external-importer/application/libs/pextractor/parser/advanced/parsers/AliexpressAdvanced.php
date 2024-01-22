<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;

/**
 * AliexpressAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AliexpressAdvanced extends AdvancedParser {

    protected $_product = array();
    protected $_c;

    public function getHttpOptions()
    {
        $user_agent = array('ia_archiver');
        return array('user-agent' => $user_agent[array_rand($user_agent)]);
    }

    protected function preParseProduct()
    {
        $this->_getProduct();
        return parent::preParseProduct();
    }

    public function parseLinks()
    {
        $urls = array();

        if ($urls = $this->xpathArray(".//a[@class='pic-rind']/@href"))
        {
            foreach ($urls as $i => $url)
            {
                $urls[$i] = strtok($url, '?');
            }

            return $urls;        }

        if (preg_match_all('~productDetailUrl":"(.+?)"~', $this->html, $matches))
        {
            $urls = $matches[1];
            foreach ($urls as $i => $url)
            {
                $urls[$i] = strtok($url, '?');
            }

            return $urls;
        }
        
        if (preg_match_all('~"productId":"(\d+)"~', $this->html, $matches))
        {
            foreach ($matches[1] as $i => $id)
            {
                $urls[] = 'https://www.aliexpress.com/item/' . $id . '.html';
            }
            return $urls;
        }
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@id='pagination-bottom']//a/@href",
        );

        return $this->xpathArray($path);
    }


    public function _getProduct()
    {
        if (!preg_match('/window\.runParams = .+?data: ({.+?reqHost.+?"}}),/ims', $this->html, $matches))
            return;

        $result = json_decode($matches[1], true);

        if (!$result || !isset($result['priceModule']) || !isset($result['pageModule']))
            return false;

        $this->_product = $result;
    }

    public function parseTitle()
    {
        if (isset($this->_product['titleModule']['subject']))
            return $this->_product['titleModule']['subject'];

        $xpath = array(
            ".//h1[@itemprop='name']",
            ".//div[@class='product-title']",
            ".//h1",                        
        );

        return $this->xpathScalar($xpath);
    }

    public function parseDescription()
    {
        $xpath = array(
            ".//div[contains(@class, 'ProductDescription-module_content')]",
        );
        
        if ($d = $this->xpathScalar($xpath, true))
            return $d;

        if (!preg_match('/"descriptionUrl":"(.+?)"/', $this->html, $matches))
            return '';

        $d = $this->getRemote($matches[1]);
        $d = preg_replace('/<script.?>.+?<\/script>/', '', $d);
        return $d;
    }

    public function parseFeatures()
    {

        if (!isset($this->_product['specsModule']['props']) || !is_array($this->_product['specsModule']['props']))
            return array();

        $features = array();
        foreach ($this->_product['specsModule']['props'] as $prop)
        {
            $feature = array();
            $feature['name'] = \sanitize_text_field($prop['attrName']);
            $feature['value'] = \sanitize_text_field($prop['attrValue']);
            $features[] = $feature;
        }

        return $features;
    }

    public function parsePrice()
    {        
        if (isset($this->_product['priceModule']['minActivityAmount']['value']))
            return $this->_product['priceModule']['minActivityAmount']['value'];

        if (isset($this->_product['priceModule']['minAmount']['value']))
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
        if (isset($this->_product['priceModule']['minAmount']['value']))
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

    public function parseImage()
    {
        if (isset($this->_product['pageModule']['imagePath']))
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

    public function parseImages()
    {
        if (isset($this->_product['imageModule']['imagePathList']) && is_array($this->_product['imageModule']['imagePathList']))
            return $this->_product['imageModule']['imagePathList'];
    }

    public function parseCurrencyCode()
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

    public function parseReviews()
    {

        if (!preg_match('~"sellerAdminSeq":(\d+),~', $this->html, $matches))
            return;

        $ownerMemberId = $matches[1];

        if (!preg_match('~(\d+)\.html~', $this->getUrl(), $matches))
            return;

        $productId = $matches[1];

        $request_url = 'https://feedback.aliexpress.com/display/productEvaluation.htm#feedback-list';
        $response = \wp_remote_post($request_url, array(
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
            'body' => 'ownerMemberId=' . $ownerMemberId . '&memberType=seller&productId=' . $productId . '&companyId=&evaStarFilterValue=all+Stars&evaSortValue=sortdefault%40feedback&page=1&currentPage=1&startValidDate=&i18n=true&withPictures=false&withAdditionalFeedback=false&onlyFromMyCountry=false&version=&isOpened=true&translate=+Y+&jumpToTop=true&v=2',
            'method' => 'POST'
        ));

        if (\is_wp_error($response))
            return;

        if (!$body = \wp_remote_retrieve_body($response))
            return;

        $xpath = new XPath(Dom::createFromString($body));

        $r = array();
        $users = $xpath->xpathArray(".//span[@class='user-name']");
        $comments = $xpath->xpathArray(".//dt[@class='buyer-feedback']/span[1]");
        $ratings = $xpath->xpathArray(".//span[@class='rate-score-number']/b");
        $dates = $xpath->xpathArray(".//span[@class='r-time-new']");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['review'] = \sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['author'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = (float)$ratings[$i];
            if (!empty($dates[$i]))
                $comment['date'] = strtotime($dates[$i]);
            $r[] = $comment;
        }

        return $r;
    }

    public function afterParseFix(Product $product)
    {
        //$product->description = '';
        return $product;
    }

}
