<?php
$PeepSoActivityShortcode = PeepSoActivityShortcode::get_instance();
global $post;


$user_stream_filters = PeepSoUser::get_stream_filters();
$stream_id_list = apply_filters('peepso_stream_id_list', array());
$small_thumbnail = PeepSo::get_option('small_url_preview_thumbnail', 0);
?>

<div class="peepso">
    <div class="ps-page ps-page--activity">
        <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

        <?php if (FALSE === $PeepSoActivityShortcode->is_permalink_page()) { PeepSoTemplate::exec_template('general', 'register-panel'); } ?>

        <?php /*override header*/ do_action('peepso_activity_single_override_header'); ?>

        <?php if (! get_current_user_id() && $PeepSoActivityShortcode->is_permalink_page()) { PeepSoTemplate::exec_template('general','login-profile-tab'); } ?>

        <!-- PeepSo Activity -->
        <div class="ps-activity">
            <!-- PeepSo Postbox -->
            <?php PeepSoTemplate::exec_template('general', 'postbox-legacy'); ?>
            <!-- end: PeepSo Postbox -->

            <!-- PeepSo Activity Filters -->
            <?php if(get_current_user_id() && FALSE === $PeepSoActivityShortcode->is_permalink_page()) { ?>

                <input type="hidden" id="peepso_context" value="stream" />

                <?php if(NULL != $user_stream_filters ) { ?>

                    <?php PeepSoTemplate::exec_template('activity', 'activity-stream-filters', array('user_stream_filters'=>$user_stream_filters,'stream_id_list'=>$stream_id_list )); ?>

                <?php } ?>

            <?php } elseif($post->post_type == 'peepso-post') { ?>

                <input type="hidden" id="peepso_post_id" value="<?php global $post; echo $post->ID; ?>" />
                <input type="hidden" id="peepso_context" value="single" />

            <?php } ?>
            <!-- end: PeepSo Activity Filters -->

            <?php echo PeepSoTemplate::exec_template('activity','hashtags', NULL,TRUE); ?>

            <!-- PeepSo Activity Posts -->
            <div class="ps-activity__container">
                <div id="ps-activitystream-recent" class="ps-posts <?php echo $small_thumbnail ? '' : 'ps-posts--narrow' ?>" style="display:none"></div>
                <div id="ps-activitystream" class="ps-posts <?php echo $small_thumbnail ? '' : 'ps-posts--narrow' ?>" style="display:none"></div>
                <?php

                // Add noscript content for single post view.
                $as = PeepSoActivityShortcode::get_instance();
                if ($as->is_permalink_page())
                {
                    $url = PeepSoUrlSegments::get_instance();
                    $post_slug = $url->get(2);
                    if (!empty($post_slug))
                    {
                        $peepso_activity = new PeepSoActivity();
                        $activity = $peepso_activity->get_activity_by_permalink(sanitize_key($post_slug));
                        if (is_object($activity))
                        {
                            $activity = apply_filters('peepso_filter_check_opengraph', $activity);
                        }
                        if (is_object($activity) && $activity->act_access == PeepSo::ACCESS_PUBLIC)
                        {
                            $user = PeepSoUser::get_instance($activity->post_author);
                            $description = strlen($human_friendly = get_post_meta($activity->ID, 'peepso_human_friendly', TRUE)) ? $human_friendly : strip_tags(apply_filters('peepso_remove_shortcodes', $activity->post_content, $activity->ID));
                            $description = (!empty($description)) ? $description : PeepSo::get_option('opengraph_description');

                            ?>
                            <!-- post content summary with semantic markup. -->
                            <noscript>
                                <article class="peepso_post">
                                    <h2><?php
                                        echo sprintf(
                                            __('Post by %s on %s', 'peepso-core'),
                                            '<a href="' . $user->get_profileurl() . '">' . trim(strip_tags($user->get_fullname())) . '</a>',
                                            '<time datetime="' . $activity->post_date_gmt . '">' .
                                            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($activity->post_date_gmt), true) .
                                            '</time>'
                                        );
                                        ?></h2>
                                    <p><?php echo nl2br($description); ?></p>
                                </article>
                            </noscript>
                            <?php

                        }
                    }
                }

                ?>
                <div id="ps-activitystream-loading" class="ps-posts__loading">
                    <?php PeepSoTemplate::exec_template('activity', 'activity-placeholder'); ?>
                </div>

                <div id="ps-no-posts" class="ps-posts__empty"><?php echo __('No posts found.', 'peepso-core'); ?></div>
                <div id="ps-no-posts-match" class="ps-posts__empty"><?php echo __('No posts found.', 'peepso-core'); ?></div>
                <div id="ps-no-more-posts" class="ps-posts__empty"><?php echo __('Nothing more to show.', 'peepso-core'); ?></div>

                <?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
            </div>
            <!-- end: PeepSo Activity Posts -->
        </div>
        <!-- end: PeepSo Activity -->
    </div>
</div>
