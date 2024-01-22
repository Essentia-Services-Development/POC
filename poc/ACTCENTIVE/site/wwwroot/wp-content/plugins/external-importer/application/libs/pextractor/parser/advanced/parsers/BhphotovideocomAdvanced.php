<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * BhphotovideocomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class BhphotovideocomAdvanced extends AdvancedParser {
    
    public function parseLinks()
    {
        $path = array(
            ".//a[@data-selenium='miniProductPageProductImgLink']/@href",
            ".//h5/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul//a[@data-selenium='listingPagingLink']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[contains(@class, 'pricesContainer')]//del[contains(@class, 'strikeThroughPrice')]",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $images = array();
        if ($results = $this->xpathArray(".//div[contains(@class, 'thumbnails_')]//img/@src"))
            unset($results[0]);

        foreach ($results as $img)
        {
            $img = str_replace('/smallimages/', '/images500x500/', $img);
            $img = str_replace('/thumbnails/', '/images500x500/', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@class='itemAttr']//tr/td[@class='attrLabels']",
                'value' => ".//div[@class='itemAttr']//tr/td[position() mod 2 = 0]",
            ),
        );
    }

    public function parseReviews()
    {
        if (!preg_match('~\/(\d+)-REG\/~', $this->getUrl(), $matches))
            return array();

        $url = 'https://www.bhphotovideo.com/bnh/controller/home?A=GetReviews&O=&Q=json&pageSize=100&currReviews=1&sku=' . urlencode($matches[1]);
        $response = $this->getRemoteJson($url);

        if (!$response || !isset($response['reviews']))
            return array();

        $results = array();
        foreach ($response['reviews'] as $r)
        {
            $review = array();
            if (!isset($r['review']))
                continue;

            $review['review'] = $r['review'];

            if (isset($r['rating']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['rating']);

            if (isset($r['name']))
                $review['author'] = $r['name'];

            if (isset($r['created_date']))
                $review['date'] = strtotime($r['created_date']);

            $results[] = $review;
        }
        return $results;
    }

    public function parseCurrencyCode()
    {
        return 'USD';
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('/images2500x2500/', '/images500x500/', $product->image);

        array_pop($product->categoryPath);
        return $product;
    }

}
