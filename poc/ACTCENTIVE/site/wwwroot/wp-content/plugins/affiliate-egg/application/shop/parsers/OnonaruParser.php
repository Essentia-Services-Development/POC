<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * OnonaruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class OnonaruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function restPostGet($url, $fix_encoding = true)
    {
        $html = parent::restPostGet($url, $fix_encoding = true);
        $html = preg_replace('#<script(.*?)>(.*?)</script>#ims', '', $html);
        return $html;
    }

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[@class='item-list']//div[@class='content']//div[@class='title']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https:\/\//', $url))
                $urls[$i] = 'https://onona.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1[@itemprop='name']"));
    }

    public function parseDescription()
    {
        return join("\r\n", $this->xpathArray(".//*[@id='blockDescriptionContentProduct']//p"));
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//span[@itemprop='price']");
    }

    public function parseOldPrice()
    {
        return '';
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@class='item-char']//dl/dd/a/u");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//*[@class='img-holder']/a/@href");
        if (!preg_match('/^https/', $img))
            return 'https://onona.ru' . $img;
        else
            return $img;
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@class='item-char']//dl/dt");
        $values = $this->xpathArray(".//div[@class='item-char']//dl/dd");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!$name = \sanitize_text_field($names[$i]))
                continue;
            if (empty($values[$i]))
                continue;

            $feature['name'] = $name;
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']/@content"));

        return $extra;
    }

    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;
    }

}
