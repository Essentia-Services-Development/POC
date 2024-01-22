<?php

$id = isset( $_GET['ID'] ) ? (int) $_GET['ID'] : null;

if ( ! ( $log = $this->get( $id ) ) ) {
	echo '<h2>' . esc_html__( 'This entry does not exist or has been deleted!', 'mailster' ) . '</h2>';
	return;


}
$addresses = maybe_unserialize( $log->receivers );

?>
<div class="wrap">
<form id="log_form" action="" method="post">
<input type="hidden" id="ID" name="mailster_data[ID]" value="<?php echo $log->ID; ?>">
<?php wp_nonce_field( 'mailster_nonce' ); ?>
<div style="height:0px; width:0px; overflow:hidden;"><input type="submit" name="save" value="1"></div>
<h1>
<?php printf( esc_html__( 'Log from %s', 'mailster' ), '<strong>' . $log->subject . '</strong>' ); ?>

	<span class="alignright">
		<input type="submit" name="delete" class="button button-link-delete" value="<?php esc_attr_e( 'Delete Entry', 'mailster' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Do you really like to remove this log entry?', 'mailster' ); ?>');">
	
	</span>
</h1>

<table class="wp-list-table widefat fixed striped table-view-list logs">
	
	<tr><th><?php esc_html_e( 'Subject', 'mailster' ); ?></th><td><?php echo esc_html( $log->subject ); ?></td></tr>
	<tr><th><?php esc_html_e( 'Sent', 'mailster' ); ?></th><td><?php echo esc_html( wp_date( 'Y-m-d H:i:s', $log->timestamp ) ); ?>, <?php printf( esc_html__( '%s ago', 'mailster' ), human_time_diff( $log->timestamp ) ); ?></td></tr>
	<tr><th><?php esc_html_e( 'Receiver', 'mailster' ); ?></th><td><code><?php echo ( implode( '</code>,<code>', $addresses ) ); ?></code></td></tr>
	<tr><th><?php esc_html_e( 'Campaign', 'mailster' ); ?></th><td><a href="<?php echo admin_url( 'post.php?post=' . $log->campaign_id . '&action=edit' ); ?>"><?php echo get_the_title( $log->campaign_id ); ?></a></td></tr>
	<tr><th><?php esc_html_e( 'Message ID', 'mailster' ); ?></th><td><code><?php echo esc_html( $log->message_id ); ?></code></td></tr>
	<tr><th><?php esc_html_e( 'Content', 'mailster' ); ?></th><td>
		<div id="previewtabs" class="nav-tab-wrapper hide-if-no-js">
			<a class="nav-tab nav-tab-active" href="#preview"><?php esc_html_e( 'Preview', 'mailster' ); ?></a>
			<a class="nav-tab" href="#html"><?php esc_html_e( 'HTML', 'mailster' ); ?></a>
			<a class="nav-tab" href="#plain"><?php esc_html_e( 'Plain', 'mailster' ); ?></a>
			<a class="nav-tab" href="#raw"><?php esc_html_e( 'Raw', 'mailster' ); ?></a>
		</div>

		<div class="tab" id="tab-preview" style="display:block">
			<?php echo $this->get_html( $log ); ?>
		</div>
		<div class="tab" id="tab-html" style="display:none">
			<textarea rows="30" cols="40" class="large-text code"><?php echo esc_html( $log->html ); ?></textarea>
		</div>
		<div class="tab" id="tab-plain" style="display:none">
			<textarea rows="30" cols="40" class="large-text code"><?php echo esc_html( $log->text ); ?></textarea>
		</div>
		<div class="tab" id="tab-raw" style="display:none">
			<textarea rows="30" cols="40" class="large-text code"><?php echo esc_html( $log->raw ); ?></textarea>
		</div>
		</td></tr>

</table>

</form>
</div>
