<?php defined( '\ABSPATH' ) || exit; ?>
<input type="text" class="input-sm col-md-4" ng-model="query_params.<?php echo esc_attr($module_id); ?>.storeId"
       ng-init="query_params.<?php echo esc_attr($module_id); ?>.storeId = ''"
       placeholder="<?php esc_html_e( 'Custom Store ID', 'content-egg' ); ?>"/>