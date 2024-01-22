<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;

/**
 * DartycomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class DartycomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//*[@class='prd-family']/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='darty_product_list_pages_list']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        if ($ul = $this->xpathScalar(".//div[@class='strong-points']//ul", true))
            return '<ul>' . $ul . '</ul>';
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@class='product_price_infos']//*[@class='darty_prix_barre']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        return $this->xpathArray(".//div[contains(@class, 'darty_product_picture_main_pic_container')]//img/@data-src");
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//h2[@itemprop='brand']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//div[@id='header-breadcrumb-zone']//li/a/text()",
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
                'name' => ".//div[@id='product_caracteristics']//tr/th",
                'value' => ".//div[@id='product_caracteristics']//tr/td",
            ),
        );
    }

    public function parseReviews()
    {
        if (!$this->xpathScalar(".//span[@class='rating_avis']/u"))
            return array();

        $url = preg_replace('~/([0-9a-z_]+\.html)~', '/avis_1__$1', $this->getUrl());
        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        $r = array();
        $users = $xpath->xpathArray(".//div[contains(@class, 'reviews_list')]//b[contains(@class, 'bloc_reviews_author_name')]");
        $comments = $xpath->xpathArray(".//div[contains(@class, 'reviews_list')]//p[contains(@class, 'bloc_reviews_text')]");
        $ratings = $xpath->xpathArray(".//div[contains(@class, 'reviews_list')]//div[@class='bloc_reviews_note']/text()");
        $dates = $xpath->xpathArray(".//div[contains(@class, 'reviews_list')]//span[@class='review_date']");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['review'] = \sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['author'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = $ratings[$i];
            if (!empty($dates[$i]) && preg_match('~\d+/\d+/\d+~', $dates[$i], $matches))
                $comment['date'] = strtotime(str_replace('/', '.', $matches[0]));
            $r[] = $comment;
        }
        return $r;
    }

    public function parseCurrencyCode()
    {
        return 'EUR';
    }

}
