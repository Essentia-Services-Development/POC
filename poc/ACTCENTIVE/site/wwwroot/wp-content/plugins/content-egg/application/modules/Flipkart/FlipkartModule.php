<?php

namespace ContentEgg\application\modules\Flipkart;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\flipkart\FlipkartApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;

/**
 * FlipkartModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class FlipkartModule extends AffiliateParserModule
{

    public function info()
    {
        return array(
            'name' => 'Flipkart',
            'description' => __('Adds items from flipkart.com', 'content-egg'),
        );
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function defaultTemplateName()
    {
        return 'item';
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

        if ($is_autoupdate)
        {
            $options['resultCount'] = $this->config('entries_per_page_update');
        } else
        {
            $options['resultCount'] = $this->config('entries_per_page');
        }

        $client = new FlipkartApi($this->config('tracking_id'), $this->config('token'));

        $pid = self::getFlipkartProductId($keyword);
        if ($pid)
        {
            $results = $client->product($pid);

            if (!is_array($results))
            {
                return array();
            }
            if (isset($results['productBaseInfoV1']))
            {
                $results = array($results);
            } else
            {
                return array();
            }
        } else
        {
            $results = $client->search($keyword, $options);
            if (!is_array($results))
            {
                return array();
            }
            if (isset($results['productInfoList']))
            {
                $results = $results['productInfoList'];
            } elseif (isset($results['products']))
            {
                $results = $results['products'];
            } else
            {
                return array();
            }
        }

        return $this->prepareResults($results);
    }

    public function prepareResults($results)
    {
        $data = array();

        foreach ($results as $key => $res)
        {
            $r = $res['productBaseInfoV1'];

            $content = new ContentProduct;
            $content->unique_id = $r['productId'];
            if ((bool) $r['inStock'])
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            } else
            {
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
            $content->merchant = 'Flipkart';
            $content->domain = 'flipkart.com';
            $content->title = $r['title'];
            $content->availability = (bool) $r['inStock'];
            $content->url = $this->getAffiliateLink($r['productUrl']);
            $content->category = $r['categoryPath'];
            $content->currencyCode = 'INR';
            $content->price = $this->getPrice($r);
            $content->priceOld = $this->getPriceOld($r);

            if (isset($r['imageUrls']['800x800']))
            {
                $content->img = $r['imageUrls']['800x800'];
            } elseif (isset($r['imageUrls']['400x400']))
            {
                $content->img = $r['imageUrls']['400x400'];
            }
            if ($r['productBrand'])
            {
                $content->manufacturer = $r['productBrand'];
            }

            if ($r['productDescription'] && $r['productDescription'] != 'NA')
            {
                $content->description = $r['productDescription'];
                if ($max_size = $this->config('description_size'))
                {
                    $content->description = TextHelper::truncate($content->description, $max_size);
                }
            }

            $content->currency = TextHelper::currencyTyping($content->currencyCode);
            if (!empty($r['discountPercentage']))
            {
                $content->percentageSaved = (int) $r['discountPercentage'];
            }

            $content->extra = new ExtraDataFlipkart;
            ExtraDataFlipkart::fillAttributes($content->extra, $r);
            ExtraDataFlipkart::fillAttributes($content->extra, $res['productShippingInfoV1']);
            ExtraDataFlipkart::fillAttributes($content->extra, $res['categorySpecificInfoV1']);

            foreach ($content->extra->specificationList as $specificationList)
            {
                foreach ($specificationList['values'] as $feature)
                {

                    $feature = array(
                        'name' => $feature['key'],
                        'value' => join('; ', $feature['value']),
                    );
                    if ($feature['name'] && strlen($feature['value']) <= 200)
                    {
                        $content->features[] = $feature;
                    }
                }
            }

            $data[] = $content;
        }

        return $data;
    }

    private function getPrice($r)
    {
        if (!empty($r['flipkartSellingPrice']))
        {
            $flipkartSellingPrice = (float) $r['flipkartSellingPrice']['amount'];
        } else
        {
            $flipkartSellingPrice = 0;
        }
        if (!empty($r['flipkartSpecialPrice']))
        {
            $flipkartSpecialPrice = (float) $r['flipkartSpecialPrice']['amount'];
        } else
        {
            $flipkartSpecialPrice = 0;
        }

        //$maximumRetailPrice = (float) $r['maximumRetailPrice']['amount'];
        if ($flipkartSellingPrice && $flipkartSellingPrice < $flipkartSpecialPrice)
        {
            return $flipkartSellingPrice;
        } elseif ($flipkartSpecialPrice)
        {
            return $flipkartSpecialPrice;
        } else
        {
            return $flipkartSellingPrice;
        }
    }

    private function getPriceOld($r)
    {
        if (empty($r['maximumRetailPrice']))
        {
            return 0;
        }
        $maximumRetailPrice = (float) $r['maximumRetailPrice']['amount'];
        if ($maximumRetailPrice && $maximumRetailPrice > $this->getPrice($r))
        {
            return $maximumRetailPrice;
        } else
        {
            return 0;
        }
    }

    public function doRequestItems(array $items)
    {
        foreach ($items as $key => $item)
        {
            $client = new FlipkartApi($this->config('tracking_id'), $this->config('token'));
            $results = $client->product($item['unique_id']);

            try
            {
                $results = $client->product($item['unique_id']);
            } catch (\Exception $e)
            {
                continue;
            }

            if (!is_array($results) || !isset($results['productBaseInfoV1']))
            {
                continue;
            }

            $r = $results['productBaseInfoV1'];

            // assign new price
            $items[$key]['price'] = $this->getPrice($r);
            $items[$key]['priceOld'] = $this->getPriceOld($r);
            $items[$key]['url'] = $this->getAffiliateLink($r['productUrl']);

            if (!empty($r['discountPercentage']))
            {
                $items[$key]['percentageSaved'] = (int) $r['discountPercentage'];
            }
            $items[$key]['domain'] = 'flipkart.com';
            if ((bool) $r['inStock'])
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            } else
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            }
        }

        return $items;
    }

    public function renderResults()
    {
        PluginAdmin::render('_metabox_results', array('module_id' => $this->getId()));
    }

    public function renderSearchResults()
    {
        PluginAdmin::render('_metabox_search_results', array('module_id' => $this->getId()));
    }

    public static function getFlipkartProductId($str)
    {
        if (preg_match('/^[0-9A-Z]{16}$/', $str))
        {
            return $str;
        }

        $query = parse_url($str, PHP_URL_QUERY);
        if ($query)
        {
            parse_str($query, $output);
            if ($output && isset($output['pid']))
            {
                return $output['pid'];
            }
        }

        return '';
    }

    //@link: https://affiliate.flipkart.com/tools/mobile-tracking-info
    public function getMobileTrackingUrl($url)
    {
        return str_replace('https://www.flipkart.com/', 'https://dl.flipkart.com/dl/', $url);
    }

    public function getAffiliateLink($url)
    {
        $url = $this->getMobileTrackingUrl($url);
        $deeplink = $this->config('deeplink');
        if ($deeplink)
        {
            $url = TextHelper::removeUrlParam($url, 'affid');

            return LinkHandler::createAffUrl($url, $deeplink);
        }

        $trackingParameters = $this->config('trackingParameters');

        return LinkHandler::createAffUrl($url, $trackingParameters);
    }

}
