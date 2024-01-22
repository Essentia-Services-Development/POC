<?php
/**
 * PVFWOF custom fields for pdfviewer post type
 *
 * @package  pdf-viewer-for-wordpress
 */

// Control core classes for avoid errors.
if ( class_exists( 'PVFWOF' ) ) {

	$prefix = 'tnc_pvfw_pdf_viewer_fields';

	// Create a metabox.
	PVFWOF::createMetabox(
		$prefix,
		array(
			'title'     => esc_html__( 'FlipBook Settings', 'pdf-viewer-for-wordpress' ),
			'post_type' => 'pdfviewer',
		)
	);

	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => esc_html__( 'Basic Settings', 'pdf-viewer-for-wordpress' ),
			'fields' => array(
				array(
					'type'  => 'subheading',
					'title' => esc_html__( 'Basic Settings', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'         => 'file',
					'type'       => 'upload',
					'title'      => 'PDF File',
					'desc'   => esc_html__( 'Select or Upload a PDF File', 'pdf-viewer-for-wordpress' ),
					'attributes' => array(
						'required' => 'required',
					),
				),

				array(
					'id'      => 'default_scroll',
					'type'    => 'select',
					'title'   => 'Default Scrolling Mode',
					'desc'   => esc_html__( 'Wrapped mode works with page-fit zoom setting only. Zoom Setting can be controlled while inserting shortcode using the TNC FlipBook Block.', 'pdf-viewer-for-wordpress' ),
					'options' => array(
						'0' => 'Vertical',
						'1' => 'Horizontal',
						'2' => 'Wrapped',
						'3' => 'Flip',
					),
					'default' => '3',
				),

				array(
					'id'      => 'default_spread',
					'type'    => 'select',
					'title'   => 'Default Spread',
					'options' => array(
						'0' => 'None',
						'1' => 'ODD',
						'2' => 'EVEN',
					),
					'default' => '0',
				),

				array(
					'id'          => 'default-zoom',
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
					'default'     => 'page-fit',
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
					'id'		=> 'default-page-number',
					'type'		=> 'text',
					'title'		=> esc_html__('Jump to Page', 'pdf-viewer-for-wordpress'),
				),

				array(
					'id'          => 'icon-size',
					'type'        => 'select',
					'title'       => 'Choose icon Size',
					'options'     => array(
					  'small'  			=> 'Small',
					  'medium'  		=> 'Medium',
					  'large'  			=> 'Large',
					  'global' => 'Use Global Settings'
					),
					'default'     => 'global'
				  ),

				  array(	
					   'id'			 =>'select-toolbar-style',
					   'type'        => 'select',
					   'title'       => 'Select Toolbar Style',
					   'options'     => array(
						'top-full-width'  		=> 'Top Full Width',
						'bottom-full-width'     => 'Bottom Full Width',
						'top-center'  			=> 'Top Center',
						'bottom-center'  		=> 'Bottom center',
						'global' => 'Use Global Settings'
						),
					   'default'     => 'global'
				  ),

				 array(
					'id'          => 'language',
					'type'        => 'select',
					'title'       => 'Viewer Language',
					'placeholder' => 'Select Language',
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
					'default'     => 'en-US',
				),

				array(
					'id'       => 'return-link',
					'type'     => 'text',
					'title'    => esc_html__( 'Return to Site Link', 'pdf-viewer-for-wordpress' ),
					'desc' => esc_html__( 'Enter the url where the Return to site button on bottom right should link to. Keeping blank will use the previous page link.', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'       => 'default-return-text',
					'type'     => 'text',
					'title'    => esc_html__( 'Return to Site Link Text', 'pdf-viewer-for-wordpress' ),
					'desc' => esc_html__( 'Return to site link that appears on bottom right corner of fullscreen viewer', 'pdf-viewer-for-wordpress' ),
				),
			),
		)
	);

	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Toolbar Elements',
			'fields' => array(
				array(
					'type'    => 'subheading',
					'content' => 'Want to use Global Settings?',
				),

				array(
					'id'      => 'toolbar-elements-use-global-settings',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Use Global Settings', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'type'    => 'subheading',
					'content' => esc_html__( 'Toolbar Elements Visibility', 'pdf-viewer-for-wordpress' ),
				),

				array(
					'id'         => 'download',
					'type'       => 'switcher',
					'title'      => 'Download',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'print',
					'type'       => 'switcher',
					'title'      => 'Print',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'fullscreen',
					'type'       => 'switcher',
					'title'      => 'Fullscreen',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'zoom',
					'type'       => 'switcher',
					'title'      => 'Zoom',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'open',
					'type'       => 'switcher',
					'title'      => 'Open',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'pagenav',
					'type'       => 'switcher',
					'title'      => 'Pagenav',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'find',
					'type'       => 'switcher',
					'title'      => 'Find',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'current_view',
					'type'       => 'switcher',
					'title'      => 'Current View',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'share',
					'type'       => 'switcher',
					'title'      => 'Share',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'toggle_left',
					'type'       => 'switcher',
					'title'      => 'Toggle Left Menu',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'toggle_menu',
					'type'       => 'switcher',
					'title'      => 'Toggle Right Menu',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'rotate',
					'type'       => 'switcher',
					'title'      => 'Rotate',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'logo',
					'type'       => 'switcher',
					'title'      => 'Logo',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'handtool',
					'type'       => 'switcher',
					'title'      => 'Handtool',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'scroll',
					'type'       => 'switcher',
					'title'      => 'Scroll',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'doc_prop',
					'type'       => 'switcher',
					'title'      => 'Document Properties',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
				array(
					'id'         => 'spread',
					'type'       => 'switcher',
					'title'      => 'Spread',
					'default'    => true,
					'dependency' => array( 'toolbar-elements-use-global-settings', '==', false ),
				),
			),
		)
	);

	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Appearance',
			'fields' => array(

				array(
					'type'    => 'subheading',
					'content' => 'Want to use Global Settings?',
				),
				array(
					'id'      => 'appearance-use-global-settings',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Use Global Settings', 'pdf-viewer-for-wordpress' ),
					'default' => true,
				),

				array(
					'type'    => 'subheading',
					'content' => 'Customize the look of your FlipBooks here',
				),

				array(
					'id'    => 'appearance-disable-flip-sound',
					'type'  => 'switcher',
					'title' => esc_html__( 'Disable Flip Sound', 'pdf-viewer-for-wordpress' ),
					'text_on'    => 'Yes',
					'text_off'   => 'No',
					'dependency'  => array( 'appearance-use-global-settings', '==', false ),
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
					'dependency'  => array( 'appearance-use-global-settings', '==', false ),
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
					'dependency'  => array( 'appearance-select-type|appearance-use-global-settings', '==|==', 'select-theme|false' ),
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
					'dependency' => array( 'appearance-select-type|appearance-use-global-settings', '==|==', 'custom-color|false' ),
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
					'dependency'  => array( 'appearance-select-type|appearance-use-global-settings', '==|==', 'custom-color|false' ),
				),

				array(
					'id'           => 'default-logo',
					'type'         => 'media',
					'title'        => esc_html__( 'Logo', 'pdf-viewer-for-wordpress' ),
					'desc'     => esc_html__( 'Logo that appears on top right corner of viewer page', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'appearance-use-global-settings', '==', false ),
					'library'      => 'image',
					'placeholder'  => 'https://',
					'button_title' => 'Upload Logo',
					'remove_title' => 'Remove Logo',
				),

				array(
					'id'           => 'default-favicon',
					'type'         => 'media',
					'title'        => esc_html__( 'Favicon', 'pdf-viewer-for-wordpress' ),
					'desc'     => esc_html__( 'Favicon for viewer pages.', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'appearance-use-global-settings', '==', false ),
					'library'      => 'image',
					'placeholder'  => 'https://',
					'button_title' => 'Upload Favicon',
					'remove_title' => 'Remove Favicon',
				),
				
				array(
					'id'      => 'default-viewer-bg-image-settings',
					'type'    => 'switcher',
					'title'   => esc_html__( 'Use Background Image', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'appearance-use-global-settings', '==', false ),
					'default' => false,
				),

				array(
					'id'           => 'default-bg-img',
					'type'         => 'media',
					'title'        => esc_html__( 'Background Image', 'pdf-viewer-for-wordpress' ),
					'desc'     => esc_html__( 'Background Image for viewer pages.', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'appearance-use-global-settings|default-viewer-bg-image-settings', '==|==', 'false|true' ),
					'library'      => 'image',
					'placeholder'  => 'https://',
					'button_title' => 'Upload Image',
					'remove_title' => 'Remove Image',
				),

				array(
					'id'           => 'default-bg-img-size',
					'type'        => 'select',
					'title'        => esc_html__( 'Background Size', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'appearance-use-global-settings|default-viewer-bg-image-settings', '==|==', 'false|true' ),
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
					'id'           => 'default-bg-img-repeat',
					'type'        => 'select',
					'title'        => esc_html__( 'Background Repeat', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'appearance-use-global-settings|default-viewer-bg-image-settings', '==|==', 'false|true' ),
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
					'id'           => 'default-bg-img-attachment',
					'type'        => 'select',
					'title'        => esc_html__( 'Background Attachment', 'pdf-viewer-for-wordpress' ),
					'dependency'  => array( 'appearance-use-global-settings|default-viewer-bg-image-settings', '==|==', 'false|true' ),
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
					'id'           => 'default-bg-img-position',
					'type'        => 'text',
					'title'        => esc_html__( 'Background Position', 'pdf-viewer-for-wordpress' ),
					'desc'					=> esc_html__( 'Use the x and y keywords to specify the horizontal and vertical position separately, like this: center center or left 20px or 50% top or right 75% etc.' ),
					'dependency'  => array( 'appearance-use-global-settings|default-viewer-bg-image-settings', '==|==', 'false|true' ),
					'placeholder' => 'center center',
					'default'     => 'center center',
				),
			),
		)
	);

	PVFWOF::createSection(
		$prefix,
		array(
			'title'  => 'Privacy/Security',
			'fields' => array(
				array(
					'type'    => 'subheading',
					'content' => 'Need to protect PDF file access to specific pdf files?',
				),

				array(
					'type'    => 'content',
					'content' => '<a href="https://codecanyon.net/item/wp-file-access-manager/26430349" target="_blank">WP File Access Manager</a> can help you to protect each and every pdf files on your website. You can set permissions for each pdf files (as well as any other file type) by user, user role, user login status. Its also compatible with WooCommerce and Paid Memberships Pro plugins.',
				),

				array(
					'type'    => 'content',
					'content' => 'Note: If you\'re using nginx web server, you need to be able to add a rule to your nginx config, otherwise WP File Access Manager won\'t be able to work.',
				),

				array(
					'type'    => 'content',
					'content' => '<a class="button button-primary" href="https://codecanyon.net/item/wp-file-access-manager/26430349" target="_blank">Get WP File Access Manager now!</a>',
				),

				array(
					'type'    => 'subheading',
					'content' => 'Customize Messages Displayed',
				),

				array(
					'type'    => 'content',
					'content' => 'Following settings are only valid when you have WP File Access Manager installed and activated.',
				),

				array(
					'id'         => 'wfam-error-heading',
					'type'       => 'text',
					'title'      => esc_html__( 'Error Heading', 'pdf-viewer-for-wordpress' ),
					'attributes' => array(
						'placeholder' => esc_html__( 'SORRY', 'pdf-viewer-for-wordpress' ),
					),
				),

				array(
					'id'         => 'wfam-error-content',
					'type'       => 'textarea',
					'title'      => esc_html__( 'Error Content', 'pdf-viewer-for-wordpress' ),
					'attributes' => array(
						'placeholder' => esc_html__( 'You do not have permission to view this file, please contact us if you think this was by a mistake.', 'pdf-viewer-for-wordpress' ),
					),
				),

				array(
					'id'         => 'wfam-error-btn-text',
					'type'       => 'text',
					'title'      => esc_html__( 'Error Button Text', 'pdf-viewer-for-wordpress' ),
					'attributes' => array(
						'placeholder' => esc_html__( 'Go To Homepage', 'pdf-viewer-for-wordpress' ),
					),
				),

				array(
					'id'         => 'wfam-error-btn-url',
					'type'       => 'text',
					'title'      => esc_html__( 'Error Button URL', 'pdf-viewer-for-wordpress' ),
					'attributes' => array(
						'placeholder' => home_url(),
					),
				),
			),
		)
	);

	// Create a shortcoder
	PVFWOF::createShortcoder( $prefix, array(
		'button_title' => 'Add Shortcode',
	) );
}



