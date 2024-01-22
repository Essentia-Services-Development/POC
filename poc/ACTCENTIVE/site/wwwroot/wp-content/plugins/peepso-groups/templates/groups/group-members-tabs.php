<?php
$PeepSoGroupUsers = new PeepSoGroupUsers($group->id);
$PeepSoGroupUsers->update_members_count('banned');
$PeepSoGroupUsers->update_members_count('pending_user');
$PeepSoGroupUsers->update_members_count('pending_admin');
?>

<div class="ps-tabs ps-members__tabs ps-group__members-tabs ps-tabs--arrows">
  <div class="ps-tabs__item ps-members__tab <?php if (!$tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $group->get_url() . 'members/'; ?>">
      <span><?php echo __('All Members', 'groupso'); ?></span>
    </a>
  </div>

  <div class="ps-tabs__item ps-members__tab <?php if ('management'==$tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $group->get_url() . 'members/management'; ?>">
      <span><?php echo __('Management', 'groupso'); ?></span>
    </a>
  </div>

  <?php if($PeepSoGroupUser->can('manage_users')) { ?>
  <div class="ps-tabs__item ps-members__tab <?php if ('invited' == $tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $group->get_url() . 'members/invited'; ?>">
      <?php echo sprintf(__('<span>Invited</span><span class="ps-tabs__count ps-js-invited-count" data-id="%d">%s</span>', 'groupso'), $group->id, $group->pending_user_members_count); ?>
    </a>
  </div>

  <div class="ps-tabs__item ps-members__tab <?php if ('pending' == $tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $group->get_url() . 'members/pending'; ?>">
      <?php echo sprintf(__('<span>Pending</span><span class="ps-tabs__count ps-js-pending-count" data-id="%d">%s</span>', 'groupso'), $group->id, $group->pending_admin_members_count); ?>
    </a>
  </div>

  <div class="ps-tabs__item ps-members__tab <?php if ('banned' == $tab) echo "ps-tabs__item--active"; ?>">
    <a href="<?php echo $group->get_url() . 'members/banned'; ?>">
      <?php echo sprintf(__('<span>Banned</span><span class="ps-tabs__count">%s</span>', 'groupso'), $group->banned_members_count); ?>
    </a>
  </div>
  <?php } ?>
</div>
