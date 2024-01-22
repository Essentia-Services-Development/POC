<?php defined( '\ABSPATH' ) || exit; ?>
<input type="text" class="input-sm col-md-4" ng-model="updateParams.<?php echo esc_attr($module_id); ?>.price_min"
       placeholder="<?php esc_attr(__( 'Min. price', 'content-egg' )) ?>"
       title="<?php esc_attr(__( 'Min. price for automatic update', 'content-egg' )) ?>"/>
<input type="text" class="input-sm col-md-4" ng-model="updateParams.<?php echo esc_attr($module_id); ?>.price_max"
       placeholder="<?php esc_attr(__( 'Max. price', 'content-egg' )) ?>"
       title="<?php esc_attr(__( 'Max. price for automatic update', 'content-egg' )) ?>"/>