<?php require 'clean-list-notice.php'; ?>
<p class="howto"><?php esc_html_e( 'Upload you subscribers as comma-separated list (CSV)', 'mailster' ); ?></p>

<form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'admin-ajax.php?action=mailster_import_subscribers_upload_handler' ); ?>">

<?php
global $is_IE, $is_opera;

if ( function_exists( '_device_can_upload' ) && ! _device_can_upload() ) {
	echo '<p>' . esc_html__( 'The web browser on your device cannot be used to upload files. You may be able to use the <a href="http://wordpress.org/extend/mobile/">native app for your device</a> instead.', 'mailster' ) . '</p>';
	return;
}

$upload_size_unit = $max_upload_size = wp_max_upload_size();
$sizes            = array( 'KB', 'MB', 'GB' );

for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ ) {
	$upload_size_unit /= 1024;
}

if ( $u < 0 ) {
	$upload_size_unit = 0;
	$u                = 0;
} else {
	$upload_size_unit = (int) $upload_size_unit;
}
?>

	<div id="media-upload-notice">
		<?php

		if ( isset( $errors['upload_notice'] ) ) {
			echo $errors['upload_notice'];
		}

		?>
	</div>
	<div id="media-upload-error">
		<?php

		if ( isset( $errors['upload_error'] ) && is_wp_error( $errors['upload_error'] ) ) {
			echo $errors['upload_error']->get_error_message();
		}

		?>
		</div>
		<?php
		if ( is_multisite() && ! is_upload_space_available() ) {
			return;
		}

		$post_params       = array(
			'action'   => 'mailster_import_handler',
			'type'     => 'upload',
			'_wpnonce' => wp_create_nonce( 'mailster_nonce' ),
		);
		$upload_action_url = admin_url( 'admin-ajax.php' );

		$plupload_init = array(
			'runtimes'            => 'html5,silverlight,flash,html4',
			'browse_button'       => 'plupload-browse-button',
			'container'           => 'plupload-upload-ui',
			'drop_element'        => 'drag-drop-area',
			'file_data_name'      => 'async-upload',
			'multiple_queues'     => true,
			'max_file_size'       => $max_upload_size . 'b',
			'url'                 => $upload_action_url,
			'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
			'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
			'filters'             => array(
				array(
					'title'      => esc_html__( 'Comma-separated values (CSV)', 'mailster' ),
					'extensions' => 'csv',
				),
			),
			'multipart'           => true,
			'urlstream_upload'    => true,
			'multipart_params'    => $post_params,
			'multi_selection'     => false,
		);

		?>

	<script type="text/javascript">
	var wpUploaderInit = <?php echo json_encode( $plupload_init ); ?>;
	</script>

	<div id="plupload-upload-ui" class="hide-if-no-js">
	<div id="drag-drop-area">
		<div class="drag-drop-inside">
		<p class="drag-drop-info"><?php esc_html_e( 'Drop your list here', 'mailster' ); ?></p>
		<p><?php echo esc_html_x( 'or', 'Uploader: Drop files here - or - Select Files', 'mailster' ); ?></p>
		<p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php esc_attr_e( 'Select File', 'mailster' ); ?>" class="button" /></p>
		<p class="uploadinfo"></p>
		</div>
	</div>
	</div>

	<div id="html-upload-ui" class="hide-if-js">
		<p id="async-upload-wrap">
			<label class="screen-reader-text" for="async-upload"><?php esc_html_e( 'Upload', 'mailster' ); ?></label>
			<input type="file" name="async-upload" id="async-upload" />
			<?php submit_button( esc_html__( 'Upload', 'mailster' ), 'button', 'html-upload', false ); ?>
			<a href="#" onclick="try{top.tb_remove();}catch(e){}; return false;"><?php esc_html_e( 'Cancel', 'mailster' ); ?></a>
		</p>
		<div class="clear"></div>
	</div>

	<p class="max-upload-size"><?php printf( esc_html__( 'Maximum upload file size: %s.', 'mailster' ), esc_html( $upload_size_unit . $sizes[ $u ] ) ); ?> <?php esc_html_e( 'Split your lists into max 50.000 subscribers each.', 'mailster' ); ?></p>
		<?php if ( ( $is_IE || $is_opera ) && $max_upload_size > 100 * 1024 * 1024 ) : ?>
		<span class="big-file-warning"><?php esc_html_e( 'Your browser has some limitations uploading large files with the multi-file uploader. Please use the browser uploader for files over 100MB.', 'mailster' ); ?></span>
		<?php endif; ?>
</form>
