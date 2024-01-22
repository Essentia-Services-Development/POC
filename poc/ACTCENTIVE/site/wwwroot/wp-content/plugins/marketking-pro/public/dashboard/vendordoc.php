<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_vendordocs_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('vendordocs')){
            ?>
            <div class="nk-content marketking_docs_page">
                <div class="container wide-xl">
                    <div class="nk-content-inner">
                        <div class="nk-aside" data-content="sideNav" data-toggle-overlay="true" data-toggle-screen="lg" data-toggle-body="true">
                        </div><!-- .nk-aside -->
                        <div class="nk-content-body">
                            <div class="nk-content-wrap">
                                <?php
                                // get docs data
                                $id = sanitize_text_field(get_query_var('id'));
                                $title = get_post_field( 'post_title', $id );
                                $docs = get_post($id);
                                $author_id = get_post_field( 'post_author', $id );
                                $author_name = get_the_author_meta( 'display_name', $author_id );
                                ?>
                                <div class="nk-block-head">
                                    <div class="nk-block-between g-3">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title"><?php echo esc_html($title);?> </h3>
                                        </div>
                                        <div class="nk-block-head-content">
                                            <a class="back-to" href="<?php echo esc_url(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'docs'); ?>"><em class="icon ni ni-arrow-left"></em><span><?php esc_html_e('Back', 'marketking');?></span></a>
                                        </div>
                                    </div>

                                </div><!-- .nk-block-head -->
                                <div class="nk-block-between g-3">
                                    <div class="ticket-info">
                                        <ul class="ticket-meta">
                                            <li class="ticket-date"><span><?php esc_html_e('Released:', 'marketking');?></span> <strong><?php echo esc_html(get_the_date(get_option( 'date_format' ), $docs));?></strong></li>
                                        </ul>
                                    </div>
                                    
                                </div><!-- .nk-block -->
                                <br />
                                <div class="nk-block nk-block-lg">
                                    <div class="card card-bordered">
                                        <div class="card-inner">
                                            <div class="ticket-msgs">
                                                <div class="ticket-msg-item">
                                                    <div class="ticket-msg-from">
                                                        <div class="ticket-msg-user user-card">
                                                            <div class="user-avatar bg-primary">
                                                                <span></span>
                                                            </div>
                                                            <div class="user-info">
                                                                <span class="lead-text"><?php echo esc_attr($author_name); ?></span>
                                                            </div>
                                                        </div><br />
                                                    </div>
                                                    <div class="ticket-msg-comment">
                                                        <?php echo apply_filters('the_content',get_post_field('post_content', $id)); ?>
                                                    </div>
                                                </div><!-- .ticket-msg-item -->
                                                
                                                
                                            </div><!-- .ticket-msgs -->
                                        </div>
                                    </div>
                                </div><!-- .nk-block -->
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}