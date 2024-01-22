<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * CromacomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class CromacomAdvanced extends AdvancedParser {

    protected $_prices = array();

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='product-info']//a/@href",
            ".//a[@class='product__list--name']/@href",
            ".//a[@class='productMainLink']/@href",
            ".//a[@class='productMainLink']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul[@class='pagination']/li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@class='pd-title pd-title-normal']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//div[@class='cp-overview pd-eligibility-wrap']",
        );

        return $this->xpathScalar($paths, true);
    }

    public function parseImage()
    {
        $paths = array(
            ".//img[@id='0prod_img']/@data-src",
        );

        return $this->xpathScalar($paths);
    }    
    
    public function parseImages()
    {
        $paths = array(
            ".//div[@class='gallery-thumbs']//img/@data-src",
        );

        $results = $this->xpathArray($paths);

        foreach ($results as $img)
        {
            $img = str_replace(',h_80,w_80', ',h_380,w_380', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function parsePrice()
    {
        $url = strtok($this->getUrl(), '?');
        if (!preg_match('~/p/(\d+)~', $url, $matches))
            return;

        $url = 'https://api.croma.com/products/mobile-app/v1/' . urlencode($matches[1]) . '/price';
        if (!$response = $this->getRemoteJson($url))
            return;
        $this->_prices = $response;

        if (isset($this->_prices['sellingPrice']['value']))
            return $this->_prices['sellingPrice']['value'];
        elseif (isset($this->_prices['mrp']['value']))
            return $this->_prices['mrp']['value'];
    }

    public function parseOldPrice()
    {
        if (isset($this->_prices['mrp']['value']))
            return $this->_prices['mrp']['value'];
    }

    public function parseInStock()
    {
        if ($this->xpathScalar(".//span[@id='outofstockmsg']") == 'This product is currently Out of Stock.')
            return false;
        else
            return true;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@class='cp-specification']//li/h4",
                'value' => ".//div[@class='cp-specification']//li[@class='cp-specification-spec-details']",
            ),
        );
    }

    /*
    public function parseReviews()
    {
        $url = strtok($this->getUrl(), '?');
        $url = rtrim($url, "#");
        $url .= '/reviewhtml/all';
        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        $r = array();
        $users = $xpath->xpathArray(".//li//div[@class='autor']");
        $comments = $xpath->xpathArray(".//li//div[@class='content']");
        $ratings = $xpath->xpathArray(".//li//div/@data-rating");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['review'] = \sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['author'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]) && preg_match('/"rating":"(.+?)"/', $ratings[$i], $matches))
                $comment['rating'] = ExtractorHelper::ratingPrepare($matches[1]);
            $r[] = $comment;
        }
        return $r;
    }
     * 
     */

    public function parseCurrencyCode()
    {
        return 'INR';
    }

}
