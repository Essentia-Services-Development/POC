<?php defined('\ABSPATH') || exit; ?>
<?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    <div class="cegg-maincol">
    <?php endif; ?>
    <div class="wrap">
        <h2><?php  esc_html_e('Integration with Affiliate Egg', 'content-egg') ?></h2>
        <?php settings_errors(); ?>

        <?php if (!ContentEgg\application\admin\AeIntegrationConfig::isAEIntegrationPosible()): ?>

            <p>
                <a target="_blank" href="https://www.keywordrush.com/affiliateegg">Affiliate Egg</a> is another plugin our team offers for adding affiliate products to your website. The biggest advantages of Affiliate Egg:
            </p>
            <ul>
                <ol>No API access required. Extracts data directly from store websites.</ol>
                <ol>Can create custom parsers for almost any store.</ol>
                <ol>Affiliate Egg parsers can be connected as separate Content Egg plugin modules. This will allow for product price updates, price comparisons, all templates, and other Content Egg features.</ol>
            </ul>
            <p>
                You can activate AE parsers as separate modules for Content Egg.
            </p>
            <a target="_blank" href="https://ce-docs.keywordrush.com/modules/affiliate-egg-integration"><?php esc_html_e('Read more...', 'content-egg'); ?></a>
        <?php endif; ?>

        <?php if (!ContentEgg\application\admin\AeIntegrationConfig::isAEIntegrationPosible()): ?>
            <div>
                <b><?php esc_html_e('Follow these steps to get started', 'content-egg'); ?>:</b>
            <ol>
                <li><?php echo sprintf(__('Install and activate <a target="_blank" href="%s">Affiliate Egg Pro</a>', 'content-egg'), 'https://www.keywordrush.com/affiliateegg'); ?></li>
            </ol>
            </div>
        <?php else: ?>
            <form action="options.php" method="POST">
                <?php settings_fields($page_slug); ?>
                <table class="form-table">
                    <?php \do_settings_fields($page_slug, 'default'); ?>
                </table>
                <?php submit_button(); ?>
            </form>   
        <?php endif; ?>
    </div>
    <?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    </div>    
    <?php include('_promo_box.php'); ?>
<?php endif; ?>          