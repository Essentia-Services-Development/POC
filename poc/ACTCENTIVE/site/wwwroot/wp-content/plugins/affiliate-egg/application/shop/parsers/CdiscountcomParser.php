<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * CdiscountcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com> 
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class CdiscountcomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EUR';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'Cookie' => 'challenge=JmNaL4p84LHEfek6eFD6LdahZYzEHwUXJOnnw9EMa2oklVY3FP3IuFr3HljUDvRFqokjUxNeV21bJtgn8_9j-zKiwH2D5746DDILG02pXTgoAKFFj7ogIkqecVnbbyP9PqJZkaQCb6RfZy8Z_wxbl7RjihG9yd2BOvlzQqoDN5BXepF6lB6QQRYREAgVLbgDKB6GQFleujc1i5SY-BJOGg; VisitContextCookie=hWznjkPj8yTwTgXsZ5LjDpF-duoAWM32HlfmN9JNZh3J_-hrTnHKcg; mssctse=W2dNXeEyrPIvX7HFSL5dTzau94EAVMqy7Xxdg1QRiXGajH0s_qkpsQCipC2md9XoYZpb49MZLOs; _$culture=CultureName__fr-FR__; CookieId=CookieId=210623131112XSNXFUME&IsA=0; _$dtype=t:d; cache_cdn=; AMCV_6A63EE6A54FA13E60A…000%7C; s_nr=1624446673484-New; s_pv=F-HP15SFQ2004NF%3AHP%20PC%20Portable%2015s-fq2004nf%20-%2015%2C6%22%20HD%20-%20i3-1115G4%20-%20RAM%204Go%20-%20Stockage%20SSD%20128Go%20-%20Windows%2010%20S%20-%20AZERTY; s_cc=true; AMCVS_6A63EE6A54FA13E60A4C98A7%40AdobeOrg=1; _cs_c=3; _cs_cvars=%7B%7D; _cs_id=eacec16f-5ced-aefb-e679-9ae1cdf063a7.1624446673.1.1624446673.1624446673.1590586488.1658610673163.Lax.0; _cs_s=1.1; TBMCookie_6223335112164439712=610068001624446672/GCJzJXp0gAGsYqPc12/L+uCUeM=; ___utmvm=###########',
    );

    public function parseCatalog($max)
    {
        return $this->xpathArray(array(".//a[@class='jsQs']/@href", ".//div[@class='prdtBILDetails']/a/@href"));
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//p[@itemprop='description']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[contains(@class, 'fpPriceBloc')]//*[contains(@class, 'fpStriked')]/text()");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']//*[@itemprop='name']");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//*[@property='twitter:image']/@content");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra['features'] = array();
        $names = $this->xpathArray(".//table[@class='fpDescTb fpDescTbPub']//td[1]");
        $values = $this->xpathArray(".//table[@class='fpDescTb fpDescTbPub']//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!trim($names[$i]))
                continue;
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['comments'] = array();
        $comments = $this->xpathArray(".//*[@class='detMainRating']//*[@class='infoCli']/p");
        $users = $this->xpathArray(".//*[@class='detMainRating']//*[@itemprop='author']");
        for ($i = 0; $i < count($comments); $i++)
        {
            $c = \sanitize_text_field($comments[$i]);
            if (!$c)
                continue;
            $comment['comment'] = $c;
            if (!empty($users[$i]))
                $comment['name'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
            {
                $r_parts = explode('/', $ratings[$i]);
                $comment['rating'] = TextHelper::ratingPrepare($r_parts[1] / 2);
            }
            $extra['comments'][] = $comment;
        }

        $extra['rating'] = TextHelper::ratingPrepare(str_replace(',', '.', $this->xpathScalar(".//*[@itemprop='ratingValue']")));
        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//*[@itemprop='availability']/@href") == 'https://schema.org/OutOfStock')
            return false;
        else
            return true;
    }

}
