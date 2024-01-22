<?php defined('\ABSPATH') || exit; ?>
<?php
$configs = array(
    ExternalImporter\application\admin\GeneralConfig::class,
    ExternalImporter\application\admin\WooConfig::class,
    ExternalImporter\application\admin\SyncConfig::class,
    ExternalImporter\application\admin\DeeplinkConfig::class,
    ExternalImporter\application\admin\ParserConfig::class,
    ExternalImporter\application\admin\FrontendConfig::class,
    ExternalImporter\application\admin\DropshippingConfig::class,
);

$user_guides = array(
    'external-importer-settings-woo' => 'https://ei-docs.keywordrush.com/importing-to-woocommerce/general-information',
    'external-importer-settings-sync' => 'https://ei-docs.keywordrush.com/synchronizing-products/general-information',
    'external-importer-settings-deeplink' => 'https://ei-docs.keywordrush.com/monetization/creating-affiliate-links',
    'external-importer-settings-parser' => 'https://ei-docs.keywordrush.com/extracting-products/how-to-avoid-getting-blocked',
    'external-importer-settings-frontend' => 'https://ei-docs.keywordrush.com/frontend/general-information',
    'external-importer-settings-dropshipping' => 'https://ei-docs.keywordrush.com/monetization/dropshipping',
);
?>

<div class="wrap">
    <h2>
        <?php _e('External Importer Settings', 'external-importer'); ?>
    </h2>

    <h2 class="nav-tab-wrapper">
        <?php foreach ($configs as $class): ?>

            <a href="?page=<?php echo \esc_attr($class::getInstance()->page_slug()); ?>" 
               class="nav-tab<?php if (!empty($_GET['page']) && $_GET['page'] == $class::getInstance()->page_slug()) echo ' nav-tab-active'; ?>">
                   <?php echo $class::getInstance()->header_name(); ?>
            </a>        
        <?php endforeach; ?>

    </h2> 

    <div class="ui-sortable meta-box-sortables">
        <div class="postbox1">
            <div class="inside">
                <?php \settings_errors(); ?>   

                <?php if (isset($user_guides[$page_slug])): ?>
                    <div class="eimporter-user-guide">
                        <br />
                        <span class="dashicons dashicons-info"></span> <a target="_blank" href="<?php echo \esc_url($user_guides[$page_slug]); ?>"><?php _e('User guide', 'external-importer'); ?></a>
                    </div>
                <?php endif; ?>

                <form action="options.php" method="POST">
                    <?php \settings_fields($page_slug); ?>
                    <table class="form-table">
                        <?php \do_settings_sections($page_slug); ?> 									
                    </table>        
                    <?php \submit_button(); ?>
                </form>

            </div>
        </div>
    </div>   
</div>
