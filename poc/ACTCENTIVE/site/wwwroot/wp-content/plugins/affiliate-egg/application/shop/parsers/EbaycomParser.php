<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EbaycomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com/
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class EbaycomParser extends ShopParser
{

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        $path = array(
            ".//ul[@id='ListViewInner']//h3/a/@href",
            ".//*[contains(@class, 'grid-gutter')]//h3/a/@href",
            ".//h3/a[@itemprop='url']/@href",
            ".//h3[@class='lvtitle']/a/@href",
            ".//a[@class='s-item__link']/@href",
            ".//h3/a/@href",
            ".//div[@class='dne-itemtile-detail']/a/@href",
            ".//div[contains(@class, 'ebayui-dne-item-featured-card')]//div[@class='dne-itemtile-detail']/a/@href",
        );

        $urls = $this->xpathArray($path);

        foreach ($urls as $i => $url)
        {
            if (strstr($url, 'itm/123456'))
                unset($urls[$i]);
            $urls[$i] = strtok($url, '?');
        }
        return array_values($urls);
    }

    public function parseTitle()
    {
        $xpaths = array(
            ".//*[@class='x-item-title__mainTitle']",
            ".//h1[@itemprop='name']/text()",
        );

        return $this->xpathScalar($xpaths);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='prodDetailSec']");
    }

    public function parsePrice()
    {
        $paths = array(
            ".//*[@itemprop='price']/@content",
            ".//span[@itemprop='price']",
            ".//span[@id='mm-saleDscPrc']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//span[@id='mm-saleOrgPrc']",
            ".//*[@id='orgPrc']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//h2[@itemprop='brand']");
    }

    public function parseImg()
    {
        $paths = array(
            ".//*[@id='icImg']/@src",
            ".//div[@class='ux-image-carousel']//img/@src",
        );

        $img = $this->xpathScalar($paths);

        if (!$img)
        {
            $results = $this->xpathScalar(".//script[contains(.,'image.src=')]");
            if (preg_match("/image\.src=\s+?'(.+?)'/msi", $results, $match))
                $img = $match[1];
        }

        $img = str_replace('/s-l300.jpg', '/s-l500.jpg', $img);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        preg_match("/\/(\d{12})/msi", $this->getUrl(), $match);
        $extra['item_id'] = isset($match[1]) ? $match[1] : '';

        $extra['features'] = array();

        $fxpath = array(
            array(
                'name' => ".//div[@class='vim x-about-this-item']//div[@class='ux-labels-values__labels-content']//span[not(contains(text(),'Condition'))]",
                'value' => ".//div[@class='vim x-about-this-item']//div[@class='ux-labels-values__values']//div[not(@id) and not(@class)]/span[not(@id) and not(@class)]",
            ),
            array(
                'name' => ".//div[@class='itemAttr']//tr/td[@class='attrLabels']",
                'value' => ".//div[@class='itemAttr']//tr/td[position() mod 2 = 0]",
            ),
            array(
                'name' => ".//div[@class='vim x-about-this-item']//div[contains(@class, 'ux-layout-section--features')]//div[@class='ux-labels-values__labels-content']",
                'value' => ".//div[@class='vim x-about-this-item']//div[contains(@class, 'ux-layout-section--features')]//div[@class='ux-labels-values__values-content']",
            ),
        );

        foreach ($fxpath as $fx)
        {
            $names = $this->xpathArray($fx['name']);
            $values = $this->xpathArray($fx['value']);
            if (!$names || !$values)
                continue;
            $feature = array();
            for ($i = 0; $i < count($names); $i++)
            {
                if (!empty($values[$i]) && $names[$i] != 'Condition:' && $names[$i] != 'Brand:')
                {
                    $feature['name'] = str_replace(":", "", $names[$i]);
                    $feature['value'] = $values[$i];
                    $extra['features'][] = $feature;
                }
            }
        }



        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@id='vi_main_img_fs_slider']//img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $new_res = preg_replace('/\/\$_\d+\./', '/$_57.', $res);
                if ($new_res !== $res)
                {
                    $extra['images'][] = $new_res;
                }
            }
        }

        $extra['comments'] = array();
        $comments = $this->xpathArray(".//*[@class='reviews']//*[@itemprop='reviewBody']");
        $users = $this->xpathArray(".//*[@class='reviews']//*[@itemprop='author']");
        $dates = $this->xpathArray(".//*[@class='reviews']//*[@itemprop='datePublished']");
        $ratings = $this->xpathArray(".//*[@class='reviews']//*[@class='ebay-star-rating']/@aria-label");
        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = TextHelper::ratingPrepare((float) $ratings[$i]);
            if (!empty($dates[$i]))
            {
                $d = strtotime($dates[$i]);
                if (!$d || $d > time())
                    $d = time() - rand(120, 24 * 3600 * 7);
                else
                    $d += rand(120, 7200);

                $comment['date'] = $d;
            }
            $extra['comments'][] = $comment;
        }
        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']/@content"));
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//span[@class='msgTextAlign'][contains(.,'This listing has ended')])");
        if (!$res)
            $res = $this->xpath->evaluate("boolean(.//span[@class='msgTextAlign'][contains(.,'This Buy It Now listing has ended')])");
        if (!$res)
            $res = $this->xpath->evaluate("boolean(.//span[@class='msgTextAlign'][contains(.,'This item is out of stock')])");
        return ($res) ? false : true;
    }

    public function getCurrency()
    {
        if (preg_match('~"currency":"([A-Z]{3})"~', $this->dom->saveHtml(), $matches))
            return $matches[1];
        /*
        $currency = $this->xpathScalar(".//span[@itemprop='priceCurrency']/@content");
        */
        return $this->currency;
    }
}
