<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * The Template for displaying the product ratings in the product panel
 *
 * Override this template by copying it to yourtheme/wc-vendors/front/ratings
 *
 * @package    WCVendors_Pro
 * @version    1.2.3
 */

// This outputs the star rating 
$stars = ''; 
for ($i = 1; $i<=stripslashes( $rating ); $i++) { $stars .= "<i class='rhicon rhi-star-full'></i>"; } 
for ($i = stripslashes( $rating ); $i<5; $i++) { $stars .=  "<i class='rhicon rhi-star-empty'></i>"; }
?> 

<div class="wcv-rating-item">
<h4><?php if ( ! empty( $rating_title ) ) echo strip_tags($rating_title); ?>  <?php echo ''.$stars; ?></h4>
<div class="wcv-rating-posted-by">
<span><?php esc_html_e( 'Posted on', 'rehub-theme'); ?> <?php echo ''.$post_date; ?></span> <?php esc_html_e( ' by ', 'rehub-theme'); echo ''.$customer_name; ?>
</div>
<p><?php echo strip_tags($comment); ?></p>
</div>