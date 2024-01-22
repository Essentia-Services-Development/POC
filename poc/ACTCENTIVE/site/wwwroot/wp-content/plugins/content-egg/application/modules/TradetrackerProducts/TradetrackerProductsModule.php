<?php

namespace ContentEgg\application\modules\TradetrackerProducts;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\tradetracker\TradetrackerSoap;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * TradetrackerProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class TradetrackerProductsModule extends AffiliateParserModule {

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Tradetracker Products',
            'description' => sprintf(__('Adds products from %s.', 'content-egg'), 'Tradetracker'),
        );
    }

    public function releaseVersion()
    {
        return '3.6.0';
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

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $options = array();

        if ($is_autoupdate)
        {
            $options['limit'] = $this->config('entries_per_page_update');
        } else
        {
            $options['limit'] = $this->config('entries_per_page');
        }

        $fields = array(
            'feedID',
            'feedCategoryName',
            'campaignID',
            'campaignCategoryID',
            'priceFrom',
            'priceTo',
        );

        foreach ($fields as $f)
        {
            if ($this->config($f))
            {
                $options[$f] = $this->config($f);
            }
        }

        /*
          if (!empty($query_params['priceFrom']))
          $options['priceFrom'] = (float)$query_params['priceFrom'];
          if (!empty($query_params['priceTo']))
          $options['priceTo'] = (float)$query_params['priceTo'];
         *
         */

        $results = $this->getApiClient()->getFeedProducts($keyword, $options);
        if (!is_array($results))
        {
            return array();
        }
        if (!isset($results[0]) && isset($results['identifier']))
        {
            $results = array($results);
        }

        return $this->prepareResults($results);
    }

    private function prepareResults($results)
    {
        // filter dublicates
        $filtered = array();
        foreach ($results as $key => $r)
        {
            $is_dublicate = false;
            foreach ($filtered as $f)
            {
                if ($r['identifier'] == $f['identifier'] && $r['name'] == $f['name'])
                {
                    $is_dublicate = true;
                    break;
                }
            }
            if (!$is_dublicate)
            {
                $filtered[] = $r;
            }
        }

        $data = array();
        foreach ($filtered as $key => $r)
        {
            $content = new ContentProduct;
            $content->extra = new ExtraDataTradetrackerProducts;
            foreach ($r['additional'] as $k => $additional)
            {
                $name = $additional['name'];
                $lower_name = strtolower($additional['name']);
                $value = $additional['value'];
                if ($name == 'brand')
                {
                    $content->manufacturer = $value;
                } elseif (property_exists($content, $name))
                {
                    $content->$name = $value;
                } elseif (property_exists($content, $lower_name))
                {
                    $content->$lower_name = $value;
                } elseif (property_exists($content->extra, $name))
                {
                    $content->extra->$name = $value;
                } else
                {
                    continue;
                }
                unset($r['additional'][$k]);
            }
            $content->extra->additional = $r['additional'];

            // Common data
            $content->unique_id = $r['identifier'] . '-' . rand(0, 99999);
            $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            $content->title = $r['name'];
            $content->category = $r['productCategoryName'];
            $content->img = $r['imageURL'];
            $content->price = (float) $r['price'];
            $content->currencyCode = TradetrackerProductsConfig::getCurrencyByLocale($this->config('locale'));
            $content->url = $r['productURL'];

            if ($this->config('subId'))
            {
                $query = parse_url($content->url, PHP_URL_QUERY);
                parse_str($query, $params);
                if (isset($params['tt']))
                {
                    $content->url = \add_query_arg('tt', $params['tt'] . $this->config('subId'), $content->url);
                }
            }

            $content->domain = TextHelper::parseDomain($content->url, 'r');
            if (!$content->domain)
            {
                $content->domain = TextHelper::parseDomain($content->url, 'u');
            }
            $content->description = strip_tags($r['description']);
            if ($max_size = $this->config('description_size'))
            {
                $content->description = TextHelper::truncate($content->description, $max_size);
            }
            $data[] = $content;
        }

        return $data;
    }

    public function doRequestItems(array $items)
    {
        foreach ($items as $key => $item)
        {
            $options = array();
            $options['limit'] = 10;
            $keyword = $item['title'];
            try
            {
                $results = $this->getApiClient()->getFeedProducts($keyword, $options);
            } catch (\Exception $e)
            {
                continue;
            }

            if (!is_array($results))
            {
                return array();
            }
            if (!isset($results[0]) && isset($results['identifier']))
            {
                $results = array($results);
            }

            $results = $this->prepareResults($results);

            $product = null;
            foreach ($results as $i => $r)
            {
                if ($this->isProductsMatch($item, $r))
                {
                    $product = $r;
                    break;
                }
            }
            if (!$product)
            {
                if ($this->config('stock_status') == 'out_of_stock')
                {
                    $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                } else
                {
                    $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_UNKNOWN;
                }
                continue;
            }
            // assign new price
            $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            $items[$key]['price'] = $product->price;
            $items[$key]['url'] = $product->url;
        }

        return $items;
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new TradetrackerSoap($this->config('customerID'), $this->config('passphrase'), $this->config('locale'), $this->config('affiliateSiteID'));
        }

        return $this->api_client;
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

    public function isProductsMatch(array $p1, ContentProduct $p2)
    {
        $p2 = json_decode(json_encode($p2), true);

        if ($p1['url'] == $p2['url'])
        {
            return true;
        }

        if ($p1['domain'] && $p1['domain'] != $p2['domain'])
        {
            return false;
        }

        if ($p1['sku'] && $p1['sku'] == $p2['sku'])
        {
            return true;
        }
        if ($p1['ean'] && $p1['ean'] == $p2['ean'])
        {
            return true;
        }
        if ($p1['img'] && $p1['img'] == $p2['img'])
        {
            return true;
        }
        if ($p1['title'] == $p2['title'] && $p1['domain'] == $p2['domain'])
        {
            return true;
        }

        return false;
    }

}
