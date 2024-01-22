<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 *  PleerruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class PleerruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='pad_tb']//*[contains(@class, 'h3')]/a/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='price_disk']//s");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();

        $results = $this->xpathArray(".//div[@class='content_wrap']//ul/li");
        $feature = array();
        foreach ($results as $res)
        {
            $splited = explode(":", $res, 2);
            if (count($splited) == 2)
            {
                $feature['name'] = sanitize_text_field($splited[0]);
                $feature['value'] = sanitize_text_field($splited[1]);
                $extra['features'][] = $feature;
            }
        }
        $extra['images'] = array();

        $extra['comments'] = array();
        $comments = $this->xpathArray(".//div[@class='blockContent'][last()]/div/table/tr");
        $comments = array_chunk($comments, 3);

        for ($i = 0; $i < count($comments); $i++)
        {
            if ($i == 0)
                continue;
            if (!empty($comments[$i]))
            {
                $comment['name'] = str_replace(":", "", $comments[$i][0]);
                $comment['date'] = '';
                $comment['comment'] = sanitize_text_field($comments[$i][1]);
                $extra['comments'][] = $comment;
            }
        }
        return $extra;
    }

}
