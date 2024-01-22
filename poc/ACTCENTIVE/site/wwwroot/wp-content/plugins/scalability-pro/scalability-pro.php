<?php
/**
 * Plugin Name: Scalability Pro
 * Plugin URI: http://www.wpintense.com
 * Description: Fixes underlying database query performance for multiple plugins including WooCommerce, Custom Post Types, wp-admin, Datafeedr, WP All Import and much more.
 * Version: 5.42
 * Author: Dave Hilditch
 * Author URI: http://www.wpintense.com	
 * License: GPL v3
 */
if (!isset($superspeedy_plugin_versions)) {
    $superspeedy_plugin_versions = array();
}
$superspeedy_plugin_versions['scalability-pro'] = '5.42';

define('SP_PLUGIN_NAME', 'Scalability Pro');
define('SPRO_DB_VERSION', '5.38');

define( 'SPROPLUGIN_DIR', dirname(__FILE__) );  
require_once SPROPLUGIN_DIR . '/defines.php';
//delete_site_option('external_updates-scalability-pro'); // needed this when downgraded puc5 to puc4

require 'plugin-updates/plugin-update-checker.php';
$SPROWidgetsUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://www.wpintense.com/assets/plugins/scalability-pro.json',
	__FILE__, //Full path to the main plugin file or functions.php.
	'scalability-pro'
);
add_filter('puc_pre_inject_update-scalability-pro', 'wpi_add_license_to_remote_update_request');



//require_once SPROPLUGIN_DIR . '/class-tgm-plugin-activation.php'; can re-add this when I upgrade this to provide the ability to require other plugins
require_once(plugin_dir_path(__FILE__) . 'wpintense-settings.php');

require_once 'class-wp-list-table-cached.php';
require_once 'bulk-editing.php';
require_once 'slow-query-log.php';
require_once 'wp-content/profiling-queries.php';


/**
 * Register the required plugins for this theme.
 *
 * In this example, we register five plugins:
 * - one included with the TGMPA library
 * - two from an external source, one from an arbitrary source, one from a GitHub repository
 * - two from the .org repo, where one demonstrates the use of the `is_callable` argument
 *
 * The variables passed to the `tgmpa()` function should be:
 * - an array of plugin arrays;
 * - optionally a configuration array.
 * If you are not changing anything in the configuration array, you can remove the array and remove the
 * variable from the function call: `tgmpa( $plugins );`.
 * In that case, the TGMPA default settings will be used.
 *
 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
 */

function awdff_dectivateplugin() {
//    wpi_dropindexes();
//   wpi_dropfulltextindex();
}

//register_activation_hook( __FILE__, 'awdff_activateplugin' );
register_deactivation_hook(__FILE__, 'remove_spro_globals');

function wpi_getIndexes() {
    $indexes = array(
        array('indexname' => 'wpi_performance_boost', 'indextable' => 'postmeta', 'indexcols' => 'post_id, meta_key(50), meta_value(15)'),
        array('indexname' => 'wpi_postmeta_boost', 'indextable' => 'postmeta', 'indexcols' => 'meta_key(50), meta_value(15)'),
        array('indexname' => 'wpi_performance_boost2', 'indextable' => 'posts', 'indexcols' => 'post_status, ID'),
        array('indexname' => 'wpi_performance_boost8', 'indextable' => 'posts', 'indexcols' => 'post_type, post_status, menu_order, post_title(15), ID'),
        array('indexname' => 'wpi_performance_boost9', 'indextable' => 'posts', 'indexcols' => 'post_type, post_status, post_date desc, ID'),
        array('indexname' => 'wpi_performance_boost6', 'indextable' => 'term_relationships', 'indexcols' => 'term_taxonomy_id, object_id'),
        array('indexname' => 'wpi_performance_boost3', 'indextable' => 'term_taxonomy', 'indexcols' => 'taxonomy, parent, term_taxonomy_id'),
        array('indexname' => 'wpi_performance_boost4', 'indextable' => 'term_taxonomy', 'indexcols' => 'taxonomy, term_id, term_taxonomy_id'),
        array('indexname' => 'wpi_performance_boost5', 'indextable' => 'term_taxonomy', 'indexcols' => 'term_taxonomy_id, taxonomy, term_id'),
        array('indexname' => 'wpi_performance_boost7', 'indextable' => 'terms', 'indexcols' => 'term_id, name(50), slug(50)'),
        array('indexname' => 'wpi_options_boost', 'indextable' => 'options', 'indexcols' => 'autoload'),
        array('indexname' => 'wpi_latest_posts_boost', 'indextable' => 'posts', 'indexcols' => 'post_type, post_status, post_date desc'),
        array('indexname' => 'wpi_perfboost_woopaypal', 'indextable' => 'posts', 'indexcols' => 'post_type, post_mime_type'),
        array('indexname' => 'wpi_scalability_pro_sitemaps', 'indextable' => 'posts', 'indexcols' => 'post_status(20), post_password(20), post_type(20), post_modified'),
        array('indexname' => 'wpi_guid', 'indextable' => 'posts', 'indexcols' => 'guid'),
        array('indexname' => 'wpi_wc_lookup_sku', 'indextable' => 'wc_product_meta_lookup', 'indexcols' => 'sku'),
        array('indexname' => 'wpi_wpallimport_hash', 'indextable' => 'pmxi_hash', 'indexcols' => 'post_id'),
        array('indexname' => 'wpi_wpallimport_unique_key', 'indextable' => 'pmxi_posts', 'indexcols' => 'unique_key(10)'),
        array('indexname' => 'wpi_wpallimport_importlookup', 'indextable' => 'pmxi_posts', 'indexcols' => 'import_id, post_id'),
        array('indexname' => 'wpi_wpallimport_pmxi_posts_lookups', 'indextable' => 'pmxi_posts', 'indexcols' => 'import_id, unique_key(100), id'),
        array('indexname' => 'wpi_wpallimport_image_filename', 'indextable' => 'pmxi_images', 'indexcols' => 'image_filename(100)'),
        array('indexname' => 'wpi_wpallimport_image_url', 'indextable' => 'pmxi_images', 'indexcols' => 'image_url(100)'), 
        array('indexname' => 'wpi_wpallimport_image_attachment', 'indextable' => 'pmxi_images', 'indexcols' => 'attachment_id'), 
        array('indexname' => 'wpi_wpallimport_boost_sku_lookup', 'indextable' => 'wc_product_meta_lookup', 'indexcols' => 'sku'),
        array('indexname' => 'wpi_wclovers_wcfm_marketplace_users', 'indextable' => 'users', 'indexcols' => 'ID, display_name'),
        array('indexname' => 'wpi_wclovers_wcfm_marketplace_usermeta', 'indextable' => 'usermeta', 'indexcols' => 'meta_key, meta_value(100), user_id'),
        array('indexname' => 'wpi_options_name', 'indextable' => 'options', 'indexcols' => 'option_name', 'isunique' => true),
        array('indexname' => 'wpi_comment_type_count', 'indextable' => 'comments', 'indexcols' => 'comment_type, comment_approved'),
        array('indexname' => 'wpi_post_title', 'indextable' => 'posts', 'indexcols' => 'post_title(50)'),
        array('indexname' => 'wpi_action_sched_lookup', 'indextable' => 'actionscheduler_actions', 'indexcols' => 'status, last_attempt_gmt, action_id'),
        array('indexname' => 'wpi_rankmath', 'indextable' => 'rank_math_analytics_objects', 'indexcols' => 'object_id'),   
        array('indexname' => 'wpi_post_modified_gmt', 'indextable' => 'posts', 'indexcols' => 'post_modified_gmt')         
    );
    /* For each index in $indexes, check if {$wpdb->prefix}{$index['indextable']} exists, if not then remove this entry from the array */
    global $wpdb;
    foreach ($indexes as $key => $index) {
        $tablename = $wpdb->prefix . $index['indextable'];
        if (is_multisite()) {
            $indexes[$key]['indexname'] .= "_" . get_current_blog_id();
        }
        if (!$wpdb->get_var("SHOW TABLES LIKE '$tablename'")) {
            unset($indexes[$key]);
        }

    } 


    return $indexes;
}

function wpi_createindexes() {
    global $wpdb;

    $selectedIndexes = is_null($_POST['indexes']) ? array() : $_POST['indexes'];
    
    $allIndexes = wpi_getIndexes();
    $indexesToCreate = array_filter($allIndexes, function($index) use ($selectedIndexes) {
        return in_array($index['indexname'], $selectedIndexes);
    });
    // Indexes to drop (i.e., unchecked ones)
    $indexesToDrop = array_filter($allIndexes, function($index) use ($selectedIndexes) {
        return !in_array($index['indexname'], $selectedIndexes);
    });

    // Loop to create checked indexes 
    foreach ($indexesToCreate as $index) {
        $indexname = $index['indexname'];
        $tablename = $wpdb->prefix . $index['indextable'];
        $indexexists = $wpdb->get_var($wpdb->prepare("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE `TABLE_CATALOG` = 'def' AND `TABLE_SCHEMA` = DATABASE() AND TABLE_NAME = %s AND `INDEX_NAME` = %s", $tablename, $indexname));
        try {
            if (is_null($indexexists)) {
                $createunique = "";
                if (isset($index['isunique']) && $index['isunique']) {
                    $createunique = "UNIQUE";
                }
                $createindex = "create $createunique index $indexname on $tablename ({$index['indexcols']});";
                $wpdb->query($createindex);
            }
        } catch (Exception $e) {        
        }
    }
    // Loop to drop unchecked indexes
    foreach ($indexesToDrop as $index) {
        $indexname = $index['indexname'];
        $tablename = $wpdb->prefix . $index['indextable'];
        $indexexists = $wpdb->get_var($wpdb->prepare("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE `TABLE_CATALOG` = 'def' AND `TABLE_SCHEMA` = DATABASE() AND TABLE_NAME = %s AND `INDEX_NAME` = %s", $tablename, $indexname));
        try {
            if (!is_null($indexexists)) {
                $dropindex = "DROP INDEX $indexname ON $tablename;";
                $wpdb->query($dropindex);
            }
        } catch (Exception $e) {        
        }
    }
}

function wpi_dropindexes() {
    global $wpdb;
    $indexes = wpi_getIndexes();
    foreach ($indexes as $index) {
        $indexname = $index['indexname'];
        $tablename = $wpdb->prefix . $index['indextable'];
        $indexexists = $wpdb->get_var($wpdb->prepare("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE `TABLE_CATALOG` = 'def' AND `TABLE_SCHEMA` = DATABASE() AND TABLE_NAME = %s AND `INDEX_NAME` = %s", $tablename, $indexname));
        try {
            if (!is_null($indexexists)) {
                $dropindex = "drop index $indexname on $tablename;";
                $wpdb->query($dropindex);
            }
        } catch (Exception $e) {        
        }
    }

    /* fetch all indexes starting with name wpi_ in the current database and drop them */
    $allindexes = $wpdb->get_results("SELECT INDEX_NAME, TABLE_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE `TABLE_CATALOG` = 'def' AND `TABLE_SCHEMA` = DATABASE() AND `INDEX_NAME` LIKE 'wpi_%';");
    foreach ($allindexes as $index) {
        $indexname = $index->INDEX_NAME;
        $tablename = $index->TABLE_NAME;
        $dropindex = "drop index $indexname on `$tablename`;";
        $wpdb->query($dropindex);
    }

}

add_filter('admin_body_class', 'sp_admin_body_class');

function sp_admin_body_class( $classes ) {

    $screen = '';
    
    $screen = get_current_screen();

    if ( ! $screen || $screen->id != 'settings_page_wpi_performance' ) return $classes;        
    
    $classes .= ' wpsp';

    return $classes;
}

add_action('admin_menu', 'spro_add_admin_menu');

add_action('admin_init', 'spro_settings_init');

add_action('admin_enqueue_scripts', 'spro_admin_scripts');
function spro_admin_scripts($hook) {
    if ($hook == 'settings_page_scalabilitypro') {
        wp_enqueue_script( 
            'spro_admin_js', 
            plugins_url( 'assets/js/sp_admin.js',__FILE__ ), 
            array('jquery'), 
            time()
        );
        // Localize the script
        wp_localize_script('spro_admin_js', 'sproVars', 
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spro_clear_cache_nonce')
        ));
        wp_enqueue_style('scalabilitypro-admincss', plugins_url('/assets/css/sp_admin.css', __FILE__), null, time());

    }
}

function spro_add_admin_menu() {
    add_options_page(SP_PLUGIN_NAME . ' Plugin', SP_PLUGIN_NAME . ' Plugin', 'manage_options', 'scalabilitypro', 'spro_options_page');
}

