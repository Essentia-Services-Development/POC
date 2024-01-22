<?php defined( '\ABSPATH' ) || exit; ?>
<select class="col-md-4 input-sm" ng-model="query_params.<?php echo esc_attr($module_id); ?>.sortField">
    <option value="">Keyword Relevance</option>
    <option value="POPULARITY">Popularity</option>
    <option value="AVERAGE_EARNINGS_PER_SALE">Avg $/sale</option>
    <option value="INITIAL_EARNINGS_PER_SALE">Initial $/sale</option>
    <option value="PCT_EARNINGS_PER_SALE">Avg %/sale</option>
    <option value="TOTAL_REBILL">Avg Rebill Total</option>
    <option value="PCT_EARNINGS_PER_REBILL">Avg %/rebill</option>
    <option value="GRAVITY">Gravity</option>
</select>