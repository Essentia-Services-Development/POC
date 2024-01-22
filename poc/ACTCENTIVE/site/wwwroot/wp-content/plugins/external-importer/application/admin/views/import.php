<?php defined('\ABSPATH') || exit; ?>
<div class="wrap nosubsub">
    <h1 class="wp-heading-inline"><?php _e('Product Import', 'external-importer'); ?></h1>    

    <div ng-app="externalImporter" class="egg-container" id="external-importer">
        <div ng-controller="ExternalImporterController" ng-cloak>
            <div clas="row">
                <div id="ei-import-area" class="col-md-8">

                    <tabset>
                        <tab>
                            <tab-heading>
                                <?php _e('Listing', 'external-importer'); ?>
                            </tab-heading>

                            <fieldset class="ei-import-tab">

                                <input type="hidden" ng-model="listingProcessor.params.salt">                                

                                <div class="input-group">
                                    <input ng-disabled="loading" type="url" ng-model="listingProcessor.params.url" select-on-click on-enter="startImport('listingProcessor')" class="form-control col-md-6" placeholder="<?php _e('Enter Listing URL', 'external-importer'); ?>" aria-label="<?php _e('Enter Listing URL', 'external-importer'); ?>">
                                    <span class="input-group-btn">
                                        <button ng-if="loading" ng-click="stopImport('listingProcessor')" type="button" class="import-action-btn btn btn-warning"><?php echo _e('Stop', 'external-importer'); ?></button>
                                        <button ng-if="!loading" ng-disabled="!listingProcessor.params.url" ng-click="startImport('listingProcessor')" type="button" class="import-action-btn btn btn-primary"><?php _e('Extract products', 'external-importer'); ?></button>
                                    </span>
                                </div>  
                                <div class="ei-import-tab-options">
                                    <label for="ie_max_results"><?php _e('Max results', 'external-importer'); ?></label>
                                    <input ng-disabled="loading" id="ie_max_results" type="number" ng-model="listingProcessor.params.max_count" step="1" min="1" class="small-text">    
                                    <label for="ie_automatic_pagination" class="ie-ml-20">
                                        <input ng-disabled="loading" id="ie_automatic_pagination" type="checkbox" ng-model="listingProcessor.params.automatic_pagination"> <?php _e('Automatic pagination', 'external-importer'); ?>
                                    </label>   
                                </div>

                            </fieldset>
                        </tab>
                        <tab>
                            <tab-heading>
                                <?php _e('Products', 'external-importer'); ?>
                            </tab-heading>

                            <fieldset class="ei-import-tab">

                                <input type="hidden" ng-model="productProcessor.params.salt">
                                <div class="row no-margin">
                                    <div class="col-md-9 no-padding">
                                        <textarea ng-model="productProcessor.params.urls" ng-disabled="loading" style="min-width: 100%; height: 85px;" placeholder="<?php _e('Enter one Product URL per row', 'external-importer'); ?>"></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <button ng-show="!loading" ng-disabled="!productProcessor.params.urls" ng-click="startImport('productProcessor');" type="button" class="btn-block btn btn-primary no-margin"><?php _e('Extract products', 'external-importer'); ?></button>                                        
                                        <?php if (\ExternalImporter\application\Plugin::isDevEnvironment()): ?>
                                            <button ng-show="!loading" ng-disabled="!productProcessor.params.urls" ng-click="restartImport('productProcessor');" type="button" class="btn-block btn no-margin"><?php _e('Restart (debug)', 'external-importer'); ?></button>                                        
                                        <?php endif; ?>                                        
                                        <button ng-show="loading" ng-click="stopImport('listingProcessor')" type="button" class="btn-block btn btn-warning no-margin"><?php echo _e('Stop', 'external-importer'); ?></button>
                                    </div>
                                </div>
                            </fieldset>

                        </tab>
                    </tabset>   

                    <?php if (\ExternalImporter\application\Plugin::isDevEnvironment()) \ExternalImporter\application\admin\PluginAdmin::render('_parser_debug'); ?>
                    <?php \ExternalImporter\application\admin\PluginAdmin::render('_products'); ?>

                </div>  
                <div class="col-md-4">

                    <div class="panel panel-default">
                        <table class="table">
                            <tr class="panel-stat-area">
                                <td align="center" width="33%"><?php _e('Fetched:', 'external-importer'); ?> <span ng-class="{ 'label label-danger': products.length >= 1000}">{{stat.success}}</span></td>
                                <td align="center" width="33%"><?php _e('Pending:', 'external-importer'); ?> {{stat.new}}</td>
                                <td align="center" width="33%"><?php _e('Errors:', 'external-importer'); ?> {{stat.errors}}</td>                                
                            </tr>  
                            <tr>
                                <td colspan="3" class="panel-settings-area">
                                    <label>
                                        <?php _e('Wait before execution', 'external-importer'); ?> 
                                        <select ng-model="settings.timeout"> 
                                            <option ng-value="0">0</option>
                                            <option ng-value="1">1</option>
                                            <option ng-value="2">2</option>
                                            <option ng-value="3">3</option>
                                            <option ng-value="4">4</option>
                                            <option ng-value="5">5</option>
                                            <option ng-value="6">6</option>
                                            <option ng-value="7">7</option>
                                            <option ng-value="10">10</option>
                                            <option ng-value="15">15</option>
                                            <option ng-value="30">30</option>
                                            <option ng-value="-1">Rand (1-5)</option>
                                            <option ng-value="-2">Rand (1-10)</option>
                                        </select> <?php _e('second(s)', 'external-importer'); ?></label>
                                    <span ng-show="in_waiting && (settings.timeout > 1 || settings.timeout < 0)"><img src="<?php echo \ExternalImporter\PLUGIN_RES . '/img/loader2.gif' ?>" /></span>
                                </td>
                            </tr>
                        </table>
                        <div style="overflow-y: scroll; height: 1050px;">
                            <ul class="list-group">
                                <li ng-repeat="l in log track by $index" class="list-group-item" ng-bind-html="l.message" ng-class="{ 'list-group-item-danger': l.type == 'error', 'list-group-item-success1': l.type == 'success', 'list-group-item-info': l.type == 'info', 'list-group-item-warning': l.type == 'warning' }"></li>
                            </ul>
                        </div>
                    </div>                    
                </div>                

            </div>

        </div>
    </div>
</div>