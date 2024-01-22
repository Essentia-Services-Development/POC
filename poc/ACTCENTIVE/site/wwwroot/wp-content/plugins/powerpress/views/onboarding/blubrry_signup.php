<?php
require_once(POWERPRESS_ABSPATH .'/powerpressadmin-auth.class.php');
$auth = new PowerPressAuth();
$General = powerpress_get_settings('powerpress_general');
if (!isset($_REQUEST['_wpnonce'])) {
    powerpress_page_message_add_error(__('Invalid link', 'powerpress'));
    powerpress_page_message_print();
    exit;
}
if (wp_verify_nonce($_REQUEST['_wpnonce'], 'powerpress-link-blubrry')) {
    // show data
    $FeedSettings = get_option('powerpress_feed_podcast');

    // First step--send data to the onboarding_start endpoint then redirect to the returned session_url
    if (isset($_GET['onboarding_type'])) {
        // add vars to the post array
        $post = array();

        // random identifier
        $session_uuid = round(time() / rand(0, 999), 0);
        $post['add_show_uuid'] = $session_uuid;

        // website, feed, and admin url
        $wp_admin_url = add_query_arg('_wpnonce', $_REQUEST['_wpnonce'], admin_url());
        $post['wp_admin_url'] = urlencode($wp_admin_url);
        $feed_url = urlencode(get_feed_link('podcast'));
        $post[ 'feed_url'] = $feed_url;
        $website_url = urlencode(get_home_url());
        $post['website_url'] = $website_url;

        // hosting or stats
        $onboarding_type = $_GET['onboarding_type'];
        $post['onboard_type'] = $onboarding_type;

        if (isset($FeedSettings['title'])) {
            $post['show_title'] = urlencode($FeedSettings['title']);
        }
        if (isset($FeedSettings['apple_cat_1'])) {
            $post['apple_category'] = urlencode($FeedSettings['apple_cat_1']);
        }
        if (isset($FeedSettings['itunes_explicit'])) {
            if($FeedSettings['itunes_explicit']) {
                $post['explicit'] = 'true';
            } else {
                $post['explicit'] = 'false';
            }
        }
        if (isset($FeedSettings['itunes_image'])) {
            $post['artwork'] = urlencode($FeedSettings['itunes_image']);
        }

        // make the API call
        $result = $auth->api(false, '/2/show/start', $post, false, 15, true, true);
        if (!is_array($result)) {
            powerpress_page_message_add_error(__('An unknown error occurred linking your account.', 'powerpress'));
            powerpress_page_message_print();
            exit;
        }

        if (!empty($result['error'])) {
            powerpress_page_message_add_error(__(print_r($result, true), 'powerpress'));
            powerpress_page_message_print();
            exit;
        }

        if (!isset($result['add_show_token']) || !isset($result['session_url'])) {
            powerpress_page_message_add_error(__('An unknown error occurred linking your account.', 'powerpress'));
            powerpress_page_message_print();
            exit;
        }
        $props = array();
        $props['add_show_uuid'] = $session_uuid;
        $props['add_show_token'] = $result['add_show_token'];
        powerpress_save_settings($props, 'powerpress_onboarding');
        $publisher_origin = '';
        if (strpos($result['session_url'], 'publish.blubrry') === false ) {
            if (defined('POWERPRESS_BLUBRRY_API_URL')) {
                $origin_array = explode('.', POWERPRESS_BLUBRRY_API_URL);
                $origin_array[0] = str_replace('api', 'publish', $origin_array[0]);
                $publisher_origin = implode('.', $origin_array);
                $publisher_origin = rtrim($publisher_origin, '/');
            } else {
                $publisher_origin = 'https://publish.blubrry.com';
            }
        }
        echo '<script>window.location.href = "' . $publisher_origin . $result['session_url'] . '&add_show_uuid=' . $session_uuid . '&add_show_token=' . $result['add_show_token'] . '";</script>';


    } elseif (isset($_GET['return_code'])) {
        $code = $_GET['return_code'];
        $onboarding_creds = get_option('powerpress_onboarding');
        if (isset($onboarding_creds['add_show_uuid']) && isset($onboarding_creds['add_show_token'])) {
            // check that this is the correct session
            if ($_GET['token'] != $onboarding_creds['add_show_token']) {
                echo "Incorrect session for this site";
                exit;
            }

            // add vars to the post array
            $post = $onboarding_creds;
            $post['return_code'] = $code;
            $post['need_client'] = true;

            // make the api call
            $result = $auth->api(false, '/2/show/link', $post, false, 15, true, true);

            if (!is_array($result)) {
                powerpress_page_message_add_error(__('An unknown error occurred linking your Blubrry account.', 'powerpress'));
                powerpress_page_message_print();
                exit;
            }

            if (!empty($result['error'])) {
                powerpress_page_message_add_error(__($result['error'], 'powerpress'));
                powerpress_page_message_print();
                exit;
            }

            // save the credentials
            $props = array();
            $props['code'] = $code;
            $props['client_id'] = $result['client_id'];
            $props['client_secret'] = $result['client_secret'];
            $props['access_token'] = $result['access_token'];
            $props['refresh_token'] = $result['refresh_token'];
            // access tokens last an hour, but we'll use a refresh token if it expires in the next ten seconds
            $props['access_expires'] = (time() + 3590);
            powerpress_save_settings($props, 'powerpress_creds');

            // set the default program
            $General['blubrry_program_keyword'] = $result['show_keyword'];
            $General['blubrry_hosting'] = $result['blubrry_hosting'];
            powerpress_save_settings($General, 'powerpress_general');

            // save the author name for the itunes:author tag
            if (!empty($result['itunes_talent_name'])) {
                $FeedSettings['itunes_talent_name'] = $result['itunes_talent_name'];
                powerpress_save_settings($FeedSettings, 'powerpress_feed_podcast');
            }

            // set the stats redirect
            powerpress_add_blubrry_redirect($result['show_keyword']);

            // check whether the account is verified & save result
            // logic in powerpressadmin.php will display a verify message if necessary
            $result = $auth->checkAccountVerified();
            if (!empty($result['account_enabled']) && !empty($result['account_confirmed'])) {
                $props['account_verified'] = true;
            } else {
                $props['account_verified'] = false;
            }
            powerpress_save_settings($props, 'powerpress_creds');

            // if they have episodes that are not hosted by Blubrry, continue to the migrate page
            if (!empty($General['blubrry_hosting']) && powerpress_admin_episodes_not_hosted() > 0) {
                echo '<script>window.location.href = "' . admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php") . '"</script>';
            } else {
                // otherwise, continue to the last page of onboarding
                echo '<script>window.location.href = "' . admin_url("admin.php?page={$_GET['page']}&step=createEpisode") . '";</script>';
            }
        }
    } else {
        powerpress_page_message_add_error(__('An unknown error occurred linking your Blubrry account.', 'powerpress'));
        exit;
    }

} else {
    powerpress_page_message_add_error(__('Invalid link', 'powerpress'));
    powerpress_page_message_print();
    exit;
}