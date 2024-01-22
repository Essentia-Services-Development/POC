<?php

function powerpress_admin_basic()
{
    if (defined('WP_DEBUG')) {
        if (WP_DEBUG) {
            wp_register_style('powerpress_settings_style',  powerpress_get_root_url() . 'css/settings.css', array(), POWERPRESS_VERSION);
        } else {
            wp_register_style('powerpress_settings_style',  powerpress_get_root_url() . 'css/settings.min.css', array(), POWERPRESS_VERSION);
        }
    } else {
        wp_register_style('powerpress_settings_style',  powerpress_get_root_url() . 'css/settings.min.css', array(), POWERPRESS_VERSION);
    }
    wp_enqueue_style("powerpress_settings_style");

    $FeedAttribs = array('type'=>'general', 'feed_slug'=>'', 'category_id'=>0, 'term_taxonomy_id'=>0, 'term_id'=>0, 'taxonomy_type'=>'', 'post_type'=>'');
	// feed_slug = channel
	
	$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'basic');
	
	$FeedSettings = powerpress_get_settings('powerpress_feed');
	$FeedSettings = powerpress_default_settings($FeedSettings, 'editfeed');
	
	$CustomFeed = get_option('powerpress_feed_'.'podcast', array()); // Get the custom podcast feed settings saved in the database
	if( !empty($CustomFeed) ) // If they enabled custom podast channels...
	{
		$FeedSettings = powerpress_merge_empty_feed_settings($CustomFeed, $FeedSettings);
		$FeedAttribs['channel_podcast'] = true;
	}
	
	$MultiSiteServiceSettings = false;
	if( is_multisite() )
	{
		$MultiSiteSettings = get_site_option('powerpress_multisite');
		if( !empty($MultiSiteSettings['services_multisite_only']) )
		{
			$MultiSiteServiceSettings = true;
		}
	}

    wp_enqueue_script('powerpress-admin', powerpress_get_root_url() . 'js/admin.js', array(), POWERPRESS_VERSION );

?>
<script type="text/javascript">

jQuery(document).ready(function($) {

	
	jQuery('#episode_box_player_links_options').change(function () {
		
		var objectChecked = jQuery('#episode_box_player_links_options').attr('checked');
		if(typeof jQuery.prop === 'function') {
			objectChecked = jQuery('#episode_box_player_links_options').prop('checked');
		}
		
		if( objectChecked == true ) {
			jQuery('#episode_box_player_links_options_div').css("display", 'block' );
		}
		else {
			jQuery('#episode_box_player_links_options_div').css("display", 'none' );
			jQuery('.episode_box_no_player_or_links').attr("checked", false );
			jQuery('#episode_box_no_player_and_links').attr("checked", false );
			if(typeof jQuery.prop === 'function') {
				jQuery('.episode_box_no_player_or_links').prop("checked", false );
				jQuery('#episode_box_no_player_and_links').prop("checked", false );
			}
		}
	} );
	
	jQuery('#episode_box_no_player_and_links').change(function () {
		
		var objectChecked = jQuery(this).attr("checked");
		if(typeof jQuery.prop === 'function') {
			objectChecked = jQuery(this).prop("checked");
		}
		
		if( objectChecked == true ) {
			jQuery('.episode_box_no_player_or_links').attr("checked", false );
			if(typeof jQuery.prop === 'function') {
				jQuery('.episode_box_no_player_or_links').prop("checked", false );
			}
		}
	} );

	jQuery('.episode_box_no_player_or_links').change(function () {
		var objectChecked = jQuery(this).attr("checked");
		if(typeof jQuery.prop === 'function') {
			objectChecked = jQuery(this).prop("checked");
		}
		
		if( objectChecked == true) {
			jQuery('#episode_box_no_player_and_links').attr("checked", false );
			if(typeof jQuery.prop === 'function') {
				jQuery('#episode_box_no_player_and_links').prop("checked", false );
			}
		}
	} );
	
	jQuery('#episode_box_feature_in_itunes').change( function() {
		var objectChecked = jQuery('#episode_box_feature_in_itunes').attr('checked');
		if(typeof jQuery.prop === 'function') {
			objectChecked = jQuery('#episode_box_feature_in_itunes').prop('checked');
		}
		if( objectChecked ) {
			jQuery("#episode_box_order").attr("disabled", true);
		} else {
			jQuery("#episode_box_order").removeAttr("disabled");
		}
	});

	//Check screen width and hide the sidenav if necessary
    let width = jQuery(window).width();
    if (width < 650) {
        //Welcome tab sidenav already gets hidden inside the tab functions
        jQuery('.toggle-sidenav').not("#welcome-toggle-sidenav").each(function(index, element) {
            jQuery(this).click();
        });
    }

} );
//-->
</script>
<input type="hidden" name="action" value="powerpress-save-settings" />
<input type="hidden" name="General[pp-gen-settings-tabs]" value="1" />
<input type="hidden" name="PlayerSettings[pp-gen-settings-tabs]" value="1" />

<input type="hidden" id="save_tab_pos" name="tab" value="<?php echo (empty($_POST['tab']) ? "settings-welcome" : esc_attr($_POST['tab'])); ?>" />
<input type="hidden" id="save_sidenav_pos" name="sidenav-tab" value="<?php echo (empty($_POST['sidenav-tab']) ? "" : esc_attr($_POST['sidenav-tab'])); ?>" />

<div id="powerpress_admin_header">
<h2><?php echo __('Blubrry PowerPress Settings', 'powerpress'); ?></h2> 

</div>

