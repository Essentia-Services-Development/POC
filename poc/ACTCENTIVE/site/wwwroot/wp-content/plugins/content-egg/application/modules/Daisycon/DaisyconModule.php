<?php

namespace ContentEgg\application\modules\Daisycon;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateFeedParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\modules\Daisycon\models\DaisyconProductModel;
use ContentEgg\application\libs\daisycon\DaisyconApi;

/**
 * DaisyconModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class DaisyconModule extends AffiliateFeedParserModule {

    const TRANSIENT_PROGRAMS = 'cegg_daisycon_programs';

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Daisycon',
            'description' => sprintf(__('Adds products from %s.', 'content-egg'), 'Daisycon'),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/daisycon',
        );
    }

    public static function getMerchantDomainPairs()
    {
        $pairs = array('Zaful' => 'zaful.com');

        return \apply_filters('cegg_daisycon_merchant_mapping', $pairs);
    }

    public static function getMerchantCurrencyPairs()
    {
        $pairs = array('Zaful' => 'USD');

        return \apply_filters('cegg_daisycon_currency_mapping', $pairs);
    }

    public function releaseVersion()
    {
        return '5.4.0';
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
        return DaisyconProductModel::model();
    }

    public function isZippedFeed()
    {
        return false;
    }

    public function isUrlSearchAllowed()
    {
        return true;
    }

    public function getFeedUrl()
    {
        return $this->config('datafeed_url');
    }

    protected function feedProductPrepare(array $data)
    {
        $product = array();
        $product['id'] = $data['daisycon_unique_id'];
        if ($data['status'] == 'active')
        {
            $product['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
        } else
        {
            $product['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
        }

        if ($data['in_stock'] == 'false')
        {
            $product['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
        }

        $product['price'] = (float) $data['price'];
        $product['title'] = \sanitize_text_field($data['title']);

        $product['orig_url'] = TextHelper::parseOriginalUrl($data['link'], 'dl');

        if (isset($data['ean']) && TextHelper::isEan($data['ean']))
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
        } elseif (filter_var($keyword, FILTER_VALIDATE_URL) || substr_count($keyword, "/") >= 3)
        {
            if (!$url_without_domain = ltrim(TextHelper::getUrlWithoutDomain($keyword), "/"))
            {
                $url_without_domain = $keyword;
            }

            $results = $this->product_model->searchByUrl($url_without_domain, $this->config('partial_url_match'), $limit);
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

            $content->unique_id = $r['daisycon_unique_id'];
            $content->title = $r['title'];
            $content->url = $r['link'];
            $content->merchant = $r['name'];
            $content->price = $r['price'];
            $content->priceOld = $r['price_old'];
            if (!empty($r['brand']))
            {
                $content->manufacturer = $r['brand'];
            }
            if (!empty($r['ean']))
            {
                $content->ean = $r['ean'];
            }
            if (!empty($r['sku']))
            {
                $content->sku = $r['sku'];
            }
            if (!empty($r['category']))
            {
                $content->category = $r['category'];
            }
            if (!empty($r['category_path']))
            {
                $content->categoryPath = $r['category_path'];
            }
            if (!empty($r['currency']))
            {
                $content->currencyCode = $r['currency'];
            }

            if (!empty($r['image_medium']))
            {
                $content->img = $r['image_medium'];
            } elseif (!empty($r['image_small']))
            {
                $content->img = $r['image_small'];
            } elseif (!empty($r['image_large']))
            {
                $content->img = $r['image_large'];
            }
            if (!empty($r['description']) && $r['title'] != $r['description'])
            {
                $content->description = $r['description'];
            } elseif (!empty($r['description_short']) && $r['title'] != $r['description_short'])
            {
                $content->description = $r['description_short'];
            }
            if ($r['status'] == 'active')
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            } else
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
            if ($r['in_stock'] == 'false')
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
            if ($program = $this->getProgram($r['id']))
            {
                $content->domain = $program['display_url'];
                if (!$content->currencyCode)
                {
                    $content->currencyCode = $program['currency_code'];
                }
            }

            $pairs = self::getMerchantDomainPairs();
            if (isset($pairs[$content->merchant]))
            {
                $content->domain = $pairs[$content->merchant];
            }

            $pairs = self::getMerchantCurrencyPairs();
            if (isset($pairs[$content->merchant]))
            {
                $content->currency = $pairs[$content->merchant];
            }

            if (!$content->currencyCode)
            {
                $content->currencyCode = 'USD';
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

            $items[$key]['price'] = $r['price'];
            $items[$key]['priceOld'] = $r['price_old'];

            if ($r['status'] == 'active')
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            } else
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
            if ($r['in_stock'] == 'false')
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
        }

        return $items;
    }

    protected function getProgram($program_id)
    {
        $programs = \get_transient(self::TRANSIENT_PROGRAMS);
        if ($programs && isset($programs[$program_id]))
        {
            return $programs[$program_id];
        }

        if (!$api_client = $this->getApiClient())
        {
            return false;
        }

        $r = $api_client->programs($program_id);
        if (!$r || !isset($r[0]['id']))
        {
            return false;
        }

        if (!$programs)
        {
            $programs = array();
        }

        $program = array(
            'advertiser_id' => $r[0]['advertiser_id'],
            'currency_code' => $r[0]['currency_code'],
            'display_url' => $r[0]['display_url'],
        );

        $programs[$program_id] = $program;
        \set_transient(self::TRANSIENT_PROGRAMS, $programs, 0);

        return $program;
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            if (!$this->config('publisher_id') || !$this->config('username') || !$this->config('password'))
            {
                $this->api_client = false;
            } else
            {
                $this->api_client = new DaisyconApi($this->config('publisher_id'), $this->config('username'), $this->config('password'));
            }
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
