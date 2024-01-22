<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\SyncConfig;
use ExternalImporter\application\components\Synchronizer;
use ExternalImporter\application\helpers\WooHelper;

/**
 * SyncFrontend class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class SyncFrontend {

    public static function initAction()
    {
        \add_filter('template_redirect', array(__CLASS__, 'update'), 10);
    }

    public static function update()
    {
        global $post;

        if (!\is_singular(array('product')))
            return;

        if (SyncConfig::getInstance()->option('update_mode') != 'frontend')
            return;

        if (!WooHelper::isEiProduct($post->ID))
            return;

        Synchronizer::maybeUpdateProduct($post->ID);
    }

}
