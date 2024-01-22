<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;
use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * CdkeyscomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class CdkeyscomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='wrap cms-content']//a[@class='product-item-link']/@href",
            ".//*[@class='product-items']//div[@class='product-item-info']/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//a[@class='action next button blue outline']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//div[@class='product attribute description']",
        );

        $txt = $this->xpathScalar($paths, true);
        $txt = str_replace('Get your instant download with CDKeys.com', '', $txt);
        return $txt;
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='product-info-price']//span[@class='old-price']//span[@class='price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//meta[@property='og:image']/@content",
            ".//img[@class='gallery-placeholder__image']/@src",
            ".//div[@class='product media']//img/@src",
        );

        return $this->xpathArray($paths);
    }

    public function parseImages()
    {
        $paths = array(
            ".//div[contains(@class, 'gallery-screenshots')]//img/@src",
        );

        return $this->xpathArray($paths);
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//div[@class='product attribute publisher']/div[@class='value']",
            ".//div[@class='product attribute developer']/div[@class='value']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseReviews()
    {

        if (!preg_match('/,"catalog_product_view_id_(\d+)",/', $this->html, $matches))
            return array();

        $url = 'https://www.cdkeys.com/review/product/listAjax/id/' . urlencode($matches[1]) . '/';

        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        if (!$reviews = $xpath->xpathArray(".//ol[@class='items review-items']//*[@itemprop='description']", true))
            return array();

        $ratings = $xpath->xpathArray(".//ol[@class='items review-items']//*[@itemprop='ratingValue']");
        $authors = $xpath->xpathArray(".//ol[@class='items review-items']//*[@itemprop='author']");
        $dates = $xpath->xpathArray(".//ol[@class='items review-items']//*[@itemprop='datePublished']");

        $results = array();
        for ($i = 0; $i < count($reviews); $i++)
        {
            $review = array();
            $review['review'] = $reviews[$i];

            if (isset($ratings[$i]))
                $review['rating'] = $ratings[$i];

            if (isset($authors[$i]))
                $review['author'] = $authors[$i];

            if (isset($dates[$i]))
            {
                $parts = explode('/', $dates[$i]);
                if (count($parts) == 3)
                    $dates[$i] = join('.', $parts);
                $review['date'] = strtotime($dates[$i]);
            }

            $results[] = $review;
        }

        return $results;
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('https://cdn.cdkeys.com/media/catalog/', 'https://cdn.cdkeys.com/700x700/media/catalog/', $product->image);

        if (!$product->categoryPath)
        {
            if (strpos($this->getUrl(), '/pc/'))
                $product->categoryPath = array('PC', 'Games');
            elseif (strpos($this->getUrl(), '/playstation-network-psn/'))
                $product->categoryPath = array('PSN');
            elseif (strpos($this->getUrl(), '/xbox-live/'))
                $product->categoryPath = array('Xbox');
            elseif (strpos($this->getUrl(), '/nintendo/'))
                $product->categoryPath = array('Nintendo');
        }

        return $product;
    }

}
