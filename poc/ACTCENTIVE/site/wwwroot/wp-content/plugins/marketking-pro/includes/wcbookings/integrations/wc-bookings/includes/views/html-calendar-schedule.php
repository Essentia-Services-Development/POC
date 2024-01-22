<?php
if ( marketking()->vendor_has_panel( 'products' ) ) {
	$checkedval = 0;
	if ( marketking()->is_vendor_team_member() ) {
		$checkedval = intval( get_user_meta( get_current_user_id(), 'marketking_teammember_available_panel_editproducts', true ) );
	}


// set locale
	$locale = get_locale();
	setlocale( LC_ALL, $locale );


// get announcements here as the unread number has to be shown in sidebar
// get all announcements that the user has access (visibility) to
	$user_id = get_current_user_id();

	if ( marketking()->is_vendor_team_member() ) {
		$user_id = marketking()->get_team_member_parent();
	}


	$user          = get_user_by( 'id', $user_id )->user_login;
	$agent_group   = get_user_meta( $user_id, 'marketking_group', true );
	$announcements = get_posts( array(
		'post_type'   => 'marketking_announce',
		'post_status' => 'publish',
		'numberposts' => - 1,
		'meta_query'  => array(
			'relation' => 'OR',
			array(
				'key'   => 'marketking_group_' . $agent_group,
				'value' => '1',
			),
			array(
				'key'   => 'marketking_user_' . $user,
				'value' => '1',
			),
		)
	) );

// Get nr of orders
	$vendor_orders_nr = count( get_posts( array(
		'post_type'   => 'shop_order',
		'post_status' => 'wc-processing',
		'numberposts' => - 1,
		'author'      => $user_id,
		'fields'      => 'ids'
	) ) );


// check how many are unread
	$unread_ann = 0;
	foreach ( $announcements as $announcement ) {
		$read_status = get_user_meta( $user_id, 'marketking_announce_read_' . $announcement->ID, true );
		if ( ! $read_status || empty( $read_status ) ) {
			$unread_ann ++;
		}
	}

	marketking()->set_data( 'unread_ann', $unread_ann );
	marketking()->set_data( 'user_id', $user_id );
	marketking()->set_data( 'announcements', $announcements );


// get all messages that are unread (unread = user is different than msg author + read time is lower than last marked time)
// get and display messages
	$currentuser      = new WP_User( $user_id );
	$currentuserlogin = $currentuser->user_login;
	$messages         = get_posts(
		array(
			'post_type'   => 'marketking_message', // only conversations
			'post_status' => 'publish',
			'numberposts' => - 1,
			'fields'      => 'ids',
			'meta_query'  => array(   // only the specific user's conversations
			                          'relation' => 'OR',
			                          array(
				                          'key'   => 'marketking_message_user',
				                          'value' => $currentuserlogin,
			                          ),
			                          array(
				                          'key'   => 'marketking_message_message_1_author',
				                          'value' => $currentuserlogin,
			                          )


			)
		)
	);
	if ( current_user_can( 'activate_plugins' ) ) {
		// include shop messages
		$messages2 = get_posts(
			array(
				'post_type'   => 'marketking_message', // only conversations
				'post_status' => 'publish',
				'numberposts' => - 1,
				'fields'      => 'ids',
				'meta_query'  => array(   // only the specific user's conversations
				                          'relation' => 'OR',
				                          array(
					                          'key'   => 'marketking_message_user',
					                          'value' => 'shop'
				                          ),
				                          array(
					                          'key'   => 'marketking_message_message_1_author',
					                          'value' => 'shop'
				                          )
				)
			)
		);
		$messages  = array_merge( $messages, $messages2 );
	}
// check how many are unread
	$unread_msg = 0;
	foreach ( $messages as $message ) {
		// check that last msg is not current user
		$nr_messages         = get_post_meta( $message, 'marketking_message_messages_number', true );
		$last_message_author = get_post_meta( $message, 'marketking_message_message_' . $nr_messages . '_author', true );
		if ( $last_message_author !== $currentuserlogin ) {
			// chek if last read time is lower than last msg time
			$last_read_time = get_user_meta( $user_id, 'marketking_message_last_read_' . $message, true );
			if ( ! empty( $last_read_time ) ) {
				$last_message_time = get_post_meta( $message, 'marketking_message_message_' . $nr_messages . '_time', true );
				if ( floatval( $last_read_time ) < floatval( $last_message_time ) ) {
					$unread_msg ++;
				}
			} else {
				$unread_msg ++;
			}
		}

	}

	marketking()->set_data( 'unread_msg', $unread_msg );
	marketking()->set_data( 'messages', $messages );


	#-----------------------------------------------------------------#
	# CALENDAR
	#-----------------------------------------------------------------#
	$productid = sanitize_text_field( Marketking_WC_Bookings::get_pagenr_query_var() );

	$user_id = get_current_user_id();

	if ( marketking()->is_vendor_team_member() ) {
		$user_id = marketking()->get_team_member_parent();
	}

	// Addons integration only if the product exists (not being added)
	$prod = sanitize_text_field( marketking()->get_pagenr_query_var() );

	if ( $prod === 'add' ) {
		global $marketking_product_add_id;
		$prod = $marketking_product_add_id;
	}
	#-----------------------------------------------------------------#
	# CALENDAR
	#-----------------------------------------------------------------#
	$canadd = marketking()->vendor_can_add_more_products( $user_id );

	// if product exists
	$post    = get_post( $productid );
	$product = wc_get_product( $productid );
	$exists  = 'existing';


	#-----------------------------------------------------------------#
	#  END: WOOCOMMERCE BOOKINGS INTEGRATION
	#-----------------------------------------------------------------#

	// get original query var
	if ( get_query_var( 'pagenr' ) === 'add' ) {
		$exists = 'new';
	}

	$logo_src = get_option( 'marketking_logo_setting', '' );
	// if no logo configured, set default marketking logo
	if ( $logo_src === '' ) {
		$logo_src = MARKETKINGCORE_URL . '/includes/assets/images/marketkinglogoblack.png';

	}
}
?>
<div class="nk-app-root">
<div class="nk-main ">

	<?php 

	include_once (apply_filters('marketking_dashboard_template', MARKETKINGCORE_DIR . 'public/dashboard/templates/sidebar.php'));


	?>

