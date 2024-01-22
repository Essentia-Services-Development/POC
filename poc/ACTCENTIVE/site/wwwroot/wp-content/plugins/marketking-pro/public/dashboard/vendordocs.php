<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
// get data
$user_id = marketking()->get_data('user_id');
$currentuser = new WP_User($user_id);

$agent_group = get_user_meta($user_id, 'marketking_group', true);
$user = get_user_by('id', $user_id) -> user_login;

$docs = get_posts(array( 'post_type' => 'marketking_docs',
          'post_status'=>'publish',
          'numberposts' => -1,
          'meta_query'=> array(
                'relation' => 'OR',
                array(
                    'key' => 'marketking_group_'.$agent_group,
                    'value' => '1',
                ),
                array(
                    'key' => 'marketking_user_'.$user, 
                    'value' => '1',
                ),
            )));

if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_vendordocs_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('vendordocs')){
            ?>
            <div class="nk-content marketking_vendordocs_page">
                <div class="container wide-xl">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-content-wrap">
                                <div class="nk-block-head">
                                    <div class="nk-block-between g-3">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title"><?php esc_html_e('Seller Documentation', 'marketking');?></h3>
                                        </div><!-- .nk-block-head-content -->
                                    </div><!-- .nk-block-between -->
                                </div><!-- .nk-block-head -->
                                <div class="nk-block">
                                    <div class="card ">
                                        <table class="table table-tickets">
                                            <thead class="tb-ticket-head">
                                                <tr class="tb-ticket-title">
                                                    <th class="tb-ticket-desc">
                                                        <span><?php esc_html_e('Article', 'marketking');?></span>
                                                    </th>
                                                    <th class="tb-ticket-seen tb-col-md">
                                                        <span><?php esc_html_e('Date published', 'marketking');?></span>
                                                    </th>
                                                   
                                                    <th class="tb-ticket-action"> &nbsp; </th>
                                                </tr><!-- .tb-ticket-title -->
                                            </thead>
                                            <tbody class="tb-ticket-body">
                                                <?php

                                                foreach ($docs as $docs){

                                                    $read_class = '';
                                                    $badge_class = 'badge-light';
                                                    $read_word = esc_html__('Read', 'marketking');

                                                    ?>
                                                    <tr class="tb-ticket-item <?php echo esc_attr($read_class);?>">
                                                        <td class="tb-ticket-desc">
                                                            <a href="<?php echo trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'docssingle?id='.esc_attr($docs->ID);?>"><span class="title"><?php echo esc_html($docs->post_title);?></span></a>
                                                        </td>
                                                        <?php
                                                        // get docs author
                                                        $author_id = get_post_field( 'post_author', $docs->ID );
                                                        $author_name = get_the_author_meta( 'display_name', $author_id );

                                                        ?>
                                                        <td class="tb-ticket-seen tb-col-md">
                                                            <span class="date-last"><em class="icon-avatar icon ni ni-user-alt-fill nk-tooltip" title="<?php echo esc_attr($author_name); ?>"></em> <?php echo esc_html(get_the_date(get_option( 'date_format' ), $docs));?>
                                                        </td>
                                                        
                                                        <td class="tb-ticket-action">
                                                            <a href="<?php echo trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'docssingle?id='.esc_attr($docs->ID);?>" class="btn btn-icon btn-trigger">
                                                                <em class="icon ni ni-chevron-right"></em>
                                                            </a>
                                                        </td>
                                                    </tr><!-- .tb-ticket-item -->
                                                    <?php
                                                }
                                                ?>
                                                
                                            </tbody>
                                        </table>
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