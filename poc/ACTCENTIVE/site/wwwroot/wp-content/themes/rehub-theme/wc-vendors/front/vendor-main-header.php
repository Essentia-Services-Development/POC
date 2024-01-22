<?php
/**
 *  Vendor Main Header - Hooked into archive-product page 
*
 *  THIS FILE WILL LOAD ON VENDORS STORE URLs (such as yourdomain.com/vendors/bobs-store/)
 *
 * @author WCVendors
 * @package WCVendors
 * @version 2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/*
*	Template Variables available 
*   $vendor : 			For pulling additional user details from vendor account.  This is an array.
*   $vendor_id  : 		current vendor user id number
*   $shop_name : 		Store/Shop Name (From Vendor Dashboard Shop Settings)
*   $shop_description : Shop Description (completely sanitized) (From Vendor Dashboard Shop Settings)
*   $seller_info : 		Seller Info(From Vendor Dashboard Shop Settings)
*	$vendor_email :		Vendors email address
*	$vendor_login : 	Vendors user_login name
*	$vendor_shop_link : URL to the vendors store
*/ 

?>
<div class="wcv_shop_wrap">
<h3 class="mt0"><?php echo esc_attr($shop_name); ?></h3>
<div class="wcv_shop_description">
<?php echo ''.$shop_description; ?>
</div>
</div>