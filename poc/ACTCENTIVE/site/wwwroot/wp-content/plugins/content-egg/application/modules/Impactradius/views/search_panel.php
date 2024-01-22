<?php defined( '\ABSPATH' ) || exit; ?>
<input type="text" class="input-sm col-md-4" ng-model="query_params.<?php echo esc_attr($module_id); ?>.minprice"
       ng-init="query_params.<?php echo esc_attr($module_id); ?>.minprice = ''"
       placeholder="<?php esc_html_e( 'Min. price', 'content-egg' ); ?>"/>
<input type="text" class="input-sm col-md-4" ng-model="query_params.<?php echo esc_attr($module_id); ?>.maxprice"
       ng-init="query_params.<?php echo esc_attr($module_id); ?>.maxprice = ''"
       placeholder="<?php esc_html_e( 'Max. price', 'content-egg' ); ?>"/>