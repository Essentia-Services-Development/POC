<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * MyntracomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class MyntracomAdvanced extends AdvancedParser {

    protected $_total_pages;
    protected $_product = null;

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();

        $httpOptions['user-agent'] = 'ia_archiver';

        // reset session cookies
        $httpOptions['cookies'] = array();
        return $httpOptions;
    }

    protected function preParseProduct()
    {
        $this->_getProduct();
        return parent::preParseProduct();
    }

    private function _getProduct()
    {
        $data = $this->xpathScalar(".//script[starts-with(normalize-space(text()),\"window.__myx =\")]");
        if (!$data)
            return false;

        $data = trim(str_replace('window.__myx =', '', $data));
        $data = trim(preg_replace('/\s+/', ' ', $data));
        $data = json_decode($data, true);
        if (!$data || !isset($data['pdpData']))
            return false;

        $this->_product = $data['pdpData'];
    }

    public function parseLinks()
    {
        $data = $this->xpathScalar(".//script[starts-with(normalize-space(text()),\"window.__myx =\")]");
        if (!$data)
            return array();

        $data = trim(str_replace('window.__myx =', '', $data));
        $data = trim(preg_replace('/\s+/', ' ', $data));
        $data = json_decode($data, true);

        if (!$data || !isset($data['searchData']['results']['products']))
            return array();

        $urls = array();
        foreach ($data['searchData']['results']['products'] as $product)
        {
            $urls[] = $product['landingPageUrl'];
        }

        if ($urls)
            $this->_total_pages = ceil((int) $data['searchData']['results']['totalCount'] / count($urls));

        return $urls;
    }

    public function parsePagination()
    {
        if (!$this->_total_pages)
            return array();

        if ($this->_total_pages > 100)
            $this->_total_pages = 100;

        $urls = array();
        for ($i = 1; $i < $this->_total_pages; $i++)
        {
            $urls[] = \add_query_arg('p', $i, $this->getUrl());
        }

        return $urls;
    }

    public function parseDescription()
    {
        if (empty($this->_product['descriptors']))
            return '';

        $description = '';
        foreach ($this->_product['descriptors'] as $i => $descriptor)
        {
            if ($i > 0)
                $description .= "<br \><br \>\r\n";
            $description .= $descriptor['description'];
        }
        return $description;
    }

    public function parseOldPrice()
    {
        if (!empty($this->_product['price']['mrp']))
            return $this->_product['price']['mrp'];
        elseif (!empty($this->_product['sizes'][0]['sizeSellerData'][0]['mrp']))
            return $this->_product['sizes'][0]['sizeSellerData'][0]['mrp'];
    }

    public function parseManufacturer()
    {
        if (!empty($this->_product['brand']))
            return $this->_product['brand']['name'];
    }

    public function parseImages()
    {
        if (!isset($this->_product['media']['albums'][0]['images']))
            return array();

        $images = array();
        foreach ($this->_product['media']['albums'][0]['images'] as $img)
        {
            if (isset($img['secureSrc']))
                $images[] = $img['secureSrc'];
        }

        return $images;
    }

    public function parseFeatures()
    {
        if (!$this->_product || !isset($this->_product['articleAttributes']))
            return;

        $features = array();
        foreach ($this->_product['articleAttributes'] as $name => $value)
        {
            $feature['name'] = \sanitize_text_field($name);
            $feature['value'] = \sanitize_text_field($value);
            $features[] = $feature;
        }

        return $features;
    }

    public function parseReviews()
    {
        if (!$this->_product || !isset($this->_product['ratings']['reviewInfo']['topReviews']))
            return array();

        $results = array();
        foreach ($this->_product['ratings']['reviewInfo']['topReviews'] as $r)
        {
            $review = array();
            if (!isset($r['reviewText']))
                continue;

            $review['review'] = $r['reviewText'];

            if (isset($r['userRating']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['userRating']);

            if (isset($r['userName']))
                $review['author'] = $r['userName'];

            if (isset($r['timestamp']))
                $review['date'] = (int) $r['timestamp'];

            $results[] = $review;
        }
        return $results;
    }

    public function parseCurrencyCode()
    {
        return 'INR';
    }

    public function afterParseFix(Product $product)
    {
        $product->image = $this->_fixImage($product->image);
        $product->images = array_map(array($this, '_fixImage'), $product->images);
        array_pop($product->categoryPath);
        return $product;
    }

    public function _fixImage($img)
    {
        $img = str_replace('h_($height)', 'h_640', $img);
        $img = str_replace('q_($qualityPercentage)', 'q_100', $img);
        $img = str_replace('w_($width)', 'w_480', $img);
        $img = preg_replace('~/h_\d+,q_\d+,w_\d+/~', '/h_640,q_100,w_480/', $img);
        return $img;
    }

}
