<?php

namespace ContentEgg\application\modules\AE;

defined('\ABSPATH') || exit;

use ContentEgg\application\admin\AeIntegrationConfig;
use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;
use ContentEgg\application\components\ContentManager;
use \Keywordrush\AffiliateEgg\ParserManager;

/**
 * AEModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AEModule extends AffiliateParserModule {

    public function __construct($module_id = null)
    {
        if (!AeIntegrationConfig::isAEIntegrationPosible())
        {
            throw new \Exception('The required Affiliate Egg plugin is not installed.');
        }

        parent::__construct($module_id);
    }

    public function info()
    {
        $name = \Keywordrush\AffiliateEgg\ShopManager::getInstance()->getShopName($this->getMyShortId());
        $uri = $this->getShopHost();
        $shop = \Keywordrush\AffiliateEgg\ShopManager::getInstance()->getItem($this->getMyShortId());
        if ($shop && method_exists($shop, 'isDeprecated') && $shop->isDeprecated())
        {
            $name .= ' (deprecated)';
        }

        return array(
            'name' => 'AE:' . $name,
            'description' => sprintf(__('Affiliate Egg parser for %s', 'content-egg'), $uri),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate-egg-integration#avoid-getting-blocked',
        );
    }

    public function getShopHost()
    {
        $uri = \Keywordrush\AffiliateEgg\ShopManager::getInstance()->getShopUri($this->getMyShortId());
        $uri = str_replace('http://', '', $uri);
        $uri = str_replace('https://', '', $uri);
        $uri = str_replace('www.', '', $uri);
        $uri = strtolower($uri);
        $uri = trim($uri, "/");

        return $uri;
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

    public function isFree()
    {
        return true;
    }

    public function isUrlSearchAllowed()
    {
        return true;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        if ($is_autoupdate)
        {
            $entries_per_page = $this->config('entries_per_page_update');
        } else
        {
            $entries_per_page = $this->config('entries_per_page');
        }

        $results = array();

        $is_url_passed = false;
        $is_catalog_url_passed = false;
        // Catalog url?
        if ($keyword[0] == '[' && preg_match('/^\[catalog(.*?)\](.+)/', $keyword, $matches))
        {
            $catalog_default = array(
                'limit' => $entries_per_page,
            );
            if ($matches[1])
            {
                $catalog_atts = \shortcode_atts($catalog_default, \shortcode_parse_atts($matches[1]));
            } else
            {
                $catalog_atts = $catalog_default;
            }
            $keyword = trim($matches[2]);
            $entries_per_page = (int) $catalog_atts['limit'];
            $is_catalog_url_passed = true;
        }

        // 1. Url passed?
        $is_url_passed = filter_var($keyword, FILTER_VALIDATE_URL) && TextHelper::getDomainWithoutSubdomain($this->getShopHost()) == TextHelper::getHostName($keyword);

        try
        {
            if ($is_url_passed)
            {
                $url = $keyword;
                // parse product by url
                if (!$is_catalog_url_passed)
                {
                    $results[] = ParserManager::getInstance()->parseProduct($url);
                    if ($results)
                    {
                        return $this->prepareResults($results);
                    }
                }

                // try parse catalog
                $product_urls = ParserManager::getInstance()->parseCatalog($url, $entries_per_page);
                if (!$product_urls)
                {
                    return array();
                }
            }

            // 2. Parse catalog
            if (!$is_url_passed)
            {
                $product_urls = ParserManager::getInstance()->parseSearchCatalog($this->getMyShortId(), $keyword, $entries_per_page);
                if (!$product_urls || !is_array($product_urls))
                {
                    return array();
                }
            }
        } catch (\Exception $e)
        {
            $code = $e->getCode();
            $message = $e->getMessage();
            if (in_array($code, array(503, 403)))
            {
                $message .= '. For more information please refer to https://ce-docs.keywordrush.com/modules/affiliate-egg-integration#avoid-getting-blocked';
            }

            throw new \Exception($message, $code);
        }

        // 3. Parse products
        $product_sleep = \Keywordrush\AffiliateEgg\GeneralConfig::getInstance()->option('product_sleep');
        foreach ($product_urls as $key => $url)
        {
            try
            {
                $results[] = ParserManager::getInstance()->parseProduct($url);
            } catch (\Exception $e)
            {
                continue;
            }

            // sleep
            if ($product_sleep && $key < count($product_urls) - 1)
            {
                usleep($product_sleep);
            }
        }

        return $this->prepareResults($results);
    }

    private function prepareResults($results)
    {
        $data = array();
        $deeplink = $this->config('deeplink');

        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;
            $content->unique_id = md5($r['orig_url']);

            $content->orig_url = $r['orig_url'];
            $content->domain = TextHelper::getHostName($r['orig_url']);
            //$content->merchant = TemplateHelper::getNameFromDomain($content->domain);
            $content->img = $r['img'];
            $content->title = $r['title'];
            $content->description = $r['description'];
            $content->price = $r['price'];
            $content->priceOld = $r['old_price'];
            $content->currencyCode = $r['currency'];
            $content->currency = TextHelper::currencyTyping($content->currencyCode);
            $content->manufacturer = $r['manufacturer'];
            $content->availability = $r['in_stock'];
            if ($r['in_stock'])
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            } else
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }

            $content->url = LinkHandler::createAffUrl($r['orig_url'], $deeplink, (array) $content);
            $content->extra = new ExtraDataAE;

            if (isset($r['extra']['rating']))
            {
                $content->rating = $r['extra']['rating'];
                unset($r['extra']['rating']);
            }

            if (isset($r['extra']['features']))
            {
                foreach ($r['extra']['features'] as $f)
                {
                    $feature = array(
                        'name' => $f['name'],
                        'value' => $f['value'],
                    );
                    $content->features[] = $feature;
                }
                unset($r['extra']['features']);
            }
            if (isset($r['extra']['comments']))
            {
                $content->extra->comments = $r['extra']['comments'];
                unset($r['extra']['comments']);
            }
            if (isset($r['extra']['images']))
            {
                $content->extra->images = $r['extra']['images'];
                unset($r['extra']['images']);
            }
            if (isset($r['extra']['category']))
            {
                $content->category = $r['extra']['category'];
                unset($r['extra']['category']);
            }

            if (isset($r['extra']['categoryPath']))
            {
                $content->categoryPath = $r['extra']['categoryPath'];
                unset($r['extra']['categoryPath']);
            }

            $content->extra->data = $r['extra'];

            $data[] = $content;
        }

        return $data;
    }

    public function doRequestItems(array $items)
    {
        $key = 0;
        $product_sleep = \Keywordrush\AffiliateEgg\GeneralConfig::getInstance()->option('product_update_sleep');
        foreach ($items as $i => $item)
        {
            if ($product_sleep && $key > 0)
            {
                usleep($product_sleep);
            }
            $key++;

            try
            {
                $r = ParserManager::getInstance()->parseProduct($item['orig_url']);
            } catch (\Exception $e)
            {
                if ($e->getCode() == 404 || $e->getCode() == 410)
                {
                    $items[$i]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                    $items[$i]['availability'] = $items[$i]['stock_status'];
                }
                continue;
            }

            $items[$i]['price'] = $r['price'];
            $items[$i]['priceOld'] = $r['old_price'];
            $items[$i]['currencyCode'] = $r['currency'];
            $items[$i]['currency'] = TextHelper::currencyTyping($items[$i]['currencyCode']);
            $items[$i]['availability'] = $r['in_stock'];
            if ($r['in_stock'])
            {
                $items[$i]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            } else
            {
                $items[$i]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
            if (isset($r['rating']))
            {
                $items[$i]['rating'] = $r['rating'];
            }

            // update url if deeplink changed
            $items[$i]['url'] = LinkHandler::createAffUrl($r['orig_url'], $this->config('deeplink'), $item);

            // update image (amazon)
            if (!$this->config('save_img') && $r['img'])
            {
                $items[$i]['img'] = $r['img'];
            }

            $items[$i] = \apply_filters('cegg_ae_module_product_update', $items[$i], $r);
        }

        return $items;
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

    public function viewDataPrepare($data)
    {
        $deeplink = $this->config('deeplink');
        foreach ($data as $key => $d)
        {
            $data[$key]['url'] = LinkHandler::createAffUrl($d['orig_url'], $deeplink, $d);
        }

        return parent::viewDataPrepare($data);
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
