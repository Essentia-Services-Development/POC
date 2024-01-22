<?php
$PeepSoUser = PeepSoUser::get_instance($view_user_id);
?>
<div class="ps-followers__tabs">
  <div class="ps-followers__tabs-inner">
    <div class="ps-followers__tab <?php if('followers' === $current) echo "ps-followers__tab--active";?>">
      <a href="<?php echo $PeepSoUser->get_profileurl() . 'followers/'; ?>">
        <span><?php echo __('Followers', 'peepso-core'); ?></span>
      </a>
    </div>
    <div class="ps-followers__tab <?php if('following' === $current) echo "ps-followers__tab--active";?>">
      <a href="<?php echo $PeepSoUser->get_profileurl() . 'followers/following'; ?>">
        <span><?php echo __('Following', 'peepso-core'); ?></span>
      </a>
    </div>
  </div>
</div>
