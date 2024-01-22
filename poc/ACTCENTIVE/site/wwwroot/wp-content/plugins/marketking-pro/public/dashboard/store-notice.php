<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_storenotice_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('storenotice')){
            ?>
            <div class="nk-content marketking_storenotice_page">
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
                                                    <h4 class="nk-block-title"><em class="icon ni ni-notice"></em>&nbsp;&nbsp;<?php esc_html_e('Store Notice','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        
                                        $notice_enabled = get_user_meta($user_id, 'marketking_notice_enabled', true);
                                        $notice_message = get_user_meta($user_id, 'marketking_notice_message', true);
                                        if (empty($notice_message)){
                                            $notice_message = '';
                                        }
                                        
                                        ?>
                                        <div class="nk-block-content">
                                            <div class="gy-3">
                                                <div class="g-item">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" <?php checked('yes',$notice_enabled, true); ?> id="noticeenabled">
                                                        <label class="custom-control-label" for="noticeenabled"><?php esc_html_e('Enable Store Notice','marketking');
                                                          ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- .nk-block-content -->
                                        <br>
                                      
                                        <div class="form-group"><label class="form-label" for="noticemessage"><?php esc_html_e('Set Notice Message','marketking');?></label><div class="form-control-wrap"><textarea class="form-control form-control-sm valid" id="noticemessage" name="noticemessage" placeholder="<?php esc_attr_e('Enter your message','marketking');?>" required="" aria-invalid="false"><?php echo esc_html($notice_message);?></textarea></div></div>

                                        <button class="btn btn-primary" type="button" id="marketking_save_notice_settings" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Settings','marketking');?></button>

                                        <br><br><br>
                                        <div class="marketking_store_notice_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-help"></em><?php esc_html_e('While','marketking');?> <strong><?php esc_html_e('Store Notice','marketking');?></strong><?php esc_html_e(' is enabled, the notice message will be displayed at the top of your store\'s pages.','marketking');?></div></div>
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