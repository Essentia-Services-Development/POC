<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * WayfaircomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class WayfaircomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='BrowseProductCard-wrapper']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='BrowseFooter']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@itemprop='name']/text()",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='DetailedPriceBlock']//div[@class='BasePriceBlock BasePriceBlock--list']/span",
            ".//*[@id='orgPrc']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        return array();
        if (!preg_match('~w(\d+)\.html~', $this->getUrl(), $matches))
            return array();

        if (!$pid = $this->xpathScalar(".//input[@name='PiID[Finish]']/@value"))
            return array();

        $request_url = 'https://www.wayfair.com/graphql?queryPath=product_images_service~1%23product_images_service~0';
        $params = array(
            'query' => 'product_images_service~1#product_images_service~0',
            'variables' => array(
                'sku' => 'W' . $matches[1],
                'optionIds' => array($pid),
            ),
        );

        $response = \wp_remote_post($request_url, array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode($params),
            'method' => 'POST'
        ));
        if (\is_wp_error($response))
            return array();

        if (!$body = \wp_remote_retrieve_body($response))
            return array();

        $js_data = json_decode($body, true);

        if (!$js_data || !isset($js_data['results'][0]['hits']))
            return array();

        $urls = array();
        foreach ($js_data['results'][0]['hits'] as $hit)
        {
            $urls[] = $hit['url'];
        }
        return $urls;
    }

    public function parseInStock()
    {
        if ($this->xpathScalar(".//button[@class='ProductDetailCarouselOverlay-outOfStock ProductDetailCarouselOverlay-outOfStock--nonInteractive']"))
            return false;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name-value' => ".//ul[@class='ProductOverviewInformation-list']/li",
            ),
        );
    }

}
