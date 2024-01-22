<?php defined('\ABSPATH') || exit; ?>

<?php
$locales = \ContentEgg\application\modules\Ebay2\Ebay2Config::getLocalesList();
$default_locale = \ContentEgg\application\modules\Ebay2\Ebay2Config::getInstance()->option('locale');
?>

<select class="input-sm col-md-4" ng-model="query_params.Ebay2.locale"
        ng-init="query_params.Ebay2.locale = '<?php echo esc_attr($default_locale); ?>'">
            <?php foreach ($locales as $value => $name): ?>
        <option value="<?php echo \esc_attr($value); ?>"><?php echo \esc_html($name); ?></option>
    <?php endforeach; ?>
</select>


<input type="text" class="input-sm col-md-4" ng-model="query_params.Ebay2.min_price"
       ng-init="query_params.Ebay2.min_price = ''" placeholder="<?php esc_html_e('Min. price', 'content-egg') ?>"/>
<input type="text" class="input-sm col-md-4" ng-model="query_params.Ebay2.max_price"
       ng-init="query_params.Ebay2.max_price = ''" placeholder="<?php esc_html_e('Max. price', 'content-egg') ?>"/>