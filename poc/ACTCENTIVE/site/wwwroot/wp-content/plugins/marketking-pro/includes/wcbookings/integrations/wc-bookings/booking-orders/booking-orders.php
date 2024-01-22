<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*

Orders View Dashboard Page
* @version 1.0.2

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/
$vendor_id = get_current_user_id();

if ( marketking()->is_vendor_team_member() ) {
	$vendor_id = marketking()->get_team_member_parent();
}


$manage_product_url  = trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-product';
$manage_resource_url = trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-resource';

?><?php
if ( intval( get_option( 'marketking_agents_can_manage_orders_setting', 1 ) ) === 1 ) {
	if ( marketking()->vendor_has_panel( 'orders' ) ) {
		?>
		<div class="nk-content marketking_orders_page" style="margin-top:65px">
		<div class="container-fluid">
		<div class="nk-content-inner">
		<div class="nk-content-body">
		<div class="nk-block-head nk-block-head-sm">
			<div class="nk-block-between">
				<div class="nk-block-head-content">
					<h3 class="nk-block-title page-title"><?php esc_html_e( 'Booking Orders', 'marketking' ); ?></h3>
					<div class="nk-block-des text-soft">
						<p><?php esc_html_e( 'Here you can view and manage all booking orders assigned to you.', 'marketking' ); ?></p>
					</div>
				</div><!-- .nk-block-head-content -->
				<div class="nk-block-head-content">
					<div class="toggle-wrap nk-block-tools-toggle">
						<a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1"
						   data-target="more-options"><em class="icon ni ni-more-v"></em></a>
						<div class="toggle-expand-content" data-content="more-options">
							<ul class="nk-block-tools g-3">
								<li>
									<div class="form-control-wrap">
										<div class="form-icon form-icon-right">
											<em class="icon ni ni-search"></em>
										</div>
										<input type="text" class="form-control"
										       id="marketking_bookingsorders_search"
										       placeholder="<?php esc_html_e( 'Search orders...', 'marketking' ); ?>">
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
													( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'add-booking-order/' ); ?>"
													   class="btn btn-primary d-md-inline-flex"><em
																class="icon ni ni-plus"></em>
														<span><?php esc_html_e( 'Add Booking Order',
																'marketking' ); ?></span>
													</a>
												</li>
												<?php
											} else {
												// show some error message that they reached the max nr of products
												?>
												<button type="button" class="btn
                                                            btn-gray d-none d-md-inline-flex"
												        disabled="disabled"><em
															class="icon ni
			                                                            ni-plus"></em>&nbsp;
												                                      &nbsp;<?php
													esc_html_e( 'Add Booking (Max Limit Reached)', 'marketking' ); ?>
												</button>
												<?php
											}
										}
									}
								}

								?>
							</ul>
						</div>
					</div>
				</div><!-- .nk-block-head-content -->
			</div><!-- .nk-block-between -->
			<div class="marketking_importexport_buttons_container">
				<?php
				do_action( 'marketking_wc_bookings_nav' );
				?>
			</div>
		</div><!-- .nk-block-head -->

		<table id="marketking_dashboard_bookingsorders_table" class="nk-tb-list is-separate mb-3">

		<thead>
		<tr class="nk-tb-item nk-tb-head">
			<th class="nk-tb-col marketking-column-mid">
				<span class="sub-text"><?php esc_html_e( 'ID', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md ">
				<span class="sub-text"><?php esc_html_e( 'Status', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md ">
				<span class="sub-text"><?php esc_html_e( 'Booked Product', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md marketking-column-mid">
				<span class="sub-text"><?php esc_html_e( '# of Persons', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md marketking-column-mid">
				<span class="sub-text"><?php esc_html_e( 'Categories', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md ">
				<span class="sub-text"><?php esc_html_e( 'Booked by', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md marketking-column-small">
				<span class="sub-text"><?php esc_html_e( 'Order', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md marketking-column-mid">
				<span class="sub-text"><?php esc_html_e( 'Start Date', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md marketking-column-mid">
				<span class="sub-text"><?php esc_html_e( 'End Date', 'marketking' ); ?></span>
			</th>
			<th class="nk-tb-col tb-col-md marketking-column-min">
				<span class="sub-text"><?php esc_html_e( 'Actions', 'marketking' ); ?></span>
			</th>

		</tr>
		</thead>

			<tfoot>
			<tr class="nk-tb-item nk-tb-head">
				<th class="nk-tb-col tb-non-tools"><?php esc_html_e( 'id', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e( 'status', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-non-tools"><?php esc_html_e( 'booked product', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e( '# of persons', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e( 'categories', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e( 'booked by', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e( 'order', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e( 'start date', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e( 'end date', 'marketking' ); ?></th>
				<th class="nk-tb-col tb-col-md tb-non-tools marketking-column-min"></th>
			</tr>
			</tfoot>
			
		<tbody>
		<?php

			$args  = [
				'post_type'   => 'wc_booking',
				'post_status' => array(
					'confirmed',
					'paid',
					'unpaid',
					'pending-confirmation',
					'cancelled',
				),
				'numberposts' => - 1,

			];
			$query = new WP_Query( $args );
			$rows  = $query->posts;

			foreach ( $rows as $row ) {


				$booking = get_wc_booking( $row->ID );

				$product   = $booking->get_product();
				$resource  = $booking->get_resource();
				$order     = $booking->get_order();
				$booked_by = $booking->get_customer();


				if ( ! in_array( $booking->get_product_id(), marketking()
					->get_vendor_products( $vendor_id ) ) ) {
					continue;
				}
				if ( $booking !== false ) {
					?>
					<tr class="nk-tb-item">
					<td class="nk-tb-col  marketking-column-mid">
						<a href="<?php echo esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-booking-order/' . $row->ID ); ?>">

							<div>
								<span class="tb-lead">#<?php echo esc_html( $row->ID ); ?></span>
							</div>
						</a>

					</td>
					<td class="nk-tb-col tb-col-md ">
						<div>
							<span class="dot bg-warning d-mb-none"></span>
							<?php
							$status = $booking->get_status();

							$statustext = $badge = '';
							if ( $status === 'confirmed' ) {
								$badge      = 'badge-success';
								$statustext = esc_html__( 'Confirmed', 'marketking' );
							} else if ( $status === 'pending-confirmation' ) {
								$badge      = 'badge-warning';
								$statustext = esc_html__( 'Pending Confirmation', 'marketking' );
							} else if ( in_array( $status, apply_filters( 'marketking_earning_completed_statuses', array( 'paid' ) ) ) ) {
								$badge      = 'badge-info';
								$statustext = esc_html__( 'Paid', 'marketking' );
							} else if ( $status === 'cancelled' ) {
								$badge      = 'badge-gray';
								$statustext = esc_html__( 'Cancelled', 'marketking' );
							} else if ( $status === 'unpaid' ) {
								$badge      = 'badge-dark';
								$statustext = esc_html__( 'Unpaid', 'marketking' );
							}
							?>
							<span class="badge badge-sm badge-dot has-bg <?php echo esc_attr( $badge ); ?> d-none d-mb-inline-flex"><?php
								echo esc_html( $statustext );
								?></span>
						</div>
					</td>
					<td class="nk-tb-col "
					    data-order="<?php echo esc_attr( $product->get_title() ); ?>">
						<div>
                                                        <span class="tb-sub text-primary"><?php


	                                                        if ( $product ) {
		                                                        echo '<a href="' . esc_url( $manage_product_url . '/' . $product->get_id() )
		                                                             . '">'
		                                                             . $product->get_title() . '</a>';

		                                                        if ( $resource ) {
			                                                        echo '(<a href="' . esc_url( $manage_resource_url . '/' . $product->get_id() ) . '">' . $resource->get_title() . '</a>)';
		                                                        }
	                                                        } else {
		                                                        echo '-';
	                                                        }
	                                                        ?>
                                                        </span>
						</div>
					</td>
					<td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php

					$persons       = get_post_meta( $booking->ID, '_booking_persons', true );
					$total_persons = 0;
					if ( ! empty( $persons ) && is_array( $persons ) ) {
						foreach ( $persons as $person_count ) {
							$total_persons = $total_persons + $person_count;
						}
					}
					?>">
						<?php
						if ( ! is_object( $booking ) || ! $booking->has_persons() ) {
							esc_html_e( 'N/A', 'marketking-multivendor-marketplace-for-woocommerce' );
						} else {

							echo esc_html( $total_persons );
						}

						?>
					</td>
					<td class="nk-tb-col tb-col-md marketking-column-mid">
						<span class="tb-sub"><?php
							//							echo esc_html( $categoriestext );

							?></span>
					</td>
					<td class="nk-tb-col tb-col-md " data-order="<?php
					echo esc_attr( $booked_by->name );
					?>">
						<div>
                                                         <span class="tb-sub"><?php

	                                                         if ( $booked_by ) {
		                                                         echo '<a href="mailto:' . $booked_by->email . '">' . $booked_by->name . '</a>';
	                                                         } else {
		                                                         echo '-';
	                                                         }
	                                                         ?></span>
						</div>
					</td>
					<?php
					$order_status = ! empty( $order ) ? $order->get_status() : '';
					$order_id     = ! empty( $order ) ? $order->get_id() : '';

					?>
					<td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php
					echo esc_attr( $order_id );
					?>">
						<a href="<?php echo esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'manage-order/' . $order_id ); ?>">

							<div>
								<span class="tb-lead"><strong>#<?php echo esc_html( $order_id ) .
								                                          '-' . $order_status;
									?></span>
							</div>
						</a>
					</td>
					<td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php


					echo esc_attr( $booking->get_start_date() );


					?>">
						<div>
								<span class="tb-lead"><?php


									echo esc_attr( $booking->get_start_date() );

									?></span>
						</div>
					</td>
					<td class="nk-tb-col tb-col-md marketking-column-mid"
					    data-order="<?php echo esc_attr( $booking->get_end_date() ); ?>">
						<div>
                          <span class="tb-lead"><?php
	                          echo esc_attr( $booking->get_end_date() );

	                          ?></span>
						</div>
					</td>
					<td class="nk-tb-col tb-col-md marketking-column-min">
						<ul class="nk-tb-actions gx-1 my-n1">
							<li class="mr-n1">
								<div class="dropdown">
									<a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
									   data-toggle="dropdown"><em
												class="icon ni ni-more-h"></em></a>
									<div class="dropdown-menu dropdown-menu-right">
										<ul class="link-list-opt no-bdr">
											<?php
											// either not team member, or team member with permission to add
											if ( ! marketking()->is_vendor_team_member() || $checkedval === 1 ) {
												?>
												<li>
													<a href="<?php echo esc_attr( trailingslashit( get_page_link(
														                              get_option( 'marketking_vendordash_page_setting', 'disabled' ) ) ). 'edit-booking-order/' . $booking->get_id() ); ?>"><em
																class="icon ni ni-edit"></em>
														<span><?php esc_html_e( 'Edit Booking Order',
																'marketking' ); ?></span>
													</a></li>
												<?php
											}

											?>

										</ul>
									</div>
								</div>
							</li>
						</ul>
					</td>

					</tr>
					<?php
				}
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
}
?>