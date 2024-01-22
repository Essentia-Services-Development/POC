<?php

$random = rand();

?>

<div class="ps-postbox__groups-options ps-dropdown__menu ps-js-postbox-dropdown ps-js-postbox-group" style="display:none">
  <a role="menuitem" class="ps-postbox__groups-option" data-option-value="">
    <div class="ps-checkbox">
      <input class="ps-checkbox__input" type="radio" name="peepso_postbox_group_<?php echo $random ?>"
        id="peepso_postbox_group_<?php echo $random ?>_" value="" <?php if(!$category_id) { echo ' checked="checked" '; } ?> />
      <label class="ps-checkbox__label" for="peepso_postbox_group_<?php echo $random ?>_">
        <span><?php echo __('My profile', 'groupso') ?></span>
      </label>
    </div>
  </a>

  <a role="menuitem" class="ps-postbox__groups-option ps-postbox__groups-option--groups" data-option-value="group">
    <div class="ps-checkbox">
      <input class="ps-checkbox__input" type="radio" name="peepso_postbox_group_<?php echo $random ?>"
        id="peepso_postbox_group_<?php echo $random ?>_group" value="group" <?php if($category_id) { echo ' checked="checked" '; } ?>/>
      <label class="ps-checkbox__label" for="peepso_postbox_group_<?php echo $random ?>_group">
        <span><?php echo __('A group', 'groupso') ?></span>
      </label>
    </div>

    <div class="ps-postbox__groups-search ps-js-group-finder">
      <input type="text" class="ps-input ps-input--sm" name="query" value=""
          placeholder="<?php echo __('Start typing to search...', 'groupso'); ?>" />

      <div class="ps-postbox__groups-view ps-js-result">
        <div class="ps-postbox__groups-list ps-js-result-list" style="max-height:35vh; overflow:auto;"></div>
        <script type="text/template" class="ps-js-result-item-template">
          {{ var __can_pin_posts = data.groupuserajax && data.groupuserajax.can_pin_posts ? 1 : 0 }}
          <div class="ps-postbox__groups-item" data-id="{{= data.id }}" data-name="{{= data.name }}"
              data-can-pin-posts="{{= __can_pin_posts }}">
            <div class="ps-postbox__groups-item-header">
              <div class="ps-postbox__groups-item-name">{{= data.name }}</div>
              {{ if ( data.privacy ) { /**/ }}
              <div class="ps-postbox__groups-item-privacy">
                  <i class="{{= data.privacy.icon }}"></i>
                  {{= data.privacy.name }}
              </div>
              {{ /**/ } }}
            </div>
            <!-- Limit the content to maximum 2 lines using a guide described here: https://css-tricks.com/almanac/properties/l/line-clamp/ -->
            <div class="ps-postbox__groups-item-desc" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
              <p>{{= data.description || '&nbsp;' }}</p>
            </div>
          </div>
        </script>
      </div>

      <div class="ps-loading ps-js-loading">
        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
      </div>
    </div>
  </a>
</div>
