<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.license">
    <option value=""><?php esc_html_e( 'Any license', 'content-egg' ); ?></option>
    <option value="4,6,3,2,1,5"><?php esc_html_e( 'Any Creative Commons', 'content-egg' ); ?></option>
    <option value="4,6,5"><?php esc_html_e( 'With Allow of commercial use', 'content-egg' ); ?></option>
    <option value="4,2,1,5"><?php esc_html_e( 'Allowed change', 'content-egg' ); ?></option>
    <option value="4,5"><?php esc_html_e( 'Commercial use and change', 'content-egg' ); ?></option>
</select>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.sort">
    <option value="relevance"><?php esc_html_e( 'Relevance', 'content-egg' ); ?></option>
    <option value="date-posted-desc"><?php esc_html_e( 'Date of post', 'content-egg' ); ?></option>
    <option value="date-taken-desc"><?php esc_html_e( 'Date of shooting', 'content-egg' ); ?></option>
    <option value="interestingness-desc"><?php esc_html_e( 'First interesting', 'content-egg' ); ?></option>
</select>