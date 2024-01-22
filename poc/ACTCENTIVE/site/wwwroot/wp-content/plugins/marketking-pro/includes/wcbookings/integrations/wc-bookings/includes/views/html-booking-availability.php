<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="bookings_availability" class="panel woocommerce_options_panel">
<div class="options_group">
<?php
$min_date      = $bookable_product->get_min_date_value( 'edit' );
$min_date_unit = $bookable_product->get_min_date_unit( 'edit' );
$max_date      = 0 === $bookable_product->get_max_date_value( 'edit' ) ? 1 : $bookable_product->get_max_date_value( 'edit' );
$max_date_unit = $bookable_product->get_max_date_unit( 'edit' );

woocommerce_wp_text_input( array(
	'id'                => '_wc_booking_qty',
	'label'             => __( 'Max bookings per block', 'marketking' ),
	'description'       => __( 'The maximum bookings allowed for each block. Can be overridden at resource level.', 'marketking' ),
	'value'             => $bookable_product->get_qty( 'edit' ),
	'desc_tip'          => true,
	'type'              => 'number',
	'custom_attributes' => array(
		'min'  => '',
		'step' => '1',
	),
) );

?>
<p class="form-field">
	<label for="_wc_booking_min_date"><?php esc_html_e( 'Minimum block bookable', 'marketking' ); ?></label>
	<input type="number" name="_wc_booking_min_date" id="_wc_booking_min_date"
	       value="<?php echo esc_attr( $min_date ); ?>" step="1" min="0"
	       style="margin-right: 7px; width: 4em;">
	<select name="_wc_booking_min_date_unit" id="_wc_booking_min_date_unit" class="short"
	        style="margin-right: 7px;">
		<option value="month" <?php selected( $min_date_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'marketking' ); ?></option>
		<option value="week" <?php selected( $min_date_unit, 'week' ); ?>><?php esc_html_e( 'Week(s)', 'marketking' ); ?></option>
		<option value="day" <?php selected( $min_date_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'marketking' ); ?></option>
		<option value="hour" <?php selected( $min_date_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'marketking' ); ?></option>
	</select> <?php esc_html_e( 'into the future', 'marketking' ); ?>
</p>
<p class="form-field">
	<label for="_wc_booking_max_date"><?php esc_html_e( 'Maximum block bookable', 'marketking' ); ?></label>
	<input type="number" name="_wc_booking_max_date" id="_wc_booking_max_date"
	       value="<?php echo esc_attr( $max_date ); ?>" step="1" min="1"
	       style="margin-right: 7px; width: 4em;">
	<select name="_wc_booking_max_date_unit" id="_wc_booking_max_date_unit" class="short"
	        style="margin-right: 7px;">
		<option value="month" <?php selected( $max_date_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'marketking' ); ?></option>
		<option value="week" <?php selected( $max_date_unit, 'week' ); ?>><?php esc_html_e( 'Week(s)', 'marketking' ); ?></option>
		<option value="day" <?php selected( $max_date_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'marketking' ); ?></option>
		<option value="hour" <?php selected( $max_date_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'marketking' ); ?></option>
	</select> <?php esc_html_e( 'into the future', 'marketking' ); ?>
</p>
<p class="form-field _wc_booking_buffer_period">
	<label for="_wc_booking_buffer_period"><?php esc_html_e( 'Require a buffer period of', 'marketking' ); ?></label>
	<input type="number" name="_wc_booking_buffer_period" id="_wc_booking_buffer_period"
	       value="<?php echo esc_attr( $bookable_product->get_buffer_period( 'edit' ) ); ?>"
	       step="1" min="0" style="margin-right: 7px; width: 4em;">
	<span class='_wc_booking_buffer_period_unit'></span>
	<?php esc_html_e( 'between bookings', 'marketking' ); ?>
</p>
<?php

woocommerce_wp_checkbox(
	array(
		'id'          => '_wc_booking_apply_adjacent_buffer',
		'value'       => $bookable_product->get_apply_adjacent_buffer( 'edit' ) ? 'yes' : 'no',
		'label'       => __( 'Adjacent Buffering?', 'marketking' ),
		'description' => __( 'By default buffer period applies forward into the future of a booking. Enabling this option will apply adjacently (before and after Bookings).', 'marketking' ),
	)
);

woocommerce_wp_select(
	array(
		'id'          => '_wc_booking_default_date_availability',
		'label'       => __( 'All dates are...', 'marketking' ),
		'description' => '',
		'value'       => $bookable_product->get_default_date_availability( 'edit' ),
		'options'     => array(
			'available'     => __( 'available by default', 'marketking' ),
			'non-available' => __( 'not-available by default', 'marketking' ),
		),
		'description' => __( 'This option affects how you use the rules below.', 'marketking' ),
	)
);