function spro_settings_init() {
    global $wpdb;
    global $SPRO_GLOBALS;

    $options = get_option('wpiperf_settings');
    $original_options = $options;    

    if (empty($options['scalability_pro_cache'])) {
        $exists = $wpdb->get_var("SELECT count(*)
        FROM information_schema.TABLES
        WHERE (TABLE_SCHEMA = '" . $wpdb->dbname . "') AND (TABLE_NAME = '" . $wpdb->prefix . "scalability_pro_cache')");
        if ($exists != 1) {
            $sql = "CREATE TABLE " . $wpdb->prefix . "scalability_pro_cache (
                postid int not null,
                userid int not null,
                cacheview varchar(50) not null,
                cachegroup varchar(50) not null,
                cachedata text CHARACTER SET utf8,
                primary key (postid, userid, cacheview, cachegroup)
            );";
            $wpdb->query($sql);
        }
        $options['scalability_pro_cache'] = 1;
    }
    if (empty($options['scalability_pro_post_count_cache'])) {
        $exists = $wpdb->get_var("SELECT count(*)
        FROM information_schema.TABLES
        WHERE (TABLE_SCHEMA = '" . $wpdb->dbname . "') AND (TABLE_NAME = '" . $wpdb->prefix . "scalability_pro_post_count_cache')");
        if ($exists != 1) {
            $sql = "CREATE TABLE " . $wpdb->prefix . "scalability_pro_post_count_cache (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                query_hash VARCHAR(32) NOT NULL,
                post_status VARCHAR(20) NOT NULL,
                num_posts BIGINT UNSIGNED NOT NULL,
                rownum INT UNSIGNED NOT NULL,
                expiry DATETIME NOT NULL
            );";
            $wpdb->query($sql);
        }
        $options['scalability_pro_post_count_cache'] = 1;
    }

    register_setting('wpi_perf_page', 'wpiperf_settings');

    add_settings_section(
        'wpiperf_wpi_perf_page_section', '', 'wpiperf_settings_section_callback', 'wpi_perf_page', array('before_section' => '<div class="spro-tab">', 'after_section' => '</div>')
    );
    add_settings_section(
        'spro_indexes', '', 'spro_indexes_callback', 'wpi_perf_page', array('before_section' => '<div class="spro-tab">', 'after_section' => '</div>')
    );
    add_settings_section(
            'spro_archive_options', '', 'spro_archive_options', 'wpi_perf_page', array('before_section' => '<div class="spro-tab">', 'after_section' => '</div>')
    );
    add_settings_section(
            'spro_pagedetail_options', '', 'spro_pagedetail_options', 'wpi_perf_page', array('before_section' => '<div class="spro-tab">', 'after_section' => '</div>')
    );
    add_settings_section(
            'spro_import_options', '', 'spro_import_options', 'wpi_perf_page', array('before_section' => '<div class="spro-tab">', 'after_section' => '</div>')
    );
    add_settings_section(
            'spro_woocommerce_options', '', 'spro_woocommerce_options', 'wpi_perf_page', array('before_section' => '<div class="spro-tab">', 'after_section' => '</div>')
    );
    add_settings_section(
            'spro_wpadmin_options', '', 'spro_wpadmin_options', 'wpi_perf_page', array('before_section' => '<div class="spro-tab">', 'after_section' => '</div>')
    );
    add_settings_section(
        'spro_slow_query_log', '', 'spro_slow_query_log', 'wpi_perf_page', array('before_section' => '<div class="spro-tab">', 'after_section' => '</div>')
    );	

    if (!isset($options['loadproductpageusingajax'])) {
        $options['loadproductpageusingajax'] = 'normalproductpage';
    }
    if (!isset($options['sidebarcssselector'])) {
        $options['sidebarcssselector'] = '.col-sm-3.col-sm-pull-9';
    }
    if (!isset($options['sortorder'])) {
        $options['sortorder'] = 'fullfunctionality';
    }
    if (!isset($options['calctotals'])) {
        $options['calctotals'] = 'keep';
    }
    if (!isset($options['calctotals_admin'])) {
        $options['calctotals_admin'] = 'keep';
    }
    if (!isset($options['defertermcounting'])) {
        $options['defertermcounting'] = 'keep';
    }
    if (!isset($options['cachepostcounts'])) {
        $options['cachepostcounts'] = 0;
    }
    if (!isset($options['removecustommeta'])) {
        $options['removecustommeta'] = 'keep';
    }
    if (!isset($options['cacheorderdeliverydate'])) {
        $options['cacheorderdeliverydate'] = 'no';
    }
    
    if (!isset($options['changetoexists'])) {
        $options['changetoexists'] = 'keep';
    }
    if (!isset($options['optimisewoodeleteoptions'])) {
        $options['optimisewoodeleteoptions'] = 'keep';
    }
        
	
	
    if (!isset($options['removecast'])) {
        $options['removecast'] = 'keep';
    }
    if (!isset($options['optimisewoogroup'])) {
        $options['optimisewoogroup'] = 'no';
    }
    if (!isset($options['optimiseprivatecheck'])) {
        $options['optimiseprivatecheck'] = 'no';
    }
    
    if (!isset($options['removewoosummary'])) {
        $options['removewoosummary'] = 'keep';
    }
    if (!isset($options['toplevelcatsonly'])) {
        $options['toplevelcatsonly'] = 'no';
    }
	
    if (!isset($options['removeajaxvariationscalc'])) {
        $options['removeajaxvariationscalc'] = 'keep';
    }
    if ($options != $original_options) {
        update_option('wpiperf_settings', $options, false);
    }

    add_settings_field(
            'sortorder', __('Remove sort options', 'wpi-performance'), 'wpiperf_sortorder_render', 'wpi_perf_page', 'spro_archive_options'
    );
    add_settings_field(
            'calctotals', __('Remove SQL_CALC_ROWS', 'wpi-performance'), 'wpiperf_calctotals_render', 'wpi_perf_page', 'spro_archive_options'
    );
    add_settings_field(
            'changetoexists', __('Alter main query to use EXISTS rather than LEFT JOIN', 'wpi-performance'), 'wpiperf_changetoexists', 'wpi_perf_page', 'spro_archive_options'
    );
    add_settings_field(
        'optimisewoogroup', __('Optimise WooCommerce Group By', 'wpi-performance'), 'wpiperf_optimise_woo_groupby', 'wpi_perf_page', 'spro_archive_options'
    );
    add_settings_field(
        'optimisewooprivate', __('Remove OR check for private items on front end', 'wpi-performance'), 'wpiperf_optimise_private_items', 'wpi_perf_page', 'spro_archive_options'
    );
    
    add_settings_field(
            'removeajaxvariationscalc', __('Remove Woo Ajax variations calculations', 'wpi-performance'), 'wpiperf_remove_woo_ajax_variations', 'wpi_perf_page', 'spro_pagedetail_options'
    );
    add_settings_field(
        'defertermcounting', __('Defer term counting', 'wpi-performance'), 'wpiperf_defertermcounting', 'wpi_perf_page', 'spro_import_options'
    );
    add_settings_field(
        'cachepostcounts', __('Cache Post Counts', 'wpi-performance'), 'wpiperf_cachepostcounts', 'wpi_perf_page', 'spro_import_options'
    );

    /* add new settings field for adding new restrictions per post type which allow admins to choose which wordpress image sizes to allow when these post types are being imported */
    add_settings_field(
        'image_sizes', __('Remove Image Sizes Globally', 'wpi-performance'), 'wpiperf_remove_image_sizes', 'wpi_perf_page', 'spro_import_options'
    );
    /* add new settings field for adding new restrictions per post type which allow admins to choose which wordpress image sizes to allow when these post types are being imported */
    add_settings_field(
        'action_scheduler', __('Optimize Action Scheduler', 'wpi-performance'), 'wpiperf_action_scheduler', 'wpi_perf_page', 'spro_import_options'
    );
    add_settings_field(
        'product_attributes_index_fix', __('Optimize Product Attributes Lookup', 'wpi-performance'), 'wpiperf_product_attributes_index_fix', 'wpi_perf_page', 'spro_import_options'
    );

	
    add_settings_field(
        'optimisewoodeleteoptions', __('Optimise WooCommerce Updates', 'wpi-performance'), 'wpiperf_optimise_woo_delete_options', 'wpi_perf_page', 'spro_woocommerce_options'
    );
    add_settings_field(
        'removecustommeta', __('Remove custom-meta select box', 'wpi-performance'), 'wpiperf_removecustommeta', 'wpi_perf_page', 'spro_woocommerce_options'
    );
    add_settings_field(
        'cacheorderdeliverydate', __('Fix Order Delivery Date plugin Admin Query', 'wpi-performance'), 'wpiperf_cacheorderdeliverydate', 'wpi_perf_page', 'spro_woocommerce_options'
    );

    add_settings_field(
        'cacheshortcode_onsale', __('Cache [sale_products] shortcode', 'wpi-performance'), 'wpiperf_cacheshortcode_onsale', 'wpi_perf_page', 'spro_woocommerce_options'
    );
    add_settings_field(
        'cacheshortcode_bestselling', __('Cache [best_selling_products] shortcode', 'wpi-performance'), 'wpiperf_cacheshortcode_bestselling', 'wpi_perf_page', 'spro_woocommerce_options'
    );
    add_settings_field(
        'cacheshortcode_uxproducts', __('Cache Flatsome [uxproducts] shortcode', 'wpi-performance'), 'wpiperf_cacheshortcode_uxproducts', 'wpi_perf_page', 'spro_woocommerce_options'
    );
    /* Create new settings field for ajax attribute editing */
    add_settings_field(
        'ajaxattributeedit', __('Ajax attribute editing', 'wpi-performance'), 'wpiperf_ajaxattributeedit', 'wpi_perf_page', 'spro_woocommerce_options'
    );
    /* Create new settings field to fix WooCommerce onboarding code */
    add_settings_field(
        'fixwoo_onboarding', __('Fix WooCommerce onboarding code', 'wpi-performance'), 'wpiperf_fixwoo_onboarding', 'wpi_perf_page', 'spro_woocommerce_options'
    );

	
	
    add_settings_field(
            'removewoosummary', __('Remove Woo order summary', 'wpi-performance'), 'wpiperf_remove_woo_order_summary', 'wpi_perf_page', 'spro_wpadmin_options'
    );
    add_settings_field(
        'toplevelcatsonly', __('Remove wp-admin WooCommerce Products Category List', 'wpi-performance'), 'wpiperf_top_level_cats_woo_admin', 'wpi_perf_page', 'spro_wpadmin_options'
    );
    add_settings_field(
        'cacheusercounts', __('Cache user counts', 'wpi-performance'), 'wpiperf_cache_usercounts_admin', 'wpi_perf_page', 'spro_wpadmin_options'
    );


    add_settings_field(
        'enable_slow_log', __('Enable Slow Query Log', 'wpi-performance'), 'spro_enable_slow_log', 'wpi_perf_page', 'spro_slow_query_log'
    );
    add_settings_field(
        'slow_query_limit', __('Slow Query Limit', 'wpi-performance'), 'spro_slow_query_limit', 'wpi_perf_page', 'spro_slow_query_log'
    );
    add_settings_field(
        'query_pattern', __('Query Pattern', 'wpi-performance'), 'spro_query_pattern', 'wpi_perf_page', 'spro_slow_query_log'
    );
    add_settings_field(
        'slow_queries', __('Slow Queries', 'wpi-performance'), 'spro_display_slow_queries', 'wpi_perf_page', 'spro_slow_query_log'
    );	

	
	
	
//        global $wp_settings_fields;
//        
//        print_r($wp_settings_fields);
}

function spro_archive_options() {
    ?>

    <div class="row">
        <div class="block float-l sp-welcome-wrapper">
            <section class="dev-box bulk-smush-wrapper wp-sp-container mb-0"> 
                <div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
                    <h3 tabindex="0"><?php echo __('Archive Options - Optimising WP_Query', 'wordpress'); ?></h3>
                </div>
                <div class="box-container">				
					<p>These options optimise the 'Main Query' on your archive pages. Archive pages include your blog list, product list pages (e.g. /shop/) and any page which lists other pages, including category pages, attribute pages etc.</p>
					<p>To measure before/after performance, install Query Monitor, visit your archive pages, click Query Monitor then CTRL+F or CMD+F and search for 'Main Query'.</p>
                </div>
            </section>				
        </div>
    </div>
    <?php
}
function spro_pagedetail_options() {
    ?>

    <div class="row">
        <div class="block float-l sp-welcome-wrapper">
            <section class="dev-box bulk-smush-wrapper wp-sp-container mb-0"> 
                <div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
                    <h3 tabindex="0"><?php echo __('Faster Product Detail or Single Post Pages', 'wordpress'); ?></h3>
                </div>
                <div class="box-container">				
					<p>If your product detail or single pages include widgets which run SQL which is optimised by the ‘archive’ optimisations above, then these pages will subequently be faster as a result. There is also this option, specifically for WooCommerce Product Variations:</p>
                </div>
            </section>				
        </div>
    </div>
    <?php
}
function spro_import_options() {
    ?>

    <div class="row">
        <div class="block float-l sp-welcome-wrapper">
            <section class="dev-box bulk-smush-wrapper wp-sp-container mb-0"> 
                <div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
                    <h3 tabindex="0"><?php echo __('Faster Imports - Optimising wp_postmeta', 'wordpress'); ?></h3>
                </div>
                <div class="box-container">				
					<p>In addition to the indexes we help you create, we auto-adjust WP All Import to defer term counts until after import. You can also override all term recounts until 2AM and you can reduce image sizes created so that imports speed up massively.</p>
                </div>
            </section>				
        </div>
    </div>
    <?php
}
function spro_woocommerce_options() {
    ?>

    <div class="row">
        <div class="block float-l sp-welcome-wrapper">
            <section class="dev-box bulk-smush-wrapper wp-sp-container mb-0"> 
                <div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
                    <h3 tabindex="0"><?php echo __('Specific WooCommerce optimisations', 'wordpress'); ?></h3>
                </div>
                <div class="box-container">				
					<p>If you are running WooCommerce, and you have a large number of products or a large number of orders, these options will help you get speed back to your site.</p>
                </div>
            </section>				
        </div>
    </div>
    <?php
}
function spro_wpadmin_options() {
    ?>

    <div class="row">
        <div class="block float-l sp-welcome-wrapper">
            <section class="dev-box bulk-smush-wrapper wp-sp-container mb-0"> 
                <div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
                    <h3 tabindex="0"><?php echo __('WP Admin Archive optimisations', 'wordpress'); ?></h3>
                </div>
                <div class="box-container">				
					<p>The WP_Query archive optimisations above will help, but there are more 'table scan' incurring widgets on wp-admin archives. Specifically 'post type' dropdown, 'category' dropdown, and the 'post status' counts. These options help you optimise those.</p>
                </div>
            </section>				
        </div>
    </div>
    <?php
}
function spro_slow_query_log() {
    ?>

    <div class="row">
        <div class="block float-l sp-welcome-wrapper">
            <section class="dev-box bulk-smush-wrapper wp-sp-container mb-0"> 
                <div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
                    <h3 tabindex="0"><?php echo __('Slow MySQL Query Log', 'wordpress'); ?></h3>
                </div>
                <div class="box-container">				
					<p>Scalability Pro includes its own Slow Query Log which does not slow down your site. It is better than the standard MySQL Slow Query log and New Relic since it includes the URL and stack trace which caused the slow query meaning that slow queries are actually more actionable.</p>
                    <p>You can optionally submit these slow query logs to us at WP Intense to help us figure out what optimisations to add next to Scalability Pro.</p>
                </div>
            </section>				
        </div>
    </div>
    <?php
}


function wpiperf_sortorder_render() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[sortorder]'>
        <option value='natural' <?php selected($options['sortorder'], 'natural'); ?>>Remove sort options (fastest)</option>
        <option value='fullfunctionality' <?php selected($options['sortorder'], 'fullfunctionality'); ?>>Keep sort options (slowest)</option>
    </select>
    <p>This option lets you use the natural index sort order on wp_posts. When WP_Query (used by WooCommerce, Custom Post Types and Blog archives) to fetch data, it provides sorting options and these sorting options require a full sort of the retrieved data. On /shop/ or large top level categories that means a full table or index scan which can take a few seconds and thrash the disk and CPU.</p>
    <p><strong>Note: </strong> Using the natural sort order will disable your default sort option you currently provide for your users and revert to the natural DB sort order which is typically based on insert order, but can change depending on the query optimizer.</p>
    <p>This option is always disabled if there is an ?orderby parameter in the URL to ensure forced ordering always works.</p>
    <?php
}

function wpiperf_calctotals_render() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[calctotals]'>
        <option value='remove' <?php selected($options['calctotals'], 'remove'); ?>>Remove SQL_CALC_ROWS on the front-end (fastest)</option>
        <option value='keep' <?php selected($options['calctotals'], 'keep'); ?>>Keep SQL_CALC_ROWS on front-end (slowest)</option>
    </select>
    <select name='wpiperf_settings[calctotals_admin]'>
        <option value='remove' <?php selected($options['calctotals_admin'], 'remove'); ?>>Remove SQL_CALC_ROWS on edit.php (fastest)</option>
        <option value='keep' <?php selected($options['calctotals_admin'], 'keep'); ?>>Keep SQL_CALC_ROWS on edit.php (slowest)</option>
    </select>
    <p>When WP_Query fetches posts/products, it also calculates the total number of matching items. This is useful to display at the top of your shop/page/edit.php, e.g. Showing 1 - 50 of 650,000. However, this count requires either an index scan or table scan. Removing it, can result in ultra-fast WP_Query speed. Depending on your setup, you may need to switch to the natural sort order above too for successful use of the indexes. Since most users only need to view page 1, this is a recommended option but you should be aware that page counts will be incorrect as a result. Because of this, most people combine this option with an infinite scroll plugin.</p>
    <p>Also on edit.php, SQL_CALC_ROWS is used. If you remove it, you remove pagination on these pages, but searching is unaffected and your pages will load far faster.</p>
    <?php
}
function wpiperf_defertermcounting() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[defertermcounting]'>
        <option value='remove' <?php selected($options['defertermcounting'], 'remove'); ?>>Defer Term Counting to Nightly (fastest)</option>
        <option value='keep' <?php selected($options['defertermcounting'], 'keep'); ?>>Keep term counting (slowest)</option>
    </select>
    <p>Helps optimise imports and other bulk modifications by deferring term counts (recounting items per category) to a nightly job.</p>
    <?php
}
function wpiperf_cachepostcounts() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[cachepostcounts]'>
        <option value=0 <?php selected($options['cachepostcounts'], 0); ?>>Use WordPress Cache (slowest)</option>
        <option value=1 <?php selected($options['cachepostcounts'], 1); ?>>Use SPRO Cache (fastest)</option>
    </select>
    <p>WordPress caches post counts per post type for you. However, as soon as one item is modified, created or deleted for that post type has its cache wiped. If you are importing, this causes your admin and imports to slow down.</p>
    <p>Note: This option is configured to always recount shop_order post types so it doesn't affect shop admins doing their job.</p>
    <p><button type="button" class="button" id="clear_post_count_cache"><?php esc_html_e('Clear Post Count Cache', 'scalability-pro'); ?></button></p>
    <?php
}
function spro_clear_postcount_cache() {
    // Security check
    check_ajax_referer('spro_clear_cache_nonce', 'nonce');

    global $wpdb;
    
    // Delete all cache data
    $wpdb->query("DELETE FROM {$wpdb->prefix}scalability_pro_post_count_cache");

    echo 'Cache cleared';
    wp_die(); // All ajax handlers die when finished
}

