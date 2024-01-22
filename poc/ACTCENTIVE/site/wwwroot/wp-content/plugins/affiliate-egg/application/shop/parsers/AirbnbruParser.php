<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AirbnbruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AirbnbruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseDescription()
    {
        return trim($this->xpathScalar(".//*[@id='summary']/../div[3]"));
    }

    public function parsePrice()
    {
        
    }

    public function parseOldPrice()
    {
        
    }

    public function parseManufacturer()
    {
        
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();

        /*
          $extra['features'] = array();
          $names = $this->xpathArray(".//*[@id='details']//div[descendant::strong]/span[1]");
          $values = $this->xpathArray(".//*[@id='details']//div[descendant::strong]/strong");

          $feature = array();
          for ($i = 0; $i < count($names); $i++)
          {

          if (!$name = \sanitize_text_field($names[$i]))
          continue;
          if (empty($values[$i]))
          continue;

          $feature['name'] = TextHelper::truncate($name, 200);
          $feature['value'] = \sanitize_text_field($values[$i]);
          $extra['features'][] = $feature;
          }

          $extra['images'] = array();
          $images = $this->xpathArray(".//*[@id='photo-gallery']//a/img/@src");
          foreach ($images as $key => $img)
          {
          $extra['images'][] = $img;
          }
         * 
         */

        // js?...
        /*
          $extra['comments'] = array();
          $users = $this->xpathArray(".//*[@id='reviews']//div[@class='name']/a");
          $comments = $this->xpathArray(".//*[@id='reviews']//p");
          for ($i = 0; $i < count($comments); $i++)
          {
          if (!empty($comments[$i]))
          {
          $name = explode(',', sanitize_text_field($users[$i]));
          $comment['name'] = trim($name[0]);
          $comment['comment'] = sanitize_text_field($comments[$i]);
          $extra['comments'][] = $comment;
          }
          }
         * 
         */
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
