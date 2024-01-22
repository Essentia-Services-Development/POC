<?php
$is_installed = FALSE;
$is_active = FALSE;
$can_install_item = FALSE;
$is_theme = FALSE;
$is_free_upgrade = FALSE;

if (in_array($item_id, $can_install)) {
    $can_install_item = TRUE;
    if ($item_id == 7354103) {
        // if Gecko
        $is_theme = TRUE;
        $is_installed = $gecko_installed;
        $activation_key = 'peepso-theme-gecko';
        $is_active = PeepSo3_Utility_Addon_Class::is_active('gecko');
    } else {
        if (count($peepso_plugins) > 0) {
            foreach ($peepso_plugins as $key=> $plugin) {
                if (strpos($plugin['Name'], ':') !== FALSE) {
                    $plugin_name = explode(': ', $plugin['Name'])[1];
                } else {
                    $plugin_name = str_replace('PeepSo', '', $plugin['Name']);
                }

                $plugin_name = strtolower($plugin_name);
                $plugin_name = trim($plugin_name);

                $product_title = trim(str_ireplace('(BETA)', '', $item_name));
                $product_title = strtolower($product_title);
                $product_title = trim($product_title);

                if ($plugin_name == $product_title) {
                    $is_installed = TRUE;

                    if (!empty($class)) {
                        $is_active = class_exists($class);
                    } else {
                        $is_active = PeepSo3_Utility_Addon_Class::is_active($plugin_name);
                    }

                    $activation_key = $key;
                }
            }
        }
    }
}
?>

<?php if ($category != '') { ?>
    <div class="pa-addons__list-group <?php if (!$can_install_item) { ?>pa-addons__addon--upgrade<?php } ?> ps-js-item ps-js-category">
        <input type="checkbox" class="ps-js-checkbox" style="display:none" />
        <span><?php echo $category; ?>:</span>
    </div>
<?php } ?>

<?php

