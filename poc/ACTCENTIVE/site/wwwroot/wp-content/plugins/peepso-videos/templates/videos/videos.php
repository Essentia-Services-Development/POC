<div class="peepso">
  <div class="ps-page ps-page--media">
    <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>
    <div class="ps-media__page">
      <?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>PeepSoVideos::profile_menu_slug())); ?>

      <?php if(get_current_user_id()) { ?>
      <div class="ps-media__page-header">
        <div class="ps-media__page-list-view">
          <div class="ps-btn__group">
            <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-media-viewmode" data-mode="small" aria-label="<?php echo __('Small thumbnails', 'vidso');?>"><i class="gcis gci-th"></i></a>
            <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-tip--inline ps-js-media-viewmode" data-mode="large" aria-label="<?php echo __('Large thumbnails', 'vidso');?>"><i class="gcis gci-th-large"></i></a>
          </div>
        </div>

        <select class="ps-input ps-input--sm ps-input--select ps-js-videos-sortby ps-js-videos-sortby--<?php echo apply_filters('peepso_user_profile_id', 0); ?>">
          <option value="desc"><?php echo __('Newest first', 'vidso');?></option>
          <option value="asc"><?php echo __('Oldest first', 'vidso');?></option>
        </select>
      </div>

      <div class="mb-20"></div>
      <div class="ps-media__page-list ps-js-videos ps-js-videos--<?php echo apply_filters('peepso_user_profile_id', 0); ?>"></div>
      <div class="ps-js-videos-triggerscroll ps-js-videos-triggerscroll--<?php echo  apply_filters('peepso_user_profile_id', 0); ?>">
          <img class="ps-loading post-ajax-loader ps-js-videos-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
      </div>
      <?php } else {
        PeepSoTemplate::exec_template('general','login-profile-tab');
      } ?>
    </div>
  </div>
</div>
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
