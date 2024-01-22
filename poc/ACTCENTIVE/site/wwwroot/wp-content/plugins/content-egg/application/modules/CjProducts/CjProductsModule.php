<?php

namespace ContentEgg\application\modules\CjProducts;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\cj\CjGraphQlApi;

/**
 * CjProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class CjProductsModule extends AffiliateParserModule
{

	private $api_client = null;

	public function info()
	{
		/*
		if (\is_admin())
			\add_action('admin_notices', array(__CLASS__, 'updateNotice'));
		 *
		 */

		return array(
			'name'          => 'CJ Products',
			'api_agreement' => 'http://www.cj.com/legal/ws-terms',
			'description'   => __('Adds products from CJ.com. You must have approval from each program separately.', 'content-egg'),
			'docs_uri'      => 'https://ce-docs.keywordrush.com/modules/affiliate/cjproducts',
		);
	}

	public static function updateNotice()
	{
		if (!CjProductsConfig::getInstance()->option('is_active'))
		{
			return;
		}

		if (CjProductsConfig::getInstance()->option('cid'))
		{
			return;
		}

		echo '<div class="notice notice-warning is-dismissible">';
		echo '<p>' . sprintf(__('CJ launched a new Product Search API. Please visit <a href="%s">CJ Product settings</a> to get started with the new API.', 'content-egg'), \get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-modules--CjProducts')) . '</p>';
		echo '</div>';
	}

	public static function getMerchantDomainPairs()
	{
		return \apply_filters('cegg_cjproducts_merchant_mapping', array());
	}

	public function getParserType()
	{
		return self::PARSER_TYPE_PRODUCT;
	}

	public function defaultTemplateName()
	{
		return 'list';
	}

	public function isItemsUpdateAvailable()
	{
		return true;
	}

	public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
	{
		if (!$cid = $this->config('cid'))
		{
			throw new \Exception('Set "Company ID" in the module settings');
		}

		if ($is_autoupdate)
		{
			$limit = $this->config('entries_per_page_update');
		}
		else
		{
			$limit = $this->config('entries_per_page');
		}

		$shoppingProducts = sprintf('companyId: %d', $cid);
		$shoppingProducts .= sprintf(',limit: %d', $limit);

		if (TextHelper::isEan($keyword))
		{
			$shoppingProducts .= sprintf(',gtin: "%s"', $keyword);
		}
		else
		{
			$shoppingProducts .= sprintf(',keywords: "%s"', addslashes($keyword));
		}

		$advertiser_ids = $this->config('advertiser_ids');
		if ($advertiser_ids && $advertiser_ids != 'joined')
		{
			$shoppingProducts .= sprintf(',partnerIds: [%s]', TextHelper::commaList($advertiser_ids));
		}

		$partner_status = $this->config('partner_status');
		if ($partner_status && $partner_status != 'ALL')
		{
			$shoppingProducts .= sprintf(',partnerStatus: %s', $partner_status);
		}

		if ($currency = $this->config('currency'))
		{
			$shoppingProducts .= sprintf(',currency: "%s"', $currency);
		}


		$fields = array(
			'adId',
			'advertiserId',
			'advertiserName',
			'availability',
			'availabilityDate',
			'brand',
			'catalogId',
			'description',
			'gtin',
			'id',
			'imageLink',
			'itemListId',
			'itemListName',
			'lastUpdated',
			'link',
			'salePriceEffectiveDateEnd',
			'salePriceEffectiveDateStart',
			'targetCountry',
			'title',
			'color',
			'condition',
			'energyEfficiencyClass',
			'energyEfficiencyClassMax',
			'energyEfficiencyClassMin',
			'expirationDate',
			'mpn',
			'unitPricingBaseMeasure',
			'unitPricingMeasure',
		);

		$resultList = join(',', $fields);
		$resultList .= ',shipping{price{amount,currency}country}';
		$resultList .= ',salePrice{amount,currency}';
		$resultList .= ',price{amount,currency}';
		$resultList .= sprintf(',linkCode(pid: "%d") {clickUrl}', $this->config('website_id'));

		$payload = '{shoppingProducts(' . $shoppingProducts . ') {resultList {' . $resultList . '}}}';
		$results = $this->getCJClient()->search($payload);

		if (!is_array($results) || !isset($results['data']['shoppingProducts']['resultList']))
		{
			return array();
		}

		return $this->prepareResults($results['data']['shoppingProducts']['resultList']);
	}

	public function doRequestItems(array $items)
	{
		if (!$cid = $this->config('cid'))
		{
			return $items;
		}

		foreach ($items as $key => $item)
		{
			$shoppingProducts = sprintf('companyId: %d', $cid);
			$shoppingProducts .= sprintf(',keywords: "%s"', addslashes($item['title']));
			$shoppingProducts .= sprintf(',partnerIds: [%d]', $item['extra']['advertiserId']);
			$shoppingProducts .= sprintf(',limit: %d', 10);

			if ($gtin = self::getGtin($item))
			{
				$shoppingProducts .= sprintf(',gtin: "%s"', $gtin);
			}

			if ($item['currencyCode'])
			{
				$shoppingProducts .= sprintf(',currency: "%s"', $item['currencyCode']);
			}

			$fields = array(
				'title',
				'link',
				'adId',
				'availability',
				'availabilityDate',
				'lastUpdated',
				'salePriceEffectiveDateEnd',
				'salePriceEffectiveDateStart',
			);

			$resultList = join(',', $fields);
			$resultList .= ',salePrice{amount,currency}';
			$resultList .= ',price{amount,currency}';

			$payload = '{shoppingProducts(' . $shoppingProducts . ') {resultList {' . $resultList . '}}}';

			try
			{
				$results = $this->getCJClient()->search($payload);
			}
			catch (\Exception $e)
			{
				continue;
			}

			if (!isset($results['data']['shoppingProducts']['resultList']) || !$results['data']['shoppingProducts']['resultList'])
			{
				$items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				$items[$key]['price']    = 0;
				$items[$key]['priceOld'] = 0;
				continue;
			}
			$results = $results['data']['shoppingProducts']['resultList'];
			if (!$item['orig_url'])
			{
				$item['orig_url'] = TextHelper::parseOriginalUrl($item['url'], 'url');
			}

			foreach ($results as $i => $r)
			{
				if ($item['orig_url'] == $r['link'])
				{
					if (in_array($r['availability'], array('in stock', 'preorder')))
					{
						$items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
					}
					else
					{
						$items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
					}
					$items[$key]['availability'] = $r['availability'];

					if ($r['salePrice'])
					{
						$items[$key]['price']    = $r['salePrice']['amount'];
						$items[$key]['priceOld'] = $r['price']['amount'];
					}
					else
					{
						$items[$key]['price']    = $r['price']['amount'];
						$items[$key]['priceOld'] = 0;
					}

					break;
				}
				else
				{
					if ($i == count($results) - 1)
					{
						$items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
					}

					continue;
				}
			}

			if ($items[$key]['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
			{
				$items[$key]['price']    = 0;
				$items[$key]['priceOld'] = 0;
			}
		}

		return $items;
	}

	private static function getGtin(array $item)
	{
		if (!empty($item['extra']['gtin']))
		{
			return $item['extra']['gtin'];
		}
		elseif (!empty($item['extra']['upc']))
		{
			return $item['extra']['upc'];
		}
		elseif (!empty($item['extra']['isbn']))
		{
			return $item['extra']['isbn'];
		}
		else
		{
			return false;
		}
	}

	private function prepareResults($results)
	{
		$data      = array();
		$used_urls = array();

		foreach ($results as $key => $r)
		{
			// dublicate?
			if (in_array($r['link'], $used_urls))
			{
				continue;
			}

			$content = new ContentProduct;

			$content->unique_id = $r['id'] . '-' . $r['advertiserId'] . '-' . $r['gtin'];
			$content->title     = $r['title'];
			$content->orig_url  = $r['link'];
			$used_urls[]        = $r['link'];
			$content->domain    = TextHelper::getHostName($content->orig_url);
			if (isset($r['linkCode']['clickUrl']))
			{
				$content->url = $r['linkCode']['clickUrl'];
			}
			else
			{
				$content->url = $content->orig_url;
			}
			$content->img          = $r['imageLink'];
			$content->manufacturer = $r['brand'];
			$content->availability = $r['availability'];
			$content->merchant     = $r['advertiserName'];

			// Product's availability Values: in stock, out of stock, preorder
			if (in_array($r['availability'], array('in stock', 'preorder')))
			{
				$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			}
			else
			{
				$content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
			}
			$content->availability = $r['availability'];

			$content->currencyCode = $r['price']['currency'];
			$content->currency     = TextHelper::currencyTyping($content->currencyCode);

			$content->description = strip_tags($r['description']);
			if ($max_size = $this->config('description_size'))
			{
				$content->description = TextHelper::truncate($content->description, $max_size);
			}

			if ($r['salePrice'])
			{
				$content->price    = $r['salePrice']['amount'];
				$content->priceOld = $r['price']['amount'];
			}
			else
			{
				$content->price = $r['price']['amount'];
			}

			$content->extra               = new ExtraDataCjProducts;
			$content->extra->id           = $r['id'];
			$content->extra->catalogId    = $r['catalogId'];
			$content->extra->advertiserId = $r['advertiserId'];
			$content->extra->adId         = $r['adId'];
			$content->extra->gtin         = $r['gtin'];
			ExtraDataCjProducts::fillAttributes($content->extra, $r);

			$data[] = $content;
		}

		return $data;
	}

	private function getCJClient()
	{
		if ($this->api_client === null)
		{
			$this->api_client = new CjGraphQlApi($this->config('access_token'));
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
