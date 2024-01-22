<?php

/*

* @version 1.0.2

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (intval(get_option( 'marketking_enable_coupons_setting', 1 )) === 1){
    if(marketking()->vendor_has_panel('coupons')){

        $checkedval = 0;
        if (marketking()->is_vendor_team_member()){
            $checkedval = intval(get_user_meta(get_current_user_id(),'marketking_teammember_available_panel_editcoupons', true));
        }

        // either not team member, or team member with permission to add
        if (!marketking()->is_vendor_team_member() || $checkedval === 1){
            ?>
            <div class="nk-content marketking_edit_coupon_page">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <form id="marketking_save_coupon_form">
                            <?php

                            $prod = sanitize_text_field(marketking()->get_pagenr_query_var());
                            $post = 0;
                            if (!empty($prod)){
                                $post = get_post($prod);
                                if (!is_a($post, 'WP_Post')){
                                    $post = 0;
                                }
                            }

                            if ($post === 0){
                                $text = esc_html__('Save New Coupon','marketking');
                                $icon = 'ni-plus';
                                $actionedit = 'add';
                            } else {
                                $text = esc_html__('Publish Coupon','marketking');
                                $icon = 'ni-edit-fill';
                                $actionedit = 'edit';
                                $coupon = new WC_Coupon($prod);
                            }

                            ?>
                            <input id="marketking_edit_coupon_action_edit" type="hidden" value="<?php echo esc_attr($actionedit);?>">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content marketking_status_text_title">
                                        <h3 class="nk-block-title page-title "><?php esc_html_e('Edit Coupon','marketking'); ?></h3>
                                        <div class="marketking_edit_status_container">
                                            <?php
                                                if ($post !== 0){
                                                    $status = get_post_status($prod);
                                                } else {
                                                    $status = 'publish';
                                                }

                                                if ($actionedit === 'add'){
                                                    $status = 'new';
                                                }

                                            ?>
                                            <p class="marketking_status_text">- &nbsp;</p>
                                            <select name="marketking_edit_coupon_status" id="marketking_edit_coupon_status" class="marketking_status_<?php echo esc_attr($status);?>" disabled>
                                                <option value="publish" <?php selected($status, 'publish', true);?>><?php esc_html_e('Published');?></option>
                                                <option value="draft" <?php selected($status, 'draft', true);?>><?php esc_html_e('Draft');?></option>
                                                <option value="new" <?php selected($status, 'new', true);?>><?php esc_html_e('New Coupon');?></option>
                                            </select>&nbsp;
                                        </div>
                                    </div><!-- .nk-block-head-content -->
                                    <div class="nk-block-head-content">
                                        <div class="toggle-wrap nk-block-tools-toggle">
                                            <div data-content="pageMenu">
                                                <ul class="nk-block-tools g-3">
                                                    <input type="hidden" id="marketking_save_coupon_button_id" value="<?php echo esc_attr($prod);?>">
                                                    <li class="nk-block-tools-opt">
                                                        <div id="marketking_save_coupon_button">
                                                            <a href="#" class="toggle btn btn-primary d-md-inline-flex"><em class="icon ni <?php echo esc_attr($icon);?>"></em><span><?php echo esc_html($text); ?></span></a>
                                                        </div>
                                                        <div id="marketking_save_coupon_draft_button" class="ml-2">
                                                            <a href="#" class="toggle btn btn-gray d-md-inline-flex"><em class="icon ni ni-file-text"></em><span><?php esc_html_e('Save as Draft','marketking'); ?></span></a>
                                                        </div>
                                                        <?php
                                                        if ($post !== 0){
                                                            // additional buttons for View coupon and Remove coupon
                                                            ?>
                                                            <div class="dropdown">
                                                                <a href="#" class="dropdown-toggle btn btn-icon btn-gray btn-trigger ml-2 text-white pl-2 pr-3" data-toggle="dropdown"><em class="icon ni ni-more-h"></em><?php esc_html_e('More','marketking'); ?></a>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <ul class="link-list-opt no-bdr">
                                                                        <li><a href="#" class="toggle marketking_delete_button_coupon" value="<?php echo esc_attr($prod);?>"><em class="icon ni ni-trash"></em><span><?php esc_html_e('Delete coupon','marketking'); ?></span></a></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div><!-- .nk-block-head-content -->
                                </div><!-- .nk-block-between -->
                            </div><!-- .nk-block-head -->

                            <!-- coupon code -->
                            <?php
                            if($post!==0){
                                $code = $coupon->get_code();
                            } else {
                                $code = '';
                            }
                            ?>

                            <?php
                            if (isset($_GET['add'])){
                                $add = sanitize_text_field($_GET['add']);;
                                if ($add === 'success'){
                                    ?>                                    
                                    <div class="alert alert-primary alert-icon"><em class="icon ni ni-check-circle"></em> <strong><?php esc_html_e('Your coupon has been created successfully','marketking');?></strong>. <?php esc_html_e('You can now continue to edit it','marketking');?>.</div>
                                    <?php
                                }
                            }
                            if (isset($_GET['update'])){
                                $add = sanitize_text_field($_GET['update']);;
                                if ($add === 'success'){
                                    ?>                                    
                                    <div class="alert alert-primary alert-icon"><em class="icon ni ni-check-circle"></em> <strong><?php esc_html_e('Your coupon has been updated successfully','marketking');?></strong>.</div>
                                    <?php
                                }
                            }
                            ?>


                            <div><div class="form-group"><div class="form-control-wrap"><input type="text" class="form-control form-control-lg form-control-outlined" id="marketking_coupon_code" value="<?php echo esc_attr($code);?>" required><label class="form-label-outlined" for="outlined-lg"><?php esc_html_e('Coupon Name','marketking');?></label></div></div></div>

                            <!-- coupon DATA -->
                            <div id="marketking_edit_product_data_container" class="postbox-container">
                                <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                                    <div id="woocommerce-coupon-data" class="postbox ">
                                        <div class="postbox-header">
                                            <h2 class="hndle ui-sortable-handle">
                                                <?php esc_html_e("Coupon data",'marketking');?>
                                            </h2>
                                        </div>
                                        <div class="inside">
                                            <?php
                                            if (!is_object($post)){
                                                global $post;

                                                $post = new stdClass();
                                                $post = new WP_Post($post);

                                                $post->ID = 0;
                                            }
                                            WC_Meta_Box_Coupon_Data::output($post); 
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <textarea id="woocommerce-coupon-description" name="excerpt" cols="5" rows="2" placeholder="<?php esc_attr_e( 'Description (optional)', 'woocommerce' ); ?>"><?php 

                                if($actionedit === 'edit'){
                                    echo esc_html($post->post_excerpt);
                                }
                             ?></textarea>

                             <?php

                             if (apply_filters('marketking_show_select_all_coupons', true)){
                                ?>
                                 <div class="marketking_coupon_select_all_products">
                                    <?php

                                    $vendor_id = get_current_user_id();
                                    if (marketking()->is_vendor_team_member()){
                                        $vendor_id = marketking()->get_team_member_parent();
                                    }

                                    $vendor_products = wc_get_products( array( 
                                        'numberposts' => -1,
                                        'post_status'    => array( 'draft', 'pending', 'private', 'publish' ),
                                        'author'   => $vendor_id,
                                        'orderby' => 'date',
                                        'order' => 'DESC',
                                    ));


                                    ?>
                                    <select id="marketking_coupon_products_select" multiple>
                                        <?php
                                        foreach ($vendor_products as $product){
                                            echo '<option value="'.esc_attr($product->get_id()).'" selected="selected">'.esc_html($product->get_name()).'</option>';
                                        }
                                        ?>
                                    </select>
                                    <button id="marketking_select_all" type="button" class="btn btn-gray btn-sm"><?php esc_html_e('Select all', 'marketking'); ?></button>&nbsp;&nbsp;<button id="marketking_unselect_all" type="button" class="btn btn-gray btn-sm"><?php esc_html_e('Unselect all', 'marketking'); ?></button>
                                </div>
                                <?php
                             }
                                                         
                            ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}
?>