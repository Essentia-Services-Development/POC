<?php

class PeepSo3_Users_Utils
{

    private static $instance;

    public static function get_instance()
    {
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }

    private function __construct()
    {
        add_filter('peepso_filter_display_name_styles', function ($options) {

            return [
                'real_name' => __('Full name', 'peepso-core'),
                'real_name_first' => __('First name', 'peepso-core'),
                'real_name_first_last_initial' => __('First name + last name initial', 'peepso-core'),
                'username' => __('Username', 'peepso-core'),
            ];
        });

        // Delete SVG avatar when profile is saved
        add_action('peepso_action_profile_field_save', function($field) {
            PeepSo3_Users_Utils::svg_avatar_delete($field->prop('user_id'));
        },10,1);

        // Delete SVG avatar if username is changed
        add_action('peepso_profile_after_save', function($user_id) {
            PeepSo3_Users_Utils::svg_avatar_delete($user_id);
        },10,1);

        // AJAX preview
        add_action('wp_ajax_peepso_name_based_avatars_preview', function() {

            $response = ['success'=> FALSE, 'message'=>'You are not allowed to do that'];

            if(PeepSo::is_admin()) {
                $PeepSoInput = new PeepSo3_Input();
                $config_background = $PeepSoInput->int('background_color');
                $config_background_grayscale = $PeepSoInput->int('background_grayscale');
                $config_font = $PeepSoInput->int('font_color');

                $letters = [0 => 'AB', 1 => 'CD', 2 => 'EF'];

                $letters = [];

                for($i=0;$i<=2;$i++) {

                    $seed = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                    shuffle($seed);
                    $rand = '';
                    foreach (array_rand($seed, 2) as $k) $rand .= $seed[$k];

                    $letters[$i] = $rand;
                }

                foreach ($letters as $id => $letters) {
                    // Delete old preview
                    $cache_path = get_option('peepso_name_based_avatar_preview_path_'.$id, '');
                    if (strlen($cache_path) && file_exists($cache_path)) {
                        @unlink($cache_path);
                    }

                    $randomize = ($config_background == 0 || $config_background == 255 || $config_background_grayscale) ? 0 : 50;
                    $color = "rgb($config_font,$config_font,$config_font)";

                    if ($config_background_grayscale) {
                        $background = "rgb($config_background,$config_background,$config_background)";
                    } else {
                        $background = "#" . PeepSo3_Utilities_String::hex_color_from_string('Lorem Ipsum', $config_background_grayscale, $randomize, $config_background);
                    }

                    $svg = '<svg width="500px" height="500px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><style type="text/css">@font-face {font-family: "montserratbold";src: url("https://cdn.peepso.com/fonts/montserrat-bold-webfont.woff") format("woff");font-weight: normal;font-style: normal;}</style></defs><rect x="0" y="0" width="500" height="500" style="fill:' . $background . '"/><text x="50%" y="50%" dy=".1em" fill="' . $color . '" text-anchor="middle" dominant-baseline="middle" style="font-family: &quot;Montserrat&quot;, sans-serif; font-size: 250px; line-height: 1">' . $letters . '</text></svg>';

                    $svg_dir = 'avatars-svg';
                    @mkdir(PeepSo::get_peepso_dir() . $svg_dir);
                    $file = $svg_dir . '/preview_'.$id.'_' . md5(microtime() . $config_background . $config_background_grayscale . $config_font) . '.svg';
                    $path = PeepSo::get_peepso_dir() . $file;
                    $url = PeepSo::get_peepso_uri() . $file;

                    $h = fopen($path, 'w');
                    fwrite($h, $svg);
                    fclose($h);

                    update_option('peepso_name_based_avatar_preview_path_'.$id, $path);

                    $response['url'][$id] = $url;
                    $response['success'] = TRUE;
                    unset($response['message']);
                }
            }

            die( json_encode($response));
        });


        // Store a string of Name Based Avatar settings to invalidate avatar cache if settings are changed
        add_action('peepso_config_after_save-appearance', function() {
            $avatar_config = [PeepSo::get_option_new('avatars_name_based'),PeepSo::get_option_new('avatars_name_based_background_color'),PeepSo::get_option_new('avatars_name_based_background_grayscale'),PeepSo::get_option_new('avatars_name_based_font_color')];
            array_walk($avatar_config, 'intval');
            $avatar_config = implode(',', $avatar_config);
            update_option('peepso_name_based_avatars_config', $avatar_config);
        });

        // Implement email allowlist / blocklist
        add_filter('peepso_register_valid_email', function($valid, $email) {

            #6591 If email is empty (for example registering via SLAI) theres nothing to check
            if(!strlen($email)) {
                return $valid;
            }

            $email_domain = substr($email, strpos($email, '@') + 1);

            #6591 If email is empty (for example registering via SLAI) theres nothing to check
            if(!strlen($email_domain)) {
                return $valid;
            }

            if (PeepSo::get_option('limitusers_blacklist_domain_enable', FALSE)) {
                $blacklist = str_replace("\r", '', PeepSo::get_option('limitusers_blacklist_domain'));
                $blacklist = explode("\n", $blacklist);

                if( count($blacklist) ) {
                    # since 5.2.2.0 
                    # we should apply this to subdomain
                    foreach ($blacklist as $key => $domain) {
                        if (strlen(str_replace(' ', '', trim($domain))) == 0) {
                            continue;
                        }

                        if (stripos($email_domain, $domain) !== false) {
                            return false;
                        }
                    }
                }
            }

            if (PeepSo::get_option('limitusers_whitelist_domain_enable', FALSE)) {
                $whitelist = str_replace("\r", '', PeepSo::get_option('limitusers_whitelist_domain'));
                $whitelist = explode("\n", $whitelist);

                if( count($whitelist) ) {
                    if (in_array($email_domain, $whitelist)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }

            return $valid;
        }, 10, 2);
    }

    public static function svg_avatars_config_string() {
        return get_option('peepso_name_based_avatars_config');
    }

    /**
     * @param null $user_id
     * @param null $name - if passed, user id will be ignored. Good enough for Groups.
     * @return string
     */
    public static function svg_avatar($user_id = NULL, $name = NULL, $forced = FALSE) {

        if($forced) {
            self::svg_avatar_delete();
        }

        if(NULL == $name) {
            if (NULL == $user_id) {
                $user_id = get_current_user_id();
            }

            $PeepSoUser = PeepSoUser::get_instance($user_id);
            $name = $PeepSoUser->get_fullname();
        }

        $current_config = PeepSo3_Users_Utils::svg_avatars_config_string();
        $cache_config = get_user_option('peepso_name_based_avatars_config', $user_id);
        $cache_user_url = get_user_option('peepso_name_based_avatar_url', $user_id);
        $cache_user_path = get_user_option('peepso_name_based_avatar_path', $user_id);

        // If the avatar is already generated and the config was not changed
        if(strlen($cache_user_url) && strlen($cache_user_path) && file_exists($cache_user_path) && $cache_config == $current_config) {
            // new PeepSoError("User $user_id returning cached avatar");
            return $cache_user_url;
        } else {

            // new PeepSoError("User $user_id generating avatar");

            // Generating new avatar, attempt to delete the old one
            self::svg_avatar_delete($user_id);

            $config_background_grayscale = PeepSo::get_option_new('avatars_name_based_background_grayscale');
            $config_background = PeepSo::get_option_new('avatars_name_based_background_color');
            $randomize = ($config_background == 0 || $config_background == 255 || $config_background_grayscale) ? 0 : 50;
            $config_font = PeepSo::get_option_new('avatars_name_based_font_color');
            $color = "rgb($config_font,$config_font,$config_font)";

            if ($config_background_grayscale) {
                $background = "rgb($config_background,$config_background,$config_background)";
            } else {
                $background = "#" . PeepSo3_Utilities_String::hex_color_from_string($name, $config_background_grayscale, $randomize, $config_background);
            }

            $name = PeepSo3_Utilities_String::maybe_mb_ucwords($name);

            if (strstr($name, " ")) {
                $name_array = explode(" ", $name);
                $letters = PeepSo3_Utilities_String::maybe_mb_substr($name_array[0], 0, 1);
                end($name_array);
                $key = key($name_array);
                $letters .= PeepSo3_Utilities_String::maybe_mb_substr($name_array[$key], 0, 1);
            } else {
                $letters = PeepSo3_Utilities_String::maybe_mb_substr($name, 0, 1);
            }

            $svg = '<svg width="500px" height="500px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><defs><style type="text/css">@font-face {font-family: "montserratbold";src: url("https://cdn.peepso.com/fonts/montserrat-bold-webfont.woff2") format("woff2"),url("https://cdn.peepso.com/fonts/montserrat-bold-webfont.woff") format("woff");font-weight: normal;font-style: normal;}</style></defs><rect x="0" y="0" width="500" height="500" style="fill:' . $background . '"/><text x="50%" y="50%" dy=".1em" fill="' . $color . '" text-anchor="middle" dominant-baseline="middle" style="font-family: &quot;Montserrat&quot;, sans-serif; font-size: 250px; line-height: 1">' . $letters . '</text></svg>';

            $svg_dir = 'avatars-svg';
            @mkdir(PeepSo::get_peepso_dir() . $svg_dir);
            $file = $svg_dir . '/' . md5($name . $config_background . $config_background_grayscale . $config_font) . '.svg';
            $path = PeepSo::get_peepso_dir() . $file;
            $url = PeepSo::get_peepso_uri() . $file;

            $h = fopen($path, 'w');
            fwrite($h, $svg);
            fclose($h);

            update_user_option($user_id, 'peepso_name_based_avatars_config', $current_config);
            update_user_option($user_id, 'peepso_name_based_avatar_url', $url);
            update_user_option($user_id, 'peepso_name_based_avatar_path', $path);
        }

        return $url;
    }

    public static function svg_avatar_delete(int $user_id) {
        $cache_user_path = get_user_option('peepso_name_based_avatar_path', $user_id);

        if(strlen($cache_user_path)) {
            @unlink($cache_user_path);
        }

        delete_user_option($user_id, 'peepso_name_based_avatars_config');
        delete_user_option($user_id, 'peepso_name_based_avatar_url');
        delete_user_option($user_id, 'peepso_name_based_avatar_path');
    }
}

PeepSo3_Users_Utils::get_instance();