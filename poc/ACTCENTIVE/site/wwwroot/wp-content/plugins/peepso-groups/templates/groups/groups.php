<?php

$categories_enabled = FALSE;
$categories_tab  = FALSE;

if(PeepSo::get_option('groups_categories_enabled', FALSE)) {

	$categories_enabled = TRUE;

	$PeepSoGroupCategories = new PeepSoGroupCategories(FALSE, NULL);
	$categories = $PeepSoGroupCategories->categories;
	if (!isset($_GET['category'])) {
		$categories_default_view = PeepSo::get_option('groups_categories_default_view', 0);
		$_GET['category'] = $categories_default_view;
	}

	if (!isset($_GET['category']) || (isset($_GET['category']) && intval($_GET['category'])==1)) {
		$categories_tab = TRUE;
	}
}
?>
<div class="peepso">
	<div class="ps-page ps-page--groups">
		<?php PeepSoTemplate::exec_template('general','navbar'); ?>
		<?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

		<div class="ps-groups">
			<?php if(get_current_user_id() || (get_current_user_id() == 0 && $allow_guest_access)) { ?>
				<div class="ps-groups__header">
					<div class="ps-groups__header-inner">
						<div class="ps-groups__list-view">
							<div class="ps-btn__group">
								<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-groups-viewmode" data-mode="grid" aria-label="<?php echo __('Grid', 'groupso');?>"><i class="gcis gci-th-large"></i></a>
								<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-groups-viewmode" data-mode="list" aria-label="<?php echo __('List', 'groupso');?>"><i class="gcis gci-th-list"></i></a>
							</div>
						</div>



						<?php if(PeepSoGroupUser::can_create()) { ?>
						<div class="ps-groups__actions">
							<a class="ps-btn ps-btn--sm ps-btn--action" href="#" onclick="peepso.groups.dlgCreate(); return false;">
								<?php echo __('Create Group', 'groupso');?>
							</a>
						</div>
						<?php } ?>
					</div>


                    <?php if(!$categories_tab) { ?>
                        <div class="ps-groups__search">
                            <form class="ps-form" role="form" name="form-peepso-search" onsubmit="return false;">
                                <div class="ps-form__row">
                                    <div class="ps-form__field ps-form__field--icon">
										<i class="gcis gci-search"></i>
                                        <input placeholder="<?php echo __('Start typing to search...', 'groupso');?>" type="text" class="ps-input ps-input--sm ps-input--icon ps-input--icon-right ps-groups__search-input ps-js-groups-query" name="query" value="<?php echo esc_attr($search); ?>" />
                                        <a href="#" class="ps-input__icon ps-input__icon--right ps-groups__filters-toggle ps-tooltip ps-form-search-opt" onclick="return false;" data-tooltip="<?php echo __('Show filters', 'groupso');?>">
                                            <i class="gcis gci-cog"></i>
                                        </a>
                                    </div>
                                    <div class="ps-groups__filter">
                                        <select class="ps-input ps-input--sm ps-input--select ps-js-groups-search-mode">
                                            <option value="exact"><?php echo __('Exact phrase', 'peepso-core'); ?></option>
                                            <option value="any"><?php echo __('Any of the words', 'peepso-core'); ?></option>
                                        </select>
                                    </div>
                                </div>



                            </form>
                        </div>



						<?php
						$default_sorting = '';
						if(!strlen(esc_attr($search)))
						{
							$default_sorting = PeepSo::get_option('groups_default_sorting','id');
							$default_sorting_order = PeepSo::get_option('groups_default_sorting_order','DESC');
						}
						?>

						<div class="ps-groups__filters ps-js-page-filters" style="<?php echo ($categories_enabled && !$categories_tab) ? "" : "display:none";?>">
                            <?php
                            #6666 GeoMyWP hooks
                            do_action('peepso_action_render_groups_search_before');
                            ?>
							<div class="ps-groups__filters-inner">
								<div class="ps-groups__filter">
									<label class="ps-groups__filter-label"><?php echo __('Sort', 'groupso'); ?></label>
									<select class="ps-input ps-input--sm ps-input--select ps-js-groups-sortby">
											<option value="id"><?php echo __('Recently added', 'groupso'); ?></option>
											<option <?php echo ('post_title' == $default_sorting) ? ' selected="selected" ' : '';?> value="post_title"><?php echo __('Alphabetical', 'groupso'); ?></option>
											<option <?php echo ('meta_members_count' == $default_sorting) ? ' selected="selected" ' : '';?>value="meta_members_count"><?php echo __('Members count', 'groupso'); ?></option>
									</select>
								</div>

								<div class="ps-groups__filter">
									<label class="ps-groups__filter-label">&nbsp;</label>
									<select class="ps-input ps-input--sm ps-input--select ps-js-groups-sortby-order">
											<option value="DESC"><?php echo __('Descending', 'groupso'); ?></option>
											<option <?php echo ('ASC' == $default_sorting_order) ? ' selected="selected" ' : '';?> value="ASC"><?php echo __('Ascending', 'groupso'); ?></option>
									</select>
								</div>

								<?php if($categories_enabled) { ?>
									<div class="ps-groups__filter">
										<label class="ps-groups__filter-label"><?php echo __('Category', 'groupso'); ?></label>
										<select class="ps-input ps-input--sm ps-input--select ps-js-groups-category">
											<option value="0"><?php echo __('No filter', 'groupso'); ?></option>
											<?php
											if(count($categories)) {
												foreach($categories as $id=>$cat) {
														$count = PeepSoGroupCategoriesGroups::update_stats_for_category($id);
													$selected = "";
													if($id==$category) {
														$selected = ' selected="selected"';
													}
													echo "<option value=\"$id\"{$selected}>{$cat->name} ($count)</option>";
												}
											}

											$count_uncategorized = PeepSoGroupCategoriesGroups::update_stats_for_category();
											if ($count_uncategorized > 0) {
												?>
												<option value="-1" <?php if(-1 == $category) { echo 'selected="selected"';}?>><?php echo __('Uncategorized', 'groupso'); ?></option>
												<?php
											}
											?>
										</select>
									</div>
								<?php } // ENDIF ?>



							</div>
                            <?php
                            #6666 GeoMyWP hooks
                            do_action('peepso_action_render_groups_search_after');
                            ?>
						</div>
					<?php } ?>
				</div>
				<?php if($categories_enabled) { ?>
				<div class="ps-groups__tabs">
					<div class="ps-groups__tabs-inner">
						<div class="ps-groups__tab <?php if(!$categories_tab) echo "ps-groups__tab--active";?>"><a href="<?php echo PeepSo::get_page('groups').'?category=0';?>"><?php echo __('Groups', 'groupso'); ?></a></div>
						<div class="ps-groups__tab <?php if($categories_tab) echo "ps-groups__tab--active";?>"><a href="<?php echo PeepSo::get_page('groups').'?category=1';?>"><?php echo __('Group Categories', 'groupso'); ?></a></div>
					</div>
				</div>
				<?php } ?>

				<?php if($categories_tab) { ?>
					<?php $single_column = PeepSo::get_option( 'groups_single_column', 0 ); ?>
					<div class="mb-20"></div>
					<div class="ps-groups__categories ps-js-group-cats" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>"></div>
					<div class="ps-groups__loading ps-js-group-cats-triggerscroll">
						<img class="ps-loading post-ajax-loader ps-js-group-cats-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="<?php echo __('Loading', 'groupso'); ?>" />
					</div>
				<?php } else { ?>
					<?php $single_column = PeepSo::get_option( 'groups_single_column', 0 ); ?>
					<div class="mb-20"></div>
					<div class="ps-groups__list <?php echo $single_column ? 'ps-groups__list--single' : '' ?> ps-js-groups" data-mode="<?php echo $single_column ? 'list' : 'grid' ?>"></div>
					<div class="ps-groups__loading ps-js-groups-triggerscroll">
						<img class="ps-loading post-ajax-loader ps-js-groups-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="<?php echo __('Loading', 'groupso'); ?>" />
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
</div><!-- .peepso wrapper -->

<?php

if(get_current_user_id()) {
	PeepSoTemplate::exec_template('activity', 'dialogs');
}
