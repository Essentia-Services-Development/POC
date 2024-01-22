<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PudraruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class PudraruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[contains(@class,'b-content')]//a[contains(@class,'js-product-link')]/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://pudra.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[contains(@class,'js-product-name')]");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='b-helper-description']");
    }

    public function parsePrice()
    {
        return (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//div[contains(@class,'js-price')]"));
    }

    public function parseOldPrice()
    {
        return '';
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//a[@class='b-card-brand']");
    }

    public function parseImg()
    {
        $res = $this->xpathScalar(".//img[contains(@class,'js-card-image-main')]/@src");
        if (!preg_match('/^http:\/\//', $res))
            $res = 'http://pudra.ru' . $res;
        return $res;
    }

    public function parseImgLarge()
    {
        $res = $this->xpathScalar(".//img[contains(@class,'js-card-image-main')]/@data-zoom-image");
        if (!preg_match('/^http:\/\//', $res))
            $res = 'http://pudra.ru' . $res;
        return $res;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//ul[contains(@class,'b-product_featuers-list')]//div[contains(@class,'b-product_featuers-title')]");
        $values = $this->xpathArray(".//ul[contains(@class,'b-product_featuers-list')]//div[contains(@class,'b-product_features-value')]");

        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($names[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();

        $extra['comments'] = array();
        $users = $this->xpathArray(".//div[contains(@class, 'b-review-comment')]//div[@class='b-review-comment-title']");
        $dates = $this->xpathArray(".//div[contains(@class, 'b-review-comment')]//span[@class='product-review-date']");
        $comments = $this->xpathArray(".//div[contains(@class, 'b-review-comment')]//div[@class='b-review-comment-description']");
        for ($i = 0; $i < count($comments); $i++)
        {
            if (!empty($comments[$i]))
            {

                $comment['name'] = (isset($users[$i])) ? trim($users[$i]) : '';
                $comment['date'] = '';
                if (isset($dates[$i]))
                {
                    $date = explode('.', $dates[$i]);
                    if (count($date) == 3)
                        $comment['date'] = strtotime(trim($date[1]) . '/' . trim($date[0]) . '/' . $date[2]);
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

}
