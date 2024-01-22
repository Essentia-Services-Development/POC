<?php

namespace ExternalImporter\application\components\scrap;

defined('\ABSPATH') || exit;

/**
 * ScrapingdogScrap class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ScrapingdogScrap extends Scrap {

    const SLUG = 'scrapingdog';

    public function doAction($url, $args)
    {
        if (!$this->needSendThrough($url))
            return $url;

        $url = 'https://api.scrapingdog.com/scrape?api_key=' . urlencode($this->getToken()) . '&url=' . urlencode($url) . '&custom_headers=true';
        $url = \apply_filters('ei_parse_url_' . $this->getSlug(), $url);

        return $url;
    }

}
