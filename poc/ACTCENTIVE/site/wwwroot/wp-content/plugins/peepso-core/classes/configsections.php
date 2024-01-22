<?php

class PeepSoConfigSections extends PeepSoConfigSectionAbstract {
    const SITE_ALERTS_SECTION = 'site_alerts_';

    public function register_config_groups() {
        $this->set_context( 'full' );

        // Don't show licenses box on our demo / d3mo site
        if ( !PeepSoSystemRequirements::is_demo_site()) {
            $this->license();
        }
    }

    private function license() {


        $upsell = PeepSo3_Helper_Addons::get_upsell('box', FALSE);

        if(PeepSo3_Utilities_String::maybe_strlen($upsell)) {
            $this->set_field(
                'bundle_upsell',
                $upsell,
                'message'
            );
        }

        $this->set_field(
            'bundle_license',
            __( 'PeepSo Bundle License Key', 'peepso-core' ),
            'text'
        );

        if ( isset( $_GET['peepso_debug'] ) ) {
            PeepSo3_Mayfly::del( 'peepso_config_licenses_bundle' );
        }

        // Get all licensed PeepSo products
        $products = apply_filters( 'peepso_license_config', array() );

        if ( count( $products ) ) {

            $new_products = array();
            foreach ( $products as $prod ) {

                $key = $prod['plugin_name'];

                if ( strstr( $prod['plugin_name'], ':' ) ) {
                    $name                = explode( ':', $prod['plugin_name'] );
                    $prod['cat']         = $name[0];
                    $prod['plugin_name'] = $name[1];
                }

                if ( !isset($prod['cat']) || !strlen($prod['cat']) ) {
                    $prod['cat'] = $prod['plugin_name'];
                }

                $new_products[ $key ] = $prod;
            }

            ksort( $new_products );

            // Loop through the list and build fields
            $prev_cat = null;
            foreach ( $new_products as $prod ) {

                if ( isset( $prod['cat'] ) && $prev_cat != $prod['cat'] ) {
                    $this->set_field(
                        'cat_' . $prod['cat'],
                        $prod['cat'],
                        'separator'
                    );

                    $prev_cat = $prod['cat'];
                }
                // label contains some extra HTML for  license checking AJAX to hook into
                $label = $prod['plugin_name'];
                $label .= ' <small style=color:#cccccc>';
                $label .= $prod['plugin_version'] . '</small>';
                $label .= ' <span class="license_status_check" id="' . $prod['plugin_slug'] . '" data-plugin-name="' . $prod['plugin_edd'] . '"><img src="images/loading.gif"></span>';

                $this->set_field(
                    'site_license_' . $prod['plugin_slug'],
                    $label,
                    'text'
                );
            }
        }

        // Build Group
        $this->set_group(
            'license',
            __( 'License Key Configuration', 'peepso-core' ),
            __( 'This is where you configure the license keys for each PeepSo add-on. You can find your license numbers <a target="_blank" href="https://www.peepso.com/my-licenses/">here</a>. Please copy them here and click SAVE at the bottom of this page.', 'peepso-core' )
            . ' ' . sprintf( __( 'We are detecting %s as your install URL. Please make sure your "supported domain" is configured properly.', 'peepso-core' ), str_ireplace( array(
                'http://',
                'https://'
            ), '', home_url() ) )
            . '<br><br><b>'
            . __( 'If some licenses are not validating, please make sure to click the SAVE button.', 'peepso-core' )
            . ' </b><br/>'
            . __( 'If that does not help, please <a target="_blank" href="https://www.peepso.com/contact/">Contact Support</a>.', 'peepso-core' )

        );
    }
}

// EOF
