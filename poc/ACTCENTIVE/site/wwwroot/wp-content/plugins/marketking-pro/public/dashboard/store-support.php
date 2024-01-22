<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_support_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('support')){
            ?>
            <div class="nk-content marketking_support_page">
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
                                                    <h4 class="nk-block-title"><em class="icon ni ni-ticket-plus"></em>&nbsp;&nbsp;<?php esc_html_e('Support Settings','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        
                                        $supportoption = get_user_meta($user_id, 'marketking_support_option', true);
                                        
                                        $supporturl = get_user_meta($user_id, 'marketking_support_url', true);

                                        $supportemail = get_user_meta($user_id, 'marketking_support_email', true);

                                        ?>
                                        <div class="form-group"> 
                                            <label class="form-label"><?php esc_html_e('Choose Support Method','marketking');?></label>
                                            <div class="form-control-wrap supportchoicewrap">
                                                <select class="form-select" id="supportchoice">

                                                    <?php
                                                    if (intval(get_option('marketking_enable_support_messaging_setting', 1)) === 1){
                                                        ?>
                                                        <option value="messaging" <?php selected('messaging', $supportoption, true);?>><?php esc_html_e('Support through messaging panel','marketking');?></option> 
                                                        <?php
                                                    }
                                                    ?>

                                                    <?php
                                                    if (intval(get_option('marketking_enable_support_external_setting', 1)) === 1){

                                                        ?>
                                                        <option value="external" <?php selected('external', $supportoption, true);?>><?php esc_html_e('Support through external URL (your own forum, ticketing system, etc.)','marketking');?></option> 

                                                        <?php
                                                    }
                                                    ?>

                                                    <?php
                                                    if (intval(get_option('marketking_enable_support_email_setting', 1)) === 1){
                                                        ?>
                                                        <option value="email" <?php selected('email', $supportoption, true);?>><?php esc_html_e('Support through a dedicated email address','marketking');?></option> 
                                                        <?php
                                                    }
                                                    ?>
                                                    
                                                    
                                                </select>
                                            </div>
                                        </div>

                                        <div id="marketking_support_url_container">
                                            <div class="form-group">
                                                <label class="form-label" for="supporturl"><?php esc_html_e('Support URL','marketking');?></label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-left">
                                                        <em class="icon ni ni-link-alt"></em>
                                                    </div>
                                                    <input type="text" class="form-control" id="supporturl" placeholder="<?php esc_attr_e('Enter your external URL here, e.g. "https://yoursite.com" (please include https:// )','marketking');?>" value="<?php echo esc_attr($supporturl);?>">
                                                </div>
                                            </div>
                                            <Br>

                                        </div>


                                        <div id="marketking_support_email_container">
                                            <div class="form-group">
                                                <label class="form-label" for="supportemail"><?php esc_html_e('Support Email','marketking');?></label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-left">
                                                        <em class="icon ni ni-emails"></em>
                                                    </div>
                                                    <input type="text" class="form-control" id="supportemail" placeholder="<?php esc_attr_e('Enter your support email here...','marketking');?>" value="<?php echo esc_attr($supportemail);?>">
                                                </div>
                                            </div>
                                            <Br>
                                        </div>
                                        <button class="btn btn-primary" type="submit" id="marketking_save_support_settings" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Settings','marketking');?></button>

                                        <br><br><br>
                                        <div class="marketking_support_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-help"></em><?php esc_html_e('Through','marketking');?> <strong><?php esc_html_e('Support Settings','marketking');?></strong><?php esc_html_e(', you can configure how your shop provides support to customers. Only customers who have purchased your products will be able to see these options.','marketking');?></div></div>
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