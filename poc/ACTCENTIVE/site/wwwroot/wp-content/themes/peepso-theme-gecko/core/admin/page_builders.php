<?php
/**
 *  Create A Simple Theme Page Builders Page
 *
 */

//  Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//  Start Class
if ( ! class_exists( 'Gecko_Theme_Page_Builders' ) ) {

	class Gecko_Theme_Page_Builders {

		/**
		 * Start things up
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// We only need to register the admin panel on the back-end
			if ( is_admin() ) {
				add_action( 'admin_menu', array( 'Gecko_Theme_Page_Builders', 'register_sub_menu' ) );
			}

		}


		/**
		 * Add sub menu page
		 *
		 * @since 1.0.0
		 */
		public static function register_sub_menu() {
			add_submenu_page(
				'gecko-settings', 'Page Builders', 'Page Builders', 'manage_options', 'gecko-page-builders', array('Gecko_Theme_Page_Builders', 'create_admin_page')
			);
		}

		/**
		 * Settings page output
		 *
		 * @since 1.0.0
		 */
		public static function create_admin_page() { ?>
				<div class="gca-dash">
					<div class="gca-dash__inner">
						<div class="gca-dash__header">
							<div class="gca-dash__logo"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/gecko.png" alt="Gecko" /></div>

							<div class="gca-dash__title"><?php esc_html_e( 'Page Builders', 'peepso-theme-gecko' ); ?></div>
						</div>

						<div class="gca-dash__main">
							<div class="gca-dash__sidebar">
								<div class="gca-dash__menu">
									<a href="admin.php?page=gecko-settings" class="gca-dash__menu-link"><i class="gcis gci-tools"></i><?php esc_html_e( 'Settings', 'peepso-theme-gecko' ); ?></a>
									<a href="admin.php?page=gecko-customizer" class="gca-dash__menu-link"><i class="gcis gci-swatchbook"></i><?php esc_html_e( 'Gecko Customizer', 'peepso-theme-gecko' ); ?></a>
									<a href="admin.php?page=gecko-page-builders" class="gca-dash__menu-link gca-dash__menu-link--active"><i class="gcis gci-file-alt"></i><?php esc_html_e( 'Page Builders', 'peepso-theme-gecko' ); ?></a>
									<?php if(!isset($_SERVER['HTTP_HOST']) || 'demo.peepso.com' != $_SERVER['HTTP_HOST'] ) { ?>
									<a href="admin.php?page=gecko-license" class="gca-dash__menu-link"><i class="gcis gci-key"></i><?php esc_html_e( 'License', 'peepso-theme-gecko' ); ?></a>
									<?php } ?>
								</div>
							</div>

							<div class="gca-dash__content">
								<div class="gc-admin__page-builders">
									<div class="gc-admin__page-builders__item" data-builder="brizy">
										<a href="https://peep.so/brizzy" target="_blank">
											<h1><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/builders/brizy.png" alt="Brizy" /></h1>
											<span class="btn-action">Get Brizy</span>
										</a>
									</div>

									<div class="gc-admin__page-builders__item" data-builder="beaver">
										<a href="https://peep.so/beaverbuilder" target="_blank">
											<h1><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/builders/beaver.jpg" alt="Beaver Builder" /></h1>
											<span class="btn-action">Get Beaver Builder</span>
										</a>
									</div>

									<div class="gc-admin__page-builders__item" data-builder="elementor">
										<a href="https://peep.so/elementor" target="_blank">
											<h1><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/builders/elementor.png" alt="Elementor" /></h1>
											<span class="btn-action">Get Elementor</span>
										</a>
									</div>
								</div>

								<div class="gca-dash__foot">
									<div class="gca-dash__ver">
										<?php esc_html_e( 'Version', 'peepso-theme-gecko' ); ?>: <?php echo wp_get_theme()->version ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
		<?php }
	}
}
new Gecko_Theme_Page_Builders();
