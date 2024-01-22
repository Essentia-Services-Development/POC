<?php
$post_id = sanitize_text_field( Marketking_WC_Bookings::get_pagenr_query_var() );

$post = get_post( $post_id );

$user_id = get_current_user_id();

if ( marketking()->is_vendor_team_member() ) {
	$user_id = marketking()->get_team_member_parent();
}


$order_id = $booking->get_order_id();

$can_edit = false;


$exists = 'existing';


// get original query var
if ( get_query_var( 'pagenr' ) === 'add' ) {
	$exists = 'new';
}

$text       = esc_html__( 'Update Booking', 'marketking' );
$icon       = 'ni-edit-fill';
$actionedit = 'edit';
?>
<style>
  #post-body-content, #titlediv, #major-publishing-actions, #minor-publishing-actions, #visibility, #submitdiv {
	display : none;
	}
</style>

<div class="nk-content marketking_orders_page" style="margin-top:65px">
<div class="container-fluid">
<div class="nk-content-inner">
<div class="nk-content-body">
<?php
if ( $order_id ) {
	$sub_orders = marketking()->get_suborders_of_order( $order_id );


	if ( $sub_orders ) {
		foreach ( $sub_orders as $sub_order ) {
			if ( in_array( $user_id, marketking()->get_vendors_of_order( $sub_order->ID ) ) ) {
				$order_id = $sub_order->ID;
				break;
			}
		}
	}


	if ( in_array( $user_id, marketking()->get_vendors_of_order( $order_id ) ) ) {
		$can_edit = true;
	}

	if ( ! $can_edit ) {
		echo '<div class="marketking-alert marketking-alert-danger">' . __( 'This is not your booking...',
				'marketking-multivendor-marketplace-for-woocommerce' ) . '</div>';

		return;
	}
}
?>
<form id="marketking_save_booking_order_form">

<div class="panel-wrap woocommerce">
<div id="booking_data" class="panel">

<div class="nk-block-head nk-block-head-sm">
	<div class="nk-block-between">
		<div class="nk-block-head-content">
			<h2>
				<?php
				/* translators: 1: booking id */
				printf( esc_html__( 'Booking #%s details', 'marketking' ), esc_html( $post->ID ) );
				?>
			</h2>
		</div><!-- .nk-block-head-content -->
		<div class="nk-block-head-content">

			<div class="toggle-wrap nk-block-tools-toggle">
				<a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1"
				   data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
				<div class="toggle-expand-content" data-content="pageMenu">
					<ul class="nk-block-tools g-3">

						<input type="hidden" name="resource_id"
						       id="marketking_save_booking_order_button_id"
						       value="<?php echo esc_attr( $post_id ); ?>">
						<input type="hidden" id="post_ID"
						       value="<?php echo esc_attr( $post_id ); ?>">

						<li class="nk-block-tools-opt">
							<div id="marketking_save_booking_order_button">
								<a href="#"
								   class="toggle btn btn-icon btn-primary d-md-none"><em
											class="icon ni <?php echo esc_attr( $icon ); ?>"></em></a>
								<a href="#"
								   class="toggle btn btn-primary d-none d-md-inline-flex"><em
											class="icon ni <?php echo esc_attr( $icon ); ?>"></em>
									<span><?php echo esc_html( $text ); ?></span>
								</a>
							</div>
							<?php
							if ( $exists === 'existing' ) {
								// additional buttons for View Resource and Remove Resource
								?>
								<div class="dropdown">
									<a href="#"
									   class="dropdown-toggle btn btn-icon btn-gray btn-trigger ml-2 text-white pl-2 pr-3"
									   data-toggle="dropdown"><em
												class="icon ni ni-more-h"></em><?php esc_html_e( 'More', 'marketking' ); ?>
									</a>
									<div class="dropdown-menu dropdown-menu-right">
										<ul class="link-list-opt no-bdr">

											<li><a href="<?php echo esc_url(
													trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'add-booking-order/'
												); ?>" class="
														       marketking_add_button_booking"
												><em
															class="icon ni ni-plus"></em>
													<span><?php esc_html_e( 'Add Booking',
															'marketking' ); ?></span>
												</a>
											</li>
											<!--<li><a href="#"
												       class="toggle
														       marketking_delete_button_resource"
												       value="<?php /*echo esc_attr( $post_id ); */ ?>"><em
																class="icon ni ni-trash"></em>
														<span><?php /*esc_html_e( 'Delete Booking',
																'marketking' ); */ ?></span>
													</a></li>-->
										</ul>
									</div>
								</div>
								<?php
							}
							?>
						</li>
					</ul>
				</div>
			</div>
		</div><!-- .nk-block-head-content -->

	</div>