woocommerce_wp_select(
	array(
		'id'          => '_wc_booking_check_availability_against',
		'label'       => __( 'Check rules against...', 'marketking' ),
		'description' => '',
		'value'       => $bookable_product->get_check_start_block_only( 'edit' ) ? 'start' : '',
		'options'     => array(
			''      => __( 'All blocks being booked', 'marketking' ),
			'start' => __( 'The starting block only', 'marketking' ),
		),
		'description' => __( 'This option affects how bookings are checked for availability.', 'marketking' ),
	)
);
?>
<p class="form-field _wc_booking_first_block_time_field">
	<label for="_wc_booking_first_block_time"><?php esc_html_e( 'First block starts at...', 'marketking' ); ?></label>
	<input type="time" name="_wc_booking_first_block_time" id="_wc_booking_first_block_time"
	       value="<?php echo esc_attr( $bookable_product->get_first_block_time( 'edit' ) ); ?>"
	       placeholder="HH:MM"/>
</p>

<?php
$documentation_link = esc_url( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'docs' );

$more_info_link = sprintf( " <a href=\"%s\">%s</a>", $documentation_link, esc_html__( 'view our documentation here.', 'marketking' ) );
woocommerce_wp_checkbox(
	array(
		'id'          => '_wc_booking_has_restricted_days',
		'value'       => $bookable_product->has_restricted_days( 'edit' ) ? 'yes' : 'no',
		'label'       => __( 'Restrict selectable days?', 'marketking' ),
		'description' => __( 'Restrict the days of the week that are able to be selected on the calendar; this will not affect your availability.', 'marketking' ),
	)
);
?>

<div class="booking-day-restriction">
	<div class="day_table_grid">
		<table class="widefat wc_input_table_wrapper">
			<tbody>
			<tr>

				<?php
				$weekdays = array(
					__( 'Sunday', 'marketking' ),
					__( 'Monday', 'marketking' ),
					__( 'Tuesday', 'marketking' ),
					__( 'Wednesday', 'marketking' ),
					__( 'Thursday', 'marketking' ),
					__( 'Friday', 'marketking' ),
					__( 'Saturday', 'marketking' ),
				);

				for ( $i = 0; $i < 7; $i ++ ) {
					?>
					<td>
						<label class="checkbox"
						       for="_wc_booking_restricted_days[<?php echo esc_attr( $i ); ?>]"
						       style="width: auto;"><?php echo esc_html( $weekdays[ $i ] ); ?>&nbsp;</label>
						<input type="checkbox" class="checkbox"
						       name="_wc_booking_restricted_days[<?php echo esc_attr( $i ); ?>]"
						       id="_wc_booking_restricted_days[<?php echo esc_attr( $i ); ?>]"
						       value="<?php echo esc_attr( $i ); ?>" <?php checked( $restricted_days[ $i ], $i ); ?>>
					</td>
					<?php
				}
				?>

			</tr>
			</tbody>
		</table>
	</div>
</div>

</div>
<div class="options_group">
	<div class="table_grid">
		<table class="widefat">
			<thead>
			<tr>
				<th class="sort" width="1%">&nbsp;</th>
				<th><?php esc_html_e( 'Range type', 'marketking' ); ?></th>
				<th><?php esc_html_e( 'Range', 'marketking' ); ?></th>
				<th></th>
				<th></th>
				<th><?php esc_html_e( 'Bookable', 'marketking' ); ?>&nbsp;<a class="tips"
				                                                                       data-tip="<?php echo wc_sanitize_tooltip( __( 'If not bookable, users won\'t be able to choose this block for their booking.', 'marketking' ) ); ?>">[?]</a>
				</th>
				<th><?php esc_html_e( 'Priority', 'marketking' ); ?>&nbsp;<a class="tips"
				                                                                       data-tip="<?php echo wc_sanitize_tooltip( get_wc_booking_priority_explanation() ); ?>">[?]</a>
				</th>
				<th class="remove" width="1%">&nbsp;</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="6">
					<a href="#" class="button add_row" data-row="<?php
					ob_start();
					include 'html-booking-availability-fields.php';
					$html = ob_get_clean();
					echo esc_attr( $html );
					?>"><?php esc_html_e( 'Add Range', 'marketking' ); ?></a>
					<span class="description"><?php 
					$priority_rules = get_wc_booking_rules_explanation();
					foreach ( $priority_rules as $priority_rule ) {
						echo esc_html( $priority_rule ) . '<br>';
					}

					?></span>
				</th>
			</tr>
			</tfoot>
			<tbody id="availability_rows">
			<?php
			$values = $bookable_product->get_availability( 'edit' );
			if ( ! empty( $values ) && is_array( $values ) ) {
				foreach ( $values as $availability ) {
					include 'html-booking-availability-fields.php';
				}
			}
			?>
			</tbody>
		</table>
	</div>
</div>
</div>
