<?php

/*

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_storepolicy_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('storepolicy')){
            ?>
            <div class="nk-content marketking_storepolicy_page">
            <div class="container-fluid">
                <?php
                if (isset($_GET['update'])){
                    $add = sanitize_text_field($_GET['update']);;
                    if ($add === 'success'){
                        ?>                                    
                        <div class="alert alert-primary alert-icon"><em class="icon ni ni-check-circle"></em> <strong><?php esc_html_e('Your settings have been saved successfully','marketking-multivendor-marketplace-for-woocommerce');?></strong>.</div>
                        <?php
                    }
                }
                ?>
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block">
                            <div class="card">
                                <div class="card-aside-wrap">
                                    <div class="card-inner card-inner-lg">
                                        <div class="nk-block-head nk-block-head-lg">
                                            <div class="nk-block-between">
                                                <div class="nk-block-head-content">
                                                    <h4 class="nk-block-title"><em class="icon ni ni-files-fill"></em>&nbsp;&nbsp;<?php esc_html_e('Store Policies','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        
                                        $policy_enabled = get_user_meta($user_id, 'marketking_policy_enabled', true);
                                        $policy_message = get_user_meta($user_id, 'marketking_policy_message', true);
                                        if (empty($policy_message)){
                                            $policy_message = '';
                                        }

                                        $replaced = array('<h3>','<h4>','<i>','<strong>','</h3>','</h4>','</i>','</strong>');
                                        $allowed = array('***h3***','***h4***','***i***','***strong***','***/h3***','***/h4***','***/i***','***/strong***');

                                        $policy_message = str_replace($allowed, $replaced, $policy_message);
                                        
                                        ?>
                                        <div class="nk-block-content">
                                            <div class="gy-3">
                                                <div class="g-item">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" <?php checked('yes',$policy_enabled, true); ?> id="policyenabled">
                                                        <label class="custom-control-label" for="policyenabled"><?php esc_html_e('Enable Store Policies','marketking');
                                                          ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- .nk-block-content -->
                                        <br>
                                      
                                        <div class="form-group"><label class="form-label" for="policymessage"><?php esc_html_e('Enter Your Terms & Conditions','marketking');?></label><div class="form-control-wrap"><textarea class="form-control form-control-sm valid" id="policymessage" name="policymessage" placeholder="<?php esc_attr_e('Enter your terms & conditions','marketking');?>" required="" aria-invalid="false"><?php echo esc_html($policy_message);?></textarea><p class="marketking_formatting_message"><?php esc_html_e('Selected HTML tags can be used to format the above text: h3, h4, i, strong.','marketking');?></p></div></div>

                                        <button class="btn btn-primary" type="button" id="marketking_save_policy_settings" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Settings','marketking');?></button>

                                        <br><br><br>
                                        <div class="marketking_store_policy_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-help"></em><?php esc_html_e('While','marketking');?> <strong><?php esc_html_e('Store Policy','marketking');?></strong><?php esc_html_e(' is enabled, a dedicated "Policies" tab will be displayed with this info on your store page.','marketking');?></div></div>
                                    </div>
                                    <?php include(MARKETKINGCORE_DIR.'/public/dashboard/templates/profile-sidebar.php'); ?>
                                </div><!-- .card-inner -->
                            </div><!-- .card-aside-wrap -->
                        </div><!-- .nk-block -->
                    </div>
                </div>
            </div>
            </div>
            <?php
        }
    }
}