<?php defined('\ABSPATH') || exit; ?>
<div ng-controllerTMP="<?php echo esc_attr($module_id); ?>Controller" ng-init="indextab_<?php echo esc_attr($module_id); ?> = activeResultTabs.<?php echo esc_attr($module_id); ?> ? 0 : 1">
    <input type="hidden" name="cegg_data[<?php echo esc_attr($module_id); ?>]" ng-value="models.<?php echo esc_attr($module_id); ?>.added | json" />
    <input type="hidden" name="cegg_updateKeywords[<?php echo esc_attr($module_id); ?>]" ng-value="updateKeywords.<?php echo esc_attr($module_id); ?>" />
    <input type="hidden" name="cegg_updateParams[<?php echo esc_attr($module_id); ?>]" ng-value="updateParams.<?php echo esc_attr($module_id); ?>| json" />

    <uib-tabset active="indextab_<?php echo esc_attr($module_id); ?>">
        <uib-tab>
            <uib-tab-heading>
                <strong><?php echo esc_html($module->getName()); ?></strong>
                <span ng-show="models.<?php echo esc_attr($module_id); ?>.added.length" class="label" ng-class="{
                            'label-danger':models.<?php echo esc_attr($module_id); ?>.added_changed, 'label-default':!models.<?php echo esc_attr($module_id); ?>.added_changed}">{{models.<?php echo esc_attr($module_id); ?>.added.length}}</span>
            </uib-tab-heading>

            <div class="data_panel">

                <div clas="row">
                    <div class="col-md-12 col-lg-6">
                        <input class="input-sm col-sm-6 shortcode-input" ng-model="shortcodes.<?php  echo esc_attr($module_id); ?>" select-on-click readonly type="text" />
                        <?php
                        $tpl_manager = ContentEgg\application\components\ModuleTemplateManager::getInstance($module_id);
                        $templates = $tpl_manager->getTemplatesList(true);
                        ?>
                        <?php if ($templates): ?>
                            <select class="input-sm col-sm-4" ng-model="selectedTemplate_<?php  echo esc_attr($module_id); ?>" ng-change="buildShortcode('<?php  echo esc_attr($module_id); ?>', selectedTemplate_<?php  echo esc_attr($module_id); ?>, selectedGroup_<?php  echo esc_attr($module_id); ?>);">
                                <option value="">&larr; <?php esc_html_e('Shortcode Template', 'content-egg'); ?></option>
                                <?php foreach ($templates as $id => $name): ?>
                                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <select ng-show="productGroups.length" class="input-sm col-sm-2" ng-model="selectedGroup_<?php  echo esc_attr($module_id); ?>" ng-change="buildShortcode('<?php  echo esc_attr($module_id); ?>', selectedTemplate_<?php  echo esc_attr($module_id); ?>, selectedGroup_<?php  echo esc_attr($module_id); ?>);">
                            <option value="">&larr; <?php esc_html_e('Group', 'content-egg'); ?></option>
                            <option ng-repeat="group in productGroups" value="{{group}}">{{group}}</option>
                        </select>

                    </div>

                    <div class="col-md-11 col-lg-5">
                        <?php if ($module->isAffiliateParser()): ?>
                            <input class="input-sm col-md-4 col-sm-12" id="updateKeyword_<?php  echo esc_attr($module_id); ?>" type="text" ng-model="updateKeywords.<?php  echo esc_attr($module_id); ?>" placeholder="<?php esc_html_e('Keyword for update', 'content-egg'); ?>" title="<?php esc_html_e('Keyword for automatic update', 'content-egg'); ?>" />
                            <?php $module->renderUpdatePanel(); ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-1 col-lg-1 text-right">
                        <a class='btn btn-default btn-sm' ng-click="deleteAll('<?php  echo esc_attr($module_id); ?>')" ng-confirm-click="<?php esc_html_e('Are you sure you want to delete all results?', 'content-egg'); ?>" ng-show='models.<?php  echo esc_attr($module_id); ?>.added.length'><?php esc_html_e('Remove all', 'content-egg'); ?></a>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <p ng-show="!models.<?php  echo esc_attr($module_id); ?>.added.length && !models.<?php  echo esc_attr($module_id); ?>.processing" class="bg-warning text-center"><br><?php esc_html_e('No data found...', 'content-egg'); ?><br><br></p>
            <?php $module->renderResults(); ?>
        </uib-tab>

        <uib-tab heading="<?php esc_html_e('Search', 'content-egg'); ?>">
            <div class="search_panel">
                <div clas="row">
                    <div class="col-md-11 col-lg-5">

                        <div class="input-group" ng-show="!models.<?php  echo esc_attr($module_id); ?>.processing">
                            <?php $module->isUrlSearchAllowed() ? $placeholder = __('Keyword or Product URL', 'content-egg') : $placeholder = __('Keyword to search', 'content-egg'); ?>
                            <input type="text" select-on-click ng-model="keywords.<?php  echo esc_attr($module_id); ?>" on-enter="find('<?php  echo esc_attr($module_id); ?>')" class="form-control" placeholder="<?php echo \esc_attr($placeholder); ?>" />
                            <div class="input-group-btn">
                                <button title="<?php echo esc_html(__('Find', 'content-egg')); ?>" ng-disabled="!keywords.<?php  echo esc_attr($module_id); ?>" ng-click="find('<?php  echo esc_attr($module_id); ?>')" type="button" class="btn btn-info" aria-label="Find">
                                    <span class="glyphicon glyphicon-search"></span>
                                </button>
                                <?php if ($module->isAffiliateParser()): ?>
                                    <button title="<?php echo esc_html(__('Add keyword for autoupdate', 'content-egg')); ?>" ng-disabled="!keywords.<?php  echo esc_attr($module_id); ?>" ng-click="setUpdateKeyword('<?php  echo esc_attr($module_id); ?>')" type="button" class="btn btn-info">
                                        <span class="glyphicon glyphicon-save"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($module->isFeedModule() && $module->isImportTime()): ?>
                            <img ng-show="models.<?php  echo esc_attr($module_id); ?>.processing" src="<?php echo esc_url(\ContentEgg\PLUGIN_RES) . '/img/importing.gif' ?>" />
                            <span ng-show="models.<?php  echo esc_attr($module_id); ?>.processing">
                                <?php esc_html_e('Loading data feed... Please wait...', 'content-egg'); ?>
                                <?php //_e('It may take a minute or two.', 'content-egg');?>
                            </span>
                        <?php else: ?>
                            <img ng-show="models.<?php  echo esc_attr($module_id); ?>.processing" src="<?php echo esc_url(\ContentEgg\PLUGIN_RES) . '/img/loader.gif' ?>" />
                        <?php endif; ?>

                    </div>
                    <div class="col-md-12 col-lg-6">
                        <div ng-show="!models.<?php  echo esc_attr($module_id); ?>.processing">
                            <?php $module->renderSearchPanel(); ?>
                        </div>
                    </div>
                    <div class="col-sm-1 text-right">
                        <a class='btn btn-default btn-sm' ng-click="addAll('<?php  echo esc_attr($module_id); ?>')" ng-show='models.<?php  echo esc_attr($module_id); ?>.results.length > 0 && !models.<?php  echo esc_attr($module_id); ?>.processing'><?php esc_html_e('Add all', 'content-egg'); ?></a>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>

            <?php $module->renderSearchResults(); ?>

            <p ng-show="!models.<?php  echo esc_attr($module_id); ?>.processing && models.<?php  echo esc_attr($module_id); ?>.loaded && models.<?php  echo esc_attr($module_id); ?>.results.length == 0" class="bg-warning text-center"><br><?php esc_html_e('Not found...', 'content-egg'); ?><br><br></p>
            <p ng-show="models.<?php  echo esc_attr($module_id); ?>.error && !models.<?php  echo esc_attr($module_id); ?>.processing" class="bg-danger text-center"><br><?php esc_html_e('Error:', 'content-egg'); ?> {{models.<?php  echo esc_attr($module_id); ?>.error}}<br><br></p>
        </uib-tab>
    </uib-tabset>
    <div class="row">
        <div class="col-sm-12"><br></div>
    </div>
</div>