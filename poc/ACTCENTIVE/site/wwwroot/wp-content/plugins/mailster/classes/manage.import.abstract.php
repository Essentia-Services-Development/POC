<?php

abstract class MailsterImport {

	protected $type;

	private $credentials;

	public function __construct() {

		$this->init();
	}

	abstract protected function init();

	public function get_type() {
		return strtolower( $this->type );
	}

	protected function update_credentials( $data, $expiration = null ) {

		if ( is_null( $expiration ) ) {
			$expiration = DAY_IN_SECONDS;
		}
		set_transient( 'mailster_importer_credentials_' . $this->type, $data, $expiration );
		$this->credentials = $data;
	}

	protected function get_credentials() {
		if ( ! $this->credentials ) {
			$this->credentials = get_transient( 'mailster_importer_credentials_' . $this->type );
		}

		return $this->credentials;
	}

	private function ajax_nonce( $return = null, $nonce = 'mailster_nonce' ) {
		mailster( 'ajax' )->ajax_nonce( $return, $nonce );
	}


	public function valid_credentials() {
		return true;
	}

	abstract protected function get_import_part( &$import_data );
	abstract protected function get_import_data();

	public function get_import_options( $data = null ) {
		ob_start();

		$this->import_options( $data );

		$output = ob_get_contents();

		ob_end_clean();

		return $output;
	}

	public function import_options( $data = null ) {

		?><form class="importer-form" data-type="<?php echo esc_attr( $this->type ); ?>">
		<?php

		if ( $data ) {
			$valid = $this->valid_credentials( $data );

			if ( is_wp_error( $valid ) ) {
				printf( '<div class="error"><p>%s</p></div>', $valid->get_error_message() );
			} else {
				$this->update_credentials( $data );
			}
		}

		// get credentials form if we need it
		if ( method_exists( $this, 'credentials_form' ) && ! $this->get_credentials() ) :

			$this->credentials_form();
			?>
			<section class="footer alternate">
				<input type="hidden" name="credentials" value="1">
				<p><?php submit_button( __( 'Next Step', 'mailster' ) . '  &#x2192;', 'primary', 'submit', false ); ?></p>
			</section>
			<?php
		else :

			$lists    = $this->get_lists();
			$statuses = $this->get_statuses();
			?>
			<p><strong><?php esc_html_e( 'Please select the lists you like to import.', 'mailster' ); ?></strong></p>
			<ul>
			<?php foreach ( $lists as $list ) : ?>
				<li><label><input type="checkbox" name="lists[]" value="<?php echo esc_attr( $list['id'] ); ?>" checked> <?php echo esc_html( $list['name'] ); ?></label></li>
			<?php endforeach; ?>
			</ul>
			<p><strong><?php esc_html_e( 'Please select the statuses you like to import.', 'mailster' ); ?></strong></p>
			<ul>
			<?php foreach ( $statuses as $status ) : ?>
				<li><label><input type="checkbox" name="statuses[]" value="<?php echo esc_attr( $status['id'] ); ?>" checked> <?php echo esc_html( $status['name'] ); ?></label></li>
			<?php endforeach; ?>
			</ul>
			<section class="footer alternate">
				<p><?php submit_button( __( 'Next Step', 'mailster' ) . '  &#x2192;', 'primary', 'submit', false ); ?></p>
			</section>

		<?php endif; ?>
		</form>
		<?php
	}

	public function filter( $insert, $data, $import_data ) {
		return $insert;
	}


	public function sanitize_raw_data( $raw_data, $offset = 0, $limit = null ) {

		$raw_data = ( trim( str_replace( array( "\r", "\r\n", "\n\n" ), "\n", $raw_data ) ) );

		if ( function_exists( 'mb_convert_encoding' ) ) {
			$encoding = mb_detect_encoding( $raw_data, 'auto' );
		} else {
			$encoding = 'UTF-8';
		}
		if ( $encoding != 'UTF-8' ) {
			$raw_data = utf8_encode( $raw_data );
			$encoding = mb_detect_encoding( $raw_data, 'auto' );
		}
		$lines = explode( "\n", $raw_data );
		if ( is_null( $limit ) ) {
			$limit = count( $lines );
		} else {
			$limit = min( $limit, $lines );
		}
		$separator = $this->get_separator( $lines[0] );
		$data      = array();
		$new_sep   = md5( uniqid() );
		$temp_sep  = md5( uniqid() );

		for ( $i = $offset; $i < $offset + $limit; $i++ ) {

			if ( ! isset( $lines[ $i ] ) ) {
				continue;
			}
			$line = trim( $lines[ $i ] );

			// handle if separator is used in a column
			if ( preg_match_all( '/("([^"]*)")/', $line, $match ) ) {
				foreach ( $match[0] as $value ) {
					if ( false !== strpos( $value, $separator ) ) {
						$line = str_replace( $value, str_replace( $separator, $temp_sep, $value ), $line );
					}
				}
			}

			$line = str_replace( $separator, $new_sep, $line );
			$line = str_replace( $temp_sep, $separator, $line );

			// cleanup quotes
			$line = str_replace( array( "'" . $new_sep . "'", '"' . $new_sep . '"' ), $new_sep, $line );
			$line = preg_replace( '#("|\')' . preg_quote( $new_sep ) . '#', $new_sep, $line );
			$line = preg_replace( '#' . preg_quote( $new_sep ) . '("|\')#', $new_sep, $line );
			$line = preg_replace( '#^("|\')#', '', $line );
			$line = preg_replace( '#("|\')$#', '', $line );
			$line = explode( $new_sep, $line );

			$has_email = false;
			foreach ( $line as $entry ) {
				if ( mailster_is_email( $entry ) ) {
					$has_email = true;
					break;
				}
			}

			if ( $i == 0 && ! $has_email ) {
				$data['header'] = $line;
			} elseif ( $has_email ) {
				$data[] = $line;
			}
		}
		return $data;
	}

	/**
	 *
	 *
	 * @param unknown $string
	 * @param unknown $fallback (optional)
	 * @return unknown
	 */
	private function get_separator( $string, $fallback = ';' ) {
		$seps      = array( ';', ',', '|', "\t" );
		$max       = 0;
		$separator = false;
		foreach ( $seps as $sep ) {
			$count = substr_count( $string, $sep );
			if ( $count > $max ) {
				$separator = $sep;
				$max       = $count;
			}
		}

		if ( $separator ) {
			return $separator;
		}

		return $fallback;
	}
}
