<?php

namespace ContentEgg\application\modules\Shareasale;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\shareasale\ShareasaleApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * ShareasaleModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ShareasaleModule extends AffiliateParserModule {

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Shareasale',
            'description' => __('Adds products from Shareasale.com. You must have approval from each program separately.', 'content-egg'),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/shareasale',
        );
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
        return false;
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

        if ($this->config('merchantId'))
        {
            $options['merchantId'] = $this->config('merchantId');
        }
        if ($this->config('excludeMerchants'))
        {
            $options['excludeMerchants'] = $this->config('excludeMerchants');
        }

        $options['affiliateId'] = $this->config('affiliateId');

        $results = $this->getShareasaleClient()->products($keyword, $options);

        if (!is_array($results) || empty($results['getProductsrecord']))
        {
            return array();
        }

        if (!isset($results['getProductsrecord'][0]) && isset($results['getProductsrecord']['productid']))
        {
            $results['getProductsrecord'] = array($results['getProductsrecord']);
        }
        $results = array_slice($results['getProductsrecord'], 0, $limit);

        return $this->prepareResults($results);
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;
            $content->unique_id = $r['productid'];

            if ($r['status'][0] == 'In stock' && $r['status'][1] == 'No')
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK_;
            } else
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            }
            $content->title = strip_tags($r['name']);
            $content->url = $r['link'];
            $content->currencyCode = 'USD'; // ???
            $content->currency = TextHelper::currencyTyping($content->currencyCode);

            if ($r['manufacture'])
            {
                $content->manufacturer = $r['manufacture'];
            }
            $content->merchant = $r['organization'];
            $content->domain = TextHelper::getHostName('http://' . $r['www']);
            if ($r['bigimage'])
            {
                $content->img = $r['bigimage'];
            }

            $r['price'] = (float) $r['price'];
            $r['retailprice'] = (float) $r['retailprice'];
            $content->price = $r['price'];
            if ($r['retailprice'] && $r['retailprice'] < $r['price'])
            {
                $content->priceOld = $r['retailprice'];
            }
            if ($r['description'])
            {
                $content->description = $r['description'];
            } elseif ($r['shortdescription'])
            {
                $content->description = $r['shortdescription'];
            }
            if ($max_size = $this->config('description_size'))
            {
                $content->description = TextHelper::truncate($content->description, $max_size);
            }
            if ($r['isbn'])
            {
                $content->isbn = $r['isbn'];
            }
            if ($r['upc'])
            {
                $content->upc = $r['upc'];
            }
            if ($r['sku'])
            {
                $content->sku = $r['sku'];
            }

            $content->extra = new ExtraDataShareasale;
            ExtraDataShareasale::fillAttributes($content->extra, $r);

            $data[] = $content;
        }

        return $data;
    }

    private function getShareasaleClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new ShareasaleApi($this->config('token'), $this->config('secret'));
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

}
