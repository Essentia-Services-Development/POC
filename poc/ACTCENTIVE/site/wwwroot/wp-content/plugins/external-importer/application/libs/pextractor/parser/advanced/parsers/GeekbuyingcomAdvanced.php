<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * GeekbuyingcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class GeekbuyingcomAdvanced extends AdvancedParser {

    private $_prices = array();

    /*
      protected function preParseProduct()
      {
      $this->_getPrices();
      return parent::preParseProduct();
      }

      protected function _getPrices()
      {
      if (!preg_match('~-(\d+)\.html~', $this->getUrl(), $matches))
      return array();

      $url = 'https://www.geekbuying.com/service/GetProductAppSalePrice?productid=' . urlencode($matches[1]);
      if ($response = $this->getRemoteJson($url))
      $this->_prices = $response;
      }
     * 
     */

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='name']/a/@href",
            ".//div[@class='width fix']//dl/dt/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@id='pagination']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@id='Description']", true);
    }

    /*
      public function parsePrice()
      {
      if (isset($this->_prices['pcPrice']))
      return $this->_prices['pcPrice'];

      $paths = array(
      ".//span[@itemprop='price']",
      ".//span[@id='mm-saleDscPrc']",
      );

      return $this->xpathScalar($paths);
      }

      public function parseOldPrice()
      {
      if (isset($this->_prices['regPirce']))
      return $this->_prices['regPirce'];

      $paths = array(
      ".//*[@class='price_box']//*[@id='regprice']",
      );

      return $this->xpathScalar($paths);
      }
     * 
     */

    public function parseImages()
    {
        return $this->xpathArray(".//ul[@id='thumbnail']//img/@data-picturepath500");
    }

    public function parseInStock()
    {
        if ($this->xpathScalar(".//*[@id='nowBuyDiv']//*[@class='btn sold_btn']") == 'Sold Out')
            return false;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//ul[@id='crumbs']//li//a",
        );

        return $this->xpathArray($paths);
    }

    public function parseReviews()
    {
        if (!preg_match('~-(\d+)\.html~', $this->getUrl(), $matches))
            return array();

        $url = 'https://www.geekbuying.com/Product/GetReview?ProductionId=' . urlencode($matches[1]) . '&selectType=All&Pagesize=50&page=1';
        $response = $this->getRemoteJson($url);

        if (!$response || !isset($response['Reviews']))
            return array();

        $results = array();
        foreach ($response['Reviews'] as $r)
        {
            $review = array();
            $review['review'] = '';

            if (!empty($r['Description']['Pros']))
                $review['review'] .= 'Pros: ' . $r['Description']['Pros'];

            if (!empty($r['Description']['Cons']))
                $review['review'] .= 'Cons: ' . $r['Description']['Cons'];

            if (!empty($r['Description']['Other']))
                $review['review'] .= $r['Description']['Other'];

            if (!$review['review'])
                continue;

            if (isset($r['StarRate']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['StarRate']);

            if (isset($r['UserName']))
                $review['author'] = $r['UserName'];

            if (isset($r['EnToUsCreationTime']))
                $review['date'] = strtotime($r['EnToUsCreationTime']);

            $results[] = $review;
        }
        return $results;
    }

}
