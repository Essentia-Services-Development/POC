<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (intval(get_option( 'marketking_enable_teams_setting', 1 )) === 1){
    if(marketking()->vendor_has_panel('teams')){
        $user_id = marketking()->get_data('user_id');
        $currentuser = new WP_User($user_id);
        
        ?>
        <div class="nk-content marketking_teams_page">
            <div class="container-fluid">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block-head nk-block-head-sm">
                            <div class="nk-block-between">
                                <div class="nk-block-head-content">
                                    <h3 class="nk-block-title page-title"><?php esc_html_e('My Team','marketking');?></h3>
                                    <div class="nk-block-des text-soft">
                                        <p><?php esc_html_e('Here you can create and configure staff or team member accounts with various roles in managing the store.', 'marketking');?></p>
                                    </div>
                                </div><!-- .nk-block-head-content -->
                                <div class="nk-block-head-content">
                                    <div class="toggle-wrap nk-block-tools-toggle">
                                        <a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1" data-target="more-options"><em class="icon ni ni-more-v"></em></a>
                                        <div class="toggle-expand-content" data-content="more-options">
                                            <ul class="nk-block-tools g-3">
                                                <li>
                                                    <div class="form-control-wrap">
                                                        <div class="form-icon form-icon-right">
                                                            <em class="icon ni ni-search"></em>
                                                        </div>
                                                        <input type="text" class="form-control" id="marketking_teams_search" placeholder="<?php esc_html_e('Search members...','marketking');?>">
                                                    </div>
                                                </li>
                                                <li class="nk-block-tools-opt">
                                                    <a href="#" class="btn btn-icon btn-primary d-md-none" data-toggle="modal" data-target="#modal_add_member"><em class="icon ni ni-plus"></em></a>
                                                    <button class="btn btn-primary d-none d-md-inline-flex" data-toggle="modal" data-target="#modal_add_member"><em class="icon ni ni-plus"></em><span><?php esc_html_e('Add','marketking');?></span></button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div><!-- .nk-block-head-content -->
                            </div><!-- .nk-block-between -->
                        </div><!-- .nk-block-head -->
                        <?php
                        if (isset($_GET['add'])){
                            $add = sanitize_text_field($_GET['add']);;
                            if ($add === 'success'){
                                ?>                                    
                                <div class="alert alert-primary alert-icon"><em class="icon ni ni-check-circle"></em> <strong><?php esc_html_e('Your new team member was added successfully','marketking');?></strong>.</div>
                                <?php
                            }
                        }
                        ?>
                        <table id="marketking_dashboard_teams_table" class="nk-tb-list is-separate mb-3">
                            <thead>
                                <tr class="nk-tb-item nk-tb-head">
                                    <th class="nk-tb-col"><span class="sub-text"><?php esc_html_e('Team Member','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-mb"><span class="sub-text"><?php esc_html_e('Description','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-mb"><span class="sub-text"><?php esc_html_e('Email','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-mb"><span class="sub-text"><?php esc_html_e('Phone','marketking'); ?></span></th>
                                    <th class="nk-tb-col"><span class="sub-text"><?php esc_html_e('Actions','marketking'); ?></span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                // get all teammembers of the user (all users with this user as parent)
                                $teammembers = get_users(array(
                                'fields' => 'ids',
                                'meta_query'=> array(
                                      'relation' => 'AND',
                                      array(
                                        'meta_key'     => 'marketking_group',
                                        'meta_value'   => 'none',
                                        'meta_compare' => '!=',
                                       ),
                                      array(
                                          'key' => 'marketking_parent_vendor',
                                          'value' => $user_id,
                                          'compare' => '=',
                                      ),
                                  )));


                                foreach ($teammembers as $member_id){
                                    $user_info = get_userdata($member_id);

                                    if (empty($user_info->first_name) && empty($user_info->last_name)){
                                        $name = $user_info->user_login;
                                    } else {
                                        $name = $user_info->first_name.' '.$user_info->last_name;
                                    }

                                    $description = get_user_meta($member_id,'marketking_member_description', true);
                                    ?>

                                    <tr class="nk-tb-item">
                                            <td class="nk-tb-col">

                                                <div>
                                                    <div class="user-card">
                                                        <div class="user-avatar bg-primary">
                                                            <span><?php echo esc_html(substr($name, 0, 2));?></span>
                                                        </div>
                                                        <div class="user-info">
                                                            <span class="tb-lead"><?php echo esc_html($name);?> <span class="dot dot-success d-md-none ml-1"></span></span>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>

                                            <td class="nk-tb-col tb-col-mb">
                                                <div>
                                                    <span class="tb-amount"><?php echo wp_kses_post($description);?></span>
                                                </div>
                                            </td>
                                            <td class="nk-tb-col tb-col-mb">
                                                <div>
                                                    <span><?php echo esc_html($user_info->user_email);?></span>
                                                </div>
                                            </td>
                                            <td class="nk-tb-col tb-col-mb">
                                                <div>
                                                    <span><?php echo esc_html(get_user_meta($member_id,'billing_phone', true));?></span>
                                                </div>
                                            </td>

                                            <td class="nk-tb-col">
                                                <div>
                                                    <span> <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'edit-team/'.$member_id);?>"><button class="btn btn-sm btn-primary marketking_edit_team" value="<?php echo esc_attr($member_id);?>"><em class="icon ni ni-account-setting-fill"></em><span><?php esc_html_e('Configure','marketking');?></span></button></a></span>
                                                    <span> <button class="btn btn-sm btn-gray marketking_delete_team" value="<?php echo esc_attr($member_id);?>"><span><?php esc_html_e('Delete','marketking');?></span></button></span>
                                                </div>
                                            </td>
                                         

                                           
                                    </tr>
                                    <?php
                                }
                                ?>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal fade" tabindex="-1" id="modal_add_member">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php esc_html_e('New Member','marketking'); ?></h5>
                            <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                <em class="icon ni ni-cross"></em>
                            </a>
                        </div>
                        <div class="modal-body">
                            <form action="#" class="form-validate is-alter" id="marketking_add_member_form">
                                <div class="form-group">
                                    <label class="form-label" for="first-name"><?php esc_html_e('First name (*)','marketking'); ?></label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" id="first-name" name="first-name" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="last-name"><?php esc_html_e('Last name (*)','marketking'); ?></label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" id="last-name" name="last-name" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="last-name"><?php esc_html_e('Description','marketking'); ?></label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" id="description" name="last-name">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="phone-no"><?php esc_html_e('Phone No','marketking'); ?></label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" id="phone-no" name="phone-no">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="username"><?php esc_html_e('Username (*)','marketking'); ?></label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="email-address"><?php esc_html_e('Email address (*)','marketking'); ?></label>
                                    <div class="form-control-wrap">
                                        <input type="email" class="form-control" id="email-address" name="email-address" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="password"><?php esc_html_e('Password (*)','marketking'); ?></label>
                                    <div class="form-control-wrap">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="marketking_add_member" class="btn btn-lg btn-primary"><?php esc_html_e('Add member','marketking'); ?></button>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>