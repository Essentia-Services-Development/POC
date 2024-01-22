<?php

namespace ContentEgg\application\modules\AmazonNoApi;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\amazon\Adsystem;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\amazon\AmazonLocales;
use ContentEgg\application\modules\Amazon\AmazonModule;
use ContentEgg\application\modules\AmazonNoApi\ExtraDataAmazonNoApi;
use ContentEgg\application\helpers\ArrayHelper;

/**
 * AmazonNoApiModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AmazonNoApiModule extends AffiliateParserModule {

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Amazon No API',
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/amazon-no-api-module',
        );
    }

    public function releaseVersion()
    {
        return '9.2.0';
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function defaultTemplateName()
    {
        return 'data_item';
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
        if (!empty($query_params['locale']) && AmazonLocales::getLocale($query_params['locale']))
        {
            $locale = $query_params['locale'];
        } else
        {
            $locale = $this->config('locale');
        }

        if ($is_autoupdate)
        {
            $limit = $this->config('entries_per_page_update');
        } else
        {
            $limit = $this->config('entries_per_page');
        }

        if ($asin = AmazonModule::parseAsinFromUrl($keyword))
        {
            $keyword = $asin;
        }

        $client = $this->getApiClient();
        $client->setLocale($locale);
        $results = $client->search($keyword);

        if (!isset($results['results']) || !is_array($results['results']))
        {
            return array();
        }

        return $this->prepareResults(array_slice($results['results'], 0, $limit), $locale);
    }

    public function doRequestItems(array $items)
    {
        foreach ($items as $key => $item)
        {
            $client = $this->getApiClient();
            $client->setLocale($item['extra']['locale']);

            try
            {
                $result = $client->getItem($item['extra']['ASIN']);
            } catch (\Exception $e)
            {
                continue;
            }

            if (!$result)
            {
                //$items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                continue;
            }

            $product = $this->prepareResult($result, $item['extra']['locale']);

            $items[$key]['price'] = $product->price;
            $items[$key]['priceOld'] = $product->priceOld;
            $items[$key]['url'] = $product->url;
            $items[$key]['img'] = $product->img;
            $items[$key]['extra'] = ArrayHelper::object2Array($product->extra);
        }

        return $items;
    }

    private function prepareResults($results, $locale)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            $data[] = $this->prepareResult($r, $locale);
        }

        return $data;
    }

    private function prepareResult($r, $locale)
    {
        $content = new ContentProduct;
        $extra = new ExtraDataAmazonNoApi;

        $associate_tag = $this->getAssociateTagForLocale($locale);

        $content->unique_id = $locale . '-' . $r['ASIN'];
        $content->stock_status = ContentProduct::STOCK_STATUS_UNKNOWN;
        $r['Price'] = preg_replace('/\(.+\)/', '', $r['Price']);
        $r['ListPrice'] = preg_replace('/\(.+\)/', '', $r['ListPrice']);
        $content->price = TextHelper::parsePriceAmount($r['Price']);
        $content->priceOld = TextHelper::parsePriceAmount($r['ListPrice']);
        $content->currencyCode = AmazonLocales::getCurrencyCode($locale);
        $content->orig_url = $r['DetailPageURL'];
        $content->title = $r['Title'];
        $content->img = preg_replace('/\._\w+\d+_\.jpg/', '.jpg', $r['ImageUrl']);
        $content->domain = AmazonNoApiConfig::getDomainByLocale($locale);
        $content->merchant = ucfirst($content->domain);
        $content->manufacturer = $r['Subtitle'];
        $extra->IsPrimeEligible = filter_var($r['IsPrimeEligible'], FILTER_VALIDATE_BOOLEAN);
        $extra->ASIN = $r['ASIN'];
        $extra->locale = $locale;
        $extra->associate_tag = $associate_tag;
        $extra->addToCartUrl = $this->generateAddToCartUrl($locale, $extra->ASIN);

        if ($this->config('link_type') == 'add_to_cart')
        {
            $content->url = $extra->addToCartUrl;
        } else
        {
            $content->url = \add_query_arg('tag', $this->getAssociateTagForLocale($locale), $content->orig_url);
        }

        ExtraDataAmazonNoApi::fillAttributes($extra, $r);

        $content->extra = $extra;

        return $content;
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new Adsystem();
        }

        return $this->api_client;
    }

    public function getAssociateTagForLocale($locale)
    {
        if ($locale == $this->config('locale'))
        {
            return $this->config('associate_tag');
        } else
        {
            return $this->config('associate_tag_' . $locale);
        }
    }

    /**
     * Add to shopping cart url
     * @link: https://webservices.amazon.com/paapi5/documentation/add-to-cart-form.html
     * @link: https://affiliate-program.amazon.com/help/node/topic/G9SMD8TQHFJ7728F
     */
    private function getAmazonAddToCartUrl($locale)
    {
        return 'https://www.' . AmazonNoApiConfig::getDomainByLocale($locale) . '/gp/aws/cart/add.html';
    }

    public function renderResults()
    {
        PluginAdmin::render('_metabox_results', array('module_id' => $this->getId()));
    }

    public function renderSearchResults()
    {
        PluginAdmin::render('_metabox_search_results', array('module_id' => $this->getId()));
    }

    public function renderSearchPanel()
    {
        $this->render('search_panel', array('module_id' => $this->getId()));
    }

    private static function parseAsinFromUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
        {
            return false;
        }
        $regex = '~/(?:exec/obidos/ASIN/|o/|gp/product/|gp/offer-listing/|(?:(?:[^"\'/]*)/)?dp/|)(B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(X|0-9])|[0-9]{10})(?:(?:/|\?|\#)(?:[^"\'\s]*))?~isx';
        if (preg_match($regex, $url, $matches))
        {
            return $matches[1];
        } else
        {
            return false;
        }
    }

    private function generateAddToCartUrl($locale, $asin)
    {
        return $this->getAmazonAddToCartUrl($locale) .
                '?ASIN.1=' . $asin . '&Quantity.1=1' .
                '&AssociateTag=' . $this->getAssociateTagForLocale($locale);
    }

    public function viewDataPrepare($data)
    {
        foreach ($data as $key => $d)
        {
            $tag_id = $this->getAssociateTagForLocale($d['extra']['locale']);

            if (strstr($data[$key]['url'], 'AssociateTag='))
            {
                $data[$key]['url'] = TextHelper::addUrlParam($data[$key]['url'], 'AssociateTag', $tag_id);
            } else
            {
                $data[$key]['url'] = TextHelper::addUrlParam($data[$key]['url'], 'tag', $tag_id);
            }
        }

        return parent::viewDataPrepare($data);
    }

}
