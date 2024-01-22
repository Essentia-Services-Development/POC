<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * FlipkartcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class FlipkartcomAdvanced extends AdvancedParser
{

    public function parseLinks()
    {
        $path = array(
            ".//a[@target='_blank' and @rel='noopener noreferrer'][1]/@href",
        );

        $urls = $this->xpathArray($path);

        foreach ($urls as $i => $url)
        {
            $pid = '';
            if ($query = parse_url($url, PHP_URL_QUERY))
            {
                parse_str($query, $params);
                if (isset($params['pid']))
                    $pid = $params['pid'];
            }

            $urls[$i] = parse_url($url, PHP_URL_PATH);
            if ($pid)
                $urls[$i] .= '?pid=' . urlencode($pid);
        }
        return $urls;
    }

    public function parsePagination()
    {
        $path = array(
            ".//nav//a[contains(@href, '&page=')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class and text() = 'Description']/../div[2]", true);

        /*
          if (preg_match('/"value":{"type":"TitleValue","image":null,"style":null,"text":"(.+?)"}}\]},"header"/ims', $this->html, $matches))
          return $matches[1];
         * 
         */
    }

    public function parseShortDescription()
    {
        if ($d = $this->xpathScalar(".//div[@class and text() = 'Highlights']/..//ul", true))
            return '<ul>' . $d . '</ul>';
    }

    public function parsePrice()
    {
        if (preg_match('/"price":(\d+?),/', $this->html, $matches))
            return $matches[1];

        if (preg_match('/"currency":"INR","decimalValue":"(.+?)",/', $this->html, $matches))
            return $matches[1];
    }

    public function parseOldPrice()
    {
        if (preg_match('/"strikeOff":true,"value":(\d+)}/', $this->html, $matches))
            return $matches[1];
    }

    public function parseImage()
    {
        if ($img = $this->xpathScalar(".//img[contains(@src, '.jpeg?q=70')]/@src"))
            return $img;

        if ($style = $this->xpathScalar(".//ul[@style]/li[@style]/div/div/@style"))
        {
            if (preg_match('/\((.+?)\)/', $style, $matches))
                return str_replace('/128/128/', '/416/416/', $matches[1]);
        }

        if (preg_match('/,"imageUrl":"(http:\/\/rukmini1\.flixcart\.com\/image\/{@width}.+?)"/', $this->html, $matches))
        {
            $img = $matches[1];
            $img = str_replace('{@width}', 832, $img);
            $img = str_replace('{@height}', 832, $img);
            $img = str_replace('{@quality}', 70, $img);
            return str_replace('/128/128/', '/416/416/', $img);
        }
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//div/ul[contains(@style, 'transform')]//img/@src");
        foreach ($results as $img)
        {

            $img = str_replace('/128/128/', '/612/612/', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function parseInStock()
    {
        $stock = $this->xpathScalar(array(".//div[contains(@class, 'col-12-12') and @style='padding:24px 0px 0px 0px']/div[1]"));
        if ($stock == 'Coming Soon' || $stock == 'Sold Out' || $stock == 'Currently Unavailable' || $stock == 'Temporarily Discontinued')
            return false;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[normalize-space(text())='Specifications']/..//table//td[contains(@class, 'col-3-12') and position() = 1]",
                'value' => ".//div[normalize-space(text())='Specifications']/..//table//td[2]",
            ),
            array(
                'name' => ".//div[normalize-space(text())='Product Details']/..//..//div[@class='row']/div[1]",
                'value' => ".//div[normalize-space(text())='Product Details']/..//..//div[@class='row']/div[2]",
            ),
        );
    }

    public function parseReviews()
    {
        if (preg_match_all('/"reviewPropertyMap":{"CERTIFIED_BUYER":true},"reviewTypeDisplayText":null,"text":"(.+?)"/ims', $this->html, $matches))
            $reviews = $matches[1];
        else
            return array();

        if (preg_match_all('/,"author":"(.+?)",/ims', $this->html, $matches))
            $authors = $matches[1];
        else
            $authors = array();

        if (preg_match_all('/,"rating":(\d+),"/ims', $this->html, $matches))
            $ratings = $matches[1];
        else
            $ratings = array();

        $results = array();
        foreach ($reviews as $i => $r)
        {
            $review = array();
            $r = str_replace('\\n', '<br>', $r);
            $review['review'] = $r;
            if (isset($authors[$i]))
                $review['author'] = $authors[$i];
            if (isset($ratings[$i]))
                $review['rating'] = $ratings[$i];
            $results[] = $review;
        }
        return $results;
    }

    public function parseCurrencyCode()
    {
        return 'INR';
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('/416/416/', '/612/612/', $product->image);
        $product->image = str_replace('/128/128/', '/612/612/', $product->image);

        if (strstr($product->description, 'from Flipkart.com'))
            $product->description = '';

        return $product;
    }

}