add_action('wp_ajax_spro_clear_postcount_cache', 'spro_clear_postcount_cache');         // If called from admin panel
add_action('wp_ajax_nopriv_spro_clear_postcount_cache', 'spro_clear_postcount_cache');  // If called elsewhere

function wpiperf_action_scheduler() {
    global $wpdb;
    $options = get_option('wpiperf_settings');
    $clearable_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_actions WHERE status = 'complete' OR status = 'failed' OR status = 'cancelled'");
    $clearable_logs = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}actionscheduler_logs;"); // Update with the correct query to count logs
    // echo out the total clearable_items using the scalability-pro domain with the wp translate function
    // add html select option to save in $options['action_scheduler'] to optimise the action scheduler table
    ?>
    <select name='wpiperf_settings[action_scheduler]'>
        <option value='default' <?php if (array_key_exists('action_scheduler', $options)) selected($options['action_scheduler'], 'default'); ?>>Keep default 30 day retention</option>
        <option value='optimize' <?php if (array_key_exists('action_scheduler', $options)) selected($options['action_scheduler'], 'optimize'); ?>>Remove clearable items daily</option>
    </select>
    <p>Helps optimise imports by removing clearable items daily from the Action Scheduler table. In particular, when you perform Product updates WooCommerce heavily leans on the Action Scheduler table to maintain their product attribute lookup tables. You can optimise this even further with our Super Speedy Filters plugin which has its own product attribute lookup tables which are far faster and quicker to maintain than the WooCommerce ones.</p>
    <?php
        echo '<p>' . sprintf(esc_html__('There are %s clearable items in the Action Scheduler table.', 'scalability-pro'), $clearable_items) . '</p>';
    // add an ajaxified button to empty the actionscheduler table now
    ?>
    <button type="button" class="button" id="empty_actionscheduler_table"><?php esc_html_e('Empty Action Scheduler Table', 'scalability-pro'); ?></button>
    <?php
        echo '<p>' . sprintf(esc_html__('There are %s clearable logs in the Action Scheduler logs.', 'scalability-pro'), $clearable_logs) . '</p>';
    ?>
        <button type="button" class="button" id="clear_action_scheduler_logs"><?php esc_html_e('Clear Action Scheduler Logs', 'scalability-pro'); ?></button>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('#empty_actionscheduler_table').click(function () {
                var data = {
                    'action': 'empty_actionscheduler_table',
                    'security': '<?php echo wp_create_nonce("empty_actionscheduler_table"); ?>'
                };
                $.post(ajaxurl, data, function (response) {
                    alert(response);
                });
            });
            $('#clear_action_scheduler_logs').click(function () {
                var data = {
                    'action': 'clear_action_scheduler_logs',
                    'security': '<?php echo wp_create_nonce("clear_action_scheduler_logs"); ?>'
                };
                $.post(ajaxurl, data, function (response) {
                    alert(response);
                });
            });
        });
    </script>
    <?php
}

function wpiperf_product_attributes_index_fix() {
    global $wpdb;
    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[wpiperf_product_attributes_index_fix]'>
        <option value='default' <?php if (array_key_exists('wpiperf_product_attributes_index_fix', $options)) selected($options['wpiperf_product_attributes_index_fix'], 'default'); ?>>Don't Optimise</option>
        <option value='optimize' <?php if (array_key_exists('wpiperf_product_attributes_index_fix', $options)) selected($options['wpiperf_product_attributes_index_fix'], 'optimize'); ?>>Optimise</option>
    </select>
    <p>WooCommerce made a product attributes lookup table to help with performance. However, they unfortunately made a mess of how this table is maintained. This option will alter SQL queries like these:        
    </p>
    <pre>
        DELETE FROM ht3_wc_product_attributes_lookup WHERE product_id = 984353 OR product_or_parent_id = 984353;
        SELECT * FROM ht3_wc_product_attributes_lookup WHERE product_id = 984353 OR product_or_parent_id = 984353;
    </pre>
    <p>These queries above need to check two columns for product_id and product_or_parent_id. This is redundant, only the product_or_parent_id needs to be checked. Enabling the option will remove the product_id column check.
    <a href="https://i.imgur.com/HWqAzZm.png">Image showing speed difference on 3 million product site</a>
    <?php
}
add_filter( 'action_scheduler_retention_period', 'spro_action_scheduler_purge' );
function spro_action_scheduler_purge( $default ) {
    $options = get_option( 'wpiperf_settings' ); 

    if ( isset( $options['action_scheduler'] ) && 'optimize' === $options['action_scheduler'] ) {
        return DAY_IN_SECONDS;
    }

    return $default; // return the default value if 'action_scheduler' isn't set to 'optimize'
}

//add ajax action empty_actionscheduler_table to empty the actionscheduler table
add_action('wp_ajax_empty_actionscheduler_table', 'empty_actionscheduler_table');
function empty_actionscheduler_table() {
    check_ajax_referer('empty_actionscheduler_table', 'security');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE status = 'complete' OR status = 'failed' OR status = 'cancelled'");
    echo esc_html__('Action Scheduler table emptied.', 'scalability-pro');
    wp_die();
}
// Add ajax action clear_action_scheduler_logs to delete the logs
add_action('wp_ajax_clear_action_scheduler_logs', 'clear_action_scheduler_logs');
function clear_action_scheduler_logs() {
    check_ajax_referer('clear_action_scheduler_logs', 'security');
    global $wpdb;
    $result = $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}actionscheduler_logs");

    // Check for failure (result will be false)
    if($result === false) {
        // Fallback to DELETE FROM
        $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_logs");
        echo esc_html__('Action Scheduler logs cleared using DELETE.', 'scalability-pro');
    } else {
        echo esc_html__('Action Scheduler logs cleared using TRUNCATE.', 'scalability-pro');
    }
    wp_die();
}


/* create function wpiperf_remove_image_sizes to read post types and provide the ability to add a new entry for that post type to the remove_image_sizes entry in our options. When a new post type is added to this repeater, it should list a checkbox of all the possible image sizes with all of them disabled by default */
function wpiperf_remove_image_sizes() {
    $options = get_option('wpiperf_settings');
    //$all_post_types = get_post_types(array('public' => true), 'objects');
    $all_post_types = array();
    $global_object = new stdClass();
    $global_object->name = 'global';
    $global_object->labels = new stdClass();
    $global_object->labels->singular_name = 'Global';

    $global_array = array('global' => $global_object);
    $all_post_types = array_merge($global_array, $all_post_types);

    $selected_post_types = isset($options['selected_post_types']) ? $options['selected_post_types'] : array();
    ?>

    <div id="wpiperf_post_types">

        <?php 
        
        foreach ($selected_post_types as $post_type_name) : ?>
            <?php
            if ($post_type_name == 'global') {
                $post_type_label = __('Global', 'scalability-pro');
            } else {
                $post_type = get_post_type_object($post_type_name);
                $post_type_label = $post_type->labels->singular_name . ' ' . __('Imports', 'scalability-pro');
            } 
            include 'partials/wpiperf_post_type_options.php';
            ?>
        <?php endforeach; ?>
    </div>

    <div id="spro_remove_images_select_container" <?php echo isset($post_type_name) && in_array($post_type_name, $selected_post_types) ? 'style="display:none"' : ''; ?>>
        <select id="wpiperf_post_type_select">
            <?php foreach ($all_post_types as $post_type_name => $post_type_object) : ?>
                <option value="<?php echo $post_type_name; ?>">
                    <?php echo $post_type_object->labels->singular_name;
                        if ($post_type_name !== 'global') {
                        echo ' ' . __('Imports', 'scalability-pro');
                        } 
                    ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="secondary" id="wpiperf_add_post_type"><?php _e('Enable', 'scalability-pro'); ?></button>
    </div>
    <script>
        const allPostTypes = <?php echo json_encode(array_values(array_diff(array_keys($all_post_types), $selected_post_types))); ?>;
        const postTypeOptionsTemplate = document.createElement('template');
        postTypeOptionsTemplate.innerHTML = '<?php
            ob_start();
            $post_type_name = '{{post_type}}';
            $post_type_label = '{{post_type_label}}';
            include 'partials/wpiperf_post_type_options.php';
            echo str_replace(array("\n", "\r", "'"), array('\n', '\r', "\\'"), ob_get_clean());
            ?>';

    </script>
    <?php
}



function wpiperf_removecustommeta() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[removecustommeta]'>
        <option value='remove' <?php selected($options['removecustommeta'], 'remove'); ?>>Remove custom-meta select box (fastest)</option>
        <option value='keep' <?php selected($options['removecustommeta'], 'keep'); ?>>Keep custom-meta select (slowest)</option>
    </select>
    <p>Page and post editing pages in wp-admin include a really badly written sql query to populate the 'custom meta' select box. This is redundant because if you want to edit meta, you can type in the meta name. Highly recommended you remove it.</p>
    <?php
}
function wpiperf_cacheorderdeliverydate() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[cacheorderdeliverydate]'>
        <option value='cache' <?php selected($options['cacheorderdeliverydate'], 'cache'); ?>>Do not calculate Order Dates (fastest)</option>
        <option value='no' <?php selected($options['cacheorderdeliverydate'], 'no'); ?>>Calculate Order Dates (slowest)</option>
    </select>
    <p>The Order Delivery Date plugin performs a table scan whenever you view your order admin screen. As your orders grow in number, this page slows down massively. The query is only used to grab the months and years of orders in order to display a select box so you can view all orders from a particular month.</p>
    <p>Enable this option to override this expensive query and change it to just grab the last 15 months, regardless of whether orders existed or not.</p>
    <?php
}



function wpiperf_cacheshortcode_onsale() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[cacheshortcode_onsale]'>
        <option value='cache' <?php if (array_key_exists('cacheshortcode_onsale', $options)) selected($options['cacheshortcode_onsale'], 'cache'); ?>>Cache results of the [sale_products] shortcode (fastest)</option>
        <option value='no' <?php if (array_key_exists('cacheshortcode_onsale', $options)) selected($options['cacheshortcode_onsale'], 'no'); ?>>Do not cache (slowest)</option>
    </select>
    <p>WooCommerce added DB caching of the badly written [sale_products] shortcode but you may still experience issues with the speed of this shortcode due to work performed in your templates. If so, enabling this cache will help. It is WPML compatible - i.e. it caches different results per language.</p>
    <p>Note: If you use a page builder to show products on sale, it is almost certainly using this shortcode and depending on your theme you may or may not see benefit from enabling this cache.</p>
    <?php
}
function wpiperf_cacheshortcode_bestselling() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[cacheshortcode_bestselling]'>
        <option value='cache' <?php if (array_key_exists('cacheshortcode_bestselling', $options)) selected($options['cacheshortcode_bestselling'], 'cache'); ?>>Cache results of the [best_selling_products] shortcode (fastest)</option>
        <option value='no' <?php if (array_key_exists('cacheshortcode_bestselling', $options)) selected($options['cacheshortcode_bestselling'], 'no'); ?>>Do not cache (slowest)</option>
    </select>
    <p>WooCommerce added DB caching of the badly written [best_selling_products] shortcode but you may still experience issues with the speed of this shortcode due to work performed in your templates. If so, enabling this cache will help. It is WPML compatible - i.e. it caches different results per language.</p>
    <p>Note: If you use a page builder to show products on sale, it is almost certainly using this shortcode and depending on your theme you may or may not see benefit from enabling this cache.</p>
    <?php
}
function wpiperf_cacheshortcode_uxproducts() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[cacheshortcode_uxproducts]'>
        <option value='cache' <?php if (array_key_exists('cacheshortcode_uxproducts', $options)) selected($options['cacheshortcode_uxproducts'], 'cache'); ?>>Cache results of the [uxproducts] shortcode (fastest)</option>
        <option value='no' <?php if (array_key_exists('cacheshortcode_uxproducts', $options)) selected($options['cacheshortcode_uxproducts'], 'no'); ?>>Do not cache (slowest)</option>
    </select>
    <p>The Flatsome theme makes use of a wrapper function for various WooCommerce shortcodes. </p>
    <p>This caches a suite of shortodes: [ux_bestseller_products], [ux_featured_products], [ux_sale_products], [ux_latest_products], [ux_custom_products], [product_lookbook], [products_pinterest_style], [ux_products].</p>
    <p>This cache is compatible with the Flatsome theme, WPML and with Woo Variation Swatches Pro which overrides the flatsome shortcodes.</p>
    <?php
}
/* Create function wpiperf_ajaxattributeedit to collect yes or no option from user */
function wpiperf_ajaxattributeedit() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[ajaxattributeedit]'>
        <option value='ajax' <?php if (array_key_exists('ajaxattributeedit', $options)) selected($options['ajaxattributeedit'], 'ajax'); ?>>Use AJAX to edit product attributes (fastest)</option>
        <option value='no' <?php if (array_key_exists('ajaxattributeedit', $options)) selected($options['ajaxattributeedit'], 'no'); ?>>Do not use AJAX (slowest)</option>
    </select>
    <p>WooCommerce loads all attribute terms on the edit product page. If you have 1000s of terms, this will make your edit product pages slow. If this applies to you, enable Ajax here to speed up your edit product pages.</p>
    <?php
}
/* Create function wpiperf_fixwoo_onboarding to collect yes or no option from user */
function wpiperf_fixwoo_onboarding() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[fixwoo_onboarding]'>
        <option value='fix' <?php if (array_key_exists('fixwoo_onboarding', $options)) selected($options['fixwoo_onboarding'], 'fix'); ?>>Fix WooCommerce onboarding (fastest)</option>
        <option value='no' <?php if (array_key_exists('fixwoo_onboarding', $options)) selected($options['fixwoo_onboarding'], 'no'); ?>>Do not fix (slowest)</option>
    </select>
    <p>WooCommerce introduced a new onboarding wizard. Even if you have already configured your store, this code uses a function called hasProducts which causes a table scan on your wp_posts table. Although this result is cached, if you are importing or editing products then that cache is wiped regularly. Enable this option to use OUR 24 hour cache to remember that you site does indeed have products. On foundthru.com this speeds up wp-admin by 10s in some cases and 70+ seconds in other cases!</p>
	<p>You can learn more and <strong>please provide feedback on this feature</strong>: <a href="https://www.wpintense.com/2022/11/04/speeding-up-woocommerce-7/">https://www.wpintense.com/2022/11/04/speeding-up-woocommerce-7/</a></p>
    <?php
}




