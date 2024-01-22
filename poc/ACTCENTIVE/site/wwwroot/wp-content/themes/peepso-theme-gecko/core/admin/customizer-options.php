<?php

//  Exit if accessed directly
if (!defined('ABSPATH')) {
	exit();
}

if (!class_exists('Gecko_Customizer_Options')) {
	/**
	 * Gecko_Customizer_Options class.
	 *
	 * @since 3.0.0.0
	 */
	class Gecko_Customizer_Options {
		private $settings;
		private $options_settings;
		private $options_css_vars;

		private static $instance = null;

		public static function get_instance() {
			if (null === self::$instance) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Default constructor.
		 *
		 * @since 3.0.0.0
		 */
		private function __construct() {
			$this->settings = GeckoConfigSettings::get_instance();
			$this->options_settings = [];
			$this->options_css_vars = [];

			// Flatten option listing to be used internally for a faster query.
			$options = $this->list();
			foreach ($options as $cat) {
				foreach ($cat['options'] as $subcat) {
					foreach ($subcat['settings'] as $option) {
						if (isset($option['setting']) && $option['setting']) {
							$this->options_settings[$option['setting']] = $option;
						} elseif (isset($option['var']) && $option['var']) {
							$this->options_css_vars[$option['var']] = $option;
						}
					}
				}
			}
		}

		/**
		 * Get all options.
		 *
		 * @since 3.0.0.0
		 *
		 * @return array
		 */
		public function list() {
			$options = [];

			/**
			 * Allow plugins to modify available font listing.
			 *
			 * @since 3.1.1.0
			 */
			$font_options = apply_filters('gecko_fonts', [
				'Sora' => 'Default (Sora)',
				'Maven Pro' => 'Maven Pro',
				'Mulish' => 'Mulish',
				'Quicksand' => 'Quicksand',
				'Comfortaa' => 'Comfortaa',
				'Exo' => 'Exo',
				'Manrope' => 'Manrope',
				'Varta' => 'Varta',
				'Roboto' => 'Roboto',
				'Roboto Condensed' => 'Roboto Condensed',
				'Roboto Slab' => 'Roboto Slab',
				'Roboto Mono' => 'Roboto Mono',
				'Open Sans' => 'Open Sans',
				'Lato' => 'Lato',
				'Baloo 2' => 'Baloo 2',
				'Kufam' => 'Kufam',
				'Merriweather' => 'Merriweather',
				'Ubuntu' => 'Ubuntu',
				'Epilogue' => 'Epilogue',
				'Work Sans' => 'Work Sans',
				'Mukta' => 'Mukta',
				'Rubik' => 'Rubik',
				'Nanum Gothic' => 'Nanum Gothic',
				'Oxygen' => 'Oxygen',
				'Dancing Script' => 'Dancing Script',
				'Abel' => 'Abel',
				'Montserrat' => 'Montserrat',
				'Source Sans Pro' => 'Source Sans Pro',
				'Oswald' => 'Oswald',
				'Lexend' => 'Lexend',
				'Poppins' => 'Poppins',
				'Noto Sans' => 'Noto Sans',
				'Noto Serif' => 'Noto Serif',
				'Fira Sans' => 'Fira Sans',
				'Titillium Web' => 'Titillium Web',
				'Averia Serif Libre' => 'Averia Serif Libre',
				'Inconsolata' => 'Inconsolata',
				'Barlow' => 'Barlow',
				'Karla' => 'Karla',
				'Libre Franklin' => 'Libre Franklin',
				'Josefin Sans' => 'Josefin Sans',
				'Barlow Condensed' => 'Barlow Condensed',
				'Dosis' => 'Dosis',
				'Patrick Hand' => 'Patrick Hand',
				'Architects Daughter' => 'Architects Daughter',
				'Fredoka One' => 'Fredoka One',
				'Satisfy' => 'Satisfy',
			]);

			$options['Site'] = [
				'name' => __('Site', 'peepso-theme-gecko'),
				'desc' => __('Site settings.', 'peepso-theme-gecko'),
				'tags' => __('Logo, fonts, typography.', 'peepso-theme-gecko'),
				'id' => 'site',
				'icon' => 'gcis gci-cog',
				'options' => [
					'Logo' => [
						'title' => __('Logo', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-image',
						'desc' => __('Select Logo.', 'peepso-theme-gecko'),
						'settings' => [
							'site-logo-image' => [
								'name' => __('Logo image', 'peepso-theme-gecko'),
								'id' => 'site-logo-image',
								'type' => 'category',
								'var' => false,
							],
							'site-logo' => [
								'name' => __('Site Logo', 'peepso-theme-gecko'),
								'id' => 'site-logo',
								'type' => 'image',
								'setting' => 'opt_custom_logo',
							],
							'site-mobile-logo' => [
								'name' => __('Site Mobile Logo', 'peepso-theme-gecko'),
								'id' => 'site-mobile-logo',
								'type' => 'image',
								'setting' => 'opt_custom_mobile_logo',
							],
							'site-icon' => [
								'name' => __('Site Icon (Favicon)', 'peepso-theme-gecko'),
								'id' => 'site-icon',
								'type' => 'image',
								'setting' => 'opt_custom_icon',
							],
							'site-logo-vis' => [
								'name' => __('Logo visibility', 'peepso-theme-gecko'),
								'id' => 'site-logo-vis',
								'type' => 'category',
								'var' => false,
							],
							'site-logo-vis-desktop' => [
							  'name' => __('Desktop', 'peepso-theme-gecko'),
							  'id' => 'site-logo-vis-desktop',
							  'type' => 'switch',
							  'setting' => 'opt_header_logo_desktop_vis',
							  'on' => '1',
							  'off' => '',
							],
							'site-logo-vis-mobile' => [
							  'name' => __('Mobile', 'peepso-theme-gecko'),
							  'id' => 'site-logo-vis-mobile',
							  'type' => 'switch',
							  'setting' => 'opt_header_logo_mobile_vis',
							  'on' => '1',
							  'off' => '',
							],
							'site-logo-tagline' => [
								'name' => __('Tagline', 'peepso-theme-gecko'),
								'id' => 'site-logo-tagline',
								'type' => 'category',
								'var' => false,
							],
							'site-logo-tagline-vis' => [
							  'name' => __('Show tagline next to the logo', 'peepso-theme-gecko'),
							  'id' => 'site-logo-tagline-vis',
							  'type' => 'switch',
							  'setting' => 'opt_header_tagline_vis',
							  'on' => '1',
							  'off' => '',
							],
							'site-logo-tagline-mobile-vis' => [
							  'name' => __('Show tagline on mobile', 'peepso-theme-gecko'),
							  'id' => 'site-logo-tagline-mobile-vis',
							  'type' => 'switch',
							  'setting' => 'opt_header_tagline_mobile_vis',
							  'on' => '1',
							  'off' => '',
							],
							'site-logo-tagline-font-size' => [
							  'name' => __('Tagline font-size', 'peepso-theme-gecko'),
							  'id' => 'site-logo-tagline-font-size',
								'type' => 'range',
								'var' => '--c-gc-header-tagline-font-size',
								'unit' => '%',
								'min' => '70',
								'max' => '120',
								'step' => '10',
							],
							'site-logo-other' => [
								'name' => __('Other', 'peepso-theme-gecko'),
								'id' => 'site-logo-other',
								'type' => 'category',
								'var' => false,
							],
							'site-logo-redirect' => [
								'name' => __('Logo link redirect', 'peepso-theme-gecko'),
								'id' => 'site-logo-redirect',
								'type' => 'default',
								'setting' => 'opt_logo_link_redirect',
							],
							'site-logo-height' => [
								'name' => __('Logo height', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like px or %.', 'peepso-theme-gecko'),
								'id' => 'site-logo-height',
								'type' => 'default',
								'var' => '--c-gc-header-logo-height',
							],
							'site-mobile-logo-height' => [
								'name' => __('Mobile logo height', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like px or %.', 'peepso-theme-gecko'),
								'id' => 'site-mobile-logo-height',
								'type' => 'default',
								'var' => '--c-gc-header-logo-height-mobile',
							],
						],
					],
					'Font' => [
						'title' => __('Fonts', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-font',
						'desc' => __('Choose one of the available fonts.', 'peepso-theme-gecko'),
						'settings' => [
							'google-font' => [
								'name' => __('Google fonts', 'peepso-theme-gecko'),
								'id' => 'google-font',
								'type' => 'select',
								'var' => '--GC-FONT-FAMILY',
								'options' => $font_options,
							],
						],
					],
					'Typography' => [
						'title' => __('Typography', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-heading',
						'desc' => __(
							'Here you can edit font-size or line-height.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'font-size' => [
								'name' => __('Global font-size', 'peepso-theme-gecko'),
								'id' => 'font-size',
								'type' => 'range',
								'var' => '--FONT-SIZE',
								'unit' => 'px',
								'min' => '14',
								'max' => '24',
								'step' => '1',
							],
							'line-height' => [
								'name' => __('Global line-height', 'peepso-theme-gecko'),
								'id' => 'line-height',
								'type' => 'range',
								'var' => '--LINE-HEIGHT',
								'min' => '1',
								'max' => '5',
								'step' => '0.1',
							],
						],
					],
				],
			];

			$options['global-colors'] = [
				'name' => __('Global colors', 'peepso-theme-gecko'),
				'desc' => __(
					'Here you can change global colors which may affect many elements. You can find more specific settings in different categories.',
					'peepso-theme-gecko'
				),
				'tags' => __('Base colors palette (primary colors, text, backgrounds).', 'peepso-theme-gecko'),
				'id' => 'global-colors',
				'icon' => 'gcis gci-palette',
				'options' => [
					'primary-colors' => [
						'title' => __('Primary colors', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-swatchbook',
						'desc' => __(
							'Base colors used for important elements like action buttons, alerts, progress bars or active element indicators.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-primary' => [
								'name' => __('Primary color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-primary',
								'type' => 'color',
								'var' => '--COLOR--PRIMARY',
							],

							'color-primary-shade' => [
								'name' => __('Primary shade color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-primary-shade',
								'type' => 'color',
								'var' => '--COLOR--PRIMARY--SHADE',
							],

							'color-primary-light' => [
								'name' => __('Primary light color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-primary-light',
								'type' => 'color',
								'var' => '--COLOR--PRIMARY--LIGHT',
							],

							'color-primary-ultralight' => [
								'name' => __('Primary ultralight color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-primary-ultralight',
								'type' => 'color',
								'var' => '--COLOR--PRIMARY--ULTRALIGHT',
							],

							'color-primary-dark' => [
								'name' => __('Primary dark color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-primary-dark',
								'type' => 'color',
								'var' => '--COLOR--PRIMARY--DARK',
							],
						],
					],

					'alt-colors' => [
						'title' => __('Alt colors', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-swatchbook',
						'desc' => __(
							'Alternative to primary color. Default color for community Join button, one of the Gradient colors and some small UI elements.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-alt' => [
								'name' => __('Alt color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-alt',
								'type' => 'color',
								'var' => '--COLOR--ALT',
							],

							'color-alt-light' => [
								'name' => __('Alt light color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-alt-light',
								'type' => 'color',
								'var' => '--COLOR--ALT--LIGHT',
							],

							'color-alt-dark' => [
								'name' => __('Alt dark color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-alt-dark',
								'type' => 'color',
								'var' => '--COLOR--ALT--DARK',
							],
						],
					],

					'gradient-colors' => [
						'title' => __('Gradient colors', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-swatchbook',
						'desc' => __(
							'Used as default gradient widget colors. (maybe on more elements in the future)',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-gradient-deg' => [
								'name' => __('Gradient angle', 'peepso-theme-gecko'),
								'id' => 'color-gradient-deg',
								'type' => 'range',
								'var' => '--COLOR--GRADIENT--DEG',
								'unit' => 'deg',
								'min' => '0',
								'max' => '360',
								'step' => '1',
							],
							'color-gradient-one' => [
								'name' => __('Gradient color 1', 'peepso-theme-gecko'),
								'id' => 'color-gradient-one',
								'type' => 'color',
								'var' => '--COLOR--GRADIENT--ONE',
							],
							'color-gradient-two' => [
								'name' => __('Gradient color 2', 'peepso-theme-gecko'),
								'id' => 'color-gradient-two',
								'type' => 'color',
								'var' => '--COLOR--GRADIENT--TWO',
							],
							'color-gradient-text' => [
								'name' => __('Text on gradient color', 'peepso-theme-gecko'),
								'id' => 'color-gradient-text',
								'type' => 'color',
								'var' => '--COLOR--GRADIENT--TEXT',
							],
							'color-gradient-links' => [
								'name' => __('Links on gradient color', 'peepso-theme-gecko'),
								'id' => 'color-gradient-links',
								'type' => 'color',
								'var' => '--COLOR--GRADIENT--LINKS',
							],
							'color-gradient-links-hover' => [
								'name' => __('Links hover color on gradient', 'peepso-theme-gecko'),
								'id' => 'color-gradient-links-hover',
								'type' => 'color',
								'var' => '--COLOR--GRADIENT--LINKS--HOVER',
							],
						],
					],

					'info-colors' => [
						'title' => __('Info colors', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-swatchbook',
						'desc' => __(
							'Info colors used for default alerts.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-info' => [
								'name' => __('Info color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-info',
								'type' => 'color',
								'var' => '--COLOR--INFO',
							],

							'color-info-light' => [
								'name' => __('Info light color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-info-light',
								'type' => 'color',
								'var' => '--COLOR--INFO--LIGHT',
							],

							'color-info-ultralight' => [
								'name' => __('Info ultralight color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-info-ultralight',
								'type' => 'color',
								'var' => '--COLOR--INFO--ULTRALIGHT',
							],

							'color-info-dark' => [
								'name' => __('Info dark color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-info-dark',
								'type' => 'color',
								'var' => '--COLOR--INFO--DARK',
							],
						],
					],

					'success-colors' => [
						'title' => __('Success colors', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-swatchbook',
						'desc' => __(
							'Success action colors used for alerts buttons etc.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-success' => [
								'name' => __('Success color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-success',
								'type' => 'color',
								'var' => '--COLOR--SUCCESS',
							],

							'color-success-light' => [
								'name' => __('Success light color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-success-light',
								'type' => 'color',
								'var' => '--COLOR--SUCCESS--LIGHT',
							],

							'color-success-ultralight' => [
								'name' => __('Success ultralight color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-success-ultralight',
								'type' => 'color',
								'var' => '--COLOR--SUCCESS--ULTRALIGHT',
							],

							'color-success-dark' => [
								'name' => __('Success dark color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-success-dark',
								'type' => 'color',
								'var' => '--COLOR--SUCCESS--DARK',
							],
						],
					],

					'warning-colors' => [
						'title' => __('Warning colors', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-swatchbook',
						'desc' => __(
							'Warning action colors used for alerts buttons etc.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-warning' => [
								'name' => __('Warning color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-warning',
								'type' => 'color',
								'var' => '--COLOR--WARNING',
							],

							'color-warning-light' => [
								'name' => __('Warning light color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-warning-light',
								'type' => 'color',
								'var' => '--COLOR--WARNING--LIGHT',
							],

							'color-warning-ultralight' => [
								'name' => __('Warning ultralight color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-warning-ultralight',
								'type' => 'color',
								'var' => '--COLOR--WARNING--ULTRALIGHT',
							],

							'color-warning-dark' => [
								'name' => __('Warning dark color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-warning-dark',
								'type' => 'color',
								'var' => '--COLOR--WARNING--DARK',
							],
						],
					],

					'abort-colors' => [
						'title' => __('Abort colors', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-swatchbook',
						'desc' => __(
							'Abort action/error colors used for alerts buttons etc.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-abort' => [
								'name' => __('Abort color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-abort',
								'type' => 'color',
								'var' => '--COLOR--ABORT',
							],

							'color-abort-light' => [
								'name' => __('Abort light color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-abort-light',
								'type' => 'color',
								'var' => '--COLOR--ABORT--LIGHT',
							],

							'color-abort-ultralight' => [
								'name' => __('Abort ultralight color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-abort-ultralight',
								'type' => 'color',
								'var' => '--COLOR--ABORT--ULTRALIGHT',
							],

							'color-abort-dark' => [
								'name' => __('Abort dark color', 'peepso-theme-gecko'),
								'id' => 'gcc-color-abort-dark',
								'type' => 'color',
								'var' => '--COLOR--ABORT--DARK',
							],
						],
					],

					'TypographyColors' => [
						'title' => __('Typography colors', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-a',
						'desc' => __(
							'Here you can edit global text colors.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-heading' => [
								'name' => __('Global headings color', 'peepso-theme-gecko'),
								'id' => 'color-heading',
								'type' => 'color',
								'var' => '--COLOR--HEADING',
							],
							'color-text' => [
								'name' => __('Global text color', 'peepso-theme-gecko'),
								'id' => 'color-text',
								'type' => 'color',
								'var' => '--COLOR--TEXT',
							],
							'color-text-light' => [
								'name' => __('Global text light color', 'peepso-theme-gecko'),
								'id' => 'color-text-light',
								'type' => 'color',
								'var' => '--COLOR--TEXT--LIGHT',
							],
							'color-text-lighten' => [
								'name' => __('Global text lighten color', 'peepso-theme-gecko'),
								'id' => 'color-text-lighten',
								'type' => 'color',
								'var' => '--COLOR--TEXT--LIGHTEN',
							],
						],
					],

					'link-colors' => [
						'title' => __('Links', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-link',
						'desc' => __('These colors are used for links.', 'peepso-theme-gecko'),
						'settings' => [
							'link-color' => [
								'name' => __('Link color', 'peepso-theme-gecko'),
								'id' => 'link-color',
								'type' => 'color',
								'var' => '--COLOR--LINK',
							],
							'link-color-hover' => [
								'name' => __('Link hover color', 'peepso-theme-gecko'),
								'id' => 'link-color-hover',
								'type' => 'color',
								'var' => '--COLOR--LINK-HOVER',
							],
							'link-color-focus' => [
								'name' => __('Link focus color', 'peepso-theme-gecko'),
								'id' => 'link-color-focus',
								'type' => 'color',
								'var' => '--COLOR--LINK-FOCUS',
							],
						],
					],

					'bg-colors' => [
						'title' => __('Backgrounds', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-adjust',
						'desc' => __(
							'These colors are used for backgrounds of components, widgets etc.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-body' => [
								'name' => __('Body background', 'peepso-theme-gecko'),
								'id' => 'color-body',
								'type' => 'color',
								'var' => '--c-gc-body-bg',
							],
							'color-app' => [
								'name' => __('Base color', 'peepso-theme-gecko'),
								'id' => 'color-app',
								'type' => 'color',
								'var' => '--COLOR--APP',
							],
							'color-app-dark' => [
								'name' => __('Base dark color', 'peepso-theme-gecko'),
								'id' => 'color-app-dark',
								'type' => 'color',
								'var' => '--COLOR--APP--DARK',
							],
							'color-app-darker' => [
								'name' => __('Base darker color', 'peepso-theme-gecko'),
								'id' => 'color-app-darker',
								'type' => 'color',
								'var' => '--COLOR--APP--DARKER',
							],
							'color-app-gray' => [
								'name' => __('Base gray color', 'peepso-theme-gecko'),
								'id' => 'color-app-gray',
								'type' => 'color',
								'var' => '--COLOR--APP--GRAY',
							],
							'color-app-light-gray' => [
								'name' => __('Base light gray color', 'peepso-theme-gecko'),
								'id' => 'color-app-light-gray',
								'type' => 'color',
								'var' => '--COLOR--APP--LIGHTGRAY',
							],
							'color-app-dark-gray' => [
								'name' => __('Base dark gray color', 'peepso-theme-gecko'),
								'id' => 'color-app-dark-gray',
								'type' => 'color',
								'var' => '--COLOR--APP--DARKGRAY',
							],
						],
					],

					'border-colors' => [
						'title' => __('Borders & Separators', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-border-all',
						'desc' => __(
							'These colors are used for borders & separators of components, widgets etc.',
							'peepso-theme-gecko'
						),
						'settings' => [
							'color-divider' => [
								'name' => __('Basic border', 'peepso-theme-gecko'),
								'id' => 'color-divider',
								'type' => 'color',
								'var' => '--DIVIDER',
							],
							'color-divider-light' => [
								'name' => __('Light border', 'peepso-theme-gecko'),
								'id' => 'color-divider-light',
								'type' => 'color',
								'var' => '--DIVIDER--LIGHT',
							],
							'color-divider-lighten' => [
								'name' => __('Lighten border', 'peepso-theme-gecko'),
								'id' => 'color-divider-lighten',
								'type' => 'color',
								'var' => '--DIVIDER--LIGHTEN',
							],
							'color-divider-dark' => [
								'name' => __('Dark border', 'peepso-theme-gecko'),
								'id' => 'color-divider-dark',
								'type' => 'color',
								'var' => '--DIVIDER--DARK',
							],
							'color-divider-invert' => [
								'name' => __('Invert border', 'peepso-theme-gecko'),
								'id' => 'color-divider-invert',
								'type' => 'color',
								'var' => '--DIVIDER--R',
							],
							'color-divider-invert-light' => [
								'name' => __('Invert light border', 'peepso-theme-gecko'),
								'id' => 'color-divider-invert-light',
								'type' => 'color',
								'var' => '--DIVIDER--R--LIGHT',
							],
						],
					],
				],
			];

			$options['Appearance'] = [
				'name' => __('Appearance', 'peepso-theme-gecko'),
				'desc' => __('Borders, shadows etc.', 'peepso-theme-gecko'),
				'tags' => __('Borders, shadows etc.', 'peepso-theme-gecko'),
				'id' => 'appearance',
				'icon' => 'gcis gci-magic',
				'options' => [
					'appearance-borders' => [
						'title' => __('Borders', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-border-style',
						'settings' => [
							'border-radius' => [
								'name' => __('Global border radius', 'peepso-theme-gecko'),
								'id' => 'border-radius',
								'type' => 'range',
								'var' => '--BORDER-RADIUS',
								'unit' => 'px',
								'min' => '0',
								'max' => '20',
								'step' => '1',
							],
						],
					],
					'appearance-shadows' => [
						'title' => __('Shadows', 'peepso-theme-gecko'),
						'desc' => __('Global shadow settings applied to most of the theme elements.', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-clone',
						'settings' => [
							'shadow-distance' => [
								'name' => __('Global shadow distance', 'peepso-theme-gecko'),
								'id' => 'shadow-distance',
								'type' => 'range',
								'var' => '--BOX-SHADOW-DIS',
								'unit' => 'px',
								'min' => '0',
								'max' => '50',
								'step' => '1',
							],
							'shadow-blur' => [
								'name' => __('Global shadow blur', 'peepso-theme-gecko'),
								'id' => 'shadow-blur',
								'type' => 'range',
								'var' => '--BOX-SHADOW-BLUR',
								'unit' => 'px',
								'min' => '0',
								'max' => '50',
								'step' => '1',
							],
							'shadow-thickness' => [
								'name' => __('Global shadow thickness', 'peepso-theme-gecko'),
								'id' => 'shadow-thickness',
								'type' => 'range',
								'var' => '--BOX-SHADOW-THICKNESS',
								'unit' => 'px',
								'min' => '0',
								'max' => '50',
								'step' => '1',
							],
							'shadow-color' => [
								'name' => __('Global shadow color', 'peepso-theme-gecko'),
								'id' => 'shadow-color',
								'type' => 'color',
								'var' => '--BOX-SHADOW-COLOR',
							],
						],
					],
				],
			];

			$options['Theme'] = [
				'name' => __('Theme', 'peepso-theme-gecko'),
				'desc' => __('Gecko theme settings.', 'peepso-theme-gecko'),
				'tags' => __('Header, footer, sidebars, layout.', 'peepso-theme-gecko'),
				'id' => 'theme',
				'icon' => 'gcis gci-table',
				'new' => true,
				'options' => [
					'General' => [
						'title' => __('General', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-cog',
						'desc' => __('Theme settings.', 'peepso-theme-gecko'),
						'settings' => [
//							'gc-admin-bar-vis' => [
//							  'name' => __('Show WP admin bar.', 'peepso-theme-gecko'),
//							  'id' => 'gc-admin-bar-vis',
//							  'type' => 'select',
//							  'setting' => 'opt_show_adminbar',
//								'options' => [
//									'1' => __('Always', 'peepso-theme-gecko'),
//									'2' => __('Only to Administrators', 'peepso-theme-gecko'),
//									'3' => __('Never', 'peepso-theme-gecko'),
//								],
//							],
							'gc-browser-zoom' => [
							  'name' => __('Allow zoom on Mobile.', 'peepso-theme-gecko'),
							  'desc' => __('Please note that zooming is always enabled on iOS devices regardless of the value of this option.', 'peepso-theme-gecko'),
							  'id' => 'gc-browser-zoom',
							  'type' => 'switch',
							  'setting' => 'opt_zoom_feature',
							  'on' => '1',
							  'off' => '',
							],
							'gc-limit_page_options' => [
							  'name' => __('Limit access to Gecko Page options.', 'peepso-theme-gecko'),
								'desc' => __('Limits the ability to edit Gecko Page options to admins only. (Moderators & Editors have access to these options as default)', 'peepso-theme-gecko'),
							  'id' => 'gc-limit_page_options',
							  'type' => 'switch',
							  'setting' => 'opt_limit_page_options',
							  'on' => '1',
							  'off' => '',
							],
							'gc-scroll_to_top' => [
							  'name' => __('Show scroll to top button.', 'peepso-theme-gecko'),
							  'id' => 'opt_scroll_to_top',
							  'type' => 'switch',
							  'setting' => 'opt_scroll_to_top',
							  'on' => '1',
							  'off' => '',
							],
							'gc-edit_link_bottom' => [
								'name' => __('Show edit link at the bottom of the page/post.', 'peepso-theme-gecko'),
								'id' => 'opt_edit_link_bottom',
								'type' => 'switch',
								'setting' => 'opt_edit_link_bottom',
								'on' => '1',
								'off' => '',
							  ],
						],
					],
					'Layout' => [
						'title' => __('Layout', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-border-all',
						'desc' => __('Theme layout settings.', 'peepso-theme-gecko'),
						'new' => true,
						'settings' => [
							'gc-layout-width' => [
								'name' => __('Layout width', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like px or %.', 'peepso-theme-gecko'),
								'id' => 'gc-layout-width',
								'type' => 'default',
								'var' => '--c-gc-layout-width',
							],
							'gc-layout-gaps' => [
								'name' => __('Columns gap', 'peepso-theme-gecko'),
								'desc' => __('This will affect the gaps between main columns (middle and sidebars).', 'peepso-theme-gecko'),
								'id' => 'gc-layout-gaps',
								'type' => 'range',
								'var' => '--c-gc-layout-gap',
								'unit' => 'px',
								'min' => '0',
								'max' => '50',
								'step' => '5',
							],
							'middle-column-size' => [
								'name' => __('Middle column size', 'peepso-theme-gecko'),
								'desc' => __('Have in mind this setting may not work if you set fixed width in "px" on sidebars. Middle column will fill all the available space then.', 'peepso-theme-gecko'),
								'id' => 'middle-column-size',
								'type' => 'range',
								'var' => '--c-gc-main-column',
								'unit' => 'fr',
								'min' => '1',
								'max' => '4',
								'step' => '1',
							],
							'middle-column-maxwidth' => [
								'name' => __('Middle column max-width', 'peepso-theme-gecko'),
								'notice' => __('It will affect not only Community pages but also Blog and other pages.', 'peepso-theme-gecko'),
								'desc' => __('With this setting you can limit middle column maximum width. Use any unit, like px or %.', 'peepso-theme-gecko'),
								'id' => 'middle-column-maxwidth',
								'type' => 'default',
								'var' => '--c-gc-main-column-maxwidth',
							],
						],
					],
					'Sidebars' => [
						'title' => __('Sidebars', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-border-all',
						'desc' => __('Theme sidebars settings.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-sticky-sidebars' => [
							  'name' => __('Sticky sidebars.', 'peepso-theme-gecko'),
							  'id' => 'gc-sticky-sidebars',
							  'type' => 'switch',
							  'setting' => 'opt_sticky_sidebar',
							  'on' => '1',
							  'off' => '',
							],
							'gc-scroll-sidebars' => [
								'name' => __('Scrollable sidebars (BETA)', 'peepso-theme-gecko'),
								'desc' => __('Works similarly to Sticky Sidebars, but it also allows to scroll sidebars independently from middle column. (when enabled it overrides Sticky sidebars feature)', 'peepso-theme-gecko'),
								'id' => 'gc-scroll-sidebars',
								'type' => 'category',
								'var' => false,
							],
							'gc-scroll-sidebar-left' => [
							  'name' => __('Scrollable left sidebar.', 'peepso-theme-gecko'),
							  'id' => 'gc-scroll-sidebar-left',
							  'type' => 'switch',
							  'setting' => 'opt_scroll_sidebar_left',
							  'on' => '1',
							  'off' => '',
							],
							'gc-scroll-sidebar-right' => [
							  'name' => __('Scrollable right sidebar.', 'peepso-theme-gecko'),
							  'id' => 'gc-scroll-sidebar-right',
							  'type' => 'switch',
							  'setting' => 'opt_scroll_sidebar_right',
							  'on' => '1',
							  'off' => '',
							],
							'gc-sidebar-gaps' => [
								'name' => __('Sidebar gaps & dimensions', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-gaps',
								'type' => 'category',
								'var' => false,
							],
							'gc-sidebar-left-width' => [
								'name' => __('Left sidebar fixed width.', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like px or %. (Default is 1fr)', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-left-width',
								'type' => 'default',
								'var' => '--c-gc-sidebar-left-width',
							],
							'gc-sidebar-right-width' => [
								'name' => __('Right sidebar fixed width.', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like px or %. (Default is 1fr)', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-right-width',
								'type' => 'default',
								'var' => '--c-gc-sidebar-right-width',
							],
							'gc-sidebar-widgets-gap' => [
								'name' => __('Widgets gap', 'peepso-theme-gecko'),
								'desc' => __('Gap between widgets in sidebars.', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-widgets-gap',
								'type' => 'range',
								'var' => '--c-gc-sidebar-widgets-gap',
								'unit' => 'px',
								'min' => '0',
								'max' => '50',
								'step' => '5',
							],
							'gc-sidebar-vis' => [
								'name' => __('Desktop visibility', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-vis',
								'type' => 'category',
								'var' => false,
							],
							'gc-sidebar-left-show' => [
								'name' => __('Show left sidebar on desktop.', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-left-show',
								'type' => 'switch',
								'setting' => 'opt_sidebar_left_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-sidebar-right-show' => [
								'name' => __('Show right sidebar on desktop.', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-right-show',
								'type' => 'switch',
								'setting' => 'opt_sidebar_right_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-sidebar-mobile-vis' => [
								'name' => __('Mobile visibility', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-mobile-vis',
								'type' => 'category',
								'var' => false,
							],
							'gc-sidebar-left-show-mobile' => [
								'name' => __('Show left sidebar on mobile.', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-left-show-mobile',
								'type' => 'switch',
								'setting' => 'opt_sidebar_left_mobile_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-sidebar-right-show-mobile' => [
								'name' => __('Show right sidebar on mobile.', 'peepso-theme-gecko'),
								'id' => 'gc-sidebar-right-show-mobile',
								'type' => 'switch',
								'setting' => 'opt_sidebar_right_mobile_vis',
								'on' => '1',
								'off' => '',
							],
						],
					],
          'Body' => [
              'title' => __('Body', 'peepso-theme-gecko'),
              'icon' => 'gcis gci-code',
              'desc' => __('Theme body settings.', 'peepso-theme-gecko'),
              'settings' => [
                  'config-body-class' => [
                      'name' => __('Body class', 'peepso-theme-gecko'),
                      'id' => 'config-body-class',
                      'type' => 'default',
                      'var' => 'config-body-class',
                  ],
									'gc-body-bg-image' => [
											'name' => __('Body background image', 'peepso-theme-gecko'),
											'id' => 'gc-body-bg-image',
											'type' => 'image',
											'setting' => 'gc-body-bg-image',
									],
									'gc-body-bg-image-fixed' => [
										'name' => __('Fixed background position', 'peepso-theme-gecko'),
										'id' => 'gc-body-bg-image-fixed',
										'type' => 'switch',
										'var' => '--c-gc-body-bg-image-fixed',
										'on' => 'fixed',
										'off' => 'unset',
									],
									'gc-body-bg-image-size' => [
											'name' => __('Body background image size', 'peepso-theme-gecko'),
											'id' => 'gc-body-bg-image-size',
											'type' => 'select',
											'var' => '--c-gc-body-bg-image-size',
											'options' => [
												'auto' => 'Auto',
												'cover' => 'Cover',
												'contain' => 'Contain',
											],
									],
									'gc-body-bg-image-repeat' => [
											'name' => __('Body background image repeat', 'peepso-theme-gecko'),
											'id' => 'gc-body-bg-image-repeat',
											'type' => 'select',
											'var' => '--c-gc-body-bg-image-repeat',
											'options' => [
												'no-repeat' => 'Do not repeat',
												'repeat' => 'Repeat',
												'repeat-x' => 'Repeat horizontally',
												'repeat-y' => 'Repeat vertically',
											],
									],
              ],
          ],
					'Header' => [
						'title' => __('Header', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-maximize',
						'desc' => __('Header settings.', 'peepso-theme-gecko'),
						'megamenu' => true,
						'settings' => [
							// Basic header
							'gc-header-basic' => [
								'name' => __('Basic header options', 'peepso-theme-gecko'),
								'id' => 'gc-header-basic',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-vis' => [
								'name' => __('Display Header', 'peepso-theme-gecko'),
								'id' => 'opt_header_vis',
								'type' => 'switch',
								'setting' => 'opt_header_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-header-full-width' => [
								'name' => __('Full width mode', 'peepso-theme-gecko'),
								'id' => 'gc-header-full-width',
								'type' => 'switch',
								'setting' => 'opt_header_full_width',
								'on' => '1',
								'off' => '',
							],
							'gc-header-height' => [
								'name' => __('Header height', 'peepso-theme-gecko'),
								'id' => 'gc-header-height',
								'type' => 'range',
								'var' => '--c-gc-header-height',
								'unit' => 'px',
								'min' => '50',
								'max' => '120',
								'step' => '1',
							],
							'gc-header-font-size' => [
								'name' => __('Header font-size', 'peepso-theme-gecko'),
								'id' => 'gc-header-font-size',
								'type' => 'range',
								'var' => '--c-gc-header-font-size',
								'unit' => '%',
								'min' => '70',
								'max' => '120',
								'step' => '10',
							],
							// Long menu support
							'gc-header-longmenu' => [
								'name' => __('Long menu support', 'peepso-theme-gecko'),
								'desc' => __(
									'If there is not enough space on header to show all the menu links inline, it will hide the rest of them under dropdown menu.',
									'peepso-theme-gecko'
								),
								'id' => 'gc-header-longmenu',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-longmenu-vis' => [
								'name' => __('Enable long menu', 'peepso-theme-gecko'),
								'id' => 'gc-header-longmenu-vis',
								'type' => 'switch',
								'setting' => 'opt_enable_longmenu',
								'on' => '1',
								'off' => '',
							],
							// Static header
							'gc-header-sticky' => [
								'name' => __('Sticky header', 'peepso-theme-gecko'),
								'desc' => __(
									'Header will follow on scroll when this setting is enabled.',
									'peepso-theme-gecko'
								),
								'id' => 'gc-header-sticky',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-sticky-desktop' => [
								'name' => __('On desktop', 'peepso-theme-gecko'),
								'id' => 'gc-header-sticky-desktop',
								'type' => 'switch',
								'var' => '--c-gc-header-sticky',
								'on' => 'fixed',
								'off' => 'absolute',
							],
							'gc-header-sticky-mobile' => [
								'name' => __('On mobile', 'peepso-theme-gecko'),
								'id' => 'gc-header-sticky-mobile',
								'type' => 'switch',
								'var' => '--c-gc-header-sticky-mobile',
								'on' => 'fixed',
								'off' => 'absolute',
							],
							// Header colors
							'gc-header-colors' => [
								'name' => __('Header colors', 'peepso-theme-gecko'),
								'id' => 'gc-header-colors',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-bg' => [
								'name' => __('Header background', 'peepso-theme-gecko'),
								'id' => 'gc-header-bg',
								'type' => 'color',
								'var' => '--c-gc-header-bg',
							],
							'gc-header-logo-color' => [
								'name' => __('Header logo (text) color', 'peepso-theme-gecko'),
								'id' => 'gc-header-logo-color',
								'type' => 'color',
								'var' => '--c-gc-header-logo-color',
							],
							'gc-header-tagline-color' => [
								'name' => __('Header tagline color', 'peepso-theme-gecko'),
								'id' => 'gc-header-tagline-color',
								'type' => 'color',
								'var' => '--c-gc-header-tagline-color',
							],
							'gc-header-text-color' => [
								'name' => __('Header text color', 'peepso-theme-gecko'),
								'id' => 'gc-header-text-color',
								'type' => 'color',
								'var' => '--c-gc-header-text-color',
							],
							'gc-header-link-color' => [
								'name' => __('Header links color', 'peepso-theme-gecko'),
								'id' => 'gc-header-link-color',
								'type' => 'color',
								'var' => '--c-gc-header-link-color',
							],
							'gc-header-link-color-hover' => [
								'name' => __('Header links hover color', 'peepso-theme-gecko'),
								'id' => 'gc-header-link-color-hover',
								'type' => 'color',
								'var' => '--c-gc-header-link-color-hover',
							],
							'gc-header-link-active-indicator' => [
								'name' => __('Header active link indicator', 'peepso-theme-gecko'),
								'id' => 'gc-header-link-active-indicator',
								'type' => 'color',
								'var' => '--c-gc-header-link-active-indicator',
							],
							// Header menu
							'gc-header-menu' => [
								'name' => __('Header menu', 'peepso-theme-gecko'),
								'id' => 'gc-header-menu',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-menu-align' => [
								'name' => __('Header menu alignment', 'peepso-theme-gecko'),
								'id' => 'gc-header-menu-align',
								'type' => 'select',
								'var' => '--c-gc-header-menu-align',
								'options' => [
									'center' => 'Center',
									'flex-start' => 'Left',
									'flex-end' => 'Right',
								],
							],
							'gc-header-menu-font-size' => [
								'name' => __('Header menu font-size', 'peepso-theme-gecko'),
								'id' => 'gc-header-menu-font-size',
								'type' => 'range',
								'var' => '--c-gc-header-menu-font-size',
								'unit' => '%',
								'min' => '70',
								'max' => '120',
								'step' => '10',
							],
							// Header search
							'gc-header-search' => [
								'name' => __('Header search', 'peepso-theme-gecko'),
								'id' => 'gc-header-search',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-search-desktop' => [
								'name' => __('Display on desktop', 'peepso-theme-gecko'),
								'id' => 'gc-header-search-desktop',
								'type' => 'switch',
								'var' => '--c-gc-header-search-vis',
								'on' => 'block',
								'off' => 'none',
							],
							'gc-header-search-mobile' => [
								'name' => __('Display on mobile', 'peepso-theme-gecko'),
								'id' => 'gc-header-search-mobile',
								'type' => 'switch',
								'var' => '--c-gc-header-search-vis-mobile',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],

					'Sidenav' => [
						'title' => __('Sidenav', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-border-style',
						'desc' => __('Additional side navigation. (Requires PeepSo)', 'peepso-theme-gecko'),
						'info' => __('In the next stage you will be able to modify menu options, sidebar size, position of elements and colors of sidenav.', 'peepso-theme-gecko'),
						'beta' => true,
						'settings' => [
							// Basic
							'gc-sidenav' => [
								'name' => __('Basic settings', 'peepso-theme-gecko'),
								'id' => 'gc-sidenav',
								'type' => 'category',
								'var' => false,
							],
							'gc-sidenav-vis' => [
								'name' => __('Show sidenav', 'peepso-theme-gecko'),
								'id' => 'gc-sidenav-vis',
								'type' => 'switch',
								'setting' => 'opt_show_sidenav',
								'on' => '1',
								'off' => '',
							],
							// Logo
							'gc-sidenav-logo' => [
								'name' => __('Logo', 'peepso-theme-gecko'),
								'id' => 'gc-sidenav-logo',
								'type' => 'category',
								'var' => false,
							],
							'gc-sidenav-logo-image' => [
								'name' => __('Sidenav logo', 'peepso-theme-gecko'),
								'id' => 'gc-sidenav-logo-image',
								'type' => 'image',
								'setting' => 'opt_sidenav_logo',
							],
							// Menu Icons
							'gc-sidenav-menu' => [
								'name' => __('Menu Icons', 'peepso-theme-gecko'),
								'desc' => __('This will only affect the menu icon not the notifications icon.', 'peepso-theme-gecko'),
								'id' => 'gc-sidenav-menu',
								'type' => 'category',
								'var' => false,
							],
							'gc-sidenav-menu-icon' => [
								'name' => __('Icon color', 'peepso-theme-gecko'),
								'id' => 'gc-sidenav-menu-icon',
								'type' => 'color',
								'var' => '--c-gc-sidenav-menu-icon',
							],
							'gc-sidenav-menu-icon-hover' => [
								'name' => __('Icon color on hover', 'peepso-theme-gecko'),
								'id' => 'gc-sidenav-menu-icon-hover',
								'type' => 'color',
								'var' => '--c-gc-sidenav-menu-icon-hover',
							],
							'gc-sidenav-menu-icon-bg-hover' => [
								'name' => __('Icon background color on hover', 'peepso-theme-gecko'),
								'id' => 'gc-sidenav-menu-icon-bg-hover',
								'type' => 'color',
								'var' => '--c-gc-sidenav-menu-icon-bg-hover',
							],
						],
					],

					'Mobile menu' => [
						'title' => __('Mobile menu', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-mobile-alt',
						'desc' => __('Mobile menu settings.', 'peepso-theme-gecko'),
						'settings' => [
							// Global
							'gc-header-sidebar-basic' => [
								'name' => __('Basic settings', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-basic',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-sidebar-position' => [
								'name' => __('Position on to the right', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-position',
								'type' => 'switch',
								'setting' => 'opt_show_header_sidebar_position',
								'on' => '1',
								'off' => '',
							],
							'gc-header-sidebar-bg' => [
								'name' => __('Background color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-bg',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-bg',
							],
							'gc-header-sidebar-overlay-bg' => [
								'name' => __('Overlay background color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-overlay-bg',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-overlay-bg',
							],
							'gc-header-sidebar-close-color' => [
								'name' => __('Close icon color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-close-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-close-color',
							],
							'gc-header-sidebar-arrow-color' => [
								'name' => __('Arrow icon color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-arrow-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-arrow-color',
							],
							// Logo
							'gc-header-sidebar-logo' => [
								'name' => __('Logo settings', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-logo',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-sidebar-menu-logo-vis' => [
								'name' => __('Show logo', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-menu-logo-vis',
								'type' => 'switch',
								'setting' => 'opt_show_header_sidebar_logo',
								'on' => '1',
								'off' => '',
							],
							'gc-header-sidebar-logo-height' => [
								'name' => __('Logo height (image)', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-logo-height',
								'type' => 'range',
								'var' => '--c-gc-header-sidebar-logo-height',
								'unit' => 'px',
								'min' => '50',
								'max' => '120',
								'step' => '1',
							],
							'gc-header-sidebar-logo-bg' => [
								'name' => __('Logo background color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-logo-bg',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-logo-bg',
							],
							'gc-header-sidebar-logo-text-color' => [
								'name' => __('Logo text color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-logo-text-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-logo-text-color',
							],
							'gc-header-sidebar-logo-font-size' => [
								'name' => __('Logo font-size', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-logo-font-size',
								'type' => 'range',
								'var' => '--c-gc-header-sidebar-logo-font-size',
								'unit' => '%',
								'min' => '70',
								'max' => '200',
								'step' => '10',
							],
							// Menu
							'gc-header-sidebar-menu' => [
								'name' => __('Menu settings', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-menu',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-sidebar-menu-links-color' => [
								'name' => __('Menu links color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-menu-links-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-menu-links-color',
							],
							'gc-header-sidebar-menu-active-link-color' => [
								'name' => __('Menu active link color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-menu-active-link-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-menu-active-link-color',
							],
							'gc-header-sidebar-menu-active-indicator-color' => [
								'name' => __('Menu active indicator color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-menu-active-indicator-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-menu-active-indicator-color',
							],
							'gc-header-sidebar-menu-bg' => [
								'name' => __('Menu background color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-menu-bg',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-menu-bg',
							],
							'gc-header-sidebar-menu-font-size' => [
								'name' => __('Menu font-size', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-menu-font-size',
								'type' => 'range',
								'var' => '--c-gc-header-sidebar-menu-font-size',
								'unit' => '%',
								'min' => '70',
								'max' => '150',
								'step' => '10',
							],
							// Above menu widgets
							'gc-header-sidebar-above-menu' => [
								'name' => __('Above menu widget settings', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-above-menu',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-sidebar-above-menu-text-color' => [
								'name' => __('Text color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-above-menu-text-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-above-menu-text-color',
							],
							'gc-header-sidebar-above-menu-links-color' => [
								'name' => __('Links color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-above-menu-links-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-above-menu-links-color',
							],
							'gc-header-sidebar-above-menu-bg' => [
								'name' => __('Background color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-above-menu-bg',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-above-menu-bg',
							],
							// Under menu widgets
							'gc-header-sidebar-under-menu' => [
								'name' => __('Under menu widget settings', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-under-menu',
								'type' => 'category',
								'var' => false,
							],
							'gc-header-sidebar-under-menu-text-color' => [
								'name' => __('Text color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-under-menu-text-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-under-menu-text-color',
							],
							'gc-header-sidebar-under-menu-links-color' => [
								'name' => __('Links color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-under-menu-links-color',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-under-menu-links-color',
							],
							'gc-header-sidebar-under-menu-bg' => [
								'name' => __('Background color', 'peepso-theme-gecko'),
								'id' => 'gc-header-sidebar-under-menu-bg',
								'type' => 'color',
								'var' => '--c-gc-header-sidebar-under-menu-bg',
							],
						],
					],

					'Footer' => [
						'title' => __('Footer', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-minimize',
						'desc' => __('Theme footer settings.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-footer-appearance' => [
								'name' => __('Footer appearance', 'peepso-theme-gecko'),
								'id' => 'gc-footer-copyrights',
								'type' => 'category',
								'var' => false,
							],
							'gc-footer-vis' => [
								'name' => __('Display Footer', 'peepso-theme-gecko'),
								'id' => 'opt_footer_vis',
								'type' => 'switch',
								'setting' => 'opt_footer_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-footer-bg' => [
							  'name' => __('Footer background.', 'peepso-theme-gecko'),
							  'id' => 'gc-footer-bg',
								'type' => 'color',
								'var' => '--c-gc-footer-bg',
							],
							'gc-footer-text-color' => [
							  'name' => __('Text color.', 'peepso-theme-gecko'),
							  'id' => 'gc-footer-text-color',
								'type' => 'color',
								'var' => '--c-gc-footer-text-color',
							],
							'gc-footer-text-color-light' => [
								'name' => __('Text light color.', 'peepso-theme-gecko'),
								'id' => 'gc-footer-text-color-light',
								'type' => 'color',
								'var' => '--c-gc-footer-text-color-light',
							],
							'gc-footer-links-color' => [
							  'name' => __('Links color.', 'peepso-theme-gecko'),
							  'id' => 'gc-footer-links-color',
								'type' => 'color',
								'var' => '--c-gc-footer-links-color',
							],
							'gc-footer-links-color-hover' => [
							  'name' => __('Links color on hover.', 'peepso-theme-gecko'),
							  'id' => 'gc-footer-links-color-hover',
								'type' => 'color',
								'var' => '--c-gc-footer-links-color-hover',
							],
							'gc-footer-copyrights' => [
								'name' => __('Footer copyrights', 'peepso-theme-gecko'),
								'id' => 'gc-footer-copyrights',
								'type' => 'category',
								'var' => false,
							],
							'gc-footer-text-line-1' => [
							    'name' => __('Footer text', 'peepso-theme-gecko') . ' ('.sprintf(__('Line %d', 'peepso-theme-gecko'), 1) .')',
							    'id' => 'gc-footer-text-line-1',
							    'type' => 'default',
							    'setting' => 'opt_footer_text_line_1',
							],
							'gc-footer-text-line-2' => [
							    'name' => __('Footer text', 'peepso-theme-gecko') . ' ('.sprintf(__('Line %d', 'peepso-theme-gecko'), 2) .')',
							    'id' => 'gc-footer-text-line-2',
							    'type' => 'default',
							    'setting' => 'opt_footer_text_line_2',
							],
							'gc-footer-widgets' => [
								'name' => __('Footer widgets', 'peepso-theme-gecko'),
								'id' => 'gc-footer-widgets',
								'type' => 'category',
								'var' => false,
							],
							'gc-footer-col' => [
								'name' => __('Columns number.', 'peepso-theme-gecko'),
								'id' => 'gc-footer-col',
								'type' => 'range',
								'var' => '--c-gc-footer-col',
								'unit' => '',
								'min' => '1',
								'max' => '6',
								'step' => '1',
							],
							'gc-footer-widgets-visibility' => [
								'name' => __('Display on desktop', 'peepso-theme-gecko'),
								'id' => 'gc-footer-widgets-visibility',
								'type' => 'switch',
								'var' => '--c-gc-footer-widgets-vis',
								'on' => 'grid',
								'off' => 'none',
							],
							'gc-footer-widgets-visibility-mobile' => [
								'name' => __('Display on mobile', 'peepso-theme-gecko'),
								'id' => 'gc-footer-widgets-visibility-mobile',
								'type' => 'switch',
								'var' => '--c-gc-footer-widgets-vis-mobile',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],

					'Landing Page (Template)' => [
						'title' => __('Landing Page (Template)', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-columns',
						'desc' => __('Landing page settings.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-landing-footer' => [
								'name' => __('Landing Page footer', 'peepso-theme-gecko'),
								'id' => 'gc-landing-footer',
								'type' => 'category',
								'var' => false,
							],
							'gc-landing-footer-full-width' => [
								'name' => __('Full width footer', 'peepso-theme-gecko'),
								'id' => 'gc-landing-footer-full-width',
								'type' => 'switch',
								'setting' => 'opt_landing_footer_full_width',
								'on' => '1',
								'off' => ''
							],
							'gc-landing-footer-widgets' => [
								'name' => __('Landing Page footer widgets', 'peepso-theme-gecko'),
								'id' => 'gc-landing-footer-widgets',
								'type' => 'category',
								'var' => false,
							],
							'gc-landing-footer-widgets-vis' => [
								'name' => __('Display footer widgets', 'peepso-theme-gecko'),
								'id' => 'gc-landing-footer-widgets-vis',
								'type' => 'switch',
								'var' => '--c-gc-landing-footer-widgets-vis',
								'on' => 'block',
								'off' => 'none',
							],
							'gc-landing-footer-social-widgets-vis' => [
								'name' => __('Display footer (social) widgets', 'peepso-theme-gecko'),
								'id' => 'gc-landing-footer-social-widgets-vis',
								'type' => 'switch',
								'var' => '--c-gc-landing-footer-social-widgets-vis',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],
				],
			];

			$options['Blog'] = [
				'name' => __('Blog', 'peepso-theme-gecko'),
				'desc' => __('Wordpress blog settings.', 'peepso-theme-gecko'),
				'tags' => __('Blog, search page, archives etc.', 'peepso-theme-gecko'),
				'id' => 'blog',
				'icon' => 'gcis gci-pen-square',
				'options' => [
					'blog-general' => [
						'title' => __('General settings', 'peepso-theme-gecko'),
						'desc' => __('All settings related to blog page.', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-cog',
						'settings' => [
							'gc-blog-sidebars-vis' => [
							  'name' => __('Show sidebars on Blog Posts.', 'peepso-theme-gecko'),
							  'id' => 'gc-blog-post-sidebars-vis',
							  'type' => 'switch',
							  'setting' => 'opt_blog_sidebars',
							  'on' => '1',
							  'off' => '',
							],
							'gc-blog-limit-post-words' => [
								'name' => __('Limit words on Blog page.', 'peepso-theme-gecko'),
								'desc' => __('Each blog post on front-end will show only limited number of words (Blog page).', 'peepso-theme-gecko'),
								'id' => 'gc-blog-limit-post-words',
								'type' => 'switch',
								'setting' => 'opt_limit_blog_post',
								'on' => '1',
								'off' => '',
							],
							'gc-blog-limit-post-words-number' => [
								'name' => __('Number of words if limit is enabled. (Default is 55)', 'peepso-theme-gecko'),
								'id' => 'gc-blog-limit-post-words-number',
								'type' => 'default',
								'setting' => 'opt_limit_blog_post_words_number',
								'default_value' => 55
							],
							'gc-blog-update' => [
								'name' => __('Show `update` date on posts meta.', 'peepso-theme-gecko'),
								'id' => 'gc-blog-update',
								'type' => 'switch',
								'setting' => 'opt_blog_update',
								'on' => '1',
								'off' => '',
							],
							'gc-blog-image-maxwidth' => [
								'name' => __('Max height of the posts featured image (Blog view).', 'peepso-theme-gecko'),
								'desc' => __('Use pixels as unit or set 100% - for auto height. (Default is 100%)', 'peepso-theme-gecko'),
								'id' => 'gc-blog-image-maxwidth',
								'type' => 'default',
								'var' => '--c-gc-blog-image-max-height',
							],
							'gc-blog-image-caption' => [
								'name' => __('Show caption under Featured Image on Blog & Search page.', 'peepso-theme-gecko'),
								'id' => 'gc-blog-image-caption',
								'type' => 'switch',
								'setting' => 'opt_blog_image_caption',
								'on' => '1',
								'off' => '',
							],
						],
					],
					'blog-grid' => [
						'title' => __('Grid layout', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-border-all',
						'desc' => __('Grid settings for blog related pages.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-blog-grid-col' => [
								'name' => __('Number of Columns.', 'peepso-theme-gecko'),
								'id' => 'gc-blog-grid-col',
								'type' => 'range',
								'setting' => 'opt_blog_grid_col',
								'unit' => '',
								'min' => '2',
								'max' => '6',
								'step' => '1',
							],
							'gc-blog-grid' => [
								'name' => __('Grid layout on Blog page.', 'peepso-theme-gecko'),
								'id' => 'gc-blog-grid',
								'type' => 'switch',
								'setting' => 'opt_blog_grid',
								'on' => '1',
								'off' => '',
							],
							'gc-blog-archives-grid' => [
								'name' => __('Grid layout on Archives page.', 'peepso-theme-gecko'),
								'id' => 'gc-blog-archives-grid',
								'type' => 'switch',
								'setting' => 'opt_archives_grid',
								'on' => '1',
								'off' => '',
							],
							'gc-blog-search-grid' => [
								'name' => __('Grid layout on Search page.', 'peepso-theme-gecko'),
								'id' => 'gc-blog-search-grid',
								'type' => 'switch',
								'setting' => 'opt_search_grid',
								'on' => '1',
								'off' => '',
							],
						],
					],
					'blog-single-post' => [
						'title' => __('Single post', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-newspaper',
						'desc' => __('Settings for single post view.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-blog-single-post-image-maxwidth' => [
								'name' => __('Max height of the post featured image.', 'peepso-theme-gecko'),
								'desc' => __('Use pixels as unit or set 100% - for auto height. (Default is 100%)', 'peepso-theme-gecko'),
								'id' => 'gc-blog-single-post-image-maxwidth',
								'type' => 'default',
								'var' => '--c-gc-post-image-max-height',
							],
							'gc-blog-single-post-image-caption' => [
								'name' => __('Show caption under Featured Image on Single post.', 'peepso-theme-gecko'),
								'id' => 'gc-blog-single-post-image-caption',
								'type' => 'switch',
								'setting' => 'opt_blog_single_post_image_caption',
								'on' => '1',
								'off' => '',
							],
						],
					],
					'search-page' => [
						'title' => __('Search results', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-search',
						'desc' => __('Settings for search results page.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-search-show-header' => [
								'name' => __('Show header.', 'peepso-theme-gecko'),
								'notice' => __('It will also affect Archives page.', 'peepso-theme-gecko'),
								'id' => 'gc-search-show-header',
								'type' => 'switch',
								'setting' => 'opt_search_header_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-search-show-footer' => [
								'name' => __('Show footer.', 'peepso-theme-gecko'),
								'notice' => __('It will also affect Archives page.', 'peepso-theme-gecko'),
								'id' => 'gc-search-show-footer',
								'type' => 'switch',
								'setting' => 'opt_search_footer_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-search-show-sidebar-left' => [
								'name' => __('Show left sidebar.', 'peepso-theme-gecko'),
								'notice' => __('It will also affect Archives page.', 'peepso-theme-gecko'),
								'id' => 'gc-search-show-sidebar-left',
								'type' => 'switch',
								'setting' => 'opt_sidebar_left_search_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-search-show-sidebar-right' => [
								'name' => __('Show right sidebar.', 'peepso-theme-gecko'),
								'notice' => __('It will also affect Archives page.', 'peepso-theme-gecko'),
								'id' => 'gc-search-show-sidebar-right',
								'type' => 'switch',
								'setting' => 'opt_sidebar_right_search_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-search-show-header-menu' => [
								'name' => __('Show header menu.', 'peepso-theme-gecko'),
								'notice' => __('It will also affect Archives page.', 'peepso-theme-gecko'),
								'id' => 'gc-search-show-header-menu',
								'type' => 'switch',
								'setting' => 'opt_header_menu_search_vis',
								'on' => '1',
								'off' => '',
							],
							'gc-search-full-width-layout' => [
								'name' => __('Enable full width layout.', 'peepso-theme-gecko'),
								'id' => 'gc-search-full-width-layout',
								'type' => 'switch',
								'setting' => 'opt_search_full_width_layout',
								'on' => '1',
								'off' => '',
							],
							'gc-search-full-width-header' => [
								'name' => __('Enable full width header.', 'peepso-theme-gecko'),
								'id' => 'gc-search-full-width-header',
								'type' => 'switch',
								'setting' => 'opt_search_full_width_header',
								'on' => '1',
								'off' => '',
							],
							// to-do:
							// * builder friendly
							// * header blend mode (transparent)
						],
					],
				],
			];

			$options['Widgets'] = [
				'name' => __('Widgets', 'peepso-theme-gecko'),
				'desc' => __('General widget settings.', 'peepso-theme-gecko'),
				'tags' => __('General widget settings.', 'peepso-theme-gecko'),
				'id' => 'widgets',
				'icon' => 'gcis gci-th-large',
				'options' => [
					'Default-Widgets' => [
						'title' => __('Default widgets', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Edit colors of default Widgets.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-widgets-default-bg' => [
								'name' => __('Default widgets Background color', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-default-bg',
								'type' => 'color',
								'var' => '--c-gc-widget-bg',
							],
							'gc-widgets-default-text-color' => [
								'name' => __('Default widgets Text color', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-default-text-color',
								'type' => 'color',
								'var' => '--c-gc-widget-text-color',
							],
							// 'gc-widgets-default-links-color' => [
							// 	'name' => __('Default widgets Links color', 'peepso-theme-gecko'),
							// 	'id' => 'gc-widgets-default-links-color',
							// 	'type' => 'color',
							// 	'var' => '--c-gc-widget-links-color',
							// ],
							// 'gc-widgets-default-links-color-hover' => [
							// 	'name' => __('Default widgets Links hover color', 'peepso-theme-gecko'),
							// 	'id' => 'gc-widgets-default-links-color-hover',
							// 	'type' => 'color',
							// 	'var' => '--c-gc-widget-links-color-hover',
							// ],
						],
					],
					'Gradient-Widgets' => [
						'title' => __('Gradient widgets', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Edit default colors of gradient widgets.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-widgets-gradient-bg' => [
								'name' => __('Gradient widgets Background color', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-gradient-bg',
								'type' => 'color',
								'var' => '--s-widget--gradient-bg',
							],
							'gc-widgets-gradient-bg-2' => [
								'name' => __('Gradient widgets Background color 2', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-gradient-bg-2',
								'type' => 'color',
								'var' => '--s-widget--gradient-bg-2',
							],
							'gc-widgets-gradient-text-color' => [
								'name' => __('Gradient widgets Text color', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-gradient-text-color',
								'type' => 'color',
								'var' => '--s-widget--gradient-text',
							],
							'gc-widgets-gradient-links-color' => [
								'name' => __('Gradient widgets Links color', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-gradient-links-color',
								'type' => 'color',
								'var' => '--s-widget--gradient-links',
							],
							'gc-widgets-gradient-links-color-hover' => [
								'name' => __('Gradient widgets Links hover color', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-gradient-links-color-hover',
								'type' => 'color',
								'var' => '--s-widget--gradient-links-hover',
							],
						],
					],
					'Sticky-top-widgets-above-header' => [
						'title' => __('Sticky Top (above header)', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Sticky top bar with widgets.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-sticky-bar-above-bg' => [
								'name' => __('Background color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-bg',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-above-bg',
							],
							'gc-sticky-bar-above-text-color' => [
								'name' => __('Text color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-text-color',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-above-text-color',
							],
							'gc-sticky-bar-above-link-color' => [
								'name' => __('Links color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-link-color',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-above-link-color',
							],
							'gc-sticky-bar-above-link-color-hover' => [
								'name' => __('Links hover color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-link-color-hover',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-above-link-color-hover',
							],
							'gc-sticky-bar-above-font-size' => [
								'name' => __('Font-size', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-font-size',
								'type' => 'range',
								'var' => '--c-gc-sticky-bar-above-font-size',
								'unit' => '%',
								'min' => '70',
								'max' => '120',
								'step' => '10',
							],
							'gc-sticky-bar-above-add-padd' => [
								'name' => __('Additional padding inside bar', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-add-padd',
								'type' => 'range',
								'var' => '--c-gc-sticky-bar-above-add-padd',
								'unit' => 'px',
								'min' => '0',
								'max' => '30',
								'step' => '5',
							],
							'gc-sticky-bar-above-full-width' => [
								'name' => __('Full width', 'peepso-theme-gecko'),
								'desc' => __('Will remove container maximum width and default side padding.', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-full-width',
								'type' => 'switch',
								'setting' => 'opt_sticky_bar_above_full_width',
								'on' => '1',
								'off' => '',
							],
							'gc-sticky-bar-above-vis' => [
								'name' => __('Display on desktop', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-vis',
								'type' => 'switch',
								'var' => '--c-gc-sticky-bar-above-vis',
								'on' => 'block',
								'off' => 'none',
							],
							'gc-sticky-bar-above-mobile-vis' => [
								'name' => __('Display on mobile', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-above-mobile-vis',
								'type' => 'switch',
								'var' => '--c-gc-sticky-bar-above-vis-mobile',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],
					'Sticky-top-widgets-under-header' => [
						'title' => __('Sticky Top (under header)', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Sticky top bar with widgets.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-sticky-bar-under-bg' => [
								'name' => __('Background color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-bg',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-under-bg',
							],
							'gc-sticky-bar-under-text-color' => [
								'name' => __('Text color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-text-color',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-under-text-color',
							],
							'gc-sticky-bar-under-link-color' => [
								'name' => __('Links color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-link-color',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-under-link-color',
							],
							'gc-sticky-bar-under-link-color-hover' => [
								'name' => __('Links hover color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-link-color-hover',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-under-link-color-hover',
							],
							'gc-sticky-bar-under-font-size' => [
								'name' => __('Font-size', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-font-size',
								'type' => 'range',
								'var' => '--c-gc-sticky-bar-under-font-size',
								'unit' => '%',
								'min' => '70',
								'max' => '120',
								'step' => '10',
							],
							'gc-sticky-bar-under-add-padd' => [
								'name' => __('Additional padding inside bar', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-add-padd',
								'type' => 'range',
								'var' => '--c-gc-sticky-bar-under-add-padd',
								'unit' => 'px',
								'min' => '0',
								'max' => '30',
								'step' => '5',
							],
							'gc-sticky-bar-under-full-width' => [
								'name' => __('Full width', 'peepso-theme-gecko'),
								'desc' => __('Will remove container maximum width and default side padding.', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-full-width',
								'type' => 'switch',
								'setting' => 'opt_sticky_bar_under_full_width',
								'on' => '1',
								'off' => '',
							],
							'gc-sticky-bar-under-vis' => [
								'name' => __('Display on desktop', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-vis',
								'type' => 'switch',
								'var' => '--c-gc-sticky-bar-under-vis',
								'on' => 'block',
								'off' => 'none',
							],
							'gc-sticky-bar-under-mobile-vis' => [
								'name' => __('Display on mobile', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-under-mobile-vis',
								'type' => 'switch',
								'var' => '--c-gc-sticky-bar-under-vis-mobile',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],
					'Sticky-top-widgets-mobile-header' => [
						'title' => __('Sticky Top (Mobile App)', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Sticky top bar with widgets for Mobile App only.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-sticky-bar-mobile-bg' => [
								'name' => __('Background color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-mobile-bg',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-mobile-bg',
							],
							'gc-sticky-bar-mobile-text-color' => [
								'name' => __('Text color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-mobile-text-color',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-mobile-text-color',
							],
							'gc-sticky-bar-mobile-link-color' => [
								'name' => __('Links color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-mobile-link-color',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-mobile-link-color',
							],
							'gc-sticky-bar-mobile-link-color-hover' => [
								'name' => __('Links hover color', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-mobile-link-color-hover',
								'type' => 'color',
								'var' => '--c-gc-sticky-bar-mobile-link-color-hover',
							],
							'gc-sticky-bar-mobile-font-size' => [
								'name' => __('Font-size', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-mobile-font-size',
								'type' => 'range',
								'var' => '--c-gc-sticky-bar-mobile-font-size',
								'unit' => '%',
								'min' => '70',
								'max' => '120',
								'step' => '10',
							],
							'gc-sticky-bar-mobile-add-padd' => [
								'name' => __('Additional padding inside bar', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-mobile-add-padd',
								'type' => 'range',
								'var' => '--c-gc-sticky-bar-mobile-add-padd',
								'unit' => 'px',
								'min' => '0',
								'max' => '30',
								'step' => '5',
							],
							'gc-sticky-bar-mobile-full-width' => [
								'name' => __('Full width', 'peepso-theme-gecko'),
								'desc' => __('Will remove container maximum width and default side padding.', 'peepso-theme-gecko'),
								'id' => 'gc-sticky-bar-mobile-full-width',
								'type' => 'switch',
								'setting' => 'opt_sticky_bar_mobile_full_width',
								'on' => '1',
								'off' => '',
							],
						],
					],
					'Top-widgets' => [
						'title' => __('Top widgets', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Top area widgets (above the middle theme part).', 'peepso-theme-gecko'),
						'settings' => [
							'gc-widgets-top-col' => [
								'name' => __('Columns number', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-top-col',
								'type' => 'range',
								'var' => '--c-gc-widgets-top-col',
								'unit' => '',
								'min' => '1',
								'max' => '6',
								'step' => '1',
							],
							'gc-widgets-top-visibility' => [
								'name' => __('Display on desktop', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-top-visibility',
								'type' => 'switch',
								'var' => '--c-gc-widgets-top-vis',
								'on' => 'block',
								'off' => 'none',
							],
							'gc-widgets-top-visibility-mobile' => [
								'name' => __('Display on mobile', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-top-visibility-mobile',
								'type' => 'switch',
								'var' => '--c-gc-widgets-top-vis-mobile',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],
					'Bottom-widgets' => [
						'title' => __('Bottom widgets', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Bottom area widgets (under the middle theme part).', 'peepso-theme-gecko'),
						'settings' => [
							'gc-widgets-bottom-col' => [
								'name' => __('Columns number', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-bottom-col',
								'type' => 'range',
								'var' => '--c-gc-widgets-bottom-col',
								'unit' => '',
								'min' => '1',
								'max' => '6',
								'step' => '1',
							],
							'gc-widgets-bottom-visibility' => [
								'name' => __('Display on desktop', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-bottom-visibility',
								'type' => 'switch',
								'var' => '--c-gc-widgets-bottom-vis',
								'on' => 'block',
								'off' => 'none',
							],
							'gc-widgets-bottom-visibility-mobile' => [
								'name' => __('Display on mobile', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-bottom-visibility-mobile',
								'type' => 'switch',
								'var' => '--c-gc-widgets-bottom-vis-mobile',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],
					'Above-content-widgets' => [
						'title' => __('Above content widgets', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Above content area widgets.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-widgets-above-content-visibility' => [
								'name' => __('Display on desktop', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-above-content-visibility',
								'type' => 'switch',
								'var' => '--c-gc-widgets-above-content-vis',
								'on' => 'block',
								'off' => 'none',
							],
							'gc-widgets-above-content-visibility-mobile' => [
								'name' => __('Display on mobile', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-above-content-visibility-mobile',
								'type' => 'switch',
								'var' => '--c-gc-widgets-above-content-vis-mobile',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],
					'Under-content-widgets' => [
						'title' => __('Under content widgets', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-window-restore',
						'desc' => __('Under content area widgets.', 'peepso-theme-gecko'),
						'settings' => [
							'gc-widgets-under-content-visibility' => [
								'name' => __('Display on desktop', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-under-content-visibility',
								'type' => 'switch',
								'var' => '--c-gc-widgets-under-content-vis',
								'on' => 'block',
								'off' => 'none',
							],
							'gc-widgets-under-content-visibility-mobile' => [
								'name' => __('Display on mobile', 'peepso-theme-gecko'),
								'id' => 'gc-widgets-under-content-visibility-mobile',
								'type' => 'switch',
								'var' => '--c-gc-widgets-under-content-vis-mobile',
								'on' => 'block',
								'off' => 'none',
							],
						],
					],
				],
			];

			$options['PeepSo'] = [
				'name' => __('PeepSo', 'peepso-theme-gecko'),
				'desc' => __('Here you can change style of the PeepSo components.', 'peepso-theme-gecko'),
				'tags' => __('General PeepSo settings.', 'peepso-theme-gecko'),
				'id' => 'peepso',
				'new' => true,
				'icon' => 'gcib gci-product-hunt',
				'options' => [
					'ps-profile-page' => [
						'title' => __('Profile page settings', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-user-cog',
						'settings' => [
							'ps-profile-cover' => [
							  'name' => __('Profile cover size.', 'peepso-theme-gecko'),
							  'id' => 'ps-profile-cover',
							  'type' => 'select',
							  'setting' => 'opt_ps_profile_page_cover',
							  'options' => [
							    '1' => __('Default', 'peepso-theme-gecko'),
							    '2' => __('Wide cover', 'peepso-theme-gecko'),
							    '3' => __('Full width cover', 'peepso-theme-gecko'),
							  ],
							],
							'ps-profile-cover-height' => [
								'name' => __('Profile cover height.', 'peepso-theme-gecko'),
								'desc' => __("Set the lowest point if you want to force cover to use its lowest possible height on all resolutions.", 'peepso-theme-gecko'),
								'id' => 'ps-profile-cover-height',
								'type' => 'range',
								'var' => '--c-ps-profile-cover-height',
								'unit' => '%',
								'min' => '10',
								'max' => '50',
								'step' => '5',
							],
							'ps-profile-cover-centered' => [
								'name' => __('Center focus Avatar and details/actions.', 'peepso-theme-gecko'),
								'id' => 'ps-profile-cover-centered',
								'type' => 'switch',
								'setting' => 'opt_ps_profile_page_cover_centered',
								'on' => '1',
								'off' => '',
							],
							'ps-profile-avatar-size' => [
								'name' => __('Profile avatar size.', 'peepso-theme-gecko'),
								'desc' => __("Avatar size may affect cover height.", 'peepso-theme-gecko'),
								'id' => 'ps-profile-avatar-size',
								'type' => 'range',
								'var' => '--c-ps-profile-avatar-size',
								'unit' => 'px',
								'min' => '80',
								'max' => '240',
								'step' => '10',
							],
						],
					],

					'ps-general' => [
						'title' => __('General', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-cog',
						'settings' => [
							'ps-page-title' => [
							  'name' => __('Show page title on PeepSo pages.', 'peepso-theme-gecko'),
							  'id' => 'ps-page-title',
							  'type' => 'switch',
							  'var' => '--c-gc-show-page-title',
							  'on' => 'block',
							  'off' => 'none',
							],
							'ps-avatar' => [
								'name' => __('Avatars', 'peepso-theme-gecko'),
								'id' => 'ps-avatar',
								'type' => 'category',
								'var' => false,
							],
							'ps-avatar-corners' => [
								'name' => __('Avatar corners', 'peepso-theme-gecko'),
								'id' => 'ps-avatar-corners',
								'type' => 'range',
								'var' => '--c-ps-avatar-style',
								'unit' => '%',
								'custom-values' => '0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50',
							],
							'ps-other' => [
								'name' => __('Other', 'peepso-theme-gecko'),
								'id' => 'ps-other',
								'type' => 'category',
								'var' => false,
							],
							'ps-side-to-side' => [
								'name' => __('Full width PeepSo pages on mobile', 'peepso-theme-gecko'),
                                'desc' => __('Remove side paddings from PeepSo pages on mobile', 'peepso-theme-gecko'),
								'id' => 'ps-side-to-side',
								'type' => 'switch',
								'setting' => 'opt_ps_side_to_side',
								'on' => '1',
								'off' => '',
							],
						],
					],

					'ps-buttons' => [
						'title' => __('Buttons', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-square-check',
						'new' => true,
						'settings' => [
							'ps-buttons-default' => [
								'name' => __('Default style', 'peepso-theme-gecko'),
								'id' => 'ps-buttons-default',
								'type' => 'category',
								'var' => false,
							],
							'ps-btn-default-bg' => [
								'name' => __('Button background', 'peepso-theme-gecko'),
								'id' => 'ps-btn-default-bg',
								'type' => 'color',
								'var' => '--c-ps-btn-bg',
							],
							'ps-btn-default-color' => [
								'name' => __('Button text color', 'peepso-theme-gecko'),
								'id' => 'ps-btn-default-color',
								'type' => 'color',
								'var' => '--c-ps-btn-color',
							],
							'ps-btn-default-bg-hover' => [
								'name' => __('Button background on Hover', 'peepso-theme-gecko'),
								'id' => 'ps-btn-default-bg-hover',
								'type' => 'color',
								'var' => '--c-ps-btn-bg-hover',
							],
							'ps-btn-default-color-hover' => [
								'name' => __('Button text color on Hover', 'peepso-theme-gecko'),
								'id' => 'ps-btn-default-color-hover',
								'type' => 'color',
								'var' => '--c-ps-btn-color-hover',
							],
							'ps-buttons-action' => [
								'name' => __('Action buttons style', 'peepso-theme-gecko'),
								'id' => 'ps-buttons-action',
								'type' => 'category',
								'var' => false,
							],
							'ps-btn-action-bg' => [
								'name' => __('Button background', 'peepso-theme-gecko'),
								'id' => 'ps-btn-action-bg',
								'type' => 'color',
								'var' => '--c-ps-btn-action-bg',
							],
							'ps-btn-action-color' => [
								'name' => __('Button text color', 'peepso-theme-gecko'),
								'id' => 'ps-btn-action-color',
								'type' => 'color',
								'var' => '--c-ps-btn-action-color',
							],
							'ps-btn-action-bg-hover' => [
								'name' => __('Button background on Hover', 'peepso-theme-gecko'),
								'id' => 'ps-btn-action-bg-hover',
								'type' => 'color',
								'var' => '--c-ps-btn-action-bg-hover',
							],
							'ps-btn-action-color-hover' => [
								'name' => __('Button text color on Hover', 'peepso-theme-gecko'),
								'id' => 'ps-btn-action-color-hover',
								'type' => 'color',
								'var' => '--c-ps-btn-action-color-hover',
							],
						],
					],

					'ps-navbar' => [
						'title' => __('Toolbar', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-minus',
						'settings' => [
							'ps-navbar-sticky' => [
								'name' => __('Toolbar sticky (BETA)', 'peepso-theme-gecko'),
								'id' => 'ps-navbar-sticky',
								'type' => 'switch',
								'setting' => 'opt_ps_navbar_sticky',
								'on' => '1',
								'off' => ''
							],
							'ps-navbar-bg' => [
								'name' => __('Toolbar background', 'peepso-theme-gecko'),
								'id' => 'ps-navbar-bg',
								'type' => 'color',
								'var' => '--c-ps-navbar-bg',
							],
							'ps-navbar-links-color' => [
								'name' => __('Toolbar links color', 'peepso-theme-gecko'),
								'id' => 'ps-navbar-links-color',
								'type' => 'color',
								'var' => '--c-ps-navbar-links-color',
							],
							'ps-navbar-links-color-hover' => [
								'name' => __('Toolbar links color on hover', 'peepso-theme-gecko'),
								'id' => 'ps-navbar-links-color-hover',
								'type' => 'color',
								'var' => '--c-ps-navbar-links-color-hover',
							],
							'ps-navbar-font-size' => [
								'name' => __('Toolbar font-size', 'peepso-theme-gecko'),
								'id' => 'ps-navbar-font-size',
								'type' => 'range',
								'var' => '--c-ps-navbar-font-size',
								'unit' => 'px',
								'min' => '14',
								'max' => '24',
								'step' => '1',
							],
							'ps-navbar-icon-size' => [
								'name' => __('Toolbar icons size', 'peepso-theme-gecko'),
								'id' => 'ps-navbar-icon-size',
								'type' => 'range',
								'var' => '--c-ps-navbar-icons-size',
								'unit' => 'px',
								'min' => '14',
								'max' => '24',
								'step' => '1',
							],
						],
					],

					'ps-stream' => [
						'title' => __('Activity stream', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-file-invoice',
						'settings' => [
							// POST
							'ps-post' => [
								'name' => __('Post', 'peepso-theme-gecko'),
								'id' => 'ps-post',
								'type' => 'category',
								'var' => false,
							],
							'ps-post-gap' => [
								'name' => __('Gap between Posts', 'peepso-theme-gecko'),
								'id' => 'ps-post-gap',
								'type' => 'range',
								'var' => '--c-ps-post-gap',
								'unit' => 'px',
								'min' => '0',
								'max' => '50',
								'step' => '1',
							],
							'ps-post-bg' => [
								'name' => __('Post background', 'peepso-theme-gecko'),
								'id' => 'ps-post-bg',
								'type' => 'color',
								'var' => '--c-ps-post-bg',
							],
							'ps-post-text-color' => [
								'name' => __('Post text color', 'peepso-theme-gecko'),
								'id' => 'ps-post-text-color',
								'type' => 'color',
								'var' => '--c-ps-post-text-color',
							],
							'ps-post-text-color-light' => [
								'name' => __('Post text light color', 'peepso-theme-gecko'),
								'id' => 'ps-post-text-color-light',
								'type' => 'color',
								'var' => '--c-ps-post-text-color-light',
							],
							'ps-post-font-size' => [
								'name' => __('Post font-size', 'peepso-theme-gecko'),
								'id' => 'ps-post-font-size',
								'type' => 'range',
								'var' => '--c-ps-post-font-size',
								'unit' => 'px',
								'min' => '14',
								'max' => '24',
								'step' => '1',
							],
							'ps-post-pinned' => [
								'name' => __('Pinned post', 'peepso-theme-gecko'),
								'id' => 'ps-post-pinned',
								'type' => 'category',
								'var' => false,
							],
							'ps-post-pinned-border-color' => [
								'name' => __('Pinned post - border color', 'peepso-theme-gecko'),
								'id' => 'ps-post-pinned-border-color',
								'type' => 'color',
								'var' => '--c-ps-post-pinned-border-color',
							],
							'ps-post-pinned-text-color' => [
								'name' => __('Pinned post - marker text color', 'peepso-theme-gecko'),
								'id' => 'ps-post-pinned-text-color',
								'type' => 'color',
								'var' => '--c-ps-post-pinned-text-color',
							],
							'ps-post-pinned-border-size' => [
								'name' => __('Pinned post - border size', 'peepso-theme-gecko'),
								'id' => 'ps-post-pinned-border-size',
								'type' => 'range',
								'var' => '--c-ps-post-pinned-border-size',
								'unit' => 'px',
								'min' => '0',
								'max' => '10',
								'step' => '1',
							],
							// POST PHOTOS
							'ps-post-photos' => [
								'name' => __('Photos in post', 'peepso-theme-gecko'),
								'id' => 'ps-post-photos',
								'type' => 'category',
								'var' => false,
							],
							'ps-post-attachment-bg' => [
								'name' => __('Post attachment background color', 'peepso-theme-gecko'),
								'id' => 'ps-post-attachment-bg',
								'type' => 'color',
								'var' => '--c-ps-post-attachment-bg',
							],
							'ps-post-photo-width' => [
								'name' => __('Force single photo to fill 100% width.', 'peepso-theme-gecko'),
								'desc' => __('Change "Single photo height" to "auto" for best results.', 'peepso-theme-gecko'),
								'id' => 'ps-post-photo-width',
								'type' => 'switch',
								'var' => '--c-ps-post-photo-width',
								'on' => '100%',
								'off' => 'auto',
							],
							'ps-post-photo-limit-width' => [
								'name' => __('Single photo limit width', 'peepso-theme-gecko'),
								'desc' => __('If the photo has "auto" width (setting above), you can limit the photo with the maximum width.', 'peepso-theme-gecko'),
								'id' => 'ps-post-photo-limit-width',
								'type' => 'default',
								'var' => '--c-ps-post-photo-limit-width',
							],
							'ps-post-photo-height-trim' => [
								'name' => __('Trim long single photo (BETA).', 'peepso-theme-gecko'),
								'desc' => __('If the photo has a "100%" width (setting above), you can add a "Click to expand" button for a photo with excessive height.', 'peepso-theme-gecko'),
								'id' => 'config-ps-post-photo-height-trim',
								'type' => 'switch',
								'var' => 'config-ps-post-photo-height-trim',
								'on' => '1',
								'off' => '',
							],
							'ps-post-photo-height' => [
								'name' => __('Single photo height', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like px, % or auto.', 'peepso-theme-gecko'),
								'id' => 'ps-post-photo-height',
								'type' => 'default',
								'var' => '--c-ps-post-photo-height',
							],
							'ps-post-gallery-width' => [
								'name' => __('Post gallery width', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like px or %.', 'peepso-theme-gecko'),
								'id' => 'ps-post-gallery-width',
								'type' => 'default',
								'var' => '--c-ps-post-gallery-width',
							],
							// POSTBOX
							'ps-postbox' => [
								'name' => __('Postbox', 'peepso-theme-gecko'),
								'id' => 'ps-postbox',
								'type' => 'category',
								'var' => false,
							],
							'ps-postbox-bg' => [
								'name' => __('Postbox background', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-bg',
								'type' => 'color',
								'var' => '--c-ps-postbox-bg',
							],
							'ps-postbox-text-color' => [
								'name' => __('Postbox text', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-text-color',
								'type' => 'color',
								'var' => '--c-ps-postbox-text-color',
							],
							'ps-postbox-text-color-light' => [
								'name' => __('Postbox icons (all)', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-text-color-light',
								'type' => 'color',
								'var' => '--c-ps-postbox-text-color-light',
							],
							'ps-postbox-icons-active-color' => [
								'name' => __('Postbox icons active (all)', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-icons-active-color',
								'type' => 'color',
								'var' => '--c-ps-postbox-icons-active-color',
							],
							'ps-postbox-icons-color' => [
								'name' => __('Post Type icons', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-icons-color',
								'type' => 'color',
								'var' => '--c-ps-postbox-icons-color',
							],
							'ps-postbox-type-icons-active' => [
								'name' => __('Post Type active icons', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-type-icons-active',
								'type' => 'color',
								'var' => '--c-ps-postbox-type-icons-active-color',
							],
							'ps-postbox-type-bg' => [
								'name' => __('Post Type background color', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-type-bg',
								'type' => 'color',
								'var' => '--c-ps-postbox-type-bg',
							],
							'ps-postbox-type-bg-hover' => [
								'name' => __('Post Type hover background color', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-type-bg-hover',
								'type' => 'color',
								'var' => '--c-ps-postbox-type-bg-hover',
							],
							'ps-postbox-separator-color' => [
								'name' => __('Postbox separator', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-separator-color',
								'type' => 'color',
								'var' => '--c-ps-postbox-separator-color',
							],
							'ps-postbox-dropdown-bg' => [
								'name' => __('Postbox dropdown background', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-dropdown-bg',
								'type' => 'color',
								'var' => '--c-ps-postbox-dropdown-bg',
							],
							'ps-postbox-dropdown-bg-light' => [
								'name' => __('Postbox dropdown background light', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-dropdown-bg-light',
								'type' => 'color',
								'var' => '--c-ps-postbox-dropdown-bg-light',
							],
							'ps-postbox-dropdown-text-color' => [
								'name' => __('Postbox dropdown text color', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-dropdown-text-color',
								'type' => 'color',
								'var' => '--c-ps-postbox-dropdown-text-color',
							],
							'ps-postbox-dropdown-icon-color' => [
								'name' => __('Postbox dropdown icon color', 'peepso-theme-gecko'),
								'id' => 'ps-postbox-dropdown-icon-color',
								'type' => 'color',
								'var' => '--c-ps-postbox-dropdown-icon-color',
							],
						],
					],

					'ps-notifs' => [
						'title' => __('Notifications', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-bell',
						'new' => true,
						'settings' => [
							'ps-notifs-unread-bg' => [
								'name' => __('Unread notification background', 'peepso-theme-gecko'),
								'id' => 'ps-notifs-unread-bg',
								'type' => 'color',
								'var' => '--c-ps-notification-unread-bg',
							],
							'ps-notifs-counter-bg' => [
								'name' => __('Notifications counter background', 'peepso-theme-gecko'),
								'id' => 'ps-notifs-counter-bg',
								'type' => 'color',
								'var' => '--c-ps-bubble-bg',
							],
							'ps-notifs-counter-color' => [
								'name' => __('Notifications counter text color', 'peepso-theme-gecko'),
								'id' => 'ps-notifs-counter-color',
								'type' => 'color',
								'var' => '--c-ps-bubble-color',
							],
						],
					],

					'ps-polls' => [
						'title' => __('Polls', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-list-ul',
						'new' => true,
						'settings' => [
							'ps-polls-item-color' => [
								'name' => __('Poll option text color', 'peepso-theme-gecko'),
								'notice' => __('Make sure text is readable on default and infill background colors.', 'peepso-theme-gecko'),
								'id' => 'ps-polls-item-color',
								'type' => 'color',
								'var' => '--c-ps-poll-item-color',
							],
							'ps-polls-item-bg' => [
								'name' => __('Poll option background color', 'peepso-theme-gecko'),
								'id' => 'ps-polls-item-bg',
								'type' => 'color',
								'var' => '--c-ps-poll-item-bg',
							],
							'ps-polls-item-infill' => [
								'name' => __('Poll option infill color', 'peepso-theme-gecko'),
								'id' => 'ps-polls-item-infill',
								'type' => 'color',
								'var' => '--c-ps-poll-item-bg-fill',
							],
						],
					],

					'ps-hashtags' => [
						'title' => __('Hashtags', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-hashtag',
						'new' => true,
						'settings' => [
							'ps-hashtags-widget' => [
								'name' => __('Hashtags in widget', 'peepso-theme-gecko'),
								'desc' => __('These settings will not affect hashtags in gradient widget style.', 'peepso-theme-gecko'),
								'id' => 'ps-hashtags-widget',
								'type' => 'category',
								'var' => false,
							],
							'ps-hashtag-color' => [
								'name' => __('Text color', 'peepso-theme-gecko'),
								'id' => 'ps-hashtag-color',
								'type' => 'color',
								'var' => '--c-ps-hashtag-color',
							],
							'ps-hashtag-bg' => [
								'name' => __('Text background', 'peepso-theme-gecko'),
								'id' => 'ps-hashtag-bg',
								'type' => 'color',
								'var' => '--c-ps-hashtag-bg',
							],
							'ps-hashtags-postbox' => [
								'name' => __('Hashtags in postbox', 'peepso-theme-gecko'),
								'id' => 'ps-hashtags-postbox',
								'type' => 'category',
								'var' => false,
							],
							'ps-hashtag-postbox-color' => [
								'name' => __('Text color', 'peepso-theme-gecko'),
								'id' => 'ps-hashtag-postbox-color',
								'type' => 'color',
								'var' => '--c-ps-hashtag-postbox-color',
							],
							'ps-hashtag-postbox-bg' => [
								'name' => __('Text background', 'peepso-theme-gecko'),
								'id' => 'ps-hashtag-postbox-bg',
								'type' => 'color',
								'var' => '--c-ps-hashtag-postbox-bg',
							],
						],
					],

					'ps-groups' => [
						'title' => __('Groups', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-users',
						'settings' => [
							'ps-group-focus-cover-height' => [
							  'name' => __('Group cover height.', 'peepso-theme-gecko'),
								'desc' => __("Set the lowest point if you want to force cover to use its lowest possible height on all resolutions.", 'peepso-theme-gecko'),
							  'id' => 'ps-group-focus-cover-height',
							  'type' => 'range',
							  'var' => '--c-ps-group-focus-cover-height',
							  'unit' => '%',
							  'min' => '10',
							  'max' => '80',
							  'step' => '5',
							],
							'ps-group-focus-avatar-size' => [
							  'name' => __('Group cover avatar size.', 'peepso-theme-gecko'),
								'desc' => __("Avatar size may affect cover height.", 'peepso-theme-gecko'),
							  'id' => 'ps-group-focus-avatar-size',
							  'type' => 'range',
							  'var' => '--c-ps-group-focus-avatar-size',
							  'unit' => 'px',
							  'min' => '80',
							  'max' => '200',
							  'step' => '10',
							],
						],
					],

					'ps-chat' => [
						'title' => __('Chat & Messages', 'peepso-theme-gecko'),
						'icon' => 'gcir gci-comments',
						'settings' => [
							'ps-chat-window-notif-bg' => [
								'name' => __('Chat window Notification background color.', 'peepso-theme-gecko'),
								'id' => 'ps-chat-window-notif-bg',
								'type' => 'color',
								'var' => '--c-ps-chat-window-notif-bg',
							],
							'ps-chat-message-bg' => [
								'name' => __('Chat message background (participants).', 'peepso-theme-gecko'),
								'id' => 'ps-chat-message-bg',
								'type' => 'color',
								'var' => '--c-ps-chat-message-bg',
							],
							'ps-chat-message-text-color' => [
								'name' => __('Chat message text color (participants).', 'peepso-theme-gecko'),
								'id' => 'ps-chat-message-text-color',
								'type' => 'color',
								'var' => '--c-ps-chat-message-text-color',
							],
							'ps-chat-message-bg-me' => [
								'name' => __('Chat message background (you).', 'peepso-theme-gecko'),
								'id' => 'ps-chat-message-bg-me',
								'type' => 'color',
								'var' => '--c-ps-chat-message-bg-me',
							],
							'ps-chat-message-text-color-me' => [
								'name' => __('Chat message text color (you).', 'peepso-theme-gecko'),
								'id' => 'ps-chat-message-text-color-me',
								'type' => 'color',
								'var' => '--c-ps-chat-message-text-color-me',
							],
						],
					],

					'ps-landing' => [
						'title' => __('Landing / Register box', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-image',
						'settings' => [
							'ps-landing-background-color' => [
								'name' => __('Background color under image.', 'peepso-theme-gecko'),
								'id' => 'ps-landing-background-color',
								'type' => 'color',
								'var' => '--c-ps-landing-background-color',
							],
							'ps-landing-desktop' => [
								'name' => __('Desktop view', 'peepso-theme-gecko'),
								'id' => 'ps-landing-desktop',
								'type' => 'category',
								'var' => false,
							],
							'ps-landing-image-height' => [
								'name' => __('Landing image height', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like %, vh, or px.', 'peepso-theme-gecko'),
								'id' => 'ps-landing-image-height',
								'type' => 'default',
								'var' => '--c-ps-landing-image-height',
							],
							'ps-landing-image-position' => [
								'name' => __('Landing image position', 'peepso-theme-gecko'),
								'id' => 'ps-landing-image-position',
								'type' => 'select',
								'var' => '--c-ps-landing-image-position',
								'options' => [
									'center' => 'Center',
									'top' => 'Top',
									'bottom' => 'Bottom',
								]
							],
							'ps-landing-image-size' => [
								'name' => __('Landing image size', 'peepso-theme-gecko'),
								'id' => 'ps-landing-image-size',
								'type' => 'select',
								'var' => '--c-ps-landing-image-size',
								'options' => [
									'cover' => 'Cover',
									'auto' => 'Auto',
									'contain' => 'Contain',
								]
							],
							'ps-landing-image-repeat' => [
								'name' => __('Repeat the landing image', 'peepso-theme-gecko'),
								'desc' => __('To fill the white space.', 'peepso-theme-gecko'),
								'id' => 'ps-landing-image-repeat',
								'type' => 'switch',
								'var' => '--c-ps-landing-image-repeat',
								'on' => 'repeat',
								'off' => 'no-repeat',
							],
							'ps-landing-mobile' => [
								'name' => __('Mobile view', 'peepso-theme-gecko'),
								'id' => 'ps-landing-mobile',
								'type' => 'category',
								'var' => false,
							],
							'ps-landing-image-height-mobile' => [
								'name' => __('Landing image height', 'peepso-theme-gecko'),
								'desc' => __('You can use any unit, like %, vh, or px.', 'peepso-theme-gecko'),
								'id' => 'ps-landing-image-height-mobile',
								'type' => 'default',
								'var' => '--c-ps-landing-image-height-mobile',
							],
							'ps-landing-image-position-mobile' => [
								'name' => __('Landing image position', 'peepso-theme-gecko'),
								'id' => 'ps-landing-image-position-mobile',
								'type' => 'select',
								'var' => '--c-ps-landing-image-position-mobile',
								'options' => [
									'center' => 'Center',
									'top' => 'Top',
									'bottom' => 'Bottom',
								]
							],
							'ps-landing-image-size-mobile' => [
								'name' => __('Landing image size', 'peepso-theme-gecko'),
								'id' => 'ps-landing-image-size-mobile',
								'type' => 'select',
								'var' => '--c-ps-landing-image-size-mobile',
								'options' => [
									'cover' => 'Cover (fill)',
									'auto' => 'Auto',
									'contain' => 'Contain',
								]
							],
							'ps-landing-image-repeat-mobile' => [
								'name' => __('Repeat the landing image', 'peepso-theme-gecko'),
								'desc' => __('To fill the white space.', 'peepso-theme-gecko'),
								'id' => 'ps-landing-image-repeat-mobile',
								'type' => 'switch',
								'var' => '--c-ps-landing-image-repeat-mobile',
								'on' => 'repeat',
								'off' => 'no-repeat',
							],
						]
					],
				],
			];

			if ( class_exists( 'woocommerce' ) ) {

			$options['WooCommerce'] = [
				'name' => __('WooCommerce', 'peepso-theme-gecko'),
				'desc' => __('Settings related to WooCommerce plugin.', 'peepso-theme-gecko'),
				'tags' => __('General WooCommerce settings.', 'peepso-theme-gecko'),
				'id' => 'woocommerce',
				'icon' => 'gcis gci-th-large',
				'options' => [
					'woo-general' => [
						'title' => __('General settings', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-minus',
						'settings' => [
							'woo-builder-friendly' => [
							  'name' => __('Builder friendly products.', 'peepso-theme-gecko'),
								'desc' => __('Makes every single product view builder friendly (full width layout with no paddings arround).', 'peepso-theme-gecko'),
							  'id' => 'woo-builder-friendly',
							  'type' => 'switch',
							  'setting' => 'opt_woo_builder',
								'on' => '1',
								'off' => '',
							],
							'woo-sidebars-vis' => [
							  'name' => __('Show sidebars on Shop & Product pages.', 'peepso-theme-gecko'),
							  'id' => 'woo-sidebars-vis',
							  'type' => 'switch',
							  'setting' => 'opt_woo_sidebars',
								'on' => '1',
								'off' => '0',
							],
							'woo-columns' => [
								'name' => __('Product columns.', 'peepso-theme-gecko'),
									'desc' => __("Set default number of product columns.", 'peepso-theme-gecko'),
								'id' => 'woo-columns',
								'type' => 'range',
								'setting' => 'opt_woo_columns',
								'unit' => '',
								'min' => '1',
								'max' => '5',
								'step' => '1',
							],
							'woo-mobile-single-col' => [
							  'name' => __('Force single column Products view on Mobile.', 'peepso-theme-gecko'),
							  'id' => 'woo-mobile-single-col',
							  'type' => 'switch',
							  'setting' => 'opt_woo_mobile_single_col',
								'on' => '1',
								'off' => '0',
							],
						],
					],
				],
			];

			} // end of WooCommerce

			if ( class_exists( 'SFWD_LMS' ) ) {

			$options['LearnDash'] = [
				'name' => __('LearnDash', 'peepso-theme-gecko'),
				'desc' => __('Settings related to LearnDash plugin.', 'peepso-theme-gecko'),
				'tags' => __('General LearnDash settings.', 'peepso-theme-gecko'),
				'id' => 'learndash',
				'icon' => 'gcis gci-th-large',
				'options' => [
					'woo-general' => [
						'title' => __('General settings', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-minus',
						'settings' => [
							'ld-courses-sidebars' => [
							  'name' => __('Show sidebars on all Courses.', 'peepso-theme-gecko'),
							  'id' => 'ld-courses-sidebars',
							  'type' => 'switch',
							  'setting' => 'opt_ld_sidebars',
								'on' => '1',
								'off' => '',
							],
						],
					],
				],
			];

		} // end of LearnDash

		if ( class_exists( '\TUTOR\Utils' ) ) {

		$options['Tutor LMS'] = [
			'name' => __('Tutor LMS', 'peepso-theme-gecko'),
			'desc' => __('Settings related to Tutor LMS plugin.', 'peepso-theme-gecko'),
			'tags' => __('General Tutor LMS settings.', 'peepso-theme-gecko'),
			'id' => 'tutorlms',
			'icon' => 'gcis gci-th-large',
			'options' => [
				'tutorlms-general' => [
					'title' => __('General settings', 'peepso-theme-gecko'),
					'icon' => 'gcis gci-minus',
					'settings' => [
						'tutorlms-overrides' => [
							'name' => __('Overrides Tutor LMS primary colors.', 'peepso-theme-gecko'),
							'id' => 'tutorlms-overrides',
							'type' => 'switch',
							'setting' => 'opt_tutorlms_overrides',
							'on' => '1',
							'off' => '',
						],
					],
				],
			],
		];

		} // end of Tutor LMS

		if ( function_exists('yoast_breadcrumb') ) {

			$options['YoastSEO'] = [
				'name' => __('Yoast SEO', 'peepso-theme-gecko'),
				'desc' => __('Settings related to Yoast SEO plugin.', 'peepso-theme-gecko'),
				'tags' => __('General Settings.', 'peepso-theme-gecko'),
				'id' => 'yoastseo',
				'icon' => 'gcis gci-file-lines',
				'options' => [
					'tutorlms-general' => [
						'title' => __('General settings', 'peepso-theme-gecko'),
						'icon' => 'gcis gci-cog',
						'settings' => [
							'yoastseo-breadcrumbs' => [
								'name' => __('Display breadcrumbs', 'peepso-theme-gecko'),
								'id' => 'yoastseo-breadcrumbs',
								'type' => 'switch',
								'setting' => 'opt_yoastseo_breadcrumbs',
								'on' => '1',
								'off' => '',
							],
						],
					],
				],
			];

			} // end of Yoast SEO

			return $options;
		}

		/**
		 * Get an option value.
		 *
		 * @since 3.0.0.0
		 *
		 * @param string $id
		 * @return string
		 */
		public function get($id) {
			$value = '';

			if (isset($this->options_settings[$id])) {
				$option_value = $this->settings->get_option($id);
				if ($option_value) {
					$value = $option_value;
				}
			} elseif (isset($this->options_css_vars[$id])) {
				$css_vars = get_option('gecko_css_vars', []);
				if (isset($css_vars[$id])) {
					$value = $css_vars[$id];
				}
			}

			return $value;
		}

		/**
		 * Update an option value.
		 *
		 * @since 3.0.0.0
		 *
		 * @param string $id
		 * @param string $value
		 * @return boolean
		 */
		public function update($id, $value) {
			if (isset($this->options_settings[$id])) {
				// Sanitize numeric value.
				if (is_string($value) && is_numeric($value)) {
					$value = (int) $value;
				}

				$this->settings->set_option($id, $value);
				return true;
			} elseif (isset($this->options_css_vars[$id])) {
				$css_vars = get_option('gecko_css_vars', []);
				$css_vars[$id] = $value;
				update_option('gecko_css_vars', $css_vars);
				return true;
			}

			return false;
		}

		/**
		 * Delete all option values.
		 *
		 * @since 3.0.0.0
		 */
		public function clear() {
			// Delete backend settings.
			$options = array_keys($this->options_settings);
			$this->settings->remove_option($options);

			// Delete CSS variables.
			delete_option('gecko_css_vars');
		}
	}
}
