<?php defined('\ABSPATH') || exit; ?>
<div class="panel panel-default">
    <table class="table">
        <tr class="panel-stat-area">
            <td width="25%">
                <span ng-show="in_progress"> <img src="<?php echo \ExternalImporter\PLUGIN_RES . '/img/loader1.gif' ?>" /></span>
            </td>
            <td align="center" width="25%"><?php _e('Imported:', 'external-importer'); ?> {{importStat.success}}</td>
            <td align="center" width="25%"><?php _e('In queue:', 'external-importer'); ?> {{importQueue.length + import_in_progress_count}}</td>
            <td align="center" width="25%"><?php _e('Errors:', 'external-importer'); ?> {{importStat.errors}}</td>                                    
        </tr>  
    </table>
    <?php if (ExternalImporter\application\helpers\WooHelper::isWooInstalled()): ?>

        <div class="col-md-12 ce-import-settings">

            <input ng-model="checkAll" type="checkbox" ng-click="toggleSeleted()">

            <label class="ie-ml-20">
                <?php _e('Default category', 'external-importer'); ?>
                <select ng-model="importParams.category" ng-init="importParams.category =<?php echo \esc_attr((int) \ExternalImporter\application\admin\WooConfig::getInstance()->option('default_category')); ?>">
                    <?php foreach (ExternalImporter\application\helpers\WooHelper::getCategoryList() as $cid => $cname): ?>
                        <?php echo '<option ng-value="' . \esc_attr($cid) . '">' . \esc_html($cname) . '</option>'; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="ie-ml-20">
                <?php _e('Import threads', 'external-importer'); ?> 
                <select ng-model="importSettings.threads"> 
                    <option ng-value="1">1</option>
                    <option ng-value="2">2</option>
                    <option ng-value="3">3</option>
                    <option ng-value="4">4</option>
                    <option ng-value="5">5</option>
                    <option ng-value="6">6</option>
                    <option ng-value="7">7</option>
                    <option ng-value="8">8</option>
                    <option ng-value="9">9</option>
                    <option ng-value="10">10</option>
                    <option ng-value="15">15</option>
                </select>
            </label>  
            <label class="ie-ml-20"><input ng-model="automaticImport" type="checkbox" value="1" ng-change="initAutomaticImport()"> <?php _e('Automatic import', 'external-importer'); ?></label>
            <span class="pull-right">

                <span class="ie-ml-20"></span>
                <button ng-click="importSelected()" ng-disabled="!selectedCount()" class="page-title-action"><?php _e('Import selected', 'external-importer'); ?> ({{selectedCount()}})</button>                                       
                <button ng-click="deleteAllProducts()" ng-disabled="!products.length" class="page-title-action"><?php _e('Remove all', 'external-importer'); ?></button>                                    
            </span>
        </div>
    <?php endif; ?>

    <div class="col-md-12 products">
        <div class="table-responsive" style="overflow-y: scroll; height: 900px;">
            <table class="table table-hover vertical-align table-condensed">
                <tbody>

                    <?php
                    $dynamic_categories = \ExternalImporter\application\admin\WooConfig::getInstance()->option('dynamic_categories');
                    $import_stock_status = \ExternalImporter\application\admin\WooConfig::getInstance()->option('import_stock_status');
                    $import_attributes = \ExternalImporter\application\admin\WooConfig::getInstance()->option('import_attributes');
                    $import_gallery = \ExternalImporter\application\admin\WooConfig::getInstance()->option('import_gallery');
                    $import_reviews = \ExternalImporter\application\admin\WooConfig::getInstance()->option('import_reviews');
                    $product_type = \ExternalImporter\application\admin\WooConfig::getInstance()->option('product_type');
                    ?>

                    <tr ng-repeat="product in products track by $index" ng-if="product" class="product-animate-if product-row" ng-class="{'warning': product._import_in_queue || product._import_in_progress, 'danger': product._import_status == 'error', 'success': product._import_status == 'success' }">
                        <th class="check-column">
                            <input ng-disabled="product._import_in_queue || product._import_status || product._import_in_progress" type="checkbox" ng-model="product._selected">
                        </th>                        
                        <td class="thumb column-thumb">
                            <img ng-src="{{product.image}}" class="img-responsive" />
                        </td>
                        <td class="title column-title">
                            <strong class="text-primary">{{(product.title| limitTo: 146) + (product.title.length > 146 ? '…' : '')}}</strong>
                            <?php if ($dynamic_categories == 'nested'): ?>
                                <div class="text-info" ng-if="product.categoryPath.length >= 1">
                                    {{(product.categoryPath.join(' » ') | limitTo: 100) + (product.categoryPath.join(' » ').length > 100 ? '…' : '')}}
                                </div>
                            <?php endif; ?>
                            <span class="text-muted">

                                <span class="text-primary"><img width="12" src="https://www.google.com/s2/favicons?domain={{product.domain}}" /> <a target="_blank" href="{{product.link}}">{{product.domain}}</a></span>
                                <?php if ($import_attributes): ?>
                                    <span ng-show="product.features.length">| <?php _e('Attributes', 'external-importer'); ?>: {{product.features.length}}</span>
                                <?php endif; ?>
                                <?php if ($import_reviews): ?>
                                    <span ng-show="product.reviews.length">| <?php _e('Reviews', 'external-importer'); ?>: {{product.reviews.length}}</span>
                                <?php endif; ?>                          
                                <?php if ($import_gallery): ?>
                                    <span ng-show="product.images.length">| <?php _e('Gallery images', 'external-importer'); ?>: {{product.images.length}}</span>
                                <?php endif; ?>  
                                <span ng-show="product.variations.length">| <?php _e('Variations', 'external-importer'); ?>: {{product.variations.length}}</span>
                                | <span ng-show="product.inStock"><?php _e('In stock', 'external-importer'); ?></span><span class="text-danger" ng-show="{{!product.inStock}}"><?php _e('Out of stock', 'external-importer'); ?></span>

                                <?php if ($dynamic_categories == 'nested'): ?>
                                    <span ng-if="product.category && product.categoryPath.length < 1"> | <span class="text-info">{{product.category}}</span></span>
                                <?php elseif ($dynamic_categories == 'create'): ?>
                                    <span ng-if="product.category"> | <span class="text-info">{{product.category}}</span></span>
                                <?php endif; ?>
                                <span ng-show="product._import_message" class="text-danger">&nbsp;&nbsp;&nbsp;{{product._import_message}}</span>
                            </span>
                        </td>
                        <td class="column-import-button">
                            <?php if (ExternalImporter\application\helpers\WooHelper::isWooInstalled()): ?>
                                <button ng-disabled="product._import_in_queue || product._import_status || product._import_in_progress" class="page-title-action" ng-click="addToImportQueue($index)">
                                    <?php _e('Import', 'external-importer'); ?>
                                </button>    
                            <?php endif; ?>
                        </td>
                        <td class="column-import-spinner">
                            <span ng-show="product._import_in_progress" class="dashicons dashicons-update spinning"></span>
                        </td>                        

                        <td class="column-price" nowrap="nowrap" style="font-size: 0.9em;">

                            <span ng-show="product.price">{{product.currencyCode}} {{product.price| currency: ''}}</span>
                            <del ng-show="product.oldPrice"><br>{{product.currencyCode}} {{product.oldPrice| currency: ''}}</del>
                        </td>
                        <td class="column-del-btn">
                            <button ng-disabled="product._import_in_queue || product._import_status || product._import_in_progress" ng-click="deleteProduct($index)" type="button" class="close pull-right"><span aria-hidden="true">&times;</span></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>   

</div>