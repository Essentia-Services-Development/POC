<?php
defined( '\ABSPATH' ) || exit;
$locales        = \ContentEgg\application\modules\Amazon\AmazonConfig::getActiveLocalesList();
$default_locale = \ContentEgg\application\modules\Amazon\AmazonConfig::getInstance()->option( 'locale' );

$module       = \ContentEgg\application\components\ModuleManager::getInstance()->factory( $module_id );
$search_index = $module->config( 'search_index' );
?>

<?php if ( count( $locales ) > 1 ): ?>
    <select class="input-sm col-md-4" ng-model="query_params.Amazon.locale"
            ng-init="query_params.Amazon.locale = '<?php echo esc_attr($default_locale); ?>'">
		<?php foreach ( $locales as $value => $name ): ?>
            <option value="<?php echo \esc_attr( $value ); ?>"><?php echo \esc_html( $name ); ?></option>
		<?php endforeach; ?>
    </select>
<?php endif; ?>

<select class="input-sm col-md-4" ng-model="query_params.<?php echo esc_attr($module_id); ?>.min_percentage_off">
    <option value=""><?php esc_html_e( 'Min. saving', 'content-egg' ); ?></option>
    <option value="5%"><?php esc_html_e( '5%', 'content-egg' ); ?></option>
    <option value="10%"><?php esc_html_e( '10%', 'content-egg' ); ?></option>
    <option value="15%"><?php esc_html_e( '15%', 'content-egg' ); ?></option>
    <option value="20%"><?php esc_html_e( '20%', 'content-egg' ); ?></option>
    <option value="25%"><?php esc_html_e( '25%', 'content-egg' ); ?></option>
    <option value="30%"><?php esc_html_e( '30%', 'content-egg' ); ?></option>
    <option value="35%"><?php esc_html_e( '35%', 'content-egg' ); ?></option>
    <option value="40%"><?php esc_html_e( '40%', 'content-egg' ); ?></option>
    <option value="50%"><?php esc_html_e( '50%', 'content-egg' ); ?></option>
    <option value="60%"><?php esc_html_e( '60%', 'content-egg' ); ?></option>
    <option value="70%"><?php esc_html_e( '70%', 'content-egg' ); ?></option>
    <option value="80%"><?php esc_html_e( '80%', 'content-egg' ); ?></option>
    <option value="90%"><?php esc_html_e( '90%', 'content-egg' ); ?></option>
</select>
<div class="clearfix"></div>

<input type="text" class="input-sm col-md-4" ng-model="query_params.Amazon.minimum_price"
       ng-init="query_params.Amazon.minimum_price = ''" placeholder="<?php esc_html_e( 'Min. price', 'content-egg' ) ?>"
       title="<?php esc_html_e( 'Min. price.', 'content-egg' ) ?>"/>
<input type="text" class="input-sm col-md-4" ng-model="query_params.Amazon.maximum_price"
       ng-init="query_params.Amazon.maximum_price = ''" placeholder="<?php esc_html_e( 'Max. price', 'content-egg' ) ?>"
       title="<?php esc_html_e( 'Max. price.', 'content-egg' ) ?>"/>

