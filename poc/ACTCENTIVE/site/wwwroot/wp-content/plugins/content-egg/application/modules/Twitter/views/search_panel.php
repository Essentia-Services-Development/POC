<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.result_type">
    <option value="recent"><?php esc_html_e( 'New', 'content-egg' ); ?></option>
    <option value="popular"><?php esc_html_e( 'Popular', 'content-egg' ); ?></option>
    <option value="mixed"><?php esc_html_e( 'Mix', 'content-egg' ); ?></option>
</select>