function wpiperf_removecast() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[removecast]'>
        <option value='remove' <?php selected($options['removecast'], 'remove'); ?>>Remove cast on wp_postmeta queries (fastest)</option>
        <option value='keep' <?php selected($options['removecast'], 'keep'); ?>>Keep cast on wp_postmeta queries (slowest)</option>
    </select>
    <p>There is a CAST function applied to the 'value' column on many wp_postmeta queries. This cast is redundant since MySQL auto-casts where necessary, but worse, because a function is applied, mysql cannot use any indexes we create on these columns. Highly recommend you remove it.</p>
    <?php
}
function wpiperf_optimise_woo_groupby() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[optimisewoogroup]'>
        <option value='optimise' <?php selected($options['optimisewoogroup'], 'optimise'); ?>>Optimise Woo Group By (fastest)</option>
        <option value='no' <?php selected($options['optimisewoogroup'], 'no'); ?>>Don't optimise (slowest)</option>
    </select>
    <p>In some situations, WooCommerce runs a GROUP BY query even when there is no need. That causes either an index scan or a table scan, followed by a sort. If you see compatibility issues, this is one option you might try disabling, otherwise choose to optimise.</p>
    <?php
}
function wpiperf_optimise_private_items() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[optimiseprivatecheck]'>
        <option value='optimise' <?php selected($options['optimiseprivatecheck'], 'optimise'); ?>>Remove check for private items on front end (faster for admins)</option>
        <option value='no' <?php selected($options['optimiseprivatecheck'], 'no'); ?>>Don't optimise (slowest)</option>
    </select>
    <p>When logged in as admin, WP_Query will check both post_status = 'publish' and post_status = 'private'. This OR statement causes the index on wp_posts to not be usable after the post_status column. For quicker admin browsing, you can optimise this but note that private items will not be visible from the front end any more.</p>
    <?php
}
function wpiperf_remove_woo_order_summary() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[removewoosummary]'>
        <option value='remove' <?php selected($options['removewoosummary'], 'remove'); ?>>Remove Woo Dashboard Summary (fastest)</option>
        <option value='keep' <?php selected($options['removewoosummary'], 'keep'); ?>>Don't optimise (slowest)</option>
    </select>
    <p>If you have a lot of orders, you will notice your wp-admin pages slowing down. WooCommerce runs the Order Summary dashboard script which you probably never look at or use and it can add seconds to wp-admin page load. Order summaries are still available by going into WooCommerce -> Reports, this just removes the dashboard widget.</p>
    <?php
}
function wpiperf_top_level_cats_woo_admin() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[toplevelcatsonly]'>
        <option value='yes' <?php selected($options['toplevelcatsonly'], 'yes'); ?>>Remove Category dropdown from wp-admin > Products (fastest)</option>
        <option value='no' <?php selected($options['toplevelcatsonly'], 'no'); ?>>Don't optimise (slowest)</option>
    </select>
    <p>If you have a lot of products, the WooCommerce back end products list will be slow - to list or search - largely because of the slow category dropdown.  This option removes the category dropdown from the wp-admin woo products list.</p>
    <?php
}
function wpiperf_cache_usercounts_admin() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[cacheusercounts]'>
        <option value='yes' <?php if (array_key_exists('cacheusercounts', $options)) selected($options['cacheusercounts'], 'yes'); ?>>Cache User Counts (fastest)</option>
        <option value='no' <?php if (array_key_exists('cacheusercounts', $options)) selected($options['cacheusercounts'], 'no'); ?>>Don't Cache User Counts (slowest)</option>
    </select>
    <p>If you have a lot of users then order and user admin can slow down significantly due to a function which counts your users and performs a table scan every time. Enable this option to cache these user counts for 12 hours. This affects the user filter dropdowns provided in admin areas which include a user count per user type.</p>
    <?php
}

function spro_enable_slow_log() {

    $options = get_option('wpiperf_settings');
    $enable_slow_log = 0;
    if (isset($options['enable_slow_log'])) {
        $enable_slow_log = $options['enable_slow_log'];
    }
    ?>
    <select name='wpiperf_settings[enable_slow_log]'>
        <option value=0 <?php selected($enable_slow_log, 0); ?>>Disable Slow Query Log</option>
        <option value=1 <?php selected($enable_slow_log, 1); ?>>Enable Slow Query Log</option>
    </select>
    <p>If you are experiencing slowness and you think it is coming from slow SQL queries, you should enable your slow query log so you can provide this information to us at WP Intense.</p>
    <?php
    // Add this to your spro_enable_slow_log function
    $real_wp_content_dir = realpath(WP_CONTENT_DIR);  // Get real path in case WP_CONTENT_DIR has symbolic links
    $target_symlink = $real_wp_content_dir . '/db.php';
    $actual_target = @readlink($target_symlink); // Suppressed in case the link doesn't exist

    if ($actual_target === $real_wp_content_dir . '/plugins/scalability-pro/wp-content/db.php') {
        echo '<p>Our advanced symlink is in place. <a  href="#" id="delete_symlink">Delete Symlink</a></p>';
    } elseif ($actual_target) {
        echo '<p>A different symlink is in place. Please disable Query Monitor or other conflicting plugins to use our symlink.</p>';
    } else {
        echo '<p>No symlink found. You can create a symlink for more accurate profiling. <a href="#" id="create_symlink">Create Symlink</a></p>';
    }
    ?>
    <p><strong>Note: </strong> For this to work fully, we also modify your wp-config.php file to include our $SPRO_GLOBALS variable. If your wp-config.php is locked down, you will need to edit it and add our variable yourself. This config (symlink + wp-config) allows at least 30 queries which run prior to the 'init' hook to be profiled.</p>
    <h4>Example wp-config.php addition</h4>
    <pre>$SPRO_GLOBALS = array (
  'enable_slow_log' => true,
  'slow_query_limit' => '0.01',
  'query_pattern' => 'product',
);</pre>
<?php

}
function spro_slow_query_limit() {

    $options = get_option('wpiperf_settings');
    $limit = 0.5;
    if (isset($options['slow_query_limit'])) {
        $limit = $options['slow_query_limit'];
    }
    ?>
    <input type="number" name='wpiperf_settings[slow_query_limit]' value="<?php echo $limit; ?>" step="0.001">
    <p>Choose a time limit (in seconds) for recording slow queries.</p>
    <?php
}
function spro_query_pattern() {

    $options = get_option('wpiperf_settings');
    $query_pattern = "";
    if (isset($options['query_pattern'])) {
        $query_pattern = $options['query_pattern'];
    }
    ?>
    <input type="text" name='wpiperf_settings[query_pattern]' value="<?php echo $query_pattern; ?>">
    <p>Optionally capture all queries containing this string, along with their stack traces.</p>
    <p><strong>CAUTION: </strong> This can cause a significant performance impact - do not leave it on for long!</p>
    <?php
}

function spro_display_slow_queries() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'spro_slow_query_log';
    $results = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY time DESC LIMIT 10");
    echo '<button id="truncate-table-button">Empty Log Table</button>';

    echo '<div class="spro_slow_queries"><table id="slow-queries-table"><thead><tr><th class="spro_date">Date</th><th>URL</th><th class="spro_query">Query</th><th colspan="2"  class="trace">Trace</th><th class="spro_duration">Duration</th></tr></thead><tbody>';
    foreach ($results as $result) {
        echo '<tr><td class="spro_date"><div class="spro_sq_td_container">' . $result->time . '</div></td><td><div class="spro_sq_td_container">' . $result->url . '</div></td><td class="spro_query"><div class="spro_sq_td_container">' . $result->query . '</div></td><td colspan="2" class="trace"><pre class="spro_sq_td_container">' . $result->stacktrace . '</pre></td><td class="spro_duration"><div class="spro_sq_td_container">' . $result->duration . '</div></td></tr>';
    }
    echo '</tbody></table></div>';
}


