<?php defined('\ABSPATH') || exit; ?>

<?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    <div class="cegg-maincol">
    <?php endif; ?>
    <div class="wrap">
        <h2>
            <?php  esc_html_e('Module Settings', 'content-egg'); ?>
            <span class="egg-label egg-label-pro">pro <small>v<?php echo esc_html(\ContentEgg\application\Plugin::version()); ?></small></span>
        </h2>

        <h2 class="nav-tab-wrapper">
            <a href="?page=content-egg-modules" class="nav-tab<?php if (!empty($_GET['page']) && $_GET['page'] == 'content-egg-modules') echo ' nav-tab-active'; ?>">
                <span class="dashicons dashicons-menu-alt3"></span>
            </a>
            <?php foreach (ContentEgg\application\components\ModuleManager::getInstance()->getConfigurableModules(true) as $m): ?>
                <?php if ($m->isDeprecated() && !$m->isActive()) continue; ?>
                <?php $c = $m->getConfigInstance(); ?>
                <a href="?page=<?php echo \esc_attr($c->page_slug()); ?>" class="nav-tab<?php if (!empty($_GET['page']) && $_GET['page'] == $c->page_slug()) echo ' nav-tab-active'; ?>">
                    <span<?php if ($m->isDeprecated()): ?> style="color: darkgray;"<?php endif; ?>>
                        <?php echo \esc_html($m->getName()); ?>                    
                    </span>
                </a>
            <?php endforeach; ?>
        </h2> 

        <div class="cegg-wrap">
            <div class="cegg-maincol">

                <h3>
                    <?php if ($module->isFeedParser() && !$module->isActive()): ?>
                        <?php  esc_html_e('Add new feed module', 'content-egg'); ?>
                    <?php else: ?>
                        <?php echo \esc_html(sprintf(__('%s Settings', 'content-egg'), $module->getName())); ?>
                    <?php endif; ?>
                    <?php if ($docs_uri = $module->getDocsUri()) echo sprintf('<a target="_blank" class="page-title-action" href="%s">' . esc_html(__('Documentation', 'content-egg')) . '</a>', esc_url_raw($docs_uri)); ?>
                </h3>

                <?php if ($module->isDeprecated()): ?>
                    <div class="cegg-warning">

                        <?php if ($module->getId() == 'Amazon'): ?>
                            <?php esc_html_e('WARNING:', 'content-egg'); ?>
                            <?php echo sprintf(__('Amazon PA-API v4 <a target="_blank" href="%s"> is deprecated</a>.', 'content-egg'), 'https://webservices.amazon.com/paapi5/documentation/faq.html'); ?>
                            <?php echo sprintf(__('Only <a target="_blank" href="%s">Content Egg Pro</a> has support for the new PA-API v5.', 'content-egg'), 'https://www.keywordrush.com/contentegg/pricing'); ?>
                            <?php esc_html_e('Please', 'content-egg'); ?> <a target="_blank" href="https://ce-docs.keywordrush.com/modules/affiliate/amazon#why-amazon-module-is-not-available-in-ce-free-version"><?php esc_html_e('read more...', 'content-egg'); ?></a>
                        <?php endif; ?>

                        <?php if ($module->getId() != 'Amazon'): ?>
                            <strong>
                                <?php esc_html_e('WARNING:', 'content-egg'); ?>
                                <?php esc_html_e('This module is deprecated', 'content-egg'); ?>
                                (<a target="_blank" href="<?php echo esc_url_raw(\ContentEgg\application\Plugin::pluginDocsUrl()); ?>/modules/deprecatedmodules"><?php esc_html_e('what does this mean', 'content-egg'); ?></a>).
                            </strong>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($module) && $requirements = $module->requirements()): ?>
                    <div class="cegg-warning">  
                        <strong>
                            <?php echo esc_html_e('WARNING:', 'content-egg'); ?>
                            <?php esc_html_e('This module cannot be activated!', 'content-egg') ?>
                            <?php esc_html_e('Please fix the following error(s):', 'content-egg') ?>
                            <ul>
                                <li><?php echo wp_kses_post(join('</li><li>', $requirements)); ?></li>
                            </ul>

                        </strong>
                    </div>
                <?php endif; ?>                            

                <?php \settings_errors(); ?>   
                <form action="options.php" method="POST">
                    <?php \settings_fields($config->page_slug()); ?>
                    <table class="form-table">
                        <?php \do_settings_sections($config->page_slug()); ?>								
                    </table>        
                    <?php \submit_button(); ?>
                </form>

            </div>

            <div class="cegg-rightcol">
                <div>
                    <?php
                    if (!empty($description))
                        echo '<p>' . wp_kses_post($description) . '</p>';
                    ?>

                    <?php if (!empty($module) && $module->isFeedModule()): ?>
                        <?php if ($last_date = $module->getLastImportDateReadable()): ?>
                            <?php $prod_count = $module->getProductCount(); ?>
                            <li><?php echo esc_html(sprintf(__('Last feed import: %s.', 'content-egg'), $last_date)); ?></li>
                            <li><?php echo esc_html(sprintf(__('Total products: %d.', 'content-egg'), $prod_count)); ?></li>
                        <?php endif; ?>
                        <li title="<?php echo \esc_attr(__('Your unzipped feed must be smaller than this.', 'content-egg')); ?>"><?php echo esc_html(sprintf(__('WordPress memory limit: %s', 'content-egg'), \WP_MAX_MEMORY_LIMIT)); ?>
                            (<a href="https://wordpress.org/support/article/editing-wp-config-php/#increasing-memory-allocated-to-php" target="_blank">?</a>)
                        </li>                                        
                        <?php if ($last_error = $module->getLastImportError()): ?>
                            <li style="color: red;"><?php echo esc_html(sprintf(__('Last error: %s', 'content-egg'), $last_error)); ?></li>
                        <?php endif; ?>    

                        <?php if ($last_date && $prod_count): ?>
                            <hr /><br />
                            <div><a target="_blank" class="page-title-action" href="<?php echo esc_url_raw(\get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-tools&action=feed-export&field=url&module=' . urlencode($module->getId()))); ?>"><?php esc_html_e('Export product URLs', 'content-egg') ?></a></div>
                            <br />
                            <div><a target="_blank" class="page-title-action" href="<?php echo esc_url_raw(\get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-tools&action=feed-export&field=ean&module=' . urlencode($module->getId()))); ?>"><?php esc_html_e('Export product EANs', 'content-egg') ?></a></div>
                            <br />
                            <div><a target="_blank" class="page-title-action" href="<?php echo esc_url_raw(\get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-tools&action=feed-export&field=ean_dublicate&module=' . urlencode($module->getId()))); ?>"><?php esc_html_e('Export duplicate EANs', 'content-egg') ?></a></div>
                        <?php endif; ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>


    </div>


    <?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    </div>    
    <?php include('_promo_box.php'); ?>
<?php endif; ?>