<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MailsterImportPaste extends MailsterImport {

	protected $type = 'paste';
	protected $name = 'Paste';

	private $api;

	function init() {}


	public function get_import_part( &$import_data ) {

		$raw_data = file_get_contents( $import_data['file'] );
		$data     = maybe_unserialize( $raw_data );
		$limit    = $import_data['performance'] ? 10 : 250;
		$offset   = ( $import_data['page'] - 1 ) * $limit;

		return array_slice( $data, $offset, $limit );
	}

	public function get_import_data() {

		parse_str( $_POST['data'], $data );

		$raw_data    = esc_textarea( stripslashes( $data['paste'] ) );
		$header      = null;
		$sample_size = 10;

		$encoding = mb_detect_encoding( $raw_data, 'auto' );

		// single quotes cause problems
		$raw_data = str_replace( '&#039;', "'", $raw_data );
		$raw_data = str_replace( '&quot;', '"', $raw_data );

		$raw_data = trim( $raw_data );

		$total_lines = substr_count( $raw_data, "\n" ) + 1;
		$data        = $this->sanitize_raw_data( $raw_data );

		if ( isset( $data['header'] ) ) {
			--$total_lines;
			$header = $data['header'];
			unset( $data['header'] );
			$data = array_values( $data );
		}
		$total   = $total_batch = count( $data );
		$removed = $total_lines - $total;

		$filename = wp_tempnam();
		mailster( 'helper' )->file_put_contents( $filename, serialize( $data ) );

		$sample = array_splice( $data, 0, $sample_size );

		return array(
			'file'        => $filename,
			'total'       => $total,
			'removed'     => $removed,
			'header'      => $header,
			'sample'      => $sample,
			'sample_last' => end( $data ),
			'encoding'    => $encoding,
		);
	}


	public function import_options( $data = null ) {
		include MAILSTER_DIR . '/views/manage/method-paste.php';
	}

	public function filter( $insert, $data, $import_data ) {

		$insert['referer'] = 'import';

		return $insert;
	}
}
