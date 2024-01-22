<?php
$PeepSoFriendsRequests = PeepSoFriendsRequests::get_instance();
?>

<div class="peepso">
  <div class="ps-page ps-page--friends ps-page--friends-pending">
    <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

    <div class="ps-friends">
      <?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'friends')); ?>

      <?php
      PeepSoTemplate::exec_template('friends', 'submenu', array('current'=>'requests'));
      ?>

      <?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

      <?php
          if ($PeepSoFriendsRequests->has_received_requests(get_current_user_id())) {
              echo '<div class="ps-friends__list-title">' . __('Received requests', 'friendso') . '</div>';
              echo '<div class="ps-members ps-friends__list ps-js-friend-requests ps-js-friend-requests-received">';
              while ($request = $PeepSoFriendsRequests->get_next_request()) {
                  ?>
                  <div class="ps-member ps-member--pending ps-js-member" data-user-id="<?php echo $request['freq_user_id'] ?>">
                    <div class="ps-member__inner">
                      <?php $PeepSoFriendsRequests->show_request_thumb($request);?>
                    </div>
                  </div>
              <?php
              }
              echo '</div>';
          } else {
              echo '<div class="ps-alert">' . __('You currently have no friend requests', 'friendso') . '</div>';
          }
      ?>

      <?php
          if ($PeepSoFriendsRequests->has_sent_requests(get_current_user_id())) {
              echo '<div class="ps-friends__list-title">' . __('Sent requests', 'friendso') . '</div>';
              echo '<div class="ps-members ps-friends__list ps-js-friend-requests ps-js-friend-requests-sent">';
              while ($request = $PeepSoFriendsRequests->get_next_request()) {
                  ?>
                  <div class="ps-member ps-member--pending ps-js-member" data-user-id="<?php echo $request['freq_friend_id'] ?>">
                      <div class="ps-member__inner">
                          <?php $PeepSoFriendsRequests->show_request_thumb($request);?>
                      </div>
                  </div>
              <?php
              }
              echo '</div>';
          }
      ?>
    </div>
  </div>
</div>
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
