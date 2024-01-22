<?php

class PeepSoGeneral
{
    protected static $_instance = NULL;

    private $navigation = array();

    public $template_tags = array(
        'access_types',				// options for post/content access types
        'post_types',				// options for post types
        'show_error',				// outputs a WP_Error object
    );

    private function __construct()
    {
        add_filter('peepso_navigation', array(&$this, 'init_navbar'), -1);
        add_filter('peepso_navigation', array(&$this, 'finish_navbar'), 999);
    }

    /*
     * return singleton instance
     */
    public static function get_instance()
    {
        if (self::$_instance === NULL) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    /** NAVIGATION DATA - BUILD **/
    public function init_navbar($navbar) {

        $user = PeepSoUser::get_instance(get_current_user_id());

        $navbar = array(
            'home' => array(
                'href' 	=> PeepSo::get_page('activity'),
                'label' => __('Activity', 'peepso-core'),
                'icon' 	=> 'gcis gci-home',
                'class' => 'ps-navbar__menu-item--home',

                'primary'           => TRUE,
                'secondary'         => FALSE,
                'mobile-primary'    => FALSE,
                'mobile-secondary'  => TRUE,
                'widget'            => TRUE,

                'icon-only' => TRUE,
            ),

            'home-following' => array(
                'href' 	=> PeepSo::get_page('activity').'#following',
                'label' => __('Following', 'peepso-core'),
                'icon' 	=> 'gcis gci-square-check',
                'class' => 'ps-navbar__menu-item--home',

                'primary'           => TRUE,
                'secondary'         => FALSE,
                'mobile-primary'    => FALSE,
                'mobile-secondary'  => TRUE,
                'widget'            => TRUE,

                'icon-only' => TRUE,
            ),
            'home-saved' => array(
                'href' 	=> PeepSo::get_page('activity').'#saved',
                'label' => __('Saved', 'peepso-core'),
                'icon' 	=> 'gcis gci-bookmark',
                'class' => 'ps-navbar__menu-item--home',

                'primary'           => TRUE,
                'secondary'         => FALSE,
                'mobile-primary'    => FALSE,
                'mobile-secondary'  => TRUE,
                'widget'            => TRUE,

                'icon-only' => TRUE,
            ),
            'members-page' => array(
                'href' 	=> PeepSo::get_page('members'),
                'label' => __('Members', 'peepso-core'),
                'icon' 	=> 'gcis gci-user-friends',

                'primary'           => TRUE,
                'secondary'         => FALSE,
                'mobile-primary'    => TRUE,
                'mobile-secondary'  => FALSE,
                'widget'            => TRUE,
            ),

            // Profile - avatar and name
            'profile-home' => array(
                'class' => 'ps-navbar__menu-item--user',
                'href' => $user->get_profileurl(),
                'label' =>'<div class="ps-avatar ps-avatar--toolbar ps-avatar--xs"><img src="' . $user->get_avatar() . '" alt="' . $user->get_fullname(). ' avatar"></div> ' . $user->get_firstname(),
                'title' => PeepSoUser::get_instance()->get_fullname(),

                'primary'           => FALSE,
                'secondary'         => TRUE,
                'mobile-primary'    => FALSE,
                'mobile-secondary'  => FALSE,
                'widget'            => FALSE,
            ),

            // Profile segments
            'profile' => array(
                'href' => '',
                'class' => 'ps-navbar__menu-link ps-dropdown__toggle ps-js-dropdown-toggle',
                'menuclass' => 'ps-dropdown__menu ps-dropdown__menu--navbar ps-js-dropdown-menu',
                'wrapclass' => 'ps-dropdown--left ps-dropdown--menu ps-dropdown--arrow ps-navbar__menu-item--user ps-js-dropdown',

                'label' => '<span class="gcis gci-angle-down"></span>',
                'title' => PeepSoUser::get_instance()->get_fullname(),

                'primary'           => FALSE,
                'secondary'         => TRUE,
                'mobile-primary'    => TRUE,
                'mobile-secondary'  => FALSE,
                'widget'            => FALSE,

                'menu' => array(),
            ),
        );

        $extra_activity_items = TRUE;

        if('core_community' != PeepSo::get_option('stream_id_default', 'core-community')) {
            $extra_activity_items = FALSE;
        } elseif(PeepSo::get_option_new('stream_id_sticky')) {
            $extra_activity_items = FALSE;
        }


        if(!$extra_activity_items || !PeepSo::get_option_new('peepso_navigation_following')) {
            unset($navbar['home-following']);
        }

        if(!$extra_activity_items || !PeepSo::get_option_new('post_save_enable') || !PeepSo::get_option_new('peepso_navigation_saved') ) {
            unset($navbar['home-saved']);
        }

        return $navbar;
    }

    public function finish_navbar($navbar) {

        $note = PeepSoNotifications::get_instance();
        $unread_notes = $note->get_unread_count_for_user();

        $navbar['notifications'] = array(
            'href' => PeepSo::get_page('notifications'),
            'label' => __('Notifications', 'peepso-core'),
            'icon' => 'gcis gci-bell',

            'primary'           => FALSE,
            'secondary'         => TRUE,
            'mobile-primary'    => FALSE,
            'mobile-secondary'  => TRUE,
            'widget'            => FALSE,

            'count' => $unread_notes,
            'class' => 'ps-notif--general ps-js-notifications',
            'icon-only' => TRUE,
            'notifications'=> TRUE,
        );

        return $navbar;
    }

    /** NAVIGATION DATA - ACCESS **/

    public function get_navigation($context = 'primary', $user_id = NULL)
    {

        // Return instance if any
        if (isset($this->navigation[$context])) {
            return $this->navigation[$context];
        }

        // Don't run the filters again if we have the raw data
        if (isset($this->navigation['unfiltered'])) {
            $navbar = $this->navigation['unfiltered'];
        } else {
            // Build the navigation
            $navbar = apply_filters('peepso_navigation', array());

            // Attach Profile sub-menu
            $navbar['profile']['menu'] = apply_filters('peepso_navigation_profile', array('_user_id'=>$user_id));
            $this->navigation['unfiltered'] = $navbar;
        }

        // Mobile: squish Profile + Submenu together and move to the end
        if('mobile-primary'== $context) {
            $navbar['profile']['label'] = $navbar['profile-home']['label'];

            $profile = $navbar['profile'];
            unset($navbar['profile']);

            $navbar['profile'] = $profile;
        }

        // Profile Submenu links shoud be absolute
        $user = PeepSoUser::get_instance();
        if(isset($navbar['profile']['menu'])) {
            unset($navbar['profile']['menu']['_user_id']);

            foreach ($navbar['profile']['menu'] as $id => $menu) {
                $url = $user->get_profileurl() . $navbar['profile']['menu'][$id]['href'];

                if('http'==substr($navbar['profile']['menu'][$id]['href'],0, 4)) {
                    $url = $navbar['profile']['menu'][$id]['href'];
                }

                $navbar['profile']['menu'][$id]['href'] = $url;
            }
        }

        // Profile Submenu extra links
        if(apply_filters('peepso_filter_navigation_preferences', TRUE)) {
            $navbar['profile']['menu']['peepso-core-preferences'] = array(
                'href' => $user->get_profileurl() . 'about/preferences/',
                'icon' => 'gcis gci-user-edit',
                'label' => __('Preferences', 'peepso-core'),
            );
        }

        // @todo #2274 this has to be peepso_navigation_profile
//        if(class_exists('PeepSoPMP')) {
//            $navbar['profile']['menu']['peepso-pmp'] = array(
//                'href' => pmpro_url("account"),
//                'label' => __('Membership', 'peepso-pmp'),
//                'icon' => 'ps-icon-vcard',
//            );
//        }
        if(apply_filters('peepso_filter_navigation_log_out', TRUE)) {
            $navbar['profile']['menu']['peepso-core-logout'] = array(
                'href' => PeepSo::get_page('logout'),
                'icon' => 'gcis gci-power-off',
                'label' => __('Log Out', 'peepso-core'),
            );
        }

        $filtered_navbar = array();
        foreach ($navbar as $nav) {

            $nav['class']       = isset($nav['class'])                          ? $nav['class'] : '';
            $nav['count']       = isset($nav['count'])                          ? $nav['count'] : 0;
            $nav['label']       = isset($nav['label'])                          ? $nav['label'] : '';
            $nav['title']       = isset($nav['title'])                          ? $nav['title'] : $nav['label'];
            $nav['menuclass']   = isset($nav['menuclass'])                      ? $nav['menuclass'] : '';
            $nav[$context]      = isset($nav[$context])                         ? $nav[$context] : FALSE;
            $nav['icon-only']   = isset($nav['icon-only'])                      ? $nav['icon-only'] : FALSE;

            if(TRUE == $nav[$context]) {
                $filtered_navbar[] = $nav;
            }
        }

        $navbar = $filtered_navbar;

        $this->navigation[$context] = $navbar;
        return $navbar;
    }

    /** RENDERING **/

    public function access_types()
    {
        $access = array(
            'public' => array(
                'icon' => 'gcis gci-globe-americas',
                'label' => __('Public', 'peepso-core'),
                'descript' => __('Can be seen by everyone, even if they\'re not members', 'peepso-core'),
            ),
            'site_members' => array(
                'icon' => 'gcis gci-user-friends',
                'label' => __('Site Members', 'peepso-core'),
                'descript' => __('Can be seen by registered members', 'peepso-core'),
            ),
            'friends' => array(
                'icon' => 'gcis gci-users',
                'label' => __('Friends', 'peeps'),
                'descript' => __('Can be seen by your friends', 'peepso-core'),
            ),
            'me' => array(
                'icon' => 'gcis gci-lock',
                'label' => __('Only Me', 'peepso-core'),
                'descript' => __('Can only be seen by you', 'peepso-core'),
            )
        );

        foreach ($access as $name => $data) {
            echo '<li data-priv="', $name, '">', PHP_EOL;
            echo '<i class="', $data['icon'], '"></i>', PHP_EOL;
            echo $data['label'], "</p>\r\n";
            echo '<span>', $data['descript'], "</span></li>", PHP_EOL;
        }
    }

    // Displays the frontend navbar
    public function render_navigation( $context = 'primary')
    {
        ob_start();
        $navbar = $this->get_navigation($context, get_current_user_id());
        foreach ($navbar as $item => $data) {

            if (isset($data['menu'])) { ?>

                <span class="ps-navbar__menu-item ps-dropdown <?php echo $data['wrapclass'];?>">
				    <a onclick="return false;"  href="<?php echo $data['href'];?>" class="<?php echo $data['class'];?>">
              <?php if(FALSE == $data['icon-only']) { echo $data['label']; }?>
            </a>
            <div class="<?php echo $data['menuclass'];?>">
              <?php foreach ($data['menu'] as $name => $submenu) { ?>
                  <a class="<?php echo isset($submenu['class']) ? $submenu['class'] : '';?>" href="<?php echo $submenu['href'];?>">
                  <i class="<?php echo $submenu['icon'];?>"></i>
                	<?php echo $submenu['label'];?>
								</a>
              <?php } ?>
    				</div>
        </span>

            <?php } else { ?>

                <span class="ps-navbar__menu-item <?php echo $data['class'];?>">
            <a class="ps-navbar__menu-link ps-js-navbar-menu" href="<?php echo $data['href'];?>" title="<?php echo esc_attr($data['title']);?>">
                <?php if (isset($data['icon']) && ( FALSE == $data['primary'] || isset($data['icon-only']) )) { ?>
                    <i class="<?php echo $data['icon'];?>"></i>
                <?php } ?>

                <?php if (FALSE == $data['icon-only']) { echo $data['label']; } ?>

                <span class="ps-notif__bubble js-counter ps-js-counter"><?php echo $data['count'] > 0 ? $data['count'] : ''; ?></span>
            </a>
        </span>

                <?php
            }
        }
        // return ob_get_clean(); // uncomment this if the compression fails testing
        return preg_replace(['/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s'],['>','<','\\1'],ob_get_clean());
    }


    /**
     * Displays the post types available on the post box. Plugins can add to these via the `peepso_post_types` filter.
     */
    public function post_types($params = array())
    {
        $opts = array(
            'status' => array(
                'icon' => 'gcis gci-pen',
                'name' => __('Status', 'peepso-core'),
                'class' => 'ps-postbox__menu-item active',
            ),
        );

        $opts = apply_filters('peepso_post_types', $opts, $params);


        foreach ($opts as $type => $data) {
            echo '<div data-tab="', $type, '" ';
            if (isset($data['class']) && !empty($data['class'])) {
                echo 'class="', $data['class'], '" ';
            }
            echo '>', PHP_EOL;
            echo '<a href="#" onclick="return false;">';

            echo '<i class="', $data['icon'], '"></i>';
            echo '<span>', $data['name'], '</span>', PHP_EOL;

            echo '</a></div>', PHP_EOL;
        }
    }

    /*
     * Displays error messages contained within an error object
     * @param WP_Error $error The instance of WP_Error to display messages from.
     */
    public function show_error($error)
    {
        if (!is_wp_error($error))
            return;

        $codes = $error->get_error_codes();
        foreach ($codes as $code) {
            echo '<div class="ps-alert ps-alert--abort">', PHP_EOL;
            $msg = $error->get_error_message($code);
            echo $msg;
            echo '</div>';
        }
    }

    /**
     * Returns the max upload size from php.ini and wp.
     * @return string The max upload size bytes in human readable format.
     */
    public function upload_size()
    {
        $upload_max_filesize = convert_php_size_to_bytes(ini_get('upload_max_filesize'));
        $post_max_size = convert_php_size_to_bytes(ini_get('post_max_size'));

        return (size_format(min($upload_max_filesize, $post_max_size, wp_max_upload_size())));
    }

    /**
     * Returns the label for login input.
     * @return string The label 'Username or email' OR 'Email'.
     */
    public static function get_login_input_label()
    {
        $login_with_email = PeepSo::get_option('login_with_email', 0);
        if ($login_with_email == 2) {
            return __('Email', 'peepso-core');
        } else {
            return __('Username or Email', 'peepso-core');
        }
    }

}

// EOF
