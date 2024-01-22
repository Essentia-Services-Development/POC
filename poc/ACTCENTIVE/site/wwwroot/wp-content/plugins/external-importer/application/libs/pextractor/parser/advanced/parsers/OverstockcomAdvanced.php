<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;
use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * OverstockcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class OverstockcomAdvanced extends AdvancedParser {

    private $_pagination = array();

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();

        // reset session cookies
        $httpOptions['cookies'] = array();
        $httpOptions['headers'] = array(
            'Accept' => '', //!!!
            'Accept-Language' => 'en-us,en;q=0.5',
            'Cache-Control' => 'no-cache',
        );
        $httpOptions['user-agent'] = 'ia_archiver';

        return $httpOptions;
    }

    public function parseLinks()
    {
        if (!strstr($this->getUrl(), 'cat.html'))
            return array();

        $url = \add_query_arg(array('format' => 'fusion', 'client_id' => 'sn_pag'), $this->getUrl());

        if ($p = ExtractorHelper::getQueryVar('page', $url))
            $url = \add_query_arg('page', $p, $url);

        $response = $this->getRemoteJson($url, false);
        if (!$response || !isset($response['products']))
            return array();

        $urls = array();
        foreach ($response['products'] as $r)
        {
            if (isset($r['urls']['productPage']))
                $urls[] = strtok($r['urls']['productPage'], '?');
        }

        // pagination
        if (isset($response['searchRequest']) && isset($response['searchHeader']))
        {
            $pages = ceil($response['searchHeader']['numberOfResults'] / $response['searchRequest']['pageRows']);
            for ($i = 1; $i < $pages; $i++)
            {
                $this->_pagination[] = \add_query_arg('page', $i + 1, $this->getUrl());
            }
        }

        return $urls;
    }

    public function parsePagination()
    {
        return $this->_pagination;
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[contains(@class, 'price-comparison')]//span[@data-cy='product-was-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//div[@id='image-gallery']//img/@src");
        foreach ($results as $img)
        {
            $img = str_replace('_80.jpg', '_600.jpg', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//h2[text()='Specifications']//../../section/div/div[1]",
                'value' => ".//h2[text()='Specifications']//../../section/div/div[2]",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'USD';
    }

    public function afterParseFix(Product $product)
    {
        if (!preg_match('/_\d+\.jpg$/', $product->image))
            $product->image = str_replace('.jpg', '_600.jpg', $product->image);

        return $product;
    }

}
