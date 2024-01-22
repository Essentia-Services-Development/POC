<?php
/**
 * Plugin Name: PeepSo Monetization: Tutor LMS
 * Plugin URI: https://peepso.com
 * Description: Integrates Tutor LMS into PeepSo user profiles and streams. Requires the Tutor LMS plugin.
 * Author: PeepSo
 * Author URI: https://peepso.com
 * Version: 6.2.7.0
 * Copyright: (c) 2015 PeepSo, Inc. All Rights Reserved.
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peepsotutorlms
 * Domain Path: /language
 *
 * We are Open Source. You can redistribute and/or modify this software under the terms of the GNU General Public License (version 2 or later)
 * as published by the Free Software Foundation. See the GNU General Public License or the LICENSE file for more details.
 * This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.
 */

class PeepSoTutorLMSPlugins {
    private static $_instance = NULL;

    const PLUGIN_EDD = 64983004;
    const PLUGIN_SLUG = 'tutorlms-integration';

    const PLUGIN_NAME	 = 'Monetization: Tutor LMS';
    const PLUGIN_VERSION = '6.2.7.0';
    const PLUGIN_RELEASE = ''; //ALPHA1, BETA10, RC1, '' for STABLE
    const MODULE_ID = 6664;

    const THIRDPARTY_MIN_VERSION =  '2.0.6';

    private $tutor_activity;

    public $widgets = array(
        'PeepSoWidgetTutorGroups',
    );

    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    private static function ready_thirdparty() {
        $result = TRUE;

        if(!defined('TUTOR_VERSION') || !version_compare(TUTOR_VERSION, self::THIRDPARTY_MIN_VERSION, '>=')) {
            $result = FALSE;
        }

        return $result;
    }

    private static function ready() {
        if(class_exists('PeepSo')) {
            $plugin_version = explode('.', self::PLUGIN_VERSION);
            $peepso_version = explode('.', PeepSo::PLUGIN_VERSION);

            if(4==count($plugin_version)) {
                array_pop($plugin_version);
            }

            if(4==count($peepso_version)) {
                array_pop($peepso_version);
            }

            $plugin_version = implode('.', $plugin_version);
            $peepso_version = implode('.', $peepso_version);

            return(self::ready_thirdparty() && class_exists('PeepSo') && $peepso_version == $plugin_version);
        }
    }

    private function __construct()
    {

        /** VERSION INDEPENDENT hooks **/

        // Admin
        if (is_admin()) {
            add_action('admin_init', array(&$this, 'peepso_check'));
            add_filter('peepso_license_config', function($list){
                $list[] = array(
                    'plugin_slug' => self::PLUGIN_SLUG,
                    'plugin_name' => self::PLUGIN_NAME,
                    'plugin_edd' => self::PLUGIN_EDD,
                    'plugin_version' => self::PLUGIN_VERSION
                );
                return ($list);
            }, 160);
        }

        // Compatibility
        add_filter('peepso_all_plugins', function($plugins) {
            $plugins[plugin_basename(__FILE__)] = get_class($this);
            return $plugins;
        });

        // Translations
        add_action('plugins_loaded', array(&$this, 'load_textdomain'));

        // Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        /** VERSION LOCKED hooks **/
        if(self::ready()) {
            if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG, 0)) {
                return;
            }

