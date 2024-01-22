<?php

namespace ContentEgg\application\modules\Viglink;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\viglink\ViglinkApi;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;

/**
 * ViglinkModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ViglinkModule extends AffiliateParserModule
{

	private $api_client = null;

	public function info()
	{
		return array(
			'name'        => 'Viglink',
			'description' => sprintf(__('Module adds products from <a target="_blank" href="%s">VigLink</a>.', 'content-egg'), 'http://www.keywordrush.com/go/viglink') . ' ' .
				__('Search for specific products from a vast catalog of over 350 million products.', 'content-egg') . ' ' .
				__('You can search by keyword or product URL.', 'content-egg'),
			'docs_uri'    => 'https://ce-docs.keywordrush.com/modules/affiliate/viglink',
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
		return 'grid';
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
		if (filter_var($keyword, FILTER_VALIDATE_URL))
		{
			$url = filter_var($keyword, FILTER_SANITIZE_URL);

			return $this->searchByUrl($url);
		}
		else
		{
			return $this->searchByKeyword($keyword, $query_params, $is_autoupdate);
		}
	}

	public function doRequestItems(array $items)
	{
		foreach ($items as $key => $item)
		{
			if (!$item['orig_url'])
			{
				continue;
			}

			try
			{
				$product = $this->searchByUrl($item['orig_url']);
			}
			catch (\Exception $ex)
			{
				continue;
			}

			if (!$product || !is_array($product))
			{
				continue;
			}
			$product = $product[0];
			// assign new data
			$items[$key]['price']        = $product->price;
			$items[$key]['currencyCode'] = $product->currencyCode;
		}

		return $items;
	}

	private function searchByUrl($url)
	{
		if (!$result = $this->getApiClient()->getMetadata($url))
		{
			return array();
		}

		return array($this->prepareByUrlResults($result));
	}

	private function searchByKeyword($keyword, $query_params = array(), $is_autoupdate = false)
	{
		if ($is_autoupdate)
		{
			$options['itemsPerPage'] = $this->config('entries_per_page_update');
		}
		else
		{
			$options['itemsPerPage'] = $this->config('entries_per_page');
		}

		if ($this->config('country'))
		{
			$options['country'] = TextHelper::getArrayFromCommaList($this->config('country'));
		}
		if ($this->config('merchant'))
		{
			$options['merchant'] = TextHelper::getArrayFromCommaList($this->config('merchant'));
		}

		if ($this->config('sortBy'))
		{
			$options['sortBy'] = $this->config('sortBy');
		}
		if ($this->config('category'))
		{
			$options['category'] = $this->config('category');
		}
		if ($this->config('filterImages'))
		{
			$options['filterImages'] = true;
		}

		// price filter
		if (!empty($query_params['priceFrom']))
		{
			$priceFrom = (float) $query_params['priceFrom'];
		}
		elseif ($this->config('priceFrom'))
		{
			$priceFrom = (float) $this->config('priceFrom');
		}
		else
		{
			$priceFrom = 0;
		}
		if (!empty($query_params['priceTo']))
		{
			$priceTo = (float) $query_params['priceTo'];
		}
		elseif ($this->config('priceTo'))
		{
			$priceTo = (float) $this->config('priceTo');
		}
		else
		{
			$priceTo = 0;
		}
		if ($priceFrom)
		{
			$options['price'] = $priceFrom . ',';
		}
		if ($priceTo)
		{
			if (!$options['price'])
			{
				$options['price'] = ',';
			}
			$options['price'] .= $priceTo;
		}

		$results = $this->getApiClient()->search($keyword, $options);

		if (!isset($results['items']) || !is_array($results['items']))
		{
			return array();
		}
		$results = $results['items'];
		if (!isset($results[0]) && isset($results['name']))
		{
			$results = array($results);
		}

		return $this->prepareByKeywordResults($results);
	}

	private function prepareByUrlResults($r)
	{
		$content            = new ContentProduct;
		$content->unique_id = md5($r['url']);
		$content->title     = $r['title'];
		$content->orig_url  = $r['url'];

		$deeplink = $this->config('deeplink');
		if ($deeplink)
		{
			$content->url = LinkHandler::createAffUrl($content->orig_url, $deeplink, (array) $content);
		}
		else
		{
			$content->url = $this->buildMonetizedUrl($r['url']);
		}

		if (!strstr($r['imageUrl'], 'data:image'))
		{
			$content->img = $r['imageUrl'];
		}

		$content->price = TextHelper::parsePriceAmount($r['price']);
		if ($currency_code = TextHelper::parseCurrencyCode($r['price']))
		{
			$content->currencyCode = $currency_code;
		}
		else
		{
			$content->currencyCode = $this->config('default_currency');
		}

		if (strstr($content->orig_url, 'amazon.com.br/'))
		{
			$content->currencyCode = 'BRL';
		}

		$content->manufacturer = $r['brandName'];
		$content->upc          = $r['upc'];
		$content->sku          = $r['sku'];
		$content->description  = $r['description'];
		$content->merchant     = $r['merchantName'];
		$content->domain       = TextHelper::getHostName($content->orig_url);

		return $content;
	}

	private function prepareByKeywordResults($results)
	{
		$data = array();
		foreach ($results as $key => $r)
		{
			$content = new ContentProduct;

			$content->unique_id    = md5($r['url']);
			$content->title        = $r['name'];
			$content->url          = $r['url'];
			$content->img          = $r['imageUrl'];
			if ($img = TextHelper::parseOriginalUrl($content->img, 'url'))
				$content->img = $img;
			$content->category     = $r['category'];
			$content->price        = (float) $r['price'];
			$content->currencyCode = 'USD';
			$content->manufacturer = $r['brand'];
			$content->upc          = $r['upc'];
			$content->merchant     = $r['merchant'];

			$domain = TextHelper::parseDomain($r['url'], 'u');
			if ($domain == 'bizrate.com')
			{
				if ($d = TextHelper::parseDomain(TextHelper::parseOriginalUrl($r['url'], 'u'), 't'))
				{
					$domain = $d;
				}
			}
			else
			{
				if ($d = TextHelper::parseDomain(TextHelper::parseOriginalUrl($r['url'], 'u'), 'url'))
				{
					$domain = $d;
				}
			}

			if ($domain)
			{
				$content->domain = $domain;
			}
			else
			{
				$merchant = strtolower($r['merchant']);
				if (TextHelper::isValidDomainName($merchant))
				{
					$content->domain = $merchant;
				}
			}

			$content->extra          = new ExtraDataViglink;
			$content->extra->country = $r['country'];
			$data[]                  = $content;
		}

		return $data;
	}

	/**
	 * Building Monetized URL
	 * @link: https://viglink-developer-center.readme.io/docs/building-monetized-urls
	 */
	private function buildMonetizedUrl($url)
	{
		$filtered = \apply_filters('cegg_viglink_affiliate_link', $url);
		if ($filtered != $url)
		{
			return $filtered;
		}

		$params = array(
			'key' => $this->config('apiKey'),
			'u'   => $url,
		);

		return 'https://redirect.viglink.com?' . http_build_query($params);
	}

	public function viewDataPrepare($data)
	{
		$deeplink = $this->config('deeplink');
		foreach ($data as $key => $d)
		{
			if (empty($d['orig_url']))
			{
				continue;
			}
			if ($deeplink)
			{
				$data[$key]['url'] = LinkHandler::createAffUrl($d['orig_url'], $deeplink, $d);
			}
			else
			{
				$data[$key]['url'] = $this->buildMonetizedUrl($d['orig_url']);
			}
		}

		return parent::viewDataPrepare($data);
	}

	private function getApiClient()
	{
		if ($this->api_client === null)
		{
			$this->api_client = new ViglinkApi($this->config('apiKey'), $this->config('secretKey'));
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

	public function renderSearchPanel()
	{
		$this->render('search_panel', array('module_id' => $this->getId()));
	}

	public function renderUpdatePanel()
	{
		$this->render('update_panel', array('module_id' => $this->getId()));
	}
}
