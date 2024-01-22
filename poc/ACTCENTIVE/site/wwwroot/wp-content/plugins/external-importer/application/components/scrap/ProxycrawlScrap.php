<?php

namespace ExternalImporter\application\components\scrap;

defined('\ABSPATH') || exit;

/**
 * ProxycrawlScrap class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ProxycrawlScrap extends Scrap {

    const SLUG = 'proxycrawl';

    public function doAction($url, $args)
    {
        if (!$this->needSendThrough($url))
            return $url;
        
        $url = 'https://api.proxycrawl.com/?token=' . urlencode($this->getToken()) . '&url=' . urlencode($url);
        
        if (!empty($args['headers']))
        {
            $headers = array();
            foreach ($args['headers'] as $name => $value)            
            {
                $headers[] = $name . ':' . $value;
            }
            $url .= '&request_headers=' . urlencode(join('|', $headers));
        }
        
        $url = \apply_filters('ei_parse_url_' . $this->getSlug(), $url);

        return $url;
    }

}
