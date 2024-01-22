<?php defined('\ABSPATH') || exit; ?>
<small class="text-danger">Debug mode is enabled</small><br>
<div ng-show="debug" class="panel panel-default" style="overflow: auto;height:400px;">

    <div class="panel-heading">
        <pre ng-show="debug">{{debug}}</pre>
        <pre ng-show="products.length">Product data: {{products[products.length - 1]| json}}
        </pre>          
    </div>  

</div>