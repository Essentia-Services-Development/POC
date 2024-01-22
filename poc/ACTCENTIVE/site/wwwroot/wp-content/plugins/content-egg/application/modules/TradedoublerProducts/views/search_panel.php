<input type="text" class="input-sm col-md-4" ng-model="query_params.<?php echo esc_attr($module_id); ?>.minPrice"
       ng-init="query_params.<?php echo esc_attr($module_id); ?>.minPrice = ''"
       placeholder="<?php esc_html_e( 'Min. price', 'content-egg' ); ?>"/>
<input type="text" class="input-sm col-md-4" ng-model="query_params.<?php echo esc_attr($module_id); ?>.maxPrice"
       ng-init="query_params.<?php echo esc_attr($module_id); ?>.maxPrice = ''"
       placeholder="<?php esc_html_e( 'Max. price', 'content-egg' ); ?>"/>