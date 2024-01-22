<?php
$PeepSoActivity = PeepSoActivity::get_instance();
$PeepSoUser		= PeepSoUser::get_instance($post_author);
$PeepSoPrivacy	= PeepSoPrivacy::get_instance();

$comments_open = TRUE;
if (isset($ID)) {
  $post_id = $ID;

  // Follow the enable comments state from its parent post.
  if (is_numeric($post_parent)) {
    $parent_act_id = $post_parent;
		$parent_post = $PeepSoActivity->get_activity_post($parent_act_id);
    $post_id = $parent_post->ID;
  }

  if (strlen(get_post_meta($post_id, 'peepso_disable_comments', TRUE))) {
    $comments_open = FALSE;
  }
}

?>
<div class="ps-lightbox__side-wrapper">
  <div class="ps-post ps-post--lightbox ps-js-modal-attachment--<?php echo $act_id; ?>">
    <div class="ps-post__header">
      <!-- post author avatar -->
      <a class="ps-avatar ps-avatar--post" href="<?php echo $PeepSoUser->get_profileurl(); ?>">
        <img data-author="<?php echo $post_author; ?>" src="<?php echo PeepSoUser::get_instance($post_author)->get_avatar(); ?>" alt="<?php echo $PeepSoUser->get_fullname(); ?> avatar" />
      </a>

      <!-- post meta -->
      <div class="ps-post__meta ps-stream-meta">
        <div class="ps-post__title">
          <?php $PeepSoActivity->post_action_title(); ?>
          <span class="ps-post__subtitle"><?php
            $post_extras = apply_filters('peepso_post_extras', array());
            echo implode(' ', $post_extras);
          ?></span>
        </div>
        <div class="ps-post__info">
          <?php
          $PeepSoActivity->post_edit_notice();
          ?>

          <?php if (($post_author == get_current_user_id() || PeepSo::is_admin()) && apply_filters('peepso_activity_has_privacy', TRUE)) { ?>
          <div class="ps-post__privacy ps-dropdown ps-dropdown--privacy ps-js-dropdown ps-js-privacy--<?php echo $act_id; ?>" title="<?php echo __('Post privacy', 'peepso-core');?>">
            <a href="#" data-value="" class="ps-post__privacy-toggle ps-dropdown__toggle ps-js-dropdown-toggle">
              <div class="ps-post__privacy-label dropdown-value">
                <?php $PeepSoActivity->post_access(); ?>
              </div>
            </a>
            <?php wp_nonce_field('change_post_privacy_' . $act_id, '_privacy_wpnonce_' . $act_id); ?>
            <?php echo $PeepSoPrivacy->render_dropdown('activity.change_post_privacy(this, ' . $act_id . ')'); ?>
          </div>
          <?php } ?>
          <a class="ps-post__date" href="<?php $PeepSoActivity->post_link(); ?>" data-timestamp="<?php $PeepSoActivity->post_timestamp(); ?>"><?php $PeepSoActivity->post_age(); ?></a>
          <a class="ps-post__copy" href="<?php $PeepSoActivity->post_link(); ?>"><?php $PeepSoActivity->post_permalink(); ?></a>
        </div>

        <div class="ps-post__options">
          <?php $PeepSoActivity->post_options(); ?>
        </div>
      </div>
    </div>
    <div class="ps-post__body ps-stream-body">
      <?php if (isset($post_attachments)) { ?>
      <div class="ps-post__content ps-js-activity-content">
        <p><?php echo $post_attachments; ?></p>
      </div>
      <?php } ?>
      <div class="ps-post__attachments ps-js-activity-content ps-stream-attachment cstream-attachment">
        <?php echo $act_description; ?>
      </div>
    </div>
    <div class="ps-post__footer">
      <input type="hidden" name="module-id" value="<?php echo $act_module_id;?>" />
      <?php wp_nonce_field('activity-delete', '_delete_nonce'); ?>
      <?php $PeepSoActivity->post_actions(); ?>
    </div>
    <?php //do_action('peepso_modal_before_comments'); ?>

    <!-- <?php if($likes = $PeepSoActivity->has_likes($act_id)){ ?>
    <div class="ps-stream-status cstream-likes ps-js-act-like--<?php echo $act_id; ?>" id="act-like-<?php echo $act_id; ?>" data-count="<?php echo $likes ?>">
      <?php $PeepSoActivity->show_like_count($likes); ?>
    </div>
    <?php } else { ?>
    <div class="ps-stream-status cstream-likes ps-js-act-like--<?php echo $act_id; ?>" id="act-like-<?php echo $act_id; ?>" data-count="0" style="display:none">
    </div>
    <?php } ?> -->
  </div>

  <div class="ps-comments ps-comments--lightbox ps-js-comments" data-comments-open="<?php echo $comments_open ? 1 : 0 ?>">
    <?php //do_action('peepso_post_before_comments'); ?>
    <div class="ps-comments__inner cstream-respond wall-cocs" id="wall-cmt-<?php echo $act_id; ?>">
      <div class="ps-comments__list ps-js-comment-container ps-js-comment-container--<?php echo $act_id; ?>"
        data-act-id="<?php echo $act_id ?>"
        data-post-id="<?php echo $post_id ?>"
        data-is-post="<?php echo $post_id == $ID ? 1 : 0 ?>"
        data-comments-open="<?php echo intval($comments_open) ?>"><?php $PeepSoActivity->show_recent_comments(); ?></div>

      <?php $show_commentsbox = apply_filters('peepso_commentsbox_display', apply_filters('peepso_permissions_comment_create', TRUE), $post_id); ?>

      <?php if(!$comments_open) { $show_commentsbox = FALSE; } ?>

      <?php if(isset($scheduled) && $scheduled) { $show_commentsbox = FALSE; } ?>

      <?php if(is_user_logged_in()) { ?>
      <div class="ps-comments__closed ps-js-comments-closed" <?php echo $comments_open ? 'style="display:none"' : '' ?>>
          <i class="fas fa-lock"></i> <?php echo __('Comments are closed', 'peepso-core');?>
      </div>
      <?php }  ?>

      <?php if (!is_user_logged_in()) { ?>
      <div class="ps-post__call-to-action">
        <i class="gcis gci-lock"></i>
        <span>
        <?php
          $disable_registration = intval(PeepSo::get_option('site_registration_disabled', 0));

          if (0 === $disable_registration) { ?>
            <?php echo sprintf( __('%sRegister%s or %sLogin%s to react or comment on this post.', 'peepso-core'),
                '<a href="' . PeepSo::get_page('register') . '">', '</a>',
                '<a href="javascript:" onClick="pswindow.show( peepsodata.login_dialog_title, peepsodata.login_dialog );">', '</a>');
                ?>
          <?php } else { ?>
            <?php echo sprintf( __('%sLogin%s to react or comment on this post.', 'peepso-core'),
                '<a href="javascript:" onClick="pswindow.show( peepsodata.login_dialog_title, peepsodata.login_dialog );">', '</a>');
                ?>
          <?php } ?>
        </span>
      </div>
      <?php } // is_user_loggged_in ?>
    </div>
  </div>
