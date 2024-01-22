<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_social_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('social')){
            ?>
            <div class="nk-content marketking_storesocial_page">
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
                                                    <h4 class="nk-block-title"><em class="icon ni ni-user-circle"></em>&nbsp;&nbsp;<?php esc_html_e('Social Profiles','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);

                                        $twitter = get_user_meta($user_id, 'marketking_twitter', true);
                                        $facebook = get_user_meta($user_id, 'marketking_facebook', true);
                                        $instagram = get_user_meta($user_id, 'marketking_instagram', true);
                                        $youtube = get_user_meta($user_id, 'marketking_youtube', true);
                                        $linkedin = get_user_meta($user_id, 'marketking_linkedin', true);
                                        $pinterest = get_user_meta($user_id, 'marketking_pinterest', true);

                                        ?>
                                        <div class="form-group <?php if (!marketking()->social_site_active('twitter')) echo 'marketking_social_hidden';?>">
                                            <label class="form-label" for="twitter"><?php esc_html_e('Twitter','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-twitter"></em>
                                                </div>
                                                <input type="text" class="form-control" id="twitter" placeholder="<?php esc_attr_e('Enter your Twitter profile link here...','marketking');?>" value="<?php echo esc_attr($twitter);?>">
                                            </div>
                                        </div>
                                        <div class="form-group <?php if (!marketking()->social_site_active('facebook')) echo 'marketking_social_hidden';?>">
                                            <label class="form-label" for="facebook"><?php esc_html_e('Facebook','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-facebook-fill"></em>
                                                </div>
                                                <input type="text" class="form-control" id="facebook" placeholder="<?php esc_attr_e('Enter your Facebook profile name here...','marketking');?>" value="<?php echo esc_attr($facebook);?>">
                                            </div>
                                        </div>
                                        <div class="form-group <?php if (!marketking()->social_site_active('instagram')) echo 'marketking_social_hidden';?>">
                                            <label class="form-label" for="instagram"><?php esc_html_e('Instagram','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-instagram"></em>
                                                </div>
                                                <input type="text" class="form-control" id="instagram" placeholder="<?php esc_attr_e('Enter your Instagram profile link here...','marketking');?>" value="<?php echo esc_attr($instagram);?>">
                                            </div>
                                        </div>
                                        <div class="form-group <?php if (!marketking()->social_site_active('youtube')) echo 'marketking_social_hidden';?>">
                                            <label class="form-label" for="youtube"><?php esc_html_e('Youtube','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-youtube"></em>
                                                </div>
                                                <input type="text" class="form-control" id="youtube" placeholder="<?php esc_attr_e('Enter your Youtube profile link here...','marketking');?>" value="<?php echo esc_attr($youtube);?>">
                                            </div>
                                        </div>
                                        <div class="form-group <?php if (!marketking()->social_site_active('linkedin')) echo 'marketking_social_hidden';?>">
                                            <label class="form-label" for="linkedin"><?php esc_html_e('Linkedin','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-linkedin"></em>
                                                </div>
                                                <input type="text" class="form-control" id="linkedin" placeholder="<?php esc_attr_e('Enter your Linkedin profile link here...','marketking');?>" value="<?php echo esc_attr($linkedin);?>">
                                            </div>
                                        </div>
                                        <div class="form-group <?php if (!marketking()->social_site_active('pinterest')) echo 'marketking_social_hidden';?>">
                                            <label class="form-label" for="pinterest"><?php esc_html_e('Pinterest','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-pinterest"></em>
                                                </div>
                                                <input type="text" class="form-control" id="pinterest" placeholder="<?php esc_attr_e('Enter your Pinterest profile link here...','marketking');?>" value="<?php echo esc_attr($pinterest);?>">
                                            </div>
                                        </div>

                                        <button class="btn btn-primary" type="submit" id="marketking_save_social_settings" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Settings','marketking');?></button>

                                        <br><br><br>
                                        <div class="marketking_store_seo_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-help"></em><?php esc_html_e('The social profiles you add here will be displayed on your store page.','marketking');?></div></div>

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