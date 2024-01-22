<?php
$PeepSoProfile=PeepSoProfile::get_instance();
$small_thumbnail = PeepSo::get_option('small_url_preview_thumbnail', 0);
$data =[];

// #5989 let admin decide "about" to be first
if(isset($current)) {
    $data = ['current'=>$current];
}
?>
<div class="peepso">
  <div class="ps-page ps-page--profile">
    <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

    <div id="ps-profile" class="ps-profile">
      <?php PeepSoTemplate::exec_template('profile', 'focus', $data); ?>

      <div class="ps-profile__layout">
        <?php
        $sidebar = '';
        ?>

        <div class="ps-profile__middle <?php if (strlen($sidebar)) echo ''; else echo 'ps-profile__middle--full'; ?>">
          <div class="ps-activity">
            <?php
            PeepSoTemplate::exec_template('general', 'postbox-legacy', array('is_current_user' => $PeepSoProfile->is_current_user()));
            ?>

            <?php PeepSoTemplate::exec_template('activity', 'activity-stream-filters-simple', array()); ?>

            <div class="ps-activity__container">
              <div id="ps-activitystream-recent" class="ps-posts <?php echo $small_thumbnail ? '' : 'ps-posts--narrow' ?>" style="display:none"></div>
              <div id="ps-activitystream" class="ps-posts <?php echo $small_thumbnail ? '' : 'ps-posts--narrow' ?>" style="display:none"></div>

              <div id="ps-activitystream-loading" class="ps-posts__loading">
                <?php PeepSoTemplate::exec_template('activity', 'activity-placeholder'); ?>
              </div>

              <div id="ps-no-posts" class="ps-posts__empty"><?php echo __('No posts found.', 'peepso-core'); ?></div>
              <div id="ps-no-posts-match" class="ps-posts__empty"><?php echo __('No posts found.', 'peepso-core'); ?></div>
              <div id="ps-no-more-posts" class="ps-posts__empty"><?php echo __('Nothing more to show.', 'peepso-core'); ?></div>

              <?php PeepSoTemplate::exec_template('activity','dialogs'); ?>
            </div>
          </div>
        </div>
      </div>
    </div><!-- end cProfileWrapper --><!-- js_bottom -->
    <div id="ps-dialogs" style="display:none">
      <?php do_action('peepso_profile_dialogs'); // give add-ons a chance to output some HTML ?>
    </div>
  </div>
</div><!--end row-->
