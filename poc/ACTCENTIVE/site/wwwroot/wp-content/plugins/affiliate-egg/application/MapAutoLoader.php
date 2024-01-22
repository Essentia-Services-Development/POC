<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MapAutoLoader class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class MapAutoLoader {

    private static $classMap = array(
        'AffiliateEgg' => 'application/AffiliateEgg.php',
        'AffiliateEggAdmin' => 'application/admin/AffiliateEggAdmin.php',
        'Plugin' => 'application/Plugin.php',
        'PluginAdmin' => 'application/admin/PluginAdmin.php',
        'LManager' => 'application/admin/LManager.php',
        'EggController' => 'application/admin/EggController.php',
        'HelpController' => 'application/admin/HelpController.php',
        'AutoblogController' => 'application/admin/AutoblogController.php',
        'LicConfig' => 'application/admin/LicConfig.php',
        'DeeplinkConfig' => 'application/admin/DeeplinkConfig.php',
        'GeneralConfig' => 'application/admin/GeneralConfig.php',
        'ProxyConfig' => 'application/admin/ProxyConfig.php',
        'ExtractorConfig' => 'application/admin/ExtractorConfig.php',
        'CookiesConfig' => 'application/admin/CookiesConfig.php',
        'Config' => 'application/admin/Config.php',
        'ShopManager' => 'application/shop/ShopManager.php',
        'Shop' => 'application/shop/Shop.php',
        'MyListTable' => 'application/admin/MyListTable.php',
        'EggTable' => 'application/admin/EggTable.php',
        'EggThickboxTable' => 'application/admin/EggThickboxTable.php',
        'AutoblogTable' => 'application/admin/AutoblogTable.php',
        'FormValidator' => 'application/admin/FormValidator.php',
        'EggModel' => 'application/models/EggModel.php',
        'CatalogModel' => 'application/models/CatalogModel.php',
        'ProductModel' => 'application/models/ProductModel.php',
        'Model' => 'application/models/Model.php',
        'CVarDumper' => 'application/vendor/CVarDumper.php',
        'ShopParser' => 'application/shop/ShopParser.php',
        'LdShopParser' => 'application/shop/LdShopParser.php',
        'MicrodataShopParser' => 'application/shop/MicrodataShopParser.php',
        'ParserManager' => 'application/shop/ParserManager.php',
        'Shortcode' => 'application/Shortcode.php',
        'TemplateManager' => 'application/TemplateManager.php',
        'EggManager' => 'application/admin/EggManager.php',
        'EnvatoConfig' => 'application/admin/EnvatoConfig.php',
        'TemplateHelper' => 'application/TemplateHelper.php',
        'Cpa' => 'application/shop/Cpa.php',
        'Scheduler' => 'application/Scheduler.php',
        'Autoupdate' => 'application/Autoupdate.php',
        'TextHelper' => 'application/TextHelper.php',
        'FileHelper' => 'application/FileHelper.php',
        'FeaturedImage' => 'application/FeaturedImage.php',
        'AutoblogModel' => 'application/models/AutoblogModel.php',
        'AutoblogItemModel' => 'application/models/AutoblogItemModel.php',
        'EggThickboxEditorButton' => 'application/admin/EggThickboxEditorButton.php',
        'LinkHandler' => 'application/LinkHandler.php',
        'TesterController' => 'application/admin/TesterController.php',
        'CustomDeeplink' => 'application/shop/CustomDeeplink.php',
        'CustomFields' => 'application/CustomFields.php',
        'CurrencyHelper' => 'application/CurrencyHelper.php',
        'PriceHistoryModel' => 'application/models/PriceHistoryModel.php',
        'PriceAlertModel' => 'application/models/PriceAlertModel.php',
        'PriceAlert' => 'application/PriceAlert.php',
        'InputHelper' => 'application/InputHelper.php',
        'ImageHelper' => 'application/ImageHelper.php',
        'CurlProxy' => 'application/CurlProxy.php',
        'GimmeproxyApi' => 'application/GimmeproxyApi.php',
        'RestClient' => 'application/RestClient.php',
        'WpHttpClient' => 'application/WpHttpClient.php',
        'AdminNotice' => 'application/admin/AdminNotice.php',
        'ProxycrawlScrap' => 'application/lib/scrap/ProxycrawlScrap.php',
        'Scrap' => 'application/lib/scrap/Scrap.php',
        'ScrapFactory' => 'application/lib/scrap/ScrapFactory.php',
        'ScrapingdogScrap' => 'application/lib/scrap/ScrapingdogScrap.php',
        'ScraperapiScrap' => 'application/lib/scrap/ScraperapiScrap.php',
    );

    public function __construct()
    {
        $this->register_auto_loader();
    }

    public function register_auto_loader()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    public static function autoload($className)
    {
        if (strpos($className, __NAMESPACE__) !== 0)
            return false;
        $className = str_replace(__NAMESPACE__, '', $className);
        $className = ltrim($className, '\\');

        if (isset(self::$classMap[$className]))
            include(PLUGIN_PATH . self::$classMap[$className]);
        else
            return false;
    }

}