<div id="powerpress_settings_page" class="powerpress_tabbed_content">
    <div class="pp-tab">
        <button id="welcome-tab" class="tablinks active" onclick="powerpress_openTab(event, 'settings-welcome')"><?php echo htmlspecialchars(__('Welcome', 'powerpress')); ?></button>
        <!-- #tab1 deprecated. was episodes tab -->
        <button id="feeds-tab" class="tablinks" onclick="powerpress_openTab(event, 'settings-feeds')"><?php echo htmlspecialchars(__('Feeds', 'powerpress')); ?></button>
        <button id="website-tab" class="tablinks" onclick="powerpress_openTab(event, 'settings-website')"><?php echo htmlspecialchars(__('Website', 'powerpress')); ?></button>
        <button id="destinations-tab" class="tablinks" onclick="powerpress_openTab(event, 'settings-destinations')"><?php echo htmlspecialchars(__('Destinations', 'powerpress')); ?></button>
        <!-- <button id="analytics-tab" class="tablinks" onclick="openTab(event, 'settings-analytics')"><?php echo htmlspecialchars(__('Analytics', 'powerpress')); ?></button> -->
        <button id="advanced-tab" class="tablinks" onclick="powerpress_openTab(event, 'settings-advanced')"><?php echo htmlspecialchars(__('Advanced', 'powerpress')); ?></button>
        <button id="make-money-tab" class="tablinks" onclick="powerpress_openTab(event, 'settings-make-money')"><?php echo htmlspecialchars(__('Make Money', 'powerpress')); ?></button>
        <?php
        $hasChannels = isset($General['channels']) && $General['channels'] == 1;
        $hasCats = isset($General['cat_casting']) && $General['cat_casting'] == 1;
        $hasTax = isset($General['taxonomy_podcasting']) && $General['taxonomy_podcasting'] == 1;
        $hasPT = isset($General['posttype_podcasting']) && $General['posttype_podcasting'] == 1;
        $slug = $_GET['feed_slug'] ?? '';

        if ((!$hasChannels && !$hasCats && !$hasTax && !$hasPT) || $slug == 'podcast') {
        ?>
        <button id="live-item-tab" class="tablinks" onclick="powerpress_openTab(event, 'settings-live-item')"><?php echo htmlspecialchars(__('Live Item', 'powerpress')); ?></button>
        <?php } ?>
        <button id="experimental-tab" class="tablinks" onclick="powerpress_openTab(event, 'settings-experimental')"><?php echo htmlspecialchars(__('Experimental', 'powerpress')); ?></button>
    </div>
	
	<div id="settings-welcome" class="pp-tabcontent active">
        <div class="pp-sidenav-toggle-container">
            <div id="welcome-toggle-sidenav" class="toggle-sidenav" title="Blubrry Services" onclick="powerpress_displaySideNav(this);">&lt;</div>
            <div class="pp-sidenav">
                <?php
                powerpressadmin_edit_blubrry_services($General);
                ?>
                <div class="pp-sidenav-extra" style="margin-top: 10%;"><a href="https://www.blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('POWERPRESS DOCUMENTATION', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/podcast-insider/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST INSIDER BLOG', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/manual/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST MANUAL', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/services/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY RESOURCES', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY SUPPORT', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://wordpress.org/support/plugin/powerpress/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY POWERPRESS FORUM', 'powerpress')); ?></a></div>

            </div>
        </div>
        <button style="display: none;" id="welcome-default-open" class="pp-sidenav-tablinks active" onclick="sideNav(event, 'welcome-all')"><img class="pp-nav-icon" style="width: 22px;" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/rss-symbol.svg"><?php echo htmlspecialchars(__('Hidden button', 'powerpress')); ?></button>
        <div id="welcome-all" class="pp-sidenav-tab active">
	        <?php powerpressadmin_welcome($General, $FeedSettings); ?>
        </div>
	</div>

    <div id="settings-feeds" class="pp-tabcontent has-sidenav">
        <div class="pp-sidenav-toggle-container">
            <div id="feeds-toggle-sidenav" class="toggle-sidenav" title="More Feed Settings and Blubrry Services" onclick="powerpress_displaySideNav(this);">&lt;</div>
            <div class="pp-sidenav">
                <div class="pp-sidenav-extra"><p class="pp-sidenav-extra-text"><b><?php echo htmlspecialchars(__('FEED SETTINGS', 'powerpress')); ?></b></p></div>
                <button id="feeds-default-open" class="pp-sidenav-tablinks active" onclick="sideNav(event, 'feeds-feeds')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/megaphone_gray.svg"><?php echo htmlspecialchars(__('Podcast Feeds', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="feeds-settings-tab" onclick="sideNav(event, 'feeds-settings')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/option_bar_settings_gray.svg"><?php echo htmlspecialchars(__('Feed Settings', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="feeds-artwork-tab" onclick="sideNav(event, 'feeds-artwork')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/camera_gray.svg"><?php echo htmlspecialchars(__('Podcast Artwork', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="feeds-seo-tab" onclick="sideNav(event, 'feeds-seo')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/fileboard_checklist_gray.svg"><?php echo htmlspecialchars(__('Podcast SEO', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="feeds-basic-tab" onclick="sideNav(event, 'feeds-basic')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/edit_gray.svg"><?php echo htmlspecialchars(__('Basic Show Information', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="feeds-rating-tab" onclick="sideNav(event, 'feeds-rating')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/star_favorite_gray.svg"><?php echo htmlspecialchars(__('Rating Settings', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="feeds-apple-tab" onclick="sideNav(event, 'feeds-apple')"><span id="apple-icon-feed" class="destinations-side-icon" style="margin-left: 2px;"></span><span class="destination-side-text" style="margin-left: 6px;"><?php echo htmlspecialchars(__('Apple Settings', 'powerpress')); ?></span></button>
                <?php
                powerpressadmin_edit_blubrry_services($General);
                ?>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('POWERPRESS DOCUMENTATION', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/podcast-insider/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST INSIDER BLOG', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/manual/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST MANUAL', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/services/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY RESOURCES', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY SUPPORT', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://wordpress.org/support/plugin/powerpress/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY POWERPRESS FORUM', 'powerpress')); ?></a></div>
            </div>
        </div>
        <div id="feeds-feeds" class="pp-sidenav-tab active">
            <?php
            powerpressadmin_edit_feed_general($FeedSettings, $General, $FeedAttribs);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="feeds-settings" class="pp-sidenav-tab">
            <?php
            powerpressadmin_edit_feed_settings($FeedSettings, $General, $FeedAttribs);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="feeds-artwork" class="pp-sidenav-tab">
            <?php
            powerpressadmin_edit_artwork($FeedSettings, $General);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="feeds-seo" class="pp-sidenav-tab">
            <?php
            require_once(POWERPRESS_ABSPATH . "/powerpressadmin-search.php");
            powerpress_admin_search();
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="feeds-basic" class="pp-sidenav-tab">
            <?php
            powerpressadmin_edit_funding($FeedSettings);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="feeds-rating" class="pp-sidenav-tab">
            <?php
            powerpressadmin_edit_tv($FeedSettings);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="feeds-apple" class="pp-sidenav-tab">
            <?php
            powerpressadmin_edit_itunes_feed($FeedSettings, $General, $FeedAttribs);
            powerpress_settings_tab_footer();
            ?>
        </div>
    </div>

    <div id="settings-website" class="pp-tabcontent">
        <div class="pp-sidenav-toggle-container">
            <div id="website-toggle-sidenav" class="toggle-sidenav" title="More Website Settings and Blubrry Services" onclick="powerpress_displaySideNav(this);">&lt;</div>
            <div class="pp-sidenav">
                <div class="pp-sidenav-extra"><p class="pp-sidenav-extra-text"><b><?php echo htmlspecialchars(__('WEBSITE SETTINGS', 'powerpress')); ?></b></p></div>
                <button id="website-default-open" class="pp-sidenav-tablinks active" onclick="sideNav(event, 'website-settings')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/desktop_gray.svg"><?php echo htmlspecialchars(__('Website Settings', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="website-blog-tab" onclick="sideNav(event, 'website-blog')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/file_gray.svg"><?php echo htmlspecialchars(__('Blog Posts and Pages', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="website-subscribe-tab" onclick="sideNav(event, 'website-subscribe')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/profile_plus_round_gray.svg"><?php echo htmlspecialchars(__('Subscribe Page', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="website-shortcodes-tab" onclick="sideNav(event, 'website-shortcodes')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/connection_pattern_gray.svg"><?php echo htmlspecialchars(__('PowerPress Shortcodes', 'powerpress')); ?></button>
                <button class="pp-sidenav-tablinks" id="website-new-window-tab" onclick="sideNav(event, 'website-new-window')"><img class="pp-nav-icon" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/play_gray.svg"><?php echo htmlspecialchars(__('Play in New Window', 'powerpress')); ?></button>
                <?php
                powerpressadmin_edit_blubrry_services($General);
                ?>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('POWERPRESS DOCUMENTATION', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/podcast-insider/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST INSIDER BLOG', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/manual/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST MANUAL', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/services/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY RESOURCES', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY SUPPORT', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://wordpress.org/support/plugin/powerpress/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY POWERPRESS FORUM', 'powerpress')); ?></a></div>
            </div>
        </div>

        <?php
        if( $General === false )
            $General = powerpress_get_settings('powerpress_general');
        $General = powerpress_default_settings($General, 'appearance');
        if( !isset($General['player_function']) )
            $General['player_function'] = 1;
        if( !isset($General['player_aggressive']) )
            $General['player_aggressive'] = 0;
        if( !isset($General['new_window_width']) )
            $General['new_window_width'] = '';
        if( !isset($General['new_window_height']) )
            $General['new_window_height'] = '';
        if( !isset($General['player_width']) )
            $General['player_width'] = '';
        if( !isset($General['player_height']) )
            $General['player_height'] = '';
        if( !isset($General['player_width_audio']) )
            $General['player_width_audio'] = '';
        if( !isset($General['disable_appearance']) )
            $General['disable_appearance'] = false;
        if( !isset($General['subscribe_links']) )
            $General['subscribe_links'] = false;
        if( !isset($General['subscribe_label']) )
            $General['subscribe_label'] = '';
        require_once( dirname(__FILE__).'/views/settings_tab_appearance.php' );

        ?>


        <div id="website-settings" class="pp-sidenav-tab active">
            <?php
            powerpressadmin_website_settings($General, $FeedSettings);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="website-blog" class="pp-sidenav-tab">
            <?php
            powerpressadmin_blog_settings($General, $FeedSettings);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="website-subscribe" class="pp-sidenav-tab">
            <?php
            powerpress_subscribe_settings($General, $FeedSettings);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="website-shortcodes" class="pp-sidenav-tab">
            <?php
            powerpress_shortcode_settings($General, $FeedAttribs);
            powerpress_settings_tab_footer();
            ?>
        </div>
        <div id="website-new-window" class="pp-sidenav-tab">
            <?php
            powerpressadmin_new_window_settings($General, $FeedSettings);
            powerpress_settings_tab_footer();
            ?>
        </div>
    </div>

    <div id="settings-destinations" class="pp-tabcontent">
        <?php
        powerpressadmin_edit_destinations($FeedSettings, $General, $FeedAttribs);
        ?>
    </div>
	
	<div id="settings-analytics" class="pp-tabcontent">
        <div class="pp-sidenav">
            <?php
            powerpressadmin_edit_blubrry_services($General);
            ?>
        </div>
		<?php
	if( $MultiSiteServiceSettings && defined('POWERPRESS_MULTISITE_VERSION') )
	{
		PowerPressMultiSitePlugin::edit_blubrry_services($General);
	}
	else
	{
		//powerpressadmin_edit_media_statistics($General);
	}
		?>
	</div>

	<div id="settings-advanced" class="pp-tabcontent">
        <div class="pp-sidenav-toggle-container">
            <div id="advanced-toggle-sidenav" class="toggle-sidenav" title="Blubrry Services" onclick="powerpress_displaySideNav(this);">&lt;</div>
            <div class="pp-sidenav">
                <?php
                powerpressadmin_edit_blubrry_services($General);
                ?>
                <div class="pp-sidenav-extra" style="margin-top: 10%;"><a href="https://www.blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('POWERPRESS DOCUMENTATION', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/podcast-insider/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST INSIDER BLOG', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/manual/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST MANUAL', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/services/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY RESOURCES', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY SUPPORT', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://wordpress.org/support/plugin/powerpress/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY POWERPRESS FORUM', 'powerpress')); ?></a></div>
            </div>
        </div>
	<?php
    powerpressadmin_advanced_options($General, false);
    ?>
    </div>

    <div id="settings-make-money" class="pp-tabcontent">
        <div class="pp-sidenav-toggle-container">
            <div id="advanced-toggle-sidenav" class="toggle-sidenav" title="Blubrry Services" onclick="powerpress_displaySideNav(this);">&lt;</div>
            <div class="pp-sidenav">
                <?php
                powerpressadmin_edit_blubrry_services($General);
                ?>
                <div class="pp-sidenav-extra" style="margin-top: 10%;"><a href="https://www.blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('POWERPRESS DOCUMENTATION', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/podcast-insider/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST INSIDER BLOG', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/manual/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST MANUAL', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/services/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY RESOURCES', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY SUPPORT', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://wordpress.org/support/plugin/powerpress/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY POWERPRESS FORUM', 'powerpress')); ?></a></div>
            </div>
        </div>
        <?php
        $origin_array = explode('.', POWERPRESS_BLUBRRY_API_URL);
        $origin_array[0] = str_replace('api', 'publish', $origin_array[0]);
        $publisher_origin = implode('.', $origin_array);
        $publisher_origin = rtrim($publisher_origin, '/'); ?>
        <button style="display: none;" id="make-money-default-open" class="pp-sidenav-tablinks active" onclick="sideNav(event, 'make-money-all')"><img class="pp-nav-icon" style="width: 22px;" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/rss-symbol.svg"><?php echo htmlspecialchars(__('Hidden button', 'powerpress')); ?></button>

        <div id="make-money-all" class="pp-sidenav-tab active">
            <div class="pp_container">
                <h1 class="pp-heading"><?php echo __('Programmatic Advertising', 'powerpress'); ?></h1>
                <br />
                <p class="pp-sub"><?php echo __('Blubrry hosting customers have access to our Programmatic Advertising service, which automatically puts ads into your show and pays YOU out directly from Blubrry! Simply configure your shows in the Blubrry Publisher then use the link there to sync you WordPress site.', 'powerpress'); ?></p>
                <br />
                <p class="pp-main"><a class="pp-main" target="_blank"
                   href="https://blubrry.com/services/programmatic-advertising/"
                   class="pp_align-center"><?php echo __('Learn More', 'powerpress'); ?></a></p>
                <br />
                <p class="pp-main"><a class="pp-main"
                   href="<?php echo $publisher_origin; ?>/partners/programmatic-advertising-management/"
                   class="pp_align-center"><?php echo __('Configure Shows for Programmatic Ads', 'powerpress'); ?></a></p>

            </div>
        </div>
    </div>

    <div id="settings-live-item" class="pp-tabcontent">
        <div class="pp-sidenav-toggle-container">
            <div id="advanced-toggle-sidenav" class="toggle-sidenav" title="Blubrry Services" onclick="powerpress_displaySideNav(this);">&lt;</div>
            <div class="pp-sidenav">
                <?php
                powerpressadmin_edit_blubrry_services($General);
                ?>
                <div class="pp-sidenav-extra" style="margin-top: 10%;"><a href="https://www.blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('POWERPRESS DOCUMENTATION', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/podcast-insider/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST INSIDER BLOG', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/manual/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST MANUAL', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/services/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY RESOURCES', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY SUPPORT', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://wordpress.org/support/plugin/powerpress/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY POWERPRESS FORUM', 'powerpress')); ?></a></div>
            </div>
        </div>
        <?php powerpressadmin_live_item_options($FeedSettings); ?>
    </div>

    <div id="settings-experimental" class="pp-tabcontent">
        <div class="pp-sidenav-toggle-container">
            <div id="advanced-toggle-sidenav" class="toggle-sidenav" title="Blubrry Services" onclick="powerpress_displaySideNav(this);">&lt;</div>
            <div class="pp-sidenav">
                <?php
                powerpressadmin_edit_blubrry_services($General);
                ?>
                <div class="pp-sidenav-extra" style="margin-top: 10%;"><a href="https://www.blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('POWERPRESS DOCUMENTATION', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://www.blubrry.com/podcast-insider/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST INSIDER BLOG', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/manual/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('PODCAST MANUAL', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/services/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY RESOURCES', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://blubrry.com/support/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY SUPPORT', 'powerpress')); ?></a></div>
                <div class="pp-sidenav-extra"><a href="https://wordpress.org/support/plugin/powerpress/" class="pp-sidenav-extra-text"><?php echo htmlspecialchars(__('BLUBRRY POWERPRESS FORUM', 'powerpress')); ?></a></div>
            </div>
        </div>
        <?php
        powerpressadmin_experimental_options($FeedSettings);
        ?>
    </div>

</div>
<div class="clear"></div>

<?php
}

function powerpressadmin_live_item_options($Feed)
{
    $lit = isset($Feed['live_item']) ? $Feed['live_item'] : array(
        'enabled' => '0',
        'status' => 'Pending',
        'guid' => '',
        'start_date_time' => '',
        'end_date_time' => '',
        'timezone' => 'EST',
        'title' => '',
        'stream_link' => '',
        'fallback_link' => '',
        'episode_link' => '',
        'cover_art' => '',
        'stream_type' => 'audio/mpeg',
        'description' => '',
        'old_status' => 'Pending'
    );

    $litError = get_option('lit_error');

    if ($litError)
        update_option('lit_error', false);

    $litErrorMsg = get_option('lit_error_msg');

    if ($litErrorMsg != "")
        update_option('lit_error_msg', '');

    ?>
    <style>
        .alert {
            font-size: 130%;
            padding: .5%;
            background-color: #f44336; /* Red */
            color: white;
            display: inline-block;
            width: 99%;
            /*margin-bottom: 1.5%;*/
        }
        .alert-danger {
            background-color: #FEF7F8;
            border-left: solid;
            border-left-color: #E36F58;
            color: #444444;
            border-left-width: 10px;
            max-width: 100%;
        }
    </style>
    <div style="margin-left: 10px;">
        <button style="display: none;" id="live-item-default-open" class="pp-sidenav-tablinks active" onclick="sideNav(event, 'live-item-all')"><img class="pp-nav-icon" style="width: 22px;" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/rss-symbol.svg"><?php echo htmlspecialchars(__('Hidden button', 'powerpress')); ?></button>
        <div id="live-item-all" class="pp-sidenav-tab active">
            <h1 class="pp-heading"><?php echo __('Live Item Tag', 'powerpress'); ?></h1>
            <?php if ($litError) {
                ?>
                <div class="alert alert-danger" role="alert">
                    <span><?php echo __($litErrorMsg, 'powerpress'); ?></span>
                </div>
            <?php } ?>
            <p><?php echo __('This is a new initiative as part of Podcasting 2.0. The Live Item tag is for those with a live component to your show, whether it be an audio stream or a video stream. It is important to know that only apps that designate the LIT function at NewPodcastApps.com support this.', 'powerpress'); ?></p>
            <p><?php echo __('Going live is a bigger part of podcasting now. What has been developed by Podcasting 2.0 is the ability for you to show up as live in the supported apps. Giving those listeners the ability to be notified in supported podcasting apps that you are live to be able to listen or watch within the podcasting apps that support this.', 'powerpress'); ?></p>
            <div class="col">
                <div class="row">
                    <input type="hidden" name="Feed[live_item][enabled]" value="0">
                    <input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="Feed[live_item][enabled]" value="1" <?php echo ( !empty($lit['enabled']) && $lit['enabled'] != '0' ?' checked':''); ?>>
                    <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em; display: flex; align-items: center; justify-content: flex-start;"">
                        <p class="pp-main">
                            Enable Live Item Feature
                            <div class="pp-tooltip-right" style="height: 16px; width: 16px; margin-left: 5px;">i
                                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__('By enabling this, you will notify apps of the status of your event, be it Pending, Live, or Ended.', 'powerpress')); ?></span>
                            </div>
                        </p>
                    </div>
                </div>
                <hr style="margin-left: -15px; margin-right: -15px;"/>
                <div class="row">
                    <h2><?php echo __('Live Item Settings', 'powerpress'); ?></h2>
                </div>
                <div class="row">
                    <div class="col-lg-2 pl-0">
                        <label for="lit-status" style="margin: 0; display: flex; align-items: center; justify-content: flex-start;">
                            <h3><?php echo __("Status", "powerpress"); ?></h3>
                            <div class="pp-tooltip-right" style="height: 16px; width: 16px; margin-left: 5px;">i
                                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;">
                                    <?php echo esc_html(__("You must set your statuses for each part of the process.
                                            Switching from Pending to Live to Ended is not automated.
                                            You have to make these status changes to inform platforms of the current Live status.", "powerpress")); ?>
                                    <br />
                                    <br />
                                    <?php echo __("Pending: Announce the parameters of when you expect to go live. This can be set hours or days before the event.", "powerpress")?>
                                    <br/>
                                    <br />
                                    <?php echo __("Live: When you are moments away from going live, update your setting to Live. This will let the supported apps know that you are truly going live. Please note that deviations on when you are expected to go live and when you actually do can change. This will inform the apps you are indeed live.", "powerpress")?>
                                    <br />
                                    <br />
                                    <?php echo __("Ended: Inform apps that your Live show has ended.", "powerpress")?>
                                </span>
                            </div>
                        </label>
                        <input type="hidden" name="Feed[live_item][old_status]" value="<?php echo !empty($lit['status']) ? $lit['status'] : 'Pending'; ?>" />
                        <input type="hidden" name="Feed[live_item][podping_status]" value="<?php echo isset($lit['podping_status']) ? $lit['podping_status'] : -1; ?>" />
                        <select name="Feed[live_item][status]" id="lit-status" class="pp-settings-select" style="width: 100% !important; font-size: 95%;">
                            <option <?php echo (!empty($lit['status']) && $lit['status']  == 'Pending') || empty($lit['status']) ? 'selected' : '' ?> value="Pending"><?php echo __("Pending", "powerpress"); ?></option>
                            <option <?php echo !empty($lit['status']) && $lit['status'] == 'Live' ? 'selected' : '' ?> value="Live"><?php echo __("Live", "powerpress"); ?></option>
                            <option <?php echo !empty($lit['status']) && $lit['status'] == 'Ended' ? 'selected' : '' ?> value="Ended"><?php echo __("Ended", "powerpress"); ?></option>
                        </select>
                    </div>
                    <div class="col-lg-2 pl-0"></div>
                    <div class="col-lg-8 pl-0">
                        <input type="hidden" name="Feed[live_item][guid]" value="<?php echo !empty($lit['guid']) ? $lit['guid'] : ''; ?>">
                        <label for="lit-guid" style="margin: 0; display: flex; align-items: center; justify-content: flex-start;">
                            <h3><?php echo __("Live Item Guid", "powerpress"); ?></h3>
                            <div class="pp-tooltip-right" style="height: 16px; width: 16px; margin-left: 5px;">i
                                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__('Like every Podcast, Podcasting 2.0 assigns a GUID for your live events. This is informational only and highly technical but this GUID will stay with your live events for the life of your show.', 'powerpress')); ?></span>
                            </div>
                        </label>
                        <p><?php echo !empty($lit['guid']) ? $lit['guid'] : __("Your GUID will be created once you give your live item a title.", "powerpress"); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 pl-0">
                        <label for="lit-start-date" style="margin: 0;"><h3><?php echo __("Start Date/Time", "powerpress"); ?><span style="color: red;">*</span></h3></label>
                        <input id="lit-start-date" name="Feed[live_item][start_date_time]" class="pp-settings-text-input" type="datetime-local" value="<?php echo !empty($lit['start_date_time']) ? $lit['start_date_time'] : "" ?>" />
                    </div>
                    <div class="col-lg-4 pl-0">
                        <label for="lit-end-date" style="margin: 0;"><h3><?php echo __("End Date/Time", "powerpress"); ?><span style="color: red;">*</span></h3></label>
                        <input id="lit-end-date" name="Feed[live_item][end_date_time]" class="pp-settings-text-input" type="datetime-local" value="<?php echo !empty($lit['end_date_time']) ? $lit['end_date_time'] : "" ?>" />
                    </div>
                    <div class="col-lg-4 pl-0">
                        <label for="lit-timezone" style="margin: 0; display: flex; align-items: center; justify-content: flex-start;">
                            <h3><?php echo __("Timezone", "powerpress"); ?><span style="color: red;">*</span></h3>
                            <div class="pp-tooltip-right" style="height: 16px; width: 16px; margin-left: 5px;">i
                                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__('Note: (EST, CST, MST, and PST) are from March 12th to Nov 15th, with (EDT, CDT, MDT, and PDT) from Nov 16th to March 11th)', 'powerpress')); ?></span>
                            </div>
                        </label>
                        <?php printTimezoneSelector(!empty($lit['timezone']) ? $lit['timezone'] : 'EST'); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 pl-0">
                        <label for="lit-title" style="margin: 0;"><h3><?php echo __("Livestream Title", "powerpress"); ?><span style="color: red;">*</span></h3></label>
                        <input id="lit-title" maxlength="100" class="pp-settings-text-input" type="text" name="Feed[live_item][title]" value="<?php echo !empty($lit['title']) ? htmlspecialchars($lit['title']) : ''; ?>" maxlength="50" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 pl-0 pr-0">
                        <label for="lit-stream-type" style="margin: 0;"><h3><?php echo __("Stream Type", "powerpress"); ?><span style="color: red;">*</span></h3></label>
                        <select id="lit-stream-type" name="Feed[live_item][stream_type]" class="pp-settings-select" style="width: 100% !important; font-size: 95%;">
                            <option <?php echo (!empty($lit['stream_type']) && $lit['stream_type'] == 'audio/mpeg') || empty($lit['stream_type']) ? 'selected' : '' ?> value="audio/mpeg">Audio - .mp3</option>
                            <option <?php echo !empty($lit['stream_type']) && $lit['stream_type'] == 'audio/x-m4a' ? 'selected' : '' ?> value="audio/x-m4a">Audio - .m4a</option>
                            <option <?php echo !empty($lit['stream_type']) && $lit['stream_type'] == 'video/mp4' ? 'selected' : '' ?> value="video/mp4">Video - .mp4</option>
                            <option <?php echo !empty($lit['stream_type']) && $lit['stream_type'] == 'application/x-mpegURL' ? 'selected' : '' ?> value="application/x-mpegURL">HLS - .m3u8</option>
                        </select>
                    </div>
                    <div class="col-lg-5">
                        <label for="lit-stream-link" style="margin: 0; display: flex; align-items: center; justify-content: flex-start;">
                            <h3><?php echo __("Audio/Video Steam Link", "powerpress"); ?><span style="color: red;">*</span></h3>
                            <div class="pp-tooltip-right" style="height: 16px; width: 16px; margin-left: 5px;">i
                                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__("This is the link to your livestream. It could be your live audio stream link if you're using Icecast/Shoutcast, or it can be a link to the video endpoint, which is typically an RTMP link. Live video is currently less supported than an audio stream.", 'powerpress')); ?></span>
                            </div>
                        </label>
                        <input id="lit-stream-link" class="pp-settings-text-input" type="text" name="Feed[live_item][stream_link]" value="<?php echo !empty($lit['stream_link']) ? htmlspecialchars($lit['stream_link']) : ''; ?>" />
                    </div>
                    <div class="col-lg-5 pl-0">
                        <label for="lit-fallback-link" style="margin: 0; display: flex; align-items: center; justify-content: flex-start;">
                            <h3><?php echo __("Fallback Link", "powerpress"); ?><span style="color: red;">*</span></h3>
                            <div class="pp-tooltip-right" style="height: 16px; width: 16px; margin-left: 5px;">i
                                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__("If the podcast app does not support your livestream protocol, you need to link to your live YouTube episode or a dedicated page on your website with the live audio or video stream contained there.", 'powerpress')); ?></span>
                            </div>
                        </label>
                        <input id="lit-fallback-link" class="pp-settings-text-input" type="text" name="Feed[live_item][fallback_link]" value="<?php echo !empty($lit['fallback_link']) ? htmlspecialchars($lit['fallback_link']) : ''; ?>" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 pl-0 pr-0"></div>
                    <div class="col-lg-5">
                        <label for="lit-episode-link" style="margin: 0; display: flex; align-items: center; justify-content: flex-start;">
                            <h3><?php echo __("Episode Link (Optional)", "powerpress"); ?></h3>
                            <div class="pp-tooltip-right" style="height: 16px; width: 16px; margin-left: 5px;">i
                                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__("This is a URL to an episode page that supports the live event. If you do not have a dedicated page for your live event, you could push them to a dedicated page that shows the schedule of your live events. You can be creative here. For example: Link to a fallback link.", 'powerpress')); ?></span>
                            </div>
                        </label>
                        <input id="lit-episode-link" class="pp-settings-text-input" type="text" name="Feed[live_item][episode_link]" value="<?php echo !empty($lit['episode_link']) ? htmlspecialchars($lit['episode_link']) : ''; ?>" />
                    </div>
                    <div class="col-lg-5 pl-0">
                        <label for="lit-cover-art" style="margin: 0; display: flex; align-items: center; justify-content: flex-start;">
                            <h3><?php echo __("Cover Art (Optional)", "powerpress"); ?></h3>
                            <div class="pp-tooltip-right" style="height: 16px; width: 16px; margin-left: 5px;">i
                                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__("This is album art that will only be shown when you are live so if you want to have specific art shown while you live, you can provide a link to it. Minimum 1400x1400, similar to Apple podcast art but solely used for your live events.", 'powerpress')); ?></span>
                            </div>
                        </label>
                        <input id="lit-cover-art" class="pp-settings-text-input" type="text" name="Feed[live_item][cover_art]" value="<?php echo !empty($lit['cover_art']) ? htmlspecialchars($lit['cover_art']) : ''; ?>" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 pl-0">
                        <label for="lit-description" style="margin: 0;"><h3><?php echo __("Description (Optional)", "powerpress"); ?></h3></label>
                        <textarea id="lit-description" name="Feed[live_item][description]" class="pp-settings-text-input" rows="10"><?php echo !empty($lit['description']) ? htmlspecialchars($lit['description']) : ""; ?></textarea>
                    </div>
                </div>
            </div>

            <?php powerpress_settings_tab_footer(); ?>
        </div>
    </div>

    <?php
}

function printTimezoneSelector($timezoneVal) {
    $tzlist = DateTimeZone::listAbbreviations();
    echo '<select name="Feed[live_item][timezone]" id="lit-timezone" class="pp-settings-select" style="width: 100% !important; font-size: 95%;">';
    foreach ($tzlist as $tz => $value) {
        if (strtoupper($tz) == $timezoneVal)
            echo '<option selected value="' . strtoupper($tz) . '">' . strtoupper($tz) . '</option>';
        else
            echo '<option value="' . strtoupper($tz) . '">' . strtoupper($tz) . '</option>';

        if ($tz == 'ywt')
            break;
    }
    echo '</select>';
}

function powerpressadmin_advanced_options($General, $link_account = false)
{
	// Break the bottom section here out into it's own function
	$ChannelsCheckbox = '';
	if( !empty($General['custom_feeds']) )
		$ChannelsCheckbox = ' onclick="alert(\''.  __('You must delete all of the Podcast Channels to disable this option.', 'powerpress')  .'\');return false;"';
	$CategoryCheckbox = '';
	//if( !empty($General['custom_cat_feeds']) ) // Decided ont to include this warning because it may imply that you have to delete the actual category, which is not true.
	//	$CategoryCheckbox = ' onclick="alert(\'You must remove podcasting from the categories to disable this option.\');return false;"';
?>
<script language="javascript">

jQuery(document).ready( function() {
	
	jQuery('.pp-expand-section').click( function(e) {
		e.preventDefault();
		
		if( jQuery(this).hasClass('pp-expand-section-expanded') ) {
			jQuery(this).removeClass('pp-expand-section-expanded');
			jQuery(this).parent().next('div').hide(400);
			jQuery(this).blur();
		} else {
			jQuery(this).addClass('pp-expand-section-expanded');
			jQuery(this).parent().next('div').show(400);
			jQuery(this).blur();
		}
	});
});

function goToPodcastSEO() {
    jQuery("#feeds-tab").click();
    jQuery("#feeds-seo-tab").click();
    return false;
}
</script>
<div style="margin-left: 10px;">

    <button style="display: none;" id="advanced-default-open" class="pp-sidenav-tablinks active" onclick="sideNav(event, 'advanced-all')"><img class="pp-nav-icon" style="width: 22px;" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/rss-symbol.svg"><?php echo htmlspecialchars(__('Hidden button', 'powerpress')); ?></button>
	<div id="advanced-all" class="pp-sidenav-tab active">
        <h1 class="pp-heading"><?php echo __('Advanced Settings', 'powerpress'); ?></h1>

        <div>
			<input type="hidden" name="General[network_mode]" value="0" />
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[network_mode]" value="1" <?php echo ( !empty($General['network_mode']) ?' checked':''); ?>/>
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Multi-Program Mode', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('This feature allows you to publish to multiple Blubrry-hosted shows from a single user account.', 'powerpress'); ?></p>
            </div>
		</div>
        <div>
            <input type="hidden" name="General[use_caps]" value="0" />
            <input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[use_caps]" value="1" <?php echo ( !empty($General['use_caps']) ?' checked':''); ?>/>
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('User Role Management', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Adding User Role Management will allow administrators, editors and authors access to create and configure podcast episodes. 
                    This feature is supported by WordPress Roles and Capabilities.', 'powerpress'); ?>
                </p>
            </div>
        </div>
		<div>
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="NULL[import_podcast]" value="1" checked disabled />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_import_feed.php'); ?>"><?php echo __('Import Podcast', 'powerpress'); ?></a></p>
                <p class="pp-sub"><?php echo __('Import podcast feed from SoundCloud, LibSyn, PodBean or other podcast service.', 'powerpress'); ?></p>
            </div>
		</div>
		<div>
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="NULL[migrate_media]" value="1" checked disabled />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_migrate.php'); ?>"><?php echo __('Migrate Media', 'powerpress'); ?></a></p>
                <p class="pp-sub"><?php echo __('Migrate media files to Blubrry Podcast Media Hosting with only a few clicks.', 'powerpress'); ?></p>
            </div>
		</div>
		<div>
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="NULL[podcasting_seo]" value="1" checked disabled />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><a id="advanced-tab-seo-link" onclick="goToPodcastSEO();return false;"><?php echo __('Podcasting SEO', 'powerpress'); ?></a></p>
                <p class="pp-sub"><?php echo __('Optimize how your podcast appears in Internet search results.', 'powerpress'); ?></p>
            </div>
		</div>
		
		<div>
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="NULL[player_options]" value="1" checked disabled />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Audio Player Options', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Select from 3 different web based audio players.', 'powerpress'); ?>
                    <b><a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_player.php&sp=1'); ?>">(<?php echo __('configure audio player', 'powerpress'); ?>)</a></b></p>
            </div>
		</div>
		<div>
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="NULL[video_player_options]" value="1" checked disabled />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Video Player Options', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Select from 3 different web based video players.', 'powerpress'); ?>
                <b><a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_videoplayer.php&sp=1'); ?>">(<?php echo __('configure video player', 'powerpress'); ?>)</a></b></p>
            </div>
		</div>
		<div>
			<input type="hidden" name="General[channels]" value="0" />
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[channels]" value="1" <?php echo ( !empty($General['channels']) ?' checked':''); echo $ChannelsCheckbox; ?> />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Custom Podcast Channels', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Manage multiple media files and/or formats to one blog post.', 'powerpress'); ?>
                <?php if( empty($General['channels']) ) { ?>
                (<?php echo __('feature will appear in left menu when enabled', 'powerpress'); ?>)
                <?php } else { ?>
                <b><a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_customfeeds.php'); ?>">(<?php echo __('configure podcast channels', 'powerpress'); ?>)</a></b>
                <?php } ?>
                </p>
            </div>
		</div>
		<div>
			<input type="hidden" name="General[cat_casting]" value="0" />
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[cat_casting]" value="1" <?php echo ( !empty($General['cat_casting']) ?' checked':'');  echo $CategoryCheckbox;  ?> />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Category Podcasting', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Manage podcasting for specific categories.', 'powerpress'); ?>
                <?php if( empty($General['cat_casting']) ) { ?>
                (<?php echo __('feature will appear in left menu when enabled', 'powerpress'); ?>)
                <?php } else { ?>
                <b><a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_categoryfeeds.php'); ?>">(<?php echo __('configure podcast categories', 'powerpress'); ?>)</a></b>
                <?php } ?>
                </p>
            </div>
		</div>
		
		
		<div>
			<input type="hidden" name="General[taxonomy_podcasting]" value="0" />
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[taxonomy_podcasting]" value="1" <?php echo ( !empty($General['taxonomy_podcasting']) ?' checked':''); ?> />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Taxonomy Podcasting', 'powerpress'); ?>
                <p class="pp-sub"><?php echo __('Manage podcasting for specific taxonomies.', 'powerpress'); ?>
                <?php if( empty($General['taxonomy_podcasting']) ) { ?>
                (<?php echo __('feature will appear in left menu when enabled', 'powerpress'); ?>)
                <?php } else { ?>
                <b><a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_taxonomyfeeds.php'); ?>">(<?php echo __('configure taxonomy podcasting', 'powerpress'); ?>)</a></b>
                <?php } ?>
                </p>
            </div>
		</div>
		<div>
			<input type="hidden" name="General[posttype_podcasting]" value="0" />
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[posttype_podcasting]" value="1" <?php echo ( !empty($General['posttype_podcasting']) ?' checked':''); ?> />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Post Type Podcasting', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Manage multiple media files and/or formats to specific post types.', 'powerpress'); ?>
                <?php if( empty($General['posttype_podcasting']) ) { ?>
                (<?php echo __('feature will appear in left menu when enabled', 'powerpress'); ?>)
                <?php } else { ?>
                <b><a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_posttypefeeds.php'); ?>">(<?php echo __('configure post type podcasting', 'powerpress'); ?>)</a></b>
                <?php } ?>
                </p>
            </div>
		</div>
		<div>
			<input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[playlist_player]" value="1" <?php echo ( !empty($General['playlist_player']) ?' checked':''); ?> />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('PowerPress Playlist Player', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Create playlists for your podcasts.', 'powerpress'); ?>
                <b><a href="https://blubrry.com/support/powerpress-documentation/powerpress-playlist-shortcode/" target="_blank">(<?php echo __('learn more', 'powerpress'); ?>)</a></b>
                </p>
            </div>
		</div>
        <div>
            <input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[powerpress_network]" value="1" <?php echo ( !empty($General['powerpress_network']) ?' checked':''); ?> />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('PowerPress Network', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Create a network of podcasts.', 'powerpress'); ?>
                    <b><a href="https://blubrry.com/support/powerpress-documentation/podcast-network/" target="_blank">(<?php echo __('learn more', 'powerpress'); ?>)</a></b>
                </p>
            </div>
        </div>
        <div>
            <input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[powerpress_accept_json]" value="1" <?php echo ( !empty($General['powerpress_accept_json']) ?' checked':''); ?> />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Allow JSON uploads', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Check this box if you plan to upload chapter files to your WordPress site.', 'powerpress'); ?>
                </p>
            </div>
        </div>
        <div>
            <input class="pp-settings-checkbox" style="margin-top: 3em;" type="checkbox" name="General[pp_show_block_errors]" value="1" <?php echo ( !isset($General['pp_show_block_errors']) || $General['pp_show_block_errors'] ?' checked':''); ?> />
            <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 2em;">
                <p class="pp-main"><?php echo __('Show errors in Block Editor', 'powerpress'); ?></p>
                <p class="pp-sub"><?php echo __('Disable if you are not planning to use the PowerPress Block.', 'powerpress'); ?>
                </p>
            </div>
        </div>
        <?php
        powerpressadmin_edit_media_statistics($General);
        powerpress_settings_tab_footer(); ?>
	</div>
</div>

<?php
}

function powerpressadmin_experimental_options($General, $link_account = false)
{
    $lightning = isset($General['value_lightning']) ? $General['value_lightning'] : array("");
    $splits = isset($General['value_split']) ? $General['value_split'] : array("");
    $pubKeys = isset($General['value_pubkey']) ? $General['value_pubkey'] : array("");
    $customKeys = isset($General['value_custom_key']) ? $General['value_custom_key'] : array("");
    $customValues = isset($General['value_custom_value']) ? $General['value_custom_value'] : array("");

    $currentPersonCount = count($pubKeys);
    if (empty($General['value_pubkey']))
        $currentPersonCount = 0;

    $valueError = isset($General['value_error']) ? $General['value_error'] : "no";
    $valueError = $valueError == "yes";

    $valueErrorMsg = isset($General['value_error_message']) ? $General['value_error_message'] : "";
    ?>
    <script>
        let currentPersonCount = <?php echo $currentPersonCount; ?>;
        jQuery(document).ready(function() {
            jQuery(document).on('click',"[name*='remove-person-']",function (e) {
                let personNum = this.id[this.id.length - 1];
                jQuery("#person-" + personNum + "-container").css({"visibility": "hidden", "position": "absolute"});
                jQuery("[name='person-" + personNum + "-pubkey']").val("");
                jQuery("[name='person-" + personNum + "-split']").val("");
            });

            jQuery("[name='newperson']").click(function (e) {
                jQuery("#newperson").css({"display": "none"});
                let tempCount = currentPersonCount + 1;
                let newHTML = '<div class="col mt-4 pl-0 pr-0" id="person-select-' + tempCount + '">' +
                    '<div class="row" style="font-size: 110%; font-weight: bold; margin-bottom: 5px;" id="person-head">' +
                    '<div class="col-lg-11 pl-0">' + tempCount + '. ' + ' </div><div class="col-lg-1"><button class="value-btn float-right pl-0" type="button" style="border: none; background: inherit; color: red; font-size: 25px;" id="cancel-btn" name="cancel-btn">&times;</button></div>' +
                    '</div>' +
                    '<div class="row" style="margin-bottom: 5px;">' +
                    '<div class="col-lg-3 pl-0">' +
                    '<label style="font-size: 110%; font-weight: bold;" for="value-select"><?php echo __('Wallet Service', 'powerpress'); ?></label>' +
                    '</div>' +
                    '<div class="col-lg-8 pl-0">' +
                    '<label style="font-size: 110%; font-weight: bold;" for="person-' + tempCount + '-select-lightning"><?php echo __('Lightning Address', 'powerpress'); ?></label>' +
                    '</div>' +
                    '<div class="col-lg-1 pl-0">' +
                    '</div>' +
                    '</div>' +
                    '<div class="row">' +
                    '<div class="col-lg-3 pl-0">' +
                    '<select name="value-select" id="value-select" class="pp-settings-select" style="width: 100% !important;">' +
                    '<option selected value="getalby"><?php echo __('Alby', 'powerpress'); ?></option>' +
                    '<option value="fountain"><?php echo __('Fountain', 'powerpress'); ?></option>' +
                    '<option value="manual"><?php echo __('Manual Entry', 'powerpress'); ?></option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="col-lg-8 pl-0">' +
                    '<input class="pp-settings-text-input" type="text" id="person-' + tempCount + '-select-lightning" name="person-' + tempCount + '-select-lightning" />' +
                    '</div>' +
                    '<div class="col-lg-1 pl-0" style="display: flex; alight-items: center;">' +
                    '<button class="value-btn pl-0" type="button" style="border: none; background: inherit; font-size: 40px;" id="next-btn" name="next-btn">&#8594</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>';

                if (currentPersonCount == 0) {
                    jQuery(newHTML).insertBefore("#add-person-container");
                } else {
                    let prevId = '#person-' + (currentPersonCount) + '-container';
                    jQuery(newHTML).insertAfter(prevId);
                }
            });

            jQuery("#recipient-container").on("change", "#value-select", function (e) {
                let selectedType = jQuery("[name='value-select']").val();

                if (selectedType === "manual") {
                    jQuery("#next-btn").click();
                }
            });

            jQuery("#recipient-container").on("click", "#cancel-btn", function (e) {
                let tempCount = currentPersonCount + 1;
                jQuery("#person-select-"+tempCount).remove();
                jQuery("#newperson").css({"display": "block"});
            });

            jQuery("#recipient-container").on("click", "#next-btn", function (e) {
                currentPersonCount += 1;

                let selectedType = jQuery("[name='value-select']").val();
                let defaultLightning = "";
                let defaultPubKey = "";
                let defaultCustomKey = "";
                let defaultCustomValue = "";
                let error = false;

                if (selectedType !== "manual") {
                    defaultLightning = jQuery("#person-" + currentPersonCount + "-select-lightning").val().replace(/\s/g,'');
                    let trimmedLightning = defaultLightning.substring(0, defaultLightning.indexOf("@"));
                    switch (selectedType) {
                        case "getalby":
                            jQuery.ajax({
                                async: false,
                                type: 'GET',
                                url: "https://getalby.com/.well-known/keysend/"+trimmedLightning,
                                success: function(data) {
                                    defaultPubKey = data['pubkey'];
                                    defaultCustomKey = data['customData'][0]['customKey'];
                                    defaultCustomValue = data['customData'][0]['customValue'];
                                },
                            }).fail(function () {
                                error = true;
                            });
                            break;
                        case "fountain":
                            jQuery.ajax({
                                async: false,
                                type: 'GET',
                                url: "https://api.fountain.fm/v1/lnurlp/"+trimmedLightning+"/keysend",
                                success: function(data) {
                                    if (data["status"] == "Not Found") {
                                        error = true;
                                    } else {
                                        defaultPubKey = data['pubkey'];
                                        defaultCustomKey = data['customData'][0]['customKey'];
                                        defaultCustomValue = data['customData'][0]['customValue'];
                                    }
                                },
                            }).fail(function () {
                                error = true;
                            });
                    }
                }

                if (!error) {
                    jQuery("#person-select-"+currentPersonCount).remove();
                    let newHTML = '<div class="row mt-4" id="person-' + currentPersonCount + '-container" style="display: flex; align-items: center;">' +
                        '<div class="col">' +
                        '<div class="row" style="font-size: 110%; font-weight: bold;">' +
                        '<div class="col-lg-11 pl-0">' + currentPersonCount + '.  </div>' +
                        '<div class="col-lg-1"><button class="value-btn float-right pl-0" type="button" style="border: none; background: inherit; color: red; font-size: 25px;" id="remove-person-' + currentPersonCount + '" name="remove-person-' + currentPersonCount + '">&times;</button></div>' +
                        '</div>' +
                        '<div class="row pl-0">' +
                        '<div class="col-lg-4 pl-0">' +
                        '<label style="font-size: 110%; font-weight: bold;" for="person-' + currentPersonCount + '-lightning"><?php echo __('Lightning Address / Name', 'powerpress'); ?></label>' +
                        '<br />' +
                        '<br />' +
                        '<input class="pp-settings-text-input" type="text" id="person-' + currentPersonCount + '-lightning" name="person-' + currentPersonCount + '-lightning" value="' + defaultLightning + '" />' +
                        '</div>' +
                        '<div class="col-lg-4 pl-0">' +
                        '<label style="font-size: 110%; font-weight: bold;" for="person-' + currentPersonCount + '-customkey"><?php echo __('Custom Key', 'powerpress'); ?></label>' +
                        '<br />' +
                        '<br />' +
                        '<input class="pp-settings-text-input" type="text" id="person-' + currentPersonCount + '-customkey" name="person-' + currentPersonCount + '-customkey" value="' + defaultCustomKey + '" />' +
                        '</div>' +
                        '<div class="col-lg-4 pl-0">' +
                        '<label style="font-size: 110%; font-weight: bold;" for="person-' + currentPersonCount + '-customvalue"><?php echo __('Custom Value', 'powerpress'); ?></label>' +
                        '<br />' +
                        '<br />' +
                        '<input class="pp-settings-text-input" type="text" id="person-' + currentPersonCount + '-customvalue" name="person-' + currentPersonCount + '-customvalue" value="' + defaultCustomValue + '" />' +
                        '</div>' +
                        '</div>' +
                        '<div class="row pl-0" style="margin-top: 20px;">' +
                        '<div class="col-lg-8 pl-0">' +
                        '<label style="font-size: 110%; font-weight: bold;" for="person-' + currentPersonCount + '-pubkey"><?php echo __('PubKey', 'powerpress'); ?></label>' +
                        '<br />' +
                        '<br />' +
                        '<input class="pp-settings-text-input" type="text" id="person-' + currentPersonCount + '-pubkey" name="person-' + currentPersonCount + '-pubkey" value="' + defaultPubKey + '" />' +
                        '</div>' +
                        '<div class="col-lg-4 pl-0">' +
                        '<label style="font-size: 110%; font-weight: bold;" for="person-' + currentPersonCount + '-split"><?php echo __('Split', 'powerpress'); ?></label>' +
                        '<br />' +
                        '<br />' +
                        '<input class="pp-settings-text-input" type="number" id="person-' + currentPersonCount + '-split" name="person-' + currentPersonCount + '-split" />' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    if (currentPersonCount == 1) {
                        jQuery(newHTML).insertBefore("#add-person-container");
                    } else {
                        let prevId = '#person-' + (currentPersonCount - 1) + '-container';
                        jQuery(newHTML).insertAfter(prevId);
                    }
                    jQuery("#newperson").css({"display": "block"});
                } else {
                    currentPersonCount -= 1;
                    let newHTML = '<div class="alert alert-danger" role="alert"><span><?php echo __("We were unable to locate wallet information for lightning address: ", 'powerpress'); ?>'+defaultLightning+'. <?php echo __("Please double check your entry and try again.", 'powerpress'); ?></span></div>';
                    jQuery(newHTML).insertBefore("#person-head");
                }
            });
        });
    </script>
    <style>
        .value-btn:hover {
            cursor: pointer;
        }
    </style>
    <div style="margin-left: 10px;">
        <button style="display: none;" id="experimental-default-open" class="pp-sidenav-tablinks active" onclick="sideNav(event, 'experimental-all')"><img class="pp-nav-icon" style="width: 22px;" alt="" src="<?php echo powerpress_get_root_url(); ?>images/settings_nav_icons/rss-symbol.svg"><?php echo htmlspecialchars(__('Hidden button', 'powerpress')); ?></button>
        <div id="experimental-all" class="pp-sidenav-tab active">
            <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: center;">
                <h1 class="pp-heading"><?php echo __('Value4Value (V4V)', 'powerpress'); ?></h1>
                <a href="https://blubrry.com/support/podcasting-2-0-introduction/" style="color: inherit; text-decoration: none;" target="_blank"><div class="pp-tooltip-right" style="height: 20px; width: 20px; margin: 1ch 0 0 1ch;">i</div></a>
            </div>
            <?php
            if ($valueError) {
            ?>
                <div class="alert alert-danger" role="alert">
                    <span><?php echo __($valueErrorMsg, 'powerpress'); ?></span>
                </div>
            <?php } ?>
            <div class="row mr-0 ml-0">
                <p class="pp-sub">
                    <?php
                    echo __('The Value Tag is part of the Podcasting 2.0 initiative geared at helping podcasters receive contributions from their listeners.
                    We highly recommend you review our dedicated documentation on the Value Tag as it is a complex topic.
                    Blubrry has partnered with Alby to participate in the Value 4 Value podcast model.
                    Signing up with ', 'powerpress');
                    echo '<a href="http://getalby.com/">'.__('Alby', 'powerpress').'</a> ';
                    echo __('(free) will get you a user@getalby.com address you can enter in the configuration below.', 'powerpress');
                    ?>
                </p>
            </div>
            <div class="row mr-0 ml-0 mt-4">
                <p class="pp-sub">
                    <?php
                    echo __('Alby and Fountain users only need to enter their Alby or Fountain address and click the arrow, and we will pre-populate the appropriate fields.', 'powerpress');
                    ?>
                </p>
            </div>
            <div class="row mr-0 ml-0 mt-4">
                <p class="pp-sub">
                    <?php echo __('Note:', 'powerpress'); ?>
                    <?php echo __('Powerpress adds an automatic 3% split to support the development of the plug-in . Thanks for your support!', 'powerpress'); ?>
                </p>
            </div>
            <div class="row mr-0 ml-0 mt-4">
                <p class="pp-sub">
                    <strong><?php echo __('Warning:', 'powerpress'); ?></strong>
                    <?php echo __('For those entering the data manually, you should check with your vendor on the valid entries for each field.', 'powerpress'); ?>
                </p>
            </div>
            <div class="col" id="recipient-container">
            <?php
            if ($currentPersonCount > 0) {
                for ($i = 0; $i < count($pubKeys); $i++) {
                $personNum = $i + 1;
                ?>
                <div class="row mt-4" id="person-<?php echo $personNum ?>-container" style="display: flex; align-items: center;">
                    <div class="col-12">
                        <div class="row" style="font-size: 110%; font-weight: bold; height: 39.5px;">
                            <div class="col-lg-11 pl-0" id="person-<?php echo $personNum; ?>-header">
                                <?php echo $personNum ?>. <?php echo htmlspecialchars($lightning[$i]) ?>
                            </div>
                            <div class="col-lg-1">
                                <button class="value-btn float-right pl-0" type="button" style="border: none; background: inherit; color: red; font-size: 25px;" id="remove-person-<?php echo $personNum; ?>" name="remove-person-<?php echo $personNum; ?>">&times;</button>
                            </div>
                        </div>
                        <div class="row pl-0">
                            <div class="col-lg-4 pl-0">
                                <label style="font-size: 110%; font-weight: bold;">
                                    <?php echo __('Lightning Address / Name', 'powerpress'); ?>
                                    <?php if ($personNum == 1) {?>
                                        <div class="pp-tooltip-right" style="height: 16px; width: 16px;">i
                                            <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__('This can be your Alby address or a lightning address from another wallet provider. If you do not have a lightning address, you may just enter a name (e.g., John Smith).', 'powerpress')); ?></span>
                                        </div>
                                    <?php }?>
                                </label>
                                <br />
                                <br />
                                <input class="pp-settings-text-input" type="text" id="person-<?php echo $personNum; ?>-lightning" name="person-<?php echo $personNum; ?>-lightning" value="<?php echo htmlspecialchars($lightning[$i]) ?>" />
                            </div>
                            <div class="col-lg-4 pl-0">
                                <label style="font-size: 110%; font-weight: bold;" for="person-<?php echo $personNum; ?>-customkey">
                                    <?php echo __('Custom Key', 'powerpress'); ?>
                                </label>
                                <br />
                                <br />
                                <input class="pp-settings-text-input" type="text" id="person-<?php echo $personNum; ?>-customkey" name="person-<?php echo $personNum; ?>-customkey" value="<?php echo htmlspecialchars($customKeys[$i]) ?>" />
                            </div>
                            <div class="col-lg-4 pl-0">
                                <label style="font-size: 110%; font-weight: bold;" for="person-<?php echo $personNum; ?>-customvalue">
                                    <?php echo __('Custom Value', 'powerpress'); ?>
                                </label>
                                <br />
                                <br />
                                <input class="pp-settings-text-input" type="text" id="person-<?php echo $personNum; ?>-customvalue" name="person-<?php echo $personNum; ?>-customvalue" value="<?php echo htmlspecialchars($customValues[$i]) ?>" />
                            </div>
                        </div>
                        <div class="row pl-0" style="margin-top: 20px;">
                            <div class="col-lg-8 pl-0">
                                <label style="font-size: 110%; font-weight: bold;" for="person-<?php echo $personNum; ?>-pubkey">
                                    <?php echo __('PubKey', 'powerpress'); ?>
                                </label>
                                <br />
                                <br />
                                <input class="pp-settings-text-input" type="text" id="person-<?php echo $personNum; ?>-pubkey" name="person-<?php echo $personNum; ?>-pubkey" value="<?php echo htmlspecialchars($pubKeys[$i]) ?>" />
                            </div>
                            <div class="col-lg-4 pl-0">
                                <label style="font-size: 110%; font-weight: bold;" for="person-<?php echo $personNum; ?>-split">
                                    <?php echo __('Split', 'powerpress'); ?>
                                </label>
                                <br />
                                <br />
                                <input class="pp-settings-text-input" type="number" id="person-<?php echo $personNum; ?>-split" name="person-<?php echo $personNum; ?>-split" value="<?php echo htmlspecialchars($splits[$i]) ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            <?php }
            }?>
                <div class="row pr-4" style="margin-top: 20px;" id="add-person-container">
                    <div class="col-lg-12" style="display: flex; justify-content: flex-end;">
                        <button class="value-btn" type="button" style="border: none; background: inherit; color: #1976D2;" id="newperson" name="newperson"><?php echo __('+ Add Person', 'powerpress'); ?></button>
                    </div>
                </div>
            </div>
            <hr />
            <?php powerpress_settings_tab_footer(); ?>
        </div>
    </div>

    <?php
}

function powerpressadmin_edit_podpress_options($General)
{
	if( !empty($General['process_podpress']) || powerpress_podpress_episodes_exist() )
	{
		if( !isset($General['process_podpress']) )
			$General['process_podpress'] = 0;
		if( !isset($General['podpress_stats']) )	
			$General['podpress_stats'] = 0;
?>

<h3><?php echo __('PodPress Options', 'powerpress'); ?></h3>
<table class="form-table">
<tr valign="top">
<th scope="row">

<?php echo __('PodPress Episodes', 'powerpress'); ?></th> 
<td>
<select name="General[process_podpress]" class="bpp_input_med">
<?php
$options = array(0=>__('Ignore', 'powerpress'), 1=>__('Include in Posts and Feeds', 'powerpress') );

foreach( $options as $value => $desc )
	echo "\t<option value=\"$value\"". ($General['process_podpress']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>  (<?php echo __('includes podcast episodes previously created in PodPress', 'powerpress'); ?>)
</td>
</tr>
	<?php if( !empty($General['podpress_stats']) || powerpress_podpress_stats_exist() ) { ?>
	<tr valign="top">
	<th scope="row">

	<?php echo __('PodPress Stats Archive', 'powerpress'); ?></th> 
	<td>
	<select name="General[podpress_stats]" class="bpp_input_sm">
	<?php
	$options = array(0=>__('Hide', 'powerpress'), 1=>__('Display', 'powerpress') );

	foreach( $options as $value => $desc )
		echo "\t<option value=\"$value\"". ($General['podpress_stats']==$value?' selected':''). ">$desc</option>\n";
		
	?>
	</select>  (<?php echo __('display archive of old PodPress statistics', 'powerpress'); ?>)
	</td>
	</tr>
	<?php } ?>
	</table>
<?php
	}
}

function powerpressadmin_edit_itunes_general($FeedSettings, $General, $FeedAttribs = array() )
{
	// Set default settings (if not set)
	if( !empty($FeedSettings) )
	{
		if( !isset($FeedSettings['itunes_url']) )
			$FeedSettings['itunes_url'] = '';
	}
	if( !isset($General['itunes_url']) )
		$General['itunes_url'] = '';
	else if( !isset($FeedSettings['itunes_url']) ) // Should almost never happen
		$FeedSettings['itunes_url'] = $General['itunes_url'];
	
	$feed_slug = $FeedAttribs['feed_slug'];
	$cat_ID = $FeedAttribs['category_id'];
	
	if( $feed_slug == 'podcast' && $FeedAttribs['type'] == 'general' )
	{
		if( empty($FeedSettings['itunes_url']) && !empty($General['itunes_url']) )
			$FeedSettings['itunes_url'] = $General['itunes_url'];
	}
	
	$itunes_feed_url = '';

	switch( $FeedAttribs['type'] )
	{
		case 'ttid': {
			$itunes_feed_url = get_term_feed_link($FeedAttribs['term_taxonomy_id'], $FeedAttribs['taxonomy_type'], 'rss2');
		}; break;
		case 'category': {
			if( !empty($General['cat_casting_podcast_feeds']) )
				$itunes_feed_url = get_category_feed_link($cat_ID, 'podcast');
			else
				$itunes_feed_url = get_category_feed_link($cat_ID);
		}; break;
		case 'channel': {
			$itunes_feed_url = get_feed_link($feed_slug);
		}; break;
		case 'post_type': {
			$itunes_feed_url = get_post_type_archive_feed_link($FeedAttribs['post_type'], $feed_slug);
		}; break;
		case 'general':
		default: {
			$itunes_feed_url = get_feed_link('podcast');
		}
	}
	
?>
<h3><?php echo __('iTunes Listing Information', 'powerpress'); ?></h3>

<?php
} // end itunes general

function powerpressadmin_edit_blubrry_services($General, $action_url = false, $action = false)
{
	$DisableStatsInDashboard = false;
	if( !empty($General['disable_dashboard_stats']) )
		$DisableStatsInDashboard = true;

?>
<div id="connect-blubrry-services">
    <?php
    $creds = get_option('powerpress_creds');
    if( $creds ) { ?>
        <div id="blubrry-services-connected-settings">
            <div style="margin-bottom: 1em;">
                <span><img src="<?php echo powerpress_get_root_url(); ?>images/done_24px.svg" style="margin: 0 0 0 8%;vertical-align: text-bottom;"  alt="<?php echo __('Enabled!', 'powerpress'); ?>" /></span>
                <p id="connected-blubrry-blurb"><?php echo __("Connected to <b>Blubrry</b>", 'powerpress'); ?></p>
            </div>
            <a style="display: block;" class="thickbox" title="<?php echo esc_attr(__('Blubrry Services Integration', 'powerpress')); ?>" href="<?php echo admin_url(); echo wp_nonce_url( "admin.php?action=powerpress-jquery-account-edit", 'powerpress-jquery-account-edit'); ?>&amp;KeepThis=true&amp;TB_iframe=true&amp;width=600&amp;height=400&amp;modal=true" target="_blank"><?php echo __('Blubrry Hosting Settings', 'powerpress'); ?></a>
        </div>
    <?php
    }
	else // Not signed up for hosting?
	{
?>
        <div id="connect-see-options">
            <img id="blubrry-logo-connect" alt="" src="<?php echo powerpress_get_root_url(); ?>images/blubrry_icon.png">
            <h4><?php echo sprintf(__('<b>PowerPress</b> works best with <b>Blubrry</b>', 'powerpress')); ?></h4>
            <p id="connect-blubrry-blurb"><?php echo sprintf(__('Get access to detailed analytics and more by <b>connecting to your Blubrry Hosting Account.</b>', 'powerpress')); ?></p>
            <p style="font-size: 125%; margin: 1ch 0 0 1ch">
                <strong><a class="button-primary  button-blubrry" id="connect-blubrry-button-options"
                           title="<?php echo esc_attr(__('Blubrry Services Info', 'powerpress')); ?>"
                           href="https://blubrry.com/services/podcast-hosting/"
                           target="_blank"><?php echo __('SEE MY OPTIONS', 'powerpress'); ?></a></strong>
            </p>
        </div>
        <div id="connect-blubrry-button-container">
            <p style="margin-top: 1ch;" class="pp-settings-text-no-margin"><?php echo __('Already have a Blubrry account?', 'powerpress'); ?></p>
            <p style="font-size: 125%; margin-top: 5px;">
                <strong><button class="button-primary  button-blubrry" id="connect-blubrry-button-options"
                           type="submit" name="blubrry-login" value="1"
                           title="<?php echo esc_attr(__('Blubrry Services Integration', 'powerpress')); ?>">
                        <?php echo __('LET\'S CONNECT', 'powerpress'); ?></button></strong>

            </p>
        </div>
<?php
	} // end not signed up for hosting
	
?>

</div>
<?php
    if (time() < strtotime('August 1 2023')) {
        $pp_notif = new PowerPress_Notification_Manager();
        $pp_notif->print_one_notice('more-test', true);
    }
}

function powerpressadmin_edit_media_statistics($General)
{
	if( !isset($General['redirect1']) )
		$General['redirect1'] = '';
	if( !isset($General['redirect2']) )
		$General['redirect2'] = '';
	if( !isset($General['redirect3']) )
		$General['redirect3'] = '';

    $DisableStatsInDashboard = false;
    if( !empty($General['disable_dashboard_stats']) )
        $DisableStatsInDashboard = true;

    $StatsIntegrationURL = '';
	if( !empty($General['blubrry_program_keyword']) )
		$StatsIntegrationURL = 'https://media.blubrry.com/'.$General['blubrry_program_keyword'].'/';
?>
    <script>
        function showSecondRedirectInput(event) {
            event.preventDefault();
            document.getElementById('powerpress_redirect2_table').style.display = 'block';
            document.getElementById('powerpress_redirect2_showlink').style.display='none';

        }
        function showThirdRedirectInput(event) {
            event.preventDefault();
            document.getElementById('powerpress_redirect3_table').style.display='block';
            document.getElementById('powerpress_redirect3_showlink').style.display='none';
        }
    </script>
<div id="blubrry_stats_settings">
<h2><?php echo __('Media Statistics', 'powerpress'); ?></h2>
    <div>
        <input name="DisableStatsInDashboard" class="pp-settings-checkbox" style="margin-top: 1em;" type="checkbox" value="1"<?php if( $DisableStatsInDashboard == true ) echo ' checked'; ?> />
        <div class="pp-settings-subsection" style="border-bottom: none; margin-top: 0;">
            <p class="pp-main"><?php echo __('Remove Statistics from WordPress Dashboard', 'powerpress'); ?></p>
        </div>
    </div>
	<div>
        <h4><?php echo __('STATS PREFIX', 'powerpress'); ?></h4>
        <p class="pp-settings-text-no-margin">
		<?php echo __('Enter your Redirect URL issued by your media statistics service provider below.', 'powerpress'); ?>
		</p>
        <div id="stats-prefix-notice" class="card alert alert-danger p-3">
            <h4 style="margin-bottom: 0.5em;">
                <img src="<?php echo powerpress_get_root_url(); ?>images/circleerror_black.svg" alt="Notice" style="width: 24px; vertical-align: text-bottom;" />
                <p>Notice</p>
            </h4>
            <p><b>Before setting a stats prefix, please carefully read the criteria below.</b></p>
            <ul>
                <li>Not all redirect/prepend prefix services are engineered the same. Once you add a prefix/prepend be aware you are reliant on those companies’ service to “always” be online. A failure on their platform will result in your show’s media not being delivered.
                    <ul>
                        <li style="font-size: 100%;">Service Reliability and LSA: All 3rd party redirect services void the Blubrry Service-Level Agreement (SLA). Please be sure the redirect service you are using has a comparable SLA and uptime guarantee.</li>
                    </ul>
                </li>
                <li>Compatibility: Third party redirect/prepend service must be HTTPS The very beginning of the prefix must have https://, https:// is only at the beginning of the url and shouldn't be anywhere in the middle of the prefix.</li>
                <li>Please review carefully the redirect/prepend companies’ service agreement. Companies may use the collected data for marketing, re-targeting your listener, attribution, or measuring your media to include reporting or selling that data to a third party.</li>
                <li>GDPR/CCPA compliance:  The service must be GDPR/CCPA compliant. We will remove the service if it is not GDPR compliant. Your provider should have public-facing documents to certify GDPR/CCPA compliance.</li>
            </ul>
        </div>

		<div style="position: relative; padding-bottom: 10px;">
			<table class="form-table">
			<tr valign="top">
			<th scope="row">
			<?php echo __('Stats Prefix 1', 'powerpress'); ?>
			</th>
			<td>
			<input type="text" class="pp-settings-text-input" name="<?php if( $StatsIntegrationURL && stripos($General['redirect1'], $StatsIntegrationURL) !== false ) echo 'NULL[redirect1]'; else echo 'General[redirect1]'; ?>" value="<?php echo esc_attr($General['redirect1']); ?>" maxlength="255" <?php if( $StatsIntegrationURL && stripos($General['redirect1'], $StatsIntegrationURL) !== false ) { echo ' readOnly="readOnly"';  $StatsIntegrationURL = false; } ?> />
			</td>
			</tr>
			</table>
			<?php if( empty($General['redirect2']) && empty($General['redirect3']) ) { ?>
			<div style="position: absolute;bottom: -2px;left: -40px;" id="powerpress_redirect2_showlink">
				<a href="#" style="margin-left: 40px;" onclick="showSecondRedirectInput(event)"><?php echo __('Add Another Prefix', 'powerpress'); ?></a href="#">
			</div>
			<?php } ?>
		</div>
	
		
		<div id="powerpress_redirect2_table" style="position: relative; <?php if( empty($General['redirect2']) && empty($General['redirect3']) ) echo 'display:none;'; ?> padding-bottom: 10px;">
			<table class="form-table">
			<tr valign="top">
			<th scope="row">
			<?php echo __('Stats Prefix 2', 'powerpress'); ?>
			</th>
			<td>
			<input type="text" class="pp-settings-text-input" name="<?php if( $StatsIntegrationURL && stripos($General['redirect2'], $StatsIntegrationURL) !== false ) echo 'NULL[redirect2]'; else echo 'General[redirect2]'; ?>" value="<?php echo esc_attr($General['redirect2']); ?>" maxlength="255" <?php if( $StatsIntegrationURL && stripos($General['redirect2'], $StatsIntegrationURL) !== false ) { echo ' readOnly="readOnly"';  $StatsIntegrationURL = false; } ?> />
			</td>
			</tr>
			</table>
			<?php if( $General['redirect3'] == '' ) { ?>
			<div style="position: absolute;bottom: -2px;left: -40px;" id="powerpress_redirect3_showlink">
				<a href="#" style="margin-left: 40px;" onclick="showThirdRedirectInput(event)"><?php echo __('Add Another Prefix', 'powerpress'); ?></a>
			</div>
			<?php } ?>
		</div>

		<div id="powerpress_redirect3_table" style="<?php if( empty($General['redirect3']) ) echo 'display:none;'; ?>">
			<table class="form-table">
			<tr valign="top">
			<th scope="row">
			<?php echo __('Stats Prefix 3', 'powerpress'); ?>
			</th>
			<td>
			<input type="text" class="pp-settings-text-input" name="<?php if( $StatsIntegrationURL && stripos($General['redirect3'], $StatsIntegrationURL) !== false ) echo 'NULL[redirect3]'; else echo 'General[redirect3]'; ?>" value="<?php echo esc_attr($General['redirect3']); ?>"  maxlength="255" <?php if( $StatsIntegrationURL && stripos($General['redirect3'], $StatsIntegrationURL) !== false ) echo ' readOnly="readOnly"'; ?> />
			</td>
			</tr>
			</table>
		</div>
	<style type="text/css">
	#TB_window {
		border: solid 1px #3D517E;
	}
	</style>
	</div>
</div><!-- end blubrry_stats_settings -->
<?php
}

	
function powerpressadmin_appearance($General=false, $Feed = false)
{
	if( $General === false )
		$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'appearance');
	if( !isset($General['player_function']) )
		$General['player_function'] = 1;
	if( !isset($General['player_aggressive']) )
		$General['player_aggressive'] = 0;
	if( !isset($General['new_window_width']) )
		$General['new_window_width'] = '';
	if( !isset($General['new_window_height']) )
		$General['new_window_height'] = '';
	if( !isset($General['player_width']) )
		$General['player_width'] = '';
	if( !isset($General['player_height']) )
		$General['player_height'] = '';
	if( !isset($General['player_width_audio']) )
		$General['player_width_audio'] = '';	
	if( !isset($General['disable_appearance']) )
		$General['disable_appearance'] = false;
	if( !isset($General['subscribe_links']) )
		$General['subscribe_links'] = true;
	if( !isset($General['subscribe_label']) )
		$General['subscribe_label'] = '';	
		
		
	/*
	$Players = array('podcast'=>__('Default Podcast (podcast)', 'powerpress') );
	if( isset($General['custom_feeds']) )
	{
		foreach( $General['custom_feeds'] as $podcast_slug => $podcast_title )
		{
			if( $podcast_slug == 'podcast' )
				continue;
			$Players[$podcast_slug] = sprintf('%s (%s)', $podcast_title, $podcast_slug);
		}
	}
	*/
    require_once( dirname(__FILE__).'/views/settings_tab_appearance.php' );
    powerpressadmin_website_settings($General, $Feed);
    powerpressadmin_blog_settings($General, $Feed);
    powerpress_subscribe_settings($General, $Feed);
    powerpress_shortcode_settings($General, $Feed);
    powerpressadmin_new_window_settings($General, $Feed);
?>

<?php  
} // End powerpress_admin_appearance()


// Admin page, footer
function powerpress_settings_tab_footer()
{ ?>
    <div class="pp-settings-footer">
        <?php powerpress_settings_save_button(); ?>
    </div>
    <?php
}
function powerpressadmin_welcome($GeneralSettings, $FeedSettings, $NewPostQueryString = '')
{
    if (isset($_GET['feed_slug'])) {
        $feed_slug = $_GET['feed_slug'];
    } else {
        $feed_slug = 'podcast';
    }
    if (isset($FeedSettings['itunes_image']) && !empty($FeedSettings['itunes_image'])) {
        $image = $FeedSettings['itunes_image'];
    } else {
        $image = powerpress_get_root_url() . 'images/pts_cover.jpg';
    }
    if (isset($FeedSettings['itunes_summary'])) {
        $description = $FeedSettings['itunes_summary'];
    } elseif (isset($FeedSettings['itunes_subtitle'])) {
        $description = $FeedSettings['itunes_subtitle'];
    } elseif (isset($FeedSettings['description'])) {
        $description = $FeedSettings['description'];
    } else {
        $description = '';
    }
    $numEp = powerpress_admin_episodes_per_feed($feed_slug);
?>
    <script>
        function goToArtworkSettings() {
            jQuery("#feeds-tab").click();
            jQuery("#feeds-artwork-tab").click();
            return false;
        }

        function goToDestinationSettings() {
            jQuery("#destinations-tab").click();
            jQuery("#destinations-apple-tab").click();
            return false;
        }
    </script>
<div>
    <div class="pp-settings-program-summary">
        <?php
        require_once('powerpressadmin-stats-widget.class.php');
        $widget = new PowerPressStatsWidget();
        $widget->powerpress_print_stats_widget();
        ?>
        <div class="prog-sum-head">
            <h2 class="pp-heading" id="welcome-title"><?php echo isset($FeedSettings['title']) ? htmlspecialchars($FeedSettings['title']) : ''; ?></h2>
            <div class="pp-settings-recent-post">
                <img id="welcome-preview-image" src="<?php echo $image; ?>" alt="Feed Image" />
                <div class="pp-settings-welcome-text">
                    <p class="pp-settings-text-no-margin" style="margin-bottom: 2ch;"><?php echo __('By', 'powerpress'); ?> <?php echo isset($FeedSettings['itunes_talent_name']) ? $FeedSettings['itunes_talent_name'] : ''; ?></p>
                    <p class="pp-settings-text-no-margin"><?php echo htmlspecialchars($description); ?></p>
                </div>
            </div>
            <div class="pp-settings-num-episodes">
                <p class="pp-settings-text-no-margin"><?php echo __('Number of Episodes', 'powerpress'); ?></p>
                <h2 class="pp-heading" style="margin-top: 5px;"><?php echo $numEp; ?></h2>
            </div>
        </div>
        <div class="prog-sum-contents">
            <a id="welcome-tab-new-post" href="<?php echo admin_url('post-new.php') . $NewPostQueryString; ?>">
                <div class="pp_button-container">
                    <?php echo __('CREATE NEW EPISODE', 'powerpress'); ?>
                </div>
            </a>
            <div class="pp-settings-podcast-status">
                <p class="pp-settings-text-no-margin" style="margin-bottom: 2ch;"><?php echo __('Podcast Status', 'powerpress'); ?></p>

                <?php if (!$GeneralSettings || (isset($GeneralSettings['pp_onboarding_incomplete']) && $GeneralSettings['pp_onboarding_incomplete'] == 1) && (isset($GeneralSettings['timestamp']) && $GeneralSettings['timestamp'] > 1576972800)) { ?>
                    <p class="pp-settings-status-text"><a class="program-status-link" href="<?php echo admin_url("admin.php?page=powerpressadmin_onboarding.php"); ?>"><img src="<?php echo powerpress_get_root_url(); ?>images/status_incomplete.svg" class="pp-settings-icon-small"  alt="<?php echo __('Not done', 'powerpress'); ?>" />Finish Show Prep</a></p>
                <?php } else { ?>
                    <p class="pp-settings-status-text"><img src="<?php echo powerpress_get_root_url(); ?>images/status_complete.svg" class="pp-settings-icon-small"  alt="<?php echo __('Done!', 'powerpress'); ?>" />Finished Show Prep</p>
                <?php }

                if (empty($FeedSettings['itunes_image'])) { ?>
                    <p id="pp-welcome-artwork-link" class="program-status-link" onclick="goToArtworkSettings();return false;"><img src="<?php echo powerpress_get_root_url(); ?>images/status_incomplete.svg" class="pp-settings-icon-small"  alt="<?php echo __('Not done', 'powerpress'); ?>" />Add Artwork to Show</p>
                <?php } else { ?>
                    <p class="pp-settings-status-text"><img src="<?php echo powerpress_get_root_url(); ?>images/status_complete.svg" class="pp-settings-icon-small"  alt="<?php echo __('Done!', 'powerpress'); ?>" />Added Artwork to Show</p>
                <?php } ?>

                <?php
                // Check if at least one destination has been set
                if(!empty($FeedSettings['itunes_url']) ||
                    !empty($FeedSettings['google_url']) ||
                    !empty($FeedSettings['spotify_url']) ||
                    !empty($FeedSettings['amazon_url']) ||
                    !empty($FeedSettings['android_url']) ||
                    !empty($FeedSettings['pandora_url']) ||
                    !empty($FeedSettings['iheart_url']) ||
                    !empty($FeedSettings['blubrry_url']) ||
                    !empty($FeedSettings['jiosaavn_url']) ||
                    !empty($FeedSettings['podchaser_url']) ||
                    !empty($FeedSettings['gaana_url']) ||
                    !empty($FeedSettings['pcindex_url']) ||
                    !empty($FeedSettings['tunein_url']) ||
                    !empty($FeedSettings['deezer_url']) ||
                    !empty($FeedSettings['anghami_url'])
                ){ ?>
                    <p id="pp-welcome-applesubmit-link" class="program-dest-welcome-link" onclick="goToDestinationSettings();return false;"><img src="<?php echo powerpress_get_root_url(); ?>images/status_complete.svg" class="pp-settings-icon-small"  alt="<?php echo __('Done!', 'powerpress'); ?>" />Set Destination Links</p>
                <?php } else { ?>
                    <p id="pp-welcome-applesubmit-link" class="program-status-link" onclick="goToDestinationSettings();return false;"><img src="<?php echo powerpress_get_root_url(); ?>images/status_incomplete.svg" class="pp-settings-icon-small"  alt="<?php echo __('Not done', 'powerpress'); ?>" />Set Destination Links</p>
                <?php } ?>

            </div>
        </div>
    </div>
	<div class="powerpress-welcome-news">
		<h2><?php echo __('<em>PODCAST INSIDER</em> NEWS &amp; UPDATES', 'powerpress'); ?></h2>
		<?php powerpressadmin_community_news(4, true); ?>
	</div>

	<div class="clear"></div>
</div>
<?php
} // End powerpressadmin_welcome()

function powerpressadmin_edit_funding($FeedSettings = false, $feed_slug='podcast', $cat_ID=false)
{
	if( !isset($FeedSettings['donate_link']) )
		$FeedSettings['donate_link'] = 0;
	if( !isset($FeedSettings['donate_url']) )
		$FeedSettings['donate_url'] = '';
	if( !isset($FeedSettings['donate_label']) )
		$FeedSettings['donate_label'] = '';

    if( !isset($FeedSettings['location']) )
        $FeedSettings['location'] = '';
    if( !isset($FeedSettings['pci_geo']) )
        $FeedSettings['pci_geo'] = '';
    if( !isset($FeedSettings['pci_osm']) )
        $FeedSettings['pci_osm'] = '';
    if( !isset($FeedSettings['frequency']) )
        $FeedSettings['frequency'] = '';
    ?>
    <style>
        #search-results, #feed-search-results, #episode-search-results {
            /* Remove default list styling */
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .search-result {
            border: 1px solid #ddd; /* Add a border to all links */
            background-color: #f6f6f6; /* Grey background color */
            display: flex; /* Make it into a block element to fill the whole list */
            align-items: center;
            height: calc(1.5em + 0.75em + 2px);
        }

        .search-result a {
            text-decoration: none; /* Remove default text underline */
            font-size: 1rem; /* Increase the font-size */
            color: black; /* Add a black text color */
            margin-left: 5%;
            margin-right: 5%;
            cursor: pointer;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            width: 90%;
            display: block;
        }

        .list-result {
            width: 80%;
            display: block;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .search-result:hover:not(.header) {
            background-color: #eee; /* Add a hover effect to all links, except for headers */
        }
    </style>
    <h1 class="pp-heading"><?php echo __('Basic Show Information', 'powerpress'); ?></h1>
    <div class="pp-settings-section">
        <h2><?php echo __('Location', 'powerpress'); ?></h2>
        <label for="Feed[location]" class="pp-settings-label"><?php echo __('Optional', 'powerpress'); ?></label>
        <input class="pp-settings-text-input" type="text" name="Feed[location]" oninput="powerpress_locationInput(event)" value="<?php echo esc_attr($FeedSettings['location']); ?>" maxlength="50" />
        <label for="Feed[location]" class="pp-settings-label-under"><?php echo __('e.g. Cleveland, Ohio', 'powerpress'); ?></label>
        <div id="pp-location-details" class="pp-settings-subsection" <?php if (empty($FeedSettings['location'])) { echo "style=\"display: none;\""; } ?>>
            <!-- Two text inputs for geo and osm, even listener on input for location so that pp-location-details appears when there is an input -->
            <label for="Feed[pci_geo]" class="pp-settings-label"><?php echo __('Geo', 'powerpress'); ?></label>
            <input class="pp-settings-text-input" type="text" name="Feed[pci_geo]" value="<?php echo esc_attr($FeedSettings['pci_geo']); ?>" maxlength="50" />
            <label for="Feed[pci_geo]" class="pp-settings-label-under"><?php echo __('e.g. geo:-27.86159,153.3169', 'powerpress'); ?></label>
            <br />
            <label for="Feed[pci_osm]" class="pp-settings-label"><?php echo __('OSM', 'powerpress'); ?></label>
            <input class="pp-settings-text-input" type="text" name="Feed[pci_osm]" value="<?php echo esc_attr($FeedSettings['pci_osm']); ?>" maxlength="50" />
            <label for="Feed[pci_osm]" class="pp-settings-label-under"><?php echo __('e.g. W43678282', 'powerpress'); ?></label>
        </div>
    </div>
    <div class="pp-settings-section">
        <h2><?php echo __('Episode Frequency', 'powerpress'); ?></h2>
        <div class="row ml-0 mr-0">
            <div class="form-check mr-4">
                <input class="form-check-input" type="radio" name="Feed[update_frequency]" id="daily" value="1" <?php echo !empty($FeedSettings['update_frequency']) && $FeedSettings['update_frequency'] == '1' ? 'checked' : '' ?>>
                <label class="form-check-label" for="daily" style="color: black; font-size: 1rem;">
                    <?php echo __("Daily", "powerpress"); ?>
                </label>
            </div>
            <div class="form-check mr-4">
                <input class="form-check-input" type="radio" name="Feed[update_frequency]" id="weekly" value="2" <?php echo !empty($FeedSettings['update_frequency']) && $FeedSettings['update_frequency'] == '2' ? 'checked' : '' ?>>
                <label class="form-check-label" for="weekly" style="color: black; font-size: 1rem;">
                    <?php echo __("Weekly", "powerpress"); ?>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="Feed[update_frequency]" id="monthly" value="3" <?php echo !empty($FeedSettings['update_frequency']) && $FeedSettings['update_frequency'] == '3' ? 'checked' : '' ?>>
                <label class="form-check-label" for="monthly" style="color: black; font-size: 1rem;">
                    <?php echo __("Monthly", "powerpress"); ?>
                </label>
            </div>
        </div>
        <div id="weekly-select" class="row ml-0 mr-0" style="display: <?php echo !empty($FeedSettings['update_frequency']) && $FeedSettings['update_frequency'] == '2' ? '' : 'none' ?>;">
            <?php
            $selectedDayList = explode(',', $FeedSettings['update_frequency_week'] ?? '');
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $count = 0;
            ?>
            <div class="row ml-0 mr-0" style="margin-top: 20px;">
                <?php
                foreach ($days as $day) {
                    ?>
                    <div class="form-check" style="display: flex; align-items: center; margin-right: 10px;">
                        <input class="form-check-input" type="checkbox" value="<?php echo $count;?>" name="Feed[freq-day-<?php echo $count; ?>]" id="freq-day-<?php echo $count; ?>" <?php echo in_array((string) $count, $selectedDayList) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="freq-day-<?php echo $count; ?>" style="color: black; font-size: 1rem;">
                            <?php echo __($day, "powerpress"); ?>
                        </label>
                    </div>
                    <?php
                    $count += 1;
                }
                ?>
            </div>
        </div>
        <div id="monthly-frequency" class="row ml-0 mr-0" style="margin-top: 20px; display: <?php echo !empty($FeedSettings['update_frequency']) && $FeedSettings['update_frequency'] == '3' ? '' : 'none' ?>;">
            <label class="mt-2 pp-settings-label" for="update_frequency_month">
                <?php echo __("Monthly Frequency", "powerpress"); ?>
            </label>
            <input class="pp-settings-text-input" required min="1" step="1" value="<?php echo $FeedSettings['update_frequency_month'] ?? '1'; ?>"
                   name="Feed[update_frequency_month]" id="update_frequency_month" type="number" />
        </div>
    </div>
    <div class="pp-settings-section">
        <h2>
            <?php echo __('Credits', 'powerpress'); ?>
            <div class="pp-tooltip-right" style="height: 20px; width: 20px; margin: 1ch 0 0 1ch;">i
                <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__('You can document your permanent host, co-host, engineer at the show level, etc. You should not duplicate credits at the episode level.', 'powerpress')); ?></span>
            </div>
        </h2>
        <div class="row mt-2 mb-3">
            <div class="col-lg-12">
                <?php
                $currentPersonCount = 0;
                $personNames = isset($FeedSettings['person_names']) ? $FeedSettings['person_names'] : array("");
                $personRoles = isset($FeedSettings['person_roles']) ? $FeedSettings['person_roles'] : array("Host");
                $personURLs = isset($FeedSettings['person_urls']) ? $FeedSettings['person_urls'] : array("");
                $linkURLs = isset($FeedSettings['link_urls']) ? $FeedSettings['link_urls'] : array("");

                for($i=0;$i<count($personNames); $i++) {
                    $currentPersonCount = $i + 1;
                    ?>
                    <div id="role-<?php echo $currentPersonCount; ?>-container">
                        <div class="row">
                            <div class="col-lg-3">
                                <label for="role-<?php echo $currentPersonCount; ?>-name" style="margin: 0;"><?php echo __("Person", "powerpress"); ?></label>
                            </div>
                            <div class="col-lg-2" style="padding: 0;">
                                <label for="role-<?php echo $currentPersonCount; ?>-role" style="margin: 0;">
                                    <?php echo __("Role", "powerpress"); ?>
                                </label>
                            </div>
                            <div class="col-lg-3">
                                <label for="role-<?php echo $currentPersonCount; ?>-personurl" style="margin: 0;">
                                    <?php echo __("Person Image URL", "powerpress"); ?>
                                    <?php if ($currentPersonCount == 1) { ?>
                                        <div class="pp-tooltip-right" style="height: 16px; width: 16px;">i
                                            <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__('This should link to a physical picture .jpeg, .jpg, .png of the individual credit.', 'powerpress')); ?></span>
                                        </div>
                                    <?php } ?>
                                </label>
                            </div>
                            <div class="col-lg-3">
                                <label for="role-<?php echo $currentPersonCount; ?>-linkurl" style="margin: 0;">
                                    <?php echo __("Link URL", "powerpress"); ?>
                                    <?php if ($currentPersonCount == 1) { ?>
                                        <div class="pp-tooltip-right" style="height: 16px; width: 16px;">i
                                            <span class="text-pp-tooltip" style="top: -50%; min-width: 200px;"><?php echo esc_html(__('This should be the link to an information page about the person getting the credit, aka a LinkedIn page or a profile page on a website.', 'powerpress')); ?></span>
                                        </div>
                                    <?php } ?>
                                </label>
                            </div>
                            <div class="col-lg-1"></div>
                        </div>
                        <div class="row" style="display: flex; align-items: center;">
                            <div class="col">
                                <div class="row" style="display: flex; align-items: center; margin-bottom: 15px;">
                                    <div class="col-lg-3">
                                        <input type="text" value="<?php echo htmlspecialchars($personNames[$i]); ?>" name="role-<?php echo $currentPersonCount; ?>-name" class="pp-settings-text-input" />
                                    </div>
                                    <div class="col-lg-2" style="padding: 0;">
                                        <select name="role-<?php echo $currentPersonCount; ?>-role" class="pp-settings-select" style="width: 100% !important;" aria-label="Default select example" >
                                            <?php printSelectOptionsRoles($personRoles[$i]) ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <input type="text" value="<?php echo htmlspecialchars($personURLs[$i]); ?>" name="role-<?php echo $currentPersonCount; ?>-personurl" class="pp-settings-text-input"  />
                                    </div>
                                    <div class="col-lg-3">
                                        <input type="text" value="<?php echo htmlspecialchars($linkURLs[$i]); ?>" name="role-<?php echo $currentPersonCount; ?>-linkurl" class="pp-settings-text-input"  />
                                    </div>
                                    <div class="col-lg-1 pl-0">
                                        <?php if ($currentPersonCount > 1) {
                                            ?>
                                            <button class="float-left pl-0" type="button" style="border: none; background: inherit; color: red; font-size: 25px;" id="remove-role-<?php echo $currentPersonCount; ?>" name="remove-role-<?php echo $currentPersonCount; ?>">&times;</button>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }?>
                <button type="button" style="border: none; background: inherit; color: #1976D2;" id="newrole" name="newrole"><?php echo __("+ Add Role", "powerpress"); ?></button>
            </div>
        </div>
    </div>
    <div class="pp-settings-section">
        <h2>
            <?php echo __("Block", "powerpress"); ?>
        </h2>
        <h3 style="font-weight: bold;">
            <?php echo __("Before you Block", "powerpress"); ?>
        </h3>
        <p>
            <?php echo __("Be aware that blocking signals to specific sites that you do not want your podcast to be found there. However, not all services honor the block and you may have to manually request for your content to be removed from these platforms.", "powerpress"); ?>
        </p>
        <div style="display: flex; align-items: center; margin-top: 20px;">
            <input class="pp-settings-checkbox" style="margin-top: 0; margin-right: 10px;" type="checkbox" value="1" id="block-check" name="Feed[block]" <?php echo isset($FeedSettings["block"]) && intval($FeedSettings["block"]) == 1 ? "checked" : "" ?>>
            <label class="form-check-label" for="block" style="color: black; font-size: 1rem;">
                <?php echo __("I understand what blocking services/directories entails.", "powerpress"); ?>
            </label>
        </div>
        <div id="block-section" class="mt-2" style="display: <?php echo isset($FeedSettings["block"]) && intval($FeedSettings["block"]) == 1 ? "" : "none" ?>;">
            <div style="display: flex; align-items: center; margin-top: 20px;">
                <input class="pp-settings-checkbox" style="margin-top: 0; margin-right: 10px;" type="checkbox" value="1" id="block-all-check" name="Feed[block_all]" <?php echo isset($FeedSettings["block_all"]) && intval($FeedSettings["block_all"]) == 1 ? "checked" : "" ?>>
                <label class="form-check-label" for="block-all-check" style="color: black; font-size: 1rem;">
                    <?php echo __("I would like to block all directories.", "powerpress"); ?>
                </label>
            </div>
            <div class="row" id="block-list" style="margin-top: 20px; display: <?php echo isset($FeedSettings["block_all"]) && intval($FeedSettings["block_all"]) == 1 ? "none" : "" ?>;">
                <?php
                $blockList = getBlockTaxonomy();
                $existingBlockList = explode(';', $FeedSettings['block_list'] ?? '');
                ?>
                <div class="col-md-6">
                    <h4 style="font-weight: bold;">
                        <?php echo __("Directories", "powerpress"); ?>
                    </h4>
                    <input type="text" id="search-directories" class="form-control" onkeyup="searchList('search-directories', 'search-results')" placeholder="Search for names..">
                    <ul id="search-results">
                        <?php foreach ($blockList as $blockDir) {
                            ?>
                            <li style="display: none;" class="search-result" id="li-<?php echo $blockDir; ?>"><a><?php echo $blockDir;?></a></li>
                            <?php
                        } ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h4 style="font-weight: bold;">Block List</h4>
                    <div class="col" style="border-radius: 5px; border: 1px solid #E2E2E2;" id="block-list-col">
                        <?php
                        $blockCount = 0;
                        if ($existingBlockList[0] != '') {
                            foreach ($existingBlockList as $blockDir) { ?>
                                <div id="block-<?php echo $blockDir?>">
                                    <div class="row" style="padding-left: 5%; padding-right: 5%; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd;">
                                        <h4 style="margin: 0;">
                                            <?php echo __($blockDir, "powerpress"); ?>
                                        </h4>
                                        <input type="hidden" name="Feed[block_list][]" value="<?php echo $blockDir?>" />
                                        <button type="button" style="border: none; background: inherit; color: red; font-size: 25px; cursor: pointer;" id="remove-block-<?php echo $blockDir; ?>">&times;</button>
                                    </div>
                                </div>
                                <?php
                                $blockCount += 1;
                            }
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!--  Donate link and label -->
    <div class="pp-settings-section">
        <h2><?php echo __('Donate Link', 'powerpress'); ?> </h2>
        <label for="donate_link"></label>
        <input class="pp-settings-checkbox" style="margin-top: 2.5ch;" type="checkbox" id="donate_link" name="Feed[donate_link]" value="1" <?php if( $FeedSettings['donate_link'] == 1 ) echo 'checked '; ?>/>
        <div class="pp-settings-subsection">
	        <p class="pp-main"><?php echo __('Syndicate a donate link with your podcast. Create your own crowdfunding page with PayPal donate buttons, or link to a service such as Patreon.', 'powerpress'); ?></p>
        </div>
        <br />
	    <label for="donate_url" class="pp-settings-label"><?php echo __('Donate URL', 'powerpress'); ?></label>
        <input class="pp-settings-text-input" type="text" id="donate_url" value="<?php echo esc_attr($FeedSettings['donate_url']); ?>" name="Feed[donate_url]" />
	    <label for="donate_label" class="pp-settings-label"><?php echo __('Donate Label', 'powerpress'); ?></label>
        <input class="pp-settings-text-input" type="text" id="donate_label" value="<?php echo esc_attr($FeedSettings['donate_label']); ?>" name="Feed[donate_label]" />
        <label for="donate_label" class="pp-settings-label-under"><?php echo __('optional', 'powerpress'); ?></label>
	    <p class="pp-settings-text" style="margin-top: 1em;"><a href="https://blubrry.com/support/powerpress-documentation/syndicating-a-donate-link-in-your-podcast/" target="_blank"><?php echo __('Learn more about syndicating donate links for podcasting', 'powerpress'); ?></a></p>
    </div>
    <script>
        let currentRoleCount = <?php echo $currentPersonCount; ?>;

        <?php
        $blockListStr = '[';
        $first = true;
        foreach ($existingBlockList as $block) {
            if (!$first)
                $blockListStr .= ',';

            $blockListStr .= "'$block'";

            $first = false;
        }
        $blockListStr .= ']';
        ?>
        let currentBlockList = <?php echo $blockListStr; ?>;

        function searchList(searchId, resultsId) {
            // Declare variables
            var input, filter, ul, li, a, i, txtValue;
            input = document.getElementById(searchId);
            filter = input.value.toUpperCase();
            ul = document.getElementById(resultsId);
            li = ul.getElementsByTagName('li');

            // Loop through all list items, and hide those who don't match the search query
            for (i = 0; i < li.length; i++) {
                a = li[i].getElementsByTagName("a")[0];
                txtValue = a.textContent || a.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1 && filter !== '' && !currentBlockList.includes(txtValue)) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";
                }
            }
        }

        jQuery(document).ready(function() {
            jQuery('[id*="li-"]').on('click', function() {
                let name = this.id.substring(3);
                let newHTML = '<div id="block-' + name + '">';
                newHTML += '<div class="row" style="padding-left: 5%; padding-right: 5%; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd;">';
                newHTML += '<h4 style="margin: 0;">' + name + '</h4>';
                newHTML += '<input type="hidden" name="Feed[block_list][]" value="' + name + '" />';
                newHTML += '<button type="button" style="border: none; background: inherit; color: red; font-size: 25px; cursor: pointer;" id="remove-block-'+name+'">&times;</button>';
                newHTML += '</div>';
                newHTML += '</div>';

                jQuery('#block-list-col').append(newHTML);

                currentBlockList.push(name);

                let ul = document.getElementById("search-results");
                let li = ul.getElementsByTagName('li');

                // hide search results after adding
                for (let i = 0; i < li.length; i++) {
                    let a = li[i].getElementsByTagName("a")[0];
                    let txtValue = a.textContent || a.innerText;

                    if (txtValue === name)
                        li[i].style.display = "none";
                }
            });

            jQuery(document).on('click',"[id*='remove-block-']", function (e) {
                let name = this.id.substring(13);
                jQuery('#block-' + name).remove();

                let index = currentBlockList.indexOf(name);
                currentBlockList.splice(index, 1);

                let input = document.getElementById('search-directories');
                let filter = input.value.toUpperCase();
                let ul = document.getElementById("search-results");
                let li = ul.getElementsByTagName('li');

                // hide search results after adding
                for (let i = 0; i < li.length; i++) {
                    let a = li[i].getElementsByTagName("a")[0];
                    let txtValue = a.textContent || a.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1 && filter !== '' && txtValue === name) {
                        li[i].style.display = "";
                    }
                }
            });

            jQuery('#block-check').on('change', function () {
                if (this.checked)
                    jQuery('#block-section').show();
                else
                    jQuery('#block-section').hide();
            });

            jQuery('#block-all-check').on('change', function () {
                if (this.checked)
                    jQuery('#block-list').hide();
                else
                    jQuery('#block-list').show();
            });

            jQuery('#daily').on('click', function() {
                jQuery('#weekly-select').hide();
                jQuery('#monthly-frequency').hide();
            });

            jQuery('#weekly').on('click', function() {
                jQuery('#weekly-select').show();
                jQuery('#monthly-frequency').hide();
            });

            jQuery('#monthly').on('click', function() {
                jQuery('#weekly-select').hide();
                jQuery('#monthly-frequency').show();
            });

            jQuery('#update_frequency_month').on('change', function() {
                let value = this.value;

                if (value < 1)
                    this.value = 1;
            });

            jQuery(document).on('click',"[name*='remove-role-']",function (e) {
                currentRoleCount -= 1;
                let roleNum = this.id[this.id.length - 1];
                jQuery("#role-" + roleNum + "-container").css({"visibility": "hidden", "position": "absolute"});
                jQuery("[name='role-" + roleNum + "-name']").val("");
            });

            jQuery("[name='newrole']").click(function (e) {
                currentRoleCount += 1;

                if (jQuery("[name='role-" + currentRoleCount + "-name']").length) {
                    jQuery("[name='role-" + currentRoleCount + "-name']").val("");
                    jQuery("[name='role-" + currentRoleCount + "-personurl']").val("");
                    jQuery("[name='role-" + currentRoleCount + "-linkurl']").val("");
                    jQuery("#role-"+currentRoleCount+"-container").css({"visibility": "visible", "position": "static"});
                } else {
                    let newHTML = '<div id="role-' + currentRoleCount + '-container">' +
                        '<div class="row">' +
                        '<div class="col-lg-3">' +
                        '<label for="role-' + currentRoleCount + '-name" style="margin: 0;"><?php echo __("Author/Artist", "powerpress"); ?></label>' +
                        '</div>' +
                        '<div class="col-lg-2" style="padding: 0;">' +
                        '<label for="role-' + currentRoleCount + '-role" style="margin: 0;"><?php echo __("Role", "powerpress"); ?></label>' +
                        '</div>' +
                        '<div class="col-lg-3">' +
                        '<label for="role-' + currentRoleCount + '-personurl" style="margin: 0;"><?php echo __("Person Image URL", "powerpress"); ?></label>' +
                        '</div>' +
                        '<div class="col-lg-3">' +
                        '<label for="role-' + currentRoleCount + '-linkurl" style="margin: 0;"><?php echo __("Link URL", "powerpress"); ?></label>' +
                        '</div>' +
                        '<div class="col-lg-1"></div>' +
                        '</div>' +
                        '<div class="row" style="display: flex; align-items: center;">' +
                        '<div class="col">' +
                        '<div class="row" style="display: flex; align-items: center; margin-bottom: 15px;">' +
                        '<div class="col-lg-3">' +
                        '<input type="text" value="" name="role-' + currentRoleCount + '-name" class="pp-settings-text-input" />' +
                        '</div>' +
                        '<div class="col-lg-2" style="padding: 0;">' +
                        '<select name="role-' + currentRoleCount + '-role" class="pp-settings-select" style="width: 100% !important;" aria-label="Default select example">' +
                        '<?php printSelectOptionsRoles("Host");  ?>' +
                        '</select>' +
                        '</div>' +
                        '<div class="col-lg-3">' +
                        '<input type="text" value="" name="role-' + currentRoleCount + '-personurl" class="pp-settings-text-input"/>' +
                        '</div>' +
                        '<div class="col-lg-3">' +
                        '<input type="text" value="" name="role-' + currentRoleCount + '-linkurl" class="pp-settings-text-input"/>' +
                        '</div>' +
                        '<div class="col-lg-1 pl-0">' +
                        '<button class="float-left pl-0" type="button" style="border: none; background: inherit; color: red; font-size: 25px;" id="remove-role-' + currentRoleCount + '" name="remove-role-' + currentRoleCount + '">&times;</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    let prevId = '#role-' + (currentRoleCount - 1) + '-container';
                    jQuery(newHTML).insertAfter(prevId)
                }
            });
        });
    </script>
<?php
}

function getBlockTaxonomy() {
    $options = ["acast","amazon","anchor","apple","audible","audioboom","backtracks","bitcoin","blubrry","buzzsprout","captivate","castos","castopod","facebook","fireside","fyyd","google","gpodder","hypercatcher","kasts","libsyn","mastodon","megafono","megaphone","omnystudio","overcast","paypal","pinecast","podbean","podcastaddict","podcastguru","podcastindex","podcasts","podchaser","podcloud","podfriend","podiant","podigee","podnews","podomatic","podserve","podverse","redcircle","relay","resonaterecordings","rss","shoutengine","simplecast","slack","soundcloud","spotify","spreaker","tiktok","transistor","twitter","whooshkaa","youtube","zencast"];
    return $options;
}

function printSelectOptionsRoles($selectedOption) {
    $options = [
            "Director", "Assistant Director", "Executive Producer", "Senior Producer", "Producer",
            "Associate Producer", "Development Producer", "Creative Director", "Host", "Co-Host",
            "Guest Host", "Guest", "Voice Actor", "Narrator", "Announcer", "Reporter", "Author",
            "Editorial Director", "Co-Writer", "Writer", "Songwriter", "Guest Writer", "Story Editor",
            "Managing Editor", "Script Editor", "Script Coordinator", "Researcher", "Editor", "Fact Checker",
            "Translator", "Transcriber", "Logger", "Studio Coordinator", "Technical Director", "Technical Manager",
            "Audio Engineer", "Remote Recording Engineer", "Post Production Engineer", "Audio Editor", "Sound Designer",
            "Foley Artist", "Composer", "Theme Music", "Music Production", "Music Contributor", "Production Coordinator",
            "Booking Coordinator", "Production Assistant", "Content Manager", "Marketing Manager", "Sales Representative",
            "Sales Manager", "Graphic Designer", "Cover Art Designer", "Social Media Manager", "Consultant", "Intern",
            "Camera Operator", "Lighting Designer", "Camera Grip", "Assistant Camera", "Editor", "Assistant Editor"
    ];

    foreach ($options as $option) {
        if ($option == $selectedOption) {
            echo '<option selected value="'.$option.'">'.__($option, "powerpress").'</option>';
        } else {
            echo '<option value="'.$option.'">'.__($option, "powerpress").'</option>';
        }
    }
}

function powerpressadmin_edit_tv($FeedSettings = false, $feed_slug='podcast', $cat_ID=false)
{
	if( !isset($FeedSettings['parental_rating']) )
		$FeedSettings['parental_rating'] = '';

?>
<h1 class="pp-heading"><?php echo __('Rating Settings', 'powerpress'); ?></h1>
<p class="pp-settings-text"><?php echo sprintf(__('A parental rating is used to display your content on %s applications available on Internet connected TV\'s. The TV Parental Rating applies to both audio and video media.', 'powerpress'), '<strong><a href="http://www.blubrry.com/roku_blubrry/" target="_blank">Blubrry</a></strong>'); ?></p>
<div class="pp-settings-section" style="border-left: none;">
    <h2><?php echo __('Parental Rating', 'powerpress'); ?>  </h2>
	<?php
	$Ratings = array(''=>__('No rating specified', 'powerpress'),
			'TV-Y'=>__('Children of all ages', 'powerpress'),
			'TV-Y7'=>__('Children 7 years and older', 'powerpress'),
			'TV-Y7-FV'=>__('Children 7 years and older [fantasy violence]', 'powerpress'),
			'TV-G'=>__('General audience', 'powerpress'),
			'TV-PG'=>__('Parental guidance suggested', 'powerpress'),
			'TV-14'=>__('May be unsuitable for children under 14 years of age', 'powerpress'),
			'TV-MA'=>__('Mature audience - may be unsuitable for children under 17', 'powerpress')
		);
	$RatingsTips = array(''=>'',
				'TV-Y'=>__('Whether animated or live-action, the themes and elements in this program are specifically designed for a very young audience, including children from ages 2-6. These programs are not expected to frighten younger children.  Examples of programs issued this rating include Sesame Street, Barney & Friends, Dora the Explorer, Go, Diego, Go! and The Backyardigans.', 'powerpress'),
				'TV-Y7'=>__('These shows may or may not be appropriate for some children under the age of 7. This rating may include crude, suggestive humor, mild fantasy violence, or content considered too scary or controversial to be shown to children under seven. Examples include Foster\'s Home for Imaginary Friends, Johnny Test, and SpongeBob SquarePants.', 'powerpress'),
				'TV-Y7-FV'=>__('When a show has noticeably more fantasy violence, it is assigned the TV-Y7-FV rating. Action-adventure shows such Pokemon series and the Power Rangers series are assigned a TV-Y7-FV rating.', 'powerpress'),
				'TV-G'=>__('Although this rating does not signify a program designed specifically for children, most parents may let younger children watch this program unattended. It contains little or no violence, no strong language and little or no sexual dialogue or situation. Networks that air informational, how-to content, or generally inoffensive content.', 'powerpress'),
				'TV-PG'=>__('This rating signifies that the program may be unsuitable for younger children without the guidance of a parent. Many parents may want to watch it with their younger children. Various game shows and most reality shows are rated TV-PG for their suggestive dialog, suggestive humor, and/or coarse language. Some prime-time sitcoms such as Everybody Loves Raymond, Fresh Prince of Bel-Air, The Simpsons, Futurama, and Seinfeld  usually air with a TV-PG rating.', 'powerpress'),
				'TV-14'=>__('Parents are strongly urged to exercise greater care in monitoring this program and are cautioned against letting children of any age watch unattended. This rating may be accompanied by any of the following sub-ratings:', 'powerpress'),
				'TV-MA'=>__('A TV-MA rating means the program may be unsuitable for those below 17. The program may contain extreme graphic violence, strong profanity, overtly sexual dialogue, very coarse language, nudity and/or strong sexual content. The Sopranos is a popular example.', 'powerpress')
		);
			
	
	foreach( $Ratings as $rating => $title )
	{
		$tip = $RatingsTips[ $rating ];
		if (!$rating) {
		    $style = "style=\"margin-bottom:\"";
        }
?>
    <div>
        <input class="pp-settings-radio" type="radio" name="Feed[parental_rating]" value="<?php echo $rating; ?>" <?php if( $FeedSettings['parental_rating'] == $rating) echo 'checked'; ?> />
        <div class="pp-settings-subsection">
            <p class="pp-main">
                <?php if( $rating ) { ?>
                    <strong><?php echo $rating; ?></strong>
                <?php } else { ?>
                    <strong><?php echo htmlspecialchars($title); ?></strong>
                <?php } ?>
            </p>
            <?php if( $rating ) { ?>
                <p class="pp-sub">
                    <?php echo htmlspecialchars($title); ?>
                </p>
            <?php } else { ?>
                <br />
            <?php  } ?>
        </div>
    </div>
	<?php
	}
?>
</div>

<?php
}

function powerpressadmin_edit_artwork($FeedSettings, $General)
{
	$SupportUploads = powerpressadmin_support_uploads();
?>

<h1 class="pp-heading"><?php echo __('Podcast Artwork', 'powerpress'); ?></h1>


<div class="pp-settings-section">
    <h2><?php echo __('Podcast Artwork', 'powerpress'); ?></h2>
    <label for="Feed[itunes_image]" class="pp-settings-label"><?php echo __('Artwork URL', 'powerpress'); ?></label>
    <input class="pp-settings-text-input" type="text" id="itunes_image" name="Feed[itunes_image]" value="<?php echo esc_attr( !empty($FeedSettings['itunes_image'])? $FeedSettings['itunes_image']:''); ?>" maxlength="255" />
    <label for="Feed[itunes_image]" class="pp-settings-label-under"><?php echo __('Apple Podcast image must be at least 1400 x 1400 pixels in .jpg or .png format. Apple Podcast image must not exceed 3000 x 3000 pixels and must use RGB color space. The filesize should not exceed 0.5MB.', 'powerpress'); ?></label>

    <?php if( $SupportUploads ) { ?>
    <input name="itunes_image_checkbox" id="itunes_image_checkbox" type="hidden" value="0" />
    <div id="itunes_image_upload">
        <div>
            <div class="pp-settings-button">
                <label class="pp-settings-button-label" for="itunes_image_file">
                    <img class="pp-settings-icon" src="<?php echo powerpress_get_root_url(); ?>images/cloud_up.svg" alt="">
                    <?php echo __('Upload Image', 'powerpress'); ?>
                </label>
                <input type="file" id="itunes_image_file" name="itunes_image_file" accept="image/*" class="pp_file_upload" style="display: none" />
            </div>
        </div>
    </div>
        <!--<a href="#" onclick="javascript: window.open( document.getElementById('itunes_image').value ); return false;"><?php echo __('preview', 'powerpress'); ?></a>-->
    <?php } ?>
</div>

    <script>
        document.getElementById('itunes_image_file').onchange = function (event) {
            document.getElementById('itunes_image').value = this.value.replace("C:\\fakepath\\", "");
            let checkbox_id = "itunes_image_checkbox";
            if (event.currentTarget.value.length > 0) {
                document.getElementById(checkbox_id).value = 1;
            }
        };
        document.getElementById('itunes_image').onchange = function(event) {
            let checkbox_id = "itunes_image_checkbox";
            if (event.currentTarget.value.length > 0) {
                document.getElementById(checkbox_id).value = 1;
            } else {
                document.getElementById(checkbox_id).value = 0;
            }
        };
    </script>
<?php

}


function powerpressadmin_edit_destinations($FeedSettings, $General, $FeedAttribs)
{
	require_once( dirname(__FILE__).'/views/settings_tab_destinations.php' );
}

