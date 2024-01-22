<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$intervals = array();

$intervals['months'] = array(
	'1'  => __( 'January', 'marketking' ),
	'2'  => __( 'February', 'marketking' ),
	'3'  => __( 'March', 'marketking' ),
	'4'  => __( 'April', 'marketking' ),
	'5'  => __( 'May', 'marketking' ),
	'6'  => __( 'June', 'marketking' ),
	'7'  => __( 'July', 'marketking' ),
	'8'  => __( 'August', 'marketking' ),
	'9'  => __( 'September', 'marketking' ),
	'10' => __( 'October', 'marketking' ),
	'11' => __( 'November', 'marketking' ),
	'12' => __( 'December', 'marketking' ),
);

$intervals['days'] = array(
	'1' => __( 'Monday', 'marketking' ),
	'2' => __( 'Tuesday', 'marketking' ),
	'3' => __( 'Wednesday', 'marketking' ),
	'4' => __( 'Thursday', 'marketking' ),
	'5' => __( 'Friday', 'marketking' ),
	'6' => __( 'Saturday', 'marketking' ),
	'7' => __( 'Sunday', 'marketking' ),
);

for ( $i = 1; $i <= 52; $i ++ ) {
	/* translators: 1: week number */
	$intervals['weeks'][ $i ] = sprintf( __( 'Week %s', 'marketking' ), $i );
}

if ( ! isset( $pricing['type'] ) ) {
	$pricing['type'] = 'custom';
}
if ( ! isset( $pricing['modifier'] ) ) {
	$pricing['modifier'] = '';
}
if ( ! isset( $pricing['base_modifier'] ) ) {
	$pricing['base_modifier'] = '';
}
if ( ! isset( $pricing['base_cost'] ) ) {
	$pricing['base_cost'] = '';
}

