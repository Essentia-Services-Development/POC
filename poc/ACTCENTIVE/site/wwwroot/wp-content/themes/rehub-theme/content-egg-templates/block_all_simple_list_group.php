<?php
/*
 * Name: Simple 4 List item + group tabs
 * Modules:
 * Module Types: PRODUCT
 * 
 */
?>
<?php 
wp_enqueue_script('rhcuttab');
use ContentEgg\application\helpers\TemplateHelper;
if (!$groups = TemplateHelper::getGroupsList($data, $groups))
{
    include(rh_locate_template('content-egg-templates/block_all_simple_list.php'));
    return;
}
?>
<?php if(is_array($groups) && count($groups) < 2) {
    include(rh_locate_template('content-egg-templates/block_all_simple_list.php'));
    return;
}?>
<div class=" clearfix"></div>
<ul class="def_btn_group list-unstyled list-line-style">
<?php foreach ($groups as $i => $group): ?>
    <li class="mr10 rtlml10 mb10<?php if ($i == 0): ?> active<?php endif; ?>">
    <?php $group_ids[$i] = TemplateHelper::generateGlobalId('rh-cegg-simple-list-'); ?>
    <a role="tab" data-toggle="tab" href="#<?php echo \esc_attr($group_ids[$i]); ?>" class="def_btn rh-ce-gr-tabs floatleft fontnormal pt5 pb5 pr10 pl10"><?php echo \esc_html($group); ?></a>
    </li>
<?php endforeach; ?>
</ul>
<div class="clearbox"></div>
<?php $globaldata = $data; foreach ($groups as $i => $group): ?>
    <div role="tabpanel" class="tab-pane rh-ce-gr-cont<?php if ($i == 0): ?> active<?php endif; ?>" id="<?php echo \esc_attr($group_ids[$i]); ?>">
        <?php 
            $data = TemplateHelper::filterByGroup($globaldata, $group); 
            include(rh_locate_template('content-egg-templates/block_all_simple_list.php')); 
        ?>
    </div>
<?php endforeach; ?>
<div class="clearfix"></div>