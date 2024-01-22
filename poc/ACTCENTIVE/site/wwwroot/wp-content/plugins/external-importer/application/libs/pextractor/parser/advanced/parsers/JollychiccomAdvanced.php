<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * JollychiccomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class JollychiccomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[contains(@class, 'pro_list_imgbox')]/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@id='changePage']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[contains(@class, 'org-price-box')]//del",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        return $this->xpathArray(".//ul[@id='thumblist']//img/@mid");
    }

    public function parseFeatures()
    {
        if (!preg_match_all('/\{"key":"(.+?)","value":"(.+?)"\}/ims', $this->html, $matches))
            return array();

        foreach ($matches[1] as $i => $value)
        {
            $feature['name'] = \sanitize_text_field($matches[1][$i]);
            $feature['value'] = \sanitize_text_field($matches[2][$i]);
            $features[] = $feature;
        }

        return $features;
    }

    public function parseReviews()
    {
        if (!preg_match('/-g(0.+?)\.html/', $this->getUrl(), $matches))
            return array();

        $request_url = 'https://www.jollychic.com/goods/get-goods-comments';

        $response = \wp_remote_post($request_url, array(
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'),
            'body' => 'goodsId=' . $matches[1] . '&page=1',
            'method' => 'POST'
        ));

        if (\is_wp_error($response))
            return array();

        if (!$body = \wp_remote_retrieve_body($response))
            return array();

        $response = json_decode($body, true);

        if (!$response || !isset($response['data']['reviewList']))
            return array();

        $results = array();
        foreach ($response['data']['reviewList'] as $r)
        {
            $review = array();
            if (!isset($r['review_content']))
                continue;

            $review['review'] = $r['review_content'];

            if (isset($r['user_point']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['user_point']);

            if (isset($r['user_name']))
                $review['author'] = $r['user_name'];

            if (isset($r['review_time']))
                $review['date'] = strtotime($r['review_time']);

            $results[] = $review;
        }
        return $results;
    }

}
