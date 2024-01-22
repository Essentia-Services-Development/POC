<?php
// PeepSo Free Bundle handlers
$reset = FALSE;
$upsell = TRUE;
$license_changed = 0;

if(isset($_REQUEST['activate_plugins']) || isset($_REQUEST['activate_themes'])) {
    $license_changed = 1;
}

if(isset($_GET['action'])) {

    // Reset button triggers the Free Bundle flow - temporarily empty the license, show T&C
    if($_GET['action'] == 'peepso-free') {
        $reset = TRUE;
        $license = '';
    }

    // T&C was approved - permanenetly reset the license
    if($_GET['action'] == 'peepso-free-accept') {
        // disable upsell box when looking at installer immediately after accepting PFB T&C sop they can see the plugin list without any additional stuff
        $upsell = FALSE;

        $license = PeepSo3_Helper_Remote_Content::get('free_bundle_license', FALSE);
        PeepSoConfigSettings::get_instance()->set_option('bundle_license', $license);
        $license_changed = 1;
        PeepSo3_Helper_Addons::license_to_name($license,FALSE);
    }
}

$has_bundle_free = PeepSo3_Helper_Addons::license_is_free_bundle(FALSE);
?>
<div class="pa-page pa-page--addons pa-addons">
    <div class="pa-addons__header">
        <?php
        if(!$license && $reset) { ?>
            <div class="pa-addons__license">

                <div id="peepso-free-terms">
                    <?php echo PeepSo3_Helper_Remote_Content::get('free_bundle_terms', FALSE);?>
                    <br/><hr><br/>
                    <a class="pa-btn"style="float:right;background:#aaeeaa" href="<?php echo admin_url('admin.php?page=peepso-installer&action=peepso-free-accept&nocache')?>">
                        <i class="gcis gci-circle-check"></i>
                        <?php echo __('Accept'); ?>
                    </a>
                    <a class="pa-btn" style="background:#eeaaaa" href="<?php echo admin_url('admin.php?page=peepso-installer')?>">
                        <i class="gcis gci-circle-xmark"></i>
                        <?php echo __('Go back'); ?>
                    </a>

                </div>
            </div>


        <?php } else { ?>
        <div class="pa-addons__license">
            <!-- License name -->
            <div class="pa-addons__license-header ps-js-license-name">
                <?php echo __('Your license', 'peepso-core'); ?>
            </div>
            <div class="pa-addons__license-form">
                <div class="pa-addons__license-key">
                    <div class="pa-addons__license-input-wrapper">
                        <i class="gcis gci-key"></i>
                        <input id="license" type="text" placeholder="<?php echo __('License key...', 'peepso-core'); ?>" value="<?php echo $license; ?>" class="pa-input pa-addons__license-input peepso-license-key <?php echo ($license != '') ? '' : 'empty-license'; ?>" />
                    </div>

                    <input type="hidden" name="license_changed" id="license_changed" value="<?php echo $license_changed;?>" />
                    <button data-running-text="<?php echo __('Checking...', 'peepso-core'); ?>" class="pa-btn pa-btn--action pa-addons_license-button"><i class="gcis gci-sync-alt"></i><span><?php echo __('Check', 'peepso-core'); ?></span></button>

                    &nbsp;

                    <!--                    <a class="pa-btn" style="--><?php ////echo ($has_bundle_free) ? 'display:none' : '';?><!--" href="--><?php //echo admin_url('admin.php?page=peepso-installer&action=peepso-free');?><!--">-->
                    <!--                        <i class="gcis gci-gift"></i>-->
                    <!--                        <span>--><?php //echo __('Free Bundle', 'peepso-core'); ?><!--</span>-->
                    <!--                    </a>-->
                </div>
                <div class="pa-addons__license-notice">

                </div>
            </div>

            <div class="pa-addons__license-message ps-js-addons-message"></div>
        </div>

        <!-- Top bulk action buttons. -->
        <div class="pa-addons__bulk-actions ps-js-bulk-actions">
            <button class="pa-btn pa-addons__bulk-action-show ps-js-bulk-show"><i class="gcis gci-cog"></i><?php echo __('Show bulk actions', 'peepso-core'); ?></button>
            <button class="pa-btn pa-addons__bulk-action-install ps-js-bulk-install" data-running-text="<?php echo __('Installing ...','peepso-core'); ?>" data-tooltip="<?php echo __('Please select one or more products', 'peepso-core'); ?>" style="display:none"><i class="gcis gci-plus"></i><span><?php echo __('Install', 'peepso-core'); ?></span></button>
            <button class="pa-btn pa-addons__bulk-action-activate ps-js-bulk-activate" data-running-text="<?php echo __('Activating ...','peepso-core'); ?>" data-tooltip="<?php echo __('Please select one or more products', 'peepso-core'); ?>" style="display:none"><i class="gcis gci-check"></i><span><?php echo __('Activate', 'peepso-core'); ?></span></button>
            <button class="pa-btn pa-addons__bulk-action-hide ps-js-bulk-hide" style="display:none"><i class="gcis gci-cog"></i><?php echo __('Hide bulk actions', 'peepso-core'); ?></button>
        </div>
    </div>

    <div class="pa-addons__actions">
        <!-- <div class="pa-addons__actions-inner">
            <div class="pa-addons__license-name ps-js-bundle-name-wrapper">&nbsp;</div>
        </div> -->

        <div class="pa-addons__actions-select-all ps-js-bulk-checkall-wrapper" style="display:none">
            <input type="checkbox" class="ps-js-bulk-checkall" id="bulk-check-all" />
            <label for="bulk-check-all"><?php echo __('Select all', 'peepso-core'); ?></label>
        </div>

        <div class="pa-addons__disabler ps-js-action-disabler"></div>
    </div>
    <?php
    // Print upsell
    if($upsell) {
        echo PeepSo3_Helper_Addons::get_upsell('installer', FALSE);
    }
    ?>
    <div style="position:relative" id="peepso_installer_addon_list">
        <div class="pa-addons__list ps-js-list"></div>
        <div class="pa-addons__disabler ps-js-action-disabler"></div>
    </div>

    <div class="pa-addons__actions pa-addons__actions--bottom">
        <div class="pa-addons__actions-inner">
            <div class="pa-addons__actions-select-all ps-js-bulk-checkall-wrapper" style="display:none">
                <input type="checkbox" class="ps-js-bulk-checkall" id="bulk-check-all" />
                <label for="bulk-check-all"><?php echo __('Select all', 'peepso-core'); ?></label>
            </div>

            <!-- Top bulk action buttons. -->
            <div class="pa-addons__bulk-actions ps-js-bulk-actions">
                <button class="pa-btn pa-addons__bulk-action-show ps-js-bulk-show"><i class="gcis gci-cog"></i><?php echo __('Show bulk actions', 'peepso-core'); ?></button>
                <button class="pa-btn pa-addons__bulk-action-install ps-js-bulk-install" data-running-text="<?php echo __('Installing ...','peepso-core'); ?>" data-tooltip="<?php echo __('Please select one or more products', 'peepso-core'); ?>" style="display:none"><i class="gcis gci-plus"></i><span><?php echo __('Install', 'peepso-core'); ?></span></button>
                <button class="pa-btn pa-addons__bulk-action-activate ps-js-bulk-activate" data-running-text="<?php echo __('Activating ...','peepso-core'); ?>" data-tooltip="<?php echo __('Please select one or more products', 'peepso-core'); ?>" style="display:none"><i class="gcis gci-check"></i><span><?php echo __('Activate', 'peepso-core'); ?></span></button>
                <button class="pa-btn pa-addons__bulk-action-hide ps-js-bulk-hide" style="display:none"><i class="gcis gci-cog"></i><?php echo __('Hide bulk actions', 'peepso-core'); ?></button>
            </div>
        </div>

        <div class="pa-addons__disabler ps-js-action-disabler"></div>
    </div>
    <?php } ?>
    <div class="pa-addons__disabler ps-js-disabler"></div>
</div>

<style type="text/css">
    #peepso_license_error_combined {
        display: none;
    }
</style>
