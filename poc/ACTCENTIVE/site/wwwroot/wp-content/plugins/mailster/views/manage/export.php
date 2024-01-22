<?php
if ( mailster( 'subscribers' )->get_count_by_status( false ) ) :

	$lists   = mailster( 'lists' )->get( null, false );
	$no_list = mailster( 'lists' )->count( false );

	$user_settings = wp_parse_args(
		get_user_option( 'mailster_export_settings' ),
		array(
			'lists'        => wp_list_pluck( $lists, 'ID' ),
			'nolists'      => true,
			'status'       => array( 0, 1, 2, 3, 4 ),
			'conditions'   => array(),
			'header'       => false,
			'dateformat'   => 0,
			'outputformat' => 'xls',
			'separator'    => ';',
			'encoding'     => 'UTF-8',
			'performance'  => 1000,
			'column'       => array( 'ID', 'email', 'firstname', 'lastname' ),
		)
	);

	if ( isset( $_GET['conditions'] ) ) {
		$user_settings['conditions'] = (array) $_GET['conditions'];
	}
	if ( isset( $_GET['lists'] ) ) {
		$user_settings['lists'] = (array) $_GET['lists'];
	}

	?>
<h2><?php esc_html_e( 'Which subscribers do you like to export?', 'mailster' ); ?><?php echo mailster()->beacon( '611bbdf3f886c9486f8d9959' ); ?></h2>

<form method="post" id="export-subscribers">
		<?php wp_nonce_field( 'mailster_nonce' ); ?>
<h4><?php esc_html_e( 'Lists', 'mailster' ); ?></h4>
<section>
		<?php if ( ! empty( $lists ) ) : ?>
	<ul>
	<li><label><input type="checkbox" class="list-toggle" checked> <?php esc_html_e( 'toggle all', 'mailster' ); ?></label></li>
	<li>&nbsp;</li>
	<input type="hidden" name="lists[]" value="-1">
			<?php mailster( 'lists' )->print_it( null, false, 'lists', esc_html__( 'total', 'mailster' ), $user_settings['lists'] ); ?>
	</ul>
	<?php endif; ?>
		<?php if ( $no_list ) : ?>
	<ul>
		<li><label><input type="hidden" name="nolists" value="0"><input type="checkbox" name="nolists" value="1" <?php checked( $user_settings['nolists'] ); ?>> <?php esc_html_e( 'subscribers not assigned to a list', 'mailster' ) . ' <span class="count">(' . number_format_i18n( $no_list ) . ' ' . esc_html__( 'total', 'mailster' ) . ')</span>'; ?></label></li>
	</ul>
	<?php endif; ?>
</section>
<h4><?php esc_html_e( 'Conditions', 'mailster' ); ?></h4>
<section>
		<p class="howto"> <?php esc_html_e( 'Define conditions to segment your selection further.', 'mailster' ); ?> </p>
		<?php mailster( 'conditions' )->view( $user_settings['conditions'], 'conditions' ); ?>
</section>
<h4><?php esc_html_e( 'Status', 'mailster' ); ?></h4>
<section>
	<p>
	<input type="hidden" name="status[]" value="-1">
		<?php foreach ( mailster( 'subscribers' )->get_status( null, true ) as $i => $name ) : ?>
			<?php
			if ( 5 == $i ) :
				continue;
			endif;
			?>
	<label><input type="checkbox" name="status[]" value="<?php echo $i; ?>" <?php checked( in_array( $i, $user_settings['status'] ) ); ?>> <?php echo esc_html( $name ); ?> </label>
	<?php endforeach; ?>
	</p>
	<p class="howto"><?php esc_html_e( 'Choose the status of your subscribes you like to export.', 'mailster' ); ?></p>
