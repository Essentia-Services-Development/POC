<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.VoucherType">
    <option value="-1.">Any voucher type</option>
    <option value="0.">All products</option>
    <option value="1.">Specific products</option>
    <option value="2.">Multi buy discount</option>
    <option value="3.">Free shipping</option>
    <option value="4.">Free product</option>
    <option value="5.">Competition</option>
</select>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.VoucherCodeContent">
    <option value="0.">Any code content</option>
    <option value="1.">Empty</option>
    <option value="2.">Filled</option>
</select>