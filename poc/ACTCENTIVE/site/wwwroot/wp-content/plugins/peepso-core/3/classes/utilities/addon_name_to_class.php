<?php

// Used to check what PHP class is responsible for an addon
class PeepSo3_Utility_Addon_Class {
    public static function _($name) {
        $name = strtolower($name);
        $name = preg_replace("/[^A-Za-z]/", '', $name);
        $map = [
            // Foundation
            'gecko'        => 'GeckoConfigSettings',

            // Core
            'audiovideo' => 'PeepSoVideos',
            'chat'          => 'PeepSoMessagesPlugin',
            'friends'       => 'PeepSoFriendsPlugin',
            'groups'        => 'PeepSoGroupsPlugin',
            'photos'        => 'PeepSoSharePhotos',

            // Early Access
            'earlyaccess'  => 'PeepSoEarlyAccessPlugin',

            // Extras
            'emaildigest'  => 'PeepSoEmailDigest',
            'userlimits'   => 'peepsolimitusers',

            // Integrations
            'badgeos'       => 'BadgeOS_PeepSo',
            'mycred'        => 'PeepSoMyCreds',
            'mobileapp'     => 'PeepSoAppPlugin',
            'sociallogininvitations'  => 'PeepSoSocialLoginPlugin',

            // E-learning
            'tutor'         => 'PeepSoTutorLMSPlugins',
            'learndash'    => 'PeepSoLearnDash',

            // Monetization
            'advancedads'  => 'PeepSoAdvancedAdsPlugin',
            'paidmembershipspro'           => 'PeepSoPMP',
            'wpadverts'     => 'PeepSoWPAdverts',
            'woocommerce'   => 'WBPWI_PeepSo_Woo_Integration',
        ];

        if(array_key_exists($name, $map)) {
            return $map[$name];
        }

        echo "<!--No class for $name-->";
        return FALSE;
    }

    public static function is_active($name) {
        if($class = self::_($name)) {
            if('polls' == $name) {
                return ( class_exists($class) || class_exists('PeepSoPollsPlugin') );
            }
            return class_exists($class);
        }

        return FALSE;
    }
}