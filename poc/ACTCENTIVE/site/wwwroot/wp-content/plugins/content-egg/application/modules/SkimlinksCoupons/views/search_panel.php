<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.offer_type">
    <option value=""><?php esc_html_e( 'All', 'content-egg' ); ?></option>
    <option value="coupon"><?php esc_html_e( 'Coupon', 'content-egg' ); ?></option>
    <option value="sweepstake"><?php esc_html_e( 'Sweepstake', 'content-egg' ); ?></option>
    <option value="hot_product"><?php esc_html_e( 'Hot product', 'content-egg' ); ?></option>
    <option value="sale"><?php esc_html_e( 'Sales', 'content-egg' ); ?></option>
    <option value="free_shipping"><?php esc_html_e( 'Free shipping', 'content-egg' ); ?></option>
    <option value="seasonal"><?php esc_html_e( 'Seasonal', 'content-egg' ); ?></option>
</select>