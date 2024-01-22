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
      <div class="ps-page ps-page--profile ps-page--profile-preferences ps-js-page-about-preferences">
        <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

        <div class="ps-profile ps-profile--edit ps-profile--preferences">
          <?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'about')); ?>

          <div class="ps-profile__edit">
            <?php if($can_edit) { PeepSoTemplate::exec_template('profile', 'profile-about-tabs', array('tabs' => $tabs, 'current_tab'=>'preferences')); } ?>

            <div class="ps-js-profile-list">
              <?php $PeepSoProfile->preferences_form_fields(TRUE, FALSE); ?>
            </div>
          </div>
        </div>
      </div>

      <div id="ps-dialogs" style="display:none">
        <?php PeepSoActivity::get_instance()->dialogs(); // give add-ons a chance to output some HTML ?>
        <?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
      </div>
    </div>
<?php }
