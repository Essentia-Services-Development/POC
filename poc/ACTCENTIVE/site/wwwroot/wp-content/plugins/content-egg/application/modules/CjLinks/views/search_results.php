<?php defined( '\ABSPATH' ) || exit; ?>
<div class="search_results"
     ng-show="models.<?php echo esc_attr($module_id); ?>.results.length > 0 && !models.<?php echo esc_attr($module_id); ?>.processing">
    <div class="row search_results_row" ng-class="{'result_added' : result.added}"
         ng-click="add(result, '<?php echo esc_attr($module_id); ?>')" repeat-done
         ng-repeat="result in models.<?php echo esc_attr($module_id); ?>.results">
        <div class="col-md-1" ng-if="result.img">
            <img ng-src="{{result.img}}" ng-if="result.img" class="img-thumbnail" style="max-height: 150px;"/>
        </div>
        <div ng-class="result.img ? 'col-md-11' : 'col-md-12'">
            <div class="small">
                <span class="text-muted">{{result.extra.linkType}} 
                    <span ng-show="result.extra.creativeWidth">{{result.extra.creativeWidth}}x{{result.extra.creativeHeight}}</span> 
                </span> -
                <em>{{result.extra.advertiserName}} ({{result.extra.advertiserId}})</em>
            </div>
            <strong ng-show="result.title">{{result.title}}</strong><br>
            <span ng-show="result.description">{{result.description}}<br></span>
            <span ng-show="result.extra.couponCode">
                <em><?php esc_html_e( 'Coupon code:', 'content-egg' ); ?> {{result.extra.couponCode}}- <span
                            ng-show="result.extra.promotionStartDate">{{result.extra.promotionStartDate * 1000 | date:'mediumDate'}} - {{result.extra.promotionEndDate * 1000 | date:'mediumDate'}}</span></em>
            </span>
        </div>
    </div>
</div>