<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * EbayAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class EbayAdvanced extends AdvancedParser
{

    public function parseLinks()
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
            $urls[$i] = strtok($url, '?');
        }

        return $urls;
    }

    public function parsePagination()
    {
        $path = array(
            ".//ol[@class='ebayui-pagination__ol']//li/a/@href",
        );

        return $this->xpathArray($path);
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
        $paths = array(
            ".//div[@class='prodDetailSec']",
        );

        if ($d = $this->xpathScalar($paths, true))
            return $d;

        if ($iframe = $this->xpathScalar(".//iframe[@id='desc_ifr']/@src"))
        {
            if ($html = $this->getRemote($iframe))
            {
                $html = preg_replace('#<script(.*?)>(.*?)</script>#ims', '', $html);
                $html = preg_replace('#<title(.*?)>(.*?)</title>#ims', '', $html);
                return $html;
            }
        }
    }

    public function parsePrice()
    {
        $paths = array(
            ".//*[@itemprop='price']",
            ".//*[@itemprop='price']/@content",
            ".//span[@itemprop='price']",
            ".//span[@id='mm-saleDscPrc']",
            ".//span[contains(@class, 'item-price')]//div[@class='display-price']",
        );

        $price = $this->xpathScalar($paths);

        return str_replace('/Stk.', '', $price);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//span[@class='vi-originalPrice']",
            ".//span[@id='mm-saleOrgPrc']",
            ".//*[@id='orgPrc']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
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

    public function parseImages()
    {
        $paths = array(
            ".//div[contains(@class, 'pic-vert-msk')]//img/@src",
            ".//div[@id='vi_main_img_fs_slider']//img/@src",
            ".//div[@class='vim ux-thumb-image-carousel']//img/@src",
        );

        $images = array();
        $results = $this->xpathArray($paths);
        foreach ($results as $img)
        {
            if (strstr($img, '96x96.gif'))
                continue;
            $img = str_replace('/s-l64.jpg', '/s-l500.jpg', $img);
            $img = str_replace('/s-l64.png', '/s-l500.png', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//h2[@itemprop='brand']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseInStock()
    {

        if ($this->xpathScalar(".//span[@class='msgTextAlign' or @class='ux-textspans ux-textspans--BOLD'][contains(.,'This listing has ended')]"))
            return false;

        if ($this->xpathScalar(".//span[@class='msgTextAlign' or @class='ux-textspans ux-textspans--BOLD'][contains(.,'This Buy It Now listing has ended')]"))
            return false;

        if ($this->xpathScalar(".//span[@class='msgTextAlign' or @class='ux-textspans ux-textspans--BOLD'][contains(.,'This item is out of stock')]"))
            return false;

        if ($this->xpathScalar(".//span[@class='msgTextAlign' or @class='ux-textspans ux-textspans--BOLD'][contains(.,'Bidding has ended on this item')]"))
            return false;

        if ($this->xpathScalar(".//div[@class='product-buy-container product-buy-container-new-ui'][contains(.,'MOMENTAN AUSVERKAUFT')]"))
            return false;

        if ($this->xpathScalar(".//span[@class='msgTextAlign' or @class='ux-textspans ux-textspans--BOLD'][contains(.,'This listing was ended by the seller')]"))
            return false;

        return true;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//*[@itemtype='https://schema.org/BreadcrumbList']//*[@itemprop='name']",
        );

        if ($categs = $this->xpathArray($paths))
        {
            array_pop($categs);
            return $categs;
        }
    }

    public function getFeaturesXpath()
    {
        return array(
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
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//*[@class='reviews']//*[@itemprop='reviewBody']",
                'rating' => ".//*[@class='reviews']//*[@class='ebay-star-rating']/@aria-label",
                'author' => ".//*[@class='reviews']//*[@itemprop='author']",
                'date' => ".//*[@class='reviews']//*[@itemprop='datePublished']",
            ),
        );
    }

    /*
      public function parseReviews()
      {
      if ($results = parent::parseReviews())
      return $results;
      }
     * 
     */

    public function parseCurrencyCode()
    {
        if (preg_match('~"currency":"([A-Z]{3})"~', $this->html, $matches))
            return $matches[1];

        /*
        $paths = array(
            ".//*[@itemprop='priceCurrency']/@content",
        );

        if ($c = $this->xpathScalar($paths))
            return $c;
        */

        switch ($this->host)
        {
            case 'ebay.com':
                return 'USD';
            case 'ebay.ca':
                return 'CAD';
            case 'ebay.in':
                return 'INR';
            case 'ebay.com.au':
                return 'AUD';
            case 'ebay.it':
            case 'ebay.de':
            case 'ebay.fr':
            case 'ebay.es':
                return 'EUR';
            case 'ebay.co.uk':
                return 'GBP';
        }
        return $this->xpathScalar(".//*[@itemprop='priceCurrency']/@content");
    }
}
