<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (intval(get_option( 'marketking_enable_reviews_setting', 1 )) === 1){
    if(marketking()->vendor_has_panel('reviews')){
        $user_id = marketking()->get_data('user_id');
        $currentuser = new WP_User($user_id);
        
        ?>
        <div class="nk-content marketking_reviews_page">
            <div class="container-fluid">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block-head nk-block-head-sm">
                            <div class="nk-block-between">
                                <div class="nk-block-head-content">
                                    <h3 class="nk-block-title page-title"><?php esc_html_e('Reviews','marketking'); ?></h3>
                                </div><!-- .nk-block-head-content -->
                                <div class="nk-block-head-content">
                                    <div class="toggle-wrap nk-block-tools-toggle">
                                        <a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                                        <div class="toggle-expand-content" data-content="pageMenu">
                                            <ul class="nk-block-tools g-3">
                                                <li>
                                                    <div class="form-control-wrap">
                                                        <div class="form-icon form-icon-right">
                                                            <em class="icon ni ni-search"></em>
                                                        </div>
                                                        <input type="text" class="form-control" id="marketking_reviews_search" placeholder="<?php esc_html_e('Search reviews...','marketking');?>">
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div><!-- .nk-block-head-content -->
                            </div><!-- .nk-block-between -->
                        </div><!-- .nk-block-head -->

                        <table id="marketking_dashboard_reviews_table" class="nk-tb-list is-separate mb-3">
                            <thead>
                                <tr class="nk-tb-item nk-tb-head">
                                    <th class="nk-tb-col tb-col-sm marketking-column-mid"><span class="sub-text"><?php esc_html_e('Product','marketking'); ?></span></th>
                                    <th class="nk-tb-col marketking-column-small"><span class="sub-text"><?php esc_html_e('Rating','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-md marketking-column-small"><span class="sub-text"><?php esc_html_e('Review','marketking'); ?></span></th>
                                    <th class="nk-tb-col marketking-column-small"><span class="sub-text"><?php esc_html_e('User','marketking'); ?></span></th>

                                    <th class="nk-tb-col tb-col-md marketking-column-mid marketking-column-min sorting"><span class="sub-text"><?php esc_html_e('Actions','marketking'); ?></span></th>                           

                                </tr>
                            </thead>
                            <?php
                            if (!marketking()->load_tables_with_ajax(get_current_user_id())){
                                ?>
                                <tfoot>
                                    <tr class="nk-tb-item nk-tb-head">
                                        <th class="nk-tb-col tb-col-sm tb-non-tools"><?php esc_html_e('product','marketking'); ?></th>
                                        <th class="nk-tb-col tb-non-tools"><?php esc_html_e('rating','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('review','marketking'); ?></th>
                                        <th class="nk-tb-col tb-non-tools"><?php esc_html_e('user','marketking'); ?></th>
                                        
                                        <th class="nk-tb-col tb-col-md tb-non-tools marketking-column-min"></th>
                                    </tr>
                                </tfoot>
                                <?php
                            }
                            ?>
                            <tbody>
                                <?php

                                if (!marketking()->load_tables_with_ajax(get_current_user_id())){

                                    $args = array ('post_type' => 'product', 'post_author' => $user_id);
                                    $comments = get_comments( $args );

                                    foreach ($comments as $review){
                                        // get product
                                        $productid = $review -> comment_post_ID;
                                        $product = wc_get_product($productid);
                                        $product_name = $product->get_title();
                                        $product_link = $product->get_permalink();

                                        $product_title = '<a href="'.esc_attr($product_link).'">'.esc_html($product_name).'</a>';

                                        $comment = $review -> comment_content;
                                        $review_id = $review->comment_ID;
                                        $rating = get_comment_meta($review_id,'rating', true); 

                                        $review_author = $review->comment_author;

                                        if (!empty($rating)){
                                        ?>
                                        <tr class="nk-tb-item">
                                            <td class="nk-tb-col tb-col-sm marketking-column-mid">
                                                <a href="<?php echo esc_attr($product_link);?>">
                                                    <span class="tb-coupon">
                                                    <span class="title"><?php echo esc_html($product_name);?></span>
                                                    </span>
                                                </a>

                                            </td>
                                            <td class="nk-tb-col marketking-column-small">
                                                <span class="tb-lead"><?php 

                                                echo esc_html($rating);
                                                ?></span>
                                            </td>
                                            <td class="nk-tb-col tb-col-md">
                                                <span class="tb-sub">
                                                <?php
                                                echo esc_html($comment);
                                                ?>
                                                </span>
                                            </td>
                                            <td class="nk-tb-col marketking-column-small">
                                                <span class="tb-sub"><?php echo esc_html($review_author );?></span>
                                            </td>
                                            
                                            <td class="nk-tb-col tb-col-md">
                                               

                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary marketking_view_review_button" value="<?php echo esc_attr($product_link);?>"><em class="icon ni ni-eye-fill"></em><span><?php esc_html_e('View','marketking');?></span></button>
                                                   
                                                  <?php
                                                  $has_reply = get_comment_meta($review_id,'has_reply', true);
                                                  if ($has_reply !== 'yes'){
                                                    ?>
                                                    <button class="btn btn-sm btn-outline-primary marketking_reply_review_button" value="<?php echo esc_attr($review_id);?>"><em class="icon ni ni-pen-fill"></em><span><?php esc_html_e('Reply','marketking');?></span></button>

                                                    <?php
                                                  }             
                                                  ?>                              
                                                  <?php
                                                    if (intval(get_option( 'marketking_enable_abusereports_setting', 1 )) === 1){
                                                        $has_report = get_comment_meta($review_id,'has_report', true);
                                                        if ($has_report !== 'yes'){
                                                            ?>
                                                            <button class="btn btn-sm btn-outline-primary marketking_report_review_button" value="<?php echo esc_attr($review_id);?>"><em class="icon ni ni-flag-fill"></em><span><?php esc_html_e('Report','marketking');?></span></button>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
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
        <a href="#" data-toggle="modal" data-target="#report_review" class="marketking_report_review_button_hidden"><input type="hidden"></a>

        <div class="modal fade" tabindex="-1" role="dialog" id="report_review">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title"><?php esc_html_e('Report Review','marketking');?></h6>
                        <a href="#" class="close" data-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                    </div>
                    <div class="modal-body p-0">
                        <div class="nk-reply-form-editor">
                            <div class="nk-reply-form-field">
                                <textarea class="form-control form-control-simple no-resize ex-large" id="marketking_report_review_content" placeholder="<?php esc_attr_e('Enter the reason you are reporting this review...','marketking');?>"></textarea>
                            </div>
                        </div><!-- .nk-reply-form-editor -->
                        <div class="nk-reply-form-tools">
                            <ul class="nk-reply-form-actions g-1">
                                <li class="mr-2"><button class="btn btn-primary" id="marketking_report_review" type="submit"><?php esc_html_e('Send','marketking');?></button></li>
                            </ul>
                            <input type="hidden" id="review_id" value="">
                           
                        </div><!-- .nk-reply-form-tools -->
                    </div><!-- .modal-body -->
                </div><!-- .modal-content -->
            </div><!-- .modla-dialog -->
        </div><!-- .modal -->

        <a href="#" data-toggle="modal" data-target="#reply_review" class="marketking_reply_review_button_hidden"><input type="hidden"></a>

        <div class="modal fade" tabindex="-1" role="dialog" id="reply_review">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title"><?php esc_html_e('Reply to Review','marketking');?></h6>
                        <a href="#" class="close" data-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                    </div>
                    <div class="modal-body p-0">
                        <div class="nk-reply-form-editor">
                            <div class="nk-reply-form-field">
                                <textarea class="form-control form-control-simple no-resize ex-large" id="marketking_reply_review_content" placeholder="<?php esc_attr_e('Here you can leave a publicly visible reply. Please note that you can only reply once.','marketking');?>"></textarea>
                            </div>
                        </div><!-- .nk-reply-form-editor -->
                        <div class="nk-reply-form-tools">
                            <ul class="nk-reply-form-actions g-1">
                                <li class="mr-2"><button class="btn btn-primary" id="marketking_reply_review" type="submit"><?php esc_html_e('Send','marketking');?></button></li>
                            </ul>
                           
                        </div><!-- .nk-reply-form-tools -->
                    </div><!-- .modal-body -->
                </div><!-- .modal-content -->
            </div><!-- .modla-dialog -->
        </div><!-- .modal -->
        <?php
    }
}
?>