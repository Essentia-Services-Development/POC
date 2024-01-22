<?php
$user = PeepSoUser::get_instance(PeepSoProfileShortcode::get_instance()->get_view_user_id());

$can_edit = FALSE;
if($user->get_id() == get_current_user_id() || current_user_can('edit_users')) {
    $can_edit = TRUE;
}

if(!$can_edit) {
    PeepSo::redirect(PeepSo::get_page('activity'));
} else {

    $PeepSoProfile = PeepSoProfile::get_instance();

    if(isset($_GET['test'])) {
        $PeepSoNotificationsQueue= PeepSoNotificationsQueue::get_instance();
        $PeepSoNotificationsQueue->debug();
    }

    $other_notifications = $PeepSoProfile->get_notification_form_fields();

    ?>

    <div class="peepso">
    <div class="ps-page ps-page--profile ps-page--profile-notifications">
        <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

        <div class="ps-profile ps-profile--edit ps-profile--notifications">
            <?php PeepSoTemplate::exec_template('profile', 'focus', array('current'=>'about')); ?>

            <div class="ps-profile__edit">
                <?php if($can_edit) { PeepSoTemplate::exec_template('profile', 'profile-about-tabs', array('tabs' => $tabs, 'current_tab'=>'notifications'));} ?>

                <div class="ps-profile__notifications">
                    <!-- Notifications intensity -->
                    <div class="ps-profile__notifications-row" id="peepso_email_intensity_container">
                        <?php if(count($other_notifications) > 0) { ?>
                        <h2 class="ps-profile__notifications-title"><?php echo __('Community notifications','peepso-core');?></h2>
                        <?php } ?>
                        <div class="ps-profile__notifications-row-title">
                            <?php echo __('Email notification intensity','peepso-core');?>
                        </div>

                        <?php
                        $levels = PeepSoNotificationsIntensity::email_notifications_intensity_levels();
                        $email_preference = PeepSoNotificationsIntensity::user_email_notifications_intensity();
                        ?>
                        <div class="ps-profile__notifications-row-desc">
                            <select class="ps-input ps-input--sm ps-input--select" name="email_intensity" id="peepso-email-intensity">
                                <?php foreach($levels as $key => $level) { ?>
                                    <option <?php if($key == $email_preference) { echo 'selected';}?> value="<?php echo $key;?>"><?php echo $level['label']; ?></option>
                                <?php } ?>
                            </select>

                            <span class="ps-form__check ps-js-loading">
    								<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
    								<i class="gcis gci-check"></i>
    							</span>
                        </div>

                        <div class="ps-profile__notifications-row-data" id="peepso_email_intensity_descriptions">
                            <?php foreach($levels as $key => $level) { ?>
                                <div class="ps-alert" id="peepso_email_intensity_<?php echo $key;?>" <?php if($key!=$email_preference) { echo 'style="display:none;"';}?>>
                                    <p>
                                        <?php

                                        echo $level['desc'];
                                        if(count($other_notifications) > 0) {
                                            echo '<br/>' . __('This setting does not affect "other notifications"', 'peepso-core') . '.';
                                        }
                                        ?>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <!-- <div class="ps-profile__notifications-row">
    						<div class="ps-profile__notifications-row-title">
    							<?php echo __('Notification preferences','peepso-core');?>
    						</div>

    						<div class="ps-profile__notifications-row-desc">
    							<p>
    								<?php
                    echo __('Email notifications require an on-site notification enabled.', 'peepso-core');
                    echo ' ' . sprintf(__('You can also use %s to quickly manage all notifications.', 'peepso-core'), '<a href="#shortcuts">'.__('shortcuts','peepso-core').'</a>');
                    ?>
    							</p>
    						</div>

    						<?php $PeepSoProfile->preferences_form_fields('notifications', TRUE); ?>
    						<?php
                    /**
                     * @deprecated
                     *
                     * This action hook was used to add the notification settings for groups.
                     * We are now using `peepso_profile_alerts` filter hook to make it consistent with other plugins.
                     */
                    do_action('peepso_render_profile_about_notifications_after');
                    ?>
    					</div> -->

                    <!-- Shortcuts -->
                    <div class="ps-profile__notifications-row" id="shortcuts">
                        <div class="ps-profile__notifications-row-title">
                            <?php echo __('Shortcuts','peepso-core');?>
                        </div>

                        <div class="ps-profile__notifications-row-desc">
                            <p>
                                <?php echo __('Quickly manage all your preferences at once.', 'peepso-core');?>
                            </p>
                        </div>

                        <?php
                        $is_realtime = TRUE;
                        if (isset($email_preference) && $email_preference > 0) {
                            $is_realtime = FALSE;
                        }
                        ?>

                        <div class="ps-profile__notifications-shortcuts ps-btn__group ps-btn__group--full" role="menu">
                            <a class="ps-profile__notifications-shortcut ps-btn ps-btn--xs ps-js-preferences-button" role="menuitem"
                               data-action="enable"
                               data-context="<?php echo isset($context) ? isset($context) : '';?>"
                               data-type="all"
                               href="<?php echo admin_url('admin-ajax.php?action=peepso_user_subscribe_all&_wpnonce=' . wp_create_nonce('peepso-user-subscribe-all') .'&redirect') ?>">
                                <?php echo __('Enable all', 'peepso-core');?>
                            </a>

                            <a class="ps-profile__notifications-shortcut ps-btn ps-btn--xs ps-js-preferences-button" role="menuitem"
                               href="<?php echo admin_url('admin-ajax.php?action=peepso_user_unsubscribe_onsite&_wpnonce=' . wp_create_nonce('peepso-user-unsubscribe-onsite') .'&redirect') ?>"
                               data-action="disable"
                               data-type="all">
                                <?php echo __('Disable all', 'peepso-core');?>
                            </a>

                            <a class="ps-profile__notifications-shortcut ps-btn ps-btn--xs ps-js-preferences-button" role="menuitem"
                               data-action="disable"
                               data-type="email"
                               style="<?php echo $is_realtime ? '' : 'display:none'; ?>"
                               href="<?php echo admin_url('admin-ajax.php?action=peepso_user_unsubscribe_emails&_wpnonce=' . wp_create_nonce('peepso-user-unsubscribe-emails') .'&redirect')?>">
                                <?php echo __('Disable emails', 'peepso-core');?>
                            </a>

                            <?php
                            do_action('peepso_render_notifications_preferences_shortcuts', $is_realtime);
                            ?>

                            <a class="ps-profile__notifications-shortcut ps-btn ps-btn--xs ps-js-preferences-button" role="menuitem"
                               data-action="reset"
                               href="<?php echo admin_url('admin-ajax.php?action=peepso_user_reset_notifications&_wpnonce=' . wp_create_nonce('peepso-user-reset-notifications') .'&redirect') ?>">
                                <?php echo __('Reset to default', 'peepso-core');?>
                            </a>
                        </div>
                    </div>

                    <div class="ps-profile__notifications-row">
                        <div class="ps-profile__notifications-row-title">
                            <?php echo __('All notifications','peepso-core');?>
                        </div>

                        <div class="ps-profile__notifications-row-desc">
                            <p>
                                <?php echo __('Email notifications require an on-site notification enabled.', 'peepso-core');?>
                            </p>
                        </div>

                        <div class="ps-profile__notifications-list ps-js-profile-list">
                            <?php $PeepSoProfile->preferences_form_fields('notifications', TRUE); ?>
                            <?php
                            /**
                             * @deprecated
                             *
                             * This action hook was used to add the notification settings for groups.
                             * We are now using `peepso_profile_alerts` filter hook to make it consistent with other plugins.
                             */
                            do_action('peepso_render_profile_about_notifications_after');
                            ?>
                        </div>
                    </div>

                    <!-- Web Push Notifications -->
                    <?php if(PeepSo::is_dev_mode('web_push') && PeepSo::get_option('web_push')) { ?>
                    <div class="ps-profile__notifications-row" id="shortcuts">
                        <div class="ps-profile__notifications-row-title">
                            <?php echo __('Web Push Notifications','peepso-core');?>
                        </div>

                        <?php
                        $web_push = PeepSo3_Web_Push::user_web_push();
                        ?>

                        <div class="ps-profile__notifications-list-item">
                            <div class="ps-form__field">
                                <div class="ps-profile__notification ps-preferences__notification">
                                    <label for="ps-js-opt-browser-push" class="ps-profile__notification-label">
                                        <?php echo __('Receive Web Push Notifications in your browser for all enabled on-site notifications.', 'peepso-core'); ?>
                                        <span class="ps-form__check ps-js-loading">
												<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif') ?>" />
												<i class="gcis gci-check"></i>
											</span>
                                    </label>
                                    <div class="ps-profile__notification-checkbox ps-preferences__checkbox">
											<span>
												<div class="ps-checkbox">
													<input type="checkbox" class="ps-checkbox__input" id="ps-js-opt-browser-push"
                                                           name="web_push" value="1" <?php if(1 == $web_push) { echo 'checked'; } ?> />
													<label class="ps-checkbox__label" for="ps-js-opt-browser-push"></label>
												</div>
											</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <?php if(count($other_notifications) > 0) {?>
                        <div class="ps-profile__notifications ps-profile__notifications--other">
                            <div class="ps-profile__notifications-row">
                                <div class="ps-profile__list ps-js-profile-list">
                                    <h2 class="ps-profile__notifications-title"><?php echo __('Other notifications','peepso-core');?></h2>
                                    <?php $PeepSoProfile->notifications_form_fields(); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div id="ps-dialogs" style="display:none">
        <?php PeepSoActivity::get_instance()->dialogs(); // give add-ons a chance to output some HTML ?>
        <?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
    </div>
<?php }
