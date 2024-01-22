<?php

class MailsterPrecheck {

	public function __construct() {

		add_action( 'plugins_loaded', array( &$this, 'init' ) );
	}


	public function init() {}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function script_styles() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_register_style( 'mailster-icons', MAILSTER_URI . 'assets/css/icons' . $suffix . '.css', array(), MAILSTER_VERSION );
		wp_register_style( 'mailster-editor-precheck-style', MAILSTER_URI . 'assets/css/editor-precheck-style' . $suffix . '.css', array( 'mailster-icons' ), MAILSTER_VERSION );
		wp_register_script( 'mailster-editor-precheck-script', MAILSTER_URI . 'assets/js/editor-precheck-script' . $suffix . '.js', array( 'jquery' ), MAILSTER_VERSION );

		ob_start();

		wp_print_styles( 'mailster-editor-precheck-style' );
		wp_print_scripts( 'mailster-editor-precheck-script' );

		do_action( 'mailster_precheck_script_styles' );

		$script_styles = ob_get_contents();

		ob_end_clean();

		return $script_styles;
	}


	public function convert( $response, $endpoint ) {

		$html = '';

		switch ( $endpoint ) {

			case 'spam_report':
				$html .= '<p>';
				$html .= sprintf( 'SpamAssassin Score: <strong>%s</strong>.<br>', $response->score );
				$html .= '(<span class="description">' . sprintf( esc_html__( 'A score above %s is considered spam.', 'mailster' ), '<strong>' . esc_html( $response->threshold ) . '</strong>' ) . '</span>)';
				$html .= '</p>';
				$html .= '<table class="wp-list-table widefat striped spamreport-table">';
				foreach ( $response->rules as $key => $data ) {
					$html .= '<tr>';
					$html .= '<td>' . esc_html( $data->score ) . '</td>';
					$html .= '<td><strong>' . esc_html( $data->code ) . '</strong><br>' . esc_html( $data->message ) . '</td>';
					if ( ! empty( $data->link ) ) {
						$html .= '<td><a href="' . esc_attr( $data->link ) . '" target="_blank" rel="nooopener noreferrer">' . esc_html__( 'info', 'mailster' ) . '</a></td>';
					}
					$html .= '</tr>';
				}
				$html .= '</table>';
				break;

			case 'tests/spf':
				$html .= esc_html( $response->message );
				$html .= '<pre>' . esc_html( $response->record ) . '</pre>';
				break;

			case 'tests/dkim':
				if ( 'fail' == $response->result ) {
					$html .= 'The DKIM Signature doesn\'t match: <pre>' . esc_html( $response->signature ) . '</pre>';
				} elseif ( 'none' == $response->result ) {
					$html .= 'You do not have a DKIM Signature setup.';
				} else {
					$html .= 'You DKIM setup is correct. <pre>' . esc_html( $response->signature ) . '</pre>';
				}
				break;

			case 'tests/dmarc':
				if ( 'fail' == $response->result ) {
					$html .= 'You do not have a valid DMARC record.';
				} elseif ( 'pass' == $response->result ) {
					$html .= 'You DMARC setup is correct. <pre>' . esc_html( $response->record ) . '</pre>';
				}
				break;

			case 'tests/rdns':
				$html .= $response->html;
				if ( 'fail' == $response->result ) {
					$html .= 'Your Reverse DNS doesn\'t resolve correctly.';
				} elseif ( 'pass' == $response->result ) {
					$html .= '<p><strong>IP:</strong> ' . esc_html( $response->ip ) . '<br><strong>HELO:</strong> ' . $response->helo . '<br><strong>rDNS:</strong> ' . $response->rdns . '</p>';
				}
				break;

			case 'tests/mx':
				if ( 'fail' == $response->result ) {
					$html .= 'You do not have a valid MX record. <br><strong>HOST:</strong> ' . esc_html( $response->host );
				} elseif ( 'pass' == $response->result ) {
					$html .= 'Your server has a MX record. <br><strong>HOST:</strong> ' . esc_html( $response->host ) . '<pre>' . esc_html( $response->record ) . '</pre>';
				}
				break;

			case 'tests/a':
				if ( 'fail' == $response->result ) {
					$html .= 'You do not have a valid A record.';
				} elseif ( 'pass' == $response->result ) {
					$html .= 'Your server has an A record. <br><strong>IP:</strong> ' . esc_html( $response->ip ) . '<pre>' . esc_html( $response->record ) . '</pre>';
				}
				break;

			case 'tests/links':
				if ( $response->count ) {
					$html .= '<table class="wp-list-table widefat striped assets-table">';
					foreach ( $response->links as $i => $link ) {
						$html .= '<tr class="asset is-' . esc_attr( $link->status ) . '" data-url="' . esc_attr( $link->href ) . '" data-tag="a" data-attr="href" data-index="' . esc_attr( $link->index ) . '">';
						$html .= '<td><span class="asset-type asset-type-' . $link->type . ' mailster-icon"></span></td>';
						$html .= '<td title="' . esc_attr( $link->message ) . '">' . $link->code . '</td>';
						$html .= '<td>';
						if ( $link->href && 'anchor' != $link->type ) {
							$html .= '<a href="' . esc_attr( $link->href ) . '" target="_blank" title="' . esc_attr__( 'open link', 'mailster' ) . '" class="open-link mailster-icon" rel="nooopener noreferrer"></a>';
						}
						$html .= '<strong class="the-link" title="' . esc_attr( $link->href ) . '">' . preg_replace( '/^https?:\/\//', '', $link->href );
						$html .= '</strong>';
						if ( $link->location ) {
							$html .= '<div class="the-location" title="' . sprintf( esc_attr__( 'This address redirects to %s.', 'mailster' ), "\n" . esc_attr( $link->location ) ) . '"> ↳ ' . esc_url( $link->location ) . '</div>';
						}
						$html .= esc_html( $link->message ) . '<br>';
						if ( $link->text ) {
							$html .= esc_html( $link->text );
						}
						$html .= '</td>';
						$html .= '</tr>';
					}
					$html .= '</table>';
				} else {
					$html .= esc_html__( 'This email doesn\'t contain links.', 'mailster' );
				}
				break;

			case 'tests/images':
				if ( $response->count ) {
					$html .= '<table class="wp-list-table widefat striped assets-table">';
					foreach ( $response->images as $i => $image ) {
						$html .= '<tr class="asset is-' . esc_attr( $image->status ) . '" data-url="' . esc_attr( $image->src ) . '" data-tag="' . esc_attr( $image->tag ) . '" data-attr="' . esc_attr( $image->attr ) . '" data-index="' . esc_attr( $image->index ) . '">';
						$html .= '<td><span class="asset-type asset-type-image mailster-icon"></span></td>';
						$html .= '<td title="' . esc_attr( $image->message ) . '">' . $image->code;
						$html .= '</td>';
						$html .= '<td>';
						$html .= '<strong class="the-link" title="' . esc_attr( $image->src ) . '">' . basename( $image->src ) . '</strong>';
						$html .= esc_html( $image->message );
						if ( $image->size ) {
							$html .= ' &ndash; ' . size_format( $image->size, 2 );
						}
						$html .= '<br>';
						if ( 'img' == $image->tag ) {
							if ( $image->alt ) {
								$html .= esc_html__( 'Alt text', 'mailster' ) . ': ' . esc_html( $image->alt );
							} else {
								$html .= esc_html__( 'No Alt text found.', 'mailster' );
							}
						}
						$html .= '</td>';
						$html .= '</tr>';
					}
					$html .= '</table>';
				} else {
					$html .= esc_html__( 'This email doesn\'t contain images.', 'mailster' );
				}
				break;

			case 'blocklist':
				if ( $response->hits ) {
					$html .= '<p>' . sprintf( esc_html__( 'Your IP %1$s is blocked on %2$d %3$s:', 'mailster' ), '<strong>' . esc_html( $response->ip ) . '</strong>', $response->hits, _n( 'list', 'lists', $response->hits, 'mailster' ) ) . '</p>';

					$html .= '<ul class="blocklist">';
					foreach ( $response->blocklist as $i => $service ) {
						$html .= '<li>';
						if ( $service->link ) {
							$html .= '<a href="' . esc_attr( $service->link ) . '" target="_blank" title="' . esc_attr__( 'open link', 'mailster' ) . '" class="open-link mailster-icon" rel="nooopener noreferrer"></a>';
						}
						$html .= sprintf( '<strong>%s</strong>: %s', $service->name, $service->message );
						$html .= '</li>';
					}
					$html .= '</ul>';
				} else {
					$html .= '<p>' . sprintf( esc_html__( 'Your IP %s is currently not blocked.', 'mailster' ), '<strong>' . esc_html( $response->ip ) . '</strong>' ) . '</p>';
				}
				break;

			case 'tests/email':
				$html .= '<dl><dt>' . esc_html__( 'Words', 'mailster' ) . ':</dt><dd>' . number_format_i18n( $response->words ) . '</dd></dl>';
				$html .= '<dl><dt>' . esc_html__( 'Characters', 'mailster' ) . ':</dt><dd>' . number_format_i18n( $response->characters ) . '</dd></dl>';
				$html .= '<dl><dt>' . esc_html__( 'Images', 'mailster' ) . ':</dt><dd>' . number_format_i18n( $response->images ) . '</dd></dl>';
				$html .= '<dl><dt>' . esc_html__( 'Image Ratio', 'mailster' ) . ':</dt><dd>' . number_format_i18n( $response->image_ratio * 100, 2 ) . '%</dd></dl>';
				$html .= '<dl><dt>' . esc_html__( 'Attachments', 'mailster' ) . ':</dt><dd>' . number_format_i18n( $response->attachments ) . '</dd></dl>';
				$html .= '<dl><dt>' . esc_html__( 'Size', 'mailster' ) . ':</dt><dd>' . size_format( $response->size, 2 ) . '</dd></dl>';
				if ( $response->tips ) {
					$html .= '<ul class="tips">';
					foreach ( $response->tips as $tip ) {
						$html .= '<li>' . esc_html( $tip ) . '</li>';
					}
					$html .= '<ul>';
				}
				break;

			case 'tests/subject':
				$html .= '<dl><dt>' . esc_html__( 'Words', 'mailster' ) . ':</dt><dd>' . number_format_i18n( $response->words ) . '</dd></dl>';
				$html .= '<dl><dt>' . esc_html__( 'Characters', 'mailster' ) . ':</dt><dd>' . number_format_i18n( $response->characters ) . '</dd></dl>';
				if ( $response->tips ) {
					$html .= '<ul class="tips">';
					foreach ( $response->tips as $tip ) {
						$html .= '<li>' . esc_html( $tip ) . '</li>';
					}
					$html .= '<ul>';
				}
				break;

			default:
				$html .= 'Missing check for <strong>' . $endpoint . '</strong>';
				break;
		}

		return $html;
	}


	public function request( $id, $endpoint = null, $timeout = 5, $try = 1 ) {

		if ( ! mailster()->is_verified() ) {
			return new WP_Error( 503, esc_html__( 'Please verify your Mailster license on the Dashboard!', 'mailster' ) );
		}

		$url  = 'https://api.precheck.email/v2';
		$url .= '/' . $id;
		if ( $endpoint ) {
			$url .= '/' . $endpoint;
		}
		$url .= '.json';

		if ( $token = get_option( 'mailster_precheck_token' ) ) {
			$authorization = 'Bearer ' . $token;
		} else {
			$authorization = mailster()->get_license();
		}

		$args = array(
			'timeout' => (int) $timeout,
			'headers' => array(
				'Authorization' => $authorization,
				'X-Domain'      => parse_url( is_multisite() ? network_site_url() : site_url(), PHP_URL_HOST ),
			),
		);

		$response = wp_remote_get( $url, $args );

		$code    = wp_remote_retrieve_response_code( $response );
		$headers = wp_remote_retrieve_headers( $response );
		$body    = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $response ) ) {
			if ( $response->get_error_code() == 'http_request_failed' ) {
				return new WP_Error( 503, esc_html__( 'The Precheck service is currently not available. Please check back later.', 'mailster' ) . $body );
			}
			return $response;
		} elseif ( 503 === $code || 500 === $code ) {
			return new WP_Error( 503, esc_html__( 'The Precheck service is currently not available. Please check back later.', 'mailster' ) . $body );
		} elseif ( 200 === $code ) {
			if ( isset( $headers['token'] ) && $token != $headers['token'] ) {
				update_option( 'mailster_precheck_token', $headers['token'] );
			}
			$json = json_decode( $body );
			if ( null === $json ) {
				return new WP_Error( 503, $body );
			}
			if ( isset( $headers['points'] ) ) {
				$json->points = floatval( $headers['points'] );
			} else {
				$json->points = null;
			}
			if ( isset( $headers['penalty'] ) ) {
				$json->penalty = floatval( $headers['penalty'] );
			} else {
				$json->penalty = null;
			}
			return $json;
		} elseif ( 429 === $code ) {
			return new WP_Error( $code, sprintf( esc_html__( 'You have hit the test limit. Please try again in %s.', 'mailster' ), human_time_diff( strtotime( $headers['retry-after'] ) ) ) . $body );
		} elseif ( 403 === $code ) {
			return new WP_Error( $code, esc_html__( 'Your license code is invalid.', 'mailster' ) . $body );
		} elseif ( 404 === $code ) {
			delete_option( 'mailster_precheck_token' );
			if ( $try > 1 ) {
				return new WP_Error( $code, esc_html__( 'This service no longer available with the current Mailster version. Please update Mailster!', 'mailster' ) );
			}
			sleep( 3 );
			return $this->request( $id, $endpoint, $timeout, ++$try );
		} elseif ( 498 === $code ) {
			delete_option( 'mailster_precheck_token' );
			return new WP_Error( $code, esc_html__( 'Your token is invalid!', 'mailster' ) . $body );
		} else {
			return new WP_Error( $code, sprintf( esc_html__( 'You have hit the test limit. Please try again in %s.', 'mailster' ), human_time_diff( strtotime( $headers['retry-after'] ) ) ) . $body );
		}
	}
}
