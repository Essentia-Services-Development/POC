<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * BookingParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class BookingParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    protected $user_agent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0';

    public function parseCatalog($max)
    {
        $xpath = array(
            ".//*[@id='hotellist_inner']//h3/a/@href",
            ".//div[@class='sr__card_photo']/a/@href",
            ".//a[@data-testid='title-link']/@href",
        );
        return $this->xpathArray($xpath);
    }

    public function parseTitle()
    {
        $title = parent::parseTitle();
        if (!$title)
        {
            $title = trim($this->xpathScalar(".//meta[@property='og:title']/@content"));
            $words = explode(',', $title);
            $title = trim($words[0]);
        }
        if (!$title)
            $this->xpathScalar("id('hp_hotel_name')");
        return $title;
    }

    public function parseDescription()
    {
        return html_entity_decode(parent::parseDescription());
    }

    public function parsePrice()
    {
        $html = $this->dom->saveHtml();
        $price = 0;
        if (preg_match_all('/"b_raw_price":"(\d.?)"/ims', $html, $matches))
            $price = $matches[1];

        if (!$price)
            $price = $this->xpathScalar(".//*[contains(@class, 'hprt-price-price-standard')]");

        if (!$price)
        {
            $price = html_entity_decode(parent::parsePrice());

            if (strlen($price) > 20)
            {
                if (preg_match('/[A-Z]{3}.+?([0-9\.\s\'\,]+)/', $price, $matches))
                    $price = trim($matches[1]);
                elseif (preg_match('/\d[0-9\.,\s]+/', $price, $matches))
                    $price = trim($matches[0]);
            }
        }

        $price = str_replace(' ', '', $price);

        return $price;
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@class='address address_clean']/span[2]");
    }

    public function parseImg()
    {
        if ($p = parent::parseImg())
            return $p;
        if ($img = $this->xpathScalar(".//meta[@property='og:image']/@content"))
        {
            $img = str_replace('/max300/', '/840x460/', $img);
            return $img;
        }
        $img = $this->xpathScalar("id('photo_wrapper')//img/@src");
        $img = str_replace('/max1024x768/', '/840x460/', $img);
        return $img;
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='hotelPoliciesInc']//*[@class='policy_name']");
        $values = $this->xpathArray(".//*[@id='hotelPoliciesInc']//p[2]");

        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!$name = \sanitize_text_field($names[$i]))
                continue;
            if (empty($values[$i]))
                continue;

            $feature['name'] = TextHelper::truncate($name, 100);
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }

        $extra['images'] = array();
        $images = $this->xpathArray(".//*[@id='photos_distinct']/a/@href");
        foreach ($images as $key => $img)
        {
            $img = str_replace('/max400/', '/840x460/', $img);
            $extra['images'][] = $img;
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@class='reviews-carousel-scroll']//p[contains(@class, 'altHotelsReviewer2')]/span");
        $comments = $this->xpathArray(".//*[@class='reviews-carousel-scroll']//p[contains(@class, 'althotelsReview2')]/span[1]");
        for ($i = 0; $i < count($comments); $i++)
        {
            if (!empty($comments[$i]))
            {
                if (isset($users[$i]))
                {
                    $name = explode(',', sanitize_text_field($users[$i]));
                    $comment['name'] = trim($name[0]);
                }
                $comment['comment'] = sanitize_text_field($comments[$i]);
                $extra['comments'][] = $comment;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

    public function getCurrency()
    {
        $currency = $this->xpathScalar(".//input[@name='selected_currency']/@value");

        if (!$currency)
        {
            if (preg_match("/b_selected_currency: '(\w+)'/ims", $this->dom->saveHtml(), $matches))
                $currency = $matches[1];
        }

        if (!$currency)
            $currency = 'USD';
        return $currency;
    }

}
