<?php

namespace ContentEgg\application\modules\Aliexpress2;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\aliexpress\Aliexpress2Api;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;
use ContentEgg\application\helpers\ArrayHelper;

/**
 * AliexpressModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class Aliexpress2Module extends AffiliateParserModule
{

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Aliexpress',
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/aliexpress',
        );
    }

    public function releaseVersion()
    {
        return '6.5.0';
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
            $options['page_size'] = $this->config('entries_per_page_update');
        }
        else
        {
            $options['page_size'] = $this->config('entries_per_page');
        }

        if (!empty($query_params['max_sale_price']) && $price = (int) $query_params['max_sale_price'])
        {
            $options['max_sale_price'] = $price * 100;
        }
        elseif ($price = (int) $this->config('max_sale_price'))
        {
            $options['max_sale_price'] = $price * 100;
        }
        else
        {
            $options['max_sale_price'] = 999999900;
        }

        if (!empty($query_params['min_sale_price']) && $price = (int) $query_params['min_sale_price'])
        {
            $options['min_sale_price'] = $price * 100;
        }
        elseif ($price = (int) $this->config('min_sale_price'))
        {
            $options['min_sale_price'] = $price * 100;
        }
        else
        {
            $options['min_sale_price'] = 1;
        }

        if ($options['min_sale_price'] && !$options['max_sale_price'])
        {
            $options['max_sale_price'] = 999999900;
        }
        if ($options['max_sale_price'] && !$options['min_sale_price'])
        {
            $options['min_sale_price'] = 1;
        }

        $options['target_currency'] = $this->config('target_currency');
        $options['target_language'] = $this->config('target_language');
        $options['platform_product_type'] = $this->config('platform_product_type');

        if ($category_id = (int) $this->config('category_id'))
        {
            $options['category_id'] = $category_id;
        }

        /*
          if ($ship_to_country = $this->config('ship_to_country'))
          $options['ship_to_country'] = $ship_to_country;
         *
         */

        if ($sort = $this->config('sort'))
        {
            $options['sort'] = $sort;
        }

        if ($tracking_id = $this->config('tracking_id'))
        {
            $options['tracking_id'] = $tracking_id;
        }

        // Is URL passed? Search by product URL
        if (filter_var($keyword, FILTER_VALIDATE_URL) && $product_id = Aliexpress2Module::parseIdFromUrl($keyword))
        {
            $keyword = $product_id;
        }

        // Product ID search?
        if (Aliexpress2Module::isProductId($keyword))
        {
            $results = $this->getAliexpressClient()->product($keyword, $options);
            if (!is_array($results) || !isset($results['aliexpress_affiliate_productdetail_get_response']))
            {
                return array();
            }
            $results = $results['aliexpress_affiliate_productdetail_get_response'];
        }
        else
        {
            $results = $this->getAliexpressClient()->search($keyword, $options);
            if (!is_array($results) || !isset($results['aliexpress_affiliate_product_query_response']))
            {
                return array();
            }
            $results = $results['aliexpress_affiliate_product_query_response'];
        }

        if (!isset($results['resp_result']['result']['products']['product']))
        {
            return array();
        }

        return $this->prepareResults($results['resp_result']['result']['products']['product']);
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;
            $content->unique_id = $r['product_id'];
            $content->title = strip_tags($r['product_title']);
            $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            $content->merchant = 'AliExpress';
            $content->orig_url = $r['product_detail_url'];
            $content->domain = TextHelper::getHostName($content->orig_url);
            $content->category = $r['first_level_category_name'];

            if (!empty($r['target_sale_price']))
            {
                $content->price = $r['target_sale_price'];
                $content->priceOld = $r['target_original_price'];
            }
            elseif (!empty($r['target_original_price']))
            {
                $content->price = $r['target_original_price'];
            }
            elseif (!empty($r['sale_price']))
            {
                $content->price = $r['sale_price'];
                $content->priceOld = $r['original_price'];
            }
            else
            {
                $content->price = $r['original_price'];
            }

            if (!empty($r['target_original_price_currency']))
            {
                $content->currencyCode = $r['target_original_price_currency'];
            }
            elseif (!empty($r['target_sale_price_currency']))
            {
                $content->currencyCode = $r['target_sale_price_currency'];
            }
            else
            {
                $content->currencyCode = $r['original_price_currency'];
            }

            $content->img = $r['product_main_image_url'];
            $content->url = $this->getAffiliateLink($r);
            $content->extra = new ExtraDataAliexpress2();
            ExtraDataAliexpress2::fillAttributes($content->extra, $r);
            if (isset($r['product_small_image_urls']['string']))
            {
                $content->extra->image_urls = $r['product_small_image_urls']['string'];
            }

            $data[] = $content;
        }

        return $data;
    }

    public function doRequestItems(array $items)
    {
        $options = array();
        $options['target_currency'] = $this->config('target_currency');
        $options['target_language'] = $this->config('target_language');
        if ($tracking_id = $this->config('tracking_id'))
        {
            $options['tracking_id'] = $tracking_id;
        }

        $product_ids = array_keys($items);

        try
        {
            $results = $this->getAliexpressClient()->product($product_ids, $options);
        }
        catch (\Exception $e)
        {
            return $items;
        }

        if (isset($results['aliexpress_affiliate_productdetail_get_response']['resp_result']['result']['products']['product']))
        {
            $results = $results['aliexpress_affiliate_productdetail_get_response']['resp_result']['result']['products']['product'];
        }
        else
        {
            $results = array();
        }

        $results = $this->prepareResults($results);

        $new = array();
        foreach ($results as $r)
        {
            $new[$r->unique_id] = $r;
        }

        // assign new data
        foreach ($items as $unique_id => $item)
        {
            if (!isset($new[$unique_id]))
            {
                $items[$unique_id]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                continue;
            }

            $result = $new[$unique_id];

            $fields = array(
                'price',
                'priceOld',
                'currency',
                'currencyCode',
                'availability',
                'orig_url',
                'stock_status',
                'url'
            );
            foreach ($fields as $field)
            {
                $items[$unique_id][$field] = $result->$field;
            }

            // all extra fields
            $items[$unique_id]['extra'] = ArrayHelper::object2Array($result->extra);
        }

        return $items;
    }

    private function getAliexpressClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new Aliexpress2Api($this->config('app_key'), $this->config('app_secret'));
        }

        return $this->api_client;
    }

    protected function getAffiliateLink(array $r)
    {
        if ($deeplink = $this->config('deeplink'))
        {
            return LinkHandler::createAffUrl($r['product_detail_url'], $deeplink);
        }
        elseif ($tracking_id = $this->config('tracking_id'))
        {
            return $r['promotion_link'];
        }
        else
        {
            return $r['product_detail_url'];
        }
    }

    public function viewDataPrepare($data)
    {
        if ($deeplink = $this->config('deeplink'))
        {
            foreach ($data as $key => $d)
            {
                $data[$key]['url'] = LinkHandler::createAffUrl($d['orig_url'], $deeplink, $d);
            }
        }

        return parent::viewDataPrepare($data);
    }

    public function renderSearchPanel()
    {
        $this->render('search_panel', array('module_id' => $this->getId()));
    }

    public function renderResults()
    {
        PluginAdmin::render('_metabox_results', array('module_id' => $this->getId()));
    }

    public function renderSearchResults()
    {
        PluginAdmin::render('_metabox_search_results', array('module_id' => $this->getId()));
    }

    public function renderUpdatePanel()
    {
        $this->render('update_panel', array('module_id' => $this->getId()));
    }

    private static function isProductId($str)
    {
        if (preg_match('/[0-9]{10,}/', $str))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private static function parseIdFromUrl($url)
    {
        $regex = '/aliexpress.+?\/.+?([0-9]{10,})\.html/';
        if (preg_match($regex, $url, $matches))
        {
            return $matches[1];
        }
        else
        {
            return '';
        }
    }
}
