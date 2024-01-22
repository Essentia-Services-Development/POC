<div class="import-wrap">
<h2><?php esc_html_e( 'Where do you like to import your subscribers from?', 'mailster' ); ?><?php echo mailster()->beacon( '63f5f51ee6d6615225472ab9' ); ?></h2>

<?php
$methods = array(
	'upload'    => esc_html__( 'Upload a CSV file', 'mailster' ),
	'paste'     => esc_html__( 'Paste the data from your spreadsheet app', 'mailster' ),
	'wordpress' => esc_html__( 'Import from your WordPress Users', 'mailster' ),
	'mailchimp' => esc_html__( 'Import from MailChimp', 'mailster' ),
);

$methods = apply_filters( 'mailster_import_methods', $methods );

if ( ! current_user_can( 'mailster_import_wordpress_users' ) ) {
	unset( $methods['wordpress'] );
}

$user_settings = wp_parse_args( get_user_option( 'mailster_import_settings' ), array( 'method' => null ) );
$current       = isset( $_GET['method'] ) ? $_GET['method'] : $user_settings['method'];
?>
<?php foreach ( $methods as $id => $name ) : ?>
	<details id="manage-import-<?php echo esc_attr( $id ); ?>" <?php __checked_selected_helper( $id, $current, true, 'open' ); ?>>
		<summary><?php echo esc_html( $name ); ?></summary>
		<div class="manage-import-body">
			<?php do_action( 'mailster_import_method', $id ); ?>
			<?php do_action( 'mailster_import_method_' . $id ); ?>
		</div>
	</details>
<?php endforeach; ?>
</div>
<div class="import-result"></div>

<div class="import-process-wrap">
	<div class="import-process">
		<h2><?php esc_html_e( 'Importing Contacts', 'mailster' ); ?>…</h2>
		<div class="import-percentage">0%</div>
		<div id="progress" class="progress"><span class="bar"><span></span></span></div>
		<div class="import-stats howto">
			<span class="import-imported">&nbsp;</span>
			<span class="import-errors" title="<?php esc_attr_e( 'Number of failed contacts', 'mailster' ); ?>">&nbsp;</span>
			<span class="import-memory" title="<?php esc_attr_e( 'Current memory usage', 'mailster' ); ?>">&nbsp;</span>
			<span class="import-time" title="<?php esc_attr_e( 'Estimated time left', 'mailster' ); ?>">&nbsp;</span></div>
		<p>
			<a class="button pause-import"><?php esc_html_e( 'Pause', 'mailster' ); ?></a>
			<a class="button resume-import"><?php esc_html_e( 'Resume', 'mailster' ); ?></a>
			<a class="button cancel-import"><?php esc_html_e( 'Cancel', 'mailster' ); ?></a>
		</p>
	</div>
</div>
