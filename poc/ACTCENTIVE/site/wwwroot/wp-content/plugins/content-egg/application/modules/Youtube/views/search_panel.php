<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.license">
    <option value="any"><?php esc_html_e( 'Any license', 'content-egg' ); ?></option>
    <option value="creativeCommon"><?php esc_html_e( 'Creative Commons', 'content-egg' ); ?></option>
    <option value="youtube"><?php esc_html_e( 'Standard license', 'content-egg' ); ?></option>
</select>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.order">
    <option value="date"><?php esc_html_e( 'Date', 'content-egg' ); ?></option>
    <option value="rating"><?php esc_html_e( 'Rating', 'content-egg' ); ?></option>
    <option value="relevance"><?php esc_html_e( 'Relevance', 'content-egg' ); ?></option>
    <option value="title"><?php esc_html_e( 'Title', 'content-egg' ); ?></option>
    <option value="viewCount"><?php esc_html_e( 'Views', 'content-egg' ); ?></option>
</select>