<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * New Booking Email
 *
 * An email sent to the vendor when a new booking is created.
 *
 * @class       WC_Marketking_Email_marketking_vendor_new_booking
 * @extends     WC_Email
 */
class WC_Marketking_Email_marketking_vendor_new_booking extends WC_Email {
	/**
	 * Subject for pending confirmation emails.
	 *
	 * @var string
	 */
	public $subject_confirmation = '';

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->id          = 'marketking_vendor_new_booking';
		$this->title       = __( 'Vendor New Booking', 'marketking-multivendor-marketplace-for-woocommerce' );
		$this->description = __( 'New booking emails are sent to the vendor when a new booking is created and paid. This email is also received when a pending confirmation booking is created.', 'marketking-multivendor-marketplace-for-woocommerce' );

		$this->heading              = __( 'New booking', 'marketking-multivendor-marketplace-for-woocommerce' );
		$this->heading_confirmation = __( 'Confirm booking', 'marketking-multivendor-marketplace-for-woocommerce' );
		$this->subject              = __( '[{blogname}] New booking for {product_title} (Order {order_number}) - {order_date}', 'marketking-multivendor-marketplace-for-woocommerce' );
		$this->subject_confirmation = __( '[{blogname}] A new booking for {product_title} (Order {order_number}) is awaiting your approval - {order_date}', 'marketking-multivendor-marketplace-for-woocommerce' );

		// Other settings
		$this->template_base  = MARKETKINGPRO_DIR . 'includes/wcbookings/integrations/wc-bookings/includes/templates/';
		$this->template_html  = 'emails/vendor-new-booking.php';
		$this->template_plain = 'emails/vendor-new-booking.php';

		// Triggers for this email
		add_action( 'woocommerce_booking_in-cart_to_paid_notification', array(
			$this,
			'queue_notification'
		) );
		add_action( 'woocommerce_booking_in-cart_to_pending-confirmation_notification', array(
			$this,
			'queue_notification'
		) );
		add_action( 'woocommerce_booking_unpaid_to_paid_notification', array(
			$this,
			'queue_notification'
		) );
		add_action( 'woocommerce_booking_unpaid_to_pending-confirmation_notification', array(
			$this,
			'queue_notification'
		) );
		add_action( 'woocommerce_booking_confirmed_to_paid_notification', array(
			$this,
			'queue_notification'
		) );
		add_action( 'woocommerce_admin_new_booking_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();

	}

	/**
	 * When bookings are created, orders and other parts may not exist yet. e.g. during order creation on checkout.
	 *
	 * This ensures emails are sent last, once all other logic is complete.
	 */
	public function queue_notification( $booking_id ) {
		wp_schedule_single_event( time(), 'woocommerce_marketking_vendor_new_booking', array( 'booking_id' => $booking_id ) );
	}

	/**
	 * trigger function.
	 */
	public function trigger( $booking_id ) {

		if ( $booking_id ) {

			$this->object = get_wc_booking( $booking_id );

			if ( ! is_object( $this->object ) || ! $this->object->get_order() ) {
				return;
			}

			if ( $this->object->has_status( 'in-cart' ) ) {
				return;
			}

			$key = array_search( '{product_title}', $this->find );
			if ( false !== $key ) {
				unset( $this->find[ $key ] );
				unset( $this->replace[ $key ] );
			}

			$this->find[]    = '{product_title}';
			$this->replace[] = $this->object->get_product()->get_title();

			if ( $this->object->get_order() ) {
				$this->find[]    = '{order_date}';
				$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->get_order()->get_date_created() ) );

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
//			$vendor_id = WCV_Vendors::get_vendor_from_product( $this->object->get_product_id() );
			// don't fire the email if the product is not owned by a vendor
			if ( ! marketking()->is_vendor( $vendor_id ) ) {
				return;
			}

			$vendor_data     = get_userdata( $vendor_id );
			$this->recipient = $vendor_data->user_email;

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
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
			),
			$this->template_base,
			$this->template_base
		);

		return ob_get_clean();
	}

	/**
	 * get_subject function.
	 *
	 * @return string
	 */
	public function get_subject(): string {

		if ( wc_booking_order_requires_confirmation( $this->object->get_order() ) && $this->object->get_status() == 'pending-confirmation' ) {
			$subject = $this->get_option( 'subject_confirmation', $this->subject_confirmation );
		} else {
			$subject = $this->get_option( 'subject', $this->subject );
		}

		return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $subject ), $this->object );
	}

	/**
	 * get_heading function.
	 *
	 * @return string
	 */
	public function get_heading(): string {

		if ( wc_booking_order_requires_confirmation( $this->object->get_order() ) && $this->object->get_status() == 'pending-confirmation' ) {
			$heading = $this->get_option( 'heading_confirmation', $this->heading_confirmation );
		} else {
			$heading = $this->get_option( 'heading', $this->heading );
		}

		return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $heading ), $this->object );

	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'              => array(
				'title'   => __( 'Enable/Disable', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'default' => 'yes',
			),
			'recipient'            => array(
				'title'       => __( 'Recipient(s)', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'marketking-multivendor-marketplace-for-woocommerce' ), esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder' => '',
				'default'     => '',
			),
			'subject'              => array(
				'title'       => __( 'Subject', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'marketking-multivendor-marketplace-for-woocommerce' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),
			'subject_confirmation' => array(
				'title'       => __( 'Subject (Pending confirmation)', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the email subject line for Pending confirmation bookings. Leave blank to use the default subject: <code>%s</code>.', 'marketking-multivendor-marketplace-for-woocommerce' ), $this->subject_confirmation ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading'              => array(
				'title'       => __( 'Email Heading', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'marketking-multivendor-marketplace-for-woocommerce' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),
			'heading_confirmation' => array(
				'title'       => __( 'Email Heading (Pending confirmation)', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification for Pending confirmation bookings. Leave blank to use the default heading: <code>%s</code>.', 'marketking-multivendor-marketplace-for-woocommerce' ), $this->heading_confirmation ),
				'placeholder' => '',
				'default'     => '',
			),
			'email_type'           => array(
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
return new WC_Marketking_Email_marketking_vendor_new_booking();