<?php
/**
 * The Template for displaying all single posts.
 *
 * @package dokan
 * @package dokan - 2014 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$store_user = dokan()->vendor->get( get_query_var( 'author' ) );
$vendor_id = $store_user->get_id();
$vendor_email = $store_user->user_email;
$map_location = $store_user->get_location();
$totaldeals = count_user_posts( $vendor_id, $post_type = 'product' );
$store_info = dokan_get_store_info( $vendor_id );
$store_url = dokan_get_store_url( $vendor_id );
$social_fields = dokan_get_social_profile_fields();
$store_description = '';
$tnc_enable = dokan_get_option( 'seller_enable_terms_and_conditions', 'dokan_general', 'off' );
if ( isset($store_info['enable_tnc']) && $store_info['enable_tnc'] == 'on' && $tnc_enable == 'on' ) {
    $store_description = wpautop( wptexturize( wp_kses_post( $store_info['store_tnc'] ) ) );
}

//$map_location = isset( $store_info['location'] ) ? esc_attr( $store_info['location'] ) : '';
$store_address_arr = $store_info['address'];
$store_address = '';
if( is_array( $store_address_arr ) && !empty( $store_address_arr ) ) {
if( !empty($store_address_arr['street_1'] )) $store_address = $store_address_arr['street_1'];
if( !empty($store_address_arr['street_2'] )) $store_address .= ', '. $store_address_arr['street_2'];
if( !empty($store_address_arr['city'] )) $store_address .= ', '. $store_address_arr['city'];
if( !empty($store_address_arr['state'] )) $store_address .= ', '. $store_address_arr['state'];
if( !empty($store_address_arr['zip'] )) $store_address .= ' '. $store_address_arr['zip'];
if( !empty($store_address_arr['country'] )) $store_address .= ', '. $store_address_arr['country'];
}

$widget_args = array( 'before_widget' => '<div class="rh-cartbox widget"><div>', 'after_widget'  => '</div></div>', 'before_title'  => '<div class="widget-inner-title rehub-main-font">', 'after_title' => '</div>' );
?>

<?php get_header(); ?>
<?php dokan_get_template_part( 'store-header' ); ?>

<!-- CONTENT -->
<div class="rh-container wcvcontent woocommerce"> 
    <div class="rh-content-wrap clearfix">
        <div class="rh-mini-sidebar-content-area floatright woocommerce page clearfix tabletblockdisplay">
            <article class="post" id="page-<?php the_ID(); ?>">
                <div role="tabvendor" class="tab-pane active" id="vendor-biography">
                    <div id="comments">
                    <?php do_action( 'dokan_vendor_biography_tab_before', $store_user, $store_info ); ?>

                    <h2 class="headline"><?php echo apply_filters( 'dokan_vendor_biography_title', esc_html__( 'Vendor Biography', 'rehub-theme' ) ); ?></h2>

                    <?php
                        if ( ! empty( $store_info['vendor_biography'] ) ) {
                            printf( '%s', apply_filters( 'the_content', $store_info['vendor_biography'] ) );
                        }
                    ?>

                    <?php do_action( 'dokan_vendor_biography_tab_after', $store_user, $store_info ); ?>
                    </div>
                </div>
                <?php if( !empty( $store_description ) ) { ?>
                <div role="tabvendor" class="tab-pane" id="vendor-about">
                    <div class="rh-cartbox widget">
                        <div>
                            <div class="widget-inner-title rehub-main-font"><?php esc_html_e( 'Terms and Conditions', 'rehub-theme' );?></div>
                            <?php echo wp_kses_post($store_description); ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <?php //dokan_content_nav( 'nav-below' ); ?>
                
            </article>
        </div>        
        <!-- Sidebar -->
        <aside class="rh-mini-sidebar user-profile-div floatleft tabletblockdisplay">       
            <?php do_action( 'dokan_sidebar_store_before', $store_user->data, $store_info ); ?>
            <?php if ( is_active_sidebar( 'sidebar-store' ) ) : ?>
                <?php dynamic_sidebar( 'sidebar-store' ); ?>
            <?php endif;?>
            <?php do_action( 'dokan_sidebar_store_after', $store_user->data, $store_info ); ?>  
            <?php if ( is_active_sidebar( 'wcw-storepage-sidebar' ) ) : ?>
                <?php dynamic_sidebar( 'wcw-storepage-sidebar' ); ?>
            <?php endif;?>                              
        
        </aside>
        <!-- /Main Side --> 
    </div>
</div>
<!-- /CONTENT -->

<?php do_action( 'woocommerce_after_main_content' ); ?>

<?php get_footer(); ?>