<?php

$random = rand();
$time_format = get_option('time_format');
$ampm = preg_match('/[gh]/', $time_format);

?><div class="ps-dropdown__menu ps-postbox__pin ps-js-postbox-dropdown ps-js-postbox-pin" style="display:none">
    <a role="menuitem" class="ps-postbox__pin-option" data-option-value="no">
        <div class="ps-checkbox">
            <input class="ps-checkbox__input" type="radio" name="peepso_postbox_pin_<?php echo $random ?>" id="peepso_postbox_pin_<?php echo $random ?>_no" value="0" checked>
            <label class="ps-checkbox__label" for="peepso_postbox_pin_<?php echo $random ?>_no">
                <?php echo __('Do not pin', 'peepso-core') ?>
            </label>
        </div>
    </a>
    <a role="menuitem" class="ps-postbox__pin-option" data-option-value="indefinitely">
        <div class="ps-checkbox">
            <input class="ps-checkbox__input" type="radio" name="peepso_postbox_pin_<?php echo $random ?>" id="peepso_postbox_pin_<?php echo $random ?>_indefinitely" value="1">
            <label class="ps-checkbox__label" for="peepso_postbox_pin_<?php echo $random ?>_indefinitely">
                <?php echo __('Pin indefinitely', 'peepso-core') ?>
            </label>
        </div>
    </a>
    <a role="menuitem" class="ps-postbox__pin-option" data-option-value="until">
        <div class="ps-checkbox">
            <input class="ps-checkbox__input" type="radio" name="peepso_postbox_pin_<?php echo $random ?>" id="peepso_postbox_pin_<?php echo $random ?>_until" value="2">
            <label class="ps-checkbox__label" for="peepso_postbox_pin_<?php echo $random ?>_until">
                <?php echo __('Pin until...', 'peepso-core') ?>
            </label>
        </div>
        <div class="ps-postbox__pin-calendar ps-js-datetime">
            <div class="ps-postbox__pin-form">
                <div class="ps-postbox__pin-label"><?php echo __('Date', 'peepso-core') ?></div>
                <div class="ps-postbox__pin-date">
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-dd"></select>
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-mm"></select>
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-date-yy"></select>
                </div>
                <div class="ps-postbox__pin-label"><?php echo __('Time', 'peepso-core') ?></div>
                <div class="ps-postbox__pin-time">
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-hh"></select>
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-mm" data-interval="<?php echo apply_filters('peepso_postbox_pin_interval_mm', 15); ?>"></select>
                    <?php if ($ampm) { ?>
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__pin-select ps-js-time-ampm"></select>
                    <?php } ?>
                </div>
                <div class="ps-postbox__pin-actions">
                    <button class="ps-btn ps-btn--sm ps-btn--cp ps-btn--action ps-js-done"><?php echo __('Done', 'peepso-core') ?></button>
                </div>
            </div>
        </div>
    </a>
</div>
