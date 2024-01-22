<?php defined('\ABSPATH') || exit; ?>
<div ng-controllerTMP="<?php echo esc_attr($module_id); ?>Controller">

    <input type="hidden" name="cegg_data[<?php echo esc_attr($module_id); ?>]"
           ng-value="models.<?php echo esc_attr($module_id); ?>.added | json"/>
    <input type="hidden" name="cegg_updateKeywords[<?php echo esc_attr($module_id); ?>]"
           ng-value="updateKeywords.<?php echo esc_attr($module_id); ?>"/>

    <uib-tabset active='0'>
        <uib-tab>
            <uib-tab-heading>
                <strong><?php echo esc_html($module->getName()); ?></strong>
                <span ng-show="models.<?php echo esc_attr($module_id); ?>.added.length" class="label"
                      ng-class="{'label-danger':models.<?php echo esc_attr($module_id); ?>.added_changed, 'label-default':!models.<?php echo esc_attr($module_id); ?>.added_changed}">{{models.<?php echo esc_html($module_id); ?>.added.length}}</span>
            </uib-tab-heading>

            <div class="data_panel">

                <div clas="row">
                    <div class="col-md-6 col-lg-6">
                        <input class="input-sm col-sm-6 shortcode-input" ng-model="shortcodes.<?php echo esc_attr($module_id); ?>"
                               select-on-click readonly type="text"/>
                               <?php
                               $tpl_manager = ContentEgg\application\components\ModuleTemplateManager::getInstance($module_id);
                               $templates = $tpl_manager->getTemplatesList(true);
                               ?>
                               <?php if ($templates): ?>
                            <select class="input-sm col-sm-6" ng-model="selectedTemplate_<?php echo esc_attr($module_id); ?>"
                                    ng-change="buildShortcode('<?php echo esc_attr($module_id); ?>', selectedTemplate_<?php echo esc_attr($module_id); ?>);">
                                <option value="">&larr; <?php esc_html_e('Shortcode Template', 'content-egg'); ?></option>
                                <?php foreach ($templates as $id => $name): ?>
                                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 col-lg-6 text-right">
                        <a class='btn btn-default btn-sm'
                           ng-click="addBlank('<?php echo esc_attr($module_id); ?>', 'contentCoupon')"><i
                                class="glyphicon glyphicon-plus"></i> <?php esc_html_e('Add coupon', 'content-egg'); ?>
                        </a>
                        <a class='btn btn-default btn-sm' ng-click="deleteAll('<?php echo esc_attr($module_id); ?>')"
                           ng-confirm-click="<?php esc_html_e('Are you sure you want to delete all results?', 'content-egg'); ?>"
                           ng-disabled='!models.<?php echo esc_attr($module_id); ?>.added.length'><?php esc_html_e('Remove all', 'content-egg'); ?></a>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>

            <?php // [RESULTS] ?>

            <div ng-init="activeResultTabs['<?php echo esc_attr($module_id); ?>'] = true" ui-sortable="{ 'ui-floating': true }"
                 ng-model="models.<?php echo esc_attr($module_id); ?>.added" class="row">
                <div class="col-md-12 added_data" ng-repeat="data in models.<?php echo esc_attr($module_id); ?>.added">
                    <div class="row" style="padding: 5px;">
                        <div class="col-md-1" ng-if="data.img">
                            <img ng-if="data.img" ng-src="{{data.img}}" class="img-responsive"
                                 style="max-height: 100px;"/>
                        </div>
                        <div ng-class="data.img ? 'col-md-9' : 'col-md-10'">
                            <div class="row" style="margin:0px;">
                                <div class="col-md-8" style="padding:0px;">
                                    <input type="text"
                                           placeholder="<?php esc_html_e('Title', 'content-egg'); ?> (<?php esc_html_e('required', 'content-egg'); ?>)"
                                           ng-model="data.title" class="form-control" style="margin-bottom: 5px;">
                                </div>
                                <div class="col-md-4" style="padding:0px;">
                                    <input type="text" placeholder="<?php esc_html_e('Coupon code', 'content-egg'); ?>"
                                           ng-model="data.code" class="form-control" style="margin-bottom: 5px;">
                                </div>
                            </div>

                            <div class="row" style="margin:0px;">
                                <div class="col-md-8" style="padding:0px;">
                                    <input type="text"
                                           placeholder="<?php esc_html_e('Affiliate URL', 'content-egg'); ?> (<?php esc_html_e('required', 'content-egg'); ?>)"
                                           ng-model="data.url" class="form-control" style="margin-bottom: 5px;">
                                </div>
                                <div class="col-md-4" style="padding:0px;">
                                    <input type="text" placeholder="<?php esc_html_e('Merchant domain', 'content-egg'); ?>"
                                           ng-model="data.domain" class="form-control" style="margin-bottom: 5px;">
                                </div>
                            </div>

                            <input type="text" placeholder="<?php esc_html_e('Image\Logo URL', 'content-egg'); ?>"
                                   ng-model="data.img" class="form-control" style="margin-bottom: 5px;">

                            <div class="row" style="margin:0px;">
                                <div class="col-md-6" style="padding:0px;">
                                    <div class="input-group" style="margin-bottom: 5px;">

                                        <input type="text" class="form-control"
                                               placeholder="<?php esc_html_e('Start date (YYYY/MM/DD)', 'content-egg'); ?>"
                                               ng-model="data.startDate"
                                               uib-datepicker-popup="yyyy/MM/dd"
                                               datepicker-append-to-body="true"
                                               is-open="startDateOpened"
                                               class="form-control"
                                               ng-model-options="{timezone: 'utc'}"
                                               />
                                        <?php /*
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default"
                                                    ng-click="startDateOpened = true"><i
                                                    class="glyphicon glyphicon-calendar"></i></button>
                                        </span>
                                         * 
                                         */?>
                                    </div>
                                </div>
                                <div class="col-md-6" style="padding:0px;">
                                    <div class="input-group" style="margin-bottom: 5px;">
                                        <input type="text" class="form-control"
                                               placeholder="<?php esc_html_e('End date (YYYY/MM/DD)', 'content-egg'); ?>"
                                               ng-model="data.endDate"
                                               uib-datepicker-popup="yyyy/MM/dd"
                                               datepicker-append-to-body="true"
                                               is-open="endDateOpened"
                                               class="form-control"
                                               ng-model-options="{timezone: 'utc'}"
                                               />
                                        <?php /*
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default"
                                                    ng-click="endDateOpened = true"><i
                                                    class="glyphicon glyphicon-calendar"></i></button>
                                        </span>
                                         * 
                                         */ ?>
                                    </div>
                                </div>

                                <textarea type="text" placeholder="<?php esc_html_e('Description', 'content-egg'); ?>"
                                          rows="1" ng-model="data.description" class="col-sm-12"
                                          style="margin-top: 5px;"></textarea>


                            </div>
                        </div>
                        <div class="col-md-2">
                            <span ng-if="data.url"><a href="{{data.url}}"
                                                      target="_blank"><?php esc_html_e('Go to ', 'content-egg'); ?></a><br><br></span>
                            <div style="padding:0xp;margin:0px;padding-top:10px;"><a style="color:#D03300;"
                                                                                     ng-click="delete(data, '<?php echo esc_attr($module_id); ?>')"><i
                                        class="glyphicon glyphicon-remove"></i> <?php esc_html_e('Remove', 'content-egg'); ?>
                                </a></div>
                            <span ng-show="data.last_update"><?php esc_html_e('Last update: '); ?>{{data.last_update * 1000| date:'shortDate'}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php // [/RESULTS] ?>
        </uib-tab>
    </uib-tabset>

</div>