/**
 *     Wp file acess manager and previewe addon 
 */

 function wpfam_preview_addon_function(){
    $wpfile_url = "https://codecanyon.net/item/wp-file-access-manager/26430349";
    $wpfile_image = plugin_dir_url(__FILE__).'../images/wpfile-pdf.png';
    $preview_url  = "https://portal.themencode.com/downloads/preview-pdf-viewer-for-wordpress-addon/";
    $preview_image = plugin_dir_url(__FILE__).'../images/Preview-Icon.png';


	?>
			<div class="addon-integration-wrapper privacy-sc-addon">
					<div class="addon-integration-container">
						<div class="addon-integration-grid">
						     <div class="addon-integration-item">
								 <div class="image-wrap">
									<img src="<?php echo $wpfile_image;?>" alt="">
									</div>
									<div class="item-content">
										<h3><?php _e( 'WP File Access Manager - Easy Way to Restrict WordPress Uploads', 'pdf-viewer-for-wordpress');?></h3>
										<p><?php _e( 'If you want to restrict access to your media library files by user login/role/woocommerce purchase or paid memberships pro level, this plugin is for you!', 'pdf-viewer-for-wordpress');?></p>
									</div>
									<div class="item-btn">
										<a target="_blank" href="<?php echo esc_url($wpfile_url);?>"> <?php _e( 'Get It Now', 'pdf-viewer-for-wordpress');?> </a>
								   </div>
								</div>
								<div class="addon-integration-item">
									<div class="image-wrap">
										<img src="<?php echo $preview_image; ?>" alt="">
										</div>
										<div class="item-content">
											<h3> <?php _e( 'Preview – TNC FlipBook – PDF viewer for WordPress Addon', 'pdf-viewer-for-wordpress');?></h3>
											<p> <?php _e( "This addon, you can select specific pages of a PDF file and set restrictions for viewers. Restricted viewers will only see a partial view of those selected pages.", "pdf-viewer-for-wordpress");?> </p>
										</div>
										<div class="item-btn">
											<a target="_blank" href="<?php  echo esc_url($preview_url);?>"> <?php _e( 'Get It Now', 'pdf-viewer-for-wordpress');?></a>
										</div>
								</div>
							</div>
						</div>
					</div>
				
   <?php  
 }



