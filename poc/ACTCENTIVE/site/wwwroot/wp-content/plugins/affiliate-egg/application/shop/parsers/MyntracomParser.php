<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MyntracomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class MyntracomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';
    protected $json_data = array();
    protected $user_agent = array('ia_archiver');
    

    public function parseCatalog($max)
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
            $url = $product['landingPageUrl'];
            if (!preg_match('/^https?:/', $url))
                $urls[] = 'http://www.myntra.com/' . $url;
            if (count($urls) >= $max)
                return $urls;
        }
    }

    protected function parseJsContent()
    {
        $data = $this->xpathScalar(".//script[starts-with(normalize-space(text()),\"window.__myx =\")]");
        if (!$data)
            return false;

        $data = trim(str_replace('window.__myx =', '', $data));
        $data = trim(preg_replace('/\s+/', ' ', $data));
        $data = json_decode($data, true);
        if (!$data || !isset($data['pdpData']))
            return false;
        $this->json_data = $data['pdpData'];
        return true;
    }

    public function parseTitle()
    {
        if (!$this->parseJsContent() || !$this->json_data)
            return '';
        if (!empty($this->json_data['name']))
            return $this->json_data['name'];
    }

    public function parseDescription()
    {
        if (empty($this->json_data['descriptors']))
            return '';

        $description = '';
        foreach ($this->json_data['descriptors'] as $i => $descriptor)
        {
            if ($i > 0)
                $description .= "\r\n";
            $description .= $descriptor['description'];
        }
        return $description;
    }

    public function parsePrice()
    {
        if (!empty($this->json_data['price']['discounted']))
            return $this->json_data['price']['discounted'];
        elseif (!empty($this->json_data['sizes'][0]['sizeSellerData'][0]['discountedPrice']))
            return $this->json_data['sizes'][0]['sizeSellerData'][0]['discountedPrice'];
        elseif (!empty($this->json_data['mrp']))
            return $this->json_data['mrp'];
    }

    public function parseOldPrice()
    {
        if (!empty($this->json_data['price']['mrp']))
            return $this->json_data['price']['mrp'];
        elseif (!empty($this->json_data['sizes'][0]['sizeSellerData'][0]['mrp']))
            return $this->json_data['sizes'][0]['sizeSellerData'][0]['mrp'];
    }

    public function parseManufacturer()
    {
        if (!empty($this->json_data['brand']))
            return $this->json_data['brand']['name'];
    }

    public function parseImg()
    {
        if (empty($this->json_data['media']) || empty($this->json_data['media']['albums']))
            return '';

        $img = $this->json_data['media']['albums'][0]['images'][0]['secureSrc'];
        $img = str_replace('h_($height)', 'h_640', $img);
        $img = str_replace('q_($qualityPercentage)', 'q_100', $img);
        $img = str_replace('w_($width)', 'w_480', $img);
        return $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
