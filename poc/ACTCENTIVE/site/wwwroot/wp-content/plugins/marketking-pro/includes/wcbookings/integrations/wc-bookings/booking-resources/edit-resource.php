<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*

Edit Booking Page
* @version 1.0.3

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there.

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?>
<?php
if ( marketking()->vendor_has_panel( 'products' ) ) {
	$checkedval = 0;
	if ( marketking()->is_vendor_team_member() ) {
		$checkedval = intval( get_user_meta( get_current_user_id(), 'marketking_teammember_available_panel_editproducts', true ) );
	}

	#-----------------------------------------------------------------#
	# EDIT-BOOKING
	#-----------------------------------------------------------------#

	$productid = sanitize_text_field( Marketking_WC_Bookings::get_pagenr_query_var() );

//	$productid = sanitize_text_field( marketking()->get_pagenr_query_var() );
	$user_id = get_current_user_id();

	if ( marketking()->is_vendor_team_member() ) {
		$user_id = marketking()->get_team_member_parent();
	}

	if ( (int) marketking()->get_product_vendor( $productid ) !== (int) $user_id ) {
	   return;
	}

	// Addons integration only if the product exists (not being added)
	$prod = sanitize_text_field( Marketking_WC_Bookings::get_pagenr_query_var() );

//	$prod = sanitize_text_field( marketking()->get_pagenr_query_var() );

	if ( $prod === 'add' ) {
//		global $marketking_product_add_id;
//		$prod = $marketking_product_add_id;
	}
	#-----------------------------------------------------------------#
	# EDIT-BOOKING
	#-----------------------------------------------------------------#

	$canadd = marketking()->vendor_can_add_more_products( $user_id );


	// if product exists
	$post    = get_post( $productid );
	$product = wc_get_product( $productid );

	$args = array(
		'post_type'   => 'bookable_resource',
		'numberposts' => - 1,
		'post_status' => array( 'draft', 'pending', 'private', 'publish' ),
		'author'      => $user_id,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'p'           => $productid,
	);

	$query            = new WP_Query( $args );
	$vendor_resources = $query->posts;

	$bookable_resource = array();

	foreach ( $vendor_resources as $resource ) {
		$bookable_resource = $resource;
	}

	if ( ! empty( $bookable_resource ) ) {
		$resource_id = $bookable_resource->ID;
	} else {
		$resource_id = '';
	}


	$exists = 'existing';


	// get original query var
	if ( get_query_var( 'pagenr' ) === 'add' ) {
		$exists = 'new';
	}


	// save post and retake it later - this helps compatibility with elementor, which changes the post ID for some reason
	$retake = 'no';
	if ( is_object( $post ) ) {
		$originalpost    = $post;
		$originalproduct = $product;
		$retake          = 'yes';
	}

	// either not team member, or team member with permission to add
	if ( ! marketking()->is_vendor_team_member() || $checkedval === 1 ) {

		if ( $canadd || $exists === 'existing' ) {

			?>
			<div class="nk-content marketking_edit_bookable_resource_page">
			<div class="container-fluid">
			<div class="nk-content-inner">
			<div class="nk-content-body">
			<form id="marketking_save_bookable_resource_form">

			<?php

			if ( $exists === 'new' ) {
				$text       = esc_html__( 'Save New Resource', 'marketking' );
				$icon       = 'ni-plus';
				$actionedit = 'add';
			} else {
				$text       = esc_html__( 'Update Resource', 'marketking' );
				$icon       = 'ni-edit-fill';
				$actionedit = 'edit';
			}

			?>

			<input id="marketking_edit_resource_action_edit" type="hidden"
			       value="<?php echo esc_attr( $actionedit ); ?>">
			<div class="nk-block-head nk-block-head-sm">
				<div class="nk-block-between">
					<div class="nk-block-head-content marketking_status_text_title">
						<h3 class="nk-block-title page-title "><?php esc_html_e( 'Edit Resource', 'marketking' ); ?></h3>

					</div><!-- .nk-block-head-content -->
					<div class="nk-block-head-content">

						<div class="toggle-wrap nk-block-tools-toggle">
							<a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1"
							   data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
							<div class="toggle-expand-content" data-content="pageMenu">
								<ul class="nk-block-tools g-3">

									<input type="hidden" name="resource_id"
									       id="marketking_save_resource_button_id"
									       value="<?php echo esc_attr( $resource_id ); ?>">
									<input type="hidden" id="post_ID"
									       value="<?php echo esc_attr( $resource_id ); ?>">

									<li class="nk-block-tools-opt">
										<div id="marketking_save_resource_button">
											<a href="#"
											   class="toggle btn btn-icon btn-primary d-md-none"><em
														class="icon ni <?php echo esc_attr( $icon ); ?>"></em></a>
											<a href="#"
											   class="toggle btn btn-primary d-none d-md-inline-flex"><em
														class="icon ni <?php echo esc_attr( $icon ); ?>"></em>
												<span><?php echo esc_html( $text ); ?></span>
											</a>
										</div>
										<?php
										if ( $exists === 'existing' ) {
											// additional buttons for View Resource and Remove Resource
											?>
											<div class="dropdown">
												<a href="#"
												   class="dropdown-toggle btn btn-icon btn-gray btn-trigger ml-2 text-white pl-2 pr-3"
												   data-toggle="dropdown"><em
															class="icon ni ni-more-h"></em><?php esc_html_e( 'More', 'marketking' ); ?>
												</a>
												<div class="dropdown-menu dropdown-menu-right">
													<ul class="link-list-opt no-bdr">

														<?php

														if ( intval( get_option( 'marketking_vendors_can_newproducts_setting', 1 ) ) === 1 ) {
															if ( apply_filters( 'marketking_vendors_can_add_products', true ) ) {
																// either not team member, or team member with permission to add
																if ( ! marketking()->is_vendor_team_member() || $checkedval === 1 ) {
																	if ( $canadd ) {


																		?>

																		<?php
																	}
																}
															}
														}
														?>
														<li><a href="<?php echo esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'bookable-resources' ); ?>" class="" value="<?php echo esc_attr( $resource_id ); ?>"><em class="icon ni ni-box"></em><span><?php esc_html_e( 'View All Resources', 'marketking' ); ?></span>
															</a></li>
														<li><a href="#"
														       class="toggle
														       marketking_delete_button_resource"
														       value="<?php echo esc_attr( $resource_id ); ?>"><em
																		class="icon ni ni-trash"></em>
																<span><?php esc_html_e( 'Delete Resource', 'marketking' ); ?></span>
															</a></li>
													</ul>
												</div>
											</div>
											<?php
										}
										?>
									</li>
								</ul>
							</div>
						</div>
					</div><!-- .nk-block-head-content -->
				</div><!-- .nk-block-between -->

			</div><!-- .nk-block-head -->

			<?php
			if ( isset( $_GET['add'] ) ) {
				$add = sanitize_text_field( $_GET['add'] );;
				if ( $add === 'success' ) {
					?>
					<div class="alert alert-primary alert-icon"><em
								class="icon ni ni-check-circle"></em>
						<strong><?php esc_html_e( 'Your resource has been created successfully', 'marketking' ); ?></strong>. <?php esc_html_e( 'You can now continue to edit it', 'marketking' ); ?>.
					</div>
					<?php
				}
			}
			if ( isset( $_GET['update'] ) ) {
				$add = sanitize_text_field( $_GET['update'] );;
				if ( $add === 'success' ) {
					?>
					<div class="alert alert-primary alert-icon"><em
								class="icon ni ni-check-circle"></em>
						<strong><?php esc_html_e( 'Your resource has been updated successfully', 'marketking' ); ?></strong>.
					</div>
					<?php
				}
			}
			?>

			      <!-- PRODUCT TITLE -->
			<?php
			if ( $exists === 'existing' ) {
				if (isset($bookable_resource->post_title)){
					$title = $bookable_resource->post_title;
				} else {
					$title = '';
				}

				if ( $title === 'Resource Name' ) {
					$title = '';
				}
			} else {
				$title = '';
			}


			?>

			<div>
				<div class="form-group">
					<div class="form-control-wrap"><input type="text"
					                                      class="form-control form-control-lg form-control-outlined"
					                                      id="marketking_resource_title"
					                                      value="<?php echo esc_attr( $title ); ?>"
					                                      required><label
								class="form-label-outlined"
								for="outlined-lg"><?php esc_html_e( 'Resource Name', 'marketking' ); ?></label>
					</div>
				</div>
			</div>
			<div class="nk-block" style="margin-top: 20px;">
				<div class="card">
					<div class="card-aside-wrap">
						<div class="card-content">
							<div class="card-inner">
								<div class="nk-block">
									<div class="nk-block-head">
										<h5 class="title"><?php esc_html_e( 'Resource Details', 'marketking' ); ?></h5>
									</div>

								</div><!-- .nk-block -->

								<div class="nk-divider divider md"></div>

								<div class="output-metabox" style="display: block;">
									<?php

									#-----------------------------------------------------------------#
									#  START: WOOCOMMERCE BOOKINGS INTEGRATION
									#-----------------------------------------------------------------#

									if ( class_exists( 'WC_Bookings' ) ) {

										Marketking_WC_Bookings_Resources::meta_box_inner();

									}


									#-----------------------------------------------------------------#
									#  END: WOOCOMMERCE BOOKINGS INTEGRATION
									#-----------------------------------------------------------------#


									?>

								</div>

							</div><!-- .card-inner -->
						</div><!-- .card-content -->

					</div><!-- .card-aside-wrap -->
				</div><!-- .card -->
			</div><!-- .nk-block -->

			<?php do_action( 'marketking_edit_product_after_tags', $post ); ?>

			<?php

			?>
			</form>
			</div>
			</div>
			</div>
			</div>
			<?php
		}
	}


}
?>