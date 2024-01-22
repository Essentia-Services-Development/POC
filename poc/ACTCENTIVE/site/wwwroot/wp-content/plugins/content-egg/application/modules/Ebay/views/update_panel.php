<?php defined( '\ABSPATH' ) || exit; ?>
<input type="text" class="input-sm col-md-4" ng-model="updateParams.<?php echo esc_attr($module_id); ?>.min_price"
       placeholder="<?php esc_html_e( 'Min. price', 'content-egg' ) ?>"
       title="<?php esc_html_e( 'Min. price for automatic update', 'content-egg' ) ?>"/>
<input type="text" class="input-sm col-md-4" ng-model="updateParams.<?php echo esc_attr($module_id); ?>.max_price"
       placeholder="<?php esc_html_e( 'Max. price', 'content-egg' ) ?>"
       title="<?php esc_html_e( 'Max. price for automatic update', 'content-egg' ) ?>"/>