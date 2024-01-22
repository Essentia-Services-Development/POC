<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
if ( ! class_exists( 'QueryMonitor' )
) {
	return; // Direct access not allowed.
}
#-----------------------------------------------------------------#
# ENQUEUE STYLES
#-----------------------------------------------------------------#
add_action( 'marketking_dashboard_head', function () {
	if ( class_exists( 'QueryMonitor' ) && is_page( 'vendor-dashboard' ) ) {
		?>
		<style>
		  #query-monitor-main {
			z-index : 999998 !important;
			}
		</style>
		<?php
	}
} );
add_action( 'wp_print_styles', function () {
	global $wp_styles;


	if ( class_exists( 'QueryMonitor' ) && is_page( 'vendor-dashboard' ) ) {

		wp_enqueue_style(
			'query-monitor',
			QueryMonitor::init()->plugin_url( 'assets/query-monitor.css' ),
			array(),
			QueryMonitor::init()->plugin_ver( 'assets/query-monitor.css' )
		);
	}

	$current_styles = $wp_styles->queue;
	$new_styles     = array(
		'query-monitor'
	);

	$wp_styles->queue = array_merge( $current_styles, $new_styles );

	?>

	<?php
}, 99 );
#-----------------------------------------------------------------#
# ENQUEUE SCRIPTS
#-----------------------------------------------------------------#
add_action( 'wp_print_scripts', function () {
	global $wp, $wp_scripts;

	$current_scripts = $wp_scripts->queue;


	if ( class_exists( 'QueryMonitor' ) && is_page( 'vendor-dashboard' )

	) {

		global $wp_locale;

		$deps = array(
			'jquery',
		);

		if ( defined( 'QM_NO_JQUERY' ) && QM_NO_JQUERY ) {
			$deps = array();
		}

		wp_enqueue_script(
			'query-monitor',
			QueryMonitor::init()->plugin_url( 'assets/query-monitor.js' ),
			array(),
			QueryMonitor::init()->plugin_ver( 'assets/query-monitor.js' )
		);
		wp_localize_script(
			'query-monitor',
			'qm_number_format',
			$wp_locale->number_format
		);
		wp_localize_script(
			'query-monitor',
			'qm_l10n',
			array(
				'ajax_error' => __( 'PHP Errors in Ajax Response', 'query-monitor' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'auth_nonce' => array(
					'on' => wp_create_nonce( 'qm-auth-on' ),
					'off' => wp_create_nonce( 'qm-auth-off' ),
					'editor-set' => wp_create_nonce( 'qm-editor-set' ),
				),
				'fatal_error' => __( 'PHP Fatal Error', 'query-monitor' ),
			)
		);

		$new_scripts = array(
			'query-monitor'
		);

		$wp_scripts->queue = array_merge( $current_scripts, $new_scripts );

		?>
		<?php if ( ! is_admin() && is_user_logged_in() ):

			?>
			<?php if ( get_query_var( 'dashpage' ) !== 'edit-booking-product' && get_query_var( 'dashpage' ) !== 'edit-product' && get_query_var( 'dashpage' ) !== 'edit-resource' ): ?>
			<div id="marketking_footer_hidden">
				<?php
				wp_footer();
				?>
			</div>

		<?php endif; ?>
		<?php endif; ?>

		<?php

	}


}, 99 );