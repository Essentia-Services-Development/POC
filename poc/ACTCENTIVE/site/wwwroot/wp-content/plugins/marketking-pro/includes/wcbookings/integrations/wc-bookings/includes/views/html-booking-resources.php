<div id="bookings_resources" class="woocommerce_options_panel panel wc-metaboxes-wrapper">
	<script>
		<?php
		if ( class_exists( '\WC_Bookings' ) ) : ?>

        let $product_type = $('#product-type');

        $product_type.change(function () {
            if ($product_type.val() !== 'booking') {

                $product_type.parents().find('.inside #general_product_data .options_group' +
                    '.show_if_booking').hide();
                $product_type.parents().find('.postbox-header .show_if_booking').hide();

            } else {
                $product_type.parents().find('.postbox-header .show_if_booking').show();
            }
        });
		<?php endif; ?>
	</script>
	<div class="options_group" id="resource_options">

		<?php
		woocommerce_wp_text_input( array(
			'id'          => '_wc_booking_resource_label',
			'placeholder' => __( 'Type', 'marketking' ),
			'label'       => __( 'Label', 'marketking' ),
			'value'       => $bookable_product->get_resource_label( 'edit' ),
			'desc_tip'    => true,
			'description' => __( 'The label shown on the frontend if the resource is customer defined.', 'marketking' ),
		) );
		?>

		<?php
		woocommerce_wp_select( array(
			'id'          => '_wc_booking_resources_assignment',
			'label'       => __( 'Resources are...', 'marketking' ),
			'description' => '',
			'desc_tip'    => true,
			'value'       => $bookable_product->get_resources_assignment( 'edit' ),
			'options'     => array(
				'customer'  => __( 'Customer selected', 'marketking' ),
				'automatic' => __( 'Automatically assigned', 'marketking' ),
			),
			'description' => __( 'Customer selected resources allow customers to choose one from the booking form.', 'marketking' ),
		) );
		?>

	</div>

	<div class="options_group">

		<div class="toolbar">
			<h3><?php esc_html_e( 'Resources', 'marketking' ); ?></h3>
			<span class="toolbar_links"><a href="#"
			                               class="close_all"><?php esc_html_e( 'Close all', 'marketking' ); ?></a><a
						href="#"
						class="expand_all"><?php esc_html_e( 'Expand all', 'marketking' ); ?></a></span>
		</div>

		<div class="woocommerce_bookable_resources wc-metaboxes">

			<div id="message" class="inline woocommerce-message updated" style="margin: 1em 0;">
				<p><?php esc_html_e( 'Resources are used if you have multiple bookable items, e.g. room types, instructors or ticket types. Availability for resources is global across all bookable products.', 'marketking' ); ?></p>
			</div>

			<?php
			global $post, $wpdb;

			$all_resources        = self::get_booking_resources();
			$product_resources    = $bookable_product->get_resource_ids( 'edit' );
			$resource_base_costs  = $bookable_product->get_resource_base_costs( 'edit' );
			$resource_block_costs = $bookable_product->get_resource_block_costs( 'edit' );
			$loop                 = 0;

			if ( $product_resources ) {
				foreach ( $product_resources as $resource_id ) {
					$resource            = new WC_Product_Booking_Resource( $resource_id );
					$resource_base_cost  = isset( $resource_base_costs[ $resource_id ] ) ? $resource_base_costs[ $resource_id ] : '';
					$resource_block_cost = isset( $resource_block_costs[ $resource_id ] ) ? $resource_block_costs[ $resource_id ] : '';

					include 'html-booking-resource.php';
					$loop ++;
				}
			}
			?>
		</div>

		<p class="toolbar">
			<button type="button"
			        class="button button-primary marketking_add_resource"><?php esc_html_e( 'Add/link Resource', 'marketking' ); ?></button>

			<select name="add_resource_id" class="add_resource_id">
				<option value=""><?php esc_html_e( 'New resource', 'marketking' ); ?></option>
				<?php
				if ( $all_resources ) {
					foreach ( $all_resources as $resource ) {

						if ( in_array( $resource->ID, $product_resources ) ) {
							continue; // ignore resources that's already on the product
						}
						echo '<option value="' . esc_attr( $resource->ID ) . '">#' . absint( $resource->ID ) . ' - ' . esc_html( $resource->post_title ) . '</option>';
					}
				}
				?>
			</select>
			<a href="<?php echo esc_url( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'bookable-resources' ); ?>"
			   target="_blank"><?php esc_html_e( 'Manage Resources', 'marketking' ); ?></a>
			<!--<a href="<?php /*echo esc_url( admin_url( 'edit.php?post_type=bookable_resource' ) ); */ ?>" target="_blank"><?php /*esc_html_e( 'Manage Resources', 'marketking' ); */ ?></a>-->

		</p>
	</div>
</div>
