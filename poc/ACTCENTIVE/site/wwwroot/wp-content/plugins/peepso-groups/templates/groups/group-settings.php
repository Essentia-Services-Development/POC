<div class="peepso">
  <div class="ps-page ps-page--group ps-page--group-settings">
    <?php PeepSoTemplate::exec_template('general','navbar'); ?>
    <?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>

    <?php if(get_current_user_id()) { ?>
      <div class="ps-group__edit">
        <?php
        PeepSoTemplate::exec_template('groups', 'group-header', array('group'=>$group, 'group_segment'=>$group_segment));
        $group_users = new PeepSoGroupUsers($group->id);
        $group_user = new PeepSoGroupUser($group->id);
        ?>

        <div class="ps-group__edit-fields">
          <!-- NAME -->
          <div class="ps-group__edit-field ps-group__edit-field--name ps-js-group-name">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span><?php echo __('Group Name', 'groupso'); ?></span>
                  <span class="ps-group__edit-field-required">*</span>
                </div>

                <?php if ($group_user->can('manage_group')) { ?>
                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_name(<?php echo $group->id; ?>, this);">
                    <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                    <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-group-name-text">
                  <?php echo $group->name;?>
                </div>
              </div>

              <?php if ($group_user->can('manage_group')) { ?>
              <div class="ps-group__edit-field-form ps-js-group-name-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <input type="text" class="ps-input ps-input--sm ps-input--count" maxlength="<?php echo PeepSoGroup::$validation['name']['maxlength'];?>" data-maxlength="<?php echo PeepSoGroup::$validation['name']['maxlength'];?>" value="<?php echo esc_attr($group->name); ?>">
                  <div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline" aria-label="<?php echo __('Characters left', 'groupso'); ?>"><?php echo PeepSoGroup::$validation['name']['maxlength'];?></span></div>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: NAME -->

          <!--  SLUG -->
          <?php if ($group_user->can('manage_group') && 2 == PeepSo::get_option('groups_slug_edit', 0)) {

          $slug = urldecode($group->slug);
          ?>
          <div class="ps-group__edit-field ps-group__edit-field--slug ps-js-group-slug">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span><?php echo __('Group Slug', 'groupso'); ?></span>
                  <span class="ps-group__edit-field-required">*</span>
                </div>

                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-group-slug-trigger" onclick="ps_group.edit_slug(<?php echo $group->id; ?>, this);">
                    <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-submit">
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                    <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-group-slug-text">
                  <?php echo PeepSo::get_page('groups')."<strong>$slug</strong>"; ?>
                </div>
              </div>

              <div class="ps-group__edit-field-form ps-js-group-slug-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <input size="30" class="ps-input ps-input--sm" maxlength="<?php echo PeepSoGroup::$validation['name']['maxlength'];?>" data-maxlength="<?php echo PeepSoGroup::$validation['name']['maxlength'];?>" value="<?php echo $slug; ?>">
                </div>
                <div class="ps-group__edit-field-desc">
                  <?php
                  echo __('Letters, numbers and dashes are recommended, eg my-amazing-group-123.','groupso') .'<br/>'.__('This field might be automatically adjusted  after editing.','groupso');
                  ?>
                </div>
              </div>
            </div>
          </div><!-- end: SLUG -->
          <?php } ?>

            <!-- DESCRIPTION -->
            <div class="ps-group__edit-field ps-group__edit-field--desc ps-js-group-desc">
                <div class="ps-group__edit-field-row">
                    <div class="ps-group__edit-field-header">
                        <div class="ps-group__edit-field-title">
                            <span><?php echo __('Group Description', 'groupso'); ?></span>
                            <span class="ps-group__edit-field-required">*</span>
                        </div>

                        <?php if ($group_user->can('manage_group')) { ?>
                            <div class="ps-group__edit-field-edit">
                                <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_desc(<?php echo $group->id; ?>, this);">
                                    <?php echo __('Edit','groupso');?>
                                </button>
                            </div>

                            <div class="ps-group__edit-field-actions">
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-cancel"><?php echo __('Cancel', 'groupso'); ?></button>
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-submit">
                                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                                    <?php echo __('Save', 'groupso'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="ps-group__edit-field-static">
                        <?php
                        $description = str_replace("\n","<br/>", $group->description);
                        $description = html_entity_decode($description);
                        if (PeepSo::get_option_new('md_groups_about', 0)) {
                            $description = PeepSo::do_parsedown($description);
                        }
                        ?>

                        <div class="ps-group__edit-field-data">
                            <span class="ps-js-group-desc-text" style="<?php echo empty($group->description) ? 'display:none' : '' ?>"><?php echo stripslashes($description); ?></span>
                            <span class="ps-js-group-desc-placeholder" style="<?php echo empty($group->description) ? '' : 'display:none' ?>"><i><?php echo __('No description', 'groupso'); ?></i></span>
                        </div>
                    </div>

                    <?php if ($group_user->can('manage_group')) { ?>
                        <div class="ps-group__edit-field-form ps-js-group-desc-editor" style="display:none">
                            <div class="ps-input__wrapper">
                                <textarea class="ps-input ps-input--sm ps-input--textarea ps-input--count" rows="10" data-maxlength="<?php echo PeepSoGroup::$validation['description']['maxlength'];?>"><?php echo html_entity_decode($group->description); ?></textarea>
                                <div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline" aria-label="<?php echo __('Characters left', 'groupso'); ?>"><?php echo PeepSoGroup::$validation['description']['maxlength'];?></span></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- end: DESCRIPTION -->


            <?php if(PeepSo::get_option_new('groups_rules_enabled')) { ?>
            <!-- RULES -->
            <div class="ps-group__edit-field ps-group__edit-field--rules ps-js-group-rules">
                <div class="ps-group__edit-field-row">
                    <div class="ps-group__edit-field-header">
                        <div class="ps-group__edit-field-title">
                            <span><?php echo __('Group Rules', 'groupso'); ?></span>
<!--                            <span class="ps-group__edit-field-required">*</span>-->
                        </div>

                        <?php if ($group_user->can('manage_group')) { ?>
                            <div class="ps-group__edit-field-edit">
                                <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_rules(<?php echo $group->id; ?>, this);">
                                    <?php echo __('Edit','groupso');?>
                                </button>
                            </div>

                            <div class="ps-group__edit-field-actions">
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-cancel"><?php echo __('Cancel', 'groupso'); ?></button>
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-submit">
                                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                                    <?php echo __('Save', 'groupso'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="ps-group__edit-field-static">
                        <?php
                        $rules = str_replace("\n","<br/>", $group->rules);
                        $rules = html_entity_decode($rules);
                        if (PeepSo::get_option_new('md_groups_rules', 0)) {
                            $rules = PeepSo::do_parsedown($rules);
                        }
                        ?>

                        <div class="ps-group__edit-field-data">
                            <span class="ps-js-group-rules-text" style="<?php echo empty($group->rules) ? 'display:none' : '' ?>"><?php echo stripslashes($rules); ?></span>
                            <span class="ps-js-group-rules-placeholder" style="<?php echo empty($group->rules) ? '' : 'display:none' ?>"><i><?php echo __('No rules', 'groupso'); ?></i></span>
                        </div>
                    </div>

                    <?php if ($group_user->can('manage_group')) { ?>
                        <div class="ps-group__edit-field-form ps-js-group-rules-editor" style="display:none">
                            <div class="ps-input__wrapper">
                                <textarea class="ps-input ps-input--sm ps-input--textarea ps-input--count" rows="10" data-maxlength="<?php echo PeepSoGroup::$validation['rules']['maxlength'];?>"><?php echo html_entity_decode($group->rules); ?></textarea>
                                <div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline" aria-label="<?php echo __('Characters left', 'groupso'); ?>"><?php echo PeepSoGroup::$validation['rules']['maxlength'];?></span></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- end: RULES -->
            <?php } ?>

          <?php do_action('peepso_action_render_group_settings_form_before'); ?>

          <?php if(PeepSo::get_option('groups_categories_enabled', FALSE)) { ?>
          <!-- CATEGORIES -->
          <div class="ps-group__edit-field ps-group__edit-field--cats ps-js-group-cat">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span><?php
                  $group_categories = PeepSoGroupCategoriesGroups::get_categories_for_group($group->id);

                  echo _n('Category', 'Categories', count($group_categories), 'groupso'); ?></span>
                  <span class="ps-group__edit-field-required">*</span>
                </div>

                <?php if ($group_user->can('manage_group')) { ?>
                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_cats(<?php echo $group->id; ?>, this);">
                      <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-cancel"><?php echo __('Cancel', 'groupso'); ?></button>
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-group-cat-text">
                  <?php
                    $group_categories_html = array();
                    foreach ($group_categories as $PeepSoGroupCategory) {
                      echo "<a href=\"{$PeepSoGroupCategory->get_url()}\">{$PeepSoGroupCategory->name}</a>";
                    }
                  ?>
                </div>
              </div>

              <?php if ($group_user->can('manage_group')) { ?>
              <div class="ps-group__edit-field-form ps-js-group-cat-editor" style="display:none">
                <div class="ps-input__wrapper ps-checkbox__grid">
                  <?php

                  $multiple_enabled = (PeepSo::get_option_new('groups_categories_multiple_max') > 1);
                  $input_type = ($multiple_enabled) ? 'checkbox' : 'radio';
                  $PeepSoGroupCategories = new PeepSoGroupCategories(FALSE, TRUE);
                  $categories = $PeepSoGroupCategories->categories;

                  if (count($categories)) {
                      foreach ($categories as $id => $category) {
                          $checked = '';
                          if (isset($group_categories[$id])) {
                              $checked = 'checked="checked"';
                          }
                          echo sprintf('<div class="ps-checkbox"><input class="ps-checkbox__input" %s type="%s" id="category_' . $id . '" name="category_id" value="%d"><label class="ps-checkbox__label" for="category_' . $id . '">%s</label></div>', $checked, $input_type, $id, $category->name);
                      }
                  }

                  ?>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: CATEGORIES -->
          <?php } ?>

          <?php do_action('peepso_action_render_group_settings_form_after'); ?>

          <?php if(!$group->is_secret) { ?>
          <!-- JOIN BUTTON -->
          <div class="ps-group__edit-field ps-group__edit-field--join ps-js-group-is_joinable">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span>
                    <?php
                      if($group->is_open) { echo __('Enable "Join" button', 'groupso'); }
                      if($group->is_closed) { echo __('Enable "Request To Join" button', 'groupso'); }
                    ?>
                  </span>
                  <div class="ps-group__edit-field-note">
                    <?php echo __('Has no effect on Site Administrators','groupso'); ?>
                  </div>
                </div>

                <?php if ($group_user->can('manage_group')) { ?>
                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_property(this, <?php echo $group->id; ?>, 'is_joinable');">
                      <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-text">
                  <?php echo ($group->is_joinable) ? __('Yes', 'groupso') : __('No', 'groupso');?>
                </div>
              </div>

              <?php if ($group_user->can('manage_group')) { ?>
              <div class="ps-group__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_joinable" class="ps-input ps-input--sm ps-input--select">
                    <option value="1"><?php echo __('Yes', 'groupso');?></option>
                    <option value="0" <?php if(FALSE == $group->is_joinable) { echo "selected";}?>><?php echo __('No', 'groupso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: JOIN BUTTON -->
          <?php } ?>

          <!-- INVITE BUTTON -->
          <div class="ps-group__edit-field ps-group__edit-field--invite ps-js-group-is_invitable">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span>
                    <?php echo __('Enable "Invite" button', 'groupso'); ?>
                  </span>
                  <div class="ps-group__edit-field-note">
                    <?php echo __('Has no effect on Owner, Managers and Site Administrators','groupso'); ?>
                  </div>
                </div>

                <?php if ($group_user->can('manage_group')) { ?>
                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_property(this, <?php echo $group->id; ?>, 'is_invitable');">
                      <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-text">
                  <?php echo ($group->is_invitable) ? __('Yes', 'groupso') : __('No', 'groupso');?>
                </div>
              </div>

              <?php if ($group_user->can('manage_group')) { ?>
              <div class="ps-group__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_invitable" class="ps-input ps-input--sm ps-input--select">
                    <option value="1"><?php echo __('Yes', 'groupso');?></option>
                    <option value="0" <?php if(FALSE == $group->is_invitable) { echo "selected";}?>><?php echo __('No', 'groupso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: INVITE BUTTON -->

            <!-- DISABLE POSTING -->
            <div class="ps-group__edit-field ps-group__edit-field--readonly ps-js-group-is_readonly">
                <div class="ps-group__edit-field-row">
                    <div class="ps-group__edit-field-header">
                        <div class="ps-group__edit-field-title">
                  <span>
                    <?php echo __('Disable new posts', 'groupso'); ?>
                  </span>
                            <div class="ps-group__edit-field-note">
                                <?php echo __('Has no effect on Owner, Managers and Site Administrators','groupso'); ?>
                            </div>
                        </div>

                        <?php if ($group_user->can('manage_group')) { ?>
                            <div class="ps-group__edit-field-edit">
                                <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_property(this, <?php echo $group->id; ?>, 'is_readonly');">
                                    <?php echo __('Edit','groupso');?>
                                </button>
                            </div>

                            <div class="ps-group__edit-field-actions">
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                                <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                                    <?php echo __('Save', 'groupso'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="ps-group__edit-field-static">
                        <div class="ps-group__edit-field-data ps-js-text">
                            <?php echo ($group->is_readonly) ? __('Yes', 'groupso') : __('No', 'groupso');?>
                        </div>
                    </div>

                    <?php if ($group_user->can('manage_group')) { ?>
                        <div class="ps-group__edit-field-form ps-js-editor" style="display:none">
                            <div class="ps-input__wrapper">
                                <select name="is_readonly" class="ps-input ps-input--sm ps-input--select">
                                    <option value="1"><?php echo __('Yes', 'groupso');?></option>
                                    <option value="0" <?php if(FALSE == $group->is_readonly) { echo "selected";}?>><?php echo __('No', 'groupso');?></option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- end: DISABLE POSTING -->

            <?php if(PeepSo::get_option_new('groups_members_tab_override')) { ?>
            <!-- MEMBERS TAB -->
            <div class="ps-group__edit-field ps-group__edit-field--members_tab ps-js-group-members_tab">
                <div class="ps-group__edit-field-row">
                    <div class="ps-group__edit-field-header">
                        <div class="ps-group__edit-field-title">
                  <span>
                    <?php echo __('Members tab', 'groupso'); ?>
                  </span>
                            <div class="ps-group__edit-field-note">
                                <?php echo __('Has no effect on Owner, Managers and Site Administrators','groupso'); ?>
                            </div>
                        </div>

                        <?php if ($group_user->can('manage_group')) { ?>
                            <div class="ps-group__edit-field-edit">
                                <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_property(this, <?php echo $group->id; ?>, 'members_tab');">
                                    <?php echo __('Edit','groupso');?>
                                </button>
                            </div>

                            <div class="ps-group__edit-field-actions">
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                                <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                                    <?php echo __('Save', 'groupso'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="ps-group__edit-field-static">
                        <div class="ps-group__edit-field-data ps-js-text">
                            <?php echo ($group->members_tab) ? __('Yes', 'groupso') : __('No', 'groupso');?>
                        </div>
                    </div>

                    <?php if ($group_user->can('manage_group')) { ?>
                        <div class="ps-group__edit-field-form ps-js-editor" style="display:none">
                            <div class="ps-input__wrapper">
                                <select name="members_tab" class="ps-input ps-input--sm ps-input--select">
                                    <option value="1"><?php echo __('Yes', 'groupso');?></option>
                                    <option value="0" <?php if(FALSE == $group->members_tab) { echo "selected";}?>><?php echo __('No', 'groupso');?></option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- end: MEMBERS TAB -->
            <?php } ?>

          <!-- DISABLE COMMENTS / REACTIONS / LIKE -->
          <div class="ps-group__edit-field ps-group__edit-field--interactable ps-js-group-is_interactable">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span>
                    <?php echo __('Disable likes/comments', 'groupso'); ?>
                  </span>
                  <div class="ps-group__edit-field-note">
                    <?php echo __('Has no effect on Owner, Managers and Site Administrators','groupso'); ?>
                  </div>
                </div>

                <?php if ($group_user->can('manage_group')) { ?>
                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_property(this, <?php echo $group->id; ?>, 'is_interactable');">
                      <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                    <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-text">
                  <?php echo ($group->is_interactable) ? __('Yes', 'groupso') : __('No', 'groupso');?>
                </div>
              </div>

              <?php if ($group_user->can('manage_group')) { ?>
              <div class="ps-group__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_interactable" class="ps-input ps-input--sm ps-input--select">
                      <option value="1"><?php echo __('Yes', 'groupso');?></option>
                      <option value="0" <?php if(FALSE == $group->is_interactable) { echo "selected";}?>><?php echo __('No', 'groupso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: DISABLE COMMENTS / REACTIONS / LIKE -->

          <!-- ALLOWED NON-MEMBER ACTIONS COMMENTS / REACTINS / LIKE -->
            <?php if($group->is_open) { ?>
          <div class="ps-group__edit-field ps-group__edit-field--allowed_non_member_actions ps-js-group-is_allowed_non_member_actions">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span>
                    <?php echo __('Allowed non-member actions', 'groupso'); ?>
                  </span>
                    <div class="ps-group__edit-field-note">
                        <?php echo __('Has no effect if the setting above is set to "yes"','groupso'); ?>
                    </div>
                </div>

                <?php if ($group_user->can('manage_group')) { ?>
                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_property(this, <?php echo $group->id; ?>, 'is_allowed_non_member_actions');">
                      <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                    <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-text">
                  <?php
                  switch ($group->is_allowed_non_member_actions) {
                    case 1:
                      echo __('Reactions', 'groupso');
                      break;
                    case 2:
                      echo __('Comments', 'groupso');
                      break;
                    case 3:
                      echo __('Reactions and comments', 'groupso');
                      break;

                    default:
                      echo __('Nothing (default)', 'groupso');;
                      break;
                  }
                  ?>
                </div>
              </div>

              <?php if ($group_user->can('manage_group')) { ?>
              <div class="ps-group__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_allowed_non_member_actions" class="ps-input ps-input--sm ps-input--select">
                      <option value="0"><?php echo __('Nothing (default)', 'groupso');?></option>
                      <option value="1" <?php if(1 == $group->is_allowed_non_member_actions) { echo "selected";}?>><?php echo __('Reactions', 'groupso');?></option>
                      <option value="2" <?php if(2 == $group->is_allowed_non_member_actions) { echo "selected";}?>><?php echo __('Comments', 'groupso');?></option>
                      <option value="3" <?php if(3 == $group->is_allowed_non_member_actions) { echo "selected";}?>><?php echo __('Reactions and comments', 'groupso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div>
          <?php } ?>
          <!-- end: ALLOWED NON-MEMBER ACTIONS COMMENTS / REACTINS / LIKE -->

          <!-- DISABLE NEW MEMBER NOTIFICATIONS -->
          <div class="ps-group__edit-field ps-group__edit-field--muted ps-js-group-is_join_muted">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span>
                    <?php echo __('Disable new member notifications', 'groupso'); ?>
                  </span>
                  <div class="ps-group__edit-field-note">
                    <?php echo __('Owners & Managers will not receive notifications about new members','groupso'); ?>
                  </div>
                </div>

                <?php if ($group_user->can('manage_group')) { ?>
                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_property(this, <?php echo $group->id; ?>, 'is_join_muted');">
                      <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-text">
                  <?php echo ($group->is_join_muted) ? __('Yes', 'groupso') : __('No', 'groupso');?>
                </div>
              </div>

              <?php if ($group_user->can('manage_group')) { ?>
              <div class="ps-group__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_join_muted" class="ps-input ps-input--sm ps-input--select">
                      <option value="1"><?php echo __('Yes', 'groupso');?></option>
                      <option value="0" <?php if(FALSE == $group->is_join_muted) { echo "selected";}?>><?php echo __('No', 'groupso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: DISABLE NEW MEMBER NOTIFICATIONS -->

          <?php if ($group->is_closed) {?>
          <!-- AUTO ACCEPT MEMBER -->
          <div class="ps-group__edit-field ps-group__edit-field--muted ps-js-group-is_auto_accept_join_request">
            <div class="ps-group__edit-field-row">
              <div class="ps-group__edit-field-header">
                <div class="ps-group__edit-field-title">
                  <span>
                    <?php echo __('Automatically accept join requests', 'groupso'); ?>
                  </span>
                  <div class="ps-group__edit-field-note">
                    <?php echo __('User immediately becomes a new member after click "join" button','groupso'); ?>
                  </div>
                </div>

                <?php if ($group_user->can('manage_group')) { ?>
                <div class="ps-group__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_group.edit_property(this, <?php echo $group->id; ?>, 'is_auto_accept_join_request');">
                      <?php echo __('Edit','groupso');?>
                  </button>
                </div>

                <div class="ps-group__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'groupso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'groupso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-group__edit-field-static">
                <div class="ps-group__edit-field-data ps-js-text">
                  <?php echo ($group->is_auto_accept_join_request) ? __('Yes', 'groupso') : __('No', 'groupso');?>
                </div>
              </div>

              <?php if ($group_user->can('manage_group')) { ?>
              <div class="ps-group__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_auto_accept_join_request" class="ps-input ps-input--sm ps-input--select">
                      <option value="1"><?php echo __('Yes', 'groupso');?></option>
                      <option value="0" <?php if(FALSE == $group->is_auto_accept_join_request) { echo "selected";}?>><?php echo __('No', 'groupso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: AUTO ACCEPT MEMBER -->
          <?php } ?>

        </div>
      </div>
    <?php } ?>
  </div>
</div>
<?php

if(get_current_user_id()) {
    PeepSoTemplate::exec_template('activity' ,'dialogs');
}