</div>

<div class="nk-block">
<div class="card">
<div class="card-aside-wrap">
<div class="card-content">
<div class="card-inner">

<p class="booking_number">
	<?php
	if ( $order ) {
		/* translators: 1: href to order id */
		printf( ' ' . esc_html__( 'Linked to order %s.', 'marketking' ), '<a href="' . esc_url(
				trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) .
				'manage-order/' . $order->get_order_number() ) . '">#' . esc_html( $order->get_order_number() ) . '</a>' );
	}

	if ( $product && is_callable( array(
			$product,
			'is_bookings_addon'
		) ) && $product->is_bookings_addon() ) {
		/* translators: 1: bookings addon title */
		printf( ' ' . esc_html__( 'Booking type: %s', 'marketking' ), esc_html( $product->bookings_addon_title() ) );
	}
	?>
</p>

<div class="booking_data_column_container">
<div class="booking_data_column">
	<h4><?php esc_html_e( 'General details', 'marketking' ); ?></h4>

	<p class="form-field form-field-wide">
		<label for="_booking_order_id"><?php esc_html_e( 'Order ID:', 'marketking'
			); ?></label>
		<span style="font-size: 14px;border:1px solid #777;padding: 5px 10px !important;border-radius: 5px;">
						<?php

						if ( $booking->get_order_id() && $order ) {
							echo __( '<span style="font-weight:600;color:black;">'
							         . $order->get_order_number() . '</span>' . ' &ndash; ' .
							         date_i18n(
								         wc_bookings_date_format(), strtotime( is_callable( array(
								         $order,
								         'get_date_created'
							         ) ) ? $order->get_date_created() : $order->post_date ) ) );
						}

						?>
					</span>
		<input type="hidden" name="_booking_order_id"
		       value="<?php echo $booking->get_order_id(); ?>">
		<!--<select name="_booking_order_id" id="_booking_order_id" data-placeholder="<?php /*esc_attr_e( 'N/A', 'marketking' ); */ ?>" data-allow_clear="true">
				<?php /*if ( $booking->get_order_id() && $order ) : */ ?>
					<option selected="selected" value="<?php /*echo esc_attr( $booking->get_order_id() ); */ ?>"><?php /*echo esc_html( $order->get_order_number() . ' &ndash; ' . date_i18n( wc_bookings_date_format(), strtotime( is_callable( array( $order, 'get_date_created' ) ) ? $order->get_date_created() : $order->post_date ) ) ); */ ?></option>
				<?php /*endif; */ ?>
			</select>-->
	</p>

	<p class="form-field form-field-wide" style="margin-top: 20px;"><label
				for="booking_date"><?php esc_html_e( 'Date created:', 'marketking' ); ?></label>

		<input type="text" name="booking_date" id="booking_date"
		       maxlength="10"
		       readonly="readonly"
		       value="<?php echo esc_attr( date_i18n( 'Y-m-d', $booking->get_date_created() ) ); ?>"
		       pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"/> @ <input
				type="text" class="hour" style="width: 4.5em;"
				readonly="readonly"
				placeholder="<?php esc_attr_e( 'h', 'marketking' ); ?>"
				name="booking_date_hour" id="booking_date_hour" maxlength="2" size="2"
				value="<?php echo esc_attr( date_i18n( 'H', $booking->get_date_created() ) ); ?>"
				min="0" max="23"/>:<input type="text" class="minute" style="width: 4.5em;"
		                                  placeholder="<?php esc_attr_e( 'm', 'marketking' ); ?>"
		                                  name="booking_date_minute" id="booking_date_minute"
		                                  maxlength="2" size="2"
		                                  readonly="readonly"
		                                  value="<?php echo esc_attr( date_i18n( 'i', $booking->get_date_created() ) ); ?>"
		                                  min="0" max="59"/>
	</p>

	<p class="form-field form-field-wide">
		<label for="_booking_status"><?php esc_attr_e( 'Booking status:', 'marketking' ); ?></label>
		<select id="_booking_status" name="_booking_status" class="wc-enhanced-select">
			<?php
			foreach ( $statuses as $key => $value ) {
				echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $booking->get_status(), false ) . '>' . esc_html( $value ) . '</option>';
			}
			?>
		</select>
		<input type="hidden" name="post_status"
		       value="<?php echo esc_attr( $booking->get_status() ); ?>">
	</p>

	<p class="form-field form-field-wide" style="margin-top: 20px;">
		<label for="_booking_customer_id"><?php esc_html_e( 'Customer:', 'marketking' ); ?></label>
		<?php
		$name              = ! empty( $customer->name ) ? ' &ndash; ' . $customer->name : '';
		$guest_placeholder = __( 'Guest', 'marketking' );
		if ( 'Guest' === $name ) {
// translators: 1: guest name
			$guest_placeholder = sprintf( _x( 'Guest (%s)', 'Admin booking guest placeholder', 'marketking' ), $name );
		}

		if ( $booking->get_customer_id() ) {
			$user            = get_user_by( 'id', $booking->get_customer_id() );
			$customer_string = sprintf(
// translators: 1: full name 2: user id 3: email
				esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'marketking' ),
				$user ? trim( $user->first_name . ' ' . $user->last_name ) : $customer->name,
				$customer->user_id,
				$customer->email
			);
		} else {
			$customer_string = '';
		}
		?>

		<input type="hidden" name="_booking_customer_id" id="_booking_customer_id" value="<?php
		echo esc_attr( $booking->get_customer_id() ); ?>" placeholder="<?php echo esc_attr(
			$customer_string ); ?>">

		<?php

		if ( $booking->get_customer_id() ) {

			echo '<label style="font-size: 14px;border:1px solid #777;padding: 5px 10px !important;border-radius: 5px;color: black;cursor:initial;">' . esc_attr( $customer_string ) . '</label>';
		} else {
			echo '<label style="font-size: 14px;border:1px solid #777;padding: 5px 10px !important;border-radius: 5px;color: black;cursor:initial;">' . esc_attr( $guest_placeholder ) . '</label>';

		}
		?>

		<!--<select name="_booking_customer_id" id="_booking_customer_id" class="wc-customer-search"
			        data-placeholder="<?php /*echo esc_attr( $guest_placeholder ); */ ?>"
			        data-allow_clear="true">
				<?php /*if ( $booking->get_customer_id() ) : */ ?>
					<option selected="selected"
					        value="<?php /*echo esc_attr( $booking->get_customer_id() ); */ ?>"><?php /*echo esc_attr( $customer_string ); */ ?></option>
				<?php /*endif; */ ?>
			</select>-->
	</p>

	<p class="form-field form-field-wide">
		<label><?php esc_html_e( 'Metadata:', 'marketking' ); ?></label>
		<?php
		$order_item_id = $booking->get_order_item_id();
		$order_item    = new WC_Order_Item_Product( $order_item_id );
		?>

	<table cellspacing="0" class="wc_bookings_metadata_table">
		<?php foreach ( $order_item->get_formatted_meta_data() as $meta ) : ?>
			<tr>
				<th><?php echo wp_kses_post( $meta->display_key ); ?>:</th>
				<td><?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	</p>
	<?php do_action( 'woocommerce_admin_booking_data_after_booking_details', $post->ID ); ?>

