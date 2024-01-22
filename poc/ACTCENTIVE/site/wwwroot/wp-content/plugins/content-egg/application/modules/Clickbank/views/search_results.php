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
            <strong ng-show="result.title">{{result.title}}</strong><br>
            <span ng-show="result.description">{{result.description}}<br></span>
            <div class="text-muted" style="padding-top: 5px;">
                <span class="text-primary">Stats: </span>
                Avg $/sale: <strong class="text-success"
                                    ng-show="result.extra.averageDollarsPerSale != '0.00'">${{result.extra.averageDollarsPerSale}}</strong><strong
                        class="text-success" ng-show="result.extra.averageDollarsPerSale == '0.00'">N/A</strong>
                | Initial $/sale: <span class="text-success">${{result.extra.initialDollarsPerSale}}</span>
                | Avg %/sale: <span class="text-success">{{result.extra.pctPerSale}}%</span>
                | Avg Rebill Total: <span class="text-success">${{result.extra.totalRebill}}</span>
                | Avg %/rebill: <span class="text-success">{{result.extra.pctPerRebill}}%</span>
                | Grav: <strong class="text-primary">{{result.extra.gravity}}</strong>
            </div>
        </div>
    </div>
</div>