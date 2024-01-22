<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking is cancelled
 *
 * An email sent to the user when a booking is cancelled or not approved.
 *
 * @class   WC_Marketking_Email_marketking_vendor_booking_cancelled
 * @extends WC_Email
 */
class WC_Marketking_Email_marketking_vendor_booking_cancelled extends WC_Email {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id          = 'marketking_vendor_booking_cancelled';
		$this->title       = __( 'Vendor Booking Cancelled', 'marketking-multivendor-marketplace-for-woocommerce' );
		$this->description = __( 'Booking cancelled emails are sent when the status of a booking goes to cancelled.', 'marketking-multivendor-marketplace-for-woocommerce' );

		$this->heading        = __( 'Booking Cancelled', 'marketking-multivendor-marketplace-for-woocommerce' );
		$this->subject        = __( '[{blogname}] A booking of "{product_title}" has been cancelled', 'marketking-multivendor-marketplace-for-woocommerce' );
		$this->customer_email = false;

		// Other settings
		$this->template_base  =  MARKETKINGPRO_DIR . 'includes/wcbookings/integrations/wc-bookings/includes/templates/';
		$this->template_html  = 'emails/vendor-booking-cancelled.php';
		$this->template_plain = 'emails/vendor-booking-cancelled.php';

		// Triggers for this email
		add_action( 'woocommerce_booking_pending-confirmation_to_cancelled_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_booking_confirmed_to_cancelled_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_booking_paid_to_cancelled_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();

	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	public function trigger( $booking_id ): void {

		$this->find    = array();
		$this->replace = array();

		if ( $booking_id ) {

			// Only send the booking email for booking post types, not orders, etc
			if ( 'wc_booking' !== get_post_type( $booking_id ) ) {
				return;
			}

			$this->object = get_wc_booking( $booking_id );

			if ( ! is_object( $this->object ) || ! $this->object->get_order() ) {
				return;
			}

			foreach ( array( '{product_title}', '{order_date}', '{order_number}' ) as $key ) {
				$key = array_search( $key, $this->find );
				if ( false !== $key ) {
					unset( $this->find[ $key ] );
					unset( $this->replace[ $key ] );
				}
			}

			if ( $this->object->get_product() ) {
				$this->find[]    = '{product_title}';
				$this->replace[] = $this->object->get_product()->get_title();
			}

			if ( $this->object->get_order() ) {

				$order_date = $this->object->get_order()->get_date_created() ? $this->object->get_order()->get_date_created()->date( 'Y-m-d H:i:s' ) : '';

				$this->find[]    = '{order_date}';
				$this->replace[] = date_i18n( wc_date_format(), strtotime( $order_date ) );

				$this->find[]    = '{order_number}';
				$this->replace[] = $this->object->get_order()->get_order_number();

			} else {

				$this->find[]    = '{order_date}';
				$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->booking_date ) );

				$this->find[]    = '{order_number}';
				$this->replace[] = __( 'N/A', 'marketking-multivendor-marketplace-for-woocommerce' );

			}

			// Get the vendor from the product
			$vendor_id = marketking()->get_product_vendor( $this->object->get_product_id() );
			// don't fire the email if the product is not owned by a vendor
			if ( ! marketking()->is_vendor( $vendor_id ) ) {
				return;
			}

			$vendor_data     = get_userdata( $vendor_id );
			$this->recipient = $vendor_data->user_email;

		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html(): string {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'booking'       => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
			),
			$this->template_base,
			$this->template_base
		);

		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_plain(): string {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'booking'       => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this,
			),
			$this->template_base,
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'default' => 'yes',
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'        => 'text',
				/* translators: 1: subject */
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'marketking-multivendor-marketplace-for-woocommerce' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'        => 'text',
				/* translators: 1: heading */
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'marketking-multivendor-marketplace-for-woocommerce' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => __( 'Plain text', 'marketking-multivendor-marketplace-for-woocommerce' ),
					'html'      => __( 'HTML', 'marketking-multivendor-marketplace-for-woocommerce' ),
					'multipart' => __( 'Multipart', 'marketking-multivendor-marketplace-for-woocommerce' ),
				),
			),
		);
	}
}
return new WC_Marketking_Email_marketking_vendor_booking_cancelled();