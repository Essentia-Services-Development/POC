<?php

class PeepSoAdminAddons extends PeepSoAjaxCallback
{

    public function check_license(PeepSoAjaxResponse $resp)
    {
        if (!PeepSo::is_admin()) {
            $resp->success(false);
            $resp->error(__('Insufficient permissions.', 'peepso-core'));
            return;
        }

        $input = new PeepSoInput();

        $peepso_bundle = [
            'free' => 64823085,
            'ultimate' => 18276205,
            'ultimate5' => 44808693,
            'monthly'   => 67105898,
            'ultimatemonthly'    => 45963636,
            'basic' => 30017488,
            'starter' => 30800413,
            'legacy'    => 2714533,
        ];

        $current_item_id = 0;
        $license = $input->value('license', '', FALSE);  // SQL Safe
        $license_changed = $input->value('license_changed', 0, [0,1]);

        if (!empty($license)) {
            PeepSoConfigSettings::get_instance()->set_option('bundle_license', $license);
        }


        $url = PeepSoLicense::PEEPSO_HOME;

        if(strlen($license)) {
            $current_item_id = PeepSo3_Helper_Addons::license_to_id($license, $license_changed);
//            foreach ($peepso_bundle as $key => $value) {
//                $api_params = [
//                    'edd_action' => 'activate_license',
//                    'license' => $license,
//                    'item_id' => $value,
//                    'url' => home_url(),
//                ];
//
//                $request = wp_remote_post($url, ['timeout' => 15, 'sslverify' => TRUE, 'body' => $api_params]);
//
//                if (is_wp_error($request)) {
//                    $request = wp_remote_post($url, ['timeout' => 15, 'sslverify' => FALSE, 'body' => $api_params]);
//                }
//
//                if (!is_wp_error($request)) {
//                    $info = json_decode(wp_remote_retrieve_body($request));
//
//                    if ($info->success === TRUE && in_array($info->license, ['valid', 'site_inactive'])) {
//                        $current_item_id = $value;
//                    }
//                }
//            }
        }

        $bundle_info = PeepSo3_Helper_Addons::get_addons();

        $new_products = $products = $classes = $can_install = [];

        // find products on bundle
        foreach ($bundle_info as $bundle) {
            if (strpos($bundle->name, 'Theme') !== FALSE) {
                $category = 'Theme';
                $product_name = 'Gecko Theme';
            } else if (strpos($bundle->name, 'Early Access') !== FALSE) {
                $category = $product_name = 'Early Access';
            } else if (strpos($bundle->name, ':')) {
                $name = explode(': ', $bundle->name);
                $category = $name[0];
                $product_name = $name[1];
            }

            if (isset($bundle->beta)) {
                $product_name .= ' (BETA)';
            }

            if (isset($bundle->class)) {
                $classes[$bundle->id] = $bundle->class;
            }

            $products[$category][$bundle->id] = $product_name;
            $product_descriptions[$bundle->id] = isset($bundle->desc) ? $bundle->desc : '';
            $product_bundles[$bundle->id] = $bundle->bundles;

            if (in_array($current_item_id, $bundle->bundles)) {
                $can_install[] = $bundle->id;
            }

            if(isset($bundle->new) && 0!=$bundle->new) {
                $new_products[] = $bundle->id;
            }
        }

        ksort($products);

        // move Gecko to top
        $lastvalue = end($products);
        $lastkey = key($products);

        $tmp = [$lastkey => $lastvalue];
        array_pop($products);

        $products = array_merge($tmp, $products);

        // move Early Access to bottom
        $products += array_splice($products, array_search('Early Access', array_keys($products)), 1);

        $html = '';

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // check PeepSo plugins
        $peepso_plugins = array_filter(get_plugins(), function ($var) {
            return ($var['Author'] == 'PeepSo');
        });

        // check Gecko theme
        $gecko_installed = FALSE;
        foreach (wp_get_themes() as $key => $value) {

            // @since 3.6.2.0 due to WordPress.org tag requirements, we have hidden  Gecko Parent in description
            $desc = (isset($value['Description'])) ? $value['Description'] : '';
            if(stristr($desc,'Gecko Parent')) {
                $gecko_installed = TRUE;
            }

            // Legacy tag-based detection
            if(!$gecko_installed) {
                $tags = (isset($value['Tags']) && is_array($value['Tags'])) ? $value['Tags'] : [];
                $tags = array_map('strtolower', $tags);

                if (in_array('gecko parent', $tags)) {
                    $gecko_installed = TRUE;
                }
            }


        }

        if($license_changed) {
            // delete license display warning   
            PeepSo3_Mayfly::del('peepso_has_displayed_license_warning');

            ob_start();

            echo "<!--\nLicense changed!";

            $activate_products = apply_filters( 'peepso_license_config', array() );

            if ( count( $activate_products ) ) {

                foreach ($activate_products as $prod) {
                    PeepSoApiRateLimit::clear();
                    PeepSo3_Mayfly::del('peepso_is_offline');

                    $slug = $prod['plugin_slug'];
                    $id = $prod['plugin_edd'];

                    echo "\nactivating $slug / $id";

                    PeepSoConfigSettings::get_instance()->set_option('site_license_' . $slug, $license); // prevent cached license
                    PeepSo3_Mayfly::del('peepso_license_' . $slug);
                    PeepSoLicense::activate_license($slug, $id);
                }
            }
            if(class_exists('Gecko_Customizer')) {
                update_option('gecko_options', ['gecko_license' => $license]);
                echo "\nactivating gecko";
            }
            echo "\n-->";
            $html .= ob_get_clean();
        }

        foreach ($products as $category_name => $categories) {
            $i = 0;
            if (count($categories) > 0) {
                foreach ($categories as $item_id => $item_name) {
                    $data = [
                        'class' => isset($classes[$item_id]) ? $classes[$item_id] : '',
                        'can_install' => $can_install,
                        'bundles' => $product_bundles[$item_id],
                        'category' => $i == 0 ? $category_name : '',
                        'item_id' => $item_id,
                        'item_name' => $item_name,
                        'item_description' => $product_descriptions[$item_id],
                        'peepso_plugins' => $peepso_plugins,
                        'gecko_installed' => $gecko_installed,
                        'is_new' => in_array($item_id, $new_products),
                        'license_changed' => $license_changed,
                    ];
                    $html .= PeepSoTemplate::exec_template('admin', 'addons_product', $data, true);
                    $i++;
                }
                $i = 0;
            }
        }

        // Set response
        $resp->set('addons', $html);

        if ($current_item_id) {
            $bundle = ucfirst(array_search($current_item_id, $peepso_bundle));
            if($bundle == 'Ultimate5') {
                $bundle_name = 'PeepSo Ultimate Bundle (5 Years)';
            } elseif($bundle == 'Ultimatemonthly') {
                $bundle_name = 'PeepSo Ultimate Bundle (Monthly)';
            } else {
                $bundle_name = 'PeepSo ' . $bundle . ' Bundle';
            }

            if ($bundle == 'Free') {
                PeepSo3_Mayfly::set('peepso_has_displayed_license_warning', 1, HOUR_IN_SECONDS);
            }

            $resp->set('bundle_name', $bundle_name);
        } else {
            PeepSo3_Mayfly::set('peepso_has_displayed_license_warning', 1, HOUR_IN_SECONDS);

            $resp->set('message', __('Invalid license key', 'peepso-core'));
        }

        $resp->success(true);
    }

