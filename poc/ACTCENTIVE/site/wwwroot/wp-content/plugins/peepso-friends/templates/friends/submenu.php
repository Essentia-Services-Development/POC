<?php
$PeepSoFriends = PeepSoFriends::get_instance();
$friends_requests = PeepSoFriendsRequests::get_instance();
?>
<div class="ps-friends__tabs">
  <div class="ps-friends__tabs-inner">
    <div class="ps-friends__tab <?php if('friends' === $current) echo "ps-friends__tab--active";?>">
      <a href="<?php echo PeepSoFriendsPlugin::get_url(get_current_user_id(), 'friends'); ?>">
        <span><?php echo __('Friends', 'friendso'); ?></span>
        <?php if($PeepSoFriends->get_num_friends(get_current_user_id()) > 0) : ?>
          <span class="ps-tabs__count"><?php echo $PeepSoFriends->get_num_friends(get_current_user_id()); ?></span>
        <?php endif; ?>
      </a>
    </div>
    <div class="ps-friends__tab <?php if('requests' === $current) echo "ps-friends__tab--active";?>">
      <a href="<?php echo PeepSoFriendsPlugin::get_url(get_current_user_id(), 'requests'); ?>">
        <span><?php echo __('Friend requests', 'friendso'); ?></span>
        <?php if (count($friends_requests->get_received_requests()) > 0): ?>
          <span class="ps-tabs__count"><?php echo count($friends_requests->get_received_requests()); ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>
</div>
