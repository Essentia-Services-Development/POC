<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * If user is deactivating plugin, find out why
 */
class EPKB_Deactivate_Feedback {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_feedback_dialog_scripts' ] );
		add_action( 'wp_ajax_epkb_deactivate_feedback', [ $this, 'ajax_epkb_deactivate_feedback' ] );
	}

	/**
	 * Enqueue feedback dialog scripts.
	 */
	public function enqueue_feedback_dialog_scripts() {
		add_action( 'admin_footer', [ $this, 'output_deactivate_feedback_dialog' ] );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'epkb-admin-feedback', Echo_Knowledge_Base::$plugin_url . 'js/admin-feedback' . $suffix . '.js', array('jquery'), Echo_Knowledge_Base::$version );
		wp_register_style( 'epkb-admin-feedback-style', Echo_Knowledge_Base::$plugin_url . 'css/admin-plugin-feedback' . $suffix . '.css', array(), Echo_Knowledge_Base::$version );

		wp_enqueue_script( 'epkb-admin-feedback' );
		wp_enqueue_style( 'epkb-admin-feedback-style' );
	}

	/**
	 * Display a dialog box to ask the user why they deactivated the KB.
	 */
	public function output_deactivate_feedback_dialog() {

		$first_version = get_option('epkb_version_first');
		$current_version = get_option('epkb_version');
		if ( version_compare( $first_version, $current_version, '==' ) ) {
			$deactivate_reasons = $this->get_deactivate_reasons( 1 );
		} else {
			$deactivate_reasons = $this->get_deactivate_reasons( 2 );
		} 	?>

        <div class="epkb-deactivate-modal" id="epkb-deactivate-modal" style="display:none;">
            <div class="epkb-deactivate-modal-wrap">
                <form id="epkb-deactivate-feedback-dialog-form" method="post">
                    <div class="epkb-deactivate-modal-header">
                        <h3><?php esc_html_e( 'Quick Feedback', 'echo-knowledge-base' ); ?></h3>
                    </div>
                    <div class="epkb-deactivate-modal-body">
                        <p><?php esc_html_e( 'Please choose a reason to deactivate:', 'echo-knowledge-base' ) ?></p>
                        <ul class="epkb-deactivate-reasons">
						    <?php foreach ( $deactivate_reasons as $reason_key => $reason_args ) { ?>
                                <li>
                                    <label>
                                        <input type="radio" name="reason_key" value="<?php echo esc_attr( $reason_key ); ?>" required>  <?php
                                        if ( ! empty( $reason_args['icon'] ) ) {    ?>
                                            <div class="epkb-deactivate-reason-icon <?php echo esc_attr( $reason_args['icon'] ); ?>"></div>  <?php
                                        }   ?>
                                        <div class="epkb-deactivate-reason-text"><?php echo esc_html( $reason_args['title'] ); ?></div>
                                    </label>
                                </li>
						    <?php } ?>
                        </ul>
                        <div class="epkb-deactivate-modal-reason-input-wrap">   <?php
                            foreach ( $deactivate_reasons as $reason_key => $reason_args ) {    ?>
                                <div class="epkb-deactivate-modal-reason-inputs epkb-deactivate-modal-reason-inputs--<?php echo esc_attr( $reason_key ); ?>">   <?php
                                    if ( ! empty( $reason_args['input_placeholder'] ) ) {   ?>
                                        <textarea name="reason_<?php echo esc_attr( $reason_key ); ?>" placeholder="<?php echo esc_attr( $reason_args['input_placeholder'] ); ?>"></textarea>   <?php
                                    }
	                                if ( ! empty( $reason_args['custom_content'] ) ) {  ?>
                                        <div class="epkb-deactivate-feedback-custom-content"><?php echo esc_html( $reason_args['custom_content'] ); ?></div> <?php
	                                }
	                                if ( isset( $reason_args['contact_email']['title'] ) ) {   ?>
                                        <div class="epkb-deactivate-feedback-contact">
                                            <p><?php echo esc_html( $reason_args['contact_email']['title'] ); ?></p>
                                            <input type="email"
                                                   name="contact_email_<?php echo esc_attr( $reason_key ); ?>"
                                                   name="feedback-contact"
                                                   class="epkb-deactivate-feedback-contact-input" <?php
                                                    if ( ! empty( $reason_args['contact_email']['required'] ) ) {    ?>
                                                        placeholder="<?php esc_attr_e( 'Enter Email', 'echo-knowledge-base' ); ?>"
                                                        data-required="true"  <?php
                                                    } else { ?>
                                                        placeholder="<?php esc_attr_e( 'Enter Email (optional)', 'echo-knowledge-base' ); ?>"  <?php
                                                    }   ?>
                                            />
                                        </div>  <?php
	                                }   ?>
                                </div>  <?php
		                    }   ?>
                        </div>
                        <p class="epkb-deactivate-modal-reasons-bottom">
	                        <?php //echo esc_html__( 'Bottom text', 'echo-knowledge-base' ); ?>
                        </p>
                    </div>

                    <div class="epkb-deactivate-modal-footer">
	                    <button class="epkb-deactivate-submit-modal"><?php echo esc_html__( 'Submit & Deactivate', 'echo-knowledge-base' ); ?></button>
	                    <button class="epkb-deactivate-button-secondary epkb-deactivate-cancel-modal"><?php echo esc_html__( 'Cancel', 'echo-knowledge-base' ); ?></button>
                        <a href="#" class="epkb-deactivate-button-secondary epkb-deactivate-skip-modal"><?php echo esc_html__( 'Skip & Deactivate', 'echo-knowledge-base' ); ?></a>
	                    <input type="hidden" name="action" value="epkb_deactivate_feedback" />  <?php
                        wp_nonce_field( '_epkb_deactivate_feedback_nonce' );    ?>
                    </div>
                </form>
            </div>
        </div>  <?php
	}

	/**
	 * Send the user feedback when KB is deactivated.
	 */
	public function ajax_epkb_deactivate_feedback() {
		global $wp_version;

		$wpnonce_value = EPKB_Utilities::post( '_wpnonce' );
		if ( empty( $wpnonce_value ) || ! wp_verify_nonce( $wpnonce_value, '_epkb_deactivate_feedback_nonce' ) ) {
			wp_send_json_error();
		}

		$reason_type = EPKB_Utilities::post( 'reason_key', 'N/A' );
		$reason_input = EPKB_Utilities::post( "reason_{$reason_type}", 'N/A' );
		$first_version = get_option( 'epkb_version_first' );

        // retrieve email
		$contact_email = EPKB_Utilities::post( "contact_email_{$reason_type}", '' );
		$contact_email = is_email( $contact_email ) ? $contact_email : '';
		$contact_user = ( ! empty( $contact_email ) ) ? 'Yes' : 'No';

		// retrieve current user
		$user = EPKB_Utilities::get_current_user();
		$first_name = empty( $user ) ? 'Uknown' : $user->first_name;

		//Theme Name and Version
		$active_theme = wp_get_theme();
		$theme_info = $active_theme->get( 'Name' ) . ' ' . $active_theme->get( 'Version' );

        // retrieve KB templates
		$templates_for_kb = '';
		$kb_configs = epkb_get_instance()->kb_config_obj->get_kb_configs();
        foreach ( $kb_configs as $kb_config ) {
	        $templates_for_kb .= 'KB #' . $kb_config['id'] . ': ' . $kb_config['templates_for_kb'] . ' ';
        }

		// send feedback
		$api_params = array(
			'epkb_action'       => 'epkb_process_user_feedback',
			'feedback_type'     => $reason_type,
			'feedback_input'    => $reason_input,
			'plugin_name'       => 'KB',
			'plugin_version'    => class_exists('Echo_Knowledge_Base') ? Echo_Knowledge_Base::$version : 'N/A',
			'first_version'     => empty($first_version) ? 'N/A' : $first_version,
			'wp_version'        => $templates_for_kb, // send templates info instead of $wp_version,
			'theme_info'        => $theme_info,
			'contact_user'      => $contact_email . ' - ' . $contact_user,
			'first_name'        => $first_name,
		);

		// Call the API
		wp_remote_post(
			esc_url_raw( add_query_arg( $api_params, 'https://www.echoknowledgebase.com' ) ),
			array(
				'timeout'   => 15,
				'body'      => $api_params,
				'sslverify' => false
			)
		);

		wp_send_json_success();
	}

	private function get_deactivate_reasons( $type ) {

		switch ( $type ) {
		   case 1:
		   	    $deactivate_reasons = [
			        'missing_feature'                => [
				        'title'             => __( 'I cannot find a feature', 'echo-knowledge-base' ),
				        'icon'              => 'epkbfa epkbfa-puzzle-piece',
				        'input_placeholder' => __( 'Please tell us what is missing', 'echo-knowledge-base' ),
				        'contact_email'     => [
                            'title'    => __( 'Let us help you find the feature. Please provide your contact email:', 'echo-knowledge-base' ),
                            'required' => false,
                        ],
			        ],
			        'couldnt_get_the_plugin_to_work' => [
				        'title'             => __( 'I couldn\'t get the plugin to work', 'echo-knowledge-base' ),
				        'icon'              => 'epkbfa epkbfa-question-circle-o',
				        'input_placeholder' => __( 'Please share the reason', 'echo-knowledge-base' ),
				        'contact_email'     => [
					        'title'    => __( 'Sorry to hear that. Let us help you. Please provide your contact email:', 'echo-knowledge-base' ),
					        'required' => false,
				        ],
			        ],
			        'bug_issue'                      => [
				        'title'             => __( 'Bug Issue', 'echo-knowledge-base' ),
				        'icon'              => 'epkbfa epkbfa-bug',
				        'input_placeholder' => __( 'Please describe the bug', 'echo-knowledge-base' ),
				        'contact_email'     => [
					        'title'    => __( 'We can fix the bug right away. Please provide your contact email:', 'echo-knowledge-base' ),
					        'required' => true,
				        ]
			        ],
			        'other'                          => [
				        'title'             => __( 'Other', 'echo-knowledge-base' ),
				        'icon'              => 'epkbfa epkbfa-ellipsis-h',
				        'input_placeholder' => __( 'Please share the reason', 'echo-knowledge-base' ),
				        'contact_email'     => [
					        'title'    => __( 'Can we talk to you about reason for removing the plugin?', 'echo-knowledge-base' ),
					        'required' => false,
				        ]
			        ],
			   ];
			   break;
		    case 2:
			default:
				$deactivate_reasons = [
					'no_longer_needed' => [
						'title'             => __( 'I no longer need the plugin', 'echo-knowledge-base' ),
						'icon'              => 'epkbfa epkbfa-question-circle-o',
						'custom_content'    => __( 'Thanks for using our products and have a great week', 'echo-knowledge-base' ) . '!',
						'input_placeholder' => '',
					],
					'missing_feature'  => [
						'title'             => __( 'I cannot find a feature', 'echo-knowledge-base' ),
						'icon'              => 'epkbfa epkbfa-puzzle-piece',
						'input_placeholder' => __( 'Please tell us what is missing', 'echo-knowledge-base' ),
						'contact_email'     => [
							'title'    => __( 'Let us help you find the feature. Please provide your contact email:', 'echo-knowledge-base' ),
							'required' => false,
						],
					],
					'bug_issue'                      => [
						'title'             => __( 'Bug Issue', 'echo-knowledge-base' ),
						'icon'              => 'epkbfa epkbfa-bug',
						'input_placeholder' => __( 'Please describe the bug', 'echo-knowledge-base' ),
						'contact_email'     => [
							'title'    => __( 'We can fix the bug right away. Please provide your contact email:', 'echo-knowledge-base' ),
							'required' => true,
						]
					],
					'other'            => [
						'title'             => __( 'Other', 'echo-knowledge-base' ),
						'icon'              => 'epkbfa epkbfa-ellipsis-h',
						'input_placeholder' => __( 'Please share the reason', 'echo-knowledge-base' ),
						'contact_email'     => [
							'title'    => __( 'Can we talk to you about reason to remove the plugin?', 'echo-knowledge-base' ),
							'required' => false,
						]
					]
			   ];
			   break;
	   }

		return $deactivate_reasons;
	}
}
