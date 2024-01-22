<?php

namespace ContentEgg\application\modules\Offer;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\components\LinkHandler;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\ContentProduct;

/**
 * OfferModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class OfferModule extends AffiliateParserModule {

    public static $globals = null;

    public function info()
    {
        return array(
            'name' => 'Offer',
            'description' => __('Manually create offer from any site with price update.', 'content-egg'),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/offermodule',
        );
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function releaseVersion()
    {
        return '3.0.0';
    }

    public function defaultTemplateName()
    {
        return 'data_grid';
    }

    public function isFree()
    {
        return true;
    }

    public function isItemsUpdateAvailable()
    {
        return true;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        return array();
    }

    public function doRequestItems(array $items)
    {
        $parser = new OfferParser();
        foreach ($items as $key => $item)
        {
            $items[$key]['extra']['last_error'] = '';
            if (isset($item['extra']['priceXpath']))
            {
                $custom = $item['extra']['priceXpath'];
            } else
            {
                $custom = '';
            }

            if (!isset($item['extra']['priceXpath']))
            {
                $item['extra']['priceXpath'] = '';
            }
            if (!$priceXpath = $this->getXpath($item['domain'], $item['extra']['priceXpath']))
            {
                continue;
            }

            if ($item['orig_url'])
            {
                $url = $item['orig_url'];
            } elseif ($item['url'])
            {
                $url = $item['url'];
            } else
            {
                continue;
            }

            $priceXpath = explode('%DELIMITER%', $priceXpath);
            try
            {
                $parser->setUrl($url);
                $price = $parser->xpathScalar($priceXpath);
            } catch (\Exception $e)
            {
                if ($e->getCode() == 404)
                {
                    $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                }

                $items[$key]['extra']['last_error'] = $e->getMessage();
                continue;
            }

            if ($items[$key]['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            }

            if (!$price)
            {
                $items[$key]['extra']['last_error'] = 'XPath is unable to find price value.';
                continue;
            }

            // assign new price
            $items[$key]['price'] = (float) TextHelper::parsePriceAmount($price);
        }

        return $items;
    }

    public function presavePrepare($data, $post_id)
    {
        $data = parent::presavePrepare($data, $post_id);
        $return = array();
        foreach ($data as $key => $item)
        {
            $item['title'] = trim(\sanitize_text_field($item['title']));
            $item['description'] = trim(\wp_kses_post($item['description']));
            $item['orig_url'] = trim(strip_tags($item['orig_url']));
            $item['img'] = trim(strip_tags($item['img']));
            $item['logo'] = trim(strip_tags($item['logo']));
            $item['extra']['deeplink'] = isset($item['extra']['deeplink']) ? trim(strip_tags($item['extra']['deeplink'])) : '';
            $item['price'] = (float) TextHelper::parsePriceAmount($item['price']);
            $item['priceOld'] = (float) TextHelper::parsePriceAmount($item['priceOld']);
            $item['rating'] = TextHelper::ratingPrepare($item['rating']);

            if (!$item['domain'])
            {
                if (!$item['extra']['deeplink'] && $original_domain = TextHelper::findOriginalDomain($item['orig_url']))
                {
                    $item['domain'] = $original_domain;
                } else
                {
                    $item['domain'] = TextHelper::getHostName($item['orig_url']);
                }
            }

            if (!$item['title'])
            {
                continue;
            }
            if (!filter_var($item['orig_url'], FILTER_VALIDATE_URL))
            {
                continue;
            }
            if ($item['img'] && !filter_var($item['img'], FILTER_VALIDATE_URL))
            {
                continue;
            }

            $deeplink = $this->getDeeplink($item['domain'], $item['extra']['deeplink']);
            $item['url'] = LinkHandler::createAffUrl($item['orig_url'], $deeplink, $item);
            $return[$key] = $item;
        }

        return $return;
    }

    public function renderResults()
    {
        PluginAdmin::render('_metabox_results', array('module_id' => $this->getId()));
    }

    public function renderMetaboxModule()
    {
        $this->render('metabox_module', array('module_id' => $this->getId(), 'module' => $this));
    }

    public function viewDataPrepare($data)
    {
        foreach ($data as $key => $d)
        {
            $deeplink = $this->getDeeplink($d['domain'], $d['extra']['deeplink']);
            $data[$key]['url'] = LinkHandler::createAffUrl($d['orig_url'], $deeplink, $d);
        }

        return parent::viewDataPrepare($data);
    }

    public function getDeeplink($domain, $custom = '')
    {
        return $this->getGlobalCustomValue('deeplink', $domain, $custom);
    }

    public function getXpath($domain, $custom = '')
    {
        return trim($this->getGlobalCustomValue('xpath', $domain, $custom));
    }

    public function getGlobal($domain)
    {
        if (self::$globals === null)
        {
            self::$globals = array();
            $globals = $this->config('global');
            foreach ($globals as $global)
            {
                self::$globals[$global['domain']] = $global;
            }
        }

        if (isset(self::$globals[$domain]))
        {
            return self::$globals[$domain];
        } else
        {
            return false;
        }
    }

    public function getGlobalCustomValue($option, $domain, $custom = '')
    {
        if (!$global = $this->getGlobal($domain))
        {
            return $custom;
        }

        if ($custom && (!isset($global['in_priority']) || !(bool) $global['in_priority'] ))
        {
            return $custom;
        }

        if (isset($global[$option]))
        {
            return $global[$option];
        } else
        {
            return null;
        }
    }

}
