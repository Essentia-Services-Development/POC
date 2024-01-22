<?php

class PeepSoNotificationsIntensity {

    /**
     * Each intensity level key is the amount of minutes that needs to pass between emails
     * @return array
     */
    public static function email_notifications_intensity_levels() {
        $levels = array(
            0 => array(
                'label' => __('Real time', 'peepso-core'),
                'schedule' => 'realtime',
                'desc'=>__('All enabled email notifications will be sent out immediately.','peepso-core'),
            ),

            60 => array(
                'label' => __('One hour', 'peepso-core'),
                'desc'=>sprintf(__('You will receive a summary of unread on-site notifications approximately %s.','peepso-core'), __('every hour','peepso-core')),
            ),

            120 => array(
                'label' => __('Two hours', 'peepso-core'),
                'desc'=>sprintf(__('You will receive a summary of unread on-site notifications approximately %s.','peepso-core'), __('every two hours','peepso-core')),
            ),


            180 => array(
                'label' => __('Three hours', 'peepso-core'),
                'desc'=>sprintf(__('You will receive a summary of unread on-site notifications approximately %s.','peepso-core'), __('every three hours','peepso-core')),
            ),

            360 => array(
                'label' => __('Four times a day', 'peepso-core'),
                'desc'=>sprintf(__('You will receive a summary of unread on-site notifications approximately %s.','peepso-core'), __('four times a day','peepso-core')),
            ),

            720 => array(
                'label' => __('Two times a day', 'peepso-core'),
                'desc'=>sprintf(__('You will receive a summary of unread on-site notifications approximately %s.','peepso-core'), __('two times a day','peepso-core')),
            ),


            1440 => array(
                'label' => __('Once a day', 'peepso-core'),
                'desc'=>sprintf(__('You will receive a summary of unread on-site notifications approximately %s.','peepso-core'), __('once a day','peepso-core')),
            ),

            10080 => array(
                'label' => __('Once in 7 days', 'peepso-core'),
                'desc'=>sprintf(__('You will receive a summary of unread on-site notifications approximately %s.','peepso-core'), __('once a week','peepso-core')),
            ),

            999999 => array(
                'label' => __('Never', 'peepso-core'),
                'schedule' => 'disabled',
                'desc'=>sprintf(__('You will not receive any email notifications.','peepso-core'), __('hour','peepso-core')),
            ),
        );

        $levels = apply_filters('peepso_filter_user_email_notification_intensity_levels', $levels);

        ksort($levels);
        return $levels;
    }

    public static function user_email_notifications_intensity($user_id = 0) {

        if(!$user_id) {
            $user_id = get_current_user_id();
        }

        if(!$user_id) { return FALSE; }

        $levels = PeepSoNotificationsIntensity::email_notifications_intensity_levels();

        $email_preference = get_user_meta($user_id, 'peepso_email_intensity', TRUE);
        if(!is_numeric($email_preference)) {
            // on fresh install default_email_intensity is empty string
            // we should convert it to intval
            $email_preference = intval(PeepSo::get_option_new('default_email_intensity'));
            update_user_meta($user_id, 'peepso_email_intensity', $email_preference);
        }

        // if level is missing, pick the next one in queue
        if(!isset($levels[$email_preference])) {
            for($i=$email_preference;$i<=999999;$i++) {
                if(isset($levels[$i])) {
                    $email_preference = $i;
                    break;
                }
            }
        }

        return $email_preference;
    }


}