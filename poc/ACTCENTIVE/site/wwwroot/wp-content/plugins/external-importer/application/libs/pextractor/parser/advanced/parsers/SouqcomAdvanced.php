<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * SouqcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class SouqcomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[contains(@class, 'itemLink') and string-length(text()) > 1]/@href",
            ".//h6[@class='title']/a/@href",
            ".//*[@class='itemTitle']/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul[contains(@class, 'pagination')]//li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseTitle()
    {
        if ($this->xpathScalar(".//title") == 'Best Deals on Souq.com Egypt | White Friday Sale on Electronics, Apparel, Computers, Grocery & more')
            return 'Souq product';
    }

    public function parseDescription()
    {
        $names = $this->xpathArray(".//*[@id='specs-short']//dt");
        $values = $this->xpathArray(".//*[@id='specs-short']//dd");
        $desc = '';
        if ($names && count($names) == count($values))
        {

            $desc .= '<ul>';
            foreach ($names as $i => $name)
            {
                $desc .= '<li>' . $name . ': ' . $values[$i] . '</li>';
            }
            $desc .= '</ul>';
        }
        
        $desc .= '<br>' . $this->xpathScalar(".//div[@id='description-full']", true);

        return $desc;
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@class='price-messaging']//*[@class='was']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseCategory()
    {
        $paths = array(
            ".//div[contains(@class, 'product-header')]/div/div/span[1]/a[2]",
        );

        return $this->xpathScalar($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//*[@id='specs-full']//dt",
                'value' => ".//*[@id='specs-full']//dd",
            ),
        );
    }

    public function parseImages()
    {
        return $this->xpathArray(".//div[contains(@class, 'slider gallary')]//a/div//img/@data-lazy");
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('/item_XL_', '/item_XXL_', $product->image);
        $product->image = str_replace('/item_L_', '/item_XXL_', $product->image);
        return $product;
    }

    public function parseInStock()
    {
        if ($this->xpathScalar(".//title") == 'Best Deals on Souq.com Egypt | White Friday Sale on Electronics, Apparel, Computers, Grocery & more')
            return false;
    }

}