            if (is_admin()) {
                add_action( 'wp_ajax_peepsotutorlms_user_courses', array(&$this,'ajax_user_courses') );
                add_action( 'wp_ajax_nopriv_peepsotutorlms_user_courses', array(&$this,'ajax_user_courses') );
            }
            add_action('peepso_init', array(&$this, 'init'));
            add_action('peepso_config_after_save-tutorlms', array(&$this, 'rebuild_cache'));
            add_filter('peepso_widgets', function ($widgets) {
                // register widgets
                foreach (scandir($widget_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'widgets' . DIRECTORY_SEPARATOR) as $widget) {
                    if (strlen($widget)>=5) require_once($widget_dir . $widget);
                }

                return array_merge($widgets, $this->widgets);
            });
        }
    }

    public function activate() {
        if (!$this->peepso_check()) {
            return (FALSE);
        }

        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'activate.php');
        $install = new PeepSoTutorLMSInstall();
        $res = $install->plugin_activation();
        if (FALSE === $res) {
            // error during installation - disable
            deactivate_plugins(plugin_basename(__FILE__));
        }
        return (TRUE);
    }

    public function init()
    {
        if( $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE != PeepSo3_Mayfly::get($mayfly = 'peepso_'.$this::PLUGIN_SLUG.'_version')) {
            // activate returns false in case of missing license
            if($this->activate()) {
                PeepSo3_Mayfly::set($mayfly, $this::PLUGIN_VERSION.$this::PLUGIN_RELEASE);
            }
        }

        PeepSo::add_autoload_directory(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
        PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));

        // Admin hooks
        if (is_admin()) {

            add_action('admin_init', array(&$this, 'peepso_check'));

            add_filter('peepso_admin_config_tabs', function( $tabs ) {
                $tabs['tutorlms'] = array(
                    'label' => __('TutorLMS', 'peepsotutorlms'),
                    'icon' => 'https://cdn.peepso.com/icons/plugins/'.self::PLUGIN_EDD.'.svg',
                    'tab' => 'tutorlms',
                    'function' => 'PeepSoConfigSectionTutorLMS',
                    'cat'   => 'monetization',
                );

                return $tabs;
            });
        }

        // Front hooks
        if(!is_admin()) {

            // Profile segment
            $profile_slug = PeepSo::get_option('tutor_navigation_profile_slug', 'courses', TRUE);

            add_action('peepso_profile_segment_'.$profile_slug,     function(){
                $pro = PeepSoProfileShortcode::get_instance();
                $this->view_user_id = PeepSoUrlSegments::get_view_id($pro->get_view_user_id());

                echo PeepSoTemplate::exec_template('tutorlms', 'profile-tutorlms', array('view_user_id' => $this->view_user_id), TRUE);
            });

            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));

            // Add chat button in instructor box
            $enable_chat = PeepSo::get_option('tutor_chat_enable');
            if(class_exists('PeepSoMessagesPlugin') && $enable_chat) {
                add_action('tutor_course/single/enrolled/after/instructors', function(){
                    global $post;

                    if ($post) {
                        $is_enrolled = tutor_utils()->is_enrolled( $post->ID, get_current_user_id() );
                        if ($is_enrolled) {
                            $instructors = tutor_utils()->get_instructors_by_course();
                            if($instructors && count($instructors)) {
                                foreach($instructors as $key => $instructor) {
                                    if ($instructor->ID != get_current_user_id()) {
                                    ?>
                                    <a class="ps-ld__instructor-chat ps-btn" href="#" onclick="peepso.messages.new_message(<?php echo $instructor->ID ;?>, false, this); return false;">
                                        <i class="ps-icon-comments"></i> <?php echo sprintf(__('Start a chat with %s', 'peepsolearndash'), $instructor->display_name);?>
                                    </a>
                                    <?php
                                    }
                                }
                            }
                        }
                    }
                });
            }

            add_action('peepso_activity_post_attachment', array(&$this, 'attach_tutor'), 30, 1);
            add_filter('peepso_activity_allow_empty_content', array(&$this, 'activity_allow_empty_content'), 10, 1);
        }

        // Global hooks

        // Profile tab link
        add_filter('peepso_navigation_profile', function($links){
            if (PeepSo::get_option('tutor_profile_enable')) {
                $slug = PeepSo::get_option('tutor_navigation_profile_slug', 'courses', TRUE);
                $links[$slug] = array(
                    'href' => $slug,
                    'label'=> PeepSo::get_option('tutor_navigation_profile_label', __('Courses', 'peepsotutorlms'), TRUE),
                    'icon' => PeepSo::get_option('tutor_navigation_profile_icon', 'gcis gci-graduation-cap', TRUE),
                );
            }

            if (PeepSo::get_option('tutor_profile_enable_dashboard')) {
                $tutor_option = get_option('tutor_option');

                if (isset($tutor_option['tutor_dashboard_page_id'])) {
                    $links['dashboard'] = array(
                        'href' => get_permalink($tutor_option['tutor_dashboard_page_id']),
                        'label'=> PeepSo::get_option('tutor_profile_dashboard_label', __('Dashboard', 'peepsotutorlms'), TRUE),
                        'icon' => PeepSo::get_option('tutor_navigation_profile_icon', 'gci gci-tachometer-alt', TRUE),
                    );
                }
                
            }

            return $links;
        });

        /**** ACTIVITY ****/

        // Activity - course enroll
        add_action('tutor_after_enroll', function( $course_id, $isEnrolled ) {
            $user_id = get_current_user_id();

            // Exit if disabled in Config
            if(1 != PeepSo::get_option('tutor_activity_enroll', 0)) {
                return;
            }

            // Exit if user is removed from course
            if ( $isEnrolled === false ) {
                return;
            }

            // Exit if post already created
            $umeta = 'peepso_tutor_post_create_enroll_course_' . $course_id;
            if ( get_user_meta( $user_id, $umeta, TRUE ) == 1 ) {
                return;
            }

            // POST TO STREAM
            $extra = array(
                'module_id' => self::MODULE_ID,
                'act_access'=> PeepSo::get_option('tutor_activity_enroll_privacy',PeepSoUser::get_instance($user_id)->get_profile_accessibility()),
            );

            $content='';

            // create an activity item
            $act = PeepSoActivity::get_instance();
            $this->tutor_activity = TRUE;
            $act_id = $act->add_post($user_id, $user_id, $content, $extra);

            update_post_meta($act_id, '_peepso_tutorlms_action_type', 'enroll');
            update_post_meta($act_id, '_peepso_tutorlms_course_id', "$course_id");

            // Set usermeta to block post duplication
            add_user_meta( $user_id,  $umeta, 1, TRUE );
        }, 10, 2);

        // Activity - course complete
        add_action('tutor_course_complete_after', function( $course_id ) {

            // Exit if disabled in Config
            if(1 != PeepSo::get_option('tutor_activity_complete', 0)) {
                return;
            }

            $user_id = get_current_user_id();

            // Exit if post already created
            $umeta = 'peepso_tutorlms_post_create_complete_course_' . $course_id;
            if ( get_user_meta( $user_id, $umeta, TRUE ) == 1 ) {
                return;
            }

            // POST TO STREAM
            $extra = array(
                'module_id' => self::MODULE_ID,
                'act_access'=> PeepSo::get_option('tutor_activity_complete_privacy',PeepSoUser::get_instance($user_id)->get_profile_accessibility()),
            );

            $content = '';

            // create an activity item
            $act = PeepSoActivity::get_instance();
            $this->tutor_activity = TRUE;
            $act_id = $act->add_post($user_id, $user_id, $content, $extra);

            update_post_meta($act_id, '_peepso_tutorlms_action_type', 'complete');
            update_post_meta($act_id, '_peepso_tutorlms_course_id', "$course_id");

            // Set usermeta to block post duplication
            add_user_meta( $user_id,  $umeta, 1, TRUE );
        }, 10, 1);

        // Activity - Course review
        add_action('tutor_after_rating_placed', function( $comment_id ) {
            new PeepSoError(json_encode($_POST), 'debug', 'tutorlms-integrations');

            // Exit if disabled in Config
            if(1 != PeepSo::get_option('tutor_activity_review', 0)) {
                return;
            }
            
            // Exit if post type is not course
            if ('course' !== get_post_type( $_POST['course_id'] )) {
                return;
            }

            $user_id = get_current_user_id();
            $course_id = $_POST['course_id'];

            // Exit if post already created
            $umeta = 'peepso_tutorlms_post_create_review_course_' . $course_id;
            if ( get_user_meta( $user_id, $umeta, TRUE ) == 1 ) {
                return;
            }

            // POST TO STREAM
            $extra = array(
                'module_id' => self::MODULE_ID,
                'act_access'=> PeepSo::get_option('tutor_activity_review_privacy',PeepSoUser::get_instance($user_id)->get_profile_accessibility()),
            );

            $content = '';

            // create an activity item
            $act = PeepSoActivity::get_instance();
            $act_id = $act->add_post($user_id, $user_id, $content, $extra);

            update_post_meta($act_id, '_peepso_tutorlms_action_type', 'review');
            update_post_meta($act_id, '_peepso_tutorlms_course_id', "$course_id");

            // Set usermeta to block post duplication
            add_user_meta( $user_id,  $umeta, 1, TRUE );
        }, 10, 1);

        // Activity utils - action text
        add_filter('peepso_activity_stream_action', function($action, $post){
            if (self::MODULE_ID === intval($post->act_module_id)) {

                if('enroll' == get_post_meta($post->ID,  '_peepso_tutorlms_action_type', true)){
                    $action = PeepSo::get_option('tutor_activity_enroll_action_text', __('enrolled in a course', 'peepsotutorlms'), TRUE);
                }

                if('complete' == get_post_meta($post->ID,  '_peepso_tutorlms_action_type', true)){
                    $action = PeepSo::get_option('tutor_activity_complete_action_text', __('completed a course', 'peepsotutorlms'), TRUE);
                }

                $wp_post = get_post(get_post_meta($post->ID, '_peepso_tutorlms_course_id', TRUE));
                $action .= sprintf(' <a class="ps-tutorlms-action-title" href="%s">%s</a>', get_the_permalink($wp_post->ID), $wp_post->post_title);

                global $post;
                $post->post_content ='';
            }

            return ($action);
        }, 10, 2);

        // Activity utils - disable edits
        add_filter('peepso_post_filters', function($options){
            if (self::MODULE_ID == intval($options['post']->act_module_id)) {
                if (isset($options['acts']['edit'])) {
                    unset($options['acts']['edit']);
                }
            }
        
            return $options;
        }, 10,1);

        // Activity utils - disable repost
        add_filter('peepso_activity_post_actions', function($actions){
            if ($actions['post']->act_module_id == self::MODULE_ID) {
                unset($actions['acts']['repost']);
            }
            return $actions;
        });

        /**** GROUPS ****/

        if(class_exists('PeepSoGroupsPlugin')) {
            add_action('add_meta_boxes', function () {
                add_meta_box('peepso-tutorlms-course-groups', __('PeepSo Groups - related groups', 'peepsotutorlms'), array(&$this, 'metabox_groups'), array('courses'), 'advanced', 'low', array());
                add_meta_box('peepso-tutorlms-course-groups-auto', __('PeepSo Groups automation - course enrolled', 'peepsotutorlms'), array(&$this, 'metabox_groups_auto'), array('courses'), 'advanced', 'low', array());
            });

            // Groups - course enroll
            add_action('tutor_after_enroll', function( $course_id, $isEnrolled ) {
                $user_id = get_current_user_id();

                // Exit if user is removed from course
                if ( $isEnrolled === true ) {
                    return;
                }

                $PeepSoCourseAutoGroups = new PeepSoTutorCourseAutoGroups();

                $groups = $PeepSoCourseAutoGroups->get_groups_by_course($course_id);
                foreach ($groups as $key => $group) {
                    $PeepSoGroupUser = new PeepSoGroupUser($group, $user_id);
                    $PeepSoGroupUser->member_join();
                }
            }, 10, 4);

        }

        /**** VIP ****/

        if(class_exists('PeepSoVIP')) {
            add_action('add_meta_boxes', function () {
                add_meta_box('peepso-tutorlms-course-vip-complete', __('PeepSo VIP automation - course completed', 'peepsotutorlms'), array(&$this, 'metabox_vip_complete'), array('courses'), 'advanced', 'low', array());
            });

            // VIP - course complete
            add_action('tutor_course_complete_after', function( $course_id ) {

                $user_id = get_current_user_id();

                $PeepSoTutorCourseVIP = new PeepSoTutorCourseVIP();
                $vipicons = $PeepSoTutorCourseVIP->get_vipicons_by_course($course_id);

                if (!empty($vipicons)) {
                    $oldicons = get_user_meta( $user_id, 'peepso_vip_user_icon', TRUE );
                    if (!empty($oldicons)) {
                        $vipicons = array_unique(array_merge($oldicons,$vipicons));
                    }
                    update_user_meta( $user_id, 'peepso_vip_user_icon', $vipicons );
                }
            }, 10, 1);
        }

        /**** METABOX SAVE ****/

        add_action('save_post', function($post_id){

            $post = get_post($post_id);
            if ('courses' != $post->post_type || (defined('DOING_AJAX') && DOING_AJAX)) {
                return;
            }

            $PeepSoCourseGroups = new PeepSoTutorCourseGroups();
            $groups = isset($_REQUEST['peepsotutorgroups']) ? $_REQUEST['peepsotutorgroups'] : array();
            $PeepSoCourseGroups->update_course_group($post_id, $groups);

            $PeepSoCourseAutoGroups = new PeepSoTutorCourseAutoGroups();
            $groups = isset($_REQUEST['peepsotutorgroupsauto']) ? $_REQUEST['peepsotutorgroupsauto'] : array();
            $PeepSoCourseAutoGroups->update_course_group($post_id, $groups);

            $PeepSoTutorCourseVIP = new PeepSoTutorCourseVIP();
            $vipicons = isset($_REQUEST['peepsotutorvipicons']) ? $_REQUEST['peepsotutorvipicons'] : array();
            $PeepSoTutorCourseVIP->update_course_vipicons($post_id, $vipicons);
        }, 10,1);
    }

    /**
     * Loads the translation file for the plugin
     */
    public function load_textdomain()
    {
        $path = str_ireplace(WP_PLUGIN_DIR, '', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
        load_plugin_textdomain('peepsotutorlms', FALSE, $path);
    }

    /**
     * Build AJAX response with user courses
     */
    public function ajax_user_courses()
    {
        ob_start();

        $input = new PeepSoInput();
        $owner = $input->int('user_id');
        $page  = $input->int('page', 1);

        $sort  = $input->value('sort', 'desc', array('asc','desc'));

        $limit = 10;
        $offset = ($page - 1) * $limit;

        if ($page < 1) {
            $page = 1;
            $offset = 0;
        }

        $course_ids = array();
        $courses = tutor_utils()->get_enrolled_courses_by_user($owner, array('private', 'publish'));

        if($courses && property_exists($courses, 'posts') && is_array($courses->posts) && count($courses->posts)) {
            foreach ($courses->posts as $course) {
                $course_ids[] = $course->ID;
            }
        }

        // Course blacklist
        $hide_courses = PeepSo::get_option('tutor_profile_hide_courses','');

        if(strlen($hide_courses)) {
            if(strstr($hide_courses, ',')) {
                $hide_courses = explode(',', $hide_courses);
            } else {
                $hide_courses = array($hide_courses);
            }

            $hide_courses = array_map('intval',$hide_courses);

            if(count($course_ids)) {
                foreach($course_ids as $k=>$v) {
                    if(in_array($v, $hide_courses)) {
                        unset($course_ids[$k]);
                    }
                }
            }
        }

        $courses = array();

        if(count($course_ids)) {
            $args = array(
                'post__in' => $course_ids,
                'post__not_id' => $hide_courses,
                'post_type' => 'courses',
                'orderby' => 'post_name',
                'post_status' => array('publish', 'private'),
                'order' => 'asc',
                'posts_per_page' => $limit,
                'offset' => $offset,
            );

            // Get the posts
            $courses = get_posts($args);
        }


        if (count($courses)) {

            $force_strip_shortcodes = PeepSo::get_option('tutor_profile_content_force_strip_shortcodes', 0);

            // Iterate posts
            foreach ($courses as $course) {

                // Choose between short description, excerpt or post_content
                $course_content = get_the_excerpt($course->ID);

                if(!strlen($course_content)) {
                    $course_content = $course->post_content;
                }

                $course_content = strip_shortcodes($course_content);

                if($force_strip_shortcodes) {
                    $course_content = preg_replace('/\[.*?\]/', '', $course_content);
                }

                $limit = intval(PeepSo::get_option('tutor_profile_content_length',50));
                $course_content = wp_trim_words($course_content, $limit,'&hellip;');

                if(0 == $limit) {
                    $course_content = FALSE;
                }

                PeepSoTemplate::exec_template('tutorlms', 'course', array('course_content' => $course_content, 'course' => $course));
            }

            $resp['success']		= 1;

            $resp['html']			= ob_get_clean();
        } else {
            $message =  (get_current_user_id() == $owner) ? __('You have not enrolled in any courses yet', 'peepsold') : sprintf(__('%s has not enrolled in any courses yet', 'peepsotutorlms'), PeepSoUser::get_instance($owner)->get_firstname());
            $resp['success']		= 0;
            $resp['error'] = PeepSoTemplate::exec_template('profile','no-results-ajax', array('message' => $message), TRUE);
        }

        $resp['page']			= $page;
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit(0);
    }

    public function metabox_groups_auto() {
        ?>
        <p><?php echo __('Automatically add users to PeepSo Groups when they enroll in this course','peepsotutorlms');?></p>
        <div style="max-height:350px;overflow:scroll">
            <table class="form-table">
                <tr class="user-admin-color-wrap">
                    <td>
                        <fieldset id="peepso-groups" class="scheme-list">
                            <?php
                            $selectedGroup = [];
                            if (isset($_REQUEST['post'])) {
                                $ld_course_id = intval( $_REQUEST['post'] );

                                $PeepSoCourseAutoGroups = new PeepSoTutorCourseAutoGroups();
                                $selectedGroup = $PeepSoCourseAutoGroups->get_groups_by_course($ld_course_id);
                            }

                            $aGroups = PeepSoGroups::admin_get_groups(0, NULL, NULL, NULL, '', 'all');
                            foreach ($aGroups as $key => $group) {
                                ?>
                                <div class="color-option">
                                    <input name="peepsotutorgroupsauto[]" id="peepsotutorgroupsauto<?php echo $key;?>" type="checkbox" value="<?php echo $group->id;?>" class="tog" <?php echo (in_array($group->id, $selectedGroup)) ? ' checked=checked':'';?>>
                                    <label for="peepsotutorgroupsauto<?php echo $key;?>">
                                        <img src="<?php echo $group->get_avatar_url();?>" style="width: 64px; height: 64px;">
                                        <div style="float:right;margin:4px;max-width:200px;">
                                            <?php echo $group->name;?>
                                            <br><small>
                                                <?php  if(intval($group->is_open)) { echo '<i class="ps-icon-globe"></i>' . __('Open', 'peepsotutorlms'); }  ?>
                                                <?php  if(intval($group->is_closed)) { echo '<i class="ps-icon-lock"></i>'.__('Closed', 'peepsotutorlms'); }  ?>
                                                <?php  if(intval($group->is_secret)) { echo '<i class="ps-icon-shield"></i>'.__('Secret', 'peepsotutorlms'); }  ?>
                                            </small>
                                        </div>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public function metabox_groups() {
        ?>
        <p><?php echo __('Show these Groups as "related" to this Course','peepsotutorlms');?></p>
        <div style="max-height:350px;overflow:scroll">
            <table class="form-table">
                <tr class="user-admin-color-wrap">
                    <td>
                        <fieldset id="peepso-groups" class="scheme-list">
                            <?php
                            $selectedGroup = [];
                            if (isset($_REQUEST['post'])) {
                                $tutor_course_id = intval( $_REQUEST['post'] );

                                $PeepSoCourseGroups = new PeepSoTutorCourseGroups();
                                $selectedGroup = $PeepSoCourseGroups->get_groups_by_course($tutor_course_id);
                            }

                            $aGroups = PeepSoGroups::admin_get_groups(0, NULL, NULL, NULL, '', 'all');
                            foreach ($aGroups as $key => $group) {
                                ?>
                                <div class="color-option">
                                    <input name="peepsotutorgroups[]" id="peepsotutorgroups<?php echo $key;?>" type="checkbox" value="<?php echo $group->id;?>" class="tog" <?php echo (in_array($group->id, $selectedGroup)) ? ' checked=checked':'';?>>
                                    <label for="peepsotutorgroups<?php echo $key;?>">
                                        <img src="<?php echo $group->get_avatar_url();?>" style="width: 64px; height: 64px;">
                                        <div style="float:right;margin:4px;max-width:200px;">
                                            <?php echo $group->name;?>
                                            <br><small>
                                                <?php  if(intval($group->is_open)) { echo '<i class="ps-icon-globe"></i>' . __('Open', 'peepsotutorlms'); }  ?>
                                                <?php  if(intval($group->is_closed)) { echo '<i class="ps-icon-lock"></i>'.__('Closed', 'peepsotutorlms'); }  ?>
                                                <?php  if(intval($group->is_secret)) { echo '<i class="ps-icon-shield"></i>'.__('Secret', 'peepsotutorlms'); }  ?>
                                            </small>
                                        </div>
                                    </label>
                                </div>
                                <?php
                            }
                            ?>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public function metabox_vip_complete() {
        ?>
        <p><?php echo __('Assign users these VIP Badges when they complete this course','peepsotutorlms');?></p>
        <div style="max-height:350px;overflow:scroll">
            <table class="form-table">
                <tr class="user-admin-color-wrap">
                    <td>
                        <fieldset id="peepso-vip" class="scheme-list">
                            <?php
                            $selectedIcon = [];
                            if (isset($_REQUEST['post'])) {
                                $tutor_course_id = intval( $_REQUEST['post'] );

                                $PeepSoTutorCourseVIP = new PeepSoTutorCourseVIP();
                                $selectedIcon = $PeepSoTutorCourseVIP->get_vipicons_by_course($tutor_course_id);
                            }

                            $PeepSoVipIconsModel = new PeepSoVipIconsModel();
                            foreach ($PeepSoVipIconsModel->vipicons as $key => $value) {

                                ?>
                                <div class="color-option">
                                    <input name="peepsotutorvipicons[]" id="peepsotutorvipicons<?php echo $key;?>" type="checkbox" value="<?php echo $value->post_id;?>" class="tog" <?php echo in_array($value->post_id, $selectedIcon) ? ' checked=checked':'';?>>
                                    <label for="vip_icon_<?php echo $key;?>"><?php echo $value->title;?> <?php  if(!intval($value->published)) { echo "<small>(".__('unpublished', 'peepso-vip').")</small>"; }  ?></label>
                                    <img src="<?php echo $value->icon_url;?>" style="width: auto; height: 16px;">
                                </div>
                                <?php
                            }
                            ?>
                        </fieldset>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    public function enqueue_scripts()
    {
        // dynamic CSS
        $css = 'plugins/tutorlms/tutorlms-'.PeepSo3_Mayfly::get('peepso_tutorlms_css').'.css';
        if(!file_exists(PeepSo::get_peepso_dir().$css) ) {
            $this->rebuild_cache();
            $css = 'plugins/tutorlms/tutorlms-'.PeepSo3_Mayfly::get('peepso_tutorlms_css').'.css';
        }

        wp_enqueue_style('peepso-tutorlms-dynamic', PeepSo::get_peepso_uri().$css, array(), self::PLUGIN_VERSION, 'all');
        wp_enqueue_script('peepso-tutorlms', PeepSo::get_asset('js/bundle.min.js', __FILE__),
            array('peepso', 'peepso-page-autoload'), self::PLUGIN_VERSION, TRUE);
    }

    /**
     * Check if PeepSo class is present (PeepSo is installed and activated)
     * If there is no PeepSo, immediately disable the plugin and display a warning
     * @return bool
     */
    public function peepso_check()
    {
        if (!class_exists('PeepSo')) {

            add_action('admin_notices', function(){
                ?>
                <div class="error peepso">
                    <strong>
                        <?php echo sprintf(__('The %s requires the PeepSo plugin to be installed and activated.', 'peepsotutorlms'), self::PLUGIN_NAME);?>
                        <a href="plugin-install.php?tab=plugin-information&plugin=peepso-core&TB_iframe=true&width=772&height=291" class="thickbox">
                            <?php echo __('Get it now!', 'peepsotutorlms');?>
                        </a>
                    </strong>
                </div>
                <?php
            });

            unset($_GET['activate']);
            deactivate_plugins(plugin_basename(__FILE__));
            return (FALSE);
        }

        if (!self::ready_thirdparty()) {

            add_action('admin_notices', function() {
                if(method_exists('PeepSo','third_party_warning')) {
                    PeepSo::third_party_warning('Tutor LMS','tutor','',self::THIRDPARTY_MIN_VERSION, self::PLUGIN_NAME);
                }
            }, 10030);
        }

        // PeepSo.com license check
        if (!PeepSoLicense::check_license(self::PLUGIN_EDD, self::PLUGIN_SLUG)) {
            add_action('admin_notices', array(&$this, 'license_notice'));
        }

        if (isset($_GET['page']) && 'peepso_config' == $_GET['page'] && !isset($_GET['tab'])) {
            add_action('admin_notices', array(&$this, 'license_notice_forced'));
        }

        // PeepSo.com new version check
        // since 1.7.6
        if(method_exists('PeepSoLicense', 'check_updates_new')) {
            PeepSoLicense::check_updates_new(self::PLUGIN_EDD, self::PLUGIN_SLUG, self::PLUGIN_VERSION, __FILE__);
        }

        return (TRUE);
    }

    public function license_notice()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG);
    }

    public function license_notice_forced()
    {
        PeepSo::license_notice(self::PLUGIN_NAME, self::PLUGIN_SLUG, true);
    }

    public function rebuild_cache()
    {
        // Directory where CSS files are stored
        $path = PeepSo::get_peepso_dir().'plugins'.DIRECTORY_SEPARATOR.'tutorlms'.DIRECTORY_SEPARATOR;

        if (!file_exists($path) ) {
            @mkdir($path, 0755, TRUE);
        }

        // Try to remove the old file
        $old_file = $path.'tutorlms-'.PeepSo3_Mayfly::get('peepso_tutorlms_css').'.css';
        @unlink($old_file);

        // New cache
        delete_option('peepso_tutorlms_css');
        PeepSo3_Mayfly::set('peepso_tutorlms_css', time());

        $image_height = intval(PeepSo::get_option('tutor_profile_featured_image_height', 150));
        $box_height = intval(PeepSo::get_option('tutor_profile_two_column_height', 350));

        if($image_height < 1) {
            $image_height = 1;
        }

        if($box_height < 1 || !PeepSo::get_option('tutor_profile_two_column_enable', 1)) {
            $box_height = 'auto';
        }

        // @todo cache this
        ob_start();
        ?>
        .ps-tutorlms__course-image {
        height: <?php echo $image_height;?>px;
        }

        .ps-tutorlms__course-image--left,
        .ps-tutorlms__course-image--right {
        width: <?php echo $image_height;?>px;
        }

        .ps-tutorlms__course{
        height: <?php echo $box_height;?>px;
        }
        <?php
        $css = ob_get_clean();

        update_option('peepso_tutorlms_css', $css);

        $file = $path.'tutorlms-'.PeepSo3_Mayfly::get('peepso_tutorlms_css').'.css';
        $h = fopen( $file, "a" );
        fputs( $h, $css );
        fclose( $h );
    }

    /**
     * Attach the tutor to the post display
     * @param  object $post The post
     */
    public function attach_tutor($post)
    {
        $course_id = get_post_meta($post->ID, '_peepso_tutorlms_course_id', TRUE);
        if ($course_id) {
            PeepSoTemplate::exec_template('tutorlms', 'course-embed', array('course_id' => $course_id));
        }
    }

    /**
     * Checks if empty content is allowed
     * @param boolean $allowed
     * @return boolean always returns TRUE
     */
    public function activity_allow_empty_content($allowed)
    {
        if ( $this->tutor_activity ) {
            $allowed = TRUE;
        }
        
        // reset tutor activity
        $this->tutor_activity = FALSE;

        return ($allowed);
    }
}


PeepSoTutorLMSPlugins::get_instance();

// EOF
