<?php
$show_result = FALSE;
global $post;

if(PeepSo::get_option('polls_show_result_before_vote', FALSE) ||  $is_voted || PeepSo::is_admin() || $post->post_author==get_current_user_id()) {
    $show_result = TRUE;
}

if($sort = intval(PeepSo::get_option_new('polls_sort_result_by_votes'))) {

  $keys = array_keys($options);
  $sort_result = $sort == 4 ? SORT_ASC : SORT_DESC;
  array_multisort(array_column($options, 'total_user_poll'), $sort_result, SORT_NUMERIC, $options, $keys);
  $options = array_combine($keys, $options);
}
?>

<div class="ps-poll ps-js-poll-item">
  <?php foreach ($options as $key => $value) : ?>
    <?php
    // Percent math

    if ($show_result) {
      if(0==$total_user_poll) {
         $percent = __('(no votes yet)', 'peepso-core');
      } else {
        $percent = ($value['total_user_poll'] / $total_user_poll) * 100;
      }
    }
    ?>
    <div class="ps-poll__item">
      <div class="ps-poll__item-inner">
        <label class="ps-poll__item-bar">
          <?php if ($show_result) : ?>
          <div class="ps-poll__item-fill" style="width: <?php echo $percent . '%'; ?>"></div>
          <?php endif; ?>
          <div class="ps-poll__item-name">
            <?php if ( is_user_logged_in()) { ?>
            <div class="ps-poll__item-input">
      					<input type="<?php echo $type; ?>" name="options_<?php echo $id; ?>[]" value="<?php echo $key; ?>" id="<?php echo $key; ?>" class="ps-js-poll-item-option" <?php echo $is_voted || !$enabled ? 'disabled' : ''; ?> <?php echo in_array($key, $user_polls) ? 'checked' : ''; ?> />
            </div>
            <?php } ?>
            <span><?php echo $value['label']; ?></span>
          </div>
          <?php if ($show_result) : ?>
          <div class="ps-poll__item-value">
            <?php echo (is_numeric($percent)) ? number_format($percent, 0, '.', ',') . '%' : $percent; ?>
          </div>
          <?php endif; ?>
        </label>

        <?php if ($show_result) : ?>
        <div class="ps-poll__item-votes">
          <?php echo '(' . $value['total_user_poll'] . ' ' . __('of', 'peepso-core') . ' ' . $total_user_poll . ')'; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if ($enabled && !$is_voted && count($options) > 1) { ?>
  <div class="ps-poll__actions">
		<?php $has_vote = isset($user_polls) && count($user_polls) > 0; ?>

		<button class="ps-btn ps-btn--xs ps-btn--action ps-js-poll-item-submit" data-id="<?php echo $id; ?>" disabled="disabled"
			<?php echo $has_vote ? '' : ' disabled="disabled"' ?>
				onclick="peepso.polls.submit_vote(<?php echo $id ?>, this);">
      <i class="gcis gci-check"></i>
			<?php if ($has_vote) { ?>
			<?php echo __('Change Vote', 'peepso-core'); ?>
			<?php } else { ?>
			<?php echo __('Submit', 'peepso-core'); ?>
			<?php } ?>
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
		</button>

		<?php if ($has_vote) { ?>
		<button class="ps-btn ps-btn--xs ps-js-poll-item-unvote" data-id="<?php echo $id; ?>"
			onclick="peepso.polls.unvote(<?php echo $id ?>, this);">
			<?php echo __('Unvote', 'peepso-core'); ?>
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="loading" style="display:none" />
		</button>
		<?php } ?>
  </div>
  <?php } ?>

	<?php if ( ! is_user_logged_in()) { ?>
		<div class="ps-poll__message"><i class="gcis gci-lock"></i><?php echo __('Login to cast your vote and to see results.', 'peepso-core'); ?></div>
	<?php } ?>
</div>
