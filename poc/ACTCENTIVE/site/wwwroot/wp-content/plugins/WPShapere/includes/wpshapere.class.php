<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     https://acmeedesign.com
*/

defined('ABSPATH') || die;

if ( !class_exists('WPSHAPERE') ) {

  class WPSHAPERE
  {
  	private $wps_df_menu;
  	private $wps_df_submenu;
  	private $wps_options = WPSHAPERE_OPTIONS_SLUG;
  	private $wps_menuorder_options = 'wpshapere_menuorder';
    private $wps_purchase_data = 'wps_purchase_data';
    public $aof_options;
    private $do_not_save;

	function __construct()
	{
      $this->do_not_save = array('title', 'openTab', 'import', 'export');
      $this->aof_options = $this->get_wps_option_data($this->wps_options);
      add_action('admin_menu', array($this, 'wps_sub_menus'));
      add_action('wp_dashboard_setup', array($this, 'initialize_dash_widgets'), 999);

	    add_filter('admin_title', array($this, 'custom_admin_title'), 999, 2);
	    add_action( 'init', array($this, 'initFunctionss') );

	    add_action( 'admin_bar_menu', array($this, 'add_wpshapere_menus'), 0 );
	    add_action( 'admin_bar_menu', array($this, 'add_wpshapere_nav_menus'), 99);
      add_action( 'admin_bar_menu', array($this, 'wps_save_adminbar_nodes'), 9990 );
      add_action( 'wp_before_admin_bar_render', array($this, 'wps_save_adminbar_nodes'), 9990 );
	    add_action('wp_dashboard_setup', array($this, 'manage_widget_functions'), 9999);
      add_action('wp_dashboard_setup', array($this, 'add_dash_widgets'), 9999);
      if($this->aof_options['disable_styles_login'] != 1) {
          if ( ! has_action( 'login_enqueue_scripts', array($this, 'wpshapereloginAssets') ) )
            add_action('login_enqueue_scripts', array($this, 'wpshapereloginAssets'), 10);
          add_action('login_head', array($this, 'wpshapeLogincss'));
       }
	    add_action( 'admin_enqueue_scripts', array($this, 'wpshapereAssets'), 99999 );
      add_action( 'admin_head', array($this, 'wpshapeOptionscss'), 9999 );
	    add_action( 'wp_before_admin_bar_render', array($this, 'wps_remove_bar_links'), 9999) ;
      if(!empty($this->aof_options['adminbar_custom_welcome_text']))
        add_action( 'admin_bar_menu', array($this, 'update_avatar_size'), 99 );
	    add_filter('login_headerurl', array($this, 'wpshapere_login_url'));
	    add_filter('login_headertext', array($this, 'wpshapere_login_title'));
	    add_action('admin_head', array($this, 'generalFns'));

      add_action('plugins_loaded',array($this, 'get_admin_users'));
      add_filter( 'admin_footer_text', array($this, 'wps_footer_content'), 99999 );
	    add_action('login_footer', array($this, 'login_footer_content'));

	    add_action('wp_head', array($this, 'frontendActions'), 99999);
      add_action( 'activated_plugin', array($this, 'wps_activated' ));
      add_action( 'aof_before_heading', array($this, 'wps_welcome_msg'));
      add_action( 'aof_after_heading', array($this, 'wps_help_link'));
      add_filter( 'login_title', array($this, 'login_page_title') );

      //add_action( 'admin_notices', array( $this, 'wps_notify_liquido' ) );
      add_action( 'aof_before_heading', array( $this, 'wps_notify_liquido_theme' ) );

	}

    /*
    * Redirect to settings page after plugin activation
    */
   function wps_activated( $plugin ) {
     if( $plugin == plugin_basename( WPSHAPERE_PATH . "wpshapere.php" ) ) {
       exit( wp_redirect( admin_url( 'admin.php?page=wpshapere-options&status=wps-activated' ) ) );
     }
   }

  function wps_welcome_msg() {
    if(isset($_GET['status']) && $_GET['status'] == "wps-activated") {
      if( ! $this->wps_has_admin_users() )
        $this->get_admin_users();
      echo '<h1 style="line-height: 1.2em;font-size: 2.8em;font-weight: 400;">' . esc_html__('Welcome to WPShapere ', 'wps') . '</h1><br />';
    }
  }

  function wps_help_link() {
    echo '<div class="wps_kb_link"><a class="wps_kb_link" target="_blank" href="https://acmeedesign.support/helpproduct/wpshapere/"><span class="dashicons dashicons-editor-help"></span> ';
    echo esc_html__('Visit Knowledgebase', 'wps');
    echo '</a>
    <a class="wpspowerbox-link" target="_blank" href="https://codecanyon.net/item/wpspowerbox-addon-for-wpshapere-wordpress-admin-theme/22169580">WPSPowerbox Addon - Add Custom Menus</a>
    </div>';

  }

  function wps_notify_liquido() {

    if( ! empty( $this->aof_options['design_type'] ) && 4 != $this->aof_options['design_type'] ) {
      ?>
      <div data-dismissible="wps-hide-liquido-notice-forever" class="notice notice-success is-dismissible">
          <p><strong><?php
          printf( __( 'New: WPShapere version 7 introduces the <a href="%s">Liquido design type</a>! Try now!', 'wps' ), esc_url( admin_url( 'admin.php?page=wpshapere-options' ) ) );
          ?></strong></p>
      </div>
    <?php
    }
  }

  function wps_notify_liquido_theme() {

    $active_admin_theme = ( isset($this->aof_options['admin_theme_preset']) && !empty($this->aof_options['admin_theme_preset']) ) ? $this->aof_options['admin_theme_preset'] : 'Default';
    if( ! empty( $this->aof_options['design_type'] ) && 4 == $this->aof_options['design_type'] && 'Liquido' != $active_admin_theme ) {
      ?>
      <div class="notice notice-success is-dismissible">
          <p><strong><?php
          printf( __( 'Hey, we also have the <a href="%s">Liquido Admin theme</a> as well. Import the Liquido admin theme color set for the best possible experience.', 'wps' ), esc_url( admin_url( 'admin.php?page=wps_themes' ) ) );
          ?></strong></p>
      </div>
    <?php
    }
  }

  function wps_load_textdomain()
  {
    load_plugin_textdomain('wps', false, dirname( plugin_basename( __FILE__ ) )  . '/languages' );
  }

  /* custom login page title */
  function login_page_title() {
    if(!empty($this->aof_options['login_page_title'])) {
      return $this->aof_options['login_page_title'];
    }
    else return get_bloginfo('name');
  }

  /*
  * function to determine multi customization is enabled
  */
	public function is_wps_single() {
	    if(!is_multisite())
		return true;
	    elseif(is_multisite() && !defined('NETWORK_ADMIN_CONTROL'))
		return true;
	    else return false;
	}

	public function initFunctionss(){
		if($this->aof_options['disable_auto_updates'] == 1) {
			add_filter( 'automatic_updater_disabled', '__return_true' );
      add_filter( 'auto_update_plugin', '__return_false' );
      add_filter( 'auto_update_theme', '__return_false' );
    }

		if($this->aof_options['disable_update_emails'] == 1)
			add_filter( 'auto_core_update_send_email', '__return_false' );

		if($this->aof_options['email_settings'] != 3) {
			add_filter( 'wp_mail_from', array($this, 'custom_email_addr') );
			add_filter( 'wp_mail_from_name', array($this, 'custom_email_name') );
		}

		if($this->aof_options['hide_profile_color_picker'] == 1) {
			remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
		}
		register_nav_menus(array(
			'wpshapere_adminbar_menu' => 'Adminbar Menu'
		));
	}

	public function wpshapereloginAssets()
	{
		wp_enqueue_script("jquery");
    if(isset($_GET['action']) && $_GET['action'] == 'rp'){}
    else {
      wp_enqueue_script( 'wps-loginjs', WPSHAPERE_DIR_URI . 'assets/js/wps-loginjs.js', array( 'jquery' ), '', true );
    }
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'fontawesome', WPSHAPERE_DIR_URI . 'assets/fontawesome5/css/fontawesome.min.css', '', WPSHAPERE_VERSION );
    wp_enqueue_style( 'lineIcons', WPSHAPERE_DIR_URI . 'assets/lineicons/lineicons.min.css', '', WPSHAPERE_VERSION );
	}
	public function wpshapereAssets($nowpage)
	{
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'fontawesome', WPSHAPERE_DIR_URI . 'assets/fontawesome5/css/fontawesome.min.css', '', WPSHAPERE_VERSION );
    wp_enqueue_style( 'lineIcons', WPSHAPERE_DIR_URI . 'assets/lineicons/lineicons.min.css', '', WPSHAPERE_VERSION );
    if( $nowpage == 'toplevel_page_wpshapere-options' ) {
      wp_enqueue_script( 'wps-livepreview', WPSHAPERE_DIR_URI . 'assets/js/wps-live-preview.min.js', array( 'jquery' ), '', true );
    }
	}

	public function wpshapeLogincss()
	{
    include_once( WPSHAPERE_PATH . 'assets/css/wpshapere.login.css.php');
	}

	public function wpshapeOptionscss()
	{
    if( is_plugin_active( 'wpspowerbox/wpspowerbox.php' ) && $this->is_user_has_custom_menu() == true ) {
    	$this->wps_admin_css();
    }
    if( isset($this->aof_options['disable_admin_theme']) && $this->aof_options['disable_admin_theme'] == 1 )
      return;
    $this->wps_admin_css();
	}

  function wps_admin_css(){
    include_once( WPSHAPERE_PATH . 'assets/css/wpshapere.css.php');
  }

  function get_admin_users() {

    if( ( isset($_POST) && isset($_POST['aof_options_save']) ) || ( isset( $_GET['status'] ) && "license_success" == $_GET['status'] ) ) {

      $admin_users = array();
      $admin_user_query = null;

      if ( is_multisite() ) {
        $admin_user_query = get_super_admins();
      }
      if(empty($admin_user_query) && !is_array($admin_user_query)) {
        $admin_user_query = new WP_User_Query( array( 'role' => 'Administrator' ) );
      }
      if(empty($admin_user_query) && !is_array($admin_user_query)) {
        $admin_user_query = new WP_User_Query( array( 'meta_key' => 'wp_user_level', 'meta_value' => '10' ) );
      }

      if ( is_multisite() ) {

        if(!empty($admin_user_query) && is_array($admin_user_query)) {
          foreach ($admin_user_query as $admin_user_name) {
            $admin_user_id = get_user_by('login', $admin_user_name);
            $admin_user_id = $admin_user_id->ID;
            $admin_users[$admin_user_id] = $admin_user_name;
          }
        }

      }
      else {

        foreach ($admin_user_query->results as $admin_data) {
          if(!empty($admin_data->data->display_name)) {
            $user_display_name = $admin_data->data->display_name;
          }
          else {
            $user_display_name = $admin_data->data->user_login;
          }
          $admin_users[$admin_data->ID] = $user_display_name;
        }

      }

      if(!empty($admin_users)) {
        $this->updateOption(WPS_ADMIN_USERS_SLUG, $admin_users);
      }
    }
  }

  /**
  * get admin users from WPS options
  * @since 2.1.20
  * @return bool
  */
  function wps_has_admin_users() {

    //get all admin users
    $admin_users_array = ( is_serialized( get_option( WPS_ADMIN_USERS_SLUG ) ) ) ? unserialize( get_option ( WPS_ADMIN_USERS_SLUG ) ) : get_option( WPS_ADMIN_USERS_SLUG );

    if( empty( $admin_users_array ) && !is_array( $admin_users_array ) )
      return true;
    else
      return false;

  }

	public function generalFns() {
    //get current user ID
    $current_user_id = get_current_user_id();

    //get menu access data
    $screen_meta_access = ( !empty($this->aof_options['show_screen_meta_to_admin'] ) ) ? $this->aof_options['show_screen_meta_to_admin'] : '';

    //get privilege users
    $wps_privilege_users = $this->get_privilege_users();

    $this->wps_menu_user_avatar();

    //remove wp version
    add_filter( 'update_footer', array($this, 'wpsremoveVersion'), 99);

    //add video responsive styles
    add_action('admin_head', array($this, 'wps_video_frame_css'), 999);

    //prevent access to wpshapere menu for non-superadmin
    if( (!current_user_can('manage_network')) && defined('NETWORK_ADMIN_CONTROL') ){
      if(isset($screen->id)) {
    		if($screen->id == "toplevel_page_wpshapere-options" || $screen->id == "wpshapere-options_page_wps_admin_menuorder" || $screen->id == "wpshapere-options_page_wps_impexp_settings") {
    		    wp_die("<div style='width:70%; margin: 30px auto; padding:30px; background:#fff'><h4>Sorry, you don't have sufficient previlege to access to this page!</h4></div>");
    		    exit();
    		}
      }
    }

    //to whom screen meta links and admin notices to hide
    if(is_super_admin($current_user_id) && isset($screen_meta_access) && $screen_meta_access == 1){
      return;
    }
    elseif(is_super_admin($current_user_id) && isset($screen_meta_access) && $screen_meta_access == 2 && !empty($wps_privilege_users) && in_array($current_user_id, $wps_privilege_users)) {
      return;
    }

    $screen = get_current_screen();
    $admin_general_options_data = ( !empty($this->aof_options['admin_generaloptions']) ) ? $this->aof_options['admin_generaloptions'] : "";
    $admin_generaloptions = (is_serialized( $admin_general_options_data )) ? unserialize( $admin_general_options_data ) : $admin_general_options_data;
    if(!empty($admin_generaloptions)) {
      foreach($admin_generaloptions as $general_opt) {
        if(isset($screen) && $general_opt == 1) {
                $screen->remove_help_tabs();
        }
        elseif($general_opt == 2) {
                add_filter('screen_options_show_screen', '__return_false');
        }
        elseif($general_opt == 3) {
                remove_action('admin_notices', 'update_nag', 3);
        }
        elseif($general_opt == 4) {
                remove_submenu_page('index.php', 'update-core.php');
        }
      }
    }

    if( isset( $this->aof_options['hide_adminbar_backend'] ) && 1 == $this->aof_options['hide_adminbar_backend'] ) {
    ?>
    <script>
      jQuery(document).ready(function() {
        jQuery('#wpadminbar').hide();
      });
      </script>
<?php
    }
	?>

		<?php
	}

  function wps_menu_user_avatar() {

    if( empty($this->aof_options['enable_menu_user_info']) || ( isset( $this->aof_options['disable_admin_theme'] ) && 1 == $this->aof_options['disable_admin_theme'] ) )
      return;

    //get current user data
    $current_user = wp_get_current_user();

    //if no user ID exists return
    if ( ! $current_user->ID )
			return;

      $avatar_img = '<img src="' . get_avatar_url( $current_user->ID ) . '">';
      $user_displayname = $current_user->display_name;
      $user_profile = '<a href="'. admin_url( 'profile.php' ) .'"><i class="wps-user"></i></a>';
      $logout_icon = '<a class="wps-menu-logout" href="' . wp_logout_url() .'"><i class="wps-power-switch"></i></a>';
      $user_profile_data = '<div class="wps-user-avatar">' . $avatar_img . '</div>';
      $user_profile_data .= '<div class="wps-user-displayname">' . $user_displayname . '</div>';
      $user_profile_data .= '<div class="wps-menu-user-actions">' . $user_profile . $logout_icon . '</div>';
?>
    <script>
    jQuery(document).ready(function($) {
      "use strict";
      jQuery('<?php echo $user_profile_data; ?>').insertBefore("#adminmenu");
    });
    </script>
<?php
  }

	public function custom_admin_title($admin_title, $title)
	{
	    return $title . " &#45; " . get_bloginfo('name');
	}

	public function custom_email_addr($email){
		if($this->aof_options['email_settings'] == 1)
			return get_option('admin_email');
		else return $this->aof_options['email_from_addr'];
	}

	public function custom_email_name($name){
		if($this->aof_options['email_settings'] == 1)
			return get_option('blogname');
		else return $this->aof_options['email_from_name'];
	}

	public function wps_sub_menus()
	{
    //Remove wpshapere menu
    if( defined('HIDE_WPSHAPERE_OPTION_LINK') || (!current_user_can('manage_network')) && defined('NETWORK_ADMIN_CONTROL') )
	    remove_menu_page('wpshapere-options');
	}

	function removeSubmenuitem($item ='' )
  {
      global $submenu;
      if(!empty($subitems)) {
          foreach ($submenu as $key => &$value) {
              if (is_array($value)) {
                  foreach ($value as $subkey => $subvalue) {
                      if ($subvalue[2] == $item) {
                          unset($submenu[$key][$subkey]);
                      }
                  }
              }
          }
      }
  }

  function initialize_dash_widgets() {
      global $wp_meta_boxes;

      $context = array("normal","side","advanced");
      $priority = array("high","low","default","core");

      $wps_widgets_list = $wp_meta_boxes['dashboard'];
      $wps_get_dash_Widgets = array();
      if (!is_array($wps_widgets_list['normal']['core'])) {
          $wps_widgets_list = array('normal'=>array('core'=>array()), 'side'=>array('core'=>array()),'advanced'=>array('core'=>array()));
      }
      foreach ($context as $context_value)
      {
          foreach ($priority as $priority_value)
          {
              if(isset($wps_widgets_list[$context_value][$priority_value]) && is_array($wps_widgets_list[$context_value][$priority_value]))
              {
                  foreach ($wps_widgets_list[$context_value][$priority_value] as $key => $data) {
                      $key = $key . "|" . $context_value . "|" . $priority_value;
                      if( is_array( $data['title'] )  )
                        $data['title'] = $data['title'][0];
                      $widget_title = preg_replace("/Configure/", "", strip_tags($data['title']));
                      $wps_get_dash_Widgets[] = array($key, $widget_title);
                  }
              }
          }
      }

      $this->updateOption('wps_widgets_list', $wps_get_dash_Widgets);

  }

	function customizephpFix($url) {
      if(preg_match('/customize.php?/', $url) && preg_match('/autofocus/', $url)) {
          $url_decode = explode('autofocus[control]=', rawurldecode($url));
          return $url_decode[1];
      }
      elseif(preg_match('/customize.php?/', $url)) {
        return 'customize.php';
      }
      else return $url;
  }

	function login_footer_content()
	{
    $login_footer_content = $this->aof_options['login_footer_content'];
    echo '<div class="login_footer_content">';
    if(!empty($login_footer_content)) {
        echo do_shortcode ($this->aof_options['login_footer_content']);
    }
    echo '</div>';
	}

  function wps_footer_content( $text ) {
    return '<p class="wps-footer-content">' . do_shortcode( $this->aof_options['admin_footer_txt'] ) . '</p>';
  }

	function wpsremoveVersion()
	{
		return '';
	}

  function wps_save_adminbar_nodes() {
    global $wp_admin_bar;
    if ( !is_object( $wp_admin_bar ) )
        return;

    $all_nodes = $wp_admin_bar->get_nodes(); //echo '<pre>'; print_r($all_nodes); echo '</pre>';
    $adminbar_nodes = array();
    foreach( $all_nodes as $node )
    {
      $node_id = (!empty($node->id)) ? strip_tags($node->id) : '';
      if(empty($node_id))
       continue;

      if(!empty($node->parent)) {
        $node_data = strip_tags($node_id) . " <strong>(Parent: " . strip_tags($node->parent) . ")</strong>";
      }
      else {
        $node_data = strip_tags($node_id);
      }
      $adminbar_nodes[$node_id] = $node_data;
    }

    $data = array();
    $saved_data = get_option(WPS_ADMINBAR_LIST_SLUG);
    if($saved_data){
        $data = array_merge($saved_data, $adminbar_nodes);
    }else{
        $data = $adminbar_nodes;
    }

    $this->updateOption(WPS_ADMINBAR_LIST_SLUG, $data);
  }

  function remove_admin_notices() {
    remove_action('admin_notices', 'update_nag', 3);
  }

  /**
  * admin bar customization
  * @since 4.9 admin bar customization method rewritten
  * @return null
  */
	function wps_remove_bar_links()
	{
    global $wp_admin_bar;
    $current_user_id = get_current_user_id();
    $wps_menu_access = $this->aof_options['show_all_menu_to_admin'];
    $wps_privilege_users = $this->get_privilege_users();

    if(is_super_admin($current_user_id) && isset($wps_menu_access) && $wps_menu_access == 1){
      return;
    }
    elseif(is_super_admin($current_user_id) && isset($wps_menu_access) && $wps_menu_access == 2 && !empty($wps_privilege_users) && in_array($current_user_id, $wps_privilege_users)) {
        return;
    }
    elseif(isset($this->aof_options['hide_admin_bar_menus']) && !empty($this->aof_options['hide_admin_bar_menus'])) {
      foreach ($this->aof_options['hide_admin_bar_menus'] as $hide_bar_menu) {
              $wp_admin_bar->remove_menu($hide_bar_menu);
      }
    }

	}

	function add_wpshapere_menus($wp_admin_bar) {
    $admin_logo_url = (!empty($this->aof_options['adminbar_logo_link'])) ? $this->aof_options['adminbar_logo_link'] : admin_url();

		if(!empty($this->aof_options['admin_logo']) || !empty($this->aof_options['adminbar_external_logo_url'])) {
			$wp_admin_bar->add_node( array(
				'id'    => 'wpshapere_site_title',
				'href'  => $admin_logo_url,
				'meta'  => array( 'class' => 'wpshapere_site_title' )
			) );
		}
    if(!empty($this->aof_options['collapsed_admin_logo']) || !empty($this->aof_options['collapsed_adminbar_ext_logo_url'])) {
			$wp_admin_bar->add_node( array(
				'id'    => 'wpshapere_clpsd_site_logo',
				'href'  => $admin_logo_url,
				'meta'  => array( 'class' => 'wps-collapsed-logo' )
			) );
		}
	}

	function add_wpshapere_nav_menus($wp_admin_bar)
	{
		//add Nav items to adminbar
		if( ( $locations = get_nav_menu_locations() ) && isset( $locations[ 'wpshapere_adminbar_menu' ] ) ) {

			$custom_nav_object = wp_get_nav_menu_object( $locations[ 'wpshapere_adminbar_menu' ] );
			if(!isset($custom_nav_object->term_id))
				return;
			$menu_items = wp_get_nav_menu_items( $custom_nav_object->term_id );

			foreach( (array) $menu_items as $key => $menu_item ) {
				if( $menu_item->classes ) {
					$classes = implode( ' ', $menu_item->classes );
				} else {
					$classes = "";
				}
				$meta = array(
					'class' 	=> $classes,
					'target' 	=> $menu_item->target,
					'title' 	=> $menu_item->attr_title
				);
				if( $menu_item->menu_item_parent ) {
					$wp_admin_bar->add_node(
						array(
						'parent' 	=> $menu_item->menu_item_parent,
						'id' 		=> $menu_item->ID,
						'title' 	=> $menu_item->title,
						'href' 		=> $menu_item->url,
						'meta' 		=> $meta
						)
					);
				} else {
					$wp_admin_bar->add_node(
						array(
						'id' 		=> $menu_item->ID,
						'title' 	=> $menu_item->title,
						'href' 		=> $menu_item->url,
						'meta' 		=> $meta
						)
					);
				}
			} //foreach
		}
	}

	public function update_avatar_size( $wp_admin_bar ) {

		//update avatar size
		$current_user = wp_get_current_user();
		if ( ! $current_user->ID )
			return;
		$avatar = get_avatar( $current_user->ID, 36 );
    $welcome_text = (!empty($this->aof_options['adminbar_custom_welcome_text'])) ? $this->aof_options['adminbar_custom_welcome_text'] . ", " . $current_user->display_name :
    sprintf( esc_html__('Howdy, %1$s'), $current_user->display_name );
		$account_node = $wp_admin_bar->get_node( 'my-account' );
		$title = $welcome_text . $avatar;
		$wp_admin_bar->add_node( array(
			'id' => 'my-account',
			'title' => $title
			) );

	}

  /**
  * check whether the user has custom menu
  * @since 6.1.3
  */
  function is_user_has_custom_menu( $menu_data = false ) {

    if( ! is_plugin_active( 'wpspowerbox/wpspowerbox.php' ) ) {
    	return false;
    }

    global $wpdb;
    $current_user_role = $this->wps_get_user_role();
    $current_user_id = get_current_user_id();

    $wps_privilege_users = ( !empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();

    if( !empty( $wps_privilege_users ) && in_array( $current_user_id, $wps_privilege_users ) )
      return false;

    //get menu data
    $wps_menu_data = '';
	  if( is_multisite() )
	  {
  		$wpspb_base_prefix = $wpdb->get_blog_prefix( get_main_site_id() );
  		$wps_menu_table = $wpspb_base_prefix.'wps_custom_menu';
  		$blog_id = get_current_blog_id();
  		$wps_menu_data = $wpdb->get_row( "SELECT * FROM " . $wps_menu_table . " WHERE menu_role_id='" . $current_user_id . "' AND status = 'publish' And blog_id = ".$blog_id );
  	    if ( empty( $wps_menu_data )) {
  		  $wps_menu_data = $wpdb->get_row( "SELECT * FROM " . $wps_menu_table . " WHERE menu_role_id='" . $current_user_role . "' AND status = 'publish' And blog_id = ".$blog_id );
  	    }
	  }
	  else
	  {
  		$wps_menu_data = $wpdb->get_row( "SELECT * FROM " . POWERBOX_MENU_TABLE . " WHERE menu_role_id='" . $current_user_id . "' AND status = 'publish'" );
  		if ( empty( $wps_menu_data )) {
  		  $wps_menu_data = $wpdb->get_row( "SELECT * FROM " . POWERBOX_MENU_TABLE . " WHERE menu_role_id='" . $current_user_role . "' AND status = 'publish'" );
  		}
	  }

    if ( !empty( $wps_menu_data ) && $menu_data == true )
      return $wps_menu_data;
    elseif ( !empty( $wps_menu_data ) )
      return true;
    else return false;

  }

	public function get_wps_option_data( $option_id ) {
    if($this->is_wps_single()) {
        $get_wps_option_data = (is_serialized(get_option($option_id))) ? unserialize(get_option($option_id)) : get_option($option_id);
     }
    else {
        $get_wps_option_data = (is_serialized(get_site_option($option_id))) ? unserialize(get_site_option($option_id)) : get_site_option($option_id);
    }
    return $get_wps_option_data;
	}

	function get_wps_image_url($imgid, $size='full') {
    global $switched, $wpdb;

    if ( is_numeric( $imgid ) ) {
  		if(!$this->is_wps_single()) {
          switch_to_blog(1);
          $imageAttachment = wp_get_attachment_image_src( $imgid, $size );
          restore_current_blog();
      }
      else $imageAttachment = wp_get_attachment_image_src( $imgid, $size );
  		return $imageAttachment[0];
    }
	}

  function wps_get_user_role() {
      global $current_user;
      $get_user_roles = $current_user->roles;
      $get_user_role = array_shift($get_user_roles);
      return $get_user_role;
  }

  function wps_get_wproles() {
      global $wp_roles;
      if ( ! isset( $wp_roles ) ) {
          $wp_roles = new WP_Roles();
      }
      return $wp_roles->get_names();
  }

/* get roles by blog based @since 6.1.4 */
function wps_get_wproles_blog( $blog_id ) {

  $switched = is_multisite() ? switch_to_blog( $blog_id ) : false;
  $wp_roles = new WP_Roles();
  $roles = $wp_roles->get_names();
  if ( $switched ) {
    restore_current_blog();
  }
  return $roles;

}

  function wps_array_merge()
  {
      $output = array();
      foreach(func_get_args() as $array) {
          foreach($array as $key => $value) {
              $output[$key] = isset($output[$key]) ?
                  array_merge($output[$key], $value) : $value;
          }
      }
      return $output;
  }

  //fn to save options
  public function updateOption($option='', $data='') {
      if(empty($option)) {
        $option = WPSHAPERE_OPTIONS_SLUG;
      }
      if(isset($data) && !empty($data)) {
        if($this->is_wps_single())
          update_option($option, $data);
        else
          update_site_option($option, $data);
      }
  }

	function wpshapere_login_url()
	{
		$login_logo_url = $this->aof_options['login_logo_url'];
		if(empty($login_logo_url))
			return site_url();
		else return $login_logo_url;
	}

	function wpshapere_login_title()
	{
		return get_bloginfo('name');
	}

  function wps_clean_slug($slug) {
      $clean_slug = trim(preg_replace("/[^a-zA-Z0-9]+/", "", $slug));
      return $clean_slug;
  }

  function clean_title($title) {
    $clean_title = trim(preg_replace('/[0-9]+/', '', $title));
    return $clean_title;
  }

  function wps_get_file_url_ext($url) {
      $ext = parse_url($url, PHP_URL_PATH);
      if (strpos($ext,'.') !== false) {
          $basename = explode('.', basename($ext));
          return $basename[1];
      }
  }

  function wps_get_domain_name($url) {
      $parse_url = parse_url($url);
      $hostname = explode('.', $parse_url['host']);
      return $hostname;
  }

  function wps_rand_key($length) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    $size = strlen( $chars );
    $str = "";
    for( $i = 0; $i < $length; $i++ ) {
            $str .= $chars[ rand( 0, $size - 1 ) ];
    }

    return $str;
  }

    function wps_get_icon_class($iconData) {
        if(!empty($iconData)) {
            $icon_class = explode('|', $iconData);
            if(isset($icon_class[0]) && isset($icon_class[1])) {
                return $icon_class[0] . ' ' . $icon_class[1];
            }
        }
    }

    public function wps_compress_css($css) {
      $cssContents = "";
      // Remove comments
      $cssContents = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
      // Remove space after colons
      $cssContents = str_replace(': ', ':', $cssContents);
      // Remove whitespace
      $cssContents = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $cssContents);
      return $cssContents;
    }

    function get_privilege_users() {
      //return WPS privilege users
      $wps_privilege_users = (!empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();
      return $wps_privilege_users;
    }

  function manage_dash_widgets() {
      if(!isset($this->aof_options['remove_dash_widgets']))
          return;

      global $wp_meta_boxes;
      $dash_widgets_removal_data = $this->aof_options['remove_dash_widgets'];
      $remove_dash_widgets = (is_serialized($dash_widgets_removal_data)) ? unserialize($dash_widgets_removal_data) : $dash_widgets_removal_data;

      //Removing unwanted widgets
      if(!empty($remove_dash_widgets) && is_array($remove_dash_widgets)) {
          foreach ($remove_dash_widgets as $widget_to_rm) {
              if($widget_to_rm == "welcome_panel") {
                  remove_action('welcome_panel', 'wp_welcome_panel');
              }
              else {
                  $widget_data = explode("|", $widget_to_rm);
                  $widget_id = $widget_data[0];
                  $widget_pos = $widget_data[1];
                  unset($wp_meta_boxes['dashboard'][$widget_pos]['core'][$widget_id]);
              }
          }
      }
  }

  function manage_widget_functions() {

    $current_user_id = get_current_user_id();
    $wps_widgets_list_access = (isset($this->aof_options['show_all_widgets_to_admin'])) ? $this->aof_options['show_all_widgets_to_admin'] : "";
    $wps_privilege_users = (!empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();

    global $wp_meta_boxes;
    $dash_widgets_removal_data = (isset($this->aof_options['remove_dash_widgets'])) ? $this->aof_options['remove_dash_widgets'] : "";
    $remove_dash_widgets = (is_serialized($dash_widgets_removal_data)) ? unserialize($dash_widgets_removal_data) : $dash_widgets_removal_data;

    //Removing unwanted widgets
    if(!empty($remove_dash_widgets) && is_array($remove_dash_widgets)) {

      if(is_super_admin($current_user_id) && isset($wps_widgets_list_access) && $wps_widgets_list_access == 1){
        return;
      }
      elseif(is_super_admin($current_user_id) && isset($wps_widgets_list_access) && $wps_widgets_list_access == 2 && !empty($wps_privilege_users) && in_array($current_user_id, $wps_privilege_users)) {
        return;
      }
      else {
          foreach ($remove_dash_widgets as $widget_to_rm) {
              if($widget_to_rm == "welcome_panel") {
                  remove_action('welcome_panel', 'wp_welcome_panel');
              }
              else {
                  $widget_data = explode("|", $widget_to_rm);
                  $widget_id = $widget_data[0];
                  $widget_pos = $widget_data[1];
                  $widget_priority = $widget_data[2];
                  unset($wp_meta_boxes['dashboard'][$widget_pos][$widget_priority][$widget_id]);
              }
          }
        }
    }

  }

  function add_dash_widgets() {
    //Creating new widgets

    $n_widgets = 4;

    $n_widgets = apply_filters('wps_dash_widgets_number', $n_widgets);

    $wps_widget_handle = array();
    for($i = 1; $i <= $n_widgets; $i++) {
      $wps_widget_handle['type'] = $this->aof_options[ 'wps_widget_' . $i .'_type' ];
      $wps_widget_handle['pos'] = $this->aof_options[ 'wps_widget_' . $i .'_position' ];
      $wps_widget_handle['title'] = $this->aof_options[ 'wps_widget_' . $i .'_title' ];

      $wps_widget_content = wpautop($this->aof_options[ 'wps_widget_' . $i .'_content'], true);

      $wps_widget_handle['content'] = do_shortcode($wps_widget_content);
      $wps_widget_handle['rss'] = $this->aof_options[ 'wps_widget_' . $i .'_rss' ];
      if(!empty($wps_widget_handle['title']) || !empty($wps_widget_handle['content']) || !empty($wps_widget_handle['rss'])) {
        add_meta_box('wps_dashwidget_' . $i, $wps_widget_handle['title'], array($this, 'wps_display_widget_content'), 'dashboard', $wps_widget_handle['pos'], 'high', $wps_widget_handle);
      }
    }
  }

  public function wps_display_widget_content($post, $wps_widget_handle)
  {
  	if($wps_widget_handle['args']['type'] == 1) {
  		echo '<div class="rss-widget">';
  		 wp_widget_rss_output(array(
  			  'url' => $wps_widget_handle['args']['rss'],
  			  'items' => 5,
  			  'show_summary' => 1,
  			  'show_author' => 1,
  			  'show_date' => 1
  		 ));
  		 echo "</div>";
  	}
  	else {
  		echo $wps_widget_handle['args']['content'];
  	}
  }

function wps_video_frame_css()
{
  $n_widgets = 4;
  $n_widgets = apply_filters('wps_dash_widgets_number', $n_widgets);

  for($i = 1; $i <= $n_widgets; $i++) {
    if(isset($this->aof_options['wps_widget_' . $i . '_type']) && $this->aof_options['wps_widget_' . $i . '_type'] == 3) {
      echo '<style>#wps_dashwidget_' . $i . ' .inside{position:relative;padding-bottom:56.25%;padding-top:25px;height:0;margin-top:0}#wps_dashwidget_' . $i . ' .inside object,#wps_dashwidget_' . $i . ' .inside embed,#wps_dashwidget_' . $i . ' .inside iframe{position: absolute;top:0;left:0;width:100%;height:100%;}</style>';
    }
  }
}

	public function frontendActions()
	{
      $css_styles = '';

	    //remove admin bar
	    if($this->aof_options['hide_admin_bar'] == 1) {
        add_filter( 'show_admin_bar', '__return_false' );
        echo '<style type="text/css">html { margin-top: 0 !important; }</style>';
	    }
	    else {

      include_once( WPSHAPERE_PATH . 'assets/css/wps-front-css.php');

		   $css_styles .= '<style type="text/css">
      		#wpadminbar, #wpadminbar .menupop .ab-sub-wrapper { background: '. $this->aof_options['admin_bar_color'] . '}
      #wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon { color: '. $this->aof_options['admin_bar_menu_color'] . '}
      #wpadminbar .ab-top-menu>li>.ab-item:focus, #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar .ab-top-menu>li:hover>.ab-item,
      #wpadminbar .ab-top-menu>li.hover>.ab-item, #wpadminbar .quicklinks .menupop ul li a:focus, #wpadminbar .quicklinks .menupop ul li a:focus strong,
      #wpadminbar .quicklinks .menupop ul li a:hover, #wpadminbar-nojs .ab-top-menu>li.menupop:hover>.ab-item, #wpadminbar .ab-top-menu>li.menupop.hover>.ab-item,
      #wpadminbar .quicklinks .menupop ul li a:hover strong, #wpadminbar .quicklinks .menupop.hover ul li a:focus, #wpadminbar .quicklinks .menupop.hover ul li a:hover,
      #wpadminbar li .ab-item:focus:before, #wpadminbar li a:focus .ab-icon:before, #wpadminbar li.hover .ab-icon:before, #wpadminbar li.hover .ab-item:before,
      #wpadminbar li:hover #adminbarsearch:before, #wpadminbar li:hover .ab-icon:before, #wpadminbar li:hover .ab-item:before,
      #wpadminbar.nojs .quicklinks .menupop:hover ul li a:focus, #wpadminbar.nojs .quicklinks .menupop:hover ul li a:hover, #wpadminbar li:hover .ab-item:after,
      #wpadminbar>#wp-toolbar a:focus span.ab-label, #wpadminbar>#wp-toolbar li.hover span.ab-label, #wpadminbar>#wp-toolbar li:hover span.ab-label {
        color: '. $this->aof_options['admin_bar_menu_hover_color'] . '}

      .quicklinks li.wpshapere_site_title { width: 200px !important; }
      .quicklinks li.wpshapere_site_title a{ outline:none; border:none;';

        if(!empty($this->aof_options['adminbar_external_logo_url']) && filter_var($this->aof_options['adminbar_external_logo_url'], FILTER_VALIDATE_URL)) {
          $adminbar_logo = esc_url( $this->aof_options['adminbar_external_logo_url']);
        }
        else {
          $adminbar_logo = (is_numeric($this->aof_options['admin_logo'])) ? $this->get_wps_image_url($this->aof_options['admin_logo']) : $this->aof_options['admin_logo'];
        }

        if(!empty($adminbar_logo)){
          $css_styles .= '.quicklinks li.wpshapere_site_title a{
            background-image:url('. $adminbar_logo . ') !important;
          background-repeat: no-repeat !important;
          background-position: center center !important;
          background-size: 70% auto !important;
          text-indent:-9999px !important;
          width: auto !important}';
        }

        $css_styles .= '#wpadminbar .ab-top-menu>li>.ab-item:focus, #wpadminbar-nojs .ab-top-menu>li.menupop:hover>.ab-item,
        #wpadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus, #wpadminbar .ab-top-menu>li:hover>.ab-item,
        #wpadminbar .ab-top-menu>li.menupop.hover>.ab-item, #wpadminbar .ab-top-menu>li.hover>.ab-item { background: none }
        #wpadminbar .quicklinks .menupop ul li a, #wpadminbar .quicklinks .menupop ul li a strong, #wpadminbar .quicklinks .menupop.hover ul li a,
        #wpadminbar.nojs .quicklinks .menupop:hover ul li a { color:'. $this->aof_options['admin_bar_menu_color'] .'; font-size:13px !important }
        #wpadminbar .quicklinks li#wp-admin-bar-my-account.with-avatar>a img {
          width: 20px; height: 20px; border-radius: 100px; -moz-border-radius: 100px; -webkit-border-radius: 100px; 	border: none;
        }
      	</style>';

      }//end of else

  }

  public function hideupdateNotices() {
    echo '<style>.update-nag, .updated, .notice { display: none; }</style>';
  }

  }

}

$wpshapere = new WPSHAPERE();
