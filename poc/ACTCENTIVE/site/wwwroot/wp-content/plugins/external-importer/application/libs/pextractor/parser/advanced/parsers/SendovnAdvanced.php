<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * SendovnAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class SendovnAdvanced extends AdvancedParser {

    private $_product = null;

    private function _maybeParseProduct()
    {
        if ($this->_product !== null)
            return;

        $path = parse_url($this->getUrl(), PHP_URL_PATH);
        $path = str_replace('.html', '', $path);
        $url = 'https://www.sendo.vn/m/wap_v2/full/san-pham/' . $path . '?platform=web';
        $response = $this->getRemoteJson($url);
        if ($response && isset($response['result']['data']['name']))
            $this->_product = $response['result'];
        else
            $this->_product = array();
    }

    public function parseLinks()
    {
        if ($urls = $this->_parseFlashSale())
            return $urls;

        $url_parts = parse_url($this->getUrl());
        $query = array();
        if (!empty($url_parts['query']))
            parse_str($url_parts['query'], $query);

        if (isset($query['q']))
        // search 
            $xhr = 'https://searchlist-api.sendo.vn/web/products?page=1&platform=web&q=' . urlencode($query['q']) . '&search_algo=algo6&size=60&sortType=rank';
        elseif (preg_match('/"category_id":(\d+)/', $this->html, $matches))
        // category
            $xhr = 'https://searchlist-api.sendo.vn/web/categories/' . (int) $matches[1] . '/products?listing_algo=algo13&page=1&platform=web&size=60&sortType=listing_v2_desc';
        else
            return array();

        if (isset($query['page']))
            $xhr = \add_query_arg('page', $query['page'], $xhr);

        $response = $this->getRemoteJson($xhr, false, 'GET', array('Referer' => 'https://www.sendo.vn/'));
        if (!$response || !isset($response['data']))
            return array();

        $urls = array();
        foreach ($response['data'] as $r)
        {
            if (!empty($r['category_path']))
                $urls[] = rtrim($r['category_path'], " /");
        }
        return $urls;
    }

    private function _parseFlashSale()
    {
        if (!strstr($this->getUrl(), 'flash-sale'))
            return false;

        $request_url = 'https://api.sendo.vn/flash-deal/buyer/ajax-product/';

        if (!$category_group_id = ExtractorHelper::getQueryVar('category_group_id', $this->getUrl()))
            $category_group_id = 0;
        if (!$slotId = ExtractorHelper::getQueryVar('slotId', $this->getUrl()))
            $slotId = 0;

        $response = \wp_remote_post($request_url, array(
            'headers' => array(),
            'body' => '{"source_block_id":"flash-sale","source_page_id":"home","source_pagetab_id":0,"category_group_id":' . urlencode($category_group_id) . ',"slot_id":' . urlencode($slotId) . ',"page":1,"limit":60,"sort_type":"random","sort_version":47}',
            'method' => 'POST'
        ));
        if (\is_wp_error($response))
            return false;

        if (!$body = \wp_remote_retrieve_body($response))
            return false;

        $response = json_decode($body, true);

        if (!$response || !isset($response['data']['products']))
            return false;

        $urls = array();
        foreach ($response['data']['products'] as $r)
        {
            $urls[] = strtok($r['url_key'], '?');
        }
        return $urls;
    }

    public function parsePagination()
    {
        return array();
    }

    public function parsePrice()
    {
        if (preg_match('~"final_price":(\d+),~', $this->html, $matches))
            return $matches[1];

        $paths = array(
            ".//*[contains(@class, 'priceBox')]//strong[contains(@class, 'currentPrice')]",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        if (preg_match('/"priceCurrency":"VND","price":(\d+?),/', $this->html, $matches))
            return $matches[1];
    }

    public function parseImages()
    {
        $this->_maybeParseProduct();
        if (isset($this->_product['data']['media']))
        {
            $images = array();
            foreach ($this->_product['data']['media'] as $i => $m)
            {
                if ($i == 0)
                    continue;
                $images[] = $m['image_500x500'];
            }
            return $images;
        }
    }

    public function parseCategoryPath()
    {
        $this->_maybeParseProduct();
        if (isset($this->_product['meta_data']['breadcrumb']))
        {
            $categs = array();
            foreach ($this->_product['meta_data']['breadcrumb'] as $i => $b)
            {
                if ($i == 0 || !empty($b['hidden']))
                    continue;
                $categs[] = $b['title'];
            }
            return $categs;
        }
    }

    public function parseReviews()
    {
        if (!preg_match('/-(\d+?)\.html/', $this->getUrl(), $matches))
            return array();

        $url = 'https://www.sendo.vn/m/wap_v2/san-pham/rating/' . urlencode($matches[1]) . '?p=1&s=10&sort=review_score&v=2';
        $response = $this->getRemoteJson($url);

        if (!$response || !isset($response['result']['data']))
            return array();

        $results = array();
        foreach ($response['result']['data'] as $r)
        {
            $review = array();
            if (!isset($r['content']))
                continue;

            $review['review'] = $r['content'];

            if (isset($r['star']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['star']);

            if (isset($r['customer_name']))
                $review['author'] = $r['customer_name'];

            if (isset($r['update_time']))
                $review['date'] = strtotime($r['update_time']);

            $results[] = $review;
        }
        return $results;
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('_250x250_', '_500x500_', $product->image);
        return $product;
    }

}
