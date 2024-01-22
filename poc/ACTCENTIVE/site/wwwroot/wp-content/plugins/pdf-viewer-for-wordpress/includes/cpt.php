<?php
/**
 * Register Custom Post type
 *
 * @package pdf-viewer-for-wordpress
 */

if ( ! function_exists( 'tnc_pvfw_pdf_post_type' ) ) {

	/**
	 * Post Type Data
	 *
	 * @return void
	 */
	function tnc_pvfw_pdf_post_type() {

		$labels = array(
			'name'                  => _x( 'FlipBooks', 'Post Type General Name', 'pdf-viewer-for-wordpress' ),
			'singular_name'         => _x( 'TNC FlipBook', 'Post Type Singular Name', 'pdf-viewer-for-wordpress' ),
			'menu_name'             => __( 'TNC FlipBook', 'pdf-viewer-for-wordpress' ),
			'name_admin_bar'        => __( 'TNC FlipBook', 'pdf-viewer-for-wordpress' ),
			'archives'              => __( 'TNC FlipBook Archives', 'pdf-viewer-for-wordpress' ),
			'attributes'            => __( 'TNC FlipBook Attributes', 'pdf-viewer-for-wordpress' ),
			'parent_item_colon'     => __( 'Parent TNC FlipBook:', 'pdf-viewer-for-wordpress' ),
			'all_items'             => __( 'All FlipBooks', 'pdf-viewer-for-wordpress' ),
			'add_new_item'          => __( 'Add New FlipBook', 'pdf-viewer-for-wordpress' ),
			'add_new'               => __( 'Add New', 'pdf-viewer-for-wordpress' ),
			'new_item'              => __( 'New FlipBook', 'pdf-viewer-for-wordpress' ),
			'edit_item'             => __( 'Edit FlipBook', 'pdf-viewer-for-wordpress' ),
			'update_item'           => __( 'Update FlipBook', 'pdf-viewer-for-wordpress' ),
			'view_item'             => __( 'View FlipBook', 'pdf-viewer-for-wordpress' ),
			'view_items'            => __( 'View FlipBooks', 'pdf-viewer-for-wordpress' ),
			'search_items'          => __( 'Search FlipBooks', 'pdf-viewer-for-wordpress' ),
			'not_found'             => __( 'Not found', 'pdf-viewer-for-wordpress' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'pdf-viewer-for-wordpress' ),
			'featured_image'        => __( 'Featured Image', 'pdf-viewer-for-wordpress' ),
			'set_featured_image'    => __( 'Set featured image', 'pdf-viewer-for-wordpress' ),
			'remove_featured_image' => __( 'Remove featured image', 'pdf-viewer-for-wordpress' ),
			'use_featured_image'    => __( 'Use as featured image', 'pdf-viewer-for-wordpress' ),
			'insert_into_item'      => __( 'Insert into FlipBook', 'pdf-viewer-for-wordpress' ),
			'uploaded_to_this_item' => __( 'Uploaded to this FlipBook', 'pdf-viewer-for-wordpress' ),
			'items_list'            => __( 'FlipBooks list', 'pdf-viewer-for-wordpress' ),
			'items_list_navigation' => __( 'FlipBooks list navigation', 'pdf-viewer-for-wordpress' ),
			'filter_items_list'     => __( 'Filter FlipBooks list', 'pdf-viewer-for-wordpress' ),
		);
		$args   = array(
			'label'               => __( 'All FlipBooks', 'pdf-viewer-for-wordpress' ),
			'description'         => __( 'TNC FlipBook items', 'pdf-viewer-for-wordpress' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'thumbnail' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'menu_position'       => 10,
			'menu_icon'           => plugins_url() . '/' . PVFW_PLUGIN_DIR . '/images/pdf-viewer-icon-white.svg',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);
		register_post_type( 'pdfviewer', $args );

		if ( get_option( 'tnc_pvfw_permalink_flushed' ) != '1' ) {
			flush_rewrite_rules();
			update_option( 'tnc_pvfw_permalink_flushed', '1', true );
		}
	}
	add_action( 'init', 'tnc_pvfw_pdf_post_type', 0 );

}

/**
 * Register REST Field
 */
register_rest_field( 'pdfviewer', 'metadata',
	array(
		'get_callback' => function ( $data ) {
			return get_post_meta( $data['id'], '', '' );
		},
	)
);

/**
 * Register meta box for displaying pdf viewer url with toolbar options.
 *
 * @package pdf-viewer-for-wordpress
 */

function tnc_pvfw_show_url_meta_boxes() {
	add_meta_box( 'tnc-pvfw-show-url-meta-box-id', __( 'Share Viewer URL', 'pdf-viewer-for-wordpress' ), 'tnc_pvfw_show_url_callback', 'pdfviewer', $context = 'side' );
}
add_action( 'add_meta_boxes', 'tnc_pvfw_show_url_meta_boxes' );


function tnc_pvfw_show_url_callback( ) {
	
	$tnc_pvfw_table_cell_url = get_post_meta( get_the_ID(), 'tnc_pvfw_pdf_viewer_fields', true );

		$url = get_post_permalink();
		$url_id = url_to_postid( $url );
		if( isset( $tnc_pvfw_table_cell_url['default-page-number'] ) && !empty( $tnc_pvfw_table_cell_url['default-page-number'] ) ){
			$page = $tnc_pvfw_table_cell_url['default-page-number'];
		} else {
			$page = '';
		}

		if( isset( $tnc_pvfw_table_cell_url['default-zoom'] ) && !empty( $tnc_pvfw_table_cell_url['default-zoom'] ) ){
			$zoom = $tnc_pvfw_table_cell_url['default-zoom'];
		} else {
			$zoom = 'auto';
		}

		if( isset( $tnc_pvfw_table_cell_url['toolbar-default-page-mode'] ) && !empty( $tnc_pvfw_table_cell_url['toolbar-default-page-mode'] ) ){
			$pagemode = $tnc_pvfw_table_cell_url['toolbar-default-page-mode'];
		} else {
			$pagemode = 'none';
		}

		$full_url = $url.'?auto_viewer=true#page=' . $page . '&zoom=' . $zoom . '&pagemode=' . $pagemode	;
	if( get_post_status () == 'publish' ) {
		?>	<div class="csf-field-text input">
				<input class="pvfw_tnc_copy_link_url tnc-pvfw-copy-share-url" id="<?php echo 'pvfw_tnc_copy_link_url_'.$url_id ?>" value="<?php esc_html_e( $full_url ) ?>" readonly />
				<span class="copy-to-clipboard-container">
					<button class="button button-small" id="<?php echo 'pvfw_tnc_copy_link_url_'.$url_id ?>" onClick="copy_url_text_formate_clipboard_target(event,this.id)">Copy URL to clipboard</button>
				</span>
				<span class="pvfw-url-copied-message" id="pvfw-url-copied-message"></span>
	</div>
		<?php
	} else {
		_e( 'URL will be available for copying after publishing.', 'pdf-viewer-for-wordpress' );
	}
}

