<?php

namespace ExternalImporter\application\components\scrap;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\admin\ParserConfig;

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

        \add_action('ei_create_from_url', array($this, 'doAction'), 10, 2);
    }

    public function needSendThrough($url)
    {
        $option_name = $this->getSlug() . '_domains';

        if (!$domains = TextHelper::commaListArray(ParserConfig::getInstance()->option($option_name)))
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
        return ParserConfig::getInstance()->option($option_name);
    }

}
