<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.rights">
    <option value=""><?php esc_html_e( 'Any license', 'content-egg' ); ?></option>
    <option value="(cc_publicdomain|cc_attribute|cc_sharealike|cc_noncommercial|cc_nonderived)"><?php esc_html_e( 'Any Creative Commons', 'content-egg' ); ?></option>
    <option value="(cc_publicdomain|cc_attribute|cc_sharealike|cc_nonderived).-(cc_noncommercial)"><?php esc_html_e( 'With Allow of commercial use', 'content-egg' ); ?></option>
    <option value="(cc_publicdomain|cc_attribute|cc_sharealike|cc_noncommercial).-(cc_nonderived)"><?php esc_html_e( 'Allowed change', 'content-egg' ); ?></option>
    <option value="(cc_publicdomain|cc_attribute|cc_sharealike).-(cc_noncommercial|cc_nonderived)"><?php esc_html_e( 'Commercial use and change', 'content-egg' ); ?></option>
</select>
