<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_vendorinvoices_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('vendorinvoices')){
            ?>
            <div class="nk-content marketking_vendorinvoices_page">
            <div class="container-fluid">
                <?php
                if (isset($_GET['update'])){
                    $add = sanitize_text_field($_GET['update']);;
                    if ($add === 'success'){
                        ?>                                    
                        <div class="alert alert-primary alert-icon"><em class="icon ni ni-check-circle"></em> <strong><?php esc_html_e('Your settings have been saved successfully','marketking');?></strong>.</div>
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
                                                    <h4 class="nk-block-title"><em class="icon ni ni-file-check-fill"></em>&nbsp;&nbsp;<?php esc_html_e('Invoice Settings','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        
                                        $marketking_invoicestore = get_user_meta($user_id, 'marketking_invoicestore', true);
                                        $marketking_invoiceaddress = get_user_meta($user_id, 'marketking_invoiceaddress', true);
                                        $marketking_invoicecustom = get_user_meta($user_id, 'marketking_invoicecustom', true);
                                        $marketking_invoicelogo = get_user_meta($user_id, 'marketking_invoicelogo', true);
                                        
                                        ?>


                                        <div class="form-group">
                                            <label class="form-label" for="invoicestorename"><?php esc_html_e('Invoice Store Name','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-briefcase "></em>
                                                </div>
                                                <input type="text" class="form-control" id="invoicestorename" placeholder="<?php esc_attr_e('Enter the store name to be shown on your invoices here...','marketking');?>" value="<?php echo esc_attr($marketking_invoicestore);?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="invoicestoreaddress"><?php esc_html_e('Invoice Store Address','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-map-pin"></em>
                                                </div>
                                                <input type="text" class="form-control" id="invoicestoreaddress" placeholder="<?php esc_attr_e('Enter the store address to be shown on your invoices here...','marketking');?>" value="<?php echo esc_attr($marketking_invoiceaddress);?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="invoicestorelogo"><?php esc_html_e('Invoice Store Logo','marketking');?></label>
                                            <div class="form-control-wrap">
                                                <div class="form-icon form-icon-left">
                                                    <em class="icon ni ni-layout-alt-fill"></em>
                                                </div>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="invoicestorelogo" placeholder="<?php esc_attr_e('Select & enter the logo URL here...','marketking');?>" value="<?php echo esc_attr($marketking_invoicelogo);?>">
                                                    <div class="input-group-append"><button class="btn btn-outline-primary btn-dim" id="marketking_invoice_logo_choose" type="button"><?php esc_html_e('Select Image','marketking');?></button></div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php

                                        if (marketking()->vendor_can_taxable($user_id)){

                                            ?>
                                            <div class="form-group"><label class="form-label" for="invoicecustominfo"><?php esc_html_e('Invoice Custom Info (optional)','marketking');?></label><div class="form-control-wrap"><textarea class="form-control form-control-sm valid" id="invoicecustominfo" name="invoicecustominfo" placeholder="<?php esc_attr_e('Enter your custom info here...','marketking');?>" required="" aria-invalid="false"><?php echo esc_html($marketking_invoicecustom);?></textarea></div></div>
                                            <?php
                                        }
                                        ?>

                                        <button class="btn btn-primary" type="button" id="marketking_save_invoice_settings" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Settings','marketking');?></button>

                                        <br><br><br>
                                        <div class="marketking_store_seo_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-help"></em><?php esc_html_e('This information will be displayed on your store invoices.','marketking');?></div></div>

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