</section>
<h4><?php esc_html_e( 'Define order and included columns', 'mailster' ); ?></h4>
<section>
		<?php

		$columns = array(
			'ID'        => esc_html__( 'ID', 'mailster' ),
			'email'     => mailster_text( 'email' ),
			'firstname' => mailster_text( 'firstname' ),
			'lastname'  => mailster_text( 'lastname' ),
		);

		$customfields = mailster()->get_custom_fields();
		$customfields = wp_list_pluck( $customfields, 'name' );

		$extra = array(
			'_statuscode' => esc_html__( 'Statuscode', 'mailster' ),
			'_listnames'  => esc_html__( 'Listnames', 'mailster' ),
			'_tagnames'   => esc_html__( 'Tagnames', 'mailster' ),
		);

		$meta = array(
			'hash'       => esc_html__( 'Hash', 'mailster' ),
			'status'     => esc_html__( 'Status', 'mailster' ),
			'added'      => esc_html__( 'Added', 'mailster' ),
			'updated'    => esc_html__( 'Updated', 'mailster' ),
			// 'ip' => __('IP Address', 'mailster'),
			'signup'     => esc_html__( 'Signup Date', 'mailster' ),
			'ip_signup'  => esc_html__( 'Signup IP', 'mailster' ),
			'confirm'    => esc_html__( 'Confirm Date', 'mailster' ),
			'ip_confirm' => esc_html__( 'Confirm IP', 'mailster' ),
			'rating'     => esc_html__( 'Rating', 'mailster' ),
		);

		$meta = $meta + mailster( 'subscribers' )->get_meta_keys();

		$fields = array( '_number' => '# (' . esc_html__( 'Counter', 'mailster' ) . ')' ) + $columns + $customfields + $extra + $meta;

		$fields = apply_filters( 'mailster_export_fields', $fields );

		?>
	<div class="export-order-wrap">
		<ul class="export-order unselected striped">
		<?php foreach ( $fields as $id => $data ) : ?>
				<?php if ( ! in_array( $id, $user_settings['column'] ) ) : ?>
				<li><input type="checkbox" name="column[]" value="<?php echo esc_attr( $id ); ?>"> <?php echo esc_html( strip_tags( $data ) ); ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		<div class="export-order-middle">
			<button class="export-order-add button-secondary">&#8680;</button><br>
			<button class="export-order-remove button-secondary">&#8678;</button>
		</div>
		<ul class="export-order selected striped">
		<?php foreach ( $user_settings['column'] as $id ) : ?>
			<?php if ( isset( $fields[ $id ] ) ) : ?>
			<li><input type="checkbox" name="column[]" value="<?php echo esc_attr( $id ); ?>" checked> <?php echo esc_html( $fields[ $id ] ); ?></li>
			<?php endif; ?>
		<?php endforeach; ?>
		</ul>
	</div>
	<p class="description"><?php esc_html_e( 'Drag and drop fields from the left column to the right and define the order by rearranging fields on the right.', 'mailster' ); ?></p>
