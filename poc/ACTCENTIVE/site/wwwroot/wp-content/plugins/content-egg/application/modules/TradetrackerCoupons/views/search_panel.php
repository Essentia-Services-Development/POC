<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.itemsType">
    <option value="voucher"><?php esc_html_e( 'Vouchers', 'content-egg' ); ?></option>
    <option value="offer"><?php esc_html_e( 'Offers', 'content-egg' ); ?></option>
    <option value="text"><?php esc_html_e( 'Text Links', 'content-egg' ); ?></option>
</select>