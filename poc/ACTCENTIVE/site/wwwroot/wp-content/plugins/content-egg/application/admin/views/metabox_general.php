<?php defined('\ABSPATH') || exit; ?>
<?php \wp_nonce_field('contentegg_metabox', 'contentegg_nonce'); ?>

<div class="row">
    <div class="col-sm-9 col-lg-5">

        <div class="input-group">
            <input ng-disabled="processCounter" type="text" ng-model="global_keywords" select-on-click on-enter="global_findAll()" class="form-control" placeholder="<?php esc_html_e('Keyword to search', 'content-egg'); ?>" aria-label="<?php esc_html_e('Keyword to search', 'content-egg'); ?>">
            <div class="input-group-btn">
                <button ng-disabled='processCounter || !global_keywords' ng-click="global_findAll()" type="button" class="btn btn-info" aria-label="Find ">
                    <?php esc_html_e('Find all', 'content-egg'); ?>
                </button>
                <button ng-show='!processCounter && global_isSearchResults()' ng-click="global_addAll()" type="button" class="btn btn-default"><?php esc_html_e('Add all', 'content-egg'); ?></button>
                <button ng-show='global_isAddedResults()' ng-click="global_deleteAll()" ng-confirm-click="<?php esc_html_e('Are you sure you want to delete the results of all modules?', 'content-egg'); ?>" type="button" class="btn btn-default"><?php esc_html_e('Remove all', 'content-egg'); ?></button>

            </div>
        </div>

    </div>

    <div class="col-sm-3 col-lg-2">
        <div class="input-group">
            <input type="text" ng-model="newProductGroup" select-on-click on-enter="addProductGroup()" class="form-control input-sm" placeholder="<?php esc_html_e('Add product group', 'content-egg'); ?>" aria-label="<?php esc_html_e('Add product group', 'content-egg'); ?>">
            <div class="input-group-btn">
                <button ng-disabled="!newProductGroup" ng-click="addProductGroup()" type="button" class="btn btn-success btn-sm" aria-label="Add">
                    +
                </button>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">

        <?php
        $tpl_manager = ContentEgg\application\components\BlockTemplateManager::getInstance();
        $templates = $tpl_manager->getTemplatesList(true);
        ?>
        <input class="input-sm col-sm-6 col-lg-5 col-lg-4 shortcode-input" ng-model="blockShortcode" select-on-click readonly type="text" />                
        <select class="input-sm col-sm-3 col-lg-3" ng-init="blockShortcodeBuillder.template = '<?php echo esc_attr(key($templates)); ?>'; buildBlockShortcode();" ng-model="blockShortcodeBuillder.template" ng-change="buildBlockShortcode();">
            <?php foreach ($templates as $id => $name): ?>
                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
            <?php endforeach; ?>
        </select>
        <select ng-show="productGroups.length" class="input-sm col-sm-2" ng-model="blockShortcodeBuillder.group" ng-change="buildBlockShortcode();">
            <option value="">- <?php esc_html_e('Groups', 'content-egg'); ?> ({{productGroups.length}}) -</option>
            <option ng-repeat="group in productGroups" value="{{group}}">{{group}}</option>                
        </select>      
        <input class="input-sm col-sm-1" ng-model="blockShortcodeBuillder.next" ng-change="buildBlockShortcode();" placeholder="Next" type="number" step="1" />
    </div>    
</div>