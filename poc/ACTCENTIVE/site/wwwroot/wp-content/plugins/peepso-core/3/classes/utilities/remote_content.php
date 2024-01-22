<?php

class PeepSo3_Helper_Remote_Content
{

    public static function get($id, $cache = TRUE, $params =[])
    {
        $value = FALSE;

        if(isset($_REQUEST['peepso_remote_no_cache'])) {
            $cache = FALSE;
        }

        $ids = [
            // General upsell
            'upsell'                    => 'https://cdn.peepso.com/upsell/upsell.php',

            'free_bundle_license'       => 'https://cdn.peepso.com/upsell/peepso-free-bundle/license.txt',
            'free_bundle_terms'         => 'https://cdn.peepso.com/upsell/peepso-free-bundle/terms.html',
            'free_bundle_disabled_text' => 'https://cdn.peepso.com/upsell/peepso-free-bundle/admin-disabled-message.html',
            'free_bundle_branding'      => 'https://cdn.peepso.com/upsell/peepso-free-bundle/powered-by-peepso.php',

            'free_bundle_stream_ads_rules'    => 'https://cdn.peepso.com/upsell/peepso-free-bundle/stream-ads-rules.json',
            'free_bundle_stream_ads_content'    => 'https://cdn.peepso.com/upsell/peepso-free-bundle/stream-ads-content.php',

            'license_to_data'           => 'https://www.peepso.com',

            'old_version_notice'        => 'https://cdn.peepso.com/upsell/old_version_warning.php',
        ];

        if (array_key_exists($id, $ids)) {

            $url = $ids[$id];

            // Repeated URLs with different params should be cached separately
            if(count($params)) {
                $id = $id.md5(serialize($params));
            }

            if ($cache) {
                $value = PeepSo3_Mayfly::get($id);
            }

            if (empty($value)) {

                // Attempt without sslverify
                $args = array('timeout' => 10, 'sslverify' => FALSE);
                if($params) {
                    $args['body'] = $params;
                }
                $resp = wp_remote_get(add_query_arg(array(), $url), $args);

                // In some cases sslverify is needed
                $args = array('timeout' => 10, 'sslverify' => TRUE);
                if($params) {
                    $args['body'] = $params;
                }
                if (is_wp_error($resp)) {
                    $resp = wp_remote_get(add_query_arg(array(), $url), $args);
                }

                if (is_wp_error($resp)) {
                    // Failure
                } else {
                    $value = $resp['body'];
                    PeepSo3_Mayfly::set($id, $value, 3600 * 24);
                }
            }
        }

        return $value;
    }
}