<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AkusherstvoruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AkusherstvoruParser extends MicrodataShopParser {

    protected $charset = 'windows-1251';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@id='catalog']//p[@class='itemName']//a/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='itemPriceDopValue']//span[@class='price']");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='itemCharacteristics']//*[@class='specification-name']");
        $values = $this->xpathArray(".//*[@id='itemCharacteristics']//*[@class='specification-value']");

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


        $extra['images'] = array();
        $images = $this->xpathArray(".//*[@id='multimedia-carousel']//img/@src");
        foreach ($images as $key => $img)
        {
            $extra['images'][] = 'http://www.akusherstvo.ru' . $img;
        }


        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@id='itemReviews']//td[2]/a");
        $comments = $this->xpathArray(".//*[@id='itemReviews']//td[2]/span");
        for ($i = 0; $i < count($comments); $i++)
        {
            if (!empty($comments[$i]))
            {
                $comment['name'] = sanitize_text_field($users[$i]);
                $comment['comment'] = sanitize_text_field($comments[$i]);
                $extra['comments'][] = $comment;
            }
        }

        return $extra;
    }

}
