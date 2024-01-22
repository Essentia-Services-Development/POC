<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.voucherTypeId">
    <option value="">Any voucher type</option>
    <option value="1">Voucher</option>
    <option value="2">Discount</option>
    <option value="3">Free article</option>
    <option value="4">Free shipping</option>
    <option value="5">Raffle</option>
</select>