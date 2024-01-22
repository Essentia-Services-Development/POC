<?php
/**
 *  Create A Simple Theme Options Panel
 *
 */

//  Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

//  Start Class
if ( ! class_exists( 'Gecko_Theme_Settings' ) ) {

    class Gecko_Theme_Settings {

        /**
         * Start things up
         *
         * @since 1.0.0
         */
        public function __construct() {

            // We only need to register the admin panel on the back-end
            if ( is_admin() ) {
                add_action( 'admin_menu', array( 'Gecko_Theme_Settings', 'add_admin_menu' ) );
                add_filter( 'gecko_sanitize_option', array('Gecko_Theme_Settings', 'sanitize'));
            }

        }

        /**
         * Sanitization callback
         *
         * @since 1.0.0
         */
        public static function sanitize( $options ) {

            // If we have options lets sanitize them
            if ( $_GET['page'] == 'gecko-settings' ) {
                $options['opt_redirect_guest'] = (int) $options['opt_redirect_guest'];
            }

            // Return sanitized options
            return $options;

        }


        /**
         * Add sub menu page
         *
         * @since 1.0.0
         */
        public static function add_admin_menu() {
            add_menu_page(
                esc_html__( 'Gecko', 'peepso-theme-gecko' ),
                esc_html__( 'Gecko', 'peepso-theme-gecko' ),
                'manage_options',
                'gecko-settings',
                array( 'Gecko_Theme_Settings', 'create_admin_page' ),
                get_template_directory_uri() . '/assets/images/logo.png'
            );
            add_submenu_page('gecko-settings', esc_html__( 'Settings', 'peepso-theme-gecko' ), esc_html__( 'Settings', 'peepso-theme-gecko' ), 'manage_options', 'gecko-settings');
        }

        /**
         * Settings page output
         *
         * @since 1.0.0
         */
        public static function create_admin_page() { ?>
            <form method="post" action="">
              <div class="gca-dash">
                <div class="gca-dash__inner">
                  <div class="gca-dash__header">
                    <div class="gca-dash__logo"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/gecko.png" alt="Gecko" /></div>

                    <div class="gca-dash__title"><?php esc_html_e( 'Settings', 'peepso-theme-gecko' ); ?></div>

                    <div class="gca-dash__actions">
                      <input type="hidden" name="gecko-config-nonce" value="<?php echo wp_create_nonce('gecko-config-nonce') ?>"/>
                      <?php submit_button(); ?>
                    </div>
                  </div>

                  <div class="gca-dash__main">
                    <div class="gca-dash__sidebar">
                      <div class="gca-dash__menu">
                        <a href="admin.php?page=gecko-settings" class="gca-dash__menu-link gca-dash__menu-link--active"><i class="gcis gci-tools"></i><?php esc_html_e( 'Settings', 'peepso-theme-gecko' ); ?></a>
                        <a href="admin.php?page=gecko-customizer" class="gca-dash__menu-link"><i class="gcis gci-swatchbook"></i><?php esc_html_e( 'Gecko Customizer', 'peepso-theme-gecko' ); ?></a>
                        <a href="admin.php?page=gecko-page-builders" class="gca-dash__menu-link"><i class="gcis gci-file-alt"></i><?php esc_html_e( 'Page Builders', 'peepso-theme-gecko' ); ?></a>
                        <?php if(!isset($_SERVER['HTTP_HOST']) || 'demo.peepso.com' != $_SERVER['HTTP_HOST'] ) { ?>
                        <a href="admin.php?page=gecko-license" class="gca-dash__menu-link"><i class="gcis gci-key"></i><?php esc_html_e( 'License', 'peepso-theme-gecko' ); ?></a>
                        <?php } ?>
                      </div>
                    </div>

                    <div class="gca-dash__content">
                      <div class="gca-dash__settings">
                        <!-- REDIRECTS -->
                        <div class="gca-dash__settings-group">
                          <div class="gca-dash__settings-name"><i class="gcis gci-directions"></i><?php esc_html_e( 'Redirects', 'peepso-theme-gecko' ); ?></div>

                          <div class="gca-dash__options">
                            <div class="gca-dash__option">
                              <div class="gca-dash__option-name"><?php esc_html_e( 'Redirect guests', 'peepso-theme-gecko' ); ?></div>
                              <?php
                              $gecko_settings = GeckoConfigSettings::get_instance();
                              $value = $gecko_settings->get_option( 'opt_redirect_guest', 0 );

                              $dropdown_args = array(
                                  'post_type'        => 'page',
                                  'selected'         => $value,
                                  'name'             => 'gecko_options[opt_redirect_guest]',
                                  'sort_column'      => 'menu_order, post_title',
                                  'echo'             => 1,
                                  'show_option_no_change' => 'Disabled',
                              );

                              wp_dropdown_pages( $dropdown_args );
                              ?>
                              <div class="gca-dash__option-desc">
                                <p><i class="gcis gci-info-circle"></i><?php esc_html_e( 'Redirect all guests to a specified page except from Registration and Privacy Policy pages', 'peepso-theme-gecko' ); ?></p>
                              </div>
                            </div>

                              <div class="gca-dash__option">
                                  <div class="gca-dash__option-desc">
                                      <p><i class="gcis gci-info-circle"></i><?php esc_html_e( 'Redirecting guests to a landing page is a great way to keep your website closed to non-members. You can define some exceptions from that rule, but keep in mind this might affect performance for guests. We recommend not defining too many exceptions.', 'peepso-theme-gecko' ); ?></p>
                                  </div>
                              </div>
                            <div class="gca-dash__option">
                              <div class="gca-dash__option-name"><?php esc_html_e( 'Redirect exceptions', 'peepso-theme-gecko' ); ?> (<?php echo __('single items','peepso-theme-gecko');?>)</div>
                              <?php
                              $gecko_settings = GeckoConfigSettings::get_instance();
                              $value = $gecko_settings->get_option( 'opt_redirect_guest_exceptions', '' );

                              $dropdown_args = array(
                                  'post_type'        => 'page',
                                  'selected'         => $value,
                                  'name'             => 'gecko_options[opt_redirect_guest_exceptions]',
                                  'sort_column'      => 'menu_order, post_title',
                                  'echo'             => 1,
                                  'show_option_no_change' => 'Disabled',
                              );

                              //wp_dropdown_pages( $dropdown_args );
                              ?>
                              <input name="gecko_options[opt_redirect_guest_exceptions]" type="text" value="<?php echo $value?>" size="64" />
                              <div class="gca-dash__option-desc">
                                <p>
                                  <?php esc_html_e( 'Comma-separated list of page IDs that should be visible to visitors', 'peepso-theme-gecko' ); ?>.
                                  <br/><br/>
                                  <?php esc_html_e( 'Use "blog" and "frontpage" to exclude your Blog Page and Frontpage', 'peepso-theme-gecko' ); ?>.
                                  <br/><br/>
                                  <?php esc_html_e('For example', 'peepso-theme-gecko');?>: <code>123,456,blog,789,frontpage</code>
                                </p>
                              </div>
                            </div>

                              <div class="gca-dash__option">
                                  <div class="gca-dash__option-name"><?php esc_html_e( 'Redirect exceptions', 'peepso-theme-gecko' ); ?> (<?php echo __('post types','peepso-theme-gecko');?>) <span class="gca-dash__label"><?php esc_html_e( 'Beta', 'peepso-theme-gecko' ); ?></span></div>
                                  <?php
                                  $gecko_settings = GeckoConfigSettings::get_instance();
                                  $value = $gecko_settings->get_option( 'opt_redirect_guest_exceptions_cpt', '' );

                                  $dropdown_args = array(
                                      'post_type'        => 'page',
                                      'selected'         => $value,
                                      'name'             => 'gecko_options[opt_redirect_guest_exceptions_cpt]',
                                      'sort_column'      => 'menu_order, post_title',
                                      'echo'             => 1,
                                      'show_option_no_change' => 'Disabled',
                                  );

                                  //wp_dropdown_pages( $dropdown_args );
                                  ?>
                                  <input name="gecko_options[opt_redirect_guest_exceptions_cpt]" type="text" value="<?php echo $value?>" size="64" />
                                  <div class="gca-dash__option-desc">
                                      <p>
                                          <?php esc_html_e( 'Comma-separated list of post types', 'peepso-theme-gecko' ); ?>.
                                          <br/><br/>
                                          <?php esc_html_e('For example', 'peepso-theme-gecko');?>: <code>post,product</code> to exclude blog posts and WooCommerce products
                                      </p>
                                  </div>
                                  <?php
                                  $cpts=get_post_types([],'objects');
                                  foreach ($cpts as $cpt) {
                                      $cpts[$cpt->name] = $cpt->labels->singular_name;
                                  }
                                  ?>
                                  <br/>
                                  <small><a href="#" class="ps-js-post-types-toggle"><?php echo __('All available post types','peepso-theme=gecko');?></a></small>

                                  <br/>
                                  <ul class="ps-js-post-types-list" style="display:none"><?php
                                    foreach($cpts as $cpt => $label) {
                                      if(stristr($cpt,'peepso')) {
                                          continue;
                                      }
                                      echo "<li><code>$cpt</code> - $label</li>";
                                    }
                                  ?></ul>
                                  <script>jQuery(function($) {
                                    $('.ps-js-post-types-toggle').on('click', function(e) {
                                      e.preventDefault();
                                      e.stopPropagation();
                                      $('.ps-js-post-types-list').toggle();
                                    });
                                  });</script>
                              </div>
                          </div>
                        </div>

                          <!-- USER PREFERENCES -->
                          <div class="gca-dash__settings-group">
                              <div class="gca-dash__settings-name"><i class="gcis gci-user-cog"></i><?php esc_html_e( 'User preferences', 'peepso-theme-gecko' ); ?></div>

                              <div class="gca-dash__options">
                                  <div class="gca-dash__option">
                                      <div class="gca-dash__option-name"><?php esc_html_e( 'Let users select preferred theme', 'peepso-theme-gecko' ); ?><span class="gca-dash__label"><?php esc_html_e( 'Beta', 'peepso-theme-gecko' ); ?></span></div>
                                      <?php
                                      $gecko_settings = GeckoConfigSettings::get_instance();
                                      $value = $gecko_settings->get_option( 'opt_user_preset', 0 );

                                      $options = [
                                          0 => __('Disabled','peepso-theme-gecko'),
                                          1 => __('All custom presets','peepso-theme-gecko'),
                                          2 => __('Selected custom presets','peepso-theme-gecko'),
                                      ];
                                      ?>
                                      <select name="gecko_options[opt_user_preset]" id="gecko_options[opt_user_preset]">
                                          <?php foreach($options as $key=>$label) { ?>
                                              <option <?php echo ($key==$value) ? 'selected' : '' ;?> value="<?php echo $key;?>"><?php echo $label;?></option>
                                          <?php } ?>
                                      </select>
                                  </div>

                                  <div class="gca-dash__option">
                                      <div class="gca-dash__option-name"><?php esc_html_e( 'Use these presets:', 'peepso-theme-gecko' ); ?></div>
                                      <?php

                                      $value = $gecko_settings->get_option( 'opt_user_preset_list', [] );
                                      if(!is_array($value)) {
                                          $value = (array) $value;
                                      }

                                      $presets = Gecko_Customizer_Preset::get_instance()->list(FALSE);

                                      $themes = [];
                                      if(count($presets)) {
                                          foreach ($presets as $preset) {
                                              $themes[$preset['id']] = $preset['label'];
                                          }
                                      }

                                      if(count($themes)) {
                                          foreach ($themes as $key => $theme) { ?>
                                              <input <?php echo (array_key_exists($key, $value)) ? 'checked' : '';?> type="checkbox" style="width:auto" name="gecko_options[opt_user_preset_list][<?php echo $key;?>]" value="1" /><?php echo $theme;?> <br/>
                                          <?php }
                                      }
                                      ?>
                                  </div>
                              </div>
                          </div>

                          <!-- OPEN GRAPH -->
                          <div class="gca-dash__settings-group">
                              <div class="gca-dash__settings-name"><i class="gcis gci-user-cog"></i><?php esc_html_e( 'Open Graph', 'peepso-theme-gecko' ); ?></div>

                              <div class="gca-dash__options">
                                  <div class="gca-dash__option">
                                      <div class="gca-dash__option-name"><?php esc_html_e( 'Inject Open Graph tags', 'peepso-theme-gecko' ); ?><span class="gca-dash__label"><?php esc_html_e( 'Beta', 'peepso-theme-gecko' ); ?></span></div>
                                      <?php
                                      $gecko_settings = GeckoConfigSettings::get_instance();
                                      $value = $gecko_settings->get_option( 'opt_open_graph', 1 );

                                      $options = [
                                          0 => __('Disabled','peepso-theme-gecko'),
                                          1 => __('Enabled','peepso-theme-gecko'),
                                      ];
                                      ?>
                                      <select name="gecko_options[opt_open_graph]" id="gecko_options[opt_open_graph]">
                                          <?php foreach($options as $key=>$label) { ?>
                                              <option <?php echo ($key==$value) ? 'selected' : '' ;?> value="<?php echo $key;?>"><?php echo $label;?></option>
                                          <?php } ?>
                                      </select>
                                  </div>
                              </div>
                          </div>

                        <!-- APP -->
                        <?php if(class_exists('PeepSoAppPlugin')) {?>
                          <div class="gca-dash__settings-group">
                            <div class="gca-dash__settings-name"><i class="gcis gci-mobile-alt"></i><?php esc_html_e( 'App', 'peepso-theme-gecko' ); ?></div>

                            <div class="gca-dash__options">
                              <div class="gca-dash__option">
                                <div class="gca-dash__option-name"><?php esc_html_e( 'Dedicated app widget positions:', 'peepso-theme-gecko' ); ?></div>
                                <?php
                                $gecko_settings = GeckoConfigSettings::get_instance();
                                $value = $gecko_settings->get_option( 'opt_app_widget_positions', 0 );
                                ?>
                                <select name="gecko_options[opt_app_widget_positions]">
                                    <option value="0"><?php echo __('Disabled','peepso-theme-gecko');?></option>
                                    <option value="1" <?php echo ($value == 1) ? "selected" : "";?>><?php echo __('Enabled','peepso-theme-gecko');?></option>
                                </select>
                                <div class="gca-dash__option-desc">
                                  <p><i class="gcis gci-info-circle"></i><?php esc_html_e( 'Requires a compatible third party wrapper. Enables widget positions for app context.', 'peepso-theme-gecko' ); ?></p>
                                </div>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                      </div>

                      <div class="gca-dash__foot">
                        <div class="gca-dash__ver">
                          <?php esc_html_e( 'Version', 'peepso-theme-gecko' ); ?>: <?php echo wp_get_theme()->version ?>
                        </div>
                        <div class="gca-dash__actions">
                          <input type="hidden" name="gecko-config-nonce" value="<?php echo wp_create_nonce('gecko-config-nonce') ?>"/>
                          <?php submit_button(); ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
        <?php }
    }
}
new Gecko_Theme_Settings();
