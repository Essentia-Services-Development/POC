<?php defined( '\ABSPATH' ) || exit; ?>
<?php $is_woo = ( \get_post_type( $GLOBALS['post']->ID ) == 'product' ) ? true : false; ?>
<div ng-controllerTMP="<?php echo esc_attr($module_id); ?>Controller">

    <input type="hidden" name="cegg_data[<?php echo esc_attr($module_id); ?>]"
           ng-value="models.<?php echo esc_attr($module_id); ?>.added | json"/>
    <input type="hidden" name="cegg_updateKeywords[<?php echo esc_attr($module_id); ?>]"
           ng-value="updateKeywords.<?php echo esc_attr($module_id); ?>"/>

    <uib-tabset active='0'>
        <uib-tab>
            <uib-tab-heading>
                <strong><?php echo esc_html($module->getName()); ?></strong>
                <span ng-show="models.<?php echo esc_attr($module_id); ?>.added.length" class="label" ng-class="{
                        'label-danger':models.<?php echo esc_attr($module_id); ?>.added_changed, 'label-default':!models.<?php echo esc_attr($module_id); ?>.added_changed}">{{models.<?php echo esc_attr($module_id);?>.added.length}}</span>
            </uib-tab-heading>

            <div class="data_panel">

                <div clas="row">
                    <div class="col-md-6 col-lg-6">
                        <input class="input-sm col-sm-6 shortcode-input" ng-model="shortcodes.<?php echo esc_attr($module_id); ?>"
                               select-on-click readonly type="text"/>
						<?php
						$tpl_manager = ContentEgg\application\components\ModuleTemplateManager::getInstance( $module_id );
						$templates   = $tpl_manager->getTemplatesList( true );
						?>
						<?php if ( $templates ): ?>
                            <select class="input-sm col-sm-6" ng-model="selectedTemplate_<?php echo esc_attr($module_id); ?>"
                                    ng-change="buildShortcode('<?php echo esc_attr($module_id); ?>', selectedTemplate_<?php echo esc_attr($module_id); ?>);">
                                <option value="">&larr; <?php esc_html_e( 'Shortcode Template', 'content-egg' ); ?></option>
								<?php foreach ( $templates as $id => $name ): ?>
                                    <option value="<?php echo esc_attr( $id ); ?>"><?php echo \esc_html( $name ); ?></option>
								<?php endforeach; ?>
                            </select>
						<?php endif; ?>
                    </div>

                    <div class="col-md-6 col-lg-6 text-right">
                        <a class='btn btn-default btn-sm' ng-click="addBlank('<?php echo esc_attr($module_id); ?>')"><i
                                    class="glyphicon glyphicon-plus"></i> <?php esc_html_e( 'Add offer', 'content-egg' ); ?></a>
                        <a class='btn btn-default btn-sm' ng-click="deleteAll('<?php echo esc_attr($module_id); ?>')"
                           ng-confirm-click="<?php esc_html_e( 'Are you sure you want to delete all results?', 'content-egg' ); ?>"
                           ng-disabled='!models.<?php echo esc_attr($module_id); ?>.added.length'><?php esc_html_e( 'Remove all', 'content-egg' ); ?></a>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>

			<?php // [RESULTS] ?>

            <div ng-init="activeResultTabs['<?php echo esc_attr($module_id); ?>'] = true" ui-sortable="{ 'ui-floating': true }"
                 ng-model="models.<?php echo esc_attr($module_id); ?>.added" class="row">
                <div class="col-md-12 added_data" ng-repeat="data in models.<?php echo esc_attr($module_id); ?>.added">
                    <div class="row" style="padding: 5px;">
                        <div class="col-md-1 text-center" ng-if="data.img">
                            <img ng-if="data.img" ng-src="{{data.img}}" class="img-responsive"
                                 style="max-height: 100px;"
                                 ng-click="buildShortcode('<?php echo esc_attr($module_id); ?>', selectedTemplate_<?php echo esc_attr($module_id); ?>, selectedGroup_<?php echo esc_attr($module_id); ?>, data.unique_id);"/>

                            <small ng-show="data.price">
                                <b>{{data.currencyCode}} {{data.price | number}}</b>
                            </small>
                        </div>
                        <div ng-class="data.img ? 'col-md-9' : 'col-md-10'">
                            <div class="row" style="margin:0px;">
                                <div class="col-md-10" style="padding:0px;">
                                    <input type="text"
                                           placeholder="<?php esc_html_e( 'Title', 'content-egg' ); ?> (<?php esc_html_e( 'required', 'content-egg' ); ?>)"
                                           ng-model="data.title" class="form-control" style="margin-bottom: 5px;">
                                </div>
                                <div class="col-md-2" style="padding-right:0px;">
                                    <select class="form-control" ng-model="data.rating" convert-to-number>
                                        <option value="1"><?php esc_html_e( 'Rating', 'content-egg' ); ?> - 1</option>
                                        <option value="2"><?php esc_html_e( 'Rating', 'content-egg' ); ?> - 2</option>
                                        <option value="3"><?php esc_html_e( 'Rating', 'content-egg' ); ?> - 3</option>
                                        <option value="4"><?php esc_html_e( 'Rating', 'content-egg' ); ?> - 4</option>
                                        <option value="5"><?php esc_html_e( 'Rating', 'content-egg' ); ?> - 5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row" style="margin:0px;">
                                <div class="col-md-6" style="padding:0px;">
                                    <input type="text"
                                           placeholder="<?php esc_html_e( 'Product URL', 'content-egg' ); ?> (<?php esc_html_e( 'required', 'content-egg' ); ?>)"
                                           ng-model="data.orig_url" class="form-control" style="margin-bottom: 5px;">
                                </div>
                                <div class="col-md-2" style="padding-right:0px;">
                                    <input type="text" placeholder="<?php esc_html_e( 'Domain', 'content-egg' ); ?>"
                                           ng-model="data.domain" class="form-control" style="margin-bottom: 5px;">

                                </div>
                                <div class="col-md-4" style="padding-right:0px;">
                                    <input type="text" placeholder="<?php esc_html_e( 'Custom Deeplink', 'content-egg' ); ?>"
                                           ng-model="data.extra.deeplink" class="form-control"
                                           style="margin-bottom: 5px;">
                                </div>
                            </div>

                            <div class="row" style="margin:0px;">
                                <div class="col-md-6" style="padding:0px;">
                                    <input type="text" placeholder="<?php esc_html_e( 'Product Image URL', 'content-egg' ); ?>"
                                           ng-model="data.img" class="form-control" style="margin-bottom: 5px;">
                                </div>
                                <div class="col-md-6" style="padding-right:0px;">
                                    <input type="text"
                                           placeholder="<?php esc_html_e( 'Merchant Logo URL (optional)', 'content-egg' ); ?>"
                                           ng-model="data.logo" class="form-control" style="margin-bottom: 5px;">
                                </div>
                            </div>
                            <div class="row" style="margin:0px;">
                                <div class="col-md-3" style="padding:0px;">
                                    <input type="text" placeholder="<?php esc_html_e( 'Price', 'content-egg' ); ?>"
                                           ng-model="data.price" class="form-control">
                                </div>
                                <div class="col-md-3" style="padding:0px;">
                                    <input type="text" placeholder="<?php esc_html_e( 'Old Price', 'content-egg' ); ?>"
                                           ng-model="data.priceOld" class="form-control">
                                </div>
                                <div class="col-md-1" style="padding-right:0px;">

                                    <select class="form-control" ng-model="data.currencyCode"
                                            ng-init="data.currencyCode = data.currencyCode || '<?php echo esc_attr($module->getConfigInstance()->option( 'default_currency' )); ?>'">
										<?php foreach ( \ContentEgg\application\helpers\CurrencyHelper::getCurrenciesList() as $currency ): ?>
                                            <option value="<?php echo \esc_attr( $currency ); ?>"><?php echo \esc_html( $currency ); ?></option>
										<?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5" style="padding-right:0px;">
                                    <input type="text"
                                           placeholder="<?php esc_html_e( 'Custom XPath Price Selector', 'content-egg' ); ?>"
                                           ng-model="data.extra.priceXpath" class="form-control">

                                </div>
                            </div>

                            <textarea type="text" placeholder="<?php esc_html_e( 'Description', 'content-egg' ); ?>" rows="1"
                                      ng-model="data.description" class="col-sm-12" style="margin-top: 5px;"></textarea>
							<?php if ( $is_woo && $module->isAffiliateParser() ): ?>
                                <label><input ng-true-value="'true'" type="checkbox" ng-model="data.woo_sync"
                                              name="woo_sync"
                                              ng-change="wooRadioChange(data.unique_id, 'woo_sync')"> <?php esc_html_e( 'Woo synchronization', 'content-egg' ); ?>
                                </label>
							<?php endif; ?>

                        </div>
                        <div class="col-md-2">

                            <div ng-show="productGroups.length" style="padding-bottom:8px;">
                                <select ng-model="data.group">
                                    <option value="">- <?php esc_html_e( 'Product group', 'content-egg' ); ?> -</option>
                                    <option ng-repeat="group in productGroups" ng-value="group">{{group}}</option>
                                </select>
                            </div>
                            <div>
                                <span ng-show="data.domain"><img
                                            src="https://www.google.com/s2/favicons?domain={{data.domain}}"> {{data.domain}}</span><span
                                        ng-hide="data.domain"><?php esc_html_e( 'Go to ', 'content-egg' ); ?></span>
                                <a title="<?php esc_attr( esc_html_e( 'Go to', 'content-egg' ) ); ?>" href="{{data.url}}"
                                   target="_blank">
                                    <i class="glyphicon glyphicon-share"></i>
                                </a>
                            </div>
                            <div style="padding:4px 0 8px 0;margin:0;">
                                <span class="text-muted">
                                    <span ng-show="data.last_update"><i class="glyphicon glyphicon-time"></i> <abbr
                                                title="<?php esc_html_e( 'Last updated:', 'content-egg' ); ?> {{data.last_update * 1000| date:'medium'}}">{{data.last_update * 1000 | date:'shortDate'}}</abbr></span>
                                    <mark ng-show="data.stock_status" ng-class="{
                                            'outofstock': data.stock_status == - 1, 'instock': data.stock_status == 1}">{{data.stock_status | stockStatus}}</mark>
                                </span>
                            </div>
                            <div style="padding:0;margin:0;"><a style="color:#D03300;"
                                                                ng-click="delete(data, '<?php echo esc_attr($module_id); ?>')"><i
                                            class="glyphicon glyphicon-remove"></i> <?php esc_html_e( 'Remove', 'content-egg' ); ?>
                                </a></div>


                            <div style="padding:4px 0 8px 0;margin:0;" ng-show="data.extra.last_error">
                                <span class="text-danger">
                                    <span ng-show="data.last_update"><i
                                                title="<?php esc_html_e( 'Last XPath error', 'content-egg' ); ?>"
                                                class="glyphicon glyphicon-warning-sign"></i> {{data.extra.last_error}}</abbr></span>
                                </span>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
			<?php // [/RESULTS] ?>
        </uib-tab>
    </uib-tabset>

</div>