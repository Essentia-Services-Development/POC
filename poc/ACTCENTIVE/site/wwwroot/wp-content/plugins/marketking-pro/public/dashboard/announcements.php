<?php

/*
* @version 1.0.1

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
// get data
$unread_ann = marketking()->get_data('unread_ann');
$announcements = marketking()->get_data('announcements');
$user_id = marketking()->get_data('user_id');
$currentuser = new WP_User($user_id);
if (intval(get_option( 'marketking_enable_announcements_setting', 1 )) === 1){

    if(marketking()->vendor_has_panel('announcements')){

        ?>
        <div class="nk-content marketking_announcements_page">
            <div class="container wide-xl">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-content-wrap">
                            <div class="nk-block-head">
                                <div class="nk-block-between g-3">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title"><?php esc_html_e('Announcements', 'marketking');?></h3>
                                        <div class="nk-block-des text-soft">
                                            <p><?php echo esc_html__('You have ','marketking').esc_html($unread_ann).esc_html__(' unread announcements.', 'marketking');?></p>
                                        </div>
                                    </div><!-- .nk-block-head-content -->
                                    <div class="ticket-msg-reply">
                                        <div class="form-action">
                                            <ul class="form-btn-group">
                                                <?php
                                                $ann_string = '';
                                                foreach ($announcements as $announcement){
                                                    $ann_string.=$announcement->ID.':';
                                                }
                                                $ann_string = substr($ann_string, 0, -1);

                                                ?>
                                                <li class="form-btn-secondary"><button type="button" id="marketking_mark_all_announcement_read" class="btn btn-dim btn-outline-light" value="<?php echo esc_attr($ann_string);?>"><?php esc_html_e('Mark all as read', 'marketking');?></button></li>
                                            </ul>
                                        </div>
                                    </div><!-- .ticket-msg-reply -->
                                </div><!-- .nk-block-between -->
                            </div><!-- .nk-block-head -->
                            <div class="nk-block">
                                <div class="card ">
                                    <table class="table table-tickets">
                                        <thead class="tb-ticket-head">
                                            <tr class="tb-ticket-title">
                                                <th class="tb-ticket-desc">
                                                    <span><?php esc_html_e('Subject', 'marketking');?></span>
                                                </th>
                                                <th class="tb-ticket-seen tb-col-md">
                                                    <span><?php esc_html_e('Date published', 'marketking');?></span>
                                                </th>
                                                <th class="tb-ticket-status">
                                                    <span><?php esc_html_e('Status', 'marketking');?></span>
                                                </th>
                                                <th class="tb-ticket-action"> &nbsp; </th>
                                            </tr><!-- .tb-ticket-title -->
                                        </thead>
                                        <tbody class="tb-ticket-body">
                                            <?php
                                            $items_per_page = 10;
                                            $pagenr = sanitize_text_field(marketking()->get_pagenr_query_var());
                                            if (empty($pagenr)){
                                                $pagenr = 1;
                                            }

                                            $pagesnr = count($announcements)/$items_per_page;

                                            $new_announcements = array_slice($announcements, (($pagenr-1)*$items_per_page), $items_per_page);

                                            foreach ($new_announcements as $announcement){
                                                $read_status = get_user_meta($user_id,'marketking_announce_read_'.$announcement->ID, true);
                                                if (!$read_status || empty($read_status)){
                                                    $read_class = 'is_unread';
                                                    $badge_class = 'badge-success';
                                                    $read_word = esc_html__('Unread', 'marketking');
                                                } else {
                                                    $read_class = '';
                                                    $badge_class = 'badge-light';
                                                    $read_word = esc_html__('Read', 'marketking');
                                                }

                                                ?>
                                                <tr class="tb-ticket-item <?php echo esc_attr($read_class);?>">
                                                    <td class="tb-ticket-desc">
                                                        <a href="<?php echo trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'announcement?id='.esc_attr($announcement->ID);?>"><span class="title"><?php echo esc_html($announcement->post_title);?></span></a>
                                                    </td>
                                                    <?php
                                                    // get announcement author
                                                    $author_id = get_post_field( 'post_author', $announcement->ID );
                                                    $author_name = get_the_author_meta( 'display_name', $author_id );

                                                    ?>
                                                    <td class="tb-ticket-seen tb-col-md">
                                                        <span class="date-last"><em class="icon-avatar icon ni ni-user-alt-fill nk-tooltip" title="<?php echo esc_attr($author_name); ?>"></em> <?php echo esc_html(get_the_date(get_option( 'date_format' ), $announcement));?>
                                                    </td>
                                                    <td class="tb-ticket-status">
                                                        <span class="badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($read_word); ?></span>
                                                    </td>
                                                    <td class="tb-ticket-action">
                                                        <a href="<?php echo trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'announcement?id='.esc_attr($announcement->ID);?>" class="btn btn-icon btn-trigger">
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
                                <div class="card">
                                    <div class="card-inner card-inner-pagination">
                                        <div class="nk-block-between-md g-3">
                                            <div class="g">
                                                <ul class="pagination justify-content-center justify-content-md-end">
                                                    <?php
                                                    $i = 1;
                                                    while ($pagesnr > 0){
                                                        ?>
                                                        <li class="page-item"><a class="page-link" href="<?php echo trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'announcements/'.esc_attr($i);?>"><?php echo esc_html($i);?></a></li>
                                                        <?php
                                                        $i++;
                                                        $pagesnr--;
                                                    }
                                                    ?>

                                                </ul><!-- .pagination -->
                                            </div>
                                        </div><!-- .nk-block-between -->
                                    </div><!-- .card-inner -->
                                </div><!-- .card -->
                            </div><!-- .nk-block -->
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>