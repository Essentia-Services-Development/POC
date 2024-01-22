<?php defined('\ABSPATH') || exit; ?>

<?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    <div class="cegg-maincol">
    <?php endif; ?>
    <div class="wrap">
        <h2>
            <?php esc_html_e('Content Egg Settings', 'content-egg'); ?>
            <?php if (\ContentEgg\application\Plugin::isPro()): ?>
            <span class="egg-label egg-label-pro">pro <small>v<?php echo esc_html(\ContentEgg\application\Plugin::version()); ?></small></span>
            <?php else: ?>
                <a target="_blank" class="page-title-action" href="<?php echo esc_url_raw(\ContentEgg\application\Plugin::pluginSiteUrl()); ?>">Go PRO</a>
            <?php endif; ?>
        </h2>

        <?php \settings_errors(); ?>   
        <form action="options.php" method="POST">
            <?php \settings_fields($page_slug); ?>
            <?php \ContentEgg\application\helpers\AdminHelper::doTabsSections($page_slug); ?>
            <?php \submit_button(); ?>
        </form>
    </div>

    <?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    </div>    
    <?php include('_promo_box.php'); ?>
<?php endif; ?>