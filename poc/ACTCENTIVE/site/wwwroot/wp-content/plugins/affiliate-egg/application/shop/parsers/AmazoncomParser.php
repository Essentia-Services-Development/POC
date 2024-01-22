<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazoncomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AmazoncomParser extends ShopParser
{

    protected $canonical_domain = 'https://www.amazon.com';
    protected $charset = 'utf-8';
    protected $currency = 'USD';
    //protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    //protected $user_agent = array('wget');
    //protected $user_agent = array('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.16; rv:86.0) Gecko/20100101 Firefox/86.0');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        //'Connection' => 'keep-alive',
    );

    public function restPostGet($url, $fix_encoding = true)
    {
        \add_action('http_api_curl', array(__CLASS__, '_setCurlOptions'), 10, 3);

        $body = parent::restPostGet($url, false);
        // fix
        $body = preg_replace('/<table id="HLCXComparisonTable".+?<\/table>/uims', '', $body);
        return $this->decodeCharset($body, $fix_encoding);
    }

    static public function _setCurlOptions($handle, $r, $url)
    {
        curl_setopt($handle, CURLOPT_ENCODING, '');
    }

    public function parseCatalog($max)
    {
        $xpath = array(
            ".//div[@class='p13n-desktop-grid']//a[@class='a-link-normal']/@href",
            ".//*[@class='aok-inline-block zg-item']/a[@class='a-link-normal']/@href",
            ".//h3[@class='newaps']/a/@href",
            ".//div[@id='resultsCol']//a[contains(@class,'s-access-detail-page')]/@href",
            ".//div[@class='zg_title']/a/@href",
            ".//div[@id='rightResultsATF']//a[contains(@class,'s-access-detail-page')]/@href",
            ".//div[@id='atfResults']/ul//li//div[contains(@class,'a-column')]/a/@href",
            ".//div[@id='mainResults']//li//a[@title]/@href",
            ".//*[@id='zg_centerListWrapper']//a[@class='a-link-normal' and not(@title)]/@href",
            ".//h5/a[@class='a-link-normal a-text-normal']/@href",
            ".//*[@data-component-type='s-product-image']//a[@class='a-link-normal']/@href",
            ".//div[@class='a-section a-spacing-none']/h2/a/@href",
            ".//h2/a[@class='a-link-normal a-text-normal']/@href",
            ".//span[@data-component-type='s-product-image']/a/@href",
        );

        $urls = $this->xpathArray($xpath);


        // Today's Deals
        if (!$urls)
            $urls = $this->parseGoldBoxDeals();

        if (!$urls)
            return array();

        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = $this->canonical_domain . $url;
        }

        // picassoRedirect fix
        foreach ($urls as $i => $url)
        {
            if (!strstr($url, '/gp/slredirect/picassoRedirect.html/'))
                continue;
            $parts = parse_url($url);
            if (empty($parts['query']))
                continue;
            parse_str($parts['query'], $output);
            if (isset($output['url']))
                $urls[$i] = $output['url'];
            else
                unset($urls[$i]);
        }

        // fix urls. prevent duplicates for autobloging
        $res = array();
        foreach ($urls as $key => $url)
        {
            if ($asin = self::parseAsinFromUrl($url))
                $res[] = $this->canonical_domain . '/dp/' . $asin . '/';
        }

        return $res;
    }

    protected function parseGoldBoxDeals()
    {

        if (!strstr($this->getUrl(), 'amazon.com/gp/goldbox'))
            return array();

        $request_url = 'https://www.amazon.com/xa/dealcontent/v2/GetDeals?nocache=' . time();
        $response = \wp_remote_post($request_url, array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => '{"requestMetadata":{"marketplaceID":"ATVPDKIKX0DER","clientID":"goldbox_mobile_pc","sessionID":"147-0111701-3832735"},"dealTargets":[{"dealID":"27929040"},{"dealID":"2dfcb07b"},{"dealID":"6727cdb5"},{"dealID":"676b0c2d"},{"dealID":"7aeb0274"},{"dealID":"7ca1692e"},{"dealID":"a6614039"},{"dealID":"af1e3631"},{"dealID":"b3db4ae7"},{"dealID":"e2b741c7"},{"dealID":"eb7ca674"},{"dealID":"f5a1f5c0"}],"responseSize":"ALL","itemResponseSize":"DEFAULT_WITH_PREEMPTIVE_LEAKING","widgetContext":{"pageType":"Landing","subPageType":"hybrid-batch-btf","deviceType":"pc","refRID":"KH2KVAGJESZ5EF3NCNGD","widgetID":"f3f63185-46c5-4297-bc5f-35921b3fb364","slotName":"merchandised-search-8"},"customerContext":{"languageOfPreference":"en_US"}}',
            'method' => 'POST'
        ));

        if (\is_wp_error($response) || !$body = \wp_remote_retrieve_body($response))
            return array();
        $js_data = json_decode($body, true);

        if (!$js_data || !isset($js_data['dealDetails']))
            return array();

        $urls = array();
        foreach ($js_data['dealDetails'] as $hit)
        {
            if (strstr($hit['egressUrl'], '/dp/'))
                $urls[] = $hit['egressUrl'];
        }
        return $urls;
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@id='title']/span",
            ".//*[@id='fine-ART-ProductLabelArtistNameLink']",
            ".//h1",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        if ($results = $this->xpathArray(array(".//div[@id='featurebullets_feature_div']//span[@class='a-list-item']", ".//div[@id='featurebullets_feature_div']//li")))
        {
            $results = array_map('\sanitize_text_field', $results);
            $key = array_search('Make sure this fits by entering your model number.', $results);
            if ($key !== false)
                unset($results[$key]);
            return '<ul><li>' . implode("</li><li>\n", $results) . '</li></ul>';
        }

        $result = $this->xpathScalar(".//script[contains(.,'iframeContent')]");
        if ($result && preg_match('/iframeContent\s=\s"(.+?)"/msi', $result, $match))
        {
            $res = urldecode($match[1]);
            if (preg_match('/class="productDescriptionWrapper">(.+?)</msi', $res, $match))
                return trim($match[1]);
        }

        $paths = array(
            ".//*[@id='bookDescription_feature_div']/noscript/div",
            ".//*[@id='productDescription']//*[@class='productDescriptionWrapper']",
            ".//*[@id='productDescription']/p/*[@class='btext']",
            ".//*[@id='productDescription']/p",
            ".//*[@id='bookDescription_feature_div']/noscript",
            ".//*[@class='dv-simple-synopsis dv-extender']",
            ".//*[@id='bookDescription_feature_div']//noscript/div",
        );

        if ($description = $this->xpathScalar($paths, true))
            return $description;

        if (preg_match('/bookDescEncodedData = "(.+?)",/', $this->dom->saveHTML(), $matches))
            return html_entity_decode(urldecode($matches[1]));

        return '';
    }

    public function parsePrice()
    {
        if (!$this->isInStock())
            return;

        $price = $this->xpathScalar(".//span[@id='style_name_0_price']/span[contains(@class, 'olpWrapper')]");
        if ($price && strstr($price, ' options from '))
        {
            $parts = explode('options from', $price);
            return $parts[1];
        }

        $paths = array(
            ".//div[@class='a-section a-spacing-none aok-align-center']//span[@class='a-offscreen']",

            ".//span[@class='a-price a-text-price a-size-medium apexPriceToPay']//span[@class='a-offscreen']",
            ".//div[@class='a-section a-spacing-small a-spacing-top-small']//a/span[@class='a-size-base a-color-price']",
            ".//*[@id='priceblock_dealprice']",
            ".//span[@id='priceblock_ourprice']",
            ".//span[@id='priceblock_saleprice']",
            ".//input[@name='displayedPrice']/@value",
            ".//*[@id='unqualifiedBuyBox']//*[@class='a-color-price']",
            ".//*[@class='dv-button-text']",
            ".//*[@id='cerberus-data-metrics']/@data-asin-price",
            ".//div[@id='olp-upd-new-freeshipping']//span[@class='a-color-price']",
            ".//span[@id='rentPrice']",
            ".//span[@id='newBuyBoxPrice']",
            ".//div[@id='olp-new']//span[@class='a-size-base a-color-price']",
            ".//span[@id='unqualified-buybox-olp']//span[@class='a-color-price']",
            ".//span[@id='price_inside_buybox']",
            ".//span[@class='slot-price']//span[@class='a-size-base a-color-price a-color-price']",
            ".//span[@class='a-button-inner']//span[contains(@class, 'a-color-price')]",
            ".//div[@id='booksHeaderSection']//span[@id='price']",
            ".//table[@class='a-lineitem a-align-top']//span[@class='a-offscreen']",
            ".//span[contains(@class, 'a-price')]//span[@class='a-offscreen']",
        );

        $price = $this->xpathScalar($paths);

        if (!$price && $price = $this->xpathScalar(".//span[@id='priceblock_ourprice']//*[@class='buyingPrice' or @class='price-large']"))
        {
            if ($cent = $this->xpathScalar(".//span[@id='priceblock_ourprice']//*[@class='verticalAlign a-size-large priceToPayPadding' or @class='a-size-small price-info-superscript']"))
                $price = $price . '.' . $cent;
        }

        if (strstr($price, ' - '))
        {
            $tprice = explode('-', $price);
            $price = $tprice[0];
        }

        return $price;
    }

    public function parseOldPrice()
    {
        if (!$this->isInStock())
            return;

        $paths = array(
            ".//*[@id='price']//span[@class='a-text-strike']",
            ".//div[@id='price']//td[contains(@class,'a-text-strike')]",
            "(.//*[@id='price']//span[@class='a-text-strike'])[2]",
            ".//*[@id='buyBoxInner']//*[contains(@class, 'a-text-strike')]",
            ".//*[@id='price']//span[contains(@class, 'priceBlockStrikePriceString')]",
            ".//span[@id='rentListPrice']",
            ".//span[@id='listPrice']",
            ".//span[@class='a-price a-text-price']//span[@aria-hidden='true']/text()",
        );
        return $this->xpathScalar($paths);
    }

    public function parseManufacturer()
    {
        $brand = $this->xpathScalar(".//a[@id='brand']");
        if (!$brand)
            $brand = $this->xpathScalar(".//*[@id='byline']//*[contains(@class, 'contributorNameID')]");
        return $brand;
    }

    public function parseImg()
    {
        $paths = array(
            ".//img[@id='miniATF_image']/@src",
            ".//img[@id='landingImage']/@data-old-hires",
            ".//img[@id='landingImage']/@data-a-dynamic-image",
            ".//img[@id='landingImage']/@src",
            ".//img[@id='ebooksImgBlkFront']/@src",
            ".//*[@id='fine-art-landingImage']/@src",
            ".//*[@class='dv-dp-packshot js-hide-on-play-left']//img/@src",
            ".//*[@id='main-image']/@src",
            ".//div[@id='mainImageContainer']/img/@src",
            ".//img[@id='imgBlkFront' and not(contains(@src, 'data:image'))]/@src",
        );

        $img = $this->xpathScalar($paths);

        if (preg_match('/^data:image/', $img))
            $img = '';

        if (preg_match('/"(https:\/\/.+?)"/', $img, $matches))
            $img = $matches[1];

        if (!$img)
        {
            $dynamic = $this->xpathScalar(".//img[@id='landingImage' or @id='imgBlkFront']/@data-a-dynamic-image");
            if (preg_match('/"(https:\/\/.+?)"/', $dynamic, $matches))
                $img = $matches[1];
        }
        if (!$img)
        {
            $img = $this->xpathScalar(".//img[@id='imgBlkFront']/@src");
            if (preg_match('/^data:image/', $img))
                $img = '';
        }

        if (!$img)
        {
            $img = $this->xpathScalar(".//*[contains(@class, 'imageThumb thumb')]/img/@src");
            $img = preg_replace('/\._.+?\_.jpg/', '.jpg', $img);
        }

        $img = str_replace('._SL1500_.', '._AC_SL520_.', $img);
        $img = str_replace('._SL1200_.', '._AC_SL520_.', $img);
        $img = str_replace('._SL1000_.', '._AC_SL520_.', $img);
        $img = str_replace('._AC_SL1500_.', '._AC_SL520_.', $img);

        return $img;
    }

    public function parseImgLarge()
    {
        // return str_replace('._SY300_.jpg', '.jpg', $this->parseImg());
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['categoryPath'] = $this->xpathArray(".//div[@id='wayfinding-breadcrumbs_feature_div']//li//a");;

        $extra['comments'] = array();
        $comments = $this->xpathArray(".//*[contains(@class, 'reviews-content')]//*[contains(@data-hook, 'review-body')]//div[@data-hook]");
        if ($comments)
        {
            $users = $this->xpathArray(".//*[contains(@class, 'reviews-content')]//*[@class='a-profile-name']");
            $dates = $this->xpathArray(".//*[contains(@class, 'reviews-content')]//*[@data-hook='review-date']");
            $ratings = $this->xpathArray(".//*[contains(@class, 'reviews-content')]//*[@data-hook='review-star-rating' or @data-hook='cmps-review-star-rating']");
        }
        else
        {

            $comments = $this->xpathArray(".//*[@id='revMH']//*[contains(@id, 'revData-dpReviewsMostHelpful')]/div[@class='a-section']");
            $users = $this->xpathArray(".//*[@id='revMH']//a[@class='noTextDecoration']");
            $dates = $this->xpathArray(".//*[@id='revMH']//span[@class='a-color-secondary']/span[@class='a-color-secondary']");
            $ratings = $this->xpathArray(".//*[@id='revMH']//span[@class='a-icon-alt']");
        }

        for ($i = 0; $i < count($comments); $i++)
        {
            if (isset($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            $date = $dates[$i];
            if (preg_match('/Reviewed in .+? on (.+)/', $date, $matches))
                $date = $matches[1];
            elseif (preg_match('/(\d.+)/', $date, $matches))
                $date = $matches[1];

            $comment['date'] = strtotime($date);
            $comment['comment'] = sanitize_text_field($comments[$i]);
            $comment['comment'] = preg_replace('/Read\smore$/', '', $comment['comment']);
            if (isset($ratings[$i]))
                $comment['rating'] = $this->prepareRating($ratings[$i]);
            $extra['comments'][] = $comment;
        }
        preg_match("/\/dp\/(.+?)\//msi", $this->getUrl(), $match);
        $extra['item_id'] = isset($match[1]) ? $match[1] : '';

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@id='altImages']//ul/li//span[contains(@data-thumb-action, 'image')]//img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $res = preg_replace('/,\d+_\.jpg/', '.jpg', $res);
                $res = preg_replace('/\._.+?_\.jpg/', '.jpg', $res);
                $extra['images'][] = $this->rewriteSslImageUrl($res);
            }
        }

        $extra['rating'] = $this->prepareRating($this->xpathScalar(".//*[@id='summaryStars']//i/span"));
        if (!$extra['rating'])
            $extra['rating'] = $this->prepareRating((float) $this->xpathScalar(".//*[@id='acrPopover']//i[contains(@class, 'a-icon a-icon-star')]"));

        $extra['ratingDecimal'] = (float) $this->xpathScalar(".//*[@id='acrPopover']//i[contains(@class, 'a-icon a-icon-star')]");

        $extra['ratingCount'] = (int) str_replace(',', '', $this->xpathScalar(".//*[@id='acrCustomerReviewText']"));
        $extra['reviewUrl'] = '';
        if ($asin = self::parseAsinFromUrl($this->getUrl()))
        {
            $url_parts = parse_url($this->getUrl());
            $extra['reviewUrl'] = $url_parts['scheme'] . '://' . $url_parts['host'] . '/product-reviews/' . $asin . '/';
        }

        return $extra;
    }

    public function getFeaturesXpath()
    {

        return array(
            array(
                'name' => ".//table[contains(@id, 'productDetails_techSpec_section')]//th",
                'value' => ".//table[contains(@id, 'productDetails_techSpec_section')]//td",
            ),
            array(
                'name' => ".//table[contains(@id, 'technicalSpecifications_section')]//th",
                'value' => ".//table[contains(@id, 'technicalSpecifications_section')]//td",
            ),
            array(
                'name' => ".//table[contains(@id, 'productDetails_detailBullets_sections')]//th",
                'value' => ".//table[contains(@id, 'productDetails_detailBullets_sections')]//td",
            ),
            array(
                'name-value' => ".//*[@id='productDetailsTable']//li[not(@id) and not(@class)]",
                'separator' => ":",
            ),
            array(
                'name' => ".//*[@id='prodDetails']//td[@class='label']",
                'value' => ".//*[@id='prodDetails']//td[@class='value']",
            ),
            array(
                'name' => ".//*[contains(@id, 'technicalSpecifications_section')]//th",
                'value' => ".//*[contains(@id, 'technicalSpecifications_section')]//td",
            ),
            array(
                'name-value' => ".//div[@id='technical-data']//li",
                'separator' => ":",
            ),
            array(
                'name-value' => ".//div[@id='detail-bullets']//li",
                'separator' => ":",
            ),
            array(
                'name' => ".//div[@id='detailBullets_feature_div']//li/span/span[1]",
                'value' => ".//div[@id='detailBullets_feature_div']//li/span/span[2]",
            ),
        );
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//div[@id='availability']/span[contains(@class,'a-color-success')]"))
            return true;

        $availability = trim($this->xpathScalar(".//div[@id='availability']/span/text()"));

        if ($availability == 'Currently unavailable.' || $availability == 'Şu anda mevcut değil.' || $availability == 'Attualmente non disponibile.' || $availability == 'Momenteel niet verkrijgbaar.')
            return false;

        return true;
    }

    private function prepareRating($rating_str)
    {
        $rating_parts = explode(' ', $rating_str);
        return TextHelper::ratingPrepare($rating_parts[0]);
    }

    private function rewriteSslImageUrl($img)
    {
        return str_replace('http://ecx.images-amazon.com', 'https://images-na.ssl-images-amazon.com', $img);
    }

    public static function parseAsinFromUrl($url)
    {
        $regex = '~/(?:exec/obidos/ASIN/|o/|gp/product/|gp/offer-listing/|(?:(?:[^"\'/]*)/)?dp/|)(B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(X|0-9])|[0-9]{10})(?:(?:/|\?|\#)(?:[^"\'\s]*))?~isx';
        if (preg_match($regex, $url, $matches))
            return $matches[1];
        else
            return false;
    }

    public function getCurrency()
    {
        if (strstr($this->parsePrice(), 'USD'))
            return 'USD';

        if (strstr($this->parsePrice(), 'AUD'))
            return 'AUD';

        return $this->currency;
    }
}