function wpiperf_remove_woo_ajax_variations() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[removeajaxvariationscalc]'>
        <option value='remove' <?php selected($options['removeajaxvariationscalc'], 'remove'); ?>>Remove Ajax variations count (fastest)</option>
        <option value='keep' <?php selected($options['removeajaxvariationscalc'], 'keep'); ?>>Keep Ajax variations count (slowest)</option>
    </select>
    <p>If you have products with LOTS of variations, WooCommerce runs some slow code to count the variations on the product detail page. This is unnecessary so you should be able to safely remove this.</p>
    <?php
}
function wpiperf_changetoexists() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[changetoexists]'>
        <option value='change' <?php selected($options['changetoexists'], 'change'); ?>>Change to EXISTS (fastest)</option>
        <option value='keep' <?php selected($options['changetoexists'], 'keep'); ?>>Keep LEFT JOINS (slowest)</option>
    </select>
    <p><strong>WARNING - EXPERIMENTAL!</strong> This option will attempt to alter the main WP_QUERY SQL call to use WHERE EXISTS rather than a LEFT JOIN. This means that the SQL Query can avoid using a GROUP BY. This feature also removes SORTING of results. In many cases it can cause the indexes to be used properly and can avoid table scans. On our reference server, it turns a 4.6 second query (820,000 products) to a 0.05 second query. This option is EXPERIMENTAL. It definitely will not alter admin queries otherwise we might accidentally break your wp-admin pages. In future, once it's proven resilient, it may be used to optimise wp-admin calls too.</p>
	<p>You can learn more from this article: <a href="https://www.wpintense.com/2017/10/14/extra-performance-boosts-for-scalability-pro/">https://www.wpintense.com/2017/10/14/extra-performance-boosts-for-scalability-pro/</a></p>
    <?php
}
function wpiperf_optimise_woo_delete_options() {

    $options = get_option('wpiperf_settings');
    ?>
    <select name='wpiperf_settings[optimisewoodeleteoptions]'>
        <option value='optimise' <?php selected($options['optimisewoodeleteoptions'], 'optimise'); ?>>Optimise DELETE wp_options operations (fastest)</option>
        <option value='keep' <?php selected($options['optimisewoodeleteoptions'], 'keep'); ?>>Keep slow deletes for wp_options (slowest)</option>
    </select>
    <p>WooCommerce has code on multiple pages which forces deletes against wp_options. These delete operations are written in such a way that they cannot use indexes on wp_options. This means, if you have a lot of options (e.g. a lot of transients) that your site will intermittently be locked - on our reference site (820,000+ products) this intermittent slowdown can last for up to 3 minutes. Enabling this option makes Scalability Pro rewrite these delete operations to be able to use the indexes and makes the delete operation virtually instant.</p>
	<p>You can learn more from this article: <a href="https://www.wpintense.com/2017/10/14/extra-performance-boosts-for-scalability-pro/">https://www.wpintense.com/2017/10/14/extra-performance-boosts-for-scalability-pro/</a></p>
    <?php
}
function wpiperf_settings_section_callback() {
    //todo: make multilingual friendly
    global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$options = get_option('wpiperf_settings');

	$wpiurls = array();
	
	$url = get_home_url();
	if ( is_ssl() ) { $url = set_url_scheme( $url, 'https' ); }
	$wpiurls[] = $url;

	$url = get_admin_url();
	if ( is_ssl() ) { $url = set_url_scheme( $url, 'https' ); }
	$wpiurls[] = $url;

	try {
		if (class_exists('WooCommerce')) {
			$url = get_permalink( wc_get_page_id( 'shop' ));
			if ( is_ssl() ) { $url = set_url_scheme( $url, 'https' ); }
			$wpiurls[] = $url;
		}
	}catch (Exception $e) {		
	}

	try {
		$url = get_home_url() . "?s=test+search+performance";
		if ( is_ssl() ) { $url = set_url_scheme( $url, 'https' ); }
		$wpiurls[] = $url;
	}catch (Exception $e) {		
	}
	if ( 'page' === get_option( 'show_on_front' ) ) {
		$url = get_permalink( get_option( 'page_for_posts' ) );
		if ( is_ssl() ) { $url = set_url_scheme( $url, 'https' ); }
		$wpiurls[] = $url;
	}
	$args = array( 'numberposts' => '1');
	$recent_posts = wp_get_recent_posts( $args );
	foreach( $recent_posts as $recent ){
		$url = get_permalink($recent["ID"]);
		if ( is_ssl() ) { $url = set_url_scheme( $url, 'https' ); }
		$wpiurls[] = $url;
	}
	echo "<script>var wpisiteurls = " . json_encode($wpiurls) . ";</script>";
	
	//set up profiling table
    /*
	$sql = "CREATE TABLE " . $wpdb->prefix . "scalability_pro_profiling (
        profileid varchar(50) not null,
        url varchar(2000) not null,
        profiledt datetime not null,
        results TEXT NOT NULL
    );";
    dbDelta($sql);	
    */
		?>
		<script>var profileresults = [];
		</script>
        <input name="wpiperf_settings[last_tab]" id="spro_tab" type="hidden" value="<?php if (array_key_exists('last_tab', $options)) echo intval($options['last_tab']); ?>" />
		<div id="wpisp_perf_profile" style="display:none"><h2>Performance Profile</h2>
			<div id="perfprofile"></div>
		</div>
		<div id="wpisp-first-run">
			<div class="row">
				<div class="">                		
					<section class="dev-box wp-sp-container" id="wp-sp-welcome-box">
						<div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
							<h3 tabindex="0">SCALABILITY PRO</h3>
						</div>
						<div> 
							<p>Welcome to Scalability Pro by <a href="https://www.wpintense.com/">www.wpintense.com</a>. This plugin helps you with <em>scalability</em> by optimising your database and giving you options to eliminate some unneccessary and slow functionality from WordPress and WooCommerce. This allows you to have a fast enough site and server to populate your cache using your favourite caching plugin.</p>
							<p>1 million + products is achievable on a $40pcm server. Follow the steps below to get started.</p>
						<ol><li>Create indexes by clicking the create index button below</li>
							<li>Set as many options as you can below, depending on your compatibility level and feature requirements (see notes for each option below)</li>
							<li>Your site will be far faster and more scalable - now you can add a caching plugin - any page caching plugin will do.</li>
							</ol>
							<p>Note: To measure page performance you measure TTFB (Time to First Byte) - to do this you can use the Query Monitor plugin. Load your slow pages and you'll see the TTFB displayed in your WP admin bar.</p>
							<p>If you are still having performance/scalability issues after the above steps:</p>
							<ol><li>Check your hosting is good quality</li>
							<li>Run Query Monitor on your slow pages - if you have > 100 queries on your slow pages, use the 'group by component' feature of Query Monitor to find out which plugins to deactivate</li>
							</ol>
							<p>For more help, please visit <a href="https://www.wpintense.com/">www.wpintense.com</a>. We're making WordPress the fastest CMS on the planet.</p>
						</div>
						<!-- Content -->
					</section>
				</div>
			</div>
		</div>
		<?php
	$optionscount = $wpdb->get_var("SELECT count(*) from $wpdb->options where autoload = 'yes'");
	if ($optionscount > 1000) {
		?>
		<div class="row" style="margin-bottom:20px"> 
			<div class="block float-l sp-welcome-wrapper">
				<section class="dev-box bulk-smush-wrapper wp-sp-container mb-0"> 
					<div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
						<h3 tabindex="0">PERFORMANCE WARNING</h3>
						<div class="sp-container-subheading roboto-medium">

						</div>			
					</div>
					<div class="box-container">				
						<p>You have <?php echo $optionscount; ?> options set to autoload. This is too many and will be slowing down your site significantly. <a target="_blank" href="https://www.wpintense.com/knowledgebase/how-do-i-fix-the-performance-warning-of-too-many-options-set-to-autoload/">Read our autoload options guide</a> to learn how to fix this quickly and easily.</p>
					</div>
				</section>				
			</div>
		</div>	
		<?php
	}
	?>
    </div>
    <?php
}
function spro_indexes_callback() {
    //todo: make multilingual friendly
    global $wpdb;
    ?>
    <div class="row">
        <div class="wp-spshit-container">
            <section class="dev-box bulk-smush-wrapper wp-sp-container" id="wp-sp-bulk-wrap-box">
                <div class="wp-sp-container-header box-title" xmlns="http://www.w3.org/1999/html">
                    <h3 tabindex="0">Index Status</h3>
                    <div class="sp-container-subheading roboto-medium">

                    </div>			
                </div>
                <div class="box-container">				
                    <p>Below you can see the indexes created and maintained by this plugin in order to help avoid table scans.</p>
                    <p>If you suspect table scans are still occurring, follow our guide to identify them: <a target="_blank" href="https://www.wpintense.com/2016/07/26/enable-slow-query-log-and-identify-slow-queries-percona-db/">Identify slow queries and table scans</a></p>
                        <?php
                        $indexes = wpi_getIndexes();
                        $maxindexes = count($indexes);

                        $indexstats = $wpdb->get_results("
                                        SELECT DISTINCT s.TABLE_NAME, s.INDEX_NAME
                                FROM INFORMATION_SCHEMA.STATISTICS s
                                WHERE 0=0
                                and s.INDEX_NAME != 'wpi_fulltext'
                                AND s.INDEX_NAME like 'wpi%'
                                AND s.TABLE_NAME like '{$wpdb->prefix}%';");
                        $createdIndexes = array_map(function ($item) {
                            return $item->INDEX_NAME;
                        }, $indexstats);

                        echo '<table class="wpi-index-table">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Index Name</th>';
                        echo '<th>Index Table</th>';
                        echo '<th>Index Columns</th>';
                        echo '<th>Index Notes</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        foreach ($indexes as $index) {
                            $indexName = $index['indexname'];
                            // Check if the current index is in the list of created indexes
                            $isChecked = in_array($indexName, $createdIndexes) ? 'checked' : '';
                            
                            echo '<tr>';
                            echo '<td><input type="checkbox" name="indexes[]" value="' . $indexName . '" ' . $isChecked . '> ' . $indexName . '</td>';
                            echo '<td>' . $index['indextable'] . '</td>';
                            echo '<td>' . $index['indexcols'] . '</td>';
                            echo '<td></td>'; // Placeholder for the Index Notes
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        ?>
                        <tfoot>
                            <tr>
                                <td colspan="4">
                                    <input type="checkbox" id="selectAll"> Select All/Deselect All
                                </td>
                            </tr>
                        </tfoot>
                        <?php

                        echo '</table>'; // End of table

                        echo '<p><span class="wp-sp-upload-images tc"><a class="button button-cta" href="javascript:void(0);" id="wpicreateindexes" title="'.__("click here to create indexes", "wpi-performance").'">Update Indexes</a></span></p>';
                        echo '<p class="tc roboto-regular"><strong>Note: </strong>Creating the indexes may time out - refresh this page to view progress and click to create them again if you have to - it will continue from where it left off until all are created</p>';

                        if (count($indexstats) > 0) {
                            echo '<p><span class="wp-sp-upload-images tc"><a href="javascript:void(0);" class="secondary" id="wpidropindexes" title="'.__("Drop All indexes", "wpi-performance").'">Drop All indexes</a></span></p>';
                        }
                    ?>		
                </div>
            </section>				
        </div>
    </div>
                    </div>
	
    <?php
}
add_action('wp_ajax_wpi_createindexes', 'wpi_createindexes');
add_action('wp_ajax_wpi_dropindexes', 'wpi_dropindexes');




function spro_options_page() { ?>
    <div class="wrap">
        
        <h2>Scalability Pro <?php _e('Settings', 'scalability-pro'); ?></h2>
        <?php
        global $wpdb;
        $options = get_option('wpiperf_settings');
        $tabs = array(
            'intro' => __('Intro','scalability-pro'),
            'indexes' => __('Indexes','scalability-pro'),
            'archive' => __('Archive Pages','scalability-pro'),
            'singles' => __('Single Pages','scalability-pro'),
            'imports' => __('Imports','scalability-pro'),
            'woocommerce' => __('WooCommerce', 'scalability-pro'),
            'wp-admin' => __('WP Admin','scalability-pro'),
            'slow-query-log' => __('Slow Query Log','scalability-pro')
        );
        echo "<h2 class=\"nav-tab-wrapper\">";
        $class = " nav-tab-active";
        foreach($tabs as $tabId => $tabName){
            echo '<a class="nav-tab'.$class.'" href="#'.$tabId.'">'.$tabName.'</a>';
            $class = "";
        }
        echo "</h2>";

        ?>
        <form action='options.php' method='post'>		
        <?php
        settings_fields('wpi_perf_page');
        do_settings_sections('wpi_perf_page');
        submit_button();
        ?>
        </form>
    </div>
    <?php
}


//add_filter('posts_distinct', 'awd_improvewoocommerce_performance_groupby_distinct', PHP_INT_MAX, 2);
function awd_improvewoocommerce_performance_groupby_distinct($distinct, $wp_query) {
    $options = get_option('wpiperf_settings');
    if (isset($options['optimisewoogroup']) && $options['optimisewoogroup'] == 'optimise') {
        $distinct = "DISTINCT";
    }
    return $distinct;
}
add_filter('posts_fields', 'awd_improvewoocommerce_performance_groupby_fields', PHP_INT_MAX, 2);
function awd_improvewoocommerce_performance_groupby_fields($fields, $wp_query) {
    $options = get_option('wpiperf_settings');
    global $wpdb;

    if (isset($options['optimisewoogroup']) && $options['optimisewoogroup'] == 'optimise') {
        if ($fields == "{$wpdb->posts}.ID" || $fields == "{$wpdb->posts}.*") {        
            $wp_query->set('spro_possibly_remove_group_by', true);
        }
    }
    return $fields;
}

add_filter('posts_clauses', 'awd_improvewoocommerce_performance_groupby_clauses', PHP_INT_MAX - 5, 2);
function awd_improvewoocommerce_performance_groupby_clauses($clauses, $wp_query) {
    global $wpdb;
    $options = get_option('wpiperf_settings');
    if ( defined( 'DOING_CRON' ) ) {
        return $clauses;
    }
    if ( defined( 'DOING_ADMIN' ) ) {
        return $clauses;
    }
    if (is_admin()) {
        return $clauses;
    }

    if ($wp_query->get('spro_possibly_remove_group_by')) {
        if ($clauses['join'] == '' && ($clauses['fields'] == "{$wpdb->prefix}posts.*" || $clauses['fields'] == "*")) {
            $clauses['distinct'] = str_ireplace('DISTINCT', '', $clauses['distinct']);
            $clauses['groupby'] = '';
        }
    }
    if ($wp_query->is_main_query()) {
        if (isset($options['sortorder']) && $options['sortorder'] == 'natural' && !is_array($wp_query->get('orderby')) 
            && in_array($wp_query->get('post_type'), SPRO_REMOVE_SORT_ORDER_POST_TYPES)
            && !isset($_GET['orderby']) 
            && $clauses['orderby'] !== 'relevance'
            )  {
            $clauses['orderby'] = '';
        }
    }
    return $clauses;
}


/* Removing the CAST on the query allows mysql to use the indexes on postmeta table - helps a LOT of places - HOT PICKS, shop, search, any meta_value based queries */
add_filter('posts_where', 'awdff_remove_cast_on_meta_query', PHP_INT_MAX);
function awdff_remove_cast_on_meta_query($where) {
    //gets the global query var object
    global $wp_query, $wpdb;

	$options = get_option('wpiperf_settings');
    if (isset($options['removecast']) && $options['removecast'] == 'remove') {
        // There is a new index added on postmeta.meta_value - limited to 15 characters - but importantly this index cannot be used if the meta_value column is unneccessarily cast to CHAR value as wp does in core
        // this statement reverts this back to remove the cast allowing mysql to use the index resulting in major speed gains
        $where = str_replace("CAST(" . $wpdb->prefix . "postmeta.meta_value AS CHAR)", $wpdb->prefix . "postmeta.meta_value ", $where);
    }
	return $where;
}

add_action('wp', 'wpi_fake_pagination_alttechnique', PHP_INT_MAX);

function wpi_fake_pagination_alttechnique() {
    global $wp_query;
    if (is_admin()) {
        return;
    }
    $options = get_option('wpiperf_settings');


    if ($wp_query->is_main_query()) {
        if (isset($options['calctotals']) && $options['calctotals'] == 'remove') {
            $wp_query->max_num_pages = 100;
            if (isset($GLOBALS['woocommerce_loop'])) {
                $GLOBALS['woocommerce_loop']['total'] = 9999;
            }
			$wp_query->found_posts = PHP_INT_MAX;
        }
    }
}

function wpi_fake_pagination($found_posts) {
    global $wp_query;
    $wp_query->max_num_pages = 100;
    return PHP_INT_MAX; // fakes pagination if CALC_TOTALS (SQL_CALC_FOUND_ROWS) is disabled
}

function custom_wc_ajax_variation_threshold( $qty, $product ) {
	$options = get_option('wpiperf_settings');
    if (isset($options['removeajaxvariationscalc']) && $options['removeajaxvariationscalc'] == 'remove') {
        $qty = 1;
    }
	return $qty;
}
add_filter( 'woocommerce_ajax_variation_threshold', 'custom_wc_ajax_variation_threshold', 10, 2 );

$options = get_option('wpiperf_settings');
if (isset($options['removecustommeta']) && $options['removecustommeta'] == 'remove') {
    add_filter('query', 'remove_awful_meta_box_select_query');
}
if (isset($options['wpiperf_product_attributes_index_fix']) && $options['wpiperf_product_attributes_index_fix'] == 'optimize') {
    add_filter('query', 'spro_optimise_attributes_query');
}
function remove_awful_meta_box_select_query($sql){
	global $wpdb;
	if (strpos($sql, "WHERE meta_key NOT BETWEEN '_' AND '_z'")) {
		$sql = "select meta_key from $wpdb->postmeta limit 1";
	}
    return $sql;
}
function spro_optimise_attributes_query($sql){
    if (preg_match("/product_id\s*=\s*(\d+)/", $sql, $matches1) && preg_match("/product_or_parent_id\s*=\s*(\d+)/", $sql, $matches2)) {
        $productId = $matches1[1];
        $parentProductId = $matches2[1];
    
        if ($productId === $parentProductId && !empty($productId)) {
            $stringToRemove = "product_id = " . $productId . " OR ";
            $sql = str_replace($stringToRemove, "", $sql);
        }
    }
    return $sql;
}
if (isset($options['cacheorderdeliverydate']) && $options['cacheorderdeliverydate'] == 'cache') {
    add_filter('query', 'spro_cacheorderdeliverydate');
}
function spro_cacheorderdeliverydate($sql) {
    if (trim($sql) == "SELECT YEAR( FROM_UNIXTIME( meta_value ) ) as year, MONTH( FROM_UNIXTIME( meta_value ) ) as month, CAST( meta_value AS UNSIGNED ) AS meta_value_num
FROM wp_postmeta
WHERE meta_key = '_orddd_timestamp'
GROUP BY year, month
ORDER BY meta_value_num DESC") {
        $sql = "select YEAR(DATE_SUB(now(), INTERVAL n MONTH)) as year, MONTH(DATE_SUB(now(), INTERVAL n MONTH)) as month, 1 as meta_value_num
        from 
        ( 
            SELECT 0 n UNION ALL SELECT 1  UNION ALL SELECT 2  UNION ALL 
            SELECT 3   UNION ALL SELECT 4  UNION ALL SELECT 5  UNION ALL
            SELECT 6   UNION ALL SELECT 7  UNION ALL SELECT 8  UNION ALL
            SELECT 9   UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL
            SELECT 12  UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL 
            SELECT 15
        ) nums;";
    }
    return $sql;
}

//defer term counting - improves import speed
//do daily term counting instead
function avoid_term_recounts( $data , $postarr ) {
  // do something with the post data
	$options = get_option('wpiperf_settings');
    if (isset($options['defertermcounting']) && $options['defertermcounting'] == 'remove') {
        wp_defer_term_counting( true );
        wp_defer_comment_counting( true );		
    }

  return $data;
}
add_filter( 'wp_insert_post_data', 'avoid_term_recounts', '99', 2 );

function spro_recount_override($terms, $taxonomy) { // stub function to be used to replace the term recount functions
//    error_log('bypassing recount for ' . $taxonomy->name);
}
function spro_change_update_count_callback() {
    global $wp_taxonomies;
    //check if manual recounts requested, if so, abort this action
	$options = get_option('wpiperf_settings');
    if (isset($options['defertermcounting']) && $options['defertermcounting'] == 'remove') {
        foreach ($wp_taxonomies as $taxonomy => &$tax_object) {
            if (in_array($taxonomy, SPRO_ALWAYS_DO_TERM_RECOUNTS)) {
                continue;
            }
            $tax_object->original_update_count_callback = $tax_object->update_count_callback; // store original function in case we later call spro_recount
            $tax_object->update_count_callback = 'spro_recount_override'; // call stub for recounts
        }
    }

    if (1==1 || isset($options['image_sizes']['global']) ) {
        add_action('template_redirect', 'spro_remove_image_sizes', PHP_INT_MAX);
        add_filter('wp_get_attachment_metadata', 'spro_override_image_sizes_front_end', PHP_INT_MAX, 2);
        add_filter( 'intermediate_image_sizes_advanced', 'spro_prevent_image_resizing', 10, 3 );
    }   

}
add_action( 'init', 'spro_change_update_count_callback' );
add_action('template_redirect', 'spro_remove_image_sizes', PHP_INT_MAX);
add_filter('wp_get_attachment_metadata', 'spro_override_image_sizes_front_end', PHP_INT_MAX, 2);
add_filter( 'intermediate_image_sizes_advanced', 'spro_prevent_image_resizing', 10, 3 );

function woo_avoid_term_recounts($recountterms) {
	$options = get_option('wpiperf_settings');
    if (isset($options['defertermcounting']) && $options['defertermcounting'] == 'remove') {
        return false;
    }
    return $recountterms;
} 
add_filter('woocommerce_product_recount_terms', 'woo_avoid_term_recounts');

function spro_avoidrecountsonimport () {
    add_filter('woocommerce_product_recount_terms', '__return_false');
}
add_action('pmxi_before_xml_import', 'spro_avoidrecountsonimport');

function spro_recount_items() {
    $options = get_option('wpiperf_settings');

    remove_filter('woocommerce_product_recount_terms', 'woo_avoid_term_recounts');
    global $wp_taxonomies;

    foreach ($wp_taxonomies as $taxonomy => &$tax_object) {
        if (isset($tax_object->original_update_count_callback)) {
            $tax_object->update_count_callback = $tax_object->original_update_count_callback; //restore the update_count_callback function
        }
        $terms = get_terms(
            $taxonomy,
            array(
                'hide_empty' => false,
                'fields'     => 'id=>parent',
            )
        );
        if (is_array($terms) && count($terms) > 0) {
            if (in_array('product', $tax_object->object_type)) {
                if (function_exists('_wc_term_recount')) {
                    _wc_term_recount( $terms, $tax_object, true, false ); // call wc term recount (accepts $tax object not string)
                }
            } else {
                wp_update_term_count_now($terms, $taxonomy); // this takes $taxonomy string
            }
        }
        // Unset to free memory
        unset($terms);

        // Force garbage collection
        gc_collect_cycles();
    }
}

//wp_schedule_event( strtotime('16:20:00'), 'daily', 'import_into_db' );
register_activation_hook(__FILE__, 'wpisp_activation');
function wpisp_activation() {
    if (! wp_next_scheduled ( 'wpisp_performance_daily' )) {
	wp_schedule_event(strtotime('02:00'), 'daily', 'wpisp_performance_daily');
    }
}

add_action('wpisp_performance_daily', 'cron_wpisp_performance_daily');
function cron_wpisp_performance_daily() {
	// run daily maintenance

    $options = get_option('wpiperf_settings');
    if (isset($options['defertermcounting']) && $options['defertermcounting'] == 'remove') {
        wp_defer_term_counting( false );
        wp_defer_comment_counting( false );
        spro_recount_items();
    }
    global $wpdb;
    // delete the vip cache daily - had to shift it into here because when imports are happening the cache is constantly being flushed which dramatically messes up large stores
    // note: this query does not delete from the object cache, but these options are not set to autooload so we should be fine
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name like 'spro_permacache%';");
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'spro_pmxe';");
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}scalability_pro_post_count_cache;");
}

