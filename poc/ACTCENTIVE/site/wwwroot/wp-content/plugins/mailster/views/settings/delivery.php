<?php echo mailster()->beacon( '611bb9f86ffe270af2a9995c', true ); ?>
<table class="form-table">
	<tr valign="top" class="settings-row settings-row-number-of-mails-sent">
		<th scope="row"><?php esc_html_e( 'Number of mails sent', 'mailster' ); ?><?php echo mailster()->beacon( '611bbcc7b37d837a3d0e47e8' ); ?></th>
		<td>
			<p><?php printf( esc_html__( 'Send max %s emails in one batch.', 'mailster' ), '<input type="number" min="1" name="mailster_options[send_at_once]" value="' . mailster_option( 'send_at_once' ) . '" class="small-text" ' . disabled( mailster_option( 'auto_send_at_once' ), true, false ) . '>' ); ?></p>
			<p><label><input type="hidden" name="mailster_options[auto_send_at_once]" value=""><input class="toggle-auto_send_at_once" type="checkbox" name="mailster_options[auto_send_at_once]" value="1" <?php checked( mailster_option( 'auto_send_at_once' ) ); ?>> <?php esc_html_e( 'automatically calculate this value.', 'mailster' ); ?></label><?php echo mailster()->beacon( '611badd0b55c2b04bf6df0a4' ); ?></p>
			<p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'ESP Limits', 'mailster' ); ?></th>
		<td><p><?php printf( esc_html__( 'My email service provider let me send %1$s within %2$s hours.', 'mailster' ), '<input type="number" min="1" name="mailster_options[send_limit]" value="' . mailster_option( 'send_limit' ) . '" class="small-text" style="width:70px">', '<input type="number" min="1" name="mailster_options[send_period]" value="' . mailster_option( 'send_period' ) . '" class="small-text">' ); ?></p>
	<?php

		$sent_this_period = get_transient( '_mailster_send_period', 0 );
		$mails_left       = max( 0, mailster_option( 'send_limit' ) - $sent_this_period );
		$next_reset       = get_option( '_transient_timeout__mailster_send_period_timeout' );
		$timeoffset       = mailster( 'helper' )->gmt_offset( true );
		$timestamp        = current_time( 'timestamp' );
		$time_format      = get_option( 'time_format' );

	if ( ! $next_reset || $next_reset < time() ) {
		$next_reset = time() + mailster_option( 'send_period' ) * 3600;
		$mails_left = mailster_option( 'send_limit' );
	}
	?>

	<p class="description"><?php printf( esc_html__( 'You can still send %1$s mails within the next %2$s', 'mailster' ), '<strong>' . number_format_i18n( $mails_left ) . '</strong>', '<strong title="' . date_i18n( $timeformat, $next_reset + $timeoffset, true ) . '">' . human_time_diff( $next_reset ) . '</strong>' ); ?> &ndash; <a href="edit.php?post_type=newsletter&page=mailster_settings&reset-limits=1&_wpnonce=<?php echo wp_create_nonce( 'mailster-reset-limits' ); ?>"><?php esc_html_e( 'reset these limits', 'mailster' ); ?></a></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-warmup">
		<th scope="row"><?php esc_html_e( 'Warmup', 'mailster' ); ?><?php echo mailster()->beacon( '611badcdf886c9486f8d989f' ); ?></th>
		<td><p><?php esc_html_e( 'Throttle the throughput for the next', 'mailster' ); ?> <select name="mailster_options[warmup]">
			<?php $mn = strtotime( 'midnight' ); ?>
			<option value="" <?php selected( ! mailster_option( 'warmup' ) ); ?>><?php esc_html_e( 'no warmup', 'mailster' ); ?> - 100%</option>
			<?php
			for ( $i = 1; $i <= 60; $i++ ) :
				$v = $mn + ( $i * DAY_IN_SECONDS );
				?>
				<option value="<?php echo $v; ?>" <?php selected( mailster_option( 'warmup' ), $v ); ?>><?php printf( esc_html__( _n( '%d day', '%d days', $i, 'mailster' ) ), $i ); ?> - <?php echo ceil( 100 * ( 1 - $i / 60 ) + 1 ) . '%'; ?></option>
			<?php endfor; ?>
			</select>
			</p>

			<p class="description"><?php esc_html_e( 'Mailster can "warmup" your current delivery method. It will gradually increase your sending volume over the defined time frame. This will help you getting started with a new domain or if you have recently switched your email provider.', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-time-frame">
		<th scope="row"><?php esc_html_e( 'Time Frame', 'mailster' ); ?><br>
		<p class="howto"><?php printf( esc_html__( 'It\'s %1$s, %2$s', 'mailster' ), date_i18n( 'l', $timestamp ), date_i18n( $time_format, $timestamp ) ); ?><br>
		<?php esc_html_e( 'Status', 'mailster' ); ?> : <?php mailster( 'helper' )->in_timeframe() ? esc_html_e( 'active', 'mailster' ) : esc_html_e( 'paused', 'mailster' ); ?></p>
		</th>
		<td><p><?php esc_html_e( 'send mails only between', 'mailster' ); ?>
			<?php $selected = mailster_option( 'time_frame_from' ); ?>
			<select name="mailster_options[time_frame_from]">
			<?php for ( $i = 0; $i < 24; $i++ ) : ?>
				<option value="<?php echo $i; ?>" <?php selected( $selected, $i ); ?>><?php echo date_i18n( $time_format, strtotime( 'midnight +' . $i . ' hours' ) ); ?></option>
			<?php endfor; ?>
			</select>
			<?php esc_html_e( 'and', 'mailster' ); ?>
			<?php $selected = mailster_option( 'time_frame_to' ); ?>
			<select name="mailster_options[time_frame_to]">
			<?php for ( $i = 0; $i < 24; $i++ ) : ?>
				<option value="<?php echo $i; ?>" <?php selected( $selected, $i ); ?>><?php echo date_i18n( $time_format, strtotime( 'midnight +' . $i . ' hours' ) ); ?></option>
			<?php endfor; ?>
			</select>
			<span class="utcoffset"><?php echo ( ( $timeoffset > 0 ) ? 'UTC + ' . ( $timeoffset / 3600 ) : '' ); ?></span></p>
			<p><?php esc_html_e( 'only on', 'mailster' ); ?>
			<?php
			$start_at       = get_option( 'start_of_week' );
			$time_frame_day = mailster_option( 'time_frame_day', array() );
			for ( $i = $start_at; $i < 7 + $start_at; $i++ ) {
				$j = $i;
				if ( $j >= 7 ) {
					$j = $j - 7;
				}

				echo '<label title="' . date_i18n( 'l', strtotime( 'sunday +' . $j . ' days' ) ) . '" class="weekday"><input name="mailster_options[time_frame_day][]" type="checkbox" value="' . $j . '" ' . checked( ( in_array( $j, $time_frame_day ) || ! $time_frame_day ), true, false ) . '>' . date_i18n( 'l', strtotime( 'sunday +' . $j . ' days' ) ) . '&nbsp;</label> ';
			}
			?>
			</p>
			<p class="description"><?php esc_html_e( 'Only affects Campaigns and Auto responders but not transactional emails.', 'mailster' ); ?></p>
	</tr>
	<tr valign="top" class="settings-row settings-row-split-campaigns">
		<th scope="row"><?php esc_html_e( 'Split campaigns', 'mailster' ); ?></th>
		<td><label><input type="hidden" name="mailster_options[split_campaigns]" value=""><input type="checkbox" name="mailster_options[split_campaigns]" value="1" <?php checked( mailster_option( 'split_campaigns' ) ); ?>> <?php esc_html_e( 'send campaigns simultaneously instead of one after the other', 'mailster' ); ?></label> </td>
	</tr>
	<tr valign="top" class="settings-row settings-row-pause-campaigns">
		<th scope="row"><?php esc_html_e( 'Pause campaigns', 'mailster' ); ?></th>
		<td><label><input type="hidden" name="mailster_options[pause_campaigns]" value=""><input type="checkbox" name="mailster_options[pause_campaigns]" value="1" <?php checked( mailster_option( 'pause_campaigns' ) ); ?>> <?php esc_html_e( 'pause campaigns if an error occurs', 'mailster' ); ?></label><p class="description"><?php esc_html_e( 'Mailster will change the status to "pause" if an error occur otherwise it tries to finish the campaign', 'mailster' ); ?></p></td>
	</tr>
	<tr valign="top" class="settings-row settings-row-time-between-mails">
		<th scope="row"><?php esc_html_e( 'Time between mails', 'mailster' ); ?></th>
		<td><p><input type="text" name="mailster_options[send_delay]" value="<?php echo mailster_option( 'send_delay' ); ?>" class="small-text"> <?php esc_html_e( 'milliseconds', 'mailster' ); ?></p><p class="description"><?php esc_html_e( 'define a delay between mails in milliseconds if you have problems with sending two many mails at once', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-max-execution-time">
		<th scope="row"><?php esc_html_e( 'Max. Execution Time', 'mailster' ); ?></th>
		<td><p><input type="text" name="mailster_options[max_execution_time]" value="<?php echo mailster_option( 'max_execution_time', 0 ); ?>" class="small-text"> <?php esc_html_e( 'seconds', 'mailster' ); ?></p><p class="description"><?php esc_html_e( 'define a maximum execution time to prevent server timeouts. If set to zero, no time limit is imposed.', 'mailster' ); ?></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-send-test">
		<th scope="row"><?php esc_html_e( 'Send Test', 'mailster' ); ?></th>
		<td>
		<div class="mailster-testmail">
			<input type="text" value="<?php echo esc_attr( $test_email ); ?>" autocomplete="off" class="form-input-tip mailster-testmail-email">
			<input type="button" value="<?php esc_attr_e( 'Send Test', 'mailster' ); ?>" class="button mailster_sendtest" data-role="basic">
			<div class="loading test-ajax-loading"></div>
		</div>
		</td>
	</tr>
</table>

	<?php

	$deliverymethods = array(
		'simple' => esc_html__( 'Simple', 'mailster' ),
		'smtp'   => 'SMTP',
	);
	$deliverymethods = apply_filters( 'mailster_delivery_methods', $deliverymethods );

	$method = mailster_option( 'deliverymethod', 'simple' );

	?>

<h3><?php esc_html_e( 'Delivery Method', 'mailster' ); ?><?php echo mailster()->beacon( '611bb9daf886c9486f8d992f' ); ?></h3>
<div class="updated inline"><p><?php printf( esc_html__( 'You are currently sending with the %s delivery method', 'mailster' ), '<strong>' . $deliverymethods[ $method ] . '</strong>' ); ?></p></div>

<div id="deliverynav" class="nav-tab-wrapper hide-if-no-js">
<?php
foreach ( $deliverymethods as $id => $name ) {

	$classes = array( 'nav-tab' );
	if ( $method == $id ) {
		$classes[] = 'nav-tab-active';
	}

	?>
	<a class="<?php echo implode( ' ', $classes ); ?>" href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></a>
	<?php } ?>
	<a href="<?php echo admin_url( 'edit.php?post_type=newsletter&page=mailster_addons&browse=delivery' ); ?>" class="button button-small alignright"><?php esc_html_e( 'More Delivery Methods', 'mailster' ); ?></a>
</div>

<input type="hidden" name="mailster_options[deliverymethod]" id="deliverymethod" value="<?php echo esc_attr( $method ); ?>" class="regular-text">

<?php foreach ( $deliverymethods as $id => $name ) : ?>
<div class="subtab" id="subtab-<?php echo $id; ?>"<?php echo $method == $id ? ' style="display:block"' : ''; ?>>
	<?php do_action( 'mailster_deliverymethod_tab', $id ); ?>
	<?php do_action( 'mailster_deliverymethod_tab_' . $id ); ?>
</div>
<?php endforeach; ?>
