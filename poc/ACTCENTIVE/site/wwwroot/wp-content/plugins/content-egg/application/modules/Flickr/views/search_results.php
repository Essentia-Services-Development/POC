<?php defined( '\ABSPATH' ) || exit; ?>
<div class="search_results"
     ng-show="models.<?php echo esc_attr($module_id); ?>.results.length > 0 && !models.<?php echo esc_attr($module_id); ?>.processing">
    <div justified-gallery="{rowHeight: 160,sizeRangeSuffixes:{lt100 : '_t',lt150 : '_q',lt240 : '_m',lt320 : '_n',lt500 : '',lt640 : '_z',lt1024 : '_b'}}">
        <a ng-class="{'result_added' : result.added}" ng-click="add(result, '<?php echo esc_attr($module_id); ?>')" repeat-done
           ng-repeat="result in models.<?php echo esc_attr($module_id); ?>.results">
            <img height="160px" alt="{{result.title}}" ng-src="{{result.img| normalizeFlickr}}"/>
        </a>
    </div>
</div>