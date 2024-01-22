<?php

namespace ContentEgg\application\modules\Awin;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateFeedParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\modules\Awin\models\AwinProductModel;

/**
 * AwinModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AwinModule extends AffiliateFeedParserModule {

    public function info()
    {
        return array(
            'name' => 'Awin',
            'description' => sprintf(__('Adds products from %s.', 'content-egg'), '<a href="http://www.keywordrush.com/go/awin">AWIN</a>'),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/awin',
        );
    }

    public static function getMerchantDomainPairs()
    {
        $pairs = array('Cdiscount FR' => 'cdiscount.com', 'Darty FR' => 'darty.com', 'SANICARE DE' => 'sanicare.de');

        return \apply_filters('cegg_awin_merchant_mapping', $pairs);
    }

    public function releaseVersion()
    {
        return '5.2.0';
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

    public function getProductModel()
    {
        return AwinProductModel::model();
    }

    public function isZippedFeed()
    {
        return true;
    }

    public function isUrlSearchAllowed()
    {
        return true;
    }

    public function getFeedUrl()
    {
        if (!$feed_url = $this->buildFeedUrl())
        {
            throw new \Exception('Wrong format of Datafeed URL.');
        }

        return $feed_url;
    }

    protected function feedProductPrepare(array $data)
    {
        $product = array();
        $product['id'] = $data['aw_product_id'];
        if (!(int) $data['in_stock'])
        {
            $product['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
        } else
        {
            $product['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
        }
        $product['price'] = (float) $data['search_price'];
        $product['title'] = \sanitize_text_field($data['product_name']);
        $product['orig_url'] = $data['merchant_deep_link'];
        if (TextHelper::isEan($data['ean']))
        {
            $product['ean'] = $data['ean'];
        } else
        {
            $product['ean'] = '';
        }
        $product['product'] = serialize($data);

        return $product;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $this->maybeImportProducts();

        if ($is_autoupdate)
        {
            $limit = $this->config('entries_per_page_update');
        } else
        {
            $limit = $this->config('entries_per_page');
        }

        if (TextHelper::isEan($keyword))
        {
            $results = $this->product_model->searchByEan($keyword, $limit);
        } elseif (filter_var($keyword, FILTER_VALIDATE_URL))
        {
            $results = $this->product_model->searchByUrl($keyword, $this->config('partial_url_match'), $limit);
        } else
        {
            $options = array();
            if (!empty($query_params['price_min']))
            {
                $options['price_min'] = (float) $query_params['price_min'];
            }
            if (!empty($query_params['price_min']))
            {
                $options['price_max'] = (float) $query_params['price_max'];
            }

            $results = $this->product_model->searchByKeyword($keyword, $limit, $options);
        }

        if (!$results)
        {
            return array();
        }

        return $this->prepareResults($results);
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $product)
        {
            if (!$r = unserialize($product['product']))
            {
                continue;
            }

            $content = new ContentProduct;
            $content->unique_id = $r['aw_product_id'];
            $content->title = $r['product_name'];
            $content->url = $r['aw_deep_link'];
            $content->orig_url = $r['merchant_deep_link'];
            $content->img = $r['merchant_image_url'];
            $content->description = $r['description'];
            $content->category = $r['category_name'];
            $content->price = $r['search_price'];
            $content->merchant = $r['merchant_name'];
            $content->currencyCode = $r['currency'];
            $content->ean = $product['ean'];

            $pairs = self::getMerchantDomainPairs();
            if (isset($pairs[$content->merchant]))
            {
                $content->domain = $pairs[$content->merchant];
            } elseif (!strstr($content->orig_url, 'https://www.awin1.com'))
            {
                $content->domain = TextHelper::getHostName($content->orig_url);
            }

            if (!empty($r['average_rating']))
            {
                $content->rating = TextHelper::ratingPrepare($r['average_rating']);
            }
            if (!empty($r['product_price_old']))
            {
                $content->priceOld = $r['product_price_old'];
            } elseif (!empty($r['base_price_amount']))
            {
                $content->priceOld = $r['base_price_amount'];
            } elseif (!empty($r['base_price']))
            {
                $content->priceOld = $r['base_price'];
            }

            if (isset($r['in_stock']) && !(int) $r['in_stock'])
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            } else
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            }

            $data[] = $content;
        }

        return $data;
    }

    public function doRequestItems(array $items)
    {
        $this->maybeImportProducts();
        foreach ($items as $key => $item)
        {
            $product = $this->product_model->searchById($item['unique_id']);
            if (!$product)
            {
                if ($this->product_model->count())
                {
                    $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                }
                continue;
            }
            if (!$r = unserialize($product['product']))
            {
                continue;
            }

            $items[$key]['price'] = $r['search_price'];
            if (isset($r['in_stock']) && !(int) $r['in_stock'])
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            } else
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            }
        }

        return $items;
    }

    private function buildFeedUrl()
    {
        $url = $this->config('datafeed_url');

        if (!$path = parse_url($url, PHP_URL_PATH))
        {
            return '';
        }

        $res = 'https://productdata.awin.com/datafeed/download';

        $params = array('apikey', 'language', 'cid', 'fid', 'bid', 'adultcontent');
        foreach ($params as $param)
        {
            $value = TextHelper::getParamFromPath($path, $param);
            if ($param == 'apikey' && !$value)
            {
                return '';
            }
            if ($value)
            {
                $res .= '/' . $param . '/' . rawurlencode($value);
            }
        }

        $default_params = array(
            'columns' => 'aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,reviews,average_rating,rating,number_available,in_stock,stock_quantity,valid_from,valid_to,is_for_sale,web_offer,pre_order,base_price_amount,product_price_old,base_price,ean',
            'format' => 'csv',
            'delimiter' => '%2C',
            'compression' => 'zip',
        );

        foreach ($default_params as $param => $value)
        {
            $res .= '/' . $param . '/' . $value;
        }

        return $res;
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
