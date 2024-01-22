<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * BestbuycomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class BestbuycomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    protected $user_agent = 'Wget';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'Cookie' => 'tfs_upg=true; UID=ec3c7c4c-beca-4b23-926c-ecfbff1498f5; CTT=830f1ce6f5720443d439553bb592ffd6; bby_rdp=l; AMCV_F6301253512D2BDB0A490D45%40AdobeOrg=1585540135%7CMCMID%7C46120550392310190720024771003903546372%7CMCAAMLH-1592461420%7C6%7CMCAAMB-1592461420%7Cj8Odv6LonN4r3an7LhD3WZrU1bUpAkFkkiY1ncBR96t2PTI%7CMCOPTOUT-1591863820s%7CNONE%7CMCAID%7CNONE%7CMCCIDH%7C-1213955086%7CvVersion%7C4.4.0; SID=3d7669b6-1cfc-4b21-a0da-41fa790fcc00; _abck=20B3EB2A1F9267CAD62D9CDABDAD4303~0~YAAQBNT1V9yzaYNyAQAAVmUMogRF7PfkxPPJSqsYA5pj+AOLUChuBkftAlq8T0Yp/Q6WArev1XAz67T1b7ovE5LwRWp/0PUym7teXCvE8wcKrA+hiHyHwAH95mkbb7OXUfbUMpAI4I6hBnYgfHr2XKg+hINGd3Ab8ZSnMxDOEwjjso45e79XfIf83JwdOBduHDhy610GTawVOjr0DsxeIqFpyiwXBf86lKJkR0I3wTh0/FZoWFUTMeFNPScTzQT6Gg4oKn0h/WO603+GRJx9s4qmKS3Y1DFyHmiq2eJlFOu6UPo58I7z/vs/acZNjQv8LOlHz+PWh4g=~-1~-1~-1; bm_sz=A5D09A585548B7D051F367C661115643~YAAQBNT1V9mzaYNyAQAAYl8Mogi1BsULstsDD4BnDnw7aErUYqb+ke3D8XYA9OhnmVHuY1vmcjQI3GpNFsUrHT9gSQ1raKaH9UQUAPNFLvBJCpUVBwxv3tZFeZk0THkVa4FjuJAiKKttUQ5kNx6ioB8FNYQMD83L3KzGK7BToc/1RHqpjR1m0+7T1VZDlsXT2A==; bby_cbc_lb=p-browse-e; vt=171b973d-abac-11ea-a446-0ef288d55923; AMCVS_F6301253512D2BDB0A490D45%40AdobeOrg=1; s_ecid=MCMID%7C46120550392310190720024771003903546372; c2=Computers%20%26%20Tablets%3A%20Computer%20Cards%20%26%20Components%3A%20CPUs%20%2F%20Processors%3A%20pdp; s_cc=true; aam_uuid=52702997647788854740683020136104639782; 52245=; _gcl_au=1.1.246044671.1591856621; s_sq=%5B%5BB%5D%5D; intl_splash=false; ltc=%20; oid=95476883; globalUserTransition=default; optimizelyEndUserId=oeu1591856632053r0.7769520364434369; COM_TEST_FIX=2020-06-11T06%3A23%3A52.343Z; bby_prc_lb=p-prc-e; basketTimestamp=1591856633691; __gads=ID=a503bc0f5c4fbb9f:T=1591856636:S=ALNI_MattP14znTxXpWzu1HD8nDhaRz3Mw; cto_bundle=gmmHAV9GVURPT3VqS3B6QTVKbG80VlVFRnNpcGxXem4wYUlQYWhEeWR4S0dBZ2taalhlUzZESW9LU3l1d1NaTyUyQlNuMTFYYlp0ZDIlMkZadEhZUFpwb1RRdGlvWnViYVo3OUl6WklQdEZOZTBBdkhBcEJEdFlmQ0slMkJvRkpTSUt2YTdFUG1rU1BlVVQlMkJvSnZ1Sm5Wb05yR0VQRkh1dyUzRCUzRA; _cs_c=1; _cs_id=d8298f59-aa48-aec3-c042-f4469ad0ad34.1591856639.1.1591856639.1591856639.1.1626020639849.Lax.0; _cs_s=1.1',
    );

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//h4[@class='sku-header']/a/@href");
    }

    public function parseTitle()
    {
        if ($p = parent::parseTitle())
            return $p;
        
        return str_replace('- Best Buy', '', $this->xpathScalar(".//h1"));
    }

    public function parseDescription()
    {
        if ($p = parent::parseDescription())
            return $p;
        
        $desc = $this->xpathScalar(".//*[@itemprop='description']");
        if (!$desc)
            $desc = $this->xpathScalar(".//*[@id='long-description']");
        return $desc;
    }

    public function parsePrice()
    {
        if ($p = parent::parsePrice())
            return $p;
        
        $paths = array(
            ".//*[@class='priceView-hero-price priceView-customer-price']/span[1]",
            ".//*[@class='pb-hero-price pb-purchase-price']",
            ".//*[contains(@class, 'priceView-hero-price')]/span",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $price = str_replace('Was', '', $this->xpathScalar(".//*[@class='pricing-price__regular-price']"));
        return $price;
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//meta[@id='schemaorg-brand-name']/@content");
    }

}
