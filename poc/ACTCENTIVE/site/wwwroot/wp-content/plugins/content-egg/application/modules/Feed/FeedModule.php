<?php

namespace ContentEgg\application\modules\Feed;

defined('\ABSPATH') || exit;

use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\components\AffiliateFeedParserModule;
use ContentEgg\application\modules\Feed\FeedName;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\components\LinkHandler;

/**
 * FeedModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class FeedModule extends AffiliateFeedParserModule
{

    public function info()
    {
        if (!$name = FeedName::getInstance()->getName($this->getId()))
        {
            $name = __('Add new', 'content-egg');
        }

        return array(
            'name' => 'Feed:' . $name,
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/feed-modules',
        );
    }

    public function releaseVersion()
    {
        return '5.2.0';
    }

    public function isFree()
    {
        return true;
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function defaultTemplateName()
    {
        return 'data_grid';
    }

    public function isItemsUpdateAvailable()
    {
        return true;
    }

    public function isUrlSearchAllowed()
    {
        return true;
    }

    public function getProductModel()
    {
        $parts = explode('__', strtolower($this->getId()));
        $id = end($parts);
        $classname = '\\ContentEgg\\application\\modules\\Feed\\models\\MyFeed' . $id . 'ProductModel';

        if (!class_exists($classname, true))
        {
            throw new \Exception('Model class does not exist.');
        }

        return $classname::model();
    }

    public function isZippedFeed()
    {
        if ($this->config('archive_format') == 'zip')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getFeedUrl()
    {
        $url = $this->config('feed_url');

        if (!filter_var($url, FILTER_VALIDATE_URL))
        {
            throw new \Exception('Invalid Feed URL');
        }

        return $url;
    }

    protected function feedProductPrepare(array $data)
    {
        $mapped_data = $this->mapProduct($data);
        $missed = array();
        if (empty($mapped_data['description']))
            $mapped_data['description'] = '';

        foreach (array_keys(FeedConfig::mappingFields()) as $field)
        {
            if (FeedConfig::isMappingFieldRequared($field) && !isset($mapped_data[$field]))
            {
                $missed[] = $field;
            }
        }

        if ($missed)
        {
            throw new \Exception(sprintf('Required mapping fields are missing in the feed: %s.', join(', ', $missed)));
        }

        $product = array();
        $product['id'] = sanitize_text_field($mapped_data['id']);
        $product['title'] = \sanitize_text_field($mapped_data['title']);

        if (!$product['id'] || !$product['title'])
        {
            return false;
        }

        if (isset($mapped_data['sale price']) && (float) $mapped_data['sale price'])
        {
            $product['price'] = (float) TextHelper::parsePriceAmount($mapped_data['sale price']);
        }
        else
        {
            $product['price'] = (float) TextHelper::parsePriceAmount($mapped_data['price']);
        }

        $product['stock_status'] = ContentProduct::STOCK_STATUS_UNKNOWN;

        if (!empty($mapped_data['availability']))
        {
            $availability = strtolower($mapped_data['availability']);
            if (strstr($availability, 'out of stock') || strstr($availability, 'outofstock'))
            {
                $product['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
            else
            {
                $product['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            }
        }
        elseif (isset($mapped_data['is in stock']) && $mapped_data['is in stock'] !== '')
        {
            if (filter_var($mapped_data['is in stock'], FILTER_VALIDATE_BOOLEAN))
            {
                $product['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            }
            else
            {
                $product['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
        }

        if (isset($mapped_data['gtin']) && TextHelper::isEan($mapped_data['gtin']))
        {
            $product['ean'] = $mapped_data['gtin'];
        }
        else
        {
            $product['ean'] = '';
        }

        if (!empty($mapped_data['direct link']))
        {
            $product['orig_url'] = $mapped_data['direct link'];
        }
        elseif ($orig_url = TextHelper::findOriginalUrl($mapped_data['affiliate link']))
        {
            $product['orig_url'] = $orig_url;
        }
        else
        {
            $product['orig_url'] = $mapped_data['affiliate link'];
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
        }
        else
        {
            $limit = $this->config('entries_per_page');
        }

        if (TextHelper::isEan($keyword))
        {
            $results = $this->product_model->searchByEan($keyword, $limit);
        }
        elseif (filter_var($keyword, FILTER_VALIDATE_URL))
        {
            $results = $this->product_model->searchByUrl($keyword, $this->config('partial_url_match'), $limit);
        }
        else
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

            $options['search_type'] = $this->config('search_type');

            $results = $this->product_model->searchByKeyword($keyword, $limit, $options);
        }
        if (!$results)
        {
            return array();
        }

        return $this->prepareResults($results);
    }

    public function doRequestItems(array $items)
    {
        $this->maybeImportProducts();
        $deeplink = $this->config('deeplink');
        foreach ($items as $key => $item)
        {
            // fix
            if (!$key)
            {
                unset($items[$key]);
                continue;
            }

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

            $r = $this->mapProduct($r);

            if (isset($r['availability']))
            {
                $items[$key]['availability'] = $r['availability'];
            }

            $items[$key]['stock_status'] = $product['stock_status'];
            if (!empty($r['sale price']))
            {
                $items[$key]['price'] = (float) TextHelper::parsePriceAmount($r['sale price']);
                if (isset($r['price']) && (float) TextHelper::parsePriceAmount($r['price']) > $items[$key]['price'])
                {
                    $items[$key]['priceOld'] = (float) TextHelper::parsePriceAmount($r['price']);
                }
            }
            else
            {
                $items[$key]['price'] = (float) TextHelper::parsePriceAmount($r['price']);
                $items[$key]['priceOld'] = 0;
            }

            if ($deeplink)
            {
                $items[$key]['url'] = LinkHandler::createAffUrl($items[$key]['orig_url'], $deeplink, $item);
            }
        }

        return $items;
    }

    private function prepareResults($results)
    {
        $data = array();
        $deeplink = $this->config('deeplink');

        foreach ($results as $product)
        {
            if (!$r = unserialize($product['product']))
            {
                continue;
            }

            $r = $this->mapProduct($r);

            $content = new ContentProduct;

            $content->unique_id = $r['id'];
            $content->title = $r['title'];
            $content->url = $r['affiliate link'];

            if (!empty($r['sale price']))
            {
                $content->price = (float) TextHelper::parsePriceAmount($r['sale price']);
                if (isset($r['price']) && (float) TextHelper::parsePriceAmount($r['price']) > $content->price)
                {
                    $content->priceOld = (float) TextHelper::parsePriceAmount($r['price']);
                }
            }
            else
            {
                $content->price = (float) TextHelper::parsePriceAmount($r['price']);
            }

            if ($content->price)
            {
                $content->price = round($content->price, 2);
            }

            if ($content->priceOld)
            {
                $content->priceOld = round($content->priceOld, 2);
            }

            if (isset($r['currency']) && strlen($r['currency']))
            {
                $content->currencyCode = $r['currency'];
            }
            else
            {
                $content->currencyCode = $this->config('currency');
            }

            if (!empty($r['description']) && $r['title'] != $r['description'])
            {
                $content->description = $r['description'];
            }

            if (isset($r['brand']))
            {
                $content->manufacturer = $r['brand'];
            }

            if (isset($r['category']))
            {
                $content->category = $r['category'];

                if (strstr($r['category'], '>'))
                {
                    $content->categoryPath = explode('>', $content->category);
                    $content->categoryPath = array_map('trim', $content->categoryPath);
                    $content->category = end($content->categoryPath);
                }
            }

            if (isset($r['availability']))
            {
                $content->availability = $r['availability'];
            }
            if (isset($r['image ​​link']) && filter_var($r['image ​​link'], FILTER_VALIDATE_URL))
            {
                $content->img = $r['image ​​link'];
            }

            $content->orig_url = $product['orig_url'];
            $content->stock_status = $product['stock_status'];
            $content->ean = $product['ean'];

            if ($content->orig_url != $content->url)
            {
                $content->domain = TextHelper::getHostName($content->orig_url);
            }
            else
            {
                $content->domain = $this->config('domain');
            }

            if ($deeplink)
            {
                $content->url = LinkHandler::createAffUrl($content->orig_url, $deeplink, (array) $content);
            }

            $data[] = $content;
        }

        return $data;
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

    public function viewDataPrepare($data)
    {
        if (!$deeplink = $this->config('deeplink'))
        {
            return parent::viewDataPrepare($data);
        }

        foreach ($data as $key => $d)
        {
            $data[$key]['url'] = LinkHandler::createAffUrl($d['orig_url'], $deeplink, $d);
        }

        return parent::viewDataPrepare($data);
    }

    public function mapProduct(array $data)
    {
        $mapped_data = array();
        $mapping = $this->config('mapping');
        foreach ($mapping as $field => $feed_field)
        {
            if (isset($data[$feed_field]))
            {
                $mapped_data[$field] = $data[$feed_field];
            }
        }

        return $mapped_data;
    }
}