    public function install(PeepSoAjaxResponse $resp)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-includes/pluggable.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $license = PeepSo3_Helper_Addons::get_license();
        if ($license) {
            $input = new PeepSoInput();
            $item_id = $input->value('item_id');

            $api_params = array(
                'edd_action' => 'get_version',
                'license'    => $license,
                'item_id'    => $item_id,
                'url'        => home_url(),
            );

            $request = wp_remote_post(PeepSoLicense::PEEPSO_HOME, ['timeout' => 15, 'sslverify' => TRUE, 'body' => $api_params]);
            if (!is_wp_error($request)) {
                $request = json_decode(wp_remote_retrieve_body($request));

                if (isset($request->download_link)) {
                    // get slug
                    $bundle_info = PeepSo3_Mayfly::get('bundle_info');
                    $item_info = array_values(array_filter($bundle_info, function ($var) use ($item_id) {
                        return ($var->id == $item_id);
                    }));

                    sort($item_info);
                    $slug = $item_info[0]->slug;

                    // activate license
                    PeepSoConfigSettings::get_instance()->set_option('site_license_' . $slug, $license); // prevent cached license
                    PeepSo3_Mayfly::del('peepso_license_' . $slug);
                    PeepSoLicense::activate_license($slug, $item_id);

                    if ($item_id == 7354103) { // Gecko
                        update_option('gecko_options', ['gecko_license' => $license]);
                        $upgrader = new Theme_Upgrader(new WP_Ajax_Upgrader_Skin(['title' => $request->name]));
                    } else {
                        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin(['title' => $request->name]));
                    }
                    $result = $upgrader->install($request->download_link, [
                        'overwrite_package' => TRUE
                    ]);

                    $resp->set('result', $result);
                } else if (isset($request->msg)) {
                    $resp->set('message', $request->msg);
                }
            }
        } else {
            PeepSo3_Mayfly::set('peepso_has_displayed_license_warning', 1, HOUR_IN_SECONDS);
            $resp->set('message', __('Invalid license key', 'peepso-core'));
        }

        $resp->success(true);
    }

    public function hide_tutorial(PeepSoAjaxResponse $resp) {
        update_user_option(get_current_user_id(), 'peepso_user_installer_tutorial', 1);
        $resp->success(true);
    }
}