<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;

defined('\ABSPATH') || exit;

/**
 * UdemycomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class UdemycomAdvanced extends AdvancedParser
{

    protected $_product_id;
    protected $_prices;
    protected $_total_page = 1;

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();
        $httpOptions['cookies'] = array();
        $httpOptions['user-agent'] = 'ia_archiver';
        return $httpOptions;
    }

    protected function preParseProduct()
    {
        $this->_getPrices();
        return parent::preParseProduct();
    }

    protected function _getPrices()
    {
        $product_id = $this->xpathScalar(".//body/@data-clp-course-id");
        if (!$product_id)
        {
            $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
            if (preg_match('/480x270\/(\d+)_/', $img, $matches))
                $product_id = $matches[1];
        }

        $this->_product_id = $product_id;
        if (!$this->_product_id)
            return;

        $url = 'https://www.udemy.com/api-2.0/course-landing-components/' . urlencode($product_id) . '/me/?components=price_text,deal_badge,discount_expiration,redeem_coupon,gift_this_course,base_purchase_section,purchase_tabs_context,subscribe_team_modal_context,lifetime_access_context';

        $headers = array(
            'User-Agent' => 'ia_archiver',
            'X-Requested-With' => 'XMLHttpRequest',
        );

        $response = $this->getRemoteJson($url, false, 'GET', $headers);
        if ($response && isset($response['price_text']['data']))
        {
            $this->_prices = $response['price_text']['data'];
        }
    }

    public function parseLinks()
    {
        if (!$category_data = $this->xpathScalar(".//div/@data-component-props"))
            return array();

        $category = json_decode($category_data, true);

        if (isset($category['topic']['id']))
            $url = 'https://www.udemy.com/api-2.0/discovery-units/all_courses/?page_size=16&subcategory=&instructional_level=&lang=&price=&duration=&closed_captions=&label_id=' . (int) $category['topic']['id'] . '&source_page=topic_page&locale=en_US&currency=usd&navigation_locale=en_US&skip_price=true&sos=pl&fl=lbl';
        elseif (isset($category['pageObject']['id']) && !isset($category['pageObject']['category_id']))
            $url = 'https://www.udemy.com/api-2.0/discovery-units/all_courses/?page_size=16&subcategory=&instructional_level=&lang=&price=&duration=&closed_captions=&category_id=' . (int) $category['pageObject']['id'] . '&source_page=category_page&locale=en_US&currency=usd&navigation_locale=en_US&skip_price=true&sos=pc&fl=cat';
        elseif (isset($category['pageObject']['category_id']) && isset($category['pageObject']['id']))
            $url = 'https://www.udemy.com/api-2.0/discovery-units/all_courses/?page_size=16&subcategory=&instructional_level=&lang=&price=&duration=&closed_captions=&subcategory_id=' . (int) $category['pageObject']['id'] . '&source_page=subcategory_page&locale=en_US&currency=usd&navigation_locale=en_US&skip_price=true&sos=ps&fl=scat';
        else
            return array();

        $url_parts = parse_url($this->getUrl());
        $query = array();
        if (!empty($url_parts['query']))
            parse_str($url_parts['query'], $query);

        if (isset($query['p']))
            $url = \add_query_arg('p', $query['p'], $url);
        if (isset($query['lang']))
            $url = \add_query_arg('lang', $query['lang'], $url);
        if (isset($query['lang']))
            $url = \add_query_arg('sort', $query['sort'], $url);
        if (isset($query['price']))
            $url = \add_query_arg('price', $query['price'], $url);

        if (!$response = $this->getRemoteJson($url, false, 'GET', array('User-Agent' => 'ia_archiver')))
            return array();

        if (!isset($response['unit']['items']))
            return array();

        $urls = array();
        foreach ($response['unit']['items'] as $item)
        {
            $urls[] = $item['url'];
        }

        // pagination
        if (isset($response['unit']['pagination']['total_page']))
            $this->_total_page = (int) $response['unit']['pagination']['total_page'];

        return $urls;
    }

    public function parsePagination()
    {
        $urls = array();
        for ($i = 2; $i <= $this->_total_page; $i++)
        {
            $urls[] = \add_query_arg('p', $i, $this->getUrl());
        }
        return $urls;
    }

    public function parsePrice()
    {
        if (isset($this->_prices['pricing_result']['price']['amount']))
            return $this->_prices['pricing_result']['price']['amount'];
        elseif (isset($this->_prices['pricing_result']['list_price']['amount']))
            return $this->_prices['pricing_result']['list_price']['amount'];
        elseif (isset($this->_prices['list_price']['amount']))
            return $this->_prices['list_price']['amount'];

        $paths = array(
            ".//meta[@property='udemy_com:price']/@content",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        if (isset($this->_prices['list_price']['amount']))
            return $this->_prices['list_price']['amount'];
    }

    public function parseDescription()
    {
        $paths = array(
            ".//div[@data-purpose='course-description']//div[contains(@class, 'show-more--content--')]",
        );

        if (!$d = $this->xpathScalar($paths, true))
            return;

        if ($r = $this->xpathScalar(".//div[contains(@class, 'what-you-will-learn--what-will-you-learn')]", true))
            $res .= $r;

        $res .= $d;

        if ($h2 = $this->xpathScalar(".//div[@data-purpose='course-curriculum']//h2"))
        {
            if ($r = $this->xpathArray(".//div[@data-purpose='course-curriculum']//*[contains(@class, 'section--section-title')]"))
            {
                $res .= '<h2>' . $h2 . '</h2>';
                $res .= '<ul><li>';
                $res .= join('</li><li>', $r);
                $res .= '</li></ul>';
            }
        }

        return $res;
    }

    public function parseShortDescription()
    {
        $paths = array(
            ".//div[@class='udlite-text-md clp-lead__headline']",
        );

        return $this->xpathScalar($paths, true);
    }


    public function parseManufacturer()
    {
        $paths = array(
            ".//div[@data-purpose='instructor-name-top']//a[1]/span",
        );

        return $this->xpathScalar($paths);
    }

    public function parseReviews()
    {
        if (!$this->_product_id)
            return array();

        $headers = array(
            'User-Agent' => 'ia_archiver',
            'X-Requested-With' => 'XMLHttpRequest',
        );

        $url = 'https://www.udemy.com/api-2.0/courses/' . urlencode($this->_product_id) . '/reviews/?courseId=' . urlencode($this->_product_id) . '&page=1&is_text_review=1&ordering=course_review_score__rank,-created&fields[course_review]=@default,response,content_html,created_formatted_with_time_since&fields[user]=@min,image_50x50,initials&fields[course_review_response]=@min,user,content_html,created_formatted_with_time_since';
        $response = $this->getRemoteJson($url, false, 'GET', $headers);

        if (!$response || !isset($response['results']))
            return array();

        $results = array();
        foreach ($response['results'] as $r)
        {
            $review = array();
            if (!isset($r['content_html']))
                continue;

            $review['review'] = $r['content_html'];

            if (isset($r['rating']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['rating']);

            if (isset($r['user']['display_name']))
                $review['author'] = $r['user']['display_name'];

            if (isset($r['created']))
                $review['date'] = strtotime($r['created']);

            $results[] = $review;
        }
        return $results;
    }

    public function parseCurrencyCode()
    {
        if (isset($this->_prices['pricing_result']['price']['currency']))
            return $this->_prices['pricing_result']['price']['currency'];
    }
}
