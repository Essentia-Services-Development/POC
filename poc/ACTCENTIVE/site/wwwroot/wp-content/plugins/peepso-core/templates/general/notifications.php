<?php

$profile_url = PeepSoUser::get_instance(get_current_user_id())->get_profileurl();
$notif_url = $profile_url . 'about/notifications/';

?>
<?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>
<?php if( get_current_user_id() ) { ?>
    <div class="ps-notifications-page ps-js-page-notifications">
        <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>
        <div class="ps-notifications-page__nav-wrapper">
            <div class="ps-btn__group">
                <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-notification-show-all" aria-label="<?php echo __('All', 'peepso-core'); ?>">
                    <i class="gcis gci-list-check"></i>
                </a>
                <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp  ps-tip ps-tip--arrow  ps-js-notification-show-unread" aria-label="<?php echo __('Unread', 'peepso-core'); ?>">
                    <i class="gcis gci-exclamation-circle"></i>
                </a>
            </div>
            <div class="ps-notifications__nav-actions">

                <a href="<?php echo $notif_url; ?>" class="ps-btn ps-btn--app ps-btn--sm  ps-tip ps-tip--arrow " aria-label="<?php echo __('Settings', 'peepso-core'); ?>">
                    <i class="gcis gci-gear"></i>
                </a>
                <a href="#" class="ps-btn ps-btn--app ps-btn--sm  ps-tip ps-tip--arrow  ps-js-notification-mark-all-as-read" aria-label="<?php echo __('Mark all as read', 'peepso-core'); ?>">
                    <i class="gcis gci-check-double"></i>
                </a>
            </div>
        </div>

        <div class="ps-notifications-page__list ps-js-page-notifications-list"></div>
        <div class="ps-posts__empty ps-js-page-notifications-alert" style="display:none"></div>
        <div class="ps-members__loading ps-js-page-notifications-triggerscroll">
            <img class="ps-loading post-ajax-loader ps-js-page-notifications-loading"
                 src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="<?php echo __('Loading', 'peepso-core'); ?>" />
        </div>
    </div>
<?php } ?>
