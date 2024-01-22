<?php
/*
 * WPSHAPERE
 * @author   AcmeeDesign
 * @url     http://acmeedesign.com
*/

defined('ABSPATH') || die;

if(!class_exists('WPS_CUSTOMIZEADMINMENU')) {
    class WPS_CUSTOMIZEADMINMENU extends WPSHAPERE {
        private $magic_priority = PHP_INT_MAX;
        private $custom_menu = array();
        private $custom_submenu = array();
        private $debug = 0;

        function __construct()
        {
            $this->aof_options = parent::get_wps_option_data( WPSHAPERE_OPTIONS_SLUG );
            add_action('admin_init', array($this, 'initialize_default_menu'), 9);
            add_action('admin_head', array($this, 'wps_load_fa_icons'), 998);
            add_action('admin_head', array($this, 'wps_load_lni_icons'), 998);
            add_action('admin_menu', array($this, 'add_admin_management_menu'));
            add_action('admin_enqueue_scripts', array($this, 'load_menu_assets'));
            add_action('plugins_loaded', array($this, 'save_menu_data'), 1);
            add_filter('parent_file', array($this, 'replace_wp_menu'));
        }

        function initialize_default_menu(){
            global $menu, $submenu;
            $this->wps_df_menu = $menu;
            $this->wps_df_submenu = $submenu;
        }

        public function add_admin_management_menu()
        {
            add_submenu_page( WPSHAPERE_MENU_SLUG , esc_html__('Manage Admin Menu', 'wps'), esc_html__('Manage Admin Menu', 'wps'), 'manage_options', 'admin_menu_management', array($this, 'wps_admin_menu_management') );
        }

        public function load_menu_assets($nowpage)
        {
          global $wps_pages_slugs;
          if($nowpage == 'wpshapere_page_admin_menu_management' || $nowpage == 'toplevel_page_wpshapere-options') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script( 'wps-sorting', WPSHAPERE_DIR_URI . 'assets/js/wps-sortjs.js', array( 'jquery' ), '', true );
            wp_enqueue_style( 'icon-picker-styles', WPSHAPERE_DIR_URI . 'assets/icon-picker/css/icon-picker.css', '', WPSHAPERE_VERSION);
            wp_enqueue_script( 'icon-pickerr', WPSHAPERE_DIR_URI . 'assets/icon-picker/js/icon-picker.min.js', array( 'jquery', ), '', true );
            wp_enqueue_script( 'jquerycookie', WPSHAPERE_DIR_URI . 'assets/js/jquery.cookie.min.js', array( 'jquery' ), '', true );
            wp_enqueue_script( 'cookie', WPSHAPERE_DIR_URI . 'assets/js/cookie.js', array( 'jquery' ), '', true );
          }
        }

        function save_menu_data() {

          if( isset( $_GET ) && !empty( $_GET['adaction'] ) && $_GET['adaction'] == 'never' ) {
            parent::updateOption( 'wps_ad_status', 'never' );
          }

          if(isset($_POST) && isset($_POST['custom_admin_menu'])) {

            $custom_menu_data = array();
            $saved_data = array();
            $custom_menu_data = $_POST;
            $saved_data = parent::get_wps_option_data( WPSHAPERE_OPTIONS_SLUG );
            if($saved_data)
                $data = array_merge( $saved_data, $custom_menu_data );
            else
                $data = $custom_menu_data;
            parent::updateOption( WPSHAPERE_OPTIONS_SLUG, $data );
            wp_safe_redirect( admin_url( 'admin.php?page=admin_menu_management' ) );
            exit();

          }

        }

        function order_menu_page($menu, $submenu)
        {
            $tmenu = $menu;
            $tsubmenu = $submenu;
            if (isset($this->aof_options['custom_admin_menu']['top_menu']) && !empty($this->aof_options['custom_admin_menu']['top_menu'])) {
                $current_user_role = parent::wps_get_user_role();
                $current_user_id = get_current_user_id();
                $wps_menu_access = $this->aof_options['show_all_menu_to_admin'];
                $wps_privilege_users = (!empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();
                $toporder = isset($this->aof_options['custom_admin_menu']['top_menu']) ? $this->aof_options['custom_admin_menu']['top_menu'] : "";
                $multiorder = isset($this->aof_options['custom_admin_menu']['sub_menu']) ? $this->aof_options['custom_admin_menu']['sub_menu'] : "";
                $topmenutitle = isset($this->aof_options['custom_admin_menu']['top_menu_title']) ? $this->aof_options['custom_admin_menu']['top_menu_title'] : "";
                $topmenuicon = isset($this->aof_options['custom_admin_menu']['menu_icon']) ? $this->aof_options['custom_admin_menu']['menu_icon'] : "";
                $submenutitle = isset($this->aof_options['custom_admin_menu']['sub_menu_title']) ? $this->aof_options['custom_admin_menu']['sub_menu_title'] : "";
                $topmenuhide = isset($this->aof_options['custom_admin_menu']['top_menu_hide']) ? $this->aof_options['custom_admin_menu']['top_menu_hide'] : array();
                $submenuhide = isset($this->aof_options['custom_admin_menu']['sub_menu_hide']) ? $this->aof_options['custom_admin_menu']['sub_menu_hide'] : array();

                // top menu custom order sort
                $menuorder = $this->cleanArray($toporder);
                array_push($menuorder, 'profile.php');

                $debug = $this->debug;

                usort($menu, function ($a, $b) use ($menuorder, $debug){
                    $pos_a = array_search(esc_html(html_entity_decode($a['2'])), $menuorder);
                    $pos_b = array_search(esc_html(html_entity_decode($b['2'])), $menuorder);

                    if($pos_a === false || $pos_b === false) {
                        //$out = self::getSubmenuParentSlug('edit-comments.php', $tmenu, $tsubmenu);
                        if ($debug) {
                            var_dump($a['2'].'----->'.$b['2'].'---->menu');
                            var_dump(esc_html(html_entity_decode($a['2'])). '----->'.esc_html(html_entity_decode($b['2'])));
                            var_dump($pos_a.'---->'.$pos_b);
                        }
                    }
                    return $pos_a - $pos_b;
                });


                // Top menu loop
                foreach($menu as $key => &$value) { //echo '<pre>'; print_r($menu); echo '</pre>';
                    // set custom title for top menu
                    if (isset($topmenutitle[$value[2]]) && !empty($topmenutitle[$value[2]])){
                        $value[0] = $topmenutitle[$value[2]];
                    }
                    // set custom icon for top menu
                    if ( (isset($topmenuicon[$value[2]]) && !empty($topmenuicon[$value[2]])) || (isset($value[2]) && $value[2] == "vc-welcome")
                    || (isset($value[2]) && $value[2] == "profile.php") ) {

                        $value[4] = str_replace('menu-icon-', 'wps-menu-icon-', $value[4]);
                        $value[4] = str_replace('toplevel_page', 'wps-icon-selected wps-toplevel_page', $value[4]);

                        //set icon for vc-welcome
                        if(isset($value[2]) && $value[2] == "vc-welcome") {
                          $iconType = explode("|", $topmenuicon['vc-general']);
                        }
                        elseif(isset($value[2]) && $value[2] == "profile.php") {
                          $iconType = explode("|", $topmenuicon['users.php']);
                        }
                        else
                          $iconType = explode("|", $topmenuicon[$value[2]]);
                        if( isset( $iconType[1] ) && $iconType[1] != "dashicons-blank" ) {
                            if($iconType[0] == "dashicons") {
                                $value[6] = trim($iconType[1]);
                            }
                            else {
                                $value[6] = "dashicons-" . $iconType[1];
                                $value[6] = "dashicons-" . $iconType[1];
                            }
                        }
                    }


                    /**
                    * clean url method for better managing admin menu items
                    * @since 6.1.3
                    */
                    if ( isset($this->aof_options['wps_manage_menu_slug_mthd']) && $this->aof_options['wps_manage_menu_slug_mthd'] == 2 ) {
                      $clean_url = true;
                    }
                    else
                      $clean_url = false;

                    if( $clean_url === true ) {
                      $topmenu_to_hide = $this->cleanURL( urldecode( $value[2] ) );
                    }
                    else {
                      $topmenu_to_hide = html_entity_decode($value[2]);
                    }

                    // hide top menus as per roles
                    if (isset($topmenuhide[$topmenu_to_hide]) && !empty($topmenuhide[$topmenu_to_hide])) {

                      if(is_super_admin($current_user_id)) {
                        if(isset($wps_menu_access) && $wps_menu_access == 2 && !empty($wps_privilege_users) && !in_array($current_user_id, $wps_privilege_users)
                        && array_key_exists($current_user_role, $topmenuhide[$topmenu_to_hide])) {
                          $this->hide_menu[$key] = $menu[$key];
                          unset($menu[$key]);
                        }
                        elseif(isset($wps_menu_access) && $wps_menu_access == 3 && !empty($wps_privilege_users) && array_key_exists($current_user_role, $topmenuhide[$topmenu_to_hide])) {
                          $this->hide_menu[$key] = $menu[$key];
                          unset($menu[$key]);
                        }
                      }
                      elseif (array_key_exists($current_user_role, $topmenuhide[$topmenu_to_hide])) {
                          $this->hide_menu[$key] = $menu[$key];
                          unset($menu[$key]);
                      }


                    }

                    //fix for remove vc menu
                    if(isset($value[2]) && $value[2] == "vc-welcome") { //if top menu slug is vcwelcome, definitely it's not an administrator user
                      $if_vc_general_hidden = isset($this->aof_options['custom_admin_menu']['top_menu_hide']['vc-general']) ?
                          $this->aof_options['custom_admin_menu']['top_menu_hide']['vc-general'] : array();
                          if(!empty($if_vc_general_hidden) && array_key_exists($current_user_role, $if_vc_general_hidden)) {
                            unset($menu[$key]);
                          }
                    }

                    //fix for remove profile.php
                    if(isset($value[2]) && $value[2] == "profile.php") { //if top menu slug is profile.php, definitely it's not an administrator user
                      $if_profile_hidden = isset($this->aof_options['custom_admin_menu']['sub_menu_hide']['users.php']['profile.php']) ?
                          $this->aof_options['custom_admin_menu']['sub_menu_hide']['users.php']['profile.php'] : array();
                          if(!empty($if_profile_hidden) && array_key_exists($current_user_role, $if_profile_hidden)) {
                            unset($menu[$key]);
                          }
                    }

                    //fix for remove edit-tags.php?taxonomy=category
                    if(isset($value[2]) && $value[2] == "edit-tags.php?taxonomy=category") { //if top menu slug is edit-tags.php?taxonomy=category, definitely it's not an administrator user
                      $if_profile_hidden = isset($this->aof_options['custom_admin_menu']['sub_menu_hide']['edit.php']['edittagsphptaxonomycategory']) ?
                          $this->aof_options['custom_admin_menu']['sub_menu_hide']['edit.php']['edittagsphptaxonomycategory'] : array();
                          if(!empty($if_profile_hidden) && array_key_exists($current_user_role, $if_profile_hidden)) {
                            unset($menu[$key]);
                          }
                    }

                    //fix for removing customize.php as a parent menu
                    if(self::find_customize($value[2]) == true) {
                      $if_customize_hidden = isset($this->aof_options['custom_admin_menu']['sub_menu_hide']['themes.php']['customize.php']) ?
                          $this->aof_options['custom_admin_menu']['sub_menu_hide']['themes.php']['customize.php'] : array(); echo $if_customize_hidden;
                          if(!empty($if_customize_hidden) && array_key_exists($current_user_role, $if_customize_hidden)) {
                            unset($menu[$key]);
                          }
                    }



                    // sub menu custom order sort
                    if (isset($submenu[$value[2]]) && !empty($submenu[$value[2]]) ) {
                      if (isset($multiorder[$value[2]])) {
                           $sortorder = $this->cleanArray($multiorder[$value[2]]);
                           // submenu custom order sort
                           usort($submenu[$value[2]], function ($a, $b) use ($sortorder, $debug){
                               $pos_a = array_search(esc_html(html_entity_decode($a['2'])), $sortorder);
                               $pos_b = array_search(esc_html(html_entity_decode($b['2'])), $sortorder);

                               if($pos_a === false || $pos_b === false) {
                                    if ($debug) {
                                        var_dump($sortorder);
                                        var_dump($a['2'].'----->'.$b['2'].'---->submenu');
                                        var_dump(esc_html(html_entity_decode($a['2'])). '----->'.esc_html(html_entity_decode($b['2'])));
                                        var_dump($pos_a.'----->'.$pos_b);
                                    }
                               }
                               return $pos_a - $pos_b;
                           });
                           $sortorder = array();
                        }


                        foreach($submenu[$value[2]] as $sub_key => &$sub_value) { //echo '<pre>'; print_r($sub_value); echo '</pre>';
                          if( ! isset( $sub_value[2] ) )
                            continue;

                            if (isset($submenutitle[$value[2]][$sub_value['2']]) && !empty($submenutitle[$value[2]][$sub_value['2']])){
                                $sub_value[0] = $submenutitle[$value[2]][$sub_value['2']];
                            }

                            /**
                            * clean url method for better managing admin menu items
                            * @since 6.1.3
                            */
                            if ( isset($this->aof_options['wps_manage_menu_slug_mthd']) && $this->aof_options['wps_manage_menu_slug_mthd'] == 2 ) {
                              $clean_url = true;
                            }
                            else
                              $clean_url = false;

                            if( $clean_url === true ) {
                              $submenu_to_hide = $this->cleanURL( urldecode( $sub_value[2] ) );
                            }
                            else {
                              $submenu_to_hide = html_entity_decode($sub_value[2]);
                              if (preg_match('/\bcustomize\b/', $sub_value[2])) {
                                $submenu_to_hide = strtok($sub_value[2],'?');
                              }
                            }

                             //hiding sub menus
                            if( is_super_admin($current_user_id) ) {

                              if( isset($wps_menu_access) && $wps_menu_access == 3 &&
                               isset($submenuhide[$topmenu_to_hide][$submenu_to_hide]) && !empty($submenuhide[$topmenu_to_hide][$submenu_to_hide]) ) {
                                unset($submenu[$topmenu_to_hide][$sub_key]);
                              }
                              else if( isset($wps_menu_access) && $wps_menu_access == 2 && !empty($wps_privilege_users) && !in_array($current_user_id, $wps_privilege_users) &&
                               isset($submenuhide[$topmenu_to_hide][$submenu_to_hide]) && !empty($submenuhide[$topmenu_to_hide][$submenu_to_hide]) ) {
                                if(array_key_exists($current_user_role, $submenuhide[$topmenu_to_hide][$submenu_to_hide])) {
                                  $this->hide_submenu[$value[2]][$sub_key] = $submenu[$value[2]][$sub_key];
                                  unset($submenu[$value[2]][$sub_key]);
                                }
                              }

                           }
                            elseif (isset($submenuhide[$topmenu_to_hide][$submenu_to_hide]) && !empty($submenuhide[$topmenu_to_hide][$submenu_to_hide])) {

                                if (array_key_exists($current_user_role, $submenuhide[$topmenu_to_hide][$submenu_to_hide])) {
                                    $this->hide_submenu[$value[2]][$sub_key] = $submenu[$value[2]][$sub_key];
                                    unset($submenu[$value[2]][$sub_key]);
                                }

                            }

                        }
                    }
                }

                /**
                * Match hidden sub menu data with parent menu for hiding
                * @since 6.1.3
                */
                if( !is_super_admin( $current_user_id ) ) {
                  if( isset( $submenuhide ) && !empty( $submenuhide ) ) {
                    foreach ( $submenuhide as $parent_key => $submenu_slug_data ) {
                      foreach ($submenu_slug_data as $submenu_slug => $hidden_roles) {
                        if ( array_key_exists( $current_user_role, $hidden_roles ) ) {
                          foreach ($menu as $parent_key => &$parent_menu_data) {
                            if( $submenu_slug == $this->cleanURL( urldecode( $parent_menu_data[2] ) ) ) {
                              unset( $menu[$parent_key] );
                            }
                          }
                        }
                      }
                    }
                  }
                }

                /**  */

            }

            return array($menu, $submenu);
        }

        public function replace_wp_menu($parent_file = '')
        {
           //if(!empty($this->aof_options['disable_menu_customize']) && $this->aof_options['disable_menu_customize'] == 1)
            //  return;

            global $menu, $submenu,$submenu_file;
            if ($this->aof_options) {
               list($menu, $submenu) = $this->order_menu_page($menu, $submenu);
            }

            return $parent_file;
        }

        public function addMenuItem($menu, $submenu)
        {
            global $_registered_pages, $_wp_submenu_nopriv, $_wp_menu_nopriv, $submenu_file,$pagenow,$admin_page_hooks,$_parent_pages,$_wp_real_parent_file, $wp_filter;
            $rm_topmenu = array();

            if ($this->aof_options) {
                $toporder = isset($this->aof_options['custom_admin_menu']['top_menu']) ? $this->aof_options['custom_admin_menu']['top_menu'] : "";
                $multiorder = isset($this->aof_options['custom_admin_menu']['sub_menu']) ? $this->aof_options['custom_admin_menu']['sub_menu'] : "";
                $topmenutitle = isset($this->aof_options['custom_admin_menu']['top_menu_title'])? $this->aof_options['custom_admin_menu']['top_menu_title']: "";
                $topmenuicon = isset($this->aof_options['custom_admin_menu']['menu_icon']) ? $this->aof_options['custom_admin_menu']['menu_icon'] : "";
                $submenutitle = isset($this->aof_options['custom_admin_menu']['sub_menu_title']) ? $this->aof_options['custom_admin_menu']['sub_menu_title'] : "";
                $current_user_role = parent::wps_get_user_role();
                if (isset($toporder) && !empty($toporder)){
                    foreach($toporder as $key => $item) {
                        $current = self::istopmenu($item, $menu);
                        $subcurrent = (empty($current) ) ? self::getSubmenuParentSlug($item, $menu, $submenu) : "";

                        if ( current_user_can( $current[1] ) || current_user_can($subcurrent[1])){
                            unset($subcurrent);
                            if (empty($current)) {
                                $subcurrent = self::getSubmenuParentSlug($item, $menu, $submenu);
                                if(isset($subcurrent) && !empty($subcurrent)) {
                                    unset($submenu[$subcurrent[count($subcurrent)-1]][$subcurrent[count($subcurrent)-2]]);
                                    $menuicon = isset($topmenuicon[$item]) ? self::menuicon($topmenuicon[$item]) : self::menuicon('');
                                    $prev_hookname = get_plugin_page_hookname($subcurrent[2], $subcurrent[count($subcurrent)-1] );
                                    $admin_page_hooks[$item] = sanitize_title( $subcurrent[0] );
                                    $hookname = get_plugin_page_hookname($item, '');

                                    if (isset($wp_filter[$prev_hookname])) {
                                        $function = self::dump_hook($prev_hookname, $wp_filter[$prev_hookname]);
                                        $this->add_hook_function($hookname, $function, $item);
                                    }

                                    $custom_top_menu[] = array($subcurrent[0], $subcurrent[1], $item, $subcurrent[3], 'menu-top'. ' '.$hookname, $hookname, $menuicon);
                                    array_splice($menu, $key, 0, $custom_top_menu);
                                    $_registered_pages[$hookname] = true;
                                    $_parent_pages[$item] = false;
                                    unset($custom_top_menu);
                                }
                                if(!$current && !$subcurrent) {
                                    $menuicon = self::menuicon($topmenuicon[$item]);
                                    $menu_title = $topmenutitle[$item];
                                    $hookname = get_plugin_page_hookname($item, '');
                                    $custom_top_menu[] = array($menu_title, 'read', $item, $menu_title, 'menu-top'. ' '.$hookname, $hookname, $menuicon);
                                    array_splice($menu, $key, 0, $custom_top_menu);
                                    $_registered_pages[$hookname] = true;
                                    $_parent_pages[$item] = false;
                                    unset($custom_top_menu);
                                }
                                unset($subcurrent);
                            }
                            unset($current);

                            if (isset($multiorder[$item]) && !empty($multiorder[$item])) {
                                foreach($multiorder[$item] as $skey => $sitem) {
                                    $subcurrent = self::issubmenu($sitem, $item, $submenu);
                                    $titles = (empty($subcurrent)) ? self::istopmenu($sitem, $menu) : "";

                                    if ( current_user_can( $subcurrent[1] ) || current_user_can($titles[1])){
                                        unset($titles);
                                        if (empty($subcurrent)) {
                                            $_parent_pages[$sitem] = $item;
                                            $titles = self::istopmenu($sitem, $menu);
                                            if ($item != $sitem){


                                            if ($titles) {
                                                //unset($menu[$titles[count($titles)-1]]);
                                                if (!in_array($titles[2], $rm_topmenu)){
                                                    $rm_topmenu[$titles[count($titles)-1]] = $titles[2];
                                                }
                                                $custom_sub_menu[] = array($titles[0], $titles[1], $sitem);
                                                if(isset($submenu[$item]) && !empty($submenu[$item])) {
                                                    array_splice($submenu[$item], $skey, 0, $custom_sub_menu);
                                                } else {
                                                    $submenu[$item] =  $custom_sub_menu;
                                                }
                                                unset($custom_sub_menu);

                                                $hookname = get_plugin_page_hookname($sitem, $item);
                                                $_registered_pages[$hookname] = true;
                                                $prev_hookname = get_plugin_page_hookname( $sitem, $titles[2]);

                                                if (isset($wp_filter[$prev_hookname])) {
                                                    $function = self::dump_hook($prev_hookname,$wp_filter[$prev_hookname]);
                                                    $this->add_hook_function($hookname, $function, $sitem);
                                                }
                                            } else {
                                                $capa_titles = self::istopmenu($item, $menu);
                                                $anothersub = self::getSubmenuParentSlug($sitem, $menu, $submenu);

                                                if(isset($anothersub) && !empty($anothersub)) {
                                                    unset($submenu[$anothersub[count($anothersub)-1]][$anothersub[count($anothersub)-2]]);
                                                    $custom_sub_menu[] = array($anothersub[0], $capa_titles[1], $sitem);
                                                    if(isset($submenu[$item]) && !empty($submenu[$item])) {
                                                        array_splice($submenu[$item], $skey, 0, $custom_sub_menu);
                                                    } else {
                                                        $submenu[$item] =  $custom_sub_menu;
                                                    }

                                                    unset($custom_sub_menu);
                                                    $hookname = get_plugin_page_hookname( $sitem, $item );
                                                    $_registered_pages[$hookname] = true;
                                                    $prev_hookname = get_plugin_page_hookname( $sitem, $anothersub[count($anothersub)-1]);
                                                    if (isset($wp_filter[$prev_hookname])) {
                                                        $function = self::dump_hook($prev_hookname,$wp_filter[$prev_hookname]);
                                                        $this->add_hook_function($hookname, $function, $sitem);
                                                    }
                                                }
                                            }
                                        }
                                        }
                                        $ismenu = self::istopmenu($sitem, $menu);
                                        $issubmenu = self::getSubmenuParentSlug($sitem, $menu, $submenu);
                                        if (!$ismenu && !$issubmenu) {
                                            $menuicon = isset($topmenuicon[$item]) ? self::menuicon($topmenuicon[$item]) : self::menuicon('');
                                            $menu_title = $topmenutitle[$item];
                                            $hookname = get_plugin_page_hookname( $sitem, $item );
                                            $custom_sub_menu[] = array($menu_title, 'read', $sitem);
                                            if(isset($submenu[$item]) && !empty($submenu[$item])) {
                                                array_splice($submenu[$item], $skey, 0, $custom_sub_menu);
                                            } else {
                                                $submenu[$item] =  $custom_sub_menu;
                                            }
                                            $_registered_pages[$hookname] = true;
                                            $_parent_pages[$sitem] = $item;
                                            unset($custom_sub_menu);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }

            if(!empty($rm_topmenu)) {
                $menu = $this->removemenu($rm_topmenu, $menu);
            }

            return array($menu, $submenu);
        }

        function add_hook_function($hookname, $function, $item)
        {
            $isobject = false;
            if(isset($function) && !empty($function)){
                if(isset($function[1]) && !empty($function[1])){
                    $function_name = $function[1];
                    $isobject = true;
                } else {
                    $function_name = $function[0];
                }
            }
            if (strpos($item, '.php') === false) {
                if ($isobject)
                    add_action($hookname, array( $this, $function_name));
                else
                    add_action($hookname, $function_name);
            }
        }

        public function menuIcon($item)
        {
            $icon = 'dashicons-marker';
            if (isset($item) && !empty($item)){
                $iconType = explode("|", $item);
                if($iconType[1] != "dashicons-blank") {
                    if($iconType[0] == "dashicons") {
                        $icon = trim($iconType[1]);
                    }
                    else {
                        $icon = "dashicons-" . $iconType[1];
                    }
                }
            }
            return $icon;
        }

        function wps_fa_iconStyles(){
            if( class_exists('WPSFAICONS') ) {
                $wps_icon_data = (isset($this->aof_options['custom_admin_menu']['menu_icon']) && !empty($this->aof_options['custom_admin_menu']['menu_icon'])) ? $this->aof_options['custom_admin_menu']['menu_icon'] : array();
                $faicons = new WPSFAICONS();
                $faicons_data = $faicons->wps_fa_icons();
                $icon_styles = "";
                if(!empty($wps_icon_data)){
                  foreach($wps_icon_data as $wps_icon){
                      if(isset($wps_icon) && !empty($wps_icon)) {
                          $get_icon_type = explode("|", $wps_icon);
                          if($get_icon_type[0] == "fas") {
                              $icon_styles .= '#adminmenu li.menu-top .dashicons-' . $get_icon_type[1] . ':before {';
                              $icon_styles .= 'font-family: "Font Awesome 5 Free" !important; content: "' . $faicons_data[$get_icon_type[1]] . '" !important;font-weight:900;';
                              $icon_styles .= '} ';
                          }
                          elseif($get_icon_type[0] == "fab") {
                              $icon_styles .= '#adminmenu li.menu-top .dashicons-' . $get_icon_type[1] . ':before {';
                              $icon_styles .= 'font-family: "Font Awesome 5 Brands" !important; content: "' . $faicons_data[$get_icon_type[1]] . '" !important;font-weight:900;';
                              $icon_styles .= '} ';
                          }
                          elseif($get_icon_type[0] != "lni" && $get_icon_type[0] != "dashicons") {
                              $icon_styles .= '#adminmenu li.menu-top .dashicons-' . $get_icon_type[1] . ':before {';
                              $icon_styles .= 'font-family: "Font Awesome 5 Free" !important; content: "' . $faicons_data[$get_icon_type[1]] . '" !important;font-weight:900;';
                              $icon_styles .= '} ';
                          }
                      }

                  } //end of foreach
                }
                return $icon_styles;
            }
        }

        function wps_lni_iconStyles(){
            if(class_exists('WPS_LNIICONS')) {
                $wps_icon_data = (isset($this->aof_options['custom_admin_menu']['menu_icon']) && !empty($this->aof_options['custom_admin_menu']['menu_icon'])) ? $this->aof_options['custom_admin_menu']['menu_icon'] : array();
                $lniicons = new WPS_LNIICONS();
                $lniicons_data = $lniicons->wps_lni_icons();
                $icon_styles = "";
                if(!empty($wps_icon_data)){
                  foreach($wps_icon_data as $wps_icon){
                      if(isset($wps_icon) && !empty($wps_icon)) {
                          $get_icon_type = explode("|", $wps_icon);
                          if($get_icon_type[0] == "lni") {
                              $icon_styles .= '#adminmenu li.menu-top .dashicons-' . $get_icon_type[1] . ':before {';
                              $icon_styles .= 'font-family: LineIcons!important; content: "' . $lniicons_data[$get_icon_type[1]] . '" !important';
                              $icon_styles .= '} ';
                          }
                      }

                  } //end of foreach
                }
                return $icon_styles;
            }
        }

        function wps_load_fa_icons() {
          if($this->wps_fa_iconStyles()) {
            echo '<style type="text/css">';
            echo parent::wps_compress_css($this->wps_fa_iconStyles());
            echo '</style>';
          }
        }

        function wps_load_lni_icons() {
          if($this->wps_lni_iconStyles()) {
            echo '<style type="text/css">';
            echo parent::wps_compress_css($this->wps_lni_iconStyles());
            echo '</style>';
          }
        }

        public function wps_admin_menu_management()
        {
           global $menu, $submenu, $_parent_pages, $_registered_pages, $admin_page_hooks,$function,$aof_options;
            $topmenutitle = isset($this->aof_options['custom_admin_menu']['top_menu_title']) ? $this->aof_options['custom_admin_menu']['top_menu_title'] : array();
            $topmenuicon = isset($this->aof_options['custom_admin_menu']['menu_icon']) ? $this->aof_options['custom_admin_menu']['menu_icon'] : array();
            $submenutitle = isset($this->aof_options['custom_admin_menu']['sub_menu_title']) ? $this->aof_options['custom_admin_menu']['sub_menu_title'] : array();

            if(isset($this->hide_menu) && !empty($this->hide_menu)){
                foreach($this->hide_menu as $key => $row) {
                    array_splice($menu, $key, 0, array($row));
                }
                unset($key, $row);
            }

            if(isset($this->hide_submenu) && !empty($this->hide_submenu)){
                foreach($this->hide_submenu as $key => $row){
                    if(isset($submenu[$key]) && !empty($submenu[$key])) {
                        foreach($row as $subkey => $subrow){
                            array_splice($submenu[$key], $subkey, 0, array($subrow));
                        }
                        unset($subkey, $subrow);
                    }
                }
                unset($key, $row);
            }
        ?>
            <div class="wrap wps-wrap">
              <?php
              $current_user_role = parent::wps_get_user_role();
              $current_user_id = get_current_user_id();
              $wps_menu_access = $this->aof_options['show_all_menu_to_admin'];
              $wps_privilege_users = (!empty($this->aof_options['privilege_users'])) ? $this->aof_options['privilege_users'] : array();
              $wps_manage_menu_mthd = isset($this->aof_options['wps_manage_menu_slug_mthd']) ? $this->aof_options['wps_manage_menu_slug_mthd'] : 1;

              if( isset( $wps_menu_access ) && $wps_menu_access == 3 && !empty( $wps_privilege_users ) && !in_array( $current_user_id, $wps_privilege_users ) )
                { ?>
                  <div id="message" class="notice notice-error">
                    <h3><?php echo esc_html__("You don't have enough permission to access this page.", 'wps'); ?></h3>
                  </div>
                <?php
                }
              else {
               ?>
              <?php $aof_options->licenseValidate(); ?>
                <h1><?php echo esc_html__('Manage Admin Menu', 'wps'); ?></h1>
                <?php parent::wps_help_link(); ?>
                <?php echo sprintf( '<a target="_blank" href="%s" class="aof-quickvideo-btn">' . esc_html__( 'Quick Video Help', 'wps' ) . '</a>',  'https://youtu.be/P03B13DpmR8' ) ?>

              <div class="wps-adminmenu-customizer">
                <div id="message" class="updated below-h2"><p>
                <?php echo esc_html__('By default, all menu items will be shown to administrator users. ', 'wps');
                echo '<a href="' . admin_url() . 'admin.php?page='. WPSHAPERE_MENU_SLUG .'#aof_options_tab9"><strong>';
                echo esc_html__('Click here ', 'wps');
                echo '</strong></a>';
                echo esc_html__('to customize who can access to all menu items.', 'wps');
                ?>
                </p></div>

                <div class="manage_admin_menu_sorter">
                    <?php
                        $actual_menulabel = $actual_submenulabel = array();
                        if(isset($this->wps_df_menu) && !empty($this->wps_df_menu)){
                            foreach($this->wps_df_menu as $pmenu){
                                if(isset($pmenu[2]) && !empty($pmenu[2])){
                                    $pslug = $pmenu[2];
                                    $actual_menulabel[$pslug] = $pmenu[0];
                                    if (isset($this->wps_df_submenu[$pmenu[2]]) && !empty($this->wps_df_submenu[$pmenu[2]])){
                                        foreach($this->wps_df_submenu[$pmenu[2]] as $psubmenu){
                                            $actual_submenulabel[$psubmenu[2]] = $psubmenu[0];
                                        }
                                    }
                                }
                            }
                        }
                    ?>
                    <form name="alter_manage_admin_menu" method="post" action="<?php echo admin_url( 'admin.php' ); ?>?page=admin_menu_management">
                      <div class="wps-manage-menu-slug-mthd">
                        <h2><?php echo esc_html__('Choose method', 'wps'); ?></h2>
                        <input type="radio" name="wps_manage_menu_slug_mthd" value="1" <?php if( $wps_manage_menu_mthd == 1 ) echo 'checked=checked'; ?> /><label for="default"><?php echo esc_html__('Default', 'wps'); ?></label>
                        <input type="radio" name="wps_manage_menu_slug_mthd" value="2" <?php if( $wps_manage_menu_mthd == 2 ) echo 'checked=checked'; ?> /><label for="default"><?php echo esc_html__('Strict method', 'wps'); ?></label>
                        <p><b><?php echo esc_html__('Note: By switching methods, you will loose your previous admin menu settings.', 'wps'); ?></b></p>
                      </div>
                    <ol class="topmenu sortUls" id="top_menu">
                        <?php $inm = 0; $mm_cu = 0; $tsl = 0; ?>
                        <?php if(isset($menu) && !empty($menu)): ?>
                            <?php foreach($menu as $menu_key => $value): $inm++; ?>
                            <?php $menu_value = ((!empty($value[0]))) ? $value[0] : "Separator";?>
                            <?php
                            $menu_icon_class = "";
                            $menu_icon_data = "";
                            if(isset($topmenuicon[$value[2]]) && !empty($topmenuicon[$value[2]])) {
                              $menu_icon_data = trim($topmenuicon[$value[2]]);
                              $icon_class_split = explode('|', $topmenuicon[$value[2]]);
                              $menu_icon_class = $icon_class_split[0] . " " . $icon_class_split[1];
                            }
                            ?>
                            <?php $custom_menu_title = (isset($topmenutitle[$value[2]]) &&!empty($topmenutitle[$value[2]])) ? $topmenutitle[$value[2]] : "";?>

                        <li id="<?php echo "top-li-".$tsl;?>">
                            <input type="hidden" name="custom_admin_menu[top_menu][]" id="<?php echo "input-top-li-".$tsl;?>" value="<?php echo $value[2];?>"/>
                            <div class="alter-sort-list alter-top-menu-<?php echo $menu_key; ?>">
                                <span class="menu_title">
                                    <?php

                                        if(isset($actual_menulabel[$value[2]]) && !empty($actual_menulabel[$value[2]])){
                                            $this->Menu_Title($actual_menulabel[$value[2]]);
                                        } else {
                                            $subcurrent = self::getSubmenuParentSlug($value[2], $this->wps_df_menu, $this->wps_df_submenu);
                                            if(isset($subcurrent) && !empty($subcurrent)){
                                                $this->Menu_Title($subcurrent[0]);
                                                unset($subcurrent);
                                            } else {
                                                $this->Menu_Title($menu_value);
                                            }
                                        }

                                    ?>
                                </span>
                                <?php $this->Issubpage($value[0]); ?>

                                <div class="alter-menu-contents" id="s">

                                  <?php if(!empty($value[0])) : ?>
                                    <div class="menu_title">
                                        <label for="menu_title"><em><?php echo esc_html__('Rename Title', 'wps'); ?></em></label>
                                        <input type="text" id="<?php echo "customtitle-top-li-".$tsl;?>" name="custom_admin_menu[top_menu_title][<?php echo $value[2];?>]" value="<?php echo esc_attr($custom_menu_title);?>" />
                                    </div>
                                    <div class="menu_icon">
                                        <label for="icon_picker"><em><?php echo esc_html__('Choose Icon', 'wps'); ?></em></label>
                                        <div id="" data-target="#menu-icon-for-<?php echo $mm_cu; ?>" class="icon-picker <?php echo esc_attr($menu_icon_class); ?>"></div>
                                        <input type="hidden" id="menu-icon-for-<?php echo $mm_cu++; ?>" name="custom_admin_menu[menu_icon][<?php echo $value[2];?>]" value="<?php echo esc_attr($menu_icon_data); ?>" />
                                    </div>
                                  <?php endif; ?>

                                    <?php echo self::hide_for_menu("top_menu", $value[2], '', $inm); ?>

                                    <ol class="menu_child_<?php echo $menu_key; ?> submenu subsortUls" id="sub_menu">
                                        <?php if (isset($submenu[$value[2]]) && !empty($submenu[$value[2]])): ?>
                                        <?php $ssl = 0; (int)$customize = 0;

                                        foreach($submenu[$value[2]] as $submenu_key => $submenu_value): $inm++;

                                        ?>
                                        <?php $disblieitem = (esc_html(html_entity_decode($value[2])) == esc_html(html_entity_decode($submenu_value[2]))) ? "ui-state-disabled" : "ui-state-disabled";?>
                                        <?php $custom_submenu_title = (isset($submenutitle[$value[2]][$submenu_value[2]]) &&!empty($submenutitle[$value[2]][$submenu_value[2]])) ? $submenutitle[$value[2]][$submenu_value[2]] : "";?>
                                            <li id="<?php echo "sub-li-".$tsl."-".$ssl;?>" class="<?php echo $disblieitem; ?>">
                                                <input type="hidden" name="custom_admin_menu[sub_menu][<?php echo $value[2];?>][]" id="<?php echo "input-sub-li-".$tsl."-".$ssl;?>" value="<?php echo $submenu_value[2];?>"/>
                                                <div class="alter-sort-list submenu_contents">
                                                    <span class="menu_title">
                                                        <?php
                                                            if (isset($actual_submenulabel[$submenu_value[2]]) && !empty($actual_submenulabel[$submenu_value[2]])){
                                                                $this->Menu_Title($actual_submenulabel[$submenu_value[2]]);
                                                            } else {
                                                                $ismenu = self::istopmenu($submenu_value[2], $this->wps_df_menu);
                                                                if(isset($ismenu) && !empty($ismenu)){
                                                                    $this->Menu_Title($ismenu[0]);
                                                                    unset($ismenu);
                                                                } else {
                                                                    $this->Menu_Title($submenu_value[0]);
                                                                }

                                                                //$this->Menu_Title($submenu_value[0]);
                                                            }
                                                        ?>
                                                    </span>
                                                    <a href="#" class="alter-edit-expand"><i class="fa fa-chevron-down" aria-hidden="true"></i> <span>Edit</span></a>
                                                    <div class="alter-menu-contents">
                                                        <div class="menu_title">
                                                            <label for="menu_title"><em><?php echo esc_html__('Rename Title', 'wps'); ?></em></label>
                                                            <input type="text" id="<?php echo "customtitle-sub-li-".$tsl."-".$ssl;?>" name="custom_admin_menu[sub_menu_title][<?php echo $value[2];?>][<?php echo $submenu_value[2];?>]" value="<?php echo $custom_submenu_title; ?>" />
                                                        </div>
                                                        <a href="#" class="alter-edit-expand"><i class="fa fa-chevron-down" aria-hidden="true"></i> <span>Edit</span></a>
                                                        <?php echo self::hide_for_menu("sub_menu", $value[2], $submenu_value[2],$inm); ?>
                                                    </div>
                                                </div>
                                            </li>
                                            <?php $ssl++; unset($custom_menu_title);?>
                                            <?php endforeach;?>
                                        <?php endif; ?>
                                    </ol>
                                </div>
                            </div>
                        </li>
                            <?php $tsl++;?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ol>
                    <input type="hidden" name="alter_menu_order" value="1" />
                    <?php wp_nonce_field('wps_menu_cz_nonce','wps_menu_cz_field'); ?>
                    <input type="submit"  class="button button-primary button-hero" value="<?php esc_html_e('Save Changes', 'wps'); ?>" />
                    </form>
                </div>
            </div>
            <?php if( !defined('POWERBOX_PATH') ) { ?>
              <?php

              $wps_ad_status = parent::get_wps_option_data( 'wps_ad_status' );

               if( empty( $wps_ad_status ) && $wps_ad_status != 'never' && !isset( $_COOKIE['wps-pro-show-15'] ) && !isset( $_COOKIE['wps-pro-show-30'] ) && !isset( $_COOKIE['wps-pro-show-60'] ) ) {
                 ?>

                   <div class="wps-close-btn-dropdown"  style="float:right;">
                      <a class="dashicons dashicons-dismiss wps-close-btn"></a>
                         <div class="wps-close-btn-dropdown-content">
                             <a href="" id="wps-pro-show-15"><?php echo esc_html__( 'SHOW AFTER 15 DAYS', 'wps' ); ?></a>
                             <a href="" id="wps-pro-show-30"><?php echo esc_html__( 'SHOW AFTER 30 DAYS', 'wps' ); ?></a>
                             <a href="" id="wps-pro-show-60"><?php echo esc_html__( 'SHOW AFTER 60 DAYS', 'wps' ); ?></a>
                             <a href="<?php echo esc_url( admin_url( 'admin.php?page=admin_menu_management&adaction=never' ) ); ?>"><?php echo esc_html__( 'NEVER SHOW AGAIN', 'wps' ); ?></a>
                         </div>
                   </div>

            <div class="powerbox-banner">
              <a href="https://codecanyon.net/item/wpspowerbox-addon-for-wpshapere-wordpress-admin-theme/22169580" target="_blank">
                <img src="<?php echo WPSHAPERE_DIR_URI ?>assets/images/powerbox-vertical-banner-a-450x760.jpg" alt="" />
              </a>
            </div>

          <?php }
            }
           ?>

          </div>
        <?php


          }
        }

        function find_customize($slug = '') {
          $customize_slug = strtok($slug,'?');
          if (preg_match('/\bcustomize\b/', $slug)) {
            if( 'customize.php' == $customize_slug  )
              return true;
            else return false;
          }
          elseif('custom-header' == $customize_slug || 'custom-background' == $customize_slug) {
            return true;
          }
          else return false;
        }

        public function removemenu($itemArray, $menu)
        {
            if (is_array($itemArray)) {
                foreach($itemArray as $key => &$value) {
                    $find = self::istopmenu($value, $menu);
                    $pos = ($find[count($find)-1]);
                    if ($pos) {
                       if (isset($menu[$pos]) && !empty($menu[$pos])) {
                            if ($menu[$pos][2] == $value) {
                                    unset($menu[$pos]);
                                    unset($value);
                            }
                        }
                    }
                }
                return $menu;
            }
            return false;
        }

        public function Issubpage($title)
        {
            //if (!empty($title) && isset($title)) //allowed hiding of separators @since 5.0.5
                echo '<a href="#" class="alter-edit-expand"><i class="fa fa-chevron-down" aria-hidden="true"></i> <span>Edit</span></a>';
        }

        public function Menu_title($title)
        {
            echo '<i class="fa fa-arrows-alt" aria-hidden="true"></i>';

            if (__('Separator', 'wps') == $title) {
              echo '<span class="menu-seperator"></span>';
            }
            else {
              echo parent::clean_title($title);
            }
        }

        public function wps_menu_data() {
           if (isset($this->aof_options['custom_admin_menu']) && !empty($this->aof_options['custom_admin_menu'])) {
               return $this->aof_options['custom_admin_menu'];
           }
           else
               return null;;
        }

        public function cleanArray($array)
        {
            if ($array && is_array($array)){
                foreach($array as &$row) {
                    $row = esc_html(html_entity_decode($row));
                }
                return $array;
            }
            return false;
        }

        /**
        * clean url method for better managing admin menu items
        * @since 6.1.3
        */
        public function cleanURL( $url ) {
          if ( !empty($url) )
            return preg_replace('/[^a-zA-Z0-9_]/', '', $url);
          else
            return NULL;
        }

        public function istopmenu($item, $menu)
        {
            if ($menu) {
                foreach($menu as $key => $value) {
                    if (in_array($item, $value)){
                        array_push($value, $key);
                        return $value;
                    }
                }
            }
            return false;
        }

        public function issubmenu($searchitem, $topkey, $submenu)
        {
            if ($submenu) {
                if (isset($submenu[$topkey]) && !empty($submenu[$topkey])) {
                    foreach($submenu[$topkey] as $key => $value) {
                        if(esc_html(html_entity_decode($searchitem)) == esc_html(html_entity_decode($value[2]))) {
                            array_push($value, $key);
                            return $value;
                        }
                    }
                }
            }
            return false;
        }

        public function getSubmenuParentSlug($item, $menu, $submenu)
        {
            $output = array();
            if (isset($menu) && !empty($menu)) {
                foreach($menu as $key => $value) {
                    if(isset($submenu[$value[2]]) && !empty($submenu[$value[2]])) {
                        $output = self::issubmenu($item, $value[2], $submenu);
                        if ($output) {
                            array_push($output, $value[2]);
                            return $output;
                        }
                    }
                }
            }
            return false;
        }

        function dump_hook( $tag, $hook ) {
            $function = array();

            foreach( $hook as $priority => $functions ) {
                foreach( $functions as $row_function ) {
                    if( $row_function['function'] != 'list_hook_details' ) {
                        if( is_string( $row_function['function'] ) )
                            $function = array_merge(array($row_function['function'])) ;
                        elseif( is_string( $row_function['function'][0] ) )
                             $function = array_merge(array($row_function['function'][0],$row_function['function'][1]));
                        elseif( is_object( $row_function['function'][0] ) )
                            $function = array_merge(array(get_class( $row_function['function'][0] ),$row_function['function'][1]));
                        else
                            $function = array();
                    }
                }
            }

            return $function;
        }

        public function hide_for_menu($level, $admin_menu_slug, $admin_submenu_slug='', $menu_count=0) {

          /**
          * clean url method for better managing admin menu items
          * @since 6.1.3
          */
          if ( isset($this->aof_options['wps_manage_menu_slug_mthd']) && $this->aof_options['wps_manage_menu_slug_mthd'] == 2 ) {
            $clean_url = true;
          }
          else
            $clean_url = false;

          $level_name = (empty($level)) ? "top_menu" : $level;
          $admin_submenu_slug = (!empty($admin_submenu_slug)) ? $admin_submenu_slug : $admin_menu_slug;
          $wps_menu_data = $this->wps_menu_data();

          if( $clean_url === true ) {
              $admin_submenu_slug = $this->cleanURL( urldecode( $admin_submenu_slug ) );
              $admin_menu_slug = $this->cleanURL( urldecode( $admin_menu_slug ) );
          }
          else {
            $admin_submenu_slug = html_entity_decode($admin_submenu_slug);
            $admin_menu_slug = html_entity_decode($admin_menu_slug);
            if (preg_match('/\bcustomize\b/', $admin_submenu_slug)) {
                $admin_submenu_slug = strtok($admin_submenu_slug,'?');
            }
          }
            $output = '<div class="hide-for-roles">' .
                '<label class="hide-for-roles" for="hide-for-roles"><em>' . esc_html__('Hide menu for', 'wps') . '</em></label>';
                $get_all_roles = parent::wps_get_wproles();
                if(!empty($get_all_roles) && is_array($get_all_roles)) {
                    $role_nm = 0;
                    $role_max_nm = count($get_all_roles);
                    $output .= "<table id='box-input-{$menu_count}' class='hide-for-roles-inputs'><tbody>";
                    $output .= "<tr><td><a class='select_all' rel='box-input-{$menu_count}' href='#select_all'>Select all</a>
                    <a class='select_none' rel='box-input-{$menu_count}' href='#select_none'>Select none</a></td></tr>";
                    $output .= "<tr>";
                    foreach ($get_all_roles as $wprole_name => $wprole_label) {
                        if($level_name == "top_menu") {
                            $ids = 'custom_admin_menu['.$level_name.'_hide][' . $admin_menu_slug .  '][' . $wprole_name .  ']';
                            $chk_value_array = (isset($wps_menu_data['top_menu_hide'][$admin_menu_slug])) ? $wps_menu_data['top_menu_hide'][$admin_menu_slug] : "";
                        }
                        elseif($level_name == "sub_menu") {
                            $ids = 'custom_admin_menu['.$level_name.'_hide][' . $admin_menu_slug .  '][' . $admin_submenu_slug .  '][' . $wprole_name .  ']';
                            $chk_value_array = (isset($wps_menu_data['sub_menu_hide'][$admin_menu_slug][$admin_submenu_slug])) ? $wps_menu_data['sub_menu_hide'][$admin_menu_slug][$admin_submenu_slug] : "";
                        }
                        $chk_value = (!empty($chk_value_array) && array_key_exists($wprole_name, $chk_value_array)) ? "checked=checked" : "";
                        if($role_nm !=0 && $role_nm % 4 == 0) {
                            $output .= "</tr><tr>";
                        }

                        $output .= '<td>';
                        $output .= '<input class="alter-inputs" type="checkbox" name="'.$ids.'" value="1"' . $chk_value . ' />
                        <span>' . $wprole_label . '</span>';
                        $output .= '</td>';

                        if($role_nm == $role_max_nm) {
                            $output .= '</tr>';
                        }
                        $role_nm++;
                    }
                    $output .= '</tbody></table>';
                }

            $output .= '</div>';

            return $output;
        }
    }
}new WPS_CUSTOMIZEADMINMENU();
