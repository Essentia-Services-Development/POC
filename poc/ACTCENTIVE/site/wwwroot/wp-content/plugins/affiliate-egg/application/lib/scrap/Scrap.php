<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;


/**
 * Scrap class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
abstract class Scrap {

    const SLUG = '';

    abstract public function doAction($url, $args);

    public function getSlug()
    {
        return static::SLUG;
    }

    public function initAction()
    {
        if (!$this->getToken())
            return;

        \add_action('affegg_create_from_url', array($this, 'doAction'), 10, 2);
    }

    public function needSendThrough($url)
    {
        $option_name = $this->getSlug() . '_domains';

        if (!$domains = TextHelper::commaListArray(ExtractorConfig::getInstance()->option($option_name)))
            return false;

        $host = TextHelper::getHostName($url);
        if (in_array($host, $domains))
            return true;
        else
            return false;
    }

    public function getToken()
    {
        $option_name = $this->getSlug() . '_token';
        return ExtractorConfig::getInstance()->option($option_name);
    }

}
