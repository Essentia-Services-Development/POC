<?php

namespace ContentEgg\application\modules\Walmart;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\walmart\WalmartApi;
use ContentEgg\application\modules\Walmart\ExtraDataWalmart;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\LinkHandler;
use ContentEgg\application\modules\Walmart\WalmartConfig;

/**
 * WalmartModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class WalmartModule extends AffiliateParserModule
{

    private $api_client = null;

    public function info()
    {
        if (\is_admin())
        {
            \add_action('admin_notices', array(__CLASS__, 'opensslNotice'));
        }

        return array(
            'name' => 'Walmart',
            'description' => sprintf(__('Adds products from %s.', 'content-egg'), 'Walmart.com'),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/walmart',
        );
    }

    public static function opensslNotice()
    {
        if (!WalmartConfig::getInstance()->option('is_active'))
        {
            return;
        }

        if (extension_loaded('openssl'))
        {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>' . sprintf(__('<a href="%s">Walmart module</a> requires the OpenSSL PHP extension!', 'content-egg'), \get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-modules--Walmart')) . '</p>';
        echo '</div>';
    }

    public function releaseVersion()
    {
        return '4.1.0';
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function defaultTemplateName()
    {
        return 'grid';
    }

    public function isItemsUpdateAvailable()
    {
        return true;
    }

    public function isUrlSearchAllowed()
    {
        return true;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $options = array();

        if ($is_autoupdate)
        {
            $limit = $this->config('entries_per_page_update');
        }
        else
        {
            $limit = $this->config('entries_per_page');
        }

        $options['numItems'] = $limit;

        $params = array(
            'publisherId',
            'sort',
            'order',
        );

        foreach ($params as $param)
        {
            $value = $this->config($param);

            if ($param == 'order' && $value == 'asc')
            {
                $value = 'ascending';
            }
            elseif ($param == 'order' && $value == 'dec')
            {
                $value = 'descending';
            }

            if ($value)
            {
                $options[$param] = $value;
            }
        }

        if (TextHelper::isEan($keyword))
        {
            $keyword = ltrim($keyword, '0');
            $keyword = str_pad($keyword, 12, '0', STR_PAD_LEFT);
            $results = $this->getApiClient()->searchUpc($keyword);
        }
        elseif ($product_id = $this->getProductId($keyword))
        {
            $results = $this->getApiClient()->products($product_id, $options);
        }
        else
        {

            if ($this->config('categoryId'))
            {
                $options['categoryId'] = (int) trim($this->config('categoryId'), ".");
            }

            $options['responseGroup'] = 'full';

            // price filter
            if (!empty($query_params['price_min']))
            {
                $price_min = (float) $query_params['price_min'];
            }
            elseif ($this->config('price_min'))
            {
                $price_min = (float) $this->config('price_min');
            }
            else
            {
                $price_min = 0;
            }
            if (!empty($query_params['price_max']))
            {
                $price_max = (float) $query_params['price_max'];
            }
            elseif ($this->config('price_max'))
            {
                $price_max = (float) $this->config('price_max');
            }
            else
            {
                $price_max = 0;
            }
            if ($price_min && !$price_max)
            {
                $price_max = 999999;
            }
            if ($price_max && !$price_min)
            {
                $price_min = 0;
            }
            if ($price_min || $price_max)
            {
                $options['facet'] = 'on';
                $options['facet.range'] = 'price:[' . (int) $price_min . ' TO ' . (int) $price_max . ']';
            }

            $results = $this->getApiClient()->search($keyword, $options);
        }

        if (!isset($results['items']) || !is_array($results['items']))
        {
            return array();
        }

        return $this->prepareResults(array_slice($results['items'], 0, $limit));
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;

            $content->unique_id = $r['itemId'];
            $content->domain = 'walmart.com';
            $content->title = $r['name'];

            if (!empty($r['shortDescription']))
            {
                $content->description = $r['shortDescription'];
            }
            elseif (!empty($r['longDescription']))
            {
                $content->description = $r['longDescription'];
            }
            $content->description = strip_tags(html_entity_decode($content->description));
            if ($max_size = $this->config('description_size'))
            {
                $content->description = TextHelper::truncateHtml($content->description, $max_size);
            }

            if (!empty($r['upc']))
            {
                $content->upc = $r['upc'];
            }
            if (!empty($r['upc']) && TextHelper::isEan($r['upc']))
            {
                $content->ean = TextHelper::fixEan($r['upc']);
            }

            $content->categoryPath = explode('/', $r['categoryPath']);
            if (isset($content->categoryPath[0]) && $content->categoryPath[0] == 'Home Page')
                array_shift($content->categoryPath);

            $content->category = current($content->categoryPath);
            if (!empty($r['brandName']))
            {
                $content->manufacturer = $r['brandName'];
            }
            $content->img = $r['largeImage'];
            if (!empty($r['customerRating']))
            {
                $content->rating = TextHelper::ratingPrepare($r['customerRating']);
            }
            if (!empty($r['numReviews']))
            {
                $content->reviewsCount = (int) $r['numReviews'];
            }
            /*
              if (!empty($r['sellerInfo']))
              $content->merchant = $r['sellerInfo'];
             *
             */

            // Possible values are [Available, Limited Supply, Last few items, Not available]
            $content->availability = $r['stock'];

            if ($r['stock'] == 'Not available')
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
            else
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            }

            if (!empty($r['salePrice']))
            {
                $content->price = $r['salePrice'];
            }

            if (!empty($r['msrp']))
            {
                $content->priceOld = $r['msrp'];
            }

            if ($content->priceOld)
            {
                $pdisc = 100 - $content->price * 100 / $content->priceOld;
                if ($pdisc > 85)
                    $content->priceOld = 0;
            }

            $content->currencyCode = 'USD';
            $content->orig_url = TextHelper::parseOriginalUrl($r['productTrackingUrl'], 'u');
            $content->url = $this->generateAffiliateUrl($content->orig_url, $r);

            $content->extra = new ExtraDataWalmart();
            ExtraDataWalmart::fillAttributes($content->extra, $r);

            if ($this->config('customer_reviews'))
            {
                $content->extra->comments = $this->parseComments($r['itemId']);
            }

            $data[] = $content;
        }

        return $data;
    }

    protected function parseComments($item_id)
    {
        try
        {
            $results = $this->getApiClient()->reviews($item_id);
        }
        catch (\Exception $e)
        {
            return array();
        }
        if (!isset($results['reviews']) || !is_array($results['reviews']))
        {
            return array();
        }

        $reviews = array();
        foreach ($results['reviews'] as $r)
        {
            $review = array();

            $review['comment'] = strip_tags($r['reviewText']);
            if ($r['title'])
            {
                if (!preg_match('/[\.\!\?]$/', $r['title']))
                {
                    $r['title'] = $r['title'] . '.';
                }
                $review['comment'] = $r['title'] . ' ' . $review['comment'];
            }

            $review['name'] = sanitize_text_field($r['reviewer']);
            $review['rating'] = TextHelper::ratingPrepare($r['overallRating']['rating']);
            $review['date'] = strtotime($r['submissionTime']);
            $review['upVotes'] = (int) $r['upVotes'];
            $review['downVotes'] = (int) $r['downVotes'];

            $reviews[] = $review;
        }

        return $reviews;
    }

    public function doRequestItems(array $items)
    {
        // Lookup for mutiple item ids 12417832,19336123 (supports upto 20 items in one call):
        $pages_count = ceil(count($items) / 20);
        $results = array();

        $options = array();
        if ($this->config('publisherId'))
        {
            $options['publisherId'] = $this->config('publisherId');
        }

        for ($i = 0; $i < $pages_count; $i++)
        {
            $items20 = array_slice($items, $i * 20, 20);
            $item_ids = array_map(function ($element)
            {
                return $element['unique_id'];
            }, $items20);
            $res = $this->getApiClient()->products($item_ids, $options);
            if (!isset($res['items']))
            {
                continue;
            }

            foreach ($res['items'] as $r)
            {
                $results[$r['itemId']] = $r;
            }
        }

        // assign new data
        foreach ($items as $key => $item)
        {
            if (!isset($results[$item['unique_id']]))
            {
                continue;
            }
            $r = $results[$item['unique_id']];
            if (!empty($r['customerRating']))
            {
                $items[$key]['rating'] = TextHelper::ratingPrepare($r['customerRating']);
            }
            if (!empty($r['numReviews']))
            {
                $items[$key]['reviewsCount'] = (int) $r['numReviews'];
            }
            $items[$key]['availability'] = $r['stock'];

            if ($r['stock'] == 'Not available')
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
            else
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            }

            $items[$key]['url'] = $this->generateAffiliateUrl($item['orig_url'], $r);

            if (!empty($r['salePrice']))
            {
                $items[$key]['price'] = $r['salePrice'];
            }
            else
            {
                $items[$key]['price'] = null;
            }
            if (!empty($r['msrp']))
            {
                $items[$key]['priceOld'] = $r['msrp'];
            }

            $items[$key]['merchant'] = 'Walmart.com';
        }

        return $items;
    }

    private function generateAffiliateUrl($url, array $r)
    {
        if ($deeplink = $this->config('deeplink'))
        {
            return LinkHandler::createAffUrl($url, $deeplink);
        }

        /*
          if (!empty($r['productTrackingUrl']))
          {
          return $r['productTrackingUrl'];
          }
         * 
         */

        return 'https://goto.walmart.com/c/' . urlencode($this->config('publisherId')) . '/568844/9383?veh=aff&sourceid=imp_000011112222333344&u=' . urlencode($url);
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {

            $private_key = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCwEuPEMmkHDnJo
72l+6enCvKrjhgEGEy3AaMk1xpe1WIXJFhYlx3sg2HgrNFnykeKWhmVTF7/ElahK
C2tiFOXc+okxajL4jFmLcxQhAwbYPOjfSpuy1gO2VqTRrM5FcCRAVmYFvBJaoC9F
vkP9lngGxirVYVtlD8Cv8ic5BVaPfgHYh4MdDWs9APWPCGI2ni5DEuw8CMiT1Tui
gJPbciRGxeWBSaSjgwKve9PLrEiEXhFbdVNG1tN23kr3kthxb3Kc368hMeU/7hVS
0FN4aehcfmrPzjG7spePQb12IW2CzoDpAq/0EBHlVZUlkixIY9BLAYXJi4bSDvVD
LF8eIB2tAgMBAAECggEAHHDQra5e3K7uuBiD9+YcxkHncJ0CqVKLv1qttawAcWB9
K8APj8arEuEkeAYayV3bNek7kLJzXXO3HU6+57bsckddxcebuB4jkKzkAXkVr/QW
wYqxn6+GJfvU37GEGB9HG8VY8XAxnsXlHOTg4qNde+qinJj/RFHJFCKPR1yfYMn7
FymXR9VZgLCeG1t68uNpkP2GCX3JAMYl4/ilAzoVDVcHyXM/8N6YUpENAZqTdiFd
jC6EMeUAMbLtpMAb8skMA7w5VSAHy6SPiTpd7jq5MQGcUJ+URUGQ+fcHawKA2XhY
Cki35oqy9AtMagpVmEe2vz1OUzZb30Y0fH+J2bA0gQKBgQDhpefV3mIoKyXiDWLb
htnXCjEIyyZx97qbeh56DnfbBDoltlTaiHLqcZPqfv5KZwR6ep9VvYY0gsjWBVQx
wwI1Zd70ZQ0eK5d48Et6ya5QpV9hfIrkUqix7pUE68IlAxsUhIyUVXCz5HLDj0L1
6BdCIOpPM+ognAKm+kIMxMBaPQKBgQDHweo+/Bo6pzTS4++RE4lrkw7kYjvNk3eR
jGQC+aQj1rySRNx56ZJc/WawNkvhj8/Nl0UgNGdyiWkAGjNp0MprrOmudzzj0Q2M
0PC48LAYeLXCtKAw/9jZNX77VSv4dEH4Gx7aAVKTF1JWzW2krwHBy8ax2zZW77jt
05RRpG+4MQKBgEWDE1k6CajwKdpqX0LbVu480IAx/OTs+Mp+ozbckCWjNrp5Ych0
clowpO1/M5z+AU4tyjniiZ5Rj8cGmzo9JcgHWtiU7KaXrTDvbYEk8hMb7rccY7kU
ka1GnxeF/SfjvgrjDl9/tplkTcparrkMR2Xyt9uwVXa4OMTxoTlHvy3NAoGBAIrs
30f8txUxsrg01ClWqA0L0qCdfTAFLnQoamnzSuet8ancgGW9PxCzH5bPvEhcZ055
tRanu4ZZ8I+kqTsffZgTQtYWkV9zxfO4YKKOqjnqwaZvTrWlSiAOZ4jOfG0oFbVC
z1sY7l/kKVy7NFUDbbplSYPqjEk8IaYtrwp0zUoRAoGAKHhb7IjIX/RlMUwFlvwe
J60RTPussmjRw8vt2W1synixm33WJCM6cbedamlLvtuw3GzV1uXxzQ9rOrbqhkhE
uuVc4Azey8mIw+SHgPhgjP9OKJNtTMr7TB5/ffS20d+WxrL9z/RY6suonhXsukOX
9rj8yoGqxWcbofwWKscg0Jw=
-----END PRIVATE KEY-----
EOD;

            $this->api_client = new WalmartApi($key_version = 2, $consumer_id = '104753f7-aaa8-4b92-b2cd-22055234e864', $private_key);
        }

        return $this->api_client;
    }

    public function getProductId($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
        {
            return false;
        }

        $url = strtok($url, '?');
        if (preg_match('/\/(\d+)$/', $url, $matches))
        {
            return $matches[1];
        }
    }

    public function presavePrepare($data, $post_id)
    {
        $data = parent::presavePrepare($data, $post_id);

        if ($post_id > 0 && $this->config('reviews_as_comments'))
        {
            // get reviews from module data
            $comments = ContentManager::getNormalizedReviews($data);
            if ($comments)
            {
                // save reviews as post comments
                ContentManager::saveReviewsAsComments($post_id, $comments);

                // remove reviews from module data
                $data = ContentManager::removeReviews($data);
            }
        }

        return $data;
    }

    public function renderResults()
    {
        PluginAdmin::render('_metabox_results', array('module_id' => $this->getId()));
    }

    public function renderSearchResults()
    {
        PluginAdmin::render('_metabox_search_results', array('module_id' => $this->getId()));
    }

    public function renderSearchPanel()
    {
        $this->render('search_panel', array('module_id' => $this->getId()));
    }

    public function renderUpdatePanel()
    {
        $this->render('update_panel', array('module_id' => $this->getId()));
    }
}
