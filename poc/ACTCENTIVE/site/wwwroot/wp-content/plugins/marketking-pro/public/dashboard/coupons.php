<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (intval(get_option( 'marketking_enable_coupons_setting', 1 )) === 1){
    if(marketking()->vendor_has_panel('coupons')){
        $user_id = marketking()->get_data('user_id');
        $currentuser = new WP_User($user_id);
        
        ?>
        <div class="nk-content marketking_coupons_page">
            <div class="container-fluid">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block-head nk-block-head-sm">
                            <div class="nk-block-between">
                                <div class="nk-block-head-content">
                                    <h3 class="nk-block-title page-title"><?php esc_html_e('Coupons','marketking'); ?></h3>
                                </div><!-- .nk-block-head-content -->
                                <div class="nk-block-head-content">
                                    <div class="toggle-wrap nk-block-tools-toggle">
                                        <div>
                                            <ul class="nk-block-tools g-3">
                                                <li>
                                                    <div class="form-control-wrap">
                                                        <div class="form-icon form-icon-right">
                                                            <em class="icon ni ni-search"></em>
                                                        </div>
                                                        <input type="text" class="form-control" id="marketking_coupons_search" placeholder="<?php esc_html_e('Search coupons...','marketking');?>">
                                                    </div>
                                                </li>
                                                <?php

                                                $checkedval = 0;
                                                if (marketking()->is_vendor_team_member()){
                                                    $checkedval = intval(get_user_meta(get_current_user_id(),'marketking_teammember_available_panel_editcoupons', true));
                                                }

                                                // either not team member, or team member with permission to add
                                                if (!marketking()->is_vendor_team_member() || $checkedval === 1){
                                                    ?>
                                                    <li class="nk-block-tools-opt">
                                                        <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'edit-coupon/add');?>" class="btn btn-primary d-md-inline-flex"><em class="icon ni ni-plus"></em><span><?php esc_html_e('Add Coupon','marketking'); ?></span></a>
                                                    </li>
                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div><!-- .nk-block-head-content -->
                            </div><!-- .nk-block-between -->
                        </div><!-- .nk-block-head -->

                        <table id="marketking_dashboard_coupons_table" class="nk-tb-list is-separate mb-3">
                            <thead>
                                <tr class="nk-tb-item nk-tb-head">
                                    <th class="nk-tb-col marketking-column-small"><span class="sub-text"><?php esc_html_e('Code','marketking'); ?></span></th>
                                    <th class="nk-tb-col marketking-column-small"><span class="sub-text"><?php esc_html_e('Type','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-sm marketking-column-mid"><span class="sub-text"><?php esc_html_e('Amount','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-sm marketking-column-mid"><span class="sub-text"><?php esc_html_e('Description','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-md marketking-column-small"><span class="sub-text"><?php esc_html_e('Usage / Limit','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-md marketking-column-small"><span class="sub-text"><?php esc_html_e('Expiry Date','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-md marketking-column-small"><span class="sub-text"><?php esc_html_e('Status','marketking'); ?></span></th>

                                    <th class="nk-tb-col tb-col-md marketking-column-small"><span class="sub-text"><?php esc_html_e('Actions','marketking'); ?></span></th>                           

                                </tr>
                            </thead>
                            <?php
                            if (!marketking()->load_tables_with_ajax(get_current_user_id())){
                                ?>
                                <tfoot>
                                    <tr class="nk-tb-item nk-tb-head">
                                        <th class="nk-tb-col tb-non-tools"><?php esc_html_e('code','marketking'); ?></th>
                                        <th class="nk-tb-col tb-non-tools"><?php esc_html_e('type','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('amount','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('description','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('limit','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('expiry','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('status','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-md tb-non-tools"></th>
                                    </tr>
                                </tfoot>
                                <?php
                            }
                            ?>
                            <tbody>
                                <?php

                                if (!marketking()->load_tables_with_ajax(get_current_user_id())){
                                    $vendor_coupons = get_posts( array( 'post_type' => 'shop_coupon','post_status'=>'any','numberposts' => -1, 'author'   => $user_id, 'fields' =>'ids') );


                                    foreach ($vendor_coupons as $couponid){
                                        $coupon = new WC_Coupon($couponid);
                                        if ($coupon !== false){
                                        ?>
                                        <tr class="nk-tb-item">
                                            <td class="nk-tb-col marketking-column-small">
                                                <a href="<?php 

                                                if (!marketking()->is_vendor_team_member() || $checkedval === 1){
                                                    echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'edit-coupon/'.$couponid);
                                                } else {
                                                    echo esc_attr(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))).'coupons';
                                                }

                                                ?>">
                                                    <span class="tb-coupon">
                                                    <?php
     
                                                    $code = $coupon -> get_code();
                                                    $type = $coupon->get_discount_type();
                                                    $amount = $coupon->get_amount();
                                                    $description = $coupon->get_description();

                                                    $expiry_date = $coupon->get_date_expires();
                                                    $usage_count = $coupon->get_usage_count();
                                                    $usage_limit = $coupon->get_usage_limit();

                                                    $time = $coupon->get_date_modified();
                                                    if ($time === null){
                                                        $time = $coupon->get_date_created();
                                                    }


                                                    ?>
                                                    <span class="title"><?php echo esc_html($code);?></span>
                                                    </span>
                                                </a>

                                            </td>
                                            <td class="nk-tb-col marketking-column-small">
                                                <span class="tb-lead"><?php 

                                                $type_name = array(
                                                    'percent'       => esc_html__( 'Percentage discount', 'woocommerce' ),
                                                    'fixed_cart'    => esc_html__( 'Fixed cart discount', 'woocommerce' ),
                                                    'fixed_product' => esc_html__( 'Fixed product discount', 'woocommerce' ),
                                                );
                                                echo esc_html($type_name[$type]);
                                                ?></span>
                                            </td>
                                            <td class="nk-tb-col tb-col-sm marketking-column-mid">
                                                <span class="tb-sub">
                                                <?php
                                                echo esc_html($amount);
                                                ?>
                                                </span>
                                            </td>
                                            <td class="nk-tb-col tb-col-sm marketking-column-mid">
                                                <span class="tb-sub"><?php echo esc_html($description);?></span>
                                            </td>


                                            <td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php echo esc_attr($usage_count);?>">
                                                <span class="tb-sub"><?php


                                                printf(
                                                    /* translators: 1: count 2: limit */
                                                    esc_html__( '%1$s / %2$s', 'woocommerce' ),
                                                    esc_html( $usage_count ),
                                                    $usage_limit ? esc_html( $usage_limit ) : '&infin;'
                                                );

                                                ?></span>
                                            </td>
                                            <td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php echo esc_attr($expiry_date);?>">
                                                <span class="tb-sub"><?php 

                                                if ( $expiry_date ) {
                                                    echo esc_html( $expiry_date->date_i18n( 'F j, Y' ) );
                                                } else {
                                                    echo '&ndash;';
                                                }

                                                ?></span>
                                            </td>
                                            <td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php echo esc_attr($expiry_date);?>">
                                               <?php 

                                                $status = get_post($couponid)->post_status;
                                                $statustext = $badge = '';
                                                if ($status === 'publish'){
                                                    $badge = 'badge-success';
                                                    $statustext = esc_html__('Published','marketking');
                                                } else if ($status === 'draft'){
                                                    $badge = 'badge-gray';
                                                    $statustext = esc_html__('Draft','marketking');
                                                } else if ($status === 'pending'){
                                                     $badge = 'badge-info';
                                                     $statustext = esc_html__('Pending','marketking');
                                                } else {
                                                    $badge = 'badge-gray';
                                                    $statustext = ucfirst($status);
                                                }
                                                ?>
                                                <span class="badge badge-sm badge-dot has-bg <?php echo esc_attr($badge);?> d-none d-mb-inline-flex"><?php
                                                echo esc_html(ucfirst($statustext));
                                                ?></span>
                                            </td>
                                            <td class="nk-tb-col tb-col-md marketking-column-mid">
                                                <ul class="nk-tb-actions gx-1 my-n1">
                                                    <li class="mr-n1">
                                                        <?php
                                                        if (!marketking()->is_vendor_team_member() || $checkedval === 1){
                                                            ?>
                                                                <div class="dropdown">
                                                                    <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        
                                                                            <ul class="link-list-opt no-bdr">
                                                                                <li><a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'edit-coupon/'.$coupon->get_id());?>"><em class="icon ni ni-edit"></em><span><?php esc_html_e('Edit coupon','marketking'); ?></span></a></li>
                                                                                <li><a href="#" class="toggle marketking_delete_button_coupon" value="<?php echo esc_attr($coupon->get_id());?>"><em class="icon ni ni-trash"></em><span><?php esc_html_e('Delete coupon','marketking'); ?></span></a></li>
                                                                            </ul>
                                                                            
                                                                    </div>
                                                                </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </li>
                                                </ul>
                                            </td>
                                            
                                        </tr>
                                        <?php
                                        }
                                    }
                                }
                                
                                ?>
                                
                            </tbody>
                            
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>