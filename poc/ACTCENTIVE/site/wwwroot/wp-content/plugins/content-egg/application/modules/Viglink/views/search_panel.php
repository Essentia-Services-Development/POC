<?php defined( '\ABSPATH' ) || exit; ?>
<input type="text" class="input-sm col-md-4" ng-model="query_params.<?php echo esc_attr($module_id); ?>.priceFrom"
       ng-init="query_params.<?php echo esc_attr($module_id); ?>.priceFrom = ''"
       placeholder="<?php esc_html_e( 'Min. price', 'content-egg' ); ?>"/>
<input type="text" class="input-sm col-md-4" ng-model="query_params.<?php echo esc_attr($module_id); ?>.priceTo"
       ng-init="query_params.<?php echo esc_attr($module_id); ?>.priceTo = ''"
       placeholder="<?php esc_html_e( 'Max. price', 'content-egg' ); ?>"/>