</div>

<div class="ps-lightbox__side-wrapper--reply">
  <?php if (is_user_logged_in() ) { ?>
  <div id="act-new-comment-<?php echo $act_id; ?>" class="ps-comments__reply ps-comments__reply--lightbox cstream-form stream-form wallform ps-js-comment-new ps-js-newcomment-<?php echo $act_id; ?>"
      data-id="<?php echo $act_id; ?>" data-type="stream-newcomment" data-formblock="true" <?php echo $show_commentsbox ? '' : 'style="display:none"'; ?>>
    <a class="ps-avatar cstream-avatar cstream-author" href="<?php echo PeepSouser::get_instance()->get_profileurl(); ?>">
      <img data-author="<?php echo $post_author; ?>" src="<?php echo PeepSoUser::get_instance()->get_avatar(); ?>" alt="" />
    </a>
    <div class="ps-comments__input-wrapper ps-textarea-wrapper cstream-form-input">
      <textarea
        data-act-id="<?php echo $act_id;?>"
        class="ps-comments__input ps-textarea cstream-form-text"
        name="comment"
        oninput="return activity.on_commentbox_change(this);"
        onfocus="activity.on_commentbox_focus(this);"
        onblur="activity.on_commentbox_blur(this);"
        placeholder="<?php echo __('Write a comment...', 'peepso-core');?>"></textarea>
      <?php
      // call function to add button addons for comments
      $PeepSoActivity->show_commentsbox_addons();
      ?>
    </div>
    <div class="ps-comments__reply-send cstream-form-submit" style="display:none;">
      <div class="ps-loading ps-comment-loading" style="display: none">
        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
        <div> </div>
      </div>
      <div class="ps-comments__reply-actions ps-comment-actions" style="display:none;">
        <button onclick="return activity.comment_cancel(<?php echo $act_id; ?>);" class="ps-btn ps-button-cancel"><?php echo __('Clear', 'peepso-core'); ?></button>
        <button onclick="return activity.comment_save(<?php echo $act_id; ?>, this);" class="ps-btn ps-btn--action ps-btn-primary ps-button-action" disabled><?php echo __('Post', 'peepso-core'); ?></button>
      </div>
    </div>
  </div>
  <?php } // is_user_loggged_in ?>
</div>
