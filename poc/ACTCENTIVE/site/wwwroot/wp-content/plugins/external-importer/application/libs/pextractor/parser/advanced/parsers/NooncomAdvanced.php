<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * NooncomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class NooncomAdvanced extends AdvancedParser {

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();

        // reset cookies
        $httpOptions['cookies'] = array();
        $httpOptions['user-agent'] = 'ia_archiver';
        
        return $httpOptions;
    }

    public function parseLinks()
    {
        $path = array(
            ".//a[contains(@class, 'product') or contains(@id, 'productBox-')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[contains(@class, 'paginationWrapper')]//li[@class='page']/a",
        );

        $pages = $this->xpathArray($path);
        $urls = array();
        foreach ($pages as $page)
        {
            $urls[] = strtok($this->getUrl(), '?') . '?page=' . (int) $page;
        }
        return $urls;
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//span[normalize-space(text())='Highlights']/../..", true);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@data-qa]//div[@class='priceWas']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//div[contains(@class, 'swiper-slide')]//img/@src");
        foreach ($results as $img)
        {
            $img = str_replace('/t_desktop-thumbnail-v1/', '/t_desktop-pdp-v1/', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//div[contains(@class, 'breadcrumb')]//span[contains(@class, 'crumb')]/a",
        );

        if ($categs = $this->xpathArray($paths))
        {
            array_shift($categs);
            return $categs;
        }
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//span[normalize-space(text())='Specifications']/..//table//td[1]",
                'value' => ".//span[normalize-space(text())='Specifications']/..//table//td[2]",
            ),
        );
    }

    public function parseReviews()
    {
        if (!preg_match('~\/(N\d+?A)\/~', $this->getUrl(), $matches))
            return array();

        $url = 'https://js.testfreaks.com/onpage/noon.com/reviews.json?key=' . urlencode($matches[1]);
        $response = $this->getRemoteJson($url);

        if (!$response || !isset($response['user_review_url']))
            return array();

        $response = $this->getRemoteJson($response['user_review_url']);
        if (!$response || !isset($response['reviews']) || !is_array($response['reviews']))
            return array();

        $results = array();

        foreach ($response['reviews'] as $r)
        {
            $review = array();
            if (!isset($r['extract']))
                continue;

            $review['review'] = $r['extract'];

            if (isset($r['score']) && isset($r['score_max']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['score'], $r['score_max']);

            if (isset($r['author']))
                $review['author'] = $r['author'];

            if (isset($r['date']))
                $review['date'] = strtotime($r['date']);

            $results[] = $review;
        }
        return $results;
    }

    public function parseInStock()
    {
        if ($this->xpathScalar(".//div[contains(@class, 'priceRow')]/p[contains(@class, 'notAvailableNote')]"))
            return false;
        
        if (strpos($this->html, '>Sorry! This product is not available.</div>'))
            return false;
        
    }

}
