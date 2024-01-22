<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * ShopeeAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ShopeeAdvanced extends AdvancedParser
{

    protected $_product = null;
    protected $_total_pages;

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();
        $httpOptions['user-agent'] = 'ia_archiver';
        return $httpOptions;
    }

    protected function preParseProduct()
    {
        $this->_getProduct();
        return parent::preParseProduct();
    }

    private function _getProduct()
    {
        $item_id = 0;
        $shop_id = 0;

        if (preg_match('~\-i\.(\d+\.\d+)~', $this->getUrl(), $matches))
        {
            $ids = $matches[1];
            $ids = explode('.', $ids);
            $item_id = $ids[1];
            $shop_id = $ids[0];
        }

        if (preg_match('~\/product\/(\d+)\/(\d+)~', $this->getUrl(), $matches))
        {
            $item_id = $matches[2];
            $shop_id = $matches[1];
        }

        if (!$item_id || !$shop_id)
            return;

        $url = 'https://' . $this->host . '/api/v4/item/get?itemid=' . urlencode($item_id) . '&shopid=' . urlencode($shop_id);
        $headers = array(
            'User-Agent' => 'ia_archiver',
            'af-ac-enc-dat' => 'AAcyLjQuMS0yAAABhEy+KSQAAAixAbwAAAAAAAAAAOYyhFB7rjob26/8rq6jA0F3J6Kfg5aGEX+GYncix7fIyPghAegUl5/quDPaC/cOFJZhyMo4BL1EMHMr27gx9mV/bm6wEBiYxfpCL1P+FjY47Bdci5nGh32uetdwdpjs212VDxeFirCBwj5OCqLM+pe9xRdl1MAPAWn3NcLgHHgMsOiQLnOHVeeJNvqYso4XqTBJlvEjgWfNNLiT5e1wPvI/KL075FY4WGJTxD5/VgWeag64UbXzKUOYil7HHJaV9RpGN+fkO1JQb1AhUtRIjdHz7ZWLWIY2kihZM0KVZFIwXPmnCZRIxMPWHatc3DJxrLbxnaL+fxXNIy7mvkK63w3LxpgTooqMMasGCGwd5bAcFFibZ9qZdVKv1qHvJ7GJdolLUe4jgWfNNLiT5e1wPvI/KL07Ryi5pAyGvCcYdPp9zJ0qGJNeXdCKln+GYVEMZ3p4Bme58s3Rr70BiA7Mq+yA2WDgWUdr92o69/RrQ/sCJAcKeAeSaOZ7frRmQaRpBqoe5/fTxUkPU99UCfbB66KuO2rUkWOzjLDykZY2dhCO2aemlnFWnboe7eG1bNIlDWBMwTWE9A0baDvW1a3aKHt9JBAy',
            'Cookie' => '__LOCALE__null=PH; _gcl_au=1.1.1662042777.1667734825; csrftoken=AKJ08gtFTn3xVVWpyYSlg5303Cu6BnNc; SPC_F=tzIo276scpmnDA84ZwcRVBs25Tx1INE4; REC_T_ID=cdb3730c-5dc7-11ed-971f-9440c93dba68; SPC_R_T_ID=avaFBeJxIwoBWo6TQ+PGs8vc5a1p9S6tPXP7FHZiswAiLuHetQW/jKUwqkepb/aIJLiIrpuKbt8nQz7PfN4jjkWBm4A82lQRZjHJ9l//uikGl0DpWsJ5p1BDlgvr4/Zvv8puf4W0pX5mzOsXIh/Aq444Q6VsN3fDPtfGEXGG8uU=; SPC_R_T_IV=VGV4WTZYVGpGZjExck45Uw==; SPC_T_ID=avaFBeJxIwoBWo6TQ+PGs8vc5a1p9S6tPXP7FHZiswAiLuHetQW/jKUwqkepb/aIJLiIrpuKbt8nQz7PfN4jjkWBm4A82lQR…lHZzVPdbCtdAAAAAAAakpkYTBZNHg=; _QPWSDCXHZQA=b3ae2b8d-e8e0-4c9f-b2a6-507909af3754; AMP_TOKEN=%24NOT_FOUND; _ga_CB0044GVTM=GS1.1.1667734826.1.1.1667734841.45.0.0; _ga=GA1.1.667246894.1667734826; _gid=GA1.2.282935809.1667734827; shopee_webUnique_ccd=9vNMYZuejkEYw0v8dSz5rw%3D%3D%7CzMpGSyr7LAoNWl4X1dJU0JcYOu6pQm5B0yExOowNiNC8SaKfJYEGyA0mvQeWl0PKhfTx6RlQkGom1Dx9sbNUoJJncXduCbAtAA%3D%3D%7CbPVWCe9zukbM%2Bjzo%7C06%7C3; ds=d1413786fb46e25c4a065ec050fe6d72; _dc_gtm_UA-61918643-6=1; _fbp=fb.1.1667734828974.1080449365',
            'sz-token' => '9vNMYZuejkEYw0v8dSz5rw==|zMpGSyr7LAoNWl4X1dJU0JcYOu6pQm5B0yExOowNiNC8SaKfJYEGyA0mvQeWl0PKhfTx6RlQkGom1Dx9sbNUoJJncXduCbAtAA==|bPVWCe9zukbM+jzo|06|3',
            'X-API-SOURCE' => 'pc',
            'X-CSRFToken' => 'AKJ08gtFTn3xVVWpyYSlg5303Cu6BnNc',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-Shopee-Language' => 'en',
        );
        $result = $this->getRemoteJson($url, false, 'GET', $headers);

        if (!$result || !isset($result['data']))
            return;

        $this->_product = $result['data'];
    }

    public function parseLinks()
    {
        $url = $this->getListingApiUri();
        if (!$url)
            $url = $this->getSellerApiUri();
        if (!$url)
            return array();

        $headers = array(
            'User-Agent' => 'ia_archiver',
            'af-ac-enc-dat' => 'AAcyLjQuMS0yAAABhEy+KSQAAAixAbwAAAAAAAAAAOYyhFB7rjob26/8rq6jA0F3J6Kfg5aGEX+GYncix7fIyPghAegUl5/quDPaC/cOFJZhyMo4BL1EMHMr27gx9mV/bm6wEBiYxfpCL1P+FjY47Bdci5nGh32uetdwdpjs212VDxeFirCBwj5OCqLM+pe9xRdl1MAPAWn3NcLgHHgMsOiQLnOHVeeJNvqYso4XqTBJlvEjgWfNNLiT5e1wPvI/KL075FY4WGJTxD5/VgWeag64UbXzKUOYil7HHJaV9RpGN+fkO1JQb1AhUtRIjdHz7ZWLWIY2kihZM0KVZFIwXPmnCZRIxMPWHatc3DJxrLbxnaL+fxXNIy7mvkK63w3LxpgTooqMMasGCGwd5bAcFFibZ9qZdVKv1qHvJ7GJdolLUe4jgWfNNLiT5e1wPvI/KL07Ryi5pAyGvCcYdPp9zJ0qGJNeXdCKln+GYVEMZ3p4Bme58s3Rr70BiA7Mq+yA2WDgWUdr92o69/RrQ/sCJAcKeAeSaOZ7frRmQaRpBqoe5/fTxUkPU99UCfbB66KuO2rUkWOzjLDykZY2dhCO2aemlnFWnboe7eG1bNIlDWBMwTWE9A0baDvW1a3aKHt9JBAy',
            'Cookie' => '__LOCALE__null=PH; _gcl_au=1.1.1662042777.1667734825; csrftoken=AKJ08gtFTn3xVVWpyYSlg5303Cu6BnNc; SPC_F=tzIo276scpmnDA84ZwcRVBs25Tx1INE4; REC_T_ID=cdb3730c-5dc7-11ed-971f-9440c93dba68; SPC_R_T_ID=avaFBeJxIwoBWo6TQ+PGs8vc5a1p9S6tPXP7FHZiswAiLuHetQW/jKUwqkepb/aIJLiIrpuKbt8nQz7PfN4jjkWBm4A82lQRZjHJ9l//uikGl0DpWsJ5p1BDlgvr4/Zvv8puf4W0pX5mzOsXIh/Aq444Q6VsN3fDPtfGEXGG8uU=; SPC_R_T_IV=VGV4WTZYVGpGZjExck45Uw==; SPC_T_ID=avaFBeJxIwoBWo6TQ+PGs8vc5a1p9S6tPXP7FHZiswAiLuHetQW/jKUwqkepb/aIJLiIrpuKbt8nQz7PfN4jjkWBm4A82lQR…lHZzVPdbCtdAAAAAAAakpkYTBZNHg=; _QPWSDCXHZQA=b3ae2b8d-e8e0-4c9f-b2a6-507909af3754; AMP_TOKEN=%24NOT_FOUND; _ga_CB0044GVTM=GS1.1.1667734826.1.1.1667734841.45.0.0; _ga=GA1.1.667246894.1667734826; _gid=GA1.2.282935809.1667734827; shopee_webUnique_ccd=9vNMYZuejkEYw0v8dSz5rw%3D%3D%7CzMpGSyr7LAoNWl4X1dJU0JcYOu6pQm5B0yExOowNiNC8SaKfJYEGyA0mvQeWl0PKhfTx6RlQkGom1Dx9sbNUoJJncXduCbAtAA%3D%3D%7CbPVWCe9zukbM%2Bjzo%7C06%7C3; ds=d1413786fb46e25c4a065ec050fe6d72; _dc_gtm_UA-61918643-6=1; _fbp=fb.1.1667734828974.1080449365',
            'sz-token' => '9vNMYZuejkEYw0v8dSz5rw==|zMpGSyr7LAoNWl4X1dJU0JcYOu6pQm5B0yExOowNiNC8SaKfJYEGyA0mvQeWl0PKhfTx6RlQkGom1Dx9sbNUoJJncXduCbAtAA==|bPVWCe9zukbM+jzo|06|3',
            'X-API-SOURCE' => 'pc',
            'X-CSRFToken' => 'AKJ08gtFTn3xVVWpyYSlg5303Cu6BnNc',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-Shopee-Language' => 'en',
        );
        $result = $this->getRemoteJson($url, false, 'GET', $headers);

        if (!$result || !isset($result['items']))
            return false;
        $urls = array();

        foreach ($result['items'] as $item)
        {
            $urls[] = str_replace(' ', '-', $item['item_basic']['name']) . '-i.' . $item['shopid'] . '.' . $item['itemid'];
        }

        if ($urls)
            $this->_total_pages = ceil((int) $result['total_count'] / count($urls));

        return $urls;
    }

    private function getListingApiUri()
    {
        $url = 'https://' . $this->host . '/api/v4/search/search_items?by=relevancy&limit=50&newest=0&order=desc&page_type=search&version=2';
        $keyword = ExtractorHelper::getQueryVar('keyword', $this->getUrl());

        if (preg_match('/-cat\.([\d\.]+)/', $this->getUrl(), $matches))
        {
            $parts = explode('.', $matches[1]);
            $category_id = $parts[count($parts) - 1];
        } elseif (preg_match('/-col\.(\d+)/', $this->getUrl(), $matches))
        {
            $category_id = $matches[1];
            $url = \add_query_arg('page_type', 'collection', $url);
        } else
            $category_id = '';

        if (!$keyword && !$category_id)
            return false;

        if ($keyword)
            $url = \add_query_arg('keyword', $keyword, $url);

        if ($category_id)
            $url = \add_query_arg('match_id', $category_id, $url);

        if ($page = ExtractorHelper::getQueryVar('page', $this->getUrl()))
        {
            if ($page > 1)
                $url = \add_query_arg('newest', $page * 50, $url);
        }

        if ($r = ExtractorHelper::getQueryVar('ratingFilter', $this->getUrl()))
            $url = \add_query_arg('rating_filter', $r, $url);

        if ($r = ExtractorHelper::getQueryVar('maxPrice', $this->getUrl()))
            $url = \add_query_arg('price_max', $r, $url);

        if ($r = ExtractorHelper::getQueryVar('minPrice', $this->getUrl()))
            $url = \add_query_arg('price_min', $r, $url);

        if ($r = ExtractorHelper::getQueryVar('newItem', $this->getUrl()))
            $url = \add_query_arg('conditions', 'new', $url);

        if ($r = ExtractorHelper::getQueryVar('sortBy', $this->getUrl()))
            $url = \add_query_arg('by', $r, $url);

        return $url;
    }

    private function getSellerApiUri()
    {
        if (!preg_match('~https://shopee.[a-z\.]+/([a-z0-9_]+)~', $this->getUrl(), $matches))
            return false;

        $username = $matches[1];

        $url = 'https://' . $this->host . '/api/v4/shop/get_shop_detail?sort_sold_out=0&username=' . urlencode($username);
        $headers = array(
            'user-agent' => 'ia_archiver',
            'af-ac-enc-dat' => 'AAcyLjMuMi00AAABg35HjlkAAAiyAbwAAAAAAAAAALUc1tfqsng0gJiCuHZwd0JlD8AdTldYGnbx0FrpOn/U+bD3TRWp+AYZKGZSsvEry9hSL5GRY7OMsPKRljZ2EI7Zp6aWZH8fbOMOZnavsZ+p/QrQaqtmq8BhA7PhHzeAyiquU9hkcFte8DFnE/xXa/poc9fIWhrFWgjHqsE3yUelzb+Udp9qBEEutObbmKU84FOIQ/yEd143nqyiCjLFAJPHo4jd9+ZZxTyxE5ClQdGMEymzrV1//yMdhxPxoeTyzExPzw4UjPhml6U83c4YR74/vnZj5Zkz5m8DKGCpgzVjIXizc6JNkRJDb7xeqLb2kgn+wefxwroP6ZL6LO5bh9QSxuKGfY+ea07u73J+63h3aE7NqpQ/DGzzHBT4YJKKfMzKlBHDNTpkAhYqvdOyLCTGr41kHoh/NSopyL8Bj6IpFOd+7aI0epy24C73hSHOLnA8uSaZUbatRPTf42k24UXHBZsiBnQHOCOVbdxuZGpOqjALNv8O/le5lp1m2WXMHuslV3nxwroP6ZL6LO5bh9QSxuKGRte8QOJXkotQFHkoLNKnlWxFbi7y9oBN/AxnZQjY+fnpEQL8rtiAQ/g0mm9xduwy',
        );
        $result = $this->getRemoteJson($url, false, 'GET', $headers);

        if (!$result || !isset($result['data']['shopid']))
            return false;

        $url = 'https://' . $this->host . '/api/v4/shop/search_items?limit=30&offset=0&order=desc&shopid=' . urlencode($result['data']['shopid']) . '&sort_by=pop';

        $shop_categoryids = ExtractorHelper::getQueryVar('shopCollection', $this->getUrl());
        $url = \add_query_arg('shop_categoryids', $shop_categoryids, $url);

        $sort_by = ExtractorHelper::getQueryVar('sortBy', $this->getUrl());
        $url = \add_query_arg('sort_by', $sort_by, $url);

        $original_categoryid = ExtractorHelper::getQueryVar('originalCategoryId', $this->getUrl());
        $url = \add_query_arg('original_categoryid', $original_categoryid, $url);

        if ($page = ExtractorHelper::getQueryVar('page', $this->getUrl()))
        {
            if ($page > 1)
                $url = \add_query_arg('offset', $page * 30, $url);
        }
        return $url;
    }

    public function parsePagination()
    {
        if (!$this->_total_pages)
            return array();

        if ($this->_total_pages > 300)
            $this->_total_pages = 300;

        $urls = array();
        for ($i = 1; $i < $this->_total_pages; $i++)
        {
            $urls[] = \add_query_arg('page', $i, $this->getUrl());
        }

        return $urls;
    }

    public function parseTitle()
    {
        if (!$this->_product)
            return;

        if (isset($this->_product['name']))
            return $this->_product['name'];
    }

    public function parseDescription()
    {
        if (isset($this->_product['description']))
            return $this->_product['description'];
    }

    public function parsePrice()
    {
        if (isset($this->_product['price']))
            return $this->_product['price'] / 100000;
        if (isset($this->_product['price_min']))
            return $this->_product['price_min'] / 100000;
    }

    public function parseOldPrice()
    {
        if (isset($this->_product['price_before_discount']))
            return $this->_product['price_before_discount'] / 100000;
        if (isset($this->_product['price_min_before_discount']))
            return $this->_product['price_min_before_discount'] / 100000;
    }

    public function parseImage()
    {
        if (isset($this->_product['image']))
            return 'https://cf.' . $this->host . '/file/' . $this->_product['image'];
    }

    public function parseImages()
    {
        if (!isset($this->_product['images']))
            return;

        $images = array();
        foreach ($this->_product['images'] as $image)
        {
            $images[] = 'https://cf.' . $this->host . '/file/' . $image;
        }
        return $images;
    }

    public function parseManufacturer()
    {
        if (isset($this->_product['brand']))
            return $this->_product['brand'];
    }

    public function parseInStock()
    {
        if (isset($this->_product['status']) && !$this->_product['status'])
            return false;

        if (isset($this->_product['stock']) && !$this->_product['stock'])
            return false;
    }

    public function parseCategoryPath()
    {
        if (!isset($this->_product['categories']))
            return;

        $categories = array();
        foreach ($this->_product['categories'] as $c)
        {
            $categories[] = $c['display_name'];
        }
        return $categories;
    }

    public function parseFeatures()
    {
        if (!isset($this->_product['attributes']))
            return;

        $attributes = array();
        foreach ($this->_product['attributes'] as $a)
        {
            $attribute = array(
                'name' => $a['name'],
                'value' => $a['value'],
            );
            $attributes[] = $attribute;
        }
        return $attributes;
    }

    public function parseReviews()
    {
        if (!preg_match('~\-i\.(\d+\.\d+)~', $this->getUrl(), $matches))
            return false;

        $ids = $matches[1];
        $ids = explode('.', $ids);

        $url = 'https://' . $this->host . '/api/v2/item/get_ratings?filter=0&flag=1&itemid=' . urlencode($ids[1]) . '&limit=50&offset=0&shopid=' . urlencode($ids[0]) . '&type=0';
        $response = $this->getRemoteJson($url, false, 'GET', array('user-agent' => 'ia_archiver'));
        if (!$response || !isset($response['data']['ratings']))
            return array();

        $results = array();
        foreach ($response['data']['ratings'] as $r)
        {
            $review = array();
            if (!isset($r['comment']))
                continue;

            $review['review'] = $r['comment'];

            if (isset($r['rating_star']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['rating_star']);

            if (isset($r['author_username']))
                $review['author'] = $r['author_username'];

            if (isset($r['editable_date']))
                $review['date'] = $r['editable_date'];

            $results[] = $review;
        }
        return $results;
    }

    public function parseCurrencyCode()
    {
        switch ($this->host)
        {
            case 'shopee.vn':
                return 'VND';
            case 'shopee.co.id':
                return 'IDR';
            case 'shopee.com.my':
                return 'MYR';
            case 'shopee.co.th':
                return 'THB';
            case 'shopee.ph':
                return 'PHP';
            case 'shopee.sg':
                return 'SGD';
            case 'shopee.com.br':
                return 'BRL';
            case 'shopee.pl':
                return 'PLZ';
        }
    }

}
