<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_storeseo_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('storeseo')){
            ?>
            <div class="nk-content marketking_storeseo_page">
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
                                                    <h4 class="nk-block-title"><em class="icon ni ni-search"></em>&nbsp;&nbsp;<?php esc_html_e('Store SEO','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        
                                        $marketking_seotitle = get_user_meta($user_id, 'marketking_seotitle', true);
                                        $marketking_metadescription = get_user_meta($user_id, 'marketking_metadescription', true);
                                        $marketking_metakeywords = get_user_meta($user_id, 'marketking_metakeywords', true);
                                        
                                        ?>


                                        <div class="form-group">
                                            <label class="form-label" for="seotitle"><?php esc_html_e('SEO Title','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-edit"></em>
                                                </div>
                                                <input type="text" class="form-control" id="seotitle" placeholder="<?php esc_attr_e('Enter your store page SEO title here...','marketking');?>" value="<?php echo esc_attr($marketking_seotitle);?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="metadescription"><?php esc_html_e('Meta Description','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-notes-alt"></em>
                                                </div>
                                                <input type="text" class="form-control" id="metadescription" placeholder="<?php esc_attr_e('Enter your store page meta description here...','marketking');?>" value="<?php echo esc_attr($marketking_metadescription);?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="metakeywords"><?php esc_html_e('Meta Keywords','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-search"></em>
                                                </div>
                                                <input type="text" class="form-control" id="metakeywords" placeholder="<?php esc_attr_e('Enter your store page meta keywords here (comma-separated) ...','marketking');?>" value="<?php echo esc_attr($marketking_metakeywords);?>">
                                            </div>
                                        </div>

                                        <button class="btn btn-primary" type="button" id="marketking_save_seo_settings" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Settings','marketking');?></button>

                                        <br><br><br>
                                        <div class="marketking_store_seo_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-help"></em><?php esc_html_e('These SEO settings apply to your store pages, setting the title and meta tags seen by search engines.','marketking');?></div></div>

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