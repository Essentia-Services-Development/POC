<?php
$user = PeepSoUser::get_instance(PeepSoProfileShortcode::get_instance()->get_view_user_id());

$can_edit = FALSE;
if($user->get_id() == get_current_user_id() || current_user_can('edit_users')) {
  $can_edit = TRUE;
}

if(!$can_edit) {
  PeepSo::redirect(PeepSo::get_page('activity'));
} else {

  $PeepSoProfile = PeepSoProfile::get_instance();
  ?>

  <div class="peepso">
    <div class="ps-page ps-page--profile ps-page--profile-account">
      <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

      <div class="ps-profile ps-profile--edit ps-profile--account">
        <?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'about')); ?>

        <div class="ps-profile__edit">
          <?php if($can_edit) { PeepSoTemplate::exec_template('profile', 'profile-about-tabs', array('tabs' => $tabs, 'current_tab'=>'account'));} ?>

          <div class="ps-profile__account ps-js-profile-list">
            <?php if (strlen($PeepSoProfile->edit_form_message())) { ?>
              <div class="ps-alert ps-alert--success">
                <?php echo $PeepSoProfile->edit_form_message(); ?>
              </div>
            <?php } ?>
            <!-- ACCOUNT DATA -->
            <div class="ps-profile__account-row ps-profile__account--basic">
              <div class="ps-profile__account-header">
                <?php echo __('Your Account', 'peepso-core'); ?>
              </div>

              <div class="ps-profile__account-form">
                <?php $PeepSoProfile->edit_form(); ?>
              </div>

              <div class="ps-alert ps-alert--neutral">
                <?php echo __('Fields marked with an asterisk (<span class="ps-form__required">*</span>) are required.', 'peepso-core'); ?>
              </div>
            </div>

            <?php if(PeepSo::get_option('site_registration_allowdelete', 0)) { ?>
            <!-- PROFILE DELETION -->
            <div class="ps-profile__account-row ps-profile__account-row--deletion">
              <div class="ps-profile__account-header"><?php echo __('Profile Deletion', 'peepso-core'); ?></div>

              <div class="ps-alert ps-alert--abort">
                <?php echo __('Deleting your account will disable your profile and remove your name and photo from most things you\'ve shared. Some information may still be visible to others, such as your name in their friends list and messages you sent.', 'peepso-core'); ?>
              </div>
              <div class="ps-profile__account-form">
                <?php $PeepSoProfile->delete_form(); ?>
              </div>
            </div>
            <?php } ?>

            <?php if(PeepSo::get_option('gdpr_enable', 1)) { ?>
            <!-- GDPR -->
            <div class="ps-profile__account-row ps-profile__account-row--gdpr">
              <div class="ps-profile__account-gdpr">
                <div class="ps-profile__account-header"><?php echo __('Export Your Community Data', 'peepso-core'); ?></div>
                <div class="ps-profile__account-form">
                  <?php $PeepSoProfile->request_data_form(); ?>
                </div>
              </div>
            </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="ps-dialogs" style="display:none">
      <?php PeepSoActivity::get_instance()->dialogs(); // give add-ons a chance to output some HTML ?>
      <?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
  </div>

<?php }
