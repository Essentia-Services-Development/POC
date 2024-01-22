<?php

global $submenu, $submenu_file, $plugin_page, $pagenow;

$slug = 'edit.php?post_type=newsletter';

if ( ! isset( $submenu[ $slug ] ) ) {
	return;
}
$current_screen = get_current_screen();

// do not show on any block editor screen
if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
	return;
}

$tabs    = array();
$current = null;
foreach ( $submenu[ $slug ] as $i => $sub_item ) {

	// Check user can access page.
	if ( ! current_user_can( $sub_item[1] ) ) {
		continue;
	}
	if ( in_array( $sub_item[1], array( 'mailster_dashboard', 'mailster_manage_templates', 'mailster_manage_addons', 'mailster_manage_subscribers' ) ) ) {
		continue;
	}

	if ( in_array( $sub_item[2], array( 'mailster_dashboard', 'mailster_tests' ) ) ) {
		continue;
	}

	if ( $i === 10 ) {
		$sub_item[0] = esc_html__( 'New', 'mailster' );
	}

	$tab = array(
		'text'    => $sub_item[0],
		'url'     => $sub_item[2],
		'classes' => '',
	);

	if ( ! strpos( $sub_item[2], '.php' ) ) {
		$tab['url'] = add_query_arg( array( 'page' => $sub_item[2] ), $slug );
	}

	$is_autoresponder = isset( $_GET['post_status'] ) && $_GET['post_status'] == 'autoresponder';

	if ( $is_autoresponder && $sub_item[1] == 'mailster_edit_autoresponders' ) {
		$tab['classes'] .= ' is-active';
		$current         = $tab;
	} elseif ( ! $is_autoresponder && ( $submenu_file === $sub_item[2] || $plugin_page === $sub_item[2] ) && $pagenow !== 'post_new.php' ) {
		$tab['classes'] .= ' is-active';
		$current         = $tab;
	}
	$tabs[] = $tab;
}

$tabs = apply_filters( 'mailster_admin_header_tabs', $tabs );

?>
<div id="mailster-admin-toolbar">
	<a href="<?php echo admin_url( 'admin.php?page=mailster_dashboard' ); ?>" class="mailster-logo" title="<?php echo esc_attr( sprintf( 'Mailster %s', MAILSTER_VERSION ) ); ?>">
		<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 692.8 611.9" xml:space="preserve" style="max-width:50px;"><path class="st0" fill="#2BB2E8" d="M471.1,24.3L346.4,176.7L221.7,24.3H0v568.1h194V273.7l152.4,207.8l152.4-207.8v318.6h194V24.3H471.1z"/></svg>
		<span class="screen-reader-text">Mailster Newsletter Plugin</span>
	</a>
	<?php
	foreach ( $tabs as $tab ) {
		printf( '<a class="mailster-tab%s" href="%s">%s</a>', $tab['classes'], esc_url( $tab['url'] ), strip_tags( $tab['text'] ) );
	}
	?>
	
	<div role="tablist" aria-orientation="horizontal" class="panel-tabs">
		<?php if ( mailster()->is_bf2023() && ! mailster_freemius()->is_whitelabeled() ) : ?>
			<?php
				$discount      = '50%';
				$checkout_args = array(
					'id'     => 'bf2023dash',
					'coupon' => 'BFD2023DASH',
				);
				$expires       = strtotime( '2023-12-02 00:00:00' );
				$offset        = $expires - time();
				$format        = _x( 'only %s left!', 'time left', 'mailster' );
				$display       = $offset > DAY_IN_SECONDS ? '' : sprintf( $format, date( 'H:i:s', strtotime( 'midnight' ) + $offset - 1 ) );
				?>
			<a class="panel-tab action" id="mailster-bf2023dash" href="<?php echo mailster()->checkout_url( $checkout_args ); ?>" data-offset=<?php echo absint( $offset ); ?> data-format="<?php echo esc_attr( $format ); ?>" title="<?php echo esc_attr( sprintf( __( 'Grab a new license for Mailster with %s off the first year!', 'mailster' ), $discount ) ); ?>"><?php printf( esc_html__( 'Get %s off for Black Friday!', 'mailster' ), $discount ); ?><?php echo '<span>' . esc_html( $display ) . '</span>'; ?></a>

		<?php elseif ( mailster()->is_trial() ) : ?>
			<?php
			$license = mailster_freemius()->_get_license();
			$expires = $license ? strtotime( $license->expiration ) : 0;
			$offset  = $expires - time();
			$display = $offset > DAY_IN_SECONDS ? human_time_diff( $expires ) : date( 'H:i:s', strtotime( 'midnight' ) + $offset - 1 );
			?>
			<?php if ( $offset > 0 ) : ?>
				<a role="tab" aria-controls="activity-panel-help" id="mailster-trial-upgrade" class="panel-tab action" href="<?php echo mailster()->get_upgrade_url(); ?>" data-offset=<?php echo absint( $offset ); ?> title="<?php esc_attr_e( 'Upgrade now!', 'mailster' ); ?>"><?php printf( esc_html__( 'Your trial expires in %s', 'mailster' ), '<span>' . esc_html( $display ) . '</span>' ); ?></a>
			<?php else : ?>
				<a role="tab" aria-controls="activity-panel-help" id="mailster-trial-upgrade" class="panel-tab action expired" href="<?php echo mailster()->get_upgrade_url(); ?>" title="<?php esc_attr_e( 'Upgrade now!', 'mailster' ); ?>"><?php esc_html_e( 'Your trial has expired!', 'mailster' ); ?><span><?php esc_html_e( 'Upgrade now!', 'mailster' ); ?></span></a>
			<?php endif; ?>
		<?php endif; ?>
		<button type="button" role="tab" aria-controls="activity-panel-help" id="mailster-admin-help" class="panel-tab" href="<?php echo mailster_freemius()->contact_url(); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5zM3.25 12a8.75 8.75 0 1117.5 0 8.75 8.75 0 01-17.5 0zM12 8.75a1.5 1.5 0 01.167 2.99c-.465.052-.917.44-.917 1.01V14h1.5v-.845A3 3 0 109 10.25h1.5a1.5 1.5 0 011.5-1.5zM11.25 15v1.5h1.5V15h-1.5z"fill="#757575"></path></svg>
			<?php esc_html_e( 'Help', 'mailster' ); ?>
		</button>
	</div>
</div>
