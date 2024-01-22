<?php

/**
 * Lists settings, default values and display of Modular Main Page.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_KB_Config_Layout_Modular {

	/**
	 * Defines KB configuration for this theme.
	 * ALL FIELDS ARE MANDATORY by default ( otherwise use 'mandatory' => 'false' )
	 *
	 * @return array with both basic and theme-specific configuration
	 */
	public static function get_fields_specification() {

        $config_specification = array(

	        'modular_main_page_toggle'                              => array(
		        'label'       => __( 'Modular Main Page', 'echo-knowledge-base' ),
		        'name'        => 'modular_main_page_toggle',
		        'type'        => EPKB_Input_Filter::CHECKBOX,
		        'default'     => 'on'
	        ),
	        'modular_main_page_custom_css_toggle'                              => array(
		        'label'       => __( 'Custom CSS', 'echo-knowledge-base' ),
		        'name'        => 'modular_main_page_custom_css_toggle',
		        'type'        => EPKB_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),

	        // Row 1
	        'ml_row_1_module'                                       => array(
		        'label'       => __( 'Row Feature', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_1_module',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'none'                  => '-----',
			        'search'                => __( 'Search Box',   'echo-knowledge-base' ),
			        'categories_articles'   => __( 'Categories & Articles',   'echo-knowledge-base' ),
			        'articles_list'         => __( 'Articles List',   'echo-knowledge-base' ),
			        'faqs'                  => __( 'FAQs',   'echo-knowledge-base' ),
			        'resource_links'        => __( 'Resource Links',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'search'
	        ),
	        'ml_row_1_desktop_width'                                => array(
		        'label'       => __( 'Row Width', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_1_desktop_width',
		        'max'         => 3000,
		        'min'         => 10,
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 100
	        ),
	        'ml_row_1_desktop_width_units'                          => array(
		        'label'       => __( 'Row Width - Units', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_1_desktop_width_units',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'style'       => 'small',
		        'options'     => array(
			        'px'            => _x( 'px', 'echo-knowledge-base' ),
			        '%'             => _x( '%',  'echo-knowledge-base' )
		        ),
		        'default'     => '%'
	        ),

	        // Row 2
	        'ml_row_2_module'                                       => array(
		        'label'       => __( 'Row Feature', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_2_module',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'none'                  => '-----',
			        'search'                => __( 'Search Box',   'echo-knowledge-base' ),
			        'categories_articles'   => __( 'Categories & Articles',   'echo-knowledge-base' ),
			        'articles_list'         => __( 'Articles List',   'echo-knowledge-base' ),
			        'faqs'                  => __( 'FAQs',   'echo-knowledge-base' ),
			        'resource_links'        => __( 'Resource Links',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'categories_articles'
	        ),
	        'ml_row_2_desktop_width'                                => array(
		        'label'       => __( 'Row Width', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_2_desktop_width',
		        'max'         => 3000,
		        'min'         => 10,
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 1080
	        ),
	        'ml_row_2_desktop_width_units'                          => array(
		        'label'       => __( 'Row Width - Units', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_2_desktop_width_units',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'style'       => 'small',
		        'options'     => array(
			        'px'            => _x( 'px', 'echo-knowledge-base' ),
			        '%'             => _x( '%',  'echo-knowledge-base' )
		        ),
		        'default'     => 'px'
	        ),

	        // Row 3
	        'ml_row_3_module'                                       => array(
		        'label'       => __( 'Row Feature', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_3_module',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'none'                  => '-----',
			        'search'                => __( 'Search Box',   'echo-knowledge-base' ),
			        'categories_articles'   => __( 'Categories & Articles',   'echo-knowledge-base' ),
			        'articles_list'         => __( 'Articles List',   'echo-knowledge-base' ),
			        'faqs'                  => __( 'FAQs',   'echo-knowledge-base' ),
			        'resource_links'        => __( 'Resource Links',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'articles_list'
	        ),
	        'ml_row_3_desktop_width'                                => array(
		        'label'       => __( 'Row Width', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_3_desktop_width',
		        'max'         => 3000,
		        'min'         => 10,
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 1080
	        ),
	        'ml_row_3_desktop_width_units'                          => array(
		        'label'       => __( 'Row Width - Units', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_3_desktop_width_units',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'style'       => 'small',
		        'options'     => array(
			        'px'            => _x( 'px', 'echo-knowledge-base' ),
			        '%'             => _x( '%',  'echo-knowledge-base' )
		        ),
		        'default'     => 'px'
	        ),

	        // Row 4
	        'ml_row_4_module'                                       => array(
		        'label'       => __( 'Row Feature', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_4_module',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'none'                  => '-----',
			        'search'                => __( 'Search Box',   'echo-knowledge-base' ),
			        'categories_articles'   => __( 'Categories & Articles',   'echo-knowledge-base' ),
			        'articles_list'         => __( 'Articles List',   'echo-knowledge-base' ),
			        'faqs'                  => __( 'FAQs',   'echo-knowledge-base' ),
			        'resource_links'        => __( 'Resource Links',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'none'
	        ),
	        'ml_row_4_desktop_width'                                => array(
		        'label'       => __( 'Row Width', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_4_desktop_width',
		        'max'         => 3000,
		        'min'         => 10,
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 1080
	        ),
	        'ml_row_4_desktop_width_units'                          => array(
		        'label'       => __( 'Row Width - Units', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_4_desktop_width_units',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'style'       => 'small',
		        'options'     => array(
			        'px'            => _x( 'px', 'echo-knowledge-base' ),
			        '%'             => _x( '%',  'echo-knowledge-base' )
		        ),
		        'default'     => 'px'
	        ),

	        // Row 5
	        'ml_row_5_module'                                       => array(
		        'label'       => __( 'Row Feature', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_5_module',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'none'                  => '-----',
			        'search'                => __( 'Search Box',   'echo-knowledge-base' ),
			        'categories_articles'   => __( 'Categories & Articles',   'echo-knowledge-base' ),
			        'articles_list'         => __( 'Articles List',   'echo-knowledge-base' ),
			        'faqs'                  => __( 'FAQs',   'echo-knowledge-base' ),
			        'resource_links'        => __( 'Resource Links',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'none'
	        ),
	        'ml_row_5_desktop_width'                                => array(
		        'label'       => __( 'Row Width', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_5_desktop_width',
		        'max'         => 3000,
		        'min'         => 10,
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 1080
	        ),
	        'ml_row_5_desktop_width_units'                          => array(
		        'label'       => __( 'Row Width - Units', 'echo-knowledge-base' ),
		        'name'        => 'ml_row_5_desktop_width_units',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'style'       => 'small',
		        'options'     => array(
			        'px'            => _x( 'px', 'echo-knowledge-base' ),
			        '%'             => _x( '%',  'echo-knowledge-base' )
		        ),
		        'default'     => 'px'
	        ),

	        // MODULE: CATEGORIES AND ARTICLES
	        'ml_categories_articles_layout'                         => array(  // TODO REMOVE LATER
		        'label'       => __( 'Layout', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_layout',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'classic'   => __( 'Classic Layout',   'echo-knowledge-base' ),
			        'product'   => __( 'Product Layout',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'classic'
	        ),
			'ml_categories_articles_icon_background_color_toggle'   => array(		// TODO REMOVE LATER
		        'label'       => __( 'Show Icon Background Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_icon_background_color_toggle',
		        'type'        => EPKB_Input_Filter::CHECKBOX,
		        'default'     => 'on'
	        ),
	        'ml_categories_articles_icon_background_color'          => array(	  // TODO REMOVE LATER
		        'label'       => __( 'Icon Background Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_icon_background_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#e9f6ff'
	        ),
	        'ml_categories_articles_top_category_icon_bg_color_toggle'   => array(
		        'label'       => __( 'Show Icon Background Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_top_category_icon_bg_color_toggle',
		        'type'        => EPKB_Input_Filter::CHECKBOX,
		        'default'     => 'on'
	        ),
	        'ml_categories_articles_top_category_icon_bg_color'          => array(
		        'label'       => __( 'Icon Background Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_top_category_icon_bg_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#e9f6ff'
	        ),
	        'ml_categories_articles_border_color'                   => array( // TODO Remove need to test
		        'label'       => __( 'Border Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_border_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#eaeaea'
	        ),
	        'ml_categories_articles_article_bg_color'               => array(
		        'label'       => __( 'Article Background Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_article_bg_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#ffffff'
	        ),
	        'ml_categories_articles_article_show_more_color'        => array(   // TODO: the setting used to set inline CSS but its value is defined only in specs;
		        'label'       => __( 'Show More Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_article_show_more_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#000000'
	        ),
	        'ml_categories_articles_back_button_bg_color'           => array(
		        'label'       => __( 'Back Button Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_back_button_bg_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#1e73be'
	        ),
	        'ml_categories_articles_title_html_tag'                 => array(		// TODO REMOVE LATER
		        'label'       => __( 'Category Title HTML Tag', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_title_html_tag',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'default'     => 'h2',
		        'style'       => 'small',
		        'options'     => array(
			        'div' => 'div',
			        'h1' => 'h1',
			        'h2' => 'h2',
			        'h3' => 'h3',
			        'h4' => 'h4',
			        'h5' => 'h5',
			        'h6' => 'h6',
			        'span' => 'span',
			        'p' => 'p',
		        ),
	        ),
	        'ml_categories_articles_category_title_html_tag'        => array(
		        'label'       => __( 'Category Title HTML Tag', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_category_title_html_tag',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'default'     => 'h2',
		        'style'       => 'small',
		        'options'     => array(
			        'div' => 'div',
			        'h1' => 'h1',
			        'h2' => 'h2',
			        'h3' => 'h3',
			        'h4' => 'h4',
			        'h5' => 'h5',
			        'h6' => 'h6',
			        'span' => 'span',
			        'p' => 'p',
		        ),
	        ),
	        'ml_categories_articles_collapse_categories'            => array(
		        'label'       => __( 'Collapse Categories', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_collapse_categories',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'all_expanded'  => __( 'All Expanded',   'echo-knowledge-base' ),
			        'all_collapsed' => __( 'All Collapsed',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'all_collapsed'
	        ),

			// MODULE: SIDEBAR
	        'ml_categories_articles_sidebar_toggle'                 => array(
		        'label'       => __( 'Sidebar', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_sidebar_toggle',
		        'type'        => EPKB_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),
	        'ml_categories_articles_sidebar_desktop_width'          => array(
		        'label'       => __( 'Sidebar Width (px/%)', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_sidebar_desktop_width',
		        'max'         => 3000,
		        'min'         => 5,
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 100
	        ),
	        'ml_categories_articles_sidebar_location'               => array(
		        'label'       => __( 'Sidebar Location', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_sidebar_location',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'left'   => __( 'Left',   'echo-knowledge-base' ),
			        'right'  => __( 'Right',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'right'
	        ),
	        'ml_categories_articles_sidebar_position_1'             => array(
		        'label'       => __( 'Sidebar Position 1', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_sidebar_position_1',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'popular_articles' => __( 'Popular Articles', 'echo-knowledge-base' ),
			        'newest_articles'   => __( 'Newest Articles',   'echo-knowledge-base' ),
			        'recent_articles'   => __( 'Recent Articles',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'popular_articles'
	        ),
	        'ml_categories_articles_sidebar_position_2'             => array(
		        'label'       => __( 'Sidebar Position 2', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_sidebar_position_2',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'none'              => '-----',
			        'popular_articles' => __( 'Popular Articles', 'echo-knowledge-base' ),
			        'newest_articles'   => __( 'Newest Articles',   'echo-knowledge-base' ),
			        'recent_articles'   => __( 'Recent Articles',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'newest_articles'
	        ),

	        // MODULE: SEARCH
	        'ml_search_layout'                                      => array(
		        'label'       => __( 'Design', 'echo-knowledge-base' ),
		        'name'        => 'ml_search_layout',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'classic'   => __( 'Classic Design',   'echo-knowledge-base' ),
			        'modern'    => __( 'Modern Design',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'classic'
	        ),
	        'ml_article_search_layout'                                      => array(
		        'label'       => __( 'Design', 'echo-knowledge-base' ),
		        'name'        => 'ml_article_search_layout',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'classic'   => __( 'Classic Design',   'echo-knowledge-base' ),
			        'modern'    => __( 'Modern Design',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'classic'
	        ),

	        // MODULE: ARTICLE LIST
	        /* 'ml_articles_list_layout'                               => array(    TODO use layout presets instead
		        'label'       => __( 'Layout', 'echo-knowledge-base' ),
		        'name'        => 'ml_articles_list_layout',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'classic'       => __( 'Classic Layout',   'echo-knowledge-base' ),
			        'drill-down'    => __( 'Drill Down Layout',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'classic'
	        ), */
	        'ml_articles_list_nof_articles_displayed'               => array(
		        'label'       => __( 'Number of Articles Listed', 'echo-knowledge-base' ),
		        'name'        => 'ml_articles_list_nof_articles_displayed',
		        'max'         => '200',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 5
	        ),
	        'ml_articles_list_column_1'                             => array(
		        'label'   => __( 'Articles List 1', 'echo-knowledge-base' ),
		        'name'    => 'ml_articles_list_column_1',
		        'type'    => EPKB_Input_Filter::SELECTION,
		        'options' => array(
			        'none'             => '-----',
			        'popular_articles' => __( 'Popular Articles', 'echo-knowledge-base' ),
			        'newest_articles'  => __( 'New Articles', 'echo-knowledge-base' ),
			        'recent_articles'  => __( 'Recently Updated Articles', 'echo-knowledge-base' )
		        ),
		        'default' => 'popular_articles'
	        ),
	        'ml_articles_list_column_2'                             => array(
		        'label'   => __( 'Articles List 2', 'echo-knowledge-base' ),
		        'name'    => 'ml_articles_list_column_2',
		        'type'    => EPKB_Input_Filter::SELECTION,
		        'options' => array(
			        'none'             => '-----',
			        'popular_articles' => __( 'Popular Articles', 'echo-knowledge-base' ),
			        'newest_articles'  => __( 'New Articles', 'echo-knowledge-base' ),
			        'recent_articles'  => __( 'Recently Updated Articles', 'echo-knowledge-base' )
		        ),
		        'default' => 'newest_articles'
	        ),
	        'ml_articles_list_column_3'                             => array(
		        'label'   => __( 'Articles List 3', 'echo-knowledge-base' ),
		        'name'    => 'ml_articles_list_column_3',
		        'type'    => EPKB_Input_Filter::SELECTION,
		        'options' => array(
			        'none'             => '-----',
			        'popular_articles' => __( 'Popular Articles', 'echo-knowledge-base' ),
			        'newest_articles'  => __( 'New Articles', 'echo-knowledge-base' ),
			        'recent_articles'  => __( 'Recently Updated Articles', 'echo-knowledge-base' )
		        ),
		        'default' => 'recent_articles'
	        ),
	        'ml_articles_list_popular_articles_msg'                  => array(
		        'label'       => __( 'Popular Articles Text', 'echo-knowledge-base' ),
		        'name'        => 'ml_articles_list_popular_articles_msg',
		        'max'         => '150',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => 'Popular Articles'
	        ),
	        'ml_articles_list_newest_articles_msg'                  => array(
		        'label'       => __( 'Newest Articles Text', 'echo-knowledge-base' ),
		        'name'        => 'ml_articles_list_newest_articles_msg',
		        'max'         => '150',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => 'Newest Articles'
	        ),
	        'ml_articles_list_recent_articles_msg'                  => array(
		        'label'       => __( 'Recently Updated Articles Text', 'echo-knowledge-base' ),
		        'name'        => 'ml_articles_list_recent_articles_msg',
		        'max'         => '150',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => 'Recently Updated Articles'
	        ),
	        'ml_articles_list_title_text'                           => array(
		        'label'       => __( 'Title', 'echo-knowledge-base' ),
		        'name'        => 'ml_articles_list_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => ''
	        ),

	        // MODULE: FAQs
	        /* 'ml_faqs_layout'                                        => array(    TODO use layout presets instead
		        'label'       => __( 'Layout', 'echo-knowledge-base' ),
		        'name'        => 'ml_faqs_layout',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'classic'       => __( 'Classic Layout',   'echo-knowledge-base' ),
			        'drill-down'    => __( 'Drill Down Layout',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'classic'
	        ), */
	        'ml_faqs_content_mode'                                  => array(
		        'label'       => __( 'Content Mode', 'echo-knowledge-base' ),
		        'name'        => 'ml_faqs_content_mode',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'content'    => __( 'Content', 'echo-knowledge-base' ),
			        'excerpt'    => __( 'Excerpt', 'echo-knowledge-base' )
		        ),
		        'default'     => 'content'
	        ),
	        'ml_faqs_custom_css_class'                              => array(
		        'label'       => __( 'Custom CSS class', 'echo-knowledge-base' ),
		        'name'        => 'ml_faqs_custom_css_class',
		        'max'         => '200',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_faqs_title_text'                                    => array(
		        'label'       => __( 'Title', 'echo-knowledge-base' ),
		        'name'        => 'ml_faqs_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => ''
	        ),

			// TODO: remove future
	        'ml_categories_columns'                                 => array(   // TODO: remove future; replaced with nof_columns
		        'label'       => __( 'Columns', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_columns',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        '2-col'   => __( '2 Columns',   'echo-knowledge-base' ),
			        '3-col'   => __( '3 Columns',   'echo-knowledge-base' ),
			        '4-col'   => __( '4 Columns',   'echo-knowledge-base' ),
		        ),
		        'default'     => '3-col'
	        ),
	        'ml_categories_articles_height_mode'                    => array(   // TODO: remove future; replaced with section_box_height_mode; should we change layout and CSS for Classic and Drill-Down as the section_box_height_mode has extra 'Maximum' option (not only 'Variable' and 'Minimum' - currently the 'Minimum' and 'Maximum' work in the same way)?
		        'label'       => __( 'Height Mode', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_height_mode',
		        'type'        => EPKB_Input_Filter::SELECTION,
		        'options'     => array(
			        'variable'  => __( 'Variable',   'echo-knowledge-base' ),
			        'fixed'     => __( 'Minimum Height',   'echo-knowledge-base' ),
		        ),
		        'default'     => 'variable'
	        ),
	        'ml_categories_articles_fixed_height'                   => array(   // TODO: remove future; replaced with section_body_height
		        'label'       => __( 'Height ( px )', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_fixed_height',
		        'max'         => '2000',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 514
	        ),
	        'ml_categories_articles_nof_articles_displayed'         => array(   // TODO: remove future; replaced with nof_articles_displayed;
		        'label'       => __( 'Number of Articles Listed', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_nof_articles_displayed',
		        'max'         => '200',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => 8
	        ),
	        'ml_categories_articles_icon_size'                      => array(   // TODO: remove future; replaced with section_head_category_icon_size
		        'label'       => __( 'Top Icon Size ( px )', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_icon_size',
		        'max'         => '250',
		        'min'         => '0',
		        'type'        => EPKB_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => '80'
	        ),
	        'ml_categories_articles_cat_desc_color'                 => array(   // TODO: remove future; replaced with section_head_description_font_color
		        'label'       => __( 'Category Desc Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_cat_desc_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#000000'
	        ),
	        'ml_categories_articles_article_color'                  => array(   // TODO: remove future; replaced with article_font_color and article_icon_color
		        'label'       => __( 'Article Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_article_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#1e73be'
	        ),
	        'ml_categories_articles_icon_color'                     => array(   // TODO: remove future; replaced with section_head_category_icon_color
		        'label'       => __( 'Top Icon Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_icon_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#7accef'
	        ),
	        'ml_categories_articles_top_category_title_color'       => array(   // TODO: remove future; replaced with section_head_font_color
		        'label'       => __( 'Top Category Title Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_top_category_title_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#000000'
	        ),
	        'ml_categories_articles_sub_category_color'             => array(   // TODO: remove future; replaced with section_category_font_color and section_category_icon_color
		        'label'       => __( 'Sub Category Icon / Text Color', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_sub_category_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => EPKB_Input_Filter::COLOR_HEX,
		        'default'     => '#2ca7db'
	        ),
	        'ml_categories_articles_back_button_text'               => array(
		        'label'       => __( 'Back Button Text', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_back_button_text',
		        'max'         => '150',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => __( 'Back', 'echo-knowledge-base' )
	        ),
	        'ml_categories_articles_show_more_text'                 => array(
		        'label'       => __( 'Show more', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_show_more_text',
		        'max'         => '150',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => __( 'Show more', 'echo-knowledge-base' )
	        ),
	        'ml_categories_articles_article_text'                   => array(
		        'label'       => __( 'Article', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_article_text',
		        'max'         => '150',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::TEXT,
		        'default'     => __( 'ARTICLE', 'echo-knowledge-base' )
	        ),
	        'ml_categories_articles_articles_text'                  => array(
		        'label'       => __( 'Articles', 'echo-knowledge-base' ),
		        'name'        => 'ml_categories_articles_articles_text',
		        'max'         => '150',
		        'min'         => '1',
		        'type'        => EPKB_Input_Filter::TEXT,
				'default'     => __( 'ARTICLES', 'echo-knowledge-base' )
	        ),
        );

		return $config_specification;
	}
}
