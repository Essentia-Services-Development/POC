<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * TikivnAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class TikivnAdvanced extends AdvancedParser {

    public function parseLinks()
    {

        if ($urls = $this->_getFilteredLinks())
            return $urls;

        if (preg_match_all('~"Product","url":"(.+?)",~', $this->html, $matches))
            $urls = $matches[1];

        if (!$urls)
        {
            $path = array(
                ".//div[contains(@class, 'product-item')]//a/@href",
                ".//*[@class='search-a-product-item']",
                ".//p[@class='title']/a/@href",
                ".//a[@class='product-item']/@href",
            );

            $urls = $this->xpathArray($path);
        }
        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
        }

        return $urls;
    }

    public function _getFilteredLinks()
    {
        $query = parse_url($this->getUrl(), PHP_URL_QUERY);
        parse_str($query, $arr);

        if (!$path = parse_url($this->getUrl(), PHP_URL_PATH))
            return array();

        if (!preg_match('~/c(\d+)~', $path, $matches))
            return array();

        $category_id = $matches[1];
        

        $url = 'https://tiki.vn/api/personalish/v1/blocks/listings?limit=300&include=advertisement&aggregations=1&category=' . urlencode($category_id);
        $url = \add_query_arg($arr, $url);
        
        
        $response = $this->getRemoteJson($url);

        if (!$response || !isset($response['data']))
            return array();

        $urls = array();
        foreach ($response['data'] as $d)
        {
            $urls[] = $d['url_key'] . '.html';
        }

        return $urls;
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='list-pager']//li/a/@href",
        );

        $urls = $this->xpathArray($path);
        if (!$urls)
            $urls = array(\add_query_arg('page', 2, $this->getUrl()));

        return $urls;
    }

    public function parseDescription()
    {
        $path = array(
            ".//div[@class='content']//div[contains(@class, 'ToggleContent__Wrapper-sc')]",
            ".//div[@class='summary']//div[@class='group border-top']",
        );
        return $this->xpathScalar($path, true);
    }

    public function parseOldPrice()
    {
        if (preg_match('/,"list_price":(\d+),"/', $this->html, $matches))
            return $matches[1];

        $paths = array(
            ".//div[@class='summary']//p[@class='original-price']",
        );

        if ($price = $this->xpathScalar($paths))
            return $price;
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        $images = array();
        $paths = array(
            ".//div[@class='review-images']//img/@src",
            ".//div[@class='group-images']//div/@src",
            ".//div[@class='review-images']//picture/@srcset",            
        );
        $results = $this->xpathArray($paths);
        foreach ($results as $img)
        {
            $img = str_replace('/w80/', '/w390/', $img);
            $img = str_replace('/w64/', '/w390/', $img);
            $img = str_replace('/100x100/', '/w390/', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[contains(@class, 'ProductDescription__Wrapper')]//table//tr/td[1]",
                'value' => ".//div[contains(@class, 'ProductDescription__Wrapper')]//table//tr/td[2]",
            ),
            array(
                'name' => ".//div[contains(@class, 'style__Wrapper')]//table//tr/td[1]",
                'value' => ".//div[contains(@class, 'style__Wrapper')]//table//tr/td[2]",
            ),            
        );
    }

    public function parseReviews()
    {
        if (!preg_match('~-p(\d+)\.html~', $this->getUrl(), $matches))
            return array();

        $url = 'https://tiki.vn/api/v2/reviews?product_id=' . urldecode($matches[1]) . '&limit=20&sort=score|desc,id|desc,stars|all&include=comments&page=1';
        $response = $this->getRemoteJson($url);

        if (!$response || !isset($response['data']))
            return array();

        $results = array();
        foreach ($response['data'] as $r)
        {
            $review = array();

            if (empty($r['content']))
                continue;

            $review['review'] .= $r['content'];

            if (isset($r['rating']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['rating']);

            if (isset($r['created_by']['name']))
                $review['author'] = $r['created_by']['name'];

            if (isset($r['created_at']))
                $review['date'] = $r['created_at'];

            $results[] = $review;
        }
        return $results;
    }

    public function parseCurrencyCode()
    {
        return 'VND';
    }

    public function parseInStock()
    {
        if ($this->xpathScalar(".//p[@class='product-status discontinued']"))
            return false;
    }

    public function afterParseFix(Product $product)
    {
        if ($product->categoryPath)
            array_shift($product->categoryPath);

        return $product;
    }

}
