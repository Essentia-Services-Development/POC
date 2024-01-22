<?php

$PeepSoVipIconsModel = new PeepSoVipIconsModel();
$empty = true;

?><div class="ps-members__filter ps-members__filter--vip">
  <div class="ps-members__filter-label"><?php echo __('VIP Icon', 'peepso-vip'); ?></div>
  <div data-name="vip_icon" class="ps-dropdown ps-dropdown--menu ps-dropdown--full ps-js-dropdown">
    <div class="ps-input ps-input--sm ps-input--select ps-js-dropdown-toggle">
      <div data-value=""><?php echo __('Select VIP Icon...', 'peepso-vip'); ?></div>
    </div>
    <div class="ps-dropdown__menu ps-js-dropdown-menu">
    <?php foreach ($PeepSoVipIconsModel->vipicons as $key => $value) { ?>
      <?php if ($value->published == 1) { ?>
        <?php if ($empty) { $empty = false; ?>
          <a href="#" data-option-value="">
            <?php echo __('Select VIP Icon...', 'peepso-vip'); ?>
          </a>
        <?php } ?>
        <a href="#" data-option-value="<?php echo $key; ?>">
          <img class="ps-vip__icon" src="<?php echo $value->icon_url; ?>"><?php echo $value->title; ?>
        </a>
      <?php } ?>
    <?php } ?>
    </div>
  </div>
</div>
