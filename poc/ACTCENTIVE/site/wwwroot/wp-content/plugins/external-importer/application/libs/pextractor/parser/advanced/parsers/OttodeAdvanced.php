<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * OttodeAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class OttodeAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[contains(@class, 'find_tile__productLink')]/@href",
            ".//a[contains(@class, 'find_tile__productImageLink')]/@href",
            ".//a[contains(@class, 'productLink')]/@href",
        );

        $urls = $this->xpathArray($path);

        $path = array(
            ".//li[contains(@class, 'find_tile')]/a/@href",
        );
        $urls = array_merge($urls, $this->xpathArray($path));

        return $urls;
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[contains(@class, 'san_paging__bottomWrapper')]//*[contains(@class, 'san_paging__btn')]",
        );

        $pages = $this->xpathArray($path);

        $urls = array();
        foreach ($pages as $p)
        {
            if (!is_numeric($p))
                continue;
            $n = ($p - 1) * 69;
            $urls[] = \add_query_arg('o', $n, $this->getUrl());
        }
        return $urls;
    }

    public function parseDescription()
    {
        if ($d = $this->xpathScalar(".//div[@class='pl_block pl_block--no-gap pdp_selling-points']", true))
            return $d;

        $paths = array(
            ".//section[@class='prd_section prd_section--flex']",
        );

        return $this->xpathScalar($paths, true);

        /*
          $res = '';
          $paths = array(
          ".//ul[@class='prd_unorderedList']",
          );

          if ($d = $this->xpathScalar($paths, true))
          $res = '<ul>' . $d . '</ul>';

          $paths = array(
          ".//div[@class='prd_moreBox__content js_prd_moreBox__content']",
          );

          if ($d = $this->xpathScalar($paths, true))
          $res .= $d;

          return $res;
         * 
         */
    }

    public function parsePrice()
    {
        $paths = array(
            ".//*[@id='normalPriceAmount']",
            ".//*[@id='reducedPriceAmount']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@id='oldPriceAmount']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        return $this->xpathArray(".//div[contains(@class, 'js_prd_zoomWrapper') and contains(@data-image-url, 'otto.de')]/@data-image-url");
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//table[@class='dv_characteristicsTable']//tr/td[1]",
                'value' => ".//table[@class='dv_characteristicsTable']//tr/td[2]",
            ),
            array(
                'name' => ".//div[@class='itemAttr']//tr/td[@class='attrLabels']",
                'value' => ".//div[@class='itemAttr']//tr/td[position() mod 2 = 0]",
            ),
        );
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//div[@class='cr_js_reviewList cr_reviewList']//p[@class='cr_review__text']",
                'rating' => ".//div[@class='cr_js_reviewList cr_reviewList']//div[@data-review-rating]/@data-review-rating",
                'date' => ".//div[@class='cr_js_reviewList cr_reviewList']//div[@data-review-creationDate]/@data-review-creationDate",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'EUR';
    }

}
