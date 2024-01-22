<?php

$random = rand();
$time_format = get_option('time_format');
$ampm = preg_match('/[gh]/', $time_format);

?><div class="ps-dropdown__menu ps-postbox__schedule ps-js-postbox-dropdown ps-js-postbox-schedule" style="display:none">
    <a role="menuitem" class="ps-postbox__schedule-option" data-option-value="now">
        <div class="ps-checkbox">
            <input class="ps-checkbox__input" type="radio" name="peepso_postbox_schedule_<?php echo $random ?>" id="peepso_postbox_schedule_<?php echo $random ?>_now" value="now" checked>
            <label class="ps-checkbox__label" for="peepso_postbox_schedule_<?php echo $random ?>_now">
                <?php echo __('Post immediately', 'peepso-core') ?>
            </label>
        </div>
    </a>
    <a role="menuitem" class="ps-postbox__schedule-option" data-option-value="future">
        <div class="ps-checkbox">
            <input class="ps-checkbox__input" type="radio" name="peepso_postbox_schedule_<?php echo $random ?>" id="peepso_postbox_schedule_<?php echo $random ?>_future" value="future">
            <label class="ps-checkbox__label" for="peepso_postbox_schedule_<?php echo $random ?>_future">
                <?php echo __('Select date and time', 'peepso-core') ?>
            </label>
        </div>
        <div class="ps-postbox__schedule-calendar ps-js-datetime">
            <div class="ps-postbox__schedule-form">
                <div class="ps-postbox__schedule-label"><?php echo __('Date', 'peepso-core') ?></div>
                <div class="ps-postbox__schedule-date">
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-date-dd"></select>
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-date-mm"></select>
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-date-yy"></select>
                </div>
                <div class="ps-postbox__schedule-label"><?php echo __('Time', 'peepso-core') ?></div>
                <div class="ps-postbox__schedule-time">
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-time-hh"></select>
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-time-mm" data-interval="<?php echo apply_filters('peepso_postbox_schedule_interval_mm', 15); ?>"></select>
                    <?php if ($ampm) { ?>
                    <select class="ps-input ps-input--sm ps-input--select ps-postbox__schedule-select ps-js-time-ampm"></select>
                    <?php } ?>
                </div>
                <div class="ps-postbox__schedule-actions">
                    <button class="ps-btn ps-btn--sm ps-btn--cp ps-btn--action ps-js-done"><?php echo __('Done', 'peepso-core') ?></button>
                </div>
            </div>
        </div>
    </a>
</div>
