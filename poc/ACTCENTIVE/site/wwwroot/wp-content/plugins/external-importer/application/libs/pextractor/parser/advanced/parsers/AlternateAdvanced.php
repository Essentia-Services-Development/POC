<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;

/**
 * AlternateAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AlternateAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[@class='productLink']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='paging']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1/span[2]",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@class='productMainContainerTable']//*[@class='msrp']",
            ".//span[@class='text-nowrap striked-price line-through']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//div[@id='medias']//li/@data-url");
        foreach ($results as $img)
        {
            $img = str_replace('/230x230/', '/o/', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//*[@class='techData']//td[1]",
                'value' => ".//*[@class='techData']//td[2]",
            ),
        );
    }

    public function parseReviews()
    {

        $url = $this->getUrl();
        $url = str_replace('/html/product/', '/html/productRatings/', $url);

        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        $r = array();
        $users = $xpath->xpathArray(".//div[@itemprop='review']//span[@itemprop='name']//strong[2]");
        $comments = $xpath->xpathArray(".//div[@itemprop='review']//div[@itemprop='description']");
        $ratings = $xpath->xpathArray(".//div[@itemprop='review']//*[@itemprop='ratingValue']");
        $dates = $xpath->xpathArray(".//div[@itemprop='review']//*[@itemprop='dateCreated']");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['review'] = \sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['author'] = str_replace('van ', '', \sanitize_text_field($users[$i]));
            if (!empty($ratings[$i]))
                $comment['rating'] = $ratings[$i];
            if (!empty($dates[$i]))
                $comment['date'] = strtotime($dates[$i]);
            $r[] = $comment;
        }
        return $r;
    }

    public function parseCurrencyCode()
    {
        return 'EUR';
    }

}