</div>
<div class="booking_data_column">
	<h4><?php esc_html_e( 'Booking specification', 'marketking' ); ?></h4>
	<p class="form-field form-field-wide">
			<span for="booked_product"
			      class=""><strong><?php _e( 'Booked product:', 'marketking' ) ?></strong></span>
		<?php

		if ( $product_id ) {
			$product_post = get_post( $product_id );
			echo '<label style="font-size: 14px;border:1px solid #777;padding: 5px 10px !important;border-radius: 5px;"><a class="" href="' . get_permalink
				( $product_id ) . '" target="_blank">' . $product_post->post_title . '</a></label>';
		} else {
			echo '-';
		}
		?>
	</p>
	<?php if ( isset($resource) ) { ?>
		<p class="form-field form-field-wide"
		   style="font-size: 14px;border:1px solid #777;padding: 5px 10px !important;border-radius: 5px;">
				<span for="booked_product"
				      class=""><strong><?php _e( 'Resource:', 'marketking-multivendor-marketplace-for-woocommerce' ) ?></strong></span>
			<?php
			echo esc_html( $resource->post_title );
			?>
		</p>
	<?php } ?>
	<?php


	/*woocommerce_wp_select( array(
		'id'            => 'product_or_resource_id',
		'class'         => 'wc-enhanced-select',
		'wrapper_class' => 'form-field form-field-wide',
		'label'         => __( 'Booked product:', 'marketking' ),
		'options'       => $bookable_products,
		'value'         => $resource_id ? $product_id . '=>' . $resource_id : $product_id,
	) );*/


	woocommerce_wp_text_input( array(
		'id'                => '_booking_parent_id',
		'label'             => __( 'Parent booking ID:', 'marketking' ),
		'wrapper_class'     => 'form-field form-field-wide',
		'placeholder'       => 'N/A',
		'class'             => '',
		'value'             => $booking->get_parent_id() ? $booking->get_parent_id() : '',
		'custom_attributes' => array(
			'readonly' => 'readonly'
		)
	) );


	$person_counts = $booking->get_person_counts();

	echo '<br class="clear" />';
	echo '<h4>' . esc_html__( 'Person(s)', 'marketking' ) . '</h4>';

	$person_types = $product ? $product->get_person_types() : array();

	if ( count( $person_counts ) > 0 || count( $person_types ) > 0 ) {
		$needs_update = false;
		$pfirst       = true;
		foreach ( $person_counts as $person_id => $person_count ) {
			$person_type = null;

			try {
				$person_type = new WC_Product_Booking_Person_Type( $person_id );
			} catch ( Exception $e ) {
				// This person type was deleted from the database.
				unset( $person_counts[ $person_id ] );
				$needs_update = true;
			}


			if ( $person_type ) {
				/*echo '<p class="form-field form-field-wide" style="font-size: 14px;border:1px solid #777;padding: 5px 10px !important;border-radius: 5px;">';*/

				/*echo esc_html( $person_type->get_name() ) . ': ';
				echo '<span style="color: black;">' . esc_attr( $person_count ) . '</span>';*/

				woocommerce_wp_text_input( array(
					'id'                => '_booking_person_' . $person_id,
					'label'             => $person_type->get_name(),
					'type'              => 'text',
					'placeholder'       => '0',
					'value'             => $person_count,
					'wrapper_class'     => 'booking-person',
					'custom_attributes' => array(
						'readonly' => 'readonly'
					)
				) );
				/*echo '</p>';*/
			}

		}

		if ( $needs_update ) {
			$booking->set_person_counts( $person_counts );
			$booking->save();
		}

		$product_booking_diff = array_diff( array_keys( $person_types ), array_keys( $person_counts ) );

		foreach ( $product_booking_diff as $id ) {
			$person_type = $person_types[ $id ];
			/*echo esc_html( $person_type->get_name() ) . ': ';
			echo '<span style="color: black;">' . esc_attr( $person_count ) . '</span>';*/
			woocommerce_wp_text_input( array(
				'id'                => '_booking_person_' . $person_type->get_id(),
				'label'             => $person_type->get_name(),
				'type'              => 'text',
				'placeholder'       => '0',
				'value'             => '0',
				'wrapper_class'     => 'booking-person',
				'custom_attributes' => array(
					'readonly' => 'readonly'
				)
			) );
		}
	} else {
		$person_counts = $booking->get_person_counts();
		$person_type   = new WC_Product_Booking_Person_Type( 0 );

		?>
		<!--<p class="form-field form-field-wide" style="font-size: 14px;">
				<span for="booked_product"
				      class=""><strong><?php /*_e( 'Quantity: ', 'marketking' ) */ ?></strong>
				</span>
				<span><?php /*echo ! empty( $person_counts[0] ) ? $person_counts[0] : 0; */ ?></span>
			</p>-->
		<?php
		woocommerce_wp_text_input( array(
			'id'                => '_booking_person_0',
			'label'             => $person_type->get_name(),
			'type'              => 'text',
			'placeholder'       => '0',
			'value'             => ! empty( $person_counts[0] ) ? $person_counts[0] : 0,
			'wrapper_class'     => 'booking-person',
			'custom_attributes' => array(
				'readonly' => 'readonly'
			)
		) );
	}
	?>
