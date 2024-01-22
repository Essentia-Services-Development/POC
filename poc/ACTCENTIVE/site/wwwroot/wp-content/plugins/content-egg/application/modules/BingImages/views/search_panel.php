<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.license">
    <option value=""><?php esc_html_e( 'Any license', 'content-egg' ); ?></option>
    <option value="Public"><?php esc_html_e( 'Public', 'content-egg' ); ?></option>
    <option value="Share"><?php esc_html_e( 'Share', 'content-egg' ); ?></option>
    <option value="ShareCommercially"><?php esc_html_e( 'Share commercially', 'content-egg' ); ?></option>
    <option value="Modify"><?php esc_html_e( 'Modify', 'content-egg' ); ?></option>
    <option value="ModifyCommercially"><?php esc_html_e( 'Modify commercially', 'content-egg' ); ?></option>
</select>