<div class="nk-wrap ">
	<?php 

	include_once (apply_filters('marketking_dashboard_template', MARKETKINGCORE_DIR . 'public/dashboard/templates/header-bar.php'));


	?>
<div class="nk-content marketking_orders_page marketking_calendar_view_page" style="margin-top:65px">
<div class="container-fluid">
<div class="nk-content-inner">
	<div class="nk-content-body">
		<div class="nk-block-head nk-block-head-sm">
			<div class="nk-block-between">
				<div class="nk-block-head-content">
					<h3 class="nk-block-title page-title"><?php esc_html_e( 'Calendar', 'marketking'); ?>
					</h3>
				</div><!-- .nk-block-head-content -->
				<div class="nk-block-head-content">
					<div class="toggle-wrap nk-block-tools-toggle">
						<div>
							<ul class="nk-block-tools g-3">
								<?php

								?>
								<li>

								</li>
								<?php

								if ( intval( get_option( 'marketking_vendors_can_newproducts_setting', 1 ) ) === 1 ) {
									if ( apply_filters( 'marketking_vendors_can_add_products', true ) ) {
										// either not team member, or team member with permission to add
										if ( ! marketking()->is_vendor_team_member() || $checkedval === 1 ) {
											if ( marketking()->vendor_can_add_more_products( $user_id ) ) {
												?>
												
												<?php
											} else {
												// show some error message that they reached the max nr of products
												?>
												<button type="button" class="btn
                                                            btn-gray d-none d-md-inline-flex"
												        disabled="disabled"><em
															class="icon ni
			                                                            ni-plus"></em>&nbsp;
												                                      &nbsp;<?php
													esc_html_e( 'Add Booking (Max Limit Reached)', 'marketking' ); ?>
												</button>
												<?php
											}
										}
									}
								}

								?>
							</ul>
						</div>
					</div>
				</div>

			</div>
		</div>
		<div class="" style="float:right;margin-bottom: 20px;">
			<?php
			//do_action( 'marketking_wc_bookings_nav' );
			?>
		</div>
		<div class="wrap woocommerce">
			<!--	<h2><?php /*esc_html_e( 'Schedule', 'marketking' ); */ ?></h2>-->

			<form method="get" id="mainform" enctype="multipart/form-data"
			      class="wc_bookings_calendar_form">
				<!--		<input type="hidden" name="post_type" value="wc_booking" />-->
				<!--		<input type="hidden" name="page" value="booking_calendar" />-->
				<input type="hidden" name="view" value="<?php echo esc_attr( $view ); ?>"/>
				<input type="hidden" name="tab" value="calendar"/>
				<input type="hidden" name="calendar_day"
				       value="<?php echo esc_attr( $day ); ?>"/>
				<input type="hidden" name="calendar_month"
				       value="<?php echo esc_attr( $month ); ?>"/>

				<?php include 'html-calendar-nav.php'; ?>

				<ul class="wc-bookings-schedule-days">
					<?php foreach ( $this->days as $day ) : ?>
						<?php $on_today_class = current_time( 'Y-m-d' ) === $day->format( 'Y-m-d' ) ? 'wc-booking-schedule-today' : ''; ?>
						<li>
							<div class="wc-bookings-schedule-date <?php echo esc_attr( $on_today_class ); ?>">
								<div class="wc-bookings-schedule-day"><?php echo esc_html( $day->format( 'd' ) ); ?></div>
								<div class="wc-bookings-schedule-weekday"><?php echo esc_html( $day->format( 'M, D' ) ); ?></div>
							</div>
							<ul class="wc-bookings-schedule-day-events">
								<?php while ( isset( $this->events_data[0] ) && date( 'Y-m-d', $this->events_data[0]['start'] ) === $day->format( 'Y-m-d' ) ) : ?>
									<?php
									$event_data  = array_shift( $this->events_data );
									$description = ! empty( $event_data['customer'] ) ? '<span class="wc-bookings-schedule-customer-name">' . $event_data['customer'] . '</span>, ' . $event_data['title'] : $event_data['title'];
									?>
									<li>
										<a class="wc-bookings-schedule-event"
										   href="<?php echo esc_url( $event_data['url'] ); ?>">
											<div class="wc-bookings-schedule-booking-duration">
												<?php echo esc_html( $event_data['time'] ); ?>
											</div>
											<div class="wc-bookings-schedule-booking-info">
												<div class="wc-bookings-schedule-booking-description">
													<?php echo wp_kses_post( $description ); ?>
												</div>
												<div class="wc-bookings-schedule-booking-details">
													<?php
													$resources = array();
													if ( ! empty( $event_data['resource'] ) ) {
														array_push( $resources, $event_data['resource'] );
													}
													if ( ! empty( $event_data['resources'] ) ) {
														echo esc_html( __( 'Resources: ', 'marketking' ) );
														echo esc_html( implode( ', ', $event_data['resources'] ) );
													}
													?>
												</div>
												<div class="wc-bookings-schedule-booking-details">
													<?php
													$persons = '';
													if ( ! empty( $event_data['persons'] ) ) {
														$persons = $event_data['persons'];
													}
													if ( ! empty( $persons ) ) {
														// Persons from Booking data already contains label
														echo esc_html( $persons );
													}
													?>
												</div>
												<?php if ( ! empty( $event_data['note'] ) ) : ?>
													<div class="wc-bookings-schedule-booking-details">
														<?php echo esc_html(
															sprintf(
															/* translators: %s: Additional note added to a booking. */
																__( "Note: %s", 'marketking' ),
																$event_data['note']
															)
														); ?>
													</div>
												<?php endif; ?>
											</div>
										</a>
									</li>
								<?php endwhile; ?>
							</ul>
						</li>
					<?php endforeach; ?>
				</ul>
			</form>
		</div>
	</div>
</div>
</div>
</div>
</div>
</div>
</div>