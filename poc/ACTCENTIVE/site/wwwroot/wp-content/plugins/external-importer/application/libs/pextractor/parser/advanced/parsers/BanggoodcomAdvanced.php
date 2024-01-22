<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * BanggoodcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class BanggoodcomAdvanced extends AdvancedParser {

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();

        // reset cookies
        $httpOptions['cookies'] = array();
        return $httpOptions;
    }

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='p-wrap']//a[contains(@href, '-p-')]/@href",
            ".//dd[@class='name']/a/@href",
            ".//span[@class='title']/a/@href",
            ".//*[@class='hot_others_box_c']//a[1]/@href",
            ".//a[@class='products_name']/@href",
            ".//ul/li//div[@class='p-wrap']/@href",
        );

        $urls = $this->xpathArray($path);

        if (!$urls && preg_match_all('/<a class="title" href="(.+?)" title=/', $this->html, $matches))
            $urls = $matches[1];

        if (!$urls)
            $urls = $this->_parseCategoryLinks();

        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
        }

        $urls = array_unique($urls);
        return $urls;
    }

    private function _parseCategoryLinks()
    {
        if (!preg_match('~-(\d+)\.html~', $this->getUrl(), $matches))
            return array();

        $url = 'https://trans.banggood.com/forwards/load/oscategory/getCategoryData.html?page_part=1&cat_id=' . urlencode($matches[1]) . '&rec_uid=2097934606|1642419862&page=1&sort=1&ori_domain=www.banggood.com';

        $response = $this->getRemoteJson($url);

        if (!$response || !isset($response['result']['product_list']))
            return array();

        $urls = array();
        foreach ($response['result']['product_list'] as $url)
        {
            $urls[] = $url['url'];
        }

        return $urls;
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='page-num notranslate']//a[contains(@href, 'page')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $path = array(
            ".//div[@class='tab-cnt-description']",
        );

        if ($d = $this->xpathScalar($path, true))
        {
            $parts = explode('<div id="specification">', $d);
            if (count($parts) == 2)
                return '<div id="specification">' . $parts[1];
            else
                return $d;
        }
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseOldPrice()
    {
        // JS
    }

    public function parseImages()
    {
        return $this->xpathArray(".//ul[@class='list cf']//li/@data-large");
    }

    public function parseInStock()
    {
        if (strstr($this->xpathScalar(".//title"), 'Banggood.com sold out'))
            return false;

        if ($this->xpathScalar(".//*[@class='addToCartBtn_box']//a[contains(@class, 'arrivalnotice')]") == 'In Stock Alert')
            return false;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//ol[@class='breadcrumb']/li/a",
        );

        return $this->xpathArray($paths);
    }

    /*
      public function parseCurrencyCode()
      {
      if (preg_match('/,"currency":"(\w+)",/', $this->html, $matches))
      return $matches[1];
      }
     * 
     */
}