// In the loop of saved items an index is supplied, but we need one for the
// add new cost range button so we can replace it when adding and index on the front end.
$index = isset( $index ) ? $index : 'bookings_cost_js_index_replace';
?>
<tr>
	<td class="sort">&nbsp;</td>
	<td>
		<div class="select wc_booking_pricing_type">
			<select name="wc_booking_pricing_type[<?php echo esc_attr( $index ); ?>]">
				<option value="custom" <?php selected( $pricing['type'], 'custom' ); ?>><?php esc_html_e( 'Date range', 'marketking' ); ?></option>
				<option value="months" <?php selected( $pricing['type'], 'months' ); ?>><?php esc_html_e( 'Range of months', 'marketking' ); ?></option>
				<option value="weeks" <?php selected( $pricing['type'], 'weeks' ); ?>><?php esc_html_e( 'Range of weeks', 'marketking' ); ?></option>
				<option value="days" <?php selected( $pricing['type'], 'days' ); ?>><?php esc_html_e( 'Range of days', 'marketking' ); ?></option>
				<option value="time" <?php selected( $pricing['type'], 'time' ); ?>><?php esc_html_e( 'Time Range', 'marketking' ); ?></option>
				<option value="persons" <?php selected( $pricing['type'], 'persons' ); ?>><?php esc_html_e( 'Person count', 'marketking' ); ?></option>
				<option value="blocks" <?php selected( $pricing['type'], 'blocks' ); ?>><?php esc_html_e( 'Block count', 'marketking' ); ?></option>
				<optgroup label="<?php esc_html_e( 'Time Ranges', 'marketking' ); ?>">
					<option value="time" <?php selected( $pricing['type'], 'time' ); ?>><?php esc_html_e( 'Time Range (all week)', 'marketking' ); ?></option>
					<option value="time:range" <?php selected( $pricing['type'], 'time:range' ); ?>><?php esc_html_e( 'Date Range with time', 'marketking' ); ?></option>
					<?php foreach ( $intervals['days'] as $key => $label ) : ?>
						<option value="time:<?php echo esc_attr( $key ); ?>" <?php selected( $pricing['type'], 'time:' . $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			</select>
		</div>
	</td>
	<td style="border-right:0;">
	<div class="bookings-datetime-select-from">
		<div class="select from_day_of_week">
			<select name="wc_booking_pricing_from_day_of_week[<?php echo esc_attr( $index ); ?>]">
				<?php foreach ( $intervals['days'] as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select from_month">
			<select name="wc_booking_pricing_from_month[<?php echo esc_attr( $index ); ?>]">
				<?php foreach ( $intervals['months'] as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select from_week">
			<select name="wc_booking_pricing_from_week[<?php echo esc_attr( $index ); ?>]">
				<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="from_date">
			<?php
			$from_date = '';
			if ( 'custom' === $pricing['type'] && ! empty( $pricing['from'] ) ) {
				$from_date = $pricing['from'];
			} elseif ( 'time:range' === $pricing['type'] && ! empty( $pricing['from_date'] ) ) {
				$from_date = $pricing['from_date'];
			}
			?>
			<input type="text" class="date-picker" name="wc_booking_pricing_from_date[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $from_date ); ?>" />
		</div>

		<div class="from_time">
			<input type="time" class="time-picker" name="wc_booking_pricing_from_time[<?php echo esc_attr( $index ); ?>]" value="<?php
			if ( strrpos( $pricing['type'], 'time' ) === 0 && ! empty( $pricing['from'] ) ) {
				echo esc_attr( $pricing['from'] );
			}
			?>" placeholder="HH:MM" />
		</div>

		<div class="from">
			<input type="number" step="1" name="wc_booking_pricing_from[<?php echo esc_attr( $index ); ?>]" value="<?php
			if ( ! empty( $pricing['from'] ) && is_numeric( $pricing['from'] ) ) {
				echo esc_attr( $pricing['from'] );
			}
			?>" />
		</div>
	</div>
	</td>
	<td style="border-right:0;" width="25px;" class="bookings-to-label-row">
		<p><?php esc_html_e( 'to', 'marketking' ); ?></p>
		<p class="bookings-datetimerange-second-label"><?php esc_html_e( 'to', 'marketking' ); ?></p>
	</td>
	<td>
	<div class="bookings-datetime-select-to">
		<div class="select to_day_of_week">
			<select name="wc_booking_pricing_to_day_of_week[<?php echo esc_attr( $index ); ?>]">
				<?php foreach ( $intervals['days'] as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select to_month">
			<select name="wc_booking_pricing_to_month[<?php echo esc_attr( $index ); ?>]">
				<?php foreach ( $intervals['months'] as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select to_week">
			<select name="wc_booking_pricing_to_week[<?php echo esc_attr( $index ); ?>]">
				<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="to_date">
			<?php
			$to_date = '';
			if ( 'custom' === $pricing['type'] && ! empty( $pricing['to'] ) ) {
				$to_date = $pricing['to'];
			} elseif ( 'time:range' === $pricing['type'] && ! empty( $pricing['to_date'] ) ) {
				$to_date = $pricing['to_date'];
			}
			?>
			<input type="text" class="date-picker" name="wc_booking_pricing_to_date[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $to_date ); ?>" />
		</div>

		<div class="to_time">
			<input type="time" class="time-picker" name="wc_booking_pricing_to_time[<?php echo esc_attr( $index ); ?>]" value="<?php
			if ( strrpos( $pricing['type'], 'time' ) === 0 && ! empty( $pricing['to'] ) ) {
				echo esc_attr( $pricing['to'] );
			}
			?>" placeholder="HH:MM" />
		</div>

		<div class="to">
			<input type="number" step="1" name="wc_booking_pricing_to[<?php echo esc_attr( $index ); ?>]" value="<?php
			if ( ! empty( $pricing['to'] ) && is_numeric( $pricing['to'] ) ) {
				echo esc_attr( $pricing['to'] );
			}
			?>" />
		</div>
	</div>
	</td>
	<td>
		<div class="select">
			<select name="wc_booking_pricing_base_cost_modifier[<?php echo esc_attr( $index ); ?>]">
				<option <?php selected( $pricing['base_modifier'], '' ); ?> value="">+</option>
				<option <?php selected( $pricing['base_modifier'], 'minus' ); ?> value="minus">-</option>
				<option <?php selected( $pricing['base_modifier'], 'times' ); ?> value="times">&times;</option>
				<option <?php selected( $pricing['base_modifier'], 'divide' ); ?> value="divide">&divide;</option>
				<option <?php selected( $pricing['base_modifier'], 'equals' ); ?> value="equals">=</option>
			</select>
		</div>
		<input type="number" step="0.01" name="wc_booking_pricing_base_cost[<?php echo esc_attr( $index ); ?>]" value="<?php
		if ( ! empty( $pricing['base_cost'] ) ) {
			echo esc_attr( $pricing['base_cost'] );
		}
		?>" placeholder="0" />
	<?php do_action( 'woocommerce_bookings_after_booking_pricing_base_cost', $pricing, $post->ID ); ?>
	</td>
	<td>
		<div class="select">
			<select name="wc_booking_pricing_cost_modifier[<?php echo esc_attr( $index ); ?>]">
				<option <?php selected( $pricing['modifier'], '' ); ?> value="">+</option>
				<option <?php selected( $pricing['modifier'], 'minus' ); ?> value="minus">-</option>
				<option <?php selected( $pricing['modifier'], 'times' ); ?> value="times">&times;</option>
				<option <?php selected( $pricing['modifier'], 'divide' ); ?> value="divide">&divide;</option>
				<option <?php selected( $pricing['modifier'], 'equals' ); ?> value="equals">=</option>
			</select>
		</div>
		<input type="number" step="0.01" name="wc_booking_pricing_cost[<?php echo esc_attr( $index ); ?>]" value="<?php
		if ( ! empty( $pricing['cost'] ) ) {
			echo esc_attr( $pricing['cost'] );
		}
		?>" placeholder="0" />
	<?php do_action( 'woocommerce_bookings_after_booking_pricing_cost', $pricing, $post->ID ); ?>
	</td>
	<td class="remove">&nbsp;</td>
</tr>