</div>
<div class="booking_data_column">
	<h4><?php esc_html_e( 'Booking date &amp; time', 'marketking' ); ?></h4>

	<?php
	woocommerce_wp_text_input( array(
		'id'          => 'booking_start_date',
		'label'       => __( 'Start date:', 'marketking' ),
		'placeholder' => 'yyyy-mm-dd',
		'value'       => date( 'Y-m-d', $booking->get_start( 'edit' ) ),
		'class'       => 'date-picker-field',
	) );

	?>

	<?php

	woocommerce_wp_text_input( array(
		'id'          => 'booking_end_date',
		'label'       => __( 'End date:', 'marketking' ),
		'placeholder' => 'yyyy-mm-dd',
		'value'       => date( 'Y-m-d', $booking->get_end( 'edit' ) ),
		'class'       => 'date-picker-field',
	) );

	woocommerce_wp_checkbox( array(
		'id'          => '_booking_all_day',
		'label'       => __( 'All day booking:', 'marketking' ),
		'description' => __( 'Check this box if the booking is for all day.', 'marketking' ),
		'value'       => $booking->get_all_day( 'edit' ) ? 'yes' : 'no',
	) );

	woocommerce_wp_text_input( array(
		'id'          => 'booking_start_time',
		'label'       => __( 'Start time:', 'marketking' ),
		'placeholder' => 'hh:mm',
		'value'       => date( 'H:i', $booking->get_start( 'edit' ) ),
		'type'        => 'time',
	) );

	woocommerce_wp_text_input( array(
		'id'          => 'booking_end_time',
		'label'       => __( 'End time:', 'marketking' ),
		'placeholder' => 'hh:mm',
		'value'       => date( 'H:i', $booking->get_end( 'edit' ) ),
		'type'        => 'time',
	) );

	if ( wc_should_convert_timezone( $booking ) ) {
		woocommerce_wp_text_input( array(
			'id'                => 'booking_start_time',
			'label'             => __( 'Start time (local timezone):', 'marketking' ),
			'placeholder'       => 'hh:mm',
			'value'             => date( 'H:i', $booking->get_start( 'edit', true ) ),
			'type'              => 'time',
			'custom_attributes' => array( 'disabled' => 'disabled' ),
		) );

		woocommerce_wp_text_input( array(
			'id'                => 'booking_end_time',
			'label'             => __( 'End time (local timezone):', 'marketking' ),
			'placeholder'       => 'hh:mm',
			'value'             => date( 'H:i', $booking->get_end( 'edit', true ) ),
			'type'              => 'time',
			'custom_attributes' => array( 'disabled' => 'disabled' ),
		) );
	}
	?>
</div>
</div>
</div>
<div class="clear"></div>
</div>

<div id="marketking_order_notes_container" class="card-aside card-aside-right"
     data-content="userAside" data-toggle-screen="xxl" data-toggle-overlay="true"
     data-toggle-body="true">
	<div class="card-inner-group">

		<div class="card-inner">
			<div class="overline-title-alt mb-2 marketking_order_totals_title"><?php esc_html_e( 'Customer Details', 'marketking' ); ?></div>
			<div class="profile-balance">
				<div class="profile-balance-group gx-4">
					<div class="profile-balance-sub" style="width: 100%;">

						<?php

						Marketking_WC_Bookings_Order_Metabox::customer_meta_box_inner( $post );
						?>
					</div>

				</div>
			</div>
		</div><!-- .card-inner -->
	</div><!-- .card-content -->

</div><!-- .card-aside-wrap -->
</div><!-- .card -->
</div><!-- .nk-block -->

</form>
</div>
</div>
</div>
</div>
