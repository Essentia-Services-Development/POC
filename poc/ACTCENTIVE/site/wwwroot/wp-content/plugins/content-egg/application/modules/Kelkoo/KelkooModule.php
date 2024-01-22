<?php

namespace ContentEgg\application\modules\Kelkoo;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\kelkoo\KelkooApi;
use ContentEgg\application\modules\Kelkoo\ExtraDataKelkoo;
use ContentEgg\application\helpers\ArrayHelper;

/**
 * KelkooModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class KelkooModule extends AffiliateParserModule {

    private $api_client = null;
    private $merchants = array();

    public function info()
    {
        if (\is_admin())
        {
            \add_action('admin_notices', array(__CLASS__, 'updateNotice'));
        }

        return array(
            'name' => 'Kelkoo',
            'description' => sprintf(__('Adds products from %s.', 'content-egg'), '<a target="_blank" href="https://www.kelkoogroup.com">Kelkoo Group</a> marketing platform'),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/kelkoo',
        );
    }

    public static function updateNotice()
    {
        if (!KelkooConfig::getInstance()->option('is_active'))
        {
            return;
        }

        if (KelkooConfig::getInstance()->option('token'))
        {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>' . sprintf(__('Kelkoo launched a new tool for API that will replace the current eCS integration. Please visit <a href="%s">Kelkoo module settings</a> to get started with the new API.', 'content-egg'), \get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-modules--Kelkoo')) . '</p>';
        echo '</div>';
    }

    public function releaseVersion()
    {
        return '4.5.0';
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

        $options['pageSize'] = $limit;
        $options['fieldsAlias'] = 'all';
        $options['country'] = $this->config('region');

        if ($this->config('merchantId'))
        {
            $options['merchantId'] = 'merchantId:' . $this->config('merchantId');
        }
        if ($this->config('rebatePercentage'))
        {
            $options['filterGreaterThan'] = 'rebatePercentage:' . (int) $this->config('rebatePercentage');
        }

        // price filter
        if (!empty($query_params['price_min']))
        {
            $options['filterGreaterThanEqual'] = 'price:' . (float) $query_params['price_min'];
        } elseif ($this->config('price_min'))
        {
            $options['filterGreaterThanEqual'] = 'price:' . (float) $this->config('price_min');
        }
        if (!empty($query_params['price_max']))
        {
            $options['filterLowerThanEqual'] = 'price:' . (float) $query_params['price_max'];
        } elseif ($this->config('price_max'))
        {
            $options['filterLowerThanEqual'] = 'price:' . (float) $this->config('price_max');
        }

        if (TextHelper::isEan($keyword))
        {
            $results = $this->getApiClient()->searchEan($keyword, $options);
        } else
        {
            $results = $this->getApiClient()->search($keyword, $options);
        }

        if (!isset($results['offers']) || !isset($results['offers']))
        {
            return array();
        }

        return $this->prepareResults($results['offers']);
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $r)
        {
            $data[] = $this->prepareResult($r);
        }

        return $data;
    }

    private function prepareResult(array $r)
    {
        $content = new ContentProduct;

        $content->unique_id = $r['offerId'];
        $content->title = $r['title'];
        $content->orig_url = $r['offerUrl']['landingUrl'];
        $content->domain = TextHelper::getHostName($content->orig_url);
        $content->price = $r['price'];
        if ((float) $r['priceWithoutRebate'] > (float) $content->price)
        {
            $content->priceOld = $r['priceWithoutRebate'];
        }
        $content->currencyCode = $r['currency'];
        $content->url = $r['goUrl'];

        if ($r['availabilityStatus'] == 'out_of_stock')
        {
            $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
        } else
        {
            $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
        }
        $content->description = $r['description'];
        if ($max_size = $this->config('description_size'))
        {
            $content->description = TextHelper::truncateHtml($content->description, $max_size);
        }
        if (isset($r['images'][0]['zoomUrl']))
        {
            $content->img = $r['images'][0]['zoomUrl'];
        }
        if (!empty($r['merchant']['name']))
        {
            $content->merchant = $r['merchant']['name'];
        }
        if (!empty($r['merchant']['logoUrl']))
        {
            $content->logo = $r['merchant']['logoUrl'];
        }
        if (!empty($r['category']['name']))
        {
            $content->category = $r['category']['name'];
        }
        if (!empty($r['brand']['name']))
        {
            $content->manufacturer = $r['brand']['name'];
        }
        if (!empty($r['code']['ean']))
        {
            $content->ean = $r['code']['ean'];
        }
        if (!empty($r['code']['sku']))
        {
            $content->sku = $r['code']['sku'];
        }

        $content->extra = new ExtraDataKelkoo();
        ExtraDataKelkoo::fillAttributes($content->extra, $r);

        return $content;
    }

    public function doRequestItems(array $items)
    {
        $options = array();
        $options['pageSize'] = 1;
        $options['fieldsAlias'] = 'all';

        foreach ($items as $key => $item)
        {
            try
            {
                if (!empty($item['extra']['country']))
                {
                    $options['country'] = $item['extra']['country'];
                } else
                {
                    $options['country'] = $this->config('region');
                }
                $result = $this->getApiClient()->offer($item['unique_id'], $options);
            } catch (\Exception $e)
            {
                continue;
            }

            if (!$result || !isset($result['offers'][0]))
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                continue;
            }

            $product = $this->prepareResult($result['offers'][0]);

            $items[$key]['stock_status'] = $product->stock_status;
            $items[$key]['price'] = $product->price;
            $items[$key]['priceOld'] = $product->priceOld;
            $items[$key]['url'] = $product->url;
            $items[$key]['img'] = $product->img;
            $items[$key]['extra'] = ArrayHelper::object2Array($product->extra);
        }

        return $items;
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new KelkooApi($this->config('token'));
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
