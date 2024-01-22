<?php
defined('\ABSPATH') || exit;

use ContentEgg\application\components\ModuleManager;

$module = ModuleManager::factory($module_id);
$is_woo = (\get_post_type($GLOBALS['post']->ID) == 'product') ? true : false;
$isAffiliateParser = $module->isAffiliateParser();
?>

<div class="data_results" ng-if="models.<?php echo esc_attr($module_id); ?>.added.length" id="<?php echo \esc_attr($module->getId()); ?>">
    <div ui-sortable="{ 'ui-floating': true }" ng-model="models.<?php echo esc_attr($module_id); ?>.added" class="row">
        <div class="col-md-12 added_data" ng-repeat="data in models.<?php echo esc_attr($module_id); ?>.added">
            <div class="row" style="padding:0;margin:0;padding-bottom:10px;" id="{{'<?php echo \esc_attr($module->getId()); ?>' + '-' + data.unique_id}}">
                <div class="col-md-1 text-center" ng-if="data.img">

                    <?php if ($isAffiliateParser): ?>
                        <img ng-src="{{data.img}}" class="img-responsive" style="max-height:75px;" ng-click="buildShortcode('<?php echo esc_attr($module_id); ?>', selectedTemplate_<?php echo esc_attr($module_id); ?>, selectedGroup_<?php echo esc_attr($module_id); ?>, data.unique_id);" />
                    <?php else: ?>
                        <img ng-src="{{data.img}}" class="img-responsive" style="max-height:75px;" />
                    <?php endif; ?>

                    <small ng-show="data.price">
                        <b>{{data.currencyCode}} {{data.price| number}}</b>
                    </small>

                </div>
                <div ng-class="data.img ? 'col-md-9' : 'col-md-10'">

                    <input type="text" placeholder="<?php esc_html_e('Title', 'content-egg'); ?>" ng-model="data.title" class="<?php echo esc_attr($isAffiliateParser) ? 'col-md-6' : 'col-md-12'; ?>" style="margin-bottom: 5px;">
                    <?php if ($isAffiliateParser): ?>
                        <input type="text" placeholder="<?php esc_html_e('Merchant name', 'content-egg'); ?>" ng-model="data.merchant" class="col-md-2" style="margin-bottom: 5px;">                    
                        <input type="text" placeholder="<?php esc_html_e('Domain', 'content-egg'); ?>" ng-model="data.domain" class="col-md-3" style="margin-bottom: 5px;">
                        <input type="text" placeholder="<?php esc_html_e('Price', 'content-egg'); ?>" ng-model="data.price" class="col-md-1" style="margin-bottom: 5px;">
                    <?php endif; ?>
                        
                    <textarea type="text" placeholder="<?php esc_html_e('Description', 'content-egg'); ?>" rows="1" ng-model="data.description" class="col-sm-12"></textarea>
                    <?php if ($isAffiliateParser): ?>
                            <?php if ($is_woo): ?>
                                <label><input ng-true-value="'true'" type="checkbox" ng-model="data.woo_sync" name="woo_sync" ng-change="wooRadioChange(data.unique_id, 'woo_sync')"> <?php esc_html_e('Woo synchronization', 'content-egg'); ?></label>
                                &nbsp;&nbsp;&nbsp;
                                <label ng-show="data.features.length"><input ng-true-value="'true'" type="checkbox" ng-model="data.woo_attr" name="woo_attr" ng-change="wooRadioChange(data.unique_id, 'woo_attr')"> <?php esc_html_e('Woo attributes', 'content-egg'); ?> ({{data.features.length}})</label>
                            <?php else: ?>
                                <small class="text-muted" ng-show="data.features.length"><?php esc_html_e('Attributes:', 'content-egg'); ?> {{data.features.length}}</small>
                            <?php endif; ?>
                    <?php endif; ?>


                    <a ng-show="data.features.length" ng-init="isFeaturesCollapsed = true" ng-click="isFeaturesCollapsed = !isFeaturesCollapsed" aria-label="Edit">
                        <span class="glyphicon glyphicon-edit"></span>
                    </a>

                    <div class="row features_wrap" uib-collapse="isFeaturesCollapsed">
                        <div class="col-md-12" ng-repeat="feature in data.features">
                            <div class="col-md-5">
                                <input type="text" ng-model="feature.name" class="input-sm form-control">
                            </div>
                            <div class="col-md-6">
                                <input type="text" ng-model="feature.value" class="input-sm form-control">                            
                            </div>
                            <div class="col-md-1">
                                <a ng-click="data.features.splice($index, 1)" aria-label="Delete">
                                    <span class="glyphicon glyphicon-remove-circle text-danger"></span>
                                </a>
                            </div>
                        </div>           
                    </div>

                </div>
                <div class="col-md-2">

                    <?php if ($isAffiliateParser): ?>

                        <div ng-show="productGroups.length" style="padding-bottom:8px;">
                            <select ng-model="data.group">
                                <option value="">- <?php esc_html_e('Product group', 'content-egg'); ?> -</option>
                                <option ng-repeat="group in productGroups" ng-value="group">{{group}}</option>                
                            </select>
                        </div>
                        <div>                        
                            <span ng-show="data.domain"><img src="https://www.google.com/s2/favicons?domain=https://{{data.domain}}"> {{data.domain}}</span><span ng-hide="data.domain"><?php esc_html_e('Go to ', 'content-egg'); ?></span> 
                            <a title="<?php echo esc_attr(__('Go to', 'content-egg')); ?>" href="{{data.url}}" target="_blank">
                                <i class="glyphicon glyphicon-share"></i>
                            </a>
                        </div>

                        <div style="padding:4px 0 8px 0;margin:0;">
                            <span class="text-muted">
                                <span ng-show="data.last_update"><i class="glyphicon glyphicon-time"></i> <abbr title="<?php esc_html_e('Last updated:', 'content-egg'); ?> {{data.last_update * 1000| date:'medium'}}">{{data.last_update * 1000| date:'shortDate'}}</abbr></span>
                                <mark ng-show="data.stock_status == - 1 || data.stock_status == 1" ng-class="{'outofstock': data.stock_status == - 1, 'instock': data.stock_status == 1}">{{data.stock_status| stockStatus}}</mark>
                            </span>



                        </div>
                    <?php endif; ?>
                    <div style="padding:0;margin:0;"><a style="color:#D03300;" ng-click="delete(data, '<?php echo esc_attr($module_id); ?>')"><i class="glyphicon glyphicon-remove"></i> <?php esc_html_e('Remove', 'content-egg'); ?></a></div>

                </div>  
            </div>

        </div>
    </div>
</div>