<?php

/**
 * Class Mailster_REST_Staticmap_Controller
 */
class Mailster_REST_Staticmap_Controller extends WP_REST_Controller {
	/**
	 * The namespace.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * Rest base for the current object.
	 *
	 * @var string
	 */
	protected $rest_base;


	public function __construct() {

		$this->namespace = 'mailster/v1';
		$this->rest_base = 'staticmap';
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<hash>[0-9a-f]+)',
			array(
				'args'   => array(
					'hash' => array(
						'description' => __( 'Unique hash to identify the image.' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_map' ),
					'permission_callback' => '__return_true', // everyone can do that
				),
				'schema' => null,

			)
		);
	}

	public function get_map( $request ) {

		$url_params = $request->get_url_params();

		$hash = (string) $url_params['hash'];

		$args = get_transient( '_mailster_staticmap_' . $hash );
		$type = mailster_option( 'static_map' );

		if ( ! $args || ! $type ) {
			return new WP_Error( 'not_found', esc_html__( 'You cannot view this resource.' ), array( 'status' => 404 ) );
		}

		if ( $type === 'google' ) {

			$coords = array();
			if ( isset( $args['coords'] ) ) {
				foreach ( $args['coords'] as $c ) {
					if ( is_string( $c ) ) {
						$coords[] = $c;
					}
				}
			}
			if ( isset( $args['lat'] ) && isset( $args['lon'] ) ) {
				$coords[] = sprintf( '%f,%f', $args['lat'], $args['lon'] );
			}

			if ( isset( $args['autoscale'] ) ) {
				unset( $args['zoom'] );
			}

			$mapurl = add_query_arg(
				array(
					'zoom'           => $args['zoom'],
					'size'           => sprintf( '%dx%d', $args['width'], $args['height'] ),
					'language'       => $args['language'],
					'visual_refresh' => true,
					'scale'          => 2,
					'key'            => mailster_option( 'google_api_key' ),
				),
				'https://maps.googleapis.com/maps/api/staticmap'
			);

			foreach ( $coords as $i => $coord ) {
				$mapurl .= '&markers=size:small%7Ccolor:0xdc3232%7Clabel:1%7C' . $coord;
			}

			wp_redirect( $mapurl, 301 );
			exit;

		}

		$expires = (int) get_option( '_transient_timeout__mailster_staticmap_' . $hash, 0 );

		include_once MAILSTER_DIR . '/classes/libs/static.map.php';
		$map = new MailsterStaticMap( $args );

		header( 'Content-Type: image/png' );
		header( 'Pragma: public' );
		header( 'Cache-Control: maxage=' . abs( time() - $expires ) );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', $expires ) . ' GMT' );
		$map->displayPNG();
		exit;
	}
}