function wpisp_add_perf_stats_var( $vars ){
  $vars[] = "wpisp_measure_performance";
  return $vars;
}
add_filter( 'query_vars', 'wpisp_add_perf_stats_var' );

	
 
add_action( 'wp_ajax_wpisp_saveperfresults', 'wpisp_saveperfresults' );
function wpisp_saveperfresults() {
	global $wpdb; 

	$sql = "INSERT INTO {$wpdb->prefix}scalability_pro_profiling (profileid,url,profiledt, results) VALUES (%s,%s,%s,%s)";
	$sql = $wpdb->prepare($sql,$_POST['profileid'],$_POST['url'],date('Y-m-d H:i:s'), $_POST['profileresults']);
	
	$wpdb->query($sql);
}

//add_filter('postmeta_form_keys', array());


function wpisp_remove_woo_meta_boxes() {
	$options = get_option('wpiperf_settings');
    if (isset($options['removewoosummary']) && $options['removewoosummary'] == 'remove') {
        remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal');
    }
}
add_action( 'admin_menu', 'wpisp_remove_woo_meta_boxes', PHP_INT_MAX );
add_action('wp_user_dashboard_setup', 'wpisp_remove_woo_meta_boxes', PHP_INT_MAX);
add_action('wp_dashboard_setup', 'wpisp_remove_woo_meta_boxes', PHP_INT_MAX);


function wpisp_changeTaxToExists($tax) {
    global $wpdb;
    //this is complicated! Need to do recursive call to unnest the tax queries as they can be nested and convert them to WHERE EXISTS
    $sql ='';
    if (!in_array('relation', $tax)) { //-- turns out there isn't always a relation
        return $tax;
    }
    $originalrelation = $tax['relation']; //there's always a relation 
    $relation = '';
    foreach($tax as $key=>$taxarray) {
        if ($key === 'relation') {
            continue;
        }
        if (array_key_exists('relation', $taxarray)) {
            $subtax = wpisp_changeTaxToExists($taxarray);
            $sql .= $relation . "(" . $subtax['sql']. ")";
            $tax[$key] = $subtax['tax']; //override $tax if sub changed it
            $relation = $originalrelation;
        } else {
            if ($taxarray['field'] == 'term_taxonomy_id') {
                //convert tax, field, terms, all params to WHERE EXISTS
                $op = " EXISTS ";
                if ($taxarray['operator'] === "NOT IN") {
                    $op = " NOT EXISTS ";
                }
                $sql .= $relation . $op . "
                (select * from " .$wpdb->term_relationships . " tr
                inner join " .$wpdb->term_taxonomy . " tt on tr.term_taxonomy_id = tt.term_taxonomy_id 
                where tt.taxonomy = '" . $taxarray['taxonomy'] . "' 
                and " . $wpdb->posts .".ID = tr.object_id 
                AND tr.term_taxonomy_id IN (" . implode(',', $taxarray['terms']) . "))"; // is the check for term_taxonomy_id redundant?

                $relation = $originalrelation;
                unset($tax[$key]);
            }
        }
    }
    if (!empty($sql)) {
        $sql = ' AND (' . $sql . ") "; //the first AND is because the tax query is required - it's only multiple tax queries that can be OR'd
    }
    if (count($tax) == 1) $tax = array(); // all that's left is the relation key, no actual taxonomies
    $output = array('tax'=> $tax, 'sql' => $sql);
    return $output;
}
add_filter('posts_where', 'wpintense_changetaxtoexists', 500, 2);
function wpintense_changetaxtoexists($where, $query) {
    if (!empty($query->get('wpintense_taxexists'))) {
        $where .= $query->get('wpintense_taxexists');
    }
    return $where;
}

function wpisp_improvewoocommerce_performance($query) {
    global $wpdb;
    if ( defined( 'DOING_CRON' ) ) return;
    if ( defined( 'DOING_ADMIN' ) ) return;
    if (wp_doing_ajax()) return;
    if ($query->is_favicon()) return;
    if ($query->is_robots()) return;
    if (!$query->is_main_query()) return;

    $options = get_option('wpiperf_settings');

    $query->set('spro_check_offset', true);
    add_filter('posts_request', 'spro_fix_offset_queries', PHP_INT_MAX, 2);

    if (is_admin()) {
        if ($query->get('post_type') == 'product' && $query->get('posts_per_page') == 1) {
            $e = new \Exception;
            if (strpos($e->getTraceAsString(), 'should_display_widget') !== false) {
                $query->set('spro_remove_orderby', true);
            }
        }
        if (isset($options['calctotals_admin']) && $options['calctotals_admin'] == 'remove') {
            $query->set('no_found_rows', true);
            add_filter('found_posts', 'wpi_fake_pagination', 1, 2);
        }
        return;
    } else {
        if (isset($options['optimiseprivatecheck']) && $options['optimiseprivatecheck'] == 'optimise') {
            $query->set('post_status', 'publish');
        }
    }
    if (isset($options['changetoexists']) && $options['changetoexists'] == 'change') {
        $taxqueries = $query->get('tax_query');
        if (is_array($taxqueries)) {
            $alteredtax = wpisp_changeTaxToExists($taxqueries);
            if (array_key_exists('tax', $alteredtax)) {
                $query->set('tax_query', $alteredtax['tax']);
            }
            if (array_key_exists('sql', $alteredtax)) {
                $query->set('wpintense_taxexists', $alteredtax['sql']);
            }
        }
    }
    if (isset($options['sortorder']) && $options['sortorder'] == 'natural' && !is_array($query->get('orderby')) && !isset($_GET['orderby']) && in_array($query->get('post_type'), SPRO_REMOVE_SORT_ORDER_POST_TYPES)) {
        $orderbycolumn = trim($query->get('orderby'));
        if (strpos($orderbycolumn, "meta_value") !== false) {
            $query->set('meta_key', '');			
        }        
    }
    if (isset($options['calctotals']) && $options['calctotals'] == 'remove') { 
        $post_types = $query->get('post_type');
        if (!is_array($post_types)) {
            $post_types = array($post_types);
        }
        if (
            in_array('product', $post_types) 
            || in_array('product_variation', $post_types) 
            || in_array('post', $post_types) 
            || ($query->is_tax() && in_array($query->get('taxonomy'), array('product_cat', 'product_tag', 'pa_brand', 'brand', 'category', 'post_tag')))
            ) {
            $query->set('no_found_rows', true);
            add_filter('found_posts', 'wpi_fake_pagination', 1, 2);
        } else {
            // LifterLMS Course PostType breaks on the dashboard if we remove no_found_rows and they seem to use empty post_type
            //error_log(print_r($query->get('post_type'), true));
        }
    }
}
add_action('pre_get_posts', 'wpisp_improvewoocommerce_performance', 9999999);

add_action('woocommerce_before_shop_loop', 'scalability_pro_fix_loop_total', PHP_INT_MAX);
function scalability_pro_fix_loop_total() {
    $options = get_option('wpiperf_settings');
    if (isset($options['calctotals']) && $options['calctotals'] == 'remove') {
        if (isset($GLOBALS['woocommerce_loop'])) {
            $GLOBALS['woocommerce_loop']['total'] = 9999;
        }    
    }
}
add_filter('posts_orderby', 'wpi_orderby_pages_callback', 10, 2);

// The posts_orderby filter
function wpi_orderby_pages_callback($orderby_statement, $wp_query) {

    if ( defined( 'DOING_CRON' ) ) {
        return $orderby_statement;
    }
    if ($wp_query->get('spro_remove_orderby')) {
        return '';
    }
    if ( defined( 'DOING_ADMIN' ) ) {
        return $orderby_statement;
    }
    if (is_admin()) {
        return $orderby_statement;
    }
    if (wp_doing_ajax()) {
        return $orderby_statement;
    }
    if (!$wp_query->is_main_query()) {
        return $orderby_statement;
    }
	if ($wp_query->is_main_query()) {
        $options = get_option('wpiperf_settings');
		if (isset($options['sortorder']) && $options['sortorder'] == 'natural'  && !is_array($wp_query->get('orderby')) && !isset($_GET['orderby']) && in_array($wp_query->get('post_type'), SPRO_REMOVE_SORT_ORDER_POST_TYPES)) {
			return '';
		}
	}
	return $orderby_statement;
}

function fww_remove_categories_from_products_page( $args, $taxonomies ) {
	if (!is_admin()) {
		return $args;
	}
	require_once(ABSPATH . 'wp-admin/includes/screen.php');
	$screen = get_current_screen();
	if (!isset($screen->id)) {
		return $args;
	}
	if ($screen->id != 'edit-product') {
		return $args;
	}
	$options = get_option('wpiperf_settings');
	if (isset($options['toplevelcatsonly']) && $options['toplevelcatsonly'] == 'yes') {
		if (isset($args['taxonomy']) && count($args['taxonomy']) == 1) {
			if ($args['taxonomy'] == 'product_type') {
				if (!isset($args['object_ids'])) {
					$args['term_taxonomy_id'] = -1;
				} else {
					if (!is_array($args['object_ids'])) {
						$args['term_taxonomy_id'] = -1;
					}
				}
			}
			if ($args['taxonomy'] == 'product_cat') {
				if (!isset($args['object_ids'])) {
					$args['term_taxonomy_id'] = -1;
					$args['parent'] = 0;
				} else {
					if (!is_array($args['object_ids'])) {
						$args['term_taxonomy_id'] = -1;
						$args['parent'] = 0;
					}
				}
			}
		}
	}
	return $args;
}
add_filter( 'get_terms_args', 'fww_remove_categories_from_products_page', 10, 2 );


/*
function fww_remove_product_type_from_products_admin($tq) {
	if (isset($tq->query_vars) && isset($tq->query_vars['taxonomy']) && isset($tq->query_vars['taxonomy'][0]) && count($tq->query_vars['taxonomy']) == 0 && $tq->query_vars['taxonomy'][0] == 'product_type') {
		error_log("TQ:
		" . print_r($tq, true));
	}
}
add_action('pre_get_terms', 'fww_remove_product_type_from_products_admin');
*/


function scalabilitypro_changewpquerytoexists($sql){
	global $wp_query, $wpdb;
	if (is_admin()) {
		return $sql;
	}
    if (isset($wp_query) && is_main_query()) {
		if (strpos($sql, "WHEREISMAINQUERY") !== false) {
			$sql = str_replace("WHEREISMAINQUERY", "", $sql);
			$meta_query = $wp_query->meta_query;
			if ($meta_query !== false) {
				$mq = $meta_query->get_sql(
					'post',
					$wpdb->posts,
					'ID',
					null
                );
				$tmpsql = $sql;
				//if meta_query order by is being used, then abort replacing this as left join IS required
				$orderbycolumn = trim($wp_query->get('orderby'));
				if (isset($mq['join']) && !empty($mq['join']) && strpos($orderbycolumn, "meta_value") === false) {
					$metaexists = str_replace("LEFT JOIN ", "AND EXISTS( SELECT * FROM ", $mq["join"]);
					$metaexists = str_replace("INNER JOIN ", "AND EXISTS( SELECT * FROM ", $metaexists);
					$metaexists = str_replace(" ON ", " WHERE ", $metaexists);
					$metaexists = str_replace("postmeta.post_id", "postmeta.post_id " . $mq["where"] . ") ", $metaexists);
					$tmpsql = str_replace($mq["where"], "", $tmpsql);
					$tmpsql = str_replace($mq["join"], "", $tmpsql);
					$tmpsql = str_replace("WHERE 1=1", "WHERE 1=1 " . $metaexists . " ", $tmpsql);
					
					if (strpos($tmpsql, "INNER JOIN") === false && strpos($tmpsql, "LEFT JOIN") === false) {
						$tmpsql = str_replace("GROUP BY {$wpdb->posts}.ID", "", $tmpsql);
					}
					
					$tmpsql = str_replace("ORDER BY {$wpdb->posts}.post_date DESC", "", $tmpsql);
					$sql = $tmpsql;
				}		
            }
            $tax_query = '';
            if (isset($wp_query->tax_query) && isset($wp_query->tax_query->queries)) {
                $tax_query = new WP_Tax_Query($wp_query->tax_query->queries);
            }
//			if (!empty($tax_query) && $tax_query !== false && count($tax_query->queries) == 1) { // only works when ONE tax query is selected
            if (!empty($tax_query) && $tax_query !== false) { // only works when ONE tax query is selected
                $tq = $tax_query->get_sql($wpdb->posts,'ID');
                if (isset($tq['join']) && !empty($tq['join'])) {
                    $sql = str_replace($tq['join'], '', $sql);
                    $sql = str_replace($tq['where'], '', $sql);
                    /*
                    $tq['join'] = str_replace('LEFT JOIN', 'INNER JOIN', $tq['join']);
                    $joinreplace = '/'.preg_quote('INNER JOIN', '/').'/';
                    $onreplace = '/'.preg_quote('ON (', '/').'/';
                    $joinchangedtoexists = preg_replace($joinreplace, "AND EXISTS (SELECT 1 FROM", $tq["join"], 1);
                    $joinchangedtoexists = preg_replace($onreplace, "WHERE (", $joinchangedtoexists, 1);
                    $joinchangedtoexists .= " " . $tq['where'] . ')';
                    $sql = str_replace("WHERE 1=1", "WHERE 1=1 ". $joinchangedtoexists, $sql);
                    */
                    $existswhere = '';
                    foreach($wp_query->tax_query->queries as $singletaxquery) {
                        $sq = new WP_Tax_Query(array($singletaxquery));
                        $newsql = $sq->get_sql($wpdb->posts,'ID');
                        if (isset($newsql['join']) && !empty($newsql['join'])) {
                            $newsql['join'] = str_replace('LEFT JOIN', 'INNER JOIN', $newsql['join']);
                            $joinreplace = '/'.preg_quote('INNER JOIN', '/').'/';
                            $onreplace = '/'.preg_quote('ON (', '/').'/';
                            $joinchangedtoexists = preg_replace($joinreplace, "AND EXISTS (SELECT 1 FROM", $newsql["join"], 1);
                            $joinchangedtoexists = preg_replace($onreplace, "WHERE (", $joinchangedtoexists, 1);
                            $joinchangedtoexists .= " " . $newsql['where'] . ')';
                            $existswhere .= $joinchangedtoexists;
                        }
                    }
                    $sql = str_replace("WHERE 1=1", "WHERE 1=1 ". $existswhere, $sql);

				}							
			}
		}
	}
	return $sql;
}


