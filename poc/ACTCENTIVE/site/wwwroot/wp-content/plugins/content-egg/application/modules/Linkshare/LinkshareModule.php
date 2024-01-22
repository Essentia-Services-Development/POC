<?php

namespace ContentEgg\application\modules\Linkshare;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\linkshare\LinkshareProductsRest;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * LinkshareModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class LinkshareModule extends AffiliateParserModule
{

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Linkshare',
            'description' => __('Adds products from Rakuten Linkshare. You must have approval from each program separately.', 'content-egg'),
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
            $options['max'] = $this->config('entries_per_page_update');
        } else
        {
            $options['max'] = $this->config('entries_per_page');
        }

        $invalid_characters = array(
            '&',
            '=',
            '?',
            '{',
            '}',
            '\\',
            '(',
            ')',
            '[',
            ']',
            '-',
            ';',
            '~',
            '|',
            '$',
            '!',
            '>',
            '*',
            '%'
        );
        $query = str_replace($invalid_characters, '', $keyword);

        if ($this->config('cat'))
        {
            $options['cat'] = $this->config('cat');
        }
        if ($this->config('mid'))
        {
            $options['mid'] = $this->config('mid');
        }
        if ($this->config('sort'))
        {
            $options['sort'] = $this->config('sort');
            $options['sorttype'] = $this->config('sorttype');
        }

        $results = $this->getApiClient()->search($query, $options, $this->config('search_logic'));
        if (!is_array($results) || !isset($results['item']))
        {
            return array();
        }

        if (!isset($results['item'][0]) && isset($results['item']['mid']))
        {
            $results['item'] = array($results['item']);
        }

        return $this->prepareResults($results['item']);
    }

    private function prepareResults($results)
    {
        $data = array();

        // filter sku dublicates
        if ($this->config('filter_duplicate'))
        {
            $urls = array();
            foreach ($results as $key => $r)
            {
                if ($murl = TextHelper::parseOriginalUrl($r['linkurl'], 'murl'))
                {
                    foreach ($urls as $url)
                    {
                        if ($murl == $url)
                        {
                            unset($results[$key]);
                            break;
                        }
                    }
                    $urls[] = $murl;
                }
            }
        }

        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;
            $content->unique_id = $r['linkid'];
            $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            $content->title = trim($r['productname']);

            if (is_array($r['saleprice']))
            {
                $content->price = (float) $r['saleprice'][0];
                $content->currencyCode = $r['saleprice']['@attributes']['currency'];
            } else
            {
                $content->price = (float) $r['saleprice'];
                $content->currencyCode = $r['currency'];
            }

            if (is_array($r['price']))
            {
                $content->priceOld = (float) $r['price'][0];
            } else
            {
                $content->priceOld = (float) $r['price'];
            }

            $content->currency = TextHelper::currencyTyping($content->currencyCode);
            $content->url = $r['linkurl'];
            $content->orig_url = TextHelper::parseOriginalUrl($r['linkurl'], 'murl');
            $content->img = (!empty($r['imageurl']) ) ? $r['imageurl'] : '';
            $content->merchant = ( $r['merchantname'] ) ? $r['merchantname'] : '';
            $content->domain = TextHelper::parseDomain($content->url, 'murl');

            if (!empty($r['description']['long']))
            {
                $content->description = $r['description']['long'];
            } elseif (!empty($r['description']['short']) && trim($r['description']['short']) != $content->title)
            {
                $content->description = $r['description']['short'];
            }
            if ($content->description)
            {
                $content->description = trim(strip_tags($content->description));
                if ($max_size = $this->config('description_size'))
                {
                    $content->description = TextHelper::truncate($content->description, $max_size);
                }
            }
            if (!empty($r['category']['primary']))
            {
                $content->category = $r['category']['primary'];
            }

            if (!$content->unique_id)
            {
                $content->unique_id = md5($content->title . $content->price);
            }

            $content->extra = new ExtraDataLinkshare;
            $content->extra->mid = ( $r['mid'] ) ? $r['mid'] : '';
            $content->extra->createdon = ( $r['createdon'] ) ? strtotime(str_replace('/', ' ', $r['createdon'])) : '';
            $content->sku = $content->extra->sku = ( $r['sku'] ) ? $r['sku'] : '';
            $content->upc = $content->extra->upccode = ( $r['upccode'] ) ? $r['upccode'] : '';
            $content->extra->keywords = ( $r['keywords'] ) ? $r['keywords'] : '';

            $data[] = $content;
        }

        return $data;
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new LinkshareProductsRest($this->config('token'));
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
