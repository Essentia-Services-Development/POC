<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * LdlccomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com> 
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class LdlccomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EUR';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='productListingWrapper']//a[@class='nom']/@href"), 0, $max);
        return $urls;
    }

    public function parseOldPrice()
    {
        
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@class='productcomments']//*[@class='infos']/span[@class='bold']");
        $comments = $this->xpathArray(".//*[@class='productcomments']//*[@class='txtcomment']");
        $ratings = $this->xpathArray(".//*[@class='productcomments']//img[@class='pNote']/@title");
        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = \sanitize_text_field($comments[$i]);
            $comment['comment'] = str_replace('Lire la suite ›', '', $comment['comment']);
            if (!empty($users[$i]))
                $comment['name'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
            {
                $r_parts = explode('/', $ratings[$i]);
                $comment['rating'] = TextHelper::ratingPrepare($r_parts[1] / 2);
            }
            $extra['comments'][] = $comment;
        }
        
        $extra['features'] = $this->_parseFeatures();
        
        return $extra;
    }
    
    public function _parseFeatures()
    {
        if (!$trs = $this->xpathArray(".//table[@id='product-parameters']//tr", true))
            return array();

        $features = array();
        $values = array();
        $i = 0;
        foreach ($trs as $tr)
        {
            $this->loadXPathStr($tr);

            $value = \sanitize_text_field($this->xpathScalar("//td[@class='checkbox' or @class='no-checkbox']"));
            if ($name = \sanitize_text_field($this->xpathScalar(".//td[@class='label']/h3")))
            {
                $i++;
                $feature['name'] = $name;
                $feature['value'] = $value;
                $features[$i] = $feature;
            } else
                $features[$i]['value'] .= ' | ' . $value;
                
        }

        return $features;
    }    

}