function wpi_str_replace_first($from, $to, $subject)
{
    $from = '/'.preg_quote($from, '/').'/';

    return preg_replace($from, $to, $subject, 1);
}
function wpi_substr($str, $startertext, $endtext, $toend = false) {
	$startpos = strpos($str, $startertext);
	$endpos = strpos($str, $endtext);
	if ($startpos === false) {
		if (!$toend) {
			return "";
		} else {
			$startpos = 0;
		}
	}
	if ($endpos === false) {
		if (!$toend) {
			return "";
		} else {
			$endpos = strlen($str);
		}
	}	
	$length = $endpos - $startpos;
	return substr($str, $startpos, $length);	
//LEFT JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id)  LEFT JOIN wp_term_relationships AS tt1 ON (wp_posts.ID = tt1.object_id)

}
function morescalable_posts_join( $join, $query ) {    
    global $wpdb;
	if (is_admin()) {
		return $join;
	}
    if ($query->is_main_query()) {
		$join = "WHEREISMAINQUERY" . $join; // using this to identify that this is the main query - stupid wordpress doesn't pass $query into the 'query' filter...
    }
    return $join;
    
}
$options = get_option('wpiperf_settings');
if (isset($options['changetoexists']) && $options['changetoexists'] == 'change') {
    //todo: remove these completely if new way works
//	add_filter('query', 'scalabilitypro_changewpquerytoexists', 99999999);
//	add_filter( 'posts_join', 'morescalable_posts_join', 99999999, 2 );
}


function scalabilitypro_fixwootransientcleanup ($sql) {
	global $wpdb;
	if (strpos($sql, "DELETE FROM {$wpdb->options} WHERE option_name LIKE ") !== false) {
		if (strpos($sql, " OR ") !== false) {
			return $sql;
		}
		$likestatement = wpi_substr($sql, "option_name LIKE ", "ORDER BY");
		if (!empty($likestatement)) {
			$newlike = str_replace("%", "product%", $likestatement) . " OR " . str_replace("%", "orders%", $likestatement) . " OR " . str_replace("%", "shipping%", $likestatement);
			$sql = str_replace($likestatement, $newlike, $sql);
		}
		
	}
	return $sql;
}

if (isset($options['optimisewoodeleteoptions']) && $options['optimisewoodeleteoptions'] == 'optimise') {
	add_filter('query', 'scalabilitypro_fixwootransientcleanup', 999999);
}

function spro_fix_offset_queries($query, $wp_query) {
	global $wpdb;
    if (!$wp_query->get('spro_check_offset')) {
        return $query;
    }
    /* some queries perform OFFSET type queries - e.g. to grab rows 50,000 to 55,000
    There is an issue with the mysql query optimiser where it stupidly checks every row from 1 to 49,999 when it doesn't need to.
    this function fixes that.
    For example: BWP Google XML Sitemaps does this:

    SELECT p.*
    FROM wp_posts p
    WHERE p.post_status = 'publish'
    AND p.post_password = ''
    AND p.post_type = 'product'
    ORDER BY p.post_modified DESC LIMIT 600000,5000

    Rewriting it as this allows the index to be used:

    select p.*
    from 
    (
    SELECT p.id
    FROM wp_posts p
    WHERE p.post_status = 'publish'
    AND p.post_password = ''
    AND p.post_type = 'product'
    ORDER BY p.post_modified DESC LIMIT 600000,5000
    ) smallset
    join wp_posts p
    on smallset.id = p.id

    currently this is only optimised for wp_posts, and only when p.* is selected
    if others come to light in future, they could be optimised too
    */
    if (strncmp($query, "SELECT p.*", strlen("SELECT p.*")) === 0 && stripos($query, " LIMIT ") !== false) {
//    if (stripos($query, "SELECT p.*") !== false && stripos($query, " LIMIT ") !== false) {
        $count = 0;
        $newquery = str_ireplace("p.*", "p.ID", $query, $count);
        if ($count == 1) { // if we replaced more than 1, something is terribly wrong
            $newquery = "select p.* from (" . $newquery . ") smallset join {$wpdb->posts} p on smallset.id = p.id";
            $query = $newquery;
        }
    }

    /* some plugins do a weird thing where they check both post_status = 'published' AND they check 'post_date != '0000-00-00 00:00:00'
    The fact is that if a post is published, it will have a post date.
    The second fact is that this != check ruins the use of indexes and caused table scans.
    On one reference site (a client of WP Intense), they have about 2 million post entries and the perf improvement is:
    4 minutes 45 seconds per 100 posts in a sitemp -> 0.89 seconds

    Here's an example from the popular Yoast SEO plugin:

    SELECT wp_posts.ID
    FROM wp_posts
    WHERE wp_posts.post_status = 'publish'
    AND wp_posts.post_type = 'product'
    AND wp_posts.post_password = ''
    AND wp_posts.post_date != '0000-00-00 00:00:00'
    ORDER BY wp_posts.post_modified ASC LIMIT 100 OFFSET 483300
    */

    $checkpoststatus = "{$wpdb->posts}.post_status = 'publish'";
    $checkpostdate = "AND {$wpdb->posts}.post_date != '0000-00-00 00:00:00'";
    if (stripos($query, $checkpoststatus) !== false && stripos($query, $checkpostdate) !== false) {
        $query = str_replace($checkpostdate, "", $query); // remove the redundant check of post_date
    }

    if (!strpos($query, "JOIN")) { // if no joins in query then GROUP BY is never needed - stupid to perform it and add CPU cycles
        $query = str_replace("GROUP BY {$wpdb->posts}.ID", "", $query);
    }
       
    if (strpos($query, 'LIKE \'%\\"wcfm\\\\_vendor\\"%\'') !== false) {
        $query = str_replace('LIKE \'%\\"wcfm\\\\_vendor\\"%\'', '= \'a:1:{s:11:"wcfm_vendor";b:1;}\'', $query);
    }


    return $query;
}


function sppro_get_cache($postid, $userid, $view, $group) {
    global $wpdb;
    $sql = $wpdb->prepare("select cachedata from " . $wpdb->prefix . "scalability_pro_cache where postid = %d and userid = %d and cacheview = %s and cachegroup = %s",
        $postid,
        $userid, 
        $view, 
        $group);

    $data = $wpdb->get_var($sql);

    return $data;
}
function sppro_set_cache($postid, $userid, $view, $group, $data) {
    global $wpdb;


    $sql = $wpdb->insert($wpdb->prefix . "scalability_pro_cache",
        array('postid' => $postid,
            'userid' => $userid, 
            'cacheview' => $view, 
            'cachegroup' => $group,
            'cachedata' => $data),
        array('%d', '%d', '%s', '%s', '%s'));
    return true;
}
function sppro_delete_cache($postid) {
    global $wpdb;
    $sql = $wpdb->prepare("delete from " . $wpdb->prefix . "scalability_pro_cache where postid = %d", $postid);

    $wpdb->query($sql);
}

add_filter('plugin_row_meta', function($pluginMeta, $pluginFile) {
	global $SPROWidgetsUpdateChecker;

	$isRelevant = ($pluginFile == $SPROWidgetsUpdateChecker->pluginFile)
	|| (!empty($SPROWidgetsUpdateChecker->muPluginFile) && $pluginFile == $SPROWidgetsUpdateChecker->muPluginFile);
	if ( $isRelevant && current_user_can('update_plugins') ) {
		$linkUrl = get_admin_url(null, 'admin.php?page=wpintense');
		$linkText = 'License key';
		$pluginMeta[] = sprintf('<a href="%s">%s</a>', esc_attr($linkUrl), $linkText);

		$pluginMeta[] = sprintf('<a href="%s">%s</a>', esc_attr(get_admin_url(null, 'admin.php?page=scalabilitypro')), "Settings");
	}
	return $pluginMeta;
}, 20, 2);

function spro_cached_user_count( $count, $strategy, $site_id ) {
	// Respect any value already set by another filter
	if ( ! is_null( $count ) ) {
		return $count;
	}
	
	$count = get_transient( 'spro_user_count' );

	if ( $count === false ) {
		// No cached value, so fetch current user count
		$count = spro_latest_user_count( $strategy, $site_id );
	}

	return $count;
}

$options = get_option('wpiperf_settings');    
if (isset($options['cacheusercounts']) && $options['cacheusercounts'] == 'yes') {
    add_filter( 'pre_count_users', 'spro_cached_user_count', 10, 3 );
}
/**
 * Counts current users as per count_users() and stores the value for use by tc33_cached_user_count() filter.
 *
 * @param string   $strategy Optional. The computational strategy to use when counting the users.
 *                           Accepts either 'time' or 'memory'. Default 'time'.
 * @param int|null $site_id  Optional. The site ID to count users for. Defaults to the current site.
 * @return array Includes a grand total and an array of counts indexed by role strings.
 *
 * @see count_users()
 */
function spro_latest_user_count( $strategy = 'time', $site_id = null ) {
	// Unhook our filters before fetching the counts
	remove_filter( 'pre_count_users', 'spro_cached_user_count' );
	$count = count_users( $strategy, $site_id );
	add_filter( 'pre_count_users', 'spro_cached_user_count', 10, 3 );

	// Save the value in our cache for 12 hours
	set_transient( 'spro_user_count', $count, 12 * HOUR_IN_SECONDS );

	return $count;
}

function spro_cacheuxproducts($atts, $content = null, $tag = '' ) {
    $languagecode = '';
    if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
        $languagecode = ICL_LANGUAGE_CODE;
    }

    $cachekey = 'uxp_' . $languagecode . $tag . hash( 'sha512', __FUNCTION__ . json_encode( $atts ) );
	$cacheddata = get_transient( $cachekey );
	if ( $cacheddata !== false ) {
		return $cacheddata;
	}
    $content = '';
    if (function_exists('wvs_pro_ux_products')) { // woovariationswatchespro overrides the ux_products shortcode with a call to their function instead
        $content = wvs_pro_ux_products($atts, $content, $tag);
    } else {
        if (function_exists('ux_products')) {
            $content = ux_products($atts, $content, $tag);
        }
    }
    if (!empty($content)) {
        set_transient( $cachekey, $content, 12 * HOUR_IN_SECONDS );
    }
    return $content;
}
function spro_cache_saleproducts($atts) {
    $languagecode = '';
    if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
        $languagecode = ICL_LANGUAGE_CODE;
    }
    $cachekey = 'sp_' . $languagecode . hash( 'sha512', __FUNCTION__ . json_encode( $atts ) );
	$cacheddata = get_transient( $cachekey );
	if ( $cacheddata !== false ) {
		return $cacheddata;
	}
    
	$content = WC_Shortcodes::sale_products($atts);
	set_transient( $cachekey, $content, 12 * HOUR_IN_SECONDS );
    return $content;
}
function spro_cache_bestsellingproducts($atts) {
    $languagecode = '';
    if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
        $languagecode = ICL_LANGUAGE_CODE;
    }
    $cachekey = 'bsp_' . $languagecode . hash( 'sha512', __FUNCTION__ . json_encode( $atts ) );
	$cacheddata = get_transient( $cachekey );
	if ( $cacheddata !== false ) {
		return $cacheddata;
	}

	$content = WC_Shortcodes::sale_products($atts);
	set_transient( $cachekey, $content, 12 * HOUR_IN_SECONDS );
    return $content;
}
function spro_cacheshortcodes($content) {
    $options = get_option('wpiperf_settings');
    if (array_key_exists('cacheshortcode_onsale', $options) && $options['cacheshortcode_onsale'] == 'cache') {
        remove_shortcode('sale_products');
        add_shortcode('sale_products_sprocached', 'spro_cache_saleproducts');
        $content = str_replace('[sale_products]', '[sale_products_sprocached]', $content);
        $content = str_replace('[sale_products ', '[sale_products_sprocached ', $content);
    }
    if (array_key_exists('cacheshortcode_bestselling', $options) && $options['cacheshortcode_bestselling'] == 'cache') {
        remove_shortcode('best_selling_products');
        add_shortcode('best_selling_products_sprocached', 'spro_cache_bestsellingproducts');
        $content = str_replace('[best_selling_products]', '[best_selling_products_sprocached]', $content);
        $content = str_replace('[best_selling_products ', '[best_selling_products_sprocached ', $content);
    }
    if (array_key_exists('cacheshortcode_uxproducts', $options) && $options['cacheshortcode_uxproducts'] == 'cache') {
        $shortcodes = array('ux_bestseller_products', 'ux_featured_products', 'ux_sale_products', 'ux_latest_products', 'ux_custom_products', 'product_lookbook', 'products_pinterest_style', 'ux_products');

        foreach ($shortcodes as $tag) {
            remove_shortcode($tag);
            add_shortcode($tag . "_sprocached", "spro_cacheuxproducts");
            $content = str_replace("[$tag]", "[$tag" . "_sprocached]", $content);
            $content = str_replace("[$tag ", "[$tag" . "_sprocached ", $content);    
        }
    }
    return $content;
}
add_filter('the_content', 'spro_cacheshortcodes',1);


if ( is_admin() ) {
    // Cache the available months for filtering on posts/attachments/CPTs.
    add_filter( 'media_library_months_with_files', 'spro_wpcom_vip_media_library_months_with_files' );
    add_filter( 'pre_months_dropdown_query', 'spro_wpcom_vip_available_post_listing_months', 10, 2 );
}

// these functions were copied directly from github for woocommerce.com VIP customers - https://github.com/Automattic/vip-go-mu-plugins-built/blob/4a76d2aa12759e734b11f7b8f8e7e0d008cd44c9/performance/vip-tweaks.php#L84
function spro_wpcom_vip_media_library_months_with_files( $months ) {
    if ( null !== $months ) {
        // Something is already filtering, abort.
        return $months;
    }

    return spro_wcom_vip_get_available_months_for_filters( 'attachment' );
}
function spro_wcom_vip_get_available_months_for_filters( $post_type ) {
	global $wpdb;

	$cache_key = "spro_permacache_available_filter_months_$post_type";
	$months    = get_option( $cache_key, false);
	if ( is_array( $months ) ) {
		// Happiest-path, cache exists already :).
		return $months;
	}

	$extra_checks = '';
	if ( 'attachment' !== $post_type ) {
		$extra_checks = " AND post_status != 'auto-draft' AND post_status != 'trash'";
	}

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$months = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s
			$extra_checks
			ORDER BY post_date DESC
			",
			$post_type
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	update_option( $cache_key, $months, 'no' );
	return $months;
}
function spro_wpcom_vip_available_post_listing_months( $months, $post_type ) {
	if ( false !== $months ) {
		// Something is already filtering, abort.
		return $months;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['post_status'] ) ) {
		// Avoid interferring if user filtered by a particular post status.
		return false;
	}

	return spro_wpcom_vip_get_available_months_for_filters( $post_type );
}
function spro_wpcom_vip_get_available_months_for_filters( $post_type ) {
	global $wpdb;

	$cache_key = "spro_permacache_available_months_$post_type";
	$months    = get_option( $cache_key, false );
	if ( is_array( $months ) ) {
		// Happiest-path, cache exists already :).
		return $months;
	}

	$extra_checks = '';
	if ( 'attachment' !== $post_type ) {
		$extra_checks = " AND post_status != 'auto-draft' AND post_status != 'trash'";
	}

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$months = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s
			$extra_checks
			ORDER BY post_date DESC
			",
			$post_type
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	update_option( $cache_key, $months, 'no');
	return $months;
}
// if LIMIT 1 or LIMIT 0, 1, we can safely remove DISTINCT from the query which would otherwise cause a tablescan
add_filter('posts_clauses', 'spro_remove_distinct_limit_1', PHP_INT_MAX, 2);
function spro_remove_distinct_limit_1($clauses, $wp_query) {
    if (trim($clauses['limits']) == 'LIMIT 0, 1' || trim($clauses['limits']) == 'LIMIT 1') {
        $clauses['distinct'] = str_ireplace('DISTINCT', '', $clauses['distinct']);
        $clauses['groupby'] = '';
    }
    $options = get_option('wpiperf_settings');
    return $clauses;
}
function spro_fix_woo_onboarding($sql) {
    global $wpdb;
//    if (strncmp($sql, "SELECT post_status, COUNT", strlen("SELECT post_status, COUNT")) === 0) {
    if (substr($sql, 0, 25) == "SELECT post_status, COUNT") {
        $e = new \Exception;
        $trace = $e->getTraceAsString();
        unset($e);
        if (strpos($trace, "is_new_install()") !== false) {
            return "SELECT 'publish' as post_status, 1 as num_posts 
            union SELECT 'future' as post_status, 1 as num_posts
            union SELECT 'draft' as post_status, 1 as num_posts
            union SELECT 'pending' as post_status, 1 as num_posts
            union SELECT 'private' as post_status, 1 as num_posts
            union SELECT 'trash' as post_status, 1 as num_posts      
            ";
        }
        if (strpos($trace, "get_homepage_template()") !== false) {
            return "SELECT 'publish' as post_status, 5 as num_posts";
        }
        if (strpos($trace, "has_products()") !== false) {
            return "SELECT 'publish' as post_status, 1 as num_posts 
            union SELECT 'future' as post_status, 1 as num_posts
            union SELECT 'draft' as post_status, 1 as num_posts
            union SELECT 'pending' as post_status, 1 as num_posts
            union SELECT 'private' as post_status, 1 as num_posts
            union SELECT 'trash' as post_status, 1 as num_posts      
            ";
        }
    }
    return $sql;
}

