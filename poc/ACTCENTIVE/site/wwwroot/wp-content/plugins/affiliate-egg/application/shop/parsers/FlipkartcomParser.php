<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * FlipkartcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com> 
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class FlipkartcomParser extends LdShopParser
{

    protected $charset = 'utf-8';
    protected $currency = 'INR';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[@target='_blank' and @rel='noopener noreferrer'][1]/@href");
    }

    public function parseTitle()
    {
        if ($p = parent::parseTitle())
            return $p;
        return $this->xpathScalar(array(".//h1/span[2]", ".//h1/span"));
    }

    public function parseDescription()
    {
        $d = $this->xpathScalar(".//div/span[normalize-space(text())='Description']/../..//div/p");
        if (!$d)
            $d = $this->xpathScalar(".//div[normalize-space(text())='Description']/../div/div");
        if (!$d)
            $d = $this->xpathScalar(".//div[normalize-space(text())='Product Description']/../div[2]");
        return $d;
    }

    public function parsePrice()
    {
        if ($price = parent::parsePrice())
            return $price;

        $html = $this->dom->saveHTML();
        if (preg_match('/"price":(\d+?),/', $html, $mathces))
            return $mathces[1];

        if (preg_match('/"currency":"INR","decimalValue":"(.+?)",/', $html, $mathces))
            return $mathces[1];
    }

    public function parseOldPrice()
    {
        $html = $this->dom->saveHTML();
        if (preg_match('/"strikeOff":true,"value":(\d+?)}/', $html, $mathces))
            return $mathces[1];
    }

    public function parseImg()
    {
        if ($img = $this->xpathScalar(".//img[contains(@src, '.jpeg?q=70')]/@src"))
        {
            $img = str_replace('/128/128/', '/612/612/', $img);
            return $img;
        }

        if ($style = $this->xpathScalar(".//ul[@style]/li[@style]/div/div/@style"))
        {
            if (preg_match('/\((.+?)\)/', $style, $matches))
                return str_replace('/128/128/', '/416/416/', $matches[1]);
        }

        if (preg_match('/,"imageUrl":"(.+?)",/', $this->html, $matches))
        {
            $img = $matches[1];
            $img = str_replace('{@width}', 500, $img);
            $img = str_replace('{@height}', 500, $img);
            $img = str_replace('{@quality}', 70, $img);
            return $img;
        }

        $img = parent::parseImg();
        $img = str_replace('/416/416/', '/612/612/', $img);
        $img = str_replace('/128/128/', '/612/612/', $img);
        return $img;
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $names = $this->xpathArray(".//div[normalize-space(text())='Specifications']/..//table//td[contains(@class, 'col-3-12') and position() = 1]");
        $values = $this->xpathArray(".//div[normalize-space(text())='Specifications']/..//table//td[2]");
        $feature = $extra['features'] = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        if (!$extra['features'])
        {
            $names = $this->xpathArray(".//div[normalize-space(text())='Product Details']/..//..//div[@class='row']/div[1]");
            $values = $this->xpathArray(".//div[normalize-space(text())='Product Details']/..//..//div[@class='row']/div[2]");
            $feature = $extra['features'] = array();
            for ($i = 0; $i < count($names); $i++)
            {
                if (!empty($values[$i]))
                {
                    $feature['name'] = sanitize_text_field($names[$i]);
                    $feature['value'] = sanitize_text_field($values[$i]);
                    $extra['features'][] = $feature;
                }
            }
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[contains(@id, 'productRating')]"));
        $extra['ratingDecimal'] = (float) $this->xpathScalar(".//*[contains(@id, 'productRating')]");
        $extra['ratingCount'] = 0;
        $extra['reviewUrl'] = '';
        if ($extra['rating'])
        {
            $html = $this->dom->saveHTML();
            if (preg_match('/<span>.+?([0-9,]+)\sReviews<\/span>/', $html, $matches))
                $extra['ratingCount'] = (int) str_replace(',', '', $matches[1]);

            $extra['reviewUrl'] = str_replace('/p/', '/product-reviews/', $this->getUrl());

            if (preg_match('/<span>([0-9,]+)\sRatings.+?<\/span>/', $html, $matches))
                $extra['ratingCount2'] = (int) str_replace(',', '', $matches[1]);
        }

        return $extra;
    }

    public function isInStock()
    {
        if (!parent::isInStock())
            return false;

        $stock = $this->xpathScalar(array(".//div[contains(@class, 'col-12-12') and @style='padding:24px 0px 0px 0px']/div[1]"));
        if ($stock == 'Coming Soon' || $stock == 'Sold Out' || $stock == 'Currently Unavailable' || $stock == 'Temporarily Discontinued')
            return false;

        return true;
    }
}
