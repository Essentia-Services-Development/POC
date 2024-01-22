<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * YandexruAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class YandexruAdvanced extends AdvancedParser {

    public function parsePagination()
    {
        $path = array(
            ".//div[@data-auto='pagination-page']",
        );

        $pages = $this->xpathArray($path);

        $urls = array();
        foreach ($pages as $p)
        {
            if (is_numeric($p))
                $urls[] = \add_query_arg('page', (int) $p, $this->getUrl());
        }

        return $urls;
    }

    public function parseTitle()
    {
        $paths = array(
            ".//meta[@itemProp='name']/@content",
            ".//div[@data-zone-name='summary']//h1",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        if ($d = $this->xpathScalar(".//div[@data-zone-name='specs']//ul", true))
            return '<ul>' . $d . '</ul>';
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[@data-zone-name='offer-cart']//div[@data-auto='price']/span/span",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@data-zone-name='offer-cart']//div[@data-auto='old-price']//span/span",
        );

        return $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//div[@itemtype='https://schema.org/BreadcrumbList']//*[@itemprop='name']/@content",
        );

        if ($categs = $this->xpathArray($paths))
        {
            array_pop($categs);
            array_shift($categs);
            return $categs;
        }
    }

    public function parseFeatures()
    {
        $url = strtok($this->getUrl(), '?');
        $url = rtrim($url, "#");
        $url .= '/spec';
        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        $names = $xpath->xpathArray(".//div[@data-auto='sku-specs']//div/span[not(@data-tid)]");
        $values = $xpath->xpathArray(".//div[@data-auto='sku-specs']//div/span[@data-tid and text()]");

        if (!$names || !$values || count($names) != count($values))
            return array();

        $features = array();
        for ($i = 0; $i < count($names); $i++)
        {
            $feature['name'] = \sanitize_text_field(html_entity_decode($names[$i]));
            $feature['value'] = \sanitize_text_field(html_entity_decode($values[$i]));
            $features[] = $feature;
        }

        return $features;
    }

    public function parseReviews()
    {
        $url = strtok($this->getUrl(), '?');
        $url = rtrim($url, "#");
        $url .= '/reviews';
        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        $r = array();
        $users = $xpath->xpathArray(".//div[@data-zone-name='productReviews']//div[@style='width:42px;height:42px']/../div[2]/div/div[2]");
        $comments = $xpath->xpathArray(".//div[@data-zone-name='productReviews']//span[normalize-space(text())='Достоинства']/../../../div", true);
        $ratings = $xpath->xpathArray(".//div[@data-zone-name='productReviews']//div[contains(@style, 'width:') and contains(@style, '%')]/@style");

        for ($i = 0; $i < count($comments); $i++)
        {
            $c = html_entity_decode($comments[$i]);
            $c = str_replace('</span><span', '</span><br><span', $c);
            $c = str_replace("\n", '<br>', $c);

            $comment['review'] = $c;
            if (!empty($users[$i]))
                $comment['author'] = \sanitize_text_field($users[$i]);

            if (!empty($ratings[$i]) && preg_match('/width:(\d+)/', $ratings[$i], $matches))
                $comment['rating'] = ExtractorHelper::ratingPrepare($matches[1] / 20);
            $r[] = $comment;
        }
        return $r;
    }

    public function parseCurrencyCode()
    {
        return 'RUR';
    }

}
