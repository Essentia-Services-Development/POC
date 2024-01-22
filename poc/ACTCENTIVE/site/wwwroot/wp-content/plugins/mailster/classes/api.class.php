<?php

class MailsterApi {


	public function __construct() {

		add_action( 'rest_api_init', array( &$this, 'rest_api_init' ) );
	}

	public function rest_api_init() {

		require MAILSTER_DIR . 'classes/rest-controller/rest.staticmap.class.php';

		$controller = new Mailster_REST_Staticmap_Controller();
		$controller->register_routes();
	}
}