/* todo: This code is awaiting testing from gfarma - https://discord.com/channels/@me/1028937439923933185/1030116452436557945
It also needs a new option and a check against that option to decide whether this should run or not
*/
function spro_remove_image_sizes() {
    global $_wp_additional_image_sizes;
    $options = get_option('wpiperf_settings');
    /* Loop through $_wp_additional_image_sizes and if the key is not in $options['image_sizes'][] then unset it */
    if (isset($options['image_sizes']['global'])) {
        foreach ($_wp_additional_image_sizes as $key => $value) {
            if (!array_key_exists($key, $options['image_sizes']['global'])) {
                unset($_wp_additional_image_sizes[$key]);
            }
        }
    }
}
function spro_override_image_sizes_front_end ($data, $attachment_id) {
    $options = get_option('wpiperf_settings');
    /* Loop through $data['sizes'] and if the key is not in $options['image_sizes'][global] then unset it */
    if (isset($options['image_sizes']['global']) && is_array($data['sizes']) && array_key_exists('sizes', $data)) {
        foreach ($data['sizes'] as $key => $value) {
            if (!array_key_exists($key, $options['image_sizes']['global'])) {
                unset($data['sizes'][$key]);
            }
        }
    }
    return $data;
}
function spro_prevent_image_resizing( $new_sizes, $image_meta, $attachment_id ) {
    $options = get_option('wpiperf_settings');
    /* Loop through $new_sizes and if the key is not in $options['image_sizes'][global] then unset it */
    if (isset($options['image_sizes']['global'])) {
        foreach ($new_sizes as $key => $value) {
            if (!array_key_exists($key, $options['image_sizes']['global'])) {
                unset($new_sizes[$key]);
            }
        }
    }
    return $new_sizes;
}
add_filter('woocommerce_product_attribute_terms', 'spro_limit_term_count', 10, 1);
add_filter('terms_clauses', 'spro_add_indicator_for_terms_shortcut', 10, 3);
add_filter('get_terms_defaults', 'spro_add_extra_query_var_to_terms_cache_key', 10, 2);
add_filter('query', 'spro_union_selected_attributes');
/*
*/
function spro_limit_term_count($args) {
    global $product;
    global $wc_product_attributes;
    /* get ajaxattributeedit option, return $args immediately if ajaxattributeedit is set to no */
    $options = get_option('wpiperf_settings');
    if (!isset($options['ajaxattributeedit']) || $options['ajaxattributeedit'] == 'no') return $args;

    $e = new \Exception;
    $trace = $e->getTraceAsString();
    if (strpos($trace, 'WC_AJAX::save_attributes') !== false) {
         return $args;
    }
// WC_Meta_Box_Product_Data::output_tabs
    if (SPRO_ATTRIBUTE_AJAX_LIMIT !== 0) {
        if (strpos($trace, 'WC_AJAX::add_attribute') !== false 
        || strpos($trace, 'spro_search_terms') !==false) { // new attribute (could be on new product or not but because it's new it doesn't have any set yet so we don't need to do product_object->get_attributes( 'edit' ); hack
            $args['number'] = SPRO_ATTRIBUTE_AJAX_LIMIT;
            $args['offset'] = 0;
            return $args;
        }
        global $product_object;
        if (isset($product_object) && is_array($product_object->get_attributes( 'edit' ))) {
            if (isset($product_object)) {
                $args['number'] = SPRO_ATTRIBUTE_AJAX_LIMIT;
                $args['offset'] = 0;
                $args['spro_add_selected_attributes'] = $product_object->get_attributes( 'edit' );
            }
        }
    }
    return $args;
}

function spro_union_selected_attributes ($sql) {
    global $wp_query;
    global $request;
    global $meta_query;
    global $query_vars;
    global $query_var_defaults; // need to merge spro_add_selected_attributes in here so it gets used in cache key
    //		$this->query_var_defaults = apply_filters( 'get_terms_defaults', $this->query_var_defaults, $taxonomies );

    global $terms;     
    global $product;
    global $wc_product_attributes;
    global $product_object;
    if (isset($wc_product_attributes) && isset($product_object) && is_array($product_object->get_attributes( 'edit' ))) {
        //todo: add extra WHERE identifier in clauses based on spro_add_selected_attributes existing and check for that here
        if (strpos($sql, "'spro_term_shortcut'") !== false) {
            $union_attributes = array();
            foreach($product_object->get_attributes( 'edit' ) as $attribute_name => $attribute) {
                if (strpos($sql, "WHERE tt.taxonomy IN ('" . $attribute_name . "')") !== false) {
                    $union_attributes[] = "UNION SELECT " . implode(' UNION SELECT ', $attribute['data']['options']);
                }
            }
            if (count($union_attributes) == 1) {
                $sql = "SELECT term_id FROM (" . $sql . ") as x " . $union_attributes[0];
            }
        }
    }
    return $sql;
}

function spro_add_indicator_for_terms_shortcut($clauses, $taxonomies, $args) {
    if (isset($args['spro_add_selected_attributes']) && !empty($args['spro_add_selected_attributes'])) {
        $clauses['where'] .= " AND 'spro_term_shortcut' = 'spro_term_shortcut' ";
    }
    return $clauses;
}

function spro_add_extra_query_var_to_terms_cache_key($query_var_defaults, $taxonomies ) {
    $query_var_defaults['spro_add_selected_attributes'] = '';
    return $query_var_defaults;
}
function spro_enqueue_admin_script( $hook ) {
    if ( 'post.php' != $hook && 'post-new.php' != $hook ) {
        return;
    }
    if (SPRO_ATTRIBUTE_AJAX_LIMIT !== 0) {
        wp_enqueue_script( 'spro_ajaxify_terms', plugins_url('/assets/js/ajaxify_terms.js', __FILE__), array(), time() );
    }
}
add_action( 'admin_enqueue_scripts', 'spro_enqueue_admin_script' );

function spro_search_terms() {
    $taxonomy = $_REQUEST['taxonomy'];
    $search_string = $_REQUEST['search_string'];
    $attribute_taxonomy = get_taxonomy($taxonomy);
    $args      = array(
        'orderby'    => ! empty( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name',
        'hide_empty' => 0,
        'search' => $search_string
    );
    $all_terms = get_terms( $taxonomy, apply_filters( 'woocommerce_product_attribute_terms', $args ) );
    if ( $all_terms ) {
        foreach ( $all_terms as $term ) {
            echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
        }
    }
    die();
}
add_action('wp_ajax_spro_search_terms', 'spro_search_terms');

add_filter( 'query', 'spro_cache_wp_all_export_meta_query' );

function spro_cache_wp_all_export_meta_query( $sql ) {
    if (!SPRO_CACHE_PMXE_META_KEYS) return $sql;
    global $wpdb;
    $table_prefix = $wpdb->prefix;
    $check_sql = "SELECT DISTINCT {$table_prefix}postmeta.meta_key FROM";

    // Check only the first 60 characters of the SQL
    // Adjust the number according to your exact string length
    if (strpos(substr($sql, 0, 60), $check_sql) === 0) {
        // Get the stack trace, but limit to only 3 levels deep
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 7);

        foreach ($trace as $call) {
            if (isset($call['function']) && $call['function'] == 'wp_all_export_get_existing_meta_by_cpt') {
                // This SQL originates from our specific function
                // Let's check if we have it cached already
                
                remove_filter( 'query', 'spro_cache_wp_all_export_meta_query' );

                $results = $wpdb->get_results("SELECT meta_value FROM {$table_prefix}postmeta WHERE meta_key = 'spro_pmxe' LIMIT 1;");

                if (!$results) {
                    $results = $wpdb->get_col($sql);
                    foreach($results as $meta_key) {
                        $wpdb->insert($table_prefix . 'postmeta', array('post_id' => 0, 'meta_key' => 'spro_pmxe', 'meta_value' => $meta_key), array('%d', '%s', '%s'));
                    }
                }
                
                $sql = "SELECT meta_value as meta_key
                FROM wp_postmeta
                WHERE meta_key = 'spro_pmxe'
                ORDER BY meta_value ASC
                ;";
                return $sql;

            }
        }
    }
    return $sql; // if not our specific SQL, just return the original SQL
}

function spro_cache_count_posts_query($query) {
    global $wpdb;

    // Only intercept the wp_count_posts queries and never for shop orders as these are dynamic
    if (strpos($query, "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts}") !== false && strpos($query, "'shop_order'") === false) {
        
        // Create a hash of the query
        $query_hash = md5($query);
        
        // Check if the cache exists and is not expired
        $rows_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}scalability_pro_post_count_cache WHERE query_hash = %s AND expiry > NOW()", $query_hash));

        if ($rows_count == 0) {
            // Cache miss or cache expired. Execute the original query to get the data.
            
            // First, remove this filter to prevent recursion.
            remove_filter('query', 'spro_cache_count_posts_query');
            
            // Clean previous entries with the same hash
            $wpdb->delete($wpdb->prefix . 'scalability_pro_post_count_cache', array('query_hash' => $query_hash));

            // Fetch data with original query
            $results = $wpdb->get_results($query, ARRAY_A);
            
            // Store each row in our cache table.
            $row_number = 0;
            foreach ($results as $row) {
                $row_number++;
                $wpdb->insert(
                    $wpdb->prefix . 'scalability_pro_post_count_cache',
                    array(
                        'query_hash' => $query_hash,
                        'post_status' => $row['post_status'],
                        'num_posts' => $row['num_posts'],
                        'rownum' => $row_number,
                        'expiry' => date('Y-m-d H:i:s', strtotime('+1 day'))
                    )
                );
            }

            // Re-add the filter back after our one-time bypass.
            add_filter('query', 'spro_cache_count_posts_query');
            
        }
        // Alter the query to fetch from the cache table.
        $query = "SELECT post_status, num_posts FROM {$wpdb->prefix}scalability_pro_post_count_cache WHERE query_hash = '{$query_hash}' ORDER BY rownum";
    }

    return $query;
}
if (isset($options['cachepostcounts']) && $options['cachepostcounts'] == 'remove') {
    add_filter('query', 'spro_cache_count_posts_query');
}

// Hook into the update_option_{option_name} action
add_action('update_option_wpiperf_settings', 'spro_update_option_callback', 10, 3);

function spro_update_option_callback($old_value, $new_value, $option_name) {
    // need this check here for enable_slow_log since if users have just updated, the init hook might update the options and enable_slow_log might not be in there
    if (array_key_exists('enable_slow_log', $new_value)) {
        $spro_values = array(
            'enable_slow_log' => $new_value['enable_slow_log'],
            'slow_query_limit' => $new_value['slow_query_limit'], // Just an example; replace with the actual value
            'query_pattern' => $new_value['query_pattern'] // Just an example; replace with the actual value
        );
        $result = add_or_update_spro_globals($spro_values); // updates wp-config.php
    }
}

add_action('wp_loaded', 'spro_show_wp_all_import_queries_in_QM', 98);
function spro_show_wp_all_import_queries_in_QM() {
    if (isset($_GET['spro_show_import_queries']) && $_GET['spro_show_import_queries']) {
        global $wp_filter;
        // Read the original file
        $current_plugin_dir = plugin_dir_path(__FILE__); // This should point to '.../plugins/scalability-pro/'
        $target_dir = dirname($current_plugin_dir); // Go up one folder to '.../plugins/'
        $filePath = $target_dir . '/wp-all-import-pro/actions/wp_loaded_99.php'; // Append the relative path to the target file

        if (file_exists($filePath) && function_exists('pmxi_wp_loaded_99')) { // check the filepath is correct and WP All Import pmxi_wp_loaded_99 exists to confirm the plugin is activated
            $originalCode = file_get_contents($filePath);

            // Replace the function names
            $modifiedCode = str_replace('pmxi_wp_loaded_99', 'spro_wp_loaded_99', $originalCode);
            $modifiedCode = str_replace('pmxi_send_json', 'spro_send_json', $modifiedCode);
            $modifiedCode = str_replace('<?php', '', $modifiedCode);

            // Empty the spro_send_json function
            $modifiedCode = preg_replace('/function spro_send_json\(([^)]*)\)\s*{.*?^\}/ms', 'function spro_send_json($1) { echo "<h2>WP All Import Output</h2><pre>" . print_r($response, true) . "</pre>";}', $modifiedCode);

            // Eval the modified code
            eval($modifiedCode);

            remove_action('wp_loaded', 'pmxi_wp_loaded_99', 99);
            add_action('wp_loaded', 'spro_wp_loaded_99', 99);
        }
    }
}

function truncate_spro_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'spro_slow_query_log';

    // Attempt to truncate the table
    $truncate_result = $wpdb->query("TRUNCATE TABLE {$table_name}");

    if ($truncate_result === false) {
        // If truncating fails, attempt to delete
        $delete_result = $wpdb->query("DELETE FROM {$table_name}");
        if ($delete_result === false) {
            wp_send_json_error('Failed to delete table contents.');
        }
    }

    wp_send_json_success('Table truncated successfully.');
}

add_action('wp_ajax_truncate_spro_table', 'truncate_spro_table');

function spro_custom_check_duplicates_filter($check, $id) {
    // You can add additional logic here if needed, for example, 
    // to apply this only for specific import IDs
    error_log('aborted checking dup for ' . $id);
    return false;
}
if (SPRO_PREVENT_WPAI_DUP_CHECK) {
    add_filter('wp_all_import_is_check_duplicates', 'spro_custom_check_duplicates_filter', 10, 2);
}
$spro_options = get_option('wpiperf_settings');

if (isset($spro_options['fixwoo_onboarding']) && $spro_options['fixwoo_onboarding'] == 'fix') {
    add_filter('query', 'spro_fix_woo_onboarding');
}

function spro_remove_product_category_filter( $filters ) {
    if ( isset( $filters['product_category'] ) ) {
        unset( $filters['product_category'] );
    }
    return $filters;
}
if (isset($spro_options['toplevelcatsonly']) && $spro_options['toplevelcatsonly'] == 'yes' ) {
    add_filter( 'woocommerce_products_admin_list_table_filters', 'spro_remove_product_category_filter', 1 );
}
