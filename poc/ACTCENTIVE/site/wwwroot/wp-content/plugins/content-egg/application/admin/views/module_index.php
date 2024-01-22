<?php defined('\ABSPATH') || exit; ?>

<?php

function _cegg_print_module_item(array $modules)
{
    foreach ($modules as $module)
    {
        echo '<a href="?page=' . \esc_attr($module->getConfigInstance()->page_slug()) . '" class = "list-group-item">';
        echo \esc_html($module->getName());
        if ($module->isActive() && !$module->isDeprecated())
            echo '<span class="label label-success">' . esc_html(__('Active', 'content-egg')) . '</span>';
        if ($module->isDeprecated())
            echo '<span class="label label-warning">' . esc_html(__('Deprecated', 'content-egg')) . '</span>';
        if ($module->isNew() && !$module->isFeedParser())
            echo '<span class="label label-info">' . esc_html(__('New', 'content-egg')) . '</span>';
        echo '</a>';
    }
}
?>

<?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    <div class="cegg-maincol">
    <?php endif; ?>


    <div class="wrap">

        <h2>
            <?php esc_html_e('Module Settings', 'content-egg'); ?>
            <span class="egg-label egg-label-pro">pro <small>v<?php echo esc_html(\ContentEgg\application\Plugin::version()); ?></small></span>
        </h2>


        <h2 class="nav-tab-wrapper">
            <a href="?page=content-egg-modules" class="nav-tab<?php if (!empty($_GET['page']) && sanitize_key(wp_unslash($_GET['page'])) == 'content-egg-modules') echo ' nav-tab-active'; ?>">
                <span class="dashicons dashicons-menu-alt3"></span>
            </a>
            <?php foreach (ContentEgg\application\components\ModuleManager::getInstance()->getConfigurableModules(true) as $m): ?>
                <?php if ($m->isDeprecated() && !$m->isActive()) continue; ?>
                <?php $c = $m->getConfigInstance(); ?>
                <a href="?page=<?php echo \esc_attr($c->page_slug()); ?>" class="nav-tab<?php if (!empty($_GET['page']) && sanitize_key(wp_unslash($_GET['page'])) == $c->page_slug()) echo ' nav-tab-active'; ?>">
                    <span<?php if ($m->isDeprecated()): ?> style="color: darkgray;"<?php endif; ?>>
                        <?php echo \esc_html($m->getName()); ?>                    
                    </span>
                </a>
            <?php endforeach; ?>
        </h2>

        <br />        
        <div class="egg-container">
            <div class="row">
                <div class="col-md-4 col-xs-12">

                    <div class="panel panel-default">
                        <div class="panel-heading"><h3 class="panel-title"><?php esc_html_e('Product modules', 'content-egg'); ?></h3></div>
                        <div class="list-group">
                            <?php _cegg_print_module_item(\ContentEgg\application\helpers\AdminHelper::getProductModules()); ?>
                        </div>
                    </div>    

                </div>
                <div class="col-md-4 col-xs-12">

                    <?php if ($modules = \ContentEgg\application\helpers\AdminHelper::getAeProductModules()): ?>
                        <div class="panel panel-default">
                            <div class="panel-heading"><h3 class="panel-title"><?php esc_html_e('Affiliate Egg modules', 'content-egg'); ?></h3></div>
                            <div class="list-group">
                                <?php _cegg_print_module_item($modules); ?>
                            </div>
                        </div>                     
                    <?php endif; ?>

                    <div class="panel panel-default">
                        <div class="panel-heading"><h3 class="panel-title"><?php esc_html_e('Feed modules', 'content-egg'); ?></h3></div>
                        <div class="list-group">
                            <?php _cegg_print_module_item(\ContentEgg\application\helpers\AdminHelper::getFeedProductModules()); ?>
                        </div>
                    </div>       

                    <div class="panel panel-default">
                        <div class="panel-heading"><h3 class="panel-title"><?php esc_html_e('Coupon modules', 'content-egg'); ?></h3></div>
                        <div class="list-group">
                            <?php _cegg_print_module_item(\ContentEgg\application\helpers\AdminHelper::getCouponModules()); ?>
                        </div>
                    </div>  

                </div>

                <div class="col-md-4 col-xs-12">

                    <div class="panel panel-default">
                        <div class="panel-heading"><h3 class="panel-title"><?php esc_html_e('Content modules', 'content-egg'); ?></h3></div>
                        <div class="list-group">
                            <?php _cegg_print_module_item(\ContentEgg\application\helpers\AdminHelper::getContentModules()); ?>
                        </div>
                    </div>                     

                </div>

            </div>
        </div>
    </div>

    <?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    </div>    
    <?php include('_promo_box.php'); ?>
<?php endif; ?>  