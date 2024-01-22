				<?php
				// get gender field
				$PeepSoUser = PeepSoUser::get_instance(0);
				$profile_fields = new PeepSoProfileFields($PeepSoUser);
				$fields = $profile_fields->load_fields();

				$genders = array();

				if(isset($fields['peepso_user_field_gender'])) {
		            $genders = $fields['peepso_user_field_gender']->meta->select_options;
		        }

				$args = array(
					'post_name__in'=>array('gender')
				);
				$fields = $profile_fields->load_fields($args);
				if (isset($fields) && isset($fields[PeepSoField::USER_META_FIELD_KEY . 'gender'])) {
					$fieldGender = $fields[PeepSoField::USER_META_FIELD_KEY . 'gender'];
				}

	            ?>

				<form class="ps-members__header ps-form ps-form-search" role="form" name="form-peepso-search" onsubmit="return false;">
					<div class="ps-members__search">
						<input placeholder="<?php echo __('Start typing to search...', 'groupso');?>" type="text" class="ps-input ps-js-members-query" name="query" value="" />
					</div>
					<a href="#" class="ps-members__filters-toggle ps-form-search-opt" onclick="return false;">
						<span class="gcis gci-cog"></span>
					</a>
				</form>
				<div class="ps-members__filters ps-js-page-filters">
					<div class="ps-members__filters-inner">
						<?php if (isset($fieldGender) && ($fieldGender->published == 1)){ ?>
						<div class="ps-members__filter">
							<div class="ps-members__filter-label"><?php echo __($fieldGender->title, 'groupso'); ?></div>
							<select class="ps-input ps-input--sm ps-input--select ps-js-members-gender">
								<option value=""><?php echo __('Any', 'groupso'); ?></option>
								<?php
								if (!empty($genders) && is_array($genders)) {
									foreach ($genders as $key => $value) {
										?>
										<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<?php } ?>

						<?php $default_sorting = PeepSo::get_option('site_memberspage_default_sorting',''); ?>
						<div class="ps-members__filter">
							<div class="ps-members__filter-label"><?php echo __('Sort', 'groupso'); ?></div>
							<select class="ps-input ps-input--sm ps-input--select ps-js-members-sortby">
								<option value=""><?php echo __('Alphabetical', 'groupso'); ?></option>
								<option <?php echo ('peepso_last_activity' == $default_sorting) ? ' selected="selected" ' : '';?> value="peepso_last_activity|asc"><?php echo __('Recently online', 'groupso'); ?></option>
								<option <?php echo ('registered' == $default_sorting) ? ' selected="selected" ' : '';?>value="registered|desc"><?php echo __('Latest members', 'groupso'); ?></option>
								<?php if (PeepSo::get_option('site_likes_profile', TRUE)) : ?>
								<option <?php echo ('most_liked' == $default_sorting) ? ' selected="selected" ' : '';?>value="most_liked|desc"><?php echo __('Most liked', 'groupso'); ?></option>
								<?php endif; ?>
							</select>
						</div>

						<?php if(class_exists('PeepSoFriendsPlugin')) { ?>
						<div class="ps-members__filter">
	                        <div class="ps-members__filter-label"><?php echo __('Following', 'groupso');?></div>
	                        <select class="ps-input ps-input--sm ps-input--select ps-js-members-following">
	                            <option value="-1"><?php echo __('All members', 'groupso'); ?></option>
	                            <option value="1"><?php echo __('Members I follow', 'groupso'); ?></option>
	                            <option value="0"><?php echo __('Members I don\'t follow', 'groupso'); ?></option>
	                        </select>
						</div>
						<?php } else { ?>
						<input type="hidden" id="only-following" name="followed" value="01" class="ps-js-members-following" />
						<?php } ?>

						<div class="ps-members__filter">
	                        <div class="ps-members__filter-label"><?php echo __('Avatars', 'groupso');?></div>
	                        <div class="ps-checkbox">
	                            <input type="checkbox" id="only-avatars" name="avatar" value="1" class="ps-checkbox__input ps-js-members-avatar" />
	                            <label class="ps-checkbox__label" for="only-avatars"><?php echo __('Only users with avatars', 'groupso'); ?></label>
	                        </div>
						</div>

						<?php do_action('peepso_action_render_member_search_fields'); ?>
					</div>
				</div>
