<?php

namespace ContentEgg\application\modules\AvantlinkProducts;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\avantlink\AvantlinkApi;
use ContentEgg\application\modules\AvantlinkProducts\ExtraDataAvantlinkProducts;

/**
 * AvantlinkProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AvantlinkProductsModule extends AffiliateParserModule
{

    private $merchant2domain = array(
        'GritrSports' => 'gritrsports.com',
        'Amazon' => 'amazon.com',
        'Ace Link Armor' => 'acelinkarmor.com',
        'Brownells' => 'brownells.com',
        'GUNS.COM' => 'guns.com',
        'Palmetto State Armory' => 'palmettostatearmory.com',
        'GunSkins' => 'gunskins.com',
        'Rossignol' => 'rossignol.com',
        'Creedmoor Sports' => 'creedmoorsports.com',
        'GritrSports' => 'gritrsports.com',
        'Gritr Outdoors' => 'gritroutdoors.com',
        'Spartan Armor Systems' => 'spartanarmorsystems.com',
        'WMD Guns' => 'wmdguns.com',
        'Shoot Steel' => 'shootsteel.com',
        'GunMag Warehouse' => 'gunmagwarehouse.com',
        'Alien Gear Holsters' => 'aliengearholsters.com',
    );
    
    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Avantlink Products',
            'description' => sprintf(__('Adds products from %s marketing platform.', 'content-egg'), '<a target="_blank" href="http://www.avantlink.com/">AvantLink</a>'),
        );
    }

    public function releaseVersion()
    {
        return '4.9.0';
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
            $limit = $this->config('entries_per_page_update');
        } else
        {
            $limit = $this->config('entries_per_page');
        }
        $options['search_results_count'] = (int) $limit;

        // price filter
        if (!empty($query_params['search_price_minimum']))
        {
            $options['search_price_minimum'] = (float) $query_params['search_price_minimum'];
        } elseif ($this->config('search_price_minimum'))
        {
            $options['search_price_minimum'] = (float) $this->config('search_price_minimum');
        }

        if (!empty($query_params['search_price_maximum']))
        {
            $options['search_price_maximum'] = (float) $query_params['search_price_maximum'];
        } elseif ($this->config('search_price_maximum'))
        {
            $options['search_price_maximum'] = (float) $this->config('search_price_maximum');
        }

        $params = array(
            'app_id',
            'custom_tracking_code',
            'search_category',
            'search_department',
            'search_results_merchant_limit',
        );
        foreach ($params as $param)
        {
            if ($value = $this->config($param))
            {
                $options[$param] = $value;
            }
        }
        $pipe_delimited_params = array(
            'datafeed_ids',
            'merchant_category_ids',
            'merchant_group_ids',
            'merchant_ids',
        );

        foreach ($pipe_delimited_params as $param)
        {
            if ($value = $this->config($param))
            {
                $values = TextHelper::getArrayFromCommaList($values);
                $options[$param] = join(',', $values);
            }
        }

        if ((int) $this->config('search_on_sale_level'))
        {
            $options['search_on_sale_level'] = (int) $this->config('search_on_sale_level');
        }
        if ($this->config('search_advanced_syntax'))
        {
            $options['search_advanced_syntax'] = true;
        }

        $results = $this->getApiClient()->search($keyword, $options);

        if (!is_array($results) || !isset($results[0]['lngProductId']))
        {
            return array();
        }

        return $this->prepareResults($results);
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;

            $content->unique_id = $r['lngProductId'];
            $content->sku = $r['strProductSKU'];
            $content->title = html_entity_decode($r['strProductName']);
            $content->manufacturer = $r['strBrandName'];
            $content->price = $r['dblProductSalePrice'];
            $content->currencyCode = 'USD';
            $content->priceOld = $r['dblProductPrice'];
            $content->url = $r['strBuyURL'];
            $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;

            if ($r['strCategoryName'])
            {
                $content->category = $r['strCategoryName'];
            } elseif ($r['strDepartmentName'])
            {
                $content->category = $r['strDepartmentName'];
            }

            if ($r['txtLongDescription'])
            {
                $content->description = strip_tags($r['txtLongDescription']);
            } elseif ($r['txtShortDescription'])
            {
                $content->description = strip_tags($r['txtShortDescription']);
            }
            if ($max_size = $this->config('description_size'))
            {
                $content->description = TextHelper::truncateHtml($content->description, $max_size);
            }

            if ($r['strLargeImage'])
            {
                $content->img = $r['strLargeImage'];
            } elseif ($r['strMediumImage'])
            {
                $content->img = $r['strMediumImage'];
            }

            if ($r['strMerchantName'])
            {
                $content->merchant = $r['strMerchantName'];
            }

            $merchant = strtolower($content->merchant);
            if (TextHelper::isValidDomainName($merchant))
            {
                $content->domain = $merchant;
            }

            if ($content->merchant && isset($this->merchant2domain[$content->merchant]))
                $content->domain = $this->merchant2domain[$content->merchant];

            $content->extra = new ExtraDataAvantlinkProducts();
            ExtraDataAvantlinkProducts::fillAttributes($content->extra, $r);

            $data[] = $content;
        }

        return $data;
    }

    public function doRequestItems(array $items)
    {
        foreach ($items as $key => $item)
        {
            if (empty($item['extra']['lngMerchantId']) || empty($item['sku']) || empty($item['extra']['lngDatafeedId']))
            {
                continue;
            }
            $result = $this->getApiClient()->priseCheck($item['extra']['lngMerchantId'], $item['sku'], $item['extra']['lngDatafeedId']);
            if (!isset($result['Table1']))
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                continue;
            }
            $r = $result['Table1'];

            // assign new data
            $items[$key]['price'] = str_replace(',', '', $r['Product_Price']);
            $items[$key]['priceOld'] = str_replace(',', '', $r['Product_Price']);
            $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;

            // fix domains
            if ($item['merchant'] && isset($this->merchant2domain[$item['merchant']]))
                $items[$key]['domain'] = $this->merchant2domain[$item['merchant']];
        }

        return $items;
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new AvantlinkApi($this->config('affiliate_id'), $this->config('website_id'));
        }

        return $this->api_client;
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

}
