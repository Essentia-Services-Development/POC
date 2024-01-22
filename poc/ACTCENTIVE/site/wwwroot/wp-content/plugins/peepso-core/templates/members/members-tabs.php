<?php

if(get_current_user_id()) {

	$blocked_member_url = PeepSo::get_page('members');
	if(0 == PeepSo::get_option('disable_questionmark_urls', 0)) {
	    $blocked_member_url .= '?';
	}
	$blocked_member_url .= 'blocked/';
	if (PeepSo::get_option('user_blocking_enable', 0) === 1) {
?>

<div class="ps-tabs ps-members__tabs ps-tabs--arrows">
  <div class="ps-tabs__item ps-members__tab <?php if (!isset($tab)) echo "ps-tabs__item--active"; ?>">
		<a href="<?php echo PeepSo::get_page('members'); ?>"><?php echo __('Members', 'peepso-core'); ?></a>
  </div>
  <div class="ps-tabs__item ps-members__tab <?php if (isset($tab) && 'blocked' == $tab) echo "ps-tabs__item--active"; ?>">
		<a href="<?php echo $blocked_member_url; ?>"><?php echo __('Blocked', 'peepso-core'); ?></a>
  </div>
</div>

<?php }
}