$addon_classes = '';
if ($is_active || !$can_install_item) {
    $addon_classes .= ' pa-addons__addon--inactive';
    if (!$can_install_item) {
        $addon_classes .= ' pa-addons__addon--upgrade';
    }
}
//$is_new =1;
?><div class="pa-addons__addon<?php echo $addon_classes ?> ps-js-item ps-js-addon" style="<?php echo $is_new ? "-webkit-linear-gradient(-45deg, #8f6B29, #FDE08D, #DF9F28);
	background: linear-gradient(-45deg, #8f6B29, #FDE08D, #DF9F28); background-size: 400% 400%;animation: ps-installer-gradient-$item_id 15s ease infinite;" : "";?>">
    <div class="pa-addons__addon-header">
        <div class="pa-addons__addon-title">
            <?php if ($can_install_item) { ?>
                <input type="checkbox" class="ps-js-checkbox"
                       data-tooltip="<?php echo __('Already installed and active','peepso-core'); ?>"
                       data-id="<?php echo $item_id ?>"
                       data-is-installed="<?php echo $is_installed ? 1 : 0 ?>"
                       data-is-active="<?php echo $is_active ? 1 : 0 ?>"
                       <?php if ($is_active) { ?>checked="checked" disabled="disabled"<?php } ?>
                       style="display:none" />
            <?php } else { ?>
                <input type="checkbox" class="ps-js-checkbox"
                       data-tooltip="<?php echo __('Please upgrade to access this feature','peepso-core'); ?>"
                       data-is-installed="0"
                       data-is-active="0"
                       disabled="disabled"
                       style="display:none" />
            <?php } ?>
            <img src="https://cdn.peepso.com/icons/plugins/<?php echo $item_id;?>.svg"/>
            <h3>&nbsp;
                <?php if($is_new) { ?>
                    <span style="color:white"><i class="gcis gci-star"></i> NEW!</span>
                <?php } ?>
                <?php echo $item_name; ?>
            </h3>
        </div>
        <div class="pa-addons__addon-actions">

            <?php
            if ($can_install_item) {
                if ($is_active) {
                    ?>
                    <!-- Active -->
                    <a class="pa-btn pa-btn--action pa-btn--addon-active" title="<?php echo $item_name; ?>" href="#">
                        <i class="gcis gci-check-circle"></i>
                        <span><?php echo __('Active', 'peepso-core'); ?></span>
                    </a>
                    <?php
                } else if ($is_installed) {
                    $activation_keyword = $is_theme ? 'activate_themes' : 'activate_plugins';
                    ?>
                    <!-- Inactive => Activate  -->
                    <a class="pa-btn pa-btn--action pa-btn--addon-inactive ps-js-addon-inactive"
                       data-mouseover-text="<?php echo __('Activate','peepso-core'); ?>"
                       data-running-text="<?php echo __('Activating...','peepso-core'); ?>"
                       data-mouseover-icon="gcis gci-check-circle"
                       data-activation-keyword="<?php echo $activation_keyword;?>"
                       data-activation-key="<?php echo $activation_key;?>"
                       href="#">
                        <i class="gcir gci-check-circle"></i>
                        <span><?php echo __('Inactive', 'peepso-core');?></span>
                    </a>
                    <?php
                } else {
                    ?>
                    <!-- Not installed => Install -->
                    <a class="pa-btn pa-btn--action pa-btn--addon-install ps-js-addon-install"
                       data-mouseover-text="<?php echo __('Install','peepso-core'); ?>"
                       data-running-text="<?php echo __('Installing...','peepso-core'); ?>"
                       data-mouseover-icon="gcir gci-arrow-alt-circle-down"
                       data-id="<?php echo $item_id; ?>" title="<?php echo $item_name; ?>"
                       href="#">
                        <i class="gcir gci-question-circle"></i>
                        <span><?php echo __('Not installed', 'peepso-core'); ?></span>
                    </a>
                    <?php
                }
            } else {
                $is_free_upgrade = FALSE;

                $upgrade_text = __('Upgrade', 'peepso-core');
                $upgrade_icon = 'gcir gci-arrow-alt-circle-up';

                $upgrade_href = 'https://www.PeepSo.com/profile/?*/edd/licenses/';
                if(!PeepSo3_Helper_Addons::get_license() || PeepSo3_Helper_Addons::license_is_free_bundle()) {
                    $upgrade_href = 'https://www.PeepSo.com/pricing/ref/429';
                }
                $upgrade_target = 'target="_blank"';

                $upgrade_mouseover_text = 'PeepSo.com';
                $upgrade_mouseover_icon = 'gcis gci-globe-americas';

                $upgrade_style= '';

                if(in_array(64823085, $bundles)) {
                    $is_free_upgrade = TRUE;

                    $upgrade_text = __('Free Bundle', 'peepso-core');
                    $upgrade_icon = 'gcis gci-gift';

                    $upgrade_mouseover_text = __('Get it now','peepso-core');
                    $upgrade_mouseover_icon = 'gcis gci-circle-down';

                    $upgrade_href = admin_url('admin.php?page=peepso-installer&action=peepso-free');
                    $upgrade_target = '';

                    $upgrade_style= "-webkit-linear-gradient(-45deg, #8f6B29, #FDE08D, #DF9F28);background: linear-gradient(-45deg, #8f6B29, #FDE08D, #DF9F28); background-size: 200% 200%;animation: ps-installer-gradient-$item_id 5s ease infinite;";
                }
                ?>
                <a class="pa-btn pa-btn--action pa-btn--addon-upgrade ps-js-addon-upgrade"
                   style="<?php echo $upgrade_style;?>
                   title="<?php echo $item_name; ?>"
                   data-mouseover-text="<?php echo $upgrade_mouseover_text;?>"
                   data-mouseover-icon="<?php echo $upgrade_mouseover_icon;?>"
                   href="<?php echo $upgrade_href;?>" <?php echo $upgrade_target;?>>
                    <i class="<?php echo $upgrade_icon;?>"></i>
                    <span><?php echo $upgrade_text; ?></span>
                </a>
                <?php
            }
            ?>
        </div>
    </div>
    <?php if(isset($item_description) && strlen($item_description)) { ?>
    <div class="pa-addons__addon-desc">
        <div class="pa-addons__addon-desc-text slide-up ps-js-addon-desc">
            <?php echo $item_description; ?>
        </div>
        <a href="#" class="pa-addons__addon-desc-btn ps-js-show-addon-desc">
            <span class="dashicons dashicons-info"></span>
            <span data-label-show="<?php echo __('Show descriptions', 'peepso-core'); ?>"
                  data-label-hide="<?php echo __('Hide descriptions', 'peepso-core'); ?>">
                <?php echo __('Show descriptions', 'peepso-core'); ?>
            </span>
        </a>
    </div>
    <?php } ?>
</div>
<?php if($is_new || $is_free_upgrade) { ?>
<style type="text/css">
    @keyframes ps-installer-gradient-<?php echo $item_id;?> {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }
</style>
<?php } ?>