</section>
<h4><?php esc_html_e( 'Output Options', 'mailster' ); ?></h4>
<section>
	<p>
		<label><span><?php esc_html_e( 'Header', 'mailster' ); ?></span><input type="hidden" name="header" value="0"><input type="checkbox" name="header" value="1" <?php checked( $user_settings['header'] ); ?>> <?php esc_html_e( 'Include Header in the output.', 'mailster' ); ?> </label>
	</p>
	<p>
		<label><span><?php esc_html_e( 'Date Format', 'mailster' ); ?></span>
		<select name="dateformat">
		<option value="0" <?php selected( $user_settings['dateformat'], 0 ); ?>>timestamp - (<?php echo current_time( 'timestamp' ); ?>)</option>
		<?php $d = mailster( 'helper' )->timeformat(); ?>
		<option value="<?php echo $d; ?>" <?php selected( $user_settings['dateformat'], $d ); ?>>
		<?php echo $d . ' - (' . date( $d, current_time( 'timestamp' ) ) . ')'; ?>
		</option>
		<?php $d = mailster( 'helper' )->dateformat(); ?>
		<option value="<?php echo $d; ?>" <?php selected( $user_settings['dateformat'], $d ); ?>>
		<?php echo $d . ' - (' . date( $d, current_time( 'timestamp' ) ) . ')'; ?>
		</option>
		<?php $d = 'Y-m-d H:i:s'; ?>
		<option value="<?php echo $d; ?>" <?php selected( $user_settings['dateformat'], $d ); ?>>
		<?php echo $d . ' - (' . date( $d, current_time( 'timestamp' ) ) . ')'; ?>
		</option>
		<?php $d = 'Y-m-d'; ?>
		<option value="<?php echo $d; ?>" <?php selected( $user_settings['dateformat'], $d ); ?>>
		<?php echo $d . ' - (' . date( $d, current_time( 'timestamp' ) ) . ')'; ?>
		</option>
		<?php $d = 'Y-d-m H:i:s'; ?>
		<option value="<?php echo $d; ?>" <?php selected( $user_settings['dateformat'], $d ); ?>>
		<?php echo $d . ' - (' . date( $d, current_time( 'timestamp' ) ) . ')'; ?>
		</option>
		<?php $d = 'Y-d-m'; ?>
		<option value="<?php echo $d; ?>" <?php selected( $user_settings['dateformat'], $d ); ?>>
		<?php echo $d . ' - (' . date( $d, current_time( 'timestamp' ) ) . ')'; ?>
		</option>
		</select>
		</label>
	</p>
	<p>
		<label><span><?php esc_html_e( 'Output Format', 'mailster' ); ?></span>
		<select name="outputformat">
			<option value="xls" <?php selected( $user_settings['outputformat'], 'xls' ); ?>><?php esc_html_e( 'Excel Spreadsheet', 'mailster' ); ?></option>
			<option value="csv" <?php selected( $user_settings['outputformat'], 'csv' ); ?>><?php esc_html_e( 'CSV', 'mailster' ); ?></option>
			<option value="html" <?php selected( $user_settings['outputformat'], 'html' ); ?>><?php esc_html_e( 'HTML', 'mailster' ); ?></option>
			</select>
		</label>
		<label id="csv-separator"<?php echo 'csv' != $user_settings['outputformat'] ? ' style="display: none;"' : ''; ?>><?php esc_html_e( 'Separator', 'mailster' ); ?>:
		<select name="separator">
			<option value=";" <?php selected( $user_settings['separator'], ';' ); ?>>;</option>
			<option value="," <?php selected( $user_settings['separator'], ',' ); ?>>,</option>
			<option value="|" <?php selected( $user_settings['separator'], '|' ); ?>>|</option>
			<option value="tab" <?php selected( $user_settings['separator'], 'tab' ); ?>><?php esc_html_e( '[Tab]', 'mailster' ); ?></option>
		</select>
		</label>
	</p>
	<p>
		<label><span><?php esc_html_e( 'CharSet', 'mailster' ); ?>:</span>
		<?php
		$charsets = array(
			'UTF-8'       => 'Unicode 8',
			'ISO-8859-1'  => 'Western European',
			'ISO-8859-2'  => 'Central European',
			'ISO-8859-3'  => 'South European',
			'ISO-8859-4'  => 'North European',
			'ISO-8859-5'  => 'Latin/Cyrillic',
			'ISO-8859-6'  => 'Latin/Arabic',
			'ISO-8859-7'  => 'Latin/Greek',
			'ISO-8859-8'  => 'Latin/Hebrew',
			'ISO-8859-9'  => 'Turkish',
			'ISO-8859-10' => 'Nordic',
			'ISO-8859-11' => 'Latin/Thai',
			'ISO-8859-13' => 'Baltic Rim',
			'ISO-8859-14' => 'Celtic',
			'ISO-8859-15' => 'Western European revision',
			'ISO-8859-16' => 'South-Eastern European',
		);
		?>
		<select name="encoding">
		<?php foreach ( $charsets as $code => $region ) { ?>
			<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $user_settings['encoding'], $code ); ?>><?php echo $code; ?> - <?php echo $region; ?></option>
			<?php } ?>
		</select>
		</label>
	</p>
	<p>
		<label><span><?php esc_html_e( 'Server Performance', 'mailster' ); ?></span>
		<select name="performance" class="performance">
			<option value="100" <?php selected( $user_settings['performance'], 100 ); ?>><?php esc_html_e( 'low', 'mailster' ); ?></option>
			<option value="1000" <?php selected( $user_settings['performance'], 1000 ); ?>><?php esc_html_e( 'normal', 'mailster' ); ?></option>
			<option value="5000" <?php selected( $user_settings['performance'], 5000 ); ?>><?php esc_html_e( 'high', 'mailster' ); ?></option>
			<option value="20000" <?php selected( $user_settings['performance'], 20000 ); ?>><?php esc_html_e( 'super high', 'mailster' ); ?></option>
			<option value="50000" <?php selected( $user_settings['performance'], 50000 ); ?>><?php esc_html_e( 'super extreme high', 'mailster' ); ?></option>
		</select>
		</label>
	</p>
</section>
<section class="footer alternate">
	<p>
		<input id="export-subscriber-button" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Export Subscribers', 'mailster' ); ?>" />
		<span class="status wp-ui-text-icon spinner"></span>
	</p>
</section>
</form>

<?php else : ?>

<h2><?php esc_html_e( 'You have no subscribers to export!', 'mailster' ); ?></h2>

<?php endif; ?>
