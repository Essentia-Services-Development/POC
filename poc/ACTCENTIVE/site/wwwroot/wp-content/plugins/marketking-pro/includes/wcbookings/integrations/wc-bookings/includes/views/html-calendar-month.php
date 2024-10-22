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
					<div class="nk-block-head nk-block-head-sm" style="padding-bottom:0">
						<div class="nk-block-between" style="padding-bottom:0;margin-top: 10px;">
							<div class="nk-block-head-content">
								<h3 class="nk-block-title page-title"><?php esc_html_e( 'Calendar', 'marketking'); ?>
								</h3>
								<div class="" style="float:right;">
									<?php
									do_action( 'marketking_wc_bookings_nav' );
									?>
								</div>

							</div><!-- .nk-block-head-content -->
						</div>

					</div>

					<div class="wrap woocommerce">

						<!--	<h2>--><?php //esc_html_e( 'Calendar', 'marketking' ); ?><!--</h2>-->

						<form method="get" id="mainform" enctype="multipart/form-data"
						      class="wc_bookings_calendar_form">
							<!--		<input type="hidden" name="post_type" value="wc_booking" />-->
							<!--		<input type="hidden" name="page" value="booking_calendar" />-->
							<input type="hidden" name="calendar_month"
							       value="<?php echo absint( $month ); ?>"/>
							<input type="hidden" name="calendar_year"
							       value="<?php echo absint( $year ); ?>"/>
							<input type="hidden" name="view"
							       value="<?php echo esc_attr( $view ); ?>"/>
							<input type="hidden" name="tab" value="calendar"/>

							<?php require 'html-calendar-nav.php'; ?>

							<?php if ( ! WC_BOOKINGS_GUTENBERG_EXISTS ) { ?>
								<script type="text/javascript">
									<?php global $wp_locale; ?>
                                    jQuery(function () {
                                        jQuery('.calendar_day').datepicker({
                                            dateFormat: 'yy-mm-dd',
                                            firstDay: <?php echo esc_attr( get_option( 'start_of_week' ) ); ?>,
                                            monthNames: JSON.parse(decodeURIComponent('<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->month ) ) ); ?>')),
                                            monthNamesShort: JSON.parse(decodeURIComponent('<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->month_abbrev ) ) ); ?>')),
                                            dayNames: JSON.parse(decodeURIComponent('<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->weekday ) ) ); ?>')),
                                            dayNamesShort: JSON.parse(decodeURIComponent('<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->weekday_abbrev ) ) ); ?>')),
                                            dayNamesMin: JSON.parse(decodeURIComponent('<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->weekday_initial ) ) ); ?>')),
                                            defaultDate: JSON.parse(decodeURIComponent('<?php echo rawurlencode( wp_json_encode( $default_date ) ); ?>')),
                                            numberOfMonths: 1,
                                            beforeShow: function (input, datePicker) {
                                                datePicker.dpDiv.addClass('wc-bookings-ui-datpicker-widget');
                                            },
                                            onSelect: function (inputDate) {
                                                document.location.search += '&calendar_day=' + inputDate + '&view=day';
                                            },
                                        });
                                    });
								</script>
							<?php } ?>

							<table class="wc_bookings_calendar widefat">
								<thead>
								<tr>
									<?php for ( $ii = get_option( 'start_of_week', 1 ); $ii < get_option( 'start_of_week', 1 ) + 7; $ii ++ ) : ?>
										<th><?php echo esc_html( date_i18n( _x( 'D', 'date format', 'marketking' ), strtotime( "next sunday +{$ii} day" ) ) ); ?></th>
									<?php endfor; ?>
								</tr>
								</thead>
								<tbody>
								<tr>
									<?php
									$timestamp     = $start_time;
									$current_date  = date( 'Y-m-d', current_time( 'timestamp' ) );
									$index         = 0;
									$this->colours = $this->get_event_color_styles( $this->events );
									while ( $timestamp <= $end_time ) :
										$timestamp_date = date( 'Y-m-d', $timestamp );
										$is_today  = $timestamp_date === $current_date;
										?>
										<td width="14.285%" class="<?php
										if ( date( 'n', $timestamp ) != absint( $month ) ) {
											echo 'calendar-diff-month';
										}

										if ( ( $timestamp + DAY_IN_SECONDS ) < current_time( 'timestamp' ) ) {
											echo ' wc-bookings-passed-day';
										} elseif ( $this->is_day_unavailable( $timestamp_date ) ) {
											echo ' wc-bookings-unavailable-day';
										}
										//
										?>">
											<a href="<?php
											/*echo esc_url( admin_url( 'edit.php?post_type=wc_booking&page=booking_calendar&view=day&tab=calendar&calendar_day=' . date( 'Y-m-d', $timestamp ) ) );*/

											echo esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'booking-calendar/?view=day&tab=calendar&calendar_day=' . date( 'Y-m-d', $timestamp ) );


											?>"<?php
											echo ' class="day_link';
											if ( $is_today ) {
												echo ' current_day';
											}
											?>">
											<?php echo esc_html( date( 'j', $timestamp ) ); ?>
											</a>
											<div class="bookings">
												<ul>
													<?php
													$this->list_bookings(
														date( 'd', $timestamp ),
														date( 'm', $timestamp ),
														date( 'Y', $timestamp )
													);
													?>
												</ul>
											</div>
										</td>
										<?php
										$timestamp = strtotime( '+1 day', $timestamp );
										$index ++;

										if ( 0 === $index % 7 ) {
											echo '</tr><tr>';
										}
									endwhile;
									?>
								</tr>
								</tbody>
							</table>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>