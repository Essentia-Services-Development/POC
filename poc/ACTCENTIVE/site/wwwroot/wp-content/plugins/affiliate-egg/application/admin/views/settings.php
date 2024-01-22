<?php defined('\ABSPATH') || exit; ?>
<?php
$pages = array(
    'affiliate-egg-deeplink-settings' => array(
        'slug' => 'affiliate-egg-deeplink-settings',
        'name' => __('Deeplink Settings', 'affegg'),
    ),
    'affiliate-egg-cookies-settings' => array(
        'slug' => 'affiliate-egg-cookies-settings',
        'name' => __('Custom Cookies', 'affegg'),
    ),
    'affiliate-egg-proxy-settings' => array(
        'slug' => 'affiliate-egg-proxy-settings',
        'name' => __('Proxy Settings', 'affegg'),
    ),
    'affiliate-egg-extractor-settings' => array(
        'slug' => 'affiliate-egg-extractor-settings',
        'name' => __('Extractor Settings', 'affegg'),
    ),
);
?>
<div class="wrap">
    <h2><?php _e('Affiliate Egg Settings', 'affegg'); ?></h2>

    <h2 class="nav-tab-wrapper">
        <a href="?page=affiliate-egg-settings" class="nav-tab<?php if (!empty($_GET['page']) && $_GET['page'] == 'affiliate-egg-settings') echo ' nav-tab-active'; ?>">
            <?php _e('General settings', 'affegg'); ?>
        </a>
        <?php foreach ($pages as $page) : ?>
            <a href="?page=<?php echo esc_attr($page['slug']); ?>" class="nav-tab<?php if (!empty($_GET['page']) && $_GET['page'] == $page['slug']) echo ' nav-tab-active'; ?>">
                <?php echo \esc_html($page['name']); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <div class="ui-sortable meta-box-sortables">
        <h3>
            <?php
            if (!empty($_GET['page']) && $_GET['page'] == 'affiliate-egg-settings')
                _e('General settings', 'affegg');
            elseif (isset($pages[$_GET['page']]))
                echo \esc_html($pages[$_GET['page']]['name']);
            ?>
        </h3>

        <?php \settings_errors(); ?>

        <?php
        if (isset($pages[$_GET['page']]))
            $page = $pages[$_GET['page']];
        else
            $page = '';
        ?>

        <?php if ($page && $page['slug'] == 'affiliate-egg-deeplink-settings') : ?>
            <div id="poststuff">
                <p>
                    <?php _e('This is list of supported shops', 'affegg') ?>
                    <?php _e('To receive the comissions for a partnership transitions and sale of products from the links, you must specify Deeplink of CPA-network for each store.', 'affegg') ?>
                </p>
                <p>
                    <?php _e('Also you can use affiliate programs of each shops. For this, add your partner affiliate ID which will be added to all links of this shop. For example:', 'affegg') ?>
                    <em>partner_id=12345</em>
                </p>
                <p>
                    <?php _e('Also you can use the template:', 'affegg') ?>
                    <em>{{url}}/partner_id-12345/</em>.
                </p>
                <p>
                    <?php _e('Read more:', 'affegg') ?> <a target="_blank" href="<?php echo \Keywordrush\AffiliateEgg\AffiliateEgg::pluginDocsUrl(); ?>/deeplink"><?php _e('Deeplink Settings', 'affegg'); ?></a>.
                </p>
                <?php if (isset($_SERVER['KEYWORDRUSH_DEVELOPMENT'])) : ?>
                    <?php $list = \Keywordrush\AffiliateEgg\ShopManager::getInstance()->getSortedListByCurrency(true); ?>
                    <textarea cols="300" rows="10">
                        <?php
                        echo 'Affiliate Egg supported shops sorted by default currency (as of ' . date('m/d/Y') . ')' . "\r\n";
                        $pre_currency = '';
                        foreach ($list as $currency => $l)
                        {
                            if ($currency != $pre_currency)
                            {
                                if ($currency == 'RUB')
                                    continue;

                                if (count($l) == 1 && ($l[0]->isDeprecated() || $l[0]->isUnstable()))
                                    echo '';
                                else
                                    echo '<tr><th>' . esc_html($currency) . '</th></tr>';
                            }


                            foreach ($l as $shop)
                            {
                                if ($shop->isDeprecated() || $shop->isUnstable())
                                    continue;

                                if (preg_match('/\.ru$/', $shop->getHost()))
                                    continue;

                                echo '<tr><td>' . esc_html($shop->getHost()) . '</td></tr>';
                            }
                            $pre_currency = $currency;
                        }
                        ?>
                    </textarea>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($page && $page['slug'] == 'affiliate-egg-cookies-settings') : ?>
            <div id="poststuff">
                <p>
                    <?php _e('Read more about this settings', 'affegg') ?> <a target="_blank" href="<?php echo \Keywordrush\AffiliateEgg\AffiliateEgg::pluginDocsUrl(); ?>/CustomCookies.html"><?php _e('here', 'affegg'); ?></a>.
                </p>
            </div>
        <?php endif; ?>
        <?php if ($page && $page['slug'] == 'affiliate-egg-proxy-settings') : ?>
            <div id="poststuff">
                <p>
                    <?php _e('Read more about this settings', 'affegg') ?> <a target="_blank" href="<?php echo \Keywordrush\AffiliateEgg\AffiliateEgg::pluginDocsUrl(); ?>/ProxySettings.html"><?php _e('here', 'affegg'); ?></a>.
                </p>
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