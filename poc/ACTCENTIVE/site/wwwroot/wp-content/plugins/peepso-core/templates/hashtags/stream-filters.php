<?php
/** SEARCH POSTS **/
$hashtag = FALSE;
$PeepSoUrlSegments = PeepSoUrlSegments::get_instance();

if('hashtag' == $PeepSoUrlSegments->get(1)) {
    $hashtag = $PeepSoUrlSegments->get(2);
}

?>
<input type="hidden" id="peepso_search_hashtag" value="<?php echo $hashtag; ?>" />
<div class="ps-posts__filter ps-posts__filter--hashtag ps-js-dropdown ps-js-activitystream-filter" data-id="peepso_search_hashtag">
	<a class="ps-posts__filter-toggle ps-js-dropdown-toggle" aria-haspopup="true">
		<span data-empty="<?php echo __('#', 'peepso-core'); ?>"
			data-keyword="<?php echo __('#', 'peepso-core'); ?>"
		><i class="gcis gci-hashtag"></i></span>
	</a>
	<div role="menu" class="ps-posts__filter-box ps-posts__filter-box--hashtag ps-js-dropdown-menu">
		<div class="ps-posts__filter-hashtag ps-posts__filter-search">
			<i class="gcis gci-hashtag"></i><input maxlength="<?php echo PeepSo::get_option('hashtags_max_length',16);?>" type="text" class="ps-input ps-input--sm"
				placeholder="<?php echo __('Type to search', 'peepso-core'); ?>" value="<?php echo $hashtag;?>" />
    </div>
    <div class="ps-posts__filter-select-desc">
    	<i class="gcis gci-info-circle"></i>
      <?php
          echo sprintf(
                  __('Letters and numbers only, minimum %d and maximum %d character(s)','peepso-core'),
                  PeepSo::get_option('hashtags_min_length',3),
                  PeepSo::get_option('hashtags_max_length',16)
          );?>
		</div>
		<div class="ps-posts__filter-actions">
			<button class="ps-posts__filter-action ps-btn ps-btn--sm ps-js-cancel"><?php echo __('Cancel', 'peepso-core'); ?></button>
			<button class="ps-posts__filter-action ps-btn ps-btn--sm ps-btn--action ps-js-search-hashtag"><?php echo __('Apply', 'peepso-core'); ?></button>
		</div>
	</div>
</div>
