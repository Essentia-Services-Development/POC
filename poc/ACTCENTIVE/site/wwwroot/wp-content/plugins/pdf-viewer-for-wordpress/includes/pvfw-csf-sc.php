<?php
/**
 * Shortcode Generator
 *
 * @package pdf-viewer-for-wordpress
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.
// Control core classes for avoid errors.
if ( class_exists( 'PVFWOF' ) ) {

	// Set a unique slug-like ID.
	$prefix = 'pvfw_csf_shortcodes';

	// Create a shortcoder.
	PVFWOF::createShortcoder(
		$prefix,
		array(
			'button_title'   => 'Add FlipBook',
			'select_title'   => 'Select Type',
			'insert_title'   => 'Insert FlipBook',
			'show_in_editor' => true,
			'gutenberg'      => array(
				'title'       => 'TNC FlipBook',
				'description' => 'Use this to Generate FlipBook for WordPress Shortcodes',
				'icon'        => 'pdf',
				'category'    => 'media',
				'keywords'    => array( 'shortcode', 'pdf', 'viewer','flipbook','tnc','tnc flipbook'),
				'placeholder' => 'Use the Add FlipBook viewer button above to generate shortcode...',
			),
		)
	);

	// tnc-pdf-viewer-iframe.
	PVFWOF::createSection(
		$prefix,
		array(
			'title'     => 'Embed a FlipBook',
			'view'      => 'normal',
			'shortcode' => 'pvfw-embed',
			'fields'    => array(

				array(
					'type'    => 'subheading',
					'content' => 'Basic Options',
				),

				array(
					'type'     => 'callback',
					'function' => 'tnc_pvfw_create_viewer_url_callback',
				),

				array(
					'id'          => 'viewer_id',
					'type'        => 'select',
					'title'       => esc_html__( 'Select Viewer to Embed', 'pdf-viewer-for-wordpress' ),
					'subtitle'    => esc_html__( 'Search using the viewer title', 'pdf-viewer-for-wordpress' ),
					'placeholder' => esc_html__( 'Select a Viewer', 'pdf-viewer-for-wordpress' ),
					'chosen'      => true,
					'ajax'        => true,
					'options'     => 'posts',
					'query_args'  => array(
						'post_type' => 'pdfviewer',
					),
				),


				array(
					'id'      => 'width',
					'type'    => 'text',
					'title'   => 'Width',
					'default' => '100%',
				),

				array(
					'id'      => 'height',
					'type'    => 'text',
					'title'   => 'Height',
					'default' => '800',
				),

				array(
					'id'    => 'iframe_title',
					'type'  => 'text',
					'title' => 'iFrame Titlte',
				),
			),

		)
	);

	// pvfw-link.
	PVFWOF::createSection(
		$prefix,
		array(
			'title'     => esc_html__( 'Link to a FlipBook', 'pdf-viewer-for-wordpress' ),
			'view'      => 'normal',
			'shortcode' => 'pvfw-link',
			'fields'    => array(

				array(
					'type'    => 'subheading',
					'content' => 'Basic Options',
				),

				array(
					'type'     => 'callback',
					'function' => 'tnc_pvfw_create_viewer_url_callback',
				),

				array(
					'id'          => 'viewer_id',
					'type'        => 'select',
					'title'       => esc_html__( 'Select Viewer to Link to', 'pdf-viewer-for-wordpress' ),
					'subtitle'    => esc_html__( 'Search using the viewer title', 'pdf-viewer-for-wordpress' ),
					'placeholder' => esc_html__( 'Select a Viewer', 'pdf-viewer-for-wordpress' ),
					'chosen'      => true,
					'ajax'        => true,
					'options'     => 'posts',
					'query_args'  => array(
						'post_type' => 'pdfviewer',
					),
				),

				array(
					'id'      => 'text',
					'type'    => 'text',
					'title'   => 'Link Text',
					'default' => 'Open PDF',
				),

				array(
					'id'      => 'class',
					'type'    => 'text',
					'title'   => 'Link CSS Class',
					'default' => 'pdf-viewer-link-single',
				),

				array(
					'id'          => 'target',
					'type'        => 'select',
					'title'       => 'Link Target',
					'placeholder' => 'Select Link Target',
					'options'     => array(
						'_blank'  => 'New Tab',
						'_parent' => 'Same Tab',
					),
					'default'     => '_parent',
				),
			),

		)
	);

	// pvfw-image-link
	PVFWOF::createSection(
		$prefix,
		array(
			'title'     => esc_html__( 'Image Link to a FlipBook', 'pdf-viewer-for-wordpress' ),
			'view'      => 'normal',
			'shortcode' => 'pvfw-image-link',
			'fields'	=> array(

				array(
					'type'    => 'subheading',
					'content' => esc_html__( 'Basic Options', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'     => 'callback',
					'function' => 'tnc_pvfw_create_viewer_url_callback',
				),

				array(
					'id'          => 'viewer_id',
					'type'        => 'select',
					'title'       => esc_html__( 'Select Viewer to Link Image', 'pdf-viewer-for-wordpress' ),
					'subtitle'    => esc_html__( 'Search using the viewer title', 'pdf-viewer-for-wordpress' ),
					'placeholder' => esc_html__( 'Select a Viewer', 'pdf-viewer-for-wordpress' ),
					'chosen'      => true,
					'ajax'        => true,
					'options'     => 'posts',
					'query_args'  => array(
						'post_type' => 'pdfviewer',
					),
				),

				array(
					'id'    => 'img_url',
					'type'  => 'upload',
					'title' => esc_html__( 'Upload Image', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'      => 'alt_text',
					'type'    => 'text',
					'title'   => esc_html__( 'Alt Text', 'pdf-viewer-for-wordpress' ),
					'default' => esc_html__( 'Image missing. Click here to open PDF file', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'          => 'target',
					'type'        => 'select',
					'title'       => esc_html__( 'Image Link Target', 'pdf-viewer-for-wordpress' ),
					'placeholder' => esc_html__( 'Select Link Target', 'pdf-viewer-for-wordpress' ),
					'options'     => array(
						'_blank'  => 'New Tab',
						'_parent' => 'Same Tab',
					),
					'default'     => '_parent',
				),

				array(
					'id'      => 'width',
					'type'    => 'text',
					'title'   => esc_html__( 'Image Width', 'pdf-viewer-for-wordpress' ),
					'desc' => esc_html__( 'Use suffix when use number like: 10px 10rem 10em etc', 'pdf-viewer-for-wordpress' ),
					'default' => '100%',
				),

				array(
					'id'      => 'height',
					'type'    => 'text',
					'title'   => esc_html__( 'Image Height', 'pdf-viewer-for-wordpress' ),
					'desc' => esc_html__( 'Use suffix when use number like: 10px 10rem 10em etc', 'pdf-viewer-for-wordpress' ),
					'default' => 'auto',
				),

				array(
					'id'      => 'alignment',
					'type'    => 'select',
					'title'   => esc_html__( 'Image Alignment', 'pdf-viewer-for-wordpress' ),
					'options' => array(
						'inherit'	=> 'Inherit',
						'left'		=> 'Left',
						'center'	=> 'Center',
						'right'		=> 'Right'
					),
					'default' => 'inherit',
				),

				array(
					'id'      => 'class',
					'type'    => 'text',
					'title'   => esc_html__( 'Image CSS Class', 'pdf-viewer-for-wordpress' ),
					'default' => esc_html__( 'pdf-viewer-image-link-single', 'pdf-viewer-for-wordpress' ),
				),
			)
		)
	);

}
