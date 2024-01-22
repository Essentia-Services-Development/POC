<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * The WCVendors Free Branding Class
 * Add two fields to Vendor Shop settings - Banner and Logo (icon)
 * 
 * Tested on: WC Vendors v.2.0
 *
 * @input: WP File Uploader
 * @output: User meta (int value with attachment IDs)
 */
 
if ( ! defined( 'WPINC' ) ) {
	die;
}

class RH_WCVendors_Free_Branding {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->base_url = get_template_directory_uri(); 
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wcvendors_update_admin_user', array( $this, 'save_shop_settings' ) );
		add_action( 'wcvendors_shop_settings_admin_saved', array( $this, 'save_shop_settings' ) );
		add_action( 'wcvendors_shop_settings_saved', array( $this, 'save_shop_settings' ) );
		add_action( 'wcvendors_admin_after_shop_name', array( $this, 'admin_user_meta_branding' ) );
		add_action( 'wcvendors_settings_after_shop_name', array( $this, 'vendor_dashboard_branding_settings' ) );
	}
	
	/**
	 * Enqueue scripts for the File Uploader (admin and frontend)
	 */
	public function enqueue_scripts() {
		if( is_admin() ) {
			wp_enqueue_script( 'rh-wcv-admin-js', $this->base_url . '/inc/wcvendor/wc-vendor-free-brand/js/wcvendors-admin.js', array( 'jquery' ), 1.0, true );
		} else {
			$setting_page = get_option('wcvendors_shop_settings_page_id');
			if (empty($setting_page)) return;
			if(is_page($setting_page)){
				wp_enqueue_script( 'rh-wcv-dashboard-js', $this->base_url . '/inc/wcvendor/wc-vendor-free-brand/js/wcvendors-dashboard.js', array( 'jquery' ), 1.0, true );
			}
		} 
	}
	
	/**
	 * General save fields
	 */
	public function save_shop_settings( $user_id ) {
		// Banner 
		if ( isset( $_POST[ '_pv_shop_banner_id' ] ) ) {
			$pv_shop_banner_id = (int) $_POST[ '_pv_shop_banner_id' ];
			update_user_meta( $user_id, 'rh_vendor_free_header', $pv_shop_banner_id );
		}
		// Icon 
		if ( isset( $_POST[ '_pv_shop_icon_id' ] ) ) {
			$pv_shop_icon_id = (int) $_POST[ '_pv_shop_icon_id' ];
			update_user_meta( $user_id, 'rh_vendor_free_logo', $pv_shop_icon_id );
		}
		// Phone
		if ( isset( $_POST[ '_pv_shop_phone' ] ) ) {
			$pv_shop_phone = sanitize_text_field( $_POST[ '_pv_shop_phone' ] );
			update_user_meta( $user_id, 'rh_vendor_free_phone', $pv_shop_phone );
		}
		// Address
		if ( isset( $_POST[ '_pv_shop_address' ] ) ) {
			$pv_shop_address = sanitize_text_field( $_POST[ '_pv_shop_address' ] );
			update_user_meta( $user_id, 'rh_vendor_free_address', $pv_shop_address );
			if( function_exists('gmw_update_user_location') ){
				gmw_update_user_location( $user_id, $pv_shop_address, true );
			}
		}		
	}

	/**
	 * Output Shop Baranding settings for Admin (and User Admin panel)
	 */
	public function admin_user_meta_branding( $user ) {
		$banner_id  = get_user_meta( $user->ID, 'rh_vendor_free_header', true ); 
		$banner_src = wp_get_attachment_image_src( $banner_id, 'medium' );
		$has_banner = is_array( $banner_src );

		$icon_id  = get_user_meta( $user->ID, 'rh_vendor_free_logo', true ); 
		$icon_src = wp_get_attachment_image_src( $icon_id, 'thumbnail' );
		$has_icon = is_array( $icon_src );
		$phone_str = get_user_meta( $user->ID, 'rh_vendor_free_phone', true );
		$address_str = get_user_meta( $user->ID, 'rh_vendor_free_address', true );
		
		?>
			<!-- Shop Phone -->
			<tr>
				<th><label for="_pv_shop_phone"><?php esc_html_e( 'Shop Phone', 'rehub-theme' ); ?></label></th>
				<td>
					<input type="text" name="_pv_shop_phone" id="_pv_shop_phone" value="<?php echo esc_attr($phone_str); ?>">
				</td>
			</tr>
			<!-- Shop Address -->
			<tr>
				<th><label for="_pv_shop_address"><?php esc_html_e( 'Shop Contact Address', 'rehub-theme' ); ?></label></th>
				<td>
					<input type="text" name="_pv_shop_address" id="_pv_shop_address" value="<?php echo esc_attr($address_str); ?>">
				</td>
			</tr>		
			<!-- Shop Banner -->
			<tr>
				<th><label for="_pv_shop_banner_id"><?php esc_html_e( 'Shop Banner', 'rehub-theme' ); ?></label></th>
				<td>
					<div class="wcv-file-uploader_pv_shop_banner_id">
						<?php if ( $has_banner ) : ?>
							<img src="<?php echo esc_url($banner_src[0]); ?>" alt="image" style="max-width:100%;" /><?php else: ?><?php esc_html_e( 'Upload an image for the banner.', 'rehub-theme' ); ?>
						<?php endif; ?>
					</div>
					
					<input id="_wcv_add_pv_shop_banner_id" type="button" class="button" value="<?php esc_html_e( 'Add Banner', 'rehub-theme' ); ?>" />
					<input id="_wcv_remove_pv_shop_banner_id" type="button" class="button" value="<?php esc_html_e( 'Remove Banner', 'rehub-theme' ); ?>" />
					<input type="hidden" name="_pv_shop_banner_id" id="_pv_shop_banner_id" data-save_button="<?php esc_html_e( 'Add Banner', 'rehub-theme' ); ?>" data-window_title="<?php esc_html_e( 'Add Banner', 'rehub-theme' ); ?>" data-upload_notice="<?php esc_html_e('Upload an image for the banner.', 'rehub-theme' ); ?>" value="<?php echo ''.$banner_id; ?>">
				</td>
			</tr>
			<!-- Shop Icon -->
			<tr>
				<th><label for="_pv_shop_icon_id"><?php esc_html_e( 'Shop Icon', 'rehub-theme' ); ?></label></th>
				<td>
					<div class="wcv-file-uploader_pv_shop_icon_id">
						<?php if ( $has_icon ) : ?>
							<img src="<?php echo esc_url($icon_src[0]); ?>" alt="image" style="max-width:100%;" /><?php else: ?><?php esc_html_e( 'Upload an image for the shop icon.', 'rehub-theme' ); ?>
						<?php endif; ?>
					</div>
					
					<input id="_wcv_add_pv_shop_icon_id" type="button" class="button" value="<?php esc_html_e( 'Add Icon', 'rehub-theme' ); ?>" />
					<input id="_wcv_remove_pv_shop_icon_id" type="button" class="button" value="<?php esc_html_e( 'Remove Icon', 'rehub-theme' ); ?>" />
					<input type="hidden" name="_pv_shop_icon_id" id="_pv_shop_icon_id" value="<?php echo ''.$icon_id; ?>">
				</td>
			</tr>
		<?php
	}

	/**
	 * Output Shop Baranding settings for Dashboard and User Admin panel.
	 * Note. The WC-Vendors has the same hook for both so it has to use different html-templates.
	 */
	public function vendor_dashboard_branding_settings() {
		$user = wp_get_current_user();
		
		if( !is_admin() ) {
			// Phone & Address
			self::store_contact();			
			// Store Banner
			self::store_banner();
			// Store Icon 
			self::store_icon();			
		} else {
			$this->admin_user_meta_branding( $user );
		}

	}

	/**
	 * File Uploader template
	 */
	public static function file_uploader_helper( $field ) { 

		$field[ 'header_text' ] = isset( $field[ 'header_text' ] ) ? $field[ 'header_text' ] : esc_html__('Image', 'rehub-theme' ); 
		$field[ 'add_text' ] = isset( $field[ 'add_text' ] ) ? $field[ 'add_text' ] : esc_html__('Add Image', 'rehub-theme' ); 
		$field[ 'remove_text' ] = isset( $field[ 'remove_text' ] ) ? $field[ 'remove_text' ] : esc_html__('Remove Image', 'rehub-theme' ); 
		$field[ 'image_meta_key' ] = isset( $field[ 'image_meta_key' ] ) ? $field[ 'image_meta_key' ] 	: '_wcv_image_id';  
		$field[ 'save_button' ] = isset( $field[ 'save_button' ] ) ? $field[ 'save_button' ] : esc_html__('Add Image', 'rehub-theme' ); 
		$field[ 'window_title' ] = isset( $field[ 'window_title' ] ) ? $field[ 'window_title' ] : esc_html__('Select an Image', 'rehub-theme' ); 
		$field[ 'value' ]	= isset( $field[ 'value' ] ) ? $field[ 'value' ] : 0; 
		$field[ 'size' ] = isset( $field[ 'size' ] ) ? $field[ 'size' ] : 'full'; 
		$field[ 'class' ] = isset( $field[ 'class'] ) ? $field[ 'class' ] : ''; 
		$field[ 'wrapper_start' ] = isset( $field[ 'wrapper_start' ] ) ? $field[ 'wrapper_start' ] : '';
		$field[ 'wrapper_end' ] = isset( $field[ 'wrapper_end' ] ) ? $field[ 'wrapper_end' ] : '';

		// Get the image src
		$image_src = wp_get_attachment_image_src( $field[ 'value' ], $field[ 'size' ] );

		// see if the array is valid
		$has_image = is_array( $image_src );

		// Container wrapper start if defined start & end required otherwise no output is shown 
		if (! empty($field['wrapper_start'] ) && ! empty($field['wrapper_end'] ) ) { 
			echo ''.$field['wrapper_start']; 
		}

		echo '<div class="wcv-file-uploader'. $field[ 'image_meta_key' ] .' '. $field[ 'class' ] .'">'; 

		if ( $has_image ) {  
			echo '<img src="'. $image_src[0].'" alt="image" style="max-width:100%;" />'; 
		}
		
		echo '</div>';
		echo '<a class="wcv-file-uploader-add'. $field[ 'image_meta_key' ] . ' ' . ( $has_image ? 'rhhidden' : '' ) . '" href="#">'.$field[ 'add_text' ].'</a>'; 
		echo '<a class="wcv-file-uploader-delete' . $field[ 'image_meta_key' ] .' ' . ( !$has_image ? 'rhhidden' : '' )  . '" href="#" >'.$field[ 'remove_text' ].'</a>'; 
		echo '<input class="wcv-img-id" name="'. $field[ 'image_meta_key'] .'" id="'. $field[ 'image_meta_key'] .'" type="hidden" value="'. esc_attr( $field[ 'value' ] ) .'" data-image_meta_key="'. $field[ 'image_meta_key' ] .'" data-save_button="'. $field[ 'save_button' ] .'" data-window_title="'. $field[ 'window_title' ] .'" />';

		// container wrapper end if defined 
		if (! empty($field['wrapper_start'] ) && ! empty($field['wrapper_end'] ) ) { 
			echo ''.$field['wrapper_end']; 
		}
	}

	/**
	 *  Output store banner uploader 
	 */
	public static function store_banner() {
		wp_enqueue_media();
		$value = get_user_meta( get_current_user_id(), 'rh_vendor_free_header', true ); 
		echo '<div class="rhpv_shop_brand_container mb15">';
		echo '<h6>'. esc_html__( 'Store Banner', 'rehub-theme'). '</h6>'; 
		echo '<span>'. esc_html__('Recommended image height: 270px','rehub-theme') .'</span>';
		self::file_uploader_helper( apply_filters( 'wcv_vendor_store_banner', array(  
			'header_text' => esc_html__('Store Banner', 'rehub-theme' ), 
			'add_text' => esc_html__('Add Store Banner', 'rehub-theme' ), 
			'remove_text' => esc_html__('Remove Store Banner', 'rehub-theme' ), 
			'image_meta_key' => '_pv_shop_banner_id', 
			'save_button' => esc_html__('Add Store Banner', 'rehub-theme' ), 
			'window_title' => esc_html__('Select an Image', 'rehub-theme' ), 
			'value' => $value
			)
		) );
		echo '</div>';
	}
	
	/**
	 *  Output store icon uploader 
	 */
	public static function store_icon() {
		$value = get_user_meta( get_current_user_id(),  'rh_vendor_free_logo', true ); 
		echo '<div class="rhpv_shop_logo_container mb15">';
		echo '<h6>'. esc_html__( 'Store Icon', 'rehub-theme'). '</h6>';
		echo '<span>'. esc_html__('Minimal image size: 150px x 150px','rehub-theme') .'</span>';
		self::file_uploader_helper( apply_filters( 'wcv_vendor_store_icon', array(  
			'header_text' => esc_html__('Store Icon', 'rehub-theme' ), 
			'add_text' => esc_html__('Add Store Icon', 'rehub-theme' ), 
			'remove_text' => esc_html__('Remove Store Icon', 'rehub-theme' ), 
			'image_meta_key' => '_pv_shop_icon_id', 
			'save_button' => esc_html__('Add Store Icon', 'rehub-theme' ), 
			'window_title' => esc_html__('Select an Image', 'rehub-theme' ), 
			'value' => $value, 
			'size' => 'thumbnail', 
			'class' => 'wcv-store-icon'
			)
		) );
		echo '</div>';
	}

	/**
	 *  Output phone and address fields
	 */
	public static function store_contact() {
		$phone_str  = get_user_meta( get_current_user_id(), 'rh_vendor_free_phone', true ); 
		$address_str  = get_user_meta( get_current_user_id(), 'rh_vendor_free_address', true ); 
		
		$phone = '<div class="pv_shop_phone_container"><h6>';
		$phone .= esc_html__( 'Shop Phone', 'rehub-theme' );
		$phone .= '</h6>';
		$phone .= esc_html__( 'Your shop contact phone number.', 'rehub-theme' );
		$phone .= '<br><input type="text" name="_pv_shop_phone" id="_pv_shop_phone" style="width:100%" placeholder="'. esc_html__( 'Phone number', 'rehub-theme' ) .'" value="'. $phone_str .'">';
		$phone .= '	</p></div>';
		
		$address = '<div class="pv_shop_phone_container"><h6>';
		$address .= esc_html__( 'Shop Address', 'rehub-theme' );
		$address .= '</h6>';
		$address .= esc_html__( 'Address where your store is placed.', 'rehub-theme' );
		$address .= '<br><input type="text" name="_pv_shop_address" id="_pv_shop_address" style="width:100%" placeholder="'. esc_html__( 'Address', 'rehub-theme' ) .'" value="'. $address_str .'">';
		$address .= '	</p></div>';
		
		echo ''.$phone .''. $address;
	}
	
}

return new RH_WCVendors_Free_Branding();