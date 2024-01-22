<?php defined( '\ABSPATH' ) || exit; ?>
<div class="search_results"
     ng-show="models.<?php echo esc_attr($module_id); ?>.results.length > 0 && !models.<?php echo esc_attr($module_id); ?>.processing">
    <div class="row search_results_row" ng-class="{'result_added' : result.added}"
         ng-click="add(result, '<?php echo esc_attr($module_id); ?>')" repeat-done
         ng-repeat="result in models.<?php echo esc_attr($module_id); ?>.results">
        <div class="col-md-4">

            <iframe ng-if="!result.added" width="320" height="180" ng-src="{{getYoutubeUri(result.extra.guid)}}"
                    frameborder="0" allowfullscreen></iframe>
            <img width="320" height="180" ng-if="result.added" ng-src="{{result.img}}"/>

        </div>
        <div class="col-md-8">
            <strong>{{result.title}}</strong>
            <p>{{result.description}}</p>
        </div>
    </div>
</div>