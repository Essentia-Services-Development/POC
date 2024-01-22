<?php

$user = PeepSoUser::get_instance(PeepSoProfileShortcode::get_instance()->get_view_user_id());

$can_edit = FALSE;
if ($user->get_id() == get_current_user_id() || current_user_can('edit_users')) {
    $can_edit = TRUE;
}

if (!$can_edit) {
  PeepSo::redirect(PeepSo::get_page('activity'));
} else {
?>

<div class="peepso">
  <div class="ps-page ps-page--mycred ps-page--mycred-history">
    <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>
    <?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'points')); ?>

    <div class="ps-mycred ps-mycred--history">
      <div class="ps-mycred__history-title">
        <?php echo sprintf(__('%s History', 'peepsocreds'), mycred_get_point_type_name(MYCRED_DEFAULT_TYPE_KEY, FALSE)); ?>
      </div>

      <div class="ps-mycred__history">
        <?php echo do_shortcode('[mycred_history user_id='.$view_user_id.']');?>
      </div>
    </div>
  </div>
</div>

<div id="ps-dialogs" style="display:none">
	<?php
	$PeepSoActivity = PeepSoActivity::get_instance();
	$PeepSoActivity->dialogs(); // give add-ons a chance to output some HTML
	?>
	<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
</div>
<?php }