<?php

if(!function_exists('add_action')){
    die("access denied.");
}

function powerpress_admin_posttypefeeds_columns($data=array()){
	$data['name'] = __('Feed Title', 'powerpress');
	$data['post-type'] = __('Post Type', 'powerpress');
	$data['feed-slug'] = __('Slug', 'powerpress');
	$data['url'] = __('Feed URL', 'powerpress');
	return $data;
}

add_filter('manage_powerpressadmin_posttypefeeds_columns', 'powerpress_admin_posttypefeeds_columns');

function powerpress_admin_posttypefeeds(){
	$post_types = powerpress_admin_get_post_types(false);

    // If we have powerpress credentials, check if the account has been verified
    $creds = get_option('powerpress_creds');
    powerpress_check_credentials($creds);
    wp_enqueue_script('powerpress-admin', powerpress_get_root_url() . 'js/admin.js', array(), POWERPRESS_VERSION );
    ?>

    <h2 class="pp-page-header"><?php echo __('Post Type Podcasting', 'powerpress'); ?></h2>
    <h3 class="pp-page-h3"><?php echo __('Post Type Podcasting adds custom podcast settings to specific Post Type feeds.', 'powerpress'); ?></h3>

    <div class="pp-card-body">
        <div class="pp-row pp-tools-row">
            <div class="pp-col-50">
                <div class="pp-row">
                    <h2 class="pp-page-sub-header">Add Podcasting to a custom Post Type</h2>
                </div>
                <div class="pp-row">
                    <div class="form-wrap">
                        <h3><?php echo __('Add Podcasting to a custom Post Type', 'powerpress'); ?></h3>
                        <input type="hidden" name="action" value="powerpress-addposttypefeed" />
                        <div class="form-field form-required">
                            <label style="font-size: 14px;" for="powerpress_post_type_select"><?php echo __('Post Type', 'powerpress'); ?></label>
                            <select id="powerpress_post_type_select" name="podcast_post_type" style="width: 95%;">
                                <option value=""><?php echo __('Select Post Type', 'powerpress'); ?></option>
                                    <?php
                                        reset($post_types);
                                        foreach($post_types as $null => $post_type){
                                            if($post_type == 'post'){
                                                continue;
                                            }

                                            $post_type = htmlspecialchars($post_type);
                                            echo "\t<option value=\"$post_type\">$post_type</option>\n";
                                        }
                                    ?>
                            </select>
                        </div>

                        <div class="form-field form-required">
                            <label style="font-size: 14px;" for="feed_title"><?php echo __('Feed Title', 'powerpress') ?></label>
                            <input name="feed_title" id="feed_title" type="text" value="" size="100"/>
                        </div>

                        <div class="form-field">
                            <label style="font-size: 14px;" for="feed_slug"><?php echo __('Feed Slug', 'powerpress') ?></label>
                            <input name="feed_slug" id="feed_slug" type="text" value="" size="40"/>
                            <p><?php echo __('The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'powerpress'); ?></p>
                        </div>

                        <?php wp_nonce_field('powerpress-add-posttype-feed'); ?>

                        <p class="submit"><input type="submit" class="powerpress_save_button_other" name="add_podcasting" value="<?php echo __('Add Podcasting to Post Type', 'powerpress'); ?>" /></p>
                    </div>
                </div>
            </div>

            <div class="pp-col-50">
                <div class="pp-row">
                    <h2 class="pp-page-sub-header">Why would I use Post Type?</h2>
                </div>
                <ul style="list-style: unset; padding-left: 20px;">
                    <li>
                        <h3 style="font-weight: 400;">You have a podcast that covers two topics that sometimes share same posts and sometimes do not. Use your main podcast feed as a combined feed of both topics 	and use category feeds to distribute topic specific episodes.</h3>
                    </li>
                     <li>
                        <h3 style="font-weight: 400;">You want to use categories to keep episodes separate from each other. Each category can be used to distribute separate podcasts with the main podcast feed combining all categories to provide a network feed</h3>
                    </li>
                </ul>
            </div>
        </div>

        <hr style="margin-bottom: 40px;">

        <div class="pp-row">
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <?php print_column_headers('powerpressadmin_posttypefeeds'); ?>
	                </tr>
	            </thead>

	            <tfoot>
	                <tr>
                        <?php print_column_headers('powerpressadmin_posttypefeeds', false); ?>
	                </tr>
	            </tfoot>

	            <tbody>
                    <?php
                        $count = 0;
                        foreach($post_types as $null => $post_type){
                            $PostTypeSettingsArray = get_option('powerpress_posttype_'. $post_type );
                            if(!$PostTypeSettingsArray){
                                continue;
                            }

                            foreach($PostTypeSettingsArray as $feed_slug => $PostTypeSettings){
                                $feed_title = ( !empty($PostTypeSettings['title']) ? $PostTypeSettings['title'] : '(blank)');
                                $columns = powerpress_admin_posttypefeeds_columns();
                                $hidden = array();

                                if($count % 2 == 0){
                                    echo '<tr valign="middle" class="alternate">';
                                } else {
                                    echo '<tr valign="middle">';
                                }

                                $edit_link = admin_url('admin.php?page='. powerpress_admin_get_page() .'&amp;action=powerpress-editposttypefeed&amp;feed_slug='. $feed_slug .'&podcast_post_type='.$post_type) ;

                                $url = get_post_type_archive_feed_link($post_type, $feed_slug);
                                if(empty($url)) {
                                    $url = '';
                                    $short_url = '';
                                } else {
                                    $short_url = str_replace('http://', '', $url);
                                    $short_url = str_replace('www.', '', $short_url);
                                    if(strlen($short_url) > 35){
                                        $short_url = substr($short_url, 0, 32).'...';
                                    }
                                }

                                foreach($columns as $column_name=>$column_display_name){
                                    $class = "class=\"column-$column_name\"";

                                    switch($column_name){
                                        case 'feed-slug': {
                                            echo "<td $class>{$feed_slug}";
                                            echo "</td>";
                                        } break;

                                        case 'name': {
                                            echo '<td '.$class.'><strong><a class="row-title" href="'.$edit_link.'" title="' . esc_attr(sprintf(__('Edit "%s"', 'powerpress'), $feed_title)) . '">'.esc_attr($feed_title).'</a></strong><br />';
                                            $actions = array();
                                            $actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit', 'powerpress') . '</a>';
                                            $actions['remove'] = "<a class='submitdelete' href='". admin_url() . wp_nonce_url("admin.php?page=". powerpress_admin_get_page() ."&amp;action=powerpress-delete-posttype-feed&amp;podcast_post_type={$post_type}&amp;feed_slug={$feed_slug}", 'powerpress-delete-posttype-feed-'.$post_type .'_'.$feed_slug) . "' onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to remove podcast settings for Post Type '%s'\n  'Cancel' to stop, 'OK' to delete.", 'powerpress'), esc_attr($feed_title) )) . "') ) { return true;}return false;\">" . __('Remove', 'powerpress') . "</a>";
                                            $action_count = count($actions);
                                            $i = 0;
                                            echo '<div class="row-actions">';
                                            foreach ( $actions as $action => $linkaction){
                                                ++$i;
                                                ($i == $action_count) ? $sep = '' : $sep = ' | ';
                                                echo '<span class="'.$action.'">'.$linkaction.$sep .'</span>';
                                            }
                                            echo '</div>';
                                            echo '</td>';

                                        } break;

                                        case 'url': {
                                            echo "<td $class><a href='$url' title='". esc_attr(sprintf(__('Visit %s', 'powerpress'), $feed_title))."' target=\"_blank\">$short_url</a>";
                                                echo '<div class="row-actions">';
                                                if( defined('POWERPRESS_FEEDVALIDATOR_URL') ) { // http://www.feedvalidator.org/check.cgi?url=
                                                    echo '<span class="'.$action .'"><a href="'. POWERPRESS_FEEDVALIDATOR_URL . urlencode( str_replace('&amp;', '&', $url) ) .'" target="_blank">' . __('Validate Feed', 'powerpress') . '</a></span>';
                                                }
                                                echo '</div>';
                                            echo "</td>";

                                        } break;

                                        case 'episode-count': {
                                            echo "<td $class>$episode_total";
                                            echo "</td>";
                                        } break;

                                        case 'post-type': {
                                            echo "<td $class>$post_type";
                                            echo "</td>";
                                        } break;

                                        default: {
                                        } break;
                                    }
                                }
                                echo "\n    </tr>\n";
                                $count++;
                            }
                        }
                    ?>
	            </tbody>
            </table>
        </div>
    </div>

<?php } ?>

<style>
    .pp-col-50 {
        width: 50%;
    }
</style>
