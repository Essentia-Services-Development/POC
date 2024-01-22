<?php

/*

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_storecategories_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('storecategories')){
            ?>
            <div class="nk-content marketking_storecategories_page">
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
                                                    <h4 class="nk-block-title"><em class="icon ni ni-box-view-fill"></em>&nbsp;&nbsp;<?php esc_html_e('Store Categories','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        
                                        ?>
                                      
                                        <div class="col-xxl-6 col-md-6 marketking_storecat_tab">
                                            <h6 class="overline-title title"><?php esc_html_e('Categories','marketking');?></h6>
                                                <div class="form-group">
                                                    <div class="form-control-wrap">
                                                        <?php

                                                        $selectedarr = get_user_meta($user_id,'marketking_store_categories', true);
                                                        if (empty($selectedarr)){
                                                            $selectedarr = array();
                                                        }

                                                        $args =  array(
                                                            'hierarchical'     => 1,
                                                            'hide_empty'       => 0,
                                                            'class'            => 'form_select',
                                                            'name'             => 'marketking_select_storecategories',
                                                            'id'               => 'marketking_select_storecategories',
                                                            'taxonomy'         => 'storecat',
                                                            'orderby'          => 'name',
                                                            'title_li'         => '',
                                                            'selected'         => implode(',',$selectedarr)
                                                        );

                                                        // Mutiple categories in pro

                                                        if(defined('MARKETKINGPRO_DIR')){
                                                            $current_id = $user_id;
                                                            if (marketking()->is_vendor_team_member()){
                                                                $current_id = marketking()->get_team_member_parent();
                                                            }
                                                            if (marketking()->vendor_can_multiple_store_categories($current_id)){
                                                                $args['multiple'] = true;
                                                            }
                                                        }

                                                        wp_dropdown_categories( $args );
                                                        ?>
                                                        
                                                    </div>
                                                </div>
                                        </div>


                                        <br><br>


                                        <button class="btn btn-primary" type="button" id="marketking_save_storecategories_settings" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Settings','marketking');?></button>

                                        <br><br><br>
                                        <div class="marketking_store_policy_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-help"></em><?php esc_html_e('Through accurate','marketking');?> <strong><?php esc_html_e('Store Categories','marketking');?></strong><?php esc_html_e(' your store can easily be found by prospective customers.','marketking');?></div></div>
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