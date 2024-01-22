<?php defined( '\ABSPATH' ) || exit; ?>
<input type="text" class="input-sm col-md-4" ng-model="query_params.Aliexpress.original_price_from"
       ng-init="query_params.Aliexpress.original_price_from = ''"
       placeholder="<?php esc_html_e( 'Min. price', 'content-egg' ); ?>"/>
<input type="text" class="input-sm col-md-4" ng-model="query_params.Aliexpress.original_price_to"
       ng-init="query_params.Aliexpress.original_price_to = ''"
       placeholder="<?php esc_html_e( 'Max. price', 'content-egg' ); ?>"/>