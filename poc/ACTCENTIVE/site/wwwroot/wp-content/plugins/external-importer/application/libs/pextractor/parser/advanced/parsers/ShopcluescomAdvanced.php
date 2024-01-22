<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * ShopcluescomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ShopcluescomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//*[@id='product_list']//div[@class='row']//a[not(@class='whishlist_ic')]/@href",
            ".//*[@class='products_list']//h5/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@id='product_list']//div[@class='product_found']/span",
        );

        if (!$total = (int) $this->xpathScalar($path))
            return;

        $pages = ceil($total / 24);
        if ($pages > 100)
            $pages = 100;

        $pagination = array();
        for ($i = 2; $i <= $pages; $i++)
        {
            $pagination[] = \add_query_arg('page', $i, $this->getUrl());
        }
        return $pagination;
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@itemprop='name']/text()",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        return trim($this->xpathScalar(".//*[@id='product_description']", true));
    }

    public function parsePrice()
    {
        $paths = array(
            ".//*[@class='f_price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@id='sec_list_price_']",
            ".//*[@id='sec_discounted_price_']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        return $this->xpathArray(".//div[@class='prd_img_gallery']//a/@data-image");
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//div[@class='prd_mid_info']//span[@class='pID']/a",
        );

        return $this->xpathScalar($paths);
    }

    public function parseInStock()
    {
        if ($this->xpathScalar(".//div[@class='soldout_content']//p[@class='discontinued']") == 'Product Sold Out')
            return false;
        else
            return true;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//div[@class='breadcrums']//li/a",
        );

        return $this->xpathArray($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//*[@id='specification']//td[@width][1]",
                'value' => ".//*[@id='specification']//td[@width][2]",
            ),
        );
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//div[@class='rnr_lists']//div[@class='review_desc']/p",
                'rating' => ".//div[@class='rnr_lists']//div[@class='prd_ratings']/span[1]",
                'author' => ".//div[@class='rnr_lists']//div[@class='r_by']",
                'date' => ".//div[@class='rnr_lists']//div[@class='r_date']",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'INR';
    }

}
