<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<!--<div class="options_group hide_if_booking">-->
<div class="options_group show_if_booking">
	<?php
	$product_id    = $bookable_product->get_ID();
	$duration_type = $bookable_product->get_duration_type( 'edit' );
	$duration      = 0 === $bookable_product->get_duration( 'edit' ) ? 1 : $bookable_product->get_duration( 'edit' );
	$duration_unit = $bookable_product->get_duration_unit( 'edit' );
	?>
	<p class="form-field">
		<label for="_wc_booking_duration_type"><?php esc_html_e( 'Booking duration', 'marketking' ); ?></label>
		<select name="_wc_booking_duration_type" id="_wc_booking_duration_type" class="" style="width: auto; margin-right: 7px;">
			<option value="fixed" <?php selected( $duration_type, 'fixed' ); ?>><?php esc_html_e( 'Fixed blocks of', 'marketking' ); ?></option>
			<option value="customer" <?php selected( $duration_type, 'customer' ); ?>><?php esc_html_e( 'Customer defined blocks of', 'marketking' ); ?></option>
		</select>
		<input type="number" name="_wc_booking_duration" id="_wc_booking_duration" value="<?php echo esc_attr( $duration ); ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
		<select name="_wc_booking_duration_unit" id="_wc_booking_duration_unit" class="short" style="width: auto; margin-right: 7px;">
			<option value="month" <?php selected( $duration_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'marketking' ); ?></option>
			<option value="day" <?php selected( $duration_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'marketking' ); ?></option>
			<option value="hour" <?php selected( $duration_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'marketking' ); ?></option>
			<option value="minute" <?php selected( $duration_unit, 'minute' ); ?>><?php esc_html_e( 'Minute(s)', 'marketking' ); ?></option>
		</select>
	</p>

	<div id="min_max_duration">
	<?php

		woocommerce_wp_text_input( array(
			'id'                => '_wc_booking_min_duration',
			'label'             => __( 'Minimum duration', 'marketking' ),
			'description'       => __( 'The minimum allowed duration the user can input.', 'marketking' ),
			'value'             => $bookable_product->get_min_duration( 'edit' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array(
				'min'           => '0',
				'step'          => '1',
			),
		) );

		woocommerce_wp_text_input( array(
			'id'                => '_wc_booking_max_duration',
			'label'             => __( 'Maximum duration', 'marketking' ),
			'description'       => __( 'The maximum allowed duration the user can input.', 'marketking' ),
			'value'             => 0 === $bookable_product->get_max_duration( 'edit' ) ? 1 : $bookable_product->get_max_duration( 'edit' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array(
				'min'           => '1',
				'step'          => '1',
			),
		) );
		?>
		<div id="enable-range-picker">
			<?php
			woocommerce_wp_checkbox( array(
				'id'          => '_wc_booking_enable_range_picker',
				'value'       => $bookable_product->get_enable_range_picker( 'edit' ) ? 'yes' : 'no',
				'label'       => __( 'Enable Calendar Range Picker?', 'marketking' ),
				'description' => __( 'Lets the user select a start and end date on the calendar - duration will be calculated automatically.', 'marketking' ),
			) );
			?>
		</div>
	</div>

	<?php
		woocommerce_wp_select( array(
			'id'                 => '_wc_booking_calendar_display_mode',
			'value'              => $bookable_product->get_calendar_display_mode( 'edit' ),
			'label'              => __( 'Calendar display mode', 'marketking' ),
			'description'        => __( 'Choose how the calendar is displayed on the booking form.', 'marketking' ),
			'options'            => array(
				''               => __( 'Display calendar on click', 'marketking' ),
				'always_visible' => __( 'Calendar always visible', 'marketking' ),
			),
			'desc_tip'           => true,
			'class'              => 'select',
		) );

		/*
		woocommerce_wp_checkbox( array(
			'id'          => '_wc_booking_requires_confirmation',
			'value'       => $bookable_product->get_requires_confirmation( 'edit' ) ? 'yes' : 'no',
			'label'       => __( 'Requires confirmation?', 'marketking' ),
			'description' => __( 'Check this box if the booking requires admin approval/confirmation. Payment will not be taken during checkout.', 'marketking' ),
		) );
		*/

		woocommerce_wp_checkbox( array(
			'id'          => '_wc_booking_user_can_cancel',
			'value'       => $bookable_product->get_user_can_cancel( 'edit' ) ? 'yes' : 'no',
			'label'       => __( 'Can be cancelled?', 'marketking' ),
			'description' => __( 'Check this box if the booking can be cancelled by the customer after it has been purchased. A refund will not be sent automatically.', 'marketking' ),
		) );

		$cancel_limit      = $bookable_product->get_cancel_limit( 'edit' );
		$cancel_limit_unit = $bookable_product->get_cancel_limit_unit( 'edit' );
	?>
	<p class="form-field booking-cancel-limit">
		<label for="_wc_booking_cancel_limit"><?php esc_html_e( 'Booking can be cancelled until', 'marketking' ); ?></label>
		<input type="number" name="_wc_booking_cancel_limit" id="_wc_booking_cancel_limit" value="<?php echo esc_attr( $cancel_limit ); ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
		<select name="_wc_booking_cancel_limit_unit" id="_wc_booking_cancel_limit_unit" class="short" style="width: auto; margin-right: 7px;">
			<option value="month" <?php selected( $cancel_limit_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'marketking' ); ?></option>
			<option value="day" <?php selected( $cancel_limit_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'marketking' ); ?></option>
			<option value="hour" <?php selected( $cancel_limit_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'marketking' ); ?></option>
			<option value="minute" <?php selected( $cancel_limit_unit, 'minute' ); ?>><?php esc_html_e( 'Minute(s)', 'marketking' ); ?></option>
		</select>
		<span class="description"><?php esc_html_e( 'before the start date.', 'marketking' ); ?></span>
	</p>

	<script type="text/javascript">
		jQuery( '._tax_status_field' ).closest( '.show_if_simple' ).addClass( 'show_if_booking' );
		jQuery( 'select#_wc_booking_duration_unit, select#_wc_booking_duration_type, input#_wc_booking_duration' ).change(function(){
			if ( [ 'day', 'month' ].includes( jQuery('select#_wc_booking_duration_unit').val() ) && '1' == jQuery('input#_wc_booking_duration').val() && 'customer' === jQuery('select#_wc_booking_duration_type').val() ) {
				jQuery('p._wc_booking_enable_range_picker_field').show();
			} else {
				jQuery('p._wc_booking_enable_range_picker_field').hide();
			}
		});
		jQuery( '#_wc_booking_duration_unit' ).change();
	</script>
</div>
