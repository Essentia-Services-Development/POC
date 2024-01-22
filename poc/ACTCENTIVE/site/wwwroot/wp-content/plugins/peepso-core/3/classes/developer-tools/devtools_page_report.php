<?php

class PeepSo3_Developer_Tools_Page_Report extends PeepSo3_Developer_Tools_Page
{
	public function __construct()
	{
		$this->title 		= __('System Report', 'peepso_debug');
		$this->description	= __('Overview your vital WordPress and environment data. Exports as formatted .txt file.', 'peepso_debug');
	}

	public function page()
	{
		$this->page_start('report');

		printf('<pre>%s</pre>', $this->page_data());

		$this->page_end();
	}

	public function page_data()
	{
		global $wpdb;

		$theme      = wp_get_theme()->Name . ' (' . wp_get_theme()->Version .')';

		// data checks for later
		$frontpage	= get_option( 'page_on_front' );
		$frontpost	= get_option( 'page_for_posts' );
		$mu_plugins = get_mu_plugins();
		$plugins	= get_plugins();
		$active		= get_option( 'active_plugins', array() );

		// multisite details
		$nt_plugins	= is_multisite() ? wp_get_active_network_plugins() : array();
		$nt_active	= is_multisite() ? get_site_option( 'active_sitewide_plugins', array() ) : array();
		$ms_sites	= is_multisite() ? get_blog_list() : null;

		// yes / no specifics
		$ismulti	= is_multisite() ? __( 'Yes', 'wordpress-system-report' ) : __( 'No', 'wordpress-system-report' );
		$safemode	= ini_get( 'safe_mode' ) ? __( 'Yes', 'wordpress-system-report' ) : __( 'No', 'wordpress-system-report' );
		$wpdebug	= defined( 'WP_DEBUG' ) ? WP_DEBUG ? __( 'Enabled', 'wordpress-system-report' ) : __( 'Disabled', 'wordpress-system-report' ) : __( 'Not Set', 'wordpress-system-report' );
		$fr_page	= $frontpage ? get_the_title( $frontpage ).' (ID# '.$frontpage.')'.'' : __( 'n/a', 'wordpress-system-report' );
		$fr_post	= $frontpage ? get_the_title( $frontpost ).' (ID# '.$frontpost.')'.'' : __( 'n/a', 'wordpress-system-report' );
		$errdisp	= ini_get( 'display_errors' ) != false ? __( 'On', 'wordpress-system-report' ) : __( 'Off', 'wordpress-system-report' );

		$jquchk		= wp_script_is( 'jquery', 'registered' ) ? $GLOBALS['wp_scripts']->registered['jquery']->ver : __( 'n/a', 'wordpress-system-report' );

		$sessenb	= (PHP_SESSION_DISABLED != session_status()) ? __( 'Enabled', 'wordpress-system-report' ) : __( 'Disabled', 'wordpress-system-report' );
		$usecck		= ini_get( 'session.use_cookies' ) ? __( 'On', 'wordpress-system-report' ) : __( 'Off', 'wordpress-system-report' );
		$useocck	= ini_get( 'session.use_only_cookies' ) ? __( 'On', 'wordpress-system-report' ) : __( 'Off', 'wordpress-system-report' );
		$hasfsock	= function_exists( 'fsockopen' ) ? __( 'Your server supports fsockopen.', 'wordpress-system-report' ) : __( 'Your server does not support fsockopen.', 'wordpress-system-report' );
		$hascurl	= function_exists( 'curl_init' ) ? __( 'Your server supports cURL.', 'wordpress-system-report' ) : __( 'Your server does not support cURL.', 'wordpress-system-report' );
		$hassoap	= class_exists( 'SoapClient' ) ? __( 'Your server has the SOAP Client enabled.', 'wordpress-system-report' ) : __( 'Your server does not have the SOAP Client enabled.', 'wordpress-system-report' );
		$hassuho	= extension_loaded( 'suhosin' ) ? __( 'Your server has SUHOSIN installed.', 'wordpress-system-report' ) : __( 'Your server does not have SUHOSIN installed.', 'wordpress-system-report' );
		$openssl	= extension_loaded('openssl') ? __( 'Your server has OpenSSL installed.', 'wordpress-system-report' ) : __( 'Your server does not have OpenSSL installed.', 'wordpress-system-report' );

		// start generating report
		$report	= '';
		$report	.= '### Begin System Info ###'."\n";
		// add filter for adding to report opening
		$report	.= apply_filters( 'snapshot_report_before', '' );

		$report	.= "\n\t".'** WORDPRESS DATA **'."\n";
		$report	.= 'Multisite:'."\t\t\t\t".$ismulti."\n";
		$report	.= 'SITE_URL:'."\t\t\t\t".site_url()."\n";
		$report	.= 'HOME_URL:'."\t\t\t\t".home_url()."\n";
		$report	.= 'WP Version:'."\t\t\t\t".get_bloginfo( 'version' )."\n";
		$report	.= 'Permalink:'."\t\t\t\t".get_option( 'permalink_structure' )."\n";
		$report	.= 'Cur Theme:'."\t\t\t\t".$theme."\n";
		$report	.= 'Post Types:'."\t\t\t\t".implode( ', ', get_post_types( '', 'names' ) )."\n";
		$report	.= 'Post Stati:'."\t\t\t\t".implode( ', ', get_post_stati() )."\n";

        $count_users = count_users();
        $report	.= 'User Count:'."\t\t\t\t".$count_users['total_users']."\n";

		$report	.= "\n\t".'** WORDPRESS CONFIG **'."\n";
		$report	.= 'WP_DEBUG:'."\t\t\t\t".$wpdebug."\n";
		$report	.= 'WP Memory Limit:'."\t\t\t".PeepSo3_Developer_Tools::num_convt( WP_MEMORY_LIMIT )/( 1024 ).'MB'."\n";
		$report	.= 'Table Prefix:'."\t\t\t\t".$wpdb->base_prefix."\n";
		$report	.= 'Show On Front:'."\t\t\t\t".get_option( 'show_on_front' )."\n";
		$report	.= 'Page On Front:'."\t\t\t\t".$fr_page."\n";
		$report	.= 'Page For Posts:'."\t\t\t\t".$fr_post."\n";

		if ( is_multisite() ) :
			$report	.= "\n\t".'** MULTISITE INFORMATION **'."\n";
			$report	.= 'Total Sites:'."\t\t\t\t".get_blog_count()."\n";
			$report	.= 'Base Site:'."\t\t\t\t".$ms_sites[0]['domain']."\n";
			$report	.= 'All Sites:'."\n";
			foreach ( $ms_sites as $site ) :
				if ( $site['path'] != '/' )
					$report	.= "\t\t".'- '. $site['domain'].$site['path']."\n";

			endforeach;
			$report	.= "\n";
		endif;


		$report	.= "\n\t".'** SERVER DATA **'."\n";
		$report	.= 'jQuery Version'."\t\t\t\t".$jquchk."\n";
		$report	.= 'PHP Version:'."\t\t\t\t".PHP_VERSION."\n";
		$report	.= 'MySQL Version:'."\t\t\t\t".$wpdb->db_version()."\n";
		$report	.= 'Server Software:'."\t\t\t".$_SERVER['SERVER_SOFTWARE']."\n";

		$report	.= "\n\t".'** PHP CONFIGURATION **'."\n";
		$report	.= 'Safe Mode:'."\t\t\t\t".$safemode."\n";
		$report	.= 'Memory Limit:'."\t\t\t\t".ini_get( 'memory_limit' )."\n";
		$report	.= 'Upload Max:'."\t\t\t\t".ini_get( 'upload_max_filesize' )."\n";
		$report	.= 'Post Max:'."\t\t\t\t".ini_get( 'post_max_size' )."\n";
		$report	.= 'Time Limit:'."\t\t\t\t".ini_get( 'max_execution_time' )."\n";
		$report	.= 'Max Input Vars:'."\t\t\t\t".ini_get( 'max_input_vars' )."\n";
		$report	.= 'Display Errors:'."\t\t\t\t".$errdisp."\n";
		$report	.= 'Sessions:'."\t\t\t\t".$sessenb."\n";
		$report	.= 'Session Name:'."\t\t\t\t".esc_html( ini_get( 'session.name' ) )."\n";
		$report	.= 'Cookie Path:'."\t\t\t\t".esc_html( ini_get( 'session.cookie_path' ) )."\n";
		$report	.= 'Save Path:'."\t\t\t\t".esc_html( ini_get( 'session.save_path' ) )."\n";
		$report	.= 'Use Cookies:'."\t\t\t\t".$usecck."\n";
		$report	.= 'Use Only Cookies:'."\t\t\t".$useocck."\n";
		$report	.= 'FSOCKOPEN:'."\t\t\t\t".$hasfsock."\n";
		$report	.= 'cURL:'."\t\t\t\t\t".$hascurl."\n";
		$report	.= 'SOAP Client:'."\t\t\t\t".$hassoap."\n";
		$report	.= 'SUHOSIN:'."\t\t\t\t".$hassuho."\n";
		$report	.= 'OpenSSL:'."\t\t\t\t".$openssl."\n";

		$report	.= "\n\t".'** PLUGIN INFORMATION **'."\n";
		if ( $plugins && $mu_plugins ) :
			$report	.= 'Total Plugins:'."\t\t\t\t".( count( $plugins ) + count( $mu_plugins ) + count( $nt_plugins ) )."\n";
		endif;

		// output must-use plugins
		if ( $mu_plugins ) :
			$report	.= 'Must-Use Plugins: ('.count( $mu_plugins ).')'. "\n";
			foreach ( $mu_plugins as $mu_path => $mu_plugin ) :
				$report	.= "\t".'- '.$mu_plugin['Name'] . ' ' . $mu_plugin['Version'] ."\n";
			endforeach;
			$report	.= "\n";
		endif;

		// if multisite, grab active network as well
		if ( is_multisite() ) :
			// active network
			$report	.= 'Network Active Plugins: ('.count( $nt_plugins ).')'. "\n";

			foreach ( $nt_plugins as $plugin_path ) :
				if ( array_key_exists( $plugin_base, $nt_plugins ) )
					continue;

				$plugin = get_plugin_data( $plugin_path );

				$report	.= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
			endforeach;
			$report	.= "\n";

		endif;

		// output active plugins
		if ( $plugins ) :
			$report	.= 'Active Plugins: ('.count( $active ).')'. "\n";
			foreach ( $plugins as $plugin_path => $plugin ) :
				if ( ! in_array( $plugin_path, $active ) )
					continue;
				$report	.= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
			endforeach;
			$report	.= "\n";
		endif;

		// output inactive plugins
		if ( $plugins ) :
			$report	.= 'Inactive Plugins: ('.( count( $plugins ) - count( $active ) ).')'. "\n";
			foreach ( $plugins as $plugin_path => $plugin ) :
				if ( in_array( $plugin_path, $active ) )
					continue;
				$report	.= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
			endforeach;
			$report	.= "\n";
		endif;


		// end it all
		$report	.= "\n".'### End System Info ###';

		return $report;
	}
}