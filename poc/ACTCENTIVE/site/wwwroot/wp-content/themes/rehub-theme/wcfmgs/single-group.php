<?php
/**
 * WCFMgs plugin templates
 *
 * Main content area
 *
 * @author 		WC Lovers
 * @package 	wcfmgs/templates/archive-groups
 * @version   2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $WCFM, $WCFMgs, $WCFMmp, $post;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

?>
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side woocommerce page clearfix" id="content">
            <article class="post" id="page-<?php the_ID(); ?>">
				<header class="woocommerce-products-header">
					<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
						<?php the_title( '<h1 class="product_title entry-title">', '</h1>' ); ?>
					<?php endif; ?>

					<?php
					do_action( 'woocommerce_archive_description' );
					?>
				</header>

				<div class="woocommerce-product-details__short-description">
					<?php echo ''.$post->post_excerpt; ?>
				</div>

				<?php
				$marketplece = wcfm_is_marketplace();

				$group_vendors = get_post_meta( $post->ID, '_group_vendors', true );
				if ( $group_vendors && is_array( $group_vendors ) && !empty( $group_vendors ) ) {

					do_action( 'wcfmgs_before_groups_vendors_loop' );

					?>
					
					<?php if( $marketplece == 'wcfmmarketplace' ) { ?>
					<div class="wcfmmp-stores-listing">
						<div id="wcfmmp-stores-wrap">
							<div class="wcfmmp-stores-content">
							<?php } ?>
							  <div class="columns-3">
							    <ul class="wcfmmp-store-wrap products columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?>">
										<?php
										foreach ( $group_vendors as $loop_index => $vendor_id ) {
											$group_ele_class = 'product';
											$columns    = 3;
											$loop_index ++;
											if ( 0 === ( $loop_index - 1 ) % $columns || 1 === $columns ) {
												$group_ele_class .= ' first';
											} elseif ( 0 === $loop_index % $columns ) {
												$group_ele_class .= ' last';
											}
											
											$shop_link = '';
											$store_logo = '';
											if( $marketplece == 'wcvendors' ) {
												$shop_link       = WCV_Vendors::get_vendor_shop_page( $vendor_id );
												$logo = get_user_meta( $vendor_id, '_wcv_store_icon_id', true );
												$logo_image_url = wp_get_attachment_image_src( $logo, 'thumbnail' );
												if ( !empty( $logo_image_url ) ) {
													$store_logo = $logo_image_url[0];
												}
											} elseif( $marketplece == 'wcpvendors' ) {
												$shop_link = get_term_link( $vendor_id, WC_PRODUCT_VENDORS_TAXONOMY );
												$vendor_data = WC_Product_Vendors_Utils::get_vendor_data_by_id( $vendor_id );
												$logo = ! empty( $vendor_data['logo'] ) ? $vendor_data['logo'] : '';
												$logo_image_url = wp_get_attachment_image_src( $logo, 'full' );
												if ( !empty( $logo_image_url ) ) {
													$store_logo = $logo_image_url[0];
												}
											} elseif( $marketplece == 'dokan' ) {
												$shop_link   = dokan_get_store_url( $vendor_id );
												$vendor_user = get_userdata( $vendor_id );
												$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
												$logo = isset( $vendor_data['gravatar'] ) ? absint( $vendor_data['gravatar'] ) : 0;
												$logo_image_url = $logo ? wp_get_attachment_url( $logo ) : '';
												if ( !empty( $logo_image_url ) ) {
													$store_logo = $logo_image_url[0];
												}
											} elseif( $marketplece == 'wcfmmarketplace' ) {
												$store_user      = wcfmmp_get_store( $vendor_id );
												$store_info      = $store_user->get_shop_info();
												$gravatar        = $store_user->get_avatar();
												$banner          = $store_user->get_list_banner();
												if( !$banner ) {
													$banner = apply_filters( 'wcfmmp_store_default_bannar', $WCFMmp->plugin_url . 'assets/images/default_banner.jpg' );
												}
												$store_name      = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : esc_html__( 'N/A', 'rehub-theme' );
												$store_url       = wcfmmp_get_store_url( $vendor_id );
												$store_address   = $store_user->get_address_string(); 
												$store_description = $store_user->get_shop_description();
											}
											if( $marketplece == 'wcfmmarketplace' ) {
												?>
												<li class="wcfmmp-single-store woocommerce coloum-2">
													<div class="store-wrapper">
														<div class="store-content">
															<div class="store-info" style="background-image: url( '<?php echo esc_url($banner); ?>');"></div>
														</div>
														<div class="store-footer">
														
															<div class="store-avatar lft">
																<img src="<?php echo ''.$gravatar; ?>" alt="Logo"/>
															</div>
															
															<div class="store-data-container rgt">
																<div class="store-data">
																	<h2><a href="<?php echo esc_url($store_url); ?>"><?php echo esc_html($store_name); ?></a></h2>
																	
																	<div class="bd_rating">
																		<?php $store_user->show_star_rating(); ?>
																
																		<?php do_action( 'after_wcfmmp_store_list_rating', $vendor_id, $store_info ); ?>
																	<div class="spacer"></div>
																	</div>
																	<?php if ( $store_address && ( $store_info['store_hide_address'] == 'no' ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'vendor_address' ) ): ?>
																		<p class="store-address"><?php echo esc_html($store_address); ?></p>
																	<?php endif ?>
							
																	<?php if ( !empty( $store_info['phone'] ) && ( $store_info['store_hide_phone'] == 'no' ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'vendor_phone' ) ) { ?>
																		<p class="store-phone">
																			<i class="wcfmrhicon rhi-phone" aria-hidden="true"></i> <?php echo esc_html( $store_info['phone'] ); ?>
																		</p>
																	<?php } ?>
																	<?php if ( $store_description && apply_filters( 'wcfm_is_allow_store_list_about', false ) ) { ?>
																		<p class="store-phone">
																			<?php 
																			$pos = strpos( $store_description, ' ', 100 );
																			echo substr( $store_description, 0, $pos ) . '...'; 
																			?>
																		</p>
																	<?php } ?>
																	<?php do_action( 'wcfmmp_store_list_after_store_info', $vendor_id, $store_info ); ?>
																</div>
															</div>
															<div class="spacer"></div>
															<a href="<?php echo esc_url($store_url); ?>" class="wcfmmp-visit-store"><?php esc_html_e( 'Visit', 'rehub-theme' ); ?> <span><?php esc_html_e( 'Store', 'rehub-theme' ); ?></span></a>
															
															<?php do_action( 'wcfmmp_store_list_footer', $vendor_id, $store_info ); ?>
														</div>
													</div>
												</li>
												<?php
											} else {
												if( !$store_logo ) $store_logo = apply_filters( 'woocommerce_placeholder_img_src', WC()->plugin_url() . '/assets/images/placeholder.png' );
												?>
												<li <?php post_class( $group_ele_class ); ?>>
													<a href="<?php echo esc_url($shop_link); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
														<img src="<?php echo esc_url($store_logo); ?>" alt="Placeholder" width="247" class="woocommerce-placeholder wp-post-image" height="300">
														<h2 class="woocommerce-loop-product__title"><?php echo ''.$WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( $vendor_id ); ?></h2>
													</a>
												</li>
												<?php
											}
											//echo $group_vendor;
										}
										?>
									</ul>
								</div>
								<?php if( $marketplece == 'wcfmmarketplace' ) { ?>
							</div>
						</div>
					</div>
					<?php } ?>
					
				<?php
					do_action( 'wcfmgs_after_groups_vendors_loop' );
				} else {
					do_action( 'wcfmgs_no_groups_vendors_found' );
				}

				do_action( 'woocommerce_after_main_content' );?>
			</article>
		</div>
		<?php 
			/**
			 * Hook: woocommerce_sidebar.
			 *
			 * @hooked woocommerce_get_sidebar - 10
			 */
			do_action( 'woocommerce_sidebar' );
		?>
	</div>
</div>
<?php get_footer( 'shop' );