<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.promotion_type">
    <option value="">Any promotion type</option>
    <option value="coupon">Coupon</option>
    <option value="sweepstakes">Sweepstakes</option>
    <option value="product">Hot Product</option>
    <option value="sale/discount">Sale/Discount</option>
    <option value="free shipping">Free shipping</option>
    <option value="seasonal link">Seasonal link</option>
</select>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.link_type">
    <option value="">Any link type</option>
    <option value="Text Link">Text Link</option>
    <option value="Banner">Banner</option>
    <option value="Content Link">Content Link</option>
    <option value="Advanced Link">Advanced Link</option>
    <option value="Flash Link">Flash Link</option>
</select>