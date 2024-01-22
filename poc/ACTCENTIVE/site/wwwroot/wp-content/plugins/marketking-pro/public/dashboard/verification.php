<?php

/*

* @version 1.0.1

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_verification_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('verification')){
            ?>
            <div class="nk-content marketking_verification_page">
            <div class="container-fluid">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block">
                            <div class="card">
                                <div class="card-aside-wrap">
                                    <div class="card-inner card-inner-lg">
                                        <div class="nk-block-head nk-block-head-lg">
                                            <div class="nk-block-between">
                                                <div class="nk-block-head-content">
                                                    <h4 class="nk-block-title"><em class="icon ni ni-shield-check-fill"></em>&nbsp;&nbsp;<?php esc_html_e('Verification','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        
                                        $agent_group = get_user_meta($user_id, 'marketking_group', true);

                                        // get if there are any verification items that apply
                                        $verificationitems = get_posts(array( 'post_type' => 'marketking_vitem',
                                                  'post_status'=>'publish',
                                                  'numberposts' => -1,
                                                  'meta_query'=> array(
                                                        'relation' => 'OR',
                                                        array(
                                                            'key' => 'marketking_group_'.$agent_group,
                                                            'value' => '1',
                                                        ),
                                                    )));
                                        $verificationitems = array_reverse($verificationitems);
                                        if (empty($verificationitems)){
                                            ?>
                                            <div class="marketking_store_notice_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-shield-check"></em><?php esc_html_e('No verification action is currently required on your part.','marketking');?></div></div>
                                            <?php
                                        } else {
                                            foreach ($verificationitems as $item){
                                                $title = $item->post_title;

                                                // get if there is any vreq in this user's name for this vitem, that is approved, or pending
                                                $vreqs = get_posts(array( 'post_type' => 'marketking_vreq',
                                                  'post_status'=>'publish',
                                                  'numberposts' => -1,
                                                  'author' => $user_id,
                                                ));
                                                $vreqs = array_reverse($vreqs);

                                                // remove vreqs that don't have to do with this vitem
                                                foreach ($vreqs as $id=>$vreq){
                                                    $vitem = get_post_meta($vreq->ID,'vitem', true);
                                                    if (intval($vitem) !== intval($item->ID)){
                                                        unset($vreqs[$id]);
                                                    }
                                                }

                                                $nrrejected = 0;
                                                $nrpending = 0;
                                                $nrapproved = 0;
                                                $rejectionreason = '';

                                                foreach ($vreqs as $id => $vreq){
                                                    // go through remaining items
                                                    $status = get_post_meta($vreq->ID,'status', true);
                                                    if ($status === 'approved'){
                                                        $nrapproved++;
                                                    } else if ($status === 'pending'){
                                                        $nrpending++;
                                                    } else if ($status === 'rejected'){
                                                        $nrrejected++;
                                                        $rejectionreason = get_post_meta($vreq->ID,'rejection_reason', true);
                                                    }
                                                }

                                                if (empty($vreqs)){
                                                    $status = 'none';
                                                    $show_upload = 'yes';
                                                } else {
                                                    if ($nrapproved > 0){
                                                        // if there's any approved request, can't upload more
                                                        $show_upload = 'no';
                                                        $status = 'approved';
                                                    } else if ($nrpending > 0){
                                                        // if there's any pending request, can't upload more
                                                        $show_upload = 'no';
                                                        $status = 'pending';
                                                    } else {
                                                        $show_upload = 'yes';
                                                        $status = 'rejected';
                                                    }
                                                }


                                                $description = get_post_meta($item->ID, 'marketking_vitem_description_textarea', true);

                                                ?>
                                                <div class="nk-block">
                                                    <div class="nk-data data-list">
                                                        <input type="hidden" class="marketking_input_verification_id" value="<?php echo esc_attr($item->ID);?>">
                                                        <input type="hidden" class="marketking_input_verification_name" value="<?php echo esc_attr($title);?>">
                                                        <div class="data-head">
                                                            <h6 class="overline-title"><?php echo esc_html($title);
                                                            if ($status === 'pending'){
                                                                echo ' - <div class="marketking_approval_status">'.esc_html__('Approval Pending','marketking').'</div>';
                                                            } else if ($status === 'approved'){
                                                                echo ' - <div class="marketking_approval_status">'.esc_html__('Successfully Approved','marketking').'</div>';
                                                            } else if ($status === 'rejected'){
                                                                echo ' - <div class="marketking_approval_status">'.esc_html__('Previous Submission Rejected, Please Re-Upload','marketking').'</div>';
                                                            }
                                                            ?></h6>
                                                        </div>

                                                        <div class="data-item data-item-profile" data-toggle="modal" data-target="<?php

                                                        if ($show_upload === 'yes' ){
                                                            echo '#verificationmodal'; 
                                                        }

                                                        ?>">
                                                            <div class="data-col">
                                                                <span class="data-label"><?php echo nl2br(esc_html($description));?></span>
                                                            </div>
                                                            <?php

                                                            if ($show_upload === 'yes' ){
                                                                ?>
                                                                <div class="data-col data-col-end"><span class="data-more"><em class="icon ni ni-forward-ios" value="" ></em></span></div>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div><!-- data-item -->
                                                        <?php
                                                        if ($status === 'rejected'){
                                                            if (!empty($rejectionreason)){
                                                                ?>
                                                                <div class="marketking_store_notice_help_alert"><div class="alert alert-danger alert-icon"><em class="icon ni ni-cross-circle"></em><?php echo esc_html__('Rejection reason:','marketking').' '.esc_html($rejectionreason);?></div></div>

                                                                <?php
                                                            }
                                                            
                                                        }
                                                        ?>

                                                    </div><!-- data-list -->
                                                </div><!-- .nk-block -->
                                                <?php
                                            }
                                        }
                                        ?>
                                        
                                    </div>
                                    <?php include(MARKETKINGCORE_DIR.'/public/dashboard/templates/profile-sidebar.php'); ?>
                                    <div class="modal fade" tabindex="-1" role="dialog" id="verificationmodal">
                                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                            <div class="modal-content">
                                                <a href="#" class="close" data-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                                                <div class="modal-body modal-body-lg">
                                                    <h5 class="title"><?php esc_html_e('Verification','marketking');?></h5>
                                                    <ul class="nk-nav nav nav-tabs">
                                                        <li class="nav-item">
                                                            <a class="nav-link active nav-tab-personal" data-toggle="tab" href="#personal"><?php esc_html_e('Upload Files','marketking');?></a>
                                                           
                                                        </li>
                                                    </ul><!-- .nav-tabs -->
                                                    <div class="tab-content">
                                                        <div class="tab-pane active" id="personal">
                                                            <div class="row gy-4">
                                                                <div class="col-md-12">
                                                                    <input type="hidden" id="marketking_upload_verification_id">
                                                                        <label class="form-label" for="default-06" id="marketking_label_file_verification"><?php esc_html_e('Choose File Upload','marketking');?></label>
                                                                        <div class="row g-3 align-center">

                                                                            <div class="col-lg-9">
                                                                                 <div class="form-group"><div class="form-control-wrap"><div class="custom-file"><input type="text" class="form-control" id="marketking_upload_file_verification"></div></div></div>
                                                                                </ul>
                                                                            </div>

                                                                            <div class="col-lg-3">
                                                                                <div class="form-group">
                                                                                    <button class="marketking_verification_choose_file_button btn btn-sm btn-secondary"><?php esc_html_e('Choose File','marketking');?></button>
                                                                                </div>
                                                                            </div>
                                                                        </div>


                                                                    
                                                                </div>

                                                                <div class="col-12">
                                                                    <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                                        <li>
                                                                            <button class="marketking_upload_verification_file_button btn btn-md btn-primary"><?php esc_html_e('Upload & Submit','marketking');?></button>
                                                                        </li>
                                                                        <li>
                                                                            <a href="#" data-dismiss="modal" class="link link-light"><?php esc_html_e('Cancel','marketking');?></a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div><!-- .tab-pane -->
                                                    </div><!-- .tab-content -->
                                                </div><!-- .modal-body -->
                                            </div><!-- .modal-content -->
                                        </div><!-- .modal-dialog -->
                                    </div><!-- .modal -->
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