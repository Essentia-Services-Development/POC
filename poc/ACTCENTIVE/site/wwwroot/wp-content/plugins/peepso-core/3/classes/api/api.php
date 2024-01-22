<?php

/**
 * Binds the current API type and version to the main PeepSo implementation
 */
class PeepSo3_API {

    private static $instance;

    public static function get_instance() {
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }

    const REST_NAMESPACE = 'peepso';
    const REST_V = 'v1';

    private function __construct()
    {
        add_action( 'rest_api_init', function(){ $this->rest_api_init(); });

        add_action('wp_enqueue_scripts', function() {
            wp_enqueue_script('wp-api');
            wp_localize_script('wp-api', 'wpApiSettings', array(
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                )
            );
        });
    }

    /**
     * Autoloader - Endpoints and Routes
     *
     * Attempts to load all classes present in the current REST directory
     *
     * Each class (Endpoint) must extend PeepSo3_Rest_V1_Endpoint and implement at least one method pair:
     *
     * create()    +   can_create()
     * read()      +   can_read()
     * edit()      +   can_edit()
     * delete()    +   can_delete()
     *
     */
    private function rest_api_init() {

        // Autoload all endpoint class files
        $rest_paths = [dirname(dirname(dirname(__FILE__)))  . DIRECTORY_SEPARATOR . 'api'. DIRECTORY_SEPARATOR . 'rest' . DIRECTORY_SEPARATOR . PeepSo3_API::REST_V];

        $rest_paths = apply_filters('peepso_rest_paths', $rest_paths);

        foreach($rest_paths as $rest_path) {
            foreach (scandir($rest_path) as $filename) {

                $path = $rest_path . DIRECTORY_SEPARATOR . $filename;

                if (!is_dir($path)) {

                    if (substr($filename, -4, 4) != '.php') {
                        continue;
                    }

                    require_once($path);

                    // Assume every file other than abstract and index are Endpoint classes
                    if (!in_array($filename, array('abstract.php', 'index.html'))) {
                        $endpoints[] = str_replace('.php', '', $filename);
                    }
                }
            }
        }

        $endpoint_abstract = "PeepSo3_REST_".PeepSo3_API::REST_V."_Endpoint";
        $cred = $endpoint_abstract::$cred;

        foreach($endpoints as $route ) {

            $endpoint = $endpoint_abstract.'_'.$route;

            if(class_exists($endpoint)) {
	            $endpoint = new $endpoint;

	            $routes = array();

	            foreach ( $cred as $method => $methods ) {
		            if ( method_exists( $endpoint, $method ) ) {
			            $routes[] = array(
				            'methods'             => $methods,
				            'callback'            => array( $endpoint, $method ),
				            'permission_callback' => function () use ( $endpoint, $method ) {
					            return $endpoint->can( $method );
				            },
			            );
		            }
	            }

	            if ( count( $routes ) ) {
		            register_rest_route(
			            PeepSo3_API::REST_NAMESPACE . '/' . PeepSo3_API::REST_V,
			            '/' . $route,
			            $routes
		            );
		            register_rest_route(
			            PeepSo3_API::REST_NAMESPACE . '/' . PeepSo3_API::REST_V,
			            '/' . $route . '/(?P<id>\d+)',
			            $routes
		            );
	            }
            }
        }

    }

    public function rest_api_paths() {
        // Autoload all endpoint class files
        $rest_paths = [dirname(dirname(dirname(__FILE__)))  . DIRECTORY_SEPARATOR . 'api'. DIRECTORY_SEPARATOR . 'rest' . DIRECTORY_SEPARATOR . PeepSo3_API::REST_V];

        $rest_paths = apply_filters('peepso_rest_paths', $rest_paths);

        return $rest_paths;
    }
}