<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*

Products Dashboard Page
* @version 1.0.2

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there.

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/
/*$productid: fix  Undefined variable $product_id: Undefined variable
$product_idclass-marketking-pro.php:3044*/
//$productid = sanitize_text_field( Marketking_WC_Bookings::get_pagenr_query_var() );


$vendor_id = get_current_user_id();

if ( marketking()->is_vendor_team_member() ) {
	$vendor_id = marketking()->get_team_member_parent();
}
?>

<?php
if ( marketking()->vendor_has_panel( 'products' ) ) {
	$checkedval = 0;
	if ( marketking()->is_vendor_team_member() ) {
		$checkedval = intval( get_user_meta( get_current_user_id(), 'marketking_teammember_available_panel_editproducts', true ) );
	}

	?>
	<div class="nk-content marketking_products_page">
	<div class="container-fluid">
	<div class="nk-content-inner">
	<div class="nk-content-body">
	<div class="nk-block-head nk-block-head-sm">
		<div class="nk-block-between">
			<div class="nk-block-head-content">
				<h3 class="nk-block-title page-title"><?php esc_html_e( 'Bookable Resources', 'marketking' );?></h3>
			</div><!-- .nk-block-head-content -->
			<div class="nk-block-head-content">
				<div class="toggle-wrap nk-block-tools-toggle">
					<div>
						<ul class="nk-block-tools g-3">

							<li>
								<div class="form-control-wrap">
									<div class="form-icon form-icon-right">
										<em class="icon ni ni-search"></em>
									</div>
									<input type="text" class="form-control"
									       id="marketking_bookings_search"
									       placeholder="<?php esc_html_e( 'Search resources...', 'marketking' ); ?>">
								</div>
							</li>
							<?php

							if ( intval( get_option( 'marketking_vendors_can_newproducts_setting', 1 ) ) === 1 ) {
								if ( apply_filters( 'marketking_vendors_can_add_products', true ) ) {
									// either not team member, or team member with permission to add
									if ( ! marketking()->is_vendor_team_member() || $checkedval === 1 ) {
										if ( marketking()->vendor_can_add_more_products( $vendor_id ) ) {
											?>
											<li class="nk-block-tools-opt">
												<a href="<?php echo esc_attr
												( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-resource/add' ); ?>"
												   class="btn btn-primary d-md-inline-flex"><em
															class="icon ni ni-plus"></em>
													<span><?php esc_html_e( 'Add Resource', 'marketking' ); ?></span>
												</a>
											</li>
											<?php
										} else {
											// show some error message that they reached the max nr of products
											?>
											<button type="button" class="btn btn-gray d-none d-md-inline-flex" disabled="disabled"><em class="icon ni ni-plus"></em>&nbsp;&nbsp;<?phpesc_html_e( 'Add Resource (Max Limit Reached)', 'marketking' ); ?></button>
											<?php
										}
									}
								}
							}

							?>

						</ul>
						<div style="margin-top: 30px;">
						</div>
						
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
			<div class="alert alert-primary alert-icon"><em class="icon ni ni-check-circle"></em>
				<strong><?php esc_html_e( 'Your product has been created successfully', 'marketking' ); ?></strong>. <?php esc_html_e( 'You can now continue to edit it', 'marketking' ); ?>.
			</div>
			<?php
		}
	}
	?>

	<table id="marketking_dashboard_bookings_table" class="nk-tb-list is-separate mb-3">
		<thead>
		<tr class="nk-tb-item nk-tb-head">
			<th class="nk-tb-col tb-col-md marketking-column-mid">
				<span class="sub-text"><?php esc_html_e( 'Name', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md marketking-column-mid">
				<span class="sub-text"><?php esc_html_e( 'Parent products', 'marketking' ); ?></span>
			</th>

			<th class="nk-tb-col tb-col-sm marketking-column-min">
				<span class="sub-text"><?php esc_html_e( 'Actions', 'marketking' ); ?></span>
			</th>

		</tr>
		</thead>
		<?php
		if ( ! marketking()->load_tables_with_ajax( get_current_user_id() ) ) {
			?>
			<tfoot>
			<tr class="nk-tb-item nk-tb-head">
				<th class="nk-tb-col tb-non-tools"><?php esc_html_e( 'Name', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e( 'Parent Products', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools marketking-column-min"></th>
			</tr>
			</tfoot>
			<?php
		}
		?>
		<tbody>
		<?php

		$args = array(
			'post_type'   => 'bookable_resource',
			'numberposts' => - 1,
			'post_status' => array( 'draft', 'pending', 'private', 'publish' ),
			'author'      => $vendor_id,
			'orderby'     => 'date',
			'order'       => 'DESC',
		);

		$query            = new WP_Query( $args );
		$vendor_resources = $query->posts;


		foreach ( $vendor_resources as $resource ) {

			?>
			<tr class="nk-tb-item">
				<td class="nk-tb-col marketking-column-small">
					<a href="<?php echo esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-resource/' . $resource->ID ); ?>">
						<span><?php echo esc_html( $resource->post_title ); ?></span>
					</a>
				</td>
				<td class="nk-tb-col tb-col-md marketking-column-mid">
					<?php
					global $wpdb;
					$parents      = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$wpdb->prefix}wc_booking_relationships WHERE resource_id = %d ORDER BY sort_order;", $resource->ID ) );
					$parent_posts = array();
					foreach ( $parents as $parent_id ) {
						if ( empty( get_the_title( $parent_id ) ) ) {
							continue;
						}

						$parent_posts[] = '<a href="' . esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-product/' . $parent_id ) . '">' . get_the_title( $parent_id ) . '</a>';
					}
					echo $parent_posts ? wp_kses_post( implode( ', ', $parent_posts ) ) : esc_html__( 'N/A', 'marketking' );
					?>
				</td>

				<td class="nk-tb-col tb-col-md marketking-column-min">
					<a href="<?php echo esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-resource/' . $resource->ID ); ?>">
						<button class="btn btn-sm btn-dim btn-secondary marketking_manage_order"
						        value="<?php echo esc_attr( $resource->ID ); ?>">
							<em class="icon ni ni-bag-fill"></em>
							<span><?php esc_html_e( 'Edit resource', 'marketking' ); ?></span>
						</button>
					</a>
					<button class="btn btn-sm btn-dim btn-gray marketking_manage_order marketking_delete_button_resource toggle"
					        value="<?php echo esc_attr( $resource->ID ); ?>">
						<em class="icon ni ni-trash"></em>
						<span><?php esc_html_e( 'Delete', 'marketking' ); ?></span>
					</button>

				</td>

			</tr>
			<?php
		}
	
		?>

		</tbody>

	</table>

	</div>
	</div>
	</div>
	</div>
	<?php

}
?>