<?php

/*

* @version 1.0.1

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (intval(get_option( 'marketking_enable_teams_setting', 1 )) === 1){
    if(marketking()->vendor_has_panel('teams')){
        ?>
        <div class="nk-content marketking_edit_team_page">
            <div class="container-fluid">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <form id="marketking_edit_team_form">
                        <?php

                        $team_member_id = sanitize_text_field(marketking()->get_pagenr_query_var());
                        
                        $text = esc_html__('Update User','marketking');
                        $icon = 'ni-edit-fill';

                        ?>
                        <div class="nk-block-head nk-block-head-sm">
                            <div class="nk-block-between">
                                <div class="nk-block-head-content marketking_status_text_title">
                                    <h3 class="nk-block-title page-title "><?php esc_html_e('Edit Team Member','marketking'); ?></h3>
                                    
                                </div><!-- .nk-block-head-content -->
                                <div class="nk-block-head-content">
                                    <div class="toggle-wrap nk-block-tools-toggle">
                                        <a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                                        <div class="toggle-expand-content" data-content="pageMenu">
                                            <ul class="nk-block-tools g-3">
                                                <input type="hidden" id="marketking_save_team_button_id" value="<?php echo esc_attr($team_member_id);?>">
                                                <li class="nk-block-tools-opt">
                                                    <div id="marketking_save_team_button">
                                                        <a href="#" class="toggle btn btn-icon btn-primary d-md-none"><em class="icon ni <?php echo esc_attr($icon);?>"></em></a>
                                                        <a href="#" class="toggle btn btn-primary d-none d-md-inline-flex"><em class="icon ni <?php echo esc_attr($icon);?>"></em><span><?php echo esc_html($text); ?></span></a>
                                                    </div>
                                                    
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div><!-- .nk-block-head-content -->
                            </div><!-- .nk-block-between -->
                        </div><!-- .nk-block-head -->

                        <?php
                        if (isset($_GET['update'])){
                            $add = sanitize_text_field($_GET['update']);;
                            if ($add === 'success'){
                                ?>                                    
                                <div class="alert alert-primary alert-icon"><em class="icon ni ni-check-circle"></em> <strong><?php esc_html_e('Your team member account has been updated successfully','marketking');?></strong>.</div>
                                <?php
                            }
                        }
                        ?>

                        <div id="marketking_edit_product_data_container" class="postbox-container">
                            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                                <div id="woocommerce-coupon-data" class="postbox ">
                                    <div class="postbox-header">
                                        <h2 class="hndle ui-sortable-handle">
                                            <?php esc_html_e("Team Member Permissions",'marketking');?>
                                        </h2>
                                    </div>
                                    <div class="inside">
                                        <div class="card-inner">
                                            <h2 class="hndle ui-sortable-handle">
                                                <?php esc_html_e("Available Dashboard Panels",'marketking');?>
                                            </h2>
                                            <div class="nk-block-content">
                                                <br >
                                                <?php

                                                $panels = marketkingpro()->get_all_vendor_available_dashboard_panels();

                                                $panelnumber = count($panels);
                                                $panelhalf = ceil($panelnumber/2);
                                                $currentpanel = 0;

                                                $panelstext = '';
                                                foreach ($panels as $panel_slug => $panel_name){
                                                    $panelstext .= $panel_slug.':';
                                                }
                                                $panelstext = substr($panelstext, 0, -1);

                                                ?>
                                                <input type="hidden" id="marketking_team_dashboard_panels" value="<?php echo esc_attr($panelstext);?>">
                                                <div class="row g-gs">
                                                    <div class="col-xxl-6 col-sm-6">
                                                        <div class="gy-3">
                                                            <?php

                                                            foreach ($panels as $panel_slug => $panel_name){

                                                                if ($currentpanel < $panelhalf){
                                                                    $checkedval = intval(get_user_meta($team_member_id, 'marketking_teammember_available_panel_'.esc_attr($panel_slug), true));

                                                                    ?>

                                                                    <div class="g-item">
                                                                        <div class="custom-control custom-switch">
                                                                            <input type="checkbox" class="custom-control-input" <?php checked(1,$checkedval, true); ?> id="marketking_group_available_panel_<?php echo esc_attr($panel_slug); ?>">
                                                                            <label class="custom-control-label" for="marketking_group_available_panel_<?php echo esc_attr($panel_slug); ?>"><?php echo esc_html($panel_name); ?></label>
                                                                        </div>
                                                                    </div>
                                                                    <?php

                                                                    $currentpanel++;
                                                                }
                                                                
                                                            }

                                                            ?>
                                                            
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="col-xxl-6 col-sm-6">
                                                        <div class="gy-3">
                                                            <?php

                                                            $currentpanel = 0;

                                                            foreach ($panels as $panel_slug => $panel_name){
                                                                if ($currentpanel >= $panelhalf){
                                                                    $checkedval = intval(get_user_meta($team_member_id, 'marketking_teammember_available_panel_'.esc_attr($panel_slug), true));

                                                                    ?>

                                                                    <div class="g-item">
                                                                        <div class="custom-control custom-switch">
                                                                            <input type="checkbox" class="custom-control-input" <?php checked(1,$checkedval, true); ?> id="marketking_group_available_panel_<?php echo esc_attr($panel_slug); ?>">
                                                                            <label class="custom-control-label" for="marketking_group_available_panel_<?php echo esc_attr($panel_slug); ?>"><?php echo esc_html($panel_name); ?></label>
                                                                        </div>
                                                                    </div>
                                                                    <?php

                                                                }
                                                                $currentpanel++;

                                                                
                                                            }

                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!-- .nk-block-content -->

                                            <br><br>
                                            <h2 class="hndle ui-sortable-handle">
                                                <?php esc_html_e("Specific Permissions",'marketking');?>
                                            </h2>
                                            <div class="nk-block-content">
                                                <br >
                                                <div class="row g-gs">
                                                    <div class="col-xxl-6 col-sm-6">
                                                        <div class="gy-3">
                                                            
                                                            <div class="g-item">
                                                                <div class="custom-control custom-switch">
                                                                    <?php $checkedval = intval(get_user_meta($team_member_id,'marketking_teammember_available_panel_editproducts', true)); ?>
                                                                    <input type="checkbox" class="custom-control-input" <?php checked(1,$checkedval, true); ?> id="marketking_group_available_panel_editproducts">
                                                                    <label class="custom-control-label" for="marketking_group_available_panel_editproducts"><?php esc_html_e('Create and edit products','marketking'); ?></label>
                                                                </div>
                                                            </div>

                                                            <div class="g-item">
                                                                <div class="custom-control custom-switch">
                                                                    <?php $checkedval = intval(get_user_meta($team_member_id,'marketking_teammember_available_panel_editcoupons', true)); ?>
                                                                    <input type="checkbox" class="custom-control-input" <?php checked(1,$checkedval, true); ?> id="marketking_group_available_panel_editcoupons">
                                                                    <label class="custom-control-label" for="marketking_group_available_panel_editcoupons"><?php esc_html_e('Create and edit coupons','marketking'); ?></label>
                                                                </div>
                                                            </div>

                                                            <div class="g-item">
                                                                <div class="custom-control custom-switch">
                                                                    <?php $checkedval = intval(get_user_meta($team_member_id,'marketking_teammember_available_panel_editorders', true)); ?>
                                                                    <input type="checkbox" class="custom-control-input" <?php checked(1,$checkedval, true); ?> id="marketking_group_available_panel_editorders">
                                                                    <label class="custom-control-label" for="marketking_group_available_panel_editorders"><?php esc_html_e('Edit and update orders','marketking'); ?></label>
                                                                </div>
                                                            </div>

                                                            
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!-- .nk-block-content -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>