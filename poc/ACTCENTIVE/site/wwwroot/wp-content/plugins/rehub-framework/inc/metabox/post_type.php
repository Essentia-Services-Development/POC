<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php $def_p_types = REHub_Framework::get_option('rehub_ptype_formeta');?>
<?php $def_p_types = (!empty($def_p_types[0])) ? (array)$def_p_types : array('post', 'blog')?>
<?php
return array(
	'id'          => 'rehub_post',
	'types'       => $def_p_types,
	'title'       => esc_html__('Post Type', 'rehub-framework'),
	'priority'    => 'high',
	'mode'        => WPALCHEMY_MODE_EXTRACT,
	'template'    => array(
		array(
			'type' => 'radioimage',
			'name' => 'rehub_framework_post_type',
			'label' => esc_html__('Choose Type of Post', 'rehub-framework'),
			'description' => '',
			'items' => array(
				array(
					'value' => 'regular',
					'label' => esc_html__('Regular', 'rehub-framework'),
					'img' => RH_FRAMEWORK_URL . '/assets/img/regular_post_icon.png',
				),
				array(
					'value' => 'video',
					'label' => esc_html__('Video', 'rehub-framework'),
					'img' => RH_FRAMEWORK_URL . '/assets/img/video_post_icon.png',
				),
				array(
					'value' => 'gallery',
					'label' => esc_html__('Gallery', 'rehub-framework'),
					'img' => RH_FRAMEWORK_URL . '/assets/img/gallery_post_icon.png',
				),
				array(
					'value' => 'review',
					'label' => esc_html__('Review', 'rehub-framework'),
					'img' => RH_FRAMEWORK_URL . '/assets/img/review_post_icon.png',
				),
				array(
					'value' => 'music',
					'label' => esc_html__('Music', 'rehub-framework'),
					'img' => RH_FRAMEWORK_URL . '/assets/img/music_post_icon.png',
				),
			),
			'default' => 'regular'
		),
		
		
		// video group
		array(
			'type'      => 'group',
			'repeating' => false,
			'length'    => 1,
			'name'      => 'video_post',
			'title'     => esc_html__('Video Post', 'rehub-framework'),
			'dependency' => array(
				'field'    => 'rehub_framework_post_type',
				'function' => 'rehub_framework_post_type_is_video',
			),
			'fields'    => array(
				// embed
				array(
					'type' => 'textbox',
					'name' => 'video_post_embed_url',
					'description' => esc_html__('Insert youtube or vimeo link on page with video', 'rehub-framework'),
					'label' => esc_html__('Video Url', 'rehub-framework'),
				),				
				array(
					'type' => 'toggle',
					'name' => 'video_post_schema_thumb',
					'label' => esc_html__('Auto thumbnail', 'rehub-framework'),
					'description' => esc_html__('Enable auto featured image from video (will not work on some servers)', 'rehub-framework'),					
				),
				array(
					'type' => 'toggle',
					'name' => 'video_post_schema',
					'label' => esc_html__('Enable schema.org for video?', 'rehub-framework'),
					'description' => esc_html__('Check this box if you want to enable videoobject schema', 'rehub-framework'),
				),	
				array(
					'type' => 'textbox',
					'name' => 'video_post_schema_title',
					'label' => esc_html__('Title', 'rehub-framework'),
					'description' => esc_html__('Set title of video block or leave blank to use post title', 'rehub-framework'),					
					'dependency' => array(
                         'field' => 'video_post_schema',
                         'function' => 'vp_dep_boolean',
                    ),
					'default' => '',
				),
				array(
					'type' => 'textbox',
					'name' => 'video_post_schema_desc',
					'label' => esc_html__('Description', 'rehub-framework'),
					'description' => esc_html__('Set description of video block or leave blank to use post excerpt', 'rehub-framework'),					
					'dependency' => array(
                         'field' => 'video_post_schema',
                         'function' => 'vp_dep_boolean',
                    ),
					'default' => '',
				),																			
			),
		),
		// gallery group
		array(
			'type'      => 'group',
			'repeating' => false,
			'length'    => 1,
			'name'      => 'gallery_post',
			'title'     => esc_html__('Gallery Post', 'rehub-framework'),
			'dependency' => array(
				'field'    => 'rehub_framework_post_type',
				'function' => 'rehub_framework_post_type_is_gallery',
			),
			
			'fields'    => array(
				array(
					'type' => 'toggle',
					'name' => 'gallery_post_images_resize',
					'label' => esc_html__('Disable height resize for slider', 'rehub-framework'),
					'description' => esc_html__('This option disable resize of photo. By default, photos are resized for 400 px height', 'rehub-framework'),												
				),				
				array(
					'type'      => 'group',
					'repeating' => true,
					'name'      => 'gallery_post_images',
					'title'     => esc_html__('Image', 'rehub-framework'),
					'fields'    => array(
						array(
							'type'      => 'upload',
							'name'      => 'gallery_post_image',
							'label'     => esc_html__('Add Image', 'rehub-framework'),
						),
						array(
							'type'      => 'textbox',
							'name'      => 'gallery_post_image_caption',
							'label'     => esc_html__('Caption', 'rehub-framework'),
						),
						array(
							'type' => 'textbox',
							'name' => 'gallery_post_video',
							'description' => esc_html__('Insert youtube link of page with video. If you set this field, image and caption will be ignored for this slide', 'rehub-framework'),
							'label' => esc_html__('Video Url', 'rehub-framework'),
						),													
					),
				),
			),
		),
		// review group
		array(
			'type'      => 'group',
			'repeating' => false,
			'length'    => 1,
			'name'      => 'review_post',
			'title'     => 'Review Post',
			'dependency' => array(
				'field'    => 'rehub_framework_post_type',
				'function' => 'rehub_framework_post_type_is_review',
			),
			'fields'    => array(											 

				array(
					'type'      => 'textbox',
					'name'      => 'review_post_heading',
					'label'     => esc_html__('Review Heading', 'rehub-framework'),
					'description' => esc_html__('Short review heading (e.g. Excellent!)', 'rehub-framework'),
				),
				array(
					'type'      => 'textarea',
					'name'      => 'review_post_summary_text',
					'label'     => esc_html__('Summary Text', 'rehub-framework'),
				),
				array(
					'type'      => 'textarea',
					'name'      => 'review_post_pros_text',
					'label'     => esc_html__('PROS. Place each from separate line (optional)', 'rehub-framework'),
				),
				array(
					'type'      => 'textarea',
					'name'      => 'review_post_cons_text',
					'label'     => esc_html__('CONS. Place each from separate line (optional)', 'rehub-framework'),
				),								

				array(
					'type' => 'toggle',
					'name' => 'review_post_product_shortcode',
					'label' => esc_html__('Enable shortcode inserting', 'rehub-framework'),
					'description' => esc_html__('If enable you can insert review box in any place of content with shortcode [review]. If disable - it will be after content.', 'rehub-framework'),					
				),

				array(
					'type'      => 'slider',
					'name'      => 'review_post_score_manual',
					'label'     => esc_html__('Set overall score', 'rehub-framework'),
					'description' => esc_html__('Enter overall score of review or leave blank to auto calculation based on criterias score', 'rehub-framework'),
					'min'       => 0,
					'max'       => 10,
					'step'      => 0.5,					
				),

				array(
					'type'      => 'group',
					'repeating' => true,
					'sortable'  => true,
					'name'      => 'review_post_criteria',
					'title'     => esc_html__('Review Criterias', 'rehub-framework'),
					'fields'    => array(
						array(
							'type'      => 'textbox',
							'name'      => 'review_post_name',
							'label'     => esc_html__('Name', 'rehub-framework'),
						),
						array(
							'type'      => 'slider',
							'name'      => 'review_post_score',
							'label'     => esc_html__('Score', 'rehub-framework'),
							'min'       => 0,
							'max'       => 10,
							'step'      => 0.5,
						),
					),
				),
			),
		),
		
		// music group
		array(
			'type'      => 'group',
			'repeating' => false,
			'length'    => 1,
			'name'      => 'music_post',
			'title'     => esc_html__('Music Post', 'rehub-framework'),
			'dependency' => array(
				'field'    => 'rehub_framework_post_type',
				'function' => 'rehub_framework_post_type_is_music',
			),
			'fields'    => array(
				array(
					'type' => 'radiobutton',
					'name' => 'music_post_source',
					'label' => esc_html__('Music Source', 'rehub-framework'),
					'items' => array(
						array(
							'value' => 'music_post_soundcloud',
							'label' => esc_html__('Music from Soundcloud', 'rehub-framework'),
						),
						array(
							'value' => 'music_post_spotify',
							'label' => esc_html__('Music from Spotify', 'rehub-framework'),
						),
					),
				),

				array(
					'type' => 'textarea',
					'name' => 'music_post_soundcloud_embed',
					'description' => esc_html__('Insert full Soundcloud embed code.', 'rehub-framework'),
					'label' => esc_html__('Soundcloud embed code', 'rehub-framework'),
					'dependency' => array(
						'field'    => 'music_post_source',
						'function' => 'rehub_framework_post_music_is_soundcloud',
					),
				),
				array(
					'type' => 'textbox',
					'name' => 'music_post_spotify_embed',
					'description' => esc_html__('To get the Spotify Song URI go to <strong>Spotify</strong> > Right click on the song you want to embed > Click <strong>Copy Spotify URI</strong> > Paste code in this field.)', 'rehub-framework'),
					'label' => esc_html__('Spotify Song URI', 'rehub-framework'),
					'dependency' => array(
						'field'    => 'music_post_source',
						'function' => 'rehub_framework_post_music_is_spotify',
					),
				),

			),
		),
		
	),
);

/**
 * EOF
 */