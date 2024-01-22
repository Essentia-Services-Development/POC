<?php
require_once dirname( __FILE__ ) . '/wp-load.php';

$sites = array(
    'http://actcentive.com/',
	'https://actcentive.com/global-brands-affiliate-marketplace/',
	'https://actcentive.com/us-marketplace/',	
	'https://actcentive.com/uk-marketplace/',
	'https://actcentive.com/ca-marketplace/',
	'https://actcentive.com/sa-marketplace/',
	'https://actcentive.com/ng-marketplace/',
	'https://actcentive.com/india-marketplace/',
	'https://actcentive.com/jp-marketplace/',
	'https://actcentive.com/au-marketplace/',
	'https://actcentive.com/nl-marketplace/',
	'https://actcentive.com/cn-marketplace/',
	'https://actcentive.com/crowdfunding-marketplace/',
	'https://actcentive.com/support/',
	'https://actcentive.com/de-marketplace/',
	'https://actcentive.com/fr-marketplace/',
	'https://actcentive.com/kr-marketplace/',
	'https://actcentive.com/podcasting-marketplace/'
);

foreach ( $sites as $site_url ) {
    $cron_url = $site_url . 'wp-cron.php?doing_wp_cron';

    // Use the WordPress HTTP API to trigger the site's cron job
    $response = wp_remote_get( $cron_url );

    // Check for errors
    if ( is_wp_error( $response ) ) {
        error_log( 'HTTP Error: ' . $response->get_error_message() );
    }

    // Pause for 1 second before processing the next site
    usleep( 1000000 );
}