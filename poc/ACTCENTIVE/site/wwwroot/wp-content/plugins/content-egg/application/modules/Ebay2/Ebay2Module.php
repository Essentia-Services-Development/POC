<?php

namespace ContentEgg\application\modules\Ebay2;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\ebay\EbayBrowse;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\components\ExtraData;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;
use ContentEgg\application\helpers\TemplateHelper;

/**
 * Ebay2Module class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class Ebay2Module extends AffiliateParserModule
{

    private $api_client_browse = null;

    public function info()
    {
        return array(
            'name' => 'Ebay',
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/ebay',
        );
    }

    public function releaseVersion()
    {
        return '9.3.0';
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function defaultTemplateName()
    {
        return 'data_list';
    }

    public function isItemsUpdateAvailable()
    {
        return true;
    }

    public function isUrlSearchAllowed()
    {
        return true;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $options = array();

        $locale = $this->getCurrentLocale($query_params);

        if ($is_autoupdate)
        {
            $options['limit'] = $this->config('entries_per_page_update');
        }
        else
        {
            $options['limit'] = $this->config('entries_per_page');
        }

        $options['fieldgroups'] = 'EXTENDED'; // This returns the shortDescription field

        if ($category_id = $this->config('category_id'))
        {
            $options['category_ids'] = TextHelper::commaList($category_id);
        }

        if ($sort_order = $this->config('sort_order'))
        {
            $options['sort'] = $sort_order;
        }

        // Filters
        // @link https://developer.ebay.com/api-docs/buy/static/ref-buy-browse-filters.html

        $filters = array();

        if ($filter = $this->getFilterPrice($query_params))
        {
            $filters['price'] = $filter;
            $filters['priceCurrency'] = Ebay2Config::getCurrencyByLocale($locale);
        }

        if ($filter = $this->getFilterBidCount())
        {
            $filters['bidCount'] = $filter;
        }

        if ($filter = $this->getFilterConditionIds($query_params))
        {
            $filters['conditionIds'] = $filter;
        }

        if ($buying_options = $this->config('buying_options'))
        {
            $filters['buyingOptions'] = '{' . join('|', $buying_options) . '}';
        }

        if ($condition = $this->config('condition'))
        {
            $filters['condition'] = '{' . $condition . '}';
        }

        if ($available_to = $this->config('available_to'))
        {
            $filters['deliveryCountry'] = strtoupper($available_to);
        }

        if ($this->config('local_pickup_only'))
        {
            $filters['deliveryOptions'] = '{SELLER_ARRANGED_LOCAL_PICKUP}';
        }

        if ($delivery_postal_code = $this->config('delivery_postal_code'))
        {
            $filters['deliveryPostalCode'] = $delivery_postal_code;
        }

        if ($exclude_sellers = $this->config('exclude_sellers'))
        {
            $filters['excludeSellers'] = '{' . TextHelper::commaList($exclude_sellers, ',', '|') . '}';
        }

        if ($sellers = $this->config('sellers'))
        {
            $filters['sellers'] = '{' . TextHelper::commaList($sellers, ',', '|') . '}';
        }

        if ($exclude_category_ids = $this->config('exclude_category_ids'))
        {
            $filters['excludeCategoryIds'] = '{' . TextHelper::commaList($exclude_category_ids, ',', '|') . '}';
        }

        if ($location_country = $this->config('location_country'))
        {
            $filters['itemLocationCountry'] = strtoupper($location_country);
        }

        if ($this->config('free_shipping_only'))
        {
            $filters['maxDeliveryCost'] = '0';
        }

        if ($this->config('payment_methods'))
        {
            $filters['paymentMethods'] = '{CREDIT_CARD}';
        }

        if ($this->config('returns_accepted'))
        {
            $filters['returnsAccepted'] = 'true';
        }

        if ($this->config('description_search'))
        {
            $filters['searchInDescription'] = 'true';
        }

        if ($this->config('priority_listing') == 'enabled' && in_array($locale, array(
            'EBAY_US',
            'EBAY_DE',
            'EBAY_FR',
            'EBAY_GB',
            'EBAY_IT',
            'EBAY_ES',
            'EBAY_AU',
            'EBAY_CA'
        )))
        {
            $filters['priorityListing'] = 'true';
        }

        if ($filters)
        {
            $nv = array();
            foreach ($filters as $name => $value)
            {
                $nv[] = $name . ':' . $value;
            }
            $options['filter'] = join(',', $nv);
        }

        $client = $this->getEbayClientBrowse();
        $client->setAccessToken($this->getAccessToken());

        $results = array();

        if ($items = $this->searchByEan($keyword, $options, $query_params))
            $results = $items;
        elseif ($item = $this->searchById($keyword, $query_params))
            $results['itemSummaries'] = array($item);
        elseif ($items = $this->searchByEpid($keyword, $options, $query_params))
            $results = $items;
        else
            $results = $client->search($keyword, $options, $this->getHeaders($query_params));

        if (!is_array($results) || !isset($results['itemSummaries']))
            return array();

        return $this->prepareResults($results['itemSummaries'], $locale);
    }

    private function searchById($keyword, $query_params)
    {
        if ($pid = self::parsePidFromUrl($keyword))
            $keyword = $pid;

        if (!preg_match('~\d{12,13}~', $keyword))
            return false;

        $client = $this->getEbayClientBrowse();
        $client->setAccessToken($this->getAccessToken());
        $item = null;

        try
        {
            $item = $client->getItemByLegacyId($keyword, array(), $this->getHeaders($query_params));
        }
        catch (\Exception $e)
        {
            // parent product
            if (strstr($e->getMessage(), 'get_items_by_item_group'))
            {
                $items = $client->getItemsByItemGroup($keyword, array(), $this->getHeaders($query_params));
                if ($items && isset($items['items']))
                    $keyword = $items['items'][0]['itemId'];

                $item = $client->getItem($keyword, array(), $this->getHeaders($query_params));
            }
        }

        if ($item)
            return $item;
        else
            return false;
    }

    private function searchByEpid($keyword, $options, $query_params)
    {
        if (!preg_match('~\d{8,11}~', $keyword))
            return false;

        $client = $this->getEbayClientBrowse();
        $client->setAccessToken($this->getAccessToken());

        try
        {
            $items = $client->searchByEpid($keyword, $options, $this->getHeaders($query_params));
        }
        catch (\Exception $e)
        {
            return false;
        }

        if ($items)
            return $items;
        else
            return false;
    }

    private function searchByEan($keyword, $options, $query_params)
    {
        if (!TextHelper::isEan($keyword))
            return false;

        $client = $this->getEbayClientBrowse();
        $client->setAccessToken($this->getAccessToken());

        try
        {
            $items = $client->searchByGtin($keyword, $options, $this->getHeaders($query_params));
        }
        catch (\Exception $e)
        {
            return false;
        }

        if (!$items || !isset($items['itemSummaries']))
            return false;

        return $items;
    }

    private function getHeaders($query_params)
    {
        $locale = $this->getCurrentLocale($query_params);
        $headers = array(
            'X-EBAY-C-MARKETPLACE-ID' => $locale,
        );

        if ($tracking_id = $this->config('tracking_id'))
        {
            $headers['X-EBAY-C-ENDUSERCTX'] = 'affiliateCampaignId=' . $tracking_id;
            if ($custom_id = $this->config('custom_id'))
            {
                $headers['X-EBAY-C-ENDUSERCTX'] .= ',affiliateReferenceId=' . $custom_id;
            }
        }

        return $headers;
    }

    private function getCurrentLocale($query_params)
    {
        if (!empty($query_params['locale']))
        {
            return $query_params['locale'];
        }
        else
        {
            return $this->config('locale');
        }
    }

    private function getFilterBidCount()
    {
        if (!$min_bids = $this->config('min_bids'))
        {
            $min_bids = '';
        }

        if (!$max_bids = $this->config('max_bids'))
        {
            $max_bids = '';
        }

        if (!$min_bids && !$max_bids)
        {
            return '';
        }

        $filter = '[';
        if ($min_bids)
        {
            $filter .= $min_bids;
        }
        if ($max_bids)
        {
            $filter .= '..' . $max_bids;
        }

        $filter .= ']';

        return $filter;
    }

    private function getFilterPrice($query_params)
    {
        if (!empty($query_params['min_price']))
        {
            $min_price = $query_params['min_price'];
        }
        else
        {
            $min_price = $this->config('min_price');
        }

        if (!empty($query_params['max_price']))
        {
            $max_price = $query_params['max_price'];
        }
        else
        {
            $max_price = $this->config('max_price');
        }

        if (!$min_price)
        {
            $min_price = '';
        }

        if (!$max_price)
        {
            $max_price = '';
        }

        if (!$min_price && !$max_price)
        {
            return '';
        }

        $filter = '[';
        if ($min_price)
        {
            $filter .= $min_price;
        }
        if ($max_price)
        {
            $filter .= '..' . $max_price;
        }

        $filter .= ']';

        return $filter;
    }

    private function getFilterConditionIds($query_params)
    {
        // from autoblog
        if (isset($query_params['product_condition']))
        {
            $conditions = array(
                'new' => 1000,
                'used' => 3000,
                'refurbished' => 2000,
                'seller_refurbished' => 2500,
                'new_other' => 1500,
                'for_parts' => 7000,
            );

            if (isset($conditions[$query_params['product_condition']]))
            {
                return '{' . $conditions[$query_params['product_condition']] . '}';
            }
        }

        if ($condition_ids = $this->config('condition_ids'))
        {
            return '{' . TextHelper::commaList($condition_ids, ',', '|') . '}';
        }

        return false;
    }

    public function doRequestItems(array $items)
    {
        $client = $this->getEbayClientBrowse();
        $client->setAccessToken($this->getAccessToken());

        $options = array();
        foreach ($items as $unique_id => $item)
        {
            $locale = $item['extra']['locale'];
            $query_params = array(
                'locale' => $locale,
            );

            try
            {
                $r = $client->getItem($unique_id, $options, $this->getHeaders($query_params));
            }
            catch (\Exception $e)
            {
                if ($e->getCode() == 404)
                {
                    $items[$unique_id]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                }

                continue;
            }

            if (!is_array($r) || !isset($r['itemId']))
            {
                continue;
            }

            $new = $this->prepareResult($r, $locale);

            // assign new data
            $items[$unique_id]['price'] = $new->price;
            $items[$unique_id]['priceOld'] = $new->priceOld;
            $items[$unique_id]['currencyCode'] = $new->currencyCode;
            $items[$unique_id]['url'] = $new->url;
            $items[$unique_id]['orig_url'] = $new->orig_url;
            $items[$unique_id]['stock_status'] = $new->stock_status;
            $items[$unique_id]['img'] = $new->img;
            $items[$unique_id]['extra']['pricePerUnitDisplay'] = $new->extra->pricePerUnitDisplay;
        }

        return $items;
    }

    private function prepareResults($results, $locale)
    {
        $data = array();

        foreach ($results as $r)
        {
            $data[] = $this->prepareResult($r, $locale);
        }

        return $data;
    }

    private function prepareResult($r, $locale)
    {
        $content = new ContentProduct;
        $content->unique_id = $r['itemId'];
        $content->title = strip_tags($r['title']);
        $content->merchant = 'eBay';
        $content->orig_url = $r['itemWebUrl'];
        $content->domain = TextHelper::getHostName($content->orig_url);
        $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;

        if (isset($r['itemAffiliateWebUrl']))
        {
            $content->url = $r['itemAffiliateWebUrl'];
        }
        elseif ($deeplink = $this->config('deeplink'))
        {
            $content->url = LinkHandler::createAffUrl($content->orig_url, $deeplink);
        }
        else
        {
            $content->url = $content->orig_url;
        }

        if (isset($r['shortDescription']))
        {
            $content->description = $r['shortDescription'];
        }

        if ($this->config('image_size') == 'large' && isset($r['thumbnailImages'][0]['imageUrl']))
        {
            $content->img = $r['thumbnailImages'][0]['imageUrl'];
        }
        elseif (isset($r['image']['imageUrl']))
        {
            $content->img = $r['image']['imageUrl'];
        }

        $content->price = (float) $r['price']['value'];
        $content->currencyCode = $r['price']['currency'];
        $content->currency = TextHelper::currencyTyping($content->currencyCode);

        if (isset($r['marketingPrice']['originalPrice']))
        {
            $content->priceOld = (float) $r['marketingPrice']['originalPrice']['value'];
        }

        if (isset($r['estimatedAvailabilities'][0]['estimatedAvailabilityStatus']) && $r['estimatedAvailabilities'][0]['estimatedAvailabilityStatus'] == 'OUT_OF_STOCK')
        {
            $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
        }

        if (isset($r['itemEndDate']) && strtotime($r['itemEndDate']) <= time())
        {
            $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
        }

        if (isset($r['categoryPath']))
        {
            $content->categoryPath = explode('|', $r['categoryPath']);
            $content->category = end($content->categoryPath);
        }

        $extra = new ExtraDataEbay2;
        $extra->locale = $locale;
        ExtraData::fillAttributes($extra, $r);

        if (isset($r['unitPrice']) && isset($r['unitPricingMeasure']))
        {
            $extra->unitPriceType = $r['unitPricingMeasure'];
            $extra->unitPrice = (float) $r['unitPrice']['value'];
            $extra->unitPriceCurrency = $r['unitPrice']['currency'];
            $extra->pricePerUnitDisplay = TemplateHelper::formatPriceCurrency($extra->unitPrice, $extra->unitPriceCurrency) . ' / ' . $extra->unitPriceType;
        }

        if (isset($r['additionalImages']) && is_array($r['additionalImages']))
        {
            foreach ($r['additionalImages'] as $ai)
            {
                $extra->images[] = $ai['imageUrl'];
            }
        }

        $extra->priorityListing = filter_var($extra->priorityListing, FILTER_VALIDATE_BOOLEAN);

        if (isset($r['shippingOptions']) && $r['shippingOptions'][0]['shippingCostType'] == 'FIXED' && !(float) $r['shippingOptions'][0]['shippingCost']['value'])
        {
            $extra->IsEligibleForSuperSaverShipping = true;
        }

        $content->extra = $extra;

        return $content;
    }

    private function getEbayClientBrowse()
    {
        if ($this->api_client_browse === null)
        {
            $app_id = $this->config('app_id');
            $cert_id = $this->config('cert_id');
            $this->api_client_browse = new EbayBrowse($app_id, $cert_id);
        }

        return $this->api_client_browse;
    }

    public function requestAccessToken()
    {
        $api_client = $this->getEbayClientBrowse();
        $response = $api_client->requestAccessToken();

        if (empty($response['access_token']) || empty($response['expires_in']))
        {
            throw new \Exception('Ebay Browse API: Invalid Response Format.');
        }

        return array($response['access_token'], (int) $response['expires_in']);
    }

    public function viewDataPrepare($data)
    {
        if ($deeplink = $this->config('deeplink'))
        {
            foreach ($data as $key => $d)
            {
                $data[$key]['url'] = LinkHandler::createAffUrl($d['orig_url'], $deeplink);
            }
        }

        return parent::viewDataPrepare($data);
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

    public static function parsePidFromUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            return false;

        $regex = '~itm\/(\d{12,13})~';
        if (preg_match($regex, $url, $matches))
            return $matches[1];
        else
            return false;
    }
}
