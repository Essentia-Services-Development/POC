<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * FnaccomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class FnaccomParser extends LdShopParser
{

    protected $charset = 'utf-8';
    protected $currency = 'EUR';
    protected $user_agent = array('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:107.0) Gecko/20100101 Firefox/107.0');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Cookie' => 'kacd_FRPRD_DEFAZ=3848392752~rv=59~id=b7ef6494191129f52f3902359544623d; QueueITAccepted-SDFrts345E-V3_frprdfnaccom=EventId%3Dfrprdfnaccom%26QueueId%3D4fa059ef-a183-494f-9486-a289c295f6e8%26RedirectType%3Dsafetynet%26IssueTime%3D1670940007%26Hash%3D6624277454894e952d2b3eaed6bc846493a8cace402e6b9e6fa51815a8245405; datadome=2BFFZS8lumjWCf6f4jbHaBN20WMbO0taINx8WJdkBw7S7X9hpaeUVKu59R-QebzB350DKzYttBU~JSizdmv-~zf-21h4rjpZ12T1mTMXgyalK3RzqMKisfsl3Vqa6lQs; ORGN=FnacAff; OrderInSession=0; kameleoonVisitorCode=tia907…862; cto_bundle=Y0RT3V92RHFUQzN1NExxYW5kcFpIYnF6RmRCdEdrbUViblFzRzRBeiUyRkZjbEZPbFVrUiUyRkJhZEVERTljUklQVXdvQ0JZMiUyRjBPQXViNGk0NVQxNGNIRFI2V2pxY3MwZWNGY0h1MFRnb0xRVnBrTm1ZMTZLcCUyQkZ3dmttMmxUajl0TlY4Skh3cHE3THVZOWR6bWpsTkFramVZTVFtRnZQajRockJ4SmhGWXo3V2FVM2dRYUloOHhoakxkVDYxYjQ4SW5LQmdKRg; cto_pxsig=9MtrlMEV9H_c8JpHSlTsFw; cto_bidid=Z2OHj19oWmJqTkpqZSUyRkdvUWg2JTJCVEViWTZNSllVdjZKUWg2QW44S1JhenlobjJJZm5BcWxTUFJVcVp4Y2o0aVglMkZEa1FjZ0lLMTRpcWpJJTJCUDM0dld5ejZLNG9HMERCRm5McSUyRjZBblA4JTJGMzElMkIlMkZFS2MlM0Q',
    );

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//p[@class='Article-desc']/a/@href");
    }

    public function parseOldPrice()
    {
        $p = $this->xpathScalar(array(".//*[@class='f-priceBox']//*[@class='f-priceBox-price f-priceBox-price--old']", ".//span[@class='f-priceBox-price f-priceBox-price--reco f-priceBox-price--alt']", ".//*[contains(@class, 'f-priceBox-price--old')]"), true);
        return str_replace('&euro;', '.', $p);
    }

    public function parseManufacturer()
    {
        if (isset($this->_ld['brand']['name']))
            return $this->_ld['brand']['name'];
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();
        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@class='f-productDetails-table']//td[1]");
        $values = $this->xpathArray(".//*[@class='f-productDetails-table']//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = \sanitize_text_field($names[$i]);
                $feature['value'] = \sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        return $extra;
    }
}
