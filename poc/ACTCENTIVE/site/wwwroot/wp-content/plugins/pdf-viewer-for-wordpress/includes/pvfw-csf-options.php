<?php
/**
 * PDF Viewer Global Settings Page
 *
 * All the settings are here, Powered by PVFWOF.
 *
 * @since 10.0
 *
 * @package pdf-viewer-for-wordpress
 */

// Control core classes for avoid errors.
if ( class_exists( 'PVFWOF' ) ) {
	$prefix = 'pvfw_csf_options';

	// Create options.
	PVFWOF::createOptions(
		$prefix,
		array(
			'framework_title' => 'TNC FlipBook - PDF viewer for WordPress <small>by <a href="https://themencode.com" target="_blank" style="color: #fff;">ThemeNcode</a></small>',
			'framework_class' => '',

			'menu_title'      => 'Global Settings',
			'menu_slug'       => 'pdf-viewer-options',
			'menu_type'       => 'submenu',
			'menu_parent'     => 'edit.php?post_type=pdfviewer',
			'show_bar_menu'   => false,
			'footer_text'     => 'Love TNC FlipBook - PDF viewer for WordPress? Please <a href="https://codecanyon.net/item/pdf-viewer-for-wordpress/8182815" target="_blank">click here</a> to support us with your valuable review on CodeCanyon.',
			'footer_after'    => '',
			'footer_credit'   => 'Thank you for using TNC FlipBook - PDF viewer for WordPress',
		)
	);

	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Welcome',
			'fields' => array(
				array (
					'type'     => 'callback',
					'function' => 'tnc_pvfw_dashboard_design_markup',
				  ),
				array(
					'type'    => 'subheading',
					'content' => esc_html__( 'Latest updates from ThemeNcode', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'     => 'callback',
					'function' => 'themencode_news_updates_callback',
				),

				array(
					'type'    => 'subheading',
					'content' => esc_html__( 'Sign up for ThemeNcode NewsLetter', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'    => 'content',
					'content' => 'It\'s a real honor for us to get you signed up for our newsletter. We only send periodic notifications about important changes and new products we release. We do not share your email with anyone else & we never spam.',
				),
				array(
					'type'    => 'content',
					'content' => '
        <iframe src="http://eepurl.com/hx1A6H" width="100%" height="700"></iframe>
        ',
				),

			),
		)
	);

	// Create a section.
	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Automatic Display',
			'fields' => array(

				array(
					'type'    => 'heading',
					'content' => esc_html__( 'Automatic Display', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'    => 'content',
					'content' => esc_html__( 'You can set options here to have all of your current .pdf links to either open or embed as a FlipBook.', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'          => 'select-automatic-display',
					'type'        => 'select',
					'title'       => 'Automatic Display',
					'desc'    => esc_html__( 'If you want to convert all current .pdf links to open as a FlipBook, select any of the options.', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'auto-iframe' => 'Automatic iFrame/Embed',
						'auto-link'   => 'Automatic Link',
					),
				),

				array(
					'type'       => 'subheading',
					'content'    => esc_html__( 'All links ending in .pdf will be replaced with Embedded FlipBook automatically', 'pdf-viewer-for-wordpress' ),
					'dependency' => array(
						'select-automatic-display',
						'==',
						'auto-iframe',
					),
				),

				array(
					'type'       => 'subheading',
					'content'    => esc_html__( 'All links ending in .pdf will open with FlipBook automatically', 'pdf-viewer-for-wordpress' ),
					'dependency' => array(
						'select-automatic-display',
						'==',
						'auto-link',
					),
				),

				array(
					'id'          => 'select-automatic-link-target',
					'type'        => 'select',
					'title'       => esc_html__( 'Link Target', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'_parent' => 'Same Tab',
						'_blank'  => 'New Tab',
					),
					'dependency'  => array(
						'select-automatic-display',
						'==',
						'auto-link',
					),
				),

				array(
					'id'         => 'select-automatic-iframe-width',
					'type'       => 'text',
					'title'      => esc_html__( 'Automatic iFrame Width', 'pdf-viewer-for-wordpress' ),
					'default'    => '100%',
					'dependency' => array(
						'select-automatic-display',
						'==',
						'auto-iframe',
					),
				),

				array(
					'id'         => 'select-automatic-iframe-height',
					'type'       => 'text',
					'title'      => esc_html__( 'Automatic iFrame Height', 'pdf-viewer-for-wordpress' ),
					'default'    => '800',
					'dependency' => array(
						'select-automatic-display',
						'==',
						'auto-iframe',
					),
				),

			),
		)
	);

	// Create a section.
	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'General Settings',
			'fields' => array(

				array(
					'type'    => 'heading',
					'content' => esc_html__( 'General Settings', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'    => 'content',
					'content' => esc_html__( 'There are some common settings related to the FlipBook, set all the options accordingly.', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'           => 'general-logo',
					'type'         => 'media',
					'title'        => esc_html__( 'Logo', 'pdf-viewer-for-wordpress' ),
					'desc'         => esc_html__( 'Logo that appears on top right corner of the FlipBook', 'pdf-viewer-for-wordpress' ),
					'library'      => 'image',
					'placeholder'  => 'https://',
					'button_title' => 'Upload Logo',
					'remove_title' => 'Remove Logo',
				),

				array(
					'id'           => 'general-favicon',
					'type'         => 'media',
					'title'        => esc_html__( 'Favicon', 'pdf-viewer-for-wordpress' ),
					'desc'     => esc_html__( 'Favicon for FlipBook.', 'pdf-viewer-for-wordpress' ),
					'library'      => 'image',
					'placeholder'  => 'https://',
					'button_title' => 'Upload Favicon',
					'remove_title' => 'Remove Favicon',
				),

				array(
					'id'       => 'general-fullscreen-text',
					'type'     => 'text',
					'title'    => esc_html__( 'FullScreen Link Text', 'pdf-viewer-for-wordpress' ),
					'desc' => esc_html__( 'Only applicable for iframe shortcode, the link that appears just above iframe', 'pdf-viewer-for-wordpress' ),
					'default'  => 'Fullscreen Mode',
				),

				array(
					'id'       => 'general-return-text',
					'type'     => 'text',
					'title'    => esc_html__( 'Return to Site Link Text', 'pdf-viewer-for-wordpress' ),
					'desc' => esc_html__( 'Return to site link that appears on the bottom right corner of the FlipBook in FullScreen mode', 'pdf-viewer-for-wordpress' ),
					'default'  => 'Return to Site',
				),

				array(
					'id'       => 'general-analytics-id',
					'type'     => 'text',
					'title'    => esc_html__( 'Google Analytics ID (GA4)', 'pdf-viewer-for-wordpress' ),
					'desc' => esc_html__( 'Example: G-XXXXXXXX', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'       => 'general-mobile-iframe-height',
					'type'     => 'text',
					'title'    => esc_html__( 'Mobile iFrame Height (Under 800px screen size)', 'pdf-viewer-for-wordpress' ),
					'desc' => esc_html__( 'Height of iFrame on smaller screens.', 'pdf-viewer-for-wordpress' ),
					'default'  => '400px',
				),

				array(
					'id'    => 'general-iframe-responsive-fix',
					'type'  => 'switcher',
					'title' => esc_html__( 'iFrame Responsive Fix', 'pdf-viewer-for-wordpress' ),
				),

			),
		)
	);

	// Create a section.
	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Appearance',
			'fields' => array(

				array(
					'type'    => 'heading',
					'content' => 'Customize the look of your FlipBook here',
				),

				array(
					'id'    => 'appearance-disable-flip-sound',
					'type'  => 'switcher',
					'title' => esc_html__( 'Disable Flip Sound', 'pdf-viewer-for-wordpress' ),
					'text_on'    => 'Yes',
					'text_off'   => 'No',
				),

				array(
					'id'          => 'appearance-select-type',
					'type'        => 'select',
					'title'       => esc_html__( 'Do you want to use a Theme or use custom colors?', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'select-theme' => 'Theme',
						'custom-color' => 'Custom Color (Defined Below)',
					),
					'default'     => 'select-theme',
				),

				array(
					'id'          => 'appearance-select-theme',
					'type'        => 'select',
					'title'       => esc_html__( 'Select Theme', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'aqua-white'    => 'Aqua White',
						'material-blue' => 'Material Blue',
						'midnight-calm' => 'Midnight Calm',
            'smart-red' => 'Smart Red',
						'louis-purple' => 'Louis Purple',
            'sea-green' => 'Sea Green',
					),
					'default'     => 'midnight-calm',
					'dependency'  => array(
						'appearance-select-type',
						'==',
						'select-theme',
					),
				),

				array(
					'id'         => 'appearance-select-colors',
					'type'       => 'color_group',
					'title'      => 'Select Colors',
					'options'    => array(
						'primary-color'   => 'Primary Color',
						'secondary-color' => 'Secondary Color',
						'text-color'      => 'Text Color',
					),
					'dependency' => array(
						'appearance-select-type',
						'==',
						'custom-color',
					),
				),

				array(
					'id'          => 'appearance-select-icon',
					'type'        => 'select',
					'title'       => esc_html__( 'Icon Style', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'dark-icons'  => 'Dark',
						'light-icons' => 'Light',
					),
					'dependency'  => array(
						'appearance-select-type',
						'==',
						'custom-color',
					),
				),

				array(
					'id'      => 'general-bg-image-settings',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Use Background Image', 'pdf-viewer-for-wordpress' ),
					'default' => false,
				),

				array(
					'id'           => 'general-bg-img',
					'type'         => 'media',
					'title'        => esc_html__( 'Background Image', 'pdf-viewer-for-wordpress' ),
					'desc'     => esc_html__( 'Background Image for FlipBook.', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'general-bg-image-settings', '==', true ),
					'library'      => 'image',
					'placeholder'  => 'https://',
					'button_title' => 'Upload Image',
					'remove_title' => 'Remove Image',
				),

				array(
					'id'           => 'general-bg-img-size',
					'type'        => 'select',
					'title'        => esc_html__( 'Background Size', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'general-bg-image-settings', '==', true ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'auto'  => 'auto',
						'cover' => 'cover',
						'contain' => 'contain',
						'initial' => 'initial',
						'inherit' => 'inherit',
						'revert' => 'revert',
						'revert-layer' => 'revert-layer',
						'unset' => 'unset',
					),
					'default'     => 'cover',
				),

				array(
					'id'           => 'general-bg-img-repeat',
					'type'        => 'select',
					'title'        => esc_html__( 'Background Repeat', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'general-bg-image-settings', '==', true ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'repeat'  => 'repeat',
						'no-repeat'  => 'no-repeat',
						'repeat-x' => 'repeat-x',
						'repeat-y' => 'repeat-y',
						'initial' => 'initial',
						'inherit' => 'inherit',
						'space' => 'space',
						'round' => 'round',
						'revert' => 'revert',
						'revert-layer' => 'revert-layer',
						'unset' => 'unset',
					),
					'default'     => 'no-repeat',
				),

				array(
					'id'           => 'general-bg-img-attachment',
					'type'        => 'select',
					'title'        => esc_html__( 'Background Attachment', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'general-bg-image-settings', '==', true ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'scroll'  => 'scroll',
						'fixed' => 'fixed',
						'local' => 'local',
						'initial' => 'initial',
						'inherit' => 'inherit',
						'revert' => 'revert',
						'revert-layer' => 'revert-layer',
						'unset' => 'unset',
					),
					'default'     => 'scroll',
				),

				array(
					'id'           => 'general-bg-img-position',
					'type'        => 'text',
					'title'        => esc_html__( 'Background Position', 'pdf-viewer-for-wordpress' ),
					'desc'					=> esc_html__( 'Use the x and y keywords to specify the horizontal and vertical position separately, like this:: center center or left 20px or 50% top or right 75% etc.' ),
					'dependency'  => array( 'general-bg-image-settings', '==', true ),
					'placeholder' => 'center center',
					'default'     => 'center center',
				),
				
				array(
					'id'          => 'appearance-icon-size',
					'type'        => 'select',
					'title'       => 'Choose icon Size',
					'options'     => array (
					  'small'  			=> 'Small',
					  'medium'  		=> 'Medium',
					  'large'  			=> 'Large',
					),
					'default'     => 'medium'
				  ),

				  array(	
					   'id'			 =>'appearance-select-toolbar-style',
					   'type'        => 'select',
					   'title'       => 'Select Toolbar Style',
					   'options'     => array(
						'top-full-width'  		=> 'Top Full Width',
						'bottom-full-width'     => 'Bottom Full Width',
						'top-center'  			=> 'Top Center',
						'bottom-center'  		=> 'Bottom center',
						),
					   'default'     => 'top-full-width'
				  ),

			),
		)
	);

	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Toolbar',
			'fields' => array(

				array(
					'type'    => 'heading',
					'content' => esc_html__( 'Toolbar Global Defaults', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'    => 'subheading',
					'content' => esc_html__( 'Specify Global Default setting here. Options in this section only applies to Automatic Link and iFrame options', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'          => 'toolbar-default-scroll',
					'type'        => 'select',
					'title'       => esc_html__( 'Default Scroll', 'pdf-viewer-for-wordpress' ),
					'desc'   => esc_html__( 'Wrapped mode works with page-fit zoom setting only. Zoom Setting can be controlled while inserting shortcode using the TNC FlipBook Block.', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'0' => 'Vertical Scrolling',
						'1' => 'Horizontal Scrolling',
						'2' => 'Wrapped Scrolling',
						'3' => 'Flip',
					),
				),

				array(
					'id'          => 'toolbar-default-spread',
					'type'        => 'select',
					'title'       => esc_html__( 'Default Spread', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'0' => 'No Spreads',
						'1' => 'Odd Spreads',
						'2' => 'Even Spreads',
					),
				),

				array(
					'id'          => 'toolbar-default-zoom',
					'type'        => 'select',
					'title'       => 'Default Zoom',
					'placeholder' => 'Select Default Zoom',
					'options'     => array(
						'auto'        => 'Auto',
						'page-fit'    => 'Page Fit',
						'page-width'  => 'Page Width',
						'page-height' => 'Page Height',
						'75'          => '75%',
						'100'         => '100%',
						'150'         => '150%',
						'200'         => '200%',
					),
					'default'     => 'auto',
				),

				array(
					'id'          => 'toolbar-viewer-language',
					'type'        => 'select',
					'title'       => esc_html__( 'FlipBook Language', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select an option',
					'options'     => array(
						'en-US' => 'en-US',
						'ach'   => 'ach',
						'af'    => 'af',
						'ak'    => 'ak',
						'an'    => 'an',
						'ar'    => 'ar',
						'as'    => 'as',
						'ast'   => 'ast',
						'az'    => 'az',
						'be'    => 'be',
						'bg'    => 'bg',
						'bn-BD' => 'bn-BD',
						'bn-IN' => 'bn-IN',
						'br'    => 'br',
						'bs'    => 'bs',
						'ca'    => 'ca',
						'cs'    => 'cs',
						'csb'   => 'csb',
						'cy'    => 'cy',
						'da'    => 'da',
						'de'    => 'de',
						'el'    => 'el',
						'en-GB' => 'en-GB',
						'en-ZA' => 'en-ZA',
						'eo'    => 'eo',
						'es-AR' => 'es-AR',
						'es-CL' => 'es-CL',
						'es-ES' => 'es-ES',
						'es-MX' => 'es-MX',
						'et'    => 'et',
						'eu'    => 'eu',
						'fa'    => 'fa',
						'ff'    => 'ff',
						'fi'    => 'fi',
						'fr'    => 'fr',
						'fy-NL' => 'fy-NL',
						'ga-IE' => 'ga-IE',
						'gd'    => 'gd',
						'gl'    => 'gl',
						'gu-IN' => 'gu-IN',
						'he'    => 'he',
						'hi-IN' => 'hi-IN',
						'hr'    => 'hr',
						'hu'    => 'hu',
						'hy-AM' => 'hy-AM',
						'id'    => 'id',
						'is'    => 'is',
						'it'    => 'it',
						'ja'    => 'ja',
						'ka'    => 'ka',
						'kk'    => 'kk',
						'km'    => 'km',
						'kn'    => 'kn',
						'ko'    => 'ko',
						'ku'    => 'ku',
						'lg'    => 'lg',
						'lij'   => 'lij',
						'lt'    => 'lt',
						'lv'    => 'lv',
						'mai'   => 'mai',
						'mk'    => 'mk',
						'ml'    => 'ml',
						'mn'    => 'mn',
						'mr'    => 'mr',
						'ms'    => 'ms',
						'my'    => 'my',
						'nb-NO' => 'nb-NO',
						'nl'    => 'nl',
						'nn-NO' => 'nn-NO',
						'nso'   => 'nso',
						'oc'    => 'oc',
						'or'    => 'or',
						'pa-IN' => 'pa-IN',
						'pl'    => 'pl',
						'pt-BR' => 'pt-BR',
						'pt-PT' => 'pt-PT',
						'rm'    => 'rm',
						'ro'    => 'ro',
						'ru'    => 'ru',
						'rw'    => 'rw',
						'sah'   => 'sah',
						'si'    => 'si',
						'sk'    => 'sk',
						'sl'    => 'sl',
						'son'   => 'son',
						'sq'    => 'sq',
						'sr'    => 'sr',
						'sv-SE' => 'sv-SE',
						'sw'    => 'sw',
						'ta'    => 'ta',
						'ta-LK' => 'ta-LK',
						'te'    => 'te',
						'th'    => 'th',
						'tl'    => 'tl',
						'tn'    => 'tn',
						'tr'    => 'tr',
						'uk'    => 'uk',
						'ur'    => 'ur',
						'vi'    => 'vi',
						'wo'    => 'wo',
						'xh'    => 'xh',
						'zh-CN' => 'zh-CN',
						'zh-TW' => 'zh-TW',
						'zu'    => 'zu',
					),
				),

				array(
                    'id'                    => 'toolbar-default-page-mode',
                    'type'                  => 'select',
                    'title'                 => esc_html__( 'Page Mode', 'pdf-viewer-for-wordpress' ),
                    'options'               => array(
                      'none'            => 'None',
                      'bookmarks'       => 'Bookmarks',
                      'thumbs'          => 'Thumbnails',
                      'attachments'     => 'Attachments',
                    ),
                    'default' => 'none',
                ),

				array(
					'type'    => 'heading',
					'content' => esc_html__( 'Toolbar Elements Visibility', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'    => 'subheading',
					'content' => esc_html__( 'Specify Global Default setting here', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'      => 'toolbar-share',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Share', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-print',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Print', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-download',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Download', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-open',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Open', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-zoom',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Zoom', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-fullscreen',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Fullscreen', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-logo',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Logo', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-find',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Find', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-pagenav',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Page Navigation', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-current-view',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Current View', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-rotate',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Rotate', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-handtool',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Handtool', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-doc-prop',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Document Properties', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-left-toggle',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Left Toggle Menu', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-right-toggle',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Right Toggle Menu', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-scroll',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Scroll Options', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'id'      => 'toolbar-spread',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Spread Options', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),
			),
		)
	);

	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Advanced',
			'fields' => array(

				array(
					'type'    => 'heading',
					'content' => esc_html__( 'Advanced Settings', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'    => 'subheading',
					'content' => esc_html__( 'Only change the first 2 settings if you know what you\'re doing or is suggested by ThemeNcode support', 'pdf-viewer-for-wordpress' ),
				),

				// Select with pages.
				array(
					'id'          => 'advanced-pdf-viewer-page',
					'type'        => 'select',
					'title'       => esc_html__( 'ThemeNcode PDF Viewer Page', 'pdf-viewer-for-wordpress' ),
					'desc'    => esc_html__( 'This should be set to the auto created page "ThemeNcode PDF Viewer [Do Not Delete]", only change if you see wrong page selected here. ', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select a page',
					'options'     => 'pages',
					'query_args'  => array(
						'posts_per_page' => - 1, // for get all pages (also it's same for posts).

					),
				),

				array(
					'id'          => 'advanced-pdf-viewer-sc-page',
					'type'        => 'select',
					'title'       => esc_html__( 'ThemeNcode PDF Viewer SC Page', 'pdf-viewer-for-wordpress' ),
					'desc'    => esc_html__( 'This should be set to the auto created page "ThemeNcode PDF Viewer SC [Do Not Delete]", only change if you see wrong page selected here. ', 'pdf-viewer-for-wordpress' ),
					'placeholder' => 'Select a page',
					'options'     => 'pages',
					'query_args'  => array(
						'posts_per_page' => - 1, // for get all pages (also it's same for posts).

					),
				),

				array(
					'id'         => 'advanced-context-menu',
					'type'       => 'switcher',
					'title'      => esc_html__( 'Context Menu/Right Click on FlipBook', 'pdf-viewer-for-wordpress' ),
					'text_on'    => 'Enabled',
					'text_off'   => 'Disabled',
					'text_width' => 100,
					'default'    => true,
				),

				array(
					'id'         => 'advanced-text-copying',
					'type'       => 'switcher',
					'title'      => esc_html__( 'Text Copying (ctrl+c) keyboard shortcut', 'pdf-viewer-for-wordpress' ),
					'text_on'    => 'Enabled',
					'text_off'   => 'Disabled',
					'text_width' => 100,
					'default'    => true,
				),

				array(
					'id'         => 'advanced-oxygen-integration',
					'type'       => 'switcher',
					'title'      => esc_html__( 'Oxygen Builder Integration', 'pdf-viewer-for-wordpress' ),
					'text_on'    => 'Enabled',
					'text_off'   => 'Disabled',
					'text_width' => 100,
					'default'    => false,
				),

			),

		)
	);

	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Custom CSS/JS',
			'fields' => array(

				array(
					'type'    => 'heading',
					'content' => esc_html__( 'Custom CSS and JS', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'type'    => 'subheading',
					'content' => esc_html__( 'The custom css and javascript code you put below will be executed on FlipBooks only.', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'       => 'custom-css',
					'type'     => 'code_editor',
					'title'    => 'Custom CSS (Only inside FlipBook)',
					'settings' => array(
						'theme' => 'mbo',
						'mode'  => 'css',
					),
				),

				array(
					'id'       => 'custom-js',
					'type'     => 'code_editor',
					'title'    => 'Custom JS (Only inside FlipBook)',
					'settings' => array(
						'theme' => 'monokai',
						'mode'  => 'javascript',
					),
					'sanitize' => false,
				),

			),
		)
	);

	
	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Export/Import',
			'fields' => array(

				array(
					'type'    => 'heading',
					'content' => 'Export/Import Settings',
				),
				array(
					'type'    => 'subheading',
					'content' => 'Take backup of all the global settings and import if needed.',
				),

				array(
					'type' => 'backup',
				),
			),
		)
	);
}


/**
 *    welcome dashboard
 */

 function tnc_pvfw_dashboard_design_markup() {

	$welcome_titile =  __( 'Welcome to TNC FlipBook - PDF viewer for WordPress', 'pdf-viewer-for-wordpress');
	$welcome_desc   =  __( "Thank you for using TNC FlipBook - PDF viewer for WordPress. On this page we'll give you some quick details that you may need.", "pdf-viewer-for-wordpress");  
	$installation_title  =  __('Installation ', 'pdf-viewer-for-wordpress');  
	$free_title =   __('Available features in TNC FlipBook - PDF viewer for WordPress ', 'pdf-viewer-for-wordpress'); 
	$flipbook_mode = __( 'Flipbook Mode', 'pdf-viewer-for-wordpress');
	$global_settings = __('Global Settings', 'pdf-viewer-for-wordpress');
	$jump_to_page  =   __('Jump to page', 'pdf-viewer-for-wordpress');
	$choose_icon_size =  __('Choose icon Size', 'pdf-viewer-for-wordpress');
	$select_toolbar_style =  __('Select Toolbar Style', 'pdf-viewer-for-wordpress');
	$available_themes =   __('Various Available Themes', 'pdf-viewer-for-wordpress');
	$automatic_display =   __('Automatic Display', 'pdf-viewer-for-wordpress'); 
	$custom_css         = __('Custom CSS', 'pdf-viewer-for-wordpress');  
	$custom_js          =  __('Custom JS', 'pdf-viewer-for-wordpress');  
	$disable_right_click  =  __('Disable Right Click', 'pdf-viewer-for-wordpress');  
	$lots_of_toolbar      = __('Lots of Toolbar Options', 'pdf-viewer-for-wordpress'); 
	$add_unlimitedP_pdf =  __('Add Unlimited PDFs ', 'pdf-viewer-for-wordpress');  
	$vertical_srooling_mode =  __('Vertical Scrolling Mode', 'pdf-viewer-for-wordpress');  
	$horizental_scrooling_mode =  __('Horizontal Scrolling Mode', 'pdf-viewer-for-wordpress');
	$wrapped_scrolling_mode   = __('Wrapped Scrolling Mode', 'pdf-viewer-for-wordpress');
	$various_zoom_option     =  __('Various Zoom Options', 'pdf-viewer-for-wordpress');
	$even_odd_page_spread =  __('Even and Odd Page Spread ', 'pdf-viewer-for-wordpress');
	$languages_100        = __('100+ Languages ', 'pdf-viewer-for-wordpress');
	$logo_on_viewer_page  =  __('Logo on Viewer Page', 'pdf-viewer-for-wordpress');
	$favicon_viewer_page  =  __('Favicon on Viewer Page', 'pdf-viewer-for-wordpress');
	$import_pdf_file_form_url =  __('Import PDF Files from URL', 'pdf-viewer-for-wordpress');
	$export_import  =  __('Export/Import', 'pdf-viewer-for-wordpress');
	$privacy_security =   __('Privacy/Security', 'pdf-viewer-for-wordpress');
	$migrate_settings = __( 'Migrate Setting', 'pdf-viewer-for-wordpress');
  
	$installation_desc   = __('TNC FlipBook - PDF viewer for WordPress comes with 2 ways of using it. You can start by creating FlipBooks using <strong> TNC FlipBook > Add New</strong> Menu, then share the link anywhere for users to access.
   
	The other way is using Automatic Link/Embed option. This setup will automatically convert all of the .pdf links on your website to open with TNC FlipBook - PDF viewer for WordPress Go to <strong> TNC FlipBook > Global Settings > Automatic Display </strong> Menu to setup this feature.
	
	Here are some videos from our documentation that will help you to figure out specific parts more easily. ', 'pdf-viewer-for-wordpress'); 
  
	$usefull_title =   __('Useful Links ', 'pdf-viewer-for-wordpress'); 
	$plugin_live_demo =   __('Plugin Live Demo ', 'pdf-viewer-for-wordpress'); 
	$plugin_documentation =  __('  Plugin Documentation ', 'pdf-viewer-for-wordpress');
	$video_documentation = __('Video Documentations', 'pdf-viewer-for-wordpress');
	$support_portal   =  __('Support Portal', 'pdf-viewer-for-wordpress'); 
  
	
	$welcome_str = <<<EOD
	  <div class="pdf-viewer-greetings-wrapper">
		<div class="pdf-viewer-welcome-section">
			 <div class="pdf-viewer-welcome-container">
				  <div class="pdf-viewer-content">
					  <h2 class="welcome-title">$welcome_titile</h2>
					  <p class="welcome-desc">$welcome_desc</p>
				  </div>
			 </div>
		</div>
		 <div class="pdf-viewer-feature-section">
			 <div class="pdf-viewer-feature-container">
				<div class="pdf-viewer-fetaure-content">
					 <div class="premium-title"><h3> $free_title </h3></div>
				 </div>
				 <div class="tnc-features-wrapper"> 
					<div class="tnc-fetaures-row">
						 <div class="tnc-features-col">
								<div class="features-content free">
									<h4> <span class="dashicons dashicons-yes-alt"></span> $flipbook_mode </h4> 
								</div>  
							</div>
						  <div class="tnc-features-col">
							  <div class="features-content free">
								  <h4> <span class="dashicons dashicons-yes-alt"></span> $vertical_srooling_mode </h4> 
							  </div>  
						 </div>
						 <div class="tnc-features-col">
							  <div class="features-content free">
								  <h4> <span class="dashicons dashicons-yes-alt"></span> $horizental_scrooling_mode </h4> 
							  </div>  
						 </div>
						 <div class="tnc-features-col">
							  <div class="features-content free">
								  <h4> <span class="dashicons dashicons-yes-alt"></span> $wrapped_scrolling_mode </h4> 
							  </div>  
						 </div>
						<div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $global_settings </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $jump_to_page  </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $choose_icon_size </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $select_toolbar_style  </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $automatic_display </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $custom_css  </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $custom_js  </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $disable_right_click </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $lots_of_toolbar  </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $add_unlimitedP_pdf </h4> 
							</div> 
						 </div>
						 <div class="tnc-features-col">
							  <div class="features-content free">
								  <h4> <span class="dashicons dashicons-yes-alt"></span> $various_zoom_option  </h4> 
							  </div>  
						 </div>
						 <div class="tnc-features-col">
							  <div class="features-content free">
								  <h4> <span class="dashicons dashicons-yes-alt"></span> $even_odd_page_spread </h4> 
							  </div>  
						 </div>
						 <div class="tnc-features-col">
							  <div class="features-content free">
								  <h4> <span class="dashicons dashicons-yes-alt"></span> $languages_100  </h4> 
							  </div>  
						 </div>
						 <div class="tnc-features-col">
						  <div class="features-content free">
							  <h4> <span class="dashicons dashicons-yes-alt"></span> $available_themes </h4> 
						  </div>  
						</div>
						<div class="tnc-features-col">
						  <div class="features-content free">
							  <h4> <span class="dashicons dashicons-yes-alt"></span> $logo_on_viewer_page  </h4> 
						  </div>  
						</div>
						<div class="tnc-features-col">
						  <div class="features-content free">
							  <h4> <span class="dashicons dashicons-yes-alt"></span> $favicon_viewer_page </h4> 
						  </div>  
						</div>
						<div class="tnc-features-col">
						  <div class="features-content free">
							  <h4> <span class="dashicons dashicons-yes-alt"></span> $import_pdf_file_form_url </h4> 
						  </div>  
						</div>
						  <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $export_import </h4> 
							</div>  
						  </div>
						  <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $migrate_settings </h4> 
							</div>  
						  </div>
						  <div class="tnc-features-col">
							<div class="features-content free">
								<h4> <span class="dashicons dashicons-yes-alt"></span> $privacy_security </h4> 
							</div>  
						  </div>
					  </div>
				 </div>
			</div>
		</div>
		<div class="tnc-installtion-secion">
			  <div class="tnc-installation-container">
				  <div class="tnc-installation-content">
					   <h3>$installation_title</h3>
					   <p> $installation_desc </p>
				  </div>
				  <div class="tnc-installtion-video">
				  <iframe width="100%" height=500" src="https://www.youtube-nocookie.com/embed/videoseries?list=PL0BHfncpP5oSkv9_LfgeXoElT_xhhEla1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
				  </div>
			  </div>
		</div>
		<div class="tnc-usefull-section">
			  <div class="tnc-usefull-section">
				<div class="tnc-usefull-container">
					  <div class="tnc-usefull-content">
							<h3> $usefull_title </h3>
					  </div>
					  <div  class="tnc-usefull-grid">
							<a target="_blank" href="https://themencode.com/tncflipbook-preview/">
							  <div class="tnc-usefull-col">
								  <div class="usefull-icon">
									  <span class="dashicons dashicons-desktop"></span>
								  </div>
								  <div class="usefull-titile">
									  <h4>$plugin_live_demo</h4>
								  </div>
							  </div>
						   </a>
						 <a target="_blank" href="https://docs.themencode.com/docs/pdf-viewer-for-wordpress/">  
							<div class="tnc-usefull-col">
								<div class="usefull-icon">
									  <span class="dashicons dashicons-welcome-write-blog"></span>
								</div>
								<div class="usefull-titile">
									<h4>$plugin_documentation</h4>
								</div>
							 </div>
						</a>
						<a target="_blank" href="https://youtu.be/zLmHpjYO9z4">
						  <div class="tnc-usefull-col">
							  <div class="usefull-icon">
								  <span class="dashicons dashicons-format-video"></span>
							  </div>
							  <div class="usefull-titile">
								  <h4> $video_documentation</h4>
							  </div>
						  </div>
					   </a>
					   <a target="_blank" href="https://themencode.support-hub.io/">
						  <div class="tnc-usefull-col">
								<div class="usefull-icon">
									<span class="dashicons dashicons-groups"></span>
								</div>
								<div class="usefull-titile">
									<h4>$support_portal</h4>
								</div>
							</div>
						  </a>
						</div>  
				  </div>
			  </div>
		</div>
	  </div>
	EOD;
  
	 echo  $welcome_str;
  }
  