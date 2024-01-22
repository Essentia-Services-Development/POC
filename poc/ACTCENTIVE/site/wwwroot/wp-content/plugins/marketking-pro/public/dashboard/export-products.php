<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if(marketking()->vendor_has_panel('products')){
    ?>
    <div class="nk-content marketking_exportproducts_page">
        <div class="container-fluid">
            <div class="nk-content-inner">
                <div class="nk-content-body">
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block-between">
                            <div class="nk-block-head-content">
                                <h3 class="nk-block-title page-title"><?php esc_html_e('Export Products','marketking'); ?></h3>
                                <div class="nk-block-des text-soft fs-15px">
                                    <p><?php esc_html_e('Export your products to an easily accessible CSV file.', 'marketking');?></p>
                                </div>
                            </div><!-- .nk-block-head-content -->
                            <div class="nk-block-head-content">
                                <div class="toggle-wrap nk-block-tools-toggle">
                                    <a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                                    <div class="toggle-expand-content" data-content="pageMenu">
                                        <ul class="nk-block-tools g-3">
                                            
                                            <?php

                                            if(intval(get_option( 'marketking_vendors_can_newproducts_setting',1 )) === 1){
                                                if (apply_filters('marketking_vendors_can_add_products', true)){
                                                    ?>
                                                    <li class="nk-block-tools-opt">
                                                        <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'products');?>" class="btn btn-icon btn-primary d-md-none"><em class="icon ni ni-package-fill"></em></a>
                                                        <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'products');?>" class="btn btn-primary d-none d-md-inline-flex"><em class="icon ni ni-package-fill"></em><span><?php esc_html_e('View Products','marketking'); ?></span></a>
                                                    </li>
                                                    <?php
                                                }
                                            }

                                            ?>
                                        </ul>
                                        <?php
                                        if (defined('MARKETKINGPRO_DIR')){
                                            if (intval(get_option('marketking_enable_importexport_setting', 1)) === 1){
                                                ?>
                                                <div class="marketking_importexport_buttons_container_impexpage">
                                                    <?php
                                                    if(intval(get_option( 'marketking_vendors_can_newproducts_setting',1 )) === 1){
                                                        if (apply_filters('marketking_vendors_can_add_products', true)){
                                                            // import option = only if vendor can add new products
                                                            if (!marketking()->is_vendor_team_member()){
                                                                ?>
                                                                
                                                                    <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'import-products');?>" class="btn btn-sm btn-gray d-none d-md-inline-flex"><em class="icon ni ni-upload"></em><span><?php esc_html_e('Import Products','marketking'); ?></span></a>
                                                                
                                                                <?php
                                                            }
                                                        }
                                                    }

                                                ?>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                        
                                    </div>
                                </div>
                            </div><!-- .nk-block-head-content -->
                        </div><!-- .nk-block-between -->
                    </div><!-- .nk-block-head -->

                    <?php

                    if (!current_user_can('activate_plugins') && !current_user_can('manage_woocommerce')){
                        $exporters = new WC_Admin_Exporters;
                        $exporters->product_exporter();
                    } else {
                        ?><br>
                        <div class="example-alert"><div class="alert alert-danger alert-icon"><em class="icon ni ni-cross-circle"></em> <?php esc_html_e('You are logged in as a user with admin backend privileges. Because of this, you can only export products through the website backend, via Products -> Export','marketking'); ?></div></div>
                            
                        
                        <?php
                    }
                    
                    ?>

                </div>
            </div>
        </div>
    </div>
    <?php
}
?>