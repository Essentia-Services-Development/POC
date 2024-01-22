<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\libs\pextractor\parser\ListingProcessor;

/**
 * MagicParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class MagicParser extends AbstractParser {

    const FORMAT = ParserFormat::MAGIC_PARSER;

    public function parseLinks()
    {
        $links1 = $this->parseLinksMethod1();
        $links2 = $this->parseLinksMethod2();

        if (count($links1) > 100 && $links2 && count($links2) < 50)
            $links = $links2;
        elseif (count($links1) > $links2)
            $links = $links1;
        elseif ($links2)
            $links = $links2;
        else
            $links = $links1;

        if (!$links)
            $links3 = $this->parseLinksMethod3();

        $links = self::filterLinks($links);
        return $links;
    }

    public function parsePagination()
    {
        return $this->parsePaginationMethod1();
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@class='name']",
            ".//h1[@class='product-name']",
            ".//h2[@class='product-name']",
            ".//h1[contains(@class, 'product-title')]",
            ".//div[contains(@class, 'ProductName')]/h1",
            ".//div[contains(@class, 'product-name')]/h1",
            ".//div[contains(@class, 'product-name')]/h2",
            ".//div[@class='name']/*[contains(@class, 'productName')]",
            ".//h1[contains(@class, 'productname')]",
            ".//h1[contains(@class, 'cardTitle')]",
            ".//h2[contains(@class, 'cardTitle')]",
            ".//h1[@class='page-title']",
            ".//h2[@class='page-title']",
            ".//*[@class='product-name']",
            ".//div[@id='product-detail']//h1",
            ".//h1[@id='pagetitle']",
        );
        return $this->xpathScalar($paths);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//meta[@property='product:price:amount']/@content",
            ".//div[contains(@class, 'price-box')]//span[@class='price']",
            ".//div[contains(@class, 'product_pric')]//span[@class='price']",
            ".//*[@data-price]/@data-price",
            ".//strong[@class='skuBestPrice']",
            ".//div[@class='product-price']",
            ".//*[@data-test='product-price']",
            ".//*[@id='our_price_display']",
            ".//*[@class='ProductPriceValue']",
            ".//*[contains(@class, 'product-intro')]/*[@class='original']",
            ".//*[contains(@class, 'product-details')]//*[@class='price']",
            ".//*[contains(@class, 'product-details')]//*[contains(@class, 'price-item')]",
            ".//*[contains(@class, 'price-box')]//*[contains(@class, 'regular-price')]",
            ".//*[contains(@class, 'product-info')]//*[contains(@class, 'regular-price')]",
            ".//*[contains(@class, 'product-info')]//*[contains(@class, 'price')]",
            ".//*[@class='Brief-minPrice']",
            ".//*[@class='woocommerce-Price-amount amount']",
            ".//*[@class='product-card-price__current']",
            ".//*[contains(@class, 'product-price')]",
        );

        return $this->xpathScalar($paths);
    }
    
    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='price-box']//*[@class='old-price']//span[@class='price']",
        );
        return $this->xpathScalar($paths);
    }    

    public function parseImage()
    {
        $paths = array(
            ".//*[contains(@class, 'product-image')]//img/@src",
            ".//div[contains(@id, 'gallery')]//img/@src",
            ".//div[contains(@id, 'gallery')]//img/@data-src",
            ".//div[contains(@id, 'productimages')]//img/@src",
            ".//img[@id='image']/@src",
            ".//img[@class='mainimage']/@src",
            ".//img[@id='image-main']/@src",
            ".//*[contains(@class, 'product-image')]//img/@src",
            ".//*[contains(@id, 'slideproduct')]//img/@src",
            ".//*[contains(@class, 'product-main-image')]//img/@src",
            ".//*[contains(@class, 'main_photo')]//img/@src",
        );
        return $this->xpathScalar($paths);
    }

    public function parseLinksMethod1()
    {
        $path = array(
            ".//h2[@class='product-name']/a/@href",
            ".//h3[@class='product-name']/a/@href",
            ".//a[@class='product-image']/@href",
            ".//*[@class='product-image']/a/@href",
            ".//a[@class='product-item-link']/@href",
            ".//*[@class='product_name']/a/@href",
            ".//a[@class='product-name']/@href",
            ".//*[@class='product-name']/a/@href",
            ".//*[@class='product-info']//a/@href",
            ".//*[@class='product_name']/a/@href",
            ".//*[@class='products-grid']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseLinksMethod2()
    {
        $img_links = $this->xpathArray(".//img/ancestor::a/@href");
        $img_links = ListingProcessor::prepareLinks($img_links, $this->base_uri);

        $txt_links = $this->xpathArray(".//a[descendant-or-self::*[string-length(normalize-space(text()))>10 and contains(normalize-space(text()), ' ')]/text()]/@href");
        $txt_links = ListingProcessor::prepareLinks($txt_links, $this->base_uri);

        return array_values(array_intersect($img_links, $txt_links));
    }

    public function parseLinksMethod3()
    {
        $path = array(
            ".//a[contains(@class, 'woocommerce-loop-product__link')]/@href",
            ".//a[contains(@class, 'productLink')]/@href",
            ".//a[contains(@class, 'product-link')]/@href",
            ".//*[starts-with(@class, 'product-')]/a/@href",
            ".//a[starts-with(@class, 'product-')]/@href",
            ".//a[starts-with(@class, 'product ')]/@href",
            ".//a[starts-with(@class, 'product')]/@href",
            ".//a[contains(@class, '-product')]/@href",
            ".//*[@itemprop='name']/a/@href",
            ".//*[contains(@class, 'product-name')]//a/@href",
            ".//*[contains(@class, 'list-product')]//a/@href",
            ".//*[contains(@class, '-product')]/a/@href",
            ".//*[starts-with(@class, 'product')]/a/@href",
        );

        $txt_links = $this->xpathArray($path);
        $txt_links = ListingProcessor::prepareLinks($txt_links, $this->base_uri);
        if (!$txt_links || count($txt_links) < 3)
            return array();

        $img_links = $this->xpathArray(".//img/ancestor::a/@href");
        $img_links = ListingProcessor::prepareLinks($img_links, $this->base_uri);

        if ($intersect = array_values(array_intersect($img_links, $txt_links)))
            return $intersect;
        else
            return $txt_links;
    }

    public static function filterLinks(array $links)
    {
        $slash_count = array();
        foreach ($links as $link)
        {
            $count = substr_count($link, '/');
            if (!isset($slash_count[$count]))
                $slash_count[$count] = 0;
            $slash_count[$count]++;
        }
        arsort($slash_count);
        $typical_slash_count = key($slash_count);

        foreach ($links as $i => $link)
        {
            if (substr_count($link, '/') != $typical_slash_count)
                unset($links[$i]);
        }

        return array_values($links);
    }

    public function parsePaginationMethod1()
    {
        $path = array(
            ".//ul[contains(@class, 'pagination')]//a/@href",
            ".//ul[contains(@class, 'paging')]//a/@href",
            ".//ul[@class='pages']//a/@href",
            ".//ul[@class='page-numbers']//a/@href",
            ".//ul[@class='pages-items']//a/@href",
            ".//*[@class='paging-list']//a/@href",
            ".//*[contains(@class, 'pagination')]//a/@href",
            ".//*[contains(@id, 'pagination')]//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseCurrencyCode()
    {
        $paths = array(
            ".//meta[@property='product:price:currency']/@content",
        );
        return $this->xpathScalar($paths);
    }

}
