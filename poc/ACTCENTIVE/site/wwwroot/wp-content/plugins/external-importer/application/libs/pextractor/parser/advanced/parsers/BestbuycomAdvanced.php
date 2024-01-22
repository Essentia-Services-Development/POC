<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * BestbuycomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class BestbuycomAdvanced extends AdvancedParser {

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();

        // reset session cookies
        $httpOptions['cookies'] = array();
        $httpOptions['user-agent'] = 'ia_archiver';

        $httpOptions['headers'] = array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-us,en;q=0.5',
            'Cache-Control' => 'no-cache',
            'Cookie' => 'tfs_upg=true; UID=ec3c7c4c-beca-4b23-926c-ecfbff1498f5; CTT=830f1ce6f5720443d439553bb592ffd6; bby_rdp=l; AMCV_F6301253512D2BDB0A490D45%40AdobeOrg=1585540135%7CMCMID%7C46120550392310190720024771003903546372%7CMCAAMLH-1592461420%7C6%7CMCAAMB-1592461420%7Cj8Odv6LonN4r3an7LhD3WZrU1bUpAkFkkiY1ncBR96t2PTI%7CMCOPTOUT-1591863820s%7CNONE%7CMCAID%7CNONE%7CMCCIDH%7C-1213955086%7CvVersion%7C4.4.0; SID=3d7669b6-1cfc-4b21-a0da-41fa790fcc00; _abck=20B3EB2A1F9267CAD62D9CDABDAD4303~0~YAAQBNT1V9yzaYNyAQAAVmUMogRF7PfkxPPJSqsYA5pj+AOLUChuBkftAlq8T0Yp/Q6WArev1XAz67T1b7ovE5LwRWp/0PUym7teXCvE8wcKrA+hiHyHwAH95mkbb7OXUfbUMpAI4I6hBnYgfHr2XKg+hINGd3Ab8ZSnMxDOEwjjso45e79XfIf83JwdOBduHDhy610GTawVOjr0DsxeIqFpyiwXBf86lKJkR0I3wTh0/FZoWFUTMeFNPScTzQT6Gg4oKn0h/WO603+GRJx9s4qmKS3Y1DFyHmiq2eJlFOu6UPo58I7z/vs/acZNjQv8LOlHz+PWh4g=~-1~-1~-1; bm_sz=A5D09A585548B7D051F367C661115643~YAAQBNT1V9mzaYNyAQAAYl8Mogi1BsULstsDD4BnDnw7aErUYqb+ke3D8XYA9OhnmVHuY1vmcjQI3GpNFsUrHT9gSQ1raKaH9UQUAPNFLvBJCpUVBwxv3tZFeZk0THkVa4FjuJAiKKttUQ5kNx6ioB8FNYQMD83L3KzGK7BToc/1RHqpjR1m0+7T1VZDlsXT2A==; bby_cbc_lb=p-browse-e; vt=171b973d-abac-11ea-a446-0ef288d55923; AMCVS_F6301253512D2BDB0A490D45%40AdobeOrg=1; s_ecid=MCMID%7C46120550392310190720024771003903546372; c2=Computers%20%26%20Tablets%3A%20Computer%20Cards%20%26%20Components%3A%20CPUs%20%2F%20Processors%3A%20pdp; s_cc=true; aam_uuid=52702997647788854740683020136104639782; 52245=; _gcl_au=1.1.246044671.1591856621; s_sq=%5B%5BB%5D%5D; intl_splash=false; ltc=%20; oid=95476883; globalUserTransition=default; optimizelyEndUserId=oeu1591856632053r0.7769520364434369; COM_TEST_FIX=2020-06-11T06%3A23%3A52.343Z; bby_prc_lb=p-prc-e; basketTimestamp=1591856633691; __gads=ID=a503bc0f5c4fbb9f:T=1591856636:S=ALNI_MattP14znTxXpWzu1HD8nDhaRz3Mw; cto_bundle=gmmHAV9GVURPT3VqS3B6QTVKbG80VlVFRnNpcGxXem4wYUlQYWhEeWR4S0dBZ2taalhlUzZESW9LU3l1d1NaTyUyQlNuMTFYYlp0ZDIlMkZadEhZUFpwb1RRdGlvWnViYVo3OUl6WklQdEZOZTBBdkhBcEJEdFlmQ0slMkJvRkpTSUt2YTdFUG1rU1BlVVQlMkJvSnZ1Sm5Wb05yR0VQRkh1dyUzRCUzRA; _cs_c=1; _cs_id=d8298f59-aa48-aec3-c042-f4469ad0ad34.1591856639.1.1591856639.1591856639.1.1626020639849.Lax.0; _cs_s=1.1',
        );
        return $httpOptions;
    }

    public function parseLinks()
    {
        $path = array(
            ".//h3[@class='heading product-title']/a/@href",
            ".//h4[@class='sku-header']/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ol[@class='paging-list']//li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//*[@class='priceView-hero-price priceView-customer-price']/span[1]",
            ".//*[@class='pb-hero-price pb-purchase-price']",
            ".//*[contains(@class, 'priceView-hero-price')]/span",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='pricing-price__regular-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDecription()
    {
        $paths = array(
            ".//div[@class='features-list all-features']",
            ".//div[contains(@id, 'shop-product-features')]",
        );

        return $this->xpathScalar($paths, true);
    }

    public function parseImages()
    {
        if (!preg_match_all('/\\"src\\\\":\\\\"(.+?)\\\\"/', $this->html, $matches))
            return array();

        $images = array();
        foreach ($matches[1] as $img)
        {
            $img = stripslashes($img);
            $img = json_decode('"' . $img . '"');
            if (!strstr($img, 'images/products'))
                continue;
            $img = str_replace('.jpg', '.jpg;maxHeight=640;maxWidth=550', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//*[@itemtype='https://schema.org/BreadcrumbList']//*[@itemprop='name']",
        );

        if ($categs = $this->xpathArray($paths))
        {
            array_pop($categs);
            return $categs;
        }
    }

    /*
    public function parseFeatures()
    {
        $purl = strtok($this->getUrl(), '?');
        if (!preg_match('~\/(\d+)\.p~', $purl, $matches))
            return;
        $sku = $matches[1];

        $url = 'https://www.bestbuy.com/api/tcfb/model.json?paths=%5B%5B%22shop%22%2C%22magellan%22%2C%22v1%22%2C%22specification%22%2C%22skus%22%2C' . $sku . '%2C%22groups%22%2C%22length%22%5D%5D&method=get';
        if (!$response = $this->getRemoteJson($url, false, 'GET', array('User-Agent' => 'ia_archiver')))
            return array();

        if (!isset($response['jsonGraph']['shop']['magellan']['v1']['specification']['skus'][$sku]['groups']['length']['value']))
            return array();

        $length = (int) $response['jsonGraph']['shop']['magellan']['v1']['specification']['skus'][$sku]['groups']['length']['value'];
        $length--;

        $url = 'https://www.bestbuy.com/api/tcfb/model.json?paths=%5B%5B%22shop%22%2C%22magellan%22%2C%22v1%22%2C%22specification%22%2C%22skus%22%2C'.$sku.'%2C%22groups%22%2C0%2C%22specifications%22%2C%7B%22from%22%3A0%2C%22to%22%3A'.$length.'%7D%2C%5B%22definition%22%2C%22displayName%22%2C%22value%22%5D%5D%2C%5B%22shop%22%2C%22magellan%22%2C%22v1%22%2C%22specification%22%2C%22skus%22%2C6449513%2C%22groups%22%2C1%2C%22specifications%22%2C%7B%22from%22%3A0%2C%22to%22%3A5%7D%2C%5B%22definition%22%2C%22displayName%22%2C%22value%22%5D%5D%2C%5B%22shop%22%2C%22magellan%22%2C%22v1%22%2C%22specification%22%2C%22skus%22%2C6449513%2C%22groups%22%2C%5B7%2C10%2C16%5D%2C%22specifications%22%2C0%2C%5B%22definition%22%2C%22displayName%22%2C%22value%22%5D%5D%2C%5B%22shop%22%2C%22magellan%22%2C%22v1%22%2C%22specification%22%2C%22skus%22%2C6449513%2C%22groups%22%2C%5B8%2C11%5D%2C%22specifications%22%2C%7B%22from%22%3A0%2C%22to%22%3A4%7D%2C%5B%22definition%22%2C%22displayName%22%2C%22value%22%5D%5D%2C%5B%22shop%22%2C%22magellan%22%2C%22v1%22%2C%22specification%22%2C%22skus%22%2C6449513%2C%22groups%22%2C%5B2%2C3%2C6%2C12%5D%2C%22specifications%22%2C%7B%22from%22%3A0%2C%22to%22%3A2%7D%2C%5B%22definition%22%2C%22displayName%22%2C%22value%22%5D%5D%2C%5B%22shop%22%2C%22magellan%22%2C%22v1%22%2C%22specification%22%2C%22skus%22%2C6449513%2C%22groups%22%2C%5B9%2C13%2C14%2C15%5D%2C%22specifications%22%2C%7B%22from%22%3A0%2C%22to%22%3A1%7D%2C%5B%22definition%22%2C%22displayName%22%2C%22value%22%5D%5D%2C%5B%22shop%22%2C%22magellan%22%2C%22v1%22%2C%22specification%22%2C%22skus%22%2C6449513%2C%22groups%22%2C%7B%22from%22%3A4%2C%22to%22%3A5%7D%2C%22specifications%22%2C%7B%22from%22%3A0%2C%22to%22%3A3%7D%2C%5B%22definition%22%2C%22displayName%22%2C%22value%22%5D%5D%5D&method=get';
        if (!$response = $this->getRemoteJson($url, false, 'GET', array('User-Agent' => 'ia_archiver')))
            return array();

        if (!isset($response['jsonGraph']['shop']['magellan']['v1']['specification']['skus'][$sku]['groups']))
            return array();

        $groups = $response['jsonGraph']['shop']['magellan']['v1']['specification']['skus'][$sku]['groups'];
    }
    */

    public function parseCurrencyCode()
    {
        return 'USD';
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('.jpg', '.jpg;maxHeight=640;maxWidth=550', $product->image);
        if ($product->categoryPath)
            array_shift($product->categoryPath);

        return $product;
    }

}
