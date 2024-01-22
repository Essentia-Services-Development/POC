<?php

$submissions = FALSE;

if(PeepSo::get_option('blogposts_submissions_enable', class_exists( 'CMUserSubmittedPosts' ))) { $submissions = TRUE; }
if(PeepSo::get_option('blogposts_submissions_enable_usp', defined('USP_VERSION')))                  { $submissions = TRUE; }


if($submissions) {

    $PeepSoUser = PeepSoUser::get_instance();
    $pro = PeepSoProfileShortcode::get_instance();

    if (PeepSoUrlSegments::get_view_id($pro->get_view_user_id()) == get_current_user_id()) {
        ?>
        <div class="ps-blogposts__tabs">
          <div class="ps-blogposts__tabs-inner">
            <div class="ps-blogposts__tab <?php if (!$create_tab) echo "ps-blogposts__tab--active"; ?>"><a
                 href="<?php echo $PeepSoUser->get_profileurl() . 'blogposts/'; ?>"><?php echo __('View', 'peepso-core'); ?></a>
            </div>
            <div class="ps-blogposts__tab <?php if ($create_tab) echo "ps-blogposts__tab--active"; ?>"><a
                 href="<?php echo $PeepSoUser->get_profileurl() . 'blogposts/create/'; ?>"><?php echo __('Create', 'peepso-core'); ?></a>
            </div>
          </div>
        </div>
        <?php
    }
}
